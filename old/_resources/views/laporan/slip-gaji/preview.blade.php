@extends('layouts.app')

@section('title', 'Preview Slip Gaji')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <h2 class="mb-0">
                    <i class="fas fa-file-invoice me-2"></i>Preview Slip Gaji
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

            <div class="alert alert-info no-print">
                <strong>Periode:</strong> {{ is_string($tanggalAwal) ? \Carbon\Carbon::parse($tanggalAwal)->format('d/m/Y') : $tanggalAwal->format('d/m/Y') }} s/d {{ is_string($tanggalAkhir) ? \Carbon\Carbon::parse($tanggalAkhir)->format('d/m/Y') : $tanggalAkhir->format('d/m/Y') }}
                | <strong>Total:</strong> {{ count($closingsWithPrevious) }} slip gaji
            </div>

            <!-- Slip Gaji Cards - 4 per halaman -->
            <div class="row" style="margin-top: 0;">
                @foreach($closingsWithPrevious as $item)
                @php
                $closing = $item['closing'];
                $periodeSebelumnya = $item['periode_sebelumnya'];

                // Ambil nama divisi dari closing->divisi atau karyawan->divisi
                $namaDivisi = $closing->divisi->vcNamaDivisi ?? $closing->karyawan->divisi->vcNamaDivisi ?? $closing->vcKodeDivisi;

                $jumlahBersih = $closing->decGapok + $closing->decUangMakan + $closing->decTransport +
                $closing->decTotallembur1 + $closing->decTotallembur2 + $closing->decTotallembur3 +
                $closing->decPremi + $closing->decRapel;
                $jumlahPotongan = $closing->decPotonganBPJSKes + $closing->decPotonganBPJSJHT +
                $closing->decPotonganBPJSJP + $closing->decIuranSPN +
                $closing->decPotonganKoperasi + $closing->decPotonganBPR +
                $closing->decPotonganHC + $closing->decPotonganAbsen + $closing->decPotonganLain;
                $jumlahPenerimaan = $jumlahBersih - $jumlahPotongan;
                @endphp
                <div class="col-md-6 col-lg-3 mb-3 slip-container">
                    <div class="card slip-gaji-card" style="border: 2px solid #000; height: 100%;">
                        <div class="card-body p-2" style="font-size: 0.7rem; padding: 6px !important;">
                            <!-- Header Divisi -->
                            <div class="text-center mb-1" style="line-height: 1.3;">
                                <h6 class="mb-1" style="font-size: 0.75rem;"><strong>{{ $namaDivisi }}</strong></h6>
                                <p class="mb-1" style="font-size: 0.55rem;">Jl. Tembokan RT 01/01 Cipeundeuy - Padalarang</p>
                                <p class="mb-1" style="font-size: 0.65rem;"><strong>{{ \Carbon\Carbon::parse($closing->periode)->format('d F Y') }}</strong></p>
                                <p class="mb-1" style="font-size: 0.5rem;">
                                    {{ \Carbon\Carbon::parse($closing->vcPeriodeAwal)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($closing->vcPeriodeAkhir)->format('d/m/Y') }}
                                </p>
                            </div>

                            <!-- Informasi Karyawan -->
                            <div class="mb-1">
                                <div class="row mb-1" style="line-height: 1.4;">
                                    <div class="col-6">Nama:</div>
                                    <div class="col-6">{{ $closing->karyawan->Nama ?? 'N/A' }}</div>
                                </div>
                                <div class="row mb-1" style="line-height: 1.4;">
                                    <div class="col-6">ID:</div>
                                    <div class="col-6">{{ $closing->vcNik }}</div>
                                </div>
                                <div class="row mb-1" style="line-height: 1.4;">
                                    <div class="col-6">Golongan:</div>
                                    <div class="col-6">{{ $closing->vcKodeGolongan }}</div>
                                </div>
                            </div>

                            <hr class="my-1" style="margin-top: 5px; margin-bottom: 5px;">

                            <!-- Group Penerimaan -->
                            <div class="mb-1" style="line-height: 1.3; margin-top: 4px;">
                                <strong style="font-size: 0.7rem;">PENERIMAAN</strong>
                            </div>

                            <!-- Earnings -->
                            <div class="row mb-1" style="line-height: 1.4;">
                                <div class="col-6">Gaji:</div>
                                <div class="col-6 text-end">Rp. {{ number_format($closing->decGapok, 0, ',', '.') }}</div>
                            </div>

                            <div class="row mb-1" style="line-height: 1.4;">
                                <div class="col-6">Kehadiran:</div>
                                <div class="col-6 text-end">{{ $closing->intHadir }} / {{ $closing->intJumlahHari }}</div>
                            </div>

                            @if($closing->intKHL > 0)
                            <div class="row mb-1" style="line-height: 1.4;">
                                <div class="col-6">KHL:</div>
                                <div class="col-6 text-end">{{ $closing->intKHL }}</div>
                            </div>
                            @endif

                            <div class="row mb-1" style="line-height: 1.4;">
                                <div class="col-6">Makan:</div>
                                <div class="col-6 text-end">{{ $closing->intMakan }}x Rp. {{ number_format($closing->decUangMakan, 0, ',', '.') }}</div>
                            </div>

                            <div class="row mb-1" style="line-height: 1.4;">
                                <div class="col-6">Transport:</div>
                                <div class="col-6 text-end">{{ $closing->intTransport }}x Rp. {{ number_format($closing->decTransport, 0, ',', '.') }}</div>
                            </div>

                            @if($closing->decTotallembur1 > 0)
                            <div class="row mb-1" style="line-height: 1.4;">
                                <div class="col-6">Lembur J1:</div>
                                <div class="col-6 text-end">{{ number_format($closing->decJamLemburKerja1, 1) }}j (Rp. {{ number_format($closing->decTotallembur1, 0, ',', '.') }})</div>
                            </div>
                            @endif

                            @if($closing->decTotallembur2 > 0)
                            <div class="row mb-1" style="line-height: 1.4;">
                                <div class="col-6">Lembur J2:</div>
                                <div class="col-6 text-end">{{ number_format($closing->decJamLemburKerja2 + $closing->decJamLemburLibur2, 1) }}j (Rp. {{ number_format($closing->decTotallembur2, 0, ',', '.') }})</div>
                            </div>
                            @endif

                            @if($closing->decTotallembur3 > 0)
                            <div class="row mb-1" style="line-height: 1.4;">
                                <div class="col-6">Lembur J3:</div>
                                <div class="col-6 text-end">{{ number_format($closing->decJamLemburKerja3 + $closing->decJamLemburLibur3, 1) }}j (Rp. {{ number_format($closing->decTotallembur3, 0, ',', '.') }})</div>
                            </div>
                            @endif

                            @if($closing->decPremi > 0)
                            <div class="row mb-1" style="line-height: 1.4;">
                                <div class="col-6">Premi:</div>
                                <div class="col-6 text-end">Rp. {{ number_format($closing->decPremi, 0, ',', '.') }}</div>
                            </div>
                            @endif

                            @if($closing->decRapel > 0)
                            <div class="row mb-1" style="line-height: 1.4;">
                                <div class="col-6">Rapel:</div>
                                <div class="col-6 text-end">Rp. {{ number_format($closing->decRapel, 0, ',', '.') }}</div>
                            </div>
                            @endif

                            <!-- Jumlah Bersih -->
                            <div class="row mb-1" style="line-height: 1.4;">
                                <div class="col-6"><strong>Jml Bersih:</strong></div>
                                <div class="col-6 text-end"><strong>Rp. {{ number_format($jumlahBersih, 0, ',', '.') }}</strong></div>
                            </div>

                            <hr class="my-1" style="margin-top: 5px; margin-bottom: 5px;">

                            <!-- Group Potongan -->
                            <div class="mb-1" style="line-height: 1.3; margin-top: 4px;">
                                <strong style="font-size: 0.7rem;">POTONGAN</strong>
                            </div>

                            <!-- Potongan -->
                            @if($closing->decPotonganBPJSKes > 0)
                            <div class="row mb-1" style="line-height: 1.4;">
                                <div class="col-6">BPJS Kes:</div>
                                <div class="col-6 text-end">Rp. {{ number_format($closing->decPotonganBPJSKes, 0, ',', '.') }}</div>
                            </div>
                            @endif

                            @if($closing->decPotonganBPJSJHT > 0)
                            <div class="row mb-1" style="line-height: 1.4;">
                                <div class="col-6">BPJS JHT:</div>
                                <div class="col-6 text-end">Rp. {{ number_format($closing->decPotonganBPJSJHT, 0, ',', '.') }}</div>
                            </div>
                            @endif

                            @if($closing->decPotonganBPJSJP > 0)
                            <div class="row mb-1" style="line-height: 1.4;">
                                <div class="col-6">BPJS JP:</div>
                                <div class="col-6 text-end">Rp. {{ number_format($closing->decPotonganBPJSJP, 0, ',', '.') }}</div>
                            </div>
                            @endif

                            @if($closing->decIuranSPN > 0)
                            <div class="row mb-1" style="line-height: 1.4;">
                                <div class="col-6">SPN:</div>
                                <div class="col-6 text-end">Rp. {{ number_format($closing->decIuranSPN, 0, ',', '.') }}</div>
                            </div>
                            @endif

                            @if($closing->decPotonganKoperasi > 0)
                            <div class="row mb-1" style="line-height: 1.4;">
                                <div class="col-6">Koperasi:</div>
                                <div class="col-6 text-end">Rp. {{ number_format($closing->decPotonganKoperasi, 0, ',', '.') }}</div>
                            </div>
                            @endif

                            @if($closing->decPotonganBPR > 0)
                            <div class="row mb-1" style="line-height: 1.4;">
                                <div class="col-6">DPLK:</div>
                                <div class="col-6 text-end">Rp. {{ number_format($closing->decPotonganBPR, 0, ',', '.') }}</div>
                            </div>
                            @endif

                            @if($closing->decPotonganHC > 0)
                            <div class="row mb-1" style="line-height: 1.4;">
                                <div class="col-6">Ijin Keluar:</div>
                                <div class="col-6 text-end">Rp. {{ number_format($closing->decPotonganHC, 0, ',', '.') }}</div>
                            </div>
                            @endif

                            @if($closing->decPotonganAbsen > 0)
                            <div class="row mb-1" style="line-height: 1.4;">
                                <div class="col-6">Absen:</div>
                                <div class="col-6 text-end">Rp. {{ number_format($closing->decPotonganAbsen, 0, ',', '.') }}</div>
                            </div>
                            @endif

                            @if($closing->decPotonganLain > 0)
                            <div class="row mb-1" style="line-height: 1.4;">
                                <div class="col-6">Lain-lain:</div>
                                <div class="col-6 text-end">Rp. {{ number_format($closing->decPotonganLain, 0, ',', '.') }}</div>
                            </div>
                            @endif

                            <hr class="my-1" style="margin-top: 5px; margin-bottom: 5px;">

                            <!-- Jumlah Penerimaan -->
                            <div class="row mb-1" style="line-height: 1.4;">
                                <div class="col-6"><strong>TOTAL GAJI:</strong></div>
                                <div class="col-6 text-end"><strong>Rp. {{ number_format($jumlahPenerimaan, 0, ',', '.') }}</strong></div>
                            </div>

                            <!-- Absensi Periode 1 dan 2 - Dipindah ke paling bawah -->
                            <div class="row mt-1" style="border-top: 1px solid #ddd; padding-top: 3px; font-size: 0.55rem; line-height: 1.3; margin-top: 4px;">
                                <div class="col-12">
                                    <strong>Absensi:</strong>
                                    @if($closing->vcClosingKe == '1')
                                    <span class="ms-2">P1: S{{ $closing->intJmlSakit }} I{{ $closing->intJmlIzin }} A{{ $closing->intJmlAlpha }} T{{ $closing->intJmlTelat }} C{{ $closing->intJmlCuti }} HC{{ $closing->intHC }}</span>
                                    @else
                                    <div class="ms-2">
                                        <div>P1: S{{ $periodeSebelumnya->intJmlSakit ?? 0 }} I{{ $periodeSebelumnya->intJmlIzin ?? 0 }} A{{ $periodeSebelumnya->intJmlAlpha ?? 0 }} T{{ $periodeSebelumnya->intJmlTelat ?? 0 }} C{{ $periodeSebelumnya->intJmlCuti ?? 0 }} HC{{ $periodeSebelumnya->intHC ?? 0 }}</div>
                                        <div>P2: S{{ $closing->intJmlSakit }} I{{ $closing->intJmlIzin }} A{{ $closing->intJmlAlpha }} T{{ $closing->intJmlTelat }} C{{ $closing->intJmlCuti }} HC{{ $closing->intHC }}</div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Footer - Penerima dipindah ke paling bawah setelah absensi -->
                            <div class="text-center mt-1" style="border-top: 1px solid #ddd; padding-top: 3px; font-size: 0.55rem; margin-top: 4px;">
                                <small><strong>Penerima:</strong> <u>{{ $closing->karyawan->Nama ?? 'N/A' }}</u></small>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body {
            margin: 0;
            padding: 0;
        }

        .no-print,
        .btn,
        .alert {
            display: none !important;
        }

        .container-fluid {
            padding: 0 !important;
            margin: 0 !important;
        }

        .row {
            margin: 0 !important;
        }

        /* Pastikan tidak ada margin dari header yang disembunyikan */
        .col-12>.no-print {
            display: none !important;
            margin: 0 !important;
            padding: 0 !important;
            height: 0 !important;
        }

        /* Pastikan row slip gaji langsung mulai dari atas */
        .row:not(.no-print) {
            margin-top: 0 !important;
        }

        /* 4 slip per halaman: 2 kolom x 2 baris */
        .slip-container {
            width: 50% !important;
            float: left;
            page-break-inside: avoid;
            break-inside: avoid;
            padding: 3px;
            box-sizing: border-box;
            height: 48vh;
            /* Set tinggi 48% dari viewport height untuk memastikan 2 baris per halaman */
        }

        .slip-gaji-card {
            page-break-inside: avoid;
            break-inside: avoid;
            margin: 0;
            height: 100%;
            width: 100%;
            display: flex;
            flex-direction: column;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            height: 100%;
            padding: 8px !important;
        }

        /* Setiap 2 slip (baris pertama), beri margin bawah */
        .slip-container:nth-child(1),
        .slip-container:nth-child(2) {
            margin-bottom: 10px;
        }

        /* Setiap 4 slip, buat page break */
        .slip-container:nth-child(4n) {
            page-break-after: always;
        }

        /* Clear float untuk baris baru */
        .slip-container:nth-child(odd) {
            clear: left;
        }

        /* Pastikan tidak ada overflow */
        .slip-gaji-card * {
            overflow: visible;
        }
    }

    @media screen {
        .slip-gaji-card {
            min-height: 550px;
        }
    }
</style>
@endsection