@extends('layouts.app')

@section('title', 'Master Jenis Ijin - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-calendar-times me-2"></i>Master Jenis Ijin (Tidak Masuk)
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
                        <table class="table table-hover" id="jenisIjinTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No.</th>
                                    <th width="15%">Kode Absen</th>
                                    <th width="30%">Keterangan</th>
                                    <th width="20%">Tanggal Dibuat</th>
                                    <th width="20%">Tanggal Diubah</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jenisIjins as $index => $jenisIjin)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><span class="badge bg-primary">{{ $jenisIjin->vcKodeAbsen }}</span></td>
                                    <td>
                                        <i class="fas fa-calendar-times text-warning me-1"></i>
                                        {{ $jenisIjin->vcKeterangan }}
                                    </td>
                                    <td>
                                        @if($jenisIjin->dtCreate)
                                        <i class="fas fa-calendar-plus text-success me-1"></i>
                                        {{ $jenisIjin->dtCreate->format('d-m-Y H:i') }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($jenisIjin->dtChange)
                                        <i class="fas fa-calendar-check text-info me-1"></i>
                                        {{ $jenisIjin->dtChange->format('d-m-Y H:i') }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary" onclick="editJenisIjin('{{ $jenisIjin->vcKodeAbsen }}')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" onclick="deleteJenisIjin('{{ $jenisIjin->vcKodeAbsen }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Tidak ada data jenis ijin</p>
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

<!-- Modal Tambah/Edit Jenis Ijin -->
<div class="modal fade" id="jenisIjinModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="jenisIjinModalLabel">Tambah Jenis Ijin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="jenisIjinForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="vcKodeAbsen" class="form-label">Kode Absen <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="vcKodeAbsen" name="vcKodeAbsen" maxlength="5" required>
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
    document.getElementById('jenisIjinForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const url = isEditMode ? `/jenis-ijin/${currentId}` : '/jenis-ijin';

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
                    bootstrap.Modal.getInstance(document.getElementById('jenisIjinModal')).hide();
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
        document.getElementById('jenisIjinModalLabel').textContent = 'Tambah Jenis Ijin';
        document.getElementById('jenisIjinForm').reset();
        document.getElementById('vcKodeAbsen').readOnly = false;
        new bootstrap.Modal(document.getElementById('jenisIjinModal')).show();
    });

    // Edit jenis ijin
    function editJenisIjin(id) {
        fetch(`/jenis-ijin/${id}`, {
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
                    document.getElementById('jenisIjinModalLabel').textContent = 'Edit Jenis Ijin';
                    document.getElementById('vcKodeAbsen').value = data.jenisIjin.vcKodeAbsen;
                    document.getElementById('vcKeterangan').value = data.jenisIjin.vcKeterangan;
                    document.getElementById('vcKodeAbsen').readOnly = true;
                    new bootstrap.Modal(document.getElementById('jenisIjinModal')).show();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Terjadi kesalahan saat memuat data');
            });
    }

    // Delete jenis ijin
    function deleteJenisIjin(id) {
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            fetch(`/jenis-ijin/${id}`, {
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

    // Auto uppercase kode absen
    document.getElementById('vcKodeAbsen').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
</script>
@endpush