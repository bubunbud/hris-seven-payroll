<?php

namespace App\Http\Controllers;

use App\Models\Izin;
use App\Models\JenisIzin;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IzinKeluarController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('dari_tanggal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('sampai_tanggal', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $nik = $request->get('nik');

        $query = Izin::with(['karyawan', 'jenisIzin'])
            ->whereBetween('dtTanggal', [$startDate, $endDate])
            ->orderBy('dtTanggal', 'desc')
            ->orderBy('dtDari');

        if ($nik) {
            $query->where('vcNik', 'like', '%' . $nik . '%');
        }

        $records = $query->paginate(25);
        $jenisIzins = JenisIzin::orderBy('vcKeterangan')->get();

        return view('absen.izin_keluar.index', compact('records', 'jenisIzins', 'startDate', 'endDate', 'nik'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'dtTanggal' => 'required|date',
            'vcNik' => 'required|string|max:10',
            'vcKodeIzin' => 'required|string|max:5|exists:m_jenis_izin,vcKodeIzin',
            'vcTipeIzin' => 'nullable|string|max:20|in:Masuk Siang,Izin Biasa,Pulang Cepat',
            'dtDari' => 'required|date_format:H:i',
            'dtSampai' => 'required|date_format:H:i',
            'vcKeterangan' => 'nullable|string|max:35',
        ]);

        // Generate vcCounter unik (panjang 9). Coba beberapa kali untuk menghindari bentrok.
        $vcCounter = null;
        for ($i = 0; $i < 5; $i++) {
            $candidate = substr(
                preg_replace(
                    '/[^0-9]/',
                    '',
                    Carbon::now()->format('mdY') . mt_rand(100, 999)
                ),
                0,
                9
            );
            if (!Izin::where('vcCounter', $candidate)->exists()) {
                $vcCounter = $candidate;
                break;
            }
            // Delay ringan untuk mengurangi kemungkinan tabrakan pada waktu yang sama
            usleep(50000); // 50ms
        }
        if (!$vcCounter) {
            return response()->json(['success' => false, 'message' => 'Gagal membangkitkan kode counter. Coba lagi.'], 422);
        }

        // Auto insert/update ke t_absen jika Jenis=Pribadi dan Tipe=Masuk Siang
        // Validasi shift SEBELUM create izin agar jika validasi gagal, izin tidak dibuat
        $isPribadi = in_array($request->vcKodeIzin, ['Z003', 'Z004']);
        $isMasukSiang = $request->vcTipeIzin === 'Masuk Siang';
        
        if ($isPribadi && $isMasukSiang) {
            // Load karyawan dengan shift untuk validasi
            $karyawan = Karyawan::with('shift')->where('Nik', $request->vcNik)->first();
            
            if (!$karyawan) {
                return response()->json([
                    'success' => false, 
                    'message' => 'NIK atau karyawan belum mempunyai data shift, silahkan di lengkapi dulu'
                ], 422);
            }
            
            // Validasi shift ada
            if (!$karyawan->vcShift || !$karyawan->shift) {
                return response()->json([
                    'success' => false, 
                    'message' => 'NIK atau karyawan belum mempunyai data shift, silahkan di lengkapi dulu'
                ], 422);
            }
            
            // Validasi jam masuk shift ada
            $jamMasukShift = $karyawan->shift->vcMasuk;
            
            if (!$jamMasukShift) {
                return response()->json([
                    'success' => false, 
                    'message' => 'NIK atau karyawan belum mempunyai data shift, silahkan di lengkapi dulu'
                ], 422);
            }
        }

        // Create izin
        Izin::create([
            'dtTanggal' => $request->dtTanggal,
            'vcNik' => $request->vcNik,
            'vcKodeIzin' => $request->vcKodeIzin,
            'vcTipeIzin' => $request->vcTipeIzin,
            'dtDari' => $request->dtDari . ':00',
            'dtSampai' => $request->dtSampai . ':00',
            'vcKeterangan' => $request->vcKeterangan,
            'vcCounter' => $vcCounter,
            'dtCreate' => Carbon::now(),
        ]);

        // Auto insert/update ke t_absen setelah izin berhasil dibuat
        if ($isPribadi && $isMasukSiang) {
            try {
                // Gunakan variabel yang sudah di-validasi sebelumnya
                // $karyawan dan $jamMasukShift sudah tersedia dari validasi di atas
                
                // Format jam masuk shift ke HH:mm:ss
                // vcMasuk di-cast sebagai datetime:H:i di model Shift, jadi akan menjadi Carbon instance
                $jamMasukFormatted = null;
                if ($jamMasukShift instanceof Carbon) {
                    $jamMasukFormatted = $jamMasukShift->format('H:i:s');
                } elseif (is_string($jamMasukShift)) {
                    // Jika string, parse dulu lalu format
                    try {
                        // Coba parse sebagai time (HH:mm atau HH:mm:ss)
                        $parts = explode(':', $jamMasukShift);
                        if (count($parts) >= 2) {
                            $hour = (int) $parts[0];
                            $minute = (int) $parts[1];
                            $second = isset($parts[2]) ? (int) $parts[2] : 0;
                            $jamMasukFormatted = sprintf('%02d:%02d:%02d', $hour, $minute, $second);
                        } else {
                            return response()->json([
                                'success' => false, 
                                'message' => 'Format jam masuk shift tidak valid'
                            ], 422);
                        }
                    } catch (\Exception $e) {
                        return response()->json([
                            'success' => false, 
                            'message' => 'Format jam masuk shift tidak valid'
                        ], 422);
                    }
                } else {
                    // Try to parse as time
                    try {
                        $carbonTime = Carbon::parse($jamMasukShift);
                        $jamMasukFormatted = $carbonTime->format('H:i:s');
                    } catch (\Exception $e) {
                        return response()->json([
                            'success' => false, 
                            'message' => 'Format jam masuk shift tidak valid'
                        ], 422);
                    }
                }
                
                // Format tanggal
                $tanggalStr = Carbon::parse($request->dtTanggal)->format('Y-m-d');
                $nikStr = (string) $request->vcNik;
                
                // Keterangan (maksimal 20 karakter sesuai constraint)
                $keterangan = 'Auto: Masuk Siang';
                if (strlen($keterangan) > 20) {
                    $keterangan = substr($keterangan, 0, 20);
                }
                
                // Cek apakah data absensi sudah ada
                $absenExists = DB::table('t_absen')
                    ->where('dtTanggal', $tanggalStr)
                    ->where('vcNik', $nikStr)
                    ->exists();
                
                if ($absenExists) {
                    // Update dtJamMasuk saja (dtJamKeluar tidak diubah)
                    DB::table('t_absen')
                        ->where('dtTanggal', $tanggalStr)
                        ->where('vcNik', $nikStr)
                        ->update([
                            'dtJamMasuk' => $jamMasukFormatted,
                            'vcketerangan' => $keterangan,
                            'dtChange' => Carbon::now()->format('Y-m-d H:i:s'),
                        ]);
                } else {
                    // Insert baru
                    DB::table('t_absen')->insert([
                        'dtTanggal' => $tanggalStr,
                        'vcNik' => $nikStr,
                        'dtJamMasuk' => $jamMasukFormatted,
                        'dtJamKeluar' => null,
                        'dtJamMasukLembur' => null,
                        'dtJamKeluarLembur' => null,
                        'vcketerangan' => $keterangan,
                        'dtCreate' => Carbon::now()->format('Y-m-d H:i:s'),
                        'dtChange' => Carbon::now()->format('Y-m-d H:i:s'),
                    ]);
                }
            } catch (\Exception $e) {
                // Log error tapi tidak gagalkan proses create izin
                \Log::error('Error auto insert/update absensi dari izin masuk siang: ' . $e->getMessage());
                // Tetap return success untuk create izin, tapi bisa tambahkan warning jika diperlukan
            }
        }

        return response()->json(['success' => true, 'message' => 'Izin Keluar berhasil ditambahkan', 'vcCounter' => $vcCounter]);
    }

    public function show(string $id)
    {
        $record = Izin::with(['karyawan', 'jenisIzin'])->findOrFail($id);
        // Pastikan tanggal dan jam dalam format yang bisa langsung dipakai input HTML
        $payload = $record->toArray();
        $payload['dtTanggal'] = $record->dtTanggal ? $record->dtTanggal->format('Y-m-d') : null;
        $payload['dtDari'] = $record->dtDari ? substr((string) $record->dtDari, 0, 5) : null;
        $payload['dtSampai'] = $record->dtSampai ? substr((string) $record->dtSampai, 0, 5) : null;
        return response()->json(['success' => true, 'record' => $payload]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'dtTanggal' => 'required|date',
            'vcNik' => 'required|string|max:10',
            'vcKodeIzin' => 'required|string|max:5|exists:m_jenis_izin,vcKodeIzin',
            'vcTipeIzin' => 'nullable|string|max:20|in:Masuk Siang,Izin Biasa,Pulang Cepat',
            'dtDari' => 'required|date_format:H:i',
            'dtSampai' => 'required|date_format:H:i',
            'vcKeterangan' => 'nullable|string|max:35',
        ]);

        $record = Izin::findOrFail($id);
        
        // Simpan nilai lama "Sampai" untuk cek apakah berubah
        $dtSampaiLama = $record->dtSampai ? substr((string) $record->dtSampai, 0, 5) : null;
        $dtSampaiBaru = $request->dtSampai;
        
        // Cek apakah "Sampai" berubah
        $isSampaiBerubah = ($dtSampaiLama !== $dtSampaiBaru);
        
        $record->update([
            'dtTanggal' => $request->dtTanggal,
            'vcNik' => $request->vcNik,
            'vcKodeIzin' => $request->vcKodeIzin,
            'vcTipeIzin' => $request->vcTipeIzin,
            'dtDari' => $request->dtDari . ':00',
            'dtSampai' => $request->dtSampai . ':00',
            'vcKeterangan' => $request->vcKeterangan,
            'dtChange' => Carbon::now(),
        ]);

        // Update keterangan di t_absen jika "Sampai" berubah dan kondisi terpenuhi
        if ($isSampaiBerubah) {
            $isPribadi = in_array($request->vcKodeIzin, ['Z003', 'Z004']);
            $isMasukSiang = $request->vcTipeIzin === 'Masuk Siang';
            
            if ($isPribadi && $isMasukSiang) {
                try {
                    // Format tanggal dan NIK
                    $tanggalStr = Carbon::parse($request->dtTanggal)->format('Y-m-d');
                    $nikStr = (string) $request->vcNik;
                    
                    // Cek apakah data absensi sudah ada
                    $absenExists = DB::table('t_absen')
                        ->where('dtTanggal', $tanggalStr)
                        ->where('vcNik', $nikStr)
                        ->exists();
                    
                    if ($absenExists) {
                        // Format jam "Sampai" ke HH:MM (tanpa detik)
                        $jamSampaiFormatted = $dtSampaiBaru; // Sudah dalam format HH:MM dari request
                        
                        // Keterangan: "Masuk Siang 14:00" (maksimal 20 karakter)
                        $keterangan = 'Masuk Siang ' . $jamSampaiFormatted;
                        if (strlen($keterangan) > 20) {
                            $keterangan = substr($keterangan, 0, 20);
                        }
                        
                        // Update keterangan di t_absen (mengganti yang lama)
                        DB::table('t_absen')
                            ->where('dtTanggal', $tanggalStr)
                            ->where('vcNik', $nikStr)
                            ->update([
                                'vcketerangan' => $keterangan,
                                'dtChange' => Carbon::now()->format('Y-m-d H:i:s'),
                            ]);
                    }
                } catch (\Exception $e) {
                    // Log error tapi tidak gagalkan proses update izin
                    \Log::error('Error update keterangan absensi dari izin masuk siang: ' . $e->getMessage());
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Izin Keluar berhasil diperbarui']);
    }

    public function destroy(string $id)
    {
        $record = Izin::findOrFail($id);
        $record->delete();
        return response()->json(['success' => true, 'message' => 'Izin Keluar berhasil dihapus']);
    }

    /**
     * Print surat izin keluar komplek (single)
     */
    public function print(string $id)
    {
        $record = Izin::with(['karyawan.bagian', 'karyawan.divisi', 'jenisIzin'])->findOrFail($id);
        
        return view('absen.izin_keluar.print', compact('record'));
    }

    /**
     * Print multiple surat izin keluar komplek
     */
    public function printMultiple(Request $request)
    {
        $ids = $request->get('ids');
        
        if (!$ids) {
            return redirect()->route('izin-keluar.index')
                ->with('error', 'Tidak ada surat izin yang dipilih untuk di-print.');
        }
        
        // ids bisa berupa array atau comma-separated string
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        
        $records = Izin::with(['karyawan.bagian', 'karyawan.divisi', 'jenisIzin'])
            ->whereIn('vcCounter', $ids)
            ->orderBy('dtTanggal', 'asc')
            ->orderBy('vcNik', 'asc')
            ->get();
        
        if ($records->isEmpty()) {
            return redirect()->route('izin-keluar.index')
                ->with('error', 'Data surat izin tidak ditemukan.');
        }
        
        return view('absen.izin_keluar.print-multiple', compact('records'));
    }
}
