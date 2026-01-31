@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-list-alt me-2"></i>View Rekap Gaji</h2>
    </div>

    <!-- Filter Card -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('view-gaji.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label for="periode_dari" class="form-label">Periode Dari</label>
                        <input type="date" class="form-control" id="periode_dari" name="periode_dari" value="{{ request('periode_dari') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="periode_sampai" class="form-label">Periode Sampai</label>
                        <input type="date" class="form-control" id="periode_sampai" name="periode_sampai" value="{{ request('periode_sampai') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="divisi" class="form-label">Divisi</label>
                        <select class="form-select" id="divisi" name="divisi">
                            <option value="SEMUA">SEMUA</option>
                            @foreach($divisis as $divisi)
                            <option value="{{ $divisi->vcKodeDivisi }}" {{ request('divisi') == $divisi->vcKodeDivisi ? 'selected' : '' }}>
                                {{ $divisi->vcNamaDivisi }} ({{ $divisi->vcKodeDivisi }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="search" class="form-label">NIK / Nama</label>
                        <div class="d-flex gap-2 align-items-start">
                            <div class="position-relative flex-grow-1">
                                <input type="text" 
                                       class="form-control" 
                                       id="search" 
                                       name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Cari NIK atau Nama (pisahkan dengan koma)" 
                                       autocomplete="off"
                                       style="height: 38px;">
                                <div id="searchAutocomplete" class="autocomplete-dropdown" style="display: none;"></div>
                            </div>
                            <button type="submit" class="btn btn-primary shadow-sm" style="height: 38px;">
                                <i class="fas fa-eye me-2"></i>Preview
                            </button>
                        </div>
                        <small class="text-muted">Ketik NIK atau nama karyawan untuk mencari (bisa multiple, pisahkan dengan koma)</small>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive" style="max-height:600px; overflow-y:auto;">
                <table class="table table-hover">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th width="6%">Periode Gajian</th>
                            <th width="6%">Periode Awal</th>
                            <th width="6%">Periode Akhir</th>
                            <th width="3%">Closing</th>
                            <th width="4%">NIK</th>
                            <th width="11%">Nama</th>
                            <th width="8%">Divisi</th>
                            <th width="8%" class="text-center">Hari Kerja</th>
                            <th width="10%" class="text-center">Absensi</th>
                            <th width="10%" class="text-center">Lembur</th>
                            <th width="11%" class="text-end">Penerimaan</th>
                            <th width="10%" class="text-end">Potongan</th>
                            <th width="41%" class="text-end">Gaji Bersih</th>
                            <th width="5%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recordsWithPrevious ?? [] as $item)
                        @php
                        $record = $item['record'];
                        $periodeSebelumnya = $item['periode_sebelumnya'] ?? null;
                        $totalPenerimaan = $record->decGapok + $record->decUangMakan + $record->decTransport +
                        $record->decPremi + $record->decTotallembur1 + $record->decTotallembur2 +
                        $record->decTotallembur3 + $record->decRapel;
                        $totalPotongan = $record->decPotonganBPJSKes + $record->decPotonganBPJSJHT +
                        $record->decPotonganBPJSJP + $record->decIuranSPN +
                        $record->decPotonganKoperasi + $record->decPotonganBPR +
                        $record->decPotonganHC + $record->decPotonganAbsen + $record->decPotonganLain;
                        $gajiBersih = $totalPenerimaan - $totalPotongan;
                        $totalJamLembur = $record->decJamLemburKerja + $record->decJamLemburLibur;
                        @endphp
                        <tr>
                            <td style="font-size: 0.75rem;"><strong>{{ $record->periode->format('d/m/Y') }}</strong></td>
                            <td style="font-size: 0.75rem;">{{ $record->vcPeriodeAwal->format('d/m/Y') }}</td>
                            <td style="font-size: 0.75rem;">{{ $record->vcPeriodeAkhir->format('d/m/Y') }}</td>
                            <td class="text-center" style="font-size: 0.75rem;">
                                <span class="badge bg-primary">{{ $record->vcClosingKe }}</span>
                            </td>
                            <td style="font-size: 0.75rem;"><strong>{{ $record->vcNik }}</strong></td>
                            <td style="font-size: 0.75rem;">{{ $record->karyawan->Nama ?? 'N/A' }}</td>
                            <td>
                                <small>{{ $record->divisi->vcNamaDivisi ?? 'N/A' }}</small>
                                <br><strong class="text-muted">{{ $record->vcKodeDivisi }}</strong>
                            </td>
                            <td class="text-center" style="font-size: 0.75rem;">
                                <div>
                                    <strong>{{ $record->jumlahHari }}</strong> hari
                                </div>
                                <small class="text-muted">Hadir: {{ $record->intHadir }}</small>
                                @if($record->intKHL > 0)
                                <div class="mt-1">
                                    <small class="text-info">KHL: {{ $record->intKHL }} hari</small>
                                </div>
                                @endif
                            </td>
                            <td class="text-center" style="font-size: 0.7rem;">
                                <div class="row g-0">
                                    <div class="col-6 border-end pe-1" style="border-right: 1px solid #dee2e6;">
                                        <div class="fw-bold mb-1" style="font-size: 0.7rem;">P1</div>
                                        @if($record->vcClosingKe == '1')
                                        {{-- Closing ke-1 = Periode 1, tampilkan data periode ini --}}
                                        <div>S: {{ $record->intJmlSakit }}</div>
                                        <div>I: {{ $record->intJmlIzin }}</div>
                                        <div>A: {{ $record->intJmlAlpha }}</div>
                                        <div>T: {{ $record->intJmlTelat }}</div>
                                        <div>C: {{ $record->intJmlCuti }}</div>
                                        <div>HC: {{ $record->intHC }}</div>
                                        @elseif($record->vcClosingKe == '2')
                                        {{-- Closing ke-2, ambil data P1 dari field int*Lalu --}}
                                        <div>S: {{ $record->intSakitLalu ?? 0 }}</div>
                                        <div>I: {{ $record->intIzinLalu ?? 0 }}</div>
                                        <div>A: {{ $record->intAlphaLalu ?? 0 }}</div>
                                        <div>T: {{ $record->intTelatLalu ?? 0 }}</div>
                                        <div>C: {{ $record->intCutiLalu ?? 0 }}</div>
                                        <div>HC: {{ $record->intHcLalu ?? 0 }}</div>
                                        @else
                                        <div class="text-muted">-</div>
                                        @endif
                                    </div>
                                    <div class="col-6 ps-1">
                                        <div class="fw-bold mb-1" style="font-size: 0.7rem;">P2</div>
                                        @if($record->vcClosingKe == '2')
                                        {{-- Closing ke-2 = Periode 2 --}}
                                        <div>S: {{ $record->intJmlSakit }}</div>
                                        <div>I: {{ $record->intJmlIzin }}</div>
                                        <div>A: {{ $record->intJmlAlpha }}</div>
                                        <div>T: {{ $record->intJmlTelat }}</div>
                                        <div>C: {{ $record->intJmlCuti }}</div>
                                        <div>HC: {{ $record->intHC }}</div>
                                        @else
                                        {{-- Closing ke-1, periode 2 belum ada --}}
                                        <div class="text-muted">-</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-center" style="font-size: 0.75rem;">
                                <div>
                                    <strong>{{ number_format($totalJamLembur, 1) }}</strong> jam
                                </div>
                                <small class="text-muted">
                                    K: {{ number_format($record->decJamLemburKerja, 1) }} |
                                    L: {{ number_format($record->decJamLemburLibur, 1) }}
                                </small>
                                @if($record->decJamLemburKerja1 > 0 || $record->decJamLemburKerja2 > 0 || $record->decJamLemburKerja3 > 0 || $record->decJamLemburLibur2 > 0 || $record->decJamLemburLibur3 > 0)
                                <div class="mt-1">
                                    <small>
                                        J1: {{ number_format($record->decJamLemburKerja1, 1) }} |
                                        J2: {{ number_format($record->decJamLemburKerja2 + $record->decJamLemburLibur2, 1) }} |
                                        J3: {{ number_format($record->decJamLemburKerja3 + $record->decJamLemburLibur3, 1) }}
                                    </small>
                                </div>
                                @endif
                                <div class="mt-1">
                                    <small class="text-success">
                                        Rp. {{ number_format($record->decTotallembur1 + $record->decTotallembur2 + $record->decTotallembur3, 0, ',', '.') }}
                                    </small>
                                </div>
                            </td>
                            <td class="text-end" style="font-size: 0.75rem;">
                                <div style="font-size: 0.75rem; line-height: 1.4;">
                                    <div style="display: flex; justify-content: space-between; gap: 8px;">
                                        <span>Gapok:</span>
                                        <span>Rp. {{ number_format($record->decGapok, 0, ',', '.') }}</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; gap: 8px;">
                                        <span>Makan:</span>
                                        <span>Rp. {{ number_format($record->decUangMakan, 0, ',', '.') }}</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; gap: 8px;">
                                        <span>Transport:</span>
                                        <span>Rp. {{ number_format($record->decTransport, 0, ',', '.') }}</span>
                                    </div>
                                    @if($record->vcClosingKe == '2')
                                    <div style="display: flex; justify-content: space-between; gap: 8px;">
                                        <span>Premi:</span>
                                        <span>Rp. {{ number_format($record->decPremi ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                    @if($record->decTotallembur1 + $record->decTotallembur2 + $record->decTotallembur3 > 0)
                                    @if($record->decTotallembur1 > 0)
                                    <div style="display: flex; justify-content: space-between; gap: 8px; padding-left: 12px;">
                                        <span><small>Lembur J1:</small></span>
                                        <span style="margin-left: -35px;"><small>Rp. {{ number_format($record->decTotallembur1, 0, ',', '.') }}</small></span>
                                    </div>
                                    @endif
                                    @if($record->decTotallembur2 > 0)
                                    <div style="display: flex; justify-content: space-between; gap: 8px; padding-left: 12px;">
                                        <span><small>Lembur J2:</small></span>
                                        <span style="margin-left: -35px;"><small>Rp. {{ number_format($record->decTotallembur2, 0, ',', '.') }}</small></span>
                                    </div>
                                    @endif
                                    @if($record->decTotallembur3 > 0)
                                    <div style="display: flex; justify-content: space-between; gap: 8px; padding-left: 12px;">
                                        <span><small>Lembur J3:</small></span>
                                        <span style="margin-left: -35px;"><small>Rp. {{ number_format($record->decTotallembur3, 0, ',', '.') }}</small></span>
                                    </div>
                                    @endif
                                    <div style="display: flex; justify-content: space-between; gap: 8px; margin-top: 2px;">
                                        <span>Lembur Total:</span>
                                        <span>Rp. {{ number_format($record->decTotallembur1 + $record->decTotallembur2 + $record->decTotallembur3, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                    @if($record->decRapel > 0)
                                    <div style="display: flex; justify-content: space-between; gap: 8px;">
                                        <span>Rapel:</span>
                                        <span>Rp. {{ number_format($record->decRapel, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                </div>
                                <div class="mt-1 pt-1 border-top">
                                    <strong class="text-success" style="font-size: 0.75rem;">Rp. {{ number_format($totalPenerimaan, 0, ',', '.') }}</strong>
                                </div>
                            </td>
                            <td class="text-end" style="font-size: 0.75rem;">
                                <div style="font-size: 0.75rem; line-height: 1.4;">
                                    @if($record->decPotonganBPJSKes > 0)
                                    <div style="display: flex; justify-content: space-between; gap: 8px;">
                                        <span>BPJS Kes:</span>
                                        <span>Rp. {{ number_format($record->decPotonganBPJSKes, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                    @if($record->decPotonganBPJSJHT > 0)
                                    <div style="display: flex; justify-content: space-between; gap: 8px;">
                                        <span>BPJS JHT:</span>
                                        <span>Rp. {{ number_format($record->decPotonganBPJSJHT, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                    @if($record->decPotonganBPJSJP > 0)
                                    <div style="display: flex; justify-content: space-between; gap: 8px;">
                                        <span>BPJS JP:</span>
                                        <span>Rp. {{ number_format($record->decPotonganBPJSJP, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                    @if($record->decIuranSPN > 0)
                                    <div style="display: flex; justify-content: space-between; gap: 8px;">
                                        <span>SPN:</span>
                                        <span>Rp. {{ number_format($record->decIuranSPN, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                    @if($record->decPotonganKoperasi > 0)
                                    <div style="display: flex; justify-content: space-between; gap: 8px;">
                                        <span>Koperasi:</span>
                                        <span>Rp. {{ number_format($record->decPotonganKoperasi, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                    @if($record->decPotonganBPR > 0)
                                    <div style="display: flex; justify-content: space-between; gap: 8px;">
                                        <span>DPLK:</span>
                                        <span>Rp. {{ number_format($record->decPotonganBPR, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                    @if($record->decPotonganHC > 0)
                                    <div style="display: flex; justify-content: space-between; gap: 8px;">
                                        <span>HC:</span>
                                        <span>Rp. {{ number_format($record->decPotonganHC, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                    @if($record->decPotonganAbsen > 0)
                                    <div style="display: flex; justify-content: space-between; gap: 8px;">
                                        <span>Absen:</span>
                                        <span>Rp. {{ number_format($record->decPotonganAbsen, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                    @if($record->decPotonganLain > 0)
                                    <div style="display: flex; justify-content: space-between; gap: 8px;">
                                        <span>Lain:</span>
                                        <span>Rp. {{ number_format($record->decPotonganLain, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                </div>
                                <div class="mt-1 pt-1 border-top">
                                    <strong class="text-danger" style="font-size: 0.75rem;">Rp. {{ number_format($totalPotongan, 0, ',', '.') }}</strong>
                                </div>
                            </td>
                            <td class="text-end" style="font-size: 0.75rem;">
                                <strong class="text-primary">Rp. {{ number_format($gajiBersih, 0, ',', '.') }}</strong>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('closing.show', [
                                    $record->vcPeriodeAwal->format('Y-m-d'),
                                    $record->vcPeriodeAkhir->format('Y-m-d'),
                                    $record->vcNik,
                                    $record->periode->format('Y-m-d'),
                                    $record->vcClosingKe
                                ]) }}" class="btn btn-sm btn-outline-info" title="Detail Gaji">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" class="text-center py-4">
                                <i class="fas fa-inbox fa-2x text-muted mb-2 d-block"></i>
                                <span class="text-muted">Tidak ada data gaji yang ditemukan</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($records->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Menampilkan {{ $records->firstItem() }} sampai {{ $records->lastItem() }} dari {{ $records->total() }} record
                </div>
                <div>
                    {{ $records->links() }}
                </div>
            </div>
        </div>
        @endif
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
@endpush