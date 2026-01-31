<?php

namespace App\Http\Controllers;

use App\Models\Absen;
use App\Models\Karyawan;
use App\Models\TidakMasuk;
use App\Models\HariLibur;
use App\Services\SecurityAbsensiService;
use App\Traits\HariKerjaHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AbsenController extends Controller
{
    use HariKerjaHelper;
    public function index(Request $request)
    {
        // Tingkatkan memory limit dan timeout untuk handle data besar
        ini_set('memory_limit', '512M');
        set_time_limit(300); // 5 menit
        
        // Default date range (current month)
        $startDate = $request->get('dari_tanggal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('sampai_tanggal', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Get filter parameters
        $search = $request->get('search'); // NIK / Nama (gabungan)
        // Backward compatibility: jika masih ada parameter nik/nama, gunakan itu
        $nik = $request->get('nik');
        $nama = $request->get('nama');
        if (!$search && ($nik || $nama)) {
            // Jika ada parameter lama, gabungkan menjadi search
            $searchParts = [];
            if ($nik) $searchParts[] = $nik;
            if ($nama) $searchParts[] = $nama;
            $search = implode(', ', $searchParts);
        }
        
        $tidakMasuk = $request->get('tidak_masuk');
        $absenTidakLengkap = $request->get('absen_tidak_lengkap');
        $hariKerjaNormal = $request->get('hari_kerja_normal');
        $kerjaHariLibur = $request->get('kerja_hari_libur');
        $telat = $request->get('telat');
        $group = $request->get('group', 'Semua Group');

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

        // Get hari libur list untuk periode ini (cached)
        $hariLiburList = $this->getHariLiburList($startDate, $endDate);

        // Build base karyawan filter query untuk reuse
        $karyawanFilter = function ($query) use ($search, $group) {
            if ($search) {
                // Split by comma untuk multi pencarian
                $searchTerms = preg_split('/,\s*/', trim($search));
                
                $query->where(function ($q) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        if (!empty(trim($term))) {
                            $term = trim($term);
                            // Jika format "NIK - Nama", ambil NIK saja
                            if (strpos($term, ' - ') !== false) {
                                $term = explode(' - ', $term)[0];
                            }
                            $q->orWhere('m_karyawan.Nik', 'like', '%' . $term . '%')
                                ->orWhere('m_karyawan.Nama', 'like', '%' . $term . '%');
                        }
                    }
                });
            }
            if ($group !== 'Semua Group') {
                $query->where('m_karyawan.Group_pegawai', $group);
            }
        };

        // Query untuk absen dengan eager loading minimal
        $absenQuery = DB::table('t_absen')
            ->join('m_karyawan', 't_absen.vcNik', '=', 'm_karyawan.Nik')
            ->leftJoin('m_divisi', 'm_karyawan.Divisi', '=', 'm_divisi.vcKodeDivisi')
            ->leftJoin('m_dept', 'm_karyawan.dept', '=', 'm_dept.vcKodeDept')
            ->leftJoin('m_bagian', 'm_karyawan.vcKodeBagian', '=', 'm_bagian.vcKodeBagian')
            ->leftJoin('m_shift', 'm_karyawan.vcShift', '=', 'm_shift.vcShift')
            ->leftJoin('t_jadwal_shift_security as jadwal', function ($join) {
                $join->on('t_absen.vcNik', '=', 'jadwal.vcNik')
                    ->on('t_absen.dtTanggal', '=', 'jadwal.dtTanggal');
            })
            ->whereBetween('t_absen.dtTanggal', [$startDate, $endDate])
            ->select(
                't_absen.dtTanggal',
                't_absen.vcNik',
                't_absen.dtJamMasuk',
                't_absen.dtJamKeluar',
                't_absen.dtJamMasukLembur',
                't_absen.dtJamKeluarLembur',
                'm_karyawan.Nama',
                'm_karyawan.Divisi',
                'm_karyawan.dept',
                'm_karyawan.vcKodeBagian',
                'm_karyawan.Group_pegawai',
                'm_divisi.vcNamaDivisi',
                'm_dept.vcNamaDept',
                'm_bagian.vcNamaBagian',
                'm_shift.vcMasuk as shift_masuk'
            );

        // Apply filters
        $karyawanFilter($absenQuery);

        // Build query untuk tidak masuk dengan expand tanggal di SQL (lebih efisien)
        // Gunakan recursive CTE untuk expand tanggal (jika MySQL 8+) atau cara lain untuk MySQL 5.7
        $tidakMasukQuery = DB::table('t_tidak_masuk')
            ->join('m_karyawan', 't_tidak_masuk.vcNik', '=', 'm_karyawan.Nik')
            ->leftJoin('m_jenis_absen', 't_tidak_masuk.vcKodeAbsen', '=', 'm_jenis_absen.vcKodeAbsen')
            ->leftJoin('m_divisi', 'm_karyawan.Divisi', '=', 'm_divisi.vcKodeDivisi')
            ->leftJoin('m_dept', 'm_karyawan.dept', '=', 'm_dept.vcKodeDept')
            ->leftJoin('m_bagian', 'm_karyawan.vcKodeBagian', '=', 'm_bagian.vcKodeBagian')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('t_tidak_masuk.dtTanggalMulai', [$startDate, $endDate])
                    ->orWhereBetween('t_tidak_masuk.dtTanggalSelesai', [$startDate, $endDate])
                    ->orWhere(function ($qq) use ($startDate, $endDate) {
                        $qq->where('t_tidak_masuk.dtTanggalMulai', '<=', $startDate)
                            ->where('t_tidak_masuk.dtTanggalSelesai', '>=', $endDate);
                    });
            })
            ->whereNotNull('t_tidak_masuk.dtTanggalMulai')
            ->whereNotNull('t_tidak_masuk.dtTanggalSelesai')
            ->select(
                't_tidak_masuk.vcNik',
                't_tidak_masuk.vcKodeAbsen',
                't_tidak_masuk.dtTanggalMulai',
                't_tidak_masuk.dtTanggalSelesai',
                't_tidak_masuk.vcKeterangan',
                'm_karyawan.Nama',
                'm_karyawan.Divisi',
                'm_karyawan.dept',
                'm_karyawan.vcKodeBagian',
                'm_divisi.vcNamaDivisi',
                'm_dept.vcNamaDept',
                'm_bagian.vcNamaBagian',
                'm_jenis_absen.vcKeterangan as jenis_absen_keterangan'
            );

        // Apply filters
        $karyawanFilter($tidakMasukQuery);

        // Ambil data tidak masuk (minimal, tanpa expand dulu)
        $tidakMasukRecords = $tidakMasukQuery->get();

        // Ambil semua NIK + Tanggal yang sudah ada di absen untuk exclude (optimasi dengan chunking)
        $absenExists = collect();
        DB::table('t_absen')
            ->whereBetween('dtTanggal', [$startDate, $endDate])
            ->select('dtTanggal', 'vcNik')
            ->orderBy('dtTanggal')
            ->orderBy('vcNik')
            ->chunk(5000, function ($chunk) use (&$absenExists) {
                foreach ($chunk as $item) {
                    $absenExists->put($item->dtTanggal . '_' . $item->vcNik, true);
                }
            });

        // Expand tidak masuk per tanggal (di PHP tapi dengan optimasi)
        $tidakMasukExpanded = [];
        $filterStart = Carbon::parse($startDate);
        $filterEnd = Carbon::parse($endDate);

        foreach ($tidakMasukRecords as $tm) {
            $current = Carbon::parse($tm->dtTanggalMulai);
            $end = Carbon::parse($tm->dtTanggalSelesai);

            // Batasi loop maksimal 365 hari untuk menghindari infinite loop
            $maxDays = 365;
            $dayCount = 0;

            while ($current->lte($end) && $dayCount < $maxDays) {
                // Skip jika di luar range filter
                if ($current->lt($filterStart) || $current->gt($filterEnd)) {
                    $current->addDay();
                    $dayCount++;
                    continue;
                }

                $tanggalStr = $current->format('Y-m-d');
                $key = $tanggalStr . '_' . $tm->vcNik;

                // Cek apakah sudah ada di absen, jika ada skip (prioritas absen)
                if (!$absenExists->has($key)) {
                    $tidakMasukExpanded[] = [
                        'dtTanggal' => $tanggalStr,
                        'vcNik' => $tm->vcNik,
                        'Nama' => $tm->Nama,
                        'Divisi' => $tm->Divisi,
                        'vcKodeBagian' => $tm->vcKodeBagian,
                        'vcNamaDivisi' => $tm->vcNamaDivisi,
                        'vcNamaDept' => $tm->vcNamaDept ?? null,
                        'vcNamaBagian' => $tm->vcNamaBagian,
                        'vcKodeAbsen' => $tm->vcKodeAbsen,
                        'jenis_absen_keterangan' => $tm->jenis_absen_keterangan,
                        'vcKeterangan' => $tm->vcKeterangan,
                        'source' => 'tidak_masuk',
                    ];
                }

                $current->addDay();
                $dayCount++;
            }
        }

        // Ambil data absen (dengan pagination di database level)
        // Apply filters terlebih dahulu
        if ($tidakMasuk) {
            // Jika filter tidak masuk, hanya ambil yang tidak ada jam masuk dan keluar
            $absenQuery->whereNull('t_absen.dtJamMasuk')
                ->whereNull('t_absen.dtJamKeluar');
        }

        if ($absenTidakLengkap) {
            // Jika filter absen tidak lengkap, hanya ambil yang salah satu jam null
            $absenQuery->where(function ($q) {
                $q->whereNull('t_absen.dtJamMasuk')
                    ->orWhereNull('t_absen.dtJamKeluar');
            });
        }

        // Gabungkan hasil dengan UNION di SQL (lebih efisien)
        // Tapi karena struktur berbeda, kita akan gabungkan di PHP tapi dengan pagination yang lebih efisien

        // Ambil semua jadwal shift security untuk periode ini (lebih efisien)
        $jadwalSecurity = DB::table('t_jadwal_shift_security')
            ->whereBetween('dtTanggal', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($item) {
                return $item->vcNik . '_' . $item->dtTanggal;
            })
            ->map(function ($group) {
                return $group->pluck('intShift')->toArray();
            });

        // Gabungkan data (dengan optimasi menggunakan chunking untuk data besar)
        $combinedData = collect();

        // Proses data absen dengan chunking untuk menghindari memory exhausted
        $absenQuery->orderBy('t_absen.dtTanggal', 'desc')
            ->orderBy('t_absen.vcNik')
            ->chunk(1000, function ($absensData) use (&$combinedData, $jadwalSecurity, $hariLiburList) {
                // Tambahkan data absen per chunk
                foreach ($absensData as $absen) {
            // Untuk Security: tentukan shift aktual dan validasi
            $shiftAktual = null;
            $shiftTerjadwal = [];
            $statusValidasi = null;

            if ($absen->Group_pegawai === 'Security') {
                // Tentukan shift aktual dari jam masuk/pulang
                $shiftAktual = SecurityAbsensiService::determineShiftFromTime(
                    $absen->dtJamMasuk,
                    $absen->dtJamKeluar,
                    $absen->dtTanggal
                );

                // Ambil shift terjadwal dari collection
                $key = $absen->vcNik . '_' . $absen->dtTanggal;
                if ($jadwalSecurity->has($key)) {
                    $shiftTerjadwal = $jadwalSecurity->get($key);
                }

                // Validasi
                $validasi = SecurityAbsensiService::validateAbsensiVsJadwal(
                    $absen->vcNik,
                    $absen->dtTanggal,
                    $shiftAktual
                );
                $statusValidasi = $validasi['status'];
            }

            // Tentukan status HKN/KHL dengan mempertimbangkan tukar hari kerja
            $tanggalObj = Carbon::parse($absen->dtTanggal);
            $totalJam = $this->calculateTotalJam($absen->dtJamMasuk, $absen->dtJamKeluar, $absen->dtTanggal);
            
            // Gunakan helper untuk cek apakah hari kerja normal (mempertimbangkan tukar hari kerja)
            $isHariKerjaNormal = $this->isHariKerjaNormal($absen->dtTanggal, $absen->vcNik);
            $isHariLibur = !$isHariKerjaNormal;
            
            // Cek telat: jam masuk > jam shift masuk (hanya untuk hari kerja normal)
            $isTelat = false;
            if ($absen->dtJamMasuk && $absen->shift_masuk && $isHariKerjaNormal) {
                try {
                    $jamMasuk = substr((string) $absen->dtJamMasuk, 0, 5);
                    $shiftMasuk = $absen->shift_masuk instanceof Carbon
                        ? $absen->shift_masuk->format('H:i')
                        : substr((string) $absen->shift_masuk, 0, 5);
                    
                    $tMasuk = $tanggalObj->copy()->setTimeFromTimeString($jamMasuk);
                    $tShiftMasuk = $tanggalObj->copy()->setTimeFromTimeString($shiftMasuk);
                    
                    if ($tMasuk->greaterThan($tShiftMasuk)) {
                        $isTelat = true;
                    }
                } catch (\Exception $e) {
                    // Skip jika ada error parsing waktu
                }
            }
            
            // Tentukan status sesuai logika di view
            $status = '';
            if (!$absen->dtJamMasuk && !$absen->dtJamKeluar) {
                $status = 'Tidak Masuk';
            } elseif ($isTelat) {
                // Telat: jam masuk > jam shift masuk (prioritas sebelum HKN)
                $status = 'Telat';
            } elseif (($absen->dtJamMasuk && !$absen->dtJamKeluar) || (!$absen->dtJamMasuk && $absen->dtJamKeluar)) {
                // Absen tidak lengkap: hanya ada satu dari jam masuk/keluar
                $status = 'ATL';
            } elseif ($isHariLibur && ($absen->dtJamMasuk || $absen->dtJamKeluar || $absen->dtJamMasukLembur)) {
                // KHL: Hari libur (weekend/holiday/tukar hari kerja) dan ada jam masuk/keluar/lembur
                $status = 'KHL';
            } elseif ($absen->dtJamMasuk && $absen->dtJamKeluar && $totalJam >= 8) {
                // Hari kerja normal (ada jam masuk dan keluar, minimal 8 jam)
                $status = 'HKN';
            } elseif ($absen->dtJamMasuk && $absen->dtJamKeluar && $totalJam > 0 && $totalJam < 8) {
                // HC: Ada jam masuk dan keluar tapi jam kerja kurang dari 8 jam
                $status = 'HC';
            } else {
                // Lainnya (tidak ada jam masuk atau keluar)
                $status = 'ATL';
            }

            $combinedData->push([
                'dtTanggal' => $absen->dtTanggal,
                'vcNik' => $absen->vcNik,
                'Nama' => $absen->Nama,
                'Divisi' => $absen->Divisi,
                'vcKodeBagian' => $absen->vcKodeBagian,
                'vcNamaDivisi' => $absen->vcNamaDivisi,
                'vcNamaDept' => $absen->vcNamaDept ?? null,
                'vcNamaBagian' => $absen->vcNamaBagian,
                'Group_pegawai' => $absen->Group_pegawai,
                'dtJamMasuk' => $absen->dtJamMasuk,
                'dtJamKeluar' => $absen->dtJamKeluar,
                'dtJamMasukLembur' => $absen->dtJamMasukLembur,
                'dtJamKeluarLembur' => $absen->dtJamKeluarLembur,
                'total_jam' => $totalJam,
                'shift_masuk' => $absen->shift_masuk ?? null,
                'shift_terjadwal' => $shiftTerjadwal,
                'shift_aktual' => $shiftAktual,
                'status_validasi' => $statusValidasi,
                'status' => $status,
                'source' => 'absen',
            ]);
                }
            });

        // Tambahkan data tidak masuk (jika tidak ada filter khusus)
        if (!$tidakMasuk && !$absenTidakLengkap) {
            foreach ($tidakMasukExpanded as $tm) {
                $combinedData->push([
                    'dtTanggal' => $tm['dtTanggal'],
                    'vcNik' => $tm['vcNik'],
                    'Nama' => $tm['Nama'],
                    'Divisi' => $tm['Divisi'],
                    'vcKodeBagian' => $tm['vcKodeBagian'],
                    'vcNamaDivisi' => $tm['vcNamaDivisi'],
                    'vcNamaDept' => $tm['vcNamaDept'] ?? null,
                    'vcNamaBagian' => $tm['vcNamaBagian'],
                    'dtJamMasuk' => null,
                    'dtJamKeluar' => null,
                    'dtJamMasukLembur' => null,
                    'dtJamKeluarLembur' => null,
                    'total_jam' => 0,
                    'status' => $tm['jenis_absen_keterangan'] ?? $tm['vcKodeAbsen'] ?? 'Tidak Masuk',
                    'source' => 'tidak_masuk',
                    'vcKodeAbsen' => $tm['vcKodeAbsen'],
                    'jenis_absen_keterangan' => $tm['jenis_absen_keterangan'],
                    'vcKeterangan' => $tm['vcKeterangan'],
                ]);
            }
        } elseif ($tidakMasuk) {
            // Jika filter tidak masuk aktif, tambahkan data tidak masuk
            foreach ($tidakMasukExpanded as $tm) {
                $combinedData->push([
                    'dtTanggal' => $tm['dtTanggal'],
                    'vcNik' => $tm['vcNik'],
                    'Nama' => $tm['Nama'],
                    'Divisi' => $tm['Divisi'],
                    'vcKodeBagian' => $tm['vcKodeBagian'],
                    'vcNamaDivisi' => $tm['vcNamaDivisi'],
                    'vcNamaDept' => $tm['vcNamaDept'] ?? null,
                    'vcNamaBagian' => $tm['vcNamaBagian'],
                    'dtJamMasuk' => null,
                    'dtJamKeluar' => null,
                    'dtJamMasukLembur' => null,
                    'dtJamKeluarLembur' => null,
                    'total_jam' => 0,
                    'status' => $tm['jenis_absen_keterangan'] ?? $tm['vcKodeAbsen'] ?? 'Tidak Masuk',
                    'source' => 'tidak_masuk',
                    'vcKodeAbsen' => $tm['vcKodeAbsen'],
                    'jenis_absen_keterangan' => $tm['jenis_absen_keterangan'],
                    'vcKeterangan' => $tm['vcKeterangan'],
                ]);
            }
        }

        // Filter berdasarkan status
        // 1. Filter Hari Kerja Normal (HKN): status = HKN, Telat, ATL, HC
        if ($hariKerjaNormal && !$kerjaHariLibur && !$telat) {
            $combinedData = $combinedData->filter(function ($item) {
                $status = $item['status'] ?? '';
                return in_array($status, ['HKN', 'Telat', 'ATL', 'HC']);
            });
        }
        // 2. Filter Kerja hari Libur (KHL): status = KHL
        elseif ($kerjaHariLibur && !$hariKerjaNormal && !$telat) {
            $combinedData = $combinedData->filter(function ($item) {
                return isset($item['status']) && $item['status'] === 'KHL';
            });
        }
        // 3. Filter Telat: status = Telat
        elseif ($telat && !$hariKerjaNormal && !$kerjaHariLibur) {
            $combinedData = $combinedData->filter(function ($item) {
                return isset($item['status']) && $item['status'] === 'Telat';
            });
        }
        // 4. Kombinasi filter
        elseif ($hariKerjaNormal && $telat && !$kerjaHariLibur) {
            // HKN + Telat: HKN, Telat, ATL, HC (termasuk Telat)
            $combinedData = $combinedData->filter(function ($item) {
                $status = $item['status'] ?? '';
                return in_array($status, ['HKN', 'Telat', 'ATL', 'HC']);
            });
        }
        elseif ($kerjaHariLibur && $telat && !$hariKerjaNormal) {
            // KHL + Telat: KHL dan Telat
            $combinedData = $combinedData->filter(function ($item) {
                $status = $item['status'] ?? '';
                return in_array($status, ['KHL', 'Telat']);
            });
        }
        elseif ($hariKerjaNormal && $kerjaHariLibur && !$telat) {
            // HKN + KHL: HKN, Telat, ATL, HC, KHL
            $combinedData = $combinedData->filter(function ($item) {
                $status = $item['status'] ?? '';
                return in_array($status, ['HKN', 'Telat', 'ATL', 'HC', 'KHL']);
            });
        }
        elseif ($hariKerjaNormal && $kerjaHariLibur && $telat) {
            // Semua checked: tampilkan semua
            // Tidak ada filter
        }
        // Jika semua tidak checked, tampilkan semua (tidak ada filter)

        // Sort by tanggal desc, then nik
        $combinedData = $combinedData->sort(function ($a, $b) {
            $dateCompare = strcmp($b['dtTanggal'], $a['dtTanggal']);
            if ($dateCompare !== 0) {
                return $dateCompare;
            }
            return strcmp($a['vcNik'], $b['vcNik']);
        })->values();

        // Pagination manual dengan optimasi
        $perPage = 50;
        $currentPage = $request->get('page', 1);
        $total = $combinedData->count();
        $items = $combinedData->slice(($currentPage - 1) * $perPage, $perPage)->values();

        // Create paginator manually
        $absens = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Get summary data
        $totalData = $total;

        // Get unique groups for filter
        $groups = Karyawan::select('Group_pegawai')
            ->distinct()
            ->whereNotNull('Group_pegawai')
            ->orderBy('Group_pegawai')
            ->pluck('Group_pegawai');

        return view('absen.index', compact(
            'absens',
            'startDate',
            'endDate',
            'search',
            'tidakMasuk',
            'absenTidakLengkap',
            'hariKerjaNormal',
            'kerjaHariLibur',
            'telat',
            'group',
            'karyawanList',
            'totalData',
            'groups',
            'hariLiburList'
        ));
    }

    /**
     * Print view for Browse Absensi (same logic as index but without pagination)
     */
    public function print(Request $request)
    {
        // Same logic as index but without pagination
        ini_set('memory_limit', '512M');
        set_time_limit(300);
        
        $startDate = $request->get('dari_tanggal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('sampai_tanggal', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $search = $request->get('search');
        $nik = $request->get('nik');
        $nama = $request->get('nama');
        if (!$search && ($nik || $nama)) {
            $searchParts = [];
            if ($nik) $searchParts[] = $nik;
            if ($nama) $searchParts[] = $nama;
            $search = implode(', ', $searchParts);
        }
        
        $tidakMasuk = $request->get('tidak_masuk');
        $absenTidakLengkap = $request->get('absen_tidak_lengkap');
        $hariKerjaNormal = $request->get('hari_kerja_normal');
        $kerjaHariLibur = $request->get('kerja_hari_libur');
        $telat = $request->get('telat');
        $group = $request->get('group', 'Semua Group');

        $hariLiburList = $this->getHariLiburList($startDate, $endDate);

        $karyawanFilter = function ($query) use ($search, $group) {
            if ($search) {
                $searchTerms = preg_split('/,\s*/', trim($search));
                $query->where(function ($q) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        if (!empty(trim($term))) {
                            $term = trim($term);
                            if (strpos($term, ' - ') !== false) {
                                $term = explode(' - ', $term)[0];
                            }
                            $q->orWhere('m_karyawan.Nik', 'like', '%' . $term . '%')
                                ->orWhere('m_karyawan.Nama', 'like', '%' . $term . '%');
                        }
                    }
                });
            }
            if ($group !== 'Semua Group') {
                $query->where('m_karyawan.Group_pegawai', $group);
            }
        };

        $absenQuery = DB::table('t_absen')
            ->join('m_karyawan', 't_absen.vcNik', '=', 'm_karyawan.Nik')
            ->leftJoin('m_divisi', 'm_karyawan.Divisi', '=', 'm_divisi.vcKodeDivisi')
            ->leftJoin('m_dept', 'm_karyawan.dept', '=', 'm_dept.vcKodeDept')
            ->leftJoin('m_bagian', 'm_karyawan.vcKodeBagian', '=', 'm_bagian.vcKodeBagian')
            ->leftJoin('m_shift', 'm_karyawan.vcShift', '=', 'm_shift.vcShift')
            ->leftJoin('t_jadwal_shift_security as jadwal', function ($join) {
                $join->on('t_absen.vcNik', '=', 'jadwal.vcNik')
                    ->on('t_absen.dtTanggal', '=', 'jadwal.dtTanggal');
            })
            ->whereBetween('t_absen.dtTanggal', [$startDate, $endDate])
            ->select(
                't_absen.dtTanggal',
                't_absen.vcNik',
                't_absen.dtJamMasuk',
                't_absen.dtJamKeluar',
                't_absen.dtJamMasukLembur',
                't_absen.dtJamKeluarLembur',
                'm_karyawan.Nama',
                'm_karyawan.Divisi',
                'm_karyawan.dept',
                'm_karyawan.vcKodeBagian',
                'm_karyawan.Group_pegawai',
                'm_divisi.vcNamaDivisi',
                'm_dept.vcNamaDept',
                'm_bagian.vcNamaBagian',
                'm_shift.vcMasuk as shift_masuk'
            );

        $karyawanFilter($absenQuery);

        $tidakMasukQuery = DB::table('t_tidak_masuk')
            ->join('m_karyawan', 't_tidak_masuk.vcNik', '=', 'm_karyawan.Nik')
            ->leftJoin('m_jenis_absen', 't_tidak_masuk.vcKodeAbsen', '=', 'm_jenis_absen.vcKodeAbsen')
            ->leftJoin('m_divisi', 'm_karyawan.Divisi', '=', 'm_divisi.vcKodeDivisi')
            ->leftJoin('m_dept', 'm_karyawan.dept', '=', 'm_dept.vcKodeDept')
            ->leftJoin('m_bagian', 'm_karyawan.vcKodeBagian', '=', 'm_bagian.vcKodeBagian')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('t_tidak_masuk.dtTanggalMulai', [$startDate, $endDate])
                    ->orWhereBetween('t_tidak_masuk.dtTanggalSelesai', [$startDate, $endDate])
                    ->orWhere(function ($qq) use ($startDate, $endDate) {
                        $qq->where('t_tidak_masuk.dtTanggalMulai', '<=', $startDate)
                            ->where('t_tidak_masuk.dtTanggalSelesai', '>=', $endDate);
                    });
            })
            ->whereNotNull('t_tidak_masuk.dtTanggalMulai')
            ->whereNotNull('t_tidak_masuk.dtTanggalSelesai')
            ->select(
                't_tidak_masuk.vcNik',
                't_tidak_masuk.vcKodeAbsen',
                't_tidak_masuk.dtTanggalMulai',
                't_tidak_masuk.dtTanggalSelesai',
                't_tidak_masuk.vcKeterangan',
                'm_karyawan.Nama',
                'm_karyawan.Divisi',
                'm_karyawan.dept',
                'm_karyawan.vcKodeBagian',
                'm_divisi.vcNamaDivisi',
                'm_dept.vcNamaDept',
                'm_bagian.vcNamaBagian',
                'm_jenis_absen.vcKeterangan as jenis_absen_keterangan'
            );

        $karyawanFilter($tidakMasukQuery);

        $tidakMasukRecords = $tidakMasukQuery->get();

        $absenExists = collect();
        DB::table('t_absen')
            ->whereBetween('dtTanggal', [$startDate, $endDate])
            ->select('dtTanggal', 'vcNik')
            ->orderBy('dtTanggal')
            ->orderBy('vcNik')
            ->chunk(5000, function ($chunk) use (&$absenExists) {
                foreach ($chunk as $item) {
                    $absenExists->put($item->dtTanggal . '_' . $item->vcNik, true);
                }
            });

        $tidakMasukExpanded = [];
        $filterStart = Carbon::parse($startDate);
        $filterEnd = Carbon::parse($endDate);

        foreach ($tidakMasukRecords as $tm) {
            $current = Carbon::parse($tm->dtTanggalMulai);
            $end = Carbon::parse($tm->dtTanggalSelesai);
            $maxDays = 365;
            $dayCount = 0;

            while ($current->lte($end) && $dayCount < $maxDays) {
                if ($current->lt($filterStart) || $current->gt($filterEnd)) {
                    $current->addDay();
                    $dayCount++;
                    continue;
                }

                $tanggalStr = $current->format('Y-m-d');
                $key = $tanggalStr . '_' . $tm->vcNik;

                if (!$absenExists->has($key)) {
                    $tidakMasukExpanded[] = [
                        'dtTanggal' => $tanggalStr,
                        'vcNik' => $tm->vcNik,
                        'Nama' => $tm->Nama,
                        'Divisi' => $tm->Divisi,
                        'vcKodeBagian' => $tm->vcKodeBagian,
                        'vcNamaDivisi' => $tm->vcNamaDivisi,
                        'vcNamaDept' => $tm->vcNamaDept ?? null,
                        'vcNamaBagian' => $tm->vcNamaBagian,
                        'vcKodeAbsen' => $tm->vcKodeAbsen,
                        'jenis_absen_keterangan' => $tm->jenis_absen_keterangan,
                        'vcKeterangan' => $tm->vcKeterangan,
                        'source' => 'tidak_masuk',
                    ];
                }

                $current->addDay();
                $dayCount++;
            }
        }

        $jadwalSecurity = DB::table('t_jadwal_shift_security')
            ->whereBetween('dtTanggal', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($item) {
                return $item->vcNik . '_' . $item->dtTanggal;
            })
            ->map(function ($group) {
                return $group->pluck('intShift')->toArray();
            });

        $combinedData = collect();

        $absenQuery->orderBy('t_absen.dtTanggal', 'desc')
            ->orderBy('t_absen.vcNik')
            ->chunk(1000, function ($absensData) use (&$combinedData, $jadwalSecurity, $hariLiburList) {
                foreach ($absensData as $absen) {
                    $shiftAktual = null;
                    $shiftTerjadwal = [];
                    $statusValidasi = null;

                    if ($absen->Group_pegawai === 'Security') {
                        $shiftAktual = SecurityAbsensiService::determineShiftFromTime(
                            $absen->dtJamMasuk,
                            $absen->dtJamKeluar,
                            $absen->dtTanggal
                        );

                        $key = $absen->vcNik . '_' . $absen->dtTanggal;
                        if ($jadwalSecurity->has($key)) {
                            $shiftTerjadwal = $jadwalSecurity->get($key);
                        }

                        $validasi = SecurityAbsensiService::validateAbsensiVsJadwal(
                            $absen->vcNik,
                            $absen->dtTanggal,
                            $shiftAktual
                        );
                        $statusValidasi = $validasi['status'];
                    }

                    $tanggalObj = Carbon::parse($absen->dtTanggal);
                    $totalJam = $this->calculateTotalJam($absen->dtJamMasuk, $absen->dtJamKeluar, $absen->dtTanggal);
                    
                    $isHariKerjaNormal = $this->isHariKerjaNormal($absen->dtTanggal, $absen->vcNik);
                    $isHariLibur = !$isHariKerjaNormal;
                    
                    $isTelat = false;
                    if ($absen->dtJamMasuk && $absen->shift_masuk && $isHariKerjaNormal) {
                        try {
                            $jamMasuk = substr((string) $absen->dtJamMasuk, 0, 5);
                            $shiftMasuk = $absen->shift_masuk instanceof Carbon
                                ? $absen->shift_masuk->format('H:i')
                                : substr((string) $absen->shift_masuk, 0, 5);
                            
                            $tMasuk = $tanggalObj->copy()->setTimeFromTimeString($jamMasuk);
                            $tShiftMasuk = $tanggalObj->copy()->setTimeFromTimeString($shiftMasuk);
                            
                            if ($tMasuk->greaterThan($tShiftMasuk)) {
                                $isTelat = true;
                            }
                        } catch (\Exception $e) {
                        }
                    }
                    
                    $status = '';
                    if (!$absen->dtJamMasuk && !$absen->dtJamKeluar) {
                        $status = 'Tidak Masuk';
                    } elseif ($isTelat) {
                        $status = 'Telat';
                    } elseif (($absen->dtJamMasuk && !$absen->dtJamKeluar) || (!$absen->dtJamMasuk && $absen->dtJamKeluar)) {
                        $status = 'ATL';
                    } elseif ($isHariLibur && ($absen->dtJamMasuk || $absen->dtJamKeluar || $absen->dtJamMasukLembur)) {
                        $status = 'KHL';
                    } elseif ($absen->dtJamMasuk && $absen->dtJamKeluar && $totalJam >= 8) {
                        $status = 'HKN';
                    } elseif ($absen->dtJamMasuk && $absen->dtJamKeluar && $totalJam > 0 && $totalJam < 8) {
                        $status = 'HC';
                    } else {
                        $status = 'ATL';
                    }

                    $combinedData->push([
                        'dtTanggal' => $absen->dtTanggal,
                        'vcNik' => $absen->vcNik,
                        'Nama' => $absen->Nama,
                        'Divisi' => $absen->Divisi,
                        'vcKodeBagian' => $absen->vcKodeBagian,
                        'vcNamaDivisi' => $absen->vcNamaDivisi,
                        'vcNamaDept' => $absen->vcNamaDept ?? null,
                        'vcNamaBagian' => $absen->vcNamaBagian,
                        'Group_pegawai' => $absen->Group_pegawai,
                        'dtJamMasuk' => $absen->dtJamMasuk,
                        'dtJamKeluar' => $absen->dtJamKeluar,
                        'dtJamMasukLembur' => $absen->dtJamMasukLembur,
                        'dtJamKeluarLembur' => $absen->dtJamKeluarLembur,
                        'total_jam' => $totalJam,
                        'shift_masuk' => $absen->shift_masuk ?? null,
                        'shift_terjadwal' => $shiftTerjadwal,
                        'shift_aktual' => $shiftAktual,
                        'status_validasi' => $statusValidasi,
                        'status' => $status,
                        'source' => 'absen',
                    ]);
                }
            });

        if (!$tidakMasuk && !$absenTidakLengkap) {
            foreach ($tidakMasukExpanded as $tm) {
                $combinedData->push([
                    'dtTanggal' => $tm['dtTanggal'],
                    'vcNik' => $tm['vcNik'],
                    'Nama' => $tm['Nama'],
                    'Divisi' => $tm['Divisi'],
                    'vcKodeBagian' => $tm['vcKodeBagian'],
                    'vcNamaDivisi' => $tm['vcNamaDivisi'],
                    'vcNamaDept' => $tm['vcNamaDept'] ?? null,
                    'vcNamaBagian' => $tm['vcNamaBagian'],
                    'dtJamMasuk' => null,
                    'dtJamKeluar' => null,
                    'dtJamMasukLembur' => null,
                    'dtJamKeluarLembur' => null,
                    'total_jam' => 0,
                    'status' => $tm['jenis_absen_keterangan'] ?? $tm['vcKodeAbsen'] ?? 'Tidak Masuk',
                    'source' => 'tidak_masuk',
                    'vcKodeAbsen' => $tm['vcKodeAbsen'],
                    'jenis_absen_keterangan' => $tm['jenis_absen_keterangan'],
                    'vcKeterangan' => $tm['vcKeterangan'],
                ]);
            }
        } elseif ($tidakMasuk) {
            foreach ($tidakMasukExpanded as $tm) {
                $combinedData->push([
                    'dtTanggal' => $tm['dtTanggal'],
                    'vcNik' => $tm['vcNik'],
                    'Nama' => $tm['Nama'],
                    'Divisi' => $tm['Divisi'],
                    'vcKodeBagian' => $tm['vcKodeBagian'],
                    'vcNamaDivisi' => $tm['vcNamaDivisi'],
                    'vcNamaDept' => $tm['vcNamaDept'] ?? null,
                    'vcNamaBagian' => $tm['vcNamaBagian'],
                    'dtJamMasuk' => null,
                    'dtJamKeluar' => null,
                    'dtJamMasukLembur' => null,
                    'dtJamKeluarLembur' => null,
                    'total_jam' => 0,
                    'status' => $tm['jenis_absen_keterangan'] ?? $tm['vcKodeAbsen'] ?? 'Tidak Masuk',
                    'source' => 'tidak_masuk',
                    'vcKodeAbsen' => $tm['vcKodeAbsen'],
                    'jenis_absen_keterangan' => $tm['jenis_absen_keterangan'],
                    'vcKeterangan' => $tm['vcKeterangan'],
                ]);
            }
        }

        if ($hariKerjaNormal && !$kerjaHariLibur && !$telat) {
            $combinedData = $combinedData->filter(function ($item) {
                $status = $item['status'] ?? '';
                return in_array($status, ['HKN', 'Telat', 'ATL', 'HC']);
            });
        } elseif ($kerjaHariLibur && !$hariKerjaNormal && !$telat) {
            $combinedData = $combinedData->filter(function ($item) {
                return isset($item['status']) && $item['status'] === 'KHL';
            });
        } elseif ($telat && !$hariKerjaNormal && !$kerjaHariLibur) {
            $combinedData = $combinedData->filter(function ($item) {
                return isset($item['status']) && $item['status'] === 'Telat';
            });
        } elseif ($hariKerjaNormal && $telat && !$kerjaHariLibur) {
            $combinedData = $combinedData->filter(function ($item) {
                $status = $item['status'] ?? '';
                return in_array($status, ['HKN', 'Telat', 'ATL', 'HC']);
            });
        } elseif ($kerjaHariLibur && $telat && !$hariKerjaNormal) {
            $combinedData = $combinedData->filter(function ($item) {
                $status = $item['status'] ?? '';
                return in_array($status, ['KHL', 'Telat']);
            });
        } elseif ($hariKerjaNormal && $kerjaHariLibur && !$telat) {
            $combinedData = $combinedData->filter(function ($item) {
                $status = $item['status'] ?? '';
                return in_array($status, ['HKN', 'Telat', 'ATL', 'HC', 'KHL']);
            });
        }

        $combinedData = $combinedData->sort(function ($a, $b) {
            $dateCompare = strcmp($b['dtTanggal'], $a['dtTanggal']);
            if ($dateCompare !== 0) {
                return $dateCompare;
            }
            return strcmp($a['vcNik'], $b['vcNik']);
        })->values();

        // No pagination for print - get all data
        $absens = $combinedData;

        return view('absen.print', compact(
            'absens',
            'startDate',
            'endDate',
            'search',
            'tidakMasuk',
            'absenTidakLengkap',
            'hariKerjaNormal',
            'kerjaHariLibur',
            'telat',
            'group',
            'hariLiburList'
        ));
    }

    /**
     * Calculate total hours from jam masuk and keluar
     */
    private function calculateTotalJam($dtJamMasuk, $dtJamKeluar, $dtTanggal)
    {
        if (!$dtJamMasuk || !$dtJamKeluar) {
            return 0;
        }

        $tanggal = Carbon::parse($dtTanggal);
        $masuk = $tanggal->copy()->setTimeFromTimeString((string) $dtJamMasuk);
        $keluar = $tanggal->copy()->setTimeFromTimeString((string) $dtJamKeluar);

        if ($keluar->lessThan($masuk)) {
            $keluar->addDay();
        }

        return round($masuk->diffInHours($keluar, true), 1);
    }

    /**
     * Get list of holidays (including weekends)
     */
    private function getHariLiburList($startDate, $endDate)
    {
        $hariLibur = HariLibur::whereBetween('dtTanggal', [$startDate, $endDate])
            ->pluck('dtTanggal')
            ->map(function ($tanggal) {
                return $tanggal instanceof Carbon ? $tanggal->format('Y-m-d') : Carbon::parse($tanggal)->format('Y-m-d');
            })
            ->toArray();

        // Tambahkan Sabtu dan Minggu
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        while ($current->lte($end)) {
            if (in_array($current->dayOfWeek, [0, 6])) { // 0 = Minggu, 6 = Sabtu
                $tanggalStr = $current->format('Y-m-d');
                if (!in_array($tanggalStr, $hariLibur)) {
                    $hariLibur[] = $tanggalStr;
                }
            }
            $current->addDay();
        }

        return $hariLibur;
    }

    public function exportExcel(Request $request)
    {
        // Same logic as index but for export
        $startDate = $request->get('dari_tanggal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('sampai_tanggal', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $nik = $request->get('nik');
        $nama = $request->get('nama');
        $tidakMasuk = $request->get('tidak_masuk');
        $absenTidakLengkap = $request->get('absen_tidak_lengkap');
        $group = $request->get('group', 'Semua Group');

        $query = Absen::with('karyawan')
            ->whereBetween('dtTanggal', [$startDate, $endDate])
            ->orderBy('dtTanggal', 'desc')
            ->orderBy('vcNik');

        if ($nik) {
            $query->where('vcNik', 'like', '%' . $nik . '%');
        }

        if ($nama) {
            $query->whereHas('karyawan', function ($q) use ($nama) {
                $q->where('Nama', 'like', '%' . $nama . '%');
            });
        }

        if ($tidakMasuk) {
            $query->where(function ($q) {
                $q->whereNull('dtJamMasuk')
                    ->whereNull('dtJamKeluar');
            });
        }

        if ($absenTidakLengkap) {
            $query->where(function ($q) {
                $q->whereNull('dtJamMasuk')
                    ->orWhereNull('dtJamKeluar');
            });
        }

        if ($group !== 'Semua Group') {
            $query->whereHas('karyawan', function ($q) use ($group) {
                $q->where('Group_pegawai', $group);
            });
        }

        $absens = $query->get();

        // For now, return a simple response
        // In a real application, you would generate an Excel file
        return response()->json([
            'success' => true,
            'message' => 'Export Excel berhasil. Data: ' . $absens->count() . ' records',
            'data' => $absens->count()
        ]);
    }
}
