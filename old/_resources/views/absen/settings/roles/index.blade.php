@extends('layouts.app')

@section('title', 'Pengelolaan Role - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-user-shield me-2"></i>Pengelolaan Role
                </h2>
                <a href="{{ route('roles.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>Tambah Role
                </a>
            </div>

            <!-- Filter & Search -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('roles.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Cari</label>
                                <input type="text" class="form-control" id="search" name="search"
                                    value="{{ request('search') }}" placeholder="Nama, Slug, atau Deskripsi">
                            </div>
                            <div class="col-md-3">
                                <label for="is_active" class="form-label">Status</label>
                                <select class="form-select" id="is_active" name="is_active">
                                    <option value="">Semua Status</option>
                                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 me-2">
                                    <i class="fas fa-search me-2"></i>Cari
                                </button>
                                <a href="{{ route('roles.index') }}" class="btn btn-secondary">
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
                                    <th width="20%">Nama Role</th>
                                    <th width="15%">Slug</th>
                                    <th width="30%">Deskripsi</th>
                                    <th width="15%">Permissions</th>
                                    <th width="10%">Status</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                <tr>
                                    <td>{{ $roles->firstItem() + $loop->index }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $role->name }}</div>
                                        <small class="text-muted">ID: {{ $role->id }}</small>
                                    </td>
                                    <td>
                                        <code class="text-primary">{{ $role->slug }}</code>
                                    </td>
                                    <td>
                                        <div class="text-muted small">{{ $role->description ?: '-' }}</div>
                                    </td>
                                    <td>
                                        @if($role->permissions->count() > 0)
                                        <span class="badge bg-info">{{ $role->permissions->count() }} Permission</span>
                                        <div class="mt-1">
                                            @foreach($role->permissions->take(3) as $permission)
                                            <small class="badge bg-secondary me-1">{{ $permission->name }}</small>
                                            @endforeach
                                            @if($role->permissions->count() > 3)
                                            <small class="text-muted">+{{ $role->permissions->count() - 3 }} lainnya</small>
                                            @endif
                                        </div>
                                        @else
                                        <span class="text-muted">Tidak ada permission</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($role->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                        @else
                                        <span class="badge bg-danger">Tidak Aktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('roles.show', $role->id) }}" class="btn btn-outline-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($role->users()->count() == 0)
                                            <form action="{{ route('roles.destroy', $role->id) }}" method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus role ini?');" class="d-inline">
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
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Tidak ada data role</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($roles->hasPages())
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
                        <div class="text-muted small">
                            Menampilkan {{ $roles->firstItem() }} sampai {{ $roles->lastItem() }} dari {{ $roles->total() }} role
                        </div>
                        <nav aria-label="Navigasi halaman">
                            {{ $roles->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection





