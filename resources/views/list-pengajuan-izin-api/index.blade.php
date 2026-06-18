@extends('layouts.app')

@section('title', 'List Pengajuan Izin dari API - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i>List Pengajuan Izin dari API
                </h2>
                <a href="{{ route('tidak-masuk.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-list me-1"></i>Lihat Izin Tidak Masuk
                </a>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Modul ini mengambil pengajuan <strong>tidak masuk kerja</strong> (Sakit + Izin Pribadi) dari API HRIS. Endpoint: <code>GET /v1/absents/requests</code>. <strong>Hanya menampilkan status Approved/Completed</strong>. Import ke Tidak Masuk.
            </div>

            @if($error)
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ $error }}
            </div>
            @endif

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-plug me-2"></i>Koneksi API — Filter Periode
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('list-pengajuan-izin-api.index') }}" id="fetchForm">
                        <input type="hidden" name="fetch" value="1">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="dari_tanggal" class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" id="dari_tanggal" name="dari_tanggal" value="{{ $dariTanggal ?? now()->subDays(30)->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label for="sampai_tanggal" class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" id="sampai_tanggal" name="sampai_tanggal" value="{{ $sampaiTanggal ?? now()->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="subordinate" value="1" id="subordinate" {{ ($subordinate ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="subordinate">Bawahan</label>
                                </div>
                                <small class="text-muted">Endpoint subordinate</small>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary" id="btnFetch">
                                    <i class="fas fa-sync-alt me-1"></i>Ambil Data dari API
                                </button>
                                <small class="text-muted d-block mt-1">Default: sebulan terakhir</small>
                            </div>
                        </div>
                    </form>
                    <p class="text-muted mb-0 mt-3">
                        Endpoint: <code>GET /v1/absents/requests</code>. Data Sakit + Izin Pribadi. Hanya status <strong>Approved/Completed</strong>.
                    </p>
                </div>
            </div>

            @if(!empty($permits))
            <input type="hidden" id="importDariTanggal" value="{{ $dariTanggal ?? '' }}">
            <input type="hidden" id="importSampaiTanggal" value="{{ $sampaiTanggal ?? '' }}">
            <input type="hidden" id="importSubordinate" value="{{ ($subordinate ?? false) ? '1' : '0' }}">
            @if($fetchMeta ?? null)
            <div class="alert alert-success mb-3">
                <i class="fas fa-check-circle me-2"></i>
                Data diambil dari <strong>{{ number_format($fetchMeta['total_fetched']) }}</strong> record (Absents API).
                Yang ditampilkan: <strong>{{ number_format($fetchMeta['total_filtered'] ?? count($permits)) }}</strong> record (Sakit + Izin Pribadi).
            </div>
            @endif
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span><i class="fas fa-table me-2"></i>Data Tidak Masuk (Sakit + Izin Pribadi) — {{ count($permits) }} record</span>
                    <button type="button" class="btn btn-success btn-sm" id="btnImport" disabled>
                        <i class="fas fa-file-import me-1"></i>Import ke Tidak Masuk
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="40">
                                        <input type="checkbox" id="checkAll" title="Pilih Semua">
                                    </th>
                                    <th>NIK</th>
                                    <th>Nama</th>
                                    <th>Purpose/Tipe</th>
                                    <th>Tanggal</th>
                                    <th>Jam</th>
                                    <th>Alasan</th>
                                    <th>Status</th>
                                    <th>Map ke</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($permits as $permit)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="row-check" value="{{ $permit['id'] }}">
                                    </td>
                                    <td>{{ $permit['employee_nik'] ?? '-' }}</td>
                                    <td>{{ trim(($permit['first_name'] ?? '') . ' ' . ($permit['middle_name'] ?? '') . ' ' . ($permit['last_name'] ?? '')) ?: '-' }}</td>
                                    <td>{{ $permit['purpose'] ?? '-' }}</td>
                                    <td>{{ $permit['date_formatted'] ?? '-' }}</td>
                                    <td class="small">{{ ($permit['time_out'] ?? '-') . ' - ' . ($permit['time_in'] ?? '-') }}</td>
                                    <td class="small">{{ Str::limit($permit['reason'] ?? '-', 25) }}</td>
                                    <td>
                                        @php $st = strtoupper($permit['status'] ?? ''); @endphp
                                        @if(in_array($st, ['APPROVED','COMPLETED']))
                                            <span class="badge bg-success">{{ $permit['status'] ?? '-' }}</span>
                                        @elseif(in_array($st, ['PENDING','SUBMITTED']))
                                            <span class="badge bg-warning text-dark">{{ $permit['status'] ?? '-' }}</span>
                                        @elseif(in_array($st, ['REJECTED','CANCELLED']))
                                            <span class="badge bg-danger">{{ $permit['status'] ?? '-' }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $permit['status'] ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td><code>{{ $permit['mapped_kode'] ?? '-' }}</code></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="resultContainer" class="mt-4" style="display: none;">
                <div class="alert" id="resultAlert">
                    <div id="resultMessage"></div>
                    <div id="resultDetails" class="mt-3"></div>
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-body text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p class="mb-0">Belum ada data. Klik "Ambil Data dari API" untuk memuat pengajuan tidak masuk (Sakit + Izin Pribadi).</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(!empty($permits))
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkAll = document.getElementById('checkAll');
    const rowChecks = document.querySelectorAll('.row-check');
    const btnImport = document.getElementById('btnImport');

    function updateImportButton() {
        const checked = document.querySelectorAll('.row-check:checked');
        btnImport.disabled = checked.length === 0;
    }

    checkAll?.addEventListener('change', function() {
        rowChecks.forEach(cb => cb.checked = this.checked);
        updateImportButton();
    });

    rowChecks.forEach(cb => {
        cb.addEventListener('change', updateImportButton);
    });

    btnImport?.addEventListener('click', function() {
        const checked = Array.from(document.querySelectorAll('.row-check:checked')).map(cb => cb.value);
        if (checked.length === 0) {
            alert('Pilih minimal 1 data untuk di-import.');
            return;
        }

        if (!confirm('Import ' + checked.length + ' data ke tabel Tidak Masuk?')) return;

        btnImport.disabled = true;
        btnImport.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memproses...';

        const dariTanggal = document.getElementById('importDariTanggal')?.value || '';
        const sampaiTanggal = document.getElementById('importSampaiTanggal')?.value || '';
        const subordinate = document.getElementById('importSubordinate')?.value === '1';

        fetch('{{ route("list-pengajuan-izin-api.import") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: checked, dari_tanggal: dariTanggal, sampai_tanggal: sampaiTanggal, subordinate: subordinate })
        })
        .then(r => r.json())
        .then(data => {
            const resultContainer = document.getElementById('resultContainer');
            const resultAlert = document.getElementById('resultAlert');
            const resultMessage = document.getElementById('resultMessage');
            const resultDetails = document.getElementById('resultDetails');

            resultContainer.style.display = 'block';

            if (data.success) {
                resultAlert.className = 'alert alert-success';
                resultMessage.innerHTML = '<strong><i class="fas fa-check-circle me-2"></i>' + data.message + '</strong>';
                let html = '<ul class="mb-0">';
                html += '<li>Insert: ' + (data.data?.inserted ?? 0) + '</li>';
                html += '<li>Update: ' + (data.data?.updated ?? 0) + '</li>';
                html += '<li>Skip: ' + (data.data?.skipped ?? 0) + '</li>';
                html += '</ul>';
                if (data.data?.errors?.length) {
                    html += '<div class="mt-2"><strong>Detail Error:</strong><ul class="mb-0 small">';
                    data.data.errors.forEach(e => {
                        html += '<li class="text-danger">NIK ' + e.nik + ': ' + e.error + '</li>';
                    });
                    html += '</ul></div>';
                }
                resultDetails.innerHTML = html;
            } else {
                resultAlert.className = 'alert alert-danger';
                resultMessage.innerHTML = '<strong><i class="fas fa-exclamation-circle me-2"></i>' + data.message + '</strong>';
                resultDetails.innerHTML = '';
            }
        })
        .catch(err => {
            document.getElementById('resultContainer').style.display = 'block';
            document.getElementById('resultAlert').className = 'alert alert-danger';
            document.getElementById('resultMessage').innerHTML = '<strong>Terjadi kesalahan: ' + err.message + '</strong>';
            document.getElementById('resultDetails').innerHTML = '';
        })
        .finally(() => {
            btnImport.disabled = document.querySelectorAll('.row-check:checked').length === 0;
            btnImport.innerHTML = '<i class="fas fa-file-import me-1"></i>Import ke Tidak Masuk';
        });
    });
});
</script>
@endif

<script>
document.getElementById('fetchForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('btnFetch');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memuat...';
    }
});
</script>
@endpush
