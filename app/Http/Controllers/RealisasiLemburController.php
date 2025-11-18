<?php

namespace App\Http\Controllers;

use App\Models\Absen;
use App\Models\Karyawan;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RealisasiLemburController extends Controller
{
    public function index(Request $request)
    {
        // Ambil range tanggal dari request
        $dariTanggal = $request->get('dari_tanggal');
        $sampaiTanggal = $request->get('sampai_tanggal');
        $search = $request->get('search');

        // Set default jika tidak ada input (awal bulan sampai akhir bulan)
        if (!$dariTanggal) {
            $dariTanggal = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if (!$sampaiTanggal) {
            $sampaiTanggal = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        // Validasi: sampai_tanggal harus >= dari_tanggal
        if ($sampaiTanggal < $dariTanggal) {
            $sampaiTanggal = $dariTanggal;
        }

        // Query untuk data absensi - hanya yang memiliki kode lembur (vcCounter)
        // yang benar-benar ada di t_lembur_header (Instruksi Kerja Lembur)
        // Termasuk hari libur yang hanya memiliki jam lembur tanpa jam masuk/keluar biasa
        $query = Absen::with(['karyawan', 'lemburHeader'])
            ->whereBetween('dtTanggal', [$dariTanggal, $sampaiTanggal])
            ->whereNotNull('vcCounter') // Hanya yang memiliki kode lembur
            ->whereHas('lemburHeader') // Pastikan vcCounter benar-benar ada di t_lembur_header
            ->where(function ($q) {
                // Termasuk yang memiliki jam masuk/keluar biasa ATAU jam lembur
                // Ini penting untuk hari libur yang hanya memiliki jam lembur
                $q->whereNotNull('dtJamMasuk')
                    ->orWhereNotNull('dtJamKeluar')
                    ->orWhereNotNull('dtJamMasukLembur')
                    ->orWhereNotNull('dtJamKeluarLembur');
            })
            ->whereHas('karyawan', function ($q) {
                $q->where('vcAktif', '1'); // Hanya karyawan aktif
            })
            ->orderBy('dtTanggal')
            ->orderBy('vcNik');

        // Filter pencarian NIK atau Nama
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('vcNik', 'like', '%' . $search . '%')
                    ->orWhereHas('karyawan', function ($subQ) use ($search) {
                        $subQ->where('Nama', 'like', '%' . $search . '%');
                    });
            });
        }

        $records = $query->paginate(100);

        // Ambil daftar hari libur dalam range tanggal untuk validasi konfirmasi lembur
        // Termasuk hari Sabtu (6) dan Minggu (0) yang otomatis hari libur
        $hariLiburList = HariLibur::whereBetween('dtTanggal', [$dariTanggal, $sampaiTanggal])
            ->pluck('dtTanggal')
            ->map(function ($tanggal) {
                return $tanggal instanceof \Carbon\Carbon
                    ? $tanggal->format('Y-m-d')
                    : (string) $tanggal;
            })
            ->toArray();

        // Tambahkan hari Sabtu dan Minggu dalam range tanggal ke daftar hari libur
        $tanggalMulai = Carbon::parse($dariTanggal);
        $tanggalAkhir = Carbon::parse($sampaiTanggal);
        $current = $tanggalMulai->copy();

        while ($current->lte($tanggalAkhir)) {
            // 0 = Minggu, 6 = Sabtu
            if (in_array($current->dayOfWeek, [0, 6])) {
                $tanggalStr = $current->format('Y-m-d');
                if (!in_array($tanggalStr, $hariLiburList)) {
                    $hariLiburList[] = $tanggalStr;
                }
            }
            $current->addDay();
        }

        return view('lembur.realisasi', compact('records', 'dariTanggal', 'sampaiTanggal', 'search', 'hariLiburList'));
    }

    public function update(Request $request, $tanggal, $nik)
    {
        $request->validate([
            'dtJamMasukLembur' => ['nullable', 'string', 'regex:/^([01][0-9]|2[0-3]):[0-5][0-9]$/'],
            'dtJamKeluarLembur' => ['nullable', 'string', 'regex:/^([01][0-9]|2[0-3]):[0-5][0-9]$/'],
        ], [
            'dtJamMasukLembur.regex' => 'Format jam masuk lembur tidak valid (HH:MM)',
            'dtJamKeluarLembur.regex' => 'Format jam keluar lembur tidak valid (HH:MM)',
        ]);

        try {
            // Pastikan tanggal dan nik adalah string
            $tanggalStr = (string) $tanggal;
            $nikStr = (string) $nik;

            // Cek apakah data absensi ada
            $absen = Absen::where('dtTanggal', $tanggalStr)
                ->where('vcNik', $nikStr)
                ->first();

            if (!$absen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data absensi tidak ditemukan'
                ], 404);
            }

            // Format jam jika ada (tambahkan :00 jika hanya HH:MM)
            $jamMasukLembur = $request->dtJamMasukLembur
                ? (strlen($request->dtJamMasukLembur) == 5 ? $request->dtJamMasukLembur . ':00' : substr($request->dtJamMasukLembur, 0, 8))
                : null;

            $jamKeluarLembur = $request->dtJamKeluarLembur
                ? (strlen($request->dtJamKeluarLembur) == 5 ? $request->dtJamKeluarLembur . ':00' : substr($request->dtJamKeluarLembur, 0, 8))
                : null;

            // Update menggunakan DB::table karena composite primary key
            $affectedRows = DB::table('t_absen')
                ->where('dtTanggal', $tanggalStr)
                ->where('vcNik', $nikStr)
                ->update([
                    'dtJamMasukLembur' => $jamMasukLembur,
                    'dtJamKeluarLembur' => $jamKeluarLembur,
                    'dtChange' => Carbon::now()->format('Y-m-d H:i:s'),
                ]);

            if ($affectedRows > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data realisasi lembur berhasil diupdate'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengupdate data'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error updating realisasi lembur: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'tanggal' => $tanggal,
                'nik' => $nik
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateBulk(Request $request)
    {
        $request->validate([
            'data' => 'required|string', // JSON string dari JavaScript
        ]);

        DB::beginTransaction();
        try {
            $dataArray = json_decode($request->data, true);

            if (!is_array($dataArray)) {
                throw new \Exception('Format data tidak valid');
            }

            $updated = 0;

            foreach ($dataArray as $item) {
                if (empty($item['nik']) || empty($item['tanggal'])) continue;

                $tanggal = $item['tanggal'];

                $absen = Absen::where('dtTanggal', $tanggal)
                    ->where('vcNik', $item['nik'])
                    ->first();

                if ($absen) {
                    $jamMasukLembur = !empty($item['dtJamMasukLembur'])
                        ? (strlen($item['dtJamMasukLembur']) == 5 ? $item['dtJamMasukLembur'] . ':00' : substr($item['dtJamMasukLembur'], 0, 8))
                        : null;

                    $jamKeluarLembur = !empty($item['dtJamKeluarLembur'])
                        ? (strlen($item['dtJamKeluarLembur']) == 5 ? $item['dtJamKeluarLembur'] . ':00' : substr($item['dtJamKeluarLembur'], 0, 8))
                        : null;

                    $absen->update([
                        'dtJamMasukLembur' => $jamMasukLembur,
                        'dtJamKeluarLembur' => $jamKeluarLembur,
                        'dtChange' => Carbon::now(),
                    ]);
                    $updated++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil update {$updated} data realisasi lembur"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error bulk update realisasi lembur: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function confirmLembur(Request $request, $tanggal, $nik)
    {
        $request->validate([
            'vcCfmLembur' => 'required|string|in:0,1',
        ]);

        try {
            // Ambil data absensi terlebih dahulu
            $absen = Absen::where('dtTanggal', $tanggal)
                ->where('vcNik', $nik)
                ->first();

            if (!$absen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data absensi tidak ditemukan'
                ], 404);
            }

            $updateData = [
                'vcCfmLembur' => $request->vcCfmLembur,
                'dtChange' => Carbon::now(),
            ];

            // Jika dikonfirmasi (vcCfmLembur = '1'), copy jam masuk dan keluar ke jam lembur
            if ($request->vcCfmLembur == '1') {
                // Copy dtJamMasuk ke dtJamMasukLembur
                if ($absen->dtJamMasuk) {
                    $jamMasuk = strlen($absen->dtJamMasuk) == 5
                        ? $absen->dtJamMasuk . ':00'
                        : substr($absen->dtJamMasuk, 0, 8);
                    $updateData['dtJamMasukLembur'] = $jamMasuk;
                }

                // Copy dtJamKeluar ke dtJamKeluarLembur
                if ($absen->dtJamKeluar) {
                    $jamKeluar = strlen($absen->dtJamKeluar) == 5
                        ? $absen->dtJamKeluar . ':00'
                        : substr($absen->dtJamKeluar, 0, 8);
                    $updateData['dtJamKeluarLembur'] = $jamKeluar;
                }
            }

            // Update menggunakan DB::table karena composite primary key
            $affectedRows = DB::table('t_absen')
                ->where('dtTanggal', $tanggal)
                ->where('vcNik', $nik)
                ->update($updateData);

            if ($affectedRows > 0) {
                $message = $request->vcCfmLembur == '1'
                    ? 'Lembur berhasil dikonfirmasi dan jam absensi telah di-copy ke jam lembur'
                    : 'Konfirmasi lembur dibatalkan';

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'dtJamMasukLembur' => $updateData['dtJamMasukLembur'] ?? null,
                        'dtJamKeluarLembur' => $updateData['dtJamKeluarLembur'] ?? null,
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengupdate data'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error confirming lembur: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($tanggal, $nik)
    {
        try {
            // Pastikan tanggal dan nik adalah string
            $tanggalStr = (string) $tanggal;
            $nikStr = (string) $nik;

            // Normalisasi format tanggal ke Y-m-d
            try {
                $tanggalCarbon = Carbon::parse($tanggalStr);
                $tanggalFormatted = $tanggalCarbon->format('Y-m-d');
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format tanggal tidak valid: ' . $tanggalStr
                ], 400);
            }

            // Cek apakah data absensi ada
            $absen = Absen::where('dtTanggal', $tanggalFormatted)
                ->where('vcNik', $nikStr)
                ->first();

            if (!$absen) {
                Log::warning('Data absensi tidak ditemukan untuk delete', [
                    'tanggal' => $tanggalFormatted,
                    'nik' => $nikStr
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Data absensi tidak ditemukan'
                ], 404);
            }

            // Hapus data realisasi lembur (set ke null) menggunakan DB::table
            // Menghapus: dtJamMasukLembur, dtJamKeluarLembur, intDurasiIstirahat (set ke 0), vcCounter, dan reset vcCfmLembur
            $affectedRows = DB::table('t_absen')
                ->where('dtTanggal', $tanggalFormatted)
                ->where('vcNik', $nikStr)
                ->update([
                    'dtJamMasukLembur' => null,
                    'dtJamKeluarLembur' => null,
                    'intDurasiIstirahat' => 0, // Set ke 0 karena kolom tidak boleh null
                    'vcCounter' => null, // Penting: hapus vcCounter agar data tidak muncul lagi di list
                    'vcCfmLembur' => '0',
                    'dtChange' => Carbon::now()->format('Y-m-d H:i:s'),
                ]);

            if ($affectedRows > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data realisasi lembur berhasil dihapus'
                ]);
            } else {
                Log::warning('Tidak ada row yang ter-update saat delete realisasi lembur', [
                    'tanggal' => $tanggalFormatted,
                    'nik' => $nikStr
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus data. Tidak ada data yang ter-update.'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error deleting realisasi lembur: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'tanggal' => $tanggal,
                'nik' => $nik
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
