<?php

namespace App\Http\Controllers;

use App\Models\Closing;
use App\Models\Divisi;
use App\Models\Departemen;
use App\Models\Bagian;
use App\Exports\RekapUpahFinanceVerExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class RekapUpahFinanceVerController extends Controller
{
    /**
     * Display form untuk cetak rekap upah finance version
     */
    public function index()
    {
        $divisis = Divisi::orderBy('vcKodeDivisi')->get();

        // Default: tanggal 1 atau 15 bulan ini (tergantung tanggal hari ini)
        $hariIni = Carbon::now()->day;
        $defaultPeriode = $hariIni <= 15
            ? Carbon::now()->startOfMonth()->format('Y-m-d') // Tanggal 1
            : Carbon::now()->startOfMonth()->addDays(14)->format('Y-m-d'); // Tanggal 15

        return view('laporan.rekap-upah-finance-ver.index', compact('divisis', 'defaultPeriode'));
    }

    /**
     * Preview/Print rekap upah finance version berdasarkan filter
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
        $query = Closing::with(['karyawan', 'gapok', 'divisi', 'karyawan.departemen', 'karyawan.bagian'])
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
            return redirect()->route('rekap-upah-finance-ver.index')
                ->with('error', 'Tidak ada data untuk periode yang dipilih');
        }

        // Ambil tanggal awal dan akhir dari data pertama
        $tanggalAwal = $closings->first()->vcPeriodeAwal;
        $tanggalAkhir = $closings->first()->vcPeriodeAkhir;

        // Group data secara hierarkis: Divisi -> Departemen -> Bagian -> Karyawan
        $groupedData = $this->groupDataHierarchically($closings);

        // Hitung grand total
        $grandTotal = $this->calculateGrandTotal($closings);

        // Ambil data divisi untuk header
        $divisiData = null;
        if ($kodeDivisi && $kodeDivisi != 'SEMUA') {
            $divisiData = Divisi::where('vcKodeDivisi', $kodeDivisi)->first();
        }
        $namaDivisi = $divisiData ? $divisiData->vcNamaDivisi : '';

        return view('laporan.rekap-upah-finance-ver.preview', compact(
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
     * Group data secara hierarkis: Divisi -> Departemen -> Bagian -> Karyawan
     */
    private function groupDataHierarchically($closings)
    {
        $grouped = [];

        // Ambil semua divisi yang ada di data
        $divisiKodes = $closings->pluck('vcKodeDivisi')->unique();

        foreach ($divisiKodes as $divisiKode) {
            $divisi = Divisi::where('vcKodeDivisi', $divisiKode)->first();
            $grouped[$divisiKode] = [
                'kode' => $divisiKode,
                'nama' => $divisi->vcNamaDivisi ?? $divisiKode,
                'departemens' => [],
            ];

            // Ambil semua departemen dari karyawan di divisi ini
            $deptKodes = $closings->filter(function ($closing) use ($divisiKode) {
                return $closing->vcKodeDivisi == $divisiKode;
            })->map(function ($closing) {
                return $closing->karyawan->dept ?? null;
            })->filter()->unique();

            foreach ($deptKodes as $deptKode) {
                $dept = Departemen::where('vcKodeDept', $deptKode)->first();
                $grouped[$divisiKode]['departemens'][$deptKode] = [
                    'kode' => $deptKode,
                    'nama' => $dept->vcNamaDept ?? $deptKode,
                    'bagians' => [],
                ];

                // Ambil semua bagian dari karyawan di departemen ini
                $bagianKodes = $closings->filter(function ($closing) use ($divisiKode, $deptKode) {
                    $karyawan = $closing->karyawan;
                    return $closing->vcKodeDivisi == $divisiKode && 
                           ($karyawan->dept ?? '') == $deptKode;
                })->map(function ($closing) {
                    return $closing->karyawan->vcKodeBagian ?? null;
                })->filter()->unique();

                foreach ($bagianKodes as $bagianKode) {
                    $bagian = Bagian::where('vcKodeBagian', $bagianKode)->first();
                    $grouped[$divisiKode]['departemens'][$deptKode]['bagians'][$bagianKode] = [
                        'kode' => $bagianKode,
                        'nama' => $bagian->vcNamaBagian ?? $bagianKode,
                        'closings' => [],
                    ];

                    // Ambil closings untuk bagian ini
                    $bagianClosings = $closings->filter(function ($closing) use ($divisiKode, $deptKode, $bagianKode) {
                        $karyawan = $closing->karyawan;
                        return $closing->vcKodeDivisi == $divisiKode && 
                               ($karyawan->dept ?? '') == $deptKode &&
                               ($karyawan->vcKodeBagian ?? '') == $bagianKode;
                    });

                    foreach ($bagianClosings as $closing) {
                        $grouped[$divisiKode]['departemens'][$deptKode]['bagians'][$bagianKode]['closings'][] = $closing;
                    }

                    // Hitung total per bagian
                    $grouped[$divisiKode]['departemens'][$deptKode]['bagians'][$bagianKode]['total'] = 
                        $this->calculateTotal($bagianClosings);
                }

                // Hitung total per departemen
                $deptClosings = $closings->filter(function ($closing) use ($divisiKode, $deptKode) {
                    $karyawan = $closing->karyawan;
                    return $closing->vcKodeDivisi == $divisiKode && 
                           ($karyawan->dept ?? '') == $deptKode;
                });
                $grouped[$divisiKode]['departemens'][$deptKode]['total'] = 
                    $this->calculateTotal($deptClosings);
            }

            // Hitung total per divisi
            $divisiClosings = $closings->filter(function ($closing) use ($divisiKode) {
                return $closing->vcKodeDivisi == $divisiKode;
            });
            $grouped[$divisiKode]['total'] = $this->calculateTotal($divisiClosings);
        }

        return $grouped;
    }

    /**
     * Calculate total untuk collection of closings
     */
    private function calculateTotal($closings)
    {
        $total = [
            'premi' => 0,
            'gaji' => 0,
            'selisih_upah' => 0,
            'jm1' => 0,
            'jm2' => 0,
            'jm3' => 0,
            'lembur' => 0,
            'uang_makan_transport' => 0,
            'bpjs_kes' => 0,
            'bpjs_naker' => 0,
            'bpjs_pensiun' => 0,
            'tdk_hdr_hc' => 0,
            'koperasi' => 0,
            'pot_spn' => 0,
            'pot_dplk' => 0,
            'pot_lain_lain' => 0,
            'penerimaan' => 0,
            'takehomepay' => 0,
        ];

        foreach ($closings as $closing) {
            $premi = $closing->decPremi ?? 0;
            $gaji = $closing->decGapok ?? 0;
            $selisihUpah = $closing->decRapel ?? 0;
            $lembur = ($closing->decTotallembur1 ?? 0) + 
                      ($closing->decTotallembur2 ?? 0) + 
                      ($closing->decTotallembur3 ?? 0);
            $uangMakanTransport = ($closing->decUangMakan ?? 0) + ($closing->decTransport ?? 0);
            
            // Gunakan decPotonganBPJS* karena field ini yang selalu terisi di database
            // decBpjs* mungkin tidak terisi di beberapa data lama
            $bpjsKes = $closing->decPotonganBPJSKes ?? $closing->decBpjsKesehatan ?? 0;
            $bpjsNaker = $closing->decPotonganBPJSJHT ?? $closing->decBpjsNaker ?? 0;
            $bpjsPensiun = $closing->decPotonganBPJSJP ?? $closing->decBpjsPensiun ?? 0;
            $tdkHdrHc = ($closing->decPotonganAbsen ?? 0) + ($closing->decPotonganHC ?? 0);
            $koperasi = $closing->decPotonganKoperasi ?? 0;
            $potSpn = $closing->decIuranSPN ?? 0;
            $potDplk = $closing->decPotonganBPR ?? 0;
            $potLainLain = $closing->decPotonganLain ?? 0;

            // Penerimaan = Premi + Gaji + Selisih Upah + Lembur + Tot Uang Makan & Transport
            $penerimaan = $premi + $gaji + $selisihUpah + $lembur + $uangMakanTransport;

            // TAKEHOMEPAY = Penerimaan - (BPJS KES + BPJS NAKER + BPJS PENSIUN + TDK HDR/HC + KOPERASI + POT. SPN + POT. DPLK + POT. LAIN-LAIN)
            $takehomepay = $penerimaan - ($bpjsKes + $bpjsNaker + $bpjsPensiun + $tdkHdrHc + $koperasi + $potSpn + $potDplk + $potLainLain);

            // JM1, JM2, JM3 berdasarkan definisi user:
            // JM1 = akumulasi jumlah jam ke-1 lembur di hari kerja normal
            // JM2 = akumulasi jam lembur ke-2 pada hari kerja normal + jam lembur ke-2 hari kerja libur
            // JM3 = akumulasi jumlah jam lembur ke-3 pada hari kerja libur
            $jm1 = round($closing->decJamLemburKerja1 ?? 0, 1);
            $jm2 = round(($closing->decJamLemburKerja2 ?? 0) + ($closing->decJamLemburLibur2 ?? 0), 1);
            $jm3 = round($closing->decJamLemburLibur3 ?? 0, 1);

            $total['premi'] += $premi;
            $total['gaji'] += $gaji;
            $total['selisih_upah'] += $selisihUpah;
            $total['jm1'] += $jm1;
            $total['jm2'] += $jm2;
            $total['jm3'] += $jm3;
            $total['lembur'] += $lembur;
            $total['uang_makan_transport'] += $uangMakanTransport;
            $total['bpjs_kes'] += $bpjsKes;
            $total['bpjs_naker'] += $bpjsNaker;
            $total['bpjs_pensiun'] += $bpjsPensiun;
            $total['tdk_hdr_hc'] += $tdkHdrHc;
            $total['koperasi'] += $koperasi;
            $total['pot_spn'] += $potSpn;
            $total['pot_dplk'] += $potDplk;
            $total['pot_lain_lain'] += $potLainLain;
            $total['penerimaan'] += $penerimaan;
            $total['takehomepay'] += $takehomepay;
        }

        return $total;
    }

    /**
     * Calculate grand total untuk semua closings
     */
    private function calculateGrandTotal($closings)
    {
        return $this->calculateTotal($closings);
    }

    /**
     * Export rekap upah finance ver ke Excel menggunakan Laravel Excel
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'periode' => 'required|date',
            'divisi' => 'nullable|string',
        ]);

        $tanggalPeriode = Carbon::parse($request->periode)->format('Y-m-d');
        $kodeDivisi = $request->divisi;

        // Query closing data berdasarkan periode gajian
        $query = Closing::with(['karyawan', 'gapok', 'divisi', 'karyawan.departemen', 'karyawan.bagian'])
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
            return redirect()->route('rekap-upah-finance-ver.index')
                ->with('error', 'Tidak ada data untuk periode yang dipilih');
        }

        // Group data secara hierarkis
        $groupedData = $this->groupDataHierarchically($closings);

        // Hitung grand total
        $grandTotal = $this->calculateGrandTotal($closings);

        // Ambil data divisi untuk header
        $divisiData = null;
        if ($kodeDivisi && $kodeDivisi != 'SEMUA') {
            $divisiData = Divisi::where('vcKodeDivisi', $kodeDivisi)->first();
        }
        $namaDivisi = $divisiData ? $divisiData->vcNamaDivisi : '';

        // Generate filename
        $filename = 'Rekap_Upah_Finance_Ver_' . Carbon::parse($tanggalPeriode)->format('Ymd') . '.xlsx';

        // Export menggunakan Laravel Excel
        return Excel::download(
            new RekapUpahFinanceVerExport($groupedData, $grandTotal, $tanggalPeriode, $namaDivisi, $kodeDivisi),
            $filename
        );
    }
}

