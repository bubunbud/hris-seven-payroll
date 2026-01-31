@extends('layouts.app')

@section('title', 'Edit Absensi - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-edit me-2"></i>Edit Absensi
                </h2>
                <a href="{{ route('edit-absensi.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>

            <!-- Alert Messages -->
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <!-- Form Edit -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>Data Karyawan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>NIK:</strong>
                        </div>
                        <div class="col-md-9">
                            {{ $absen->vcNik }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Nama:</strong>
                        </div>
                        <div class="col-md-9">
                            {{ $absen->karyawan->Nama ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Divisi:</strong>
                        </div>
                        <div class="col-md-9">
                            {{ $absen->karyawan->divisi->vcNamaDivisi ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Bagian:</strong>
                        </div>
                        <div class="col-md-9">
                            {{ $absen->karyawan->bagian->vcNamaBagian ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Tanggal:</strong>
                        </div>
                        <div class="col-md-9">
                            {{ \Carbon\Carbon::parse($absen->dtTanggal)->format('d/m/Y') }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Shift Masuk:</strong>
                        </div>
                        <div class="col-md-9">
                            {{ $absen->karyawan->shift->vcMasuk ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Edit Jam -->
            <div class="card mt-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>Edit Jam Absensi
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('edit-absensi.update') }}" id="editForm">
                        @csrf
                        <input type="hidden" name="tanggal" value="{{ $absen->dtTanggal }}">
                        <input type="hidden" name="nik" value="{{ $absen->vcNik }}">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="jam_masuk" class="form-label">
                                    <i class="fas fa-sign-in-alt text-success me-1"></i>Jam Masuk
                                </label>
                                <input type="time" 
                                       class="form-control @error('jam_masuk') is-invalid @enderror" 
                                       id="jam_masuk" 
                                       name="jam_masuk"
                                       value="{{ $absen->dtJamMasuk ? substr((string) $absen->dtJamMasuk, 0, 5) : '' }}">
                                @error('jam_masuk')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Format: HH:MM (contoh: 08:00)</small>
                            </div>

                            <div class="col-md-6">
                                <label for="jam_keluar" class="form-label">
                                    <i class="fas fa-sign-out-alt text-danger me-1"></i>Jam Keluar
                                </label>
                                <input type="time" 
                                       class="form-control @error('jam_keluar') is-invalid @enderror" 
                                       id="jam_keluar" 
                                       name="jam_keluar"
                                       value="{{ $absen->dtJamKeluar ? substr((string) $absen->dtJamKeluar, 0, 5) : '' }}">
                                @error('jam_keluar')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Format: HH:MM (contoh: 17:00)</small>
                            </div>

                            <div class="col-md-6">
                                <label for="jam_masuk_lembur" class="form-label">
                                    <i class="fas fa-moon text-info me-1"></i>Jam Masuk Lembur
                                </label>
                                <input type="time" 
                                       class="form-control @error('jam_masuk_lembur') is-invalid @enderror" 
                                       id="jam_masuk_lembur" 
                                       name="jam_masuk_lembur"
                                       value="{{ $absen->dtJamMasukLembur ? substr((string) $absen->dtJamMasukLembur, 0, 5) : '' }}">
                                @error('jam_masuk_lembur')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Format: HH:MM (opsional)</small>
                            </div>

                            <div class="col-md-6">
                                <label for="jam_keluar_lembur" class="form-label">
                                    <i class="fas fa-moon text-info me-1"></i>Jam Keluar Lembur
                                </label>
                                <input type="time" 
                                       class="form-control @error('jam_keluar_lembur') is-invalid @enderror" 
                                       id="jam_keluar_lembur" 
                                       name="jam_keluar_lembur"
                                       value="{{ $absen->dtJamKeluarLembur ? substr((string) $absen->dtJamKeluarLembur, 0, 5) : '' }}">
                                @error('jam_keluar_lembur')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Format: HH:MM (opsional)</small>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Perhatian:</strong> Perubahan jam absensi akan tercatat di sistem. Pastikan data yang diinput sudah benar.
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                                </button>
                                <a href="{{ route('edit-absensi.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Batal
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Validasi form sebelum submit
    document.getElementById('editForm').addEventListener('submit', function(e) {
        const jamMasuk = document.getElementById('jam_masuk').value;
        const jamKeluar = document.getElementById('jam_keluar').value;

        // Validasi: jika ada jam masuk atau keluar, pastikan keduanya diisi
        if ((jamMasuk && !jamKeluar) || (!jamMasuk && jamKeluar)) {
            e.preventDefault();
            alert('Jam masuk dan jam keluar harus diisi bersamaan atau dikosongkan bersamaan.');
            return false;
        }

        // Konfirmasi sebelum submit
        if (!confirm('Apakah Anda yakin ingin menyimpan perubahan data absensi ini?')) {
            e.preventDefault();
            return false;
        }
    });
</script>
@endpush

