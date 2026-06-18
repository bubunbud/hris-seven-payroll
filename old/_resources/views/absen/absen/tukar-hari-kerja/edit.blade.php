@extends('layouts.app')

@section('title', 'Edit Tukar Hari Kerja - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-exchange-alt me-2"></i>Edit Tukar Hari Kerja
                </h2>
                <a href="{{ route('tukar-hari-kerja.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <form action="{{ route('tukar-hari-kerja.update', $tukarHariKerja->id) }}" method="POST" id="tukarHariKerjaForm">
                @csrf
                @method('PUT')

                <!-- Header Section -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Tukar Hari Kerja</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Tipe Tukar <span class="text-danger">*</span></label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="vcTipeTukar" id="tipe_libur_kerja" value="LIBUR_KE_KERJA" {{ $tukarHariKerja->vcTipeTukar == 'LIBUR_KE_KERJA' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tipe_libur_kerja">
                                        Libur → Kerja (Hari libur ditukar menjadi hari kerja normal)
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="vcTipeTukar" id="tipe_kerja_libur" value="KERJA_KE_LIBUR" {{ $tukarHariKerja->vcTipeTukar == 'KERJA_KE_LIBUR' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tipe_kerja_libur">
                                        Kerja → Libur (Hari kerja normal ditukar menjadi hari libur)
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="dtTanggalLibur" class="form-label">Tanggal Libur <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dtTanggalLibur" name="dtTanggalLibur" value="{{ $tukarHariKerja->dtTanggalLibur->format('Y-m-d') }}" required>
                                <small class="text-muted">Tanggal hari libur yang ditukar</small>
                            </div>

                            <div class="col-md-4">
                                <label for="dtTanggalKerja" class="form-label">Tanggal Kerja <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dtTanggalKerja" name="dtTanggalKerja" value="{{ $tukarHariKerja->dtTanggalKerja->format('Y-m-d') }}" required>
                                <small class="text-muted">Tanggal hari kerja pengganti</small>
                            </div>

                            <div class="col-md-4">
                                <label for="vcKeterangan" class="form-label">Keterangan</label>
                                <input type="text" class="form-control" id="vcKeterangan" name="vcKeterangan" value="{{ $tukarHariKerja->vcKeterangan }}" placeholder="Alasan tukar hari kerja">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Scope <span class="text-danger">*</span></label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="vcScope" id="scope_perorangan" value="PERORANGAN" {{ $tukarHariKerja->vcScope == 'PERORANGAN' ? 'checked' : '' }} disabled>
                                    <label class="form-check-label" for="scope_perorangan">Perorangan</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="vcScope" id="scope_group" value="GROUP" {{ $tukarHariKerja->vcScope == 'GROUP' ? 'checked' : '' }} disabled>
                                    <label class="form-check-label" for="scope_group">Group</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="vcScope" id="scope_semua_bu" value="SEMUA_BU" {{ $tukarHariKerja->vcScope == 'SEMUA_BU' ? 'checked' : '' }} disabled>
                                    <label class="form-check-label" for="scope_semua_bu">Semua BU</label>
                                </div>
                                <input type="hidden" name="vcScope" value="{{ $tukarHariKerja->vcScope }}">
                                <small class="text-muted d-block">Scope tidak dapat diubah setelah dibuat</small>
                            </div>

                            <div class="col-md-3">
                                <label for="vcStatus" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="vcStatus" name="vcStatus" required>
                                    <option value="1" {{ $tukarHariKerja->vcStatus == '1' ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ $tukarHariKerja->vcStatus == '0' ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Info</label>
                                <div>
                                    <small class="text-muted">
                                        <strong>Kode:</strong> {{ $tukarHariKerja->vcKodeTukar }}<br>
                                        <strong>Jumlah Karyawan:</strong> {{ $tukarHariKerja->details->count() }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daftar Karyawan (Read-only) -->
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Daftar Karyawan</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="15%">NIK</th>
                                        <th>Nama</th>
                                        <th width="15%">Divisi</th>
                                        <th width="10%">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tukarHariKerja->details as $index => $detail)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $detail->karyawan->Nik ?? $detail->vcNik }}</td>
                                        <td>{{ $detail->karyawan->Nama ?? '-' }}</td>
                                        <td>{{ $detail->karyawan->divisi->vcNamaDivisi ?? '-' }}</td>
                                        <td class="text-center">
                                            @if($detail->vcStatus == '1')
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-danger">Nonaktif</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Tidak ada data karyawan</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary me-2" onclick="window.location.href='{{ route('tukar-hari-kerja.index') }}'">
                                <i class="fas fa-times me-2"></i>Batal
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


