@extends('layouts.app')

@section('title', 'Preview Rekap Upah Karyawan')

@section('content')
<style>
    @media print {
        @page {
            size: landscape;
            margin: 2.5cm 1cm 2cm 1cm;

            @bottom-right {
                content: "Halaman " counter(page) " dari " counter(pages);
                font-size: 8pt;
            }
        }

        body {
            font-size: 9pt;
        }

        .no-print {
            display: none;
        }

        /* Sembunyikan sidebar dan tombol menu */
        .sidebar,
        .d-lg-none,
        #toggleSidebar {
            display: none !important;
        }

        .app-wrapper {
            display: block;
        }

        .content {
            padding: 0;
            margin: 0;
        }

        .page-break {
            page-break-after: always;
        }

        .print-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            text-align: center;
            padding: 5px 0;
            border-bottom: 2px solid #000;
            background-color: #fff;
            z-index: 1000;
            /* Print header muncul di semua halaman termasuk pertama */
        }

        .print-header h3 {
            margin: 0;
            font-size: 12pt;
            font-weight: bold;
        }

        .print-header h4 {
            margin: 2px 0;
            font-size: 10pt;
        }

        /* Report header tetap muncul di halaman pertama */
        .report-header {
            display: block !important;
            margin-bottom: 15px;
            margin-top: 0;
            position: relative;
            z-index: 1001;
            background-color: #fff;
        }

        /* Print header muncul di semua halaman termasuk pertama (di atas report-header) */
        /* Di halaman pertama, report-header akan muncul setelah print-header */
        .report-content {
            margin-top: 0;
            padding-top: 0;
        }

        /* Nama penandatangan rata kiri (hanya untuk nama, bukan caption dan jabatan) */
        .signature-name {
            text-align: left !important;
        }
    }

    .print-header {
        display: none;
    }


    .rekap-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 8pt;
    }

    .rekap-table th,
    .rekap-table td {
        border: 1px solid #000;
        padding: 3px 4px;
        text-align: left;
    }

    .rekap-table th {
        background-color: #f0f0f0;
        font-weight: bold;
        text-align: center;
    }

    .rekap-table .text-right {
        text-align: right;
    }

    .rekap-table .text-center {
        text-align: center;
    }

    .rekap-table .bold {
        font-weight: bold;
    }

    .rekap-table .indent-1 {
        padding-left: 15px;
    }

    .rekap-table .indent-2 {
        padding-left: 30px;
    }

    .rekap-table .indent-3 {
        padding-left: 45px;
    }

    .report-header {
        text-align: center;
        margin-bottom: 10px;
    }

    .report-header h3 {
        margin: 0;
        font-size: 14pt;
        font-weight: bold;
    }

    .report-header h4 {
        margin: 5px 0;
        font-size: 11pt;
    }

    .report-footer {
        margin-top: 20px;
        font-size: 8pt;
    }

    .signature-block {
        display: inline-block;
        width: 18%;
        vertical-align: top;
        margin-right: 1%;
    }

    .signature-block .signature-line {
        border-top: 1px solid #000;
        width: 100%;
        margin-top: 0;
        padding-top: 0;
        min-height: 50px;
    }
</style>

