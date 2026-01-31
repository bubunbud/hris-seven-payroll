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

            <!-- Filter & Search Section -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('jabatan.index') }}" id="filterForm">
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
                            <div class="col-md-4">
                                <label for="search_nama" class="form-label">Cari Nama Jabatan</label>
                                <div class="position-relative">
                                    <input type="text"
                                           class="form-control"
                                           id="search_nama"
                                           name="search_nama"
                                           value="{{ $searchNama ?? '' }}"
                                           placeholder="Ketik sebagian nama jabatan...">
                                    <!-- Dropdown autocomplete -->
                                    <div id="jabatan-autocomplete"
                                         class="list-group position-absolute w-100"
                                         style="z-index: 1000; max-height: 220px; overflow-y: auto; display: none;">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i>Cari
                                </button>
                                <button type="button" class="btn btn-secondary w-100" onclick="window.location.href='{{ route('jabatan.index') }}'">
                                    <i class="fas fa-redo me-1"></i>Reset
                                </button>
                            </div>
                            <div class="col-md-2">
                                <div class="text-muted small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Filter per Divisi & cari Nama Jabatan dengan autocomplete.
                                </div>
                            </div>
                        </div>
                    </form>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="jabatanModalLabel">Tambah Jabatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="jabatanForm">
                <input type="hidden" name="_method" id="_method" value="POST">
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
                                <div class="form-text">Pilih divisi untuk generate kode jabatan otomatis</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vcKodeJabatan" class="form-label">Kode Jabatan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="vcKodeJabatan" name="vcKodeJabatan" maxlength="7" required readonly>
                                <div class="form-text">Kode akan otomatis ter-generate setelah memilih Divisi</div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vcNamaJabatan" class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="vcNamaJabatan" name="vcNamaJabatan" maxlength="50" required>
                                <div class="form-text">Maksimal 50 karakter</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vcGrade" class="form-label">Grade</label>
                                <input type="text" class="form-control" id="vcGrade" name="vcGrade" maxlength="25">
                                <div class="form-text">Maksimal 25 karakter (opsional)</div>
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
    // Data nama jabatan untuk autocomplete (diisi dari backend)
    const jabatanNames = @json($jabatanNames ?? []);

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

    // Form submission (Tambah & Edit)
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
        
        // Hapus field divisi dari formData karena hanya untuk generate kode, tidak disimpan
        formData.delete('divisi');

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
        const divisiSelect = document.getElementById('divisi_select');
        const kodeJabatanInput = document.getElementById('vcKodeJabatan');
        if (divisiSelect) {
            divisiSelect.value = '';
            // Pastikan required aktif lagi saat mode tambah
            divisiSelect.setAttribute('required', 'required');
            // Tampilkan dropdown divisi saat tambah
            const divisiLabel = divisiSelect.closest('.mb-3');
            if (divisiLabel) {
                divisiLabel.style.display = 'block';
            }
        }
        if (kodeJabatanInput) {
            kodeJabatanInput.value = '';
            kodeJabatanInput.readOnly = true;
        }
        new bootstrap.Modal(document.getElementById('jabatanModal')).show();
    });

    // Auto-generate kode jabatan saat pilih divisi
    const divisiSelect = document.getElementById('divisi_select');
    const kodeJabatanInput = document.getElementById('vcKodeJabatan');
    
    if (divisiSelect && kodeJabatanInput) {
        divisiSelect.addEventListener('change', function() {
            // Hanya generate jika bukan edit mode
            if (isEditMode) return;
            
            const kodeDivisi = this.value;
            
            if (!kodeDivisi) {
                kodeJabatanInput.value = '';
                kodeJabatanInput.readOnly = true;
                return;
            }

            // Fetch kode jabatan otomatis dari server
            fetch('/jabatan/generate-kode', {
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
                        kodeJabatanInput.value = data.kodeJabatan;
                        kodeJabatanInput.readOnly = true; // Set readonly setelah di-generate
                    } else {
                        alert('Gagal generate kode jabatan: ' + (data.message || 'Unknown error'));
                        kodeJabatanInput.value = '';
                        kodeJabatanInput.readOnly = true;
                    }
                })
                .catch(error => {
                    console.error('Error generating kode jabatan:', error);
                    alert('Terjadi kesalahan saat generate kode jabatan');
                    kodeJabatanInput.value = '';
                    kodeJabatanInput.readOnly = true;
                });
        });
    }

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
                    
                    // Sembunyikan dropdown divisi saat edit dan hilangkan required agar tidak menghambat submit
                    const divisiSelect = document.getElementById('divisi_select');
                    const divisiLabel = divisiSelect?.closest('.mb-3');
                    if (divisiSelect) {
                        divisiSelect.removeAttribute('required');
                    }
                    if (divisiLabel) {
                        divisiLabel.style.display = 'none';
                    }
                    
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

    // === Autocomplete Nama Jabatan ===
    (function initJabatanAutocomplete() {
        const input = document.getElementById('search_nama');
        const dropdown = document.getElementById('jabatan-autocomplete');
        if (!input || !dropdown || !Array.isArray(jabatanNames)) return;

        let currentIndex = -1;

        function hideDropdown() {
            dropdown.style.display = 'none';
            dropdown.innerHTML = '';
            currentIndex = -1;
        }

        function showDropdown(items) {
            dropdown.innerHTML = '';
            if (!items.length) {
                hideDropdown();
                return;
            }
            items.forEach((name, idx) => {
                const a = document.createElement('button');
                a.type = 'button';
                a.className = 'list-group-item list-group-item-action';
                a.textContent = name;
                a.addEventListener('click', () => {
                    input.value = name;
                    hideDropdown();
                    // Setelah pilih dari autocomplete, langsung submit filter
                    document.getElementById('filterForm').submit();
                });
                dropdown.appendChild(a);
            });
            dropdown.style.display = 'block';
            currentIndex = -1;
        }

        input.addEventListener('input', function () {
            const term = this.value.toLowerCase().trim();
            if (!term) {
                hideDropdown();
                return;
            }
            const filtered = jabatanNames
                .filter(n => n && n.toLowerCase().includes(term))
                .slice(0, 20);
            showDropdown(filtered);
        });

        input.addEventListener('keydown', function (e) {
            const items = dropdown.querySelectorAll('.list-group-item');
            if (!items.length || dropdown.style.display === 'none') return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                currentIndex = (currentIndex + 1) % items.length;
                items.forEach((el, idx) => el.classList.toggle('active', idx === currentIndex));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                currentIndex = (currentIndex - 1 + items.length) % items.length;
                items.forEach((el, idx) => el.classList.toggle('active', idx === currentIndex));
            } else if (e.key === 'Enter') {
                if (currentIndex >= 0 && currentIndex < items.length) {
                    e.preventDefault();
                    const chosen = items[currentIndex].textContent;
                    input.value = chosen;
                    hideDropdown();
                    document.getElementById('filterForm').submit();
                }
            } else if (e.key === 'Escape') {
                hideDropdown();
            }
        });

        document.addEventListener('click', function (e) {
            if (!dropdown.contains(e.target) && e.target !== input) {
                hideDropdown();
            }
        });
    })();
</script>
@endpush