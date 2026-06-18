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
                                    <td><span class="badge text-bg-secondary" style="color: #000 !important;">{{ $row->vcCounter }}</span></td>
                                    <td>{{ $row->vcKeterangan }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('izin-keluar.print', $row->vcCounter) }}" class="btn btn-outline-success" target="_blank" title="Print Surat Izin">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <button class="btn btn-outline-primary" onclick="editRecord('{{ $row->vcCounter }}')" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-hapus-izin-keluar"
                                                title="Hapus"
                                                data-vc-counter="{{ $row->vcCounter }}"
                                                data-kode-izin="{{ $row->vcKodeIzin }}"
                                                data-tipe-izin="{{ $row->vcTipeIzin ?? '' }}"
                                                data-nik="{{ $row->vcNik }}"
                                                data-tanggal="{{ $row->dtTanggal?->format('Y-m-d') ?? '' }}">
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
                        <label for="vcNik" class="form-label">NIK / Nama <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="text" class="form-control" id="vcNik" maxlength="50" required autocomplete="off" placeholder="Ketik NIK atau Nama">
                            <input type="hidden" id="vcNikHidden" name="vcNik">
                            <div id="nikAutocomplete" class="autocomplete-dropdown" style="display: none;"></div>
                        </div>
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
                                <label for="dtDari" class="form-label">Dari (HH:MM) <span class="text-danger" id="dtDariRequired">*</span></label>
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
                                <label for="dtSampai" class="form-label">Sampai (HH:MM) <span class="text-danger" id="dtSampaiRequired">*</span></label>
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

{{-- Konfirmasi hapus: opsi hapus seluruh record t_absen untuk Masuk Siang --}}
<div class="modal fade" id="deleteIzinModal" tabindex="-1" aria-labelledby="deleteIzinModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteIzinModalLabel"><i class="fas fa-trash-alt me-2 text-danger"></i>Konfirmasi hapus izin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Anda akan menghapus surat izin keluar komplek ini.</p>
                <p class="mb-2"><strong>Tipe Masuk Siang</strong> — untuk karyawan berikut, aplikasi bisa sekaligus menghapus <strong>satu record absensi lengkap</strong> di tabel <code>t_absen</code> (tanggal dan NIK sama dengan izin).</p>
                <ul class="mb-3 small text-muted">
                    <li><strong>NIK:</strong> <span id="deleteIzinNik"></span></li>
                    <li><strong>Tanggal absensi:</strong> <span id="deleteIzinTanggal"></span></li>
                </ul>
                <div class="alert alert-warning small mb-0">
                    <strong>Hapus record absensi</strong> berarti <strong>seluruh baris</strong> untuk kombinasi tanggal + NIK tersebut dihapus (jam masuk, jam keluar, keterangan, lembur di baris itu, jika ada). Pilih sesuai kebutuhan.
                </div>
            </div>
            <div class="modal-footer flex-wrap gap-2 justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-primary" id="deleteIzinTanpaAbsenBtn">
                        Hanya hapus izin
                    </button>
                    <button type="button" class="btn btn-danger" id="deleteIzinDanAbsenBtn">
                        Hapus izin + hapus record absensi
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="deletePendingCounter" value="">
@endsection

@push('scripts')
<script>
    let isEditMode = false;
    let currentId = null; // vcCounter sebagai PK
    let izinFormSubmitting = false;

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
        // Reset field NIK dan hidden field
        document.getElementById('vcNik').value = '';
        document.getElementById('vcNikHidden').value = '';
        document.getElementById('namaPreview').textContent = '';
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
        // Reset field "Dari" - enable dan set required
        const dtDariField = document.getElementById('dtDari');
        const dtDariRequired = document.getElementById('dtDariRequired');
        dtDariField.disabled = false;
        dtDariField.setAttribute('required', 'required');
        dtDariField.value = '';
        if (dtDariRequired) {
            dtDariRequired.style.display = 'inline';
        }

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
        if (izinFormSubmitting) return;

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

        const url = isEditMode ? `/izin-keluar/${currentId}` : '/izin-keluar';
        document.getElementById('_method').value = isEditMode ? 'PUT' : 'POST';
        const formData = new FormData(this);

        // Jika field "Dari" disabled (Pulang Cepat), hapus dari formData agar tidak dikirim
        const dtDariField = document.getElementById('dtDari');
        if (dtDariField && dtDariField.disabled) {
            formData.delete('dtDari');
        }

        const submitBtn = this.querySelector('button[type="submit"]');
        izinFormSubmitting = true;
        if (submitBtn) submitBtn.disabled = true;

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
            })
            .finally(() => {
                izinFormSubmitting = false;
                if (submitBtn) submitBtn.disabled = false;
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
                                } else {
                                    document.getElementById('vcNik').value = nikValue;
                                }
                            })
                            .catch(() => {
                                document.getElementById('vcNik').value = nikValue;
                            });
                    } else {
                        document.getElementById('vcNik').value = nikValue;
                    }
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

                                    // Handle field "Dari" berdasarkan tipe izin
                                    const tipeIzin = document.getElementById('vcTipeIzin').value;
                                    const dtDariField = document.getElementById('dtDari');
                                    const dtDariRequired = document.getElementById('dtDariRequired');

                                    // Jika tipe izin = "Pulang Cepat", disable field "Dari"
                                    if (tipeIzin === 'Pulang Cepat') {
                                        dtDariField.disabled = true;
                                        dtDariField.removeAttribute('required');
                                        dtDariField.value = '';
                                        if (dtDariRequired) {
                                            dtDariRequired.style.display = 'none';
                                        }
                                    } else {
                                        // Enable field "Dari" dan set required
                                        dtDariField.disabled = false;
                                        dtDariField.setAttribute('required', 'required');
                                        if (dtDariRequired) {
                                            dtDariRequired.style.display = 'inline';
                                        }

                                        // Jika tipe izin = "Masuk Siang", auto-fill jam "Dari"
                                        if (tipeIzin === 'Masuk Siang' && jamMasukShift) {
                                            dtDariField.value = jamMasukShift;
                                        }
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

    function formatTanggalId(ymd) {
        if (!ymd) return '-';
        const p = String(ymd).split('-');
        if (p.length !== 3) return ymd;
        return `${p[2]}/${p[1]}/${p[0]}`;
    }

    function submitDeleteIzin(id, hapusAbsensi) {
        // FormData: Laravel selalu mengisi $request->input() untuk _method + hapus_absensi (lebih andal daripada JSON)
        const formData = new FormData();
        formData.append('_method', 'DELETE');
        formData.append('hapus_absensi', hapusAbsensi ? '1' : '0');

        fetch(`/izin-keluar/${encodeURIComponent(id)}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
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

    function deleteRecord(id, kodeIzin, tipeIzin, nik, tanggalYmd) {
        const isPribadi = kodeIzin === 'Z003' || kodeIzin === 'Z004';
        const tipeNorm = String(tipeIzin || '').trim();
        const isMasukSiang = isPribadi && tipeNorm === 'Masuk Siang';

        if (isMasukSiang) {
            const delModal = document.getElementById('deleteIzinModal');
            const pending = document.getElementById('deletePendingCounter');
            if (!delModal || !pending) {
                if (!confirm('Hapus data ini? (modal tidak tersedia, hanya izin yang dihapus)\nPilih OK untuk lanjut.')) return;
                submitDeleteIzin(id, false);
                return;
            }
            pending.value = id;
            document.getElementById('deleteIzinNik').textContent = nik || '-';
            document.getElementById('deleteIzinTanggal').textContent = formatTanggalId(tanggalYmd);
            let modal = bootstrap.Modal.getInstance(delModal);
            if (!modal) modal = new bootstrap.Modal(delModal);
            modal.show();
            return;
        }

        if (!confirm('Hapus data ini?')) return;
        submitDeleteIzin(id, false);
    }

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-hapus-izin-keluar');
        if (!btn) return;
        e.preventDefault();
        deleteRecord(
            btn.getAttribute('data-vc-counter') || '',
            btn.getAttribute('data-kode-izin') || '',
            btn.getAttribute('data-tipe-izin') || '',
            btn.getAttribute('data-nik') || '',
            btn.getAttribute('data-tanggal') || ''
        );
    });

    (function wireDeleteIzinModal() {
        const delModalEl = document.getElementById('deleteIzinModal');
        if (!delModalEl) return;

        const tanpaAbsen = document.getElementById('deleteIzinTanpaAbsenBtn');
        const denganAbsen = document.getElementById('deleteIzinDanAbsenBtn');
        if (!tanpaAbsen || !denganAbsen) return;

        function tutupModal() {
            const inst = bootstrap.Modal.getInstance(delModalEl);
            if (inst) inst.hide();
        }

        tanpaAbsen.addEventListener('click', function() {
            const id = document.getElementById('deletePendingCounter').value;
            tutupModal();
            if (id) submitDeleteIzin(id, false);
        });

        denganAbsen.addEventListener('click', function() {
            const id = document.getElementById('deletePendingCounter').value;
            tutupModal();
            if (id) submitDeleteIzin(id, true);
        });
    })();

    // Variabel global untuk menyimpan jam pulang shift dan jam masuk shift
    let jamPulangShift = null;
    let jamMasukShift = null;

    // Function untuk load data karyawan dan shift berdasarkan NIK
    function loadKaryawanData(nik) {
        if (!nik || !nik.trim()) {
            document.getElementById('namaPreview').textContent = '';
            jamPulangShift = null;
            jamMasukShift = null;
            return;
        }
        fetch(`/karyawan/${nik.trim()}`, {
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
                item.scrollIntoView({
                    block: 'nearest',
                    behavior: 'smooth'
                });
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

    // Event listener untuk field "vcTipeIzin" - auto-fill jam "Dari" jika Tipe = "Masuk Siang" atau disable jika "Pulang Cepat"
    document.getElementById('vcTipeIzin').addEventListener('change', function() {
        const tipeIzin = this.value;
        const vcKodeIzin = document.getElementById('vcKodeIzin').value;
        const dtDariField = document.getElementById('dtDari');
        const dtDariRequired = document.getElementById('dtDariRequired');
        const dtSampaiField = document.getElementById('dtSampai');
        const dtSampaiRequired = document.getElementById('dtSampaiRequired');

        // Cek apakah jenis izin adalah izin pribadi (Z003 atau Z004)
        const isPribadi = vcKodeIzin === 'Z003' || vcKodeIzin === 'Z004';

        // Jika tipe izin = "Pulang Cepat" dan jenis izin pribadi, disable field "Dari" dan hapus required
        if (isPribadi && tipeIzin === 'Pulang Cepat') {
            dtDariField.disabled = true;
            dtDariField.removeAttribute('required');
            dtDariField.value = ''; // Clear value
            if (dtDariRequired) {
                dtDariRequired.style.display = 'none'; // Sembunyikan tanda required
            }
            // Untuk Pulang Cepat, jam "Sampai" tetap wajib diisi
            if (dtSampaiField) {
                dtSampaiField.setAttribute('required', 'required');
            }
            if (dtSampaiRequired) {
                dtSampaiRequired.style.display = 'inline';
            }
        } else {
            // Enable field "Dari" dan set required
            dtDariField.disabled = false;
            dtDariField.setAttribute('required', 'required');
            if (dtDariRequired) {
                dtDariRequired.style.display = 'inline'; // Tampilkan tanda required
            }

            // Jika tipe izin = "Masuk Siang" dan jenis izin pribadi, auto-fill jam "Dari"
            if (isPribadi && tipeIzin === 'Masuk Siang' && jamMasukShift) {
                dtDariField.value = jamMasukShift;
            }

            // Khusus Masuk Siang (izin pribadi): jam "Sampai" boleh kosong (tidak wajib)
            if (isPribadi && tipeIzin === 'Masuk Siang') {
                if (dtSampaiField) {
                    dtSampaiField.removeAttribute('required');
                }
                if (dtSampaiRequired) {
                    dtSampaiRequired.style.display = 'none';
                }
            } else {
                // Selain Masuk Siang: jam "Sampai" tetap required
                if (dtSampaiField) {
                    dtSampaiField.setAttribute('required', 'required');
                }
                if (dtSampaiRequired) {
                    dtSampaiRequired.style.display = 'inline';
                }
            }
        }
    });

    // Event listener untuk field "vcKodeIzin" - auto-fill jam "Dari" jika sudah pilih Tipe = "Masuk Siang" atau disable jika "Pulang Cepat"
    document.getElementById('vcKodeIzin').addEventListener('change', function() {
        const vcKodeIzin = this.value;
        const tipeIzin = document.getElementById('vcTipeIzin').value;
        const dtDariField = document.getElementById('dtDari');
        const dtDariRequired = document.getElementById('dtDariRequired');
        const dtSampaiField = document.getElementById('dtSampai');
        const dtSampaiRequired = document.getElementById('dtSampaiRequired');

        // Cek apakah jenis izin adalah izin pribadi (Z003 atau Z004)
        const isPribadi = vcKodeIzin === 'Z003' || vcKodeIzin === 'Z004';

        // Jika tipe izin = "Pulang Cepat" dan jenis izin pribadi, disable field "Dari" dan hapus required
        if (isPribadi && tipeIzin === 'Pulang Cepat') {
            dtDariField.disabled = true;
            dtDariField.removeAttribute('required');
            dtDariField.value = ''; // Clear value
            if (dtDariRequired) {
                dtDariRequired.style.display = 'none'; // Sembunyikan tanda required
            }
            // Untuk Pulang Cepat, jam "Sampai" tetap wajib diisi
            if (dtSampaiField) {
                dtSampaiField.setAttribute('required', 'required');
            }
            if (dtSampaiRequired) {
                dtSampaiRequired.style.display = 'inline';
            }
        } else {
            // Enable field "Dari" dan set required
            dtDariField.disabled = false;
            dtDariField.setAttribute('required', 'required');
            if (dtDariRequired) {
                dtDariRequired.style.display = 'inline'; // Tampilkan tanda required
            }

            // Jika tipe izin = "Masuk Siang" dan jenis izin pribadi, auto-fill jam "Dari"
            if (isPribadi && tipeIzin === 'Masuk Siang' && jamMasukShift) {
                dtDariField.value = jamMasukShift;
            }

            // Khusus Masuk Siang (izin pribadi): jam "Sampai" boleh kosong (tidak wajib)
            if (isPribadi && tipeIzin === 'Masuk Siang') {
                if (dtSampaiField) {
                    dtSampaiField.removeAttribute('required');
                }
                if (dtSampaiRequired) {
                    dtSampaiRequired.style.display = 'none';
                }
            } else {
                // Selain Masuk Siang: jam "Sampai" tetap required
                if (dtSampaiField) {
                    dtSampaiField.setAttribute('required', 'required');
                }
                if (dtSampaiRequired) {
                    dtSampaiRequired.style.display = 'inline';
                }
            }
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
        const dtSampaiField = document.getElementById('dtSampai');
        const dtSampaiRequired = document.getElementById('dtSampaiRequired');

        if (!tipeIzinGroup || !tipeIzinSelect) return;

        // Tampilkan field tipe jika jenis izin = Z003 atau Z004, atau jika keterangan mengandung "pribadi"
        const isPribadi = kodeIzin === 'Z003' || kodeIzin === 'Z004' || keterangan.includes('pribadi');

        if (isPribadi) {
            tipeIzinGroup.classList.remove('d-none');
            tipeIzinSelect.setAttribute('required', 'required');

            // Handle field "Dari" berdasarkan tipe izin yang sudah dipilih
            const dtDariField = document.getElementById('dtDari');
            const dtDariRequired = document.getElementById('dtDariRequired');
            const tipeIzinValue = tipeIzinSelect.value;

            if (tipeIzinValue === 'Pulang Cepat') {
                // Disable field "Dari" untuk Pulang Cepat
                dtDariField.disabled = true;
                dtDariField.removeAttribute('required');
                dtDariField.value = '';
                if (dtDariRequired) {
                    dtDariRequired.style.display = 'none';
                }

                // Untuk Pulang Cepat, jam "Sampai" wajib
                if (dtSampaiField) {
                    dtSampaiField.setAttribute('required', 'required');
                }
                if (dtSampaiRequired) {
                    dtSampaiRequired.style.display = 'inline';
                }
            } else {
                // Enable field "Dari" untuk tipe lain
                dtDariField.disabled = false;
                dtDariField.setAttribute('required', 'required');
                if (dtDariRequired) {
                    dtDariRequired.style.display = 'inline';
                }

                // Jika tipe izin sudah dipilih "Masuk Siang" dan jam masuk shift tersedia, auto-fill jam "Dari"
                if (tipeIzinValue === 'Masuk Siang' && jamMasukShift) {
                    dtDariField.value = jamMasukShift;
                }

                // Khusus Masuk Siang (izin pribadi): jam "Sampai" boleh kosong
                if (tipeIzinValue === 'Masuk Siang') {
                    if (dtSampaiField) {
                        dtSampaiField.removeAttribute('required');
                    }
                    if (dtSampaiRequired) {
                        dtSampaiRequired.style.display = 'none';
                    }
                } else {
                    // Selain Masuk Siang: jam "Sampai" tetap required
                    if (dtSampaiField) {
                        dtSampaiField.setAttribute('required', 'required');
                    }
                    if (dtSampaiRequired) {
                        dtSampaiRequired.style.display = 'inline';
                    }
                }
            }
        } else {
            tipeIzinGroup.classList.add('d-none');
            tipeIzinSelect.removeAttribute('required');
            tipeIzinSelect.value = ''; // Reset value jika bukan pribadi

            // Enable field "Dari" jika bukan pribadi
            const dtDariField = document.getElementById('dtDari');
            const dtDariRequired = document.getElementById('dtDariRequired');
            dtDariField.disabled = false;
            dtDariField.setAttribute('required', 'required');
            if (dtDariRequired) {
                dtDariRequired.style.display = 'inline';
            }

            // Untuk bukan pribadi: jam "Sampai" tetap required
            if (dtSampaiField) {
                dtSampaiField.setAttribute('required', 'required');
            }
            if (dtSampaiRequired) {
                dtSampaiRequired.style.display = 'inline';
            }
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

    if (searchInput) {
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
    }

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
        if (searchInput && autocompleteDiv && !searchInput.contains(e.target) && !autocompleteDiv.contains(e.target)) {
            autocompleteDiv.style.display = 'none';
            selectedIndex = -1;
        }
    });

    function updateSelectedItem(items) {
        items.forEach((item, index) => {
            item.classList.toggle('active', index === selectedIndex);
        });
    }

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