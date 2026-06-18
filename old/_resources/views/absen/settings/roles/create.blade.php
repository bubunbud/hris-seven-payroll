@extends('layouts.app')

@section('title', 'Tambah Role - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-user-shield me-2"></i>Tambah Role
                </h2>
                <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('roles.store') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nama Role <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}" required
                                    placeholder="Contoh: Administrator, HR Manager">
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="slug" class="form-label">Slug</label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                    id="slug" name="slug" value="{{ old('slug') }}"
                                    placeholder="Akan di-generate otomatis jika kosong">
                                @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Slug akan di-generate otomatis dari nama jika kosong</small>
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label">Deskripsi</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                    id="description" name="description" rows="3"
                                    placeholder="Deskripsi role dan fungsinya">{{ old('description') }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Aktifkan role
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <hr>
                                <label class="form-label fw-semibold">Assign Permissions</label>
                                <p class="text-muted small">Pilih permissions yang akan diberikan kepada role ini</p>

                                @php
                                $moduleLabels = [
                                'master-data' => 'Master Data',
                                'absensi' => 'Absensi',
                                'proses-gaji' => 'Proses Payroll',
                                'laporan' => 'Laporan',
                                'settings' => 'Settings',
                                ];
                                
                                // Permission group (utama)
                                $groupPermissions = [
                                    'view-master-data', 'create-master-data', 'edit-master-data', 'delete-master-data',
                                    'view-absensi', 'create-absensi', 'edit-absensi', 'delete-absensi',
                                    'view-proses-gaji', 'create-proses-gaji', 'edit-proses-gaji', 'delete-proses-gaji',
                                    'view-laporan', 'print-laporan', 'export-laporan',
                                    'view-settings', 'manage-users', 'manage-roles', 'manage-permissions'
                                ];
                                @endphp
                                @if($permissions->count() > 0)
                                @foreach($permissions as $module => $modulePermissions)
                                @php
                                $moduleName = $moduleLabels[$module] ?? ($module ? ucwords(str_replace('-', ' ', $module)) : 'Lainnya');
                                
                                // Pisahkan permission group dan granular
                                $groupPerms = $modulePermissions->filter(function($perm) use ($groupPermissions) {
                                    return in_array($perm->slug, $groupPermissions);
                                });
                                $granularPerms = $modulePermissions->filter(function($perm) use ($groupPermissions) {
                                    return !in_array($perm->slug, $groupPermissions);
                                });
                                @endphp
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <strong>{{ $moduleName }}</strong>
                                    </div>
                                    <div class="card-body">
                                        @if($groupPerms->count() > 0)
                                        <div class="mb-3">
                                            <h6 class="text-primary mb-2">
                                                <i class="fas fa-layer-group me-1"></i>Permission Group (Akses Penuh Modul)
                                            </h6>
                                            <div class="row g-2">
                                                @foreach($groupPerms as $permission)
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="permissions[]" value="{{ $permission->id }}"
                                                            id="permission_{{ $permission->id }}"
                                                            {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                            <strong>{{ $permission->name }}</strong>
                                                            @if($permission->description)
                                                            <br><small class="text-muted">{{ $permission->description }}</small>
                                                            @endif
                                                        </label>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                        
                                        @if($granularPerms->count() > 0)
                                        <div class="mt-3 pt-3 border-top">
                                            <h6 class="text-success mb-2">
                                                <i class="fas fa-list-ul me-1"></i>Permission Granular (Akses Per Submenu)
                                            </h6>
                                            <div class="row g-2">
                                                @foreach($granularPerms as $permission)
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="permissions[]" value="{{ $permission->id }}"
                                                            id="permission_{{ $permission->id }}"
                                                            {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                            <strong>{{ $permission->name }}</strong>
                                                            @if($permission->description)
                                                            <br><small class="text-muted">{{ $permission->description }}</small>
                                                            @endif
                                                        </label>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                                @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Belum ada permission. <a href="{{ route('permissions.create') }}">Buat permission terlebih dahulu</a>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Simpan
                            </button>
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection








