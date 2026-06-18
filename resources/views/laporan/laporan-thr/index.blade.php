@extends('layouts.app')

@section('title', 'Laporan THR Karyawan')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i>Laporan DAFTAR TUNJANGAN HARI RAYA (THR) KARYAWAN
                </h2>
            </div>

            <!-- Form Filter -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Laporan THR</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('laporan-thr.preview') }}" id="formLaporanThr">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="tahun" class="form-label">Tahun <span class="text-danger">*</span></label>
                                <select class="form-select" id="tahun" name="tahun" required>
                                    <option value="">Pilih Tahun</option>
                                    @foreach($years as $yearOption)
                                    <option value="{{ $yearOption }}" {{ $tahun == $yearOption ? 'selected' : '' }}>
                                        {{ $yearOption }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="divisi" class="form-label">Divisi</label>
                                <select class="form-select" id="divisi" name="divisi">
                                    <option value="SEMUA" {{ $divisi == 'SEMUA' ? 'selected' : '' }}>SEMUA DIVISI</option>
                                    @foreach($divisis as $divisiOption)
                                    <option value="{{ $divisiOption->vcKodeDivisi }}" {{ $divisi == $divisiOption->vcKodeDivisi ? 'selected' : '' }}>
                                        {{ $divisiOption->vcKodeDivisi }} - {{ $divisiOption->vcNamaDivisi }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="agama" class="form-label">Agama</label>
                                <select class="form-select" id="agama" name="agama">
                                    <option value="Semua Agama" {{ $agama == 'Semua Agama' ? 'selected' : '' }}>Semua Agama</option>
                                    @foreach($agamas as $agamaOption)
                                    <option value="{{ $agamaOption }}" {{ $agama == $agamaOption ? 'selected' : '' }}>
                                        {{ $agamaOption }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg me-3">
                                    <i class="fas fa-print me-2"></i>Preview & Cetak
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
        document.getElementById('formLaporanThr').addEventListener('submit', function(e) {
            const tahun = document.getElementById('tahun').value;

            if (!tahun) {
                e.preventDefault();
                alert('Tahun harus dipilih!');
                return false;
            }
        });
    });
</script>
@endsection


