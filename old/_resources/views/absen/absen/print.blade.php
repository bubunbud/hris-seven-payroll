<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Browse Absensi - {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 1cm;
            }

            body {
                font-size: 8pt;
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .print-header {
                text-align: center;
                margin-bottom: 0;
                padding: 5px 0;
                border-bottom: 2px solid #000;
            }

            .print-header h3 {
                font-size: 12pt;
                font-weight: bold;
                margin: 3px 0;
            }

            .print-header h5 {
                font-size: 10pt;
                margin: 3px 0;
            }

            .print-header p {
                font-size: 9pt;
                margin: 3px 0;
            }

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
                border-top: 1px solid #333;
            }

            .table th,
            .table td {
                padding: 4px 6px;
                border: 1px solid #333;
                vertical-align: middle;
            }

            .table thead th {
                background-color: #f0f0f0 !important;
                font-weight: bold;
                text-align: center;
            }

            .table tbody td {
                text-align: left;
            }

            .table tbody td:nth-child(4) {
                white-space: nowrap;
            }

            .badge {
                padding: 2px 6px;
                font-size: 7pt;
                font-weight: normal;
            }

            .page-break {
                page-break-after: always;
            }
        }

        @media screen {
            body {
                padding: 20px;
            }

            .no-print {
                margin-bottom: 20px;
            }
        }

        .print-header {
            display: block;
            text-align: center;
            margin-bottom: 0;
            padding: 10px 0;
            border-bottom: 2px solid #000;
        }

        .print-header h3 {
            font-size: 16pt;
            font-weight: bold;
            margin: 5px 0;
        }

        .print-header h5 {
            font-size: 12pt;
            margin: 5px 0;
        }

        .print-header p {
            font-size: 10pt;
            margin: 3px 0;
        }

        .table {
            font-size: 9pt;
            border-top: 1px solid #333;
        }

        .table th,
        .table td {
            padding: 6px 8px;
            border: 1px solid #ddd;
        }

        .table thead th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .table tbody td:nth-child(4) {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="no-print mb-3">
            <a href="{{ route('absen.index', request()->query()) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Cetak
            </button>
        </div>

        <!-- Print Header -->
        <div class="print-header">
            <h3>Daftar Absensi Karyawan Per Periode</h3>
            <h5>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</h5>
            @if($search)
            <p><strong>NIK/Nama:</strong> <strong>{{ $search }}</strong></p>
            @endif
            @if($group !== 'Semua Group')
            <p><strong>Group:</strong> {{ $group }}</p>
            @endif
            @if($tidakMasuk || $absenTidakLengkap || $hariKerjaNormal || $kerjaHariLibur || $telat)
            <p>
                <strong>Status:</strong>
                @if($tidakMasuk) Tidak Masuk @endif
                @if($absenTidakLengkap) Absen Tidak Lengkap @endif
                @if($hariKerjaNormal) Hari Kerja Normal @endif
                @if($kerjaHariLibur) Kerja Hari Libur @endif
                @if($telat) Telat @endif
            </p>
            @endif
            <p><strong>Total Data:</strong> {{ number_format($absens->count()) }} record</p>
        </div>

        <!-- Data Table -->
        <div class="table-responsive" style="margin-top: 0;">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="8%">Tanggal</th>
                        <th width="7%">NIK</th>
                        <th width="15%">Nama</th>
                        <th width="15%">Divisi</th>
                        <th width="12%">Departemen</th>
                        <th width="12%">Bagian</th>
                        <th width="8%">Jam Masuk</th>
                        <th width="8%">Jam Pulang</th>
                        <th width="7%">Total Jam</th>
                        <th width="8%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absens as $item)
                    @php
                    $dtTanggal = $item['dtTanggal'] ?? null;
                    $vcNik = $item['vcNik'] ?? '';
                    $Nama = $item['Nama'] ?? 'N/A';
                    $vcNamaDivisi = $item['vcNamaDivisi'] ?? 'N/A';
                    $vcNamaDept = $item['vcNamaDept'] ?? 'N/A';
                    $vcNamaBagian = $item['vcNamaBagian'] ?? 'N/A';
                    $dtJamMasuk = $item['dtJamMasuk'] ?? null;
                    $dtJamKeluar = $item['dtJamKeluar'] ?? null;
                    $total_jam = $item['total_jam'] ?? 0;
                    $source = $item['source'] ?? 'absen';
                    $shift_masuk = $item['shift_masuk'] ?? null;
                    $Group_pegawai = $item['Group_pegawai'] ?? null;
                    $shift_terjadwal = $item['shift_terjadwal'] ?? [];
                    $shift_aktual = $item['shift_aktual'] ?? null;
                    $status_validasi = $item['status_validasi'] ?? null;
                    $vcKodeAbsen = $item['vcKodeAbsen'] ?? null;
                    $jenis_absen_keterangan = $item['jenis_absen_keterangan'] ?? null;
                    $vcKeterangan = $item['vcKeterangan'] ?? null;
                    $status = $item['status'] ?? '';
                    
                    $badgeClass = '';
                    switch ($status) {
                        case 'Tidak Masuk':
                            $badgeClass = 'bg-danger';
                            break;
                        case 'Telat':
                            $badgeClass = 'bg-warning text-dark';
                            break;
                        case 'ATL':
                            $badgeClass = 'bg-warning text-dark';
                            break;
                        case 'KHL':
                            $badgeClass = 'bg-info';
                            break;
                        case 'HKN':
                            $badgeClass = 'bg-success';
                            break;
                        case 'HC':
                            $badgeClass = 'bg-warning text-dark';
                            break;
                        default:
                            $badgeClass = 'bg-secondary';
                            break;
                    }
                    @endphp
                    <tr>
                        <td>{{ $dtTanggal ? \Carbon\Carbon::parse($dtTanggal)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $vcNik }}</td>
                        <td>{{ $Nama }}</td>
                        <td style="white-space: nowrap;">{{ $vcNamaDivisi }}</td>
                        <td>{{ $vcNamaDept }}</td>
                        <td>{{ $vcNamaBagian }}</td>
                        <td>
                            @if($dtJamMasuk)
                            {{ \Carbon\Carbon::parse($dtJamMasuk)->format('H:i') }}
                            @elseif($source === 'tidak_masuk')
                            <small>{{ $jenis_absen_keterangan ?? $vcKodeAbsen ?? '-' }}</small>
                            @else
                            -
                            @endif
                        </td>
                        <td>
                            @if($dtJamKeluar)
                            {{ \Carbon\Carbon::parse($dtJamKeluar)->format('H:i') }}
                            @else
                            -
                            @endif
                        </td>
                        <td class="text-center">
                            @if($total_jam > 0)
                            {{ $total_jam }} jam
                            @else
                            -
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                            @if($source === 'tidak_masuk' && $vcKeterangan)
                            <br><small>{{ strlen($vcKeterangan) > 20 ? substr($vcKeterangan, 0, 20) . '...' : $vcKeterangan }}</small>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <p class="text-muted mb-0">Tidak ada data absensi untuk periode ini</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Legend -->
        <div class="mt-3 no-print">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-info-circle me-2"></i>Keterangan Status
                    </h6>
                    <div class="row">
                        <div class="col-md-3">
                            <span class="badge bg-success me-2">HKN</span>
                            <span class="text-muted">Hari Kerja Normal</span>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-info me-2">KHL</span>
                            <span class="text-muted">Kerja Hari Libur</span>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-warning text-dark me-2">Telat</span>
                            <span class="text-muted">Jam masuk > jam shift masuk</span>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-warning text-dark me-2">ATL</span>
                            <span class="text-muted">Absen Tidak Lengkap</span>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-warning text-dark me-2">HC</span>
                            <span class="text-muted">Jam kerja kurang dari 8 jam</span>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-danger me-2">Tidak Masuk</span>
                            <span class="text-muted">Tidak ada absensi</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto print saat halaman dimuat (opsional, bisa di-comment jika tidak diinginkan)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>