<div class="container-fluid">
    <div class="row no-print">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0 no-print">
                    <i class="fas fa-file-alt me-2"></i>Preview Rekap Upah Karyawan
                </h2>
                <div>
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </button>
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Header (akan muncul di setiap halaman saat print) -->
    <div class="print-header">
        <h3>REKAPITULASI UPAH KARYAWAN</h3>
        <h4>
            @if($kodeDivisi && $kodeDivisi != 'SEMUA')
            {{ $kodeDivisi }} -> {{ $namaDivisi }}
            @else
            SEMUA DIVISI
            @endif
        </h4>
        <h4>Periode {{ \Carbon\Carbon::parse($tanggalPeriode)->format('d F Y') }}</h4>
    </div>


    <!-- Report Content -->
    <div class="report-content">
        @php
        $pageNumber = 1;
        $totalPages = 1; // Will be calculated
        $rowsPerPage = 30; // Approximate rows per page
        $currentRow = 0;
        @endphp

        <!-- Report Header -->
        <div class="report-header">
            <h3>REKAPITULASI UPAH KARYAWAN</h3>
            <h4>
                @if($kodeDivisi && $kodeDivisi != 'SEMUA')
                {{ $kodeDivisi }} -> {{ $namaDivisi }}
                @else
                SEMUA DIVISI
                @endif
            </h4>
            <h4>Periode {{ \Carbon\Carbon::parse($tanggalPeriode)->format('d F Y') }}</h4>
        </div>

        <!-- Main Table -->
        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="width: 3%;">Urt</th>
                    <th style="width: 6%;">ID</th>
                    <th style="width: 15%;">BAGIAN</th>
                    <th style="width: 5%;">GOL</th>
                    <th style="width: 5%;">PREMI</th>
                    <th style="width: 6%;">GAJI</th>
                    <th style="width: 4%;">TSM</th>
                    <th colspan="3" style="width: 6%;">JAM LEMBUR</th>
                    <th style="width: 5%;">SELISIH UPAH</th>
                    <th style="width: 6%;">UPAH LEMBUR</th>
                    <th style="width: 4%;">PERSEN LEMBUR (%)</th>
                    <th style="width: 5%;">POT. BPJS KES</th>
                    <th style="width: 5%;">POT. BPJS NAKER</th>
                    <th style="width: 5%;">POT. BPJS PENSIUN</th>
                    <th style="width: 5%;">POT. TDK HDR/HC</th>
                    <th style="width: 5%;">POT. LAIN-LAIN</th>
                    <th style="width: 6%;">TOTAL PENERIMAAN</th>
                    <th style="width: 6%;">GENERAL TOTAL</th>
                </tr>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th style="width: 2%;">JM1</th>
                    <th style="width: 2%;">JM2</th>
                    <th style="width: 2%;">JM3</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @php
                $urutGlobal = 0;
                @endphp

                @foreach($groupedData as $divisiKode => $divisi)
                @foreach($divisi['departemens'] as $deptKode => $departemen)
                @php
                $isFirstBagian = true;
                @endphp

                <!-- Judul Departemen (muncul di awal departemen) -->
                <tr class="bold" style="background-color: #e0e0e0;">
                    <td colspan="3"><strong>DEPARTEMEN: {{ $departemen['nama'] }}</strong></td>
                    <td colspan="17"></td>
                </tr>

                @foreach($departemen['bagians'] as $bagianKode => $bagian)
                @php
                $urutBagian = 0;
                @endphp

                <!-- Bagian Header -->
                <tr class="bold">
                    <td colspan="3" class="indent-1"><strong>Bagian {{ $bagian['nama'] }}</strong></td>
                    <td colspan="17"></td>
                </tr>

                <!-- Karyawan dalam Bagian -->
                @foreach($bagian['karyawans'] as $closing)
                @php
                $urutBagian++;
                $urutGlobal++;
                $currentRow++;

                $karyawan = $closing->karyawan;
                $upahLembur = ($closing->decTotallembur1 ?? 0) + ($closing->decTotallembur2 ?? 0) + ($closing->decTotallembur3 ?? 0);

                // General Total = Premi + Gaji + Selisih Upah + Upah Lembur
                $generalTotal = ($closing->decPremi ?? 0) +
                ($closing->decGapok ?? 0) +
                ($closing->decRapel ?? 0) +
                $upahLembur;

                // Total Potongan = BPJS Kes + BPJS Naker + BPJS Pensiun + Tdk HDR/HC + Lain-lain
                $totalPotongan = ($closing->decPotonganBPJSKes ?? 0) +
                ($closing->decPotonganBPJSJHT ?? 0) +
                ($closing->decPotonganBPJSJP ?? 0) +
                (($closing->decPotonganAbsen ?? 0) + ($closing->decPotonganHC ?? 0)) +
                ($closing->decPotonganLain ?? 0);

                // Total Penerimaan = General Total - Total Potongan
                $totalPenerimaan = $generalTotal - $totalPotongan;

                $persenLembur = ($closing->decGapok ?? 0) > 0
                ? ($upahLembur / ($closing->decGapok ?? 1)) * 100
                : 0;
                @endphp
                <tr>
                    <td class="text-center">{{ $urutGlobal }}</td>
                    <td>{{ $closing->vcNik }}</td>
                    <td class="indent-2">{{ $karyawan->Nama ?? 'N/A' }}</td>
                    <td class="text-center">{{ $closing->vcKodeGolongan }}</td>
                    <td class="text-right">{{ number_format($closing->decPremi ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($closing->decGapok ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">0</td>
                    <td class="text-right">{{ number_format($closing->decJamLemburKerja1 ?? 0, 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format(($closing->decJamLemburKerja2 ?? 0) + ($closing->decJamLemburLibur2 ?? 0), 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format(($closing->decJamLemburKerja3 ?? 0) + ($closing->decJamLemburLibur3 ?? 0), 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($closing->decRapel ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($upahLembur, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($persenLembur, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($closing->decPotonganBPJSKes ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($closing->decPotonganBPJSJHT ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($closing->decPotonganBPJSJP ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format(($closing->decPotonganAbsen ?? 0) + ($closing->decPotonganHC ?? 0), 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($closing->decPotonganLain ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totalPenerimaan, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($generalTotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach

                <!-- Total Bagian -->
                @php
                $bagianTotal = $bagian['total'];
                $bagianPersenLembur = $bagianTotal['gaji'] > 0
                ? ($bagianTotal['upah_lembur'] / $bagianTotal['gaji']) * 100
                : 0;
                @endphp
                <tr class="bold">
                    <td colspan="3" class="indent-1"><strong>Total Bag. {{ $bagian['nama'] }}</strong></td>
                    <td></td>
                    <td class="text-right">{{ number_format($bagianTotal['premi'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['gaji'], 0, ',', '.') }}</td>
                    <td class="text-right">0</td>
                    <td class="text-right">{{ number_format($bagianTotal['jam_lembur_jm1'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['jam_lembur_jm2'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['jam_lembur_jm3'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['selisih_upah'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['upah_lembur'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianPersenLembur, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['pot_bpjs_kes'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['pot_bpjs_naker'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['pot_bpjs_pensiun'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['pot_tdk_hdr_hc'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['pot_lain_lain'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['total_penerimaan'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['general_total'], 0, ',', '.') }}</td>
                </tr>
                @endforeach

                <!-- Total Departemen (setelah semua bagian dalam departemen) -->
                @php
                $deptTotal = $departemen['total'];
                $deptPersenLembur = $deptTotal['gaji'] > 0
                ? ($deptTotal['upah_lembur'] / $deptTotal['gaji']) * 100
                : 0;
                @endphp
                <tr class="bold" style="background-color: #f0f0f0;">
                    <td colspan="3"><strong>Total Dept. {{ $departemen['nama'] }}</strong></td>
                    <td></td>
                    <td class="text-right">{{ number_format($deptTotal['premi'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['gaji'], 0, ',', '.') }}</td>
                    <td class="text-right">0</td>
                    <td class="text-right">{{ number_format($deptTotal['jam_lembur_jm1'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['jam_lembur_jm2'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['jam_lembur_jm3'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['selisih_upah'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['upah_lembur'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptPersenLembur, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['pot_bpjs_kes'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['pot_bpjs_naker'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['pot_bpjs_pensiun'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['pot_tdk_hdr_hc'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['pot_lain_lain'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['total_penerimaan'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['general_total'], 0, ',', '.') }}</td>
                </tr>
                @endforeach

                <!-- Total Divisi -->
                @php
                $divisiTotal = $divisi['total'];
                $divisiPersenLembur = $divisiTotal['gaji'] > 0
                ? ($divisiTotal['upah_lembur'] / $divisiTotal['gaji']) * 100
                : 0;
                @endphp
                <tr class="bold">
                    <td colspan="3"><strong>Total {{ $divisi['nama'] }}</strong></td>
                    <td></td>
                    <td class="text-right">{{ number_format($divisiTotal['premi'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($divisiTotal['gaji'], 0, ',', '.') }}</td>
                    <td class="text-right">0</td>
                    <td class="text-right">{{ number_format($divisiTotal['jam_lembur_jm1'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($divisiTotal['jam_lembur_jm2'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($divisiTotal['jam_lembur_jm3'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($divisiTotal['selisih_upah'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($divisiTotal['upah_lembur'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($divisiPersenLembur, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($divisiTotal['pot_bpjs_kes'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($divisiTotal['pot_bpjs_naker'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($divisiTotal['pot_bpjs_pensiun'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($divisiTotal['pot_tdk_hdr_hc'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($divisiTotal['pot_lain_lain'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($divisiTotal['total_penerimaan'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($divisiTotal['general_total'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Grand Total (only if multiple divisi) -->
        @if(count($groupedData) > 1)
        <table class="rekap-table" style="margin-top: 10px;">
            <tbody>
                @php
                $grandPersenLembur = $grandTotal['gaji'] > 0
                ? ($grandTotal['upah_lembur'] / $grandTotal['gaji']) * 100
                : 0;
                @endphp
                <tr class="bold">
                    <td colspan="3"><strong>GRAND TOTAL</strong></td>
                    <td></td>
                    <td class="text-right">{{ number_format($grandTotal['premi'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['gaji'], 0, ',', '.') }}</td>
                    <td class="text-right">0</td>
                    <td class="text-right">{{ number_format($grandTotal['jam_lembur_jm1'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['jam_lembur_jm2'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['jam_lembur_jm3'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['selisih_upah'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['upah_lembur'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandPersenLembur, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['pot_bpjs_kes'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['pot_bpjs_naker'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['pot_bpjs_pensiun'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['pot_tdk_hdr_hc'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['pot_lain_lain'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['total_penerimaan'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['general_total'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        @endif

        <!-- Footer Signature Blocks -->
        <div class="report-footer">
            <div style="text-align: center; margin-top: 30px;">
                <p>Bandung Barat, {{ \Carbon\Carbon::now()->format('d F Y') }}</p>
            </div>

            <!-- Caption Row (di bawah tempat, tanggal) -->
            <div style="margin-top: 30px; display: flex; justify-content: space-around; align-items: center;">
                <div class="signature-block">
                    <p style="margin: 0; text-align: center;"><strong>Dibuat Oleh,</strong></p>
                </div>
                <div class="signature-block">
                    <p style="margin: 0; text-align: center;"><strong>Diperiksa Oleh,</strong></p>
                </div>
                <div class="signature-block">
                    <p style="margin: 0; text-align: center;"><strong>Mengetahui,</strong></p>
                </div>
                <div class="signature-block">
                    <p style="margin: 0; text-align: center;"><strong>Mengetahui,</strong></p>
                </div>
                <div class="signature-block">
                    <p style="margin: 0; text-align: center;"><strong>Mengetahui,</strong></p>
                </div>
            </div>

            <!-- Nama Penandatangan Row (di atas garis) -->
            <div style="margin-top: 70px; display: flex; justify-content: space-around; align-items: flex-end;">
                <div class="signature-block">
                    <p class="signature-name" style="margin: 0; min-height: 20px; padding-bottom: 2px;">{{ $divisiData && $divisiData->vcStaff ? $divisiData->vcStaff : '' }}</p>
                </div>
                <div class="signature-block">
                    <p class="signature-name" style="margin: 0; min-height: 20px; padding-bottom: 2px;">{{ $divisiData && $divisiData->vcKeuangan ? $divisiData->vcKeuangan : '' }}</p>
                </div>
                <div class="signature-block">
                    <p class="signature-name" style="margin: 0; min-height: 20px; padding-bottom: 2px;">{{ $divisiData && $divisiData->vcKabag ? $divisiData->vcKabag : '' }}</p>
                </div>
                <div class="signature-block">
                    <p class="signature-name" style="margin: 0; min-height: 20px; padding-bottom: 2px;">{{ $divisiData && $divisiData->vPPIC ? $divisiData->vPPIC : '' }}</p>
                </div>
                <div class="signature-block">
                    <p class="signature-name" style="margin: 0; min-height: 20px; padding-bottom: 2px;">{{ $divisiData && $divisiData->vcPlantManager ? $divisiData->vcPlantManager : '' }}</p>
                </div>
            </div>

            <!-- Garis Tanda Tangan -->
            <div style="margin-top: 0; display: flex; justify-content: space-around; align-items: center;">
                <div class="signature-block">
                    <div class="signature-line"></div>
                </div>
                <div class="signature-block">
                    <div class="signature-line"></div>
                </div>
                <div class="signature-block">
                    <div class="signature-line"></div>
                </div>
                <div class="signature-block">
                    <div class="signature-line"></div>
                </div>
                <div class="signature-block">
                    <div class="signature-line"></div>
                </div>
            </div>

            <!-- Jabatan Row (di bawah garis) -->
            <div style="margin-top: -45px; display: flex; justify-content: space-around; align-items: flex-start;">
                <div class="signature-block">
                    <p style="margin: 0; text-align: center; padding-top: 0;">Staff Payroll</p>
                </div>
                <div class="signature-block">
                    <p style="margin: 0; text-align: center; padding-top: 0;">Kabag HR/Keuangan</p>
                </div>
                <div class="signature-block">
                    <p style="margin: 0; text-align: center; padding-top: 0;">Manager / Ka. Dept</p>
                </div>
                <div class="signature-block">
                    <p style="margin: 0; text-align: center; padding-top: 0;">Direktur / General Manager</p>
                </div>
                <div class="signature-block">
                    <p style="margin: 0; text-align: center; padding-top: 0;">General Manager / Direktur</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection