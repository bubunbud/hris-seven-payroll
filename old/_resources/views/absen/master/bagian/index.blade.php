@extends('layouts.app')

@section('title', 'Master Bagian - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-layer-group me-2"></i>Master Bagian
                </h2>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addBagianModal" id="addBtn">
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
            <!-- Filter Section -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('bagian.index') }}" id="filterForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="filter_divisi" class="form-label">Filter Berdasarkan Divisi</label>
                                <select class="form-select" id="filter_divisi" name="filter_divisi" onchange="document.getElementById('filterForm').submit();">
                                    <option value="">Semua Divisi</option>
                                    @foreach($divisis as $divisi)
                                    <option value="{{ $divisi->vcKodeDivisi }}" {{ $filterDivisi == $divisi->vcKodeDivisi ? 'selected' : '' }}>
                                        {{ $divisi->vcKodeDivisi }} - {{ $divisi->vcNamaDivisi }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-secondary w-100" onclick="window.location.href='{{ route('bagian.index') }}'">
                                    <i class="fas fa-redo me-1"></i>Reset Filter
                                </button>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Filter berdasarkan prefix kode bagian sesuai divisi
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="bagianTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="15%">Kode Bagian</th>
                                    <th width="30%">Nama Bagian</th>
                                    <th width="20%">Jabatan PIC</th>
                                    <th width="20%">PIC Bagian</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bagians as $index => $bagian)
                                <tr data-id="{{ $bagian->vcKodeBagian }}" data-jabatan="{{ $bagian->vcKodeJabatan ?? '' }}" class="table-row">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $bagian->vcKodeBagian }}</td>
                                    <td>{{ $bagian->vcNamaBagian }}</td>
                                    <td>
                                        @if($bagian->jabatan)
                                        <span class="badge bg-info">{{ $bagian->vcKodeJabatan }}</span> - {{ $bagian->jabatan->vcNamaJabatan }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $bagian->vcPICBagian ?? '-' }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary btn-sm edit-row" data-id="{{ $bagian->vcKodeBagian }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm delete-row" data-id="{{ $bagian->vcKodeBagian }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Tidak ada data bagian</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">{{ $bagians->count() }} Data.</span>
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

<!-- Modal Tambah Bagian -->
<div class="modal fade" id="addBagianModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Tambah Bagian Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addBagianForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="divisi_select" class="form-label">Bisnis Unit / Divisi <span class="text-danger">*</span></label>
                                <select class="form-select" id="divisi_select" name="divisi" required>
                                    <option value="">Pilih Divisi</option>
                                    @foreach($divisis as $divisi)
                                    <option value="{{ $divisi->vcKodeDivisi }}">{{ $divisi->vcKodeDivisi }} - {{ $divisi->vcNamaDivisi }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Pilih divisi untuk generate kode bagian otomatis</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="kode_bagian" class="form-label">Kode Bagian <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kode_bagian" name="vcKodeBagian" required maxlength="7" readonly>
                                <div class="form-text">Kode akan otomatis ter-generate setelah memilih Divisi</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama_bagian" class="form-label">Nama Bagian <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_bagian" name="vcNamaBagian" required maxlength="35">
                                <div class="form-text">Maksimal 35 karakter</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pic_bagian" class="form-label">PIC Bagian</label>
                                <input type="text" class="form-control" id="pic_bagian" name="vcPICBagian" maxlength="50">
                                <div class="form-text">Maksimal 50 karakter</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="kode_jabatan" class="form-label">Jabatan PIC</label>
                                <select class="form-select" id="kode_jabatan" name="vcKodeJabatan">
                                    <option value="">Pilih Jabatan</option>
                                    @foreach($jabatans as $jabatan)
                                    <option value="{{ $jabatan->vcKodeJabatan }}">{{ $jabatan->vcKodeJabatan }} - {{ $jabatan->vcNamaJabatan }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Jabatan Personal In Charge bagian ini</div>
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

<!-- Modal Edit Bagian -->
<div class="modal fade" id="editBagianModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Edit Bagian
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editBagianForm">
                <input type="hidden" id="edit_kode_bagian" name="vcKodeBagian">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_nama_bagian" class="form-label">Nama Bagian <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_nama_bagian" name="vcNamaBagian" required maxlength="35">
                                <div class="form-text">Maksimal 35 karakter</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_pic_bagian" class="form-label">PIC Bagian</label>
                                <input type="text" class="form-control" id="edit_pic_bagian" name="vcPICBagian" maxlength="50">
                                <div class="form-text">Maksimal 50 karakter</div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="edit_kode_jabatan" class="form-label">Jabatan PIC</label>
                                <select class="form-select" id="edit_kode_jabatan" name="vcKodeJabatan">
                                    <option value="">Pilih Jabatan</option>
                                    @foreach($jabatans as $jabatan)
                                    <option value="{{ $jabatan->vcKodeJabatan }}">{{ $jabatan->vcKodeJabatan }} - {{ $jabatan->vcNamaJabatan }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Jabatan Personal In Charge bagian ini</div>
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
        let selectedBagianId = null;

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
                selectedBagianId = this.dataset.id;

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
                selectedBagianId = id; // Set selectedBagianId
                editBagian(id);
            });
        });

        // Delete button in action column
        document.querySelectorAll('.delete-row').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const id = this.dataset.id;
                deleteBagian(id);
            });
        });

        // Main edit button
        document.getElementById('editBtn').addEventListener('click', function() {
            if (selectedBagianId) {
                editBagian(selectedBagianId);
            }
        });

        // Main delete button
        document.getElementById('deleteBtn').addEventListener('click', function() {
            if (selectedBagianId && confirm('Apakah Anda yakin ingin menghapus bagian ini?')) {
                deleteBagian(selectedBagianId);
            }
        });

        // Refresh button
        document.getElementById('refreshBtn').addEventListener('click', function() {
            location.reload();
        });

        // Auto-fill PIC Bagian berdasarkan Jabatan PIC (Form Tambah)
        const kodeJabatanSelect = document.getElementById('kode_jabatan');
        const kodeBagianInput = document.getElementById('kode_bagian');
        const picBagianInput = document.getElementById('pic_bagian');

        // Function untuk trigger load karyawan jika kondisi terpenuhi
        function triggerLoadKaryawan() {
            if (kodeJabatanSelect && kodeBagianInput && picBagianInput) {
                const kodeJabatan = kodeJabatanSelect.value;
                const kodeBagian = kodeBagianInput.value.trim();
                if (kodeJabatan && kodeBagian) {
                    loadKaryawanByJabatan(kodeJabatan, kodeBagian, picBagianInput);
                } else {
                    picBagianInput.value = '';
                }
            }
        }

        if (kodeJabatanSelect && kodeBagianInput && picBagianInput) {
            kodeJabatanSelect.addEventListener('change', function() {
                triggerLoadKaryawan();
            });
        }

        // Auto-fill PIC Bagian berdasarkan Jabatan PIC (Form Edit)
        const editKodeJabatanSelect = document.getElementById('edit_kode_jabatan');
        const editKodeBagianInput = document.getElementById('edit_kode_bagian');
        const editPicBagianInput = document.getElementById('edit_pic_bagian');

        if (editKodeJabatanSelect && editKodeBagianInput && editPicBagianInput) {
            editKodeJabatanSelect.addEventListener('change', function() {
                const kodeJabatan = this.value;
                const kodeBagian = editKodeBagianInput.value.trim();
                if (kodeJabatan && kodeBagian) {
                    // Simpan nilai PIC yang sudah ada sebelum load
                    const currentPic = editPicBagianInput.value;
                    loadKaryawanByJabatan(kodeJabatan, kodeBagian, editPicBagianInput, currentPic);
                } else {
                    // Jika jabatan dikosongkan, kosongkan juga PIC
                    editPicBagianInput.value = '';
                }
            });
        }

        // Function untuk load karyawan berdasarkan jabatan dan kode bagian
        function loadKaryawanByJabatan(kodeJabatan, kodeBagian, targetInput, preserveValue = null) {
            if (!kodeJabatan || !kodeBagian || !targetInput) return;

            fetch('/bagian/get-karyawan-by-jabatan', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        jabatan: kodeJabatan,
                        kode_bagian: kodeBagian
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.picBagian) {
                        targetInput.value = data.picBagian;
                    } else {
                        // Jika tidak ditemukan, kosongkan atau preserve nilai yang ada
                        if (preserveValue !== null) {
                            // Saat edit, jika tidak ditemukan, biarkan nilai yang sudah ada
                            targetInput.value = preserveValue;
                        } else {
                            // Saat tambah, kosongkan jika tidak ditemukan
                            targetInput.value = '';
                        }
                        if (data.message) {
                            console.warn(data.message);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading karyawan:', error);
                    // Preserve nilai jika ada
                    if (preserveValue !== null) {
                        targetInput.value = preserveValue;
                    } else {
                        targetInput.value = '';
                    }
                });
        }

        // Reset form saat modal tambah dibuka
        const addModal = document.getElementById('addBagianModal');
        if (addModal) {
            addModal.addEventListener('show.bs.modal', function() {
                // Reset form
                document.getElementById('addBagianForm').reset();
                document.getElementById('pic_bagian').value = '';
                document.getElementById('kode_jabatan').value = '';
                const divisiSelect = document.getElementById('divisi_select');
                const kodeBagianInput = document.getElementById('kode_bagian');
                if (divisiSelect) divisiSelect.value = '';
                if (kodeBagianInput) {
                    kodeBagianInput.value = '';
                    kodeBagianInput.readOnly = true;
                }
            });
        }

        // Auto-generate kode bagian saat pilih divisi
        const divisiSelect = document.getElementById('divisi_select');
        const kodeBagianInputForGenerate = document.getElementById('kode_bagian');
        
        if (divisiSelect && kodeBagianInputForGenerate) {
            divisiSelect.addEventListener('change', function() {
                const kodeDivisi = this.value;
                
                if (!kodeDivisi) {
                    kodeBagianInputForGenerate.value = '';
                    kodeBagianInputForGenerate.readOnly = true;
                    return;
                }

                // Fetch kode bagian otomatis dari server
                fetch('/bagian/generate-kode', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            divisi: kodeDivisi
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            kodeBagianInputForGenerate.value = data.kodeBagian;
                            kodeBagianInputForGenerate.readOnly = true; // Set readonly setelah di-generate
                            
                            // Trigger load karyawan jika jabatan sudah dipilih
                            setTimeout(() => {
                                triggerLoadKaryawan();
                            }, 100);
                        } else {
                            alert('Gagal generate kode bagian: ' + (data.message || 'Unknown error'));
                            kodeBagianInputForGenerate.value = '';
                            kodeBagianInputForGenerate.readOnly = true;
                        }
                    })
                    .catch(error => {
                        console.error('Error generating kode bagian:', error);
                        alert('Terjadi kesalahan saat generate kode bagian');
                        kodeBagianInputForGenerate.value = '';
                        kodeBagianInputForGenerate.readOnly = true;
                    });
            });
        }

        // Add form submission
        document.getElementById('addBagianForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            // Hapus field divisi dari formData karena hanya untuk generate kode, tidak disimpan
            formData.delete('divisi');
            const data = Object.fromEntries(formData);

            fetch('/bagian', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(async response => {
                    // Cek content type
                    const contentType = response.headers.get('content-type');
                    let result;
                    
                    if (contentType && contentType.includes('application/json')) {
                        result = await response.json();
                    } else {
                        const text = await response.text();
                        try {
                            result = JSON.parse(text);
                        } catch (e) {
                            // Jika bukan JSON dan response OK, anggap sukses
                            if (response.ok) {
                                result = { success: true, message: 'Bagian berhasil ditambahkan.' };
                            } else {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                        }
                    }
                    
                    if (!response.ok) {
                        // Handle validation errors
                        if (result.errors) {
                            let errorMessages = [];
                            for (let field in result.errors) {
                                errorMessages.push(result.errors[field].join(', '));
                            }
                            throw new Error('Validasi gagal:\n' + errorMessages.join('\n'));
                        }
                        throw new Error(result.message || `HTTP error! status: ${response.status}`);
                    }
                    
                    return result;
                })
                .then(data => {
                    if (data.success) {
                        alert('Data berhasil disimpan');
                        location.reload();
                    } else {
                        alert('Gagal menyimpan data: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan: ' + error.message);
                });
        });

        // Edit form submission
        document.getElementById('editBagianForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Pastikan selectedBagianId ter-set dari hidden input
            const kodeBagian = document.getElementById('edit_kode_bagian').value.trim();
            if (!kodeBagian) {
                alert('Kode Bagian tidak ditemukan');
                return;
            }

            // Debug: log kode bagian
            console.log('Updating bagian dengan kode:', kodeBagian);

            // Gunakan method spoofing untuk PUT
            const formData = new FormData(this);
            formData.append('_method', 'PUT');

            // Debug: log form data
            console.log('Form data:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            // Encode kode bagian untuk URL
            const encodedKodeBagian = encodeURIComponent(kodeBagian);
            console.log('URL:', `/bagian/${encodedKodeBagian}`);

            fetch(`/bagian/${encodedKodeBagian}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(async response => {
                    const contentType = response.headers.get('content-type');
                    let data;

                    if (contentType && contentType.includes('application/json')) {
                        data = await response.json();
                    } else {
                        const text = await response.text();
                        try {
                            data = JSON.parse(text);
                        } catch (e) {
                            if (response.ok) {
                                data = {
                                    success: true,
                                    message: 'Data berhasil disimpan'
                                };
                            } else {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                        }
                    }

                    if (!response.ok) {
                        // Jika ada error validasi, tampilkan detail error
                        if (data.errors) {
                            let errorMessages = [];
                            for (let field in data.errors) {
                                errorMessages.push(data.errors[field].join(', '));
                            }
                            throw new Error('Validasi gagal:\n' + errorMessages.join('\n'));
                        }
                        throw new Error(data.message || `HTTP error! status: ${response.status}`);
                    }

                    return data;
                })
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Gagal mengupdate data: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    console.error('Error details:', error.message);
                    alert('Terjadi kesalahan: ' + error.message);
                });
        });

        function editBagian(id) {
            // Pastikan selectedBagianId ter-set
            selectedBagianId = id;

            // Get data from selected row
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (!row) {
                alert('Data tidak ditemukan');
                return;
            }

            const cells = row.cells;

            const kodeBagian = cells[1].textContent.trim();
            const namaBagian = cells[2].textContent.trim();
            // cells[3] sekarang adalah Jabatan PIC, cells[4] adalah PIC Bagian
            const picBagian = cells[4].textContent === '-' ? '' : cells[4].textContent.trim();

            // Ambil kode jabatan dari data attribute
            const kodeJabatan = row.dataset.jabatan || '';

            // Set form values
            document.getElementById('edit_kode_bagian').value = kodeBagian;
            document.getElementById('edit_nama_bagian').value = namaBagian;
            document.getElementById('edit_pic_bagian').value = picBagian;
            document.getElementById('edit_kode_jabatan').value = kodeJabatan;

            // Jika ada kode jabatan dan kode bagian, coba load karyawan
            if (kodeJabatan && kodeBagian && editPicBagianInput) {
                // Tunggu sebentar untuk memastikan select sudah ter-set
                setTimeout(() => {
                    loadKaryawanByJabatan(kodeJabatan, kodeBagian, editPicBagianInput);
                }, 100);
            }

            // Show modal
            new bootstrap.Modal(document.getElementById('editBagianModal')).show();
        }

        function deleteBagian(id) {
            if (confirm('Apakah Anda yakin ingin menghapus bagian ini?')) {
                fetch(`/bagian/${id}`, {
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