@extends('layouts.app')

@section('title', 'Dashboard - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </h1>
        </div>
    </div>

    <!-- 1. Data Karyawan yang Tidak Masuk Hari Ini -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-times me-2"></i>
                        Data Karyawan yang Tidak Masuk Hari Ini
                    </h5>
                </div>
                <div class="card-body">
                    @if($tidakMasukData->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIK</th>
                                        <th>Nama</th>
                                        <th>Divisi</th>
                                        <th>Bagian</th>
                                        <th>Jenis</th>
                                        <th>Periode</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tidakMasukData as $index => $tm)
                                        <tr>
                                            <td>{{ $tidakMasukData->firstItem() + $index }}</td>
                                            <td>{{ $tm->vcNik }}</td>
                                            <td>{{ $tm->Nama }}</td>
                                            <td>{{ $tm->vcNamaDivisi ?? '-' }}</td>
                                            <td>{{ $tm->vcNamaBagian ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-warning text-dark">
                                                    {{ $tm->jenis_absen_keterangan ?? '-' }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($tm->dtTanggalMulai)->format('d/m/Y') }} 
                                                s/d 
                                                {{ \Carbon\Carbon::parse($tm->dtTanggalSelesai)->format('d/m/Y') }}
                                            </td>
                                            <td>{{ $tm->vcKeterangan ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <p class="mb-0">
                                    Menampilkan {{ $tidakMasukData->firstItem() }} - {{ $tidakMasukData->lastItem() }} 
                                    dari {{ $tidakMasukData->total() }} data
                                </p>
                            </div>
                            <div>
                                {{ $tidakMasukData->links() }}
                            </div>
                        </div>
                    @else
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle me-2"></i>Tidak ada karyawan yang tidak masuk hari ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Data Karyawan yang Melakukan Izin Keluar Komplek Hari Ini -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-sign-out-alt me-2"></i>
                        Data Karyawan yang Melakukan Izin Keluar Komplek Hari Ini
                    </h5>
                </div>
                <div class="card-body">
                    @if($izinKeluarData->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIK</th>
                                        <th>Nama</th>
                                        <th>Divisi</th>
                                        <th>Bagian</th>
                                        <th>Jenis Izin</th>
                                        <th>Tipe Izin</th>
                                        <th>Waktu</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($izinKeluarData as $index => $izin)
                                        <tr>
                                            <td>{{ $izinKeluarData->firstItem() + $index }}</td>
                                            <td>{{ $izin->vcNik }}</td>
                                            <td>{{ $izin->Nama }}</td>
                                            <td>{{ $izin->vcNamaDivisi ?? '-' }}</td>
                                            <td>{{ $izin->vcNamaBagian ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $izin->jenis_izin_keterangan ?? '-' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($izin->vcTipeIzin)
                                                    <span class="badge bg-secondary">{{ $izin->vcTipeIzin }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                {{ $izin->dtDari ?? '-' }} 
                                                @if($izin->dtSampai)
                                                    - {{ $izin->dtSampai }}
                                                @endif
                                            </td>
                                            <td>{{ $izin->vcKeterangan ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <p class="mb-0">
                                    Menampilkan {{ $izinKeluarData->firstItem() }} - {{ $izinKeluarData->lastItem() }} 
                                    dari {{ $izinKeluarData->total() }} data
                                </p>
                            </div>
                            <div>
                                {{ $izinKeluarData->links() }}
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>Tidak ada karyawan yang melakukan izin keluar komplek hari ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Data Karyawan yang Tidak Ada Data Absensi (Finger Print) Hari Ini -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-fingerprint me-2"></i>
                        Data Karyawan yang Tidak Ada Data Absensi (Finger Print) Hari Ini
                    </h5>
                </div>
                <div class="card-body">
                    @if($tidakAbsenData->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIK</th>
                                        <th>Nama</th>
                                        <th>Divisi</th>
                                        <th>Bagian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tidakAbsenData as $index => $karyawan)
                                        <tr>
                                            <td>{{ $tidakAbsenData->firstItem() + $index }}</td>
                                            <td>{{ $karyawan->Nik }}</td>
                                            <td>{{ $karyawan->Nama }}</td>
                                            <td>{{ $karyawan->vcNamaDivisi ?? '-' }}</td>
                                            <td>{{ $karyawan->vcNamaBagian ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <p class="mb-0">
                                    Menampilkan {{ $tidakAbsenData->firstItem() }} - {{ $tidakAbsenData->lastItem() }} 
                                    dari {{ $tidakAbsenData->total() }} data
                                </p>
                            </div>
                            <div>
                                {{ $tidakAbsenData->links() }}
                            </div>
                        </div>
                    @else
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle me-2"></i>Semua karyawan sudah melakukan absensi hari ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Data Karyawan Absensi Hari Ini hingga 2 Hari Kebelakang -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-check me-2"></i>
                        Data Karyawan Absensi (Hari Ini - 2 Hari Kebelakang)
                    </h5>
                </div>
                <div class="card-body">
                    @if($absenData->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>NIK</th>
                                        <th>Nama</th>
                                        <th>Divisi</th>
                                        <th>Bagian</th>
                                        <th>Jam Masuk</th>
                                        <th>Jam Keluar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($absenData as $index => $absen)
                                        <tr>
                                            <td>{{ $absenData->firstItem() + $index }}</td>
                                            <td>{{ \Carbon\Carbon::parse($absen->dtTanggal)->format('d/m/Y') }}</td>
                                            <td>{{ $absen->vcNik }}</td>
                                            <td>{{ $absen->Nama }}</td>
                                            <td>{{ $absen->vcNamaDivisi ?? '-' }}</td>
                                            <td>{{ $absen->vcNamaBagian ?? '-' }}</td>
                                            <td>{{ $absen->dtJamMasuk ?? '-' }}</td>
                                            <td>{{ $absen->dtJamKeluar ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <p class="mb-0">
                                    Menampilkan {{ $absenData->firstItem() }} - {{ $absenData->lastItem() }} 
                                    dari {{ $absenData->total() }} data
                                </p>
                            </div>
                            <div>
                                {{ $absenData->links() }}
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>Tidak ada data absensi untuk periode ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
