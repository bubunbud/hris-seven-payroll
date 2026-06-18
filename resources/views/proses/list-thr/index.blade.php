@extends('layouts.app')

@section('title', 'List THR')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-list me-2"></i>List THR
                </h2>
            </div>

            <!-- Filter Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('list-thr.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="tahun" class="form-label">Tahun</label>
                                <select class="form-select" id="tahun" name="tahun">
                                    <option value="">Semua Tahun</option>
                                    @foreach($years as $yearOption)
                                    <option value="{{ $yearOption }}" {{ $tahun == $yearOption ? 'selected' : '' }}>
                                        {{ $yearOption }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="agama" class="form-label">Agama</label>
                                <select class="form-select" id="agama" name="agama">
                                    <option value="Semua Agama" {{ $agama == 'Semua Agama' || !$agama ? 'selected' : '' }}>Semua Agama</option>
                                    @foreach($agamas as $agamaOption)
                                    <option value="{{ $agamaOption }}" {{ $agama == $agamaOption ? 'selected' : '' }}>
                                        {{ $agamaOption }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="divisi" class="form-label">Divisi</label>
                                <select class="form-select" id="divisi" name="divisi">
                                    <option value="SEMUA" {{ $divisi == 'SEMUA' || !$divisi ? 'selected' : '' }}>Semua Divisi</option>
                                    @foreach($divisis as $divisiOption)
                                    <option value="{{ $divisiOption->vcKodeDivisi }}" {{ $divisi == $divisiOption->vcKodeDivisi ? 'selected' : '' }}>
                                        {{ $divisiOption->vcKodeDivisi }} - {{ $divisiOption->vcNamaDivisi }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="group_pegawai" class="form-label">Group Pegawai</label>
                                <select class="form-select" id="group_pegawai" name="group_pegawai">
                                    <option value="Semua Group" {{ $groupPegawai == 'Semua Group' ? 'selected' : '' }}>Semua Group</option>
                                    @foreach($groups as $groupOption)
                                    <option value="{{ $groupOption }}" {{ $groupPegawai == $groupOption ? 'selected' : '' }}>
                                        {{ $groupOption }}
                                    </option>
                                    @endforeach
                                </select>
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
                                <small class="text-muted">Ketik NIK atau nama karyawan untuk mencari</small>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-1"></i>Cari
                                    </button>
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
                    <strong>Jumlah Data: {{ number_format($totalData) }}</strong>
                </div>
            </div>

            <!-- Data Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover table-striped">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="8%">Tanggal THR</th>
                                    <th width="6%">NIK</th>
                                    <th width="12%">Nama</th>
                                    <th width="8%">Agama</th>
                                    <th width="8%">Group</th>
                                    <th width="8%">Divisi</th>
                                    <th width="8%">Golongan</th>
                                    <th width="10%" class="text-end">Gaji Pokok</th>
                                    <th width="8%">Tgl Masuk</th>
                                    <th width="12%">Masa Kerja</th>
                                    <th width="6%" class="text-center">x Gaji</th>
                                    <th width="10%" class="text-end">Nilai THR</th>
                                    <th width="10%">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($thrData as $thr)
                                <tr>
                                    <td>{{ $thr->dtTanggalTHR ? $thr->dtTanggalTHR->format('d/m/Y') : '-' }}</td>
                                    <td><strong>{{ $thr->vcNik }}</strong></td>
                                    <td>{{ $thr->karyawan->Nama ?? 'N/A' }}</td>
                                    <td>{{ $thr->vcAgama }}</td>
                                    <td>
                                        <span class="badge bg-{{ $thr->vcGroupPegawai == 'Operator' ? 'primary' : 'info' }}">
                                            {{ $thr->vcGroupPegawai }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $thr->vcKodeDivisi }}</strong><br>
                                        <small class="text-muted">{{ $thr->divisi->vcNamaDivisi ?? 'N/A' }}</small>
                                    </td>
                                    <td>{{ $thr->vcGolongan ?? '-' }}</td>
                                    <td class="text-end">
                                        @if($thr->decGajiPokok !== null)
                                            {{ number_format($thr->decGajiPokok, 0, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $thr->dtTanggalMasuk ? $thr->dtTanggalMasuk->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        <small>{{ $thr->vcMasaKerja }}</small><br>
                                        <small class="text-muted">
                                            ({{ $thr->intMasaKerjaHari }} hari / 
                                            {{ number_format($thr->decMasaKerjaBulan, 2) }} bln / 
                                            {{ number_format($thr->decMasaKerjaTahun, 2) }} thn)
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ number_format($thr->decXGaji, 2) }}</span>
                                    </td>
                                    <td class="text-end">
                                        @if($thr->decNilaiTHR !== null)
                                            <strong>{{ number_format($thr->decNilaiTHR, 0, ',', '.') }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $thr->vcKeterangan ?? '-' }}</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="13" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2 d-block"></i>
                                        <span class="text-muted">Tidak ada data THR yang ditemukan</span>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Autocomplete untuk search NIK/Nama (mirip dengan Browse Absensi)
    const searchInput = document.getElementById('search');
    const autocompleteDiv = document.getElementById('searchAutocomplete');
    const karyawanList = @json($karyawanList);
    
    let selectedIndex = -1;
    let filteredList = [];

    if (searchInput && autocompleteDiv) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            
            if (query.length === 0) {
                autocompleteDiv.style.display = 'none';
                filteredList = [];
                return;
            }

            // Filter karyawan list
            filteredList = karyawanList.filter(k => {
                return k.search.includes(query);
            }).slice(0, 10); // Limit to 10 results

            if (filteredList.length > 0) {
                autocompleteDiv.innerHTML = filteredList.map((k, index) => {
                    const isSelected = index === selectedIndex ? 'autocomplete-item-selected' : '';
                    return `
                        <div class="autocomplete-item ${isSelected}" data-index="${index}" data-nik="${k.nik}" data-nama="${k.nama}">
                            <strong>${k.nik}</strong> - ${k.nama}
                            <br><small class="text-muted">${k.divisi}</small>
                        </div>
                    `;
                }).join('');
                autocompleteDiv.style.display = 'block';
            } else {
                autocompleteDiv.style.display = 'none';
            }

            selectedIndex = -1;
        });

        searchInput.addEventListener('keydown', function(e) {
            if (!autocompleteDiv.style.display || autocompleteDiv.style.display === 'none') {
                return;
            }

            const items = autocompleteDiv.querySelectorAll('.autocomplete-item');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                updateSelection();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, -1);
                updateSelection();
            } else if (e.key === 'Enter' && selectedIndex >= 0) {
                e.preventDefault();
                selectItem(filteredList[selectedIndex]);
            } else if (e.key === 'Escape') {
                autocompleteDiv.style.display = 'none';
            }
        });

        autocompleteDiv.addEventListener('click', function(e) {
            const item = e.target.closest('.autocomplete-item');
            if (item) {
                const index = parseInt(item.dataset.index);
                selectItem(filteredList[index]);
            }
        });

        function updateSelection() {
            const items = autocompleteDiv.querySelectorAll('.autocomplete-item');
            items.forEach((item, index) => {
                if (index === selectedIndex) {
                    item.classList.add('autocomplete-item-selected');
                    item.scrollIntoView({ block: 'nearest' });
                } else {
                    item.classList.remove('autocomplete-item-selected');
                }
            });
        }

        function selectItem(karyawan) {
            // Format: "NIK - Nama"
            const currentValue = searchInput.value.trim();
            const searchTerms = currentValue ? currentValue.split(',').map(s => s.trim()) : [];
            
            // Cek apakah sudah ada
            const exists = searchTerms.some(term => {
                const parts = term.split(' - ');
                return parts[0] === karyawan.nik;
            });

            if (!exists) {
                searchTerms.push(`${karyawan.nik} - ${karyawan.nama}`);
                searchInput.value = searchTerms.join(', ');
            }

            autocompleteDiv.style.display = 'none';
            searchInput.focus();
        }

        // Hide autocomplete when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !autocompleteDiv.contains(e.target)) {
                autocompleteDiv.style.display = 'none';
            }
        });
    }
});
</script>

<style>
.autocomplete-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.autocomplete-item {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
}

.autocomplete-item:hover,
.autocomplete-item-selected {
    background-color: #f8f9fa;
}

.autocomplete-item:last-child {
    border-bottom: none;
}
</style>
@endsection















