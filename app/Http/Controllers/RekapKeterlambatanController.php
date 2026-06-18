<?php

namespace App\Http\Controllers;

use App\Models\Absen;
use App\Models\Karyawan;
use App\Models\Divisi;
use App\Models\Departemen;
use App\Models\Bagian;
use App\Models\Izin;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\HariKerjaHelper;

class RekapKeterlambatanController extends Controller
{
    use HariKerjaHelper;

    /**
     * Rekap Absensi Keterlambatan per karyawan
     * Kolom: NIK, Nama, Divisi, Departemen, Bagian, Jumlah Telat (hari), Jumlah Menit Telat
     * Filter: dari tanggal, sampai tanggal, Divisi, Departemen, Bagian, NIK/Nama (autocomplete)
     */
    public function index(Request $request)
    {
        // Default periode: bulan berjalan
        $startDate = $request->get('dari_tanggal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('sampai_tanggal', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $divisiId = $request->get('divisi');
        $departemenId = $request->get('departemen');
        $bagianId = $request->get('bagian');
        $search = $request->get('search');

        // Load dropdown data
        $divisis = Divisi::orderBy('vcNamaDivisi')->get();
        $departemens = Departemen::orderBy('vcNamaDept')->get();
        $bagians = Bagian::orderBy('vcNamaBagian')->get();

        // Load karyawan aktif untuk autocomplete (konsep seperti Browse Absensi)
        $karyawans = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->with(['divisi', 'bagian', 'departemen'])
            ->orderBy('Nama')
            ->get(['Nik', 'Nama', 'Divisi', 'dept', 'vcKodeBagian']);

        $karyawanList = $karyawans->map(function ($k) {
            $divisiNama = $k->divisi->vcNamaDivisi ?? $k->Divisi ?? '-';
            $bagianNama = $k->bagian->vcNamaBagian ?? $k->vcKodeBagian ?? '-';
            $deptNama = $k->departemen->vcNamaDept ?? $k->dept ?? '-';

            return [
                'nik' => $k->Nik ?: '',
                'nama' => $k->Nama ?: '',
                'divisi' => $divisiNama,
                'departemen' => $deptNama,
                'bagian' => $bagianNama,
                'search' => strtolower(($k->Nik ?: '') . ' ' . ($k->Nama ?: '')),
            ];
        })->values();

        // Filter karyawan scope untuk perhitungan (divisi/departemen/bagian/search)
        $karyawanQuery = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti');

        if ($divisiId) {
            $karyawanQuery->where('Divisi', $divisiId);
        }
        if ($departemenId) {
            $karyawanQuery->where('dept', $departemenId);
        }
        if ($bagianId) {
            $karyawanQuery->where('vcKodeBagian', $bagianId);
        }
        if ($search) {
            $searchTrim = trim($search);
            // Jika format "NIK - Nama", ambil NIK saja untuk pencarian yang lebih akurat
            if (strpos($searchTrim, ' - ') !== false) {
                $searchTrim = explode(' - ', $searchTrim)[0];
            }
            $karyawanQuery->where(function ($q) use ($searchTrim) {
                $q->where('Nik', 'like', '%' . $searchTrim . '%')
                    ->orWhere('Nama', 'like', '%' . $searchTrim . '%');
            });
        }

        $targetKaryawans = $karyawanQuery
            ->with(['divisi', 'departemen', 'bagian', 'shift'])
            ->orderBy('Nama')
            ->get();

        $nikList = $targetKaryawans->pluck('Nik')->toArray();

        $rekapData = [];

        if (!empty($nikList)) {
            // Preload absen untuk semua karyawan dalam scope
            $allAbsen = Absen::with(['karyawan.shift', 'karyawan.divisi', 'karyawan.departemen', 'karyawan.bagian'])
                ->whereIn('vcNik', $nikList)
                ->whereBetween('dtTanggal', [$startDate, $endDate])
                ->whereNotNull('dtJamMasuk')
                ->get()
                ->groupBy('vcNik');

            // Preload izin masuk siang untuk semua karyawan dalam periode
            // Izin masuk siang: vcTipeIzin = 'Masuk Siang'
            // Jika ada izin masuk siang, jam masuk sudah dikoreksi menjadi jam shift, jadi tidak perlu dihitung telat
            $izinMasukSiang = Izin::whereIn('vcNik', $nikList)
                ->whereBetween('dtTanggal', [$startDate, $endDate])
                ->where('vcTipeIzin', 'Masuk Siang')
                ->get()
                ->groupBy(function ($izin) {
                    $tanggal = $izin->dtTanggal instanceof Carbon
                        ? $izin->dtTanggal->format('Y-m-d')
                        : Carbon::parse($izin->dtTanggal)->format('Y-m-d');
                    return $izin->vcNik . '_' . $tanggal;
                });

            foreach ($targetKaryawans as $karyawan) {
                $nik = $karyawan->Nik;
                $absenList = $allAbsen->get($nik, collect());

                $jumlahTelat = 0;
                $menitTelat = 0;
                $detailTelat = []; // Array untuk menyimpan detail tanggal telat

                foreach ($absenList as $ab) {
                    $tanggalStr = $ab->dtTanggal instanceof Carbon
                        ? $ab->dtTanggal->format('Y-m-d')
                        : Carbon::parse($ab->dtTanggal)->format('Y-m-d');

                    // Hanya hitung jika hari kerja normal (mempertimbangkan tukar hari kerja)
                    if (!$this->isHariKerjaNormal($tanggalStr, $nik)) {
                        continue;
                    }

                    // Skip jika ada izin masuk siang di tanggal tersebut
                    // Izin masuk siang sudah mengoreksi jam masuk menjadi jam shift, jadi tidak perlu dihitung telat
                    $keyIzin = $nik . '_' . $tanggalStr;
                    if ($izinMasukSiang->has($keyIzin)) {
                        continue; // Skip perhitungan telat jika ada izin masuk siang
                    }

                    $jamMasuk = $ab->dtJamMasuk ? substr((string) $ab->dtJamMasuk, 0, 5) : null;
                    $shiftMasuk = null;
                    if ($ab->karyawan && $ab->karyawan->shift) {
                        $rawShiftMasuk = $ab->karyawan->shift->vcMasuk;
                        if ($rawShiftMasuk instanceof Carbon) {
                            $shiftMasuk = $rawShiftMasuk->format('H:i');
                        } elseif (!empty($rawShiftMasuk)) {
                            $shiftMasuk = substr((string) $rawShiftMasuk, 0, 5);
                        }
                    }

                    if ($jamMasuk && $shiftMasuk) {
                        $tanggal = $ab->dtTanggal instanceof Carbon ? $ab->dtTanggal->copy() : Carbon::parse($ab->dtTanggal);
                        $tMasuk = $tanggal->copy()->setTimeFromTimeString($jamMasuk);
                        $tShiftMasuk = $tanggal->copy()->setTimeFromTimeString($shiftMasuk);

                        if ($tMasuk->greaterThan($tShiftMasuk)) {
                            // Telat 1 menit sudah dianggap telat
                            $selisih = $tShiftMasuk->diffInMinutes($tMasuk);
                            if ($selisih >= 1) {
                                $jumlahTelat++;
                                $menitTelat += $selisih;

                                // Simpan detail tanggal telat
                                $detailTelat[] = [
                                    'tanggal' => $tanggalStr,
                                    'tanggal_formatted' => Carbon::parse($tanggalStr)->format('d/m/Y'),
                                    'jam_masuk' => $jamMasuk,
                                    'shift_masuk' => $shiftMasuk,
                                    'menit_telat' => $selisih,
                                ];
                            }
                        }
                    }
                }

                if ($jumlahTelat > 0 || $menitTelat > 0) {
                    // Sort detail telat berdasarkan tanggal
                    usort($detailTelat, function ($a, $b) {
                        return strcmp($a['tanggal'], $b['tanggal']);
                    });

                    $rekapData[] = [
                        'nik' => $nik,
                        'nama' => $karyawan->Nama,
                        'divisi' => $karyawan->divisi->vcNamaDivisi ?? $karyawan->Divisi ?? '-',
                        'departemen' => $karyawan->departemen->vcNamaDept ?? $karyawan->dept ?? '-',
                        'bagian' => $karyawan->bagian->vcNamaBagian ?? $karyawan->vcKodeBagian ?? '-',
                        'jumlah_telat' => $jumlahTelat,
                        'menit_telat' => $menitTelat,
                        'detail_telat' => $detailTelat, // Tambahkan detail tanggal telat
                    ];
                }
            }
        }

        // Sort hasil berdasarkan divisi, departemen, bagian, nama
        $rekapData = collect($rekapData)->sortBy([
            ['divisi', 'asc'],
            ['departemen', 'asc'],
            ['bagian', 'asc'],
            ['nama', 'asc'],
        ])->values()->all();

        return view('absen.rekap-keterlambatan.index', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'divisiId' => $divisiId,
            'departemenId' => $departemenId,
            'bagianId' => $bagianId,
            'search' => $search,
            'divisis' => $divisis,
            'departemens' => $departemens,
            'bagians' => $bagians,
            'karyawanList' => $karyawanList,
            'rekapData' => $rekapData,
        ]);
    }

