@extends('layouts.app')

@section('title', 'Master Shift Security - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-shield-alt me-2"></i>Master Shift Security / Satpam
                </h2>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-success" id="addBtn">
                        <i class="fas fa-plus me-1"></i>Tambah
                    </button>
                </div>
            </div>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="shiftTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No.</th>
                                    <th width="10%">Kode Shift</th>
                                    <th width="15%">Nama Shift</th>
                                    <th width="12%">Jam Masuk</th>
                                    <th width="12%">Jam Pulang</th>
                                    <th width="10%">Durasi (Jam)</th>
                                    <th width="8%">Cross Day</th>
                                    <th width="13%">Toleransi</th>
                                    <th width="13%">Keterangan</th>
                                    <th width="5%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shifts as $index => $shift)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><span class="badge bg-primary">{{ $shift->vcKodeShift }}</span></td>
                                    <td><strong>{{ $shift->vcNamaShift }}</strong></td>
                                    <td>
                                        <i class="fas fa-clock text-success me-1"></i>
                                        {{ \Carbon\Carbon::parse($shift->dtJamMasuk)->format('H:i') }}
                                    </td>
                                    <td>
                                        <i class="fas fa-clock text-danger me-1"></i>
                                        {{ \Carbon\Carbon::parse($shift->dtJamPulang)->format('H:i') }}
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ number_format($shift->intDurasiJam, 2) }} jam</span>
                                    </td>
                                    <td>
                                        @if($shift->isCrossDay)
                                        <span class="badge bg-warning text-dark"><i class="fas fa-moon me-1"></i>Ya</span>
                                        @else
                                        <span class="badge bg-secondary">Tidak</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>Masuk: ±{{ $shift->intToleransiMasuk }}m<br>Pulang: ±{{ $shift->intToleransiPulang }}m</small>
                                    </td>
                                    <td class="small">{{ Str::limit($shift->vcKeterangan ?? '-', 20) }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary btn-edit-shift" data-id="{{ $shift->vcKodeShift }}" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-delete-shift" data-id="{{ $shift->vcKodeShift }}" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Tidak ada data shift security</p>
                                        <button type="button" class="btn btn-sm btn-primary mt-2" id="btnAddFirst">
                                            <i class="fas fa-plus me-1"></i>Tambah Shift Pertama
                                        </button>
                                    </td>
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

<!-- Modal Tambah/Edit Shift Security -->
<div class="modal fade" id="shiftModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Shift Security</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="shiftForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vcKodeShift" class="form-label">Kode Shift <span class="text-danger">*</span></label>
                                <select class="form-select" id="vcKodeShift" name="vcKodeShift" required>
                                    <option value="">-- Pilih Kode Shift --</option>
                                    <option value="1">1 - Shift 1</option>
                                    <option value="2">2 - Shift 2</option>
                                    <option value="3">3 - Shift 3</option>
                                </select>
                                <div class="form-text">Kode shift harus 1, 2, atau 3</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vcNamaShift" class="form-label">Nama Shift <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="vcNamaShift" name="vcNamaShift" maxlength="20" required placeholder="Contoh: Shift 1">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dtJamMasuk" class="form-label">Jam Masuk <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="dtJamMasuk" name="dtJamMasuk" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dtJamPulang" class="form-label">Jam Pulang <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="dtJamPulang" name="dtJamPulang" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="intDurasiJam" class="form-label">Durasi (Jam) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="intDurasiJam" name="intDurasiJam" value="8.00" step="0.01" min="0" max="24" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="intToleransiMasuk" class="form-label">Toleransi Masuk (Menit) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="intToleransiMasuk" name="intToleransiMasuk" value="30" min="0" max="120" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="intToleransiPulang" class="form-label">Toleransi Pulang (Menit) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="intToleransiPulang" name="intToleransiPulang" value="30" min="0" max="120" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="isCrossDay" name="isCrossDay" value="1">
                                    <label class="form-check-label" for="isCrossDay">Cross Day (Shift melewati tengah malam)</label>
                                </div>
                                <div class="form-text">Centang jika shift berakhir di hari berikutnya (contoh: 22:30 - 06:30)</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vcKeterangan" class="form-label">Keterangan</label>
                                <input type="text" class="form-control" id="vcKeterangan" name="vcKeterangan" maxlength="100" placeholder="Contoh: Pagi, Siang, Malam">
                            </div>
                        </div>
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
    let currentId = null;

    document.getElementById('shiftForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        data.isCrossDay = document.getElementById('isCrossDay').checked ? 1 : 0;

        const url = isEditMode ? '{{ url("master-shift-security") }}/' + currentId : '{{ route("master-shift-security.store") }}';
        const method = isEditMode ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                bootstrap.Modal.getInstance(document.getElementById('shiftModal')).hide();
                location.reload();
            } else {
                showAlert('error', data.message || 'Terjadi kesalahan saat menyimpan data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Terjadi kesalahan saat menyimpan data');
        });
    });

    function openAddModal() {
        isEditMode = false;
        currentId = null;
        document.getElementById('modalTitle').textContent = 'Tambah Shift Security';
        document.getElementById('shiftForm').reset();
        document.getElementById('vcKodeShift').disabled = false;
        document.getElementById('intDurasiJam').value = '8.00';
        document.getElementById('intToleransiMasuk').value = '30';
        document.getElementById('intToleransiPulang').value = '30';
        document.getElementById('isCrossDay').checked = false;
        new bootstrap.Modal(document.getElementById('shiftModal')).show();
    }

    document.getElementById('addBtn').addEventListener('click', openAddModal);
    document.getElementById('btnAddFirst')?.addEventListener('click', openAddModal);

    document.querySelectorAll('.btn-edit-shift').forEach(btn => {
        btn.addEventListener('click', function() { editShift(parseInt(this.getAttribute('data-id'))); });
    });
    document.querySelectorAll('.btn-delete-shift').forEach(btn => {
        btn.addEventListener('click', function() { deleteShift(parseInt(this.getAttribute('data-id'))); });
    });

    function editShift(id) {
        fetch('{{ url("master-shift-security") }}/' + id, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const s = data.shift;
                isEditMode = true;
                currentId = id;
                document.getElementById('modalTitle').textContent = 'Edit Shift Security';
                document.getElementById('vcKodeShift').value = s.vcKodeShift;
                document.getElementById('vcKodeShift').disabled = true;
                document.getElementById('vcNamaShift').value = s.vcNamaShift;
                document.getElementById('dtJamMasuk').value = s.dtJamMasuk;
                document.getElementById('dtJamPulang').value = s.dtJamPulang;
                document.getElementById('intDurasiJam').value = s.intDurasiJam;
                document.getElementById('intToleransiMasuk').value = s.intToleransiMasuk;
                document.getElementById('intToleransiPulang').value = s.intToleransiPulang;
                document.getElementById('isCrossDay').checked = s.isCrossDay;
                document.getElementById('vcKeterangan').value = s.vcKeterangan || '';
                new bootstrap.Modal(document.getElementById('shiftModal')).show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Terjadi kesalahan saat memuat data');
        });
    }

    function deleteShift(id) {
        if (!confirm('Apakah Anda yakin ingin menghapus shift security ini?')) return;

        fetch('{{ url("master-shift-security") }}/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                location.reload();
            } else {
                showAlert('error', data.message || 'Terjadi kesalahan saat menghapus data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Terjadi kesalahan saat menghapus data');
        });
    }

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        const container = document.querySelector('.container-fluid');
        container.insertAdjacentHTML('afterbegin', alertHtml);
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) bootstrap.Alert.getOrCreateInstance(alert).close();
        }, 5000);
    }
</script>
@endpush
