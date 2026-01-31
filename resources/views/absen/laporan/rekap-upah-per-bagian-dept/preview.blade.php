@extends('layouts.app')

@section('title', 'Preview Rekap Upah Per Bagian / Departemen')

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

        .report-content {
            margin-top: 0;
            padding-top: 0;
        }

        /* Nama penandatangan center */
        .signature-name {
            text-align: center !important;
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
                    <i class="fas fa-file-alt me-2"></i>Preview Rekap Upah Per Bagian / Departemen
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
        <h3>REKAP UPAH KARYAWAN PER BAGIAN / DEPARTEMEN</h3>
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
        <!-- Report Header -->
        <div class="report-header">
            <h3>REKAP UPAH KARYAWAN PER BAGIAN / DEPARTEMEN</h3>
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
                    <th style="width: 5%;">ID</th>
                    <th style="width: 15%;">BAGIAN</th>
                    <th style="width: 4%;">GOL</th>
                    <th style="width: 5%;">PREMI</th>
                    <th style="width: 6%;">GAJI</th>
                    <th style="width: 4%;">TSM</th>
                    <th colspan="4" style="width: 8%;">JAM LEMBUR</th>
                    <th style="width: 5%;">SELISIH UPAH</th>
                    <th style="width: 6%;">UPAH LEMBUR</th>
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
                    <th style="width: 2%;">JM4</th>
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
                <!-- Judul Departemen -->
                <tr class="bold" style="background-color: #e0e0e0;">
                    <td colspan="3"><strong>Dept. {{ $departemen['nama'] }}</strong></td>
                    <td colspan="17"></td>
                </tr>

                @foreach($departemen['bagians'] as $bagianKode => $bagian)
                @php
                $urutGlobal++;
                $bagianTotal = $bagian['total'];
                @endphp

                <!-- Total Bagian -->
                <tr class="bold">
                    <td class="text-center">{{ $urutGlobal }}</td>
                    <td>{{ $bagianKode }}</td>
                    <td><strong>Total Bag. {{ $bagian['nama'] }}</strong></td>
                    <td></td>
                    <td class="text-right">{{ number_format($bagianTotal['premi'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['gaji'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['tsm'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['jam_lembur_jm1'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['jam_lembur_jm2'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['jam_lembur_jm3'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['jam_lembur_jm4'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['selisih_upah'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['upah_lembur'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['pot_bpjs_kes'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['pot_bpjs_naker'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['pot_bpjs_pensiun'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['pot_tdk_hdr_hc'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['pot_lain_lain'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['total_penerimaan'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['general_total'], 0, ',', '.') }}</td>
                </tr>
                @endforeach

                <!-- Total Departemen -->
                @php
                $deptTotal = $departemen['total'];
                @endphp
                <tr class="bold" style="background-color: #f0f0f0;">
                    <td colspan="3"><strong>Total Dept. {{ $departemen['nama'] }}</strong></td>
                    <td></td>
                    <td class="text-right">{{ number_format($deptTotal['premi'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['gaji'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['tsm'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['jam_lembur_jm1'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['jam_lembur_jm2'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['jam_lembur_jm3'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['jam_lembur_jm4'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['selisih_upah'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['upah_lembur'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['pot_bpjs_kes'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['pot_bpjs_naker'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['pot_bpjs_pensiun'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['pot_tdk_hdr_hc'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['pot_lain_lain'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['total_penerimaan'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($deptTotal['general_total'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
                @endforeach

                <!-- Grand Total -->
                <tr class="bold" style="background-color: #c0c0c0;">
                    <td colspan="3"><strong>GRAND TOTAL</strong></td>
                    <td></td>
                    <td class="text-right">{{ number_format($grandTotal['premi'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['gaji'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['tsm'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['jam_lembur_jm1'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['jam_lembur_jm2'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['jam_lembur_jm3'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['jam_lembur_jm4'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['selisih_upah'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['upah_lembur'], 0, ',', '.') }}</td>
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

        <!-- Footer Signature Blocks -->
        <div class="report-footer">
            <div style="text-align: center; margin-top: 30px;">
                <p>Bandung Barat, {{ \Carbon\Carbon::now()->format('d F Y') }}</p>
            </div>

            <!-- Caption Row -->
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

            <!-- Nama Penandatangan Row -->
            <div style="margin-top: 70px; display: flex; justify-content: space-around; align-items: flex-end;">
                <div class="signature-block">
                    <p class="signature-name" style="margin: 0; min-height: 20px; padding-bottom: 2px; text-align: center;">{{ $divisiData && $divisiData->vcStaff ? $divisiData->vcStaff : '' }}</p>
                </div>
                <div class="signature-block">
                    <p class="signature-name" style="margin: 0; min-height: 20px; padding-bottom: 2px; text-align: center;">{{ $divisiData && $divisiData->vcKeuangan ? $divisiData->vcKeuangan : '' }}</p>
                </div>
                <div class="signature-block">
                    <p class="signature-name" style="margin: 0; min-height: 20px; padding-bottom: 2px; text-align: center;">{{ $divisiData && $divisiData->vcKabag ? $divisiData->vcKabag : '' }}</p>
                </div>
                <div class="signature-block">
                    <p class="signature-name" style="margin: 0; min-height: 20px; padding-bottom: 2px; text-align: center;">{{ $divisiData && $divisiData->vPPIC ? $divisiData->vPPIC : '' }}</p>
                </div>
                <div class="signature-block">
                    <p class="signature-name" style="margin: 0; min-height: 20px; padding-bottom: 2px; text-align: center;">{{ $divisiData && $divisiData->vcPlantManager ? $divisiData->vcPlantManager : '' }}</p>
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

            <!-- Jabatan Row -->
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






