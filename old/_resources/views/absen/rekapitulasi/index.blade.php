@extends('layouts.app')

@section('title', 'Rekapitulasi Absensi Karyawan - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i>Rekapitulasi Absensi Karyawan
                </h2>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('rekapitulasi-absensi.index') }}" id="filterForm" onsubmit="return validateFilter()">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="dari_tanggal" class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" id="dari_tanggal" name="dari_tanggal" value="{{ $startDate }}">
                            </div>
                            <div class="col-md-3">
                                <label for="sampai_tanggal" class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" id="sampai_tanggal" name="sampai_tanggal" value="{{ $endDate }}">
                            </div>
                            <div class="col-md-4">
                                <label for="search" class="form-label">Pencarian (NIK / Nama) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="search" name="search" value="{{ $search }}" placeholder="Cari NIK atau Nama..." required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 shadow-sm">
                                    <i class="fas fa-eye me-2"></i>Preview
                                </button>
                            </div>
                            @if($karyawan && $rekapitulasi)
                            <div class="col-md-2 d-flex align-items-end">
                                <a href="{{ route('rekapitulasi-absensi.print', ['dari_tanggal' => $startDate, 'sampai_tanggal' => $endDate, 'search' => $search]) }}" 
                                   target="_blank" 
                                   class="btn btn-success w-100 shadow-sm">
                                    <i class="fas fa-print me-2"></i>Cetak
                                </a>
                            </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            @if($karyawan && $rekapitulasi)
            <div class="card">
                <div class="card-body">
                    <div class="mb-4">
                        <h4 class="mb-3">Rekapitulasi Absensi Karyawan Periode {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} sampai {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</h4>
                        
                        <div class="row mb-2">
                            <div class="col-md-2">
                                <strong>NIK:</strong> {{ $karyawan->Nik }}
                            </div>
                            <div class="col-md-3">
                                <strong>Nama:</strong> {{ $karyawan->Nama }}
                            </div>
                            <div class="col-md-3">
                                <strong>Tanggal Masuk:</strong> {{ $karyawan->Tgl_Masuk ? \Carbon\Carbon::parse($karyawan->Tgl_Masuk)->format('d/m/Y') : '-' }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Divisi:</strong> {{ $karyawan->divisi->vcNamaDivisi ?? '-' }}
                            </div>
                            <div class="col-md-4">
                                <strong>Departemen:</strong> {{ $karyawan->departemen->vcNamaDept ?? '-' }}
                            </div>
                            <div class="col-md-4">
                                <strong>Bagian:</strong> {{ $karyawan->bagian->vcNamaBagian ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th style="background-color: #4472C4; color: white; width: 30%;">Kriteria</th>
                                    <th style="background-color: #4472C4; color: white; width: 15%; text-align: right;">Jumlah</th>
                                    <th style="background-color: #4472C4; color: white; width: 15%; text-align: right;">Persentase</th>
                                    <th style="background-color: #4472C4; color: white; width: 40%;">Formulasi atau aturan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rekapitulasi as $item)
                                <tr>
                                    <td style="background-color: #D9E1F2;">{{ $item['kriteria'] }}</td>
                                    <td style="text-align: right;">{{ number_format($item['jumlah'], 0) }}</td>
                                    <td style="text-align: right;">
                                        @if($item['persentase'] !== null)
                                            {{ str_replace('.', ',', number_format($item['persentase'], 3)) }}%
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $item['formulasi'] ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(count($averageJamKerjaPerBulan) > 0)
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-clock me-2"></i>Rata-rata Jam Kerja per Bulan
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Keterangan:</strong> Data rata-rata jam kerja di atas merupakan perhitungan berdasarkan kehadiran aktual (Hadir) saja, tidak termasuk Cuti, Sakit, atau Izin Resmi.
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-success">
                                        <tr>
                                            <th style="background-color: #28a745; color: white; width: 25%;">Bulan</th>
                                            <th style="background-color: #28a745; color: white; width: 20%; text-align: right;">Total Jam Kerja</th>
                                            <th style="background-color: #28a745; color: white; width: 20%; text-align: right;">Jumlah Hari Kerja</th>
                                            <th style="background-color: #28a745; color: white; width: 20%; text-align: right;">Rata-rata Jam Kerja/Hari</th>
                                            <th style="background-color: #28a745; color: white; width: 15%; text-align: center;">Visualisasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($averageJamKerjaPerBulan as $data)
                                        <tr>
                                            <td style="background-color: #d4edda;"><strong>{{ $data['bulan'] }}</strong></td>
                                            <td style="text-align: right;">{{ number_format($data['total_jam_kerja'], 2) }} jam</td>
                                            <td style="text-align: right;">{{ number_format($data['jumlah_hari_kerja'], 0) }} hari</td>
                                            <td style="text-align: right;">
                                                <strong>{{ number_format($data['rata_rata_jam_kerja'], 2) }} jam</strong>
                                            </td>
                                            <td style="text-align: center;">
                                                @php
                                                    $percentage = $data['jumlah_hari_kerja'] > 0 ? ($data['rata_rata_jam_kerja'] / 8) * 100 : 0;
                                                    $percentage = min(100, max(0, $percentage));
                                                    $color = $data['rata_rata_jam_kerja'] >= 8 ? 'success' : ($data['rata_rata_jam_kerja'] >= 7 ? 'warning' : 'danger');
                                                @endphp
                                                <div class="progress" style="height: 25px;">
                                                    <div class="progress-bar bg-{{ $color }}" role="progressbar" 
                                                         style="width: {{ $percentage }}%" 
                                                         aria-valuenow="{{ $percentage }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        {{ number_format($percentage, 1) }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td style="background-color: #f8f9fa;"><strong>Rata-rata Keseluruhan</strong></td>
                                            <td style="text-align: right;">
                                                <strong>{{ number_format(collect($averageJamKerjaPerBulan)->sum('total_jam_kerja'), 2) }} jam</strong>
                                            </td>
                                            <td style="text-align: right;">
                                                <strong>{{ number_format(collect($averageJamKerjaPerBulan)->sum('jumlah_hari_kerja'), 0) }} hari</strong>
                                            </td>
                                            <td style="text-align: right;">
                                                @php
                                                    $totalJam = collect($averageJamKerjaPerBulan)->sum('total_jam_kerja');
                                                    $totalHari = collect($averageJamKerjaPerBulan)->sum('jumlah_hari_kerja');
                                                    $rataRataKeseluruhan = $totalHari > 0 ? round($totalJam / $totalHari, 2) : 0;
                                                @endphp
                                                <strong style="color: #28a745; font-size: 1.1em;">{{ number_format($rataRataKeseluruhan, 2) }} jam</strong>
                                            </td>
                                            <td style="text-align: center;">
                                                @php
                                                    $avgPercentage = $totalHari > 0 ? ($rataRataKeseluruhan / 8) * 100 : 0;
                                                    $avgPercentage = min(100, max(0, $avgPercentage));
                                                    $avgColor = $rataRataKeseluruhan >= 8 ? 'success' : ($rataRataKeseluruhan >= 7 ? 'warning' : 'danger');
                                                @endphp
                                                <div class="progress" style="height: 30px;">
                                                    <div class="progress-bar bg-{{ $avgColor }}" role="progressbar" 
                                                         style="width: {{ $avgPercentage }}%" 
                                                         aria-valuenow="{{ $avgPercentage }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        <strong>{{ number_format($avgPercentage, 1) }}%</strong>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @elseif($search)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>Karyawan tidak ditemukan atau tidak aktif.
            </div>
            @endif

            @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
        font-size: 0.9rem;
    }
    .table td {
        font-size: 0.9rem;
    }
    .table-bordered {
        border: 1px solid #dee2e6;
    }
    .table-bordered th,
    .table-bordered td {
        border: 1px solid #dee2e6;
        padding: 8px;
    }
</style>
@endpush

@push('scripts')
<script>
    function validateFilter() {
        const search = document.getElementById('search').value.trim();
        
        if (!search) {
            alert('Silakan isi NIK atau Nama untuk mencari karyawan');
            return false;
        }
        
        return true;
    }
</script>
@endpush

