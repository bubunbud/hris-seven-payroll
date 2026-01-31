@extends('layouts.app')

@section('title', 'Detail Permission - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-key me-2"></i>Detail Permission
                </h2>
                <div class="d-flex gap-2">
                    <a href="{{ route('permissions.edit', $permission->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <a href="{{ route('permissions.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <strong>Informasi Permission</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="text-muted text-uppercase small fw-semibold">Nama Permission</div>
                                    <div class="fs-5 fw-semibold">{{ $permission->name }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted text-uppercase small fw-semibold">Slug</div>
                                    <div><code class="text-primary fs-6">{{ $permission->slug }}</code></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted text-uppercase small fw-semibold">Module</div>
                                    <div>
                                        @if($permission->module)
                                        @php
                                        $moduleLabels = [
                                        'master-data' => 'Master Data',
                                        'absensi' => 'Absensi',
                                        'proses-gaji' => 'Proses Payroll',
                                        'laporan' => 'Laporan',
                                        'settings' => 'Settings',
                                        ];
                                        $moduleName = $moduleLabels[$permission->module] ?? ucwords(str_replace('-', ' ', $permission->module));
                                        @endphp
                                        <span class="badge bg-info fs-6">{{ $moduleName }}</span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="text-muted text-uppercase small fw-semibold">Deskripsi</div>
                                    <div>{{ $permission->description ?: '-' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted text-uppercase small fw-semibold">Dibuat</div>
                                    <div>{{ $permission->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted text-uppercase small fw-semibold">Diupdate</div>
                                    <div>{{ $permission->updated_at->format('d/m/Y H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <strong>Roles yang Memiliki Permission Ini ({{ $permission->roles->count() }})</strong>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            @if($permission->roles->count() > 0)
                            <ul class="list-unstyled">
                                @foreach($permission->roles as $role)
                                <li class="mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-shield text-primary me-2"></i>
                                        <div>
                                            <div class="fw-semibold">{{ $role->name }}</div>
                                            <small class="text-muted">{{ $role->slug }}</small>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                            @else
                            <p class="text-muted">Belum ada role yang memiliki permission ini</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection





