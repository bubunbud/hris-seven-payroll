@extends('layouts.app')

@section('title', 'Instruksi Kerja Lembur - HRIS Seven Payroll')

@push('styles')
<link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/webfonts/fa-solid-900.woff2" as="font" type="font/woff2" crossorigin>
@endpush

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
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary" onclick="editRecord('{{ $row->vcCounter }}')" title="Edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-outline-info" onclick="viewRecord('{{ $row->vcCounter }}')" title="Detail">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" onclick="deleteRecord('{{ $row->vcCounter }}')" title="Hapus">
                                                <i class="fas fa-trash"></i> Delete
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

<style>
    /* Pastikan detail row tidak wrap dan lebih compact */
    .detail-row {
        flex-wrap: nowrap !important;
    }

    .detail-row .col-md-1,
    .detail-row .col-md-2,
    .detail-row .col-md-3 {
        padding-left: 0.25rem;
        padding-right: 0.25rem;
    }

    .detail-row .form-label {
        font-size: 0.75rem;
        margin-bottom: 0.25rem;
    }

    .detail-row .form-control-sm,
    .detail-row .form-select-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    @media (min-width: 768px) {
        .detail-row .col-deskripsi-wide {
            flex: 0 0 17%;
            max-width: 17%;
        }

        .detail-row .col-penanggung-shrink,
        .detail-row .col-nominal-shrink {
            flex: 0 0 11.5%;
            max-width: 11.5%;
        }

        .detail-row .col-durasi-shrink {
            flex: 0 0 7%;
            max-width: 7%;
        }

        .detail-row .col-istirahat-shrink {
            flex: 0 0 7%;
            max-width: 7%;
        }
    }

    /* Icon untuk tombol aksi */
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .btn-group-sm .btn i {
        margin-right: 0.25rem;
    }
</style>

