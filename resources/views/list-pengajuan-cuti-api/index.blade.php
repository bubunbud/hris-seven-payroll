@extends('layouts.app')

@section('title', 'List Pengajuan Cuti dari API - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-cloud-download-alt me-2"></i>List Pengajuan Cuti dari API
                </h2>
                <a href="{{ route('tidak-masuk.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-list me-1"></i>Lihat Tidak Masuk
                </a>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Modul ini mengambil pengajuan cuti dari API HRIS eksternal. <strong>Hanya menampilkan status Approved/Completed</strong>. Import ke Tidak Masuk.
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
                    <form method="GET" action="{{ route('list-pengajuan-cuti-api.index') }}" id="fetchForm">
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
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary" id="btnFetch">
                                    <i class="fas fa-sync-alt me-1"></i>Ambil Data dari API
                                </button>
                                <small class="text-muted d-block mt-1">Default: sebulan terakhir</small>
                            </div>
                        </div>
                    </form>
                    <p class="text-muted mb-0 mt-3">
                        Data diambil berdasarkan periode tanggal. Hanya status <strong>Approved/Completed</strong>.
                    </p>
                </div>
            </div>

            @if(!empty($leaves))
            <input type="hidden" id="importDariTanggal" value="{{ $dariTanggal ?? '' }}">
            <input type="hidden" id="importSampaiTanggal" value="{{ $sampaiTanggal ?? '' }}">
            @if($fetchMeta ?? null)
            <div class="alert alert-success mb-3">
                <i class="fas fa-check-circle me-2"></i>
                Data diambil: <strong>{{ number_format((int) ($fetchMeta['total_fetched'] ?? 0)) }}</strong> record. Ditampilkan: <strong>{{ number_format((int) ($fetchMeta['total_approved'] ?? count($leaves))) }}</strong> (status Approved/Completed).
            </div>
            @endif
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span><i class="fas fa-table me-2"></i>Data Pengajuan Cuti (Approved/Completed) — {{ count($leaves) }} record</span>
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
                                    <th>Jenis Cuti</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Hari</th>
                                    <th>Alasan</th>
                                    <th>Status</th>
                                    <th>Map ke</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaves as $leave)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="row-check" value="{{ $leave['id'] }}">
                                    </td>
                                    <td>{{ $leave['employee_nik'] ?? '-' }}</td>
                                    <td>{{ trim(($leave['first_name'] ?? '') . ' ' . ($leave['middle_name'] ?? '') . ' ' . ($leave['last_name'] ?? '')) ?: '-' }}</td>
                                    <td>{{ $leave['leave_type_name'] ?? '-' }}</td>
                                    <td>{{ $leave['start_date'] ?? '-' }}</td>
                                    <td>{{ $leave['end_date'] ?? '-' }}</td>
                                    <td>{{ $leave['total_days'] ?? '-' }}</td>
                                    <td class="small">{{ Str::limit($leave['reason'] ?? '-', 30) }}</td>
                                    <td>
                                        @php $st = strtoupper($leave['status'] ?? ''); @endphp
                                        @if(in_array($st, ['APPROVED','COMPLETED']))
                                            <span class="badge bg-success">{{ $leave['status'] ?? '-' }}</span>
                                        @elseif(in_array($st, ['PENDING','SUBMITTED']))
                                            <span class="badge bg-warning text-dark">{{ $leave['status'] ?? '-' }}</span>
                                        @elseif(in_array($st, ['REJECTED','CANCELLED']))
                                            <span class="badge bg-danger">{{ $leave['status'] ?? '-' }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $leave['status'] ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td><code>{{ $leave['mapped_kode'] ?? 'C010' }}</code></td>
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
                    <p class="mb-0">Belum ada data. Klik "Ambil Data dari API" untuk memuat pengajuan cuti (Approved/Completed).</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(!empty($leaves))
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

        fetch('{{ route("list-pengajuan-cuti-api.import") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: checked, dari_tanggal: dariTanggal, sampai_tanggal: sampaiTanggal })
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
