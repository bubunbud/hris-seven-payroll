@extends('layouts.app')

@section('title', 'Input/Edit Absensi Karyawan - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-edit me-2"></i>Input/Edit Absensi Karyawan Per Periode
                </h2>
                <div>
                    <a href="{{ route('edit-absensi.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>Tambah Data Absensi
                    </a>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('edit-absensi.index') }}" id="filterForm">
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
                    </form>
                </div>
            </div>

            <!-- Alert Messages -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <!-- Data Count -->
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Jumlah Data {{ number_format($absens->total()) }}.</strong>
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
                                    <th width="8%">Status</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($absens as $item)
                                @php
                                $dtTanggal = $item['dtTanggal'] ?? null;
                                $vcNik = $item['vcNik'] ?? '';
                                $Nama = $item['Nama'] ?? 'N/A';
                                $vcNamaDivisi = $item['vcNamaDivisi'] ?? 'N/A';
                                $vcNamaBagian = $item['vcNamaBagian'] ?? 'N/A';
                                $dtJamMasuk = $item['dtJamMasuk'] ?? null;
                                $dtJamKeluar = $item['dtJamKeluar'] ?? null;
                                $total_jam = $item['total_jam'] ?? 0;
                                $status = $item['status'] ?? '';

                                // Tentukan badge class berdasarkan status
                                $badgeClass = '';
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
                                        <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('edit-absensi.edit', ['tanggal' => $dtTanggal, 'nik' => $vcNik]) }}" 
                                               class="btn btn-sm btn-warning" 
                                               title="Edit Absensi">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </a>
                                            @php
                                                $user = auth()->user();
                                                $canDelete = false;
                                                if ($user) {
                                                    $allowedRoles = ['superadmin', 'admin', 'Superadmin', 'Administrator'];
                                                    $canDelete = $user->hasAnyRole($allowedRoles);
                                                }
                                            @endphp
                                            @if($canDelete)
                                            <form action="{{ route('edit-absensi.destroy') }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus data absensi untuk {{ $vcNik }} pada tanggal {{ $dtTanggal ? \Carbon\Carbon::parse($dtTanggal)->format('d/m/Y') : '-' }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="tanggal" value="{{ $dtTanggal }}">
                                                <input type="hidden" name="nik" value="{{ $vcNik }}">
                                                <button type="submit" 
                                                        class="btn btn-sm btn-danger" 
                                                        title="Hapus Absensi">
                                                    <i class="fas fa-trash me-1"></i>Hapus
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
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
    const karyawanList = @json($karyawanList ?? []);

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
                item.scrollIntoView({
                    block: 'nearest'
                });
            } else {
                item.classList.remove('active');
            }
        });
    }

    // Auto-submit form on date change
    document.getElementById('dari_tanggal').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });

    document.getElementById('sampai_tanggal').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });

    // Auto-submit form on group change
    document.getElementById('group').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
</script>
@endpush




