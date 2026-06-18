@extends('layouts.app')

@section('title', 'Form Perjalanan Dinas - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-plane me-2"></i>Form Perjalanan Dinas
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
                    <form method="GET" action="{{ route('perjalanan-dinas.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="dari_tanggal" class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" id="dari_tanggal" name="dari_tanggal" value="{{ $startDate }}">
                                <small class="text-muted">Filter berdasarkan Tanggal Form atau Tanggal Dinas</small>
                            </div>
                            <div class="col-md-3">
                                <label for="sampai_tanggal" class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" id="sampai_tanggal" name="sampai_tanggal" value="{{ $endDate }}">
                                <small class="text-muted">Filter berdasarkan Tanggal Form atau Tanggal Dinas</small>
                            </div>
                            <div class="col-md-3">
                                <label for="search" class="form-label">No RPD / NIK / Nama</label>
                                <div class="position-relative">
                                    <input type="text"
                                        class="form-control"
                                        id="search"
                                        name="search"
                                        value="{{ $search ?? '' }}"
                                        placeholder="Cari No RPD, NIK atau Nama"
                                        autocomplete="off">
                                    <div id="searchAutocomplete" class="autocomplete-dropdown" style="display: none;"></div>
                                </div>
                                <small class="text-muted">Ketik No RPD, NIK atau nama untuk mencari</small>
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
                                    <th width="12%">No. RPD</th>
                                    <th width="12%">Tanggal Dinas</th>
                                    <th width="8%">Durasi</th>
                                    <th width="18%">Tujuan Dinas</th>
                                    <th width="20%">Karyawan yang Bertugas</th>
                                    <th width="8%">Jml Karyawan</th>
                                    <th width="8%">Menginap</th>
                                    <th width="14%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($records as $row)
                                <tr>
                                    <td><span class="badge bg-secondary">{{ $row->vcNoRpd ?? '-' }}</span></td>
                                    <td>
                                        @if($row->dtTanggalDinasDari && $row->dtTanggalDinasSampai)
                                            {{ $row->dtTanggalDinasDari->format('d/m/Y') }} - {{ $row->dtTanggalDinasSampai->format('d/m/Y') }}
                                        @elseif($row->dtTanggalDinasDari)
                                            {{ $row->dtTanggalDinasDari->format('d/m/Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($row->intDurasiHari)
                                            <span class="badge bg-info">{{ $row->intDurasiHari }} hari</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $row->vcTujuanDinas ?? '-' }}</td>
                                    <td>
                                        @if($row->karyawans && $row->karyawans->count() > 0)
                                            @foreach($row->karyawans->take(3) as $karyawan)
                                                <span class="badge bg-light text-dark">{{ $karyawan->vcNamaKaryawan }}</span>
                                            @endforeach
                                            @if($row->karyawans->count() > 3)
                                                <span class="text-muted">+{{ $row->karyawans->count() - 3 }} lainnya</span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center"><span class="badge bg-info">{{ $row->karyawans ? $row->karyawans->count() : 0 }}</span></td>
                                    <td class="text-center">
                                        @if($row->hotels && $row->hotels->where('isMenginap', true)->count() > 0)
                                            <span class="badge bg-warning">Ya</span>
                                        @else
                                            <span class="badge bg-secondary">Tidak</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-info" onclick="previewRecord('{{ $row->vcNoRpd }}')" title="Preview">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="{{ route('perjalanan-dinas.print', $row->vcNoRpd) }}" class="btn btn-outline-success" target="_blank" title="Print">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <button class="btn btn-outline-primary" onclick="editRecord('{{ $row->vcNoRpd }}')" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteRecord('{{ $row->vcNoRpd }}')" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
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

<!-- Modal Preview -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>Preview Form Perjalanan Dinas
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewModalBody">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat data...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success" id="btnPrintFromPreview">
                    <i class="fas fa-print me-1"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="perjalananDinasModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="perjalananDinasModalLabel">Tambah Form Perjalanan Dinas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="perjalananDinasForm" novalidate>
                <input type="hidden" name="_method" id="_method" value="POST">
                <div class="modal-body" style="max-height: calc(100vh - 200px); overflow-y: auto; padding-bottom: 20px;">
                    <!-- Header Section -->
                    <div class="card mb-3 border-primary">
                        <div class="card-header bg-primary text-white">
                            <strong>1. Header / Master</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Baris Pertama: Tanggal Form, Pemberi Tugas, Jabatan Pemberi Tugas -->
                                <div class="col-md-4">
                                    <label for="dtTanggalForm" class="form-label">Tanggal Form Dinas <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="dtTanggalForm" name="dtTanggalForm" required disabled style="background-color: #e9ecef;">
                                    <small class="text-muted">Otomatis terisi dengan tanggal hari ini</small>
                                </div>
                                <div class="col-md-4">
                                    <label for="vcPemberiTugas" class="form-label">Pemberi Tugas <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="text" class="form-control" id="vcPemberiTugas" name="vcPemberiTugas" maxlength="100" required placeholder="Ketik NIK atau Nama" autocomplete="off">
                                        <input type="hidden" id="vcPemberiTugasHidden">
                                        <div id="pemberiTugasAutocomplete" class="autocomplete-dropdown" style="display: none; z-index: 1055;"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="vcJabatanPemberiTugas" class="form-label">Jabatan Pemberi Tugas</label>
                                    <input type="text" class="form-control" id="vcJabatanPemberiTugas" name="vcJabatanPemberiTugas" maxlength="100" placeholder="Jabatan Pemberi Tugas" readonly style="background-color: #e9ecef;">
                                </div>
                                
                                <!-- Baris Kedua: Tanggal Mulai Dinas, Tanggal Sampai Dinas, Durasi Dinas -->
                                <div class="col-md-4">
                                    <label for="dtTanggalDinasDari" class="form-label">Tanggal Mulai Dinas <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="dtTanggalDinasDari" name="dtTanggalDinasDari" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="dtTanggalDinasSampai" class="form-label">Tanggal Sampai Dinas <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="dtTanggalDinasSampai" name="dtTanggalDinasSampai" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="intDurasiHari" class="form-label">Durasi Dinas (Hari)</label>
                                    <input type="number" class="form-control" id="intDurasiHari" name="intDurasiHari" min="0" placeholder="Otomatis terisi" readonly style="background-color: #e9ecef;">
                                    <small class="text-muted">Otomatis dihitung dari tanggal</small>
                                </div>
                                
                                <!-- Baris Ketiga: Tujuan Dinas -->
                                <div class="col-md-12">
                                    <label for="vcTujuanDinas" class="form-label">Tujuan Dinas <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="vcTujuanDinas" name="vcTujuanDinas" maxlength="200" required placeholder="Tempat / Instansi / Negara">
                                </div>
                                
                                <!-- Baris Keempat: Maksud/Uraian Perjalanan Dinas -->
                                <div class="col-md-12">
                                    <label for="vcMaksudPerjalananDinas" class="form-label">Maksud / Uraian Perjalanan Dinas</label>
                                    <textarea class="form-control" id="vcMaksudPerjalananDinas" name="vcMaksudPerjalananDinas" rows="3" placeholder="Uraian maksud perjalanan dinas"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Karyawan Section -->
                    <div class="card mb-3 border-success">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <strong>2. Karyawan Yang Ditugaskan</strong>
                            <button type="button" class="btn btn-sm btn-light" id="btnAddKaryawan">
                                <i class="fas fa-plus me-1"></i>Tambah Karyawan
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="karyawanContainer">
                                <!-- Karyawan rows akan ditambahkan di sini -->
                            </div>
                        </div>
                    </div>

                    <!-- Jadwal Section -->
                    <div class="card mb-3 border-info">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <strong>3. Jadwal dan Moda Perjalanan</strong>
                            <button type="button" class="btn btn-sm btn-light" id="btnAddJadwal">
                                <i class="fas fa-plus me-1"></i>Tambah Jadwal
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="jadwalContainer">
                                <!-- Jadwal rows akan ditambahkan di sini -->
                            </div>
                        </div>
                    </div>

                    <!-- Hotel Section -->
                    <div class="card mb-3 border-warning">
                        <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                            <strong>4. Hotel / Penginapan</strong>
                            <button type="button" class="btn btn-sm btn-light" id="btnAddHotel">
                                <i class="fas fa-plus me-1"></i>Tambah Hotel
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="hotelContainer">
                                <!-- Hotel rows akan ditambahkan di sini -->
                            </div>
                        </div>
                    </div>

                    <!-- Otorisasi Section -->
                    <div class="card mb-3 border-secondary">
                        <div class="card-header bg-secondary text-white">
                            <strong>5. Otorisasi (Tanda Tangan Pihak Berwenang)</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="vcMengajukan" class="form-label">Mengajukan - Penerima Tugas</label>
                                    <input type="text" class="form-control" id="vcMengajukan" name="vcMengajukan" maxlength="100" placeholder="Nama Penerima Tugas">
                                </div>
                                <div class="col-md-4">
                                    <label for="vcMenyetujui" class="form-label">Menyetujui - Pemberi Tugas</label>
                                    <input type="text" class="form-control" id="vcMenyetujui" name="vcMenyetujui" maxlength="100" placeholder="Nama Pemberi Tugas">
                                </div>
                                <div class="col-md-4">
                                    <label for="vcMengetahui" class="form-label">Mengetahui - HRD</label>
                                    <div class="position-relative">
                                        <input type="text" class="form-control" id="vcMengetahui" name="vcMengetahui" maxlength="100" placeholder="NIK atau Nama HRD" autocomplete="off">
                                        <input type="hidden" id="vcMengetahuiHidden">
                                        <div id="mengetahuiAutocomplete" class="autocomplete-dropdown" style="display: none; z-index: 1055;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Destinasi Section -->
                    <div class="card mb-3 border-danger">
                        <div class="card-header bg-danger text-white">
                            <strong>6. Destinasi (diisi oleh petugas di tempat tujuan)</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Tiba / Kedatangan -->
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Kedatangan / Tiba</label>
                                </div>
                                <div class="col-md-4">
                                    <label for="tiba_kembali_dtTanggalTiba" class="form-label">Hari / Tanggal</label>
                                    <input type="date" class="form-control" id="tiba_kembali_dtTanggalTiba" name="tiba_kembali[dtTanggalTiba]">
                                </div>
                                <div class="col-md-4">
                                    <label for="tiba_kembali_dtJamTiba" class="form-label">Jam</label>
                                    <input type="time" class="form-control" id="tiba_kembali_dtJamTiba" name="tiba_kembali[dtJamTiba]">
                                </div>
                                
                                <!-- Kembali / Kepulangan -->
                                <div class="col-md-12 mt-3">
                                    <label class="form-label fw-bold">Kembali / Kepulangan</label>
                                </div>
                                <div class="col-md-4">
                                    <label for="tiba_kembali_dtTanggalKembali" class="form-label">Hari / Tanggal</label>
                                    <input type="date" class="form-control" id="tiba_kembali_dtTanggalKembali" name="tiba_kembali[dtTanggalKembali]">
                                </div>
                                <div class="col-md-4">
                                    <label for="tiba_kembali_dtJamKembali" class="form-label">Jam</label>
                                    <input type="time" class="form-control" id="tiba_kembali_dtJamKembali" name="tiba_kembali[dtJamKembali]">
                                </div>
                                
                                <!-- Keterangan -->
                                <div class="col-md-12 mt-3">
                                    <label for="tiba_kembali_vcKeteranganKedatangan" class="form-label">Keterangan</label>
                                    <textarea class="form-control" id="tiba_kembali_vcKeteranganKedatangan" name="tiba_kembali[vcKeteranganKedatangan]" rows="2" placeholder="Keterangan tambahan"></textarea>
                                </div>
                                
                                <!-- Otorisasi Tuan Rumah / Destinator -->
                                <div class="col-md-12 mt-3">
                                    <label for="tiba_kembali_vcTandaTanganPihakBerwenang" class="form-label">Otorisasi Tuan Rumah / Destinator</label>
                                    <input type="text" class="form-control" id="tiba_kembali_vcTandaTanganPihakBerwenang" name="tiba_kembali[vcTandaTanganPihakBerwenang]" maxlength="100" placeholder="Nama Jelas, TTD & Cap Perusahaan">
                                    <small class="text-muted">Nama Jelas, TTD & Cap Perusahaan</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="position: sticky; bottom: 0; background-color: white; border-top: 1px solid #dee2e6; z-index: 10; padding: 1rem;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .autocomplete-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .autocomplete-item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
    }
    .autocomplete-item:hover, .autocomplete-item.selected {
        background-color: #f0f0f0;
    }
    .modal .autocomplete-dropdown {
        z-index: 1055;
    }
</style>
@endsection

@push('scripts')
<script>
    // Base path untuk subfolder support
    const fullUrl = '{{ url("/") }}';
    const basePath = fullUrl.replace(/^https?:\/\/[^\/]+/, '') || '';

    function makeUrl(path) {
        const cleanPath = path.startsWith('/') ? path.substring(1) : path;
        if (!basePath) {
            return `/${cleanPath}`;
        }
        const cleanBase = basePath.endsWith('/') ? basePath.slice(0, -1) : basePath;
        return `${cleanBase}/${cleanPath}`;
    }

    // Helper function untuk format date (reusable)
    function formatDateForInput(dateStr) {
        if (!dateStr) return '';
        if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) return dateStr;
        if (dateStr.includes(' ')) return dateStr.split(' ')[0];
        try {
            const d = new Date(dateStr);
            if (!isNaN(d.getTime())) {
                return d.toISOString().split('T')[0];
            }
        } catch(e) {}
        return dateStr;
    }

    // Helper function untuk format time (reusable)
    function formatTimeForInput(timeStr) {
        if (!timeStr) return '';
        if (timeStr.includes(':')) {
            const parts = timeStr.split(':');
            return parts[0].padStart(2, '0') + ':' + (parts[1] || '00').padStart(2, '0').substring(0, 2);
        }
        return timeStr;
    }

    let isEditMode = false;
    let currentId = null;
    let karyawanIndex = 0;
    let jadwalIndex = 0;
    let hotelIndex = 0;

    const karyawanList = @json($karyawanList ?? []);

    // Autocomplete untuk search filter
    let searchTimeout;
    let selectedIndex = -1;
    const searchInput = document.getElementById('search');
    const autocompleteDiv = document.getElementById('searchAutocomplete');

    if (searchInput && autocompleteDiv) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const term = this.value.trim().toLowerCase();
            
            if (term.length < 2) {
                autocompleteDiv.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                const matches = karyawanList.filter(k => 
                    k.search.includes(term) || 
                    k.nik.toLowerCase().includes(term) ||
                    k.nama.toLowerCase().includes(term)
                ).slice(0, 10);

                if (matches.length > 0) {
                    autocompleteDiv.innerHTML = matches.map((k, idx) => 
                        `<div class="autocomplete-item ${idx === selectedIndex ? 'selected' : ''}" data-nik="${k.nik}" data-nama="${k.nama}">
                            <strong>${k.nik}</strong> - ${k.nama}
                        </div>`
                    ).join('');
                    autocompleteDiv.style.display = 'block';
                } else {
                    autocompleteDiv.style.display = 'none';
                }
                selectedIndex = -1;
            }, 200);
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
            } else if (e.key === 'Escape') {
                autocompleteDiv.style.display = 'none';
                selectedIndex = -1;
            }
        });

        document.addEventListener('click', function(e) {
            if (!autocompleteDiv.contains(e.target) && e.target !== searchInput) {
                autocompleteDiv.style.display = 'none';
            }
        });

        autocompleteDiv.addEventListener('click', function(e) {
            const item = e.target.closest('.autocomplete-item');
            if (item) {
                const nik = item.dataset.nik;
                const nama = item.dataset.nama;
                searchInput.value = `${nik} - ${nama}`;
                autocompleteDiv.style.display = 'none';
            }
        });
    }

    function updateSelectedItem(items) {
        items.forEach((item, idx) => {
            item.classList.toggle('selected', idx === selectedIndex);
        });
        if (selectedIndex >= 0) {
            items[selectedIndex].scrollIntoView({ block: 'nearest' });
        }
    }

    // Auto-calculate durasi hari
    function calculateDurasiHari() {
        const tanggalDari = document.getElementById('dtTanggalDinasDari').value;
        const tanggalSampai = document.getElementById('dtTanggalDinasSampai').value;
        const durasiInput = document.getElementById('intDurasiHari');
        
        if (tanggalDari && tanggalSampai) {
            const dari = new Date(tanggalDari);
            const sampai = new Date(tanggalSampai);
            
            if (sampai >= dari) {
                const diffTime = Math.abs(sampai - dari);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; // +1 untuk include hari pertama
                durasiInput.value = diffDays;
            } else {
                durasiInput.value = '';
            }
        } else {
            durasiInput.value = '';
        }
    }

    // Event listeners untuk auto-calculate durasi
    document.getElementById('dtTanggalDinasDari').addEventListener('change', calculateDurasiHari);
    document.getElementById('dtTanggalDinasSampai').addEventListener('change', calculateDurasiHari);

    // Add Button Click
    document.getElementById('addBtn').addEventListener('click', function() {
        isEditMode = false;
        currentId = null;
        karyawanIndex = 0;
        jadwalIndex = 0;
        hotelIndex = 0;
        
        document.getElementById('perjalananDinasModalLabel').textContent = 'Tambah Form Perjalanan Dinas';
        document.getElementById('_method').value = 'POST';
        document.getElementById('perjalananDinasForm').reset();
        
        // Clear containers
        document.getElementById('karyawanContainer').innerHTML = '';
        document.getElementById('jadwalContainer').innerHTML = '';
        document.getElementById('hotelContainer').innerHTML = '';
        
        // Clear tiba_kembali fields
        document.getElementById('tiba_kembali_dtTanggalTiba').value = '';
        document.getElementById('tiba_kembali_dtJamTiba').value = '';
        document.getElementById('tiba_kembali_dtTanggalKembali').value = '';
        document.getElementById('tiba_kembali_dtJamKembali').value = '';
        document.getElementById('tiba_kembali_vcKeteranganKedatangan').value = '';
        document.getElementById('tiba_kembali_vcTandaTanganPihakBerwenang').value = '';
        
        // Set default tanggal hari ini (untuk disabled field, tetap set value untuk submit)
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('dtTanggalForm').value = today;
        
        // Clear autocomplete fields
        document.getElementById('vcPemberiTugas').value = '';
        document.getElementById('vcPemberiTugasHidden').value = '';
        document.getElementById('vcJabatanPemberiTugas').value = '';
        document.getElementById('vcMengetahui').value = '';
        document.getElementById('vcMengetahuiHidden').value = '';
        document.getElementById('vcMengajukan').value = '';
        document.getElementById('vcMenyetujui').value = '';
        
        const modal = new bootstrap.Modal(document.getElementById('perjalananDinasModal'));
        modal.show();
    });

    // Add Karyawan Row
    document.getElementById('btnAddKaryawan').addEventListener('click', function() {
        addKaryawanRow();
    });

    function addKaryawanRow(data = null) {
        const index = karyawanIndex++;
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 karyawan-row border-bottom pb-2';
        row.dataset.index = index;

        row.innerHTML = `
            <div class="col-md-3">
                <label class="form-label small">NIK / Nama <span class="text-danger">*</span></label>
                <div class="position-relative">
                    <input type="text" class="form-control form-control-sm nik-input" placeholder="Ketik NIK atau Nama" autocomplete="off" required>
                    <input type="hidden" class="nik-hidden" name="karyawans[${index}][vcNik]">
                    <div class="nik-autocomplete autocomplete-dropdown" style="display: none; z-index: 1055;"></div>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Bisnis Unit</label>
                <input type="text" class="form-control form-control-sm bisnis-unit-display" readonly style="background-color: #e9ecef;">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Departemen</label>
                <input type="text" class="form-control form-control-sm dept-display" readonly style="background-color: #e9ecef;">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Jabatan</label>
                <input type="text" class="form-control form-control-sm jabatan-display" readonly style="background-color: #e9ecef;">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Klasifikasi Grade</label>
                <select class="form-select form-select-sm" name="karyawans[${index}][vcKlasifikasiGrade]">
                    <option value="">Pilih Grade</option>
                    <option value="Senior Management">Senior Management</option>
                    <option value="Middle Management">Middle Management</option>
                    <option value="Junior Management">Junior Management</option>
                    <option value="Staff">Staff</option>
                    <option value="Operator/Driver">Operator/Driver</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small">&nbsp;</label>
                <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeKaryawanRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;

        document.getElementById('karyawanContainer').appendChild(row);

        // Setup autocomplete untuk NIK input
        const nikInput = row.querySelector('.nik-input');
        const nikHidden = row.querySelector('.nik-hidden');
        const nikAutocomplete = row.querySelector('.nik-autocomplete');
        const bisnisUnitDisplay = row.querySelector('.bisnis-unit-display');
        const deptDisplay = row.querySelector('.dept-display');
        const jabatanDisplay = row.querySelector('.jabatan-display');

        if (nikInput) {
            nikInput.addEventListener('input', function() {
                const term = this.value.trim().toLowerCase();
                if (term.length < 2) {
                    nikAutocomplete.style.display = 'none';
                    return;
                }

                const matches = karyawanList.filter(k => 
                    k.search.includes(term) || 
                    k.nik.toLowerCase().includes(term) ||
                    k.nama.toLowerCase().includes(term)
                ).slice(0, 10);

                if (matches.length > 0) {
                    nikAutocomplete.innerHTML = matches.map(k => 
                        `<div class="autocomplete-item" data-nik="${k.nik}" data-nama="${k.nama}">
                            <strong>${k.nik}</strong> - ${k.nama}
                        </div>`
                    ).join('');
                    nikAutocomplete.style.display = 'block';
                } else {
                    nikAutocomplete.style.display = 'none';
                }
            });

            nikAutocomplete.addEventListener('click', function(e) {
                const item = e.target.closest('.autocomplete-item');
                if (item) {
                    const nik = item.dataset.nik;
                    const nama = item.dataset.nama;
                    nikInput.value = `${nik} - ${nama}`;
                    nikHidden.value = nik;
                    nikAutocomplete.style.display = 'none';
                    
                    // Fetch karyawan data
                    fetch(makeUrl(`perjalanan-dinas/get-karyawan-data?nik=${nik}`), {
                        method: 'GET',
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            bisnisUnitDisplay.value = data.data.bisnis_unit || '-';
                            deptDisplay.value = data.data.departemen || '-';
                            jabatanDisplay.value = data.data.jabatan || '-';
                        }
                    })
                    .catch(err => console.error('Error fetching karyawan data:', err));
                }
            });
        }

        // Load data if editing
        if (data) {
            nikInput.value = `${data.vcNik} - ${data.vcNamaKaryawan}`;
            nikHidden.value = data.vcNik;
            
            // Set nilai dari data yang sudah ada terlebih dahulu (fallback)
            if (data.karyawan && data.karyawan.divisi) {
                bisnisUnitDisplay.value = data.karyawan.divisi.vcNamaDivisi || data.karyawan.Divisi || '-';
            } else if (data.karyawan && data.karyawan.Divisi) {
                bisnisUnitDisplay.value = data.karyawan.Divisi;
            } else {
                bisnisUnitDisplay.value = '-';
            }
            deptDisplay.value = data.departemen?.vcNamaDept || '-';
            jabatanDisplay.value = data.jabatan?.vcNamaJabatan || '-';
            
            // Fetch karyawan data untuk get bisnis unit yang lebih lengkap
            fetch(makeUrl(`perjalanan-dinas/get-karyawan-data?nik=${data.vcNik}`), {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(response => {
                if (response.success) {
                    bisnisUnitDisplay.value = response.data.bisnis_unit || bisnisUnitDisplay.value || '-';
                    deptDisplay.value = response.data.departemen || deptDisplay.value || '-';
                    jabatanDisplay.value = response.data.jabatan || jabatanDisplay.value || '-';
                }
            })
            .catch(err => {
                console.error('Error fetching karyawan data:', err);
                // Keep existing values from fallback above
            });
            
            row.querySelector(`select[name="karyawans[${index}][vcKlasifikasiGrade]"]`).value = data.vcKlasifikasiGrade || '';
        }
    }

    function removeKaryawanRow(btn) {
        btn.closest('.karyawan-row').remove();
    }

    // Add Jadwal Row
    document.getElementById('btnAddJadwal').addEventListener('click', function() {
        addJadwalRow();
    });

    function addJadwalRow(data = null) {
        const index = jadwalIndex++;
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 jadwal-row border-bottom pb-2';
        row.dataset.index = index;

        row.innerHTML = `
            <div class="col-md-2">
                <label class="form-label small">Moda Perjalanan <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm moda-perjalanan-select" name="jadwals[${index}][vcModaPerjalanan]" required>
                    <option value="">Pilih Moda</option>
                    <option value="Kendaraan Dinas">Kendaraan Dinas</option>
                    <option value="Kendaraan Pribadi">Kendaraan Pribadi</option>
                    <option value="Kendaraan Umum">Kendaraan Umum</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Tanggal Berangkat</label>
                <input type="date" class="form-control form-control-sm tanggal-berangkat-input" name="jadwals[${index}][dtTanggalBerangkat]">
            </div>
            <div class="col-md-1">
                <label class="form-label small">Jam</label>
                <input type="time" class="form-control form-control-sm" name="jadwals[${index}][dtJamBerangkat]">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Tanggal Sampai</label>
                <input type="date" class="form-control form-control-sm tanggal-sampai-input" name="jadwals[${index}][dtTanggalKembali]">
                <small class="text-muted">Default = Tanggal Berangkat</small>
            </div>
            <div class="col-md-1">
                <label class="form-label small">Jam</label>
                <input type="time" class="form-control form-control-sm" name="jadwals[${index}][dtJamKembali]">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Keterangan Moda</label>
                <input type="text" class="form-control form-control-sm" name="jadwals[${index}][vcKeteranganBerangkat]" maxlength="200" placeholder="Keterangan moda perjalanan">
            </div>
            <div class="col-md-1">
                <label class="form-label small">&nbsp;</label>
                <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeJadwalRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;

        // Auto-set tanggal sampai = tanggal berangkat saat tanggal berangkat diubah
        const tanggalBerangkatInput = row.querySelector('.tanggal-berangkat-input');
        const tanggalSampaiInput = row.querySelector('.tanggal-sampai-input');
        
        if (tanggalBerangkatInput && tanggalSampaiInput) {
            tanggalBerangkatInput.addEventListener('change', function() {
                if (this.value && !tanggalSampaiInput.value) {
                    tanggalSampaiInput.value = this.value;
                }
            });
        }

        document.getElementById('jadwalContainer').appendChild(row);

        // Load data if editing
        if (data) {
            row.querySelector(`select[name="jadwals[${index}][vcModaPerjalanan]"]`).value = data.vcModaPerjalanan || '';
            row.querySelector(`input[name="jadwals[${index}][dtTanggalBerangkat]"]`).value = formatDateForInput(data.dtTanggalBerangkat);
            row.querySelector(`input[name="jadwals[${index}][dtJamBerangkat]"]`).value = formatTimeForInput(data.dtJamBerangkat);
            row.querySelector(`input[name="jadwals[${index}][dtTanggalKembali]"]`).value = formatDateForInput(data.dtTanggalKembali);
            row.querySelector(`input[name="jadwals[${index}][dtJamKembali]"]`).value = formatTimeForInput(data.dtJamKembali);
            row.querySelector(`input[name="jadwals[${index}][vcKeteranganBerangkat]"]`).value = data.vcKeteranganBerangkat || '';
        }
    }

    function removeJadwalRow(btn) {
        btn.closest('.jadwal-row').remove();
    }

    // Add Hotel Row
    document.getElementById('btnAddHotel').addEventListener('click', function() {
        addHotelRow();
    });

    function addHotelRow(data = null) {
        const index = hotelIndex++;
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 hotel-row border-bottom pb-2';
        row.dataset.index = index;

        row.innerHTML = `
            <div class="col-md-12">
                <div class="form-check mb-2">
                    <input class="form-check-input hotel-checkbox" type="checkbox" name="hotels[${index}][isMenginap]" value="1" ${data && data.isMenginap ? 'checked' : ''}>
                    <label class="form-check-label">
                        Menginap
                    </label>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Tanggal Menginap</label>
                <input type="date" class="form-control form-control-sm" name="hotels[${index}][dtTanggalMenginap]">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Kota/Provinsi/Negara</label>
                <input type="text" class="form-control form-control-sm" name="hotels[${index}][vcKotaProvinsiNegara]" maxlength="200">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Nama Hotel/Penginapan</label>
                <input type="text" class="form-control form-control-sm" name="hotels[${index}][vcNamaHotel]" maxlength="200">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Keterangan</label>
                <input type="text" class="form-control form-control-sm" name="hotels[${index}][vcKeteranganHotel]">
            </div>
            <div class="col-md-1">
                <label class="form-label small">&nbsp;</label>
                <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeHotelRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;

        document.getElementById('hotelContainer').appendChild(row);

        // Load data if editing
        if (data) {
            row.querySelector(`input[name="hotels[${index}][isMenginap]"]`).checked = data.isMenginap || false;
            row.querySelector(`input[name="hotels[${index}][dtTanggalMenginap]"]`).value = formatDateForInput(data.dtTanggalMenginap);
            row.querySelector(`input[name="hotels[${index}][vcKotaProvinsiNegara]"]`).value = data.vcKotaProvinsiNegara || '';
            row.querySelector(`input[name="hotels[${index}][vcNamaHotel]"]`).value = data.vcNamaHotel || '';
            row.querySelector(`input[name="hotels[${index}][vcKeteranganHotel]"]`).value = data.vcKeteranganHotel || '';
        }
    }

    function removeHotelRow(btn) {
        btn.closest('.hotel-row').remove();
    }

    // Form Submit
    document.getElementById('perjalananDinasForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validasi manual sebelum submit
        const dtTanggalForm = document.getElementById('dtTanggalForm').value;
        if (!dtTanggalForm) {
            alert('Error: Tanggal Form Dinas harus diisi');
            document.getElementById('dtTanggalForm').focus();
            return;
        }
        
        const formData = new FormData(this);
        
        // Handle disabled field dtTanggalForm - tetap kirim value
        const dtTanggalFormValue = document.getElementById('dtTanggalForm').value;
        if (dtTanggalFormValue) {
            formData.set('dtTanggalForm', dtTanggalFormValue);
        }
        
        // Handle Pemberi Tugas - ambil nama dari format "NIK - Nama" atau langsung nama
        const pemberiTugasValue = document.getElementById('vcPemberiTugas').value;
        if (pemberiTugasValue) {
            const namaPemberiTugas = pemberiTugasValue.includes(' - ') 
                ? pemberiTugasValue.split(' - ')[1] 
                : pemberiTugasValue;
            formData.set('vcPemberiTugas', namaPemberiTugas);
        }
        
        // Handle Mengetahui - ambil nama dari format "NIK - Nama" atau langsung nama
        const mengetahuiValue = document.getElementById('vcMengetahui').value;
        if (mengetahuiValue) {
            const namaMengetahui = mengetahuiValue.includes(' - ') 
                ? mengetahuiValue.split(' - ')[1] 
                : mengetahuiValue;
            formData.set('vcMengetahui', namaMengetahui);
        }
        
        // Pastikan _method terkirim untuk PUT request
        if (isEditMode) {
            formData.append('_method', 'PUT');
        }
        
        // Debug: Log form data untuk memastikan semua field terkirim
        console.log('Form Data:');
        console.log('isEditMode:', isEditMode);
        console.log('currentId:', currentId);
        console.log('dtTanggalForm:', document.getElementById('dtTanggalForm').value);
        for (let [key, value] of formData.entries()) {
            console.log(key, ':', value);
        }
        
        const url = isEditMode 
            ? makeUrl(`perjalanan-dinas/${currentId}`)
            : makeUrl('perjalanan-dinas');
        
        const method = isEditMode ? 'POST' : 'POST'; // Gunakan POST untuk semua, karena _method sudah di-set

        fetch(url, {
            method: method,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(r => {
            if (!r.ok) {
                return r.json().then(err => {
                    throw new Error(JSON.stringify(err));
                });
            }
            return r.json();
        })
        .then(data => {
            if (data.success) {
                alert('Form Perjalanan Dinas berhasil disimpan');
                location.reload();
            } else {
                const errorMsg = data.message || (data.errors ? JSON.stringify(data.errors) : 'Gagal menyimpan');
                alert('Error: ' + errorMsg);
            }
        })
        .catch(err => {
            console.error('Error:', err);
            try {
                const errorData = JSON.parse(err.message);
                if (errorData.errors) {
                    const errorMessages = Object.values(errorData.errors).flat().join('\n');
                    alert('Error Validasi:\n' + errorMessages);
                } else {
                    alert('Error: ' + (errorData.message || 'Gagal menyimpan data'));
                }
            } catch (e) {
                alert('Error: Gagal menyimpan data. ' + err.message);
            }
        });
    });

    // Edit Record
    function editRecord(id) {
        fetch(makeUrl(`perjalanan-dinas/${id}`), {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                isEditMode = true;
                currentId = id;
                karyawanIndex = 0;
                jadwalIndex = 0;
                hotelIndex = 0;
                
                document.getElementById('perjalananDinasModalLabel').textContent = 'Edit Form Perjalanan Dinas';
                document.getElementById('_method').value = 'PUT';
                
                const rec = data.data;
                
                // Fill header - pastikan semua field terisi
                const dtTanggalFormValue = formatDateForInput(rec.dtTanggalForm);
                if (!dtTanggalFormValue) {
                    console.warn('dtTanggalForm is empty, using current date as fallback');
                    const today = new Date().toISOString().split('T')[0];
                    document.getElementById('dtTanggalForm').value = today;
                } else {
                    document.getElementById('dtTanggalForm').value = dtTanggalFormValue;
                }
                
                // Fill Pemberi Tugas dengan autocomplete format
                if (rec.vcPemberiTugas) {
                    // Coba cari di karyawanList untuk format "NIK - Nama"
                    const pemberiTugas = rec.vcPemberiTugas;
                    const foundKaryawan = karyawanList.find(k => k.nama === pemberiTugas || k.nik === pemberiTugas);
                    if (foundKaryawan) {
                        document.getElementById('vcPemberiTugas').value = `${foundKaryawan.nik} - ${foundKaryawan.nama}`;
                        document.getElementById('vcPemberiTugasHidden').value = foundKaryawan.nik;
                        loadPemberiTugasData(foundKaryawan.nik);
                    } else {
                        document.getElementById('vcPemberiTugas').value = pemberiTugas;
                    }
                }
                
                document.getElementById('dtTanggalDinasDari').value = formatDateForInput(rec.dtTanggalDinasDari) || '';
                document.getElementById('dtTanggalDinasSampai').value = formatDateForInput(rec.dtTanggalDinasSampai) || '';
                document.getElementById('intDurasiHari').value = rec.intDurasiHari || '';
                
                // Fill Pemberi Tugas dengan autocomplete format
                if (rec.vcPemberiTugas) {
                    // Coba cari di karyawanList untuk format "NIK - Nama"
                    const pemberiTugas = rec.vcPemberiTugas;
                    const foundKaryawan = karyawanList.find(k => k.nama === pemberiTugas || k.nik === pemberiTugas);
                    if (foundKaryawan) {
                        document.getElementById('vcPemberiTugas').value = `${foundKaryawan.nik} - ${foundKaryawan.nama}`;
                        document.getElementById('vcPemberiTugasHidden').value = foundKaryawan.nik;
                        loadPemberiTugasData(foundKaryawan.nik);
                    } else {
                        document.getElementById('vcPemberiTugas').value = pemberiTugas;
                    }
                }
                
                document.getElementById('vcJabatanPemberiTugas').value = rec.vcJabatanPemberiTugas || '';
                document.getElementById('vcTujuanDinas').value = rec.vcTujuanDinas || '';
                document.getElementById('vcMaksudPerjalananDinas').value = rec.vcMaksudPerjalananDinas || '';
                document.getElementById('vcMengajukan').value = rec.vcMengajukan || '';
                document.getElementById('vcMenyetujui').value = rec.vcMenyetujui || '';
                
                // Fill Mengetahui dengan autocomplete format
                if (rec.vcMengetahui) {
                    const mengetahui = rec.vcMengetahui;
                    const foundKaryawan = karyawanList.find(k => k.nama === mengetahui || k.nik === mengetahui);
                    if (foundKaryawan) {
                        document.getElementById('vcMengetahui').value = `${foundKaryawan.nik} - ${foundKaryawan.nama}`;
                        document.getElementById('vcMengetahuiHidden').value = foundKaryawan.nik;
                    } else {
                        document.getElementById('vcMengetahui').value = mengetahui;
                    }
                }
                
                // Fill tiba_kembali / destinasi
                if (rec.tiba_kembali) {
                    const tk = rec.tiba_kembali;
                    document.getElementById('tiba_kembali_dtTanggalTiba').value = formatDateForInput(tk.dtTanggalTiba) || '';
                    document.getElementById('tiba_kembali_dtJamTiba').value = formatTimeForInput(tk.dtJamTiba) || '';
                    document.getElementById('tiba_kembali_dtTanggalKembali').value = formatDateForInput(tk.dtTanggalKembali) || '';
                    document.getElementById('tiba_kembali_dtJamKembali').value = formatTimeForInput(tk.dtJamKembali) || '';
                    document.getElementById('tiba_kembali_vcKeteranganKedatangan').value = tk.vcKeteranganKedatangan || '';
                    document.getElementById('tiba_kembali_vcTandaTanganPihakBerwenang').value = tk.vcTandaTanganPihakBerwenang || '';
                } else {
                    // Clear tiba_kembali fields
                    document.getElementById('tiba_kembali_dtTanggalTiba').value = '';
                    document.getElementById('tiba_kembali_dtJamTiba').value = '';
                    document.getElementById('tiba_kembali_dtTanggalKembali').value = '';
                    document.getElementById('tiba_kembali_dtJamKembali').value = '';
                    document.getElementById('tiba_kembali_vcKeteranganKedatangan').value = '';
                    document.getElementById('tiba_kembali_vcTandaTanganPihakBerwenang').value = '';
                }
                
                // Debug: Log untuk memastikan field terisi
                console.log('Edit Record - dtTanggalForm:', document.getElementById('dtTanggalForm').value);
                
                // Clear containers
                document.getElementById('karyawanContainer').innerHTML = '';
                document.getElementById('jadwalContainer').innerHTML = '';
                document.getElementById('hotelContainer').innerHTML = '';
                
                // Load karyawans
                if (rec.karyawans && rec.karyawans.length > 0) {
                    rec.karyawans.forEach(k => addKaryawanRow(k));
                }
                
                // Load jadwals
                if (rec.jadwals && rec.jadwals.length > 0) {
                    rec.jadwals.forEach(j => addJadwalRow(j));
                }
                
                // Load hotels
                if (rec.hotels && rec.hotels.length > 0) {
                    rec.hotels.forEach(h => addHotelRow(h));
                }
                
                const modal = new bootstrap.Modal(document.getElementById('perjalananDinasModal'));
                modal.show();
            } else {
                alert('Error: ' + (data.message || 'Gagal memuat data'));
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Error: Gagal memuat data');
        });
    }

    // Preview Record
    let currentPreviewId = null;
    function previewRecord(id) {
        currentPreviewId = id;
        const previewBody = document.getElementById('previewModalBody');
        previewBody.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Memuat data...</p></div>';
        
        const modal = new bootstrap.Modal(document.getElementById('previewModal'));
        modal.show();
        
        fetch(makeUrl(`perjalanan-dinas/${id}`), {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const rec = data.data;
                let html = `
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Umum</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3"><strong>No. RPD:</strong></div>
                                <div class="col-md-3"><span class="badge bg-secondary">${rec.vcNoRpd || '-'}</span></div>
                                <div class="col-md-3"><strong>Tanggal Form:</strong></div>
                                <div class="col-md-3">${rec.dtTanggalForm || '-'}</div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-3"><strong>Tanggal Mulai Dinas:</strong></div>
                                <div class="col-md-3">${rec.dtTanggalDinasDari || '-'}</div>
                                <div class="col-md-3"><strong>Tanggal Sampai Dinas:</strong></div>
                                <div class="col-md-3">${rec.dtTanggalDinasSampai || '-'}</div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-3"><strong>Durasi:</strong></div>
                                <div class="col-md-3"><span class="badge bg-info">${rec.intDurasiHari || 0} hari</span></div>
                                <div class="col-md-3"><strong>Pemberi Tugas:</strong></div>
                                <div class="col-md-3">${rec.vcPemberiTugas || '-'}</div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-3"><strong>Jabatan Pemberi Tugas:</strong></div>
                                <div class="col-md-9">${rec.vcJabatanPemberiTugas || '-'}</div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-3"><strong>Tujuan Dinas:</strong></div>
                                <div class="col-md-9">${rec.vcTujuanDinas || '-'}</div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-3"><strong>Maksud/Uraian:</strong></div>
                                <div class="col-md-9">${rec.vcMaksudPerjalananDinas || '-'}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Karyawan Yang Ditugaskan</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>NIK</th>
                                            <th>Nama</th>
                                            <th>Bisnis Unit</th>
                                            <th>Departemen</th>
                                            <th>Jabatan</th>
                                            <th>Klasifikasi Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                `;
                
                if (rec.karyawans && rec.karyawans.length > 0) {
                    rec.karyawans.forEach((k, idx) => {
                        const bisnisUnit = k.karyawan?.divisi?.vcNamaDivisi || k.karyawan?.Divisi || '-';
                        const dept = k.departemen?.vcNamaDept || '-';
                        const jabatan = k.jabatan?.vcNamaJabatan || '-';
                        html += `
                            <tr>
                                <td>${idx + 1}</td>
                                <td>${k.vcNik || '-'}</td>
                                <td>${k.vcNamaKaryawan || '-'}</td>
                                <td>${bisnisUnit}</td>
                                <td>${dept}</td>
                                <td>${jabatan}</td>
                                <td>${k.vcKlasifikasiGrade || '-'}</td>
                            </tr>
                        `;
                    });
                } else {
                    html += '<tr><td colspan="7" class="text-center text-muted">Tidak ada data</td></tr>';
                }
                
                html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Jadwal dan Moda Perjalanan</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Moda Perjalanan</th>
                                            <th>Tanggal Berangkat</th>
                                            <th>Jam</th>
                                            <th>Tanggal Sampai</th>
                                            <th>Jam</th>
                                            <th>Keterangan Moda</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                `;
                
                if (rec.jadwals && rec.jadwals.length > 0) {
                    rec.jadwals.forEach((j, idx) => {
                        const tglBerangkat = j.dtTanggalBerangkat ? (j.dtTanggalBerangkat.includes(' ') ? j.dtTanggalBerangkat.split(' ')[0] : j.dtTanggalBerangkat) : '-';
                        const jamBerangkat = j.dtJamBerangkat ? (j.dtJamBerangkat.includes(':') ? j.dtJamBerangkat.substring(0, 5) : j.dtJamBerangkat) : '-';
                        const tglSampai = j.dtTanggalKembali ? (j.dtTanggalKembali.includes(' ') ? j.dtTanggalKembali.split(' ')[0] : j.dtTanggalKembali) : '-';
                        const jamSampai = j.dtJamKembali ? (j.dtJamKembali.includes(':') ? j.dtJamKembali.substring(0, 5) : j.dtJamKembali) : '-';
                        html += `
                            <tr>
                                <td>${idx + 1}</td>
                                <td><span class="badge bg-primary">${j.vcModaPerjalanan || '-'}</span></td>
                                <td>${tglBerangkat}</td>
                                <td>${jamBerangkat}</td>
                                <td>${tglSampai}</td>
                                <td>${jamSampai}</td>
                                <td>${j.vcKeteranganBerangkat || '-'}</td>
                            </tr>
                        `;
                    });
                } else {
                    html += '<tr><td colspan="7" class="text-center text-muted">Tidak ada data</td></tr>';
                }
                
                html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;
                
                if (rec.hotels && rec.hotels.length > 0 && rec.hotels.some(h => h.isMenginap)) {
                    html += `
                    <div class="card mb-3">
                        <div class="card-header bg-warning text-white">
                            <h5 class="mb-0"><i class="fas fa-hotel me-2"></i>Hotel / Penginapan</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal Menginap</th>
                                            <th>Kota/Provinsi/Negara</th>
                                            <th>Nama Hotel</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    `;
                    
                    rec.hotels.filter(h => h.isMenginap).forEach((h, idx) => {
                        const tglMenginap = h.dtTanggalMenginap ? (h.dtTanggalMenginap.includes(' ') ? h.dtTanggalMenginap.split(' ')[0] : h.dtTanggalMenginap) : '-';
                        html += `
                            <tr>
                                <td>${idx + 1}</td>
                                <td>${tglMenginap}</td>
                                <td>${h.vcKotaProvinsiNegara || '-'}</td>
                                <td>${h.vcNamaHotel || '-'}</td>
                                <td>${h.vcKeteranganHotel || '-'}</td>
                            </tr>
                        `;
                    });
                    
                    html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    `;
                }
                
                html += `
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="fas fa-signature me-2"></i>Otorisasi</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Mengajukan (Penerima Tugas):</strong><br>
                                    ${rec.vcMengajukan || '-'}
                                </div>
                                <div class="col-md-4">
                                    <strong>Menyetujui (Pemberi Tugas):</strong><br>
                                    ${rec.vcMenyetujui || '-'}
                                </div>
                                <div class="col-md-4">
                                    <strong>Mengetahui (HRD):</strong><br>
                                    ${rec.vcMengetahui || '-'}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                previewBody.innerHTML = html;
            } else {
                previewBody.innerHTML = '<div class="alert alert-danger">Error: ' + (data.message || 'Gagal memuat data') + '</div>';
            }
        })
        .catch(err => {
            console.error('Error:', err);
            previewBody.innerHTML = '<div class="alert alert-danger">Error: Gagal memuat data</div>';
        });
    }
    
    // Print from Preview
    document.getElementById('btnPrintFromPreview').addEventListener('click', function() {
        if (currentPreviewId) {
            window.open(makeUrl(`perjalanan-dinas/${currentPreviewId}/print`), '_blank');
        }
    });

    // Delete Record
    function deleteRecord(id) {
        if (!confirm('Apakah Anda yakin ingin menghapus Form Perjalanan Dinas ini?')) {
            return;
        }

        fetch(makeUrl(`perjalanan-dinas/${id}`), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Form Perjalanan Dinas berhasil dihapus');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Gagal menghapus'));
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Error: Gagal menghapus data');
        });
    }

    // Autocomplete untuk Pemberi Tugas
    let pemberiTugasTimeout;
    let pemberiTugasSelectedIndex = -1;
    const pemberiTugasInput = document.getElementById('vcPemberiTugas');
    const pemberiTugasAutocomplete = document.getElementById('pemberiTugasAutocomplete');
    const pemberiTugasHidden = document.getElementById('vcPemberiTugasHidden');
    const jabatanPemberiTugasInput = document.getElementById('vcJabatanPemberiTugas');

    if (pemberiTugasInput && pemberiTugasAutocomplete) {
        pemberiTugasInput.addEventListener('input', function() {
            const value = this.value.trim().toLowerCase();
            clearTimeout(pemberiTugasTimeout);
            
            if (value.length === 0) {
                pemberiTugasAutocomplete.style.display = 'none';
                pemberiTugasSelectedIndex = -1;
                pemberiTugasHidden.value = '';
                jabatanPemberiTugasInput.value = '';
                return;
            }
            
            if (value.length < 2) {
                pemberiTugasAutocomplete.style.display = 'none';
                return;
            }
            
            pemberiTugasTimeout = setTimeout(() => {
                const results = karyawanList.filter(k => {
                    const searchText = (k.nik + ' ' + k.nama + ' ' + k.divisi + ' ' + k.bagian).toLowerCase();
                    return searchText.includes(value);
                }).slice(0, 20);
                displayPemberiTugasAutocomplete(results);
            }, 200);
        });

        pemberiTugasInput.addEventListener('keydown', function(e) {
            const items = pemberiTugasAutocomplete.querySelectorAll('.autocomplete-item');
            if (items.length === 0) return;
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                pemberiTugasSelectedIndex = Math.min(pemberiTugasSelectedIndex + 1, items.length - 1);
                updatePemberiTugasSelectedItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                pemberiTugasSelectedIndex = Math.max(pemberiTugasSelectedIndex - 1, -1);
                updatePemberiTugasSelectedItem(items);
            } else if (e.key === 'Enter' && pemberiTugasSelectedIndex >= 0) {
                e.preventDefault();
                items[pemberiTugasSelectedIndex].click();
            }
        });

        pemberiTugasInput.addEventListener('blur', function() {
            setTimeout(() => {
                const value = this.value.trim();
                if (value && !pemberiTugasHidden.value) {
                    if (value.includes(' - ')) {
                        const nikOnly = value.split(' - ')[0].trim();
                        pemberiTugasInput.value = nikOnly;
                        pemberiTugasHidden.value = nikOnly;
                        loadPemberiTugasData(nikOnly);
                    } else {
                        pemberiTugasHidden.value = value;
                        loadPemberiTugasData(value);
                    }
                }
            }, 200);
        });
    }

    function displayPemberiTugasAutocomplete(karyawans) {
        if (!karyawans || karyawans.length === 0) {
            pemberiTugasAutocomplete.innerHTML = '<div class="autocomplete-item">Tidak ada karyawan ditemukan</div>';
            pemberiTugasAutocomplete.style.display = 'block';
            return;
        }
        pemberiTugasAutocomplete.innerHTML = '';
        karyawans.forEach((karyawan, index) => {
            if (!karyawan || !karyawan.nik) return;
            const item = document.createElement('div');
            item.className = 'autocomplete-item';
            item.innerHTML = `
                <strong>${karyawan.nik || ''}</strong> - ${karyawan.nama || ''}
                <small>Divisi: ${karyawan.divisi || '-'} | Bagian: ${karyawan.bagian || '-'}</small>
            `;
            item.addEventListener('click', function() {
                selectPemberiTugasKaryawan(karyawan);
            });
            pemberiTugasAutocomplete.appendChild(item);
        });
        pemberiTugasAutocomplete.style.display = 'block';
        pemberiTugasSelectedIndex = -1;
    }

    function selectPemberiTugasKaryawan(karyawan) {
        pemberiTugasInput.value = `${karyawan.nik} - ${karyawan.nama}`;
        pemberiTugasHidden.value = karyawan.nik;
        pemberiTugasAutocomplete.style.display = 'none';
        pemberiTugasSelectedIndex = -1;
        loadPemberiTugasData(karyawan.nik);
        pemberiTugasInput.focus();
    }

    function updatePemberiTugasSelectedItem(items) {
        items.forEach((item, index) => {
            item.classList.toggle('active', index === pemberiTugasSelectedIndex);
            if (index === pemberiTugasSelectedIndex) {
                item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
        });
    }

    function loadPemberiTugasData(nik) {
        if (!nik || !nik.trim()) {
            jabatanPemberiTugasInput.value = '';
            return;
        }
        fetch(makeUrl(`karyawan/${nik.trim()}`), {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.karyawan) {
                // Auto-fill Jabatan Pemberi Tugas
                if (data.karyawan.jabatan && data.karyawan.jabatan.vcNamaJabatan) {
                    jabatanPemberiTugasInput.value = data.karyawan.jabatan.vcNamaJabatan;
                } else if (data.karyawan.Jabat) {
                    const jabatan = data.karyawan.Jabat.includes(' -> ') 
                        ? data.karyawan.Jabat.split(' -> ')[1] 
                        : data.karyawan.Jabat;
                    jabatanPemberiTugasInput.value = jabatan;
                }
                
                // Auto-fill Menyetujui (Pemberi Tugas) di Otorisasi
                document.getElementById('vcMenyetujui').value = data.karyawan.Nama || '';
            } else {
                jabatanPemberiTugasInput.value = '';
            }
        })
        .catch(() => {
            jabatanPemberiTugasInput.value = '';
        });
    }

    // Autocomplete untuk Mengetahui (HRD)
    let mengetahuiTimeout;
    let mengetahuiSelectedIndex = -1;
    const mengetahuiInput = document.getElementById('vcMengetahui');
    const mengetahuiAutocomplete = document.getElementById('mengetahuiAutocomplete');
    const mengetahuiHidden = document.getElementById('vcMengetahuiHidden');

    if (mengetahuiInput && mengetahuiAutocomplete) {
        mengetahuiInput.addEventListener('input', function() {
            const value = this.value.trim().toLowerCase();
            clearTimeout(mengetahuiTimeout);
            
            if (value.length === 0) {
                mengetahuiAutocomplete.style.display = 'none';
                mengetahuiSelectedIndex = -1;
                mengetahuiHidden.value = '';
                return;
            }
            
            if (value.length < 2) {
                mengetahuiAutocomplete.style.display = 'none';
                return;
            }
            
            mengetahuiTimeout = setTimeout(() => {
                const results = karyawanList.filter(k => {
                    const searchText = (k.nik + ' ' + k.nama + ' ' + k.divisi + ' ' + k.bagian).toLowerCase();
                    return searchText.includes(value);
                }).slice(0, 20);
                displayMengetahuiAutocomplete(results);
            }, 200);
        });

        mengetahuiInput.addEventListener('keydown', function(e) {
            const items = mengetahuiAutocomplete.querySelectorAll('.autocomplete-item');
            if (items.length === 0) return;
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                mengetahuiSelectedIndex = Math.min(mengetahuiSelectedIndex + 1, items.length - 1);
                updateMengetahuiSelectedItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                mengetahuiSelectedIndex = Math.max(mengetahuiSelectedIndex - 1, -1);
                updateMengetahuiSelectedItem(items);
            } else if (e.key === 'Enter' && mengetahuiSelectedIndex >= 0) {
                e.preventDefault();
                items[mengetahuiSelectedIndex].click();
            }
        });

        mengetahuiInput.addEventListener('blur', function() {
            setTimeout(() => {
                const value = this.value.trim();
                if (value && !mengetahuiHidden.value) {
                    if (value.includes(' - ')) {
                        const nikOnly = value.split(' - ')[0].trim();
                        mengetahuiInput.value = nikOnly;
                        mengetahuiHidden.value = nikOnly;
                    } else {
                        mengetahuiHidden.value = value;
                    }
                }
            }, 200);
        });
    }

    function displayMengetahuiAutocomplete(karyawans) {
        if (!karyawans || karyawans.length === 0) {
            mengetahuiAutocomplete.innerHTML = '<div class="autocomplete-item">Tidak ada karyawan ditemukan</div>';
            mengetahuiAutocomplete.style.display = 'block';
            return;
        }
        mengetahuiAutocomplete.innerHTML = '';
        karyawans.forEach((karyawan, index) => {
            if (!karyawan || !karyawan.nik) return;
            const item = document.createElement('div');
            item.className = 'autocomplete-item';
            item.innerHTML = `
                <strong>${karyawan.nik || ''}</strong> - ${karyawan.nama || ''}
                <small>Divisi: ${karyawan.divisi || '-'} | Bagian: ${karyawan.bagian || '-'}</small>
            `;
            item.addEventListener('click', function() {
                selectMengetahuiKaryawan(karyawan);
            });
            mengetahuiAutocomplete.appendChild(item);
        });
        mengetahuiAutocomplete.style.display = 'block';
        mengetahuiSelectedIndex = -1;
    }

    function selectMengetahuiKaryawan(karyawan) {
        mengetahuiInput.value = `${karyawan.nik} - ${karyawan.nama}`;
        mengetahuiHidden.value = karyawan.nik;
        mengetahuiAutocomplete.style.display = 'none';
        mengetahuiSelectedIndex = -1;
        mengetahuiInput.focus();
    }

    function updateMengetahuiSelectedItem(items) {
        items.forEach((item, index) => {
            item.classList.toggle('active', index === mengetahuiSelectedIndex);
            if (index === mengetahuiSelectedIndex) {
                item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
        });
    }

    // Auto-fill Mengajukan saat karyawan pertama ditambahkan
    const originalAddKaryawanRow = addKaryawanRow;
    addKaryawanRow = function(data = null) {
        const result = originalAddKaryawanRow.call(this, data);
        
        // Setelah karyawan ditambahkan, update Mengajukan jika belum ada
        setTimeout(() => {
            const karyawanRows = document.querySelectorAll('.karyawan-row');
            if (karyawanRows.length > 0 && !document.getElementById('vcMengajukan').value) {
                const firstKaryawanRow = karyawanRows[0];
                const namaInput = firstKaryawanRow.querySelector('.nik-input');
                if (namaInput && namaInput.value) {
                    // Ambil nama dari format "NIK - Nama"
                    const namaValue = namaInput.value.includes(' - ') 
                        ? namaInput.value.split(' - ')[1] 
                        : namaInput.value;
                    document.getElementById('vcMengajukan').value = namaValue;
                }
            }
        }, 100);
        
        return result;
    };

    // Update Mengajukan saat karyawan pertama berubah
    document.addEventListener('input', function(e) {
        if (e.target && e.target.classList.contains('nik-input')) {
            const karyawanRows = document.querySelectorAll('.karyawan-row');
            if (karyawanRows.length > 0) {
                const firstRow = karyawanRows[0];
                if (e.target.closest('.karyawan-row') === firstRow) {
                    const namaValue = e.target.value.includes(' - ') 
                        ? e.target.value.split(' - ')[1] 
                        : e.target.value;
                    document.getElementById('vcMengajukan').value = namaValue || '';
                }
            }
        }
    });
</script>
@endpush

