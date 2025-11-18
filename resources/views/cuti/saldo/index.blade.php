@extends('layouts.app')

@section('title', 'Saldo Cuti - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-calendar-check me-2"></i>Saldo Cuti Karyawan
                </h2>
                @if($needMigration)
                <button type="button" class="btn btn-warning" onclick="window.migrateSaldo()" id="btnMigrate">
                    <i class="fas fa-sync-alt me-2"></i>Migrasi Saldo ke Tahun {{ $tahun }}
                </button>
                @endif
            </div>

            <!-- Info Rule Cuti -->
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <strong><i class="fas fa-info-circle me-2"></i>Aturan Pengurangan Saldo Cuti:</strong>
                <ul class="mb-0 mt-2">
                    <li>Hanya <strong>Cuti Tahunan (C010)</strong> dan <strong>Cuti Bersama (C012)</strong> yang mengurangi saldo cuti</li>
                    <li><strong>Cuti Bersama</strong> yang diinput di Master Hari Libur akan otomatis mengurangi saldo cuti <strong>semua karyawan</strong> pada tahun tersebut</li>
                    <li>Penggunaan = (Penggunaan Individu) + (Jumlah Hari Cuti Bersama di tahun {{ $tahun }}: <strong>{{ $cutiBersama }} hari</strong>)</li>
                    <li><strong>Prioritas Pengurangan:</strong> Saldo <strong>Tahun Lalu</strong> dikurangi terlebih dahulu, baru Saldo <strong>Tahun Ini</strong></li>
                    <li><strong>Hangus Saldo Tahun Lalu:</strong> Saldo Tahun Lalu yang masih tersisa akan <strong>hangus/terhapus</strong> pada <strong>1 April</strong> setiap tahun</li>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

            <!-- Filter -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('saldo-cuti.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="tahun" class="form-label">Tahun</label>
                                <select class="form-select" id="tahun" name="tahun">
                                    @foreach($tahunList as $t)
                                    <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="nik" class="form-label">NIK</label>
                                <input type="text" class="form-control" id="nik" name="nik" value="{{ $nik }}" placeholder="Cari NIK">
                            </div>
                            <div class="col-md-4">
                                <label for="search" class="form-label">Pencarian (NIK / Nama)</label>
                                <input type="text" class="form-control" id="search" name="search" value="{{ $search }}" placeholder="Cari NIK atau Nama...">
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
                    <div class="table-responsive" style="max-height:600px; overflow-y:auto;">
                        <table class="table table-hover table-sm">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="7%">NIK</th>
                                    <th width="16%">Nama Karyawan</th>
                                    <th width="8%" class="text-center">Tahun Lalu</th>
                                    <th width="8%" class="text-center">Tahun Ini</th>
                                    <th width="8%" class="text-center">Total Saldo</th>
                                    <th width="9%" class="text-center text-warning">Individu</th>
                                    <th width="9%" class="text-center text-info">Cuti Bersama</th>
                                    <th width="9%" class="text-center text-danger">Total Penggunaan</th>
                                    <th width="9%" class="text-center text-success">Saldo Sisa</th>
                                    <th width="10%">Status</th>
                                    <th width="7%">Detail</th>
                                    <th width="7%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($karyawans as $karyawan)
                                @php
                                $nikKey = (string) ($karyawan->Nik ?? '');
                                $saldo = $saldoData[$nikKey] ?? [];
                                @endphp
                                <tr>
                                    <td><strong>{{ $karyawan->Nik }}</strong></td>
                                    <td>{{ $karyawan->Nama }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-info" title="Penangguhan sisa cuti tahun lalu yang belum terpakai">{{ number_format($saldo['tahun_lalu'] ?? 0, 0) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary" title="Jatah cuti tahun ini (12 hari untuk karyawan tetap)">{{ number_format($saldo['tahun_ini'] ?? 0, 0) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <strong>{{ number_format($saldo['total_saldo'] ?? 0, 0) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning" title="Penggunaan cuti individu (C010 dan C012)">{{ number_format($saldo['penggunaan_individu'] ?? 0, 0) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info" title="Cuti Bersama dari Master Hari Libur (otomatis mengurangi semua karyawan)">{{ number_format($saldo['cuti_bersama'] ?? 0, 0) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger" title="Total: Individu + Cuti Bersama">{{ number_format($saldo['penggunaan'] ?? 0, 0) }}</span>
                                        <br><small class="text-muted">
                                            <span title="Terpakai dari Tahun Lalu">TL: {{ number_format($saldo['tahun_lalu_terpakai'] ?? 0, 0) }}</span> |
                                            <span title="Terpakai dari Tahun Ini">TI: {{ number_format($saldo['tahun_ini_terpakai'] ?? 0, 0) }}</span>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        @php
                                        $sisa = $saldo['saldo_sisa'] ?? 0;
                                        $badgeClass = $sisa < 0 ? 'bg-danger' : ($sisa < 5 ? 'bg-warning' : 'bg-success' );
                                            @endphp
                                            <span class="badge {{ $badgeClass }}" title="Sisa saldo cuti yang masih bisa digunakan">
                                            {{ number_format($sisa, 0) }}
                                            </span>
                                            <br><small class="text-muted">
                                                <span title="Sisa Tahun Lalu">TL: {{ number_format($saldo['tahun_lalu_sisa'] ?? 0, 0) }}</span> |
                                                <span title="Sisa Tahun Ini">TI: {{ number_format($saldo['tahun_ini_sisa'] ?? 0, 0) }}</span>
                                            </small>
                                    </td>
                                    <td>
                                        @if(($saldo['sudah_1_april'] ?? false) && ($saldo['tahun_lalu'] ?? 0) > 0)
                                        <span class="badge bg-secondary" title="Saldo Tahun Lalu sudah hangus per 1 April">Tahun Lalu Hangus</span>
                                        @else
                                        @php
                                        $sisa = $saldo['saldo_sisa'] ?? 0;
                                        if ($sisa < 0) {
                                            $statusText='Kurang' ;
                                            $statusClass='danger' ;
                                            } elseif ($sisa < 5) {
                                            $statusText='Hampir Habis' ;
                                            $statusClass='warning' ;
                                            } else {
                                            $statusText='Aman' ;
                                            $statusClass='success' ;
                                            }
                                            @endphp
                                            <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                                            @endif
                                    </td>
                                    <td>
                                        @php
                                        $detailCuti = $saldo['detail_cuti'] ?? [];
                                        @endphp
                                        @if(count($detailCuti) > 0)
                                        <button class="btn btn-sm btn-outline-info btn-detail-cuti"
                                            data-nik="{{ $karyawan->Nik }}"
                                            data-nama="{{ htmlspecialchars($karyawan->Nama, ENT_QUOTES, 'UTF-8') }}"
                                            data-detail="{{ base64_encode(json_encode($detailCuti, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE)) }}"
                                            title="Lihat Detail Cuti">
                                            <i class="fas fa-list"></i> ({{ count($detailCuti) }})
                                        </button>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="window.editSaldo('{{ $karyawan->Nik }}', '{{ $tahun }}')" title="Edit Saldo">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="12" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Belum ada data</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($karyawans->hasPages())
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
                        <div class="text-muted small">
                            Menampilkan {{ $karyawans->firstItem() }} sampai {{ $karyawans->lastItem() }} dari {{ $karyawans->total() }} data
                        </div>
                        <nav aria-label="Navigasi halaman">
                            {{ $karyawans->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Saldo Cuti -->
<div class="modal fade" id="saldoModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saldoModalLabel">Tambah/Edit Saldo Cuti</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="saldoForm">
                <input type="hidden" name="_method" id="_method" value="POST">
                <input type="hidden" name="vcNik" id="vcNik">
                <input type="hidden" name="intTahun" id="intTahun" value="{{ $tahun }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="karyawan_nama" class="form-label">Nama Karyawan</label>
                        <input type="text" class="form-control" id="karyawan_nama" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="decTahunLalu" class="form-label">Tahun Lalu <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="decTahunLalu" name="decTahunLalu" step="1" min="0" required>
                        <div class="form-text">Penangguhan sisa cuti tahun lalu yang belum terpakai (jumlah hari, bulat)</div>
                    </div>
                    <div class="mb-3">
                        <label for="decTahunIni" class="form-label">Tahun Ini</label>
                        <input type="number" class="form-control" id="decTahunIni" name="decTahunIni" step="1" min="0" value="0">
                        <div class="form-text">Jatah cuti tahun ini (12 hari untuk karyawan tetap/permanen, jumlah hari bulat)</div>
                    </div>
                    <div class="mb-3">
                        <label for="vcKeterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="vcKeterangan" name="vcKeterangan" rows="2"></textarea>
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

<!-- Modal Detail Cuti -->
<div class="modal fade" id="detailCutiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailCutiModalLabel">Detail Cuti Individu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailCutiModalBody">
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
    (function() {
        'use strict';

        try {
            // Variabel tahun untuk digunakan di fungsi
            const tahunSaldoCuti = parseInt('{{ $tahun }}', 10);

            // Pastikan fungsi tersedia di window object
            window.showAlert = function(type, message) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const alertHtml = `
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        ${message}
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
                }, 5000);
            };

            window.showDetailCuti = function(nik, nama, detailCuti) {
                const modal = document.getElementById('detailCutiModal');
                const modalLabel = document.getElementById('detailCutiModalLabel');
                const modalBody = document.getElementById('detailCutiModalBody');

                modalLabel.textContent = `Detail Cuti Individu - ${nama} (NIK: ${nik})`;

                if (!detailCuti || detailCuti.length === 0) {
                    modalBody.innerHTML = '<p class="text-muted">Tidak ada data cuti individu.</p>';
                } else {
                    let html = `
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Kode</th>
                                        <th>Tanggal Mulai</th>
                                        <th>Tanggal Selesai</th>
                                        <th class="text-center">Jumlah Hari (Total)</th>
                                        <th class="text-center">Hari di Tahun ${tahunSaldoCuti}</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    let totalHariDiTahun = 0;
                    detailCuti.forEach((record, index) => {
                        totalHariDiTahun += record.jumlah_hari_di_tahun || 0;
                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td><span class="badge bg-primary">${record.kode}</span></td>
                                <td>${record.tanggal_mulai}</td>
                                <td>${record.tanggal_selesai}</td>
                                <td class="text-center">${record.jumlah_hari_total}</td>
                                <td class="text-center"><strong>${record.jumlah_hari_di_tahun}</strong></td>
                                <td>${record.keterangan}</td>
                            </tr>
                        `;
                    });

                    html += `
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="5" class="text-end">Total Hari di Tahun ${tahunSaldoCuti}:</th>
                                        <th class="text-center">${totalHariDiTahun}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="alert alert-info mt-3 mb-0">
                            <small>
                                <strong>Keterangan:</strong><br>
                                • <strong>Jumlah Hari (Total):</strong> Total hari dari tanggal mulai sampai tanggal selesai<br>
                                • <strong>Hari di Tahun ${tahunSaldoCuti}:</strong> Jumlah hari yang benar-benar di tahun ${tahunSaldoCuti} (untuk cuti lintas tahun)
                            </small>
                        </div>
                    `;

                    modalBody.innerHTML = html;
                }

                new bootstrap.Modal(modal).show();
            };

            window.editSaldo = function(nik, tahun) {
                const form = document.getElementById('saldoForm');
                form.reset();
                document.getElementById('_method').value = 'POST';
                document.getElementById('vcNik').value = nik;
                document.getElementById('intTahun').value = tahun;

                // Fetch data karyawan untuk nama
                fetch(`/karyawan/${nik}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && data.karyawan) {
                            document.getElementById('karyawan_nama').value = data.karyawan.Nama || nik;
                        } else {
                            document.getElementById('karyawan_nama').value = nik;
                        }
                    })
                    .catch(() => {
                        document.getElementById('karyawan_nama').value = nik;
                    });

                // Cek apakah sudah ada data saldo
                const recordId = encodeURIComponent(nik + '|' + tahun);
                fetch(`/saldo-cuti/${recordId}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => {
                        if (r.ok) {
                            return r.json();
                        }
                        // Jika 404, berarti belum ada data (tambah baru)
                        return {
                            success: false
                        };
                    })
                    .then(data => {
                        if (data.success && data.record) {
                            // Edit mode - isi dengan data yang ada
                            document.getElementById('decTahunLalu').value = data.record.decTahunLalu || 0;
                            document.getElementById('decTahunIni').value = data.record.decTahunIni || 0;
                            document.getElementById('vcKeterangan').value = data.record.vcKeterangan || '';
                            document.getElementById('saldoModalLabel').textContent = 'Edit Saldo Cuti';
                        } else {
                            // Tambah mode - kosongkan form
                            document.getElementById('decTahunLalu').value = 0;
                            document.getElementById('decTahunIni').value = 0;
                            document.getElementById('vcKeterangan').value = '';
                            document.getElementById('saldoModalLabel').textContent = 'Tambah Saldo Cuti';
                        }
                    })
                    .catch(err => {
                        console.error('Error fetching saldo cuti:', err);
                        // Tetap tampilkan modal dengan form kosong
                        document.getElementById('decTahunLalu').value = 0;
                        document.getElementById('decTahunIni').value = 0;
                        document.getElementById('vcKeterangan').value = '';
                        document.getElementById('saldoModalLabel').textContent = 'Tambah Saldo Cuti';
                    });

                // Tampilkan modal (tampilkan dulu, data akan diisi kemudian)
                const modalElement = document.getElementById('saldoModal');
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                } else {
                    console.error('Modal saldoModal tidak ditemukan');
                    alert('Terjadi kesalahan. Silakan refresh halaman dan coba lagi.');
                }
            };

            // Event listener untuk tombol detail cuti
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-detail-cuti')) {
                    const btn = e.target.closest('.btn-detail-cuti');
                    const nik = btn.getAttribute('data-nik');
                    const nama = btn.getAttribute('data-nama');
                    const detailBase64 = btn.getAttribute('data-detail');
                    try {
                        // Decode dari base64 terlebih dahulu
                        const detailJson = atob(detailBase64);
                        const detailCuti = JSON.parse(detailJson);
                        window.showDetailCuti(nik, nama, detailCuti);
                    } catch (err) {
                        console.error('Error parsing detail cuti:', err);
                        console.error('Base64 string:', detailBase64);
                        alert('Terjadi kesalahan saat memuat detail cuti: ' + err.message);
                    }
                }
            });

            // Pastikan DOM sudah ready sebelum menambahkan event listener
            document.addEventListener('DOMContentLoaded', function() {
                const saldoForm = document.getElementById('saldoForm');
                if (saldoForm) {
                    saldoForm.addEventListener('submit', function(e) {
                        e.preventDefault();

                        const formData = new FormData(this);
                        const url = '/saldo-cuti';

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
                                    bootstrap.Modal.getInstance(document.getElementById('saldoModal')).hide();
                                    window.showAlert('success', data.message);
                                    setTimeout(() => location.reload(), 1000);
                                } else {
                                    window.showAlert('error', data.message || 'Gagal menyimpan data');
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                window.showAlert('error', err.message || 'Terjadi kesalahan saat menyimpan');
                            });
                    });
                }

                // Auto submit filter
                const tahunSelect = document.getElementById('tahun');
                if (tahunSelect) {
                    tahunSelect.addEventListener('change', () => {
                        const filterForm = document.getElementById('filterForm');
                        if (filterForm) {
                            filterForm.submit();
                        }
                    });
                }
            });

            // Migrasi saldo cuti
            window.migrateSaldo = function() {
                const tahun = parseInt('{{ $tahun }}', 10);
                if (!confirm('Proses migrasi saldo cuti akan:\n' +
                        '1. Memindahkan saldo sisa tahun ' + (tahun - 1) + ' ke "Tahun Lalu" tahun ' + tahun + '\n' +
                        '2. Memberikan jatah 12 hari di "Tahun Ini" untuk karyawan tetap/permanen\n\n' +
                        'Lanjutkan?')) {
                    return;
                }

                const btn = document.getElementById('btnMigrate');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';

                const formData = new FormData();
                formData.append('tahun', tahun);

                fetch('/saldo-cuti/migrate', {
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
                            // Replace newline dengan <br> untuk alert
                            const message = String(data.message || '').replace(/\n/g, '<br>');
                            window.showAlert('success', message);

                            // Hide tombol migrasi setelah berhasil
                            btn.style.display = 'none';

                            // Reload setelah 2 detik
                            setTimeout(() => location.reload(), 2000);
                        } else {
                            const message = String(data.message || '').replace(/\n/g, '<br>');
                            window.showAlert('error', message);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        window.showAlert('error', err.message || 'Terjadi kesalahan saat migrasi saldo');
                    });
            };

            // Pastikan semua fungsi tersedia
            console.log('Saldo Cuti script loaded. Functions available:', {
                showAlert: typeof window.showAlert,
                showDetailCuti: typeof window.showDetailCuti,
                editSaldo: typeof window.editSaldo,
                migrateSaldo: typeof window.migrateSaldo
            });
        } catch (error) {
            console.error('Error loading Saldo Cuti script:', error);
            alert('Terjadi kesalahan saat memuat script. Silakan refresh halaman.');
        }
    })();
</script>
@endpush