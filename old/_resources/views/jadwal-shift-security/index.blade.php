@extends('layouts.app')

@section('title', 'Jadwal Shift Satpam - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>Jadwal Shift Satpam
                </h2>
                <a href="{{ route('absen.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Periode -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('jadwal-shift-security.index') }}" id="filterForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label for="bulan" class="form-label">Bulan</label>
                                <select class="form-select" id="bulan" name="bulan" onchange="document.getElementById('filterForm').submit();">
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                                        {{ Carbon\Carbon::create(null, $i, 1)->locale('id')->monthName }}
                                        </option>
                                        @endfor
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="tahun" class="form-label">Tahun</label>
                                <select class="form-select" id="tahun" name="tahun" onchange="document.getElementById('filterForm').submit();">
                                    @for($i = date('Y') - 2; $i <= date('Y') + 2; $i++)
                                        <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_nama" class="form-label">Filter NIK / Nama</label>
                                <div class="input-group">
                                    <input type="text"
                                        class="form-control"
                                        id="filter_nama"
                                        name="filter_nama"
                                        value="{{ $filterNama ?? '' }}"
                                        placeholder="Cari NIK atau Nama...">
                                    @if(!empty($filterNama))
                                    <button type="button"
                                        class="btn btn-outline-secondary"
                                        onclick="document.getElementById('filter_nama').value=''; document.getElementById('filterForm').submit();"
                                        title="Hapus filter">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @endif
                                    <button type="submit" class="btn btn-outline-primary" title="Cari">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-5 text-end">
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImportExcel">
                                    <i class="fas fa-file-excel me-1"></i>Import Excel/CSV
                                </button>
                                <button type="button" class="btn btn-primary" id="btnSimpan">
                                    <i class="fas fa-save me-1"></i>Simpan Jadwal
                                </button>
                                <button type="button" class="btn btn-info" id="btnCopyBulanSebelumnya">
                                    <i class="fas fa-copy me-1"></i>Copy Bulan Sebelumnya
                                </button>
                                <a href="{{ route('jadwal-shift-security.report') }}" class="btn btn-warning">
                                    <i class="fas fa-chart-bar me-1"></i>Report
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid Jadwal -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        Jadwal Shift - {{ Carbon\Carbon::create($tahun, $bulan, 1)->locale('id')->monthName }} {{ $tahun }}
                        @if(!empty($filterNama))
                        <span class="badge bg-info ms-2">Filter: {{ $filterNama }} ({{ $satpams->count() }} satpam)</span>
                        @else
                        <span class="badge bg-secondary ms-2">{{ $satpams->count() }} satpam</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 70vh; overflow-y: auto; overflow-x: auto;">
                        <table class="table table-bordered table-sm mb-0" id="jadwalGrid">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="sticky-col" style="position: sticky; left: 0; background: #f8f9fa; z-index: 10; min-width: 200px;">Nama Satpam</th>
                                    @foreach($tanggalList as $tgl)
                                    <th class="text-center {{ $tgl['is_weekend'] ? 'bg-warning' : '' }} {{ $tgl['is_libur'] ? 'bg-danger text-white' : '' }}"
                                        style="min-width: 60px; max-width: 60px;"
                                        title="{{ $tgl['nama_hari'] }}">
                                        <div>{{ $tgl['hari'] }}</div>
                                        <small style="font-size: 0.7em;">{{ substr($tgl['nama_hari'], 0, 3) }}</small>
                                    </th>
                                    @endforeach
                                    <th class="text-center sticky-col-right" style="position: sticky; right: 0; background: #f8f9fa; z-index: 10; min-width: 80px;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($satpams as $satpam)
                                <tr data-nik="{{ $satpam->Nik }}">
                                    <td class="sticky-col" style="position: sticky; left: 0; background: white; z-index: 9;">
                                        <strong>{{ $satpam->Nama }}</strong><br>
                                        <small class="text-muted">{{ $satpam->Nik }}</small>
                                    </td>
                                    @foreach($tanggalList as $tgl)
                                    @php
                                    $jadwalHari = $jadwal[$satpam->Nik][$tgl['tanggal']] ?? null;
                                    $shifts = [];
                                    $isOff = false;

                                    if ($jadwalHari) {
                                    if ($jadwalHari instanceof \Illuminate\Support\Collection) {
                                    // Cek apakah ada record dengan vcKeterangan = 'OFF' atau intShift = NULL
                                    $offRecord = $jadwalHari->first(function($j) {
                                    return ($j->vcKeterangan ?? '') === 'OFF' || ($j->intShift ?? null) === null;
                                    });
                                    if ($offRecord) {
                                    $isOff = true;
                                    } else {
                                    $shifts = $jadwalHari->pluck('intShift')->filter(function($s) {
                                    return $s !== null && $s !== '';
                                    })->toArray();
                                    }
                                    } elseif (is_array($jadwalHari)) {
                                    // Cek apakah ada record dengan vcKeterangan = 'OFF' atau intShift = NULL
                                    foreach ($jadwalHari as $j) {
                                    $keterangan = is_object($j) ? ($j->vcKeterangan ?? '') : ($j['vcKeterangan'] ?? '');
                                    $shift = is_object($j) ? ($j->intShift ?? null) : ($j['intShift'] ?? null);

                                    if ($keterangan === 'OFF' || $shift === null) {
                                    $isOff = true;
                                    break;
                                    } elseif ($shift !== null && $shift !== '') {
                                    $shifts[] = $shift;
                                    }
                                    }
                                    }
                                    }

                                    $value = $isOff ? 'OFF' : (!empty($shifts) ? implode(',', array_unique($shifts)) : '');
                                    @endphp
                                    @php
                                    // Cek apakah ada jadwal yang di-override
                                    $isOverridden = false;
                                    if ($jadwalHari) {
                                    if ($jadwalHari instanceof \Illuminate\Support\Collection) {
                                    $isOverridden = $jadwalHari->contains(function($j) {
                                    return ($j->isOverride ?? false) === true;
                                    });
                                    } elseif (is_array($jadwalHari)) {
                                    $isOverridden = collect($jadwalHari)->contains(function($j) {
                                    return (is_object($j) && ($j->isOverride ?? false) === true) ||
                                    (is_array($j) && ($j['isOverride'] ?? false) === true);
                                    });
                                    }
                                    }
                                    @endphp
                                    <td class="text-center {{ $tgl['is_weekend'] ? 'bg-warning-subtle' : '' }} {{ $tgl['is_libur'] ? 'bg-danger-subtle' : '' }} {{ $isOverridden ? 'bg-info-subtle' : '' }}"
                                        data-tanggal="{{ $tgl['tanggal'] }}"
                                        data-nik="{{ $satpam->Nik }}">
                                        <div class="d-flex align-items-center gap-1">
                                            <input type="text"
                                                class="form-control form-control-sm jadwal-cell text-center flex-grow-1"
                                                value="{{ $value }}"
                                                data-nik="{{ $satpam->Nik }}"
                                                data-tanggal="{{ $tgl['tanggal'] }}"
                                                placeholder="1,2,3"
                                                style="min-width: 50px; font-size: 0.85em;"
                                                title="Format: 1, 2, 3, atau OFF. Multiple shift: 1,2">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-warning btn-override p-1"
                                                data-nik="{{ $satpam->Nik }}"
                                                data-nama="{{ $satpam->Nama }}"
                                                data-tanggal="{{ $tgl['tanggal'] }}"
                                                title="Override Jadwal (Urgent)"
                                                style="font-size: 0.7em; padding: 2px 4px !important;">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </button>
                                        </div>
                                        @if($isOverridden)
                                        <small class="text-info d-block" style="font-size: 0.65em;">
                                            <i class="fas fa-info-circle"></i> Override
                                        </small>
                                        @endif
                                    </td>
                                    @endforeach
                                    <td class="text-center sticky-col-right total-shift"
                                        style="position: sticky; right: 0; background: white; z-index: 9; font-weight: bold;"
                                        data-nik="{{ $satpam->Nik }}">
                                        0
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <strong>Keterangan:</strong><br>
                                • <strong>1</strong> = Shift 1 (06:30-14:30)<br>
                                • <strong>2</strong> = Shift 2 (14:30-22:30)<br>
                                • <strong>3</strong> = Shift 3 (22:30-06:30)<br>
                                • <strong>OFF</strong> = Lepas / Tidak Masuk<br>
                                • <strong>Format Multiple:</strong> "1,2" = Shift 1 dan Shift 2 (penggantian)
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                <span class="badge bg-warning">Weekend</span>
                                <span class="badge bg-danger">Hari Libur</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Override -->
<div class="modal fade" id="overrideModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Override Jadwal (Urgent)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="overrideForm">
                <div class="modal-body">
                    <input type="hidden" id="overrideNik" name="vcNik">
                    <input type="hidden" id="overrideTanggal" name="dtTanggal">
                    <input type="hidden" id="overrideShiftLama" name="intShiftLama">

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Override Jadwal (Urgent)</strong><br>
                        <small>Fitur ini digunakan untuk perubahan jadwal yang mendesak. Semua perubahan akan dicatat untuk audit.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Satpam</label>
                        <input type="text" class="form-control" id="overrideNama" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">NIK</label>
                        <input type="text" class="form-control" id="overrideNikDisplay" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="text" class="form-control" id="overrideTanggalDisplay" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Shift Lama</label>
                        <select class="form-select" id="overrideShiftLamaSelect" name="intShiftLama">
                            <option value="">-- Tidak Ada / OFF --</option>
                            <option value="1">Shift 1 (06:30-14:30)</option>
                            <option value="2">Shift 2 (14:30-22:30)</option>
                            <option value="3">Shift 3 (22:30-06:30)</option>
                        </select>
                        <small class="text-muted">Pilih shift yang akan diganti, atau kosongkan jika menambah shift baru</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Shift Baru <span class="text-danger">*</span></label>
                        <select class="form-select" id="overrideShiftBaru" name="intShiftBaru" required>
                            <option value="">-- Pilih Shift --</option>
                            <option value="1">Shift 1 (06:30-14:30)</option>
                            <option value="2">Shift 2 (14:30-22:30)</option>
                            <option value="3">Shift 3 (22:30-06:30)</option>
                        </select>
                        <small class="text-muted">Pilih shift baru yang akan ditetapkan</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Override <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="overrideAlasan" name="vcAlasan" rows="4" required placeholder="Jelaskan alasan override jadwal secara detail... (contoh: Satpam A ada keperluan keluarga mendesak, perlu diganti shift)"></textarea>
                        <small class="text-muted">Wajib diisi minimal 10 karakter, maksimal 500 karakter.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>Simpan Override
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hitung total shift per satpam
        function calculateTotal() {
            document.querySelectorAll('tr[data-nik]').forEach(row => {
                const nik = row.getAttribute('data-nik');
                const cells = row.querySelectorAll('.jadwal-cell');
                let total = 0;

                cells.forEach(cell => {
                    const value = cell.value.trim().toUpperCase();
                    if (value && value !== 'OFF') {
                        // Hitung jumlah shift (bisa multiple: "1,2" = 2 shift)
                        const shifts = value.split(',').filter(s => s.trim() && !isNaN(s.trim()));
                        total += shifts.length;
                    }
                });

                const totalCell = row.querySelector('.total-shift');
                if (totalCell) {
                    totalCell.textContent = total;
                }
            });
        }

        // Validasi input cell
        document.querySelectorAll('.jadwal-cell').forEach(cell => {
            cell.addEventListener('blur', function() {
                let value = this.value.trim().toUpperCase();

                // Validasi format
                if (value && value !== 'OFF') {
                    const parts = value.split(',');
                    const valid = parts.every(part => {
                        const num = part.trim();
                        return num === '1' || num === '2' || num === '3';
                    });

                    if (!valid) {
                        alert('Format tidak valid! Gunakan: 1, 2, 3, OFF, atau "1,2" untuk multiple shift');
                        this.value = '';
                        value = '';
                    } else {
                        // Normalize: sort dan remove duplicate
                        const sorted = parts.map(p => p.trim()).sort().filter((v, i, a) => a.indexOf(v) === i);
                        this.value = sorted.join(',');
                    }
                }

                calculateTotal();
            });

            cell.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    this.blur();
                }
            });
        });

        // Simpan jadwal
        document.getElementById('btnSimpan').addEventListener('click', function() {
            const jadwalData = [];

            document.querySelectorAll('.jadwal-cell').forEach(cell => {
                const nik = cell.getAttribute('data-nik');
                const tanggal = cell.getAttribute('data-tanggal');
                const value = cell.value.trim().toUpperCase();

                if (value === 'OFF') {
                    // Simpan "OFF" sebagai record khusus
                    jadwalData.push({
                        vcNik: nik,
                        dtTanggal: tanggal,
                        isOff: true
                    });
                } else if (value) {
                    // Simpan shift normal (1, 2, 3)
                    const shifts = value.split(',').map(s => parseInt(s.trim())).filter(s => !isNaN(s) && s >= 1 && s <= 3);
                    shifts.forEach(shift => {
                        jadwalData.push({
                            vcNik: nik,
                            dtTanggal: tanggal,
                            intShift: shift
                        });
                    });
                }
            });

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('bulan', '{{ $bulan }}');
            formData.append('tahun', '{{ $tahun }}');
            formData.append('jadwal', JSON.stringify(jadwalData));

            fetch('{{ route("jadwal-shift-security.store") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Jadwal berhasil disimpan!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Terjadi kesalahan'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menyimpan jadwal!');
                });
        });

        // Copy bulan sebelumnya
        document.getElementById('btnCopyBulanSebelumnya').addEventListener('click', function() {
            if (!confirm('Copy jadwal dari bulan sebelumnya? Ini akan mengganti jadwal bulan ini.')) {
                return;
            }

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('bulan', '{{ $bulan }}');
            formData.append('tahun', '{{ $tahun }}');

            fetch('{{ route("jadwal-shift-security.copy-previous-month") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message + '\nJadwal yang di-copy: ' + data.copied_count + ' record');
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Terjadi kesalahan'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat copy jadwal!');
                });
        });

        // Override jadwal - buka modal
        document.querySelectorAll('.btn-override').forEach(btn => {
            btn.addEventListener('click', function() {
                const nik = this.getAttribute('data-nik');
                const nama = this.getAttribute('data-nama');
                const tanggal = this.getAttribute('data-tanggal');

                // Ambil shift lama dari input cell yang bersebelahan
                const cell = this.closest('td').querySelector('.jadwal-cell');
                const shiftLama = cell ? cell.value.trim().toUpperCase() : '';

                // Format tanggal untuk display
                const tanggalObj = new Date(tanggal + 'T00:00:00');
                const tanggalDisplay = tanggalObj.toLocaleDateString('id-ID', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                // Set nilai form
                document.getElementById('overrideNik').value = nik;
                document.getElementById('overrideNikDisplay').value = nik;
                document.getElementById('overrideNama').value = nama;
                document.getElementById('overrideTanggal').value = tanggal;
                document.getElementById('overrideTanggalDisplay').value = tanggalDisplay;

                // Set shift lama (jika ada)
                const shiftLamaSelect = document.getElementById('overrideShiftLamaSelect');
                if (shiftLama && shiftLama !== 'OFF' && shiftLama.trim() !== '') {
                    // Jika multiple shift, ambil yang pertama
                    const firstShift = shiftLama.split(',')[0].trim();
                    if (['1', '2', '3'].includes(firstShift)) {
                        shiftLamaSelect.value = firstShift;
                    } else {
                        shiftLamaSelect.value = '';
                    }
                } else {
                    shiftLamaSelect.value = '';
                }

                // Reset shift baru dan alasan
                document.getElementById('overrideShiftBaru').value = '';
                document.getElementById('overrideAlasan').value = '';

                // Buka modal
                const modal = new bootstrap.Modal(document.getElementById('overrideModal'));
                modal.show();
            });
        });

        // Submit form override
        document.getElementById('overrideForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('_token', '{{ csrf_token() }}');

            // Ambil shift lama dari select (bukan hidden)
            const shiftLamaSelect = document.getElementById('overrideShiftLamaSelect');
            formData.set('intShiftLama', shiftLamaSelect.value || '');

            // Validasi
            const shiftBaru = document.getElementById('overrideShiftBaru').value;
            const alasan = document.getElementById('overrideAlasan').value.trim();

            if (!shiftBaru) {
                alert('Shift Baru harus dipilih!');
                return;
            }

            if (!alasan || alasan.length < 10) {
                alert('Alasan override harus diisi minimal 10 karakter!');
                return;
            }

            if (alasan.length > 500) {
                alert('Alasan override maksimal 500 karakter!');
                return;
            }

            // Konfirmasi
            if (!confirm('Apakah Anda yakin ingin melakukan override jadwal ini? Perubahan akan dicatat untuk audit.')) {
                return;
            }

            // Submit
            fetch('{{ route("jadwal-shift-security.override") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Override jadwal berhasil disimpan!');
                        // Tutup modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('overrideModal'));
                        if (modal) {
                            modal.hide();
                        }
                        // Reload halaman
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Terjadi kesalahan'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menyimpan override!');
                });
        });

        // Hitung total awal
        calculateTotal();

        // Submit form import Excel
        document.getElementById('importExcelForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('bulan', '{{ $bulan }}');
            formData.append('tahun', '{{ $tahun }}');

            const fileInput = document.getElementById('importFile');
            if (!fileInput.files || !fileInput.files[0]) {
                alert('Pilih file terlebih dahulu!');
                return;
            }

            formData.append('file', fileInput.files[0]);

            // Disable button
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Mengimport...';

            fetch('{{ route("jadwal-shift-security.import") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let message = data.message;
                        if (data.errors && data.errors.length > 0) {
                            message += '\n\nError:\n' + data.errors.slice(0, 5).join('\n');
                            if (data.errors.length > 5) {
                                message += '\n... dan ' + (data.errors.length - 5) + ' error lainnya';
                            }
                        }
                        alert(message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Terjadi kesalahan'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengimport file!');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-upload me-1"></i>Import';
                });
        });
    });
</script>

<!-- Modal Import Excel/CSV -->
<div class="modal fade" id="modalImportExcel" tabindex="-1" aria-labelledby="modalImportExcelLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalImportExcelLabel">
                    <i class="fas fa-file-excel me-2"></i>Import Jadwal dari Excel/CSV
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importExcelForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong><i class="fas fa-info-circle me-1"></i>Format File:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Format: <strong>CSV</strong> (Comma Separated Values)</li>
                            <li>Kolom: <strong>NIK, Tanggal, Shift, Keterangan (optional)</strong></li>
                            <li>Contoh: <code>19950011, 2025-12-01, 1, </code></li>
                            <li>Format tanggal: <strong>Y-m-d</strong> (2025-12-01) atau <strong>d/m/Y</strong> (01/12/2025)</li>
                            <li>Shift: <strong>1, 2, 3, atau OFF</strong> (bisa multiple: 1,2)</li>
                            <li>Baris pertama akan di-skip sebagai header</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <label for="importFile" class="form-label">Pilih File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="importFile" name="file" accept=".csv,.txt,.xlsx,.xls" required>
                        <div class="form-text">Format yang didukung: CSV, TXT (maksimal 10MB)</div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>Perhatian:</strong> Import akan menambahkan jadwal ke periode yang dipilih. Jika ada duplikasi (NIK + Tanggal + Shift sama), jadwal lama akan diganti.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload me-1"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection