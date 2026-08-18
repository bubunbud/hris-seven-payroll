@extends('layouts.app')

@section('title', 'Master Mesin Fingerprint - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-fingerprint me-2"></i>Master Mesin Fingerprint</h2>
                <div class="btn-group">
                    <a href="{{ route('tarik-data-fingerprint.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-download me-1"></i>Tarik Data
                    </a>
                    <button type="button" class="btn btn-success btn-sm" id="addBtn">
                        <i class="fas fa-plus me-1"></i>Tambah Mesin
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No.</th>
                                    <th>Nama</th>
                                    <th>Merk / Tipe</th>
                                    <th>IP</th>
                                    <th>Port</th>
                                    <th>Status</th>
                                    <th>Last Pull</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mesins as $i => $mesin)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td><strong>{{ $mesin->vcNama }}</strong></td>
                                    <td>{{ $mesin->vcMerk }} / {{ $mesin->vcTipe ?: '-' }}</td>
                                    <td><code>{{ $mesin->vcIp }}</code></td>
                                    <td>{{ $mesin->intPort }}</td>
                                    <td>
                                        @if($mesin->vcAktif === '1')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $mesin->dtLastPull ? $mesin->dtLastPull->format('d/m/Y H:i:s') : '-' }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-info" onclick="testMesin({{ $mesin->id }})" title="Test Koneksi">
                                                <i class="fas fa-plug"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" onclick="editMesin({{ $mesin->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" onclick="deleteMesin({{ $mesin->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Belum ada mesin terdaftar.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="mesinModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="mesinForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="mesinModalTitle">Tambah Mesin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="mesin_id">
                    <div class="mb-3">
                        <label class="form-label">Nama Mesin <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="vcNama" required maxlength="100">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Merk</label>
                            <input type="text" class="form-control" id="vcMerk" value="Solution" maxlength="50">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipe</label>
                            <input type="text" class="form-control" id="vcTipe" placeholder="X302-S" maxlength="50">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-8">
                            <label class="form-label">IP Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="vcIp" required placeholder="192.168.29.9">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Port</label>
                            <input type="number" class="form-control" id="intPort" value="4370" min="1" max="65535">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Comm Key</label>
                            <input type="number" class="form-control" id="intCommKey" value="0" min="0">
                            <small class="text-muted">0 = tanpa password</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="vcAktif">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" id="vcKeterangan" rows="2" maxlength="255"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const mesinModal = new bootstrap.Modal(document.getElementById('mesinModal'));

document.getElementById('addBtn').addEventListener('click', () => {
    document.getElementById('mesinModalTitle').textContent = 'Tambah Mesin';
    document.getElementById('mesinForm').reset();
    document.getElementById('mesin_id').value = '';
    document.getElementById('intPort').value = 4370;
    document.getElementById('intCommKey').value = 0;
    document.getElementById('vcMerk').value = 'Solution';
    document.getElementById('vcAktif').value = '1';
    mesinModal.show();
});

function editMesin(id) {
    fetch(`{{ url('mesin-fingerprint') }}/${id}`)
        .then(r => r.json())
        .then(res => {
            const m = res.mesin;
            document.getElementById('mesinModalTitle').textContent = 'Edit Mesin';
            document.getElementById('mesin_id').value = m.id;
            document.getElementById('vcNama').value = m.vcNama;
            document.getElementById('vcMerk').value = m.vcMerk || 'Solution';
            document.getElementById('vcTipe').value = m.vcTipe || '';
            document.getElementById('vcIp').value = m.vcIp;
            document.getElementById('intPort').value = m.intPort;
            document.getElementById('intCommKey').value = m.intCommKey || 0;
            document.getElementById('vcAktif').value = m.vcAktif;
            document.getElementById('vcKeterangan').value = m.vcKeterangan || '';
            mesinModal.show();
        });
}

document.getElementById('mesinForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('mesin_id').value;
    const payload = {
        vcNama: document.getElementById('vcNama').value,
        vcMerk: document.getElementById('vcMerk').value,
        vcTipe: document.getElementById('vcTipe').value,
        vcIp: document.getElementById('vcIp').value,
        intPort: document.getElementById('intPort').value,
        intCommKey: document.getElementById('intCommKey').value,
        vcAktif: document.getElementById('vcAktif').value,
        vcKeterangan: document.getElementById('vcKeterangan').value,
    };
    const url = id ? `{{ url('mesin-fingerprint') }}/${id}` : '{{ route('mesin-fingerprint.store') }}';
    const method = id ? 'PUT' : 'POST';

    fetch(url, {
        method,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) location.reload();
        else alert(res.message || 'Gagal menyimpan');
    });
});

function testMesin(id) {
    fetch(`{{ url('mesin-fingerprint') }}/${id}/test-connection`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(res => alert(res.message || (res.success ? 'OK' : 'Gagal')));
}

function deleteMesin(id) {
    if (!confirm('Hapus mesin ini?')) return;
    fetch(`{{ url('mesin-fingerprint') }}/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(res => { if (res.success) location.reload(); else alert(res.message); });
}
</script>
@endpush
