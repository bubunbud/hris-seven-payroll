@extends('layouts.app')

@section('title', 'Tambah User - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-user-plus me-2"></i>Tambah User
                </h2>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('users.store') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" required>
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Minimal 6 karakter</small>
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control"
                                    id="password_confirmation" name="password_confirmation" required>
                            </div>

                            <div class="col-md-6">
                                <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select @error('role_id') is-invalid @enderror"
                                    id="role_id" name="role_id" required>
                                    <option value="">Pilih Role</option>
                                    @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('role_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="nik" class="form-label">NIK Karyawan</label>
                                <select class="form-select @error('nik') is-invalid @enderror"
                                    id="nik" name="nik">
                                    <option value="">Pilih Karyawan (Opsional)</option>
                                    @foreach($karyawans as $karyawan)
                                    <option value="{{ $karyawan->Nik }}" {{ old('nik') == $karyawan->Nik ? 'selected' : '' }}>
                                        {{ $karyawan->Nik }} - {{ $karyawan->Nama }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('nik')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Link user ke data karyawan (opsional)</small>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Aktifkan user
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Simpan
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
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





