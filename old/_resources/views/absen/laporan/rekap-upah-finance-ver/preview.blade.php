@extends('layouts.app')

@section('title', 'Preview Rekap Upah Finance Ver')

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
</style>

<div class="container-fluid">
    <div class="row no-print">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0 no-print">
                    <i class="fas fa-file-alt me-2"></i>Preview Rekap Upah Finance Ver
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

    <!-- Print Header -->
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
                    <th style="width: 2%;">No</th>
                    <th style="width: 5%;">NIK</th>
                    <th style="width: 12%;">NAMA</th>
                    <th style="width: 4%;">GOL</th>
                    <th style="width: 5%;">PREMI</th>
                    <th style="width: 6%;">GAJI</th>
                    <th style="width: 4%;">TSM</th>
                    <th style="width: 3%;">JM1</th>
                    <th style="width: 3%;">JM2</th>
                    <th style="width: 3%;">JM3</th>
                    <th style="width: 5%;">SELISIH UPAH</th>
                    <th style="width: 6%;">LEMBUR</th>
                    <th style="width: 6%;">Uang Makan + Transport</th>
                    <th style="width: 4%;">BPJS KES</th>
                    <th style="width: 4%;">BPJS NAKER</th>
                    <th style="width: 4%;">BPJS PENSIUN</th>
                    <th style="width: 4%;">TDK HDR/HC</th>
                    <th style="width: 4%;">KOPERASI</th>
                    <th style="width: 4%;">POT. SPN</th>
                    <th style="width: 4%;">POT. DPLK</th>
                    <th style="width: 4%;">POT. LAIN-LAIN</th>
                    <th style="width: 6%;">PENERIMAAN</th>
                    <th style="width: 6%;">TAKEHOMEPAY</th>
                </tr>
            </thead>
            <tbody>
                @php
                $no = 1;
                @endphp

                @foreach($groupedData as $divisiKode => $divisiData)
                    @foreach($divisiData['departemens'] as $deptKode => $deptData)
                        <!-- Header Departemen -->
                        <tr class="bold" style="background-color: #d0d0d0;">
                            <td colspan="23" class="text-left"><strong>Dept. {{ $deptData['nama'] }}</strong></td>
                        </tr>
                        @foreach($deptData['bagians'] as $bagianKode => $bagianData)
                            @if(count($bagianData['closings']) > 0)
                                <!-- Header Bagian -->
                                <tr class="bold" style="background-color: #e0e0e0;">
                                    <td colspan="23" class="text-left"><strong>Bagia {{ $bagianData['nama'] }}</strong></td>
                                </tr>
                                @foreach($bagianData['closings'] as $closing)
                                    @php
                                    $karyawan = $closing->karyawan;
                                    if (!$karyawan) continue;

                                    // Mapping field sesuai ketentuan
                                    $premi = $closing->decPremi ?? 0;
                                    $gaji = $closing->decGapok ?? 0;
                                    $selisihUpah = $closing->decRapel ?? 0;
                                    // JM1, JM2, JM3 mengikuti definisi:
                                    // JM1 = jam ke-1 lembur hari kerja normal
                                    // JM2 = jam ke-2 lembur hari kerja normal + jam ke-2 lembur hari libur
                                    // JM3 = jam ke-3 lembur hari libur
                                    $jm1 = $closing->decJamLemburKerja1 ?? 0;
                                    $jm2 = ($closing->decJamLemburKerja2 ?? 0) + ($closing->decJamLemburLibur2 ?? 0);
                                    $jm3 = $closing->decJamLemburLibur3 ?? 0;
                                    $lembur = ($closing->decTotallembur1 ?? 0) + 
                                              ($closing->decTotallembur2 ?? 0) + 
                                              ($closing->decTotallembur3 ?? 0);
                                    $uangMakanTransport = ($closing->decUangMakan ?? 0) + ($closing->decTransport ?? 0);
                                    
                                    // Gunakan decPotonganBPJS* karena field ini yang selalu terisi di database
                                    // decBpjs* mungkin tidak terisi di beberapa data lama
                                    $bpjsKes = $closing->decPotonganBPJSKes ?? $closing->decBpjsKesehatan ?? 0;
                                    $bpjsNaker = $closing->decPotonganBPJSJHT ?? $closing->decBpjsNaker ?? 0;
                                    $bpjsPensiun = $closing->decPotonganBPJSJP ?? $closing->decBpjsPensiun ?? 0;
                                    $tdkHdrHc = ($closing->decPotonganAbsen ?? 0) + ($closing->decPotonganHC ?? 0);
                                    $koperasi = $closing->decPotonganKoperasi ?? 0;
                                    $potSpn = $closing->decIuranSPN ?? 0;
                                    $potDplk = $closing->decPotonganBPR ?? 0;
                                    $potLainLain = $closing->decPotonganLain ?? 0;

                                    // Penerimaan = Premi + Gaji + Selisih Upah + Lembur + Tot Uang Makan & Transport
                                    $penerimaan = $premi + $gaji + $selisihUpah + $lembur + $uangMakanTransport;

                                    // TAKEHOMEPAY = Penerimaan - (BPJS KES + BPJS NAKER + BPJS PENSIUN + TDK HDR/HC + KOPERASI + POT. SPN + POT. DPLK + POT. LAIN-LAIN)
                                    $takehomepay = $penerimaan - ($bpjsKes + $bpjsNaker + $bpjsPensiun + $tdkHdrHc + $koperasi + $potSpn + $potDplk + $potLainLain);
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $no++ }}</td>
                                        <td>{{ $closing->vcNik }}</td>
                                        <td>{{ $karyawan->Nama ?? '' }}</td>
                                        <td class="text-center">{{ $closing->vcKodeGolongan ?? '' }}</td>
                                        <td class="text-right">{{ number_format($premi, 0, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($gaji, 0, ',', '.') }}</td>
                                        <td class="text-right">0</td>
                                        <td class="text-right">{{ number_format($jm1, 1, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($jm2, 1, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($jm3, 1, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($selisihUpah, 0, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($lembur, 0, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($uangMakanTransport, 0, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($bpjsKes, 0, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($bpjsNaker, 0, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($bpjsPensiun, 0, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($tdkHdrHc, 0, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($koperasi, 0, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($potSpn, 0, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($potDplk, 0, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($potLainLain, 0, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($penerimaan, 0, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($takehomepay, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach

                                <!-- Total Bagian -->
                                @php
                                $bagianTotal = $bagianData['total'];
                                @endphp
                                <tr class="bold" style="background-color: #f0f0f0;">
                                    <td colspan="4" class="text-center"><strong>Total Bag. {{ $bagianData['nama'] }}</strong></td>
                                    <td class="text-right">{{ number_format($bagianTotal['premi'], 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($bagianTotal['gaji'], 0, ',', '.') }}</td>
                                    <td class="text-right">0</td>
                                    <td class="text-right">{{ number_format($bagianTotal['jm1'], 1, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($bagianTotal['jm2'], 1, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($bagianTotal['jm3'], 1, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($bagianTotal['selisih_upah'], 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($bagianTotal['lembur'], 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($bagianTotal['uang_makan_transport'], 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($bagianTotal['bpjs_kes'], 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($bagianTotal['bpjs_naker'], 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($bagianTotal['bpjs_pensiun'], 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($bagianTotal['tdk_hdr_hc'], 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($bagianTotal['koperasi'], 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($bagianTotal['pot_spn'], 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($bagianTotal['pot_dplk'], 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($bagianTotal['pot_lain_lain'], 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($bagianTotal['penerimaan'], 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($bagianTotal['takehomepay'], 0, ',', '.') }}</td>
                                </tr>
                            @endif
                        @endforeach

                        <!-- Total Departemen -->
                        @php
                        $deptTotal = $deptData['total'];
                        @endphp
                        <tr class="bold" style="background-color: #e0e0e0;">
                            <td colspan="4" class="text-center"><strong>Total Dept. {{ $deptData['nama'] }}</strong></td>
                            <td class="text-right">{{ number_format($deptTotal['premi'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($deptTotal['gaji'], 0, ',', '.') }}</td>
                            <td class="text-right">0</td>
                            <td class="text-right">{{ number_format($deptTotal['jm1'], 1, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($deptTotal['jm2'], 1, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($deptTotal['jm3'], 1, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($deptTotal['selisih_upah'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($deptTotal['lembur'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($deptTotal['uang_makan_transport'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($deptTotal['bpjs_kes'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($deptTotal['bpjs_naker'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($deptTotal['bpjs_pensiun'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($deptTotal['tdk_hdr_hc'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($deptTotal['koperasi'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($deptTotal['pot_spn'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($deptTotal['pot_dplk'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($deptTotal['pot_lain_lain'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($deptTotal['penerimaan'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($deptTotal['takehomepay'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endforeach

                <!-- Grand Total -->
                <tr class="bold" style="background-color: #d0d0d0;">
                    <td colspan="4" class="text-center"><strong>GRAND TOTAL</strong></td>
                    <td class="text-right">{{ number_format($grandTotal['premi'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['gaji'], 0, ',', '.') }}</td>
                    <td class="text-right">0</td>
                    <td class="text-right">{{ number_format($grandTotal['jm1'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['jm2'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['jm3'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['selisih_upah'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['lembur'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['uang_makan_transport'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['bpjs_kes'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['bpjs_naker'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['bpjs_pensiun'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['tdk_hdr_hc'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['koperasi'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['pot_spn'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['pot_dplk'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['pot_lain_lain'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['penerimaan'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($grandTotal['takehomepay'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function exportToExcel() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("rekap-upah-finance-ver.export-excel") }}';

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

