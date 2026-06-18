@extends('layouts.app')

@section('title', 'Tarik Data Absensi API - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-cloud-download-alt me-2"></i>Tarik Data Absensi API
                </h2>
                <a href="{{ route('absen.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-list me-1"></i>Browse Absensi
                </a>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Modul ini menarik <strong>jam masuk &amp; jam pulang</strong> dari API HRIS eksternal
                (<code>GET /v1/management/attendances/logs</code>) ke tabel lokal <code>t_absen</code>.
                Field <code>note</code> / <code>shift</code> dari API <strong>tidak</strong> disimpan.
                Kredensial API diatur di file <code>.env</code> (HRIS_API_*).
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-plug me-2"></i>Koneksi API — {{ $apiBaseUrl }}
                </div>
                <div class="card-body">
                    <form id="tarikDataApiForm">
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label for="dari_tanggal" class="form-label">Dari Tanggal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dari_tanggal" name="dari_tanggal" value="{{ $defaultDari }}" required>
                            </div>
                            <div class="col-md-3">
                                <label for="sampai_tanggal" class="form-label">Sampai Tanggal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="sampai_tanggal" name="sampai_tanggal" value="{{ $defaultSampai }}" required>
                            </div>
                            <div class="col-md-3">
                                <label for="nik" class="form-label">NIK (opsional)</label>
                                <input type="text" class="form-control" id="nik" name="nik" maxlength="20" placeholder="Kosongkan = semua karyawan">
                                <small class="text-muted">Filter di sisi API</small>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100" id="btnTarik">
                                    <i class="fas fa-download me-2"></i>Tarik Data
                                </button>
                            </div>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="skip_unknown_nik" name="skip_unknown_nik" value="1" checked>
                            <label class="form-check-label" for="skip_unknown_nik">
                                Lewati NIK yang tidak ada di master karyawan
                            </label>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <i class="fas fa-exchange-alt me-2"></i>Pemetaan ke t_absen
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Field API</th>
                                    <th>Kolom t_absen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td><code>date</code></td><td><code>dtTanggal</code></td></tr>
                                <tr><td><code>nik</code></td><td><code>vcNik</code></td></tr>
                                <tr><td><code>clock_in</code></td><td><code>dtJamMasuk</code></td></tr>
                                <tr><td><code>clock_out</code></td><td><code>dtJamKeluar</code></td></tr>
                                <tr class="table-secondary"><td><code>note</code> / <code>shift</code></td><td><em>tidak ditarik</em></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <ul class="text-muted small mb-0 mt-2">
                        <li>Record <strong>baru</strong> (tanggal + NIK belum ada): di-insert dengan jam masuk/pulang dari API.</li>
                        <li>Record <strong>sudah ada</strong>: hanya kolom jam yang <strong>masih kosong</strong> di database yang diisi dari API.</li>
                        <li>Jika jam masuk &amp; pulang di database sudah terisi, record dilewati (tidak di-overwrite).</li>
                    </ul>
                </div>
            </div>

            <div id="resultPanel" class="card mt-4 d-none">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-check-circle me-2"></i>Hasil Tarik Data
                </div>
                <div class="card-body" id="resultBody"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function renderDetailTable(title, rows, columns, truncated, total) {
    if (!Array.isArray(rows) || rows.length === 0) return '';
    let html = `<p class="mb-1 mt-3"><strong>${title}</strong>`;
    if (truncated) {
        html += ` <span class="text-muted small">(menampilkan ${rows.length} dari ${total})</span>`;
    }
    html += `</p><div class="table-responsive">
        <table class="table table-sm table-bordered mb-0">
        <thead class="table-light"><tr>`;
    columns.forEach(col => { html += `<th>${col.label}</th>`; });
    html += `</tr></thead><tbody>`;
    rows.forEach(row => {
        html += '<tr>';
        columns.forEach(col => {
            const val = row[col.key] ?? '-';
            html += `<td class="${col.class || ''}">${val}</td>`;
        });
        html += '</tr>';
    });
    html += '</tbody></table></div>';
    return html;
}

document.getElementById('tarikDataApiForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const btn = document.getElementById('btnTarik');
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';

    const payload = {
        dari_tanggal: document.getElementById('dari_tanggal').value,
        sampai_tanggal: document.getElementById('sampai_tanggal').value,
        nik: document.getElementById('nik').value.trim() || null,
        skip_unknown_nik: document.getElementById('skip_unknown_nik').checked ? 1 : 0,
    };

    fetch('{{ route('tarik-data-absensi-api.pull') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        const panel = document.getElementById('resultPanel');
        const body = document.getElementById('resultBody');
        panel.classList.remove('d-none');

        if (ok && data.success) {
            const d = data.data || {};
            const hasErrors = (d.errors ?? 0) > 0;
            const hasSkipped = (d.skipped ?? 0) > 0;
            let html = `<p class="mb-2">${data.message}</p>
                <ul class="mb-3">
                    <li>Total dari API: <strong>${d.total_api ?? 0}</strong></li>
                    <li>Berhasil insert (baru): <strong class="text-success">${d.inserted ?? 0}</strong></li>
                    <li>Berhasil update (sudah ada): <strong class="text-primary">${d.updated ?? 0}</strong></li>
                    <li>Lewati: <strong class="text-warning">${d.skipped ?? 0}</strong></li>
                    <li>Error: <strong class="text-danger">${d.errors ?? 0}</strong></li>
                </ul>`;

            if (hasSkipped && d.skipped_reasons) {
                const labels = {
                    nik_tidak_di_master: 'NIK tidak ada di master karyawan',
                    data_tidak_lengkap: 'Data API tidak lengkap (tanpa tanggal/NIK)',
                    format_tidak_valid: 'Format data API tidak valid',
                    jam_sudah_lengkap: 'Jam masuk & pulang di database sudah terisi',
                    tidak_ada_jam_dari_api: 'API tidak mengirim jam masuk/pulang',
                };
                html += '<div class="alert alert-warning py-2 mb-3"><strong>Ringkasan dilewati:</strong><ul class="mb-0 mt-1">';
                for (const [key, count] of Object.entries(d.skipped_reasons)) {
                    html += `<li>${labels[key] || key}: <strong>${count}</strong></li>`;
                }
                html += '</ul></div>';
            }

            html += renderDetailTable(
                'Daftar record dilewati (selain jam sudah lengkap)',
                d.skipped_details,
                [
                    { key: 'tanggal', label: 'Tanggal' },
                    { key: 'nik', label: 'NIK' },
                    { key: 'nama', label: 'Nama' },
                    { key: 'alasan', label: 'Alasan', class: 'text-warning small' },
                ],
                d.skipped_details_truncated,
                d.skipped_details_total ?? d.skipped
            );

            if (hasErrors && d.error_summary) {
                html += '<div class="alert alert-danger py-2 mb-3"><strong>Ringkasan error:</strong><ul class="mb-0 mt-1">';
                for (const [reason, count] of Object.entries(d.error_summary)) {
                    html += `<li>${reason}: <strong>${count}</strong> record</li>`;
                }
                html += '</ul></div>';
            }

            html += renderDetailTable(
                'Daftar record error',
                d.error_details,
                [
                    { key: 'tanggal', label: 'Tanggal' },
                    { key: 'nik', label: 'NIK' },
                    { key: 'nama', label: 'Nama' },
                    { key: 'error', label: 'Penyebab', class: 'text-danger small' },
                ],
                d.error_details_truncated,
                d.errors
            );

            body.innerHTML = html;
            panel.querySelector('.card-header').className = (hasErrors || hasSkipped)
                ? 'card-header bg-warning text-dark'
                : 'card-header bg-success text-white';
        } else {
            body.innerHTML = `<p class="text-danger mb-0">${data.message || 'Gagal menarik data'}</p>`;
            panel.querySelector('.card-header').className = 'card-header bg-danger text-white';
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan: ' + err.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = original;
    });
});
</script>
@endpush
