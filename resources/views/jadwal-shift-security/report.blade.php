@extends('layouts.app')

@section('title', 'Report Jadwal Shift Satpam - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Report Jadwal Shift Satpam
                </h2>
                <a href="{{ route('jadwal-shift-security.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Periode -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('jadwal-shift-security.report') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label for="bulan_awal" class="form-label">Bulan Awal</label>
                                <select class="form-select" id="bulan_awal" name="bulan_awal">
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ $bulanAwal == $i ? 'selected' : '' }}>
                                            {{ Carbon\Carbon::create(null, $i, 1)->locale('id')->monthName }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="tahun_awal" class="form-label">Tahun Awal</label>
                                <select class="form-select" id="tahun_awal" name="tahun_awal">
                                    @for($i = date('Y') - 2; $i <= date('Y') + 2; $i++)
                                        <option value="{{ $i }}" {{ $tahunAwal == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="bulan_akhir" class="form-label">Bulan Akhir</label>
                                <select class="form-select" id="bulan_akhir" name="bulan_akhir">
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ $bulanAkhir == $i ? 'selected' : '' }}>
                                            {{ Carbon\Carbon::create(null, $i, 1)->locale('id')->monthName }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="tahun_akhir" class="form-label">Tahun Akhir</label>
                                <select class="form-select" id="tahun_akhir" name="tahun_akhir">
                                    @for($i = date('Y') - 2; $i <= date('Y') + 2; $i++)
                                        <option value="{{ $i }}" {{ $tahunAkhir == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_nama" class="form-label">Filter NIK / Nama</label>
                                <div class="input-group">
                                    <input type="text"
                                        class="form-control"
                                        id="filter_nama"
                                        name="filter_nama"
                                        value="{{ $filterNama ?? '' }}"
                                        placeholder="Cari NIK atau Nama...">
                                    @if(!empty($filterNama))
                                    <button type="button"
                                        class="btn btn-outline-secondary"
                                        onclick="document.getElementById('filter_nama').value=''; this.form.submit();"
                                        title="Hapus filter">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Buttons -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Periode:</strong> 
                            {{ Carbon\Carbon::create($tahunAwal, $bulanAwal, 1)->locale('id')->monthName }} {{ $tahunAwal }} 
                            - 
                            {{ Carbon\Carbon::create($tahunAkhir, $bulanAkhir, 1)->locale('id')->monthName }} {{ $tahunAkhir }}
                            @if(!empty($filterNama))
                            <span class="badge bg-info ms-2">Filter: {{ $filterNama }}</span>
                            @endif
                        </div>
                        <div>
                            <a href="{{ route('jadwal-shift-security.report', array_merge(request()->all(), ['export' => 'csv'])) }}" 
                               class="btn btn-success">
                                <i class="fas fa-file-csv me-1"></i>Export CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Data -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Data Jadwal Shift</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Nama</th>
                                    <th>Tanggal</th>
                                    <th>Shift</th>
                                    <th>Keterangan</th>
                                    <th>Override</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $no = 1;
                                    $totalRecords = 0;
                                @endphp
                                @forelse($jadwalGrouped as $nik => $data)
                                    @foreach($data['jadwal'] as $jadwal)
                                        <tr>
                                            <td>{{ $no++ }}</td>
                                            <td>{{ $nik }}</td>
                                            <td>{{ $data['karyawan']->Nama ?? '-' }}</td>
                                            <td>{{ $jadwal->dtTanggal->format('d/m/Y') }}</td>
                                            <td>
                                                @if($jadwal->intShift === null)
                                                    <span class="badge bg-secondary">OFF</span>
                                                @else
                                                    <span class="badge bg-primary">Shift {{ $jadwal->intShift }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $jadwal->vcKeterangan ?? '-' }}</td>
                                            <td>
                                                @if($jadwal->isOverride)
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>Ya
                                                    </span>
                                                @else
                                                    <span class="badge bg-success">Tidak</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @php $totalRecords++; @endphp
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Tidak ada data jadwal untuk periode yang dipilih
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Total Record:</strong></td>
                                    <td><strong>{{ $totalRecords }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary by Shift -->
    @if(!empty($jadwalGrouped))
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Summary per Shift</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @php
                            $summary = [
                                'shift1' => 0,
                                'shift2' => 0,
                                'shift3' => 0,
                                'off' => 0,
                                'override' => 0
                            ];
                            foreach($jadwalGrouped as $data) {
                                foreach($data['jadwal'] as $jadwal) {
                                    if($jadwal->intShift === null) {
                                        $summary['off']++;
                                    } elseif($jadwal->intShift == 1) {
                                        $summary['shift1']++;
                                    } elseif($jadwal->intShift == 2) {
                                        $summary['shift2']++;
                                    } elseif($jadwal->intShift == 3) {
                                        $summary['shift3']++;
                                    }
                                    if($jadwal->isOverride) {
                                        $summary['override']++;
                                    }
                                }
                            }
                        @endphp
                        <div class="col-md-2">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $summary['shift1'] }}</h3>
                                    <p class="mb-0">Shift 1</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $summary['shift2'] }}</h3>
                                    <p class="mb-0">Shift 2</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $summary['shift3'] }}</h3>
                                    <p class="mb-0">Shift 3</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $summary['off'] }}</h3>
                                    <p class="mb-0">OFF</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-dark">
                                <div class="card-body text-center">
                                    <h3>{{ $summary['override'] }}</h3>
                                    <p class="mb-0">Override</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-dark text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $totalRecords }}</h3>
                                    <p class="mb-0">Total</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

