@extends('layouts.app')

@section('title', 'Tambah Tukar Hari Kerja - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-exchange-alt me-2"></i>Tambah Tukar Hari Kerja
                </h2>
                <a href="{{ route('tukar-hari-kerja.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <form action="{{ route('tukar-hari-kerja.store') }}" method="POST" id="tukarHariKerjaForm">
                @csrf

                <!-- Header Section -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Tukar Hari Kerja</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Tipe Tukar <span class="text-danger">*</span></label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="vcTipeTukar" id="tipe_libur_kerja" value="LIBUR_KE_KERJA" checked>
                                    <label class="form-check-label" for="tipe_libur_kerja">
                                        Libur → Kerja (Hari libur ditukar menjadi hari kerja normal)
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="vcTipeTukar" id="tipe_kerja_libur" value="KERJA_KE_LIBUR">
                                    <label class="form-check-label" for="tipe_kerja_libur">
                                        Kerja → Libur (Hari kerja normal ditukar menjadi hari libur)
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="dtTanggalLibur" class="form-label">Tanggal Libur <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dtTanggalLibur" name="dtTanggalLibur" required>
                                <small class="text-muted">Tanggal hari libur yang ditukar</small>
                            </div>

                            <div class="col-md-4">
                                <label for="dtTanggalKerja" class="form-label">Tanggal Kerja <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dtTanggalKerja" name="dtTanggalKerja" required>
                                <small class="text-muted">Tanggal hari kerja pengganti</small>
                            </div>

                            <div class="col-md-4">
                                <label for="vcKeterangan" class="form-label">Keterangan</label>
                                <input type="text" class="form-control" id="vcKeterangan" name="vcKeterangan" placeholder="Alasan tukar hari kerja">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Scope <span class="text-danger">*</span></label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="vcScope" id="scope_perorangan" value="PERORANGAN" checked>
                                    <label class="form-check-label" for="scope_perorangan">Perorangan</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="vcScope" id="scope_group" value="GROUP">
                                    <label class="form-check-label" for="scope_group">Group</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="vcScope" id="scope_semua_bu" value="SEMUA_BU">
                                    <label class="form-check-label" for="scope_semua_bu">Semua BU</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Section (untuk GROUP dan SEMUA_BU) -->
                <div class="card mb-3" id="filterSection" style="display: none;">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Karyawan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="vcKodeDivisi" class="form-label">Divisi <span class="text-danger">*</span></label>
                                <select class="form-select" id="vcKodeDivisi" name="vcKodeDivisi">
                                    <option value="">Pilih Divisi</option>
                                    @foreach($divisis as $divisi)
                                    <option value="{{ $divisi->vcKodeDivisi }}">{{ $divisi->vcKodeDivisi }} - {{ $divisi->vcNamaDivisi }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="vcKodeDept" class="form-label">Departemen</label>
                                <select class="form-select" id="vcKodeDept" name="vcKodeDept" disabled>
                                    <option value="">Pilih Divisi terlebih dahulu</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="vcKodeBagian" class="form-label">Bagian</label>
                                <select class="form-select" id="vcKodeBagian" name="vcKodeBagian" disabled>
                                    <option value="">Pilih Departemen terlebih dahulu</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <button type="button" class="btn btn-primary" id="btnLoadKaryawan">
                                    <i class="fas fa-search me-2"></i>Load Karyawan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employee Selection Section -->
                <div class="card mb-3" id="employeeSection" style="display: none;">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Pilih Karyawan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Available Employees -->
                            <div class="col-md-5">
                                <h6>Karyawan Tersedia</h6>
                                <div class="mb-2">
                                    <input type="text" class="form-control form-control-sm" id="searchAvailable" placeholder="Cari...">
                                </div>
                                <div class="border rounded p-2" style="height: 400px; overflow-y: auto;">
                                    <table class="table table-sm table-hover mb-0" id="tableAvailable">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th width="5%">
                                                    <input type="checkbox" id="selectAllAvailable">
                                                </th>
                                                <th width="20%">NIK</th>
                                                <th>Nama</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyAvailable">
                                            <!-- Will be populated by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">Total: <span id="totalAvailable">0</span> karyawan</small>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                                <button type="button" class="btn btn-primary mb-2" id="btnAddSelected">
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                                <button type="button" class="btn btn-secondary" id="btnRemoveSelected">
                                    <i class="fas fa-arrow-left"></i>
                                </button>
                            </div>

                            <!-- Selected Employees -->
                            <div class="col-md-5">
                                <h6>Karyawan Terpilih</h6>
                                <div class="mb-2">
                                    <input type="text" class="form-control form-control-sm" id="searchSelected" placeholder="Cari...">
                                </div>
                                <div class="border rounded p-2" style="height: 400px; overflow-y: auto;">
                                    <table class="table table-sm table-hover mb-0" id="tableSelected">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th width="5%">
                                                    <input type="checkbox" id="selectAllSelected">
                                                </th>
                                                <th width="20%">NIK</th>
                                                <th>Nama</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodySelected">
                                            <!-- Will be populated by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">Total: <span id="totalSelected">0</span> karyawan</small>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden input for selected karyawan -->
                        <input type="hidden" name="karyawan_ids" id="karyawan_ids" value="">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-info" id="btnPreview">
                                <i class="fas fa-eye me-2"></i>Preview
                            </button>
                            <div>
                                <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('tukar-hari-kerja.index') }}'">
                                    <i class="fas fa-times me-2"></i>Batal
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview Tukar Hari Kerja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- Will be populated by JavaScript -->
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
let availableKaryawans = [];
let selectedKaryawans = [];

