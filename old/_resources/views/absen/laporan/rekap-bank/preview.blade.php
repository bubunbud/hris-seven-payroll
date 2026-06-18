@extends('layouts.app')

@section('title', 'Preview Rekap Bank')

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
</style>

<div class="container-fluid">
    <div class="row no-print">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0 no-print">
                    <i class="fas fa-file-alt me-2"></i>Preview Rekap Bank
                </h2>
                <div>
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </button>
                    <button type="button" class="btn btn-success" onclick="exportToExcel()">
                        <i class="fas fa-file-excel me-2"></i>Export Excel
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
        <h3>REKAP BANK</h3>
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
            <h3>REKAP BANK</h3>
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
                    <th style="width: 2%;">No</th>
                    <th style="width: 5%;">NIK</th>
                    <th style="width: 12%;">Nama</th>
                    <th style="width: 5%;">Jenis Kelamin</th>
                    <th style="width: 5%;">Tgl. Lahir</th>
                    <th style="width: 4%;">Tipe ID</th>
                    <th style="width: 8%;">No. KTP</th>
                    <th style="width: 8%;">No. Rekening</th>
                    <th style="width: 4%;">CIF</th>
                    <th style="width: 5%;">Unit Bisnis</th>
                    <th style="width: 6%;">Gaji + Lembur</th>
                    <th style="width: 4%;">Beban TGI</th>
                    <th style="width: 4%;">SIA-EXP</th>
                    <th style="width: 4%;">SIA-Prod</th>
                    <th style="width: 4%;">Beban RMA</th>
                    <th style="width: 4%;">Beban SMU</th>
                    <th style="width: 4%;">ABN JKT</th>
                    <th style="width: 4%;">BPJS Kes</th>
                    <th style="width: 4%;">BPJS Naker</th>
                    <th style="width: 4%;">BPJS Pen</th>
                    <th style="width: 4%;">Iuran SPN</th>
                    <th style="width: 4%;">Pot Lain-lain</th>
                    <th style="width: 4%;">Pot. Koperasi</th>
                    <th style="width: 4%;">DPLK/CAR</th>
                    <th style="width: 5%;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @php
                $no = 1;
                $grandTotalGajiLembur = 0;
                $grandTotalBebanTgi = 0;
                $grandTotalSiaExp = 0;
                $grandTotalSiaProd = 0;
                $grandTotalBebanRma = 0;
                $grandTotalBebanSmu = 0;
                $grandTotalAbnJkt = 0;
                $grandTotalBpjsKes = 0;
                $grandTotalBpjsNaker = 0;
                $grandTotalBpjsPen = 0;
                $grandTotalIuranSpn = 0;
                $grandTotalPotLain = 0;
                $grandTotalPotKoperasi = 0;
                $grandTotalDplk = 0;
                $grandTotalJumlah = 0;
                @endphp

                @foreach($closings as $closing)
                @php
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
                $tglLahir = $karyawan->TTL ? \Carbon\Carbon::parse($karyawan->TTL)->format('d/m/Y') : '';

                // CIF dan Unit Bisnis dari divisi
                $cif = $closing->vcKodeDivisi ?? '';
                $unitBisnis = $closing->vcKodeDivisi ?? '';

                // Grand total
                $grandTotalGajiLembur += $gajiLembur;
                $grandTotalBebanTgi += ($closing->decBebanTgi ?? 0);
                $grandTotalSiaExp += ($closing->decBebanSiaExp ?? 0);
                $grandTotalSiaProd += ($closing->decBebanSiaProd ?? 0);
                $grandTotalBebanRma += ($closing->decBebanRma ?? 0);
                $grandTotalBebanSmu += ($closing->decBebanSmu ?? 0);
                $grandTotalAbnJkt += ($closing->decBebanAbnJkt ?? 0);
                $grandTotalBpjsKes += ($closing->decPotonganBPJSKes ?? 0);
                $grandTotalBpjsNaker += ($closing->decPotonganBPJSJHT ?? 0);
                $grandTotalBpjsPen += ($closing->decPotonganBPJSJP ?? 0);
                $grandTotalIuranSpn += ($closing->decIuranSPN ?? 0);
                $grandTotalPotLain += $potLainLain;
                $grandTotalPotKoperasi += ($closing->decPotonganKoperasi ?? 0);
                $grandTotalDplk += ($closing->decPotonganBPR ?? 0);
                $grandTotalJumlah += $jumlah;
                @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $closing->vcNik }}</td>
                    <td>{{ $karyawan->Nama ?? '' }}</td>
                    <td class="text-center">{{ $karyawan->Jenis_Kelamin ?? '' }}</td>
                    <td class="text-center">{{ $tglLahir }}</td>
                    <td class="text-center">KTP</td>
                    <td>{{ $karyawan->intNoBadge ?? '' }}</td>
                    <td>{{ $karyawan->intNorek ?? '' }}</td>
                    <td class="text-center">{{ $cif }}</td>
                    <td class="text-center">{{ $unitBisnis }}</td>
                    <td class="text-right">{{ number_format($gajiLembur, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($closing->decBebanTgi ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($closing->decBebanSiaExp ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($closing->decBebanSiaProd ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($closing->decBebanRma ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($closing->decBebanSmu ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($closing->decBebanAbnJkt ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($closing->decPotonganBPJSKes ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($closing->decPotonganBPJSJHT ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($closing->decPotonganBPJSJP ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($closing->decIuranSPN ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($potLainLain, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($closing->decPotonganKoperasi ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($closing->decPotonganBPR ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($jumlah, 0, ',', '.') }}</td>
                </tr>
                @endforeach

                <!-- Grand Total -->
                <tr style="background-color: #f0f0f0; font-weight: bold;">
                    <td colspan="10" class="text-center"><strong>GRAND TOTAL</strong></td>
                    <td class="text-right">{{ number_format($grandTotalGajiLembur, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotalBebanTgi, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotalSiaExp, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotalSiaProd, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotalBebanRma, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotalBebanSmu, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotalAbnJkt, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotalBpjsKes, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotalBpjsNaker, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotalBpjsPen, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotalIuranSpn, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotalPotLain, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotalPotKoperasi, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotalDplk, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotalJumlah, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    function exportToExcel() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("rekap-bank.export-excel") }}';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        const periodeInput = document.createElement('input');
        periodeInput.type = 'hidden';
        periodeInput.name = 'periode';
        periodeInput.value = '{{ $tanggalPeriode }}';
        form.appendChild(periodeInput);

        const divisiInput = document.createElement('input');
        divisiInput.type = 'hidden';
        divisiInput.name = 'divisi';
        divisiInput.value = '{{ $kodeDivisi ?? "SEMUA" }}';
        form.appendChild(divisiInput);

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
</script>
@endpush
@endsection