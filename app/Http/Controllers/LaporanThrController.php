<?php

namespace App\Http\Controllers;

use App\Models\ClosingThr;
use App\Models\Karyawan;
use App\Models\Divisi;
use App\Models\Departemen;
use App\Models\Bagian;
use App\Models\PeriodeThr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanThrController extends Controller
{
    /**
     * Display filter form for THR report
     */
    public function index(Request $request)
    {
        // Get distinct years from t_closing_thr
        $years = ClosingThr::selectRaw('YEAR(dtTanggalTHR) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->map(function ($year) {
                return (string) $year;
            })
            ->toArray();

        // Get distinct agama from t_closing_thr
        $agamas = ClosingThr::select('vcAgama')
            ->distinct()
            ->orderBy('vcAgama')
            ->pluck('vcAgama')
            ->filter()
            ->toArray();

        // Get divisi list
        $divisis = Divisi::orderBy('vcKodeDivisi')->get();

        // Default values
        $tahun = $request->get('tahun', count($years) > 0 ? $years[0] : date('Y'));
        $divisi = $request->get('divisi', 'SEMUA');
        $agama = $request->get('agama', 'Semua Agama');

        return view('laporan.laporan-thr.index', compact(
            'years',
            'agamas',
            'divisis',
            'tahun',
            'divisi',
            'agama'
        ));
    }

    /**
     * Preview THR report with grouping
     */
    public function preview(Request $request)
    {
        $request->validate([
            'tahun' => 'required|string|digits:4',
            'divisi' => 'nullable|string',
            'agama' => 'nullable|string',
        ]);

        $tahun = $request->tahun;
        $kodeDivisi = $request->divisi;
        $agamaFilter = $request->agama;

        // Query closing THR untuk Operator dan Security dengan vcAktif = 1 (selalu aktif)
        $query = ClosingThr::with(['karyawan', 'divisi', 'gapok'])
            ->whereYear('dtTanggalTHR', $tahun)
            ->whereIn('vcGroupPegawai', ['Operator', 'Security'])
            ->whereHas('karyawan', function ($q) {
                $q->where('vcAktif', '1')
                  ->whereNull('Tgl_Berhenti');
            });

        // Filter divisi
        if ($kodeDivisi && $kodeDivisi != 'SEMUA') {
            $query->where('vcKodeDivisi', $kodeDivisi);
        }

        // Filter agama
        if ($agamaFilter && $agamaFilter != 'Semua Agama') {
            $query->where('vcAgama', $agamaFilter);
        }

        $closingThrs = $query->orderBy('vcKodeDivisi')
            ->orderBy('vcNik')
            ->get();

        if ($closingThrs->isEmpty()) {
            return redirect()->route('laporan-thr.index')
                ->with('error', 'Tidak ada data THR untuk filter yang dipilih');
        }

        // Ambil tanggal THR dari data pertama
        $tanggalTHR = $closingThrs->first()->dtTanggalTHR;

        // Group data secara hierarkis: Divisi -> Departemen -> Bagian
        $groupedData = $this->groupDataHierarchically($closingThrs);

        // Hitung grand total
        $grandTotal = $this->calculateGrandTotal($closingThrs);

        // Ambil data divisi untuk header
        $divisiData = null;
        if ($kodeDivisi && $kodeDivisi != 'SEMUA') {
            $divisiData = Divisi::where('vcKodeDivisi', $kodeDivisi)->first();
        }
        $namaDivisi = $divisiData ? $divisiData->vcNamaDivisi : '';

        // Ambil agama untuk header
        $namaAgama = $agamaFilter && $agamaFilter != 'Semua Agama' ? $agamaFilter : '';

        // Ambil vcNamaHariRaya dari t_periode_thr
        // dtTanggalTHR di t_closing_thr = dtCutoffTHR di t_periode_thr
        $namaHariRaya = '';
        
        if ($closingThrs->isNotEmpty()) {
            $closingThrFirst = $closingThrs->first();
            $dtTanggalTHR = $closingThrFirst->dtTanggalTHR;
            
            if ($dtTanggalTHR) {
                $tanggalTHRFormatted = $dtTanggalTHR->format('Y-m-d');
                
                // Coba query dengan berbagai kombinasi filter untuk memastikan menemukan data
                $periodeThr = null;
                
                // 1. Coba dengan filter lengkap: dtCutoffTHR + divisi + kategori (jika ada)
                if ($kodeDivisi && $kodeDivisi != 'SEMUA' && $agamaFilter && $agamaFilter != 'Semua Agama') {
                    $kategori = $this->mapAgamaToKategori($agamaFilter);
                    $periodeThr = PeriodeThr::where('dtCutoffTHR', $tanggalTHRFormatted)
                        ->where('vcKodeDivisi', $kodeDivisi)
                        ->where('dtKategori', $kategori)
                        ->first();
                }
                
                // 2. Jika tidak ketemu, coba dengan dtCutoffTHR + divisi saja
                if (!$periodeThr) {
                    $divisiFilter = ($kodeDivisi && $kodeDivisi != 'SEMUA') 
                        ? $kodeDivisi 
                        : $closingThrFirst->vcKodeDivisi;
                    
                    $periodeThr = PeriodeThr::where('dtCutoffTHR', $tanggalTHRFormatted)
                        ->where('vcKodeDivisi', $divisiFilter)
                        ->first();
                }
                
                // 3. Jika masih tidak ketemu, coba dengan dtCutoffTHR + kategori saja (jika ada filter agama)
                if (!$periodeThr && $agamaFilter && $agamaFilter != 'Semua Agama') {
                    $kategori = $this->mapAgamaToKategori($agamaFilter);
                    $periodeThr = PeriodeThr::where('dtCutoffTHR', $tanggalTHRFormatted)
                        ->where('dtKategori', $kategori)
                        ->first();
                }
                
                // 4. Fallback terakhir: ambil yang pertama berdasarkan dtCutoffTHR saja
                if (!$periodeThr) {
                    $periodeThr = PeriodeThr::where('dtCutoffTHR', $tanggalTHRFormatted)
                        ->first();
                }
                
                // Ambil vcNamaHariRaya jika ada
                if ($periodeThr && !empty($periodeThr->vcNamaHariRaya)) {
                    $namaHariRaya = trim($periodeThr->vcNamaHariRaya);
                }
            }
        }

        // Ambil data pengesahan dari m_divisi
        $namaHrGaManager = '';
        $namaSeniorFinanceManager = '';
        $namaGmBackOffice = '';
        
        // Jika ada filter divisi spesifik, ambil dari divisi tersebut
        if ($kodeDivisi && $kodeDivisi != 'SEMUA' && $divisiData) {
            $namaHrGaManager = $divisiData->vcHrGaManager ?? '';
            $namaSeniorFinanceManager = $divisiData->vcSeniorFinanceManager ?? '';
            $namaGmBackOffice = $divisiData->vcGmBackOffice ?? '';
        } else {
            // Jika filter SEMUA, ambil dari divisi pertama yang ada di data
            if ($closingThrs->isNotEmpty()) {
                $closingThrFirst = $closingThrs->first();
                $kodeDivisiFirst = $closingThrFirst->vcKodeDivisi;
                if ($kodeDivisiFirst) {
                    $divisiFirst = Divisi::where('vcKodeDivisi', $kodeDivisiFirst)->first();
                    if ($divisiFirst) {
                        $namaHrGaManager = $divisiFirst->vcHrGaManager ?? '';
                        $namaSeniorFinanceManager = $divisiFirst->vcSeniorFinanceManager ?? '';
                        $namaGmBackOffice = $divisiFirst->vcGmBackOffice ?? '';
                    }
                }
            }
        }

        return view('laporan.laporan-thr.preview', compact(
            'groupedData',
            'grandTotal',
            'tanggalTHR',
            'tahun',
            'namaDivisi',
            'kodeDivisi',
            'namaAgama',
            'divisiData',
            'namaHariRaya',
            'namaHrGaManager',
            'namaSeniorFinanceManager',
            'namaGmBackOffice'
        ));
    }

    /**
     * Group data secara hierarkis: Divisi -> Departemen -> Bagian -> Karyawan
     */
    private function groupDataHierarchically($closingThrs)
    {
        $grouped = [];

        // Ambil semua divisi yang ada di data
        $divisiKodes = $closingThrs->pluck('vcKodeDivisi')->unique();

        foreach ($divisiKodes as $divisiKode) {
            // Ambil departemen berdasarkan hirarki dari m_hirarki_dept
            $hirarkiDept = DB::table('m_hirarki_dept')
                ->join('m_dept', 'm_hirarki_dept.vcKodeDept', '=', 'm_dept.vcKodeDept')
                ->where('m_hirarki_dept.vcKodeDivisi', $divisiKode)
                ->select('m_hirarki_dept.vcKodeDept', 'm_dept.vcNamaDept')
                ->orderBy('m_dept.vcKodeDept')
                ->get();

            $divisi = Divisi::where('vcKodeDivisi', $divisiKode)->first();
            $grouped[$divisiKode] = [
                'kode' => $divisiKode,
                'nama' => $divisi->vcNamaDivisi ?? $divisiKode,
                'departemens' => [],
            ];

            // Loop melalui departemen berdasarkan hirarki
            foreach ($hirarkiDept as $hirarkiDeptItem) {
                $deptKode = $hirarkiDeptItem->vcKodeDept;

                // Cek apakah ada data karyawan untuk departemen ini
                $hasDataForDept = $closingThrs->filter(function ($thr) use ($divisiKode, $deptKode) {
                    $karyawan = $thr->karyawan;
                    if (!$karyawan) return false;
                    return $thr->vcKodeDivisi == $divisiKode && ($karyawan->dept ?? '') == $deptKode;
                })->count() > 0;

                // Skip departemen yang tidak ada datanya
                if (!$hasDataForDept) {
                    continue;
                }

                // Ambil bagian berdasarkan hirarki dari m_hirarki_bagian
                $hirarkiBagian = DB::table('m_hirarki_bagian')
                    ->join('m_bagian', 'm_hirarki_bagian.vcKodeBagian', '=', 'm_bagian.vcKodeBagian')
                    ->where('m_hirarki_bagian.vcKodeDivisi', $divisiKode)
                    ->where('m_hirarki_bagian.vcKodeDept', $deptKode)
                    ->select('m_hirarki_bagian.vcKodeBagian', 'm_bagian.vcNamaBagian')
                    ->orderBy('m_bagian.vcKodeBagian')
                    ->get();

                $grouped[$divisiKode]['departemens'][$deptKode] = [
                    'kode' => $deptKode,
                    'nama' => $hirarkiDeptItem->vcNamaDept,
                    'bagians' => [],
                ];

                // Loop melalui bagian berdasarkan hirarki
                foreach ($hirarkiBagian as $hirarkiBagianItem) {
                    $bagianKode = $hirarkiBagianItem->vcKodeBagian;

                    // Cari semua closing THR yang sesuai dengan divisi, departemen, dan bagian ini
                    $thrsForBagian = $closingThrs->filter(function ($thr) use ($divisiKode, $deptKode, $bagianKode) {
                        $karyawan = $thr->karyawan;
                        if (!$karyawan) return false;

                        return $thr->vcKodeDivisi == $divisiKode &&
                            ($karyawan->dept ?? '') == $deptKode &&
                            ($karyawan->vcKodeBagian ?? '') == $bagianKode;
                    });

                    if ($thrsForBagian->count() > 0) {
                        // Hitung total per bagian
                        $totalBagian = $this->calculateTotalBagian($thrsForBagian);

                        $grouped[$divisiKode]['departemens'][$deptKode]['bagians'][$bagianKode] = [
                            'kode' => $bagianKode,
                            'nama' => $hirarkiBagianItem->vcNamaBagian,
                            'karyawans' => $thrsForBagian->values()->all(),
                            'total' => $totalBagian,
                        ];
                    }
                }

                // Hitung total per departemen
                $totalDept = $this->calculateTotalDepartemen(
                    $closingThrs->filter(function ($thr) use ($divisiKode, $deptKode) {
                        $karyawan = $thr->karyawan;
                        if (!$karyawan) return false;
                        return $thr->vcKodeDivisi == $divisiKode && ($karyawan->dept ?? '') == $deptKode;
                    })
                );
                $grouped[$divisiKode]['departemens'][$deptKode]['total'] = $totalDept;

                // Jika setelah loop bagian, departemen tidak memiliki bagian yang punya data, hapus departemen
                if (empty($grouped[$divisiKode]['departemens'][$deptKode]['bagians'])) {
                    unset($grouped[$divisiKode]['departemens'][$deptKode]);
                }
            }

            // Handle karyawan yang tidak ada di hirarki (fallback)
            $thrsWithoutHirarki = $closingThrs->filter(function ($thr) use ($divisiKode, $grouped) {
                if ($thr->vcKodeDivisi != $divisiKode) return false;

                $karyawan = $thr->karyawan;
                if (!$karyawan) return false;

                $deptKode = $karyawan->dept ?? 'UNKNOWN';
                $bagianKode = $karyawan->vcKodeBagian ?? 'UNKNOWN';

                // Cek apakah sudah ada di grouped
                if (isset($grouped[$divisiKode]['departemens'][$deptKode]['bagians'][$bagianKode])) {
                    return false;
                }

                return true;
            });

            if ($thrsWithoutHirarki->count() > 0) {
                foreach ($thrsWithoutHirarki as $thr) {
                    $karyawan = $thr->karyawan;
                    if (!$karyawan) continue;

                    $deptKode = $karyawan->dept ?? 'UNKNOWN';
                    $bagianKode = $karyawan->vcKodeBagian ?? 'UNKNOWN';

                    // Ambil nama departemen dan bagian
                    $dept = Departemen::where('vcKodeDept', $deptKode)->first();
                    $bagian = Bagian::where('vcKodeBagian', $bagianKode)->first();

                    if (!isset($grouped[$divisiKode]['departemens'][$deptKode])) {
                        $grouped[$divisiKode]['departemens'][$deptKode] = [
                            'kode' => $deptKode,
                            'nama' => $dept ? $dept->vcNamaDept : $deptKode,
                            'bagians' => [],
                            'total' => ['gaji_pokok' => 0, 'nilai_thr' => 0],
                        ];
                    }

                    if (!isset($grouped[$divisiKode]['departemens'][$deptKode]['bagians'][$bagianKode])) {
                        $grouped[$divisiKode]['departemens'][$deptKode]['bagians'][$bagianKode] = [
                            'kode' => $bagianKode,
                            'nama' => $bagian ? $bagian->vcNamaBagian : $bagianKode,
                            'karyawans' => [],
                            'total' => ['gaji_pokok' => 0, 'nilai_thr' => 0],
                        ];
                    }

                    $grouped[$divisiKode]['departemens'][$deptKode]['bagians'][$bagianKode]['karyawans'][] = $thr;
                }

                // Recalculate totals untuk bagian dan departemen yang baru ditambahkan
                foreach ($grouped[$divisiKode]['departemens'] as $deptKode => &$deptData) {
                    foreach ($deptData['bagians'] as $bagianKode => &$bagianData) {
                        $bagianData['total'] = $this->calculateTotalBagian(collect($bagianData['karyawans']));
                    }
                    $deptData['total'] = $this->calculateTotalDepartemen(
                        collect($deptData['bagians'])->flatMap(function ($bagian) {
                            return $bagian['karyawans'];
                        })
                    );
                }
            }
        }

        return $grouped;
    }

    /**
     * Calculate total per bagian
     */
    private function calculateTotalBagian($thrs)
    {
        $totalGajiPokok = 0;
        $totalNilaiTHR = 0;

        foreach ($thrs as $thr) {
            if ($thr->decGajiPokok !== null) {
                $totalGajiPokok += $thr->decGajiPokok;
            }
            if ($thr->decNilaiTHR !== null) {
                $totalNilaiTHR += $thr->decNilaiTHR;
            }
        }

        return [
            'gaji_pokok' => $totalGajiPokok,
            'nilai_thr' => $totalNilaiTHR,
        ];
    }

    /**
     * Calculate total per departemen
     */
    private function calculateTotalDepartemen($thrs)
    {
        return $this->calculateTotalBagian($thrs);
    }

    /**
     * Calculate grand total
     */
    private function calculateGrandTotal($closingThrs)
    {
        $totalGajiPokok = 0;
        $totalNilaiTHR = 0;

        foreach ($closingThrs as $thr) {
            if ($thr->decGajiPokok !== null) {
                $totalGajiPokok += $thr->decGajiPokok;
            }
            if ($thr->decNilaiTHR !== null) {
                $totalNilaiTHR += $thr->decNilaiTHR;
            }
        }

        return [
            'gaji_pokok' => $totalGajiPokok,
            'nilai_thr' => $totalNilaiTHR,
        ];
    }

    /**
     * Mapping agama ke kategori THR (sama seperti di ClosingThrController)
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
     * Display filter form for THR Staff report
     */
    public function indexStaff(Request $request)
    {
        // Get distinct years from t_closing_thr
        $years = ClosingThr::selectRaw('YEAR(dtTanggalTHR) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->map(function ($year) {
                return (string) $year;
            })
            ->toArray();

        // Get distinct agama from t_closing_thr
        $agamas = ClosingThr::select('vcAgama')
            ->distinct()
            ->orderBy('vcAgama')
            ->pluck('vcAgama')
            ->filter()
            ->toArray();

        // Get divisi list
        $divisis = Divisi::orderBy('vcKodeDivisi')->get();

        // Default values
        $tahun = $request->get('tahun', count($years) > 0 ? $years[0] : date('Y'));
        $divisi = $request->get('divisi', 'SEMUA');
        $agama = $request->get('agama', 'Semua Agama');
        $masa = $request->get('masa', 'Semua'); // Lebih dari 1 tahun, Kurang dari 1 tahun, Semua

        return view('laporan.laporan-thr-staff.index', compact(
            'years',
            'agamas',
            'divisis',
            'tahun',
            'divisi',
            'agama',
            'masa'
        ));
    }

    /**
     * Preview THR Staff report
     */
    public function previewStaff(Request $request)
    {
        $request->validate([
            'tahun' => 'required|string|digits:4',
            'divisi' => 'nullable|string',
            'agama' => 'nullable|string',
            'masa' => 'nullable|string|in:Lebih dari 1 tahun,Kurang dari 1 tahun,Semua',
        ]);

        $tahun = $request->tahun;
        $kodeDivisi = $request->divisi;
        $agamaFilter = $request->agama;
        $masaFilter = $request->masa ?? 'Semua';

        // Query closing THR untuk Staff dan Management dengan vcAktif = 1
        $query = ClosingThr::with(['karyawan.bagian', 'divisi'])
            ->whereYear('dtTanggalTHR', $tahun)
            ->whereIn('vcGroupPegawai', ['Staff', 'Management'])
            ->whereHas('karyawan', function ($q) {
                $q->where('vcAktif', '1')
                  ->whereNull('Tgl_Berhenti');
            });

        // Filter divisi
        if ($kodeDivisi && $kodeDivisi != 'SEMUA') {
            $query->where('vcKodeDivisi', $kodeDivisi);
        }

        // Filter agama
        if ($agamaFilter && $agamaFilter != 'Semua Agama') {
            $query->where('vcAgama', $agamaFilter);
        }

        // Filter masa kerja
        if ($masaFilter == 'Lebih dari 1 tahun') {
            $query->where('decMasaKerjaTahun', '>=', 1.0);
        } elseif ($masaFilter == 'Kurang dari 1 tahun') {
            $query->where('decMasaKerjaTahun', '<', 1.0);
        }

        $closingThrs = $query->orderBy('vcKodeDivisi')
            ->orderBy('vcNik')
            ->get();

        if ($closingThrs->isEmpty()) {
            return redirect()->route('laporan-thr-staff.index')
                ->with('error', 'Tidak ada data THR Staff/Management untuk filter yang dipilih');
        }

        // Ambil tanggal THR dari data pertama
        $tanggalTHR = $closingThrs->first()->dtTanggalTHR;

        // Ambil data divisi untuk header
        $divisiData = null;
        if ($kodeDivisi && $kodeDivisi != 'SEMUA') {
            $divisiData = Divisi::where('vcKodeDivisi', $kodeDivisi)->first();
        }
        $namaDivisi = $divisiData ? $divisiData->vcNamaDivisi : '';

        // Ambil agama untuk header
        $namaAgama = $agamaFilter && $agamaFilter != 'Semua Agama' ? $agamaFilter : '';

        // Ambil vcNamaHariRaya dari t_periode_thr
        $namaHariRaya = '';
        if ($closingThrs->isNotEmpty()) {
            $closingThrFirst = $closingThrs->first();
            $dtTanggalTHR = $closingThrFirst->dtTanggalTHR;
            
            if ($dtTanggalTHR) {
                $tanggalTHRFormatted = $dtTanggalTHR->format('Y-m-d');
                $divisiFilter = ($kodeDivisi && $kodeDivisi != 'SEMUA') 
                    ? $kodeDivisi 
                    : $closingThrFirst->vcKodeDivisi;
                
                $periodeThr = PeriodeThr::where('dtCutoffTHR', $tanggalTHRFormatted)
                    ->where('vcKodeDivisi', $divisiFilter)
                    ->first();
                
                if (!$periodeThr && $agamaFilter && $agamaFilter != 'Semua Agama') {
                    $kategori = $this->mapAgamaToKategori($agamaFilter);
                    $periodeThr = PeriodeThr::where('dtCutoffTHR', $tanggalTHRFormatted)
                        ->where('dtKategori', $kategori)
                        ->first();
                }
                
                if (!$periodeThr) {
                    $periodeThr = PeriodeThr::where('dtCutoffTHR', $tanggalTHRFormatted)->first();
                }
                
                if ($periodeThr && !empty($periodeThr->vcNamaHariRaya)) {
                    $namaHariRaya = trim($periodeThr->vcNamaHariRaya);
                }
            }
        }

        // Ambil data pengesahan dari m_divisi
        $namaHrGaManager = '';
        $namaSeniorFinanceManager = '';
        $namaGmBackOffice = '';
        
        if ($kodeDivisi && $kodeDivisi != 'SEMUA' && $divisiData) {
            $namaHrGaManager = $divisiData->vcHrGaManager ?? '';
            $namaSeniorFinanceManager = $divisiData->vcSeniorFinanceManager ?? '';
            $namaGmBackOffice = $divisiData->vcGmBackOffice ?? '';
        } else {
            if ($closingThrs->isNotEmpty()) {
                $closingThrFirst = $closingThrs->first();
                $kodeDivisiFirst = $closingThrFirst->vcKodeDivisi;
                if ($kodeDivisiFirst) {
                    $divisiFirst = Divisi::where('vcKodeDivisi', $kodeDivisiFirst)->first();
                    if ($divisiFirst) {
                        $namaHrGaManager = $divisiFirst->vcHrGaManager ?? '';
                        $namaSeniorFinanceManager = $divisiFirst->vcSeniorFinanceManager ?? '';
                        $namaGmBackOffice = $divisiFirst->vcGmBackOffice ?? '';
                    }
                }
            }
        }

        return view('laporan.laporan-thr-staff.preview', compact(
            'closingThrs',
            'tanggalTHR',
            'tahun',
            'namaDivisi',
            'kodeDivisi',
            'namaAgama',
            'divisiData',
            'namaHariRaya',
            'namaHrGaManager',
            'namaSeniorFinanceManager',
            'namaGmBackOffice',
            'masaFilter'
        ));
    }
}
