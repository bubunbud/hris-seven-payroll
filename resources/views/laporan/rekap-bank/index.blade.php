@extends('layouts.app')

@section('title', 'Rekap Bank')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i>Rekap Bank
                </h2>
            </div>

            <!-- Form Filter -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Rekap Bank</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('rekap-bank.preview') }}" id="formRekapBank">
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
                                    <i class="fas fa-print me-2"></i>Preview
                                </button>
                                <button type="button" class="btn btn-success btn-lg me-3" onclick="exportToExcel()">
                                    <i class="fas fa-file-excel me-2"></i>Export Excel
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
        document.getElementById('formRekapBank').addEventListener('submit', function(e) {
            const periode = document.getElementById('periode').value;

            if (!periode) {
                e.preventDefault();
                alert('Periode Gajian harus diisi!');
                return false;
            }
        });
    });

    function exportToExcel() {
        const periode = document.getElementById('periode').value;
        const divisi = document.getElementById('divisi').value;

        if (!periode) {
            alert('Periode Gajian harus diisi!');
            return;
        }

        // Create form for export
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("rekap-bank.export-excel") }}';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        const periodeInput = document.createElement('input');
        periodeInput.type = 'hidden';
        periodeInput.name = 'periode';
        periodeInput.value = periode;
        form.appendChild(periodeInput);

        const divisiInput = document.createElement('input');
        divisiInput.type = 'hidden';
        divisiInput.name = 'divisi';
        divisiInput.value = divisi;
        form.appendChild(divisiInput);

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
</script>
@endsection






