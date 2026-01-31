@extends('layouts.app')

@section('title', 'Detail User - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-user me-2"></i>Detail User
                </h2>
                <div class="d-flex gap-2">
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <strong>Informasi User</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="text-muted text-uppercase small fw-semibold">Nama</div>
                                    <div class="fs-5 fw-semibold">{{ $user->name }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted text-uppercase small fw-semibold">Email</div>
                                    <div class="fs-5">{{ $user->email }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted text-uppercase small fw-semibold">Role</div>
                                    <div>
                                        @if($user->roles->count() > 0)
                                        @foreach($user->roles as $role)
                                        @php
                                        $roleColors = [
                                        'admin' => 'danger',
                                        'hr' => 'primary',
                                        'manager' => 'warning',
                                        'user' => 'secondary'
                                        ];
                                        $color = $roleColors[$role->slug] ?? 'info';
                                        @endphp
                                        <span class="badge bg-{{ $color }} fs-6 me-1">{{ $role->name }}</span>
                                        @endforeach
                                        @else
                                        <span class="badge bg-secondary fs-6">{{ strtoupper($user->role) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted text-uppercase small fw-semibold">Status</div>
                                    <div>
                                        @if($user->is_active)
                                        <span class="badge bg-success fs-6">Aktif</span>
                                        @else
                                        <span class="badge bg-danger fs-6">Tidak Aktif</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted text-uppercase small fw-semibold">NIK</div>
                                    <div>
                                        @if($user->nik)
                                        <span class="badge bg-info fs-6">{{ $user->nik }}</span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted text-uppercase small fw-semibold">Dibuat</div>
                                    <div>{{ $user->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted text-uppercase small fw-semibold">Diupdate</div>
                                    <div>{{ $user->updated_at->format('d/m/Y H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($user->karyawan)
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <strong>Data Karyawan Terkait</strong>
                        </div>
                        <div class="card-body">
                            <div class="text-muted text-uppercase small fw-semibold">NIK</div>
                            <div class="mb-3">
                                <span class="badge bg-info">{{ $user->karyawan->Nik }}</span>
                            </div>
                            <div class="text-muted text-uppercase small fw-semibold">Nama</div>
                            <div class="mb-3 fw-semibold">{{ $user->karyawan->Nama }}</div>
                            @if($user->karyawan->departemen)
                            <div class="text-muted text-uppercase small fw-semibold">Departemen</div>
                            <div class="mb-3">{{ $user->karyawan->departemen->vcNamaDept ?? '-' }}</div>
                            @endif
                            @if($user->karyawan->jabatan)
                            <div class="text-muted text-uppercase small fw-semibold">Jabatan</div>
                            <div>{{ $user->karyawan->jabatan->vcNamaJabatan ?? '-' }}</div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection





