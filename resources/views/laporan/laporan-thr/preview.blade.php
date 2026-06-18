@extends('layouts.app')

@section('title', 'Preview Laporan THR')

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
    }

    .thr-table .text-right {
        text-align: right;
    }

    .thr-table .text-center {
        text-align: center;
    }

    .thr-table .bold {
        font-weight: bold;
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

    .total-row {
        background-color: #e8e8e8;
        font-weight: bold;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Action Buttons -->
            <div class="no-print mb-3">
                <button type="button" id="btnCetakThr" class="btn btn-primary">
                    <i class="fas fa-print me-2"></i>Cetak
                </button>
                <a href="{{ route('laporan-thr.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>

            <!-- Printable area: di-render sebagai image agar PDF sulit dikonversi ke Excel -->
            <div id="printable-report-thr">
            <!-- Report Header -->
            <div class="report-header">
                <h3>DAFTAR TUNJANGAN HARI RAYA (THR) KARYAWAN</h3>
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
                <h5 style="font-size: 10pt; margin-top: 5px;">Tanggal THR: {{ $tanggalTHR ? $tanggalTHR->format('d/m/Y') : '-' }}</h5>
            </div>

            <!-- Report Content -->
            <div class="report-content">
                @php
                $noUrut = 1;
                @endphp

                @foreach($groupedData as $divisiKode => $divisiData)
                @foreach($divisiData['departemens'] as $deptKode => $deptData)
                <table class="thr-table">
                    <thead>
                        <tr>
                            <th width="3%">No.</th>
                            <th width="6%">NIK</th>
                            <th width="12%">NAMA</th>
                            <th width="8%">Departemen</th>
                            <th width="10%">Bagian</th>
                            <th width="6%">Golongan</th>
                            <th width="8%" class="text-right">Gaji Pokok</th>
                            <th width="7%">Tanggal Masuk</th>
                            <th width="7%">Tanggal THR</th>
                            <th width="10%">Masa Kerja</th>
                            <th width="6%" class="text-right">Masa Kerja (Hari)</th>
                            <th width="6%" class="text-right">Masa Kerja (Tahun)</th>
                            <th width="5%" class="text-center">(x Gaji)</th>
                            <th width="8%" class="text-right">Nilai THR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deptData['bagians'] as $bagianKode => $bagianData)
                        @foreach($bagianData['karyawans'] as $thr)
                        @php
                        $karyawan = $thr->karyawan;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $noUrut++ }}</td>
                            <td>{{ $thr->vcNik }}</td>
                            <td>{{ $karyawan->Nama ?? 'N/A' }}</td>
                            <td>{{ $deptData['nama'] }}</td>
                            <td>{{ $bagianData['nama'] }}</td>
                            <td class="text-center">{{ $thr->vcGolongan ?? '-' }}</td>
                            <td class="text-right">
                                @if($thr->decGajiPokok !== null)
                                {{ number_format($thr->decGajiPokok, 0, ',', '.') }}
                                @else
                                -
                                @endif
                            </td>
                            <td class="text-center">
                                {{ $thr->dtTanggalMasuk ? $thr->dtTanggalMasuk->format('d/m/Y') : '-' }}
                            </td>
                            <td class="text-center">
                                {{ $thr->dtTanggalTHR ? $thr->dtTanggalTHR->format('d/m/Y') : '-' }}
                            </td>
                            <td>{{ $thr->vcMasaKerja ?? '-' }}</td>
                            <td class="text-right">{{ number_format($thr->intMasaKerjaHari, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($thr->decMasaKerjaTahun, 2, ',', '.') }}</td>
                            <td class="text-center">{{ number_format($thr->decXGaji, 1, ',', '.') }}</td>
                            <td class="text-right">
                                @if($thr->decNilaiTHR !== null)
                                {{ number_format($thr->decNilaiTHR, 0, ',', '.') }}
                                @else
                                -
                                @endif
                            </td>
                        </tr>
                        @endforeach

                        <!-- Total Bagian -->
                        <tr class="total-row">
                            <td colspan="6" class="text-right bold">Total Bag. {{ $bagianData['nama'] }}</td>
                            <td class="text-right bold">
                                {{ number_format($bagianData['total']['gaji_pokok'], 0, ',', '.') }}
                            </td>
                            <td colspan="6"></td>
                            <td class="text-right bold">
                                {{ number_format($bagianData['total']['nilai_thr'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach

                        <!-- Total Departemen -->
                        <tr class="total-row">
                            <td colspan="6" class="text-right bold">Total Dept. {{ $deptData['nama'] }}</td>
                            <td class="text-right bold">
                                {{ number_format($deptData['total']['gaji_pokok'], 0, ',', '.') }}
                            </td>
                            <td colspan="6"></td>
                            <td class="text-right bold">
                                {{ number_format($deptData['total']['nilai_thr'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <br>
                @endforeach
                @endforeach

                <!-- Grand Total -->
                <table class="thr-table">
                    <tbody>
                        <tr class="total-row">
                            <td colspan="6" class="text-right bold">GRAND TOTAL</td>
                            <td class="text-right bold">
                                {{ number_format($grandTotal['gaji_pokok'], 0, ',', '.') }}
                            </td>
                            <td colspan="6"></td>
                            <td class="text-right bold">
                                {{ number_format($grandTotal['nilai_thr'], 0, ',', '.') }}
                            </td>
                        </tr>
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
                        </div>
                    </div>
                    <div class="signature-block">
                        <div style="text-align: center; margin-bottom: 6px;">
                            <strong>Mengetahui</strong>
                            <br><br><br><br><br>
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
            </div><!-- /#printable-report-thr -->
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('btnCetakThr').addEventListener('click', function() {
        var btn = this;
        var originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';

        var element = document.getElementById('printable-report-thr');
        html2canvas(element, {
            scale: 2,
            useCORS: true,
            logging: false,
            letterRendering: true,
            allowTaint: true
        }).then(function(canvas) {
            var imgData = canvas.toDataURL('image/png');
            var printWindow = window.open('', '_blank');
            printWindow.document.write(
                '<html><head><title>Cetak Laporan THR</title>' +
                '<style>body{margin:0;padding:0;} img{width:100%;height:auto;display:block;}</style></head>' +
                '<body><img src="' + imgData + '" /></body></html>'
            );
            printWindow.document.close();
            printWindow.focus();
            setTimeout(function() {
                printWindow.print();
                printWindow.onafterprint = function() { printWindow.close(); };
            }, 250);
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }).catch(function(err) {
            console.error('html2canvas error:', err);
            alert('Gagal memproses. Mencoba cetak biasa...');
            window.print();
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    });
});
</script>
@endpush
@endsection