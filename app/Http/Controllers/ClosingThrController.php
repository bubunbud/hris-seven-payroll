<?php

namespace App\Http\Controllers;

use App\Models\PeriodeThr;
use App\Models\ClosingThr;
use App\Models\Karyawan;
use App\Models\Gapok;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClosingThrController extends Controller
{
    /**
     * Mapping agama ke kategori THR
     */
    private function mapAgamaToKategori($agama)
    {
        $agama = trim($agama ?? '');
        
        $mapping = [
            'Islam' => 'Islam (Idul Fitri)',
            'Kristen' => 'Kristen (Natal)',
            'Hindu' => 'Hindu (Nyepi)',
            'Budha' => 'Budha (Waisak)',
        ];
        
        return $mapping[$agama] ?? 'Lainnya';
    }

    /**
     * Hitung masa kerja dalam format Tahun, Bulan, Hari
     */
    private function calculateMasaKerja($tanggalCutoff, $tanggalMasuk)
    {
        // Jika tanggal masuk null atau kosong, return masa kerja = 0
        if (empty($tanggalMasuk)) {
            return [
                'tahun' => 0,
                'bulan' => 0,
                'hari' => 0,
                'total_hari' => 0,
                'total_bulan' => 0.0,
                'total_tahun' => 0.0,
                'format_text' => '0 Tahun, 0 Bulan, 0 Hari'
            ];
        }
        
        try {
            $cutoff = Carbon::parse($tanggalCutoff);
            $masuk = Carbon::parse($tanggalMasuk);
        } catch (\Exception $e) {
            Log::error('Error parsing tanggal in calculateMasaKerja', [
                'tanggalCutoff' => $tanggalCutoff,
                'tanggalMasuk' => $tanggalMasuk,
                'error' => $e->getMessage()
            ]);
            return [
                'tahun' => 0,
                'bulan' => 0,
                'hari' => 0,
                'total_hari' => 0,
                'total_bulan' => 0.0,
                'total_tahun' => 0.0,
                'format_text' => '0 Tahun, 0 Bulan, 0 Hari'
            ];
        }
        
        // Jika tanggal masuk lebih besar dari cutoff, masa kerja = 0
        if ($masuk->greaterThan($cutoff)) {
            return [
                'tahun' => 0,
                'bulan' => 0,
                'hari' => 0,
                'total_hari' => 0,
                'total_bulan' => 0.0,
                'total_tahun' => 0.0,
                'format_text' => '0 Tahun, 0 Bulan, 0 Hari'
            ];
        }
        
        // Hitung selisih
        $diff = $masuk->diff($cutoff);
        
        $tahun = $diff->y;
        $bulan = $diff->m;
        $hari = $diff->d;
        
        // Total dalam hari
        $totalHari = $masuk->diffInDays($cutoff);
        
        // Total dalam bulan (desimal)
        // Rata-rata 1 bulan = 30.44 hari (365.25 / 12)
        $totalBulan = round($totalHari / 30.44, 2);
        
        // Total dalam tahun (desimal)
        $totalTahun = round($totalHari / 365.25, 2);
        
        // Format text
        $formatText = "{$tahun} Tahun, {$bulan} Bulan, {$hari} Hari";
        
        return [
            'tahun' => $tahun,
            'bulan' => $bulan,
            'hari' => $hari,
            'total_hari' => $totalHari,
            'total_bulan' => $totalBulan,
            'total_tahun' => $totalTahun,
            'format_text' => $formatText
        ];
    }

    /**
     * Hitung THR berdasarkan masa kerja
     * - >= 12 bulan: THR = 1 bulan upah (decXGaji = 1.0)
     * - < 12 bulan: THR = (Masa Kerja / 12) x 1 bulan upah (decXGaji = roundup(masa_kerja_bulan / 12))
     */
    private function calculateTHR($masaKerjaBulan, $gajiPokok)
    {
        if ($masaKerjaBulan >= 12) {
            $decXGaji = 1.0;
        } else {
            // Roundup: (masa_kerja_bulan / 12) dibulatkan ke atas ke 2 desimal
            $ratio = $masaKerjaBulan / 12;
            $decXGaji = ceil($ratio * 100) / 100; // Roundup ke 2 desimal
        }
        
        $decNilaiTHR = round($gajiPokok * $decXGaji, 2);
        
        return [
            'decXGaji' => $decXGaji,
            'decNilaiTHR' => $decNilaiTHR
        ];
    }

    /**
     * Display the closing THR form
     */
    public function index()
    {
        // Ambil semua periode THR yang belum diproses (vcStatus = '0')
        $periodes = PeriodeThr::with('divisi')
            ->where('vcStatus', '0') // Hanya yang belum diproses
            ->orderBy('dtPeriode', 'desc')
            ->orderBy('dtKategori', 'asc')
            ->orderBy('vcKodeDivisi', 'asc')
            ->get();

        // Cek apakah sudah ada closing THR untuk setiap periode
        $periodesWithStatus = $periodes->map(function ($periode) {
            try {
                // Cek dengan cara yang lebih sederhana: langsung cek di t_closing_thr berdasarkan dtTanggalTHR dan vcKodeDivisi
                $closingExists = false;
                if ($periode->dtCutoffTHR && $periode->vcKodeDivisi) {
                    $closingExists = ClosingThr::where('dtTanggalTHR', $periode->dtCutoffTHR->format('Y-m-d'))
                        ->where('vcKodeDivisi', $periode->vcKodeDivisi)
                        ->exists();
                }
                
                return [
                    'periode' => $periode,
                    'closing_exists' => $closingExists
                ];
            } catch (\Exception $e) {
                Log::error('Error checking closing status', [
                    'periode' => $periode->toArray(),
                    'error' => $e->getMessage()
                ]);
                return [
                    'periode' => $periode,
                    'closing_exists' => false
                ];
            }
        });

        return view('proses.closing-thr.index', compact('periodesWithStatus'));
    }

    /**
     * Process THR calculation (store)
     * Method ini akan melakukan perhitungan THR untuk Operator berdasarkan periode yang dipilih
     */
    public function store(Request $request)
    {
        try {
            // Log request untuk debugging
            Log::info('Closing THR Store Request', [
                'all' => $request->all(),
                'input_periodes' => $request->input('periodes'),
                'content_type' => $request->header('Content-Type'),
                'is_json' => $request->isJson(),
            ]);
        } catch (\Exception $e) {
            // Ignore logging error
        }
        
        try {
            // Baca request body sebagai JSON - gunakan cara yang lebih aman
            $periodes = [];
            
            // Baca langsung dari request content (paling reliable)
            $content = $request->getContent();
            if (!empty($content)) {
                try {
                    $decoded = json_decode($content, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded['periodes'])) {
                        $periodes = $decoded['periodes'];
                    }
                } catch (\Exception $e) {
                    Log::warning('Error decoding JSON from content', ['error' => $e->getMessage()]);
                }
            }
            
            // Jika masih kosong, coba dari json() method
            if (empty($periodes)) {
                try {
                    if ($request->isJson()) {
                        $jsonData = $request->json()->all();
                        $periodes = $jsonData['periodes'] ?? [];
                    }
                } catch (\Exception $e) {
                    Log::warning('Error reading from json()', ['error' => $e->getMessage()]);
                }
            }
            
            // Jika masih kosong, coba dari input biasa
            if (empty($periodes)) {
                $periodes = $request->input('periodes', []);
            }
            
            // Validasi
            if (!is_array($periodes)) {
                Log::error('Periodes is not array', [
                    'periodes' => $periodes,
                    'type' => gettype($periodes),
                    'request_all' => $request->all()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Format data tidak valid. Periodes harus berupa array.'
                ], 422);
            }
            
            if (count($periodes) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pilih minimal 1 periode yang akan diproses!'
                ], 422);
            }

            // Validasi setiap item
            foreach ($periodes as $key => $periodeKey) {
                if (!is_string($periodeKey) && !is_numeric($periodeKey)) {
                    Log::error('Invalid periode format', [
                        'key' => $key,
                        'value' => $periodeKey,
                        'type' => gettype($periodeKey)
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => "Format periode tidak valid pada index {$key}: " . gettype($periodeKey)
                    ], 422);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error parsing request in store', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error parsing request: ' . $e->getMessage()
            ], 500);
        }

        DB::beginTransaction();
        try {
            $processed = 0;
            $errors = [];
            $totalKaryawan = 0;

            foreach ($periodes as $index => $periodeKey) {
                try {
                    // Convert to string
                    if (!is_string($periodeKey) && !is_numeric($periodeKey)) {
                        $errors[] = "Format periode tidak valid pada index {$index}: " . gettype($periodeKey);
                        continue;
                    }
                    
                    $periodeKey = (string) $periodeKey;
                    
                    // Validate format
                    if (empty($periodeKey)) {
                        $errors[] = "Periode key kosong pada index {$index}";
                        continue;
                    }
                    
                    $parts = explode('|', $periodeKey);
                    if (count($parts) != 3) {
                        $errors[] = "Format periode tidak valid (harus 3 bagian dipisah |): {$periodeKey}";
                        continue;
                    }

                    // Extract parts - pastikan $parts adalah array dan ada elemennya
                    if (!is_array($parts) || count($parts) < 3) {
                        $errors[] = "Format periode tidak valid (harus 3 bagian dipisah |): {$periodeKey}";
                        continue;
                    }
                    
                    $dtPeriode = isset($parts[0]) ? trim((string) $parts[0]) : '';
                    $dtKategori = isset($parts[1]) ? trim((string) $parts[1]) : '';
                    $vcKodeDivisi = isset($parts[2]) ? trim((string) $parts[2]) : '';
                    
                    // Validate parts
                    if (empty($dtPeriode) || empty($dtKategori) || empty($vcKodeDivisi)) {
                        $errors[] = "Data periode tidak lengkap: {$periodeKey}";
                        continue;
                    }

                    // Ambil data periode THR
                    $periodeThr = PeriodeThr::where('dtPeriode', $dtPeriode)
                        ->where('dtKategori', $dtKategori)
                        ->where('vcKodeDivisi', $vcKodeDivisi)
                        ->first();

                    if (!$periodeThr) {
                        $errors[] = "Periode THR tidak ditemukan: {$periodeKey}";
                        continue;
                    }

                    if ($periodeThr->vcStatus == '1') {
                        $errors[] = "Periode THR sudah diproses: {$periodeKey}";
                        continue;
                    }

                // Proses perhitungan THR untuk Operator dan Staff
                $result = $this->processTHR($periodeThr);
                    
                    // Pastikan result adalah array dengan key yang diperlukan
                    if (!is_array($result)) {
                        $errors[] = "Error: Invalid result format for periode {$periodeKey}";
                        Log::error("Invalid result format", ['result' => $result, 'type' => gettype($result)]);
                        continue;
                    }
                    
                    if (!isset($result['success'])) {
                        $errors[] = "Error: Missing 'success' key in result for periode {$periodeKey}";
                        Log::error("Missing success key", ['result' => $result]);
                        continue;
                    }
                    
                    if ($result['success']) {
                        $processed++;
                        $totalKaryawan += isset($result['total_karyawan']) ? (int)$result['total_karyawan'] : 0;
                        
                        // Update status periode menjadi sudah diproses
                        // Gunakan DB::table() karena model menggunakan composite primary key
                        DB::table('t_periode_thr')
                            ->where('dtPeriode', $dtPeriode)
                            ->where('dtKategori', $dtKategori)
                            ->where('vcKodeDivisi', $vcKodeDivisi)
                            ->update(['vcStatus' => '1']);
                    } else {
                        $errors[] = isset($result['message']) ? $result['message'] : "Error processing periode {$periodeKey}";
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error processing periode {$periodeKey}: " . $e->getMessage();
                    Log::error("Error in store loop", [
                        'periodeKey' => $periodeKey,
                        'index' => $index,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            DB::commit();

            $message = "Closing THR berhasil diproses. ";
            $message .= "Diproses: {$processed} periode. ";
            $message .= "Total karyawan: {$totalKaryawan}.";
            if (count($errors) > 0) {
                $message .= " Error: " . implode(', ', $errors);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'processed' => $processed,
                'total_karyawan' => $totalKaryawan,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log error dengan detail lengkap
            $requestJson = null;
            try {
                if ($request->isJson()) {
                    $requestJson = $request->json()->all();
                }
            } catch (\Exception $jsonError) {
                // Ignore JSON parsing error
            }
            
            Log::error('Error processing closing THR', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_all' => $request->all(),
                'request_input' => $request->input(),
                'request_json' => $requestJson,
            ]);
            
            // Return error message yang lebih informatif
            $errorMessage = 'Terjadi kesalahan saat memproses closing THR.';
            if (config('app.debug')) {
                $errorMessage .= ' ' . $e->getMessage() . ' (File: ' . basename($e->getFile()) . ', Line: ' . $e->getLine() . ')';
            } else {
                $errorMessage .= ' Silakan hubungi administrator atau cek log untuk detail.';
            }
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 500);
        }
    }

    /**
     * Proses perhitungan THR untuk semua group pegawai (Management, Staff, Operator, Security)
     * - Operator & Security: hitung decGajiPokok dan decNilaiTHR
     * - Management & Staff: hanya hitung decXGaji (masa kerja), decGajiPokok dan decNilaiTHR = null
     */
    private function processTHR($periodeThr)
    {
        try {
            // Validasi input
            if (!$periodeThr || !is_object($periodeThr)) {
                return [
                    'success' => false,
                    'message' => 'Periode THR tidak valid',
                    'total_karyawan' => 0
                ];
            }
            
            // Validasi property yang diperlukan
            if (empty($periodeThr->dtCutoffTHR)) {
                return [
                    'success' => false,
                    'message' => 'Tanggal Cutoff THR tidak ditemukan',
                    'total_karyawan' => 0
                ];
            }
            
            try {
                $dtCutoffTHR = Carbon::parse($periodeThr->dtCutoffTHR);
            } catch (\Exception $e) {
                Log::error('Error parsing dtCutoffTHR', [
                    'dtCutoffTHR' => $periodeThr->dtCutoffTHR,
                    'error' => $e->getMessage()
                ]);
                return [
                    'success' => false,
                    'message' => 'Format tanggal Cutoff THR tidak valid',
                    'total_karyawan' => 0
                ];
            }
            
            $dtKategori = $periodeThr->dtKategori ?? null;
            $vcKodeDivisi = $periodeThr->vcKodeDivisi ?? null;
            
            if (empty($dtKategori) || empty($vcKodeDivisi)) {
                return [
                    'success' => false,
                    'message' => 'Data periode THR tidak lengkap (kategori atau divisi kosong)',
                    'total_karyawan' => 0
                ];
            }
            
            // Mapping kategori ke agama (reverse mapping)
            // Extract agama dari kategori (format: "Islam (Idul Fitri)" -> "Islam")
            $dtKategoriStr = is_string($dtKategori) ? trim($dtKategori) : (string) $dtKategori;
            
            // Jika kategori mengandung kurung, extract bagian sebelum kurung
            $agamaFilter = null;
            if (strpos($dtKategoriStr, '(') !== false) {
                // Format: "Islam (Idul Fitri)" -> ambil "Islam"
                $parts = explode('(', $dtKategoriStr);
                $agamaFilter = trim($parts[0] ?? '');
            } else {
                // Jika tidak ada kurung, gunakan langsung (untuk backward compatibility)
                $agamaFilter = $dtKategoriStr;
            }
            
            // Mapping untuk memastikan format agama sesuai dengan m_karyawan.Agama
            $agamaNormalized = [
                'Islam' => 'Islam',
                'Kristen' => 'Kristen',
                'Hindu' => 'Hindu',
                'Budha' => 'Budha',
                'Buddha' => 'Buddha', // Handle typo
            ];
            
            // Normalize agama filter
            if (isset($agamaNormalized[$agamaFilter])) {
                $agamaFilter = $agamaNormalized[$agamaFilter];
            }
            
            // Query karyawan semua group (Management, Staff, Operator, Security) dengan filter:
            // 1. Group_pegawai = Management, Staff, Operator, Security
            // 2. Agama sesuai dengan kategori (mapping otomatis)
            // 3. vcAktif = '1' (hanya aktif)
            // 4. Divisi sesuai
            $query = Karyawan::whereIn('Group_pegawai', ['Management', 'Staff', 'Operator', 'Security'])
                ->where('vcAktif', '1')
                ->where('Divisi', $vcKodeDivisi);
            
            // Filter agama jika kategori bukan "Lainnya"
            if ($agamaFilter && $dtKategoriStr !== 'Lainnya' && !empty($agamaFilter)) {
                // Handle multiple agama values (Islam, Buddha, dll)
                $agamaValues = [$agamaFilter];
                // Jika agama adalah "Buddha", juga cari "Budha" (typo handling)
                if ($agamaFilter === 'Buddha') {
                    $agamaValues[] = 'Budha';
                } elseif ($agamaFilter === 'Budha') {
                    $agamaValues[] = 'Buddha';
                }
                $query->whereIn('Agama', $agamaValues);
            } else if ($dtKategoriStr === 'Lainnya') {
                // Untuk "Lainnya", ambil semua agama yang tidak termasuk dalam mapping
                $query->whereNotIn('Agama', ['Islam', 'Kristen', 'Hindu', 'Budha', 'Buddha']);
            }
            
            $karyawans = $query->get();
            
            if ($karyawans->isEmpty()) {
                return [
                    'success' => true,
                    'message' => "Tidak ada karyawan yang memenuhi kriteria untuk periode ini",
                    'total_karyawan' => 0
                ];
            }
            
            // Ambil keterangan dari periode THR
            $vcKeterangan = $periodeThr->vcKeterangan ?? null;
            
            $processedCount = 0;
            $errors = [];
            
            foreach ($karyawans as $karyawan) {
                try {
                    // Cek apakah sudah ada data closing THR untuk karyawan ini
                    $existing = ClosingThr::where('dtTanggalTHR', $dtCutoffTHR->format('Y-m-d'))
                        ->where('vcNik', $karyawan->Nik)
                        ->where('vcAgama', $karyawan->Agama)
                        ->first();
                    
                    if ($existing) {
                        // Skip jika sudah ada
                        continue;
                    }
                    
                    // Tentukan apakah ini Operator atau Security (yang dapat perhitungan THR)
                    // Hanya Operator & Security: hitung decGajiPokok dan decNilaiTHR
                    // Management & Staff: decGajiPokok dan decNilaiTHR = null
                    $isOperator = ($karyawan->Group_pegawai === 'Operator');
                    $isSecurity = ($karyawan->Group_pegawai === 'Security');
                    $hitungNilaiThr = $isOperator || $isSecurity;
                    
                    // Untuk Operator & Security: hitung gaji pokok dan nilai THR
                    // Untuk Management & Staff: tidak hitung (null)
                    $gajiPokok = null;
                    $decNilaiTHR = null;
                    $decXGaji = 0;
                    
                    // Hitung masa kerja (untuk semua group)
                    $tglMasuk = null;
                    if (!empty($karyawan->Tgl_Masuk)) {
                        try {
                            $tglMasuk = Carbon::parse($karyawan->Tgl_Masuk)->format('Y-m-d');
                        } catch (\Exception $e) {
                            Log::warning("Error parsing Tgl_Masuk for NIK {$karyawan->Nik}: " . $e->getMessage());
                        }
                    }
                    
                    $masaKerja = $this->calculateMasaKerja(
                        $dtCutoffTHR->format('Y-m-d'),
                        $tglMasuk
                    );
                    
                    // Validasi hasil calculateMasaKerja
                    if (!is_array($masaKerja) || !isset($masaKerja['total_bulan'])) {
                        $errors[] = "Error calculating masa kerja for NIK {$karyawan->Nik}";
                        Log::error("Invalid masaKerja result", ['masaKerja' => $masaKerja, 'nik' => $karyawan->Nik]);
                        continue;
                    }
                    
                    // Hitung decXGaji berdasarkan masa kerja
                    if ($masaKerja['total_bulan'] >= 12) {
                        $decXGaji = 1.0;
                    } else {
                        // Roundup: (masa_kerja_bulan / 12) dibulatkan ke atas ke 2 desimal
                        $ratio = $masaKerja['total_bulan'] / 12;
                        $decXGaji = ceil($ratio * 100) / 100; // Roundup ke 2 desimal
                    }
                    
                    // Untuk Operator & Security: hitung gaji pokok dan nilai THR
                    if ($hitungNilaiThr) {
                        // Ambil gaji pokok dari m_gapok
                        $gapok = Gapok::find($karyawan->Gol);
                        if (!$gapok) {
                            $errors[] = "Golongan {$karyawan->Gol} tidak ditemukan untuk NIK {$karyawan->Nik}";
                            continue;
                        }
                        
                        // Gaji Pokok per bulan = Upah + Tunjangan Keluarga + Tunjangan Masa Kerja + Tunjangan Jabatan 1 + Tunjangan Jabatan 2
                        $gajiPokok = (float) ($gapok->upah ?? 0)
                            + (float) ($gapok->tunj_keluarga ?? 0)
                            + (float) ($gapok->tunj_masa_kerja ?? 0)
                            + (float) ($gapok->tunj_jabatan1 ?? 0)
                            + (float) ($gapok->tunj_jabatan2 ?? 0);
                        
                        // Hitung nilai THR untuk Operator & Security
                        $decNilaiTHR = round($gajiPokok * $decXGaji, 2);
                    }
                    // Untuk Management & Staff: gajiPokok dan decNilaiTHR tetap null
                    
                    // Simpan ke t_closing_thr
                    try {
                        ClosingThr::create([
                            'dtTanggalTHR' => $dtCutoffTHR->format('Y-m-d'),
                            'vcNik' => (string) $karyawan->Nik,
                            'vcAgama' => (string) ($karyawan->Agama ?? ''),
                            'vcKodeDivisi' => (string) $vcKodeDivisi,
                            'vcGroupPegawai' => (string) ($karyawan->Group_pegawai ?? ''),
                            'vcGolongan' => $karyawan->Gol ? (string) $karyawan->Gol : null,
                            'decGajiPokok' => $gajiPokok !== null ? (float) $gajiPokok : null,
                            'dtTanggalMasuk' => $tglMasuk,
                            'vcMasaKerja' => (string) ($masaKerja['format_text'] ?? ''),
                            'intMasaKerjaHari' => (int) ($masaKerja['total_hari'] ?? 0),
                            'decMasaKerjaBulan' => (float) ($masaKerja['total_bulan'] ?? 0),
                            'decMasaKerjaTahun' => (float) ($masaKerja['total_tahun'] ?? 0),
                            'decXGaji' => (float) $decXGaji,
                            'decNilaiTHR' => $decNilaiTHR !== null ? (float) $decNilaiTHR : null,
                            'vcKeterangan' => $vcKeterangan,
                            'dtCreate' => Carbon::now(),
                        ]);
                    } catch (\Exception $e) {
                        $errors[] = "Error saving closing THR for NIK {$karyawan->Nik}: " . $e->getMessage();
                        Log::error("Error creating ClosingThr", [
                            'nik' => $karyawan->Nik,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        continue;
                    }
                    
                    $processedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Error processing NIK {$karyawan->Nik}: " . $e->getMessage();
                    Log::error("Error processing THR for NIK {$karyawan->Nik}: " . $e->getMessage());
                }
            }
            
            $message = "Berhasil memproses {$processedCount} karyawan";
            if (count($errors) > 0) {
                $message .= ". Error: " . implode(', ', array_slice($errors, 0, 5)); // Limit error messages
            }
            
            return [
                'success' => true,
                'message' => $message,
                'total_karyawan' => $processedCount,
                'errors' => $errors
            ];
        } catch (\Exception $e) {
            Log::error('Error in processTHROperator: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'total_karyawan' => 0
            ];
        }
    }
}
