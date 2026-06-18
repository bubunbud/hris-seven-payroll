@extends('layouts.app')

@section('title', 'Master Shift Kerja - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-clock me-2"></i>Master Shift Kerja
                </h2>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-success" id="addBtn">
                        <i class="fas fa-plus me-1"></i>Tambah
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="shiftTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No.</th>
                                    <th width="15%">Kode Shift</th>
                                    <th width="20%">Jam Masuk</th>
                                    <th width="20%">Jam Pulang</th>
                                    <th width="25%">Keterangan</th>
                                    <th width="10%">Jumlah Karyawan</th>
                                    <th width="5%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shifts as $index => $shift)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><span class="badge bg-primary">{{ $shift->vcShift }}</span></td>
                                    <td>
                                        <i class="fas fa-clock text-success me-1"></i>
                                        {{ $shift->vcMasuk ? \Carbon\Carbon::parse($shift->vcMasuk)->format('H:i') : '-' }}
                                    </td>
                                    <td>
                                        <i class="fas fa-clock text-danger me-1"></i>
                                        {{ $shift->vcPulang ? \Carbon\Carbon::parse($shift->vcPulang)->format('H:i') : '-' }}
                                    </td>
                                    <td>{{ $shift->vcKeterangan ?: '-' }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $shift->karyawans()->count() }} karyawan</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary" onclick="editShift('{{ $shift->vcShift }}')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" onclick="deleteShift('{{ $shift->vcShift }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Tidak ada data shift kerja</p>
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

<!-- Modal Tambah/Edit Shift -->
<div class="modal fade" id="shiftModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Shift Kerja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="shiftForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="vcShift" class="form-label">Kode Shift <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="vcShift" name="vcShift" maxlength="5" required>
                        <div class="form-text">Maksimal 5 karakter</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vcMasuk" class="form-label">Jam Masuk <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="vcMasuk" name="vcMasuk" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vcPulang" class="form-label">Jam Pulang <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="vcPulang" name="vcPulang" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="vcKeterangan" class="form-label">Keterangan</label>
                        <input type="text" class="form-control" id="vcKeterangan" name="vcKeterangan" maxlength="50">
                        <div class="form-text">Maksimal 50 karakter</div>
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

    // Form submission
    document.getElementById('shiftForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const url = isEditMode ? `/shift/${currentId}` : '/shift';
        const method = isEditMode ? 'PUT' : 'POST';

        fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(Object.fromEntries(formData))
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

    // Add button click
    document.getElementById('addBtn').addEventListener('click', function() {
        isEditMode = false;
        currentId = null;
        document.getElementById('modalTitle').textContent = 'Tambah Shift Kerja';
        document.getElementById('shiftForm').reset();
        new bootstrap.Modal(document.getElementById('shiftModal')).show();
    });

    // Edit shift
    function editShift(id) {
        fetch(`/shift/${id}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    isEditMode = true;
                    currentId = id;
                    document.getElementById('modalTitle').textContent = 'Edit Shift Kerja';
                    document.getElementById('vcShift').value = data.shift.vcShift;
                    document.getElementById('vcMasuk').value = data.shift.vcMasuk;
                    document.getElementById('vcPulang').value = data.shift.vcPulang;
                    document.getElementById('vcKeterangan').value = data.shift.vcKeterangan || '';
                    new bootstrap.Modal(document.getElementById('shiftModal')).show();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Terjadi kesalahan saat memuat data');
            });
    }

    // Delete shift
    function deleteShift(id) {
        if (confirm('Apakah Anda yakin ingin menghapus shift kerja ini?')) {
            fetch(`/shift/${id}`, {
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
    }

    // Show alert
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Insert alert at the top of the page
        const container = document.querySelector('.container-fluid');
        container.insertAdjacentHTML('afterbegin', alertHtml);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            }
        }, 5000);
    }
</script>
@endpush



