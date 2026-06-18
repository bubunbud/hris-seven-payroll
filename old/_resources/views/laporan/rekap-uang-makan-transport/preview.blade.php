@extends('layouts.app')

@section('title', 'Preview Rekap Uang Makan & Transport')

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
                    <i class="fas fa-file-alt me-2"></i>Preview Rekap Uang Makan & Transport
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
        <h3>DAFTAR UANG MAKAN & TRANSPORT KARYAWAN</h3>
        <h4>
            @if($kodeDivisi && $kodeDivisi != 'SEMUA')
            {{ $kodeDivisi }} -> {{ $namaDivisi }}
            @else
            SEMUA DIVISI
            @endif
        </h4>
        <h4>Periode {{ \Carbon\Carbon::parse($tanggalAwal)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($tanggalAkhir)->format('d F Y') }}</h4>
    </div>

    <!-- Report Content -->
    <div class="report-content">
        <!-- Report Header -->
        <div class="report-header">
            <h3>DAFTAR UANG MAKAN & TRANSPORT KARYAWAN</h3>
            <h4>
                @if($kodeDivisi && $kodeDivisi != 'SEMUA')
                {{ $kodeDivisi }} -> {{ $namaDivisi }}
                @else
                SEMUA DIVISI
                @endif
            </h4>
            <h4>Periode {{ \Carbon\Carbon::parse($tanggalAwal)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($tanggalAkhir)->format('d F Y') }}</h4>
        </div>

        <!-- Main Table -->
        <table class="rekap-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 3%;">No</th>
                    <th rowspan="2" style="width: 6%;">NIK</th>
                    <th rowspan="2" style="width: 15%;">NAMA PEGAWAI</th>
                    <th rowspan="2" style="width: 4%;">GOL</th>
                    <th colspan="3" style="width: 12%;">Tunjangan Makan</th>
                    <th colspan="3" style="width: 12%;">Tunjangan Transport</th>
                    <th colspan="9" style="width: 27%;">Hari Kerja</th>
                    <th rowspan="2" style="width: 8%;">Jumlah Penerimaan</th>
                    <th rowspan="2" style="width: 10%;">Keterangan</th>
                </tr>
                <tr>
                    <th style="width: 4%;">HK</th>
                    <th style="width: 4%;">HL</th>
                    <th style="width: 4%;">T.M.</th>
                    <th style="width: 4%;">HK</th>
                    <th style="width: 4%;">HL</th>
                    <th style="width: 4%;">T.Tr.</th>
                    <th style="width: 3%;">A</th>
                    <th style="width: 3%;">C</th>
                    <th style="width: 3%;">IR</th>
                    <th style="width: 3%;">I</th>
                    <th style="width: 3%;">H</th>
                    <th style="width: 3%;">T</th>
                    <th style="width: 3%;">S</th>
                    <th style="width: 3%;">H/C</th>
                    <th style="width: 3%;">IND</th>
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
                    <td colspan="21"><strong>Dept. {{ $departemen['nama'] }}</strong></td>
                </tr>

                @foreach($departemen['bagians'] as $bagianKode => $bagian)
                @php
                $urutBagian = 0;
                @endphp

                <!-- Bagian Header -->
                <tr class="bold">
                    <td colspan="3" style="padding-left: 15px;"><strong>Bagian {{ $bagian['nama'] }}</strong></td>
                    <td colspan="18"></td>
                </tr>

                <!-- Karyawan dalam Bagian -->
                @foreach($bagian['karyawans'] as $closing)
                @php
                $urutBagian++;
                $urutGlobal++;

                $karyawan = $closing->karyawan;
                $jumlahPenerimaan = ($closing->decUangMakan ?? 0) + ($closing->decTransport ?? 0);
                @endphp
                <tr>
                    <td class="text-center">{{ $urutGlobal }}</td>
                    <td>{{ $closing->vcNik }}</td>
                    <td>{{ $karyawan->Nama ?? 'N/A' }}</td>
                    <td class="text-center">{{ $closing->vcKodeGolongan }}</td>
                    <td class="text-center">{{ $closing->intMakanKerja ?? 0 }}</td>
                    <td class="text-center">{{ $closing->intMakanLibur ?? 0 }}</td>
                    <td class="text-right">{{ number_format($closing->decUangMakan ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $closing->intTransportKerja ?? 0 }}</td>
                    <td class="text-center">{{ $closing->intTransportLibur ?? 0 }}</td>
                    <td class="text-right">{{ number_format($closing->decTransport ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $closing->intJmlAlpha ?? 0 }}</td>
                    <td class="text-center">{{ $closing->intJmlCuti ?? 0 }}</td>
                    <td class="text-center">{{ $closing->intJmlIzinR ?? 0 }}</td>
                    <td class="text-center">{{ $closing->intJmlIzin ?? 0 }}</td>
                    <td class="text-center">{{ $closing->intHadir ?? 0 }}</td>
                    <td class="text-center">{{ $closing->intJmlTelat ?? 0 }}</td>
                    <td class="text-center">{{ $closing->intJmlSakit ?? 0 }}</td>
                    <td class="text-center">{{ $closing->intHC ?? 0 }}</td>
                    <td class="text-center">0</td>
                    <td class="text-right">{{ number_format($jumlahPenerimaan, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                @endforeach

                <!-- Total Bagian -->
                @php
                $bagianTotal = $bagian['total'];
                @endphp
                <tr class="bold">
                    <td colspan="3" style="padding-left: 15px;"><strong>TOTAL {{ $bagian['nama'] }}</strong></td>
                    <td></td>
                    <td class="text-center">{{ $bagianTotal['makan_kerja'] }}</td>
                    <td class="text-center">{{ $bagianTotal['makan_libur'] }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['uang_makan'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $bagianTotal['transport_kerja'] }}</td>
                    <td class="text-center">{{ $bagianTotal['transport_libur'] }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['uang_transport'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $bagianTotal['alpha'] }}</td>
                    <td class="text-center">{{ $bagianTotal['cuti'] }}</td>
                    <td class="text-center">{{ $bagianTotal['izin_resmi'] }}</td>
                    <td class="text-center">{{ $bagianTotal['izin'] }}</td>
                    <td class="text-center">{{ $bagianTotal['hadir'] }}</td>
                    <td class="text-center">{{ $bagianTotal['telat'] }}</td>
                    <td class="text-center">{{ $bagianTotal['sakit'] }}</td>
                    <td class="text-center">{{ $bagianTotal['hc'] }}</td>
                    <td class="text-center">{{ $bagianTotal['indisipliner'] }}</td>
                    <td class="text-right">{{ number_format($bagianTotal['jumlah_penerimaan'], 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                @endforeach

                <!-- Total Departemen -->
                @php
                $deptTotal = $departemen['total'];
                @endphp
                <tr class="bold" style="background-color: #f0f0f0;">
                    <td colspan="3"><strong>TOTAL DEPT. {{ $departemen['nama'] }}</strong></td>
                    <td></td>
                    <td class="text-center">{{ $deptTotal['makan_kerja'] }}</td>
                    <td class="text-center">{{ $deptTotal['makan_libur'] }}</td>
                    <td class="text-right">{{ number_format($deptTotal['uang_makan'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $deptTotal['transport_kerja'] }}</td>
                    <td class="text-center">{{ $deptTotal['transport_libur'] }}</td>
                    <td class="text-right">{{ number_format($deptTotal['uang_transport'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $deptTotal['alpha'] }}</td>
                    <td class="text-center">{{ $deptTotal['cuti'] }}</td>
                    <td class="text-center">{{ $deptTotal['izin_resmi'] }}</td>
                    <td class="text-center">{{ $deptTotal['izin'] }}</td>
                    <td class="text-center">{{ $deptTotal['hadir'] }}</td>
                    <td class="text-center">{{ $deptTotal['telat'] }}</td>
                    <td class="text-center">{{ $deptTotal['sakit'] }}</td>
                    <td class="text-center">{{ $deptTotal['hc'] }}</td>
                    <td class="text-center">{{ $deptTotal['indisipliner'] }}</td>
                    <td class="text-right">{{ number_format($deptTotal['jumlah_penerimaan'], 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                @endforeach

                <!-- Total Divisi -->
                @php
                $divisiTotal = $divisi['total'];
                @endphp
                <tr class="bold" style="background-color: #d0d0d0;">
                    <td colspan="3"><strong>TOTAL DIVISI {{ $divisi['nama'] }}</strong></td>
                    <td></td>
                    <td class="text-center">{{ $divisiTotal['makan_kerja'] }}</td>
                    <td class="text-center">{{ $divisiTotal['makan_libur'] }}</td>
                    <td class="text-right">{{ number_format($divisiTotal['uang_makan'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $divisiTotal['transport_kerja'] }}</td>
                    <td class="text-center">{{ $divisiTotal['transport_libur'] }}</td>
                    <td class="text-right">{{ number_format($divisiTotal['uang_transport'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $divisiTotal['alpha'] }}</td>
                    <td class="text-center">{{ $divisiTotal['cuti'] }}</td>
                    <td class="text-center">{{ $divisiTotal['izin_resmi'] }}</td>
                    <td class="text-center">{{ $divisiTotal['izin'] }}</td>
                    <td class="text-center">{{ $divisiTotal['hadir'] }}</td>
                    <td class="text-center">{{ $divisiTotal['telat'] }}</td>
                    <td class="text-center">{{ $divisiTotal['sakit'] }}</td>
                    <td class="text-center">{{ $divisiTotal['hc'] }}</td>
                    <td class="text-center">{{ $divisiTotal['indisipliner'] }}</td>
                    <td class="text-right">{{ number_format($divisiTotal['jumlah_penerimaan'], 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                @endforeach

                <!-- Grand Total -->
                <tr class="bold" style="background-color: #c0c0c0;">
                    <td colspan="3"><strong>GRAND TOTAL</strong></td>
                    <td></td>
                    <td class="text-center">{{ $grandTotal['makan_kerja'] }}</td>
                    <td class="text-center">{{ $grandTotal['makan_libur'] }}</td>
                    <td class="text-right">{{ number_format($grandTotal['uang_makan'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $grandTotal['transport_kerja'] }}</td>
                    <td class="text-center">{{ $grandTotal['transport_libur'] }}</td>
                    <td class="text-right">{{ number_format($grandTotal['uang_transport'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $grandTotal['alpha'] }}</td>
                    <td class="text-center">{{ $grandTotal['cuti'] }}</td>
                    <td class="text-center">{{ $grandTotal['izin_resmi'] }}</td>
                    <td class="text-center">{{ $grandTotal['izin'] }}</td>
                    <td class="text-center">{{ $grandTotal['hadir'] }}</td>
                    <td class="text-center">{{ $grandTotal['telat'] }}</td>
                    <td class="text-center">{{ $grandTotal['sakit'] }}</td>
                    <td class="text-center">{{ $grandTotal['hc'] }}</td>
                    <td class="text-center">{{ $grandTotal['indisipliner'] }}</td>
                    <td class="text-right">{{ number_format($grandTotal['jumlah_penerimaan'], 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <!-- Keterangan -->
        <div style="margin-top: 20px; font-size: 8pt;">
            <p><strong>KETERANGAN:</strong></p>
            <p style="margin: 0;">A = Alpa/Mangkir, C = Cuti, D = Dinas, I = Ijin, H = Hadir, T = Terlambat, S = Sakit, H/C = Hadir/Cepat Pulang, IND = Indisipliner</p>
            <p style="margin: 0;">JML = Jumlah, H.K = Hari kerja, H.L = Hari Libur, T.M = Tunjangan Makan, T.Tr = Tunjangan Transport</p>
        </div>

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






