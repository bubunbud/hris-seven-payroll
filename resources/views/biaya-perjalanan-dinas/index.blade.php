@extends('layouts.app')

@section('title', 'Form Biaya Perjalanan Dinas - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-money-bill-wave me-2"></i>Form Biaya Perjalanan Dinas (BPD)
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
                    <form method="GET" action="{{ route('biaya-perjalanan-dinas.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="dari_tanggal" class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" id="dari_tanggal" name="dari_tanggal" value="{{ $startDate }}">
                            </div>
                            <div class="col-md-3">
                                <label for="sampai_tanggal" class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" id="sampai_tanggal" name="sampai_tanggal" value="{{ $endDate }}">
                            </div>
                            <div class="col-md-4">
                                <label for="search" class="form-label">No BPD / No RPD</label>
                                <input type="text"
                                    class="form-control"
                                    id="search"
                                    name="search"
                                    value="{{ $search ?? '' }}"
                                    placeholder="Cari No BPD atau No RPD"
                                    autocomplete="off">
                            </div>
                            <div class="col-md-2">
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
                                    <th width="10%">Status</th>
                                    <th width="10%">No. BPD</th>
                                    <th width="10%">No. RPD</th>
                                    <th width="13%">Pemberi Tugas</th>
                                    <th width="10%">Kasbon</th>
                                    <th width="12%">Total Pengeluaran</th>
                                    <th width="12%">Kekurangan/Kelebihan</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($records as $row)
                                <tr>
                                    <td>
                                        @if(($row->vcStatus ?? 'complete') === 'draft')
                                            <span class="badge bg-warning text-dark">Draft</span>
                                        @else
                                            <span class="badge bg-success">Lengkap</span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-secondary">{{ $row->vcNoBpd ?? '-' }}</span></td>
                                    <td><span class="badge bg-info">{{ $row->vcNoRpd ?? '-' }}</span></td>
                                    <td>{{ $row->vcPemberiTugas ?? '-' }}</td>
                                    <td class="text-end">{{ $row->decKasbonNilai ? number_format($row->decKasbonNilai, 0, ',', '.') : '0' }}</td>
                                    <td class="text-end">{{ $row->decTotalPengeluaran ? number_format($row->decTotalPengeluaran, 0, ',', '.') : '0' }}</td>
                                    <td class="text-end">
                                        @if($row->decKekuranganKelebihan)
                                            <span class="badge {{ $row->decKekuranganKelebihan < 0 ? 'bg-danger' : ($row->decKekuranganKelebihan > 0 ? 'bg-warning' : 'bg-success') }}">
                                                {{ number_format($row->decKekuranganKelebihan, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('biaya-perjalanan-dinas.print', $row->vcNoBpd) }}" class="btn btn-outline-success" target="_blank" title="Print">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <button class="btn btn-outline-primary" onclick="editRecord('{{ $row->vcNoBpd }}')" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteRecord('{{ $row->vcNoBpd }}')" title="Hapus">
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

<!-- Modal Form -->
<div class="modal fade" id="bpdModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bpdModalLabel">Tambah Form Biaya Perjalanan Dinas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bpdForm" novalidate>
                <input type="hidden" name="_method" id="_method" value="POST">
                <input type="hidden" name="is_draft" id="is_draft" value="0">
                <div class="modal-body" style="max-height: calc(100vh - 200px); overflow-y: auto; padding-bottom: 20px;">
                    <!-- Header Section -->
                    <div class="card mb-3 border-primary">
                        <div class="card-header bg-primary text-white">
                            <strong>1. Informasi Umum</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="vcNoRpd" class="form-label">No. RPD <span class="text-danger">*</span></label>
                                    <select class="form-select" id="vcNoRpd" name="vcNoRpd" required>
                                        <option value="">Pilih No. RPD</option>
                                        @foreach($rpdList as $rpd)
                                        <option value="{{ $rpd->vcNoRpd }}" 
                                            data-pemberi-tugas="{{ $rpd->vcPemberiTugas }}"
                                            data-tujuan="{{ $rpd->vcTujuanDinas }}">
                                            {{ $rpd->vcNoRpd }} - {{ $rpd->vcPemberiTugas }} ({{ $rpd->vcTujuanDinas }})
                                        </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Pilih RPD yang sudah dibuat sebelumnya</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="vcPemberiTugas" class="form-label">Pemberi Tugas <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="vcPemberiTugas" name="vcPemberiTugas" maxlength="100" required readonly style="background-color: #e9ecef;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kasbon Section -->
                    <div class="card mb-3 border-warning">
                        <div class="card-header bg-warning text-white">
                            <strong>2. Kasbon Perjalanan Dinas</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="decKasbonNilai" class="form-label">Nilai Kasbon</label>
                                    <input type="number" class="form-control" id="decKasbonNilai" name="decKasbonNilai" step="0.01" min="0" placeholder="0">
                                </div>
                                <div class="col-md-6">
                                    <label for="vcKasbonTerbilang" class="form-label">Terbilang</label>
                                    <input type="text" class="form-control" id="vcKasbonTerbilang" name="vcKasbonTerbilang" maxlength="500" placeholder="Otomatis terisi" readonly style="background-color: #e9ecef;">
                                    <small class="text-muted">Otomatis terisi dari nilai kasbon</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Biaya Section -->
                    <div class="card mb-3 border-success">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <strong>3. Laporan Biaya Perjalanan Dinas</strong>
                            <button type="button" class="btn btn-sm btn-light" id="btnAddDetail">
                                <i class="fas fa-plus me-1"></i>Tambah Detail
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Catatan:</strong> Kuitansi dan bukti pembayaran yang sah agar dilampirkan secara tersusun
                            </div>
                            <div id="detailContainer">
                                <!-- Detail rows akan ditambahkan di sini -->
                            </div>
                        </div>
                    </div>

                    <!-- Summary Section -->
                    <div class="card mb-3 border-info">
                        <div class="card-header bg-info text-white">
                            <strong>4. Ringkasan</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="decTotalPengeluaran" class="form-label">Total Pengeluaran</label>
                                    <input type="number" class="form-control" id="decTotalPengeluaran" name="decTotalPengeluaran" step="0.01" min="0" readonly style="background-color: #e9ecef;" value="0">
                                    <small class="text-muted">Otomatis dihitung dari detail biaya</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="decKekuranganKelebihan" class="form-label">Kekurangan / Kelebihan</label>
                                    <input type="number" class="form-control" id="decKekuranganKelebihan" name="decKekuranganKelebihan" step="0.01" readonly style="background-color: #e9ecef;" value="0">
                                    <small class="text-muted">Otomatis dihitung: Total Pengeluaran - Kasbon</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Laporan Singkat Section -->
                    <div class="card mb-3 border-secondary">
                        <div class="card-header bg-secondary text-white">
                            <strong>5. Laporan Singkat</strong>
                        </div>
                        <div class="card-body">
                            <textarea class="form-control" id="vcLaporanSingkat" name="vcLaporanSingkat" rows="4" placeholder="Tuliskan laporan singkat perjalanan dinas"></textarea>
                        </div>
                    </div>

                    <!-- Otorisasi Section -->
                    <div class="card mb-3 border-dark">
                        <div class="card-header bg-dark text-white">
                            <strong>6. Otorisasi</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="vcMelaporkan" class="form-label">Melaporkan - Penerima Tugas</label>
                                    <input type="text" class="form-control" id="vcMelaporkan" name="vcMelaporkan" maxlength="100" placeholder="Nama Penerima Tugas">
                                </div>
                                <div class="col-md-3">
                                    <label for="vcMenyetujui" class="form-label">Menyetujui - Pemberi Tugas</label>
                                    <input type="text" class="form-control" id="vcMenyetujui" name="vcMenyetujui" maxlength="100" placeholder="Nama Pemberi Tugas">
                                </div>
                                <div class="col-md-3">
                                    <label for="vcMengetahuiHrd" class="form-label">Mengetahui - HRD</label>
                                    <input type="text" class="form-control" id="vcMengetahuiHrd" name="vcMengetahuiHrd" maxlength="100" placeholder="Nama HRD">
                                </div>
                                <div class="col-md-3">
                                    <label for="vcMengetahuiFinance" class="form-label">Mengetahui - Finance & Accounting</label>
                                    <input type="text" class="form-control" id="vcMengetahuiFinance" name="vcMengetahuiFinance" maxlength="100" placeholder="Nama Finance">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="position: sticky; bottom: 0; background-color: white; border-top: 1px solid #dee2e6; z-index: 10; padding: 1rem;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-outline-warning" id="btnSimpanDraft" title="Simpan hanya Bagian 1 (Informasi Umum) dan Bagian 2 (Kasbon)">
                        <i class="fas fa-file-alt me-1"></i>Simpan Draft
                    </button>
                    <button type="button" class="btn btn-primary" id="btnSimpanLengkap">
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

    let isEditMode = false;
    let currentId = null;
    let detailIndex = 0;

    // Kategori dan Sub Kategori
    const kategoriBiaya = {
        'Penginapan': [],
        'Kendaraan Umum': ['Lokal Rumah', 'Antar Kota (PP)', 'Lokal Lokasi'],
        'Kendaraan Dinas/Pribadi': ['Bensin', 'Tol', 'Parkir'],
        'Makan/Minum': ['Makan/minum (lump sum)', 'Makan/minum (on bill)', 'Uang saku'],
        'Lain-lain': []
    };

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

    // Simpan Draft - set flag lalu submit
    document.getElementById('btnSimpanDraft').addEventListener('click', function() {
        document.getElementById('is_draft').value = '1';
        document.getElementById('bpdForm').requestSubmit();
    });

    // Simpan Lengkap - set flag lalu submit (default)
    document.getElementById('btnSimpanLengkap').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('is_draft').value = '0';
        document.getElementById('bpdForm').requestSubmit();
    });

    // Add Button Click
    document.getElementById('addBtn').addEventListener('click', function() {
        isEditMode = false;
        currentId = null;
        detailIndex = 0;
        
        document.getElementById('bpdModalLabel').textContent = 'Tambah Form Biaya Perjalanan Dinas';
        document.getElementById('_method').value = 'POST';
        document.getElementById('is_draft').value = '0';
        document.getElementById('bpdForm').reset();
        
        // Hapus opsi RPD yang ditambah saat edit (RPD dengan BPD status lengkap)
        const rpdSelect = document.getElementById('vcNoRpd');
        Array.from(rpdSelect.options).filter(opt => opt.getAttribute('data-dynamic') === '1').forEach(opt => opt.remove());
        
        // Clear containers
        document.getElementById('detailContainer').innerHTML = '';
        
        // Clear readonly fields
        document.getElementById('vcPemberiTugas').value = '';
        document.getElementById('decTotalPengeluaran').value = '0';
        document.getElementById('decKekuranganKelebihan').value = '0';
        document.getElementById('vcKasbonTerbilang').value = '';
        
        const modal = new bootstrap.Modal(document.getElementById('bpdModal'));
        modal.show();
    });

    // RPD Select Change - Auto-fill data
    document.getElementById('vcNoRpd').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const noRpd = selectedOption.value;
            const pemberiTugas = selectedOption.getAttribute('data-pemberi-tugas');
            
            // Auto-fill Pemberi Tugas
            document.getElementById('vcPemberiTugas').value = pemberiTugas || '';
            
            // Fetch RPD data untuk auto-fill otorisasi
            fetch(makeUrl(`biaya-perjalanan-dinas/get-rpd-data/${noRpd}`), {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('vcMenyetujui').value = data.data.vcMenyetujui || data.data.vcPemberiTugas || '';
                    document.getElementById('vcMelaporkan').value = data.data.vcMelaporkan || '';
                }
            })
            .catch(err => {
                console.error('Error fetching RPD data:', err);
            });
        } else {
            document.getElementById('vcPemberiTugas').value = '';
            document.getElementById('vcMenyetujui').value = '';
            document.getElementById('vcMelaporkan').value = '';
        }
    });

    // Kasbon Nilai Change - Auto-generate Terbilang
    document.getElementById('decKasbonNilai').addEventListener('input', function() {
        const nilai = parseFloat(this.value) || 0;
        if (nilai > 0) {
            // Fetch terbilang dari server
            fetch(makeUrl(`biaya-perjalanan-dinas/convert-terbilang/${nilai}`), {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('vcKasbonTerbilang').value = data.terbilang || '';
                }
            })
            .catch(err => {
                console.error('Error converting terbilang:', err);
            });
        } else {
            document.getElementById('vcKasbonTerbilang').value = '';
        }
        calculateSummary();
    });

    // Add Detail Row
    document.getElementById('btnAddDetail').addEventListener('click', function() {
        addDetailRow();
    });

    function addDetailRow(data = null) {
        const index = detailIndex++;
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 detail-row border-bottom pb-2';
        row.dataset.index = index;

        const kategoriOptions = Object.keys(kategoriBiaya).map(k => 
            `<option value="${k}" ${data && data.vcKategoriBiaya === k ? 'selected' : ''}>${k}</option>`
        ).join('');

        row.innerHTML = `
            <div class="col-md-2">
                <label class="form-label small">Kategori Biaya <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm kategori-select" name="details[${index}][vcKategoriBiaya]" required>
                    <option value="">Pilih Kategori</option>
                    ${kategoriOptions}
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Sub Kategori</label>
                <select class="form-select form-select-sm subkategori-select" name="details[${index}][vcSubKategori]">
                    <option value="">Pilih Sub Kategori</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Tanggal Dari</label>
                <input type="date" class="form-control form-control-sm tanggal-dari" name="details[${index}][dtTanggalDari]">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Tanggal Sampai</label>
                <input type="date" class="form-control form-control-sm tanggal-sampai" name="details[${index}][dtTanggalSampai]">
            </div>
            <div class="col-md-1">
                <label class="form-label small">Nilai</label>
                <input type="number" class="form-control form-control-sm nilai-input" name="details[${index}][decNilai]" step="0.01" min="0" value="${data ? (data.decNilai || '') : ''}">
            </div>
            <div class="col-md-1">
                <label class="form-label small">Total</label>
                <input type="number" class="form-control form-control-sm total-input" name="details[${index}][decTotal]" step="0.01" min="0" value="${data ? (data.decTotal || '') : ''}">
            </div>
            <div class="col-md-1">
                <label class="form-label small">Keterangan</label>
                <input type="text" class="form-control form-control-sm" name="details[${index}][vcKeterangan]" value="${data ? (data.vcKeterangan || '') : ''}">
            </div>
            <div class="col-md-1">
                <label class="form-label small">&nbsp;</label>
                <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeDetailRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;

        document.getElementById('detailContainer').appendChild(row);

        // Setup kategori change event
        const kategoriSelect = row.querySelector('.kategori-select');
        const subkategoriSelect = row.querySelector('.subkategori-select');
        
        kategoriSelect.addEventListener('change', function() {
            const kategori = this.value;
            const subKategoris = kategoriBiaya[kategori] || [];
            
            subkategoriSelect.innerHTML = '<option value="">Pilih Sub Kategori</option>';
            subKategoris.forEach(sub => {
                subkategoriSelect.innerHTML += `<option value="${sub}">${sub}</option>`;
            });
            
            // Show/hide tanggal fields untuk Penginapan
            const tanggalDari = row.querySelector('.tanggal-dari').closest('.col-md-2');
            const tanggalSampai = row.querySelector('.tanggal-sampai').closest('.col-md-2');
            if (kategori === 'Penginapan') {
                tanggalDari.style.display = 'block';
                tanggalSampai.style.display = 'block';
            } else {
                tanggalDari.style.display = 'none';
                tanggalSampai.style.display = 'none';
                row.querySelector('.tanggal-dari').value = '';
                row.querySelector('.tanggal-sampai').value = '';
            }
        });

        // Setup nilai/total change event untuk calculate summary
        row.querySelector('.nilai-input').addEventListener('input', function() {
            const nilai = parseFloat(this.value) || 0;
            const totalInput = row.querySelector('.total-input');
            if (!totalInput.value || parseFloat(totalInput.value) === 0) {
                totalInput.value = nilai;
            }
            calculateSummary();
        });

        row.querySelector('.total-input').addEventListener('input', function() {
            calculateSummary();
        });

        // Load data if editing
        if (data) {
            kategoriSelect.dispatchEvent(new Event('change'));
            setTimeout(() => {
                if (data.vcSubKategori) {
                    subkategoriSelect.value = data.vcSubKategori;
                }
                if (data.dtTanggalDari) {
                    row.querySelector('.tanggal-dari').value = formatDateForInput(data.dtTanggalDari);
                }
                if (data.dtTanggalSampai) {
                    row.querySelector('.tanggal-sampai').value = formatDateForInput(data.dtTanggalSampai);
                }
            }, 100);
        }
    }

    function removeDetailRow(btn) {
        btn.closest('.detail-row').remove();
        calculateSummary();
    }

    // Calculate Summary
    function calculateSummary() {
        let totalPengeluaran = 0;
        document.querySelectorAll('.total-input').forEach(input => {
            const total = parseFloat(input.value) || 0;
            totalPengeluaran += total;
        });

        const kasbonNilai = parseFloat(document.getElementById('decKasbonNilai').value) || 0;
        const kekuranganKelebihan = totalPengeluaran - kasbonNilai;

        document.getElementById('decTotalPengeluaran').value = totalPengeluaran.toFixed(2);
        document.getElementById('decKekuranganKelebihan').value = kekuranganKelebihan.toFixed(2);
    }

    // Form Submit
    document.getElementById('bpdForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Pastikan _method terkirim untuk PUT request
        if (isEditMode) {
            formData.append('_method', 'PUT');
        }
        
        const url = isEditMode 
            ? makeUrl(`biaya-perjalanan-dinas/${currentId}`)
            : makeUrl('biaya-perjalanan-dinas');
        
        const method = 'POST';

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
                alert('Form BPD berhasil disimpan');
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
        fetch(makeUrl(`biaya-perjalanan-dinas/${id}`), {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                isEditMode = true;
                currentId = id;
                detailIndex = 0;
                
                document.getElementById('bpdModalLabel').textContent = 'Edit Form Biaya Perjalanan Dinas';
                document.getElementById('_method').value = 'PUT';
                document.getElementById('is_draft').value = '0';
                
                const rec = data.data;
                
                // Pastikan RPD saat edit ada di dropdown (bisa tidak ada jika status lengkap)
                const rpdSelect = document.getElementById('vcNoRpd');
                const hasOption = Array.from(rpdSelect.options).some(opt => opt.value === (rec.vcNoRpd || ''));
                if (rec.vcNoRpd && !hasOption) {
                    const pd = rec.perjalanan_dinas || rec.perjalananDinas || {};
                    const tujuan = pd.vcTujuanDinas || pd.vc_tujuan_dinas || '';
                    const label = rec.vcNoRpd + ' - ' + (rec.vcPemberiTugas || '') + (tujuan ? ' (' + tujuan + ')' : '');
                    const opt = new Option(label, rec.vcNoRpd);
                    opt.setAttribute('data-pemberi-tugas', rec.vcPemberiTugas || '');
                    opt.setAttribute('data-tujuan', tujuan);
                    opt.setAttribute('data-dynamic', '1');
                    rpdSelect.appendChild(opt);
                }
                
                // Fill header
                rpdSelect.value = rec.vcNoRpd || '';
                document.getElementById('vcPemberiTugas').value = rec.vcPemberiTugas || '';
                document.getElementById('decKasbonNilai').value = rec.decKasbonNilai || '';
                document.getElementById('vcKasbonTerbilang').value = rec.vcKasbonTerbilang || '';
                document.getElementById('vcLaporanSingkat').value = rec.vcLaporanSingkat || '';
                document.getElementById('vcMelaporkan').value = rec.vcMelaporkan || '';
                document.getElementById('vcMenyetujui').value = rec.vcMenyetujui || '';
                document.getElementById('vcMengetahuiHrd').value = rec.vcMengetahuiHrd || '';
                document.getElementById('vcMengetahuiFinance').value = rec.vcMengetahuiFinance || '';
                
                // Clear containers
                document.getElementById('detailContainer').innerHTML = '';
                
                // Load details
                if (rec.details && rec.details.length > 0) {
                    rec.details.forEach(d => addDetailRow(d));
                }
                
                calculateSummary();
                
                const modal = new bootstrap.Modal(document.getElementById('bpdModal'));
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

    // Delete Record
    function deleteRecord(id) {
        if (!confirm('Apakah Anda yakin ingin menghapus Form BPD ini?')) {
            return;
        }

        fetch(makeUrl(`biaya-perjalanan-dinas/${id}`), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Form BPD berhasil dihapus');
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
</script>
@endpush

