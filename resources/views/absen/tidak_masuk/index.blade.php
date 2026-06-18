@extends('layouts.app')

@section('title', 'Izin Tidak Masuk - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-user-times me-2"></i>Izin Tidak Masuk
                </h2>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-success" id="addBtn">
                        <i class="fas fa-plus me-1"></i>Tambah
                    </button>
                </div>
            </div>

            <!-- Filter -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('tidak-masuk.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="dari_tanggal" class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" id="dari_tanggal" name="dari_tanggal" value="{{ $startDate }}">
                            </div>
                            <div class="col-md-3">
                                <label for="sampai_tanggal" class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" id="sampai_tanggal" name="sampai_tanggal" value="{{ $endDate }}">
                            </div>
                            <div class="col-md-3">
                                <label for="search" class="form-label">NIK / Nama</label>
                                <div class="position-relative">
                                    <input type="text"
                                        class="form-control"
                                        id="search"
                                        name="search"
                                        value="{{ $search ?? '' }}"
                                        placeholder="Cari NIK atau Nama (pisahkan dengan koma)"
                                        autocomplete="off">
                                    <div id="searchAutocomplete" class="autocomplete-dropdown" style="display: none;"></div>
                                </div>
                                <small class="text-muted">Ketik NIK atau nama karyawan untuk mencari (bisa multiple, pisahkan dengan koma)</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100 shadow-sm">
                                    <i class="fas fa-eye me-2"></i>Preview
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="max-height:600px; overflow-y:auto;">
                        <table class="table table-hover">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="18%">Periode</th>
                                    <th width="8%">Jml Hari</th>
                                    <th width="10%">NIK</th>
                                    <th width="20%">Nama</th>
                                    <th width="18%">Jenis Izin</th>
                                    <th width="18%">Keterangan</th>
                                    <th width="8%">Dibayar</th>
                                    <th width="8%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($records as $row)
                                <tr>
                                    <td>{{ $row->periode_text }}</td>
                                    <td>
                                        @if($row->jumlah_hari > 0)
                                        <span class="badge bg-info">{{ $row->jumlah_hari }}</span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td><strong>{{ $row->vcNik }}</strong></td>
                                    <td>{{ $row->karyawan->Nama ?? 'N/A' }}</td>
                                    <td>{{ $row->jenisAbsen->vcKeterangan ?? $row->vcKodeAbsen }}</td>
                                    <td>{{ $row->vcKeterangan }}</td>
                                    <td>
                                        @if(($row->vcDibayar ?? 'N') === 'Y')
                                        <span class="badge bg-success">Ya</span>
                                        @else
                                        <span class="badge bg-secondary">Tidak</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="editRecord('{{ $row->vcNik }}', '{{ $row->vcKodeAbsen }}', '{{ $row->dtTanggalMulai?->format('Y-m-d') }}', '{{ $row->dtTanggalSelesai?->format('Y-m-d') }}')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteRecord('{{ $row->vcNik }}', '{{ $row->vcKodeAbsen }}', '{{ $row->dtTanggalMulai?->format('Y-m-d') }}', '{{ $row->dtTanggalSelesai?->format('Y-m-d') }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Belum ada data</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($records->hasPages())
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
                        <div class="text-muted small">
                            Menampilkan {{ $records->firstItem() }} sampai {{ $records->lastItem() }} dari {{ $records->total() }} data
                        </div>
                        <nav aria-label="Navigasi halaman">
                            {{ $records->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="tmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tmModalLabel">Tambah Izin Tidak Masuk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="tmForm">
                <input type="hidden" name="_method" id="_method" value="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="vcNik" class="form-label">NIK / Nama <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="text" class="form-control" id="vcNik" maxlength="50" required autocomplete="off" placeholder="Ketik NIK atau Nama">
                            <input type="hidden" id="vcNikHidden" name="vcNik">
                            <div id="nikAutocomplete" class="autocomplete-dropdown" style="display: none;"></div>
                        </div>
                        <div class="form-text" id="namaPreview"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dtTanggalMulai" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dtTanggalMulai" name="dtTanggalMulai" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dtTanggalSelesai" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dtTanggalSelesai" name="dtTanggalSelesai" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="durasiHari" class="form-label">Durasi (Jumlah Hari)</label>
                        <input type="text" class="form-control bg-light" id="durasiHari" readonly>
                        <div class="form-text">Jumlah hari dihitung secara otomatis berdasarkan tanggal mulai dan tanggal selesai (inklusif)</div>
                    </div>
                    <div class="mb-3">
                        <label for="vcKodeAbsen" class="form-label">Jenis Izin <span class="text-danger">*</span></label>
                        <select class="form-select" id="vcKodeAbsen" name="vcKodeAbsen" required>
                            <option value="">Pilih Jenis Izin</option>
                            @foreach($jenisAbsens as $j)
                            <option value="{{ $j->vcKodeAbsen }}">{{ $j->vcKeterangan }} ({{ $j->vcKodeAbsen }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="vcKeterangan" class="form-label">Keterangan</label>
                        <input type="text" class="form-control" id="vcKeterangan" name="vcKeterangan" maxlength="100">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Y" id="vcDibayar" name="vcDibayar">
                        <label class="form-check-label" for="vcDibayar">
                            Dibayar
                        </label>
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
    let currentId = null; // base64(nik|kode|mulai|selesai)

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.querySelectorAll('.alert').forEach(a => a.remove());
        document.querySelector('.container-fluid').insertAdjacentHTML('afterbegin', alertHtml);
        setTimeout(() => {
            const el = document.querySelector('.alert');
            if (el) el.remove();
        }, 3000);
    }

    // Fungsi untuk menghitung durasi hari
    function calculateDuration() {
        const tanggalMulai = document.getElementById('dtTanggalMulai').value;
        const tanggalSelesai = document.getElementById('dtTanggalSelesai').value;
        const durasiInput = document.getElementById('durasiHari');

        if (tanggalMulai && tanggalSelesai) {
            const mulai = new Date(tanggalMulai);
            const selesai = new Date(tanggalSelesai);

            if (selesai >= mulai) {
                // Hitung selisih hari (inklusif: jika sama = 1 hari)
                const diffTime = Math.abs(selesai - mulai);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                durasiInput.value = diffDays + ' hari';
                durasiInput.classList.remove('text-danger');
                durasiInput.classList.add('text-success', 'fw-bold');
            } else {
                durasiInput.value = 'Tanggal selesai tidak boleh sebelum tanggal mulai';
                durasiInput.classList.remove('text-success', 'fw-bold');
                durasiInput.classList.add('text-danger');
            }
        } else {
            durasiInput.value = '';
            durasiInput.classList.remove('text-success', 'text-danger', 'fw-bold');
        }
    }

    // Event listener untuk menghitung durasi saat tanggal berubah
    document.getElementById('dtTanggalMulai').addEventListener('change', calculateDuration);
    document.getElementById('dtTanggalSelesai').addEventListener('change', calculateDuration);

    document.getElementById('addBtn').addEventListener('click', () => {
        isEditMode = false;
        currentId = null;
        document.getElementById('tmModalLabel').textContent = 'Tambah Izin Tidak Masuk';
        document.getElementById('tmForm').reset();
        document.getElementById('_method').value = 'POST';
        // Reset field NIK dan hidden field
        document.getElementById('vcNik').value = '';
        document.getElementById('vcNikHidden').value = '';
        document.getElementById('namaPreview').textContent = '';
        document.getElementById('vcNik').readOnly = false;
        document.getElementById('durasiHari').value = '';
        document.getElementById('durasiHari').classList.remove('text-success', 'text-danger', 'fw-bold');
        new bootstrap.Modal(document.getElementById('tmModal')).show();
    });

    document.getElementById('tmForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Pastikan hidden field vcNik terisi sebelum submit
        const nikInput = document.getElementById('vcNik');
        const nikHidden = document.getElementById('vcNikHidden');
        if (nikInput && nikHidden) {
            const nikValue = nikInput.value.trim();
            // Jika format "NIK - Nama", ambil NIK saja
            if (nikValue.includes(' - ')) {
                const nikOnly = nikValue.split(' - ')[0].trim();
                nikHidden.value = nikOnly;
            } else if (nikValue && !nikHidden.value) {
                nikHidden.value = nikValue;
            }
            // Validasi: pastikan hidden field terisi
            if (!nikHidden.value) {
                showAlert('error', 'NIK harus diisi');
                return;
            }
        }
        
        const url = isEditMode ? `/tidak-masuk/${currentId}` : '/tidak-masuk';
        document.getElementById('_method').value = isEditMode ? 'PUT' : 'POST';
        const formData = new FormData(this);

        fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    bootstrap.Modal.getInstance(document.getElementById('tmModal')).hide();
                    location.reload();
                } else {
                    showAlert('error', data.message || 'Gagal menyimpan data');
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('error', 'Terjadi kesalahan saat menyimpan');
            });
    });

    function editRecord(nik, kodeAbsen, tMulai, tSelesai) {
        const id = btoa(nik + '|' + kodeAbsen + '|' + tMulai + '|' + tSelesai);
        fetch(`/tidak-masuk/${id}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    isEditMode = true;
                    currentId = id;
                    document.getElementById('tmModalLabel').textContent = 'Edit Izin Tidak Masuk';
                    document.getElementById('_method').value = 'PUT';
                    // Set NIK: hidden field untuk submit, input untuk display
                    const nikValue = data.record.vcNik || '';
                    document.getElementById('vcNikHidden').value = nikValue;
                    // Fetch nama karyawan untuk ditampilkan di input
                    if (nikValue) {
                        fetch(`/karyawan/${nikValue}`, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json'
                                }
                            })
                            .then(r => r.json())
                            .then(dataKaryawan => {
                                if (dataKaryawan.success && dataKaryawan.karyawan) {
                                    document.getElementById('vcNik').value = `${nikValue} - ${dataKaryawan.karyawan.Nama || ''}`;
                                    document.getElementById('namaPreview').textContent = 'Nama: ' + (dataKaryawan.karyawan.Nama || '');
                                } else {
                                    document.getElementById('vcNik').value = nikValue;
                                    document.getElementById('namaPreview').textContent = '';
                                }
                            })
                            .catch(() => {
                                document.getElementById('vcNik').value = nikValue;
                                document.getElementById('namaPreview').textContent = '';
                            });
                    } else {
                        document.getElementById('vcNik').value = nikValue;
                        document.getElementById('namaPreview').textContent = '';
                    }
                    document.getElementById('dtTanggalMulai').value = data.record.dtTanggalMulai || '';
                    document.getElementById('dtTanggalSelesai').value = data.record.dtTanggalSelesai || '';
                    document.getElementById('vcKodeAbsen').value = data.record.vcKodeAbsen || '';
                    document.getElementById('vcKeterangan').value = data.record.vcKeterangan || '';
                    document.getElementById('vcDibayar').checked = (data.record.vcDibayar === 'Y');
                    document.getElementById('vcNik').readOnly = true;
                    // Hitung durasi setelah data diisi
                    calculateDuration();
                    new bootstrap.Modal(document.getElementById('tmModal')).show();
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('error', 'Gagal memuat data');
            });
    }

    function deleteRecord(nik, kodeAbsen, tMulai, tSelesai) {
        if (!confirm('Hapus data ini?')) return;
        const id = btoa(nik + '|' + kodeAbsen + '|' + tMulai + '|' + tSelesai);
        fetch(`/tidak-masuk/${id}`, {
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
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    location.reload();
                } else {
                    showAlert('error', data.message || 'Gagal menghapus data');
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('error', 'Terjadi kesalahan saat menghapus');
            });
    }

    // Function untuk load data karyawan berdasarkan NIK
    function loadKaryawanData(nik) {
        if (!nik || !nik.trim()) {
            document.getElementById('namaPreview').textContent = '';
            return;
        }
        fetch(`/karyawan/${nik.trim()}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.karyawan && data.karyawan.Nama) {
                    document.getElementById('namaPreview').textContent = 'Nama: ' + data.karyawan.Nama;
                } else {
                    document.getElementById('namaPreview').textContent = '';
                }
            })
            .catch(() => {
                document.getElementById('namaPreview').textContent = '';
            });
    }

    // Autocomplete untuk field NIK di modal
    let nikAutocompleteTimeout;
    let nikSelectedIndex = -1;
    const nikInput = document.getElementById('vcNik');
    const nikAutocompleteDiv = document.getElementById('nikAutocomplete');
    const nikHiddenInput = document.getElementById('vcNikHidden');

    if (nikInput && nikAutocompleteDiv) {
        nikInput.addEventListener('input', function() {
            const value = this.value.trim().toLowerCase();
            clearTimeout(nikAutocompleteTimeout);
            
            if (value.length === 0) {
                nikAutocompleteDiv.style.display = 'none';
                nikSelectedIndex = -1;
                nikHiddenInput.value = '';
                loadKaryawanData('');
                return;
            }
            
            if (value.length < 2) {
                nikAutocompleteDiv.style.display = 'none';
                return;
            }
            
            nikAutocompleteTimeout = setTimeout(() => {
                const results = karyawanList.filter(k => {
                    const searchText = (k.nik + ' ' + k.nama + ' ' + k.divisi + ' ' + k.bagian).toLowerCase();
                    return searchText.includes(value);
                }).slice(0, 20);
                displayNikAutocomplete(results);
            }, 200);
        });

        nikInput.addEventListener('keydown', function(e) {
            const items = nikAutocompleteDiv.querySelectorAll('.autocomplete-item');
            if (items.length === 0) return;
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                nikSelectedIndex = Math.min(nikSelectedIndex + 1, items.length - 1);
                updateNikSelectedItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                nikSelectedIndex = Math.max(nikSelectedIndex - 1, -1);
                updateNikSelectedItem(items);
            } else if (e.key === 'Enter' && nikSelectedIndex >= 0) {
                e.preventDefault();
                items[nikSelectedIndex].click();
            } else if (e.key === 'Enter' && nikSelectedIndex === -1 && this.value.trim()) {
                // Jika Enter tanpa pilihan, coba load data langsung (untuk NIK yang diketik manual)
                const nikValue = this.value.trim();
                // Jika format "NIK - Nama", ambil NIK saja
                if (nikValue.includes(' - ')) {
                    const nikOnly = nikValue.split(' - ')[0].trim();
                    nikInput.value = nikOnly;
                    nikHiddenInput.value = nikOnly;
                    loadKaryawanData(nikOnly);
                } else {
                    nikHiddenInput.value = nikValue;
                    loadKaryawanData(nikValue);
                }
                nikAutocompleteDiv.style.display = 'none';
            }
        });

        // Blur event untuk load data jika user mengetik manual
        nikInput.addEventListener('blur', function() {
            setTimeout(() => {
                const value = this.value.trim();
                if (value && !nikHiddenInput.value) {
                    // Jika format "NIK - Nama", ambil NIK saja
                    if (value.includes(' - ')) {
                        const nikOnly = value.split(' - ')[0].trim();
                        nikInput.value = nikOnly;
                        nikHiddenInput.value = nikOnly;
                        loadKaryawanData(nikOnly);
                    } else {
                        nikHiddenInput.value = value;
                        loadKaryawanData(value);
                    }
                }
            }, 200);
        });
    }

    function displayNikAutocomplete(karyawans) {
        if (!karyawans || karyawans.length === 0) {
            nikAutocompleteDiv.innerHTML = '<div class="autocomplete-item">Tidak ada karyawan ditemukan</div>';
            nikAutocompleteDiv.style.display = 'block';
            return;
        }
        nikAutocompleteDiv.innerHTML = '';
        karyawans.forEach((karyawan, index) => {
            if (!karyawan || !karyawan.nik) return;
            const item = document.createElement('div');
            item.className = 'autocomplete-item';
            item.innerHTML = `
                <strong>${karyawan.nik || ''}</strong> - ${karyawan.nama || ''}
                <small>Divisi: ${karyawan.divisi || '-'} | Bagian: ${karyawan.bagian || '-'}</small>
            `;
            item.addEventListener('click', function() {
                selectNikKaryawan(karyawan);
            });
            nikAutocompleteDiv.appendChild(item);
        });
        nikAutocompleteDiv.style.display = 'block';
        nikSelectedIndex = -1;
    }

    function selectNikKaryawan(karyawan) {
        nikInput.value = `${karyawan.nik} - ${karyawan.nama}`;
        nikHiddenInput.value = karyawan.nik;
        nikAutocompleteDiv.style.display = 'none';
        nikSelectedIndex = -1;
        loadKaryawanData(karyawan.nik);
        nikInput.focus();
    }

    function updateNikSelectedItem(items) {
        items.forEach((item, index) => {
            item.classList.toggle('active', index === nikSelectedIndex);
            if (index === nikSelectedIndex) {
                item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
        });
    }

    // Close autocomplete when clicking outside
    document.addEventListener('click', function(e) {
        if (nikInput && nikAutocompleteDiv && !nikInput.contains(e.target) && !nikAutocompleteDiv.contains(e.target)) {
            nikAutocompleteDiv.style.display = 'none';
            nikSelectedIndex = -1;
        }
    });

    // Auto submit filter saat tanggal berubah
    document.getElementById('dari_tanggal').addEventListener('change', () => document.getElementById('filterForm').submit());
    document.getElementById('sampai_tanggal').addEventListener('change', () => document.getElementById('filterForm').submit());

    // Autocomplete functionality
    let searchTimeout;
    let selectedIndex = -1;
    const searchInput = document.getElementById('search');
    const autocompleteDiv = document.getElementById('searchAutocomplete');
    const karyawanList = @json($karyawanList ?? []);

    function getCurrentSearchTerms() {
        const value = searchInput.value.trim();
        if (!value) return [];
        return value.split(',').map(term => term.trim()).filter(term => term.length > 0);
    }

    function getCurrentTypingTerm() {
        const value = searchInput.value.trim();
        if (!value) return '';
        const terms = value.split(',');
        return terms[terms.length - 1].trim();
    }

    searchInput.addEventListener('input', function() {
        const currentTerm = getCurrentTypingTerm().toLowerCase();
        clearTimeout(searchTimeout);
        if (currentTerm.length === 0) {
            autocompleteDiv.style.display = 'none';
            selectedIndex = -1;
            return;
        }
        if (currentTerm.length < 2) {
            autocompleteDiv.style.display = 'none';
            return;
        }
        searchTimeout = setTimeout(() => {
            const results = karyawanList.filter(k => k.search.includes(currentTerm)).slice(0, 20);
            displayAutocomplete(results);
        }, 200);
    });

    function displayAutocomplete(karyawans) {
        if (!karyawans || karyawans.length === 0) {
            autocompleteDiv.innerHTML = '<div class="autocomplete-item">Tidak ada karyawan ditemukan</div>';
            autocompleteDiv.style.display = 'block';
            return;
        }
        autocompleteDiv.innerHTML = '';
        karyawans.forEach((karyawan, index) => {
            if (!karyawan || !karyawan.nik) return;
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
        selectedIndex = -1;
    }

    function selectKaryawan(karyawan) {
        const currentTerms = getCurrentSearchTerms();
        currentTerms.pop();
        const newTerm = `${karyawan.nik} - ${karyawan.nama}`;
        currentTerms.push(newTerm);
        searchInput.value = currentTerms.join(', ');
        autocompleteDiv.style.display = 'none';
        selectedIndex = -1;
        searchInput.focus();
    }

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !autocompleteDiv.contains(e.target)) {
            autocompleteDiv.style.display = 'none';
            selectedIndex = -1;
        }
    });

    searchInput.addEventListener('keydown', function(e) {
        const items = autocompleteDiv.querySelectorAll('.autocomplete-item');
        if (items.length === 0) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
            updateSelectedItem(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, -1);
            updateSelectedItem(items);
        } else if (e.key === 'Enter' && selectedIndex >= 0) {
            e.preventDefault();
            items[selectedIndex].click();
        }
    });

    function updateSelectedItem(items) {
        items.forEach((item, index) => {
            item.classList.toggle('active', index === selectedIndex);
        });
    }
</script>
@endpush

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
    /* Autocomplete di dalam modal harus z-index lebih tinggi */
    #nikAutocomplete {
        z-index: 1055 !important;
    }
</style>
@endpush