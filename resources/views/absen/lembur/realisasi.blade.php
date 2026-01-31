@extends('layouts.app')

@section('title', 'Realisasi Lembur - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-clock me-2"></i>Realisasi Lembur
                </h2>
            </div>

            <!-- Filter -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('realisasi-lembur.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="dari_tanggal" class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" id="dari_tanggal" name="dari_tanggal" value="{{ $dariTanggal }}">
                            </div>
                            <div class="col-md-2">
                                <label for="sampai_tanggal" class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" id="sampai_tanggal" name="sampai_tanggal" value="{{ $sampaiTanggal }}">
                            </div>
                            <div class="col-md-5">
                                <label for="search" class="form-label">Pencarian (NIK / Nama)</label>
                                <input type="text" class="form-control" id="search" name="search" value="{{ $search }}" placeholder="Pencarian...">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 shadow-sm">
                                    <i class="fas fa-search me-2"></i>Preview
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <strong>Total: {{ $records->total() }} Data</strong>
                        </div>
                        <div>
                            <button type="button" class="btn btn-success" id="btnSaveAll">
                                <i class="fas fa-save me-1"></i>Simpan Semua
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height:600px; overflow-y:auto;">
                        <form id="realisasiForm">
                            <table class="table table-hover table-sm">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="6%">Tanggal</th>
                                        <th width="5%">NIK</th>
                                        <th width="11%">Nama</th>
                                        <th width="7%">Jam Masuk</th>
                                        <th width="7%">Jam Pulang</th>
                                        <th width="9%" class="bg-success bg-opacity-25">Jam Awal Lembur</th>
                                        <th width="9%" class="bg-success bg-opacity-25">Jam Akhir Lembur</th>
                                        <th width="6%">Istirahat</th>
                                        <th width="7%">Total Jam Lembur</th>
                                        <th width="8%">Kode Lembur</th>
                                        <th width="8%" class="text-center">Konfirmasi</th>
                                        <th width="8%" class="text-center">Status</th>
                                        <th width="8%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($records as $row)
                                    @php
                                    $tanggalStr = $row->dtTanggal->format('Y-m-d');
                                    $isHariLibur = in_array($tanggalStr, $hariLiburList ?? []);
                                    @endphp
                                    <tr data-nik="{{ $row->vcNik }}" data-tanggal="{{ $tanggalStr }}" data-hari-libur="{{ $isHariLibur ? '1' : '0' }}" data-counter="{{ $row->vcCounter ?? '' }}">
                                        <td>{{ $row->dtTanggal->format('d/m/Y') }}</td>
                                        <td><strong>{{ $row->vcNik }}</strong></td>
                                        <td>{{ $row->karyawan->Nama ?? 'N/A' }}</td>
                                        <td>{{ $row->dtJamMasuk ? substr($row->dtJamMasuk, 0, 5) : '-' }}</td>
                                        <td>{{ $row->dtJamKeluar ? substr($row->dtJamKeluar, 0, 5) : '-' }}</td>
                                        <td class="bg-success bg-opacity-10">
                                            <input type="time"
                                                class="form-control form-control-sm jam-masuk-lembur"
                                                name="data[{{ $row->vcNik }}][dtJamMasukLembur]"
                                                value="{{ $row->dtJamMasukLembur ? substr($row->dtJamMasukLembur, 0, 5) : '' }}"
                                                data-nik="{{ $row->vcNik }}">
                                        </td>
                                        <td class="bg-success bg-opacity-10">
                                            <input type="time"
                                                class="form-control form-control-sm jam-keluar-lembur"
                                                name="data[{{ $row->vcNik }}][dtJamKeluarLembur]"
                                                value="{{ $row->dtJamKeluarLembur ? substr($row->dtJamKeluarLembur, 0, 5) : '' }}"
                                                data-nik="{{ $row->vcNik }}">
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary durasi-istirahat" data-nik="{{ $row->vcNik }}" data-istirahat="{{ $row->intDurasiIstirahat ?? 0 }}">
                                                {{ $row->intDurasiIstirahat ?? 0 }} menit
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info total-jam-lembur" data-nik="{{ $row->vcNik }}">0.00</span>
                                        </td>
                                        <td>
                                            @if($row->vcCounter)
                                            <span class="badge bg-primary" title="Klik untuk melihat detail lembur" style="cursor: pointer;" onclick="viewLembur('{{ $row->vcCounter }}')">
                                                {{ $row->vcCounter }}
                                            </span>
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                            $tanggalStr = $row->dtTanggal->format('Y-m-d');
                                            $isHariLibur = in_array($tanggalStr, $hariLiburList ?? []);
                                            @endphp
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input confirm-lembur-checkbox"
                                                    type="checkbox"
                                                    data-nik="{{ $row->vcNik }}"
                                                    data-tanggal="{{ $tanggalStr }}"
                                                    {{ $row->vcCfmLembur == '1' ? 'checked' : '' }}
                                                    {{ !$isHariLibur ? 'disabled title="Konfirmasi lembur hanya untuk hari libur"' : '' }}
                                                    onchange="confirmLembur('{{ $row->vcNik }}', '{{ $tanggalStr }}', this.checked)">
                                            </div>
                                            @if(!$isHariLibur)
                                            <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                                                <i class="fas fa-info-circle"></i> Bukan HL
                                            </small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge status-lembur" data-nik="{{ $row->vcNik }}" id="status-{{ $row->vcNik }}">
                                                -
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" onclick="saveRow('{{ $row->vcNik }}')">
                                                <i class="fas fa-save"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="clearRow('{{ $row->vcNik }}')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="13" class="text-center py-4">
                                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">Belum ada data</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </form>
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

<!-- Modal View Detail Lembur -->
<div class="modal fade" id="viewLemburModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Lembur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewLemburModalBody">
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

    // Hitung total jam lembur otomatis
    function calculateTotalJam(nik, counter = null) {
        // Jika ada counter, gunakan selector yang lebih spesifik
        let row;
        if (counter) {
            row = document.querySelector(`tr[data-nik="${nik}"][data-counter="${counter}"]`);
        }
        // Jika tidak ada counter atau tidak ditemukan, gunakan selector biasa
        if (!row) {
            row = document.querySelector(`tr[data-nik="${nik}"]`);
        }
        // Jika masih tidak ditemukan, coba ambil semua row dengan NIK yang sama dan hitung yang pertama
        if (!row) {
            const rows = document.querySelectorAll(`tr[data-nik="${nik}"]`);
            if (rows.length > 0) {
                row = rows[0];
            }
        }
        if (!row) return;

        const jamMasukLemburInput = row.querySelector('.jam-masuk-lembur');
        const jamKeluarLemburInput = row.querySelector('.jam-keluar-lembur');
        const totalJamEl = row.querySelector('.total-jam-lembur');
        const istirahatEl = row.querySelector('.durasi-istirahat');

        if (!totalJamEl) return;

        // Ambil nilai dari input, jika kosong coba ambil dari value attribute atau defaultValue
        let jamMasukLembur = '';
        let jamKeluarLembur = '';

        if (jamMasukLemburInput) {
            jamMasukLembur = jamMasukLemburInput.value ||
                jamMasukLemburInput.getAttribute('value') ||
                jamMasukLemburInput.defaultValue ||
                '';
        }

        if (jamKeluarLemburInput) {
            jamKeluarLembur = jamKeluarLemburInput.value ||
                jamKeluarLemburInput.getAttribute('value') ||
                jamKeluarLemburInput.defaultValue ||
                '';
        }

        // Normalisasi format waktu (pastikan format HH:MM)
        if (jamMasukLembur && jamMasukLembur.length > 5) {
            jamMasukLembur = jamMasukLembur.substring(0, 5);
        }
        if (jamKeluarLembur && jamKeluarLembur.length > 5) {
            jamKeluarLembur = jamKeluarLembur.substring(0, 5);
        }

        // Ambil durasi istirahat dalam menit
        const durasiIstirahatMenit = istirahatEl ? parseInt(istirahatEl.dataset.istirahat || 0) : 0;

        if (jamMasukLembur && jamKeluarLembur) {
            // Pastikan format waktu valid (HH:MM)
            const timeRegex = /^([01][0-9]|2[0-3]):[0-5][0-9]$/;
            if (!timeRegex.test(jamMasukLembur) || !timeRegex.test(jamKeluarLembur)) {
                totalJamEl.textContent = '0.00';
                totalJamEl.className = 'badge bg-secondary total-jam-lembur';
                return;
            }

            const mulai = new Date('2000-01-01T' + jamMasukLembur + ':00');
            let selesai = new Date('2000-01-01T' + jamKeluarLembur + ':00');

            // Jika selesai < mulai, berarti overnight (tambah 1 hari)
            if (selesai < mulai) {
                selesai.setDate(selesai.getDate() + 1);
            }

            const diffMs = selesai - mulai;
            let diffHours = diffMs / (1000 * 60 * 60);

            // Kurangi waktu istirahat (dalam menit, dikonversi ke jam)
            if (durasiIstirahatMenit > 0) {
                diffHours = diffHours - (durasiIstirahatMenit / 60);
            }

            if (diffHours > 0) {
                totalJamEl.textContent = diffHours.toFixed(2);
                totalJamEl.className = 'badge bg-info total-jam-lembur';
            } else {
                totalJamEl.textContent = '0.00';
                totalJamEl.className = 'badge bg-secondary total-jam-lembur';
            }
        } else {
            totalJamEl.textContent = '0.00';
            totalJamEl.className = 'badge bg-secondary total-jam-lembur';
        }
    }

    // Event listener untuk hitung otomatis saat input berubah
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('jam-masuk-lembur') || e.target.classList.contains('jam-keluar-lembur')) {
            const nik = e.target.dataset.nik;
            calculateTotalJam(nik);
            checkStatusLembur(nik);
        }
    });

    // Hitung status lembur (OK/NOT OK)
    function checkStatusLembur(nik) {
        const row = document.querySelector(`tr[data-nik="${nik}"]`);
        if (!row) return;

        // Ambil data hari libur
        const isHariLibur = row.dataset.hariLibur === '1';

        // Ambil jam masuk dari kolom ke-4 (index 3)
        const jamMasukCell = row.querySelectorAll('td')[3];
        // Ambil jam pulang dari kolom ke-5 (index 4)
        const jamPulangCell = row.querySelectorAll('td')[4];
        const jamMasukLemburInput = row.querySelector('.jam-masuk-lembur');
        const jamKeluarLemburInput = row.querySelector('.jam-keluar-lembur');
        const statusBadge = row.querySelector('.status-lembur[data-nik="' + nik + '"]');

        if (!statusBadge || !jamPulangCell) return;

        const jamMasuk = jamMasukCell ? jamMasukCell.textContent.trim() : '';
        const jamPulang = jamPulangCell.textContent.trim();

        // Skip jika jam keluar lembur kosong
        if (!jamKeluarLemburInput || !jamKeluarLemburInput.value) {
            statusBadge.textContent = '-';
            statusBadge.className = 'badge status-lembur bg-secondary';
            return;
        }

        const jamMasukLembur = jamMasukLemburInput ? jamMasukLemburInput.value.substring(0, 5) : '';
        const jamKeluarLembur = jamKeluarLemburInput.value.substring(0, 5);

        // Jika jam masuk atau keluar lembur kosong, skip
        if (!jamMasukLembur || !jamKeluarLembur) {
            statusBadge.textContent = '-';
            statusBadge.className = 'badge status-lembur bg-secondary';
            return;
        }

        let isOK = false;

        if (isHariLibur) {
            // Untuk hari libur, status OK jika:
            // 1. Jam awal lembur = jam masuk DAN jam akhir lembur = jam pulang, ATAU
            // 2. Range lembur masih di dalam range absensi (jam awal lembur >= jam masuk DAN jam akhir lembur <= jam pulang)

            if (jamMasuk && jamMasuk !== '-' && jamPulang && jamPulang !== '-') {
                const jamMasukTime = jamMasuk.substring(0, 5);
                const jamPulangTime = jamPulang.substring(0, 5);

                // Konversi ke waktu untuk perbandingan
                const masukTime = new Date('2000-01-01T' + jamMasukTime + ':00');
                const pulangTime = new Date('2000-01-01T' + jamPulangTime + ':00');
                const masukLemburTime = new Date('2000-01-01T' + jamMasukLembur + ':00');
                const keluarLemburTime = new Date('2000-01-01T' + jamKeluarLembur + ':00');

                // Handle overnight (jika keluar < masuk, berarti melewati tengah malam)
                if (keluarLemburTime < masukLemburTime) {
                    keluarLemburTime.setDate(keluarLemburTime.getDate() + 1);
                }
                if (pulangTime < masukTime) {
                    pulangTime.setDate(pulangTime.getDate() + 1);
                }

                // Kondisi 1: Jam awal lembur = jam masuk DAN jam akhir lembur = jam pulang
                const samaPersis = jamMasukLembur === jamMasukTime && jamKeluarLembur === jamPulangTime;

                // Kondisi 2: Range lembur masih di dalam range absensi
                const dalamRange = masukLemburTime >= masukTime && keluarLemburTime <= pulangTime;

                isOK = samaPersis || dalamRange;
            } else {
                // Jika tidak ada jam masuk/pulang absensi, gunakan logika default
                // (jam akhir lembur <= jam pulang jika ada, atau skip)
                if (jamPulang && jamPulang !== '-') {
                    const jamPulangTime = jamPulang.substring(0, 5);
                    const pulangTime = new Date('2000-01-01T' + jamPulangTime + ':00');
                    const keluarLemburTime = new Date('2000-01-01T' + jamKeluarLembur + ':00');
                    isOK = keluarLemburTime <= pulangTime;
                }
            }
        } else {
            // Untuk hari kerja, status OK jika jam akhir lembur <= jam pulang
            if (jamPulang && jamPulang !== '-') {
                const jamPulangTime = jamPulang.substring(0, 5);
                const pulangTime = new Date('2000-01-01T' + jamPulangTime + ':00');
                const keluarLemburTime = new Date('2000-01-01T' + jamKeluarLembur + ':00');
                isOK = keluarLemburTime <= pulangTime;
            }
        }

        // Update status badge
        if (isOK) {
            statusBadge.textContent = 'OK';
            statusBadge.className = 'badge status-lembur bg-success';
        } else {
            statusBadge.textContent = 'NOT OK';
            statusBadge.className = 'badge status-lembur bg-danger';
        }
    }

    // Hitung total jam untuk semua row saat load
    // Gunakan DOMContentLoaded atau setTimeout untuk memastikan semua input sudah ter-render
    function initializeCalculations() {
        document.querySelectorAll('tr[data-nik]').forEach((row, index) => {
            const nik = row.dataset.nik;
            const counter = row.dataset.counter || '';

            if (nik) {
                // Cek apakah input sudah memiliki value
                const jamMasukInput = row.querySelector('.jam-masuk-lembur');
                const jamKeluarInput = row.querySelector('.jam-keluar-lembur');

                if (jamMasukInput && jamKeluarInput) {
                    // Ambil value dari berbagai sumber
                    const jamMasukValue = jamMasukInput.value ||
                        jamMasukInput.getAttribute('value') ||
                        jamMasukInput.defaultValue || '';
                    const jamKeluarValue = jamKeluarInput.value ||
                        jamKeluarInput.getAttribute('value') ||
                        jamKeluarInput.defaultValue || '';

                    // Jika value sudah ada, hitung langsung
                    if (jamMasukValue && jamKeluarValue) {
                        // Force set value untuk memastikan input type="time" membaca value
                        if (!jamMasukInput.value) {
                            jamMasukInput.value = jamMasukValue.substring(0, 5);
                        }
                        if (!jamKeluarInput.value) {
                            jamKeluarInput.value = jamKeluarValue.substring(0, 5);
                        }

                        // Hitung dengan delay kecil untuk memastikan value sudah ter-set
                        setTimeout(() => {
                            calculateTotalJam(nik, counter);
                            checkStatusLembur(nik);
                        }, 50);
                    } else {
                        // Jika belum ada, coba lagi setelah delay lebih lama
                        setTimeout(() => {
                            // Coba lagi ambil value
                            const jamMasukRetry = jamMasukInput.value ||
                                jamMasukInput.getAttribute('value') ||
                                jamMasukInput.defaultValue || '';
                            const jamKeluarRetry = jamKeluarInput.value ||
                                jamKeluarInput.getAttribute('value') ||
                                jamKeluarInput.defaultValue || '';

                            if (jamMasukRetry && jamKeluarRetry) {
                                if (!jamMasukInput.value) {
                                    jamMasukInput.value = jamMasukRetry.substring(0, 5);
                                }
                                if (!jamKeluarInput.value) {
                                    jamKeluarInput.value = jamKeluarRetry.substring(0, 5);
                                }
                            }

                            calculateTotalJam(nik, counter);
                            checkStatusLembur(nik);
                        }, 500);
                    }
                }
            }
        });
    }

    // Panggil saat DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            // Delay sedikit untuk memastikan semua value sudah ter-set
            setTimeout(initializeCalculations, 300);
            // Retry sekali lagi setelah delay lebih lama untuk memastikan semua input sudah ter-render
            setTimeout(initializeCalculations, 1000);
        });
    } else {
        // DOM sudah ready, panggil dengan delay
        setTimeout(initializeCalculations, 300);
        // Retry sekali lagi setelah delay lebih lama
        setTimeout(initializeCalculations, 1000);
    }

    // Tambahkan event listener untuk input type="time" saat value berubah
    // Ini memastikan perhitungan dilakukan saat value ter-set (termasuk dari server)
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('jam-masuk-lembur') || e.target.classList.contains('jam-keluar-lembur')) {
            const nik = e.target.dataset.nik;
            if (nik) {
                // Delay kecil untuk memastikan kedua input sudah ter-update
                setTimeout(() => {
                    calculateTotalJam(nik);
                    checkStatusLembur(nik);
                }, 100);
            }
        }
    });

    // Save per row
    function saveRow(nik) {
        const row = document.querySelector(`tr[data-nik="${nik}"]`);
        if (!row) return;

        const tanggal = row.dataset.tanggal;
        if (!tanggal) {
            showAlert('error', 'Tanggal tidak ditemukan');
            return;
        }

        const jamMasukLembur = row.querySelector('.jam-masuk-lembur').value;
        const jamKeluarLembur = row.querySelector('.jam-keluar-lembur').value;

        const formData = new FormData();
        formData.append('dtJamMasukLembur', jamMasukLembur || '');
        formData.append('dtJamKeluarLembur', jamKeluarLembur || '');
        formData.append('_method', 'PUT');

        fetch(`/realisasi-lembur/${tanggal}/${nik}`, {
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
                    showAlert('success', data.message);
                } else {
                    showAlert('error', data.message || 'Gagal menyimpan data');
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('error', err.message || 'Terjadi kesalahan saat menyimpan');
            });
    }

    // Konfirmasi lembur (checkbox change)
    function confirmLembur(nik, tanggal, checked) {
        const checkbox = document.querySelector(`input.confirm-lembur-checkbox[data-nik="${nik}"][data-tanggal="${tanggal}"]`);
        if (!checkbox) return;

        // Disable checkbox saat proses
        checkbox.disabled = true;

        const formData = new FormData();
        formData.append('vcCfmLembur', checked ? '1' : '0');

        fetch(`/realisasi-lembur/${tanggal}/${nik}/confirm`, {
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
                    checkbox.disabled = false; // Re-enable setelah success

                    // Jika dikonfirmasi, update jam awal dan akhir lembur dari jam masuk/keluar absensi
                    if (checked && data.data) {
                        const row = document.querySelector(`tr[data-nik="${nik}"][data-tanggal="${tanggal}"]`);
                        if (row) {
                            const jamMasukLemburInput = row.querySelector('.jam-masuk-lembur');
                            const jamKeluarLemburInput = row.querySelector('.jam-keluar-lembur');

                            if (jamMasukLemburInput && data.data.dtJamMasukLembur) {
                                // Format HH:MM:SS ke HH:MM untuk input type="time"
                                const jamMasuk = data.data.dtJamMasukLembur.substring(0, 5);
                                jamMasukLemburInput.value = jamMasuk;
                            }

                            if (jamKeluarLemburInput && data.data.dtJamKeluarLembur) {
                                // Format HH:MM:SS ke HH:MM untuk input type="time"
                                const jamKeluar = data.data.dtJamKeluarLembur.substring(0, 5);
                                jamKeluarLemburInput.value = jamKeluar;
                            }

                            // Hitung ulang total jam lembur dan status
                            calculateTotalJam(nik);
                            checkStatusLembur(nik);
                        }
                    }
                } else {
                    // Revert checkbox jika gagal
                    checkbox.checked = !checked;
                    checkbox.disabled = false;
                    showAlert('error', data.message || 'Gagal mengkonfirmasi lembur');
                }
            })
            .catch(err => {
                console.error(err);
                // Revert checkbox jika error
                checkbox.checked = !checked;
                checkbox.disabled = false;
                showAlert('error', err.message || 'Terjadi kesalahan saat konfirmasi lembur');
            });
    }

    // Clear per row (hapus data realisasi lembur)
    function clearRow(nik) {
        if (!confirm('Hapus data realisasi lembur untuk NIK ini?')) return;

        const row = document.querySelector(`tr[data-nik="${nik}"]`);
        if (!row) return;

        const tanggal = row.dataset.tanggal;
        if (!tanggal) {
            showAlert('error', 'Tanggal tidak ditemukan');
            return;
        }

        // POST request dengan FormData (untuk CSRF token)
        const formData = new FormData();
        // FormData kosong sudah cukup, CSRF token sudah di header

        fetch(`/realisasi-lembur/${tanggal}/${nik}/delete`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                    // Jangan set Content-Type untuk FormData, browser akan set otomatis dengan boundary
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
                    showAlert('success', data.message);
                    // Clear input fields
                    row.querySelector('.jam-masuk-lembur').value = '';
                    row.querySelector('.jam-keluar-lembur').value = '';
                    // Uncheck confirm checkbox
                    const confirmCheckbox = row.querySelector('.confirm-lembur-checkbox');
                    if (confirmCheckbox) {
                        confirmCheckbox.checked = false;
                    }
                    // Recalculate
                    calculateTotalJam(nik);
                    checkStatusLembur(nik);
                    // Reload setelah 1 detik
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert('error', data.message || 'Gagal menghapus data');
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('error', err.message || 'Terjadi kesalahan saat menghapus');
            });
    }

    // Save all
    document.getElementById('btnSaveAll').addEventListener('click', function() {
        if (!confirm('Simpan semua perubahan data realisasi lembur?')) return;

        const formData = new FormData();

        const data = [];
        document.querySelectorAll('tr[data-nik]').forEach(row => {
            const nik = row.dataset.nik;
            const tanggal = row.dataset.tanggal;
            if (!tanggal) return; // Skip jika tidak ada tanggal

            const jamMasukLembur = row.querySelector('.jam-masuk-lembur').value;
            const jamKeluarLembur = row.querySelector('.jam-keluar-lembur').value;

            data.push({
                tanggal: tanggal,
                nik: nik,
                dtJamMasukLembur: jamMasukLembur || '',
                dtJamKeluarLembur: jamKeluarLembur || ''
            });
        });

        formData.append('data', JSON.stringify(data));

        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...';

        fetch('/realisasi-lembur/bulk', {
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
                btn.disabled = false;
                btn.innerHTML = originalText;
                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert('error', data.message || 'Gagal menyimpan data');
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.innerHTML = originalText;
                showAlert('error', err.message || 'Terjadi kesalahan saat menyimpan');
            });
    });

    // Auto submit filter saat tanggal berubah
    document.getElementById('dari_tanggal')?.addEventListener('change', () => {
        document.getElementById('filterForm').submit();
    });
    document.getElementById('sampai_tanggal')?.addEventListener('change', () => {
        document.getElementById('filterForm').submit();
    });

    // View detail lembur
    function viewLembur(kodeLembur) {
        fetch(`/lembur/${kodeLembur}`, {
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
                                    <div class="col-md-6"><strong>Business Unit:</strong> ${rec.vcBusinessUnit || '-'}</div>
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
                                                <th>Jabatan</th>
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
                                    <td>${detail.namaJabatan || detail.vcKodeJabatan || '-'}</td>
                                </tr>
                            `;
                        });
                    } else {
                        html += '<tr><td colspan="3" class="text-center">Tidak ada data</td></tr>';
                    }

                    html += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;

                    document.getElementById('viewLemburModalBody').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('viewLemburModal')).show();
                } else {
                    showAlert('error', 'Gagal memuat data lembur');
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('error', 'Gagal memuat data lembur');
            });
    }
</script>
@endpush