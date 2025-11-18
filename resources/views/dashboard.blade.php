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

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card bg-primary text-white h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between mb-auto">
                        <div>
                            <h4 class="card-title">Browse Absensi</h4>
                            <p class="card-text">Lihat data absensi karyawan per periode</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-check fa-2x"></i>
                        </div>
                    </div>
                    <a href="{{ route('absen.index') }}" class="btn btn-light btn-sm mt-2">
                        <i class="fas fa-arrow-right me-1"></i>Buka
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card bg-success text-white h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between mb-auto">
                        <div>
                            <h4 class="card-title">Input Tidak Masuk</h4>
                            <p class="card-text">Input data karyawan yang tidak masuk kerja</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-times fa-2x"></i>
                        </div>
                    </div>
                    <a href="{{ route('tidak-masuk.index') }}" class="btn btn-light btn-sm mt-2">
                        <i class="fas fa-arrow-right me-1"></i>Buka
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card bg-warning text-white h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between mb-auto">
                        <div>
                            <h4 class="card-title">Izin Keluar Komplek</h4>
                            <p class="card-text">Input izin keluar komplek karyawan</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-sign-out-alt fa-2x"></i>
                        </div>
                    </div>
                    <a href="{{ route('izin-keluar.index') }}" class="btn btn-light btn-sm mt-2">
                        <i class="fas fa-arrow-right me-1"></i>Buka
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between mb-auto">
                        <div>
                            <h4 class="card-title">Saldo Cuti</h4>
                            <p class="card-text">Kelola saldo cuti karyawan</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-alt fa-2x"></i>
                        </div>
                    </div>
                    <a href="{{ route('saldo-cuti.index') }}" class="btn btn-light btn-sm mt-2">
                        <i class="fas fa-arrow-right me-1"></i>Buka
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card bg-danger text-white h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between mb-auto">
                        <div>
                            <h4 class="card-title">Statistik Absensi</h4>
                            <p class="card-text">Lihat statistik dan laporan absensi</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-bar fa-2x"></i>
                        </div>
                    </div>
                    <a href="{{ route('absensi.statistik.index') }}" class="btn btn-light btn-sm mt-2">
                        <i class="fas fa-arrow-right me-1"></i>Buka
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Selamat Datang di HRIS Seven Payroll
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text">
                        Sistem Human Resources Information System (HRIS) Seven Payroll adalah aplikasi
                        untuk mengelola data karyawan, absensi, dan penggajian dalam perusahaan.
                        Gunakan menu navigasi di atas untuk mengakses berbagai fitur yang tersedia.
                    </p>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6><i class="fas fa-check-circle text-success me-2"></i>Fitur yang Tersedia:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-angle-right me-2"></i>Manajemen Absensi</li>
                                <li><i class="fas fa-angle-right me-2"></i>Manajemen Karyawan</li>
                                <li><i class="fas fa-angle-right me-2"></i>Proses Penggajian</li>
                                <li><i class="fas fa-angle-right me-2"></i>Laporan dan Statistik</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-cog text-primary me-2"></i>Teknologi:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-angle-right me-2"></i>Laravel Framework</li>
                                <li><i class="fas fa-angle-right me-2"></i>Bootstrap 5</li>
                                <li><i class="fas fa-angle-right me-2"></i>Font Awesome Icons</li>
                                <li><i class="fas fa-angle-right me-2"></i>MySQL Database</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



