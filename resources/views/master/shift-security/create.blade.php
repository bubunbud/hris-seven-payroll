@extends('layouts.app')

@section('title', 'Tambah Master Shift Security - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-shield-alt me-2"></i>Tambah Master Shift Security
                </h2>
                <a href="{{ route('master-shift-security.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('master-shift-security.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vcKodeShift" class="form-label">Kode Shift <span class="text-danger">*</span></label>
                                    <select class="form-select" id="vcKodeShift" name="vcKodeShift" required>
                                        <option value="">-- Pilih Kode Shift --</option>
                                        <option value="1" {{ old('vcKodeShift') == '1' ? 'selected' : '' }}>1 - Shift 1</option>
                                        <option value="2" {{ old('vcKodeShift') == '2' ? 'selected' : '' }}>2 - Shift 2</option>
                                        <option value="3" {{ old('vcKodeShift') == '3' ? 'selected' : '' }}>3 - Shift 3</option>
                                    </select>
                                    <small class="text-muted">Kode shift harus 1, 2, atau 3</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vcNamaShift" class="form-label">Nama Shift <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="vcNamaShift" name="vcNamaShift"
                                        value="{{ old('vcNamaShift') }}" required maxlength="20" placeholder="Contoh: Shift 1">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dtJamMasuk" class="form-label">Jam Masuk <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="dtJamMasuk" name="dtJamMasuk"
                                        value="{{ old('dtJamMasuk') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dtJamPulang" class="form-label">Jam Pulang <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="dtJamPulang" name="dtJamPulang"
                                        value="{{ old('dtJamPulang') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="intDurasiJam" class="form-label">Durasi (Jam) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="intDurasiJam" name="intDurasiJam"
                                        value="{{ old('intDurasiJam', '8.00') }}" step="0.01" min="0" max="24" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="intToleransiMasuk" class="form-label">Toleransi Masuk (Menit) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="intToleransiMasuk" name="intToleransiMasuk"
                                        value="{{ old('intToleransiMasuk', '30') }}" min="0" max="120" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="intToleransiPulang" class="form-label">Toleransi Pulang (Menit) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="intToleransiPulang" name="intToleransiPulang"
                                        value="{{ old('intToleransiPulang', '30') }}" min="0" max="120" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="isCrossDay" name="isCrossDay"
                                            value="1" {{ old('isCrossDay') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="isCrossDay">
                                            Cross Day (Shift melewati tengah malam)
                                        </label>
                                    </div>
                                    <small class="text-muted">Centang jika shift berakhir di hari berikutnya (contoh: 22:30 - 06:30)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vcKeterangan" class="form-label">Keterangan</label>
                                    <input type="text" class="form-control" id="vcKeterangan" name="vcKeterangan"
                                        value="{{ old('vcKeterangan') }}" maxlength="100" placeholder="Contoh: Pagi, Siang, Malam">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('master-shift-security.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
