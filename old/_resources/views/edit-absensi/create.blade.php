@extends('layouts.app')

@section('title', 'Input Absensi Baru - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-plus me-2"></i>Input Absensi Baru
                </h2>
                <a href="{{ route('edit-absensi.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>

            <!-- Alert Messages -->
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <!-- Form Input -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>Form Input Absensi Baru
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('edit-absensi.store') }}" id="createForm">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="tanggal" class="form-label">
                                    <i class="fas fa-calendar text-primary me-1"></i>Tanggal <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control @error('tanggal') is-invalid @enderror" 
                                       id="tanggal" 
                                       name="tanggal"
                                       value="{{ old('tanggal', date('Y-m-d')) }}"
                                       required>
                                @error('tanggal')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="nik" class="form-label">
                                    <i class="fas fa-id-card text-info me-1"></i>NIK <span class="text-danger">*</span>
                                </label>
                                <div class="position-relative">
                                    <input type="text" 
                                           class="form-control @error('nik') is-invalid @enderror" 
                                           id="nik" 
                                           value="{{ old('nik') }}"
                                           placeholder="Cari NIK atau Nama Karyawan..."
                                           autocomplete="off"
                                           required>
                                    <input type="hidden" id="nik_hidden" name="nik" value="{{ old('nik') }}">
                                    <div id="nikAutocomplete" class="autocomplete-dropdown" style="display: none;"></div>
                                </div>
                                @error('nik')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Ketik NIK atau nama karyawan untuk mencari</small>
                            </div>

                            <div class="col-md-12">
                                <div class="alert alert-info" id="karyawanInfo" style="display: none;">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Informasi Karyawan:</strong>
                                    <span id="infoNama"></span>
                                    <span id="infoDivisi"></span>
                                    <span id="infoBagian"></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="jam_masuk" class="form-label">
                                    <i class="fas fa-sign-in-alt text-success me-1"></i>Jam Masuk
                                </label>
                                <input type="time" 
                                       class="form-control @error('jam_masuk') is-invalid @enderror" 
                                       id="jam_masuk" 
                                       name="jam_masuk"
                                       value="{{ old('jam_masuk') }}">
                                @error('jam_masuk')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Format: HH:MM (contoh: 08:00)</small>
                            </div>

                            <div class="col-md-6">
                                <label for="jam_keluar" class="form-label">
                                    <i class="fas fa-sign-out-alt text-danger me-1"></i>Jam Keluar
                                </label>
                                <input type="time" 
                                       class="form-control @error('jam_keluar') is-invalid @enderror" 
                                       id="jam_keluar" 
                                       name="jam_keluar"
                                       value="{{ old('jam_keluar') }}">
                                @error('jam_keluar')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Format: HH:MM (contoh: 17:00)</small>
                            </div>

                            <div class="col-md-6">
                                <label for="jam_masuk_lembur" class="form-label">
                                    <i class="fas fa-moon text-info me-1"></i>Jam Masuk Lembur
                                </label>
                                <input type="time" 
                                       class="form-control @error('jam_masuk_lembur') is-invalid @enderror" 
                                       id="jam_masuk_lembur" 
                                       name="jam_masuk_lembur"
                                       value="{{ old('jam_masuk_lembur') }}">
                                @error('jam_masuk_lembur')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Format: HH:MM (opsional)</small>
                            </div>

                            <div class="col-md-6">
                                <label for="jam_keluar_lembur" class="form-label">
                                    <i class="fas fa-moon text-info me-1"></i>Jam Keluar Lembur
                                </label>
                                <input type="time" 
                                       class="form-control @error('jam_keluar_lembur') is-invalid @enderror" 
                                       id="jam_keluar_lembur" 
                                       name="jam_keluar_lembur"
                                       value="{{ old('jam_keluar_lembur') }}">
                                @error('jam_keluar_lembur')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Format: HH:MM (opsional)</small>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Perhatian:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Jam masuk dan jam keluar harus diisi bersamaan atau dikosongkan bersamaan.</li>
                                        <li>Pastikan data yang diinput sudah benar sebelum disimpan.</li>
                                        <li>Data absensi untuk tanggal dan NIK yang sama tidak boleh duplikat.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-2"></i>Simpan Data Absensi
                                </button>
                                <a href="{{ route('edit-absensi.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Batal
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .autocomplete-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        max-height: 300px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        margin-top: 2px;
    }
    .autocomplete-item {
        padding: 0.75rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s;
    }
    .autocomplete-item:hover,
    .autocomplete-item.active {
        background-color: #f8f9fa;
    }
    .autocomplete-item:last-child {
        border-bottom: none;
    }
    .autocomplete-item strong {
        color: #0d6efd;
    }
    .autocomplete-item small {
        color: #6c757d;
        display: block;
        margin-top: 0.25rem;
    }
</style>
@endpush

