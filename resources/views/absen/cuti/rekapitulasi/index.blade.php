@extends('layouts.app')

@section('title', 'Rekapitulasi Cuti')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-3 no-print">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Rekapitulasi Cuti</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('rekapitulasi-cuti.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="dari_tanggal" class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" id="dari_tanggal" name="dari_tanggal" value="{{ $startDate }}">
                            </div>
                            <div class="col-md-3">
                                <label for="sampai_tanggal" class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" id="sampai_tanggal" name="sampai_tanggal" value="{{ $endDate }}">
                            </div>
                            <div class="col-md-3">
                                <label for="divisi" class="form-label">Divisi</label>
                                <select class="form-select" id="divisi" name="divisi">
                                    <option value="">Semua Divisi</option>
                                    @foreach($divisis as $div)
                                    <option value="{{ $div->vcKodeDivisi }}" {{ $divisiId == $div->vcKodeDivisi ? 'selected' : '' }}>{{ $div->vcNamaDivisi }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="departemen" class="form-label">Departemen</label>
                                <select class="form-select" id="departemen" name="departemen">
                                    <option value="">Semua Departemen</option>
                                    @foreach($departemens as $dept)
                                    <option value="{{ $dept->vcKodeDept }}" {{ $departemenId == $dept->vcKodeDept ? 'selected' : '' }}>{{ $dept->vcNamaDept }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="group_pegawai" class="form-label">Group Pegawai</label>
                                <select class="form-select" id="group_pegawai" name="group_pegawai">
                                    <option value="">Semua Group</option>
                                    @foreach($groups as $group)
                                    <option value="{{ $group }}" {{ $groupPegawai == $group ? 'selected' : '' }}>{{ $group }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary shadow-sm">
                                    <i class="fas fa-search me-2"></i>Preview
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if(count($rekapitulasiData) > 0)
            <div class="card shadow-sm print-area">
                <div class="card-body">
                    <div class="mb-3 text-center print-header">
                        <h4 class="mb-1"><strong>REKAPITULASI CUTI KARYAWAN</strong></h4>
                        <h5 class="mb-1">ABN GROUP</h5>
                        <p class="mb-1">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</p>
                    </div>
                    <div class="mb-3 text-end no-print">
                        <button type="button" class="btn btn-primary shadow-sm me-2" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Cetak/Print
                        </button>
                        <a href="{{ route('rekapitulasi-cuti.export', ['dari_tanggal' => $startDate, 'sampai_tanggal' => $endDate, 'divisi' => $divisiId, 'departemen' => $departemenId, 'group_pegawai' => $groupPegawai]) }}"
                            class="btn btn-success shadow-sm">
                            <i class="fas fa-file-excel me-2"></i>Export Excel
                        </a>
                    </div>

                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-bordered table-sm" style="font-size: 0.85rem;">
                            <thead class="table-light" style="position: sticky; top: 0; z-index: 10; background-color: #f8f9fa;">
                                <tr>
                                    <th style="text-align: center; vertical-align: middle; width: 3%;">No.</th>
                                    <th style="text-align: center; vertical-align: middle; width: 7%;">NIK</th>
                                    <th style="text-align: center; vertical-align: middle; width: 15%;">Nama</th>
                                    <th style="text-align: center; vertical-align: middle; width: 12%;">Bisnis Unit/Divisi</th>
                                    <th style="text-align: center; vertical-align: middle; width: 12%;">Departemen</th>
                                    <th style="text-align: center; vertical-align: middle; width: 12%;">Bagian</th>
                                    <th style="text-align: center; vertical-align: middle; width: 8%;">Cuti Tahun Lalu</th>
                                    <th style="text-align: center; vertical-align: middle; width: 8%;">Cuti Tahun Ini</th>
                                    <th style="text-align: center; vertical-align: middle; width: 8%;">Cuti Pribadi</th>
                                    <th style="text-align: center; vertical-align: middle; width: 8%;">Cuti Bersama</th>
                                    <th style="text-align: center; vertical-align: middle; width: 8%;">Saldo Cuti</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rekapitulasiData as $data)
                                <tr>
                                    <td style="text-align: center;">{{ $data['no'] }}</td>
                                    <td style="text-align: center;"><strong>{{ $data['nik'] }}</strong></td>
                                    <td>{{ $data['nama'] }}</td>
                                    <td style="text-align: center;">{{ $data['divisi'] }}</td>
                                    <td style="text-align: center;">{{ $data['departemen'] }}</td>
                                    <td style="text-align: center;">{{ $data['bagian'] }}</td>
                                    <td style="text-align: center;">{{ $data['cuti_tahun_lalu'] }}</td>
                                    <td style="text-align: center;">{{ $data['cuti_tahun_ini'] }}</td>
                                    <td style="text-align: center;">{{ $data['cuti_pribadi'] }}</td>
                                    <td style="text-align: center;">{{ $data['cuti_bersama'] }}</td>
                                    <td style="text-align: center;"><strong>{{ $data['saldo_cuti'] }}</strong></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-3 print-keterangan">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light no-print">
                                    <strong>Keterangan:</strong>
                                </div>
                                <div class="card-body" style="font-size: 0.85rem;">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <p class="mb-1"><strong>Cuti Tahun Lalu:</strong> Saldo cuti tahun lalu yang tersedia.</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p class="mb-1"><strong>Cuti Tahun Ini:</strong> Saldo cuti tahun ini yang tersedia.</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p class="mb-1"><strong>Cuti Pribadi:</strong> Jumlah hari cuti tahunan (C010) yang digunakan dalam periode yang dipilih.</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p class="mb-1"><strong>Cuti Bersama:</strong> Jumlah hari cuti bersama yang berlaku dalam periode yang dipilih (sama untuk semua karyawan).</p>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            <p class="mb-1"><strong>Saldo Cuti:</strong> Sisa saldo cuti yang tersedia untuk karyawan pada tahun periode yang dipilih (setelah dikurangi penggunaan).</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Tidak ada data untuk ditampilkan. Silakan pilih filter yang berbeda.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table th {
        font-weight: 600;
        border: 1px solid #dee2e6;
    }

    .table td {
        border: 1px solid #dee2e6;
        padding: 4px 6px;
    }

    .table-bordered {
        border: 2px solid #333;
    }

    .table-bordered th,
    .table-bordered td {
        border: 1px solid #333;
    }

    /* Print Styles */
    @media print {
        /* Set page size dan orientation ke A4 Landscape */
        @page {
            size: A4 landscape;
            margin: 0.5cm;
        }

        /* Sembunyikan elemen yang tidak perlu saat print */
        body * {
            visibility: hidden;
        }

        /* Tampilkan hanya area yang akan di-print */
        .print-area,
        .print-area * {
            visibility: visible;
        }

        .print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        /* Sembunyikan tombol dan filter saat print */
        .no-print {
            display: none !important;
        }

        /* Styling untuk print */
        body {
            margin: 0;
            padding: 0;
        }

        .card {
            border: none;
            box-shadow: none;
            margin: 0;
            padding: 0;
        }

        .card-body {
            padding: 10px;
        }

        .card-header {
            background-color: #fff !important;
            color: #000 !important;
            border-bottom: 2px solid #000;
            padding: 8px;
        }

        /* Header untuk print */
        .print-header {
            text-align: center;
            margin-bottom: 10px;
            padding: 5px 0;
        }

        .print-header h4 {
            font-size: 12pt;
            font-weight: bold;
            margin: 3px 0;
        }

        .print-header h5 {
            font-size: 11pt;
            margin: 3px 0;
        }

        .print-header p {
            font-size: 9pt;
            margin: 3px 0;
        }

        /* Table responsive untuk print - fit to page */
        .table-responsive {
            overflow: visible !important;
            max-height: none !important;
        }

        .table {
            font-size: 7pt;
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin: 0;
        }

        /* Kolom dengan width proporsional untuk fit semua kolom */
        .table th,
        .table td {
            padding: 3px 2px;
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            overflow: hidden;
        }

        /* Width untuk setiap kolom - disesuaikan untuk fit 11 kolom */
        .table th:nth-child(1),
        .table td:nth-child(1) {
            width: 3%;
        }

        .table th:nth-child(2),
        .table td:nth-child(2) {
            width: 6%;
        }

        .table th:nth-child(3),
        .table td:nth-child(3) {
            width: 12%;
        }

        .table th:nth-child(4),
        .table td:nth-child(4) {
            width: 10%;
        }

        .table th:nth-child(5),
        .table td:nth-child(5) {
            width: 10%;
        }

        .table th:nth-child(6),
        .table td:nth-child(6) {
            width: 10%;
        }

        .table th:nth-child(7),
        .table td:nth-child(7) {
            width: 8%;
        }

        .table th:nth-child(8),
        .table td:nth-child(8) {
            width: 8%;
        }

        .table th:nth-child(9),
        .table td:nth-child(9) {
            width: 8%;
        }

        .table th:nth-child(10),
        .table td:nth-child(10) {
            width: 8%;
        }

        .table th:nth-child(11),
        .table td:nth-child(11) {
            width: 8%;
        }

        .table th {
            background-color: #f0f0f0 !important;
            color: #000 !important;
            font-weight: bold;
        }

        .table-bordered {
            border: 2px solid #000;
        }

        /* Keterangan untuk print */
        .print-keterangan {
            margin-top: 10px;
            font-size: 7pt;
            page-break-inside: avoid;
        }

        .print-keterangan .card-body {
            padding: 5px;
        }

        .print-keterangan p {
            margin: 2px 0;
            font-size: 7pt;
        }

        /* Page break */
        .page-break {
            page-break-after: always;
        }

        /* Pastikan tabel tidak terpotong */
        table {
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        /* Pastikan header tabel muncul di setiap halaman */
        thead tr {
            page-break-after: avoid;
            page-break-inside: avoid;
        }
    }
</style>
@endpush


