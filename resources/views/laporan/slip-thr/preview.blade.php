@extends('layouts.app')

@section('title', 'Preview Slip THR')

@section('content')
<style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 1cm;
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

        .slips-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5cm;
            width: 100%;
        }

        .slip-container {
            page-break-inside: avoid;
            margin-bottom: 0;
            padding: 10px;
            border: 1px solid #000;
            background: white;
            height: fit-content;
        }
    }

    .slips-wrapper {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        width: 100%;
        max-width: 1000px;
        margin: 0 auto;
    }

    .slip-container {
        width: 100%;
        padding: 15px;
        border: 1px solid #ddd;
        background: white;
        margin-bottom: 15px;
    }

    .slip-header {
        text-align: center;
        margin-bottom: 25px;
        border-bottom: 2px solid #000;
        padding-bottom: 15px;
    }

    .slip-header h2 {
        margin: 0;
        font-size: 14pt;
        font-weight: bold;
        letter-spacing: 1px;
    }

    .slip-header h3 {
        margin: 5px 0 0;
        font-size: 10pt;
        font-weight: bold;
    }

    .slip-header h4 {
        margin: 5px 0 0;
        font-size: 9pt;
        font-weight: normal;
    }

    .slip-body {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .slip-left {
        width: 45%;
    }

    .slip-right {
        width: 50%;
    }

    .slip-field {
        margin-bottom: 8px;
    }

    .slip-field-label {
        font-weight: bold;
        display: inline-block;
        width: 100px;
        font-size: 9pt;
    }

    .slip-field-value {
        display: inline-block;
        font-size: 9pt;
    }

    .slip-financial {
        margin-top: 15px;
        border-top: 1px solid #000;
        padding-top: 10px;
    }

    .slip-financial-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 9pt;
    }

    .slip-financial-label {
        font-weight: bold;
        width: 120px;
    }

    .slip-financial-value {
        text-align: right;
        width: 150px;
        font-weight: bold;
    }

    .slip-total {
        border-top: 2px solid #000;
        padding-top: 8px;
        margin-top: 8px;
        font-size: 10pt;
        font-weight: bold;
    }

    .slip-recipient {
        margin-top: 20px;
        text-align: center;
    }

    .slip-recipient-label {
        font-weight: bold;
        margin-bottom: 30px;
        font-size: 10pt;
    }

    .slip-recipient-name {
        font-size: 11pt;
        font-weight: bold;
        text-decoration: underline;
    }

    .slip-keterangan {
        margin-top: 25px;
        text-align: center;
        font-size: 9pt;
        line-height: 1.6;
        padding: 10px;
        border-top: 1px solid #ddd;
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
                <a href="{{ route('slip-thr.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>

            <!-- Slip THR -->
            <div class="slips-wrapper">
            @foreach($closingThrs as $thr)
            @php
            $karyawan = $thr->karyawan;
            $divisi = $thr->divisi;
            @endphp
            <div class="slip-container">
                <!-- Header -->
                <div class="slip-header">
                    <h2>SLIP PENERIMAAN THR {{ $tahun }}</h2>
                    @if($divisi)
                    <h3 style="font-weight: bold;">{{ strtoupper($divisi->vcNamaDivisi) }}</h3>
                    @else
                    <h3 style="font-weight: bold;">-</h3>
                    @endif
                    @if(!empty($thr->namaHariRaya))
                    <h4 style="font-size: 9pt; margin-top: 5px;">{{ strtoupper($thr->namaHariRaya) }}</h4>
                    @endif
                </div>

                <!-- Body -->
                <div class="slip-body">
                    <div class="slip-left">
                        <div class="slip-field">
                            <span class="slip-field-label">NIK</span>
                            <span class="slip-field-value">: {{ $thr->vcNik }}</span>
                        </div>
                        <div class="slip-field">
                            <span class="slip-field-label">Nama</span>
                            <span class="slip-field-value">: {{ $karyawan->Nama ?? 'N/A' }}</span>
                        </div>
                        <div class="slip-field">
                            <span class="slip-field-label">Status/Gol.</span>
                            <span class="slip-field-value">: {{ $thr->vcGolongan ?? '-' }}</span>
                        </div>
                        <div class="slip-field">
                            <span class="slip-field-label">Masa Kerja</span>
                            <span class="slip-field-value">: {{ $thr->vcMasaKerja ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="slip-right">
                        <div class="slip-financial">
                            <div class="slip-financial-row">
                                <span class="slip-financial-label">Gaji</span>
                                <span class="slip-financial-value">
                                    @if($thr->decGajiPokok !== null)
                                        {{ number_format($thr->decGajiPokok, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <div class="slip-financial-row">
                                <span class="slip-financial-label">Besar THR</span>
                                <span class="slip-financial-value">
                                    @if($thr->decNilaiTHR !== null)
                                        Rp. {{ number_format($thr->decNilaiTHR, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <div class="slip-financial-row">
                                <span class="slip-financial-label">Lain-Lain</span>
                                <span class="slip-financial-value">Rp. </span>
                            </div>
                            <div class="slip-financial-row slip-total">
                                <span class="slip-financial-label">Total</span>
                                <span class="slip-financial-value">
                                    @if($thr->decNilaiTHR !== null)
                                        {{ number_format($thr->decNilaiTHR, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Penerima -->
                <div class="slip-recipient">
                    <div class="slip-recipient-label">Penerima</div>
                    <div class="slip-recipient-name">{{ $karyawan->Nama ?? 'N/A' }}</div>
                </div>

                <!-- Keterangan -->
                <div class="slip-keterangan">
                    @if(!empty($thr->vcKeterangan))
                        {{ $thr->vcKeterangan }}
                    @else
                        -
                    @endif
                </div>
            </div>
            @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

