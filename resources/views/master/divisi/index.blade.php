@extends('layouts.app')

@section('title', 'Master Divisi - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-sitemap me-2"></i>Master Divisi
                </h2>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addDivisiModal">
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
                        <table class="table table-hover mb-0" id="divisiTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="15%">Kode Divisi</th>
                                    <th width="30%">Nama Divisi</th>
                                    <th width="8%" class="text-center">Sen</th>
                                    <th width="8%" class="text-center">Sel</th>
                                    <th width="8%" class="text-center">Rab</th>
                                    <th width="8%" class="text-center">Kam</th>
                                    <th width="8%" class="text-center">Jum</th>
                                    <th width="8%" class="text-center">Sab</th>
                                    <th width="8%" class="text-center">Mgg</th>
                                    <th width="20%">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($divisis as $index => $divisi)
                                <tr data-id="{{ $divisi->vcKodeDivisi }}" class="table-row">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $divisi->vcKodeDivisi }}</td>
                                    <td>{{ $divisi->vcNamaDivisi }}</td>
                                    <td class="text-center">
                                        <i class="fas fa-check text-success {{ $divisi->vcSenin ? '' : 'd-none' }}"></i>
                                        <i class="fas fa-times text-danger {{ $divisi->vcSenin ? 'd-none' : '' }}"></i>
                                    </td>
                                    <td class="text-center">
                                        <i class="fas fa-check text-success {{ $divisi->vcSelasa ? '' : 'd-none' }}"></i>
                                        <i class="fas fa-times text-danger {{ $divisi->vcSelasa ? 'd-none' : '' }}"></i>
                                    </td>
                                    <td class="text-center">
                                        <i class="fas fa-check text-success {{ $divisi->vcRabu ? '' : 'd-none' }}"></i>
                                        <i class="fas fa-times text-danger {{ $divisi->vcRabu ? 'd-none' : '' }}"></i>
                                    </td>
                                    <td class="text-center">
                                        <i class="fas fa-check text-success {{ $divisi->vcKamis ? '' : 'd-none' }}"></i>
                                        <i class="fas fa-times text-danger {{ $divisi->vcKamis ? 'd-none' : '' }}"></i>
                                    </td>
                                    <td class="text-center">
                                        <i class="fas fa-check text-success {{ $divisi->vcJumat ? '' : 'd-none' }}"></i>
                                        <i class="fas fa-times text-danger {{ $divisi->vcJumat ? 'd-none' : '' }}"></i>
                                    </td>
                                    <td class="text-center">
                                        <i class="fas fa-check text-success {{ $divisi->vcSabtu ? '' : 'd-none' }}"></i>
                                        <i class="fas fa-times text-danger {{ $divisi->vcSabtu ? 'd-none' : '' }}"></i>
                                    </td>
                                    <td class="text-center">
                                        <i class="fas fa-check text-success {{ $divisi->vcMinggu ? '' : 'd-none' }}"></i>
                                        <i class="fas fa-times text-danger {{ $divisi->vcMinggu ? 'd-none' : '' }}"></i>
                                    </td>
                                    <td>{{ $divisi->vcKeterangan ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Tidak ada data divisi</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">{{ $divisis->count() }} Data.</span>
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

<!-- Modal Tambah Divisi -->
<div class="modal fade" id="addDivisiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Tambah Divisi Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addDivisiForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="kode_divisi" class="form-label">Kode Divisi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kode_divisi" name="vcKodeDivisi" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama_divisi" class="form-label">Nama Divisi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_divisi" name="vcNamaDivisi" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hari Kerja</label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sen" name="vcSenin" checked>
                                    <label class="form-check-label" for="sen">Senin</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sel" name="vcSelasa" checked>
                                    <label class="form-check-label" for="sel">Selasa</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="rab" name="vcRabu" checked>
                                    <label class="form-check-label" for="rab">Rabu</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="kam" name="vcKamis" checked>
                                    <label class="form-check-label" for="kam">Kamis</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="jum" name="vcJumat" checked>
                                    <label class="form-check-label" for="jum">Jumat</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sab" name="vcSabtu">
                                    <label class="form-check-label" for="sab">Sabtu</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="mgg" name="vcMinggu">
                                    <label class="form-check-label" for="mgg">Minggu</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="vcKeterangan" rows="3"></textarea>
                    </div>

                    <hr>
                    <h6 class="mb-3">Tanda Tangan Laporan</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vcStaff" class="form-label">Staff Payroll</label>
                                <input type="text" class="form-control" id="vcStaff" name="vcStaff" placeholder="Nama Staff Payroll">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vcKeuangan" class="form-label">Kabag HR/Keuangan</label>
                                <input type="text" class="form-control" id="vcKeuangan" name="vcKeuangan" placeholder="Nama Kabag HR/Keuangan">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vcKabag" class="form-label">Manager / Ka. Dept</label>
                                <input type="text" class="form-control" id="vcKabag" name="vcKabag" placeholder="Nama Manager / Ka. Dept">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vPPIC" class="form-label">Direktur / General Manager</label>
                                <input type="text" class="form-control" id="vPPIC" name="vPPIC" placeholder="Nama Direktur / General Manager">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vcPlantManager" class="form-label">General Manager / Direktur</label>
                                <input type="text" class="form-control" id="vcPlantManager" name="vcPlantManager" placeholder="Nama General Manager / Direktur">
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

<!-- Modal Edit Divisi -->
<div class="modal fade" id="editDivisiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Edit Divisi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editDivisiForm">
                <input type="hidden" id="edit_kode_divisi" name="vcKodeDivisi">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_nama_divisi" class="form-label">Nama Divisi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_nama_divisi" name="vcNamaDivisi" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hari Kerja</label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_sen" name="vcSenin">
                                    <label class="form-check-label" for="edit_sen">Senin</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_sel" name="vcSelasa">
                                    <label class="form-check-label" for="edit_sel">Selasa</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_rab" name="vcRabu">
                                    <label class="form-check-label" for="edit_rab">Rabu</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_kam" name="vcKamis">
                                    <label class="form-check-label" for="edit_kam">Kamis</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_jum" name="vcJumat">
                                    <label class="form-check-label" for="edit_jum">Jumat</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_sab" name="vcSabtu">
                                    <label class="form-check-label" for="edit_sab">Sabtu</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_mgg" name="vcMinggu">
                                    <label class="form-check-label" for="edit_mgg">Minggu</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="edit_keterangan" name="vcKeterangan" rows="3"></textarea>
                    </div>

                    <hr>
                    <h6 class="mb-3">Tanda Tangan Laporan</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_vcStaff" class="form-label">Staff Payroll</label>
                                <input type="text" class="form-control" id="edit_vcStaff" name="vcStaff" placeholder="Nama Staff Payroll">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_vcKeuangan" class="form-label">Kabag HR/Keuangan</label>
                                <input type="text" class="form-control" id="edit_vcKeuangan" name="vcKeuangan" placeholder="Nama Kabag HR/Keuangan">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_vcKabag" class="form-label">Manager / Ka. Dept</label>
                                <input type="text" class="form-control" id="edit_vcKabag" name="vcKabag" placeholder="Nama Manager / Ka. Dept">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_vPPIC" class="form-label">Direktur / General Manager</label>
                                <input type="text" class="form-control" id="edit_vPPIC" name="vPPIC" placeholder="Nama Direktur / General Manager">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_vcPlantManager" class="form-label">General Manager / Direktur</label>
                                <input type="text" class="form-control" id="edit_vcPlantManager" name="vcPlantManager" placeholder="Nama General Manager / Direktur">
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
        let selectedDivisiId = null;

        // Row selection
        document.querySelectorAll('.table-row').forEach(row => {
            row.addEventListener('click', function() {
                // Remove previous selection
                document.querySelectorAll('.table-row').forEach(r => r.classList.remove('table-primary'));

                // Add selection to current row
                this.classList.add('table-primary');
                selectedRow = this;
                selectedDivisiId = this.dataset.id;

                // Enable edit and delete buttons
                document.getElementById('editBtn').disabled = false;
                document.getElementById('deleteBtn').disabled = false;
            });
        });

        // Edit button
        document.getElementById('editBtn').addEventListener('click', function() {
            if (selectedDivisiId) {
                // Fetch data divisi dari server
                fetch(`/divisi/${selectedDivisiId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Set form values
                        document.getElementById('edit_kode_divisi').value = data.vcKodeDivisi;
                        document.getElementById('edit_nama_divisi').value = data.vcNamaDivisi || '';
                        document.getElementById('edit_keterangan').value = data.vcKeterangan || '';

                        // Set checkboxes
                        document.getElementById('edit_sen').checked = data.vcSenin == 1;
                        document.getElementById('edit_sel').checked = data.vcSelasa == 1;
                        document.getElementById('edit_rab').checked = data.vcRabu == 1;
                        document.getElementById('edit_kam').checked = data.vcKamis == 1;
                        document.getElementById('edit_jum').checked = data.vcJumat == 1;
                        document.getElementById('edit_sab').checked = data.vcSabtu == 1;
                        document.getElementById('edit_mgg').checked = data.vcMinggu == 1;

                        // Set field tanda tangan
                        document.getElementById('edit_vcStaff').value = data.vcStaff || '';
                        document.getElementById('edit_vcKeuangan').value = data.vcKeuangan || '';
                        document.getElementById('edit_vcKabag').value = data.vcKabag || '';
                        document.getElementById('edit_vPPIC').value = data.vPPIC || '';
                        document.getElementById('edit_vcPlantManager').value = data.vcPlantManager || '';

                        // Show modal
                        new bootstrap.Modal(document.getElementById('editDivisiModal')).show();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Gagal memuat data divisi');
                    });
            }
        });

        // Delete button
        document.getElementById('deleteBtn').addEventListener('click', function() {
            if (selectedDivisiId && confirm('Apakah Anda yakin ingin menghapus divisi ini?')) {
                // Implement delete functionality
                fetch(`/divisi/${selectedDivisiId}`, {
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
                            alert('Gagal menghapus data');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan');
                    });
            }
        });

        // Refresh button
        document.getElementById('refreshBtn').addEventListener('click', function() {
            location.reload();
        });

        // Add form submission
        document.getElementById('addDivisiForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            // Convert checkboxes to boolean
            const days = ['vcSenin', 'vcSelasa', 'vcRabu', 'vcKamis', 'vcJumat', 'vcSabtu', 'vcMinggu'];
            days.forEach(day => {
                data[day] = formData.has(day) ? 1 : 0;
            });

            fetch('/divisi', {
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
                        alert('Gagal menyimpan data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan');
                });
        });

        // Edit form submission
        document.getElementById('editDivisiForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            // Convert checkboxes to boolean
            const days = ['vcSenin', 'vcSelasa', 'vcRabu', 'vcKamis', 'vcJumat', 'vcSabtu', 'vcMinggu'];
            days.forEach(day => {
                data[day] = formData.has(day) ? 1 : 0;
            });

            fetch(`/divisi/${selectedDivisiId}`, {
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
                        alert('Gagal mengupdate data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan');
                });
        });
    });
</script>
@endpush