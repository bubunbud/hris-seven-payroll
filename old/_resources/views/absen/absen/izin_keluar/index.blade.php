@extends('layouts.app')

@section('title', 'Izin Keluar Komplek Kantor - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-door-open me-2"></i>Izin Keluar Komplek Kantor
                </h2>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-info" id="printMultipleBtn" style="display: none;">
                        <i class="fas fa-print me-1"></i>Print Selected
                    </button>
                    <button type="button" class="btn btn-success" id="addBtn">
                        <i class="fas fa-plus me-1"></i>Tambah
                    </button>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('izin-keluar.index') }}" id="filterForm">
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

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="max-height:600px; overflow-y:auto;">
                        <table class="table table-hover">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="3%">
                                        <input type="checkbox" id="selectAll" title="Pilih Semua">
                                    </th>
                                    <th width="10%">Tanggal</th>
                                    <th width="9%">NIK</th>
                                    <th width="16%">Nama</th>
                                    <th width="11%">Jenis Izin</th>
                                    <th width="11%">Tipe/Kategori</th>
                                    <th width="7%">Dari</th>
                                    <th width="7%">Sampai</th>
                                    <th width="9%">Counter</th>
                                    <th width="9%">Keterangan</th>
                                    <th width="5%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($records as $row)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="izin-checkbox" value="{{ $row->vcCounter }}" data-counter="{{ $row->vcCounter }}">
                                    </td>
                                    <td>{{ $row->dtTanggal?->format('d/m/Y') }}</td>
                                    <td><strong>{{ $row->vcNik }}</strong></td>
                                    <td>{{ $row->karyawan->Nama ?? 'N/A' }}</td>
                                    <td>{{ $row->jenisIzin->vcKeterangan ?? $row->vcKodeIzin }}</td>
                                    <td>
                                        @if($row->vcTipeIzin)
                                            <span class="badge bg-info">{{ $row->vcTipeIzin }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $row->dtDari ? substr($row->dtDari,0,5) : '-' }}</td>
                                    <td>{{ $row->dtSampai ? substr($row->dtSampai,0,5) : '-' }}</td>
                                    <td><span class="badge text-bg-secondary">{{ $row->vcCounter }}</span></td>
                                    <td>{{ $row->vcKeterangan }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('izin-keluar.print', $row->vcCounter) }}" class="btn btn-outline-success" target="_blank" title="Print Surat Izin">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <button class="btn btn-outline-primary" onclick="editRecord('{{ $row->vcCounter }}')" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteRecord('{{ $row->vcCounter }}')" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center py-4">
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
<div class="modal fade" id="izinModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="izinModalLabel">Tambah Izin Keluar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="izinForm">
                <input type="hidden" name="_method" id="_method" value="POST">
                <div class="modal-body">
                    <div class="mb-3 d-none" id="vcCounterGroup">
                        <label for="vcCounter" class="form-label">Kode Counter</label>
                        <input type="text" class="form-control" id="vcCounter" name="vcCounter" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="dtTanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="dtTanggal" name="dtTanggal" required>
                    </div>
                    <div class="mb-3">
                        <label for="vcNik" class="form-label">NIK <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="vcNik" name="vcNik" maxlength="10" required>
                        <div class="form-text" id="namaPreview"></div>
                    </div>
                    <div class="mb-3">
                        <label for="vcKodeIzin" class="form-label">Jenis Izin <span class="text-danger">*</span></label>
                        <select class="form-select" id="vcKodeIzin" name="vcKodeIzin" required onchange="if(typeof toggleTipeIzinField === 'function') toggleTipeIzinField();">
                            <option value="">Pilih Jenis Izin</option>
                            @foreach($jenisIzins as $j)
                            <option value="{{ $j->vcKodeIzin }}" data-keterangan="{{ strtolower($j->vcKeterangan) }}">{{ $j->vcKeterangan }} ({{ $j->vcKodeIzin }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="vcTipeIzinGroup">
                        <label for="vcTipeIzin" class="form-label">Tipe/Kategori Izin</label>
                        <select class="form-select" id="vcTipeIzin" name="vcTipeIzin">
                            <option value="">Pilih Tipe/Kategori</option>
                            <option value="Masuk Siang">Masuk Siang</option>
                            <option value="Izin Biasa">Izin Biasa</option>
                            <option value="Pulang Cepat">Pulang Cepat</option>
                        </select>
                        <div class="form-text">Pilih tipe/kategori izin untuk jenis izin pribadi</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dtDari" class="form-label">Dari (HH:MM) <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="dtDari" name="dtDari" required>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="tidakKembali" name="tidakKembali">
                                    <label class="form-check-label" for="tidakKembali">
                                        Tidak Kembali
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dtSampai" class="form-label">Sampai (HH:MM) <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="dtSampai" name="dtSampai" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="vcKeterangan" class="form-label">Keterangan</label>
                        <input type="text" class="form-control" id="vcKeterangan" name="vcKeterangan" maxlength="35">
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
    let currentId = null; // vcCounter sebagai PK

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const html = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
        document.querySelectorAll('.alert').forEach(a => a.remove());
        document.querySelector('.container-fluid').insertAdjacentHTML('afterbegin', html);
        setTimeout(() => {
            const a = document.querySelector('.alert');
            if (a) a.remove();
        }, 4000);
    }

    document.getElementById('addBtn').addEventListener('click', () => {
        isEditMode = false;
        currentId = null;
        document.getElementById('izinModalLabel').textContent = 'Tambah Izin Keluar';
        document.getElementById('izinForm').reset();
        document.getElementById('_method').value = 'POST';
        document.getElementById('vcNik').readOnly = false;
        document.getElementById('vcCounterGroup').classList.add('d-none');
        document.getElementById('vcCounter').value = '';
        // Reset checkbox dan jam shift
        document.getElementById('tidakKembali').checked = false;
        jamPulangShift = null;
        jamMasukShift = null;
        // Set default tanggal = hari ini (YYYY-MM-DD)
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        document.getElementById('dtTanggal').value = `${yyyy}-${mm}-${dd}`;
        // Reset field tipe (akan muncul otomatis jika jenis izin pribadi dipilih)
        document.getElementById('vcTipeIzinGroup').classList.add('d-none');
        document.getElementById('vcTipeIzin').removeAttribute('required');
        document.getElementById('vcTipeIzin').value = '';
        
        const modal = new bootstrap.Modal(document.getElementById('izinModal'));
        modal.show();
        
        // Pastikan event listener siap setelah modal dibuka
        // Event delegation pada document sudah menangani ini, tapi kita pasang lagi untuk memastikan
        setTimeout(() => {
            const vcKodeIzinSelectAfterModal = document.getElementById('vcKodeIzin');
            if (vcKodeIzinSelectAfterModal) {
                // Pasang event listener langsung pada element
                vcKodeIzinSelectAfterModal.addEventListener('change', function() {
                    toggleTipeIzinField();
                });
            }
            // Pastikan field tipe muncul jika jenis izin sudah dipilih (tidak perlu, karena form di-reset)
            toggleTipeIzinField();
        }, 300);
    });

    document.getElementById('izinForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const url = isEditMode ? `/izin-keluar/${currentId}` : '/izin-keluar';
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
                    bootstrap.Modal.getInstance(document.getElementById('izinModal')).hide();
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

    function editRecord(id) {
        fetch(`/izin-keluar/${id}`, {
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
                    document.getElementById('izinModalLabel').textContent = 'Edit Izin Keluar';
                    document.getElementById('_method').value = 'PUT';
                    document.getElementById('vcCounterGroup').classList.remove('d-none');
                    document.getElementById('vcCounter').value = data.record.vcCounter || id;
                    document.getElementById('dtTanggal').value = data.record.dtTanggal;
                    document.getElementById('vcNik').value = data.record.vcNik;
                    document.getElementById('vcKodeIzin').value = data.record.vcKodeIzin;
                    // Trigger change event untuk show/hide field tipe
                    document.getElementById('vcKodeIzin').dispatchEvent(new Event('change'));
                    // Set value tipe setelah field muncul
                    setTimeout(() => {
                        document.getElementById('vcTipeIzin').value = data.record.vcTipeIzin || '';
                    }, 100);
                    document.getElementById('dtDari').value = data.record.dtDari?.substring(0, 5) || '';
                    document.getElementById('dtSampai').value = data.record.dtSampai?.substring(0, 5) || '';
                    document.getElementById('vcKeterangan').value = data.record.vcKeterangan || '';
                    document.getElementById('vcNik').readOnly = true;
                    
                    // Ambil data shift untuk karyawan yang sedang di-edit
                    const nikEdit = data.record.vcNik;
                    if (nikEdit) {
                        fetch(`/karyawan/${nikEdit}`, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json'
                                }
                            })
                            .then(r => r.json()).then(dataKaryawan => {
                                if (dataKaryawan.success && dataKaryawan.karyawan?.shift) {
                                    // Ambil jam pulang shift
                                    if (dataKaryawan.karyawan.shift.vcPulang) {
                                        const vcPulang = dataKaryawan.karyawan.shift.vcPulang;
                                        if (typeof vcPulang === 'string') {
                                            jamPulangShift = vcPulang.substring(0, 5);
                                        } else if (vcPulang && typeof vcPulang === 'object') {
                                            try {
                                                const date = new Date(vcPulang);
                                                if (!isNaN(date.getTime())) {
                                                    const hours = String(date.getHours()).padStart(2, '0');
                                                    const minutes = String(date.getMinutes()).padStart(2, '0');
                                                    jamPulangShift = `${hours}:${minutes}`;
                                                } else {
                                                    const strPulang = String(vcPulang);
                                                    jamPulangShift = strPulang.substring(0, 5);
                                                }
                                            } catch (e) {
                                                const strPulang = String(vcPulang);
                                                jamPulangShift = strPulang.substring(0, 5);
                                            }
                                        } else {
                                            jamPulangShift = null;
                                        }
                                    } else {
                                        jamPulangShift = null;
                                    }
                                    
                                    // Ambil jam masuk shift
                                    if (dataKaryawan.karyawan.shift.vcMasuk) {
                                        const vcMasuk = dataKaryawan.karyawan.shift.vcMasuk;
                                        if (typeof vcMasuk === 'string') {
                                            jamMasukShift = vcMasuk.substring(0, 5);
                                        } else if (vcMasuk && typeof vcMasuk === 'object') {
                                            try {
                                                const date = new Date(vcMasuk);
                                                if (!isNaN(date.getTime())) {
                                                    const hours = String(date.getHours()).padStart(2, '0');
                                                    const minutes = String(date.getMinutes()).padStart(2, '0');
                                                    jamMasukShift = `${hours}:${minutes}`;
                                                } else {
                                                    const strMasuk = String(vcMasuk);
                                                    jamMasukShift = strMasuk.substring(0, 5);
                                                }
                                            } catch (e) {
                                                const strMasuk = String(vcMasuk);
                                                jamMasukShift = strMasuk.substring(0, 5);
                                            }
                                        } else {
                                            jamMasukShift = null;
                                        }
                                    } else {
                                        jamMasukShift = null;
                                    }
                                    
                                    // Cek apakah jam "Sampai" sama dengan jam pulang shift
                                    setTimeout(() => {
                                        checkTidakKembali();
                                    }, 100);
                                    
                                    // Jika tipe izin = "Masuk Siang", auto-fill jam "Dari"
                                    const tipeIzin = document.getElementById('vcTipeIzin').value;
                                    if (tipeIzin === 'Masuk Siang' && jamMasukShift) {
                                        document.getElementById('dtDari').value = jamMasukShift;
                                    }
                                } else {
                                    jamPulangShift = null;
                                    jamMasukShift = null;
                                    document.getElementById('tidakKembali').checked = false;
                                }
                            }).catch(() => {
                                jamPulangShift = null;
                                jamMasukShift = null;
                                document.getElementById('tidakKembali').checked = false;
                            });
                    } else {
                        jamPulangShift = null;
                        jamMasukShift = null;
                        document.getElementById('tidakKembali').checked = false;
                    }
                    
                    new bootstrap.Modal(document.getElementById('izinModal')).show();
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('error', 'Gagal memuat data');
            });
    }

    function deleteRecord(id) {
        if (!confirm('Hapus data ini?')) return;
        fetch(`/izin-keluar/${id}`, {
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

    // Variabel global untuk menyimpan jam pulang shift dan jam masuk shift
    let jamPulangShift = null;
    let jamMasukShift = null;

    // Autofill nama saat NIK di-blur dan ambil data shift
    document.getElementById('vcNik').addEventListener('blur', function() {
        const nik = this.value.trim();
        if (!nik) {
            jamPulangShift = null;
            return;
        }
        fetch(`/karyawan/${nik}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json()).then(data => {
                if (data.success && data.karyawan) {
                    document.getElementById('namaPreview').textContent = data.karyawan.Nama ? 'Nama: ' + data.karyawan.Nama : '';
                    
                    // Ambil jam pulang shift dan jam masuk shift
                    if (data.karyawan.shift) {
                        // Ambil jam pulang shift
                        if (data.karyawan.shift.vcPulang) {
                            const vcPulang = data.karyawan.shift.vcPulang;
                            // Format jam pulang shift ke HH:MM
                            if (typeof vcPulang === 'string') {
                                jamPulangShift = vcPulang.substring(0, 5);
                            } else if (vcPulang && typeof vcPulang === 'object') {
                                try {
                                    const date = new Date(vcPulang);
                                    if (!isNaN(date.getTime())) {
                                        const hours = String(date.getHours()).padStart(2, '0');
                                        const minutes = String(date.getMinutes()).padStart(2, '0');
                                        jamPulangShift = `${hours}:${minutes}`;
                                    } else {
                                        const strPulang = String(vcPulang);
                                        jamPulangShift = strPulang.substring(0, 5);
                                    }
                                } catch (e) {
                                    const strPulang = String(vcPulang);
                                    jamPulangShift = strPulang.substring(0, 5);
                                }
                            } else {
                                jamPulangShift = null;
                            }
                        } else {
                            jamPulangShift = null;
                        }
                        
                        // Ambil jam masuk shift
                        if (data.karyawan.shift.vcMasuk) {
                            const vcMasuk = data.karyawan.shift.vcMasuk;
                            // Format jam masuk shift ke HH:MM
                            if (typeof vcMasuk === 'string') {
                                jamMasukShift = vcMasuk.substring(0, 5);
                            } else if (vcMasuk && typeof vcMasuk === 'object') {
                                try {
                                    const date = new Date(vcMasuk);
                                    if (!isNaN(date.getTime())) {
                                        const hours = String(date.getHours()).padStart(2, '0');
                                        const minutes = String(date.getMinutes()).padStart(2, '0');
                                        jamMasukShift = `${hours}:${minutes}`;
                                    } else {
                                        const strMasuk = String(vcMasuk);
                                        jamMasukShift = strMasuk.substring(0, 5);
                                    }
                                } catch (e) {
                                    const strMasuk = String(vcMasuk);
                                    jamMasukShift = strMasuk.substring(0, 5);
                                }
                            } else {
                                jamMasukShift = null;
                            }
                        } else {
                            jamMasukShift = null;
                        }
                    } else {
                        jamPulangShift = null;
                        jamMasukShift = null;
                    }
                    
                    // Cek apakah jam "Sampai" sudah sama dengan jam pulang shift
                    checkTidakKembali();
                } else {
                    document.getElementById('namaPreview').textContent = '';
                    jamPulangShift = null;
                    jamMasukShift = null;
                }
            }).catch(() => {
                document.getElementById('namaPreview').textContent = '';
                jamPulangShift = null;
                jamMasukShift = null;
            });
    });

    // Function untuk check/uncheck checkbox "Tidak Kembali" berdasarkan jam "Sampai"
    function checkTidakKembali() {
        const dtSampai = document.getElementById('dtSampai').value;
        const tidakKembaliCheckbox = document.getElementById('tidakKembali');
        
        if (jamPulangShift && dtSampai === jamPulangShift) {
            tidakKembaliCheckbox.checked = true;
        } else {
            tidakKembaliCheckbox.checked = false;
        }
    }

    // Event listener untuk checkbox "Tidak Kembali"
    document.getElementById('tidakKembali').addEventListener('change', function() {
        if (this.checked && jamPulangShift) {
            document.getElementById('dtSampai').value = jamPulangShift;
        }
    });

    // Event listener untuk field "dtSampai" - auto-check checkbox jika sama dengan jam pulang shift
    document.getElementById('dtSampai').addEventListener('change', function() {
        checkTidakKembali();
    });

    // Event listener untuk field "vcTipeIzin" - auto-fill jam "Dari" jika Tipe = "Masuk Siang"
    document.getElementById('vcTipeIzin').addEventListener('change', function() {
        const tipeIzin = this.value;
        const vcKodeIzin = document.getElementById('vcKodeIzin').value;
        
        // Cek apakah jenis izin adalah izin pribadi (Z003 atau Z004)
        const isPribadi = vcKodeIzin === 'Z003' || vcKodeIzin === 'Z004';
        
        // Jika tipe izin = "Masuk Siang" dan jenis izin pribadi, auto-fill jam "Dari"
        // Field tetap editable (tidak readonly/disabled)
        if (isPribadi && tipeIzin === 'Masuk Siang' && jamMasukShift) {
            document.getElementById('dtDari').value = jamMasukShift;
        }
    });
    
    // Event listener untuk field "vcKodeIzin" - auto-fill jam "Dari" jika sudah pilih Tipe = "Masuk Siang"
    document.getElementById('vcKodeIzin').addEventListener('change', function() {
        const vcKodeIzin = this.value;
        const tipeIzin = document.getElementById('vcTipeIzin').value;
        
        // Cek apakah jenis izin adalah izin pribadi (Z003 atau Z004)
        const isPribadi = vcKodeIzin === 'Z003' || vcKodeIzin === 'Z004';
        
        // Jika tipe izin = "Masuk Siang" dan jenis izin pribadi, auto-fill jam "Dari"
        // Field tetap editable (tidak readonly/disabled)
        if (isPribadi && tipeIzin === 'Masuk Siang' && jamMasukShift) {
            document.getElementById('dtDari').value = jamMasukShift;
        }
    });

    // Function untuk show/hide field Tipe/Kategori berdasarkan jenis izin
    // Didefinisikan di window scope agar bisa dipanggil dari inline handler
    window.toggleTipeIzinField = function() {
        const vcKodeIzinSelect = document.getElementById('vcKodeIzin');
        if (!vcKodeIzinSelect) return;
        
        const selectedOption = vcKodeIzinSelect.options[vcKodeIzinSelect.selectedIndex];
        const kodeIzin = vcKodeIzinSelect.value;
        const keterangan = selectedOption ? (selectedOption.getAttribute('data-keterangan') || '') : '';
        const tipeIzinGroup = document.getElementById('vcTipeIzinGroup');
        const tipeIzinSelect = document.getElementById('vcTipeIzin');
        
        if (!tipeIzinGroup || !tipeIzinSelect) return;
        
        // Tampilkan field tipe jika jenis izin = Z003 atau Z004, atau jika keterangan mengandung "pribadi"
        const isPribadi = kodeIzin === 'Z003' || kodeIzin === 'Z004' || keterangan.includes('pribadi');
        
        if (isPribadi) {
            tipeIzinGroup.classList.remove('d-none');
            tipeIzinSelect.setAttribute('required', 'required');
            
            // Jika tipe izin sudah dipilih "Masuk Siang" dan jam masuk shift tersedia, auto-fill jam "Dari"
            if (tipeIzinSelect.value === 'Masuk Siang' && jamMasukShift) {
                document.getElementById('dtDari').value = jamMasukShift;
            }
        } else {
            tipeIzinGroup.classList.add('d-none');
            tipeIzinSelect.removeAttribute('required');
            tipeIzinSelect.value = ''; // Reset value jika bukan pribadi
        }
    };

    // Show/hide field Tipe/Kategori berdasarkan jenis izin
    // Pasang event listener dengan event delegation pada document (selalu bekerja)
    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'vcKodeIzin') {
            toggleTipeIzinField();
        }
    });
    
    // Juga pasang langsung pada element jika sudah ada saat halaman dimuat
    const vcKodeIzinSelect = document.getElementById('vcKodeIzin');
    if (vcKodeIzinSelect) {
        vcKodeIzinSelect.addEventListener('change', toggleTipeIzinField);
    }
    
    // Pastikan function dipanggil saat modal dibuka (untuk edit mode)
    const izinModal = document.getElementById('izinModal');
    if (izinModal) {
        izinModal.addEventListener('shown.bs.modal', function() {
            // Pasang event listener lagi setelah modal dibuka (untuk memastikan)
            const vcKodeIzinSelectModal = document.getElementById('vcKodeIzin');
            if (vcKodeIzinSelectModal) {
                // Hapus listener lama jika ada, lalu pasang yang baru
                const newToggleFunction = function() {
                    toggleTipeIzinField();
                };
                vcKodeIzinSelectModal.removeEventListener('change', toggleTipeIzinField);
                vcKodeIzinSelectModal.addEventListener('change', newToggleFunction);
                // Cek apakah ada jenis izin yang sudah dipilih saat modal dibuka
                toggleTipeIzinField();
            }
        });
    }

    // Auto submit filter
    document.getElementById('dari_tanggal').addEventListener('change', () => document.getElementById('filterForm').submit());
    document.getElementById('sampai_tanggal').addEventListener('change', () => document.getElementById('filterForm').submit());

    // Multiple Print Functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const izinCheckboxes = document.querySelectorAll('.izin-checkbox');
    const printMultipleBtn = document.getElementById('printMultipleBtn');

    // Select All checkbox
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            izinCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            togglePrintMultipleBtn();
        });
    }

    // Individual checkbox change
    izinCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Update select all checkbox state
            if (selectAllCheckbox) {
                const allChecked = Array.from(izinCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(izinCheckboxes).some(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
            }
            togglePrintMultipleBtn();
        });
    });

    // Toggle Print Multiple button visibility
    function togglePrintMultipleBtn() {
        const checked = document.querySelectorAll('.izin-checkbox:checked');
        if (printMultipleBtn) {
            printMultipleBtn.style.display = checked.length > 0 ? 'inline-block' : 'none';
        }
    }

    // Print Multiple button click
    if (printMultipleBtn) {
        printMultipleBtn.addEventListener('click', function() {
            const checked = Array.from(document.querySelectorAll('.izin-checkbox:checked'));
            
            if (checked.length === 0) {
                alert('Pilih minimal 1 surat izin yang akan di-print!');
                return;
            }

            if (checked.length > 10) {
                if (!confirm(`Anda akan print ${checked.length} surat izin. Lanjutkan?`)) {
                    return;
                }
            }

            // Collect checked counter values
            const counters = checked.map(cb => cb.value);
            
            // Build URL with query parameter
            const idsParam = counters.join(',');
            const url = `{{ route('izin-keluar.print-multiple') }}?ids=${idsParam}`;
            
            // Open in new window
            window.open(url, '_blank');
        });
    }
</script>
@endpush

