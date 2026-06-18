@extends('layouts.app')

@section('title', 'Master Jabatan - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-briefcase me-2"></i>Master Jabatan
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
                        <table class="table table-hover" id="jabatanTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No.</th>
                                    <th width="15%">Kode Jabatan</th>
                                    <th width="35%">Nama Jabatan</th>
                                    <th width="15%">Grade</th>
                                    <th width="15%">Tanggal Dibuat</th>
                                    <th width="15%">Tanggal Diubah</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jabatans as $index => $jabatan)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><span class="badge bg-primary">{{ $jabatan->vcKodeJabatan }}</span></td>
                                    <td>
                                        <i class="fas fa-briefcase text-primary me-1"></i>
                                        {{ $jabatan->vcNamaJabatan }}
                                    </td>
                                    <td>
                                        @if($jabatan->vcGrade)
                                        <span class="badge bg-success">{{ $jabatan->vcGrade }}</span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($jabatan->dtCreate)
                                        <i class="fas fa-calendar-plus text-success me-1"></i>
                                        {{ $jabatan->dtCreate->format('d-m-Y H:i') }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($jabatan->dtChange)
                                        <i class="fas fa-calendar-check text-info me-1"></i>
                                        {{ $jabatan->dtChange->format('d-m-Y H:i') }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            @if(!empty($jabatan->vcKodeJabatan))
                                            <button type="button" class="btn btn-outline-primary" onclick="editJabatan('{{ $jabatan->vcKodeJabatan }}')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" onclick="deleteJabatan('{{ $jabatan->vcKodeJabatan }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Tidak ada data jabatan</p>
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

<!-- Modal Tambah/Edit Jabatan -->
<div class="modal fade" id="jabatanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="jabatanModalLabel">Tambah Jabatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="jabatanForm">
                <input type="hidden" name="_method" id="_method" value="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="vcKodeJabatan" class="form-label">Kode Jabatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="vcKodeJabatan" name="vcKodeJabatan" maxlength="7" required>
                        <div class="form-text">Maksimal 7 karakter</div>
                    </div>
                    <div class="mb-3">
                        <label for="vcNamaJabatan" class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="vcNamaJabatan" name="vcNamaJabatan" maxlength="50" required>
                        <div class="form-text">Maksimal 50 karakter</div>
                    </div>
                    <div class="mb-3">
                        <label for="vcGrade" class="form-label">Grade</label>
                        <input type="text" class="form-control" id="vcGrade" name="vcGrade" maxlength="10">
                        <div class="form-text">Maksimal 10 karakter (opsional)</div>
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
    document.getElementById('jabatanForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const url = isEditMode ? `/jabatan/${currentId}` : '/jabatan';

        // Set method spoofing for PUT requests BEFORE creating FormData
        if (isEditMode) {
            document.getElementById('_method').value = 'PUT';
        } else {
            document.getElementById('_method').value = 'POST';
        }

        // Create FormData after setting the method
        const formData = new FormData(this);

        // Debug: Log the method and form data
        console.log('Method:', document.getElementById('_method').value);
        console.log('URL:', url);
        console.log('FormData entries:');
        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }

        fetch(url, {
                method: 'POST',
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
                    bootstrap.Modal.getInstance(document.getElementById('jabatanModal')).hide();
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
        document.getElementById('jabatanModalLabel').textContent = 'Tambah Jabatan';
        document.getElementById('jabatanForm').reset();
        document.getElementById('_method').value = 'POST'; // Ensure method is set to POST
        document.getElementById('vcKodeJabatan').readOnly = false;
        new bootstrap.Modal(document.getElementById('jabatanModal')).show();
    });

    // Edit jabatan
    function editJabatan(id) {
        fetch(`/jabatan/${id}`, {
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
                    document.getElementById('jabatanModalLabel').textContent = 'Edit Jabatan';
                    document.getElementById('_method').value = 'PUT'; // Set method to PUT for edit
                    document.getElementById('vcKodeJabatan').value = data.jabatan.vcKodeJabatan;
                    document.getElementById('vcNamaJabatan').value = data.jabatan.vcNamaJabatan;
                    document.getElementById('vcGrade').value = data.jabatan.vcGrade || '';
                    document.getElementById('vcKodeJabatan').readOnly = true;
                    new bootstrap.Modal(document.getElementById('jabatanModal')).show();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Terjadi kesalahan saat memuat data');
            });
    }

    // Delete jabatan
    function deleteJabatan(id) {
        // Validasi ID tidak boleh kosong
        if (!id || id.trim() === '') {
            showAlert('error', 'ID jabatan tidak valid');
            return;
        }

        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            // Gunakan FormData untuk method spoofing (lebih kompatibel dengan Laravel)
            const formData = new FormData();
            formData.append('_method', 'DELETE');
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            fetch(`/jabatan/${id}`, {
                    method: 'POST', // Use POST for method spoofing
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => {
                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        return response.text().then(text => {
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                throw new Error('Invalid JSON response');
                            }
                        });
                    }
                })
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
                    showAlert('error', 'Terjadi kesalahan saat menghapus data: ' + error.message);
                });
        }
    }

    // Auto uppercase kode jabatan
    document.getElementById('vcKodeJabatan').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
</script>
@endpush