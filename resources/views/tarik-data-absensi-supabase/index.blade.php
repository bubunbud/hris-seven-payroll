@extends('layouts.app')

@section('title', 'Tarik Data Absensi Supabase - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-cloud-download-alt me-2"></i>Tarik Data Absensi Supabase</h2>
            </div>

            <div class="alert alert-info small">
                <i class="fas fa-info-circle me-1"></i>
                API: <code>{{ $apiBaseUrl }}</code> — alur <strong>dry run</strong> (preview dulu, baru simpan ke <code>t_absen</code>).
                Jam yang sudah terisi di database tidak akan ditimpa.
                <br><strong>Catatan:</strong> gunakan <code>service_role</code> key di <code>.env</code> (bukan publishable key) karena RLS Supabase.
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">Tarik Log dari Supabase</div>
                <div class="card-body">
                    <form id="pullForm">
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Dari Tanggal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dari_tanggal" value="{{ $defaultDari }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sampai Tanggal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="sampai_tanggal" value="{{ $defaultSampai }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">NIK (opsional)</label>
                                <input type="text" class="form-control" id="nik" maxlength="20" placeholder="Semua NIK">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100" id="btnPull">
                                    <i class="fas fa-download me-1"></i>Pull Data
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

            <div id="pullAlert" class="alert d-none mb-4" role="alert"></div>

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
                                        <th>NIK</th>
                                        <th>Nama</th>
                                        <th>Type</th>
                                        <th>Sumber</th>
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
                                        <th>Sumber</th>
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

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

    fetch('{{ route('tarik-data-absensi-supabase.pull') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
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
        renderPreview(d.data, d.message);
        document.getElementById('previewPanel').classList.remove('d-none');
        document.getElementById('saveResultPanel').classList.add('d-none');
    })
    .catch(err => alert(err.message))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-download me-1"></i>Pull Data';
    });
});

function renderPreview(data, message) {
    currentBatchId = data.batch_id;
    document.getElementById('btnSave').disabled = (data.raw_total || 0) === 0;

    const alertEl = document.getElementById('pullAlert');
    alertEl.classList.remove('d-none', 'alert-warning', 'alert-info', 'alert-success');
    if ((data.raw_total || 0) === 0) {
        alertEl.classList.add('alert-warning');
        alertEl.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i><strong>Tidak ada data.</strong> ' + (message || '')
            + '<br><small class="text-muted">Cek Supabase Table Editor (tabel <code>attendance</code>). Jika data ada di dashboard tapi tidak muncul di sini, kemungkinan RLS — gunakan <code>service_role</code> key di <code>.env</code>.</small>';
    } else {
        alertEl.classList.add('alert-success');
        alertEl.textContent = message || ('Berhasil menarik ' + data.raw_total + ' log.');
    }

    document.getElementById('rawLogsTitle').innerHTML =
        `<i class="fas fa-list me-2"></i>Logs dari Supabase — ${data.raw_total} records`;

    const rawBody = document.getElementById('rawLogsBody');
    rawBody.innerHTML = '';
    (data.raw_logs || []).forEach((row, i) => {
        rawBody.innerHTML += `<tr>
            <td>${i + 1}</td>
            <td>${row.waktu}</td>
            <td>${row.nik}</td>
            <td>${row.nama || '-'}</td>
            <td>${row.type_raw}</td>
            <td>${row.sumber || 'Supabase'}</td>
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
            <td>${row.sumber}</td>
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

    fetch('{{ route('tarik-data-absensi-supabase.save') }}', {
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
