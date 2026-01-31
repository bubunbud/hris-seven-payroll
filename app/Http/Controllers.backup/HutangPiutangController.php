<?php

namespace App\Http\Controllers;

use App\Models\HutangPiutang;
use App\Models\MasterHutangPiutang;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HutangPiutangController extends Controller
{
    public function index(Request $request)
    {
        $periodeAwal = $request->get('periode_awal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $periodeAkhir = $request->get('periode_akhir', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $nik = $request->get('nik');
        $hutangPiutang = $request->get('hutang_piutang');
        $debitKredit = $request->get('debit_kredit');

        // Query untuk data - filter berdasarkan overlap periode
        $query = HutangPiutang::with(['karyawan', 'masterHutangPiutang'])
            ->where(function ($q) use ($periodeAwal, $periodeAkhir) {
                $q->where(function ($qq) use ($periodeAwal, $periodeAkhir) {
                    // Periode awal dalam range filter
                    $qq->whereBetween('dtTanggalAwal', [$periodeAwal, $periodeAkhir]);
                })->orWhere(function ($qq) use ($periodeAwal, $periodeAkhir) {
                    // Periode akhir dalam range filter
                    $qq->whereBetween('dtTanggalAkhir', [$periodeAwal, $periodeAkhir]);
                })->orWhere(function ($qq) use ($periodeAwal, $periodeAkhir) {
                    // Periode mencakup seluruh range filter
                    $qq->where('dtTanggalAwal', '<=', $periodeAwal)
                        ->where('dtTanggalAkhir', '>=', $periodeAkhir);
                });
            })
            ->orderBy('dtCreate', 'desc');

        if ($nik) {
            $query->where('vcNik', 'like', '%' . $nik . '%');
        }

        if ($hutangPiutang) {
            $query->where('vcJenis', $hutangPiutang);
        }

        if ($debitKredit) {
            // Mapping Debit/Kredit ke 0/1: Debit = 0, Kredit = 1
            $vcFlagValue = ($debitKredit == 'Debit') ? '0' : '1';
            $query->where('vcFlag', $vcFlagValue);
        }

        $records = $query->paginate(25);

        // Data untuk dropdown
        $masterHutangPiutangs = MasterHutangPiutang::orderBy('vcKeterangan')->get();

        return view('hutang_piutang.index', compact(
            'records',
            'masterHutangPiutangs',
            'periodeAwal',
            'periodeAkhir',
            'nik',
            'hutangPiutang',
            'debitKredit'
        ));
    }

    public function store(Request $request)
    {
        // Mapping field name dari form ke database
        $request->merge([
            'dtTanggalAwal' => $request->get('dtTanggalAwal', $request->get('dtPeriodeAwal')),
            'dtTanggalAkhir' => $request->get('dtTanggalAkhir', $request->get('dtPeriodeAkhir')),
            'decAmount' => $request->get('decAmount', $request->get('decJumlah')),
            'vcFlag' => $request->get('vcFlag', $request->get('vcDebitKredit')),
        ]);

        $request->validate([
            'dtTanggalAwal' => 'required|date',
            'dtTanggalAkhir' => 'required|date|after_or_equal:dtTanggalAwal',
            'vcNik' => 'required|string|max:10|exists:m_karyawan,Nik',
            'vcJenis' => 'required|string|max:5|exists:m_hutang_piutang,vcJenis',
            'vcFlag' => 'required|in:Debit,Kredit',
            'decAmount' => 'required|numeric|min:0',
            'vcKeterangan' => 'nullable|string|max:35',
        ], [
            'dtTanggalAwal.required' => 'Periode awal harus diisi',
            'dtTanggalAkhir.required' => 'Periode akhir harus diisi',
            'dtTanggalAkhir.after_or_equal' => 'Periode akhir tidak boleh sebelum periode awal',
            'vcNik.required' => 'NIK harus diisi',
            'vcNik.exists' => 'NIK tidak ditemukan',
            'vcJenis.required' => 'Jenis Hutang/Piutang harus dipilih',
            'vcJenis.exists' => 'Jenis Hutang/Piutang tidak valid',
            'vcFlag.required' => 'Debit/Kredit harus dipilih',
            'decAmount.required' => 'Jumlah harus diisi',
            'decAmount.numeric' => 'Jumlah harus berupa angka',
            'decAmount.min' => 'Jumlah tidak boleh negatif',
        ]);

        // Mapping Debit/Kredit ke 0/1: Debit = 0 (menambah), Kredit = 1 (mengurangi)
        $vcFlagValue = ($request->vcFlag == 'Debit') ? '0' : '1';

        // Cek apakah sudah ada record dengan composite key yang sama
        $exists = HutangPiutang::where('dtTanggalAwal', $request->dtTanggalAwal)
            ->where('dtTanggalAkhir', $request->dtTanggalAkhir)
            ->where('vcNik', $request->vcNik)
            ->where('vcJenis', $request->vcJenis)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Data dengan periode, NIK, dan jenis yang sama sudah ada'
            ], 422);
        }

        HutangPiutang::create([
            'dtTanggalAwal' => $request->dtTanggalAwal,
            'dtTanggalAkhir' => $request->dtTanggalAkhir,
            'vcNik' => $request->vcNik,
            'vcJenis' => $request->vcJenis,
            'vcFlag' => $vcFlagValue, // Debit = 0, Kredit = 1
            'decAmount' => $request->decAmount,
            'vcKeterangan' => $request->vcKeterangan,
            'vcPeriodik' => 'N', // Default value
            'dtCreate' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Hutang-Piutang berhasil ditambahkan'
        ]);
    }

    public function show(string $id)
    {
        // Decode composite key: tanggal_awal|tanggal_akhir|nik|jenis
        $parts = explode('|', base64_decode($id));
        $record = HutangPiutang::with(['karyawan', 'masterHutangPiutang'])
            ->where('dtTanggalAwal', $parts[0])
            ->where('dtTanggalAkhir', $parts[1])
            ->where('vcNik', $parts[2])
            ->where('vcJenis', $parts[3])
            ->firstOrFail();

        // Mapping vcFlag ke Debit/Kredit: 0 = Debit, 1 = Kredit
        $debitKredit = ($record->vcFlag == '0') ? 'Debit' : 'Kredit';

        return response()->json([
            'success' => true,
            'record' => [
                'dtTanggalAwal' => $record->dtTanggalAwal->format('Y-m-d'),
                'dtTanggalAkhir' => $record->dtTanggalAkhir->format('Y-m-d'),
                'vcNik' => $record->vcNik,
                'nama' => $record->karyawan->Nama ?? '-',
                'vcJenis' => $record->vcJenis,
                'vcDebitKredit' => $debitKredit,
                'decJumlah' => number_format($record->decAmount, 2, '.', ''),
                'vcKeterangan' => $record->vcKeterangan,
            ]
        ]);
    }

    public function update(Request $request, string $id)
    {
        // Mapping field name dari form ke database
        $request->merge([
            'dtTanggalAwal' => $request->get('dtTanggalAwal', $request->get('dtPeriodeAwal')),
            'dtTanggalAkhir' => $request->get('dtTanggalAkhir', $request->get('dtPeriodeAkhir')),
            'decAmount' => $request->get('decAmount', $request->get('decJumlah')),
            'vcFlag' => $request->get('vcFlag', $request->get('vcDebitKredit')),
        ]);

        $request->validate([
            'dtTanggalAwal' => 'required|date',
            'dtTanggalAkhir' => 'required|date|after_or_equal:dtTanggalAwal',
            'vcNik' => 'required|string|max:10|exists:m_karyawan,Nik',
            'vcJenis' => 'required|string|max:5|exists:m_hutang_piutang,vcJenis',
            'vcFlag' => 'required|in:Debit,Kredit',
            'decAmount' => 'required|numeric|min:0',
            'vcKeterangan' => 'nullable|string|max:35',
        ]);

        // Mapping Debit/Kredit ke 0/1: Debit = 0 (menambah), Kredit = 1 (mengurangi)
        $vcFlagValue = ($request->vcFlag == 'Debit') ? '0' : '1';

        // Decode composite key
        $parts = explode('|', base64_decode($id));
        $record = HutangPiutang::where('dtTanggalAwal', $parts[0])
            ->where('dtTanggalAkhir', $parts[1])
            ->where('vcNik', $parts[2])
            ->where('vcJenis', $parts[3])
            ->firstOrFail();

        // Cek jika composite key berubah, pastikan tidak duplikat
        if (
            $record->dtTanggalAwal->format('Y-m-d') != $request->dtTanggalAwal ||
            $record->dtTanggalAkhir->format('Y-m-d') != $request->dtTanggalAkhir ||
            $record->vcNik != $request->vcNik ||
            $record->vcJenis != $request->vcJenis
        ) {
            $exists = HutangPiutang::where('dtTanggalAwal', $request->dtTanggalAwal)
                ->where('dtTanggalAkhir', $request->dtTanggalAkhir)
                ->where('vcNik', $request->vcNik)
                ->where('vcJenis', $request->vcJenis)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan periode, NIK, dan jenis yang sama sudah ada'
                ], 422);
            }
        }

        // Update record (perlu delete dan insert karena composite key)
        $oldData = $record->toArray();
        $record->delete();

        HutangPiutang::create([
            'dtTanggalAwal' => $request->dtTanggalAwal,
            'dtTanggalAkhir' => $request->dtTanggalAkhir,
            'vcNik' => $request->vcNik,
            'vcJenis' => $request->vcJenis,
            'vcFlag' => $vcFlagValue, // Debit = 0, Kredit = 1
            'decAmount' => $request->decAmount,
            'vcKeterangan' => $request->vcKeterangan,
            'vcPeriodik' => $oldData['vcPeriodik'] ?? 'N',
            'dtCreate' => $oldData['dtCreate'] ?? Carbon::now(),
            'dtChange' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Hutang-Piutang berhasil diupdate'
        ]);
    }

    public function destroy(string $id)
    {
        // Decode composite key
        $parts = explode('|', base64_decode($id));
        $record = HutangPiutang::where('dtTanggalAwal', $parts[0])
            ->where('dtTanggalAkhir', $parts[1])
            ->where('vcNik', $parts[2])
            ->where('vcJenis', $parts[3])
            ->firstOrFail();

        $record->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Hutang-Piutang berhasil dihapus'
        ]);
    }

    public function getNamaByNik(Request $request)
    {
        $nik = $request->get('nik');
        $karyawan = Karyawan::where('Nik', $nik)->first();

        if ($karyawan) {
            return response()->json([
                'success' => true,
                'nama' => $karyawan->Nama
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'NIK tidak ditemukan'
        ], 404);
    }

    public function upload(Request $request)
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

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

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

                    // Expected format: Periode Awal, Periode Akhir, NIK, Jenis, D/K, Jumlah, Keterangan
                    if (count($row) < 6) {
                        $errors[] = "Baris $rowNumber: Kolom tidak lengkap (minimal 6 kolom)";
                        $errorCount++;
                        continue;
                    }

                    $periodeAwal = trim($row[0]);
                    $periodeAkhir = trim($row[1]);
                    $nik = trim($row[2]);
                    $jenis = trim($row[3]);
                    $debitKredit = trim($row[4]);
                    $jumlah = trim($row[5]);
                    $keterangan = isset($row[6]) ? trim($row[6]) : '';

                    // Validasi data - gunakan === '' bukan empty() karena empty("0") = true
                    if ($periodeAwal === '' || $periodeAkhir === '' || $nik === '' || $jenis === '' || $debitKredit === '' || $jumlah === '') {
                        $errors[] = "Baris $rowNumber: Data tidak lengkap";
                        $errorCount++;
                        continue;
                    }

                    // Convert tanggal format
                    $tanggalAwal = $this->parseDate($periodeAwal);
                    $tanggalAkhir = $this->parseDate($periodeAkhir);

                    if (!$tanggalAwal || !$tanggalAkhir) {
                        $errors[] = "Baris $rowNumber: Format tanggal tidak valid";
                        $errorCount++;
                        continue;
                    }

                    // Validasi NIK
                    $karyawan = Karyawan::where('Nik', $nik)->first();
                    if (!$karyawan) {
                        $errors[] = "Baris $rowNumber: NIK $nik tidak ditemukan";
                        $errorCount++;
                        continue;
                    }

                    // Validasi Jenis
                    $masterJenis = MasterHutangPiutang::where('vcJenis', $jenis)->first();
                    if (!$masterJenis) {
                        $errors[] = "Baris $rowNumber: Jenis $jenis tidak ditemukan";
                        $errorCount++;
                        continue;
                    }

                    // Validasi Debit/Kredit - bisa teks atau kode
                    $debitKreditLower = strtolower(trim($debitKredit));
                    $vcFlagValue = null;

                    // Mapping: Debit = 0 (menambah), Kredit = 1 (mengurangi)
                    if (in_array($debitKreditLower, ['debit', 'd', '0'])) {
                        $vcFlagValue = '0'; // Debit = 0 (menambah formulasi)
                    } elseif (in_array($debitKreditLower, ['kredit', 'k', '1'])) {
                        $vcFlagValue = '1'; // Kredit = 1 (mengurangi formulasi)
                    } else {
                        $errors[] = "Baris $rowNumber: Debit/Kredit harus 'Debit', 'Kredit', '0', atau '1'";
                        $errorCount++;
                        continue;
                    }

                    // Parse jumlah (bisa format Indonesia atau internasional)
                    $jumlahClean = str_replace(['.', ','], ['', '.'], $jumlah);
                    $jumlahValue = (float) $jumlahClean;

                    if ($jumlahValue <= 0) {
                        $errors[] = "Baris $rowNumber: Jumlah harus lebih dari 0";
                        $errorCount++;
                        continue;
                    }

                    // Cek duplikasi
                    $exists = HutangPiutang::where('dtTanggalAwal', $tanggalAwal)
                        ->where('dtTanggalAkhir', $tanggalAkhir)
                        ->where('vcNik', $nik)
                        ->where('vcJenis', $jenis)
                        ->exists();

                    if ($exists) {
                        $errors[] = "Baris $rowNumber: Data dengan periode, NIK, dan jenis yang sama sudah ada";
                        $errorCount++;
                        continue;
                    }

                    // Insert data
                    try {
                        HutangPiutang::create([
                            'dtTanggalAwal' => $tanggalAwal,
                            'dtTanggalAkhir' => $tanggalAkhir,
                            'vcNik' => $nik,
                            'vcJenis' => $jenis,
                            'vcFlag' => $vcFlagValue, // Simpan sebagai 0 atau 1
                            'decAmount' => $jumlahValue,
                            'vcKeterangan' => $keterangan,
                            'vcPeriodik' => 'N',
                            'dtCreate' => Carbon::now(),
                        ]);
                        $successCount++;
                    } catch (\Exception $e) {
                        $errors[] = "Baris $rowNumber: " . $e->getMessage();
                        $errorCount++;
                    }
                }

                fclose($handle);
            } else {
                // Untuk Excel, gunakan pendekatan sederhana (konversi ke CSV dulu atau install library)
                return response()->json([
                    'success' => false,
                    'message' => 'Format Excel belum didukung. Silakan konversi ke CSV terlebih dahulu atau hubungi administrator.'
                ], 422);
            }

            $message = "Upload selesai. Berhasil: $successCount, Gagal: $errorCount";
            if (!empty($errors)) {
                $message .= "\n\nError details:\n" . implode("\n", array_slice($errors, 0, 10));
                if (count($errors) > 10) {
                    $message .= "\n... dan " . (count($errors) - 10) . " error lainnya";
                }
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deteksi separator CSV secara otomatis
     * Membaca beberapa baris pertama dan menghitung kemunculan separator
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

    private function parseDate($dateString)
    {
        $dateString = trim($dateString);

        // Coba berbagai format tanggal
        $formats = [
            'Y-m-d',
            'd/m/Y',
            'd-m-Y',
            'Y/m/d',
            'm/d/Y',
            'd.m.Y',
            'Y.m.d',
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $dateString);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                continue;
            }
        }

        // Coba parse dengan Carbon langsung
        try {
            return Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
