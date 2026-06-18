@extends('layouts.app')

@section('title', 'Dashboard Level Group (Holding View) - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-building me-2"></i>Dashboard Level Group (Holding View)
                </h2>
                <div class="text-muted">
                    <i class="fas fa-calendar me-1"></i>
                    {{ \Carbon\Carbon::now()->format('d F Y') }}
                </div>
            </div>
        </div>
    </div>

    <!-- A. Ringkasan SDM Group -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>A. Ringkasan SDM Group
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <!-- Total Karyawan -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Total Karyawan (All BU)</h6>
                                    <h2 class="card-title mb-0">{{ number_format($sdmSummary['total_karyawan']) }}</h2>
                                    <small class="text-white-50">Karyawan Aktif</small>
                                </div>
                            </div>
                        </div>

                        <!-- Rata-rata Usia -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Rata-rata Usia</h6>
                                    <h2 class="card-title mb-0">{{ number_format($sdmSummary['rata_rata_usia'], 1) }}</h2>
                                    <small class="text-white-50">Tahun</small>
                                </div>
                            </div>
                        </div>

                        <!-- Rata-rata Masa Kerja -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Rata-rata Masa Kerja</h6>
                                    <h2 class="card-title mb-0">{{ number_format($sdmSummary['rata_rata_masa_kerja'], 1) }}</h2>
                                    <small class="text-white-50">Tahun</small>
                                </div>
                            </div>
                        </div>

                        <!-- Rasio Gender -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-warning text-dark h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Rasio Gender</h6>
                                    <div class="d-flex justify-content-around">
                                        <div>
                                            <small class="d-block">Laki-laki</small>
                                            <strong>{{ number_format($sdmSummary['rasio_gender']['Laki-laki'] ?? 0) }}</strong>
                                        </div>
                                        <div>
                                            <small class="d-block">Perempuan</small>
                                            <strong>{{ number_format($sdmSummary['rasio_gender']['Perempuan'] ?? 0) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Komposisi Status Pegawai -->
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-user-tag me-2"></i>Komposisi Status Pegawai</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartStatusPegawai" height="200"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Komposisi Group Pegawai -->
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-users-cog me-2"></i>Komposisi Group Pegawai</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartGroupPegawai" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <!-- Perbandingan Jumlah Karyawan per BU -->
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Perbandingan Jumlah Karyawan per Business Unit</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartKaryawanPerBU" height="120"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- B. Headcount Movement -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>B. Headcount Movement
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Join Tahun Ini</h6>
                                    <h2 class="card-title mb-0">{{ number_format($headcountMovement['join_tahun_ini']) }}</h2>
                                    <small class="text-white-50">Karyawan Baru</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card bg-danger text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Resign Tahun Ini</h6>
                                    <h2 class="card-title mb-0">{{ number_format($headcountMovement['resign_tahun_ini']) }}</h2>
                                    <small class="text-white-50">Karyawan Keluar</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card bg-warning text-dark h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Turnover Rate</h6>
                                    <h2 class="card-title mb-0">{{ number_format($headcountMovement['turnover_rate'], 2) }}%</h2>
                                    <small class="text-muted">Per Tahun</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Net Growth</h6>
                                    <h2 class="card-title mb-0 {{ $headcountMovement['net_growth'] >= 0 ? 'text-white' : 'text-warning' }}">
                                        {{ $headcountMovement['net_growth'] >= 0 ? '+' : '' }}{{ number_format($headcountMovement['net_growth']) }}
                                    </h2>
                                    <small class="text-white-50">Karyawan</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Headcount Tahun Lalu</h6>
                                    <h3 class="text-muted">{{ number_format($headcountMovement['headcount_tahun_lalu']) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Headcount Tahun Ini</h6>
                                    <h3 class="text-primary">{{ number_format($headcountMovement['headcount_tahun_ini']) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- C. Payroll Summary Group -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>C. Payroll Summary Group
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="card bg-warning text-dark h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Total Payroll Bulan Ini</h6>
                                    <h4 class="card-title mb-0">Rp {{ number_format($payrollSummary['payroll_bulan_ini'], 0, ',', '.') }}</h4>
                                    <small class="text-muted">Bulan Berjalan</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Total Payroll YTD</h6>
                                    <h4 class="card-title mb-0">Rp {{ number_format($payrollSummary['payroll_ytd'], 0, ',', '.') }}</h4>
                                    <small class="text-white-50">Year to Date</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Rata-rata Gaji</h6>
                                    <h4 class="card-title mb-0">Rp {{ number_format($payrollSummary['rata_rata_gaji'], 0, ',', '.') }}</h4>
                                    <small class="text-white-50">Per Karyawan/Bulan</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2">Total Karyawan</h6>
                                    <h4 class="card-title mb-0">{{ number_format($payrollSummary['total_karyawan_bulan_ini']) }}</h4>
                                    <small class="text-white-50">Bulan Ini</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Payroll Cost per Business Unit</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Business Unit</th>
                                                    <th class="text-end">Total Payroll</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($payrollSummary['payroll_per_bu'] as $index => $bu)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td><strong>{{ $bu->vcNamaDivisi }}</strong></td>
                                                    <td class="text-end">Rp {{ number_format($bu->total_payroll ?? 0, 0, ',', '.') }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">Tidak ada data payroll</td>
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

    <!-- D. Alert & Notifikasi Eksekutif -->
    @if(count($executiveAlerts) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>D. Alert & Notifikasi Eksekutif
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($executiveAlerts as $alert)
                    <div class="alert alert-{{ $alert['type'] }} alert-dismissible fade show" role="alert">
                        <h6 class="alert-heading">
                            <i class="fas fa-{{ $alert['icon'] }} me-2"></i>{{ $alert['title'] }}
                        </h6>
                        <p class="mb-2">{{ $alert['message'] }}</p>
                        @if(isset($alert['data']) && count($alert['data']) > 0)
                        <div class="mt-3">
                            @if(isset($alert['data'][0]['periodes']))
                            {{-- Format untuk Payroll Belum Diproses --}}
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Business Unit</th>
                                        <th>Jumlah Periode</th>
                                        <th>Detail Periode</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($alert['data'] as $data)
                                    <tr>
                                        <td><strong>{{ $data['divisi'] }}</strong></td>
                                        <td class="text-center">{{ $data['jumlah_periode'] }}</td>
                                        <td>
                                            @foreach($data['periodes'] as $periode)
                                            <span class="badge bg-secondary me-1">{{ $periode['periode'] }}</span>
                                            @endforeach
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @elseif(isset($alert['data'][0]['karyawans']))
                            {{-- Format untuk Kontrak Habis dan Pensiun --}}
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Business Unit</th>
                                        <th>Jumlah Karyawan</th>
                                        <th>Detail Karyawan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($alert['data'] as $data)
                                    <tr>
                                        <td><strong>{{ $data['divisi'] }}</strong></td>
                                        <td class="text-center">{{ $data['jumlah_karyawan'] }}</td>
                                        <td>
                                            <div class="row g-2">
                                                @foreach($data['karyawans'] as $karyawan)
                                                <div class="col-12">
                                                    <div class="card border-secondary mb-2">
                                                        <div class="card-body p-2">
                                                            <div class="d-flex justify-content-between align-items-start">
                                                                <div>
                                                                    <strong>{{ $karyawan['nama'] }}</strong> (NIK: {{ $karyawan['nik'] }})
                                                                    @if(isset($karyawan['tanggal_kontrak_habis']))
                                                                    <br>
                                                                    <small class="text-muted">
                                                                        Tgl Masuk: {{ $karyawan['tanggal_masuk'] }} | 
                                                                        Kontrak Habis: {{ $karyawan['tanggal_kontrak_habis'] }} | 
                                                                        Sisa: {{ $karyawan['sisa_hari'] }} hari
                                                                    </small>
                                                                    @elseif(isset($karyawan['tanggal_pensiun']))
                                                                    <br>
                                                                    <small class="text-muted">
                                                                        Tgl Lahir: {{ $karyawan['tanggal_lahir'] }} | 
                                                                        Usia: {{ $karyawan['usia'] }} tahun | 
                                                                        Pensiun: {{ $karyawan['tanggal_pensiun'] }} | 
                                                                        Sisa: {{ $karyawan['sisa_bulan'] }} bulan
                                                                    </small>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @endif
                        </div>
                        @endif
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .card {
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-2px);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // Chart Komposisi Status Pegawai (Pie Chart) dengan label persentase
    @if(count($sdmSummary['komposisi_status']) > 0)
    // Plugin untuk menampilkan persentase di pie chart
    const pieLabelPlugin = {
        id: 'pieLabel',
        afterDatasetsDraw: function(chart) {
            const ctx = chart.ctx;
            ctx.save();
            ctx.font = 'bold 12px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = '#333';
            
            const data = chart.data.datasets[0].data;
            const total = data.reduce((a, b) => a + b, 0);
            
            chart.data.datasets.forEach((dataset, i) => {
                const meta = chart.getDatasetMeta(i);
                meta.data.forEach((element, index) => {
                    const value = dataset.data[index];
                    const percentage = ((value / total) * 100).toFixed(1);
                    const position = element.tooltipPosition();
                    // Hanya tampilkan persentase
                    ctx.fillText(percentage + '%', position.x, position.y);
                });
            });
            ctx.restore();
        }
    };
    
    const ctxStatusPegawai = document.getElementById('chartStatusPegawai').getContext('2d');
    const chartStatusPegawai = new Chart(ctxStatusPegawai, {
        type: 'pie',
        plugins: [pieLabelPlugin],
        data: {
            labels: {!! json_encode(array_keys($sdmSummary['komposisi_status'])) !!},
            datasets: [{
                data: {!! json_encode(array_values($sdmSummary['komposisi_status'])) !!},
                backgroundColor: [
                    '#2596be',
                    '#28a745',
                    '#ffc107',
                    '#dc3545',
                    '#6c757d',
                    '#17a2b8'
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
                                label += ': ';
                            }
                            label += context.parsed + ' orang';
                            return label;
                        }
                    }
                }
            }
        }
    });
    @else
    document.getElementById('chartStatusPegawai').parentElement.innerHTML = '<p class="text-center text-muted">Tidak ada data</p>';
    @endif

    // Chart Komposisi Group Pegawai (Pie Chart) dengan label persentase
    @if(count($sdmSummary['komposisi_group_pegawai']) > 0)
    // Plugin untuk menampilkan persentase di pie chart
    const pieLabelPlugin2 = {
        id: 'pieLabel2',
        afterDatasetsDraw: function(chart) {
            const ctx = chart.ctx;
            ctx.save();
            ctx.font = 'bold 12px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = '#333';
            
            const data = chart.data.datasets[0].data;
            const total = data.reduce((a, b) => a + b, 0);
            
            chart.data.datasets.forEach((dataset, i) => {
                const meta = chart.getDatasetMeta(i);
                meta.data.forEach((element, index) => {
                    const value = dataset.data[index];
                    const percentage = ((value / total) * 100).toFixed(1);
                    const position = element.tooltipPosition();
                    // Hanya tampilkan persentase
                    ctx.fillText(percentage + '%', position.x, position.y);
                });
            });
            ctx.restore();
        }
    };
    
    const ctxGroupPegawai = document.getElementById('chartGroupPegawai').getContext('2d');
    const chartGroupPegawai = new Chart(ctxGroupPegawai, {
        type: 'pie',
        plugins: [pieLabelPlugin2],
        data: {
            labels: {!! json_encode(array_keys($sdmSummary['komposisi_group_pegawai'])) !!},
            datasets: [{
                data: {!! json_encode(array_values($sdmSummary['komposisi_group_pegawai'])) !!},
                backgroundColor: [
                    '#2596be',
                    '#28a745',
                    '#ffc107',
                    '#dc3545',
                    '#6c757d',
                    '#17a2b8',
                    '#6610f2',
                    '#e83e8c'
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
                                label += ': ';
                            }
                            label += context.parsed + ' orang';
                            return label;
                        }
                    }
                }
            }
        }
    });
    @else
    document.getElementById('chartGroupPegawai').parentElement.innerHTML = '<p class="text-center text-muted">Tidak ada data</p>';
    @endif

    // Chart Karyawan per BU (Bar Chart) dengan label angka di atas bar
    @if($sdmSummary['karyawan_per_bu']->count() > 0)
    // Plugin untuk menampilkan label di atas bar
    const barLabelPlugin = {
        id: 'barLabel',
        afterDatasetsDraw: function(chart) {
            const ctx = chart.ctx;
            ctx.save();
            ctx.font = 'bold 12px Arial';
            ctx.fillStyle = '#333';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'bottom';
            
            chart.data.datasets.forEach((dataset, i) => {
                const meta = chart.getDatasetMeta(i);
                meta.data.forEach((bar, index) => {
                    const data = dataset.data[index];
                    // Pastikan label selalu muncul, bahkan untuk bar yang kecil
                    const yPos = Math.max(bar.y - 8, 10);
                    ctx.fillText(data, bar.x, yPos);
                });
            });
            ctx.restore();
        }
    };
    
    const ctxKaryawanPerBU = document.getElementById('chartKaryawanPerBU').getContext('2d');
    const chartKaryawanPerBU = new Chart(ctxKaryawanPerBU, {
        type: 'bar',
        plugins: [barLabelPlugin],
        data: {
            labels: {!! json_encode($sdmSummary['karyawan_per_bu']->pluck('vcNamaDivisi')->toArray()) !!},
            datasets: [{
                label: 'Jumlah Karyawan',
                data: {!! json_encode($sdmSummary['karyawan_per_bu']->pluck('jumlah')->toArray()) !!},
                backgroundColor: '#2596be',
                borderColor: '#1f7da1',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: true
                }
            },
            animation: {
                duration: 1000,
                onComplete: function() {
                    // Trigger redraw untuk memastikan label muncul
                    chartKaryawanPerBU.update('none');
                }
            }
        }
    });
    @else
    document.getElementById('chartKaryawanPerBU').parentElement.innerHTML = '<p class="text-center text-muted">Tidak ada data</p>';
    @endif
</script>
@endpush

