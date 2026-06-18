@extends('layouts.app')

@section('title', 'Instruksi Kerja Lembur - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i>Instruksi Kerja Lembur
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
                    <form method="GET" action="{{ route('instruksi-kerja-lembur.index') }}" id="filterForm">
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
                                <label for="nik" class="form-label">NIK</label>
                                <input type="text" class="form-control" id="nik" name="nik" value="{{ $nik }}" placeholder="Cari NIK">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
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
                                    <th width="9%">Kode Lembur</th>
                                    <th width="9%">Tanggal</th>
                                    <th width="10%">Jenis</th>
                                    <th width="10%">Divisi</th>
                                    <th width="10%">Departemen</th>
                                    <th width="10%">Bagian</th>
                                    <th width="10%">Diajukan Oleh</th>
                                    <th width="7%">Jml Karyawan</th>
                                    <th width="12%">Penanggung Biaya</th>
                                    <th width="13%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($records as $row)
                                <tr>
                                    <td><span class="badge bg-secondary">{{ $row->vcCounter ?? '-' }}</span></td>
                                    <td>{{ $row->dtTanggalLembur ? $row->dtTanggalLembur->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        @if($row->vcJenisLembur)
                                        <span class="badge {{ $row->vcJenisLembur == 'Hari Libur' ? 'bg-warning' : 'bg-success' }}">
                                            {{ $row->vcJenisLembur }}
                                        </span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $row->vcBusinessUnit ?? '-' }}</td>
                                    <td>{{ $row->departemen->vcNamaDept ?? ($row->vcKodeDept ?? '-') }}</td>
                                    <td>{{ $row->bagian->vcNamaBagian ?? ($row->vcKodeBagian ?? '-') }}</td>
                                    <td>{{ $row->vcDiajukanOleh ?? '-' }}</td>
                                    <td class="text-center"><span class="badge bg-info">{{ $row->details ? $row->details->count() : 0 }}</span></td>
                                    <td>
                                        @if($row->vcPenanggungBiaya === 'Lainnya')
                                        {{ $row->vcPenanggungBiayaLainnya ?? '-' }}
                                        @else
                                        {{ $row->vcPenanggungBiaya ?? '-' }}
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="editRecord('{{ $row->vcCounter }}')" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-info" onclick="viewRecord('{{ $row->vcCounter }}')" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteRecord('{{ $row->vcCounter }}')" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
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

<!-- Modal Form -->
<div class="modal fade" id="lemburModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lemburModalLabel">Tambah Instruksi Kerja Lembur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="lemburForm">
                <input type="hidden" name="_method" id="_method" value="POST">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <!-- Header Section -->
                    <div class="card mb-3 border-primary">
                        <div class="card-header bg-primary text-white">
                            <strong>Header / Master</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Row 1: Divisi, Departemen, Bagian -->
                                <div class="col-md-4">
                                    <label for="vcKodeDivisi" class="form-label">Divisi <span class="text-danger">*</span></label>
                                    <select class="form-select" id="vcKodeDivisi" name="vcKodeDivisi" required>
                                        <option value="">Pilih Divisi</option>
                                        @foreach($divisis as $divisi)
                                        <option value="{{ $divisi->vcKodeDivisi }}">{{ $divisi->vcKodeDivisi }} - {{ $divisi->vcNamaDivisi }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="vcKodeDept" class="form-label">Departemen <span class="text-danger">*</span></label>
                                    <select class="form-select" id="vcKodeDept" name="vcKodeDept" required disabled>
                                        <option value="">Pilih Divisi terlebih dahulu</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="vcKodeBagian" class="form-label">Bagian <span class="text-danger">*</span></label>
                                    <select class="form-select" id="vcKodeBagian" name="vcKodeBagian" required disabled>
                                        <option value="">Pilih Departemen terlebih dahulu</option>
                                    </select>
                                </div>

                                <!-- Row 2: Diajukan Oleh, Jabatan Pengaju, Kepala Departemen -->
                                <div class="col-md-4">
                                    <label for="vcDiajukanOleh" class="form-label">Diajukan Oleh <span class="text-danger">*</span></label>
                                    <div class="searchable-select-wrapper">
                                        <input type="text" class="form-control searchable-select-search" id="vcDiajukanOleh_search" placeholder="Cari NIK atau Nama..." autocomplete="off" required>
                                        <select class="form-select searchable-select" id="vcDiajukanOleh" name="vcDiajukanOleh" size="1" required>
                                            <option value="">Pilih Karyawan</option>
                                            @foreach($karyawans as $karyawan)
                                            <option value="{{ $karyawan->Nik }}" data-search="{{ strtolower($karyawan->Nik . ' ' . $karyawan->Nama) }}">{{ $karyawan->Nik }} - {{ $karyawan->Nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="vcJabatanPengaju" class="form-label">Jabatan Pengaju</label>
                                    <input type="text" class="form-control" id="vcJabatanPengaju_display" readonly style="background-color: #e9ecef;" placeholder="Otomatis terisi dari Diajukan Oleh">
                                    <input type="hidden" id="vcJabatanPengaju" name="vcJabatanPengaju">
                                </div>
                                <div class="col-md-4">
                                    <label for="vcKepalaDept" class="form-label">Kepala Departemen</label>
                                    <input type="text" class="form-control" id="vcKepalaDept" name="vcKepalaDept" readonly style="background-color: #e9ecef;" placeholder="Otomatis terisi dari Departemen">
                                </div>

                                <!-- Row 3: Tanggal Lembur, Jenis Lembur -->
                                <div class="col-md-4">
                                    <label for="dtTanggalLembur" class="form-label">Tanggal Lembur <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="dtTanggalLembur" name="dtTanggalLembur" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="vcJenisLembur" class="form-label">Jenis Lembur</label>
                                    <input type="text" class="form-control" id="vcJenisLembur" name="vcJenisLembur" readonly style="background-color: #e9ecef;">
                                </div>

                                <div class="col-12">
                                    <label for="vcAlasanDasarLembur" class="form-label">Alasan / Dasar Lembur</label>
                                    <textarea class="form-control" id="vcAlasanDasarLembur" name="vcAlasanDasarLembur" rows="2" maxlength="200"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Section -->
                    <div class="card border-success">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <strong>Detail / Child - Daftar Karyawan</strong>
                            <button type="button" class="btn btn-sm btn-light" id="btnAddDetail">
                                <i class="fas fa-plus me-1"></i>Tambah Karyawan
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="detailContainer">
                                <!-- Detail rows akan ditambahkan di sini -->
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

<!-- Modal View Detail -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Instruksi Kerja Lembur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <!-- Content akan diisi oleh JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let isEditMode = false;
    let currentId = null;
    let detailIndex = 0;

    // Hierarchical dropdown elements
    const divisiSelect = document.getElementById('vcKodeDivisi');
    const departemenSelect = document.getElementById('vcKodeDept');
    const bagianSelect = document.getElementById('vcKodeBagian');

    // ===== HIERARCHICAL DROPDOWN FUNCTIONALITY =====
    // When divisi changes, load departemens
    divisiSelect.addEventListener('change', function() {
        const divisiKode = this.value;

        // Reset departemen and bagian
        departemenSelect.innerHTML = '<option value="">Pilih Departemen</option>';
        departemenSelect.disabled = !divisiKode;
        departemenSelect.required = !!divisiKode;
        bagianSelect.innerHTML = '<option value="">Pilih Bagian</option>';
        bagianSelect.disabled = true;
        bagianSelect.required = false;

        if (divisiKode) {
            loadDepartemens(divisiKode);

            // Tidak perlu load karyawan di sini karena akan di-load saat departemen dipilih
        } else {
            // Jika divisi dikosongkan, disable semua detail rows
            document.querySelectorAll('.detail-row').forEach(row => {
                const nikSelect = row.querySelector('.nik-select');
                const wrapper = row.querySelector('.searchable-select-wrapper');
                if (nikSelect) {
                    nikSelect.innerHTML = '<option value="">Pilih Departemen terlebih dahulu</option>';
                    nikSelect.disabled = true;
                    nikSelect.removeAttribute('required');
                }
                if (wrapper) {
                    const searchInput = wrapper.querySelector('.searchable-select-search');
                    if (searchInput) {
                        searchInput.disabled = true;
                        searchInput.placeholder = 'Pilih Departemen terlebih dahulu';
                        searchInput.value = '';
                    }
                }
            });
        }
    });

    // When departemen changes, load bagians, kepala departemen, dan karyawan untuk detail rows
    departemenSelect.addEventListener('change', function() {
        const divisiKode = divisiSelect.value;
        const deptKode = this.value;

        // Reset bagian
        bagianSelect.innerHTML = '<option value="">Pilih Bagian</option>';
        bagianSelect.disabled = !deptKode;
        bagianSelect.required = !!deptKode;

        // Load kepala departemen
        if (deptKode) {
            loadKepalaDept(deptKode);

            // Load karyawan untuk semua detail rows yang sudah ada
            // Gunakan retry logic untuk memastikan detail rows sudah ter-render
            let attempts = 0;
            const maxAttempts = 10;
            const loadKaryawanForAllRows = () => {
                attempts++;
                const detailRows = document.querySelectorAll('.detail-row');
                console.log('Departemen change - Attempt', attempts, ': Mencari detail rows, ditemukan:', detailRows.length);

                if (detailRows.length === 0 && attempts < maxAttempts) {
                    setTimeout(loadKaryawanForAllRows, 200);
                    return;
                }

                if (detailRows.length > 0) {
                    console.log('Departemen change - Detail rows ditemukan, mulai load karyawan untuk', detailRows.length, 'rows');
                }

                detailRows.forEach(row => {
                    const nikSelect = row.querySelector('.nik-select');
                    const wrapper = row.querySelector('.searchable-select-wrapper');
                    if (nikSelect && wrapper && deptKode) {
                        const rowIndex = row.dataset.index;
                        console.log('Departemen berubah, load karyawan untuk row', rowIndex);

                        nikSelect.disabled = true;
                        nikSelect.removeAttribute('required');
                        nikSelect.innerHTML = '<option value="">Memuat...</option>';

                        fetch('/instruksi-kerja-lembur/get-karyawans-by-departemen', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    departemen: deptKode
                                })
                            })
                            .then(response => response.json())
                            .then(result => {
                                if (result.success) {
                                    console.log('Karyawan ter-load untuk row', rowIndex, ', jumlah:', result.karyawans.length);
                                    nikSelect.innerHTML = '<option value="">Pilih NIK</option>';
                                    result.karyawans.forEach(k => {
                                        const option = document.createElement('option');
                                        option.value = k.Nik;
                                        option.textContent = k.Nik + ' - ' + k.Nama;
                                        option.setAttribute('data-search', (k.Nik + ' ' + k.Nama).toLowerCase());
                                        nikSelect.appendChild(option);
                                    });
                                    nikSelect.disabled = false;
                                    if (nikSelect.options.length > 1) {
                                        nikSelect.setAttribute('required', 'required');
                                    } else {
                                        nikSelect.removeAttribute('required');
                                    }
                                    console.log('Select enabled untuk row', rowIndex, ', options count:', nikSelect.options.length, ', required:', nikSelect.hasAttribute('required'));

                                    const searchInput = wrapper.querySelector('.searchable-select-search');
                                    if (searchInput) {
                                        console.log('Enable search input untuk row', rowIndex, 'setelah karyawan ter-load');
                                        searchInput.disabled = false;
                                        searchInput.placeholder = 'Cari NIK atau Nama karyawan aktif...';
                                        searchInput.removeAttribute('disabled');
                                    }

                                    setTimeout(() => {
                                        if (nikSelect.options.length > 1) {
                                            console.log('Re-inisialisasi searchable select untuk row', rowIndex, 'dengan', nikSelect.options.length, 'options');
                                            delete wrapper.dataset.initialized;
                                            initSearchableSelectForDetail(wrapper, nikSelect);
                                        }
                                    }, 200);
                                } else {
                                    nikSelect.innerHTML = '<option value="">Tidak ada data</option>';
                                    nikSelect.disabled = true;
                                    nikSelect.removeAttribute('required');
                                }
                            })
                            .catch(error => {
                                console.error('Error loading karyawans untuk row', rowIndex, ':', error);
                                nikSelect.innerHTML = '<option value="">Error memuat data</option>';
                            });
                    } else if (nikSelect && !deptKode) {
                        nikSelect.innerHTML = '<option value="">Pilih Departemen terlebih dahulu</option>';
                        nikSelect.disabled = true;
                        nikSelect.removeAttribute('required');
                        const searchInput = wrapper ? wrapper.querySelector('.searchable-select-search') : null;
                        if (searchInput) {
                            searchInput.disabled = true;
                            searchInput.placeholder = 'Pilih Departemen terlebih dahulu';
                            searchInput.value = '';
                        }
                    }
                });
            };

            setTimeout(loadKaryawanForAllRows, 200);
        } else {
            // Reset kepala departemen jika departemen kosong
            const kepalaDeptInput = document.getElementById('vcKepalaDept');
            if (kepalaDeptInput) {
                kepalaDeptInput.value = '';
            }

            // Disable semua detail rows jika departemen dikosongkan
            document.querySelectorAll('.detail-row').forEach(row => {
                const nikSelect = row.querySelector('.nik-select');
                const wrapper = row.querySelector('.searchable-select-wrapper');
                if (nikSelect) {
                    nikSelect.innerHTML = '<option value="">Pilih Departemen terlebih dahulu</option>';
                    nikSelect.disabled = true;
                    nikSelect.removeAttribute('required');
                }
                if (wrapper) {
                    const searchInput = wrapper.querySelector('.searchable-select-search');
                    if (searchInput) {
                        searchInput.disabled = true;
                        searchInput.placeholder = 'Pilih Departemen terlebih dahulu';
                        searchInput.value = '';
                    }
                }
            });
        }

        if (divisiKode && deptKode) {
            loadBagians(divisiKode, deptKode);
        }
    });

    function loadDepartemens(divisiKode, callback) {
        departemenSelect.disabled = true;
        departemenSelect.innerHTML = '<option value="">Memuat...</option>';

        fetch('/instruksi-kerja-lembur/get-departemens', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    divisi: divisiKode
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    departemenSelect.innerHTML = '<option value="">Pilih Departemen</option>';
                    data.departemens.forEach(dept => {
                        const option = document.createElement('option');
                        option.value = dept.vcKodeDept;
                        option.textContent = dept.vcKodeDept + ' - ' + dept.vcNamaDept;
                        departemenSelect.appendChild(option);
                    });
                    departemenSelect.disabled = false;
                } else {
                    departemenSelect.innerHTML = '<option value="">Tidak ada data</option>';
                }
                // Panggil callback jika ada
                if (callback) callback();
            })
            .catch(error => {
                console.error('Error loading departemens:', error);
                departemenSelect.innerHTML = '<option value="">Error memuat data</option>';
                if (callback) callback();
            });
    }

    function loadBagians(divisiKode, deptKode, callback) {
        bagianSelect.disabled = true;
        bagianSelect.innerHTML = '<option value="">Memuat...</option>';

        fetch('/instruksi-kerja-lembur/get-bagians', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    divisi: divisiKode,
                    departemen: deptKode
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bagianSelect.innerHTML = '<option value="">Pilih Bagian</option>';
                    data.bagians.forEach(bagian => {
                        const option = document.createElement('option');
                        option.value = bagian.vcKodeBagian;
                        option.textContent = bagian.vcKodeBagian + ' - ' + bagian.vcNamaBagian;
                        bagianSelect.appendChild(option);
                    });
                    bagianSelect.disabled = false;
                } else {
                    bagianSelect.innerHTML = '<option value="">Tidak ada data</option>';
                }
                // Panggil callback jika ada
                if (callback) callback();
            })
            .catch(error => {
                console.error('Error loading bagians:', error);
                bagianSelect.innerHTML = '<option value="">Error memuat data</option>';
                if (callback) callback();
            });
    }

    // Function to load kepala departemen
    function loadKepalaDept(deptKode) {
        const kepalaDeptInput = document.getElementById('vcKepalaDept');
        if (!kepalaDeptInput) return;

        kepalaDeptInput.value = 'Memuat...';

        fetch('{{ route("instruksi-kerja-lembur.get-kepala-dept") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    departemen: deptKode
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.kepalaDept) {
                    kepalaDeptInput.value = data.kepalaDept;
                } else {
                    kepalaDeptInput.value = '';
                }
            })
            .catch(error => {
                console.error('Error loading kepala departemen:', error);
                kepalaDeptInput.value = '';
            });
    }

    // ===== AUTO-FILL DIVISI, DEPARTEMEN, BAGIAN BERDASARKAN DIAJUKAN OLEH =====
    // Event listener untuk auto-fill Divisi, Departemen, Bagian saat "Diajukan Oleh" dipilih
    // Menggunakan event delegation pada document untuk memastikan event tetap ter-trigger
    // meskipun element di-clone oleh initSearchableSelect
    // (Event listener ini akan di-attach di DOMContentLoaded untuk menghindari duplikasi)

    // ===== SEARCHABLE SELECT FUNCTIONALITY =====
    const style = document.createElement('style');
    style.textContent = `
        .searchable-select-wrapper {
            position: relative;
        }
        .searchable-select-search {
            width: 100%;
            margin-bottom: 4px;
        }
        .searchable-select {
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 1000;
            display: none;
            background: white;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .searchable-select-wrapper.active .searchable-select {
            display: block;
        }
        .searchable-select option {
            padding: 8px 12px;
            cursor: pointer;
        }
        .searchable-select option:hover {
            background-color: #f8f9fa;
        }
        .searchable-select option:checked,
        .searchable-select option:focus,
        .searchable-select option[selected] {
            background-color: #0d6efd;
            color: white;
        }
        .searchable-select option[style*="display: none"] {
            display: none !important;
        }
    `;
    document.head.appendChild(style);

    function filterOptions(select, searchTerm) {
        const term = searchTerm ? searchTerm.toLowerCase().trim() : '';
        const options = select.options;

        let visibleCount = 0;
        let firstVisibleIndex = -1;
        for (let i = 0; i < options.length; i++) {
            const option = options[i];
            // Skip option kosong (placeholder)
            if (!option.value) {
                option.style.display = term ? 'none' : '';
                continue;
            }

            const searchText = option.getAttribute('data-search') || option.textContent.toLowerCase();

            if (!term || searchText.includes(term)) {
                option.style.display = '';
                option.removeAttribute('selected');
                if (firstVisibleIndex < 0 && option.value !== '') {
                    firstVisibleIndex = i;
                }
                visibleCount++;
            } else {
                option.style.display = 'none';
                option.removeAttribute('selected');
            }
        }

        // Set selected index ke first visible option jika ada
        if (visibleCount > 0 && term && firstVisibleIndex >= 0) {
            select.selectedIndex = firstVisibleIndex;
        } else if (visibleCount === 0 && term) {
            select.selectedIndex = -1;
        }
    }

    // Fungsi khusus untuk inisialisasi searchable select di detail
    function initSearchableSelectForDetail(wrapper, select) {
        const searchInput = wrapper.querySelector('.searchable-select-search');

        if (!searchInput || !select) {
            console.warn('initSearchableSelectForDetail: searchInput atau select tidak ditemukan', {
                searchInput: !!searchInput,
                select: !!select,
                wrapper: wrapper
            });
            return;
        }

        // Jika sudah diinisialisasi, hapus event listeners lama dengan clone
        if (wrapper.dataset.initialized === 'true') {
            const newSearchInput = searchInput.cloneNode(true);
            const newSelect = select.cloneNode(true);
            searchInput.parentNode.replaceChild(newSearchInput, searchInput);
            select.parentNode.replaceChild(newSelect, select);
        }

        // Update references (setelah clone jika ada)
        const actualSearchInput = wrapper.querySelector('.searchable-select-search');
        const actualSelect = wrapper.querySelector('.searchable-select');

        if (!actualSearchInput || !actualSelect) {
            console.warn('initSearchableSelectForDetail: actualSearchInput atau actualSelect tidak ditemukan setelah clone');
            return;
        }

        // Hapus flag initialized untuk memastikan inisialisasi ulang
        delete wrapper.dataset.initialized;

        // Set placeholder
        if (!actualSearchInput.placeholder || actualSearchInput.placeholder.includes('Pilih Departemen')) {
            actualSearchInput.placeholder = 'Cari NIK atau Nama karyawan aktif...';
        }

        // Event listener untuk focus - pastikan dropdown muncul
        actualSearchInput.addEventListener('focus', function() {
            // Cek apakah select sudah memiliki options (selain placeholder)
            const hasOptions = actualSelect.options.length > 1;
            if (!hasOptions) {
                console.warn('Focus: Select belum memiliki options, pastikan departemen sudah dipilih. Options count:', actualSelect.options.length);
                // Disable search input jika belum ada options
                actualSearchInput.disabled = true;
                actualSearchInput.placeholder = 'Pilih Departemen terlebih dahulu';
                wrapper.classList.remove('active');
                return;
            }

            console.log('Focus event triggered on search input, options count:', actualSelect.options.length);
            wrapper.classList.add('active');
            // Filter options berdasarkan value yang ada
            filterOptions(actualSelect, actualSearchInput.value);
            // Jika tidak ada value, tampilkan semua options yang tersedia
            if (!actualSearchInput.value.trim()) {
                const options = actualSelect.options;
                let hasVisibleOptions = false;
                for (let i = 0; i < options.length; i++) {
                    if (options[i].value !== '') {
                        options[i].style.display = '';
                        hasVisibleOptions = true;
                    }
                }
                if (!hasVisibleOptions && options.length > 1) {
                    console.warn('Tidak ada options yang tersedia untuk ditampilkan');
                }
            }
        }, {
            passive: true
        });

        // Event listener untuk input - filter saat mengetik
        actualSearchInput.addEventListener('input', function() {
            // Cek apakah select sudah memiliki options (selain placeholder)
            const hasOptions = actualSelect.options.length > 1;
            if (!hasOptions) {
                console.warn('Input: Select belum memiliki options, pastikan departemen sudah dipilih. Options count:', actualSelect.options.length);
                wrapper.classList.remove('active');
                return;
            }

            const term = this.value.trim();
            console.log('Input event triggered, term:', term, 'options count:', actualSelect.options.length);
            // Pastikan dropdown selalu muncul saat mengetik
            wrapper.classList.add('active');
            // Filter options berdasarkan term yang diketik
            filterOptions(actualSelect, term);

            // Debug: cek visible options setelah filter
            const visibleOptions = Array.from(actualSelect.options).filter(opt => opt.style.display !== 'none' && opt.value !== '');
            console.log('Visible options after filter:', visibleOptions.length);

            // Jika tidak ada hasil, tetap tampilkan dropdown kosong untuk feedback
            if (visibleOptions.length === 0 && term) {
                wrapper.classList.add('active');
            }
        }, {
            passive: true
        });

        actualSelect.addEventListener('click', function(e) {
            if (e.target.tagName === 'OPTION' && e.target.style.display !== 'none') {
                const selectedValue = e.target.value;
                if (!selectedValue || selectedValue.trim() === '') {
                    console.warn('Click: Option dengan value kosong dipilih');
                    return;
                }

                // Set value terlebih dahulu
                actualSelect.value = selectedValue;
                actualSearchInput.value = e.target.textContent;
                wrapper.classList.remove('active');

                // Verifikasi value ter-set
                if (actualSelect.value !== selectedValue) {
                    console.warn('Click: Value tidak ter-set dengan benar, coba set lagi');
                    actualSelect.value = selectedValue;
                }

                // Set required jika ini adalah nik-select dan sudah enabled
                if (actualSelect.classList.contains('nik-select') && !actualSelect.disabled && selectedValue) {
                    actualSelect.setAttribute('required', 'required');
                    console.log('✓ Click: NIK dipilih, set required untuk', actualSelect.name, ', value:', selectedValue);
                }

                // Trigger change event
                actualSelect.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
                actualSearchInput.blur();
            }
        });

        actualSelect.addEventListener('mousedown', function(e) {
            if (e.target.tagName === 'OPTION' && e.target.style.display !== 'none') {
                e.preventDefault();
                const selectedValue = e.target.value;
                if (!selectedValue || selectedValue.trim() === '') {
                    console.warn('Mousedown: Option dengan value kosong dipilih');
                    return;
                }

                // Set value terlebih dahulu
                actualSelect.value = selectedValue;
                actualSearchInput.value = e.target.textContent;
                wrapper.classList.remove('active');

                // Verifikasi value ter-set
                if (actualSelect.value !== selectedValue) {
                    console.warn('Mousedown: Value tidak ter-set dengan benar, coba set lagi');
                    actualSelect.value = selectedValue;
                }

                // Set required jika ini adalah nik-select dan sudah enabled
                if (actualSelect.classList.contains('nik-select') && !actualSelect.disabled && selectedValue) {
                    actualSelect.setAttribute('required', 'required');
                    console.log('✓ Mousedown: NIK dipilih, set required untuk', actualSelect.name, ', value:', selectedValue);
                }

                // Trigger change event
                actualSelect.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
                actualSearchInput.blur();
            }
        });

        // Close dropdown saat klik di luar
        // Gunakan unique handler per wrapper untuk menghindari duplikasi
        if (!wrapper.dataset.clickHandlerAttached) {
            const clickOutsideHandler = function(e) {
                if (!wrapper.contains(e.target)) {
                    wrapper.classList.remove('active');
                }
            };
            document.addEventListener('click', clickOutsideHandler, true);
            wrapper.dataset.clickHandlerAttached = 'true';
        }

        actualSearchInput.addEventListener('keydown', function(e) {
            const visibleOptions = Array.from(actualSelect.options).filter(opt => opt.style.display !== 'none' && opt.value !== '');
            if (visibleOptions.length === 0) return;

            let currentSelected = visibleOptions.findIndex(opt => opt.selected);
            if (currentSelected < 0 && visibleOptions.length > 0) {
                currentSelected = 0;
                visibleOptions[0].selected = true;
                actualSelect.selectedIndex = visibleOptions[0].index;
            }

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                const nextIndex = (currentSelected + 1) % visibleOptions.length;
                visibleOptions.forEach(opt => opt.selected = false);
                visibleOptions[nextIndex].selected = true;
                actualSelect.selectedIndex = visibleOptions[nextIndex].index;
                const optionElement = visibleOptions[nextIndex];
                if (optionElement) {
                    optionElement.scrollIntoView({
                        block: 'nearest',
                        behavior: 'smooth'
                    });
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                const prevIndex = currentSelected <= 0 ? visibleOptions.length - 1 : currentSelected - 1;
                visibleOptions.forEach(opt => opt.selected = false);
                visibleOptions[prevIndex].selected = true;
                actualSelect.selectedIndex = visibleOptions[prevIndex].index;
                const optionElement = visibleOptions[prevIndex];
                if (optionElement) {
                    optionElement.scrollIntoView({
                        block: 'nearest',
                        behavior: 'smooth'
                    });
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (currentSelected >= 0 && visibleOptions[currentSelected]) {
                    const selectedOption = visibleOptions[currentSelected];
                    const selectedValue = selectedOption.value;

                    if (!selectedValue || selectedValue.trim() === '') {
                        console.warn('Enter: Option dengan value kosong dipilih');
                        return;
                    }

                    // Set value terlebih dahulu
                    actualSelect.value = selectedValue;
                    actualSearchInput.value = selectedOption.textContent;
                    wrapper.classList.remove('active');

                    // Verifikasi value ter-set
                    if (actualSelect.value !== selectedValue) {
                        console.warn('Enter: Value tidak ter-set dengan benar, coba set lagi');
                        actualSelect.value = selectedValue;
                    }

                    // Set required jika ini adalah nik-select dan sudah enabled
                    if (actualSelect.classList.contains('nik-select') && !actualSelect.disabled && selectedValue) {
                        actualSelect.setAttribute('required', 'required');
                        console.log('✓ Enter (ForDetail): NIK dipilih, set required untuk', actualSelect.name, ', value:', selectedValue);
                    }

                    // Trigger change event
                    actualSelect.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                    actualSearchInput.blur();
                }
            } else if (e.key === 'Escape') {
                wrapper.classList.remove('active');
                actualSearchInput.blur();
            }
        });

        actualSelect.addEventListener('change', function() {
            const selectedOption = actualSelect.options[actualSelect.selectedIndex];
            const currentValue = actualSelect.value;

            console.log('Change event triggered untuk', actualSelect.name, {
                selectedIndex: actualSelect.selectedIndex,
                currentValue: currentValue,
                selectedOptionValue: selectedOption ? selectedOption.value : null,
                selectedOptionText: selectedOption ? selectedOption.textContent : null
            });

            if (selectedOption && selectedOption.value && selectedOption.value.trim() !== '') {
                // Pastikan value ter-set dengan benar
                actualSelect.value = selectedOption.value;
                actualSearchInput.value = selectedOption.textContent;

                // Set required jika ini adalah nik-select dan sudah enabled
                if (actualSelect.classList.contains('nik-select') && !actualSelect.disabled) {
                    actualSelect.setAttribute('required', 'required');
                    console.log('✓ Change (ForDetail): NIK dipilih, set required untuk', actualSelect.name, ', value:', selectedOption.value);

                    // Verifikasi value ter-set dengan benar
                    setTimeout(() => {
                        const verifyValue = actualSelect.value;
                        if (verifyValue !== selectedOption.value) {
                            console.warn('⚠ Value tidak konsisten setelah change event. Expected:', selectedOption.value, ', Actual:', verifyValue);
                            // Coba set lagi
                            actualSelect.value = selectedOption.value;
                        } else {
                            console.log('✓ Value verified:', verifyValue);
                        }
                    }, 100);
                }
            } else {
                actualSearchInput.value = '';
                // Hapus required jika NIK dikosongkan
                if (actualSelect.classList.contains('nik-select')) {
                    actualSelect.removeAttribute('required');
                    console.log('✗ Change (ForDetail): NIK dikosongkan untuk', actualSelect.name);
                }
            }
        });

        wrapper.dataset.initialized = 'true';
        console.log('initSearchableSelectForDetail: selesai, options count:', actualSelect.options.length);
    }

    function initSearchableSelect(wrapper) {
        const searchInput = wrapper.querySelector('.searchable-select-search');
        const select = wrapper.querySelector('.searchable-select');

        if (!searchInput || !select) return;

        // Hapus event listeners lama jika ada (dengan clone)
        const newSearchInput = searchInput.cloneNode(true);
        const newSelect = select.cloneNode(true);
        searchInput.parentNode.replaceChild(newSearchInput, searchInput);
        select.parentNode.replaceChild(newSelect, select);

        // Update references
        const actualSearchInput = wrapper.querySelector('.searchable-select-search');
        const actualSelect = wrapper.querySelector('.searchable-select');

        actualSearchInput.addEventListener('focus', function() {
            // Pastikan dropdown muncul saat focus
            wrapper.classList.add('active');
            // Filter options berdasarkan value yang ada
            filterOptions(actualSelect, actualSearchInput.value);
            // Jika tidak ada value, tampilkan semua options
            if (!actualSearchInput.value.trim()) {
                const options = actualSelect.options;
                for (let i = 0; i < options.length; i++) {
                    if (options[i].value !== '') {
                        options[i].style.display = '';
                    }
                }
            }
        });

        actualSearchInput.addEventListener('input', function() {
            const term = this.value.trim();
            // Pastikan dropdown selalu muncul saat mengetik
            wrapper.classList.add('active');
            // Filter options berdasarkan term yang diketik
            filterOptions(actualSelect, term);

            // Debug: log untuk memastikan filter berjalan
            const visibleOptions = Array.from(actualSelect.options).filter(opt => opt.style.display !== 'none' && opt.value !== '');
            if (visibleOptions.length === 0 && term) {
                // Jika tidak ada hasil, tetap tampilkan dropdown dengan pesan
                wrapper.classList.add('active');
            }
        });

        actualSelect.addEventListener('click', function(e) {
            if (e.target.tagName === 'OPTION' && e.target.style.display !== 'none') {
                actualSearchInput.value = e.target.textContent;
                wrapper.classList.remove('active');
                actualSelect.value = e.target.value;
                // Trigger change event dengan bubbles untuk memastikan event listener lain ter-trigger
                actualSelect.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
                actualSearchInput.blur();
            }
        });

        // Tambahkan event listener untuk mousedown agar bisa klik option
        actualSelect.addEventListener('mousedown', function(e) {
            if (e.target.tagName === 'OPTION' && e.target.style.display !== 'none') {
                e.preventDefault();
                actualSearchInput.value = e.target.textContent;
                wrapper.classList.remove('active');
                actualSelect.value = e.target.value;
                // Trigger change event dengan bubbles untuk memastikan event listener lain ter-trigger
                actualSelect.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
                actualSearchInput.blur();
            }
        });

        // Close dropdown saat klik di luar
        document.addEventListener('click', function(e) {
            if (!wrapper.contains(e.target)) {
                wrapper.classList.remove('active');
            }
        }, true);

        actualSearchInput.addEventListener('keydown', function(e) {
            const visibleOptions = Array.from(actualSelect.options).filter(opt => opt.style.display !== 'none' && opt.value !== '');
            if (visibleOptions.length === 0) return;

            let currentSelected = visibleOptions.findIndex(opt => opt.selected);
            if (currentSelected < 0 && visibleOptions.length > 0) {
                currentSelected = 0;
                visibleOptions[0].selected = true;
                actualSelect.selectedIndex = visibleOptions[0].index;
            }

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                const nextIndex = (currentSelected + 1) % visibleOptions.length;
                visibleOptions.forEach(opt => opt.selected = false);
                visibleOptions[nextIndex].selected = true;
                actualSelect.selectedIndex = visibleOptions[nextIndex].index;
                // Scroll ke option yang dipilih
                const optionElement = visibleOptions[nextIndex];
                if (optionElement) {
                    optionElement.scrollIntoView({
                        block: 'nearest',
                        behavior: 'smooth'
                    });
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                const prevIndex = currentSelected <= 0 ? visibleOptions.length - 1 : currentSelected - 1;
                visibleOptions.forEach(opt => opt.selected = false);
                visibleOptions[prevIndex].selected = true;
                actualSelect.selectedIndex = visibleOptions[prevIndex].index;
                // Scroll ke option yang dipilih
                const optionElement = visibleOptions[prevIndex];
                if (optionElement) {
                    optionElement.scrollIntoView({
                        block: 'nearest',
                        behavior: 'smooth'
                    });
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (currentSelected >= 0 && visibleOptions[currentSelected]) {
                    const selectedOption = visibleOptions[currentSelected];
                    actualSearchInput.value = selectedOption.textContent;
                    actualSelect.value = selectedOption.value;
                    wrapper.classList.remove('active');
                    // Set required jika ini adalah nik-select dan sudah enabled
                    if (actualSelect.classList.contains('nik-select') && !actualSelect.disabled && selectedOption.value) {
                        actualSelect.setAttribute('required', 'required');
                        console.log('Enter: NIK dipilih, set required untuk', actualSelect.name, ', value:', selectedOption.value);
                    }
                    // Trigger change event dengan bubbles untuk memastikan event listener lain ter-trigger
                    actualSelect.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                    actualSearchInput.blur();
                }
            } else if (e.key === 'Escape') {
                wrapper.classList.remove('active');
                actualSearchInput.blur();
            }
        });

        actualSelect.addEventListener('change', function() {
            const selectedOption = actualSelect.options[actualSelect.selectedIndex];
            if (selectedOption && selectedOption.value) {
                actualSearchInput.value = selectedOption.textContent;
                // Pastikan required di-set saat NIK dipilih (untuk detail row)
                if (actualSelect.classList.contains('nik-select') && !actualSelect.disabled) {
                    actualSelect.setAttribute('required', 'required');
                    console.log('Change event: NIK dipilih, set required untuk', actualSelect.name, ', value:', selectedOption.value);
                }
            } else {
                actualSearchInput.value = '';
                // Hapus required jika NIK dikosongkan (untuk detail row)
                if (actualSelect.classList.contains('nik-select')) {
                    actualSelect.removeAttribute('required');
                }
            }
            // Note: Change event sudah ter-trigger secara otomatis saat value di-set
            // Trigger manual sudah dilakukan di click, mousedown, dan Enter handler
        });
    }

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const safeMessage = String(message || '').replace(/\n/g, '<br>').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/&lt;br&gt;/g, '<br>');
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${safeMessage}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.querySelectorAll('.alert').forEach(a => a.remove());
        const container = document.querySelector('.container-fluid');
        if (container) {
            container.insertAdjacentHTML('afterbegin', alertHtml);
        }
        setTimeout(() => {
            const el = document.querySelector('.alert');
            if (el) el.remove();
        }, 6000);
    }


    function addDetailRow(data = null) {
        const index = detailIndex++;
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 detail-row border-bottom pb-2';
        row.dataset.index = index;

        // Karyawan akan dimuat dinamis berdasarkan bagian yang dipilih
        row.innerHTML = `
            <div class="col-md-3">
                <label class="form-label small">NIK/Nama <span class="text-danger">*</span></label>
                <div class="searchable-select-wrapper">
                    <input type="text" class="form-control form-control-sm searchable-select-search" placeholder="Pilih Departemen terlebih dahulu" autocomplete="off" disabled>
                    <select class="form-select form-select-sm nik-select searchable-select" name="details[${index}][vcNik]" size="1" disabled>
                        <option value="">Pilih Departemen terlebih dahulu</option>
                    </select>
                </div>
                <div class="form-text nama-preview small" style="display:none;"></div>
                <div class="form-text text-muted small">Ketik untuk mencari karyawan aktif</div>
                <input type="hidden" name="details[${index}][vcNamaKaryawan]" class="nama-karyawan-hidden">
                <input type="hidden" class="jabatan-kode" name="details[${index}][vcKodeJabatan]">
            </div>
            <div class="col-md-1">
                <label class="form-label small">Jam Mulai <span class="text-danger">*</span></label>
                <input type="time" class="form-control form-control-sm jam-mulai" name="details[${index}][dtJamMulaiLembur]" step="60" required>
            </div>
            <div class="col-md-1">
                <label class="form-label small">Jam Selesai <span class="text-danger">*</span></label>
                <input type="time" class="form-control form-control-sm jam-selesai" name="details[${index}][dtJamSelesaiLembur]" step="60" required>
            </div>
            <div class="col-md-1">
                <label class="form-label small">Durasi (Jam)</label>
                <input type="number" class="form-control form-control-sm durasi-lembur" name="details[${index}][decDurasiLembur]" step="0.01" min="0" readonly>
            </div>
                    <div class="col-md-1">
                        <label class="form-label small">Istirahat</label>
                        <input type="number" class="form-control form-control-sm durasi-istirahat" name="details[${index}][intDurasiIstirahat]" min="0" value="0" placeholder="Menit">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Deskripsi</label>
                        <input type="text" class="form-control form-control-sm" name="details[${index}][vcDeskripsiLembur]" maxlength="200" placeholder="Contoh: 30 menit">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Penanggung Beban</label>
                        <select class="form-select form-select-sm penanggung-beban" name="details[${index}][vcPenanggungBebanLembur]">
                            <option value="">Pilih</option>
                            <option value="TGI">TGI</option>
                            <option value="SIA-EXP">SIA-EXP</option>
                            <option value="SIA-PROD">SIA-PROD</option>
                            <option value="RMA">RMA</option>
                            <option value="SMU">SMU</option>
                            <option value="ABN-JKT">ABN-JKT</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                        <input type="text" class="form-control form-control-sm penanggung-beban-lainnya mt-1" name="details[${index}][vcPenanggungBebanLainnya]" maxlength="100" placeholder="Sebutkan jika Lainnya..." style="display:none;">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small">Aksi</label>
                        <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeDetailRow(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
        `;

        document.getElementById('detailContainer').appendChild(row);

        // Setup event listeners untuk row ini
        setupDetailRowEvents(row, index, data);

        // Inisialisasi searchable select awal (meskipun select masih kosong)
        // Ini akan memastikan event listener ter-attach sejak awal
        const wrapper = row.querySelector('.searchable-select-wrapper');
        const nikSelect = row.querySelector('.nik-select');
        if (wrapper && nikSelect) {
            // Inisialisasi awal dengan select yang masih kosong
            // Event listener akan tetap berfungsi meskipun options belum ter-load
            setTimeout(() => {
                initSearchableSelectForDetail(wrapper, nikSelect);
            }, 50);
        }
    }

    function setupDetailRowEvents(row, index, data = null) {
        const nikSelect = row.querySelector('.nik-select');
        const namaInput = row.querySelector('.nama-karyawan-hidden');
        const jabatanKode = row.querySelector('.jabatan-kode');
        const namaPreview = row.querySelector('.nama-preview');
        const jamMulai = row.querySelector('.jam-mulai');
        const jamSelesai = row.querySelector('.jam-selesai');
        const durasiLembur = row.querySelector('.durasi-lembur');
        const durasiIstirahat = row.querySelector('.durasi-istirahat');
        const penanggungBeban = row.querySelector('.penanggung-beban');
        const penanggungBebanLainnya = row.querySelector('.penanggung-beban-lainnya');

        // Ambil departemen select dari header (global)
        const departemenSelectHeader = document.getElementById('vcKodeDept');

        // Load karyawan saat departemen dipilih
        function loadKaryawansByDepartemen(callback) {
            // Gunakan departemen select dari header
            const departemenKode = departemenSelectHeader ? departemenSelectHeader.value : '';
            if (!departemenKode) {
                nikSelect.innerHTML = '<option value="">Pilih Departemen terlebih dahulu</option>';
                nikSelect.disabled = true;
                nikSelect.removeAttribute('required'); // Hapus required saat disabled

                // Disable search input juga
                const wrapper = row.querySelector('.searchable-select-wrapper');
                if (wrapper) {
                    const searchInput = wrapper.querySelector('.searchable-select-search');
                    if (searchInput) {
                        searchInput.disabled = true;
                        searchInput.placeholder = 'Pilih Departemen terlebih dahulu';
                        searchInput.value = '';
                    }
                }

                if (callback) callback();
                return;
            }

            nikSelect.disabled = true;
            nikSelect.removeAttribute('required'); // Hapus required saat loading
            nikSelect.innerHTML = '<option value="">Memuat...</option>';

            fetch('/instruksi-kerja-lembur/get-karyawans-by-departemen', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        departemen: departemenKode
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        console.log('Karyawan ter-load untuk row', index, ', jumlah:', result.karyawans.length);
                        nikSelect.innerHTML = '<option value="">Pilih NIK</option>';
                        result.karyawans.forEach(k => {
                            const option = document.createElement('option');
                            option.value = k.Nik;
                            option.textContent = k.Nik + ' - ' + k.Nama;
                            option.setAttribute('data-search', (k.Nik + ' ' + k.Nama).toLowerCase());
                            nikSelect.appendChild(option);
                        });
                        nikSelect.disabled = false;
                        // Set required hanya jika select enabled dan memiliki options (selain placeholder)
                        if (nikSelect.options.length > 1) {
                            nikSelect.setAttribute('required', 'required');
                        } else {
                            nikSelect.removeAttribute('required');
                        }
                        console.log('Select enabled untuk row', index, ', options count:', nikSelect.options.length, ', required:', nikSelect.hasAttribute('required'));

                        // Enable search input dan update placeholder
                        const wrapper = row.querySelector('.searchable-select-wrapper');
                        if (wrapper) {
                            const searchInput = wrapper.querySelector('.searchable-select-search');
                            if (searchInput) {
                                console.log('Enable search input untuk row', index, 'setelah karyawan ter-load');
                                searchInput.disabled = false;
                                searchInput.placeholder = 'Cari NIK atau Nama karyawan aktif...';
                                // Pastikan search input benar-benar enabled
                                searchInput.removeAttribute('disabled');
                            } else {
                                console.warn('Search input tidak ditemukan untuk row', index);
                            }

                            // Tunggu sebentar untuk memastikan DOM sudah ter-update dan options sudah ter-render
                            setTimeout(() => {
                                // Pastikan select sudah memiliki options
                                if (nikSelect.options.length <= 1) {
                                    console.warn('Select belum memiliki options, tunggu lagi...', nikSelect.options.length);
                                    setTimeout(() => {
                                        // Re-inisialisasi dengan options yang sudah ter-load
                                        if (nikSelect.options.length > 1) {
                                            console.log('Re-inisialisasi setelah retry dengan', nikSelect.options.length, 'options');
                                            initSearchableSelectForDetail(wrapper, nikSelect);
                                        } else {
                                            console.error('Select masih belum memiliki options setelah retry');
                                        }
                                    }, 200);
                                    return;
                                }

                                console.log('Re-inisialisasi searchable select dengan', nikSelect.options.length, 'options');
                                // Re-inisialisasi searchable select dengan options yang sudah ter-load
                                initSearchableSelectForDetail(wrapper, nikSelect);
                            }, 200);
                        }

                        // Panggil callback jika ada (untuk set NIK setelah karyawan ter-load)
                        if (callback) callback();
                    } else {
                        nikSelect.innerHTML = '<option value="">Tidak ada data</option>';
                        nikSelect.disabled = true;
                        nikSelect.removeAttribute('required'); // Hapus required saat tidak ada data
                        if (callback) callback();
                    }
                })
                .catch(error => {
                    console.error('Error loading karyawans:', error);
                    nikSelect.innerHTML = '<option value="">Error memuat data</option>';
                    if (callback) callback();
                });
        }

        // Load karyawan saat departemen berubah (dari header)
        // Setiap row memiliki event listener sendiri untuk memastikan semua row ter-trigger
        if (departemenSelectHeader) {
            // Buat handler function yang akan dipanggil saat departemen berubah
            const changeHandler = function() {
                const departemenValue = departemenSelectHeader ? departemenSelectHeader.value : '';
                console.log('Departemen berubah untuk row', index, ', nilai:', departemenValue);
                if (departemenValue) {
                    console.log('Memanggil loadKaryawansByDepartemen untuk row', index);
                    loadKaryawansByDepartemen();
                } else {
                    console.log('Departemen kosong, disable search input untuk row', index);
                    // Disable search input jika departemen dikosongkan
                    const wrapper = row.querySelector('.searchable-select-wrapper');
                    if (wrapper) {
                        const searchInput = wrapper.querySelector('.searchable-select-search');
                        if (searchInput) {
                            searchInput.disabled = true;
                            searchInput.placeholder = 'Pilih Departemen terlebih dahulu';
                            searchInput.value = '';
                        }
                    }
                }
            };

            // Attach event listener (tidak menggunakan once agar bisa dipanggil berkali-kali)
            departemenSelectHeader.addEventListener('change', changeHandler);

            // Simpan handler di row untuk tracking (meskipun tidak bisa di-remove dengan mudah)
            row.dataset.departemenChangeHandler = 'attached';

            // Debug: log untuk memastikan event listener ter-attach
            console.log('Event listener untuk departemen change ter-attach untuk row', index);
        }

        // Jika departemen sudah dipilih saat row dibuat, load karyawan segera
        // Gunakan retry logic untuk memastikan departemen sudah ter-set (termasuk dari auto-fill)
        let checkDepartemenAttempts = 0;
        const maxCheckDepartemenAttempts = 15; // Check sampai 15 kali (3 detik total)
        const checkDepartemenAndLoad = () => {
            checkDepartemenAttempts++;
            if (departemenSelectHeader && departemenSelectHeader.value) {
                console.log('Row', index, '- Departemen ditemukan pada attempt', checkDepartemenAttempts, ', departemen:', departemenSelectHeader.value);
                // Jika ada data NIK yang perlu di-set, gunakan callback
                if (data && data.vcNik) {
                    const nikToSet = data.vcNik; // Simpan NIK yang akan di-set
                    loadKaryawansByDepartemen(function() {
                        // Set NIK setelah karyawan ter-load
                        // Gunakan multiple attempts untuk memastikan options sudah ter-load
                        let attempts = 0;
                        const maxAttempts = 15; // Tambah attempts untuk lebih reliable
                        const checkAndSetNik = () => {
                            attempts++;
                            if (nikSelect.options.length > 1) {
                                // Cari option dengan value yang sesuai
                                const option = Array.from(nikSelect.options).find(opt => opt.value === nikToSet);
                                if (option) {
                                    nikSelect.value = nikToSet;
                                    const searchInput = row.querySelector('.searchable-select-search');
                                    if (searchInput) {
                                        searchInput.value = option.textContent;
                                    }
                                    nikSelect.dispatchEvent(new Event('change'));
                                } else if (attempts < maxAttempts) {
                                    // Jika option belum ada, coba lagi setelah 300ms
                                    setTimeout(checkAndSetNik, 300);
                                }
                            } else if (attempts < maxAttempts) {
                                // Jika options belum ter-load, coba lagi setelah 300ms
                                setTimeout(checkAndSetNik, 300);
                            }
                        };
                        // Mulai check setelah 200ms untuk memberi waktu options ter-render
                        setTimeout(checkAndSetNik, 200);
                    });
                } else {
                    // Tidak ada NIK yang perlu di-set, langsung load karyawan
                    console.log('Row', index, '- Tidak ada NIK yang perlu di-set, langsung load karyawan');
                    loadKaryawansByDepartemen();
                }
            } else if (checkDepartemenAttempts < maxCheckDepartemenAttempts) {
                // Departemen belum dipilih, coba lagi setelah 200ms
                console.log('Row', index, '- Departemen belum dipilih pada attempt', checkDepartemenAttempts, ', akan retry...');
                setTimeout(checkDepartemenAndLoad, 200);
            } else {
                // Sudah max attempts, pastikan search input tetap disabled
                console.log('Row', index, '- Departemen belum dipilih setelah', maxCheckDepartemenAttempts, 'attempts, search input tetap disabled');
                const wrapper = row.querySelector('.searchable-select-wrapper');
                if (wrapper) {
                    const searchInput = wrapper.querySelector('.searchable-select-search');
                    if (searchInput) {
                        searchInput.disabled = true;
                        searchInput.placeholder = 'Pilih Departemen terlebih dahulu';
                    }
                }
            }
        };

        // Mulai check setelah 100ms
        setTimeout(checkDepartemenAndLoad, 100);

        // Event: NIK dipilih
        nikSelect.addEventListener('change', function() {
            const nik = this.value.trim();
            if (!nik) {
                if (namaInput) namaInput.value = '';
                if (jabatanKode) jabatanKode.value = '';
                namaPreview.style.display = 'none';
                // Hapus required jika NIK dikosongkan
                this.removeAttribute('required');
                return;
            }

            // Pastikan select enabled dan memiliki required saat NIK dipilih
            if (!this.disabled) {
                this.setAttribute('required', 'required');
                console.log('NIK dipilih untuk row', index, ', set required:', nik);
            }

            fetch(`/karyawan/${nik}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(result => {
                    if (result.success && result.karyawan) {
                        if (namaInput) namaInput.value = result.karyawan.Nama || '';
                        const kodeJabatan = result.karyawan.Jabat || '';
                        if (jabatanKode) jabatanKode.value = kodeJabatan;
                        namaPreview.textContent = '✓ Valid';
                        namaPreview.style.display = 'block';
                        namaPreview.className = 'form-text nama-preview small text-success';
                    } else {
                        if (namaInput) namaInput.value = '';
                        if (jabatanKode) jabatanKode.value = '';
                        namaPreview.textContent = 'NIK tidak ditemukan';
                        namaPreview.style.display = 'block';
                        namaPreview.className = 'form-text nama-preview small text-danger';
                    }
                })
                .catch(() => {
                    namaPreview.style.display = 'none';
                });
        });

        // Event: Hitung durasi dari jam mulai dan selesai
        function calculateDurasiDetail() {
            const mulai = jamMulai.value;
            const selesai = jamSelesai.value;
            const istirahat = parseInt(durasiIstirahat.value) || 0;

            if (mulai && selesai) {
                const mulaiTime = new Date('2000-01-01T' + mulai + ':00');
                let selesaiTime = new Date('2000-01-01T' + selesai + ':00');

                if (selesaiTime < mulaiTime) {
                    selesaiTime.setDate(selesaiTime.getDate() + 1);
                }

                const diffMs = selesaiTime - mulaiTime;
                const diffMinutes = diffMs / (1000 * 60);
                // Kurangi durasi dengan istirahat (dalam menit)
                const diffHours = (diffMinutes - istirahat) / 60;

                if (diffHours > 0) {
                    durasiLembur.value = diffHours.toFixed(2);
                } else {
                    durasiLembur.value = '0.00';
                }
            } else {
                durasiLembur.value = '';
            }
        }

        jamMulai.addEventListener('change', calculateDurasiDetail);
        jamSelesai.addEventListener('change', calculateDurasiDetail);
        durasiIstirahat.addEventListener('change', calculateDurasiDetail);

        // Event: Penanggung beban lainnya
        penanggungBeban.addEventListener('change', function() {
            if (this.value === 'Lainnya') {
                penanggungBebanLainnya.style.display = 'block';
                penanggungBebanLainnya.required = true;
            } else {
                penanggungBebanLainnya.style.display = 'none';
                penanggungBebanLainnya.required = false;
                penanggungBebanLainnya.value = '';
            }
        });

        // Jika data ada (edit mode) dan bagian belum ter-load saat row dibuat
        // (NIK akan di-set melalui callback di loadKaryawansByDivisi)
        // Tapi jika divisi sudah ter-load, kita perlu set NIK setelah karyawan ter-load
        if (data && data.vcNik) {
            // Jika divisi sudah ter-load, tunggu karyawan ter-load lalu set NIK
            if (divisiSelectHeader && divisiSelectHeader.value) {
                // Divisi sudah ter-load, tunggu karyawan ter-load
                setTimeout(() => {
                    if (nikSelect.options.length > 1) {
                        nikSelect.value = data.vcNik || '';
                        const searchInput = row.querySelector('.searchable-select-search');
                        if (searchInput && nikSelect.selectedIndex >= 0) {
                            const selectedOption = nikSelect.options[nikSelect.selectedIndex];
                            if (selectedOption) {
                                searchInput.value = selectedOption.textContent;
                            }
                        }
                        nikSelect.dispatchEvent(new Event('change'));
                    } else {
                        // Jika belum ter-load, coba lagi
                        setTimeout(() => {
                            if (nikSelect.options.length > 1) {
                                nikSelect.value = data.vcNik || '';
                                const searchInput = row.querySelector('.searchable-select-search');
                                if (searchInput && nikSelect.selectedIndex >= 0) {
                                    const selectedOption = nikSelect.options[nikSelect.selectedIndex];
                                    if (selectedOption) {
                                        searchInput.value = selectedOption.textContent;
                                    }
                                }
                                nikSelect.dispatchEvent(new Event('change'));
                            }
                        }, 1000);
                    }
                }, 1000);
            }
        }

        // Set field lainnya jika ada data
        if (data) {
            if (jamMulai && data.dtJamMulaiLembur) {
                jamMulai.value = data.dtJamMulaiLembur;
            }
            if (jamSelesai && data.dtJamSelesaiLembur) {
                jamSelesai.value = data.dtJamSelesaiLembur;
            }
            if (durasiLembur && data.decDurasiLembur) {
                durasiLembur.value = data.decDurasiLembur;
            }
            if (durasiIstirahat && data.intDurasiIstirahat) {
                durasiIstirahat.value = data.intDurasiIstirahat;
            }
            if (row.querySelector('input[name*="vcDeskripsiLembur"]') && data.vcDeskripsiLembur) {
                row.querySelector('input[name*="vcDeskripsiLembur"]').value = data.vcDeskripsiLembur;
            }
            if (penanggungBeban && data.vcPenanggungBebanLembur) {
                penanggungBeban.value = data.vcPenanggungBebanLembur;
                if (data.vcPenanggungBebanLembur === 'Lainnya') {
                    penanggungBebanLainnya.style.display = 'block';
                    if (data.vcPenanggungBebanLainnya) {
                        penanggungBebanLainnya.value = data.vcPenanggungBebanLainnya;
                    }
                }
            }
        }
    }

    function removeDetailRow(index) {
        document.querySelectorAll('.detail-row').forEach(row => {
            if (row.dataset.index == index) {
                row.remove();
            }
        });
    }

    document.getElementById('btnAddDetail')?.addEventListener('click', () => {
        addDetailRow();
    });

    // Auto-detect jenis lembur saat tanggal berubah
    const dtTanggalLemburInput = document.getElementById('dtTanggalLembur');
    const vcJenisLemburInput = document.getElementById('vcJenisLembur');

    if (dtTanggalLemburInput && vcJenisLemburInput) {
        dtTanggalLemburInput.addEventListener('change', function() {
            const tanggal = this.value;
            if (tanggal) {
                checkJenisLembur(tanggal);
            } else {
                vcJenisLemburInput.value = '';
            }
        });
    }

    function checkJenisLembur(tanggal) {
        fetch('{{ route("instruksi-kerja-lembur.check-jenis-lembur") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    tanggal: tanggal
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && vcJenisLemburInput) {
                    vcJenisLemburInput.value = data.jenisLembur || '';
                }
            })
            .catch(error => {
                console.error('Error checking jenis lembur:', error);
            });
    }

    document.getElementById('addBtn').addEventListener('click', () => {
        isEditMode = false;
        currentId = null;
        detailIndex = 0;
        document.getElementById('lemburModalLabel').textContent = 'Tambah Instruksi Kerja Lembur';
        document.getElementById('lemburForm').reset();
        document.getElementById('_method').value = 'POST';
        document.getElementById('detailContainer').innerHTML = '';

        // Reset hierarchical dropdowns
        divisiSelect.value = '';
        departemenSelect.innerHTML = '<option value="">Pilih Departemen</option>';
        departemenSelect.disabled = true;
        departemenSelect.required = false;
        bagianSelect.innerHTML = '<option value="">Pilih Departemen terlebih dahulu</option>';
        bagianSelect.disabled = true;
        bagianSelect.required = false;

        // Reset field lainnya
        document.getElementById('vcJenisLembur').value = '';
        document.getElementById('vcDiajukanOleh').value = '';
        const diajukanOlehSearch = document.getElementById('vcDiajukanOleh_search');
        if (diajukanOlehSearch) {
            diajukanOlehSearch.value = '';
        }
        const jabatanPengajuDisplay = document.getElementById('vcJabatanPengaju_display');
        const jabatanPengajuInput = document.getElementById('vcJabatanPengaju');
        if (jabatanPengajuDisplay) jabatanPengajuDisplay.value = '';
        if (jabatanPengajuInput) jabatanPengajuInput.value = '';
        const kepalaDeptInput = document.getElementById('vcKepalaDept');
        if (kepalaDeptInput) kepalaDeptInput.value = '';

        // Set default tanggal = hari ini
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        document.getElementById('dtTanggalLembur').value = `${yyyy}-${mm}-${dd}`;

        // Auto-detect jenis lembur untuk tanggal default
        checkJenisLembur(`${yyyy}-${mm}-${dd}`);

        // Tambah satu baris detail kosong
        addDetailRow();

        // Tampilkan modal
        const modal = new bootstrap.Modal(document.getElementById('lemburModal'));
        modal.show();
    });

    document.getElementById('lemburForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Hapus required dari semua select yang disabled atau belum ter-load untuk menghindari error validasi
        let hasValidRow = false;
        const invalidRows = [];

        document.querySelectorAll('.detail-row').forEach((row, idx) => {
            const nikSelect = row.querySelector('.nik-select');
            const jamMulai = row.querySelector('.jam-mulai');
            const jamSelesai = row.querySelector('.jam-selesai');

            // Validasi NIK
            if (nikSelect) {
                // Ambil value dari select (bisa dari value atau selectedIndex)
                let nikValue = nikSelect.value;

                // Jika value kosong, coba ambil dari selectedIndex
                if (!nikValue || nikValue.trim() === '') {
                    const selectedOption = nikSelect.options[nikSelect.selectedIndex];
                    if (selectedOption && selectedOption.value) {
                        nikValue = selectedOption.value;
                        // Update value select untuk memastikan konsistensi
                        nikSelect.value = nikValue;
                    }
                }

                const hasValue = nikValue && nikValue.trim() !== '';
                const isEnabled = !nikSelect.disabled;

                console.log('Validasi row', idx, ':', {
                    name: nikSelect.name,
                    value: nikValue,
                    hasValue: hasValue,
                    isEnabled: isEnabled,
                    disabled: nikSelect.disabled,
                    selectedIndex: nikSelect.selectedIndex,
                    optionsCount: nikSelect.options.length
                });

                // Select dianggap valid jika: enabled, memiliki value, dan value bukan empty string
                if (isEnabled && hasValue && nikValue !== '') {
                    // Pastikan select yang valid memiliki required
                    nikSelect.setAttribute('required', 'required');

                    // Validasi Jam Mulai dan Jam Selesai
                    const jamMulaiValue = jamMulai ? jamMulai.value.trim() : '';
                    const jamSelesaiValue = jamSelesai ? jamSelesai.value.trim() : '';

                    if (!jamMulaiValue) {
                        jamMulai.setAttribute('required', 'required');
                        invalidRows.push({
                            index: idx,
                            name: nikSelect.name,
                            reason: 'Jam Mulai harus diisi',
                            value: nikValue,
                            enabled: isEnabled
                        });
                        console.log('✗ Invalid row:', idx, '- Jam Mulai harus diisi');
                        return; // Skip row ini
                    }

                    if (!jamSelesaiValue) {
                        jamSelesai.setAttribute('required', 'required');
                        invalidRows.push({
                            index: idx,
                            name: nikSelect.name,
                            reason: 'Jam Selesai harus diisi',
                            value: nikValue,
                            enabled: isEnabled
                        });
                        console.log('✗ Invalid row:', idx, '- Jam Selesai harus diisi');
                        return; // Skip row ini
                    }

                    // Jika semua valid, hapus required dari jam (karena sudah diisi)
                    if (jamMulai) jamMulai.removeAttribute('required');
                    if (jamSelesai) jamSelesai.removeAttribute('required');

                    hasValidRow = true;
                    console.log('✓ Valid row:', idx, {
                        nik: nikValue,
                        jamMulai: jamMulaiValue,
                        jamSelesai: jamSelesaiValue
                    });
                } else {
                    // Hapus required jika select tidak valid
                    nikSelect.removeAttribute('required');
                    invalidRows.push({
                        index: idx,
                        name: nikSelect.name,
                        reason: !isEnabled ? 'disabled' : (!hasValue ? 'no value' : 'empty value'),
                        value: nikValue,
                        enabled: isEnabled
                    });
                    console.log('✗ Invalid select:', nikSelect.name, {
                        disabled: !isEnabled,
                        hasValue: hasValue,
                        value: nikValue,
                        reason: !isEnabled ? 'disabled' : (!hasValue ? 'no value' : 'empty value')
                    });
                }
            } else {
                invalidRows.push({
                    index: idx,
                    name: 'N/A',
                    reason: 'nikSelect not found'
                });
            }
        });

        const detailRows = document.querySelectorAll('.detail-row');
        if (detailRows.length === 0) {
            showAlert('error', 'Minimal harus ada 1 karyawan dalam detail');
            return;
        }

        if (!hasValidRow) {
            console.error('Tidak ada row yang valid. Invalid rows:', invalidRows);
            const errorMessages = [];
            invalidRows.forEach(row => {
                if (row.reason === 'Jam Mulai harus diisi') {
                    errorMessages.push(`Row ${row.index + 1}: Jam Mulai harus diisi`);
                } else if (row.reason === 'Jam Selesai harus diisi') {
                    errorMessages.push(`Row ${row.index + 1}: Jam Selesai harus diisi`);
                }
            });

            let errorMsg = 'Minimal harus ada 1 karyawan yang valid dalam detail. ';
            if (errorMessages.length > 0) {
                errorMsg += errorMessages.join(', ') + '. ';
            }
            errorMsg += 'Pastikan Departemen sudah dipilih, karyawan sudah ter-load, NIK sudah dipilih, dan Jam Mulai serta Jam Selesai sudah diisi.';

            showAlert('error', errorMsg);
            return;
        }

        const url = isEditMode ? `/instruksi-kerja-lembur/${currentId}` : '/instruksi-kerja-lembur';
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
            .then(r => {
                if (!r.ok) {
                    return r.json().then(err => {
                        throw new Error(err.message || 'HTTP Error: ' + r.status);
                    });
                }
                return r.json();
            })
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('lemburModal')).hide();
                    showAlert('success', data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    bootstrap.Modal.getInstance(document.getElementById('lemburModal')).hide();
                    showAlert('error', data.message || 'Gagal menyimpan data');
                }
            })
            .catch(err => {
                console.error(err);
                bootstrap.Modal.getInstance(document.getElementById('lemburModal')).hide();
                showAlert('error', err.message || 'Terjadi kesalahan saat menyimpan');
            });
    });

    function editRecord(id) {
        fetch(`/instruksi-kerja-lembur/${id}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    isEditMode = true;
                    currentId = id;
                    detailIndex = 0;
                    document.getElementById('lemburModalLabel').textContent = 'Edit Instruksi Kerja Lembur';
                    document.getElementById('_method').value = 'PUT';

                    const rec = data.record;

                    // Set divisi first, then trigger change to load departemens
                    if (rec.vcKodeDivisi) {
                        divisiSelect.value = rec.vcKodeDivisi;

                        // Load departemens dengan callback untuk set value setelah ter-load
                        loadDepartemens(rec.vcKodeDivisi, function() {
                            // Set departemen setelah departemen ter-load
                            if (rec.vcKodeDept) {
                                departemenSelect.value = rec.vcKodeDept;

                                // Load kepala departemen setelah departemen ter-set
                                if (rec.vcKodeDept) {
                                    loadKepalaDept(rec.vcKodeDept);
                                }

                                // Load bagians dengan callback untuk set value setelah ter-load
                                loadBagians(rec.vcKodeDivisi, rec.vcKodeDept, function() {
                                    // Set bagian setelah bagian ter-load
                                    if (rec.vcKodeBagian) {
                                        bagianSelect.value = rec.vcKodeBagian;
                                        // Trigger change untuk load karyawan
                                        bagianSelect.dispatchEvent(new Event('change'));
                                    }
                                });
                            }
                        });
                    }

                    document.getElementById('dtTanggalLembur').value = rec.dtTanggalLembur;
                    document.getElementById('vcJenisLembur').value = rec.vcJenisLembur || '';
                    document.getElementById('vcAlasanDasarLembur').value = rec.vcAlasanDasarLembur || '';

                    // Set Jabatan Pengaju
                    const jabatanPengajuDisplay = document.getElementById('vcJabatanPengaju_display');
                    const jabatanPengajuInput = document.getElementById('vcJabatanPengaju');
                    if (jabatanPengajuInput && rec.vcJabatanPengaju) {
                        jabatanPengajuInput.value = rec.vcJabatanPengaju;
                        // Set nama jabatan dari data yang sudah ada
                        if (jabatanPengajuDisplay) {
                            jabatanPengajuDisplay.value = rec.vcJabatanPengajuNama || rec.vcJabatanPengaju;
                        }
                    }

                    // Set Kepala Departemen
                    const kepalaDeptInput = document.getElementById('vcKepalaDept');
                    if (kepalaDeptInput && rec.vcKepalaDept) {
                        kepalaDeptInput.value = rec.vcKepalaDept;
                    }

                    // Set "Diajukan Oleh" dengan update search input juga
                    const diajukanOlehSelect = document.getElementById('vcDiajukanOleh');
                    const diajukanOlehSearch = document.getElementById('vcDiajukanOleh_search');
                    if (diajukanOlehSelect && rec.vcDiajukanOleh) {
                        diajukanOlehSelect.value = rec.vcDiajukanOleh;
                        // Update search input dengan text dari option yang dipilih
                        const selectedOption = diajukanOlehSelect.options[diajukanOlehSelect.selectedIndex];
                        if (selectedOption && selectedOption.value) {
                            if (diajukanOlehSearch) {
                                diajukanOlehSearch.value = selectedOption.textContent;
                            }
                        }
                        // Trigger change event untuk memastikan search input ter-update dan auto-fill jabatan
                        diajukanOlehSelect.dispatchEvent(new Event('change'));
                    }

                    // Clear detail container
                    document.getElementById('detailContainer').innerHTML = '';

                    // Simpan detail data untuk di-load setelah bagian ter-load
                    const detailDataToLoad = rec.details && rec.details.length > 0 ? rec.details : [];

                    // Function untuk load detail setelah bagian dan karyawan ter-load
                    function loadDetailsAfterKaryawanLoaded() {
                        if (detailDataToLoad.length > 0) {
                            detailDataToLoad.forEach((detail, idx) => {
                                // Tambahkan delay kecil untuk setiap row agar tidak conflict
                                setTimeout(() => {
                                    addDetailRow({
                                        vcNik: detail.vcNik,
                                        vcNamaKaryawan: detail.vcNamaKaryawan,
                                        vcKodeJabatan: detail.vcKodeJabatan,
                                        dtJamMulaiLembur: detail.dtJamMulaiLembur,
                                        dtJamSelesaiLembur: detail.dtJamSelesaiLembur,
                                        decDurasiLembur: detail.decDurasiLembur,
                                        intDurasiIstirahat: detail.intDurasiIstirahat,
                                        vcDeskripsiLembur: detail.vcDeskripsiLembur,
                                        vcPenanggungBebanLembur: detail.vcPenanggungBebanLembur,
                                        vcPenanggungBebanLainnya: detail.vcPenanggungBebanLainnya
                                    });
                                }, idx * 100); // Delay 100ms per row
                            });
                        } else {
                            addDetailRow();
                        }
                    }

                    // Jika bagian sudah di-set, tunggu bagian ter-load dan trigger load karyawan, baru load detail
                    // Tunggu divisi ter-load sebelum load detail rows
                    // Pastikan divisi sudah ter-set dan karyawan sudah ter-load untuk semua row
                    if (rec.vcKodeDivisi) {
                        // Tunggu divisi ter-set dan karyawan ter-load
                        // Karena divisi sudah di-set, kita perlu delay untuk memastikan karyawan ter-load
                        setTimeout(() => {
                            loadDetailsAfterKaryawanLoaded();
                        }, 1500);
                    } else {
                        // Jika tidak ada divisi, langsung load detail (tapi akan disabled)
                        loadDetailsAfterKaryawanLoaded();
                    }

                    new bootstrap.Modal(document.getElementById('lemburModal')).show();
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('error', 'Gagal memuat data');
            });
    }

    function viewRecord(id) {
        fetch(`/instruksi-kerja-lembur/${id}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const rec = data.record;
                    let html = `
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white"><strong>Header</strong></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6"><strong>Counter:</strong> ${rec.vcCounter}</div>
                                    <div class="col-md-6"><strong>Tanggal:</strong> ${rec.dtTanggalLembur}</div>
                                    <div class="col-md-6"><strong>Divisi:</strong> ${rec.vcKodeDivisi || '-'}</div>
                                    <div class="col-md-6"><strong>Departemen:</strong> ${rec.vcKodeDept || '-'}</div>
                                    <div class="col-md-6"><strong>Bagian:</strong> ${rec.vcKodeBagian || '-'}</div>
                                    <div class="col-md-6"><strong>Diajukan Oleh:</strong> ${rec.vcDiajukanOleh || '-'}</div>
                                    <div class="col-12"><strong>Alasan:</strong> ${rec.vcAlasanDasarLembur || '-'}</div>
                                    <div class="col-md-6"><strong>Rencana Durasi:</strong> ${rec.decRencanaDurasiJam || '-'} jam</div>
                                    <div class="col-md-6"><strong>Pukul:</strong> ${rec.dtRencanaDariPukul || '-'} s/d ${rec.dtRencanaSampaiPukul || '-'}</div>
                                    <div class="col-12"><strong>Penanggung Biaya:</strong> ${rec.vcPenanggungBiaya || '-'} ${rec.vcPenanggungBiayaLainnya ? '(' + rec.vcPenanggungBiayaLainnya + ')' : ''}</div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header bg-success text-white"><strong>Detail Karyawan</strong></div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>NIK</th>
                                                <th>Nama</th>
                                                <th>Jam Mulai</th>
                                                <th>Jam Selesai</th>
                                                <th>Durasi (Jam)</th>
                                                <th>Istirahat (Menit)</th>
                                                <th>Deskripsi</th>
                                                <th>Penanggung Beban</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                    `;

                    if (rec.details && rec.details.length > 0) {
                        rec.details.forEach(detail => {
                            html += `
                                <tr>
                                    <td>${detail.vcNik}</td>
                                    <td>${detail.vcNamaKaryawan}</td>
                                    <td>${detail.dtJamMulaiLembur || '-'}</td>
                                    <td>${detail.dtJamSelesaiLembur || '-'}</td>
                                    <td>${detail.decDurasiLembur || '-'}</td>
                                    <td>${detail.intDurasiIstirahat || '0'}</td>
                                    <td>${detail.vcDeskripsiLembur || '-'}</td>
                                    <td>${detail.vcPenanggungBebanLembur || '-'} ${detail.vcPenanggungBebanLainnya ? '(' + detail.vcPenanggungBebanLainnya + ')' : ''}</td>
                                </tr>
                            `;
                        });
                    } else {
                        html += '<tr><td colspan="8" class="text-center">Tidak ada data</td></tr>';
                    }

                    html += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;

                    document.getElementById('viewModalBody').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('viewModal')).show();
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('error', 'Gagal memuat data');
            });
    }

    function deleteRecord(id) {
        if (!confirm('Hapus data instruksi kerja lembur ini? Semua detail akan ikut terhapus.')) return;
        fetch(`/instruksi-kerja-lembur/${id}`, {
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

    // Make removeDetailRow globally accessible
    window.removeDetailRow = removeDetailRow;

    // Initialize searchable select on page load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.searchable-select-wrapper').forEach(wrapper => {
            initSearchableSelect(wrapper);
        });

        // Pastikan event listener untuk "Diajukan Oleh" tetap berfungsi setelah searchable select diinisialisasi
        // Gunakan event delegation pada document untuk memastikan event tetap ter-trigger
        // meskipun element di-clone oleh initSearchableSelect
        document.addEventListener('change', function(e) {
            if (e.target && e.target.id === 'vcDiajukanOleh') {
                const nik = e.target.value.trim();
                if (!nik) {
                    // Clear semua field jika NIK kosong
                    if (divisiSelect) divisiSelect.value = '';
                    if (departemenSelect) departemenSelect.value = '';
                    if (bagianSelect) bagianSelect.value = '';
                    const jabatanPengajuDisplay = document.getElementById('vcJabatanPengaju_display');
                    const jabatanPengajuInput = document.getElementById('vcJabatanPengaju');
                    const kepalaDeptInput = document.getElementById('vcKepalaDept');
                    if (jabatanPengajuDisplay) jabatanPengajuDisplay.value = '';
                    if (jabatanPengajuInput) jabatanPengajuInput.value = '';
                    if (kepalaDeptInput) kepalaDeptInput.value = '';
                    return;
                }

                // Fetch data karyawan untuk mendapatkan Divisi, Departemen, Bagian
                fetch('{{ route("instruksi-kerja-lembur.get-karyawan-data") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            nik: nik
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.karyawan) {
                            const karyawan = data.karyawan;

                            // Auto-fill Jabatan Pengaju
                            const jabatanPengajuDisplay = document.getElementById('vcJabatanPengaju_display');
                            const jabatanPengajuInput = document.getElementById('vcJabatanPengaju');
                            if (jabatanPengajuDisplay && karyawan.namaJabatan) {
                                jabatanPengajuDisplay.value = karyawan.namaJabatan;
                            }
                            if (jabatanPengajuInput && karyawan.jabatan) {
                                jabatanPengajuInput.value = karyawan.jabatan;
                            }

                            // Auto-fill Divisi, Departemen, Bagian berdasarkan data karyawan yang dipilih
                            if (karyawan.divisi && divisiSelect) {
                                // Set divisi
                                divisiSelect.value = karyawan.divisi;

                                // Trigger change event untuk memastikan event listener ter-trigger
                                divisiSelect.dispatchEvent(new Event('change', {
                                    bubbles: true
                                }));

                                // Load departemens dengan callback untuk set departemen setelah ter-load
                                if (karyawan.departemen) {
                                    loadDepartemens(karyawan.divisi, function() {
                                        // Set departemen setelah departemens ter-load
                                        if (departemenSelect && karyawan.departemen) {
                                            departemenSelect.value = karyawan.departemen;

                                            // Load kepala departemen setelah departemen ter-set
                                            loadKepalaDept(karyawan.departemen);

                                            // Trigger change event untuk departemen agar karyawan ter-load di detail rows
                                            departemenSelect.dispatchEvent(new Event('change', {
                                                bubbles: true
                                            }));

                                            // Load bagians dengan callback untuk set bagian setelah ter-load
                                            if (karyawan.bagian) {
                                                loadBagians(karyawan.divisi, karyawan.departemen, function() {
                                                    // Set bagian setelah bagians ter-load
                                                    if (bagianSelect && karyawan.bagian) {
                                                        bagianSelect.value = karyawan.bagian;
                                                        // Trigger change untuk load karyawan di detail (jika diperlukan)
                                                        bagianSelect.dispatchEvent(new Event('change'));
                                                    }
                                                });
                                            }
                                        }
                                    });
                                } else {
                                    // Jika tidak ada departemen, tetap load departemens
                                    loadDepartemens(karyawan.divisi);
                                }
                            }
                        } else {
                            console.error('Gagal memuat data karyawan:', data);
                        }
                    })
                    .catch(error => {
                        console.error('Error loading karyawan data:', error);
                    });
            }
        });
    });

    // Auto submit filter
    document.getElementById('dari_tanggal')?.addEventListener('change', () => document.getElementById('filterForm').submit());
    document.getElementById('sampai_tanggal')?.addEventListener('change', () => document.getElementById('filterForm').submit());
</script>
@endpush