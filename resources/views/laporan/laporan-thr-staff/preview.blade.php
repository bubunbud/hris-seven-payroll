@extends('layouts.app')

@section('title', 'Preview Rekap THR Staff')

@section('content')
<style>
    @media print {
        @page {
            size: landscape;
            margin: 2.5cm 1cm 2cm 1cm;
        }

        body {
            font-size: 9pt;
        }

        .no-print {
            display: none;
        }

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
    }

    .thr-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 8pt;
    }

    .thr-table th,
    .thr-table td {
        border: 1px solid #000;
        padding: 3px 4px;
        text-align: left;
    }

    .thr-table th {
        background-color: #f0f0f0;
        font-weight: bold;
        text-align: center;
        padding: 10px 4px;
        vertical-align: middle;
    }

    .thr-table .text-right {
        text-align: right;
    }

    .thr-table .text-center {
        text-align: center;
    }

    .report-header {
        text-align: center;
        margin-bottom: 15px;
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
        margin-top: 30px;
        font-size: 8pt;
    }

    .signature-block {
        display: inline-block;
        width: 30%;
        vertical-align: top;
        margin-right: 2%;
        text-align: center;
    }

    .signature-block:last-child {
        margin-right: 0;
    }

    .signature-line {
        border-top: 1px solid #000;
        margin-top: 0;
        padding-top: 1px;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Action Buttons -->
            <div class="no-print mb-3">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print me-2"></i>Cetak
                </button>
                <a href="{{ route('laporan-thr-staff.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>

            <!-- Report Header -->
            <div class="report-header">
                <h3>DAFTAR TUNJANGAN HARI RAYA (THR) KARYAWAN </h3>
                @if($namaDivisi)
                <h4>{{ strtoupper($namaDivisi) }}</h4>
                @endif
                @if($namaHariRaya)
                <h4>{{ strtoupper($namaHariRaya) }} - {{ $tahun }}</h4>
                @elseif($namaAgama)
                <h4>{{ strtoupper($namaAgama) }} - {{ $tahun }}</h4>
                @else
                <h4>TAHUN {{ $tahun }}</h4>
                @endif
                @if($masaFilter == 'Lebih dari 1 tahun')
                <h4>KARYAWAN STAFF LEBIH DARI 1 TAHUN</h4>
                @elseif($masaFilter == 'Kurang dari 1 tahun')
                <h4>KARYAWAN STAFF KURANG DARI 1 TAHUN</h4>
                @endif
                <h5 style="font-size: 10pt; margin-top: 5px;">Tanggal THR: {{ $tanggalTHR ? $tanggalTHR->format('d/m/Y') : '-' }}</h5>
            </div>

            <!-- Report Content -->
            <div class="report-content">
                @php
                $noUrut = 1;
                @endphp

                <table class="thr-table">
                    <thead>
                        <tr>
                            <th width="3%">No.</th>
                            <th width="7%">NIK</th>
                            <th width="13%">Nama</th>
                            <th width="20%">Divisi</th>
                            <th width="13%">Bagian</th>
                            <th width="7%">Tgl Masuk</th>
                            <th width="9%" class="text-right">MASA KERJA (hari)</th>
                            <th width="9%" class="text-right">MASA KERJA (tahun)</th>
                            <th width="9%" class="text-center">BESAR THR (x gaji)</th>
                            <th width="10%" class="text-right">BESAR THR (Rp.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($closingThrs as $thr)
                        @php
                        $karyawan = $thr->karyawan;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $noUrut++ }}</td>
                            <td>{{ $thr->vcNik }}</td>
                            <td>{{ $karyawan->Nama ?? 'N/A' }}</td>
                            <td>{{ $thr->divisi->vcNamaDivisi ?? $thr->vcKodeDivisi }}</td>
                            <td>{{ $karyawan->bagian->vcNamaBagian ?? ($karyawan->vcKodeBagian ?? '-') }}</td>
                            <td class="text-center">
                                {{ $thr->dtTanggalMasuk ? $thr->dtTanggalMasuk->format('d/m/Y') : '-' }}
                            </td>
                            <td class="text-right">{{ number_format($thr->intMasaKerjaHari, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($thr->decMasaKerjaTahun, 2, ',', '.') }}</td>
                            <td class="text-center">{{ number_format($thr->decXGaji, 1, ',', '.') }}</td>
                            <td class="text-right">-</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Report Footer -->
            <div class="report-footer">
                <p><strong>Keterangan:</strong> Perhitungan THR didasarkan pada tanggal mulai masuk kerja sampai tanggal {{ $tanggalTHR ? $tanggalTHR->format('d F Y') : '-' }}</p>

                <div style="margin-top: 15px; text-align: center;">
                    <p>Bandung Barat, {{ date('d F Y') }}</p>
                </div>

                <div style="margin-top: 30px;">
                    <div class="signature-block">
                        <div style="text-align: center; margin-bottom: 6px;">
                            <strong>Dibuat Oleh</strong>
                            <br><br><br><br><br>
                        </div>
                    </div>
                    <div class="signature-block">
                        <div style="text-align: center; margin-bottom: 6px;">
                            <strong>Mengetahui</strong>
                        </div>
                    </div>
                    <div class="signature-block">
                        <div style="text-align: center; margin-bottom: 6px;">
                            <strong>Menyetujui</strong>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 6px;">
                    <div class="signature-block">
                        <div style="text-align: center; margin-bottom: 5px;">
                            <strong>{{ $namaHrGaManager ?: '-' }}</strong>
                        </div>
                    </div>
                    <div class="signature-block">
                        <div style="text-align: center; margin-bottom: 5px;">
                            <strong>{{ $namaSeniorFinanceManager ?: '-' }}</strong>
                        </div>
                    </div>
                    <div class="signature-block">
                        <div style="text-align: center; margin-bottom: 5px;">
                            <strong>{{ $namaGmBackOffice ?: '-' }}</strong>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 0;">
                    <div class="signature-block">
                        <div class="signature-line">
                            <div style="text-align: center; padding-top: 5px;">HR & GA Manager</div>
                        </div>
                    </div>
                    <div class="signature-block">
                        <div class="signature-line">
                            <div style="text-align: center; padding-top: 5px;">Senior Finance Manager</div>
                        </div>
                    </div>
                    <div class="signature-block">
                        <div class="signature-line">
                            <div style="text-align: center; padding-top: 5px;">GM Back Office</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection












