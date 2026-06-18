<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $closing->karyawan->Nama ?? $closing->vcNik }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body { margin: 0; padding: 20px; }
            .no-print { display: none; }
        }
        .slip-gaji-card {
            border: 2px solid #000;
            max-width: 800px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="no-print mb-3">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </button>
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print
                    </button>
                </div>

                <div class="card slip-gaji-card">
                    <div class="card-body p-4">
                        <!-- Header Perusahaan -->
                        <div class="text-center mb-3">
                            <h4 class="mb-1"><strong>PT. RENALTECH MITRA ABADI</strong></h4>
                            <p class="mb-1 small">Jl. Tembokan RT 01/01 Cipeundeuy - Padalarang</p>
                            <p class="mb-3"><strong>{{ $closing->periode->format('d F Y') }}</strong></p>
                        </div>

                        <!-- Informasi Karyawan -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Nama Karyawan:</strong> {{ $closing->karyawan->Nama ?? 'N/A' }}
                            </div>
                            <div class="col-3">
                                <strong>ID:</strong> {{ $closing->vcNik }}
                            </div>
                            <div class="col-3">
                                <strong>Golongan:</strong> {{ $closing->vcKodeGolongan }}
                            </div>
                        </div>

                        <hr>

                        @php
                            $jumlahBersih = $closing->decGapok + $closing->decUangMakan + $closing->decTransport + 
                                          $closing->decPremi + $closing->decTotallembur1 + $closing->decTotallembur2 + 
                                          $closing->decTotallembur3 + $closing->decRapel;
                            $jumlahPotongan = $closing->decPotonganBPJSKes + $closing->decPotonganBPJSJHT + 
                                            $closing->decPotonganBPJSJP + $closing->decIuranSPN + 
                                            $closing->decPotonganKoperasi + $closing->decPotonganBPR + 
                                            $closing->decPotonganHC + $closing->decPotonganAbsen + $closing->decPotonganLain;
                            $jumlahPenerimaan = $jumlahBersih - $jumlahPotongan;
                        @endphp

                        <!-- Earnings -->
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Gaji:</strong>
                            </div>
                            <div class="col-6 text-end">
                                Rp. {{ number_format($closing->decGapok, 2, ',', '.') }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Jml Kehadiran:</strong>
                            </div>
                            <div class="col-6 text-end">
                                {{ $closing->intHadir }} hari / {{ $closing->intJumlahHari }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Jml KHL:</strong>
                            </div>
                            <div class="col-6 text-end">
                                {{ $closing->intKHL }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Tunj. Makan:</strong>
                            </div>
                            <div class="col-6 text-end">
                                {{ $closing->intMakan }} x {{ number_format($closing->decVarMakan, 0, ',', '.') }},00 
                                Rp. {{ number_format($closing->decUangMakan, 2, ',', '.') }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Tunj. Transport:</strong>
                            </div>
                            <div class="col-6 text-end">
                                {{ $closing->intTransport }} x {{ number_format($closing->decVarTransport, 0, ',', '.') }},00 
                                Rp. {{ number_format($closing->decTransport, 2, ',', '.') }}
                            </div>
                        </div>

                        @if($closing->decTotallembur1 > 0)
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Jam Pertama:</strong>
                            </div>
                            <div class="col-6 text-end">
                                {{ number_format($closing->decJamLemburKerja1, 2) }} jam
                                (Rp. {{ number_format($closing->decTotallembur1, 2, ',', '.') }})
                            </div>
                        </div>
                        @endif

                        @if($closing->decTotallembur2 > 0)
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Jam Kedua:</strong>
                            </div>
                            <div class="col-6 text-end">
                                {{ number_format($closing->decJamLemburKerja2 + $closing->decJamLemburLibur2, 2) }} jam
                                (Rp. {{ number_format($closing->decTotallembur2, 2, ',', '.') }})
                            </div>
                        </div>
                        @endif

                        @if($closing->decTotallembur3 > 0)
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Jam Ketiga dst:</strong>
                            </div>
                            <div class="col-6 text-end">
                                {{ number_format($closing->decJamLemburKerja3 + $closing->decJamLemburLibur3, 2) }} jam
                                (Rp. {{ number_format($closing->decTotallembur3, 2, ',', '.') }})
                            </div>
                        </div>
                        @endif

                        @if($closing->decPremi > 0)
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Premi Hadir:</strong>
                            </div>
                            <div class="col-6 text-end">
                                Rp. {{ number_format($closing->decPremi, 2, ',', '.') }}
                            </div>
                        </div>
                        @endif

                        @if($closing->decRapel > 0)
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Rapel/Selisih Upah:</strong>
                            </div>
                            <div class="col-6 text-end">
                                Rp. {{ number_format($closing->decRapel, 2, ',', '.') }}
                            </div>
                        </div>
                        @endif

                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>S:</strong> {{ $closing->intJmlSakit }}
                            </div>
                            <div class="col-6 text-end">
                                <strong>I:</strong> {{ $closing->intJmlIzin }}
                            </div>
                        </div>

                        <hr>

                        <!-- Jumlah Bersih -->
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Jumlah Bersih:</strong>
                            </div>
                            <div class="col-6 text-end">
                                <u><strong>Rp. {{ number_format($jumlahBersih, 2, ',', '.') }}</strong></u>
                            </div>
                        </div>

                        <!-- Potongan -->
                        @if($closing->decPotonganBPJSKes > 0)
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Pot. BPJS Kes:</strong>
                            </div>
                            <div class="col-6 text-end">
                                Rp. {{ number_format($closing->decPotonganBPJSKes, 2, ',', '.') }}
                            </div>
                        </div>
                        @endif

                        @if($closing->decPotonganBPJSJHT > 0)
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Pot. BPJS JHT:</strong>
                            </div>
                            <div class="col-6 text-end">
                                Rp. {{ number_format($closing->decPotonganBPJSJHT, 2, ',', '.') }}
                            </div>
                        </div>
                        @endif

                        @if($closing->decPotonganBPJSJP > 0)
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Pot. BPJS JP:</strong>
                            </div>
                            <div class="col-6 text-end">
                                Rp. {{ number_format($closing->decPotonganBPJSJP, 2, ',', '.') }}
                            </div>
                        </div>
                        @endif

                        @if($closing->decIuranSPN > 0)
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Pot. Iuran SPN:</strong>
                            </div>
                            <div class="col-6 text-end">
                                Rp. {{ number_format($closing->decIuranSPN, 2, ',', '.') }}
                            </div>
                        </div>
                        @endif

                        @if($closing->decPotonganKoperasi > 0)
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Pot. Koperasi:</strong>
                            </div>
                            <div class="col-6 text-end">
                                Rp. {{ number_format($closing->decPotonganKoperasi, 2, ',', '.') }}
                            </div>
                        </div>
                        @endif

                        @if($closing->decPotonganBPR > 0)
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Pot. DPLK/Asuransi:</strong>
                            </div>
                            <div class="col-6 text-end">
                                Rp. {{ number_format($closing->decPotonganBPR, 2, ',', '.') }}
                            </div>
                        </div>
                        @endif

                        @if($closing->decPotonganHC > 0)
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Pot. Izin Keluar:</strong>
                            </div>
                            <div class="col-6 text-end">
                                Rp. {{ number_format($closing->decPotonganHC, 2, ',', '.') }}
                            </div>
                        </div>
                        @endif

                        @if($closing->decPotonganAbsen > 0)
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Pot. Absen:</strong>
                            </div>
                            <div class="col-6 text-end">
                                Rp. {{ number_format($closing->decPotonganAbsen, 2, ',', '.') }}
                            </div>
                        </div>
                        @endif

                        @if($closing->decPotonganLain > 0)
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Pot. Lain-lain:</strong>
                            </div>
                            <div class="col-6 text-end">
                                Rp. {{ number_format($closing->decPotonganLain, 2, ',', '.') }}
                            </div>
                        </div>
                        @endif

                        @if($closing->vcClosingKe == '2')
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Absensi yg lalu:</strong>
                            </div>
                            <div class="col-6 text-end">
                                I: {{ $closing->intIzinLalu }} | A: {{ $closing->intAlphaLalu }} | 
                                T: {{ $closing->intTelatLalu }} | C: {{ $closing->intCutiLalu }} | 
                                S: {{ $closing->intSakitLalu }} | HC: {{ $closing->intHcLalu }}
                            </div>
                        </div>
                        @endif

                        <hr>

                        <!-- Jumlah Penerimaan -->
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Jumlah Penerimaan:</strong>
                            </div>
                            <div class="col-6 text-end">
                                <u><strong>Rp. {{ number_format($jumlahPenerimaan, 2, ',', '.') }}</strong></u>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

