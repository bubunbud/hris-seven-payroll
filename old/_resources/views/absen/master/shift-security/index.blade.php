@extends('layouts.app')

@section('title', 'Master Shift Security - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-shield-alt me-2"></i>Master Shift Security / Satpam
                </h2>
                <a href="{{ route('master-shift-security.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>Tambah Shift
                </a>
            </div>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No.</th>
                                    <th width="10%">Kode Shift</th>
                                    <th width="15%">Nama Shift</th>
                                    <th width="15%">Jam Masuk</th>
                                    <th width="15%">Jam Pulang</th>
                                    <th width="10%">Durasi (Jam)</th>
                                    <th width="10%">Cross Day</th>
                                    <th width="10%">Toleransi</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shifts as $index => $shift)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $shift->vcKodeShift }}</span>
                                    </td>
                                    <td><strong>{{ $shift->vcNamaShift }}</strong></td>
                                    <td>
                                        <i class="fas fa-clock text-success me-1"></i>
                                        {{ \Carbon\Carbon::parse($shift->dtJamMasuk)->format('H:i') }}
                                    </td>
                                    <td>
                                        <i class="fas fa-clock text-danger me-1"></i>
                                        {{ \Carbon\Carbon::parse($shift->dtJamPulang)->format('H:i') }}
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ number_format($shift->intDurasiJam, 2) }} jam</span>
                                    </td>
                                    <td>
                                        @if($shift->isCrossDay)
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-moon me-1"></i>Ya
                                        </span>
                                        @else
                                        <span class="badge bg-secondary">Tidak</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>
                                            Masuk: ±{{ $shift->intToleransiMasuk }}m<br>
                                            Pulang: ±{{ $shift->intToleransiPulang }}m
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('master-shift-security.edit', $shift->vcKodeShift) }}"
                                                class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('master-shift-security.destroy', $shift->vcKodeShift) }}"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus shift ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Tidak ada data shift security</p>
                                        <a href="{{ route('master-shift-security.create') }}" class="btn btn-sm btn-primary mt-2">
                                            <i class="fas fa-plus me-1"></i>Tambah Shift Pertama
                                        </a>
                                    </td>
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
@endsection
