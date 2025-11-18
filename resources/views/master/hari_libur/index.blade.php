@extends('layouts.app')

@section('title', 'Master Hari Libur - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>Master Hari Libur
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
                        <table class="table table-hover" id="hariLiburTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No.</th>
                                    <th width="15%">Tanggal</th>
                                    <th width="30%">Keterangan</th>
                                    <th width="20%">Tipe Hari Libur</th>
                                    <th width="15%">Tanggal Dibuat</th>
                                    <th width="15%">Tanggal Diubah</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hariLiburs as $index => $hariLibur)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <i class="fas fa-calendar text-primary me-1"></i>
                                        {{ $hariLibur->dtTanggal ? $hariLibur->dtTanggal->format('d-m-Y') : '-' }}
                                    </td>
                                    <td>
                                        <i class="fas fa-info-circle text-info me-1"></i>
                                        {{ $hariLibur->vcKeterangan }}
                                    </td>
                                    <td>
                                        @if($hariLibur->vcTipeHariLibur)
                                        @if($hariLibur->vcTipeHariLibur == 'Libur Nasional')
                                        <span class="badge bg-danger">{{ $hariLibur->vcTipeHariLibur }}</span>
                                        @else
                                        <span class="badge bg-warning text-dark">{{ $hariLibur->vcTipeHariLibur }}</span>
                                        @endif
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($hariLibur->dtCreate)
                                        <i class="fas fa-calendar-plus text-success me-1"></i>
                                        {{ $hariLibur->dtCreate->format('d-m-Y') }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($hariLibur->dtChange)
                                        <i class="fas fa-calendar-check text-info me-1"></i>
                                        {{ $hariLibur->dtChange->format('d-m-Y') }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary" onclick="editHariLibur('{{ $hariLibur->dtTanggal }}')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" onclick="deleteHariLibur('{{ $hariLibur->dtTanggal }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Tidak ada data hari libur</p>
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

<!-- Modal Tambah/Edit Hari Libur -->
<div class="modal fade" id="hariLiburModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="hariLiburModalLabel">Tambah Hari Libur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="hariLiburForm">
                <input type="hidden" name="_method" id="_method" value="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="dtTanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="dtTanggal" name="dtTanggal" required>
                    </div>
                    <div class="mb-3">
                        <label for="vcKeterangan" class="form-label">Keterangan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="vcKeterangan" name="vcKeterangan" maxlength="35" required>
                        <div class="form-text">Maksimal 35 karakter</div>
                    </div>
                    <div class="mb-3">
                        <label for="vcTipeHariLibur" class="form-label">Tipe Hari Libur <span class="text-danger">*</span></label>
                        <select class="form-select" id="vcTipeHariLibur" name="vcTipeHariLibur" required>
                            <option value="">Pilih Tipe Hari Libur</option>
                            <option value="Libur Nasional">Libur Nasional</option>
                            <option value="Cuti Bersama">Cuti Bersama</option>
                        </select>
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

    // Helper function to format date for input field
    function formatDateForInput(dateString) {
        if (!dateString) return '';
        // Convert from YYYY-MM-DD to YYYY-MM-DD format for input[type="date"]
        const date = new Date(dateString);
        return date.toISOString().split('T')[0];
    }

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
    document.getElementById('hariLiburForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const url = isEditMode ? `/hari-libur/${currentId}` : '/hari-libur';

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
                    bootstrap.Modal.getInstance(document.getElementById('hariLiburModal')).hide();
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
        document.getElementById('hariLiburModalLabel').textContent = 'Tambah Hari Libur';
        document.getElementById('hariLiburForm').reset();
        document.getElementById('_method').value = 'POST'; // Ensure method is set to POST
        document.getElementById('dtTanggal').readOnly = false;
        new bootstrap.Modal(document.getElementById('hariLiburModal')).show();
    });

    // Edit hari libur
    function editHariLibur(id) {
        fetch(`/hari-libur/${id}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Received data:', data); // Debug log
                if (data.success) {
                    isEditMode = true;
                    currentId = id;
                    document.getElementById('hariLiburModalLabel').textContent = 'Edit Hari Libur';
                    document.getElementById('_method').value = 'PUT'; // Set method to PUT for edit
                    document.getElementById('dtTanggal').value = formatDateForInput(data.hariLibur.dtTanggal);
                    document.getElementById('vcKeterangan').value = data.hariLibur.vcKeterangan;
                    document.getElementById('vcTipeHariLibur').value = data.hariLibur.vcTipeHariLibur || '';
                    document.getElementById('dtTanggal').readOnly = true;
                    new bootstrap.Modal(document.getElementById('hariLiburModal')).show();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Terjadi kesalahan saat memuat data');
            });
    }

    // Delete hari libur
    function deleteHariLibur(id) {
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            fetch(`/hari-libur/${id}`, {
                    method: 'POST', // Use POST for method spoofing
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        _method: 'DELETE'
                    })
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
</script>
@endpush