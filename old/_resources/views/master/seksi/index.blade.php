@extends('layouts.app')

@section('title', 'Master Seksi - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-layer-group me-2"></i>Master Seksi
                </h2>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSeksiModal">
                        <i class="fas fa-plus me-1"></i>Tambah
                    </button>
                    <button type="button" class="btn btn-warning" id="editBtn" disabled>
                        <i class="fas fa-edit me-1"></i>Edit
                    </button>
                    <button type="button" class="btn btn-info" id="refreshBtn">
                        <i class="fas fa-sync me-1"></i>Refresh
                    </button>
                    <button type="button" class="btn btn-danger" id="deleteBtn" disabled>
                        <i class="fas fa-trash me-1"></i>Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="seksiTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="20%">Kode Seksi</th>
                                    <th width="60%">Nama Seksi</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($seksis as $index => $seksi)
                                <tr data-id="{{ $seksi->vcKodeSeksi }}" class="table-row">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $seksi->vcKodeSeksi }}</td>
                                    <td>{{ $seksi->vcNamaSeksi }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary btn-sm edit-row" data-id="{{ $seksi->vcKodeSeksi }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm delete-row" data-id="{{ $seksi->vcKodeSeksi }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Tidak ada data seksi</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">{{ $seksis->count() }} Data.</span>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-sm btn-outline-secondary me-2">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <div class="mx-2">1 / 1</div>
                            <button class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Seksi -->
<div class="modal fade" id="addSeksiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Tambah Seksi Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSeksiForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="kode_seksi" class="form-label">Kode Seksi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kode_seksi" name="vcKodeSeksi" required maxlength="7">
                                <div class="form-text">Maksimal 7 karakter</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama_seksi" class="form-label">Nama Seksi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_seksi" name="vcNamaSeksi" required maxlength="35">
                                <div class="form-text">Maksimal 35 karakter</div>
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

<!-- Modal Edit Seksi -->
<div class="modal fade" id="editSeksiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Edit Seksi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editSeksiForm">
                <input type="hidden" id="edit_kode_seksi" name="vcKodeSeksi">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_nama_seksi" class="form-label">Nama Seksi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_nama_seksi" name="vcNamaSeksi" required maxlength="35">
                                <div class="form-text">Maksimal 35 karakter</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let selectedRow = null;
        let selectedSeksiId = null;

        // Row selection
        document.querySelectorAll('.table-row').forEach(row => {
            row.addEventListener('click', function(e) {
                // Skip if clicking on action buttons
                if (e.target.closest('.btn-group')) return;

                // Remove previous selection
                document.querySelectorAll('.table-row').forEach(r => r.classList.remove('table-primary'));

                // Add selection to current row
                this.classList.add('table-primary');
                selectedRow = this;
                selectedSeksiId = this.dataset.id;

                // Enable edit and delete buttons
                document.getElementById('editBtn').disabled = false;
                document.getElementById('deleteBtn').disabled = false;
            });
        });

        // Edit button in action column
        document.querySelectorAll('.edit-row').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const id = this.dataset.id;
                editSeksi(id);
            });
        });

        // Delete button in action column
        document.querySelectorAll('.delete-row').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const id = this.dataset.id;
                deleteSeksi(id);
            });
        });

        // Main edit button
        document.getElementById('editBtn').addEventListener('click', function() {
            if (selectedSeksiId) {
                editSeksi(selectedSeksiId);
            }
        });

        // Main delete button
        document.getElementById('deleteBtn').addEventListener('click', function() {
            if (selectedSeksiId && confirm('Apakah Anda yakin ingin menghapus seksi ini?')) {
                deleteSeksi(selectedSeksiId);
            }
        });

        // Refresh button
        document.getElementById('refreshBtn').addEventListener('click', function() {
            location.reload();
        });

        // Add form submission
        document.getElementById('addSeksiForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            fetch('/seksi', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Gagal menyimpan data: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan');
                });
        });

        // Edit form submission
        document.getElementById('editSeksiForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            fetch(`/seksi/${selectedSeksiId}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Gagal mengupdate data: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan');
                });
        });

        function editSeksi(id) {
            // Get data from selected row
            const row = document.querySelector(`tr[data-id="${id}"]`);
            const cells = row.cells;

            const kodeSeksi = cells[1].textContent;
            const namaSeksi = cells[2].textContent;

            // Set form values
            document.getElementById('edit_kode_seksi').value = kodeSeksi;
            document.getElementById('edit_nama_seksi').value = namaSeksi;

            // Show modal
            new bootstrap.Modal(document.getElementById('editSeksiModal')).show();
        }

        function deleteSeksi(id) {
            if (confirm('Apakah Anda yakin ingin menghapus seksi ini?')) {
                fetch(`/seksi/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Gagal menghapus data: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan');
                    });
            }
        }
    });
</script>
@endpush







