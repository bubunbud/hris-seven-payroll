@extends('layouts.app')

@section('title', 'Pengelolaan Permission - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-key me-2"></i>Pengelolaan Permission
                </h2>
                <a href="{{ route('permissions.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>Tambah Permission
                </a>
            </div>

            @php
            $moduleLabels = [
            'master-data' => 'Master Data',
            'absensi' => 'Absensi',
            'proses-gaji' => 'Proses Payroll',
            'laporan' => 'Laporan',
            'settings' => 'Settings',
            ];
            @endphp

            <!-- Filter & Search -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('permissions.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Cari</label>
                                <input type="text" class="form-control" id="search" name="search"
                                    value="{{ request('search') }}" placeholder="Nama, Slug, atau Deskripsi">
                            </div>
                            <div class="col-md-3">
                                <label for="module" class="form-label">Module</label>
                                <select class="form-select" id="module" name="module">
                                    <option value="">Semua Module</option>
                                    @foreach($modules as $mod)
                                    @php
                                    $moduleName = $moduleLabels[$mod] ?? ($mod ? ucwords(str_replace('-', ' ', $mod)) : 'Lainnya');
                                    @endphp
                                    <option value="{{ $mod }}" {{ request('module') == $mod ? 'selected' : '' }}>
                                        {{ $moduleName }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 me-2">
                                    <i class="fas fa-search me-2"></i>Cari
                                </button>
                                <a href="{{ route('permissions.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Alert -->
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

            <!-- Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="25%">Nama Permission</th>
                                    <th width="20%">Slug</th>
                                    <th width="20%">Module</th>
                                    <th width="20%">Deskripsi</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permissions as $permission)
                                <tr>
                                    <td>{{ $permissions->firstItem() + $loop->index }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $permission->name }}</div>
                                        <small class="text-muted">ID: {{ $permission->id }}</small>
                                    </td>
                                    <td>
                                        <code class="text-primary">{{ $permission->slug }}</code>
                                    </td>
                                    <td>
                                        @if($permission->module)
                                        @php
                                        $moduleName = $moduleLabels[$permission->module] ?? ucwords(str_replace('-', ' ', $permission->module));
                                        @endphp
                                        <span class="badge bg-info">{{ $moduleName }}</span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-muted small">{{ $permission->description ?: '-' }}</div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('permissions.show', $permission->id) }}" class="btn btn-outline-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('permissions.edit', $permission->id) }}" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($permission->roles()->count() == 0)
                                            <form action="{{ route('permissions.destroy', $permission->id) }}" method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus permission ini?');" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Tidak ada data permission</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($permissions->hasPages())
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
                        <div class="text-muted small">
                            Menampilkan {{ $permissions->firstItem() }} sampai {{ $permissions->lastItem() }} dari {{ $permissions->total() }} permission
                        </div>
                        <nav aria-label="Navigasi halaman">
                            {{ $permissions->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection





