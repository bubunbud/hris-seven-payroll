@extends('layouts.app')

@section('title', 'Browse Tidak Absen - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-calendar-times me-2"></i>Browse Tidak Absen (Alpha)
                </h2>
            </div>

            <!-- Filter Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('browse-tidak-absen.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="dari_tanggal" class="form-label">Dari Tanggal</label>
                                <div class="input-group">
                                    <input type="date" class="form-control" id="dari_tanggal" name="dari_tanggal"
                                        value="{{ $startDate }}">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="sampai_tanggal" class="form-label">Sampai Tanggal</label>
                                <div class="input-group">
                                    <input type="date" class="form-control" id="sampai_tanggal" name="sampai_tanggal"
                                        value="{{ $endDate }}">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="nik" class="form-label">NIK</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="nik" name="nik"
                                        value="{{ $nik }}" placeholder="Cari NIK">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="nama" class="form-label">Nama</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="nama" name="nama"
                                        value="{{ $nama }}" placeholder="Cari Nama">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="group" class="form-label">Group</label>
                                <select class="form-select" id="group" name="group">
                                    <option value="Semua Group" {{ $group == 'Semua Group' ? 'selected' : '' }}>Semua Group</option>
                                    @foreach($groups as $groupOption)
                                    <option value="{{ $groupOption }}" {{ $group == $groupOption ? 'selected' : '' }}>
                                        {{ $groupOption }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary w-100 shadow-sm px-4">
                                        <i class="fas fa-eye me-2"></i>Preview
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Data Count -->
            <div class="alert alert-warning">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Jumlah Data Alpha: {{ number_format($totalData) }}.</strong>
                <small class="d-block mt-1">Menampilkan karyawan aktif yang tidak ada data absensi dan tidak ada data tidak masuk pada hari kerja normal.</small>
            </div>

            <!-- Data Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover table-striped" id="absenTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="8%">Tanggal</th>
                                    <th width="7%">NIK</th>
                                    <th width="15%">Nama</th>
                                    <th width="12%">Divisi</th>
                                    <th width="12%">Bagian</th>
                                    <th width="8%">Jam Masuk</th>
                                    <th width="8%">Jam Pulang</th>
                                    <th width="7%">Total Jam</th>
                                    <th width="8%">Shift Terjadwal</th>
                                    <th width="8%">Shift Aktual</th>
                                    <th width="7%">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($absens as $item)
                                @php
                                $dtTanggal = $item['dtTanggal'] ?? null;
                                $vcNik = $item['vcNik'] ?? '';
                                $Nama = $item['Nama'] ?? 'N/A';
                                $vcNamaDivisi = $item['vcNamaDivisi'] ?? 'N/A';
                                $vcNamaBagian = $item['vcNamaBagian'] ?? 'N/A';
                                $dtJamMasuk = $item['dtJamMasuk'] ?? null;
                                $dtJamKeluar = $item['dtJamKeluar'] ?? null;
                                $total_jam = $item['total_jam'] ?? 0;
                                $status = $item['status'] ?? 'Alpha';
                                $badgeClass = 'bg-danger';
                                @endphp
                                <tr>
                                    <td>
                                        <i class="fas fa-calendar text-primary me-1"></i>
                                        {{ $dtTanggal ? \Carbon\Carbon::parse($dtTanggal)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>
                                        <strong>{{ $vcNik }}</strong>
                                    </td>
                                    <td>
                                        <i class="fas fa-user text-info me-1"></i>
                                        {{ $Nama }}
                                    </td>
                                    <td>
                                        <i class="fas fa-building text-secondary me-1"></i>
                                        {{ $vcNamaDivisi }}
                                    </td>
                                    <td>
                                        <i class="fas fa-sitemap text-warning me-1"></i>
                                        {{ $vcNamaBagian }}
                                    </td>
                                    <td>
                                        <span class="text-muted">-</span>
                                    </td>
                                    <td>
                                        <span class="text-muted">-</span>
                                    </td>
                                    <td>
                                        <span class="text-muted">-</span>
                                    </td>
                                    <td>
                                        <span class="text-muted">-</span>
                                    </td>
                                    <td>
                                        <span class="text-muted">-</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Tidak ada data Alpha untuk periode ini</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($absens->hasPages())
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
                        <div class="text-muted small">
                            Menampilkan {{ $absens->firstItem() }} sampai {{ $absens->lastItem() }} dari {{ $absens->total() }} data
                        </div>
                        <nav aria-label="Navigasi halaman">
                            {{ $absens->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Legend -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-info-circle me-2"></i>Keterangan
                    </h6>
                    <div class="row">
                        <div class="col-md-12">
                            <span class="badge bg-danger me-2">Alpha</span>
                            <span class="text-muted">Karyawan aktif yang tidak ada data absensi (jam masuk dan jam pulang) dan tidak ada data tidak masuk pada hari kerja normal.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Auto-submit form on date change
    document.getElementById('dari_tanggal').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });

    document.getElementById('sampai_tanggal').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });

    // Auto-submit form on group change
    document.getElementById('group').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
</script>
@endpush

