@extends('layouts.app')

@section('title', 'Dashboard Karyawan (Employee Self Service) - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-user me-2"></i>Dashboard Karyawan (Employee Self Service)
                </h2>
                <div>
                    @if($isAdmin)
                    <form method="GET" action="{{ route('dashboard.employee') }}" class="d-inline-block me-3">
                        <div class="input-group">
                            <select name="nik" class="form-select" onchange="this.form.submit()" style="min-width: 300px;">
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach($allKaryawans as $k)
                                <option value="{{ $k->Nik }}" {{ $selectedNik == $k->Nik ? 'selected' : '' }}>
                                    {{ $k->Nik }} - {{ $k->Nama }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                    @endif
                    <span class="badge bg-primary fs-6">{{ $karyawan->Nama ?? 'N/A' }}</span>
                    <div class="text-muted mt-1">
                        <i class="fas fa-calendar me-1"></i>
                        {{ \Carbon\Carbon::now()->format('d F Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- A. Informasi Pribadi -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-circle me-2"></i>A. Informasi Pribadi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Profil Singkat -->
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <h6 class="card-title mb-3"><i class="fas fa-id-card me-2"></i>Profil Singkat</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td width="40%"><strong>NIK</strong></td>
                                            <td>: {{ $personalInfo['profil']['nik'] }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Nama</strong></td>
                                            <td>: {{ $personalInfo['profil']['nama'] }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Divisi</strong></td>
                                            <td>: {{ $personalInfo['profil']['divisi'] }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Departemen</strong></td>
                                            <td>: {{ $personalInfo['profil']['departemen'] }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Bagian</strong></td>
                                            <td>: {{ $personalInfo['profil']['bagian'] }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Jabatan</strong></td>
                                            <td>: {{ $personalInfo['profil']['jabatan'] }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Golongan</strong></td>
                                            <td>: {{ $personalInfo['profil']['golongan'] }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Group Pegawai</strong></td>
                                            <td>: {{ $personalInfo['profil']['group_pegawai'] }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Status Kerja -->
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <h6 class="card-title mb-3"><i class="fas fa-briefcase me-2"></i>Status Kerja</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td width="40%"><strong>Status Pegawai</strong></td>
                                            <td>: 
                                                <span class="badge bg-{{ $personalInfo['status_kerja']['status_pegawai'] == 'Tetap' ? 'success' : 'info' }}">
                                                    {{ $personalInfo['status_kerja']['status_pegawai'] }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tanggal Masuk</strong></td>
                                            <td>: {{ $personalInfo['status_kerja']['tanggal_masuk'] }}</td>
                                        </tr>
                                        @if($personalInfo['status_kerja']['tanggal_berhenti'])
                                        <tr>
                                            <td><strong>Tanggal Berhenti</strong></td>
                                            <td>: {{ $personalInfo['status_kerja']['tanggal_berhenti'] }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td><strong>Status Aktif</strong></td>
                                            <td>: 
                                                <span class="badge bg-{{ $personalInfo['status_kerja']['status_aktif'] == 'Aktif' ? 'success' : 'danger' }}">
                                                    {{ $personalInfo['status_kerja']['status_aktif'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    @if($personalInfo['jadwal_shift'] && $personalInfo['jadwal_shift']->isNotEmpty())
                                    <hr>
                                    <h6 class="mb-2"><i class="fas fa-calendar-alt me-2"></i>Jadwal Shift</h6>
                                    <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Shift</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($personalInfo['jadwal_shift'] as $jadwal)
                                                <tr>
                                                    <td>{{ $jadwal['tanggal'] }}</td>
                                                    <td>{{ $jadwal['shift'] }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($personalInfo['masa_kontrak'])
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="alert alert-{{ $personalInfo['masa_kontrak']['status'] == 'Akan Habis' ? 'warning' : 'info' }}">
                                <h6 class="alert-heading">
                                    <i class="fas fa-file-contract me-2"></i>Masa Kontrak
                                </h6>
                                <p class="mb-2">
                                    <strong>Tanggal Mulai:</strong> {{ $personalInfo['masa_kontrak']['tanggal_mulai'] }} | 
                                    <strong>Tanggal Habis:</strong> {{ $personalInfo['masa_kontrak']['tanggal_habis'] }} | 
                                    <strong>Sisa Hari:</strong> <span class="badge bg-{{ $personalInfo['masa_kontrak']['sisa_hari'] <= 30 ? 'danger' : 'warning' }}">{{ $personalInfo['masa_kontrak']['sisa_hari'] }} hari</span>
                                </p>
                                <p class="mb-0"><strong>Status:</strong> {{ $personalInfo['masa_kontrak']['status'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- B. Absensi & Jadwal -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-check me-2"></i>B. Absensi & Jadwal
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Kehadiran Hari Ini -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6 class="mb-3"><i class="fas fa-clock me-2"></i>Kehadiran Hari Ini</h6>
                            @if($absensiInfo['kehadiran_hari_ini'])
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Jam Masuk:</strong><br>
                                            <span class="fs-5">{{ $absensiInfo['kehadiran_hari_ini']['jam_masuk'] ?? '-' }}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Jam Keluar:</strong><br>
                                            <span class="fs-5">{{ $absensiInfo['kehadiran_hari_ini']['jam_keluar'] ?? '-' }}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Total Jam:</strong><br>
                                            <span class="fs-5">{{ number_format($absensiInfo['kehadiran_hari_ini']['total_jam'], 1) }} jam</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Status:</strong><br>
                                            <span class="badge bg-{{ $absensiInfo['kehadiran_hari_ini']['status'] == 'HKN' ? 'success' : ($absensiInfo['kehadiran_hari_ini']['status'] == 'KHL' ? 'info' : 'warning') }} fs-6">
                                                {{ $absensiInfo['kehadiran_hari_ini']['status'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>Belum ada data kehadiran untuk hari ini.
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <!-- Riwayat Absensi -->
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Absensi (30 Hari Terakhir)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-sm table-bordered table-hover">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Jam Masuk</th>
                                                    <th>Jam Keluar</th>
                                                    <th>Total Jam</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($absensiInfo['riwayat_absensi'] as $absen)
                                                <tr>
                                                    <td>{{ $absen['tanggal'] }}</td>
                                                    <td>{{ $absen['jam_masuk'] ?? '-' }}</td>
                                                    <td>{{ $absen['jam_keluar'] ?? '-' }}</td>
                                                    <td>{{ number_format($absen['total_jam'], 1) }} jam</td>
                                                    <td>
                                                        <span class="badge bg-{{ $absen['status'] == 'HKN' ? 'success' : ($absen['status'] == 'KHL' ? 'info' : 'warning') }}">
                                                            {{ $absen['status'] }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">Tidak ada data absensi</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Riwayat Tidak Masuk -->
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-calendar-times me-2"></i>Riwayat Tidak Masuk (Bulan Ini)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-sm table-bordered table-hover">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th>Tanggal Mulai</th>
                                                    <th>Tanggal Selesai</th>
                                                    <th>Jenis</th>
                                                    <th>Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($absensiInfo['riwayat_tidak_masuk'] as $tidakMasuk)
                                                <tr>
                                                    <td>{{ $tidakMasuk['tanggal_mulai'] }}</td>
                                                    <td>{{ $tidakMasuk['tanggal_selesai'] }}</td>
                                                    <td>{{ $tidakMasuk['jenis'] }}</td>
                                                    <td>{{ $tidakMasuk['keterangan'] }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">Tidak ada data tidak masuk</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Riwayat Izin Keluar Komplek -->
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-door-open me-2"></i>Riwayat Izin Keluar Komplek (Bulan Ini)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-sm table-bordered table-hover">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Jam Keluar</th>
                                                    <th>Jam Masuk</th>
                                                    <th>Durasi (jam)</th>
                                                    <th>Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($absensiInfo['riwayat_izin_keluar'] as $izin)
                                                <tr>
                                                    <td>{{ $izin['tanggal'] }}</td>
                                                    <td>{{ $izin['jam_keluar'] }}</td>
                                                    <td>{{ $izin['jam_masuk'] }}</td>
                                                    <td>{{ number_format($izin['durasi'], 1) }}</td>
                                                    <td>{{ $izin['keterangan'] }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">Tidak ada data izin keluar</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- C. Cuti & Izin -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-times me-2"></i>C. Cuti & Izin
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <!-- Sisa Cuti -->
                        <div class="col-md-12 mb-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title mb-3"><i class="fas fa-calendar-check me-2"></i>Sisa Cuti Tahun {{ $cutiInfo['sisa_cuti']['tahun'] }}</h6>
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <small class="text-white-50">Saldo Tahun Lalu</small>
                                                <h4 class="mb-0">{{ number_format($cutiInfo['sisa_cuti']['saldo_tahun_lalu']) }} hari</h4>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <small class="text-white-50">Saldo Tahun Ini</small>
                                                <h4 class="mb-0">{{ number_format($cutiInfo['sisa_cuti']['saldo_tahun_ini']) }} hari</h4>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <small class="text-white-50">Digunakan</small>
                                                <h4 class="mb-0">{{ number_format($cutiInfo['sisa_cuti']['saldo_digunakan']) }} hari</h4>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <small class="text-white-50">Sisa Cuti</small>
                                                <h4 class="mb-0">{{ number_format($cutiInfo['sisa_cuti']['saldo_sisa']) }} hari</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Riwayat Cuti -->
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Cuti (Tahun {{ $cutiInfo['sisa_cuti']['tahun'] }})</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-sm table-bordered table-hover">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th>Tanggal Mulai</th>
                                                    <th>Tanggal Selesai</th>
                                                    <th>Durasi</th>
                                                    <th>Jenis</th>
                                                    <th>Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($cutiInfo['riwayat_cuti'] as $cuti)
                                                <tr>
                                                    <td>{{ $cuti['tanggal_mulai'] }}</td>
                                                    <td>{{ $cuti['tanggal_selesai'] }}</td>
                                                    <td class="text-center">{{ $cuti['durasi'] }} hari</td>
                                                    <td>{{ $cuti['jenis'] }}</td>
                                                    <td>{{ $cuti['keterangan'] }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">Tidak ada riwayat cuti</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pengajuan Cuti -->
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>Pengajuan Cuti</h6>
                                </div>
                                <div class="card-body">
                                    @if(count($cutiInfo['pengajuan_cuti']) > 0)
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-sm table-bordered table-hover">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Status</th>
                                                    <th>Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($cutiInfo['pengajuan_cuti'] as $pengajuan)
                                                <tr>
                                                    <td>{{ $pengajuan['tanggal'] ?? '-' }}</td>
                                                    <td>{{ $pengajuan['status'] ?? '-' }}</td>
                                                    <td>{{ $pengajuan['keterangan'] ?? '-' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <p class="text-muted text-center mb-0">Tidak ada pengajuan cuti.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- D. Payroll -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>D. Payroll
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Slip Gaji Terbaru -->
                    @if($payrollInfo['slip_gaji_terbaru'])
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6 class="mb-3"><i class="fas fa-file-invoice-dollar me-2"></i>Slip Gaji Terbaru</h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>Periode:</strong> {{ $payrollInfo['slip_gaji_terbaru']['periode_awal'] }} - {{ $payrollInfo['slip_gaji_terbaru']['periode_akhir'] }}<br>
                                            <strong>Tanggal Gajian:</strong> {{ $payrollInfo['slip_gaji_terbaru']['periode'] }}<br>
                                            <strong>Closing Ke:</strong> {{ $payrollInfo['slip_gaji_terbaru']['closing_ke'] }}
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <h5 class="mb-0">Take Home Pay</h5>
                                            <h3 class="text-success mb-0">Rp {{ number_format($payrollInfo['slip_gaji_terbaru']['take_home_pay'], 0, ',', '.') }}</h3>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr>
                                                    <td width="50%"><strong>Gaji Pokok</strong></td>
                                                    <td class="text-end">Rp {{ number_format($payrollInfo['slip_gaji_terbaru']['gaji_pokok'], 0, ',', '.') }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Uang Makan</strong></td>
                                                    <td class="text-end">Rp {{ number_format($payrollInfo['slip_gaji_terbaru']['uang_makan'], 0, ',', '.') }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Transport</strong></td>
                                                    <td class="text-end">Rp {{ number_format($payrollInfo['slip_gaji_terbaru']['transport'], 0, ',', '.') }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Lembur</strong></td>
                                                    <td class="text-end">Rp {{ number_format($payrollInfo['slip_gaji_terbaru']['lembur'], 0, ',', '.') }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Total Gaji</strong></td>
                                                    <td class="text-end"><strong>Rp {{ number_format($payrollInfo['slip_gaji_terbaru']['total_gaji'], 0, ',', '.') }}</strong></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr>
                                                    <td width="50%"><strong>Total Potongan</strong></td>
                                                    <td class="text-end text-danger">Rp {{ number_format($payrollInfo['slip_gaji_terbaru']['total_potongan'], 0, ',', '.') }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>Belum ada data slip gaji.
                    </div>
                    @endif

                    <div class="row">
                        <!-- Riwayat Slip Gaji -->
                        <div class="col-md-8 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Slip Gaji (12 Bulan Terakhir)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-sm table-bordered table-hover">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th>Periode</th>
                                                    <th>Tanggal Gajian</th>
                                                    <th>Closing Ke</th>
                                                    <th class="text-end">Take Home Pay</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($payrollInfo['riwayat_slip_gaji'] as $slip)
                                                <tr>
                                                    <td>{{ $slip['periode_awal'] }} - {{ $slip['periode_akhir'] }}</td>
                                                    <td>{{ $slip['periode'] }}</td>
                                                    <td>{{ $slip['closing_ke'] }}</td>
                                                    <td class="text-end">Rp {{ number_format($slip['take_home_pay'], 0, ',', '.') }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">Tidak ada riwayat slip gaji</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- THR / Bonus -->
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-gift me-2"></i>THR / Bonus</h6>
                                </div>
                                <div class="card-body">
                                    @if(count($payrollInfo['thr_bonus']) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Periode</th>
                                                    <th class="text-end">Jumlah</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($payrollInfo['thr_bonus'] as $thr)
                                                <tr>
                                                    <td>{{ $thr['periode'] ?? '-' }}</td>
                                                    <td class="text-end">Rp {{ number_format($thr['jumlah'] ?? 0, 0, ',', '.') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <p class="text-muted text-center mb-0">Tidak ada data THR/Bonus.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

