@extends('layouts.app')

@section('title', 'Dashboard Level Business Unit (BU View) - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-building me-2"></i>Dashboard Level Business Unit (BU View)
                </h2>
                <div>
                    @if($isAdmin)
                    <form method="GET" action="{{ route('dashboard.bu') }}" class="d-inline-block me-3">
                        <div class="input-group">
                            <select name="bu_kode" class="form-select" onchange="this.form.submit()" style="min-width: 250px;">
                                <option value="">-- Pilih Business Unit --</option>
                                @foreach($allDivisis as $divisi)
                                <option value="{{ $divisi->vcKodeDivisi }}" {{ $buKode == $divisi->vcKodeDivisi ? 'selected' : '' }}>
                                    {{ $divisi->vcNamaDivisi }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                    @endif
                    <span class="badge bg-primary fs-6">{{ $bu->vcNamaDivisi ?? 'N/A' }}</span>
                    <div class="text-muted mt-1">
                        <i class="fas fa-calendar me-1"></i>
                        {{ \Carbon\Carbon::now()->format('d F Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- A. Statistik SDM BU -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>A. Statistik SDM BU
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <!-- Total Karyawan BU -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Total Karyawan BU</h6>
                                    <h2 class="card-title mb-0">{{ number_format($sdmStats['total_karyawan']) }}</h2>
                                    <small class="text-white-50">Karyawan Aktif</small>
                                </div>
                            </div>
                        </div>

                        <!-- Group Pegawai 1 -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">{{ $kontrakStats['group_pegawai_1_nama'] ?? 'N/A' }}</h6>
                                    <h2 class="card-title mb-0">{{ number_format($kontrakStats['group_pegawai_1_jumlah'] ?? 0) }}</h2>
                                    <small class="text-white-50">Group Pegawai</small>
                                </div>
                            </div>
                        </div>

                        <!-- Group Pegawai 2 -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">{{ $kontrakStats['group_pegawai_2_nama'] ?? 'N/A' }}</h6>
                                    <h2 class="card-title mb-0">{{ number_format($kontrakStats['group_pegawai_2_jumlah'] ?? 0) }}</h2>
                                    <small class="text-white-50">Group Pegawai</small>
                                </div>
                            </div>
                        </div>

                        <!-- Total Departemen -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-warning text-dark h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Total Departemen</h6>
                                    <h2 class="card-title mb-0">{{ number_format($sdmStats['distribusi_dept']->count()) }}</h2>
                                    <small class="text-muted">Departemen</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Distribusi per Departemen -->
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Distribusi per Departemen</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartDistribusiDept" height="200"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Status Kepegawaian -->
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-user-tag me-2"></i>Status Kepegawaian</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartStatusKepegawaian" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-sitemap me-2"></i>Struktur Jabatan (Top 10)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Jabatan</th>
                                                    <th class="text-end">Jumlah Karyawan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($sdmStats['struktur_jabatan'] as $index => $jabatan)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td><strong>{{ $jabatan->vcNamaJabatan }}</strong></td>
                                                    <td class="text-end">{{ number_format($jabatan->jumlah) }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">Tidak ada data jabatan</td>
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

    <!-- B. Absensi & Kehadiran -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-check me-2"></i>B. Absensi & Kehadiran
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Persentase Kehadiran -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Persentase Kehadiran</h6>
                                    <h2 class="card-title mb-0">{{ number_format($absensiStats['persentase_kehadiran'], 2) }}%</h2>
                                    <small class="text-white-50">Bulan Ini</small>
                                </div>
                            </div>
                        </div>

                        <!-- Telat -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-warning text-dark h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Telat</h6>
                                    <h2 class="card-title mb-0">{{ number_format($absensiStats['telat']) }}</h2>
                                    <small class="text-muted">Kali</small>
                                </div>
                            </div>
                        </div>

                        <!-- Alpha -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-danger text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Alpha</h6>
                                    <h2 class="card-title mb-0">{{ number_format($absensiStats['alpha']) }}</h2>
                                    <small class="text-white-50">Kali</small>
                                </div>
                            </div>
                        </div>

                        <!-- Absensi Tidak Lengkap -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Absensi Tidak Lengkap</h6>
                                    <h2 class="card-title mb-0">{{ number_format($absensiStats['absensi_tidak_lengkap']) }}</h2>
                                    <small class="text-white-50">Missing Log</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <!-- Lembur Total Jam -->
                        <div class="col-md-6 mb-3">
                            <div class="card bg-warning text-dark h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Lembur Total Jam</h6>
                                    <h3 class="card-title mb-0">{{ number_format($absensiStats['lembur_total_jam'], 2) }}</h3>
                                    <small class="text-muted">Jam</small>
                                </div>
                            </div>
                        </div>

                        <!-- Lembur Total Biaya -->
                        <div class="col-md-6 mb-3">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Lembur Total Biaya</h6>
                                    <h3 class="card-title mb-0">Rp {{ number_format($absensiStats['lembur_total_biaya'], 0, ',', '.') }}</h3>
                                    <small class="text-white-50">Bulan Ini</small>
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
                    <div class="row">
                        <!-- Sisa Cuti Rata-rata -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Sisa Cuti Rata-rata</h6>
                                    <h2 class="card-title mb-0">{{ number_format($cutiStats['sisa_cuti_rata_rata'], 2) }}</h2>
                                    <small class="text-white-50">Hari</small>
                                </div>
                            </div>
                        </div>

                        <!-- Cuti Diambil Bulan Ini -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Cuti Diambil Bulan Ini</h6>
                                    <h2 class="card-title mb-0">{{ number_format($cutiStats['cuti_diambil_bulan_ini']) }}</h2>
                                    <small class="text-white-50">Karyawan</small>
                                </div>
                            </div>
                        </div>

                        <!-- Cuti Pending Approval -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-warning text-dark h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Cuti Pending Approval</h6>
                                    <h2 class="card-title mb-0">{{ number_format($cutiStats['cuti_pending_approval']) }}</h2>
                                    <small class="text-muted">Menunggu</small>
                                </div>
                            </div>
                        </div>

                        <!-- Karyawan Minus Cuti -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-danger text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Karyawan Minus Cuti</h6>
                                    <h2 class="card-title mb-0">{{ number_format($cutiStats['karyawan_minus_cuti']) }}</h2>
                                    <small class="text-white-50">Karyawan</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- D. Payroll BU -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>D. Payroll BU
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Total Gaji Bruto -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Total Gaji Bruto</h6>
                                    <h4 class="card-title mb-0">Rp {{ number_format($payrollStats['total_gaji_bruto'], 0, ',', '.') }}</h4>
                                    <small class="text-white-50">Bulan Ini</small>
                                </div>
                            </div>
                        </div>

                        <!-- Total Potongan -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-danger text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Total Potongan</h6>
                                    <h4 class="card-title mb-0">Rp {{ number_format($payrollStats['total_potongan'], 0, ',', '.') }}</h4>
                                    <small class="text-white-50">Bulan Ini</small>
                                </div>
                            </div>
                        </div>

                        <!-- Total Take Home Pay -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Total Take Home Pay</h6>
                                    <h4 class="card-title mb-0">Rp {{ number_format($payrollStats['total_take_home_pay'], 0, ',', '.') }}</h4>
                                    <small class="text-white-50">Bulan Ini</small>
                                </div>
                            </div>
                        </div>

                        <!-- Lembur & Tunjangan -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Lembur & Tunjangan</h6>
                                    <h4 class="card-title mb-0">Rp {{ number_format($payrollStats['lembur_tunjangan'], 0, ',', '.') }}</h4>
                                    <small class="text-white-50">Bulan Ini</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <!-- Slip Gaji Sudah Terbit -->
                        <div class="col-md-6 mb-3">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Slip Gaji Sudah Terbit</h6>
                                    <h3 class="card-title mb-0">{{ number_format($payrollStats['slip_sudah_terbit']) }} / {{ number_format($payrollStats['total_karyawan']) }}</h3>
                                    <small class="text-white-50">Karyawan</small>
                                </div>
                            </div>
                        </div>

                        <!-- Slip Gaji Belum Terbit -->
                        <div class="col-md-6 mb-3">
                            <div class="card bg-warning text-dark h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Slip Gaji Belum Terbit</h6>
                                    <h3 class="card-title mb-0">{{ number_format($payrollStats['slip_belum_terbit']) }} / {{ number_format($payrollStats['total_karyawan']) }}</h3>
                                    <small class="text-muted">Karyawan</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- E. Kontrak & Masa Berlaku -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-contract me-2"></i>E. Kontrak & Masa Berlaku
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <!-- Kontrak Habis < 30 Hari -->
                        <div class="col-md-12 mb-3">
                            <div class="alert alert-warning">
                                <h6 class="alert-heading">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Kontrak Habis < 30 Hari
                                </h6>
                                <p class="mb-0">Terdapat <strong>{{ count($kontrakStats['kontrak_habis_30_hari']) }}</strong> karyawan dengan kontrak yang akan habis dalam 30 hari ke depan.</p>
                            </div>
                        </div>
                    </div>

                    @if(count($kontrakStats['kontrak_habis_30_hari']) > 0)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>NIK</th>
                                            <th>Nama</th>
                                            <th>Tanggal Masuk</th>
                                            <th>Tanggal Kontrak Habis</th>
                                            <th class="text-end">Sisa Hari</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($kontrakStats['kontrak_habis_30_hari'] as $index => $karyawan)
                                        <tr class="{{ $karyawan['sisa_hari'] <= 7 ? 'table-danger' : '' }}">
                                            <td>{{ $index + 1 }}</td>
                                            <td><strong>{{ $karyawan['nik'] }}</strong></td>
                                            <td>{{ $karyawan['nama'] }}</td>
                                            <td>{{ $karyawan['tanggal_masuk'] }}</td>
                                            <td>{{ $karyawan['tanggal_kontrak_habis'] }}</td>
                                            <td class="text-end">
                                                <span class="badge bg-{{ $karyawan['sisa_hari'] <= 7 ? 'danger' : 'warning' }}">
                                                    {{ $karyawan['sisa_hari'] }} hari
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <!-- Reminder Perpanjangan Kontrak -->
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-bell me-2"></i>Reminder Perpanjangan Kontrak (30-60 Hari)</h6>
                                </div>
                                <div class="card-body">
                                    @if(count($kontrakStats['reminder_perpanjangan']) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>No</th>
                                                    <th>NIK</th>
                                                    <th>Nama</th>
                                                    <th>Tanggal Masuk</th>
                                                    <th>Tanggal Kontrak Habis</th>
                                                    <th class="text-end">Sisa Hari</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($kontrakStats['reminder_perpanjangan'] as $index => $karyawan)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td><strong>{{ $karyawan['nik'] }}</strong></td>
                                                    <td>{{ $karyawan['nama'] }}</td>
                                                    <td>{{ $karyawan['tanggal_masuk'] }}</td>
                                                    <td>{{ $karyawan['tanggal_kontrak_habis'] }}</td>
                                                    <td class="text-end">
                                                        <span class="badge bg-info">{{ $karyawan['sisa_hari'] }} hari</span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <p class="text-muted text-center mb-0">Tidak ada reminder perpanjangan kontrak.</p>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // Chart Distribusi per Departemen
    const ctxDistribusiDept = document.getElementById('chartDistribusiDept').getContext('2d');
    const chartDistribusiDept = new Chart(ctxDistribusiDept, {
        type: 'pie',
        data: {
            labels: {!! json_encode($sdmStats['distribusi_dept']->pluck('vcNamaDept')->toArray()) !!},
            datasets: [{
                data: {!! json_encode($sdmStats['distribusi_dept']->pluck('jumlah')->toArray()) !!},
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                    '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Chart Status Kepegawaian
    const ctxStatusKepegawaian = document.getElementById('chartStatusKepegawaian').getContext('2d');
    const statusKepegawaianLabels = {!! json_encode(array_keys($sdmStats['status_kepegawaian'])) !!};
    const statusKepegawaianData = {!! json_encode(array_values($sdmStats['status_kepegawaian'])) !!};
    
    // Format labels dengan jumlah
    const statusKepegawaianLabelsWithCount = statusKepegawaianLabels.map((label, index) => {
        return label + ' (' + statusKepegawaianData[index] + ')';
    });
    
    const chartStatusKepegawaian = new Chart(ctxStatusKepegawaian, {
        type: 'doughnut',
        data: {
            labels: statusKepegawaianLabelsWithCount,
            datasets: [{
                data: statusKepegawaianData,
                backgroundColor: [
                    '#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label = label.split(' (')[0]; // Hapus jumlah dari label untuk tooltip
                            }
                            if (context.parsed !== null) {
                                label += ': ' + context.parsed + ' pegawai';
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection

