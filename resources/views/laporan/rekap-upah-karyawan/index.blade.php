@extends('layouts.app')

@section('title', 'Rekap Upah Karyawan')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i>Rekap Upah Karyawan
                </h2>
            </div>

            <!-- Form Filter -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Rekap Upah Karyawan</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('rekap-upah-karyawan.preview') }}" id="formRekapUpah">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="periode" class="form-label">Periode Gajian <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="date" class="form-control" id="periode" name="periode"
                                        value="{{ $defaultPeriode }}" required>
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                                <small class="form-text text-muted">Contoh: 1 Oktober 2025 atau 15 Oktober 2025</small>
                            </div>

                            <div class="col-md-6">
                                <label for="divisi" class="form-label">Divisi Karyawan</label>
                                <select class="form-select" id="divisi" name="divisi">
                                    <option value="SEMUA">SEMUA DIVISI</option>
                                    @foreach($divisis as $divisi)
                                    <option value="{{ $divisi->vcKodeDivisi }}">
                                        {{ $divisi->vcKodeDivisi }} - {{ $divisi->vcNamaDivisi }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg me-3">
                                    <i class="fas fa-print me-2"></i>Cetak
                                </button>
                                <button type="button" class="btn btn-danger btn-lg" onclick="window.location.href='{{ route('dashboard') }}'">
                                    <i class="fas fa-times me-2"></i>Keluar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validasi form
        document.getElementById('formRekapUpah').addEventListener('submit', function(e) {
            const periode = document.getElementById('periode').value;

            if (!periode) {
                e.preventDefault();
                alert('Periode Gajian harus diisi!');
                return false;
            }
        });
    });
</script>
@endsection

