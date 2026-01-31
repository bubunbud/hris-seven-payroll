<?php

namespace App\Http\Controllers;

use App\Models\Closing;
use App\Models\Divisi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
     * Export rekap bank ke Excel
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

        // Generate Excel content menggunakan format TSV (Tab Separated Values) untuk Excel
        $filename = 'Rekap_Bank_' . Carbon::parse($tanggalPeriode)->format('Ymd') . '.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($closings, $tanggalAwal, $tanggalAkhir, $namaDivisi, $kodeDivisi) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header
            $this->putCsvLine($file, ['REKAP BANK']);
            $this->putCsvLine($file, ['Periode: ' . Carbon::parse($tanggalAwal)->format('d F Y')]);
            if ($kodeDivisi && $kodeDivisi != 'SEMUA') {
                $this->putCsvLine($file, ['Divisi: ' . $kodeDivisi . ' -> ' . $namaDivisi]);
            } else {
                $this->putCsvLine($file, ['Divisi: SEMUA DIVISI']);
            }
            $this->putCsvLine($file, []); // Empty row

            // Column headers
            $this->putCsvLine($file, [
                'No',
                'NIK',
                'Nama',
                'Jenis Kelamin',
                'Tgl. Lahir',
                'Tipe ID',
                'No. KTP',
                'No. Rekening',
                'CIF',
                'Unit Bisnis',
                'Gaji + Lembur',
                'Beban TGI',
                'SIA-EXP',
                'SIA-Prod',
                'Beban RMA',
                'Beban SMU',
                'ABN JKT',
                'BPJS Kes',
                'BPJS Naker',
                'BPJS Pen',
                'Iuran SPN',
                'Pot Lain-lain',
                'Pot. Koperasi',
                'DPLK/CAR',
                'Jumlah'
            ]);

            // Data rows
            $no = 1;
            foreach ($closings as $closing) {
                $karyawan = $closing->karyawan;
                if (!$karyawan) continue;

                // Hitung Gaji + Lembur
                $gajiLembur = ($closing->decGapok ?? 0) +
                    ($closing->decUangMakan ?? 0) +
                    ($closing->decTransport ?? 0) +
                    ($closing->decPremi ?? 0) +
                    (($closing->decTotallembur1 ?? 0) + ($closing->decTotallembur2 ?? 0) + ($closing->decTotallembur3 ?? 0)) +
                    ($closing->decRapel ?? 0);

                // Hitung Pot Lain-lain = decPotonganLain + decPotonganAbsen + decPotonganHC
                $potLainLain = ($closing->decPotonganLain ?? 0) +
                    ($closing->decPotonganAbsen ?? 0) +
                    ($closing->decPotonganHC ?? 0);

                // Hitung total potongan
                $totalPotongan = ($closing->decPotonganBPJSKes ?? 0) +
                    ($closing->decPotonganBPJSJHT ?? 0) +
                    ($closing->decPotonganBPJSJP ?? 0) +
                    ($closing->decIuranSPN ?? 0) +
                    $potLainLain +
                    ($closing->decPotonganKoperasi ?? 0) +
                    ($closing->decPotonganBPR ?? 0);

                // Jumlah = Gaji + Lembur - Total Potongan
                $jumlah = $gajiLembur - $totalPotongan;

                // Format tanggal lahir
                $tglLahir = $karyawan->TTL ? Carbon::parse($karyawan->TTL)->format('d/m/Y') : '';

                // CIF dan Unit Bisnis dari divisi
                $cif = $closing->vcKodeDivisi ?? '';
                $unitBisnis = $closing->vcKodeDivisi ?? '';

                $this->putCsvLine($file, [
                    $no++,
                    $closing->vcNik,
                    $karyawan->Nama ?? '',
                    $karyawan->Jenis_Kelamin ?? '',
                    $tglLahir,
                    'KTP',
                    $karyawan->intNoBadge ?? '',
                    $karyawan->intNorek ?? '',
                    $cif,
                    $unitBisnis,
                    number_format($gajiLembur, 0, ',', '.'),
                    number_format($closing->decBebanTgi ?? 0, 0, ',', '.'),
                    number_format($closing->decBebanSiaExp ?? 0, 0, ',', '.'),
                    number_format($closing->decBebanSiaProd ?? 0, 0, ',', '.'),
                    number_format($closing->decBebanRma ?? 0, 0, ',', '.'),
                    number_format($closing->decBebanSmu ?? 0, 0, ',', '.'),
                    number_format($closing->decBebanAbnJkt ?? 0, 0, ',', '.'),
                    number_format($closing->decPotonganBPJSKes ?? 0, 0, ',', '.'),
                    number_format($closing->decPotonganBPJSJHT ?? 0, 0, ',', '.'),
                    number_format($closing->decPotonganBPJSJP ?? 0, 0, ',', '.'),
                    number_format($closing->decIuranSPN ?? 0, 0, ',', '.'),
                    number_format($potLainLain, 0, ',', '.'),
                    number_format($closing->decPotonganKoperasi ?? 0, 0, ',', '.'),
                    number_format($closing->decPotonganBPR ?? 0, 0, ',', '.'),
                    number_format($jumlah, 0, ',', '.')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper function untuk menulis CSV line dengan tab separator untuk Excel
     */
    private function putCsvLine($file, $data)
    {
        // Gunakan tab sebagai separator untuk Excel (lebih universal)
        $line = [];
        foreach ($data as $field) {
            // Convert ke string dan escape tab/newline
            $field = (string) $field;
            // Replace tab dengan space, newline dengan space
            $field = str_replace(["\t", "\n", "\r"], [' ', ' ', ' '], $field);
            // Jika mengandung tab atau newline, wrap dengan quotes
            if (strpos($field, "\t") !== false || strpos($field, "\n") !== false || strpos($field, '"') !== false) {
                $field = '"' . str_replace('"', '""', $field) . '"';
            }
            $line[] = $field;
        }
        fwrite($file, implode("\t", $line) . "\n");
    }
}
