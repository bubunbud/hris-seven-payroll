@extends('layouts.app')

@section('title', 'Rekap Bank THR')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i>Rekap Bank THR
                </h2>
            </div>

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Rekap Bank THR (Group: Operator & Security)</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('rekap-bank-thr.preview') }}" id="formRekapBankThr">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="tanggal_thr" class="form-label">Tanggal THR <span class="text-danger">*</span></label>
                                <select class="form-select" id="tanggal_thr" name="tanggal_thr" required>
                                    <option value="">-- Pilih Tanggal THR --</option>
                                    @foreach($tanggalThrOptions as $opt)
                                    <option value="{{ $opt['value'] }}" {{ ($defaultTanggal ?? '') == $opt['value'] ? 'selected' : '' }}>
                                        {{ $opt['label'] }}
                                    </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Data diambil dari t_closing_thr untuk Group Operator & Security</small>
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
        document.getElementById('formRekapBankThr').addEventListener('submit', function(e) {
            const tanggalThr = document.getElementById('tanggal_thr').value;
            if (!tanggalThr) {
                e.preventDefault();
                alert('Tanggal THR harus dipilih!');
                return false;
            }
        });
    });

    function exportToExcel() {
        const tanggalThr = document.getElementById('tanggal_thr').value;
        const divisi = document.getElementById('divisi').value;

        if (!tanggalThr) {
            alert('Tanggal THR harus dipilih!');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("rekap-bank-thr.export-excel") }}';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        const tanggalInput = document.createElement('input');
        tanggalInput.type = 'hidden';
        tanggalInput.name = 'tanggal_thr';
        tanggalInput.value = tanggalThr;
        form.appendChild(tanggalInput);

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
