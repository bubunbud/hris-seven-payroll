<?php

namespace App\Http\Controllers;

use App\Models\SaldoCuti;
use App\Models\Karyawan;
use App\Models\TidakMasuk;
use App\Models\JenisIjin;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaldoCutiController extends Controller
{
    public function index(Request $request)
    {
        $tahunSekarang = Carbon::now()->year;
        $tahun = (int) $request->get('tahun', $tahunSekarang);

        // Validasi: hanya tahun sekarang dan tahun sebelumnya yang diizinkan
        $tahunSebelumnya = $tahunSekarang - 1;
        if ($tahun != $tahunSekarang && $tahun != $tahunSebelumnya) {
            $tahun = $tahunSekarang; // Default ke tahun sekarang jika tidak valid
        }

        $nik = $request->get('nik');
        $search = $request->get('search');

        // Hanya C010 (Cuti Tahunan) dan C012 (Cuti Bersama) yang mengurangi saldo
        $kodeCuti = ['C010', 'C012'];

        // Query karyawan aktif
        $query = Karyawan::where('vcAktif', '1')
            ->orderBy('Nama');

        // Filter pencarian
        if ($nik) {
            $query->where('Nik', 'like', '%' . $nik . '%');
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('Nik', 'like', '%' . $search . '%')
                    ->orWhere('Nama', 'like', '%' . $search . '%');
            });
        }

        $karyawans = $query->paginate(50);

        // Pastikan tahun adalah integer untuk semua operasi
        $tahunInt = (int) $tahun;

        // Hitung cuti bersama dari m_hari_libur sekali untuk semua karyawan (efisien)
        $cutiBersama = HariLibur::where('vcTipeHariLibur', 'Cuti Bersama')
            ->whereYear('dtTanggal', $tahunInt)
            ->count();

        // Hitung saldo cuti untuk setiap karyawan
        $saldoData = [];
        foreach ($karyawans as $karyawan) {
            // Validasi NIK untuk menghindari error
            if (empty($karyawan->Nik) || (!is_string($karyawan->Nik) && !is_numeric($karyawan->Nik))) {
                continue; // Skip jika NIK tidak valid
            }

            // Ambil saldo dari tabel m_saldo_cuti jika ada
            $saldoCuti = SaldoCuti::where('vcNik', (string) $karyawan->Nik)
                ->where('intTahun', $tahunInt)
                ->first();

            // Pastikan tipe data menjadi integer karena ini jumlah hari yang pasti bulat
            $tahunLalu = $saldoCuti ? (int) round($saldoCuti->decTahunLalu ?? 0) : 0;
            $tahunIni = $saldoCuti ? (int) round($saldoCuti->decTahunIni ?? 0) : 0;

            // HANGUS SALDO TAHUN LALU: Jika sudah 1 April tahun ini dan masih ada saldo tahun lalu, hanguskan
            // Rule: Saldo tahun lalu hanya berlaku sampai bulan Maret, jika 1 April masih ada maka hangus
            $tanggalSekarang = Carbon::now();
            $tanggal1April = Carbon::create($tahunInt, 4, 1);
            $sudah1April = $tanggalSekarang->greaterThanOrEqualTo($tanggal1April);

            if ($sudah1April && $tahunLalu > 0) {
                // Auto-hanguskan saldo tahun lalu jika sudah 1 April
                if ($saldoCuti && ($saldoCuti->decTahunLalu ?? 0) > 0) {
                    try {
                        $nikStr = (string) $karyawan->Nik;
                        $keteranganLama = $saldoCuti->vcKeterangan ?? '';
                        $keteranganBaru = ($keteranganLama ? $keteranganLama . ' | ' : '') . 'Saldo tahun lalu hangus per 1 April ' . $tahunInt;

                        // Gunakan DB::table untuk update karena composite primary key
                        DB::table('m_saldo_cuti')
                            ->where('vcNik', $nikStr)
                            ->where('intTahun', $tahunInt)
                            ->update([
                                'decTahunLalu' => 0,
                                'vcKeterangan' => $keteranganBaru,
                                'dtChange' => Carbon::now()->format('Y-m-d H:i:s'),
                            ]);

                        $tahunLalu = 0; // Update nilai untuk tampilan
                    } catch (\Exception $e) {
                        Log::error('Error hangus saldo tahun lalu: ' . $e->getMessage(), [
                            'nik' => $karyawan->Nik ?? 'N/A',
                            'tahun' => $tahunInt,
                            'trace' => $e->getTraceAsString()
                        ]);
                        // Continue tanpa throw, hanya log error
                    }
                }
            }

            // Hitung penggunaan cuti dari t_tidak_masuk (C010 dan C012 saja)
            // Perhitungan harus menghitung hari yang benar-benar di tahun tersebut
            // karena cuti bisa lintas tahun (dimulai tahun sebelumnya atau berakhir tahun berikutnya)
            $tanggalAwalTahun = Carbon::create($tahunInt, 1, 1);
            $tanggalAkhirTahun = Carbon::create($tahunInt, 12, 31);

            $cutiRecords = TidakMasuk::where('vcNik', (string) $karyawan->Nik)
                ->whereIn('vcKodeAbsen', $kodeCuti)
                ->where(function ($q) use ($tanggalAwalTahun, $tanggalAkhirTahun) {
                    // Query overlap: ambil semua cuti yang overlap dengan tahun tersebut
                    $q->where(function ($qq) use ($tanggalAwalTahun, $tanggalAkhirTahun) {
                        // Cuti yang dimulai di tahun ini
                        $qq->whereBetween('dtTanggalMulai', [
                            $tanggalAwalTahun->format('Y-m-d'),
                            $tanggalAkhirTahun->format('Y-m-d')
                        ]);
                    })->orWhere(function ($qq) use ($tanggalAwalTahun, $tanggalAkhirTahun) {
                        // Cuti yang berakhir di tahun ini (dimulai sebelum tahun ini)
                        $qq->where('dtTanggalMulai', '<', $tanggalAwalTahun->format('Y-m-d'))
                            ->whereBetween('dtTanggalSelesai', [
                                $tanggalAwalTahun->format('Y-m-d'),
                                $tanggalAkhirTahun->format('Y-m-d')
                            ]);
                    })->orWhere(function ($qq) use ($tanggalAwalTahun, $tanggalAkhirTahun) {
                        // Cuti yang mencakup seluruh tahun (dimulai sebelum dan berakhir sesudah)
                        $qq->where('dtTanggalMulai', '<=', $tanggalAwalTahun->format('Y-m-d'))
                            ->where('dtTanggalSelesai', '>=', $tanggalAkhirTahun->format('Y-m-d'));
                    });
                })
                ->get();

            // Hitung hari yang benar-benar di tahun tersebut dan simpan detail record
            $penggunaanCuti = 0;
            $detailCutiRecords = [];

            foreach ($cutiRecords as $record) {
                if (!$record->dtTanggalMulai || !$record->dtTanggalSelesai) {
                    continue;
                }

                $mulai = Carbon::parse($record->dtTanggalMulai);
                $selesai = Carbon::parse($record->dtTanggalSelesai);

                // Hitung overlap dengan tahun
                $overlapMulai = max($mulai, $tanggalAwalTahun);
                $overlapSelesai = min($selesai, $tanggalAkhirTahun);

                if ($overlapMulai <= $overlapSelesai) {
                    $hariOverlap = $overlapMulai->diffInDays($overlapSelesai) + 1;
                    $penggunaanCuti += $hariOverlap;

                    // Simpan detail untuk ditampilkan di UI
                    $detailCutiRecords[] = [
                        'kode' => $record->vcKodeAbsen,
                        'tanggal_mulai' => $mulai->format('d/m/Y'),
                        'tanggal_selesai' => $selesai->format('d/m/Y'),
                        'jumlah_hari_total' => $record->jumlah_hari,
                        'jumlah_hari_di_tahun' => $hariOverlap,
                        'keterangan' => $record->vcKeterangan ?? '-',
                    ];
                }
            }

            // Total penggunaan = penggunaan individu + cuti bersama
            $totalPenggunaan = $penggunaanCuti + $cutiBersama;

            // PERHITUNGAN DENGAN PRIORITAS: Kurangi Tahun Lalu dulu, baru Tahun Ini
            $sisaPenggunaan = $totalPenggunaan;
            $tahunLaluTerpakai = min($tahunLalu, $sisaPenggunaan); // Ambil dari tahun lalu sebisa mungkin
            $sisaPenggunaan -= $tahunLaluTerpakai;

            $tahunIniTerpakai = min($tahunIni, $sisaPenggunaan); // Ambil sisa dari tahun ini
            $sisaPenggunaan -= $tahunIniTerpakai;

            $saldoTahunLaluSisa = $tahunLalu - $tahunLaluTerpakai;
            $saldoTahunIniSisa = $tahunIni - $tahunIniTerpakai;
            $totalSaldoSisa = $saldoTahunLaluSisa + $saldoTahunIniSisa;

            // Pastikan NIK adalah string untuk digunakan sebagai array key
            $nikKey = trim((string) ($karyawan->Nik ?? ''));

            if (!empty($nikKey) && is_string($nikKey)) {
                $saldoData[$nikKey] = [
                    'tahun_lalu' => (int) round($tahunLalu),
                    'tahun_ini' => (int) round($tahunIni),
                    'total_saldo' => (int) round($tahunLalu + $tahunIni),
                    'penggunaan_individu' => (int) round($penggunaanCuti),
                    'cuti_bersama' => (int) $cutiBersama,
                    'penggunaan' => (int) round($totalPenggunaan),
                    'tahun_lalu_terpakai' => (int) round($tahunLaluTerpakai),
                    'tahun_ini_terpakai' => (int) round($tahunIniTerpakai),
                    'tahun_lalu_sisa' => (int) round($saldoTahunLaluSisa),
                    'tahun_ini_sisa' => (int) round($saldoTahunIniSisa),
                    'saldo_sisa' => (int) round($totalSaldoSisa),
                    'record_id' => $saldoCuti ? ($nikKey . '|' . $tahunInt) : null,
                    'sudah_1_april' => (bool) $sudah1April,
                    'detail_cuti' => $detailCutiRecords, // Detail record cuti untuk ditampilkan
                ];
            }
        }

        // Tahun-tahun yang tersedia: tahun sekarang dan tahun sebelumnya saja
        $tahunList = [
            $tahunSekarang,
            $tahunSebelumnya
        ];

        // Cek apakah perlu migrasi saldo (untuk tahun baru)
        $needMigration = $this->checkNeedMigration($tahun);

        return view('cuti.saldo.index', compact(
            'karyawans',
            'saldoData',
            'tahun',
            'nik',
            'search',
            'tahunList',
            'kodeCuti',
            'cutiBersama',
            'needMigration'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vcNik' => 'required|string|max:8|exists:m_karyawan,Nik',
            'intTahun' => 'required|integer|min:2020|max:2100',
            'decTahunLalu' => 'required|numeric|min:0',
            'decTahunIni' => 'nullable|numeric|min:0',
            'vcKeterangan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Pastikan vcNik dan intTahun adalah string dan integer
            $vcNik = (string) $request->vcNik;
            $intTahun = (int) $request->intTahun;

            // Cek apakah record sudah ada
            $saldoCuti = SaldoCuti::where('vcNik', $vcNik)
                ->where('intTahun', $intTahun)
                ->first();

            $data = [
                'decTahunLalu' => (int) round($request->decTahunLalu), // Bulatkan ke integer karena ini jumlah hari
                'decTahunIni' => (int) round($request->decTahunIni ?? 0), // Bulatkan ke integer karena ini jumlah hari
                'vcKeterangan' => $request->vcKeterangan ?? null,
                'dtChange' => Carbon::now()->format('Y-m-d H:i:s'), // Format ke string untuk DB::table
            ];

            if ($saldoCuti) {
                // Update existing record menggunakan DB::table karena composite key
                DB::table('m_saldo_cuti')
                    ->where('vcNik', $vcNik)
                    ->where('intTahun', $intTahun)
                    ->update($data);
            } else {
                // Create new record
                $data['vcNik'] = $vcNik;
                $data['intTahun'] = $intTahun;
                $data['dtCreate'] = Carbon::now()->format('Y-m-d H:i:s'); // Format ke string untuk DB::table

                DB::table('m_saldo_cuti')->insert($data);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Saldo cuti berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving saldo cuti: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id)
    {
        // Format: nik|tahun
        $parts = explode('|', $id);
        $saldoCuti = SaldoCuti::with('karyawan')
            ->where('vcNik', $parts[0])
            ->where('intTahun', $parts[1])
            ->first();

        if (!$saldoCuti) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'record' => $saldoCuti
        ]);
    }

    public function update(Request $request, string $id)
    {
        // Sama seperti store karena menggunakan updateOrCreate
        return $this->store($request);
    }

    /**
     * Migrasi saldo cuti dari tahun sebelumnya ke tahun baru
     * - Saldo sisa tahun sebelumnya dipindahkan ke "Tahun Lalu" tahun baru
     * - Karyawan tetap/permanen mendapat jatah 12 hari di "Tahun Ini"
     */
    public function migrateSaldo(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer|min:2020|max:2100'
        ]);

        $tahunBaru = (int) $request->input('tahun', Carbon::now()->year);
        $tahunLalu = $tahunBaru - 1;

        // Validasi: hanya bisa migrasi untuk tahun baru (lebih besar dari tahun sebelumnya)
        if ($tahunBaru <= $tahunLalu) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun yang dipilih harus lebih besar dari tahun sebelumnya'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Ambil semua karyawan aktif
            $karyawans = Karyawan::where('vcAktif', '1')->get();

            $migrated = 0;
            $setJatah = 0;
            $errors = [];

            foreach ($karyawans as $karyawan) {
                try {
                    // 1. Ambil saldo tahun lalu
                    $saldoTahunLalu = SaldoCuti::where('vcNik', $karyawan->Nik)
                        ->where('intTahun', $tahunLalu)
                        ->first();

                    // Hitung saldo sisa tahun lalu
                    $kodeCuti = ['C010', 'C012'];
                    $cutiBersama = HariLibur::where('vcTipeHariLibur', 'Cuti Bersama')
                        ->whereYear('dtTanggal', $tahunLalu)
                        ->count();

                    $penggunaanTahunLalu = TidakMasuk::where('vcNik', $karyawan->Nik)
                        ->whereIn('vcKodeAbsen', $kodeCuti)
                        ->whereYear('dtTanggalMulai', $tahunLalu)
                        ->get()
                        ->sum(function ($item) {
                            return $item->jumlah_hari;
                        });

                    $tahunLaluValue = $saldoTahunLalu ? $saldoTahunLalu->decTahunLalu : 0;
                    $tahunIniValue = $saldoTahunLalu ? $saldoTahunLalu->decTahunIni : 0;
                    $totalSaldoTahunLalu = $tahunLaluValue + $tahunIniValue;
                    $saldoSisaTahunLalu = max(0, $totalSaldoTahunLalu - $penggunaanTahunLalu - $cutiBersama);

                    // 2. Cek apakah karyawan tetap/permanen (untuk set jatah 12 hari)
                    // Cek Status_Pegawai: "Tetap", "Permanen", "Permanent", atau mengandung kata "tetap"/"permanen"
                    $isPermanent = false;
                    $statusPegawai = strtolower(trim($karyawan->Status_Pegawai ?? ''));
                    if (
                        in_array($statusPegawai, ['tetap', 'permanen', 'permanent', 'pt', 'pns'])
                        || strpos($statusPegawai, 'tetap') !== false
                        || strpos($statusPegawai, 'permanen') !== false
                    ) {
                        $isPermanent = true;
                    }

                    // 3. Cek apakah sudah ada saldo untuk tahun baru
                    $saldoTahunBaru = SaldoCuti::where('vcNik', $karyawan->Nik)
                        ->where('intTahun', $tahunBaru)
                        ->first();

                    if (!$saldoTahunBaru) {
                        // Create saldo baru untuk tahun baru
                        SaldoCuti::create([
                            'vcNik' => $karyawan->Nik,
                            'intTahun' => $tahunBaru,
                            'decTahunLalu' => $saldoSisaTahunLalu, // Saldo sisa tahun lalu dipindahkan
                            'decTahunIni' => $isPermanent ? 12 : 0, // 12 hari untuk karyawan tetap
                            'vcKeterangan' => 'Auto-migrasi dari tahun ' . $tahunLalu,
                            'dtCreate' => Carbon::now(),
                            'dtChange' => Carbon::now(),
                        ]);
                        $migrated++;
                        if ($isPermanent) {
                            $setJatah++;
                        }
                    } else {
                        // Update jika sudah ada (hanya update tahun lalu jika belum diisi)
                        if ($saldoTahunBaru->decTahunLalu == 0 && $saldoSisaTahunLalu > 0) {
                            $saldoTahunBaru->decTahunLalu = $saldoSisaTahunLalu;
                            $saldoTahunBaru->dtChange = Carbon::now();
                            $saldoTahunBaru->save();
                            $migrated++;
                        }

                        // Update jatah tahun ini jika karyawan tetap dan belum diisi
                        if ($isPermanent && $saldoTahunBaru->decTahunIni == 0) {
                            $saldoTahunBaru->decTahunIni = 12;
                            $saldoTahunBaru->dtChange = Carbon::now();
                            $saldoTahunBaru->save();
                            $setJatah++;
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = "NIK {$karyawan->Nik}: " . $e->getMessage();
                    Log::error("Error migrating saldo for NIK {$karyawan->Nik}: " . $e->getMessage());
                }
            }

            DB::commit();

            $message = "Migrasi saldo cuti selesai!\n";
            $message .= "Berhasil migrasi saldo: {$migrated} karyawan\n";
            $message .= "Berhasil set jatah 12 hari: {$setJatah} karyawan tetap";

            if (!empty($errors)) {
                $message .= "\n\nErrors: " . count($errors) . " error ditemukan.";
                if (count($errors) <= 5) {
                    $message .= "\n" . implode("\n", $errors);
                }
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'migrated' => $migrated,
                'set_jatah' => $setJatah,
                'errors' => $errors
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error migrating saldo cuti: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cek apakah perlu migrasi saldo (tahun baru dan belum ada proses migrasi)
     */
    private function checkNeedMigration($tahun)
    {
        $tahunLalu = $tahun - 1;
        $tahunSekarang = Carbon::now()->year;

        // Hanya cek jika tahun yang dipilih >= tahun sekarang
        if ($tahun < $tahunSekarang) {
            return false;
        }

        // Jika tahun sekarang < tahun yang dipilih, berarti tahun depan - pasti perlu migrasi
        if ($tahun > $tahunSekarang) {
            // Cek apakah sudah ada saldo tahun baru yang sudah dimigrasi
            $saldoTahunBaru = SaldoCuti::where('intTahun', $tahun)
                ->where(function ($q) {
                    $q->where('decTahunLalu', '>', 0)
                        ->orWhere('decTahunIni', '>', 0);
                })
                ->exists();
            return !$saldoTahunBaru;
        }

        // Jika tahun sama dengan tahun sekarang, cek apakah sudah masuk tahun baru (setelah 1 Januari)
        $tanggalSekarang = Carbon::now();
        $tanggal1Januari = Carbon::create($tahun, 1, 1);

        // Jika sudah lewat 1 Januari tahun ini dan belum ada migrasi
        if ($tanggalSekarang->greaterThan($tanggal1Januari)) {
            $tahunLalu = $tahun - 1;
            // Cek apakah ada saldo tahun baru yang sudah dimigrasi
            $saldoTahunBaru = SaldoCuti::where('intTahun', $tahun)
                ->where(function ($q) {
                    $q->where('decTahunLalu', '>', 0)
                        ->orWhere('decTahunIni', '>', 0);
                })
                ->exists();

            // Cek juga apakah ada data saldo tahun lalu yang bisa dimigrasi
            $adaSaldoTahunLalu = SaldoCuti::where('intTahun', $tahunLalu)->exists();

            return !$saldoTahunBaru && $adaSaldoTahunLalu;
        }

        return false;
    }

    /**
     * Import/Update saldo cuti dari Excel/CSV
     * Format 1: NIK, Tahun, Saldo Tahun Lalu, Saldo Tahun Ini, Keterangan (opsional)
     * Format 2: NIK, Tahun, Sisa Saldo, Keterangan (opsional) - akan dihitung otomatis
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx,xls|max:10240', // max 10MB
        ], [
            'file.required' => 'File harus dipilih',
            'file.mimes' => 'File harus berformat CSV, TXT, XLSX, atau XLS',
            'file.max' => 'Ukuran file maksimal 10MB',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $skipFirstRow = $request->get('skip_header', true);
        $separator = $request->get('separator', 'auto'); // auto, comma, tab, semicolon
        $importMode = $request->get('import_mode', 'sisa_saldo'); // 'sisa_saldo' atau 'detail'

        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        $updatedCount = 0;
        $insertedCount = 0;

        DB::beginTransaction();
        try {
            if (in_array(strtolower($extension), ['csv', 'txt'])) {
                // Parse CSV
                $data = [];
                $handle = fopen($file->getRealPath(), 'r');
                $rowNumber = 0;

                // Deteksi separator otomatis jika diperlukan
                $detectedSeparator = ',';
                if ($separator === 'auto') {
                    $detectedSeparator = $this->detectCsvSeparator($file->getRealPath());
                } else {
                    $separatorMap = [
                        'comma' => ',',
                        'tab' => "\t",
                        'semicolon' => ';'
                    ];
                    $detectedSeparator = $separatorMap[$separator] ?? ',';
                }

                while (($row = fgetcsv($handle, 1000, $detectedSeparator)) !== false) {
                    $rowNumber++;

                    // Skip header row if option enabled
                    if ($skipFirstRow && $rowNumber === 1) {
                        continue;
                    }

                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    // Expected format berdasarkan mode:
                    // Mode 'sisa_saldo': NIK, Tahun, Sisa Saldo, Keterangan (opsional)
                    // Mode 'detail': NIK, Tahun, Saldo Tahun Lalu, Saldo Tahun Ini, Keterangan (opsional)
                    if (count($row) < 3) {
                        $errors[] = "Baris $rowNumber: Kolom tidak lengkap (minimal: NIK, Tahun, dan kolom ketiga)";
                        $errorCount++;
                        continue;
                    }

                    $nik = trim($row[0]);
                    $tahun = trim($row[1]);
                    $keterangan = isset($row[3]) ? trim($row[3]) : (isset($row[4]) ? trim($row[4]) : '');
                    
                    $saldoTahunLalu = null;
                    $saldoTahunIni = null;
                    $sisaSaldo = null;

                    // Validasi NIK
                    if (empty($nik)) {
                        $errors[] = "Baris $rowNumber: NIK tidak boleh kosong";
                        $errorCount++;
                        continue;
                    }

                    // Validasi NIK exists
                    $karyawan = Karyawan::where('Nik', $nik)->first();
                    if (!$karyawan) {
                        $errors[] = "Baris $rowNumber: NIK '$nik' tidak ditemukan di database";
                        $errorCount++;
                        continue;
                    }

                    // Validasi Tahun
                    if (empty($tahun) || !is_numeric($tahun)) {
                        $errors[] = "Baris $rowNumber: Tahun harus berupa angka";
                        $errorCount++;
                        continue;
                    }
                    $tahunInt = (int) $tahun;
                    if ($tahunInt < 2020 || $tahunInt > 2100) {
                        $errors[] = "Baris $rowNumber: Tahun harus antara 2020-2100";
                        $errorCount++;
                        continue;
                    }

                    // Tentukan mode import berdasarkan jumlah kolom dan import_mode
                    if ($importMode === 'sisa_saldo' || count($row) == 3 || (count($row) == 4 && !is_numeric(trim($row[3])))) {
                        // Mode: Import Sisa Saldo
                        $sisaSaldo = isset($row[2]) ? trim($row[2]) : '0';
                        
                        // Validasi Sisa Saldo
                        if (!is_numeric($sisaSaldo) || $sisaSaldo < 0) {
                            $errors[] = "Baris $rowNumber: Sisa Saldo harus berupa angka >= 0";
                            $errorCount++;
                            continue;
                        }
                        $sisaSaldoInt = (int) round($sisaSaldo);
                        
                        // Hitung penggunaan cuti menggunakan helper method yang sama dengan di view
                        $totalPenggunaan = $this->calculatePenggunaanCuti($nik, $tahunInt);
                        
                        // LOGIKA: Agar Saldo Sisa akhir = Input Sisa Saldo
                        // Formula di view: Saldo Sisa = (decTahunLalu - tahunLaluTerpakai) + (decTahunIni - tahunIniTerpakai)
                        // Dimana: tahunLaluTerpakai = min(decTahunLalu, penggunaan), tahunIniTerpakai = min(decTahunIni, sisa_penggunaan)
                        // 
                        // Strategi: Set decTahunLalu = 0, decTahunIni = Sisa Saldo + Penggunaan
                        // Maka: tahunLaluTerpakai = min(0, penggunaan) = 0
                        //       sisa_penggunaan = penggunaan - 0 = penggunaan
                        //       tahunIniTerpakai = min((Sisa Saldo + Penggunaan), penggunaan) = penggunaan
                        //       Saldo Sisa = (0 - 0) + ((Sisa Saldo + Penggunaan) - penggunaan) = Sisa Saldo ✓
                        // 
                        // Untuk proses awal update, decTahunIni bisa lebih dari 12 hari
                        // Yang terpenting adalah Saldo Sisa sesuai dengan input
                        $saldoTahunIniInt = $sisaSaldoInt + $totalPenggunaan;
                        $saldoTahunLaluInt = 0;
                        
                        // Update keterangan jika kosong
                        if (empty($keterangan)) {
                            $keterangan = "Import sisa saldo: $sisaSaldoInt hari (Penggunaan: $totalPenggunaan hari, Set decTahunIni: $saldoTahunIniInt hari)";
                        }
                    } else {
                        // Mode: Import Detail (Saldo Tahun Lalu dan Tahun Ini)
                        $saldoTahunLalu = isset($row[2]) ? trim($row[2]) : '0';
                        $saldoTahunIni = isset($row[3]) ? trim($row[3]) : '0';
                        
                        // Validasi Saldo Tahun Lalu
                        if (!is_numeric($saldoTahunLalu) || $saldoTahunLalu < 0) {
                            $errors[] = "Baris $rowNumber: Saldo Tahun Lalu harus berupa angka >= 0";
                            $errorCount++;
                            continue;
                        }
                        $saldoTahunLaluInt = (int) round($saldoTahunLalu);

                        // Validasi Saldo Tahun Ini
                        if (!is_numeric($saldoTahunIni) || $saldoTahunIni < 0) {
                            $errors[] = "Baris $rowNumber: Saldo Tahun Ini harus berupa angka >= 0";
                            $errorCount++;
                            continue;
                        }
                        $saldoTahunIniInt = (int) round($saldoTahunIni);
                    }

                    // Cek apakah record sudah ada
                    $saldoCuti = SaldoCuti::where('vcNik', $nik)
                        ->where('intTahun', $tahunInt)
                        ->first();

                    // Hitung decSaldoDigunakan dan decSaldoSisa
                    $decSaldoDigunakan = 0;
                    $decSaldoSisa = 0;
                    
                    if ($importMode === 'sisa_saldo' || (isset($sisaSaldo) && $sisaSaldo !== null)) {
                        // Untuk mode sisa_saldo, hitung penggunaan dan set saldo sisa sesuai input
                        $decSaldoDigunakan = $this->calculatePenggunaanCuti($nik, $tahunInt);
                        $decSaldoSisa = $sisaSaldoInt; // Langsung set sesuai input
                    } else {
                        // Untuk mode detail, hitung dari decTahunLalu dan decTahunIni
                        // (akan dihitung ulang di view, tapi kita set juga di DB untuk konsistensi)
                        $decSaldoDigunakan = $this->calculatePenggunaanCuti($nik, $tahunInt);
                        // Saldo sisa akan dihitung ulang di view berdasarkan formula
                        $decSaldoSisa = 0; // Temporary, akan dihitung ulang di view
                    }

                    $data = [
                        'decTahunLalu' => $saldoTahunLaluInt,
                        'decTahunIni' => $saldoTahunIniInt,
                        'decSaldoDigunakan' => $decSaldoDigunakan,
                        'decSaldoSisa' => $decSaldoSisa,
                        'vcKeterangan' => $keterangan ?: ($saldoCuti ? ($saldoCuti->vcKeterangan ?? '') : 'Import dari Excel'),
                        'dtChange' => Carbon::now()->format('Y-m-d H:i:s'),
                    ];

                    if ($saldoCuti) {
                        // Update existing record menggunakan DB::table karena composite key
                        DB::table('m_saldo_cuti')
                            ->where('vcNik', $nik)
                            ->where('intTahun', $tahunInt)
                            ->update($data);
                        $updatedCount++;
                    } else {
                        // Create new record
                        $data['vcNik'] = $nik;
                        $data['intTahun'] = $tahunInt;
                        $data['dtCreate'] = Carbon::now()->format('Y-m-d H:i:s');

                        DB::table('m_saldo_cuti')->insert($data);
                        $insertedCount++;
                    }

                    $successCount++;
                }

                fclose($handle);
            } else {
                // Untuk Excel, gunakan pendekatan sederhana (konversi ke CSV dulu atau install library)
                return response()->json([
                    'success' => false,
                    'message' => 'Format Excel (.xlsx/.xls) belum didukung secara langsung. Silakan konversi ke CSV terlebih dahulu atau hubungi administrator untuk install library PhpSpreadsheet.'
                ], 422);
            }

            DB::commit();

            $message = "Import selesai!\n";
            $message .= "Berhasil: $successCount record\n";
            $message .= "- Di-update: $updatedCount record\n";
            $message .= "- Di-insert: $insertedCount record\n";
            $message .= "Gagal: $errorCount record";

            if (!empty($errors)) {
                $message .= "\n\nError details:\n" . implode("\n", array_slice($errors, 0, 20));
                if (count($errors) > 20) {
                    $message .= "\n... dan " . (count($errors) - 20) . " error lainnya";
                }
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'updated_count' => $updatedCount,
                'inserted_count' => $insertedCount,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error importing saldo cuti: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $file->getClientOriginalName()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hitung penggunaan cuti untuk tahun tertentu (helper method)
     * Menggunakan logika yang sama dengan perhitungan di method index()
     */
    private function calculatePenggunaanCuti($nik, $tahun)
    {
        $tanggalAwalTahun = Carbon::create($tahun, 1, 1);
        $tanggalAkhirTahun = Carbon::create($tahun, 12, 31);
        
        // Hitung penggunaan cuti individu (C010 dan C012)
        $penggunaanCuti = TidakMasuk::where('vcNik', $nik)
            ->whereIn('vcKodeAbsen', ['C010', 'C012'])
            ->where(function ($q) use ($tanggalAwalTahun, $tanggalAkhirTahun) {
                $q->where(function ($qq) use ($tanggalAwalTahun, $tanggalAkhirTahun) {
                    $qq->whereBetween('dtTanggalMulai', [
                        $tanggalAwalTahun->format('Y-m-d'),
                        $tanggalAkhirTahun->format('Y-m-d')
                    ]);
                })->orWhere(function ($qq) use ($tanggalAwalTahun, $tanggalAkhirTahun) {
                    $qq->where('dtTanggalMulai', '<', $tanggalAwalTahun->format('Y-m-d'))
                        ->whereBetween('dtTanggalSelesai', [
                            $tanggalAwalTahun->format('Y-m-d'),
                            $tanggalAkhirTahun->format('Y-m-d')
                        ]);
                })->orWhere(function ($qq) use ($tanggalAwalTahun, $tanggalAkhirTahun) {
                    $qq->where('dtTanggalMulai', '<=', $tanggalAwalTahun->format('Y-m-d'))
                        ->where('dtTanggalSelesai', '>=', $tanggalAkhirTahun->format('Y-m-d'));
                });
            })
            ->whereNotNull('dtTanggalMulai')
            ->whereNotNull('dtTanggalSelesai')
            ->get()
            ->sum(function ($item) use ($tanggalAwalTahun, $tanggalAkhirTahun) {
                $mulai = Carbon::parse($item->dtTanggalMulai);
                $selesai = Carbon::parse($item->dtTanggalSelesai);
                
                $overlapMulai = max($mulai, $tanggalAwalTahun);
                $overlapSelesai = min($selesai, $tanggalAkhirTahun);
                
                if ($overlapMulai <= $overlapSelesai) {
                    return $overlapMulai->diffInDays($overlapSelesai) + 1;
                }
                return 0;
            });
        
        // Tambahkan cuti bersama untuk tahun tersebut
        $cutiBersamaTahun = HariLibur::where('vcTipeHariLibur', 'Cuti Bersama')
            ->whereYear('dtTanggal', $tahun)
            ->count();
        
        return $penggunaanCuti + $cutiBersamaTahun;
    }

    /**
     * Deteksi separator CSV secara otomatis
     */
    private function detectCsvSeparator($filePath)
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ','; // Default ke koma jika gagal
        }

        $separators = [',', "\t", ';'];
        $counts = [',' => 0, "\t" => 0, ';' => 0];
        $sampleLines = 5; // Baca 5 baris pertama untuk sampling
        $lineCount = 0;

        while (($line = fgets($handle)) !== false && $lineCount < $sampleLines) {
            foreach ($separators as $sep) {
                $counts[$sep] += substr_count($line, $sep);
            }
            $lineCount++;
        }

        fclose($handle);

        // Pilih separator dengan jumlah terbanyak
        $maxCount = max($counts);
        if ($maxCount > 0) {
            foreach ($separators as $sep) {
                if ($counts[$sep] === $maxCount) {
                    return $sep;
                }
            }
        }

        return ','; // Default ke koma
    }
}
