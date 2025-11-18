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
}