@push('scripts')
<script>
    let searchTimeout;
    let selectedKaryawan = null;
    const nikInput = document.getElementById('nik');
    const nikHidden = document.getElementById('nik_hidden');
    const autocompleteDiv = document.getElementById('nikAutocomplete');
    const infoDiv = document.getElementById('karyawanInfo');
    const infoNama = document.getElementById('infoNama');
    const infoDivisi = document.getElementById('infoDivisi');
    const infoBagian = document.getElementById('infoBagian');
    // Data karyawan untuk pencarian lokal (dibatasi di controller)
    const karyawanList = @json($karyawanList);

    // Autocomplete search (pencarian lokal, tanpa fetch)
    nikInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();

        clearTimeout(searchTimeout);

        if (query.length === 0) {
            autocompleteDiv.style.display = 'none';
            selectedKaryawan = null;
            nikHidden.value = '';
            updateKaryawanInfo(null);
            return;
        }

        if (query.length < 2) {
            autocompleteDiv.style.display = 'none';
            return;
        }

        // Debounce 200ms
        searchTimeout = setTimeout(() => {
            const results = karyawanList.filter(k => k.search.includes(query)).slice(0, 20);
            displayAutocomplete(results);
        }, 200);
    });

    // Display autocomplete results
    function displayAutocomplete(karyawans) {
        if (!karyawans || karyawans.length === 0) {
            autocompleteDiv.innerHTML = '<div class="autocomplete-item">Tidak ada karyawan ditemukan</div>';
            autocompleteDiv.style.display = 'block';
            return;
        }

        autocompleteDiv.innerHTML = '';
        karyawans.forEach((karyawan, index) => {
            if (!karyawan || !karyawan.nik) return; // Skip invalid data
            
            const item = document.createElement('div');
            item.className = 'autocomplete-item';
            item.innerHTML = `
                <strong>${karyawan.nik || ''}</strong> - ${karyawan.nama || ''}
                <small>Divisi: ${karyawan.divisi || '-'} | Bagian: ${karyawan.bagian || '-'}</small>
            `;
            item.addEventListener('click', function() {
                selectKaryawan(karyawan);
            });
            autocompleteDiv.appendChild(item);
        });
        autocompleteDiv.style.display = 'block';
    }

    // Select karyawan from autocomplete
    function selectKaryawan(karyawan) {
        selectedKaryawan = karyawan;
        nikInput.value = `${karyawan.nik} - ${karyawan.nama}`;
        nikHidden.value = karyawan.nik;
        autocompleteDiv.style.display = 'none';
        updateKaryawanInfo(karyawan);
    }

    // Update karyawan info display
    function updateKaryawanInfo(karyawan) {
        if (karyawan) {
            infoNama.textContent = `Nama: ${karyawan.nama}`;
            infoDivisi.textContent = ` | Divisi: ${karyawan.divisi}`;
            infoBagian.textContent = ` | Bagian: ${karyawan.bagian}`;
            infoDiv.style.display = 'block';
        } else {
            infoDiv.style.display = 'none';
        }
    }

    // Hide autocomplete when clicking outside
    document.addEventListener('click', function(e) {
        if (!nikInput.contains(e.target) && !autocompleteDiv.contains(e.target)) {
            autocompleteDiv.style.display = 'none';
        }
    });

    // Handle keyboard navigation
    let selectedIndex = -1;
    nikInput.addEventListener('keydown', function(e) {
        const items = autocompleteDiv.querySelectorAll('.autocomplete-item');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
            updateSelectedItem(items, selectedIndex);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, -1);
            updateSelectedItem(items, selectedIndex);
        } else if (e.key === 'Enter' && selectedIndex >= 0 && items[selectedIndex]) {
            e.preventDefault();
            items[selectedIndex].click();
        } else if (e.key === 'Escape') {
            autocompleteDiv.style.display = 'none';
            selectedIndex = -1;
        }
    });

    function updateSelectedItem(items, index) {
        items.forEach((item, i) => {
            if (i === index) {
                item.classList.add('active');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('active');
            }
        });
    }

    // Validate NIK on blur (cek ke data lokal)
    nikInput.addEventListener('blur', function() {
        setTimeout(() => {
            if (selectedKaryawan) return;
            const nikValue = nikInput.value.trim().split(' - ')[0];
            if (!nikValue) return;
            const found = karyawanList.find(k => k.nik === nikValue);
            if (found) {
                selectKaryawan(found);
            } else {
                nikInput.value = '';
                nikHidden.value = '';
                updateKaryawanInfo(null);
                alert('NIK tidak ditemukan. Silakan pilih dari hasil pencarian.');
            }
        }, 200);
    });

    // Validasi form sebelum submit
    document.getElementById('createForm').addEventListener('submit', function(e) {
        // Ensure hidden NIK is set
        if (!nikHidden.value && selectedKaryawan) {
            nikHidden.value = selectedKaryawan.nik;
        }

        // Validate NIK is selected
        if (!nikHidden.value) {
            e.preventDefault();
            alert('Silakan pilih karyawan dari hasil pencarian.');
            return false;
        }

        const jamMasuk = document.getElementById('jam_masuk').value;
        const jamKeluar = document.getElementById('jam_keluar').value;

        // Validasi: jika ada jam masuk atau keluar, pastikan keduanya diisi
        if ((jamMasuk && !jamKeluar) || (!jamMasuk && jamKeluar)) {
            e.preventDefault();
            alert('Jam masuk dan jam keluar harus diisi bersamaan atau dikosongkan bersamaan.');
            return false;
        }

        // Konfirmasi sebelum submit
        if (!confirm('Apakah Anda yakin ingin menyimpan data absensi baru ini?')) {
            e.preventDefault();
            return false;
        }
    });
</script>
@endpush

