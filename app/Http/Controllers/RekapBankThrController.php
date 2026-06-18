<?php

namespace App\Http\Controllers;

use App\Models\ClosingThr;
use App\Models\Divisi;
use App\Exports\RekapBankThrExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class RekapBankThrController extends Controller
{
    /**
     * Display form untuk cetak Rekap Bank THR
     */
    public function index()
    {
        $divisis = Divisi::orderBy('vcKodeDivisi')->get();

        // Ambil tahun yang ada data THR Operator dan Security
        $years = ClosingThr::selectRaw('YEAR(dtTanggalTHR) as year')
            ->whereIn('vcGroupPegawai', ['Operator', 'Security'])
            ->distinct()
            ->orderByRaw('YEAR(dtTanggalTHR) DESC')
            ->pluck('year')
            ->map(fn($y) => (string) $y)
            ->toArray();

        // Ambil tanggal THR yang tersedia (untuk dropdown)
        $tanggalThrOptions = ClosingThr::select('dtTanggalTHR')
            ->whereIn('vcGroupPegawai', ['Operator', 'Security'])
            ->distinct()
            ->orderBy('dtTanggalTHR', 'desc')
            ->get()
            ->map(function ($r) {
                $d = $r->dtTanggalTHR;
                return [
                    'value' => $d instanceof Carbon ? $d->format('Y-m-d') : Carbon::parse($d)->format('Y-m-d'),
                    'label' => $d instanceof Carbon ? $d->format('d/m/Y') : Carbon::parse($d)->format('d/m/Y'),
                ];
            });

        $defaultTahun = count($years) > 0 ? $years[0] : date('Y');
        $defaultTanggal = $tanggalThrOptions->first()['value'] ?? null;

        return view('laporan.rekap-bank-thr.index', compact(
            'divisis',
            'years',
            'tanggalThrOptions',
            'defaultTahun',
            'defaultTanggal'
        ));
    }

    /**
     * Preview/Print Rekap Bank THR
     */
    public function preview(Request $request)
    {
        $request->validate([
            'tanggal_thr' => 'required|date',
            'divisi' => 'nullable|string',
        ]);

        $tanggalThr = Carbon::parse($request->tanggal_thr)->format('Y-m-d');
        $kodeDivisi = $request->divisi;

        $query = ClosingThr::with(['karyawan', 'divisi'])
            ->where('dtTanggalTHR', $tanggalThr)
            ->whereIn('vcGroupPegawai', ['Operator', 'Security'])
            ->whereHas('karyawan', function ($q) {
                $q->where('vcAktif', '1')
                  ->whereNull('Tgl_Berhenti');
            });

        if ($kodeDivisi && $kodeDivisi != 'SEMUA') {
            $query->where('vcKodeDivisi', $kodeDivisi);
        }

        $closingThrs = $query->orderBy('vcKodeDivisi')
            ->orderBy('vcNik')
            ->get();

        if ($closingThrs->isEmpty()) {
            return redirect()->route('rekap-bank-thr.index')
                ->with('error', 'Tidak ada data THR Operator/Security untuk tanggal yang dipilih');
        }

        $divisiData = null;
        if ($kodeDivisi && $kodeDivisi != 'SEMUA') {
            $divisiData = Divisi::where('vcKodeDivisi', $kodeDivisi)->first();
        }
        $namaDivisi = $divisiData ? $divisiData->vcNamaDivisi : '';

        // Summary per divisi (hanya jika filter SEMUA / tidak ada filter divisi)
        $summaryPerDivisi = [];
        if (!$kodeDivisi || $kodeDivisi == 'SEMUA') {
            $grouped = $closingThrs->groupBy('vcKodeDivisi');
            foreach ($grouped as $kode => $items) {
                $totalNilaiThr = $items->sum(fn($c) => (float) ($c->decNilaiTHR ?? 0));
                $summaryPerDivisi[] = [
                    'kode' => $kode,
                    'nama' => $items->first()->divisi->vcNamaDivisi ?? $kode,
                    'jumlah_karyawan' => $items->count(),
                    'nilai_thr' => $totalNilaiThr,
                    'jumlah' => $totalNilaiThr, // sama dengan nilai_thr
                ];
            }
            // Urutkan berdasarkan kode divisi
            usort($summaryPerDivisi, fn($a, $b) => strcmp($a['kode'], $b['kode']));
        }

        return view('laporan.rekap-bank-thr.preview', compact(
            'closingThrs',
            'tanggalThr',
            'namaDivisi',
            'kodeDivisi',
            'divisiData',
            'summaryPerDivisi'
        ));
    }

    /**
     * Export Rekap Bank THR ke Excel
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'tanggal_thr' => 'required|date',
            'divisi' => 'nullable|string',
        ]);

        $tanggalThr = Carbon::parse($request->tanggal_thr)->format('Y-m-d');
        $kodeDivisi = $request->divisi;

        $query = ClosingThr::with(['karyawan', 'divisi'])
            ->where('dtTanggalTHR', $tanggalThr)
            ->whereIn('vcGroupPegawai', ['Operator', 'Security'])
            ->whereHas('karyawan', function ($q) {
                $q->where('vcAktif', '1')
                  ->whereNull('Tgl_Berhenti');
            });

        if ($kodeDivisi && $kodeDivisi != 'SEMUA') {
            $query->where('vcKodeDivisi', $kodeDivisi);
        }

        $closingThrs = $query->orderBy('vcKodeDivisi')
            ->orderBy('vcNik')
            ->get();

        if ($closingThrs->isEmpty()) {
            return redirect()->route('rekap-bank-thr.index')
                ->with('error', 'Tidak ada data THR Operator/Security untuk tanggal yang dipilih');
        }

        $divisiData = null;
        if ($kodeDivisi && $kodeDivisi != 'SEMUA') {
            $divisiData = Divisi::where('vcKodeDivisi', $kodeDivisi)->first();
        }
        $namaDivisi = $divisiData ? $divisiData->vcNamaDivisi : '';

        // Summary per divisi (hanya jika filter SEMUA)
        $summaryPerDivisi = [];
        if (!$kodeDivisi || $kodeDivisi == 'SEMUA') {
            $grouped = $closingThrs->groupBy('vcKodeDivisi');
            foreach ($grouped as $kode => $items) {
                $totalNilaiThr = $items->sum(fn($c) => (float) ($c->decNilaiTHR ?? 0));
                $summaryPerDivisi[] = [
                    'kode' => $kode,
                    'nama' => $items->first()->divisi->vcNamaDivisi ?? $kode,
                    'jumlah_karyawan' => $items->count(),
                    'nilai_thr' => $totalNilaiThr,
                    'jumlah' => $totalNilaiThr,
                ];
            }
            usort($summaryPerDivisi, fn($a, $b) => strcmp($a['kode'], $b['kode']));
        }

        $filename = 'Rekap_Bank_THR_' . Carbon::parse($tanggalThr)->format('Ymd') . '.xlsx';

        return Excel::download(
            new RekapBankThrExport($closingThrs, $tanggalThr, $namaDivisi, $kodeDivisi, $summaryPerDivisi),
            $filename
        );
    }
}