<!-- Modal Form -->
<div class="modal fade" id="lemburModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lemburModalLabel">Tambah Instruksi Kerja Lembur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="lemburForm" novalidate>
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

                                <!-- Row 2.5: Free Role Checkbox -->
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="freeRoleEnabled" name="freeRoleEnabled" value="1">
                                        <label class="form-check-label" for="freeRoleEnabled">
                                            <strong>Free Role Enabled</strong> - Bebas memberikan instruksi lembur kepada karyawan dari divisi/departemen/bagian manapun tanpa pembatasan
                                        </label>
                                    </div>
                                    <small class="text-muted">Jika dicentang, kolom detail dapat memilih karyawan dari semua divisi/departemen/bagian, tidak terbatas pada divisi/departemen/bagian yang dipilih di header.</small>
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
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header pb-2">
                <h5 class="modal-title">Detail Instruksi Kerja Lembur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="viewModalBody"></div>
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
    // Base path untuk subfolder support
    const fullUrl = '{{ url("/") }}';
    // Hanya ambil path (tanpa domain/port) agar kompatibel di localhost dan server
    const basePath = fullUrl.replace(/^https?:\/\/[^\/]+/, '') || '';
    // // console.log disabled for performance // Disabled for performance

    function makeUrl(path) {
        const cleanPath = path.startsWith('/') ? path.substring(1) : path;
        if (!basePath) {
            return `/${cleanPath}`;
        }
        const cleanBase = basePath.endsWith('/') ? basePath.slice(0, -1) : basePath;
        return `${cleanBase}/${cleanPath}`;
    }

    let isEditMode = false;
    let currentId = null;
    let detailIndex = 0;

    // Hierarchical dropdown elements
    const divisiSelect = document.getElementById('vcKodeDivisi');
    const departemenSelect = document.getElementById('vcKodeDept');
    const bagianSelect = document.getElementById('vcKodeBagian');
    const freeRoleCheckbox = document.getElementById('freeRoleEnabled');

    // Global functions untuk onclick handlers
    function editRecord(id) {
        if (!id) {
            console.error('editRecord: ID tidak valid', id);
            if (typeof showAlert === 'function') {
                showAlert('error', 'ID tidak valid');
            } else {
                alert('ID tidak valid');
            }
            return;
        }

        // // console.log disabled for performance // Disabled for performance
        
        fetch(makeUrl(`instruksi-kerja-lembur/${id}`), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(r => {
                if (!r.ok) {
                    throw new Error(`HTTP error! status: ${r.status}`);
                }
                return r.json();
            })
            .then(data => {
                // // console.log disabled for performance // Disabled for performance
                if (data.success) {
                    isEditMode = true;
                    currentId = id;
                    detailIndex = 0;
                    document.getElementById('lemburModalLabel').textContent = 'Edit Instruksi Kerja Lembur';
                    document.getElementById('_method').value = 'PUT';

                    const rec = data.record;

                    if (freeRoleCheckbox) {
                        freeRoleCheckbox.checked = !!rec.is_free_role;
                        freeRoleCheckbox.dispatchEvent(new Event('change'));
                    }

                    // Set divisi first, then trigger change to load departemens
                    if (rec.vcKodeDivisi && divisiSelect) {
                        divisiSelect.value = rec.vcKodeDivisi;

                        // Load departemens dengan callback untuk set value setelah ter-load
                        if (typeof loadDepartemens === 'function') {
                            loadDepartemens(rec.vcKodeDivisi, function() {
                                // Set departemen setelah departemen ter-load
                                if (rec.vcKodeDept && departemenSelect) {
                                    departemenSelect.value = rec.vcKodeDept;

                                    // Load kepala departemen setelah departemen ter-set
                                    if (rec.vcKodeDept && typeof loadKepalaDept === 'function') {
                                        loadKepalaDept(rec.vcKodeDept);
                                    }

                                    // Load bagians dengan callback untuk set value setelah ter-load
                                    if (typeof loadBagians === 'function') {
                                        loadBagians(rec.vcKodeDivisi, rec.vcKodeDept, function() {
                                            // Set bagian setelah bagian ter-load
                                            if (rec.vcKodeBagian && bagianSelect) {
                                                bagianSelect.value = rec.vcKodeBagian;
                                                // Trigger change untuk load karyawan
                                                bagianSelect.dispatchEvent(new Event('change'));
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    }

                    const dtTanggalLembur = document.getElementById('dtTanggalLembur');
                    const vcJenisLembur = document.getElementById('vcJenisLembur');
                    const vcAlasanDasarLembur = document.getElementById('vcAlasanDasarLembur');
                    if (dtTanggalLembur) dtTanggalLembur.value = rec.dtTanggalLembur || '';
                    if (vcJenisLembur) vcJenisLembur.value = rec.vcJenisLembur || '';
                    if (vcAlasanDasarLembur) vcAlasanDasarLembur.value = rec.vcAlasanDasarLembur || '';

                    // Set Jabatan Pengaju
                    const jabatanPengajuDisplay = document.getElementById('vcJabatanPengaju_display');
                    const jabatanPengajuInput = document.getElementById('vcJabatanPengaju');
                    if (jabatanPengajuInput) {
                        jabatanPengajuInput.value = rec.vcJabatanPengaju || '';
                    }
                    if (jabatanPengajuDisplay) {
                        jabatanPengajuDisplay.value = rec.vcJabatanPengajuNama ? `${rec.vcJabatanPengajuNama} (${rec.vcJabatanPengaju || '-'})` : (rec.vcJabatanPengaju || '');
                    }

                    // Set Kepala Departemen
                    const kepalaDeptInput = document.getElementById('vcKepalaDept');
                    if (kepalaDeptInput && rec.vcKepalaDept) {
                        kepalaDeptInput.value = rec.vcKepalaDept;
                    }

                    // Set "Diajukan Oleh" dengan update search input juga
                    const diajukanOlehSelect = document.getElementById('vcDiajukanOleh');
                    const diajukanOlehSearch = document.getElementById('vcDiajukanOleh_search');
                    if (diajukanOlehSelect) {
                        diajukanOlehSelect.value = rec.vcDiajukanOleh || '';
                        // Trigger supaya hook pengisian otomatis tetap berjalan
                        diajukanOlehSelect.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    }
                    if (diajukanOlehSearch) {
                        diajukanOlehSearch.value = rec.vcDiajukanOleh ?
                            `${rec.vcDiajukanOleh}${rec.namaPengaju ? ' - ' + rec.namaPengaju : ''}` :
                            '';
                    }

                    // Clear detail container dan reset detailIndex
                    const detailContainer = document.getElementById('detailContainer');
                    if (detailContainer) {
                        detailContainer.innerHTML = '';
                    }
                    // Reset detailIndex untuk memastikan index dimulai dari 0
                    detailIndex = 0;

                    // Simpan detail data untuk di-load setelah bagian ter-load
                    const detailDataToLoad = rec.details && rec.details.length > 0 ? rec.details : [];

                    // Function untuk load detail setelah bagian dan karyawan ter-load
                    function loadDetailsAfterKaryawanLoaded() {
                        if (detailDataToLoad.length > 0) {
                            detailDataToLoad.forEach((detail, idx) => {
                                // Tambahkan delay kecil untuk setiap row agar tidak conflict
                                setTimeout(() => {
                                    if (typeof addDetailRow === 'function') {
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
                                            vcPenanggungBebanLainnya: detail.vcPenanggungBebanLainnya,
                                            decLemburExternal: detail.decLemburExternal
                                        });
                                    }
                                }, idx * 100); // Delay 100ms per row
                            });
                        } else {
                            if (typeof addDetailRow === 'function') {
                                addDetailRow();
                            }
                        }
                    }

                    // Jika bagian sudah di-set, tunggu bagian ter-load dan trigger load karyawan, baru load detail
                    // Tunggu divisi ter-load sebelum load detail rows
                    // Pastikan divisi sudah ter-set dan karyawan sudah ter-load untuk semua row
                    if (rec.vcKodeDivisi) {
                        const detailLoadDelay = rec.is_free_role ? 400 : 1500;
                        setTimeout(() => {
                            loadDetailsAfterKaryawanLoaded();
                        }, detailLoadDelay);
                    } else {
                        // Jika tidak ada divisi, langsung load detail (tapi akan disabled)
                        loadDetailsAfterKaryawanLoaded();
                    }

                    const lemburModal = document.getElementById('lemburModal');
                    if (lemburModal) {
                        new bootstrap.Modal(lemburModal).show();
                    }
                } else {
                    console.error('editRecord: Response tidak berhasil', data);
                    if (typeof showAlert === 'function') {
                        showAlert('error', data.message || 'Gagal memuat data');
                    } else {
                        alert(data.message || 'Gagal memuat data');
                    }
                }
            })
            .catch(err => {
                console.error('editRecord: Error', err);
                if (typeof showAlert === 'function') {
                    showAlert('error', 'Gagal memuat data: ' + err.message);
                } else {
                    alert('Gagal memuat data: ' + err.message);
                }
            });
    }

    function viewRecord(id) {
        fetch(makeUrl(`instruksi-kerja-lembur/${id}`), {
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
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <div><strong>Informasi Umum</strong></div>
                                ${rec.is_free_role ? '<span class="badge bg-warning text-dark">Free Role</span>' : ''}
                            </div>
                            <div class="card-body">
                                <div class="row gy-3">
                                    <div class="col-md-4">
                                        <div class="text-muted text-uppercase small fw-semibold">Kode Lembur</div>
                                        <div class="fs-5 fw-semibold">${rec.vcCounter || '-'}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-muted text-uppercase small fw-semibold">Tanggal</div>
                                        <div class="fs-5">${rec.dtTanggalLembur || '-'}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-muted text-uppercase small fw-semibold">Jenis Lembur</div>
                                        <div class="fs-5">${rec.vcJenisLembur || '-'}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-muted text-uppercase small fw-semibold">Divisi</div>
                                        <div class="fw-semibold">${rec.vcKodeDivisi ? `${rec.vcKodeDivisi}${rec.namaDivisi ? ' - ' + rec.namaDivisi : ''}` : '-'}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-muted text-uppercase small fw-semibold">Departemen</div>
                                        <div class="fw-semibold">${rec.vcKodeDept ? `${rec.vcKodeDept}${rec.namaDept ? ' - ' + rec.namaDept : ''}` : '-'}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-muted text-uppercase small fw-semibold">Bagian</div>
                                        <div class="fw-semibold">${rec.vcKodeBagian ? `${rec.vcKodeBagian}${rec.namaBagian ? ' - ' + rec.namaBagian : ''}` : '-'}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted text-uppercase small fw-semibold">Diajukan Oleh</div>
                                        <div class="fw-semibold mb-1">${rec.vcDiajukanOleh ? `${rec.vcDiajukanOleh}${rec.namaPengaju ? ' - ' + rec.namaPengaju : ''}` : '-'}</div>
                                        ${rec.vcJabatanPengajuNama ? `<small class="text-muted">Jabatan: ${rec.vcJabatanPengajuNama}</small>` : ''}
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted text-uppercase small fw-semibold">Kepala Departemen</div>
                                        <div class="fw-semibold">${rec.vcKepalaDept || '-'}</div>
                                    </div>
                                    <div class="col-12">
                                        <div class="text-muted text-uppercase small fw-semibold">Alasan / Dasar Lembur</div>
                                        <div class="border rounded p-3 bg-light">${rec.vcAlasanDasarLembur || '-'}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header bg-success text-white"><strong>Detail Karyawan</strong></div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="min-width:110px;">NIK</th>
                                                <th style="min-width:160px;">Nama</th>
                                                <th>Jam Mulai</th>
                                                <th>Jam Selesai</th>
                                                <th>Durasi (Jam)</th>
                                                <th>Istirahat (Menit)</th>
                                                <th style="min-width:200px;">Deskripsi</th>
                                                <th style="min-width:180px;">Penanggung Beban</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                    `;

                    if (rec.details && rec.details.length > 0) {
                        rec.details.forEach(detail => {
                            html += `
                                <tr>
                                    <td>${detail.vcNik || '-'}</td>
                                    <td>${detail.vcNamaKaryawan || '-'}</td>
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

                    const viewModalBody = document.getElementById('viewModalBody');
                    if (viewModalBody) {
                        viewModalBody.innerHTML = html;
                    }
                    const viewModal = document.getElementById('viewModal');
                    if (viewModal) {
                        new bootstrap.Modal(viewModal).show();
                    }
                }
            })
            .catch(err => {
                console.error(err);
                if (typeof showAlert === 'function') {
                    showAlert('error', 'Gagal memuat data');
                } else {
                    alert('Gagal memuat data');
                }
            });
    }

    function deleteRecord(id) {
        if (!confirm('Hapus data instruksi kerja lembur ini? Semua detail akan ikut terhapus.')) return;
        fetch(makeUrl(`instruksi-kerja-lembur/${id}`), {
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
                    if (typeof showAlert === 'function') {
                        showAlert('success', data.message);
                    } else {
                        alert(data.message);
                    }
                    location.reload();
                } else {
                    if (typeof showAlert === 'function') {
                        showAlert('error', data.message || 'Gagal menghapus data');
                    } else {
                        alert(data.message || 'Gagal menghapus data');
                    }
                }
            })
            .catch(err => {
                console.error(err);
                if (typeof showAlert === 'function') {
                    showAlert('error', 'Terjadi kesalahan saat menghapus');
                } else {
                    alert('Terjadi kesalahan saat menghapus');
                }
            });
    }

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
                if (nikSelect) {
                    nikSelect.innerHTML = '<option value="">Pilih Departemen terlebih dahulu</option>';
                    nikSelect.disabled = true;
                    nikSelect.removeAttribute('required');
                }
            });
        }
    });

    // When departemen changes, load bagians, kepala departemen, dan karyawan untuk detail rows
    departemenSelect.addEventListener('change', function() {
        const divisiKode = divisiSelect.value;
        const deptKode = this.value;

        // Check Free Role - jika enabled, skip filter departemen
        const isFreeRoleEnabled = freeRoleCheckbox && freeRoleCheckbox.checked;

        // Reset bagian
        bagianSelect.innerHTML = '<option value="">Pilih Bagian</option>';
        bagianSelect.disabled = !deptKode;
        bagianSelect.required = !!deptKode;

        // Load kepala departemen
        if (deptKode) {
            loadKepalaDept(deptKode);

            const detailRowsForDept = document.querySelectorAll('.detail-row');
            const delayPerRow = isFreeRoleEnabled ? 150 : 0;

            detailRowsForDept.forEach((row, idx) => {
                const nikSelect = row.querySelector('.nik-select');
                const wrapper = row.querySelector('.searchable-select-wrapper');
                if (!nikSelect || !wrapper) {
                    return;
                }

                const loadRowData = () => {
                    const rowIndex = row.dataset.index;
                    // console.log disabled for performance

                    nikSelect.disabled = true;
                    nikSelect.removeAttribute('required');
                    nikSelect.innerHTML = '<option value="">Memuat...</option>';

                    const loadUrl = isFreeRoleEnabled ?
                        'instruksi-kerja-lembur/get-all-karyawans' :
                        'instruksi-kerja-lembur/get-karyawans-by-departemen';

                    const payload = isFreeRoleEnabled ? {} : {
                        departemen: deptKode
                    };

                    fetch(makeUrl(loadUrl), {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                const list = result.karyawans || [];
                                nikSelect.innerHTML = '<option value="">Pilih NIK</option>';
                                list.forEach(k => {
                                    const option = document.createElement('option');
                                    option.value = k.Nik;
                                    option.textContent = `${k.Nik} - ${k.Nama}`;
                                    option.setAttribute('data-search', (k.Nik + ' ' + k.Nama).toLowerCase());
                                    nikSelect.appendChild(option);
                                });

                                nikSelect.disabled = false;
                                if (nikSelect.options.length > 1) {
                                    nikSelect.setAttribute('required', 'required');
                                } else {
                                    nikSelect.removeAttribute('required');
                                }

                                const searchInput = wrapper.querySelector('.searchable-select-search');
                                if (searchInput) {
                                    searchInput.disabled = false;
                                    searchInput.placeholder = isFreeRoleEnabled ?
                                        'Cari NIK atau Nama karyawan aktif (Free Role)...' :
                                        'Cari NIK atau Nama karyawan aktif...';
                                    searchInput.removeAttribute('disabled');
                                }

                                setTimeout(() => {
                                    delete wrapper.dataset.initialized;
                                    initSearchableSelectForDetail(wrapper, nikSelect);
                                }, 150);
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
                };

                setTimeout(loadRowData, idx * delayPerRow);
            });
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

        fetch(makeUrl('instruksi-kerja-lembur/get-departemens'), {
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

        fetch(makeUrl('instruksi-kerja-lembur/get-bagians'), {
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
            if (searchInput && searchInput.parentNode) {
                const newSearchInput = searchInput.cloneNode(true);
                searchInput.parentNode.replaceChild(newSearchInput, searchInput);
            }
            if (select && select.parentNode) {
                const newSelect = select.cloneNode(true);
                select.parentNode.replaceChild(newSelect, select);
            }
        }

        // Update references (setelah clone jika ada)
        const actualSearchInput = wrapper.querySelector('.searchable-select-search');
        const actualSelect = wrapper.querySelector('.searchable-select');

        if (!actualSearchInput || !actualSelect) {
            // console.warn disabled for performance
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
                // console.warn disabled for performance
                // Disable search input jika belum ada options
                actualSearchInput.disabled = true;
                actualSearchInput.placeholder = 'Pilih Departemen terlebih dahulu';
                wrapper.classList.remove('active');
                return;
            }

            // console.log disabled for performance
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
                    // console.warn disabled for performance
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
                // console.warn disabled for performance
                wrapper.classList.remove('active');
                return;
            }

            const term = this.value.trim();
            // console.log disabled for performance
            // Pastikan dropdown selalu muncul saat mengetik
            wrapper.classList.add('active');
            // Filter options berdasarkan term yang diketik
            filterOptions(actualSelect, term);

            // Debug: cek visible options setelah filter
            const visibleOptions = Array.from(actualSelect.options).filter(opt => opt.style.display !== 'none' && opt.value !== '');
            // console.log disabled for performance

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
                    // console.warn disabled for performance
                    return;
                }

                // Set value terlebih dahulu
                actualSelect.value = selectedValue;
                actualSearchInput.value = e.target.textContent;
                wrapper.classList.remove('active');

                // Verifikasi value ter-set
                if (actualSelect.value !== selectedValue) {
                    // console.warn disabled for performance
                    actualSelect.value = selectedValue;
                }

                // Set required jika ini adalah nik-select dan sudah enabled
                if (actualSelect.classList.contains('nik-select') && !actualSelect.disabled && selectedValue) {
                    actualSelect.setAttribute('required', 'required');
                    // console.log disabled for performance
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
                    // console.warn disabled for performance
                    return;
                }

                // Set value terlebih dahulu
                actualSelect.value = selectedValue;
                actualSearchInput.value = e.target.textContent;
                wrapper.classList.remove('active');

                // Verifikasi value ter-set
                if (actualSelect.value !== selectedValue) {
                    // console.warn disabled for performance
                    actualSelect.value = selectedValue;
                }

                // Set required jika ini adalah nik-select dan sudah enabled
                if (actualSelect.classList.contains('nik-select') && !actualSelect.disabled && selectedValue) {
                    actualSelect.setAttribute('required', 'required');
                    // console.log disabled for performance
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
                        // console.warn disabled for performance
                        return;
                    }

                    // Set value terlebih dahulu
                    actualSelect.value = selectedValue;
                    actualSearchInput.value = selectedOption.textContent;
                    wrapper.classList.remove('active');

                    // Verifikasi value ter-set
                    if (actualSelect.value !== selectedValue) {
                        // console.warn disabled for performance
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
                            // console.warn disabled for performance
                            // Coba set lagi
                            actualSelect.value = selectedOption.value;
                        } else {
                            // console.log disabled for performance
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
        // console.log disabled for performance
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
                        // console.log disabled for performance
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
                    // console.log disabled for performance
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
        row.dataset.initializing = data ? 'true' : 'false';

        // Karyawan akan dimuat dinamis berdasarkan bagian yang dipilih
        row.innerHTML = `
            <div class="col-md-2">
                <label class="form-label small">NIK/Nama <span class="text-danger">*</span></label>
                <div class="searchable-select-wrapper">
                    <input type="text" class="form-control form-control-sm searchable-select-search" placeholder="Pilih Departemen terlebih dahulu" autocomplete="off" disabled>
                    <select class="form-select form-select-sm nik-select searchable-select" name="details[${index}][vcNik]" size="1" disabled data-required="true">
                        <option value="">Pilih Departemen terlebih dahulu</option>
                    </select>
                </div>
                <div class="form-text nama-preview small" style="display:none;"></div>
                <div class="form-text text-muted small">Ketik untuk mencari</div>
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
            <div class="col-md-1 col-durasi-shrink">
                <label class="form-label small">Durasi (Jam)</label>
                <input type="number" class="form-control form-control-sm durasi-lembur" name="details[${index}][decDurasiLembur]" step="0.01" min="0" readonly>
            </div>
            <div class="col-md-1 col-istirahat-shrink">
                <label class="form-label small">Istirahat</label>
                <input type="number" class="form-control form-control-sm durasi-istirahat" name="details[${index}][intDurasiIstirahat]" min="0" value="0" placeholder="Menit">
            </div>
            <div class="col-md-1 col-deskripsi-wide">
                <label class="form-label small">Deskripsi</label>
                <input type="text" class="form-control form-control-sm" name="details[${index}][vcDeskripsiLembur]" maxlength="200" placeholder="Opsional">
            </div>
            <div class="col-md-2 col-penanggung-shrink">
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
            <div class="col-md-2 col-nominal-shrink">
                <label class="form-label small">Nominal Lembur</label>
                <input type="text" class="form-control form-control-sm nominal-lembur" name="details[${index}][decLemburExternal]" readonly style="background-color: #f8f9fa; font-weight: bold; text-align: right;" placeholder="Akan tampil setelah simpan">
                <small class="text-muted" style="font-size: 10px;">Nominal ditampilkan setelah data disimpan</small>
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
        const isRowInitializing = () => row.dataset.initializing === 'true';
        const penanggungBeban = row.querySelector('.penanggung-beban');
        const penanggungBebanLainnya = row.querySelector('.penanggung-beban-lainnya');
        const nominalLembur = row.querySelector('.nominal-lembur');

        // Ambil departemen dan divisi select dari header (global)
        const departemenSelectHeader = document.getElementById('vcKodeDept');
        const divisiSelectHeader = document.getElementById('vcKodeDivisi');

        // Load karyawan saat departemen dipilih
        function loadKaryawansByDepartemen(callback) {
            // Check Free Role Enabled
            const isFreeRoleEnabled = freeRoleCheckbox && freeRoleCheckbox.checked;

            // Jika Free Role enabled, load semua karyawan tanpa filter
            if (isFreeRoleEnabled) {
                nikSelect.disabled = true;
                nikSelect.removeAttribute('required');
                nikSelect.innerHTML = '<option value="">Memuat...</option>';

                fetch(makeUrl('instruksi-kerja-lembur/get-all-karyawans'), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            console.log('Karyawan (Free Role) ter-load untuk row', index, ', jumlah:', result.karyawans.length);
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
                            }

                            // Enable search input
                            const wrapper = row.querySelector('.searchable-select-wrapper');
                            if (wrapper) {
                                const searchInput = wrapper.querySelector('.searchable-select-search');
                                if (searchInput) {
                                    searchInput.disabled = false;
                                    searchInput.placeholder = 'Cari NIK atau Nama karyawan aktif (Free Role)...';
                                    searchInput.removeAttribute('disabled');
                                }

                                setTimeout(() => {
                                    if (nikSelect.options.length > 1) {
                                        initSearchableSelectForDetail(wrapper, nikSelect);
                                    }
                                }, 200);
                            }

                            if (callback) callback();
                        } else {
                            nikSelect.innerHTML = '<option value="">Tidak ada data</option>';
                            nikSelect.disabled = true;
                            if (callback) callback();
                        }
                    })
                    .catch(error => {
                        console.error('Error loading all karyawans:', error);
                        nikSelect.innerHTML = '<option value="">Error memuat data</option>';
                        if (callback) callback();
                    });
                return;
            }

            // Normal mode: filter berdasarkan departemen
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

            fetch(makeUrl('instruksi-kerja-lembur/get-karyawans-by-departemen'), {
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
                        // console.log disabled for performance
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
                                // console.log disabled for performance
                                searchInput.disabled = false;
                                searchInput.placeholder = 'Cari NIK atau Nama karyawan aktif...';
                                // Pastikan search input benar-benar enabled
                                searchInput.removeAttribute('disabled');
                            } else {
                                // console.warn disabled for performance
                            }

                            // Tunggu sebentar untuk memastikan DOM sudah ter-update dan options sudah ter-render
                            setTimeout(() => {
                                // Pastikan select sudah memiliki options
                                if (nikSelect.options.length <= 1) {
                                    // console.warn disabled for performance
                                    setTimeout(() => {
                                        // Re-inisialisasi dengan options yang sudah ter-load
                                        if (nikSelect.options.length > 1) {
                                            // console.log disabled for performance
                                            initSearchableSelectForDetail(wrapper, nikSelect);
                                        } else {
                                            console.error('Select masih belum memiliki options setelah retry');
                                        }
                                    }, 200);
                                    return;
                                }

                                // console.log disabled for performance
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
                // console.log disabled for performance

                // Check Free Role - jika enabled, langsung load semua karyawan
                const isFreeRoleEnabled = freeRoleCheckbox && freeRoleCheckbox.checked;

                if (isFreeRoleEnabled || departemenValue) {
                    // console.log disabled for performance
                    loadKaryawansByDepartemen();
                } else {
                    // console.log disabled for performance
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
            // console.log disabled for performance
        }

        // Jika Free Role enabled atau departemen sudah dipilih saat row dibuat, load karyawan segera
        // Gunakan retry logic untuk memastikan departemen sudah ter-set (termasuk dari auto-fill)
        let checkDepartemenAttempts = 0;
        const maxCheckDepartemenAttempts = 15; // Check sampai 15 kali (3 detik total)
        const checkDepartemenAndLoad = () => {
            checkDepartemenAttempts++;
            const isFreeRoleEnabled = freeRoleCheckbox && freeRoleCheckbox.checked;
            const hasDepartemen = departemenSelectHeader && departemenSelectHeader.value;

            if (isFreeRoleEnabled || hasDepartemen) {
                // console.log disabled for performance
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
                    // console.log disabled for performance
                    loadKaryawansByDepartemen();
                }
            } else if (checkDepartemenAttempts < maxCheckDepartemenAttempts) {
                // Departemen belum dipilih, coba lagi setelah 200ms
                // console.log disabled for performance
                setTimeout(checkDepartemenAndLoad, 200);
            } else {
                // Sudah max attempts, pastikan search input tetap disabled
                // console.log disabled for performance
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
                // console.log disabled for performance
            }

            fetch(makeUrl(`karyawan/${nik}`), {
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

            // Hitung nominal setelah durasi dihitung
            calculateNominalLembur();
        }

        // Function: Hitung nominal lembur via API
        function calculateNominalLembur() {
            // Preview dinonaktifkan sementara
            return;
        }

        const handleDurasiChange = () => {
            if (isRowInitializing()) return;
            calculateDurasiDetail();
        };
        jamMulai.addEventListener('change', handleDurasiChange);
        jamSelesai.addEventListener('change', handleDurasiChange);
        durasiIstirahat.addEventListener('change', handleDurasiChange);

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
            // Hitung nominal saat penanggung beban dipilih (dengan sedikit delay untuk memastikan value sudah ter-set)
            setTimeout(() => {
                calculateNominalLembur(isRowInitializing());
            }, 100);
        });

        // Event: Hitung nominal saat NIK dipilih
        nikSelect.addEventListener('change', function() {
            if (isRowInitializing()) return;
            calculateNominalLembur();
        });

        // Event: Hitung nominal saat tanggal berubah
        const tanggalLemburInput = document.getElementById('dtTanggalLembur');
        if (tanggalLemburInput) {
            tanggalLemburInput.addEventListener('change', function() {
                calculateNominalLembur(isRowInitializing());
            });
        }

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
            // Set nominal lembur jika ada
            if (nominalLembur && data.decLemburExternal) {
                const nominal = parseFloat(data.decLemburExternal || 0);
                nominalLembur.value = nominal.toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        }

        // Hitung nominal setelah semua field di-set (jika data ada)
        if (data) {
            setTimeout(() => {
                row.dataset.initializing = 'false';
            }, 500);
        } else {
            row.dataset.initializing = 'false';
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

        if (freeRoleCheckbox) {
            freeRoleCheckbox.checked = false;
            freeRoleCheckbox.dispatchEvent(new Event('change'));
        }

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

        // Hapus required dari semua select yang hidden (searchable-select) untuk menghindari error "not focusable"
        // Karena select yang hidden tidak bisa di-focus oleh browser saat validasi HTML5
        // Lakukan ini SEBELUM validasi apapun
        // Hapus required dari SEMUA select yang memiliki class searchable-select (termasuk yang di detail rows)
        document.querySelectorAll('.searchable-select').forEach(select => {
            select.removeAttribute('required');
            // Pastikan juga tidak ada required di parent atau attribute lainnya
            if (select.hasAttribute('data-required')) {
                select.removeAttribute('data-required');
            }
        });
        
        // Hapus required dari semua input yang hidden juga
        document.querySelectorAll('input[type="hidden"][required]').forEach(input => {
            input.removeAttribute('required');
        });
        
        // Hapus required dari semua select yang memiliki style display:none atau visibility:hidden
        document.querySelectorAll('select[required]').forEach(select => {
            const style = window.getComputedStyle(select);
            if (style.display === 'none' || style.visibility === 'hidden' || select.classList.contains('searchable-select')) {
                select.removeAttribute('required');
            }
        });

        // Hapus required dari semua select yang disabled atau belum ter-load untuk menghindari error validasi
        let hasValidRow = false;
        const invalidRows = [];

        // LANGKAH 1: Pastikan semua value ter-set SEBELUM validasi
        // Update value dari semua select yang hidden berdasarkan search input atau selectedIndex
        document.querySelectorAll('.detail-row').forEach((row) => {
            const nikSelect = row.querySelector('.nik-select');
            if (nikSelect && !nikSelect.disabled) {
                let nikValue = nikSelect.value;
                
                // Jika value kosong, coba ambil dari selectedIndex
                if (!nikValue || nikValue.trim() === '') {
                    const selectedOption = nikSelect.options[nikSelect.selectedIndex];
                    if (selectedOption && selectedOption.value) {
                        nikValue = selectedOption.value;
                        nikSelect.value = nikValue;
                    } else {
                        // Jika masih kosong, coba ambil dari search input (untuk searchable-select)
                        const wrapper = row.querySelector('.searchable-select-wrapper');
                        if (wrapper) {
                            const searchInput = wrapper.querySelector('.searchable-select-search');
                            if (searchInput && searchInput.value) {
                                // Cari option yang sesuai dengan text di search input
                                const searchText = searchInput.value.trim();
                                const options = Array.from(nikSelect.options);
                                const matchedOption = options.find(opt => {
                                    if (!opt.value) return false;
                                    const optText = opt.textContent.trim();
                                    // Cek apakah search text mengandung NIK atau sebaliknya
                                    return optText.includes(searchText) || searchText.includes(opt.value) || opt.value === searchText.split(' - ')[0];
                                });
                                if (matchedOption && matchedOption.value) {
                                    nikValue = matchedOption.value;
                                    nikSelect.value = nikValue;
                                }
                            }
                        }
                    }
                }
            }
        });

        // LANGKAH 2: Validasi setelah value ter-set
        document.querySelectorAll('.detail-row').forEach((row, idx) => {
            const nikSelect = row.querySelector('.nik-select');
            const jamMulai = row.querySelector('.jam-mulai');
            const jamSelesai = row.querySelector('.jam-selesai');

            // Validasi NIK
            if (nikSelect) {
                // Ambil value dari select (sudah di-update di langkah 1)
                let nikValue = nikSelect.value;

                // Jika masih kosong, coba ambil dari selectedIndex sekali lagi
                if (!nikValue || nikValue.trim() === '') {
                    const selectedOption = nikSelect.options[nikSelect.selectedIndex];
                    if (selectedOption && selectedOption.value) {
                        nikValue = selectedOption.value;
                        nikSelect.value = nikValue;
                    }
                }

                const hasValue = nikValue && nikValue.trim() !== '';
                const isEnabled = !nikSelect.disabled;

                // // console.log disabled for performance // Disabled for performance

                // Select dianggap valid jika: enabled, memiliki value, dan value bukan empty string
                if (isEnabled && hasValue && nikValue !== '') {
                    // Jangan set required ke select yang hidden (searchable-select), karena browser tidak bisa focus
                    // Validasi sudah dilakukan manual di sini

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
                        // // console.log disabled for performance // Disabled for performance
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
                        // // console.log disabled for performance // Disabled for performance
                        return; // Skip row ini
                    }

                    // Jika semua valid, hapus required dari jam (karena sudah diisi)
                    if (jamMulai) jamMulai.removeAttribute('required');
                    if (jamSelesai) jamSelesai.removeAttribute('required');

                    hasValidRow = true;
                    // // console.log disabled for performance // Disabled for performance
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

        // Cek apakah ada detail rows
        const detailRowsCheck = document.querySelectorAll('.detail-row');
        if (detailRowsCheck.length === 0) {
            showAlert('error', 'Minimal harus ada 1 karyawan dalam detail');
            return;
        }

        if (!hasValidRow) {
            // console.error('Tidak ada row yang valid. Invalid rows:', invalidRows); // Disabled for performance
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

        // Re-index semua detail rows untuk memastikan index konsisten (0, 1, 2, dst.)
        // Ini penting untuk mode Edit dimana rows bisa di-edit atau ditambah
        // Lakukan SEBELUM membuat FormData agar perubahan name attribute ter-capture
        const detailRows = document.querySelectorAll('.detail-row');
        detailRows.forEach((row, newIndex) => {
            // Update dataset.index untuk tracking
            row.dataset.index = newIndex;
            
            // Update semua name attributes di dalam row dengan index baru
            const inputs = row.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.name && input.name.includes('details[')) {
                    // Extract field name dari name attribute
                    // Format: details[oldIndex][fieldName]
                    const match = input.name.match(/details\[\d+\]\[(.+)\]/);
                    if (match && match[1]) {
                        const fieldName = match[1];
                        input.name = `details[${newIndex}][${fieldName}]`;
                    }
                }
            });
        });

        const url = isEditMode ? `${basePath}/instruksi-kerja-lembur/${currentId}` : `${basePath}/instruksi-kerja-lembur`;
        document.getElementById('_method').value = isEditMode ? 'PUT' : 'POST';
        
        // Buat FormData manual SETELAH re-indexing untuk memastikan semua data ter-capture dengan benar
        const formData = new FormData();
        
        // Tambahkan field header dari form
        const form = document.getElementById('lemburForm');
        const headerFields = ['vcKodeDivisi', 'vcKodeDept', 'vcKodeBagian', 'dtTanggalLembur', 'vcDiajukanOleh', 'vcAlasanDasarLembur', 'vcJabatanPengaju', 'vcKepalaDept'];
        headerFields.forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field && !field.disabled) {
                formData.append(fieldName, field.value || '');
            }
        });
        
        // Tambahkan freeRoleEnabled
        if (freeRoleCheckbox) {
            formData.append('freeRoleEnabled', freeRoleCheckbox.checked ? 1 : 0);
        } else {
            formData.append('freeRoleEnabled', 0);
        }
        
        // Tambahkan _method untuk PUT request
        formData.append('_method', isEditMode ? 'PUT' : 'POST');
        
        // Tambahkan detail rows SETELAH re-indexing (name attributes sudah di-update)
        // Gunakan querySelector berdasarkan name attribute yang sudah di-update untuk memastikan data ter-capture
        detailRows.forEach((row, newIndex) => {
            // Ambil semua input/select di row berdasarkan name attribute yang sudah di-update
            const nikSelect = row.querySelector(`select[name="details[${newIndex}][vcNik]"]`);
            const jamMulai = row.querySelector(`input[name="details[${newIndex}][dtJamMulaiLembur]"]`);
            const jamSelesai = row.querySelector(`input[name="details[${newIndex}][dtJamSelesaiLembur]"]`);
            const durasiLembur = row.querySelector(`input[name="details[${newIndex}][decDurasiLembur]"]`);
            const durasiIstirahat = row.querySelector(`input[name="details[${newIndex}][intDurasiIstirahat]"]`);
            const deskripsi = row.querySelector(`input[name="details[${newIndex}][vcDeskripsiLembur]"]`);
            const penanggungBeban = row.querySelector(`select[name="details[${newIndex}][vcPenanggungBebanLembur]"]`);
            const penanggungBebanLainnya = row.querySelector(`input[name="details[${newIndex}][vcPenanggungBebanLainnya]"]`);
            const nominalLembur = row.querySelector(`input[name="details[${newIndex}][decLemburExternal]"]`);
            const namaKaryawan = row.querySelector(`input[name="details[${newIndex}][vcNamaKaryawan]"]`);
            const jabatanKode = row.querySelector(`input[name="details[${newIndex}][vcKodeJabatan]"]`);
            
            // Ambil NIK value dengan berbagai cara untuk memastikan ter-capture
            let nikValue = '';
            if (nikSelect && !nikSelect.disabled) {
                nikValue = nikSelect.value;
                
                // Jika value kosong, coba ambil dari selectedIndex
                if (!nikValue || nikValue.trim() === '') {
                    const selectedOption = nikSelect.options[nikSelect.selectedIndex];
                    if (selectedOption && selectedOption.value) {
                        nikValue = selectedOption.value;
                        nikSelect.value = nikValue;
                    } else {
                        // Jika masih kosong, coba ambil dari search input (untuk searchable-select)
                        const wrapper = row.querySelector('.searchable-select-wrapper');
                        if (wrapper) {
                            const searchInput = wrapper.querySelector('.searchable-select-search');
                            if (searchInput && searchInput.value) {
                                // Cari option yang sesuai dengan text di search input
                                const searchText = searchInput.value.trim();
                                const options = Array.from(nikSelect.options);
                                const matchedOption = options.find(opt => {
                                    if (!opt.value) return false;
                                    const optText = opt.textContent.trim();
                                    // Cek apakah search text mengandung NIK atau sebaliknya
                                    return optText.includes(searchText) || searchText.includes(opt.value) || opt.value === searchText.split(' - ')[0];
                                });
                                if (matchedOption && matchedOption.value) {
                                    nikValue = matchedOption.value;
                                    nikSelect.value = nikValue;
                                }
                            }
                        }
                    }
                }
            }
            
            // Skip row jika NIK tidak ada atau disabled atau kosong
            if (!nikSelect || nikSelect.disabled || !nikValue || nikValue.trim() === '') {
                return;
            }
            
            // Tambahkan semua field detail dengan index yang sudah di-update
            // Pastikan semua field required ada
            if (nikValue) formData.append(`details[${newIndex}][vcNik]`, nikValue);
            if (namaKaryawan && namaKaryawan.value) formData.append(`details[${newIndex}][vcNamaKaryawan]`, namaKaryawan.value);
            if (jabatanKode && jabatanKode.value) formData.append(`details[${newIndex}][vcKodeJabatan]`, jabatanKode.value);
            if (jamMulai && jamMulai.value) formData.append(`details[${newIndex}][dtJamMulaiLembur]`, jamMulai.value);
            if (jamSelesai && jamSelesai.value) formData.append(`details[${newIndex}][dtJamSelesaiLembur]`, jamSelesai.value);
            if (durasiLembur && durasiLembur.value) formData.append(`details[${newIndex}][decDurasiLembur]`, durasiLembur.value);
            if (durasiIstirahat) formData.append(`details[${newIndex}][intDurasiIstirahat]`, durasiIstirahat.value || '0');
            if (deskripsi && deskripsi.value) formData.append(`details[${newIndex}][vcDeskripsiLembur]`, deskripsi.value);
            if (penanggungBeban && penanggungBeban.value) formData.append(`details[${newIndex}][vcPenanggungBebanLembur]`, penanggungBeban.value);
            if (penanggungBebanLainnya && penanggungBebanLainnya.value) formData.append(`details[${newIndex}][vcPenanggungBebanLainnya]`, penanggungBebanLainnya.value);
            
            // Convert nominal dari format dengan separator ke format angka
            if (nominalLembur && nominalLembur.value) {
                const numericValue = nominalLembur.value.replace(/\./g, '').replace(',', '.');
                formData.append(`details[${newIndex}][decLemburExternal]`, numericValue);
            }
        });

        fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async r => {
                const responseText = await r.text();
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    throw new Error('Invalid JSON response: ' + responseText.substring(0, 100));
                }
                
                if (!r.ok) {
                    throw new Error(data.message || data.error || 'HTTP Error: ' + r.status);
                }
                
                return data;
            })
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('lemburModal')).hide();
                    showAlert('success', data.message || 'Data berhasil disimpan');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    bootstrap.Modal.getInstance(document.getElementById('lemburModal')).hide();
                    const errorMsg = data.message || data.error || 'Gagal menyimpan data';
                    if (data.errors) {
                        const errorList = Object.values(data.errors).flat().join(', ');
                        showAlert('error', errorMsg + ': ' + errorList);
                    } else {
                        showAlert('error', errorMsg);
                    }
                }
            })
            .catch(err => {
                console.error('Error saving:', err);
                bootstrap.Modal.getInstance(document.getElementById('lemburModal')).hide();
                const errorMessage = err.message || 'Terjadi kesalahan saat menyimpan';
                showAlert('error', errorMessage);
                // Log error details untuk debugging
                if (err.response) {
                    console.error('Response error:', err.response);
                }
            });
    });

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

    // Pastikan semua fungsi global tersedia untuk onclick handlers
    // Fungsi yang didefinisikan dengan 'function' sudah otomatis di global scope,
    // tapi kita pastikan dengan menambahkan ke window untuk kompatibilitas
    if (typeof editRecord === 'function') {
        window.editRecord = editRecord;
    }
    if (typeof viewRecord === 'function') {
        window.viewRecord = viewRecord;
    }
    if (typeof deleteRecord === 'function') {
        window.deleteRecord = deleteRecord;
    }

    // Debug: Log untuk memastikan fungsi tersedia
    console.log('IKL Functions loaded:', {
        editRecord: typeof editRecord,
        viewRecord: typeof viewRecord,
        deleteRecord: typeof deleteRecord
    });
</script>
@endpush