    /**
     * Preview untuk cetak Rekap Absensi Keterlambatan
     */
    public function preview(Request $request)
    {
        // Ambil parameter yang sama dengan index
        $startDate = $request->get('dari_tanggal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('sampai_tanggal', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $divisiId = $request->get('divisi');
        $departemenId = $request->get('departemen');
        $bagianId = $request->get('bagian');
        $search = $request->get('search');

        // Load data untuk filter display
        $divisis = Divisi::orderBy('vcNamaDivisi')->get();
        $departemens = Departemen::orderBy('vcNamaDept')->get();
        $bagians = Bagian::orderBy('vcNamaBagian')->get();

        // Filter karyawan scope untuk perhitungan
        $karyawanQuery = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti');

        if ($divisiId) {
            $karyawanQuery->where('Divisi', $divisiId);
        }
        if ($departemenId) {
            $karyawanQuery->where('dept', $departemenId);
        }
        if ($bagianId) {
            $karyawanQuery->where('vcKodeBagian', $bagianId);
        }
        if ($search) {
            $searchTrim = trim($search);
            if (strpos($searchTrim, ' - ') !== false) {
                $searchTrim = explode(' - ', $searchTrim)[0];
            }
            $karyawanQuery->where(function ($q) use ($searchTrim) {
                $q->where('Nik', 'like', '%' . $searchTrim . '%')
                    ->orWhere('Nama', 'like', '%' . $searchTrim . '%');
            });
        }

        $targetKaryawans = $karyawanQuery
            ->with(['divisi', 'departemen', 'bagian', 'shift'])
            ->orderBy('Nama')
            ->get();

        $nikList = $targetKaryawans->pluck('Nik')->toArray();
        $rekapData = [];

        if (!empty($nikList)) {
            $allAbsen = Absen::with(['karyawan.shift', 'karyawan.divisi', 'karyawan.departemen', 'karyawan.bagian'])
                ->whereIn('vcNik', $nikList)
                ->whereBetween('dtTanggal', [$startDate, $endDate])
                ->whereNotNull('dtJamMasuk')
                ->get()
                ->groupBy('vcNik');

            // Preload izin masuk siang untuk semua karyawan dalam periode
            // Izin masuk siang: vcTipeIzin = 'Masuk Siang'
            // Jika ada izin masuk siang, jam masuk sudah dikoreksi menjadi jam shift, jadi tidak perlu dihitung telat
            $izinMasukSiang = Izin::whereIn('vcNik', $nikList)
                ->whereBetween('dtTanggal', [$startDate, $endDate])
                ->where('vcTipeIzin', 'Masuk Siang')
                ->get()
                ->groupBy(function ($izin) {
                    $tanggal = $izin->dtTanggal instanceof Carbon
                        ? $izin->dtTanggal->format('Y-m-d')
                        : Carbon::parse($izin->dtTanggal)->format('Y-m-d');
                    return $izin->vcNik . '_' . $tanggal;
                });

            foreach ($targetKaryawans as $karyawan) {
                $nik = $karyawan->Nik;
                $absenList = $allAbsen->get($nik, collect());

                $jumlahTelat = 0;
                $menitTelat = 0;
                $detailTelat = [];

                foreach ($absenList as $ab) {
                    $tanggalStr = $ab->dtTanggal instanceof Carbon
                        ? $ab->dtTanggal->format('Y-m-d')
                        : Carbon::parse($ab->dtTanggal)->format('Y-m-d');

                    if (!$this->isHariKerjaNormal($tanggalStr, $nik)) {
                        continue;
                    }

                    // Skip jika ada izin masuk siang di tanggal tersebut
                    // Izin masuk siang sudah mengoreksi jam masuk menjadi jam shift, jadi tidak perlu dihitung telat
                    $keyIzin = $nik . '_' . $tanggalStr;
                    if ($izinMasukSiang->has($keyIzin)) {
                        continue; // Skip perhitungan telat jika ada izin masuk siang
                    }

                    $jamMasuk = $ab->dtJamMasuk ? substr((string) $ab->dtJamMasuk, 0, 5) : null;
                    $shiftMasuk = null;
                    if ($ab->karyawan && $ab->karyawan->shift) {
                        $rawShiftMasuk = $ab->karyawan->shift->vcMasuk;
                        if ($rawShiftMasuk instanceof Carbon) {
                            $shiftMasuk = $rawShiftMasuk->format('H:i');
                        } elseif (!empty($rawShiftMasuk)) {
                            $shiftMasuk = substr((string) $rawShiftMasuk, 0, 5);
                        }
                    }

                    if ($jamMasuk && $shiftMasuk) {
                        $tanggal = $ab->dtTanggal instanceof Carbon ? $ab->dtTanggal->copy() : Carbon::parse($ab->dtTanggal);
                        $tMasuk = $tanggal->copy()->setTimeFromTimeString($jamMasuk);
                        $tShiftMasuk = $tanggal->copy()->setTimeFromTimeString($shiftMasuk);

                        if ($tMasuk->greaterThan($tShiftMasuk)) {
                            $selisih = $tShiftMasuk->diffInMinutes($tMasuk);
                            if ($selisih >= 1) {
                                $jumlahTelat++;
                                $menitTelat += $selisih;

                                $detailTelat[] = [
                                    'tanggal' => $tanggalStr,
                                    'tanggal_formatted' => Carbon::parse($tanggalStr)->format('d/m/Y'),
                                    'jam_masuk' => $jamMasuk,
                                    'shift_masuk' => $shiftMasuk,
                                    'menit_telat' => $selisih,
                                ];
                            }
                        }
                    }
                }

                if ($jumlahTelat > 0 || $menitTelat > 0) {
                    usort($detailTelat, function ($a, $b) {
                        return strcmp($a['tanggal'], $b['tanggal']);
                    });

                    $rekapData[] = [
                        'nik' => $nik,
                        'nama' => $karyawan->Nama,
                        'divisi' => $karyawan->divisi->vcNamaDivisi ?? $karyawan->Divisi ?? '-',
                        'departemen' => $karyawan->departemen->vcNamaDept ?? $karyawan->dept ?? '-',
                        'bagian' => $karyawan->bagian->vcNamaBagian ?? $karyawan->vcKodeBagian ?? '-',
                        'jumlah_telat' => $jumlahTelat,
                        'menit_telat' => $menitTelat,
                        'detail_telat' => $detailTelat,
                    ];
                }
            }
        }

        $rekapData = collect($rekapData)->sortBy([
            ['divisi', 'asc'],
            ['departemen', 'asc'],
            ['bagian', 'asc'],
            ['nama', 'asc'],
        ])->values()->all();

        // Get selected filter names
        $selectedDivisi = $divisis->where('vcKodeDivisi', $divisiId)->first();
        $selectedDepartemen = $departemens->where('vcKodeDept', $departemenId)->first();
        $selectedBagian = $bagians->where('vcKodeBagian', $bagianId)->first();

        return view('absen.rekap-keterlambatan.preview', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'divisiId' => $divisiId,
            'departemenId' => $departemenId,
            'bagianId' => $bagianId,
            'search' => $search,
            'selectedDivisi' => $selectedDivisi,
            'selectedDepartemen' => $selectedDepartemen,
            'selectedBagian' => $selectedBagian,
            'rekapData' => $rekapData,
        ]);
    }

