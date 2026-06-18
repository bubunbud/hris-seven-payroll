<?php

namespace App\Http\Controllers;

use App\Models\Izin;
use App\Models\JenisIzin;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IzinKeluarController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('dari_tanggal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('sampai_tanggal', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        // Get filter parameters
        $search = $request->get('search'); // NIK / Nama (gabungan)
        // Backward compatibility: jika masih ada parameter nik, gunakan itu
        $nik = $request->get('nik');
        if (!$search && $nik) {
            $search = $nik;
        }

        // Load karyawan aktif untuk autocomplete lokal
        $karyawans = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->with(['divisi', 'bagian'])
            ->orderBy('Nama')
            ->get(['Nik', 'Nama', 'Divisi', 'vcKodeBagian']);

        // Siapkan data sederhana untuk frontend (hindari logic berat di Blade)
        $karyawanList = $karyawans->map(function ($k) {
            $divisiNama = '-';
            if ($k->divisi && isset($k->divisi->vcNamaDivisi)) {
                $divisiNama = $k->divisi->vcNamaDivisi;
            } elseif ($k->Divisi) {
                $divisiNama = $k->Divisi;
            }

            $bagianNama = '-';
            if ($k->bagian && isset($k->bagian->vcNamaBagian)) {
                $bagianNama = $k->bagian->vcNamaBagian;
            }

            return [
                'nik' => $k->Nik ?: '',
                'nama' => $k->Nama ?: '',
                'divisi' => $divisiNama,
                'bagian' => $bagianNama,
                'search' => strtolower(($k->Nik ?: '') . ' ' . ($k->Nama ?: '')),
            ];
        })->values();

        $query = Izin::with(['karyawan', 'jenisIzin'])
            ->whereBetween('dtTanggal', [$startDate, $endDate])
            ->orderBy('dtTanggal', 'desc')
            ->orderBy('dtDari');

        // Apply search filter (multi-term support)
        if ($search) {
            $searchTerms = preg_split('/,\s*/', trim($search));
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    if (!empty(trim($term))) {
                        $term = trim($term);
                        // Jika format "NIK - Nama", ambil NIK saja
                        if (strpos($term, ' - ') !== false) {
                            $term = explode(' - ', $term)[0];
                        }
                        $q->orWhere('vcNik', 'like', '%' . $term . '%')
                            ->orWhereHas('karyawan', function ($q2) use ($term) {
                                $q2->where('Nama', 'like', '%' . $term . '%');
                            });
                    }
                }
            });
        }

        $records = $query->paginate(25);
        $jenisIzins = JenisIzin::orderBy('vcKeterangan')->get();

        return view('absen.izin_keluar.index', compact('records', 'jenisIzins', 'startDate', 'endDate', 'search', 'karyawanList'));
    }

    public function store(Request $request)
    {
        // Cek apakah kondisi "Pulang Cepat" (Jenis Izin = Pribadi dan Tipe = Pulang Cepat)
        $isPribadi = in_array($request->vcKodeIzin, ['Z003', 'Z004']);
        $isPulangCepat = $request->vcTipeIzin === 'Pulang Cepat';
        $isDariRequired = !($isPribadi && $isPulangCepat);
        $isMasukSiang = $isPribadi && $request->vcTipeIzin === 'Masuk Siang';
        
        $rules = [
            'dtTanggal' => 'required|date',
            'vcNik' => 'required|string|max:10',
            'vcKodeIzin' => 'required|string|max:5|exists:m_jenis_izin,vcKodeIzin',
            'vcKeterangan' => 'nullable|string|max:35',
        ];

        // Aturan dtSampai:
        // - Default: required
        // - Khusus Jenis Izin Pribadi (Z003/Z004) dengan Tipe = Masuk Siang: boleh kosong (nullable)
        if ($isMasukSiang) {
            $rules['dtSampai'] = 'nullable|date_format:H:i';
        } else {
            $rules['dtSampai'] = 'required|date_format:H:i';
        }
        
        // Validasi vcTipeIzin: hanya untuk jenis izin pribadi (Z003, Z004)
        if ($isPribadi) {
            $rules['vcTipeIzin'] = 'nullable|string|max:20|in:Masuk Siang,Izin Biasa,Pulang Cepat';
        } else {
            // Untuk jenis izin lain (seperti Z001), vcTipeIzin bisa kosong tanpa validasi in:
            $rules['vcTipeIzin'] = 'nullable|string|max:20';
        }
        
        // Field "dtDari" hanya required jika bukan kondisi "Pulang Cepat"
        if ($isDariRequired) {
            $rules['dtDari'] = 'required|date_format:H:i';
        } else {
            $rules['dtDari'] = 'nullable|date_format:H:i';
        }
        
        $request->validate($rules);

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
        // Jika kondisi "Pulang Cepat", dtDari bisa null
        $dtDariValue = $request->dtDari ? $request->dtDari . ':00' : null;

        // dtSampai:
        // - Jika diisi → simpan HH:MM:00
        // - Jika kosong & Masuk Siang → fallback ke dtDari (untuk menghindari error DB NOT NULL),
        //   sementara logika t_absen tetap tidak menggunakan dtJamKeluar
        // - Jika kosong & bukan Masuk Siang → secara teori tidak terjadi karena divalidasi required
        if ($request->dtSampai) {
            $dtSampaiValue = $request->dtSampai . ':00';
        } elseif ($isMasukSiang) {
            $dtSampaiValue = $dtDariValue; // fallback aman untuk kolom di t_izin
        } else {
            $dtSampaiValue = null;
        }

        // vcCounter panjang 9: 2 digit tahun + 7 digit acak (tidak dipotong substr — algoritma lama mdY+rand
        // membuat hanya ~9 kemungkinan/hari setelah substr 9, sangat mudah bentrok + race exists/create).
        $vcCounter = null;
        $createPayload = [
            'dtTanggal' => $request->dtTanggal,
            'vcNik' => $request->vcNik,
            'vcKodeIzin' => $request->vcKodeIzin,
            'vcTipeIzin' => $request->vcTipeIzin,
            'dtDari' => $dtDariValue,
            'dtSampai' => $dtSampaiValue,
            'vcKeterangan' => $request->vcKeterangan,
            'dtCreate' => Carbon::now(),
        ];

        for ($attempt = 0; $attempt < 25; $attempt++) {
            $candidate = $this->newVcCounterCandidate();
            try {
                Izin::create(array_merge($createPayload, ['vcCounter' => $candidate]));
                $vcCounter = $candidate;
                break;
            } catch (QueryException $e) {
                if ($this->isMysqlDuplicateKey($e)) {
                    continue;
                }
                throw $e;
            }
        }

        if ($vcCounter === null) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat nomor counter unik. Silakan coba lagi.',
            ], 500);
        }

        // Format tanggal dan NIK untuk update t_absen
        $tanggalStr = Carbon::parse($request->dtTanggal)->format('Y-m-d');
        $nikStr = (string) $request->vcNik;
        
        // Format jam "Sampai" ke HH:mm:ss untuk dtJamKeluar
        $jamSampaiFormatted = $request->dtSampai ? ($request->dtSampai . ':00') : null;
        
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
                
                // Keterangan (maksimal 20 karakter sesuai constraint)
                // Jika Jam "Sampai" diisi, tambahkan ke keterangan
                // Format yang diinginkan: "Masuk Siang" + jam (tanpa prefix "Auto:")
                $keterangan = 'Masuk Siang';
                if ($request->dtSampai) {
                    $keterangan .= ' ' . $request->dtSampai;
                }
                if (strlen($keterangan) > 20) {
                    $keterangan = substr($keterangan, 0, 20);
                }
                
                // Cek apakah data absensi sudah ada
                $absenExists = DB::table('t_absen')
                    ->where('dtTanggal', $tanggalStr)
                    ->where('vcNik', $nikStr)
                    ->exists();
                
                if ($absenExists) {
                    // Update hanya dtJamMasuk dan vcketerangan (TIDAK menyentuh dtJamKeluar)
                    DB::table('t_absen')
                        ->where('dtTanggal', $tanggalStr)
                        ->where('vcNik', $nikStr)
                        ->update([
                            'dtJamMasuk' => $jamMasukFormatted,
                            'vcketerangan' => $keterangan,
                            'dtChange' => Carbon::now()->format('Y-m-d H:i:s'),
                        ]);
                } else {
                    // Insert baru: set dtJamMasuk, dtJamKeluar dibiarkan null
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
        // Izin Biasa (pribadi): tidak menyentuh t_absen
        } elseif ($isPribadi && $isPulangCepat) {
            // Hanya Pulang Cepat: jam "Sampai" → t_absen.dtJamKeluar
            try {
                $keterangan = 'Auto: Pulang Cepat';
                if (strlen($keterangan) > 20) {
                    $keterangan = substr($keterangan, 0, 20);
                }
                
                // Cek apakah data absensi sudah ada
                $absenExists = DB::table('t_absen')
                    ->where('dtTanggal', $tanggalStr)
                    ->where('vcNik', $nikStr)
                    ->exists();
                
                if ($absenExists) {
                    // Update dtJamKeluar saja
                    DB::table('t_absen')
                        ->where('dtTanggal', $tanggalStr)
                        ->where('vcNik', $nikStr)
                        ->update([
                            'dtJamKeluar' => $jamSampaiFormatted,
                            'vcketerangan' => $keterangan,
                            'dtChange' => Carbon::now()->format('Y-m-d H:i:s'),
                        ]);
                } else {
                    // Insert baru dengan dtJamKeluar
                    DB::table('t_absen')->insert([
                        'dtTanggal' => $tanggalStr,
                        'vcNik' => $nikStr,
                        'dtJamMasuk' => null,
                        'dtJamKeluar' => $jamSampaiFormatted,
                        'dtJamMasukLembur' => null,
                        'dtJamKeluarLembur' => null,
                        'vcketerangan' => $keterangan,
                        'dtCreate' => Carbon::now()->format('Y-m-d H:i:s'),
                        'dtChange' => Carbon::now()->format('Y-m-d H:i:s'),
                    ]);
                }
            } catch (\Exception $e) {
                // Log error tapi tidak gagalkan proses create izin
                \Log::error('Error auto insert/update absensi dari izin pulang cepat: ' . $e->getMessage());
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
        // Cek apakah kondisi "Pulang Cepat" (Jenis Izin = Pribadi dan Tipe = Pulang Cepat)
        $isPribadi = in_array($request->vcKodeIzin, ['Z003', 'Z004']);
        $isPulangCepat = $request->vcTipeIzin === 'Pulang Cepat';
        $isDariRequired = !($isPribadi && $isPulangCepat);
        $isMasukSiang = $isPribadi && $request->vcTipeIzin === 'Masuk Siang';
        
        $rules = [
            'dtTanggal' => 'required|date',
            'vcNik' => 'required|string|max:10',
            'vcKodeIzin' => 'required|string|max:5|exists:m_jenis_izin,vcKodeIzin',
            'vcKeterangan' => 'nullable|string|max:35',
        ];

        // Aturan dtSampai:
        // - Default: required
        // - Khusus Jenis Izin Pribadi (Z003/Z004) dengan Tipe = Masuk Siang: boleh kosong (nullable)
        if ($isMasukSiang) {
            $rules['dtSampai'] = 'nullable|date_format:H:i';
        } else {
            $rules['dtSampai'] = 'required|date_format:H:i';
        }
        
        // Validasi vcTipeIzin: hanya untuk jenis izin pribadi (Z003, Z004)
        if ($isPribadi) {
            $rules['vcTipeIzin'] = 'nullable|string|max:20|in:Masuk Siang,Izin Biasa,Pulang Cepat';
        } else {
            // Untuk jenis izin lain (seperti Z001), vcTipeIzin bisa kosong tanpa validasi in:
            $rules['vcTipeIzin'] = 'nullable|string|max:20';
        }
        
        // Field "dtDari" hanya required jika bukan kondisi "Pulang Cepat"
        if ($isDariRequired) {
            $rules['dtDari'] = 'required|date_format:H:i';
        } else {
            $rules['dtDari'] = 'nullable|date_format:H:i';
        }
        
        $request->validate($rules);

        $record = Izin::findOrFail($id);
        
        // Simpan nilai lama "Sampai" untuk cek apakah berubah
        $dtSampaiLama = $record->dtSampai ? substr((string) $record->dtSampai, 0, 5) : null;
        $dtSampaiBaru = $request->dtSampai;
        
        // Cek apakah "Sampai" berubah
        $isSampaiBerubah = ($dtSampaiLama !== $dtSampaiBaru);
        
        // Jika kondisi "Pulang Cepat", dtDari bisa null
        $dtDariValue = $request->dtDari ? $request->dtDari . ':00' : null;

        // dtSampai:
        // - Jika diisi → simpan HH:MM:00
        // - Jika kosong & Masuk Siang → fallback ke nilai lama (hindari NULL untuk kolom NOT NULL)
        // - Jika kosong & bukan Masuk Siang → secara teori tidak terjadi karena divalidasi required
        if ($request->dtSampai) {
            $dtSampaiDb = $request->dtSampai . ':00';
        } elseif ($isMasukSiang) {
            $dtSampaiDb = $record->dtSampai; // jaga nilai lama
        } else {
            $dtSampaiDb = null;
        }
        
        $record->update([
            'dtTanggal' => $request->dtTanggal,
            'vcNik' => $request->vcNik,
            'vcKodeIzin' => $request->vcKodeIzin,
            'vcTipeIzin' => $request->vcTipeIzin,
            'dtDari' => $dtDariValue,
            'dtSampai' => $dtSampaiDb,
            'vcKeterangan' => $request->vcKeterangan,
            'dtChange' => Carbon::now(),
        ]);

        // Format tanggal dan NIK untuk update t_absen
        $tanggalStr = Carbon::parse($request->dtTanggal)->format('Y-m-d');
        $nikStr = (string) $request->vcNik;
        
        // Format jam "Sampai" ke HH:mm:ss untuk dtJamKeluar
        $jamSampaiFormatted = $request->dtSampai ? ($request->dtSampai . ':00') : null;
        
        // Sync t_absen: Masuk Siang → dtJamMasuk; Pulang Cepat → dtJamKeluar; Izin Biasa → tidak ubah absen
        $isPribadiUpdate = in_array($request->vcKodeIzin, ['Z003', 'Z004']);

        if ($isPribadiUpdate) {
            try {
                $isMasukSiangUpd = $request->vcTipeIzin === 'Masuk Siang';
                $isPulangCepatUpd = $request->vcTipeIzin === 'Pulang Cepat';

                $absenExists = DB::table('t_absen')
                    ->where('dtTanggal', $tanggalStr)
                    ->where('vcNik', $nikStr)
                    ->exists();

                if ($isMasukSiangUpd) {
                    $keterangan = 'Masuk Siang';
                    if ($request->dtSampai) {
                        $keterangan .= ' ' . $request->dtSampai;
                    }
                    if (strlen($keterangan) > 20) {
                        $keterangan = substr($keterangan, 0, 20);
                    }

                    if ($absenExists) {
                        $updateData = [
                            'vcketerangan' => $keterangan,
                            'dtChange' => Carbon::now()->format('Y-m-d H:i:s'),
                        ];
                        $karyawan = Karyawan::with('shift')->where('Nik', $nikStr)->first();
                        if ($karyawan && $karyawan->shift && $karyawan->shift->vcMasuk) {
                            $jamMasukShift = $karyawan->shift->vcMasuk;
                            $jamMasukFormatted = null;

                            if ($jamMasukShift instanceof Carbon) {
                                $jamMasukFormatted = $jamMasukShift->format('H:i:s');
                            } elseif (is_string($jamMasukShift)) {
                                $parts = explode(':', $jamMasukShift);
                                if (count($parts) >= 2) {
                                    $hour = (int) $parts[0];
                                    $minute = (int) $parts[1];
                                    $second = isset($parts[2]) ? (int) $parts[2] : 0;
                                    $jamMasukFormatted = sprintf('%02d:%02d:%02d', $hour, $minute, $second);
                                }
                            }

                            if ($jamMasukFormatted) {
                                $updateData['dtJamMasuk'] = $jamMasukFormatted;
                            }
                        }

                        DB::table('t_absen')
                            ->where('dtTanggal', $tanggalStr)
                            ->where('vcNik', $nikStr)
                            ->update($updateData);
                    } else {
                        $insertData = [
                            'dtTanggal' => $tanggalStr,
                            'vcNik' => $nikStr,
                            'dtJamMasuk' => null,
                            'dtJamKeluar' => null,
                            'dtJamMasukLembur' => null,
                            'dtJamKeluarLembur' => null,
                            'vcketerangan' => $keterangan,
                            'dtCreate' => Carbon::now()->format('Y-m-d H:i:s'),
                            'dtChange' => Carbon::now()->format('Y-m-d H:i:s'),
                        ];

                        $karyawan = Karyawan::with('shift')->where('Nik', $nikStr)->first();
                        if ($karyawan && $karyawan->shift && $karyawan->shift->vcMasuk) {
                            $jamMasukShift = $karyawan->shift->vcMasuk;
                            $jamMasukFormatted = null;

                            if ($jamMasukShift instanceof Carbon) {
                                $jamMasukFormatted = $jamMasukShift->format('H:i:s');
                            } elseif (is_string($jamMasukShift)) {
                                $parts = explode(':', $jamMasukShift);
                                if (count($parts) >= 2) {
                                    $hour = (int) $parts[0];
                                    $minute = (int) $parts[1];
                                    $second = isset($parts[2]) ? (int) $parts[2] : 0;
                                    $jamMasukFormatted = sprintf('%02d:%02d:%02d', $hour, $minute, $second);
                                }
                            }

                            if ($jamMasukFormatted) {
                                $insertData['dtJamMasuk'] = $jamMasukFormatted;
                            }
                        }

                        DB::table('t_absen')->insert($insertData);
                    }
                } elseif ($isPulangCepatUpd) {
                    $keterangan = 'Auto: Pulang Cepat';
                    if (strlen($keterangan) > 20) {
                        $keterangan = substr($keterangan, 0, 20);
                    }

                    if ($absenExists) {
                        $updateData = [
                            'vcketerangan' => $keterangan,
                            'dtChange' => Carbon::now()->format('Y-m-d H:i:s'),
                        ];
                        if ($jamSampaiFormatted) {
                            $updateData['dtJamKeluar'] = $jamSampaiFormatted;
                        }

                        DB::table('t_absen')
                            ->where('dtTanggal', $tanggalStr)
                            ->where('vcNik', $nikStr)
                            ->update($updateData);
                    } else {
                        DB::table('t_absen')->insert([
                            'dtTanggal' => $tanggalStr,
                            'vcNik' => $nikStr,
                            'dtJamMasuk' => null,
                            'dtJamKeluar' => $jamSampaiFormatted,
                            'dtJamMasukLembur' => null,
                            'dtJamKeluarLembur' => null,
                            'vcketerangan' => $keterangan,
                            'dtCreate' => Carbon::now()->format('Y-m-d H:i:s'),
                            'dtChange' => Carbon::now()->format('Y-m-d H:i:s'),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error update absensi dari izin keluar: ' . $e->getMessage());
            }
        }

        return response()->json(['success' => true, 'message' => 'Izin Keluar berhasil diperbarui']);
    }

    /**
     * Baca flag hapus_absensi dari form atau body JSON (beberapa lingkungan tidak merge JSON ke input()).
     */
    private function requestHapusAbsensi(Request $request): bool
    {
        if ($request->has('hapus_absensi')) {
            return $request->boolean('hapus_absensi');
        }

        $contentType = (string) $request->header('Content-Type');
        if (str_contains($contentType, 'json')) {
            $decoded = json_decode($request->getContent(), true);
            if (is_array($decoded) && array_key_exists('hapus_absensi', $decoded)) {
                return filter_var($decoded['hapus_absensi'], FILTER_VALIDATE_BOOLEAN);
            }
        }

        return false;
    }

    public function destroy(Request $request, string $id)
    {
        $hapusAbsensi = $this->requestHapusAbsensi($request);

        try {
            DB::transaction(function () use ($id, $hapusAbsensi) {
                $record = Izin::lockForUpdate()->findOrFail($id);

                $isPribadi = in_array($record->vcKodeIzin, ['Z003', 'Z004']);
                $tipeNorm = trim((string) ($record->vcTipeIzin ?? ''));
                $isMasukSiang = $isPribadi && $tipeNorm === 'Masuk Siang';

                if ($isMasukSiang && $hapusAbsensi) {
                    $tanggalStr = Carbon::parse($record->dtTanggal)->format('Y-m-d');
                    $nikStr = trim((string) $record->vcNik);

                    // whereDate + TRIM(vcNik): cocokkan kolom datetime/date & NIK bertipe CHAR yang ter-padding
                    DB::table('t_absen')
                        ->whereDate('dtTanggal', $tanggalStr)
                        ->whereRaw('TRIM(vcNik) = ?', [$nikStr])
                        ->delete();
                }

                $record->delete();
            });
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error hapus izin keluar / absensi terkait: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data. Silakan coba lagi.',
            ], 500);
        }

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

    /**
     * Nomor counter 9 digit: yy + 7 digit acak (10 juta ruang per tahun kalender).
     */
    private function newVcCounterCandidate(): string
    {
        $yy = Carbon::now()->format('y');
        $suffix = str_pad((string) random_int(0, 9_999_999), 7, '0', STR_PAD_LEFT);

        return $yy . $suffix;
    }

    private function isMysqlDuplicateKey(QueryException $e): bool
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            return str_contains(strtolower($e->getMessage()), 'unique');
        }

        $code = (int) ($e->errorInfo[1] ?? 0);

        return $code === 1062;
    }
}
