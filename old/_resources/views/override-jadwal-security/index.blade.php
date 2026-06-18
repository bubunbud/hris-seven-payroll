@extends('layouts.app')

@section('title', 'List Override Jadwal Security - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-history me-2"></i>List Override Jadwal Security
                </h2>
            </div>

            <!-- Filter Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('override-jadwal-security.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="dari_tanggal" class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" id="dari_tanggal" name="dari_tanggal"
                                    value="{{ $dariTanggal }}">
                            </div>
                            <div class="col-md-3">
                                <label for="sampai_tanggal" class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" id="sampai_tanggal" name="sampai_tanggal"
                                    value="{{ $sampaiTanggal }}">
                            </div>
                            <div class="col-md-2">
                                <label for="nik" class="form-label">NIK</label>
                                <input type="text" class="form-control" id="nik" name="nik"
                                    value="{{ $nik }}" placeholder="Cari NIK">
                            </div>
                            <div class="col-md-2">
                                <label for="nama" class="form-label">Nama</label>
                                <input type="text" class="form-control" id="nama" name="nama"
                                    value="{{ $nama }}" placeholder="Cari Nama">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-1"></i>Cari
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Data Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No.</th>
                                    <th width="10%">Tanggal Override</th>
                                    <th width="8%">NIK</th>
                                    <th width="15%">Nama Satpam</th>
                                    <th width="10%">Tanggal Jadwal</th>
                                    <th width="12%">Shift Lama</th>
                                    <th width="12%">Shift Baru</th>
                                    <th width="20%">Alasan</th>
                                    <th width="8%">User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($overrides as $index => $override)
                                <tr>
                                    <td>{{ $overrides->firstItem() + $index }}</td>
                                    <td>
                                        <i class="fas fa-calendar text-primary me-1"></i>
                                        {{ \Carbon\Carbon::parse($override->dtOverrideAt)->format('d/m/Y H:i') }}
                                    </td>
                                    <td><strong>{{ $override->vcNik }}</strong></td>
                                    <td>
                                        <i class="fas fa-user text-info me-1"></i>
                                        {{ $override->karyawan->Nama ?? 'N/A' }}
                                    </td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($override->dtTanggal)->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        @if($override->intShiftLama)
                                        <span class="badge bg-secondary">
                                            {{ $shiftNames[$override->intShiftLama] ?? 'Shift ' . $override->intShiftLama }}
                                        </span>
                                        @else
                                        <span class="badge bg-dark">OFF / Tidak Ada</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            {{ $shiftNames[$override->intShiftBaru] ?? 'Shift ' . $override->intShiftBaru }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ strlen($override->vcAlasan) > 50 ? substr($override->vcAlasan, 0, 50) . '...' : $override->vcAlasan }}</small>
                                        @if(strlen($override->vcAlasan) > 50)
                                        <a href="{{ route('override-jadwal-security.show', $override->id) }}"
                                            class="text-primary" title="Lihat detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endif
                                    </td>
                                    <td>
                                        <small>
                                            <i class="fas fa-user-circle text-secondary me-1"></i>
                                            {{ $override->vcOverrideBy }}
                                        </small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Tidak ada data override</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($overrides->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            Menampilkan {{ $overrides->firstItem() }} sampai {{ $overrides->lastItem() }} dari {{ $overrides->total() }} data
                        </div>
                        <nav>
                            {{ $overrides->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection