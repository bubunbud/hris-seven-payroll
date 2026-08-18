@extends('layouts.app')

@section('title', 'Tarik Data Izin/Sakit/Cuti Supabase - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-calendar-minus me-2"></i>Tarik Data Izin / Sakit / Cuti Supabase</h2>
                <a href="{{ route('tidak-masuk.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-list me-1"></i>Browse Tidak Masuk
                </a>
            </div>

            <div class="alert alert-info small">
                <i class="fas fa-info-circle me-1"></i>
                Sumber: tabel <code>leave_requests</code> di Supabase — hanya status <strong>approved</strong>.
                Preview dulu, baru simpan ke <code>t_tidak_masuk</code>.
                <br>Mapping: <strong>Sakit</strong> → S010, <strong>Izin</strong> → I002, <strong>Cuti</strong> → C010 (cuti melahirkan → C012).
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">Tarik Pengajuan dari Supabase</div>
                <div class="card-body">
                    <form id="pullForm">
                        <div class="row g-3 mb-3">
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
                            <div class="col-md-4">
                                <label class="form-label">Tipe</label>
                                <div class="d-flex flex-wrap gap-3 pt-1">
                                    <div class="form-check">
                                        <input class="form-check-input type-filter" type="checkbox" value="sakit" id="type_sakit" checked>
                                        <label class="form-check-label" for="type_sakit">Sakit</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input type-filter" type="checkbox" value="izin" id="type_izin" checked>
                                        <label class="form-check-label" for="type_izin">Izin</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input type-filter" type="checkbox" value="cuti" id="type_cuti" checked>
                                        <label class="form-check-label" for="type_cuti">Cuti</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
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
                        <span id="rawTitle"><i class="fas fa-list me-2"></i>Data dari Supabase</span>
                        <button type="button" class="btn btn-success btn-sm" id="btnSave" disabled>
                            <i class="fas fa-save me-1"></i>Simpan ke t_tidak_masuk
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 360px;">
                            <table class="table table-sm table-striped mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>No.</th>
                                        <th>NIK</th>
                                        <th>Nama</th>
                                        <th>Tipe</th>
                                        <th>Kode</th>
                                        <th>Mulai</th>
                                        <th>Selesai</th>
                                        <th>Hari</th>
                                        <th>Status</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody id="rawBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-light"><i class="fas fa-layer-group me-2"></i>Preview Simpan ke t_tidak_masuk</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>NIK</th>
                                        <th>Nama</th>
                                        <th>Tipe</th>
                                        <th>Kode</th>
                                        <th>Jenis Absen</th>
                                        <th>Mulai</th>
                                        <th>Selesai</th>
                                        <th>Hari</th>
                                        <th>Aksi</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody id="previewBody"></tbody>
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
    const types = Array.from(document.querySelectorAll('.type-filter:checked')).map(el => el.value);
    if (!types.length) { alert('Pilih minimal satu tipe'); return; }

    const btn = document.getElementById('btnPull');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

    fetch('{{ route('tarik-data-leave-supabase.pull') }}', {
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
            types: types,
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
    alertEl.classList.remove('d-none', 'alert-warning', 'alert-success');
    if ((data.raw_total || 0) === 0) {
        alertEl.classList.add('alert-warning');
        alertEl.textContent = message || 'Tidak ada data.';
    } else {
        alertEl.classList.add('alert-success');
        alertEl.textContent = message || '';
    }

    document.getElementById('rawTitle').innerHTML =
        `<i class="fas fa-list me-2"></i>leave_requests — ${data.raw_total} record`;

    const rawBody = document.getElementById('rawBody');
    rawBody.innerHTML = '';
    (data.raw_rows || []).forEach((row, i) => {
        rawBody.innerHTML += `<tr>
            <td>${i + 1}</td>
            <td>${row.nik}</td>
            <td>${row.nama || '-'}</td>
            <td>${row.type_label || row.type}</td>
            <td><code>${row.vcKodeAbsen}</code></td>
            <td>${row.dtTanggalMulai}</td>
            <td>${row.dtTanggalSelesai}</td>
            <td>${row.jumlah_hari}</td>
            <td>${row.status || '-'}</td>
            <td class="small">${row.vcKeterangan || '-'}</td>
        </tr>`;
    });

    const previewBody = document.getElementById('previewBody');
    previewBody.innerHTML = '';
    (data.preview_save || []).forEach(row => {
        const cls = row.aksi === 'Lewati' ? 'table-warning' : (row.aksi === 'Insert' ? 'table-success' : 'table-info');
        previewBody.innerHTML += `<tr class="${cls}">
            <td>${row.nik}</td>
            <td>${row.nama}</td>
            <td>${row.type}</td>
            <td><code>${row.vcKodeAbsen}</code></td>
            <td>${row.jenis_absen}</td>
            <td>${row.dtTanggalMulai}</td>
            <td>${row.dtTanggalSelesai}</td>
            <td>${row.jumlah_hari}</td>
            <td><strong>${row.aksi}</strong></td>
            <td class="small">${row.keterangan}</td>
        </tr>`;
    });
}

document.getElementById('btnSave').addEventListener('click', function() {
    if (!currentBatchId) return;
    if (!confirm('Simpan data ke t_tidak_masuk?')) return;

    const btn = this;
    btn.disabled = true;

    fetch('{{ route('tarik-data-leave-supabase.save') }}', {
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
        } else {
            body.innerHTML = `<p class="text-danger mb-0">${d.message}</p>`;
            panel.querySelector('.card-header').className = 'card-header bg-danger text-white';
            btn.disabled = false;
        }
    });
});
</script>
@endpush
