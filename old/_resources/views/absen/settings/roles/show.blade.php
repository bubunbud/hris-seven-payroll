@extends('layouts.app')

@section('title', 'Detail Role - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-user-shield me-2"></i>Detail Role
                </h2>
                <div class="d-flex gap-2">
                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <strong>Informasi Role</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="text-muted text-uppercase small fw-semibold">Nama Role</div>
                                    <div class="fs-5 fw-semibold">{{ $role->name }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted text-uppercase small fw-semibold">Slug</div>
                                    <div><code class="text-primary fs-6">{{ $role->slug }}</code></div>
                                </div>
                                <div class="col-12">
                                    <div class="text-muted text-uppercase small fw-semibold">Deskripsi</div>
                                    <div>{{ $role->description ?: '-' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted text-uppercase small fw-semibold">Status</div>
                                    <div>
                                        @if($role->is_active)
                                        <span class="badge bg-success fs-6">Aktif</span>
                                        @else
                                        <span class="badge bg-danger fs-6">Tidak Aktif</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted text-uppercase small fw-semibold">Jumlah User</div>
                                    <div><span class="badge bg-info fs-6">{{ $role->users()->count() }} User</span></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted text-uppercase small fw-semibold">Dibuat</div>
                                    <div>{{ $role->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted text-uppercase small fw-semibold">Diupdate</div>
                                    <div>{{ $role->updated_at->format('d/m/Y H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <strong>Permissions ({{ $role->permissions->count() }})</strong>
                        </div>
                        <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                            @php
                            $moduleLabels = [
                            'master-data' => 'Master Data',
                            'absensi' => 'Absensi',
                            'proses-gaji' => 'Proses Payroll',
                            'laporan' => 'Laporan',
                            'settings' => 'Settings',
                            ];
                            @endphp
                            @if($role->permissions->count() > 0)
                            @foreach($permissions as $module => $modulePermissions)
                            @php
                            $rolePermissions = $role->permissions->where('module', $module);
                            $moduleName = $moduleLabels[$module] ?? ($module ? ucwords(str_replace('-', ' ', $module)) : 'Lainnya');
                            @endphp
                            @if($rolePermissions->count() > 0)
                            <div class="mb-3">
                                <strong class="text-primary">{{ $moduleName }}</strong>
                                <ul class="list-unstyled mt-2">
                                    @foreach($rolePermissions as $permission)
                                    <li class="mb-1">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        {{ $permission->name }}
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                            @endforeach
                            @else
                            <p class="text-muted">Tidak ada permission yang di-assign</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection





