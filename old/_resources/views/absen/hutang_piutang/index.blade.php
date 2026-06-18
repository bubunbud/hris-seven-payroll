@extends('layouts.app')

@section('title', 'Hutang-Piutang Karyawan - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-file-invoice-dollar me-2"></i>Hutang-Piutang Karyawan
                </h2>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-success" id="addBtn">
                        <i class="fas fa-plus me-1"></i>Tambah
                    </button>
                    <button type="button" class="btn btn-info" id="uploadBtn">
                        <i class="fas fa-file-upload me-1"></i>Upload Excel/CSV
                    </button>
                </div>
            </div>

            <!-- Filter -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('hutang-piutang.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="periode_awal" class="form-label">Periode Awal</label>
                                <input type="date" class="form-control" id="periode_awal" name="periode_awal" value="{{ $periodeAwal }}">
                            </div>
                            <div class="col-md-2">
                                <label for="periode_akhir" class="form-label">Periode Akhir</label>
                                <input type="date" class="form-control" id="periode_akhir" name="periode_akhir" value="{{ $periodeAkhir }}">
                            </div>
                            <div class="col-md-2">
                                <label for="nik" class="form-label">NIK</label>
                                <input type="text" class="form-control" id="nik" name="nik" value="{{ $nik }}" placeholder="Cari NIK">
                            </div>
                            <div class="col-md-2">
                                <label for="hutang_piutang" class="form-label">Hutang/Piutang</label>
                                <select class="form-select" id="hutang_piutang" name="hutang_piutang">
                                    <option value="">Semua</option>
                                    @foreach($masterHutangPiutangs as $master)
                                    <option value="{{ $master->vcJenis }}" {{ $hutangPiutang == $master->vcJenis ? 'selected' : '' }}>
                                        {{ $master->vcKeterangan }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="debit_kredit" class="form-label">Debit/Kredit</label>
                                <select class="form-select" id="debit_kredit" name="debit_kredit">
                                    <option value="">Semua</option>
                                    <option value="Debit" {{ $debitKredit == 'Debit' ? 'selected' : '' }}>Debit</option>
                                    <option value="Kredit" {{ $debitKredit == 'Kredit' ? 'selected' : '' }}>Kredit</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
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
                                    <th width="12%">Periode Awal</th>
                                    <th width="12%">Periode Akhir</th>
                                    <th width="10%">NIK</th>
                                    <th width="18%">Nama</th>
                                    <th width="15%">Jenis</th>
                                    <th width="8%">D/K</th>
                                    <th width="10%">Jumlah</th>
                                    <th width="10%">Keterangan</th>
                                    <th width="5%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($records as $row)
                                <tr>
                                    <td>{{ $row->dtTanggalAwal->format('d/m/Y') }}</td>
                                    <td>{{ $row->dtTanggalAkhir->format('d/m/Y') }}</td>
                                    <td><strong>{{ $row->vcNik }}</strong></td>
                                    <td>{{ $row->karyawan->Nama ?? 'N/A' }}</td>
                                    <td>{{ $row->masterHutangPiutang->vcKeterangan ?? $row->vcJenis }}</td>
                                    <td>
                                        @if($row->vcFlag == '0')
                                        <span class="badge bg-success">Debit</span>
                                        @else
                                        <span class="badge bg-danger">Kredit</span>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format($row->decAmount, 2, ',', '.') }}</td>
                                    <td>{{ $row->vcKeterangan }}</td>
                                    <td>
                                        @php
                                        $compositeKey = $row->dtTanggalAwal->format('Y-m-d') . '|' . $row->dtTanggalAkhir->format('Y-m-d') . '|' . $row->vcNik . '|' . $row->vcJenis;
                                        $encodedKey = base64_encode($compositeKey);
                                        @endphp
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="editRecord('{{ $encodedKey }}')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteRecord('{{ $encodedKey }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
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
<div class="modal fade" id="hpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="hpModalLabel">Tambah Hutang-Piutang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="hpForm">
                <input type="hidden" name="_method" id="_method" value="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dtTanggalAwal" class="form-label">Periode Awal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dtTanggalAwal" name="dtTanggalAwal" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dtTanggalAkhir" class="form-label">Periode Akhir <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dtTanggalAkhir" name="dtTanggalAkhir" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="vcNik" class="form-label">NIK <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="vcNik" name="vcNik" maxlength="10" required>
                        <div class="form-text" id="namaPreview"></div>
                    </div>
                    <div class="mb-3">
                        <label for="vcJenis" class="form-label">Hutang/Piutang <span class="text-danger">*</span></label>
                        <select class="form-select" id="vcJenis" name="vcJenis" required>
                            <option value="">Pilih Jenis</option>
                            @foreach($masterHutangPiutangs as $master)
                            <option value="{{ $master->vcJenis }}">{{ $master->vcKeterangan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vcDebitKredit" class="form-label">Debit/Kredit <span class="text-danger">*</span></label>
                                <select class="form-select" id="vcDebitKredit" name="vcDebitKredit" required>
                                    <option value="Debit">Debit</option>
                                    <option value="Kredit" selected>Kredit</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="decJumlah" class="form-label">Jumlah <span class="text-danger">*</span></label>
                                <input type="text" class="form-control text-end" id="decJumlah" name="decJumlah" placeholder="0,00" required>
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

<!-- Modal Upload -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Data Hutang-Piutang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="uploadFile" class="form-label">Pilih File (CSV/TXT/XLSX/XLS) <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="uploadFile" name="file" accept=".csv,.txt,.xlsx,.xls" required>
                        <div class="form-text">Format: CSV dengan kolom: Periode Awal, Periode Akhir, NIK, Jenis, D/K, Jumlah, Keterangan</div>
                    </div>
                    <div class="mb-3">
                        <label for="separator" class="form-label">Separator <span class="text-danger">*</span></label>
                        <select class="form-select" id="separator" name="separator" required>
                            <option value="auto" selected>Auto Detect (Otomatis)</option>
                            <option value="comma">Koma (,)</option>
                            <option value="tab">Tab (Seperti Excel)</option>
                            <option value="semicolon">Semicolon (;)</option>
                        </select>
                        <div class="form-text">Pilih separator yang digunakan dalam file CSV. Pilih "Auto Detect" untuk deteksi otomatis.</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="skipHeader" name="skip_header" value="1" checked>
                            <label class="form-check-label" for="skipHeader">
                                Skip baris pertama (header)
                            </label>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <strong>Contoh Format CSV:</strong><br>
                                <small>
                                    Periode Awal,Periode Akhir,NIK,Jenis,D/K,Jumlah,Keterangan<br>
                                    2025-01-01,2025-01-31,19800002,0,Kredit,500000.00,Potongan Koperasi<br>
                                    2025-01-01,2025-01-31,19910003,1,Kredit,250000.00,Potongan DPLK
                                </small>
                            </div>
                            <a href="{{ asset('template_hutang_piutang.csv') }}" download class="btn btn-sm btn-success ms-3">
                                <i class="fas fa-download me-1"></i>Download Template CSV
                            </a>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <strong>Keterangan:</strong><br>
                                • Periode Awal & Akhir: Format tanggal YYYY-MM-DD atau DD/MM/YYYY<br>
                                • NIK: Harus ada di Master Karyawan<br>
                                • Jenis: 0=Potongan Koperasi, 1=Potongan DPLK, 2=Potongan SPN, 3=Selisih Upah, 4=Potongan Lain-lain<br>
                                • D/K: Debit (menambah) atau Kredit (mengurangi). Bisa menggunakan teks "Debit"/"Kredit" atau kode "0"/"1"<br>
                                • Jumlah: Angka (bisa menggunakan titik atau koma sebagai pemisah desimal)<br>
                                • Keterangan: Opsional (maksimal 35 karakter)<br>
                                • <strong>Separator:</strong> File bisa menggunakan koma (,), tab (seperti Excel), atau semicolon (;). Pilih "Auto Detect" untuk deteksi otomatis.
                            </small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>Upload
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
        }, 4000);
    }

    // Format number untuk input jumlah
    const jumlahInput = document.getElementById('decJumlah');
    if (jumlahInput) {
        jumlahInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^\d,]/g, '');
            value = value.replace(/,/g, '.');
            if (value && !isNaN(value)) {
                const num = parseFloat(value);
                e.target.value = num.toFixed(2).replace('.', ',');
            }
        });

        jumlahInput.addEventListener('blur', function(e) {
            let value = e.target.value.replace(/,/g, '.');
            if (value && !isNaN(value)) {
                const num = parseFloat(value);
                e.target.value = num.toFixed(2).replace('.', ',');
            } else if (!value) {
                e.target.value = '0,00';
            }
        });
    }

    document.getElementById('addBtn').addEventListener('click', () => {
        isEditMode = false;
        currentId = null;
        document.getElementById('hpModalLabel').textContent = 'Tambah Hutang-Piutang';
        document.getElementById('hpForm').reset();
        document.getElementById('_method').value = 'POST';
        document.getElementById('vcNik').readOnly = false;
        document.getElementById('namaPreview').textContent = '';

        // Set default tanggal periode = bulan ini
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        document.getElementById('dtTanggalAwal').value = firstDay.toISOString().split('T')[0];
        document.getElementById('dtTanggalAkhir').value = lastDay.toISOString().split('T')[0];
        document.getElementById('vcDebitKredit').value = 'Kredit';
        document.getElementById('decJumlah').value = '0,00';

        new bootstrap.Modal(document.getElementById('hpModal')).show();
    });

    document.getElementById('hpForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const url = isEditMode ? `/hutang-piutang/${currentId}` : '/hutang-piutang';
        document.getElementById('_method').value = isEditMode ? 'PUT' : 'POST';
        const formData = new FormData(this);

        // Convert jumlah dari format Indonesia ke format database
        let jumlah = formData.get('decJumlah').replace(/\./g, '').replace(',', '.');
        if (!jumlah || isNaN(jumlah)) {
            showAlert('error', 'Jumlah harus diisi dengan benar');
            return;
        }

        // Buat body dengan mapping field name
        const bodyData = {
            dtTanggalAwal: formData.get('dtTanggalAwal'),
            dtTanggalAkhir: formData.get('dtTanggalAkhir'),
            vcNik: formData.get('vcNik'),
            vcJenis: formData.get('vcJenis'),
            vcDebitKredit: formData.get('vcDebitKredit'),
            decJumlah: jumlah,
            vcKeterangan: formData.get('vcKeterangan')
        };

        fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(bodyData)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    bootstrap.Modal.getInstance(document.getElementById('hpModal')).hide();
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
        fetch(`/hutang-piutang/${id}`, {
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
                    document.getElementById('hpModalLabel').textContent = 'Edit Hutang-Piutang';
                    document.getElementById('_method').value = 'PUT';
                    document.getElementById('dtTanggalAwal').value = data.record.dtTanggalAwal;
                    document.getElementById('dtTanggalAkhir').value = data.record.dtTanggalAkhir;
                    document.getElementById('vcNik').value = data.record.vcNik;
                    document.getElementById('vcJenis').value = data.record.vcJenis;
                    document.getElementById('vcDebitKredit').value = data.record.vcDebitKredit;
                    document.getElementById('vcKeterangan').value = data.record.vcKeterangan || '';

                    // Format jumlah dari database ke format Indonesia
                    const jumlah = parseFloat(data.record.decJumlah);
                    document.getElementById('decJumlah').value = jumlah.toFixed(2).replace('.', ',');

                    document.getElementById('namaPreview').textContent = 'Nama: ' + data.record.nama;
                    document.getElementById('vcNik').readOnly = true;
                    new bootstrap.Modal(document.getElementById('hpModal')).show();
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('error', 'Gagal memuat data');
            });
    }

    function deleteRecord(id) {
        if (!confirm('Hapus data ini?')) return;
        fetch(`/hutang-piutang/${id}`, {
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

    // Autofill nama saat NIK di-blur
    document.getElementById('vcNik').addEventListener('blur', function() {
        const nik = this.value.trim();
        if (!nik) {
            document.getElementById('namaPreview').textContent = '';
            return;
        }
        fetch(`/karyawan/${nik}`, {
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
    });

    // Upload Button
    document.getElementById('uploadBtn')?.addEventListener('click', () => {
        document.getElementById('uploadForm').reset();
        document.getElementById('skipHeader').checked = true;
        new bootstrap.Modal(document.getElementById('uploadModal')).show();
    });

    // Upload Form
    document.getElementById('uploadForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const fileInput = document.getElementById('uploadFile');

        if (!fileInput.files.length) {
            showAlert('error', 'Pilih file terlebih dahulu');
            return;
        }

        // Show loading
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Mengupload...';

        fetch('{{ route("hutang-piutang.upload") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

                if (data.success) {
                    let message = data.message;
                    if (data.success_count > 0 || data.error_count > 0) {
                        message = `Berhasil: ${data.success_count}, Gagal: ${data.error_count}`;
                        if (data.errors && data.errors.length > 0) {
                            message += '\n\nError:\n' + data.errors.slice(0, 5).join('\n');
                            if (data.errors.length > 5) {
                                message += `\n... dan ${data.errors.length - 5} error lainnya`;
                            }
                        }
                    }
                    showAlert('success', message);
                    bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert('error', data.message || 'Gagal mengupload file');
                }
            })
            .catch(err => {
                console.error(err);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                showAlert('error', 'Terjadi kesalahan saat mengupload file');
            });
    });

    // Auto submit filter saat tanggal berubah
    document.getElementById('periode_awal')?.addEventListener('change', () => document.getElementById('filterForm').submit());
    document.getElementById('periode_akhir')?.addEventListener('change', () => document.getElementById('filterForm').submit());
</script>
@endpush