@extends('layouts.app')

@section('title', 'Izin Keluar Komplek Kantor - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-door-open me-2"></i>Izin Keluar Komplek Kantor
                </h2>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-success" id="addBtn">
                        <i class="fas fa-plus me-1"></i>Tambah
                    </button>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('izin-keluar.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="dari_tanggal" class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" id="dari_tanggal" name="dari_tanggal" value="{{ $startDate }}">
                            </div>
                            <div class="col-md-3">
                                <label for="sampai_tanggal" class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" id="sampai_tanggal" name="sampai_tanggal" value="{{ $endDate }}">
                            </div>
                            <div class="col-md-3">
                                <label for="nik" class="form-label">NIK</label>
                                <input type="text" class="form-control" id="nik" name="nik" value="{{ $nik }}" placeholder="Cari NIK">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 shadow-sm">
                                    <i class="fas fa-eye me-2"></i>Preview
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="max-height:600px; overflow-y:auto;">
                        <table class="table table-hover">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="11%">Tanggal</th>
                                    <th width="10%">NIK</th>
                                    <th width="18%">Nama</th>
                                    <th width="14%">Jenis Izin</th>
                                    <th width="10%">Dari</th>
                                    <th width="10%">Sampai</th>
                                    <th width="12%">Counter</th>
                                    <th width="10%">Keterangan</th>
                                    <th width="5%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($records as $row)
                                <tr>
                                    <td>{{ $row->dtTanggal?->format('d/m/Y') }}</td>
                                    <td><strong>{{ $row->vcNik }}</strong></td>
                                    <td>{{ $row->karyawan->Nama ?? 'N/A' }}</td>
                                    <td>{{ $row->jenisIzin->vcKeterangan ?? $row->vcKodeIzin }}</td>
                                    <td>{{ $row->dtDari ? substr($row->dtDari,0,5) : '-' }}</td>
                                    <td>{{ $row->dtSampai ? substr($row->dtSampai,0,5) : '-' }}</td>
                                    <td><span class="badge text-bg-secondary">{{ $row->vcCounter }}</span></td>
                                    <td>{{ $row->vcKeterangan }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="editRecord('{{ $row->vcCounter }}')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteRecord('{{ $row->vcCounter }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Belum ada data</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($records->hasPages())
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
                        <div class="text-muted small">
                            Menampilkan {{ $records->firstItem() }} sampai {{ $records->lastItem() }} dari {{ $records->total() }} data
                        </div>
                        <nav aria-label="Navigasi halaman">
                            {{ $records->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="izinModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="izinModalLabel">Tambah Izin Keluar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="izinForm">
                <input type="hidden" name="_method" id="_method" value="POST">
                <div class="modal-body">
                    <div class="mb-3 d-none" id="vcCounterGroup">
                        <label for="vcCounter" class="form-label">Kode Counter</label>
                        <input type="text" class="form-control" id="vcCounter" name="vcCounter" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="dtTanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="dtTanggal" name="dtTanggal" required>
                    </div>
                    <div class="mb-3">
                        <label for="vcNik" class="form-label">NIK <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="vcNik" name="vcNik" maxlength="10" required>
                        <div class="form-text" id="namaPreview"></div>
                    </div>
                    <div class="mb-3">
                        <label for="vcKodeIzin" class="form-label">Jenis Izin <span class="text-danger">*</span></label>
                        <select class="form-select" id="vcKodeIzin" name="vcKodeIzin" required>
                            <option value="">Pilih Jenis Izin</option>
                            @foreach($jenisIzins as $j)
                            <option value="{{ $j->vcKodeIzin }}">{{ $j->vcKeterangan }} ({{ $j->vcKodeIzin }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dtDari" class="form-label">Dari (HH:MM) <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="dtDari" name="dtDari" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dtSampai" class="form-label">Sampai (HH:MM) <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="dtSampai" name="dtSampai" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="vcKeterangan" class="form-label">Keterangan</label>
                        <input type="text" class="form-control" id="vcKeterangan" name="vcKeterangan" maxlength="35">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let isEditMode = false;
    let currentId = null; // vcCounter sebagai PK

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const html = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
        document.querySelectorAll('.alert').forEach(a => a.remove());
        document.querySelector('.container-fluid').insertAdjacentHTML('afterbegin', html);
        setTimeout(() => {
            const a = document.querySelector('.alert');
            if (a) a.remove();
        }, 4000);
    }

    document.getElementById('addBtn').addEventListener('click', () => {
        isEditMode = false;
        currentId = null;
        document.getElementById('izinModalLabel').textContent = 'Tambah Izin Keluar';
        document.getElementById('izinForm').reset();
        document.getElementById('_method').value = 'POST';
        document.getElementById('vcNik').readOnly = false;
            document.getElementById('vcCounterGroup').classList.add('d-none');
            document.getElementById('vcCounter').value = '';
            // Set default tanggal = hari ini (YYYY-MM-DD)
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            document.getElementById('dtTanggal').value = `${yyyy}-${mm}-${dd}`;
        new bootstrap.Modal(document.getElementById('izinModal')).show();
    });

    document.getElementById('izinForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const url = isEditMode ? `/izin-keluar/${currentId}` : '/izin-keluar';
        document.getElementById('_method').value = isEditMode ? 'PUT' : 'POST';
        const formData = new FormData(this);

        fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    bootstrap.Modal.getInstance(document.getElementById('izinModal')).hide();
                    location.reload();
                } else {
                    showAlert('error', data.message || 'Gagal menyimpan data');
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('error', 'Terjadi kesalahan saat menyimpan');
            });
    });

    function editRecord(id) {
        fetch(`/izin-keluar/${id}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    isEditMode = true;
                    currentId = id;
                    document.getElementById('izinModalLabel').textContent = 'Edit Izin Keluar';
                    document.getElementById('_method').value = 'PUT';
                    document.getElementById('vcCounterGroup').classList.remove('d-none');
                    document.getElementById('vcCounter').value = data.record.vcCounter || id;
                    document.getElementById('dtTanggal').value = data.record.dtTanggal;
                    document.getElementById('vcNik').value = data.record.vcNik;
                    document.getElementById('vcKodeIzin').value = data.record.vcKodeIzin;
                    document.getElementById('dtDari').value = data.record.dtDari?.substring(0, 5) || '';
                    document.getElementById('dtSampai').value = data.record.dtSampai?.substring(0, 5) || '';
                    document.getElementById('vcKeterangan').value = data.record.vcKeterangan || '';
                    document.getElementById('vcNik').readOnly = true;
                    new bootstrap.Modal(document.getElementById('izinModal')).show();
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('error', 'Gagal memuat data');
            });
    }

    function deleteRecord(id) {
        if (!confirm('Hapus data ini?')) return;
        fetch(`/izin-keluar/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    _method: 'DELETE'
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    location.reload();
                } else {
                    showAlert('error', data.message || 'Gagal menghapus data');
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('error', 'Terjadi kesalahan saat menghapus');
            });
    }

    // Autofill nama saat NIK di-blur (opsional)
    document.getElementById('vcNik').addEventListener('blur', function() {
        const nik = this.value.trim();
        if (!nik) return;
        fetch(`/karyawan/${nik}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json()).then(data => {
                document.getElementById('namaPreview').textContent = (data.success && data.karyawan?.Nama) ? 'Nama: ' + data.karyawan.Nama : '';
            }).catch(() => {
                document.getElementById('namaPreview').textContent = '';
            });
    });

    // Auto submit filter
    document.getElementById('dari_tanggal').addEventListener('change', () => document.getElementById('filterForm').submit());
    document.getElementById('sampai_tanggal').addEventListener('change', () => document.getElementById('filterForm').submit());
</script>
@endpush

