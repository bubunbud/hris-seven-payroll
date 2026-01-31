<?php

namespace App\Http\Controllers;

use App\Models\Closing;
use App\Models\Divisi;
use App\Exports\RekapBankExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class RekapBankController extends Controller
{
    /**
     * Display form untuk cetak rekap bank
     */
    public function index()
    {
        $divisis = Divisi::orderBy('vcKodeDivisi')->get();

        // Default: tanggal 1 atau 15 bulan ini (tergantung tanggal hari ini)
        $hariIni = Carbon::now()->day;
        $defaultPeriode = $hariIni <= 15
            ? Carbon::now()->startOfMonth()->format('Y-m-d') // Tanggal 1
            : Carbon::now()->startOfMonth()->addDays(14)->format('Y-m-d'); // Tanggal 15

        return view('laporan.rekap-bank.index', compact('divisis', 'defaultPeriode'));
    }

    /**
     * Preview/Print rekap bank berdasarkan filter
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
            return redirect()->route('rekap-bank.index')
                ->with('error', 'Tidak ada data untuk periode yang dipilih');
        }

        // Ambil tanggal awal dan akhir dari data pertama
        $tanggalAwal = $closings->first()->vcPeriodeAwal;
        $tanggalAkhir = $closings->first()->vcPeriodeAkhir;

        // Ambil data divisi untuk header
        $divisiData = null;
        if ($kodeDivisi && $kodeDivisi != 'SEMUA') {
            $divisiData = Divisi::where('vcKodeDivisi', $kodeDivisi)->first();
        }
        $namaDivisi = $divisiData ? $divisiData->vcNamaDivisi : '';

        return view('laporan.rekap-bank.preview', compact(
            'closings',
            'tanggalAwal',
            'tanggalAkhir',
            'tanggalPeriode',
            'namaDivisi',
            'kodeDivisi',
            'divisiData'
        ));
    }

    /**
     * Export rekap bank ke Excel menggunakan Laravel Excel
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
            return redirect()->route('rekap-bank.index')
                ->with('error', 'Tidak ada data untuk periode yang dipilih');
        }

        // Ambil tanggal awal dan akhir dari data pertama
        $tanggalAwal = $closings->first()->vcPeriodeAwal;
        $tanggalAkhir = $closings->first()->vcPeriodeAkhir;

        // Ambil data divisi untuk header
        $divisiData = null;
        if ($kodeDivisi && $kodeDivisi != 'SEMUA') {
            $divisiData = Divisi::where('vcKodeDivisi', $kodeDivisi)->first();
        }
        $namaDivisi = $divisiData ? $divisiData->vcNamaDivisi : '';

        // Generate filename
        $filename = 'Rekap_Bank_' . Carbon::parse($tanggalPeriode)->format('Ymd') . '.xlsx';

        // Export menggunakan Laravel Excel
        return Excel::download(
            new RekapBankExport($closings, $tanggalAwal, $tanggalAkhir, $namaDivisi, $kodeDivisi),
            $filename
        );
    }
}
