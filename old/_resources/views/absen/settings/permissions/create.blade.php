@extends('layouts.app')

@section('title', 'Tambah Permission - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-key me-2"></i>Tambah Permission
                </h2>
                <a href="{{ route('permissions.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('permissions.store') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nama Permission <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}" required
                                    placeholder="Contoh: View Master Data, Create User">
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

                            <div class="col-md-6">
                                <label for="module" class="form-label">Module</label>
                                <input type="text" class="form-control @error('module') is-invalid @enderror"
                                    id="module" name="module" value="{{ old('module') }}"
                                    placeholder="Contoh: master-data, settings, absensi">
                                @error('module')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Module/group untuk mengelompokkan permission</small>
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label">Deskripsi</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                    id="description" name="description" rows="3"
                                    placeholder="Deskripsi permission dan fungsinya">{{ old('description') }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Simpan
                            </button>
                            <a href="{{ route('permissions.index') }}" class="btn btn-secondary">
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





