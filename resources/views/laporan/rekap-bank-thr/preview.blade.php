@extends('layouts.app')

@section('title', 'Preview Rekap Bank THR')

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
        body { font-size: 9pt; }
        .no-print { display: none; }
        .sidebar, .d-lg-none, #toggleSidebar { display: none !important; }
        .app-wrapper { display: block; }
        .content { padding: 0; margin: 0; }
        .page-break { page-break-after: always; }
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
        .print-header h3 { margin: 0; font-size: 12pt; font-weight: bold; }
        .print-header h4 { margin: 2px 0; font-size: 10pt; }
        .report-header { display: block !important; margin-bottom: 15px; margin-top: 0; position: relative; z-index: 1001; background-color: #fff; }
        .report-content { margin-top: 0; padding-top: 0; }
    }

    .print-header { display: none; }

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

    .rekap-table .text-right { text-align: right; }
    .rekap-table .text-center { text-align: center; }
    .report-header { text-align: center; margin-bottom: 10px; }
    .report-header h3 { margin: 0; font-size: 14pt; font-weight: bold; }
    .report-header h4 { margin: 5px 0; font-size: 11pt; }
</style>

<div class="container-fluid">
    <div class="row no-print">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0 no-print">
                    <i class="fas fa-file-alt me-2"></i>Preview Rekap Bank THR
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

    <div class="print-header">
        <h3>REKAP BANK THR</h3>
        <h4>Group: Operator & Security</h4>
        <h4>
            @if($kodeDivisi && $kodeDivisi != 'SEMUA')
            {{ $kodeDivisi }} -> {{ $namaDivisi }}
            @else
            SEMUA DIVISI
            @endif
        </h4>
        <h4>Tanggal THR: {{ \Carbon\Carbon::parse($tanggalThr)->format('d F Y') }}</h4>
    </div>

    <div class="report-content">
        <div class="report-header">
            <h3>REKAP BANK THR</h3>
            <h4>Group: Operator & Security</h4>
            <h4>
                @if($kodeDivisi && $kodeDivisi != 'SEMUA')
                {{ $kodeDivisi }} -> {{ $namaDivisi }}
                @else
                SEMUA DIVISI
                @endif
            </h4>
            <h4>Tanggal THR: {{ \Carbon\Carbon::parse($tanggalThr)->format('d F Y') }}</h4>
        </div>

        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="width: 3%;">No</th>
                    <th style="width: 8%;">NIK</th>
                    <th style="width: 18%;">Nama</th>
                    <th style="width: 10%;">Jenis Kelamin</th>
                    <th style="width: 10%;">Tgl. Lahir</th>
                    <th style="width: 6%;">Tipe ID</th>
                    <th style="width: 12%;">No. KTP</th>
                    <th style="width: 12%;">No. Rekening</th>
                    <th style="width: 10%;">Unit Bisnis</th>
                    <th style="width: 11%;">Nilai THR</th>
                    <th style="width: 11%;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @php
                $no = 1;
                $grandTotalThr = 0;
                @endphp

                @foreach($closingThrs as $ct)
                @php
                $karyawan = $ct->karyawan;
                if (!$karyawan) continue;

                $tglLahir = $karyawan->TTL ? \Carbon\Carbon::parse($karyawan->TTL)->format('d/m/Y') : '';
                $unitBisnis = $ct->vcKodeDivisi ?? '';
                $nilaiThr = $ct->decNilaiTHR ?? 0;
                $grandTotalThr += $nilaiThr;
                @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $ct->vcNik }}</td>
                    <td>{{ $karyawan->Nama ?? '' }}</td>
                    <td class="text-center">{{ $karyawan->Jenis_Kelamin ?? '' }}</td>
                    <td class="text-center">{{ $tglLahir }}</td>
                    <td class="text-center">KTP</td>
                    <td>{{ $karyawan->intNoBadge ?? '' }}</td>
                    <td>{{ $karyawan->intNorek ?? '' }}</td>
                    <td class="text-center">{{ $unitBisnis }}</td>
                    <td class="text-right">{{ number_format($nilaiThr, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($nilaiThr, 0, ',', '.') }}</td>
                </tr>
                @endforeach

                <tr style="background-color: #f0f0f0; font-weight: bold;">
                    <td colspan="9" class="text-center"><strong>GRAND TOTAL</strong></td>
                    <td class="text-right">{{ number_format($grandTotalThr, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotalThr, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        @if(!empty($summaryPerDivisi))
        <div class="mt-4">
            <h5 class="mb-2"><strong>RINCIAN JUMLAH</strong></h5>
            <table class="rekap-table">
                <thead>
                    <tr>
                        <th style="width: 30%;">Unit Bisnis / Divisi</th>
                        <th style="width: 15%;" class="text-center">Jumlah</th>
                        <th style="width: 27%;" class="text-right">Nilai THR</th>
                        <th style="width: 28%;" class="text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $totalJumlahKaryawan = 0;
                    $totalNilaiThr = 0;
                    $totalJumlah = 0;
                    @endphp
                    @foreach($summaryPerDivisi as $s)
                    @php
                    $totalJumlahKaryawan += $s['jumlah_karyawan'];
                    $totalNilaiThr += $s['nilai_thr'];
                    $totalJumlah += $s['jumlah'];
                    @endphp
                    <tr>
                        <td>{{ $s['kode'] }}</td>
                        <td class="text-center">{{ $s['jumlah_karyawan'] }}</td>
                        <td class="text-right">{{ number_format($s['nilai_thr'], 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($s['jumlah'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    <tr style="background-color: #f0f0f0; font-weight: bold;">
                        <td><strong>TOTAL</strong></td>
                        <td class="text-center"><strong>{{ $totalJumlahKaryawan }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalNilaiThr, 0, ',', '.') }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalJumlah, 0, ',', '.') }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function exportToExcel() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("rekap-bank-thr.export-excel") }}';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        const tanggalInput = document.createElement('input');
        tanggalInput.type = 'hidden';
        tanggalInput.name = 'tanggal_thr';
        tanggalInput.value = '{{ $tanggalThr }}';
        form.appendChild(tanggalInput);

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
