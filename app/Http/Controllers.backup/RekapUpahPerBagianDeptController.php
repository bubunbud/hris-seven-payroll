<?php

namespace App\Http\Controllers;

use App\Models\Closing;
use App\Models\Divisi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RekapUpahPerBagianDeptController extends Controller
{
    /**
     * Display form untuk cetak rekap upah per bagian/dept
     */
    public function index()
    {
        $divisis = Divisi::orderBy('vcKodeDivisi')->get();

        // Default: tanggal 1 atau 15 bulan ini (tergantung tanggal hari ini)
        $hariIni = Carbon::now()->day;
        $defaultPeriode = $hariIni <= 15
            ? Carbon::now()->startOfMonth()->format('Y-m-d') // Tanggal 1
            : Carbon::now()->startOfMonth()->addDays(14)->format('Y-m-d'); // Tanggal 15

        return view('laporan.rekap-upah-per-bagian-dept.index', compact('divisis', 'defaultPeriode'));
    }

    /**
     * Preview/Print rekap upah per bagian/dept berdasarkan filter
     */
    public function preview(Request $request)
    {
        $request->validate([
            'periode' => 'required|date',
            'divisi' => 'nullable|string',
        ]);

        $tanggalPeriode = Carbon::parse($request->periode)->format('Y-m-d');
        $kodeDivisi = $request->divisi;

        // Query closing data berdasarkan periode gajian
        $query = Closing::with(['karyawan', 'divisi'])
            ->where('periode', $tanggalPeriode)
            ->whereHas('karyawan', function ($q) {
                $q->where('vcAktif', '1'); // Hanya karyawan aktif
            });

        if ($kodeDivisi && $kodeDivisi != 'SEMUA') {
            $query->where('vcKodeDivisi', $kodeDivisi);
        }

        $closings = $query->orderBy('vcKodeDivisi')
            ->orderBy('vcNik')
            ->orderBy('vcClosingKe')
            ->get();

        if ($closings->isEmpty()) {
            return redirect()->route('rekap-upah-per-bagian-dept.index')
                ->with('error', 'Tidak ada data untuk periode yang dipilih');
        }

        // Ambil tanggal awal dan akhir dari data pertama
        $tanggalAwal = $closings->first()->vcPeriodeAwal;
        $tanggalAkhir = $closings->first()->vcPeriodeAkhir;

        // Group data secara hierarkis: Divisi -> Departemen -> Bagian
        $groupedData = $this->groupDataHierarchically($closings);

        // Hitung grand total
        $grandTotal = $this->calculateGrandTotal($closings);

        // Ambil data divisi untuk header dan tanda tangan
        $divisiData = null;
        if ($kodeDivisi && $kodeDivisi != 'SEMUA') {
            $divisiData = Divisi::where('vcKodeDivisi', $kodeDivisi)->first();
        }
        $namaDivisi = $divisiData ? $divisiData->vcNamaDivisi : '';

        return view('laporan.rekap-upah-per-bagian-dept.preview', compact(
            'groupedData',
            'grandTotal',
            'tanggalAwal',
            'tanggalAkhir',
            'tanggalPeriode',
            'namaDivisi',
            'kodeDivisi',
            'divisiData'
        ));
    }

    /**
     * Group data secara hierarkis: Divisi -> Departemen -> Bagian
     * Hanya menampilkan total per bagian, bukan per karyawan
     */
    private function groupDataHierarchically($closings)
    {
        $grouped = [];

        // Ambil semua divisi yang ada di data
        $divisiKodes = $closings->pluck('vcKodeDivisi')->unique();

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
                $hasDataForDept = $closings->filter(function ($closing) use ($divisiKode, $deptKode) {
                    $karyawan = $closing->karyawan;
                    if (!$karyawan) return false;
                    return $closing->vcKodeDivisi == $divisiKode && ($karyawan->dept ?? '') == $deptKode;
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

                    // Cari semua closing yang sesuai dengan divisi, departemen, dan bagian ini
                    $closingsForBagian = $closings->filter(function ($closing) use ($divisiKode, $deptKode, $bagianKode) {
                        $karyawan = $closing->karyawan;
                        if (!$karyawan) return false;

                        return $closing->vcKodeDivisi == $divisiKode &&
                            ($karyawan->dept ?? '') == $deptKode &&
                            ($karyawan->vcKodeBagian ?? '') == $bagianKode;
                    });

                    if ($closingsForBagian->count() > 0) {
                        $grouped[$divisiKode]['departemens'][$deptKode]['bagians'][$bagianKode] = [
                            'kode' => $bagianKode,
                            'nama' => $hirarkiBagianItem->vcNamaBagian,
                            'karyawans' => $closingsForBagian->values()->all(),
                        ];
                    }
                }

                // Jika setelah loop bagian, departemen tidak memiliki bagian yang punya data, hapus departemen
                if (empty($grouped[$divisiKode]['departemens'][$deptKode]['bagians'])) {
                    unset($grouped[$divisiKode]['departemens'][$deptKode]);
                }
            }

            // Handle karyawan yang tidak ada di hirarki (fallback)
            $closingsWithoutHirarki = $closings->filter(function ($closing) use ($divisiKode, $grouped) {
                if ($closing->vcKodeDivisi != $divisiKode) return false;

                $karyawan = $closing->karyawan;
                if (!$karyawan) return false;

                $deptKode = $karyawan->dept ?? 'UNKNOWN';
                $bagianKode = $karyawan->vcKodeBagian ?? 'UNKNOWN';

                // Cek apakah sudah ada di grouped
                return !isset($grouped[$divisiKode]['departemens'][$deptKode]['bagians'][$bagianKode]);
            });

            foreach ($closingsWithoutHirarki as $closing) {
                $karyawan = $closing->karyawan;
                if (!$karyawan) continue;

                $deptKode = $karyawan->dept ?? 'UNKNOWN';
                $bagianKode = $karyawan->vcKodeBagian ?? 'UNKNOWN';

                // Initialize departemen jika belum ada
                if (!isset($grouped[$divisiKode]['departemens'][$deptKode])) {
                    $departemen = $karyawan->departemen;
                    $grouped[$divisiKode]['departemens'][$deptKode] = [
                        'kode' => $deptKode,
                        'nama' => $departemen->vcNamaDept ?? $deptKode,
                        'bagians' => [],
                    ];
                }

                // Initialize bagian jika belum ada
                if (!isset($grouped[$divisiKode]['departemens'][$deptKode]['bagians'][$bagianKode])) {
                    $bagian = $karyawan->bagian;
                    $grouped[$divisiKode]['departemens'][$deptKode]['bagians'][$bagianKode] = [
                        'kode' => $bagianKode,
                        'nama' => $bagian->vcNamaBagian ?? $bagianKode,
                        'karyawans' => [],
                    ];
                }

                // Add karyawan
                $grouped[$divisiKode]['departemens'][$deptKode]['bagians'][$bagianKode]['karyawans'][] = $closing;
            }
        }

        // Calculate totals for each level
        foreach ($grouped as $divisiKode => &$divisi) {
            foreach ($divisi['departemens'] as $deptKode => &$departemen) {
                foreach ($departemen['bagians'] as $bagianKode => &$bagian) {
                    // Calculate total for bagian
                    $bagian['total'] = $this->calculateTotal($bagian['karyawans']);
                }
                // Calculate total for departemen
                $departemen['total'] = $this->calculateTotal(
                    collect($departemen['bagians'])->flatMap(function ($bagian) {
                        return $bagian['karyawans'];
                    })->all()
                );
            }
            // Calculate total for divisi
            $divisi['total'] = $this->calculateTotal(
                collect($divisi['departemens'])->flatMap(function ($departemen) {
                    return collect($departemen['bagians'])->flatMap(function ($bagian) {
                        return $bagian['karyawans'];
                    });
                })->all()
            );
        }

        return $grouped;
    }

    /**
     * Calculate total for a collection of closings
     */
    private function calculateTotal($closings)
    {
        $total = [
            'premi' => 0,
            'gaji' => 0,
            'tsm' => 0,
            'jam_lembur_jm1' => 0,
            'jam_lembur_jm2' => 0,
            'jam_lembur_jm3' => 0,
            'jam_lembur_jm4' => 0,
            'selisih_upah' => 0,
            'upah_lembur' => 0,
            'pot_bpjs_kes' => 0,
            'pot_bpjs_naker' => 0,
            'pot_bpjs_pensiun' => 0,
            'pot_tdk_hdr_hc' => 0,
            'pot_lain_lain' => 0,
            'total_penerimaan' => 0,
            'general_total' => 0,
        ];

        foreach ($closings as $closing) {
            $total['premi'] += $closing->decPremi ?? 0;
            $total['gaji'] += $closing->decGapok ?? 0;
            $total['tsm'] += 0; // TSM selalu 0
            $total['jam_lembur_jm1'] += $closing->decJamLemburKerja1 ?? 0;
            $total['jam_lembur_jm2'] += ($closing->decJamLemburKerja2 ?? 0) + ($closing->decJamLemburLibur2 ?? 0);
            $total['jam_lembur_jm3'] += ($closing->decJamLemburKerja3 ?? 0) + ($closing->decJamLemburLibur3 ?? 0);
            $total['jam_lembur_jm4'] += 0; // JM4 belum ada di database, default 0
            $total['selisih_upah'] += $closing->decRapel ?? 0;

            $upahLembur = ($closing->decTotallembur1 ?? 0) + ($closing->decTotallembur2 ?? 0) + ($closing->decTotallembur3 ?? 0);
            $total['upah_lembur'] += $upahLembur;

            $total['pot_bpjs_kes'] += $closing->decPotonganBPJSKes ?? 0;
            $total['pot_bpjs_naker'] += $closing->decPotonganBPJSJHT ?? 0;
            $total['pot_bpjs_pensiun'] += $closing->decPotonganBPJSJP ?? 0;
            $total['pot_tdk_hdr_hc'] += ($closing->decPotonganAbsen ?? 0) + ($closing->decPotonganHC ?? 0);
            $total['pot_lain_lain'] += $closing->decPotonganLain ?? 0;

            // General Total = Premi + Gaji + Selisih Upah + Upah Lembur
            $generalTotal = ($closing->decPremi ?? 0) +
                ($closing->decGapok ?? 0) +
                ($closing->decRapel ?? 0) +
                $upahLembur;
            $total['general_total'] += $generalTotal;

            // Total Potongan = BPJS Kes + BPJS Naker + BPJS Pensiun + Tdk HDR/HC + Lain-lain
            $totalPotongan = ($closing->decPotonganBPJSKes ?? 0) +
                ($closing->decPotonganBPJSJHT ?? 0) +
                ($closing->decPotonganBPJSJP ?? 0) +
                (($closing->decPotonganAbsen ?? 0) + ($closing->decPotonganHC ?? 0)) +
                ($closing->decPotonganLain ?? 0);

            // Total Penerimaan = General Total - Total Potongan
            $totalPenerimaan = $generalTotal - $totalPotongan;
            $total['total_penerimaan'] += $totalPenerimaan;
        }

        return $total;
    }

    /**
     * Calculate grand total
     */
    private function calculateGrandTotal($closings)
    {
        return $this->calculateTotal($closings);
    }
}