// Function to handle scope change
function handleScopeChange(scopeValue) {
    const filterSection = document.getElementById('filterSection');
    const employeeSection = document.getElementById('employeeSection');
    
    if (scopeValue === 'PERORANGAN') {
        filterSection.style.display = 'none';
        employeeSection.style.display = 'block';
        loadAllKaryawans();
    } else {
        filterSection.style.display = 'block';
        employeeSection.style.display = 'none';
    }
}

// Toggle filter section based on scope
document.querySelectorAll('input[name="vcScope"]').forEach(radio => {
    radio.addEventListener('change', function() {
        handleScopeChange(this.value);
    });
});

// Initialize on page load - check if PERORANGAN is already selected
document.addEventListener('DOMContentLoaded', function() {
    const checkedScope = document.querySelector('input[name="vcScope"]:checked');
    if (checkedScope) {
        handleScopeChange(checkedScope.value);
    }
});

// Load karyawan berdasarkan filter
document.getElementById('btnLoadKaryawan')?.addEventListener('click', function() {
    const divisi = document.getElementById('vcKodeDivisi').value;
    const dept = document.getElementById('vcKodeDept').value;
    const bagian = document.getElementById('vcKodeBagian').value;

    if (!divisi) {
        alert('Pilih Divisi terlebih dahulu');
        return;
    }

    fetch('{{ route("tukar-hari-kerja.get-karyawan") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            divisi: divisi,
            departemen: dept,
            bagian: bagian
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            availableKaryawans = data.karyawans;
            renderAvailableKaryawans();
            document.getElementById('employeeSection').style.display = 'block';
        }
    });
});

// Load all karyawan untuk scope PERORANGAN
function loadAllKaryawans() {
    // Show loading state
    const tbody = document.getElementById('tbodyAvailable');
    tbody.innerHTML = '<tr><td colspan="3" class="text-center">Memuat data karyawan...</td></tr>';
    
    fetch('{{ route("tukar-hari-kerja.get-karyawan") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            availableKaryawans = data.karyawans || [];
            renderAvailableKaryawans();
        } else {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Gagal memuat data karyawan</td></tr>';
        }
    })
    .catch(error => {
        console.error('Error loading karyawan:', error);
        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Terjadi kesalahan saat memuat data</td></tr>';
    });
}

// Render available karyawans
function renderAvailableKaryawans() {
    const tbody = document.getElementById('tbodyAvailable');
    const search = document.getElementById('searchAvailable').value.toLowerCase();
    
    let filtered = availableKaryawans.filter(k => {
        const nik = k.Nik ? k.Nik.toLowerCase() : '';
        const nama = k.Nama ? k.Nama.toLowerCase() : '';
        return nik.includes(search) || nama.includes(search);
    });

    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Tidak ada data karyawan</td></tr>';
    } else {
        tbody.innerHTML = filtered.map(k => `
            <tr>
                <td><input type="checkbox" class="check-available" value="${k.Nik}"></td>
                <td>${k.Nik}</td>
                <td>${k.Nama}</td>
            </tr>
        `).join('');
    }

    document.getElementById('totalAvailable').textContent = filtered.length;
}

// Render selected karyawans
function renderSelectedKaryawans() {
    const tbody = document.getElementById('tbodySelected');
    const search = document.getElementById('searchSelected').value.toLowerCase();
    
    let filtered = selectedKaryawans.filter(k => {
        const nik = k.Nik.toLowerCase();
        const nama = k.Nama.toLowerCase();
        return nik.includes(search) || nama.includes(search);
    });

    tbody.innerHTML = filtered.map(k => `
        <tr>
            <td><input type="checkbox" class="check-selected" value="${k.Nik}"></td>
            <td>${k.Nik}</td>
            <td>${k.Nama}</td>
        </tr>
    `).join('');

    document.getElementById('totalSelected').textContent = selectedKaryawans.length;
    updateKaryawanIds();
}

// Update hidden input
function updateKaryawanIds() {
    document.getElementById('karyawan_ids').value = JSON.stringify(selectedKaryawans.map(k => k.Nik));
}

