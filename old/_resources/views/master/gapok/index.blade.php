@extends('layouts.app')

@section('title', 'Master Gaji Pokok - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-money-bill-wave me-2"></i>Master Gaji Pokok
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
                        <table class="table table-hover" id="gapokTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No.</th>
                                    <th width="15%">Kode Golongan</th>
                                    <th width="12%">Upah</th>
                                    <th width="12%">Tunj. Keluarga</th>
                                    <th width="12%">Tunj. Masa Kerja</th>
                                    <th width="10%">Tunj. Jabatan 1</th>
                                    <th width="10%">Tunj. Jabatan 2</th>
                                    <th width="12%" class="text-end">Gaji Pokok</th>
                                    <th width="10%">Uang Makan</th>
                                    <th width="10%">Uang Transport</th>
                                    <th width="10%">Premi</th>
                                    <th width="15%">Keterangan</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($gapoks as $index => $gapok)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <i class="fas fa-tag text-primary me-1"></i>
                                        <strong>{{ $gapok->vcKodeGolongan }}</strong>
                                    </td>
                                    <td>
                                        <i class="fas fa-money-bill text-success me-1"></i>
                                        Rp {{ number_format($gapok->upah, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        @if($gapok->tunj_keluarga > 0)
                                        <i class="fas fa-users text-info me-1"></i>
                                        Rp {{ number_format($gapok->tunj_keluarga, 0, ',', '.') }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($gapok->tunj_masa_kerja > 0)
                                        <i class="fas fa-clock text-warning me-1"></i>
                                        Rp {{ number_format($gapok->tunj_masa_kerja, 0, ',', '.') }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($gapok->tunj_jabatan1 > 0)
                                        <i class="fas fa-briefcase text-primary me-1"></i>
                                        Rp {{ number_format($gapok->tunj_jabatan1, 0, ',', '.') }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($gapok->tunj_jabatan2 > 0)
                                        <i class="fas fa-briefcase text-secondary me-1"></i>
                                        Rp {{ number_format($gapok->tunj_jabatan2, 0, ',', '.') }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @php
                                            $gajiPokok = ($gapok->upah ?? 0) 
                                                + ($gapok->tunj_keluarga ?? 0)
                                                + ($gapok->tunj_masa_kerja ?? 0)
                                                + ($gapok->tunj_jabatan1 ?? 0)
                                                + ($gapok->tunj_jabatan2 ?? 0);
                                        @endphp
                                        <strong class="text-primary">
                                            <i class="fas fa-calculator me-1"></i>
                                            Rp {{ number_format($gajiPokok, 0, ',', '.') }}
                                        </strong>
                                    </td>
                                    <td>
                                        @if($gapok->uang_makan > 0)
                                        <i class="fas fa-utensils text-success me-1"></i>
                                        Rp {{ number_format($gapok->uang_makan, 0, ',', '.') }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($gapok->uang_transport > 0)
                                        <i class="fas fa-car text-info me-1"></i>
                                        Rp {{ number_format($gapok->uang_transport, 0, ',', '.') }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($gapok->premi > 0)
                                        <i class="fas fa-gift text-danger me-1"></i>
                                        Rp {{ number_format($gapok->premi, 0, ',', '.') }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($gapok->vcKeterangan)
                                        <i class="fas fa-info-circle text-info me-1"></i>
                                        {{ $gapok->vcKeterangan }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary" onclick="editGapok('{{ $gapok->vcKodeGolongan }}')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" onclick="deleteGapok('{{ $gapok->vcKodeGolongan }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="13" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Tidak ada data gaji pokok</p>
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

<!-- Modal Tambah/Edit Gaji Pokok -->
<div class="modal fade" id="gapokModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gapokModalLabel">Tambah Gaji Pokok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="gapokForm">
                <input type="hidden" name="_method" id="_method" value="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vcKodeGolongan" class="form-label">Kode Golongan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="vcKodeGolongan" name="vcKodeGolongan" maxlength="10" required>
                                <div class="form-text">Maksimal 10 karakter</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="upah" class="form-label">Upah <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="upah" name="upah" min="0" step="0.01" required>
                                <div class="form-text">Masukkan upah pokok</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tunj_keluarga" class="form-label">Tunjangan Keluarga</label>
                                <input type="number" class="form-control" id="tunj_keluarga" name="tunj_keluarga" min="0" step="0.01">
                                <div class="form-text">Opsional</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tunj_masa_kerja" class="form-label">Tunjangan Masa Kerja</label>
                                <input type="number" class="form-control" id="tunj_masa_kerja" name="tunj_masa_kerja" min="0" step="0.01">
                                <div class="form-text">Opsional</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tunj_jabatan1" class="form-label">Tunjangan Jabatan 1</label>
                                <input type="number" class="form-control" id="tunj_jabatan1" name="tunj_jabatan1" min="0" step="0.01">
                                <div class="form-text">Opsional</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tunj_jabatan2" class="form-label">Tunjangan Jabatan 2</label>
                                <input type="number" class="form-control" id="tunj_jabatan2" name="tunj_jabatan2" min="0" step="0.01">
                                <div class="form-text">Opsional</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="uang_makan" class="form-label">Uang Makan</label>
                                <input type="number" class="form-control" id="uang_makan" name="uang_makan" min="0" step="0.01">
                                <div class="form-text">Opsional</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="uang_transport" class="form-label">Uang Transport</label>
                                <input type="number" class="form-control" id="uang_transport" name="uang_transport" min="0" step="0.01">
                                <div class="form-text">Opsional</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="premi" class="form-label">Premi</label>
                                <input type="number" class="form-control" id="premi" name="premi" min="0" step="0.01">
                                <div class="form-text">Opsional</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vcKeterangan" class="form-label">Keterangan</label>
                                <input type="text" class="form-control" id="vcKeterangan" name="vcKeterangan" maxlength="50">
                                <div class="form-text">Maksimal 50 karakter</div>
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

    // Show alert function
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        document.querySelectorAll('.alert').forEach(alert => alert.remove());
        const container = document.querySelector('.container-fluid');
        container.insertAdjacentHTML('afterbegin', alertHtml);

        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 3000);
    }

    // Form submission
    document.getElementById('gapokForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const url = isEditMode ? `/gapok/${currentId}` : '/gapok';

        // Set method spoofing for PUT requests BEFORE creating FormData
        if (isEditMode) {
            document.getElementById('_method').value = 'PUT';
        } else {
            document.getElementById('_method').value = 'POST';
        }

        // Create FormData after setting the method
        const formData = new FormData(this);

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
                    bootstrap.Modal.getInstance(document.getElementById('gapokModal')).hide();
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
        document.getElementById('gapokModalLabel').textContent = 'Tambah Gaji Pokok';
        document.getElementById('gapokForm').reset();
        document.getElementById('_method').value = 'POST'; // Ensure method is set to POST
        document.getElementById('vcKodeGolongan').readOnly = false;
        new bootstrap.Modal(document.getElementById('gapokModal')).show();
    });

    // Edit gapok
    function editGapok(id) {
        fetch(`/gapok/${id}`, {
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
                    document.getElementById('gapokModalLabel').textContent = 'Edit Gaji Pokok';
                    document.getElementById('_method').value = 'PUT'; // Set method to PUT for edit
                    document.getElementById('vcKodeGolongan').value = data.gapok.vcKodeGolongan;
                    document.getElementById('upah').value = data.gapok.upah;
                    document.getElementById('tunj_keluarga').value = data.gapok.tunj_keluarga || '';
                    document.getElementById('tunj_masa_kerja').value = data.gapok.tunj_masa_kerja || '';
                    document.getElementById('tunj_jabatan1').value = data.gapok.tunj_jabatan1 || '';
                    document.getElementById('tunj_jabatan2').value = data.gapok.tunj_jabatan2 || '';
                    document.getElementById('uang_makan').value = data.gapok.uang_makan || '';
                    document.getElementById('uang_transport').value = data.gapok.uang_transport || '';
                    document.getElementById('premi').value = data.gapok.premi || '';
                    document.getElementById('vcKeterangan').value = data.gapok.vcKeterangan || '';
                    document.getElementById('vcKodeGolongan').readOnly = true;
                    new bootstrap.Modal(document.getElementById('gapokModal')).show();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Terjadi kesalahan saat memuat data');
            });
    }

    // Delete gapok
    function deleteGapok(id) {
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            fetch(`/gapok/${id}`, {
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

    // Auto uppercase kode golongan
    document.getElementById('vcKodeGolongan').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
</script>
@endpush


