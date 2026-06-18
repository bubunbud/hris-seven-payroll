@extends('layouts.app')

@section('title', 'Master Ijin Keluar - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-sign-out-alt me-2"></i>Master Ijin Keluar Komplek Perusahaan
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
                        <table class="table table-hover" id="jenisIzinTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No.</th>
                                    <th width="15%">Kode Izin</th>
                                    <th width="30%">Keterangan</th>
                                    <th width="20%">Tanggal Dibuat</th>
                                    <th width="20%">Tanggal Diubah</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jenisIzins as $index => $jenisIzin)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><span class="badge bg-primary">{{ $jenisIzin->vcKodeIzin }}</span></td>
                                    <td>
                                        <i class="fas fa-sign-out-alt text-info me-1"></i>
                                        {{ $jenisIzin->vcKeterangan }}
                                    </td>
                                    <td>
                                        @if($jenisIzin->dtCreate)
                                        <i class="fas fa-calendar-plus text-success me-1"></i>
                                        {{ $jenisIzin->dtCreate->format('d-m-Y H:i') }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($jenisIzin->dtChange)
                                        <i class="fas fa-calendar-check text-info me-1"></i>
                                        {{ $jenisIzin->dtChange->format('d-m-Y H:i') }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary" onclick="editJenisIzin('{{ $jenisIzin->vcKodeIzin }}')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" onclick="deleteJenisIzin('{{ $jenisIzin->vcKodeIzin }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Tidak ada data jenis izin</p>
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

<!-- Modal Tambah/Edit Jenis Izin -->
<div class="modal fade" id="jenisIzinModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="jenisIzinModalLabel">Tambah Jenis Izin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="jenisIzinForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="vcKodeIzin" class="form-label">Kode Izin <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="vcKodeIzin" name="vcKodeIzin" maxlength="5" required>
                        <div class="form-text">Maksimal 5 karakter</div>
                    </div>
                    <div class="mb-3">
                        <label for="vcKeterangan" class="form-label">Keterangan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="vcKeterangan" name="vcKeterangan" maxlength="25" required>
                        <div class="form-text">Maksimal 25 karakter</div>
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

    // Show alert function
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

        // Remove existing alerts
        document.querySelectorAll('.alert').forEach(alert => alert.remove());

        // Add new alert
        const container = document.querySelector('.container-fluid');
        container.insertAdjacentHTML('afterbegin', alertHtml);

        // Auto hide after 3 seconds
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 3000);
    }

    // Form submission
    document.getElementById('jenisIzinForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const url = isEditMode ? `/jenis-izin/${currentId}` : '/jenis-izin';

        // Add method spoofing for PUT request
        if (isEditMode) {
            formData.append('_method', 'PUT');
        }

        fetch(url, {
                method: 'POST', // Always use POST for FormData
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    bootstrap.Modal.getInstance(document.getElementById('jenisIzinModal')).hide();
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
        document.getElementById('jenisIzinModalLabel').textContent = 'Tambah Jenis Izin';
        document.getElementById('jenisIzinForm').reset();
        document.getElementById('vcKodeIzin').readOnly = false;
        new bootstrap.Modal(document.getElementById('jenisIzinModal')).show();
    });

    // Edit jenis izin
    function editJenisIzin(id) {
        fetch(`/jenis-izin/${id}`, {
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
                    document.getElementById('jenisIzinModalLabel').textContent = 'Edit Jenis Izin';
                    document.getElementById('vcKodeIzin').value = data.jenisIzin.vcKodeIzin;
                    document.getElementById('vcKeterangan').value = data.jenisIzin.vcKeterangan;
                    document.getElementById('vcKodeIzin').readOnly = true;
                    new bootstrap.Modal(document.getElementById('jenisIzinModal')).show();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Terjadi kesalahan saat memuat data');
            });
    }

    // Delete jenis izin
    function deleteJenisIzin(id) {
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            fetch(`/jenis-izin/${id}`, {
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

    // Auto uppercase kode izin
    document.getElementById('vcKodeIzin').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
</script>
@endpush


