@extends('layouts.app')

@section('title', 'Tarik Data Fingerprint - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-fingerprint me-2"></i>Pull Attendance Data</h2>
                <a href="{{ route('mesin-fingerprint.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-server me-1"></i>Master Mesin
                </a>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">Tarik Log dari Mesin Fingerprint</div>
                <div class="card-body">
                    <form id="pullForm">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Pilih Mesin <span class="text-danger">*</span></label>
                                <select class="form-select" id="mesin_ids" multiple size="4" required>
                                    @foreach($mesins as $mesin)
                                    <option value="{{ $mesin->id }}">{{ $mesin->vcNama }} — {{ $mesin->vcIp }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Ctrl+klik untuk pilih lebih dari satu</small>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" id="dari_tanggal" value="{{ $defaultDari }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" id="sampai_tanggal" value="{{ $defaultSampai }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">NIK (opsional)</label>
                                <input type="text" class="form-control" id="nik" maxlength="20" placeholder="Semua NIK">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100" id="btnPull">
                                    <i class="fas fa-download me-1"></i>Pull Logs
                                </button>
                            </div>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="skip_unknown_nik" checked>
                            <label class="form-check-label" for="skip_unknown_nik">Lewati NIK yang tidak ada di master saat simpan</label>
                        </div>
                    </form>
                </div>
            </div>

            <div id="previewPanel" class="d-none">
                <div class="card mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <span id="rawLogsTitle"><i class="fas fa-list me-2"></i>Logs Preview</span>
                        <button type="button" class="btn btn-success btn-sm" id="btnSave" disabled>
                            <i class="fas fa-save me-1"></i>Save to Database
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 320px;">
                            <table class="table table-sm table-striped mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>No.</th>
                                        <th>Date &amp; Time</th>
                                        <th>Employee NIK</th>
                                        <th>State</th>
                                        <th>Type</th>
                                        <th>Verified</th>
                                        <th>Mesin</th>
                                    </tr>
                                </thead>
                                <tbody id="rawLogsBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-light"><i class="fas fa-layer-group me-2"></i>Preview Agregasi (siap simpan ke t_absen)</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>NIK</th>
                                        <th>Nama</th>
                                        <th>Jam Masuk</th>
                                        <th>Jam Pulang</th>
                                        <th>Mesin</th>
                                        <th>Aksi</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody id="aggBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="saveResultPanel" class="card d-none">
                    <div class="card-header bg-success text-white">Hasil Simpan</div>
                    <div class="card-body" id="saveResultBody"></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">Attendance Management Menu</div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('mesin-fingerprint.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-server me-2"></i>Attendance Machines
                        </a>
                        <span class="list-group-item active">
                            <i class="fas fa-download me-2"></i>Pull Data
                        </span>
                        <a href="{{ route('absen.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-table me-2"></i>Browse Attendance Data
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentBatchId = null;

document.getElementById('pullForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnPull');
    const selected = Array.from(document.getElementById('mesin_ids').selectedOptions).map(o => parseInt(o.value));
    if (!selected.length) { alert('Pilih minimal satu mesin'); return; }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

    fetch('{{ route('tarik-data-fingerprint.pull') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            mesin_ids: selected,
            dari_tanggal: document.getElementById('dari_tanggal').value,
            sampai_tanggal: document.getElementById('sampai_tanggal').value,
            nik: document.getElementById('nik').value.trim() || null,
        }),
    })
    .then(r => r.json().then(d => ({ ok: r.ok, d })))
    .then(({ ok, d }) => {
        if (!ok || !d.success) {
            alert(d.message || 'Gagal menarik data');
            return;
        }
        renderPreview(d.data);
        document.getElementById('previewPanel').classList.remove('d-none');
        document.getElementById('saveResultPanel').classList.add('d-none');
    })
    .catch(err => alert(err.message))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-download me-1"></i>Pull Logs';
    });
});

function renderPreview(data) {
    currentBatchId = data.batch_id;
    document.getElementById('btnSave').disabled = false;

    const meta = (data.machine_meta || []).map(m => `${m.nama} (${m.total})`).join(', ');
    document.getElementById('rawLogsTitle').innerHTML =
        `<i class="fas fa-list me-2"></i>Logs from [${meta}] — ${data.raw_total} records`;

    const rawBody = document.getElementById('rawLogsBody');
    rawBody.innerHTML = '';
    (data.raw_logs || []).forEach((row, i) => {
        rawBody.innerHTML += `<tr>
            <td>${i + 1}</td>
            <td>${row.waktu}</td>
            <td>${row.nik}</td>
            <td>${row.state}</td>
            <td>${row.type_raw}</td>
            <td>${row.verified}</td>
            <td>${row.mesin_nama}</td>
        </tr>`;
    });

    const aggBody = document.getElementById('aggBody');
    aggBody.innerHTML = '';
    (data.preview_save || []).forEach(row => {
        const cls = row.aksi === 'Lewati' ? 'table-warning' : (row.aksi === 'Insert' ? 'table-success' : 'table-info');
        aggBody.innerHTML += `<tr class="${cls}">
            <td>${row.tanggal}</td>
            <td>${row.nik}</td>
            <td>${row.nama}</td>
            <td>${row.jam_masuk}</td>
            <td>${row.jam_pulang}</td>
            <td>${row.mesin}</td>
            <td><strong>${row.aksi}</strong></td>
            <td class="small">${row.keterangan}</td>
        </tr>`;
    });
}

document.getElementById('btnSave').addEventListener('click', function() {
    if (!currentBatchId) return;
    if (!confirm('Simpan data agregasi ke t_absen?')) return;

    const btn = this;
    btn.disabled = true;

    fetch('{{ route('tarik-data-fingerprint.save') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            batch_id: currentBatchId,
            skip_unknown_nik: document.getElementById('skip_unknown_nik').checked ? 1 : 0,
        }),
    })
    .then(r => r.json().then(d => ({ ok: r.ok, d })))
    .then(({ ok, d }) => {
        const panel = document.getElementById('saveResultPanel');
        const body = document.getElementById('saveResultBody');
        panel.classList.remove('d-none');
        if (ok && d.success) {
            body.innerHTML = `<p class="mb-2">${d.message}</p>
                <ul class="mb-0">
                    <li>Insert: <strong>${d.data.inserted}</strong></li>
                    <li>Update: <strong>${d.data.updated}</strong></li>
                    <li>Lewati: <strong>${d.data.skipped}</strong></li>
                    <li>Error: <strong>${d.data.errors}</strong></li>
                </ul>`;
            panel.querySelector('.card-header').className = 'card-header bg-success text-white';
            currentBatchId = null;
            btn.disabled = true;
        } else {
            body.innerHTML = `<p class="text-danger mb-0">${d.message}</p>`;
            panel.querySelector('.card-header').className = 'card-header bg-danger text-white';
            btn.disabled = false;
        }
    });
});
</script>
@endpush
