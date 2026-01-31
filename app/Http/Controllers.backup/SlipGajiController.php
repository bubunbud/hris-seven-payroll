<?php

namespace App\Http\Controllers;

use App\Models\Closing;
use App\Models\Divisi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SlipGajiController extends Controller
{
    /**
     * Display form untuk cetak slip gaji
     */
    public function index()
    {
        $divisis = Divisi::orderBy('vcKodeDivisi')->get();
        
        // Default: tanggal 1 atau 15 bulan ini (tergantung tanggal hari ini)
        $hariIni = Carbon::now()->day;
        $defaultPeriode = $hariIni <= 15 
            ? Carbon::now()->startOfMonth()->format('Y-m-d') // Tanggal 1
            : Carbon::now()->startOfMonth()->addDays(14)->format('Y-m-d'); // Tanggal 15

        return view('laporan.slip-gaji.index', compact('divisis', 'defaultPeriode'));
    }

    /**
     * Preview/Print slip gaji berdasarkan filter
     */
    public function preview(Request $request)
    {
        $request->validate([
            'periode' => 'required|date',
            'divisi' => 'nullable|string',
            'nik_dari' => 'nullable|string',
            'nik_sampai' => 'nullable|string',
        ]);

        $tanggalPeriode = Carbon::parse($request->periode)->format('Y-m-d');
        $kodeDivisi = $request->divisi;
        $nikDari = $request->nik_dari;
        $nikSampai = $request->nik_sampai;

        // Query closing data berdasarkan periode gajian (tanggal 1 atau 15)
        $query = Closing::with(['karyawan', 'gapok', 'divisi'])
            ->where('periode', $tanggalPeriode);

        if ($kodeDivisi && $kodeDivisi != 'SEMUA') {
            $query->where('vcKodeDivisi', $kodeDivisi);
        }

        if ($nikDari) {
            $query->where('vcNik', '>=', $nikDari);
        }

        if ($nikSampai) {
            $query->where('vcNik', '<=', $nikSampai);
        }

        $closings = $query->orderBy('vcNik')->orderBy('vcClosingKe')->get();

        if ($closings->isEmpty()) {
            return redirect()->route('slip-gaji.index')
                ->with('error', 'Tidak ada data slip gaji untuk periode yang dipilih');
        }

        // Ambil tanggal awal dan akhir dari data pertama (semua closing dengan periode yang sama memiliki range yang sama)
        $tanggalAwal = $closings->first()->vcPeriodeAwal;
        $tanggalAkhir = $closings->first()->vcPeriodeAkhir;

        // Ambil data periode sebelumnya untuk setiap closing (untuk absensi periode 1 dan 2)
        $closingsWithPrevious = [];
        foreach ($closings as $closing) {
            $periodeSebelumnya = null;
            
            // Jika closing ke-2, ambil closing ke-1 dengan periode yang sama
            if ($closing->vcClosingKe == '2') {
                $periodeSebelumnya = Closing::where('vcNik', $closing->vcNik)
                    ->where('periode', $closing->periode)
                    ->where('vcClosingKe', '1')
                    ->first();
            } 
            // Jika closing ke-1, ambil closing ke-2 dari periode sebelumnya
            elseif ($closing->vcClosingKe == '1') {
                $periodeSebelumnya = Closing::where('vcNik', $closing->vcNik)
                    ->where('periode', '<', $closing->periode)
                    ->where('vcClosingKe', '2')
                    ->orderBy('periode', 'desc')
                    ->first();
            }
            
            $closingsWithPrevious[] = [
                'closing' => $closing,
                'periode_sebelumnya' => $periodeSebelumnya,
            ];
        }

        return view('laporan.slip-gaji.preview', compact('closingsWithPrevious', 'tanggalAwal', 'tanggalAkhir', 'tanggalPeriode'));
    }

    /**
     * Print single slip gaji
     */
    public function print($periodeAwal, $periodeAkhir, $nik, $periode, $closingKe)
    {
        $closing = Closing::where('vcPeriodeAwal', $periodeAwal)
            ->where('vcPeriodeAkhir', $periodeAkhir)
            ->where('vcNik', $nik)
            ->where('periode', $periode)
            ->where('vcClosingKe', $closingKe)
            ->with(['karyawan', 'gapok', 'divisi'])
            ->first();

        if (!$closing) {
            abort(404, 'Data closing tidak ditemukan');
        }

        return view('laporan.slip-gaji.print', compact('closing'));
    }
}
