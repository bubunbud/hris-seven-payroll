@extends('layouts.app')

@section('title', 'Browse Absensi Karyawan - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-calendar-check me-2"></i>Browse Absensi Karyawan Per Periode
                </h2>
            </div>

            <!-- Filter Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('absen.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="dari_tanggal" class="form-label">Dari Tanggal</label>
                                <div class="input-group">
                                    <input type="date" class="form-control" id="dari_tanggal" name="dari_tanggal"
                                        value="{{ $startDate }}">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="sampai_tanggal" class="form-label">Sampai Tanggal</label>
                                <div class="input-group">
                                    <input type="date" class="form-control" id="sampai_tanggal" name="sampai_tanggal"
                                        value="{{ $endDate }}">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
                            <div class="col-md-4">
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
                            <div class="col-md-2">
                                <label for="group" class="form-label">Group</label>
                                <select class="form-select" id="group" name="group">
                                    <option value="Semua Group" {{ $group == 'Semua Group' ? 'selected' : '' }}>Semua Group</option>
                                    @foreach($groups as $groupOption)
                                    <option value="{{ $groupOption }}" {{ $group == $groupOption ? 'selected' : '' }}>
                                        {{ $groupOption }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary w-100 shadow-sm px-4">
                                        <i class="fas fa-eye me-2"></i>Preview
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-12">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="tidak_masuk" name="tidak_masuk"
                                        value="1" {{ $tidakMasuk ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tidak_masuk">
                                        Tidak Masuk
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="absen_tidak_lengkap" name="absen_tidak_lengkap"
                                        value="1" {{ $absenTidakLengkap ? 'checked' : '' }}>
                                    <label class="form-check-label" for="absen_tidak_lengkap">
                                        Absen Tidak Lengkap
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="hari_kerja_normal" name="hari_kerja_normal"
                                        value="1" {{ $hariKerjaNormal ? 'checked' : '' }}>
                                    <label class="form-check-label" for="hari_kerja_normal">
                                        Hari Kerja Normal (HKN)
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="kerja_hari_libur" name="kerja_hari_libur"
                                        value="1" {{ $kerjaHariLibur ? 'checked' : '' }}>
                                    <label class="form-check-label" for="kerja_hari_libur">
                                        Kerja hari Libur (KHL)
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="telat" name="telat"
                                        value="1" {{ $telat ? 'checked' : '' }}>
                                    <label class="form-check-label" for="telat">
                                        Telat
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Data Count -->
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Jumlah Data {{ number_format($totalData) }}.</strong>
                </div>
                @if($totalData > 0)
                <div>
                    <a href="{{ route('absen.print', request()->query()) }}" 
                       class="btn btn-success btn-sm" 
                       target="_blank">
                        <i class="fas fa-print me-2"></i>Cetak
                    </a>
                </div>
                @endif
            </div>

            <!-- Data Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover table-striped" id="absenTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="8%">Tanggal</th>
                                    <th width="7%">NIK</th>
                                    <th width="15%">Nama</th>
                                    <th width="12%">Divisi</th>
                                    <th width="12%">Bagian</th>
                                    <th width="8%">Jam Masuk</th>
                                    <th width="8%">Jam Pulang</th>
                                    <th width="7%">Total Jam</th>
                                    <th width="8%">Shift Terjadwal</th>
                                    <th width="8%">Shift Aktual</th>
                                    <th width="7%">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($absens as $item)
                                @php
                                // Handle data dari absen atau tidak masuk (sekarang semua array)
                                $dtTanggal = $item['dtTanggal'] ?? null;
                                $vcNik = $item['vcNik'] ?? '';
                                $Nama = $item['Nama'] ?? 'N/A';
                                $vcNamaDivisi = $item['vcNamaDivisi'] ?? 'N/A';
                                $vcNamaBagian = $item['vcNamaBagian'] ?? 'N/A';
                                $dtJamMasuk = $item['dtJamMasuk'] ?? null;
                                $dtJamKeluar = $item['dtJamKeluar'] ?? null;
                                $dtJamMasukLembur = $item['dtJamMasukLembur'] ?? null;
                                $total_jam = $item['total_jam'] ?? 0;
                                $source = $item['source'] ?? 'absen';
                                $shift_masuk = $item['shift_masuk'] ?? null;
                                $Group_pegawai = $item['Group_pegawai'] ?? null;

                                // Data shift untuk Security
                                $shift_terjadwal = $item['shift_terjadwal'] ?? [];
                                $shift_aktual = $item['shift_aktual'] ?? null;
                                $status_validasi = $item['status_validasi'] ?? null;

                                // Data khusus tidak masuk
                                $vcKodeAbsen = $item['vcKodeAbsen'] ?? null;
                                $jenis_absen_keterangan = $item['jenis_absen_keterangan'] ?? null;
                                $vcKeterangan = $item['vcKeterangan'] ?? null;

                                // Gunakan status yang sudah dihitung di controller (sudah mempertimbangkan tukar hari kerja)
                                // Jika status belum ada (untuk backward compatibility), hitung ulang dengan logika sederhana
                                $status = $item['status'] ?? '';
                                $badgeClass = '';

                                // Jika status belum ada, hitung ulang (fallback untuk backward compatibility)
                                if (empty($status) && $source === 'absen') {
                                    $tanggalObj = \Carbon\Carbon::parse($dtTanggal);
                                    
                                    // Hitung total jam jika ada data
                                    if ($dtJamMasuk && $dtJamKeluar) {
                                        $masuk = $tanggalObj->copy()->setTimeFromTimeString((string) $dtJamMasuk);
                                        $keluar = $tanggalObj->copy()->setTimeFromTimeString((string) $dtJamKeluar);
                                        if ($keluar->lessThan($masuk)) {
                                            $keluar->addDay();
                                        }
                                        $total_jam = round($masuk->diffInHours($keluar, true), 1);
                                    } else {
                                        $total_jam = $total_jam ?? 0;
                                    }

                                    // Note: Untuk penentuan status yang akurat dengan tukar hari kerja,
                                    // sebaiknya gunakan status yang sudah dihitung di controller
                                    // Logika di bawah ini hanya fallback dan tidak mempertimbangkan tukar hari kerja
                                    $isWeekend = in_array($tanggalObj->dayOfWeek, [0, 6]);
                                    $isHoliday = in_array($dtTanggal, $hariLiburList);
                                    $isHariLibur = $isWeekend || $isHoliday;

                                    // Cek telat: jam masuk > jam shift masuk
                                    $isTelat = false;
                                    if ($dtJamMasuk && $shift_masuk && $source === 'absen' && !$isHariLibur) {
                                        try {
                                            $jamMasuk = substr((string) $dtJamMasuk, 0, 5);
                                            $shiftMasuk = $shift_masuk instanceof \Carbon\Carbon
                                                ? $shift_masuk->format('H:i')
                                                : substr((string) $shift_masuk, 0, 5);

                                            $tMasuk = $tanggalObj->copy()->setTimeFromTimeString($jamMasuk);
                                            $tShiftMasuk = $tanggalObj->copy()->setTimeFromTimeString($shiftMasuk);

                                            if ($tMasuk->greaterThan($tShiftMasuk)) {
                                                $isTelat = true;
                                            }
                                        } catch (\Exception $e) {
                                            // Skip jika ada error parsing waktu
                                        }
                                    }

                                    // Tentukan status (fallback logic)
                                    if (!$dtJamMasuk && !$dtJamKeluar) {
                                        $status = 'Tidak Masuk';
                                        $badgeClass = 'bg-danger';
                                    } elseif ($isTelat) {
                                        $status = 'Telat';
                                        $badgeClass = 'bg-warning text-dark';
                                    } elseif (($dtJamMasuk && !$dtJamKeluar) || (!$dtJamMasuk && $dtJamKeluar)) {
                                        $status = 'ATL';
                                        $badgeClass = 'bg-warning text-dark';
                                    } elseif ($isHariLibur && ($dtJamMasuk || $dtJamKeluar || $dtJamMasukLembur)) {
                                        $status = 'KHL';
                                        $badgeClass = 'bg-info';
                                    } elseif ($dtJamMasuk && $dtJamKeluar && $total_jam >= 8) {
                                        $status = 'HKN';
                                        $badgeClass = 'bg-success';
                                    } elseif ($dtJamMasuk && $dtJamKeluar && $total_jam > 0 && $total_jam < 8) {
                                        $status = 'HC';
                                        $badgeClass = 'bg-warning text-dark';
                                    } else {
                                        $status = 'ATL';
                                        $badgeClass = 'bg-warning text-dark';
                                    }
                                }

                                // Tentukan badge class berdasarkan status (jika belum di-set)
                                if (empty($badgeClass)) {
                                    switch ($status) {
                                        case 'Tidak Masuk':
                                            $badgeClass = 'bg-danger';
                                            break;
                                        case 'Telat':
                                            $badgeClass = 'bg-warning text-dark';
                                            break;
                                        case 'ATL':
                                            $badgeClass = 'bg-warning text-dark';
                                            break;
                                        case 'KHL':
                                            $badgeClass = 'bg-info';
                                            break;
                                        case 'HKN':
                                            $badgeClass = 'bg-success';
                                            break;
                                        case 'HC':
                                            $badgeClass = 'bg-warning text-dark';
                                            break;
                                        default:
                                            $badgeClass = 'bg-secondary';
                                            break;
                                    }
                                }
                                    @endphp
                                    <tr>
                                    <td>
                                        <i class="fas fa-calendar text-primary me-1"></i>
                                        {{ $dtTanggal ? \Carbon\Carbon::parse($dtTanggal)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>
                                        <strong>{{ $vcNik }}</strong>
                                    </td>
                                    <td>
                                        <i class="fas fa-user text-info me-1"></i>
                                        {{ $Nama }}
                                    </td>
                                    <td>
                                        <i class="fas fa-building text-secondary me-1"></i>
                                        {{ $vcNamaDivisi }}
                                    </td>
                                    <td>
                                        <i class="fas fa-sitemap text-warning me-1"></i>
                                        {{ $vcNamaBagian }}
                                    </td>
                                    <td>
                                        @if($dtJamMasuk)
                                        <i class="fas fa-sign-in-alt text-success me-1"></i>
                                        {{ \Carbon\Carbon::parse($dtJamMasuk)->format('H:i') }}
                                        @elseif($source === 'tidak_masuk')
                                        <span class="text-muted"><small>{{ $jenis_absen_keterangan ?? $vcKodeAbsen ?? '-' }}</small></span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($dtJamKeluar)
                                        <i class="fas fa-sign-out-alt text-danger me-1"></i>
                                        {{ \Carbon\Carbon::parse($dtJamKeluar)->format('H:i') }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($total_jam > 0)
                                        <span class="badge bg-info">{{ $total_jam }} jam</span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($Group_pegawai === 'Security' && !empty($shift_terjadwal))
                                        @foreach($shift_terjadwal as $shift)
                                        <span class="badge bg-primary me-1">S{{ $shift }}</span>
                                        @endforeach
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($Group_pegawai === 'Security' && $shift_aktual)
                                        <span class="badge bg-info">S{{ $shift_aktual }}</span>
                                        @if($status_validasi === 'tidak_sesuai')
                                        <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Tidak sesuai</small>
                                        @elseif($status_validasi === 'sesuai')
                                        <br><small class="text-success"><i class="fas fa-check"></i> Sesuai</small>
                                        @elseif($status_validasi === 'tidak_masuk')
                                        <br><small class="text-warning"><i class="fas fa-times"></i> Tidak masuk</small>
                                        @endif
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                                        @if($source === 'tidak_masuk' && $vcKeterangan)
                                        <br><small class="text-muted">{{ strlen($vcKeterangan) > 20 ? substr($vcKeterangan, 0, 20) . '...' : $vcKeterangan }}</small>
                                        @endif
                                    </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-4">
                                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">Tidak ada data absensi untuk periode ini</p>
                                        </td>
                                    </tr>
                                    @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($absens->hasPages())
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
                        <div class="text-muted small">
                            Menampilkan {{ $absens->firstItem() }} sampai {{ $absens->lastItem() }} dari {{ $absens->total() }} data
                        </div>
                        <nav aria-label="Navigasi halaman">
                            {{ $absens->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Legend -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-info-circle me-2"></i>Keterangan Status
                    </h6>
                    <div class="row">
                        <div class="col-md-3">
                            <span class="badge bg-success me-2">HKN</span>
                            <span class="text-muted">Hari Kerja Normal</span>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-info me-2">KHL</span>
                            <span class="text-muted">Kerja Hari Libur</span>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-warning text-dark me-2">Telat</span>
                            <span class="text-muted">Jam masuk > jam shift masuk</span>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-warning text-dark me-2">ATL</span>
                            <span class="text-muted">Absen Tidak Lengkap</span>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-warning text-dark me-2">HC</span>
                            <span class="text-muted">Jam kerja kurang dari 8 jam</span>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-danger me-2">Tidak Masuk</span>
                            <span class="text-muted">Tidak ada absensi</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

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
</style>
@endpush

@push('scripts')
<script>
    let searchTimeout;
    let selectedIndex = -1;
    const searchInput = document.getElementById('search');
    const autocompleteDiv = document.getElementById('searchAutocomplete');
    // Data karyawan untuk pencarian lokal (dibatasi di controller)
    const karyawanList = @json($karyawanList);

    // Fungsi untuk mendapatkan nilai NIK dari input (handle format "NIK - Nama" atau multiple dengan koma)
    function getCurrentSearchTerms() {
        const value = searchInput.value.trim();
        if (!value) return [];
        return value.split(',').map(term => term.trim()).filter(term => term.length > 0);
    }

    // Fungsi untuk mendapatkan term yang sedang diketik (term terakhir)
    function getCurrentTypingTerm() {
        const value = searchInput.value.trim();
        if (!value) return '';
        const terms = value.split(',');
        return terms[terms.length - 1].trim();
    }

    // Autocomplete search (pencarian lokal, tanpa fetch)
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

        // Debounce 200ms
        searchTimeout = setTimeout(() => {
            const results = karyawanList.filter(k => k.search.includes(currentTerm)).slice(0, 20);
            displayAutocomplete(results);
        }, 200);
    });

    // Display autocomplete results
    function displayAutocomplete(karyawans) {
        if (!karyawans || karyawans.length === 0) {
            autocompleteDiv.innerHTML = '<div class="autocomplete-item">Tidak ada karyawan ditemukan</div>';
            autocompleteDiv.style.display = 'block';
            return;
        }

        autocompleteDiv.innerHTML = '';
        karyawans.forEach((karyawan, index) => {
            if (!karyawan || !karyawan.nik) return; // Skip invalid data
            
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

    // Select karyawan from autocomplete
    function selectKaryawan(karyawan) {
        const currentTerms = getCurrentSearchTerms();
        const currentTerm = getCurrentTypingTerm();
        
        // Hapus term terakhir yang sedang diketik
        currentTerms.pop();
        
        // Tambahkan karyawan yang dipilih
        const newTerm = `${karyawan.nik} - ${karyawan.nama}`;
        currentTerms.push(newTerm);
        
        // Update input value
        searchInput.value = currentTerms.join(', ');
        autocompleteDiv.style.display = 'none';
        selectedIndex = -1;
        
        // Focus kembali ke input
        searchInput.focus();
    }

    // Hide autocomplete when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !autocompleteDiv.contains(e.target)) {
            autocompleteDiv.style.display = 'none';
            selectedIndex = -1;
        }
    });

    // Handle keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        const items = autocompleteDiv.querySelectorAll('.autocomplete-item');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (autocompleteDiv.style.display === 'none') return;
            selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
            updateSelectedItem(items, selectedIndex);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (autocompleteDiv.style.display === 'none') return;
            selectedIndex = Math.max(selectedIndex - 1, -1);
            updateSelectedItem(items, selectedIndex);
        } else if (e.key === 'Enter' && selectedIndex >= 0 && items[selectedIndex]) {
            e.preventDefault();
            items[selectedIndex].click();
        } else if (e.key === 'Escape') {
            autocompleteDiv.style.display = 'none';
            selectedIndex = -1;
        } else if (e.key === 'Tab') {
            autocompleteDiv.style.display = 'none';
            selectedIndex = -1;
        }
    });

    function updateSelectedItem(items, index) {
        items.forEach((item, i) => {
            if (i === index) {
                item.classList.add('active');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('active');
            }
        });
    }
</script>
<script>
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        document.querySelectorAll('.alert').forEach(alert => alert.remove());
        const container = document.querySelector('.container-fluid');
        container.insertAdjacentHTML('afterbegin', alertHtml);

        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }

    // Auto-submit form on date change
    document.getElementById('dari_tanggal').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });

    document.getElementById('sampai_tanggal').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });

    // Auto-submit form on checkbox change
    document.getElementById('tidak_masuk').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });

    document.getElementById('absen_tidak_lengkap').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });

    // Auto-submit form on group change
    document.getElementById('group').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
</script>
@endpush