// Add selected karyawans
document.getElementById('btnAddSelected')?.addEventListener('click', function() {
    const checked = document.querySelectorAll('.check-available:checked');
    checked.forEach(cb => {
        const nik = cb.value;
        const karyawan = availableKaryawans.find(k => k.Nik === nik);
        if (karyawan && !selectedKaryawans.find(k => k.Nik === nik)) {
            selectedKaryawans.push(karyawan);
        }
    });
    renderAvailableKaryawans();
    renderSelectedKaryawans();
});

// Remove selected karyawans
document.getElementById('btnRemoveSelected')?.addEventListener('click', function() {
    const checked = document.querySelectorAll('.check-selected:checked');
    const niks = Array.from(checked).map(cb => cb.value);
    selectedKaryawans = selectedKaryawans.filter(k => !niks.includes(k.Nik));
    renderSelectedKaryawans();
});

// Search available
document.getElementById('searchAvailable')?.addEventListener('input', renderAvailableKaryawans);

// Search selected
document.getElementById('searchSelected')?.addEventListener('input', renderSelectedKaryawans);

// Select all available
document.getElementById('selectAllAvailable')?.addEventListener('change', function() {
    document.querySelectorAll('.check-available').forEach(cb => cb.checked = this.checked);
});

// Select all selected
document.getElementById('selectAllSelected')?.addEventListener('change', function() {
    document.querySelectorAll('.check-selected').forEach(cb => cb.checked = this.checked);
});

// Preview
document.getElementById('btnPreview')?.addEventListener('click', function() {
    const formData = new FormData(document.getElementById('tukarHariKerjaForm'));
    const data = Object.fromEntries(formData);
    data.karyawan_ids = selectedKaryawans.map(k => k.Nik);

    fetch('{{ route("tukar-hari-kerja.preview") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('previewContent').innerHTML = `
                <p><strong>Total Karyawan:</strong> ${data.total_karyawan}</p>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>Divisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.karyawans.map(k => `
                                <tr>
                                    <td>${k.nik}</td>
                                    <td>${k.nama}</td>
                                    <td>${k.divisi}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
            new bootstrap.Modal(document.getElementById('previewModal')).show();
        }
    });
});

// Load departemen by divisi
document.getElementById('vcKodeDivisi')?.addEventListener('change', function() {
    const divisi = this.value;
    const deptSelect = document.getElementById('vcKodeDept');
    const bagianSelect = document.getElementById('vcKodeBagian');

    if (divisi) {
        fetch('{{ route("karyawan.get-departemens") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ divisi: divisi })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                deptSelect.innerHTML = '<option value="">Semua Departemen</option>' +
                    data.departemens.map(d => `<option value="${d.vcKodeDept}">${d.vcKodeDept} - ${d.vcNamaDept}</option>`).join('');
                deptSelect.disabled = false;
            }
        });
    } else {
        deptSelect.innerHTML = '<option value="">Pilih Divisi terlebih dahulu</option>';
        deptSelect.disabled = true;
    }

    bagianSelect.innerHTML = '<option value="">Pilih Departemen terlebih dahulu</option>';
    bagianSelect.disabled = true;
});

// Load bagian by divisi and departemen
document.getElementById('vcKodeDept')?.addEventListener('change', function() {
    const divisi = document.getElementById('vcKodeDivisi').value;
    const dept = this.value;
    const bagianSelect = document.getElementById('vcKodeBagian');

    if (divisi && dept) {
        fetch('{{ route("karyawan.get-bagians") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ divisi: divisi, departemen: dept })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bagianSelect.innerHTML = '<option value="">Semua Bagian</option>' +
                    data.bagians.map(b => `<option value="${b.vcKodeBagian}">${b.vcKodeBagian} - ${b.vcNamaBagian}</option>`).join('');
                bagianSelect.disabled = false;
            }
        });
    } else {
        bagianSelect.innerHTML = '<option value="">Pilih Departemen terlebih dahulu</option>';
        bagianSelect.disabled = true;
    }
});

// Form validation
document.getElementById('tukarHariKerjaForm')?.addEventListener('submit', function(e) {
    const scope = document.querySelector('input[name="vcScope"]:checked').value;
    
    if (scope === 'PERORANGAN' && selectedKaryawans.length === 0) {
        e.preventDefault();
        alert('Pilih minimal satu karyawan');
        return false;
    }

    if ((scope === 'GROUP' || scope === 'SEMUA_BU') && !document.getElementById('vcKodeDivisi').value) {
        e.preventDefault();
        alert('Pilih Divisi terlebih dahulu');
        return false;
    }

    // Set karyawan_ids
    if (scope === 'PERORANGAN') {
        document.getElementById('karyawan_ids').value = JSON.stringify(selectedKaryawans.map(k => k.Nik));
    } else {
        document.getElementById('karyawan_ids').value = JSON.stringify([]);
    }
});
</script>
@endpush


