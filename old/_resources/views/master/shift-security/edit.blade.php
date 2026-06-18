@extends('layouts.app')

@section('title', 'Edit Master Shift Security - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-shield-alt me-2"></i>Edit Master Shift Security
                </h2>
                <a href="{{ route('master-shift-security.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('master-shift-security.update', $shiftSecurity->vcKodeShift) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vcKodeShift" class="form-label">Kode Shift</label>
                                    <input type="text" class="form-control" id="vcKodeShift"
                                        value="{{ $shiftSecurity->vcKodeShift }}" readonly>
                                    <small class="text-muted">Kode shift tidak dapat diubah</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vcNamaShift" class="form-label">Nama Shift <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="vcNamaShift" name="vcNamaShift"
                                        value="{{ old('vcNamaShift', $shiftSecurity->vcNamaShift) }}" required maxlength="20">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dtJamMasuk" class="form-label">Jam Masuk <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="dtJamMasuk" name="dtJamMasuk"
                                        value="{{ old('dtJamMasuk', \Carbon\Carbon::parse($shiftSecurity->dtJamMasuk)->format('H:i')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dtJamPulang" class="form-label">Jam Pulang <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="dtJamPulang" name="dtJamPulang"
                                        value="{{ old('dtJamPulang', \Carbon\Carbon::parse($shiftSecurity->dtJamPulang)->format('H:i')) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="intDurasiJam" class="form-label">Durasi (Jam) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="intDurasiJam" name="intDurasiJam"
                                        value="{{ old('intDurasiJam', $shiftSecurity->intDurasiJam) }}" step="0.01" min="0" max="24" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="intToleransiMasuk" class="form-label">Toleransi Masuk (Menit) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="intToleransiMasuk" name="intToleransiMasuk"
                                        value="{{ old('intToleransiMasuk', $shiftSecurity->intToleransiMasuk) }}" min="0" max="120" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="intToleransiPulang" class="form-label">Toleransi Pulang (Menit) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="intToleransiPulang" name="intToleransiPulang"
                                        value="{{ old('intToleransiPulang', $shiftSecurity->intToleransiPulang) }}" min="0" max="120" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="isCrossDay" name="isCrossDay"
                                            value="1" {{ old('isCrossDay', $shiftSecurity->isCrossDay) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="isCrossDay">
                                            Cross Day (Shift melewati tengah malam)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vcKeterangan" class="form-label">Keterangan</label>
                                    <input type="text" class="form-control" id="vcKeterangan" name="vcKeterangan"
                                        value="{{ old('vcKeterangan', $shiftSecurity->vcKeterangan) }}" maxlength="100">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('master-shift-security.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