    /**
     * Get Departemen berdasarkan Divisi (hierarki filter)
     * Menggunakan data dari karyawan aktif untuk konsistensi
     */
    public function getDepartemensByDivisi(Request $request)
    {
        $divisiId = $request->get('divisi');

        if (!$divisiId) {
            return response()->json([
                'success' => true,
                'departemens' => []
            ]);
        }

        // Ambil departemen dari karyawan aktif yang ada di divisi tersebut
        $departemens = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->where('Divisi', $divisiId)
            ->whereNotNull('dept')
            ->distinct()
            ->join('m_dept', 'm_karyawan.dept', '=', 'm_dept.vcKodeDept')
            ->select('m_dept.vcKodeDept', 'm_dept.vcNamaDept')
            ->orderBy('m_dept.vcNamaDept')
            ->get();

        return response()->json([
            'success' => true,
            'departemens' => $departemens
        ]);
    }

    /**
     * Get Bagian berdasarkan Departemen (hierarki filter)
     * Menggunakan data dari karyawan aktif untuk konsistensi
     */
    public function getBagiansByDepartemen(Request $request)
    {
        $departemenId = $request->get('departemen');
        $divisiId = $request->get('divisi'); // Optional: untuk filter lebih spesifik

        if (!$departemenId) {
            return response()->json([
                'success' => true,
                'bagians' => []
            ]);
        }

        try {
            // Ambil bagian dari karyawan aktif yang ada di departemen tersebut
            $query = DB::table('m_karyawan')
                ->join('m_bagian', 'm_karyawan.vcKodeBagian', '=', 'm_bagian.vcKodeBagian')
                ->where('m_karyawan.vcAktif', '1')
                ->whereNull('m_karyawan.Tgl_Berhenti')
                ->where('m_karyawan.dept', $departemenId)
                ->whereNotNull('m_karyawan.vcKodeBagian')
                ->select('m_bagian.vcKodeBagian', 'm_bagian.vcNamaBagian')
                ->groupBy('m_bagian.vcKodeBagian', 'm_bagian.vcNamaBagian');

            // Jika divisi juga dipilih, filter berdasarkan divisi juga
            if ($divisiId) {
                $query->where('m_karyawan.Divisi', $divisiId);
            }

            $bagians = $query->orderBy('m_bagian.vcNamaBagian')->get();

            return response()->json([
                'success' => true,
                'bagians' => $bagians
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getBagiansByDepartemen: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading data: ' . $e->getMessage(),
                'bagians' => []
            ], 500);
        }
    }
}
