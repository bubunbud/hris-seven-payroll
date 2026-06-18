@extends('layouts.app')

@section('title', 'Cetak Slip THR')

@section('content')
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
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .autocomplete-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
    }

    .autocomplete-item:hover,
    .autocomplete-item.selected {
        background-color: #f0f0f0;
    }

    .autocomplete-item strong {
        color: #007bff;
    }

    .autocomplete-item small {
        display: block;
        color: #6c757d;
        margin-top: 0.25rem;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-file-invoice me-2"></i>Cetak Slip THR
                </h2>
            </div>

            <!-- Form Filter -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Slip THR</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('slip-thr.preview') }}" id="formSlipThr">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="tahun" class="form-label">Tahun <span class="text-danger">*</span></label>
                                <select class="form-select" id="tahun" name="tahun" required>
                                    <option value="">Pilih Tahun</option>
                                    @foreach($years as $yearOption)
                                    <option value="{{ $yearOption }}" {{ $tahun == $yearOption ? 'selected' : '' }}>
                                        {{ $yearOption }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="divisi" class="form-label">Divisi</label>
                                <select class="form-select" id="divisi" name="divisi">
                                    <option value="SEMUA" {{ $divisi == 'SEMUA' ? 'selected' : '' }}>SEMUA DIVISI</option>
                                    @foreach($divisis as $divisiOption)
                                    <option value="{{ $divisiOption->vcKodeDivisi }}" {{ $divisi == $divisiOption->vcKodeDivisi ? 'selected' : '' }}>
                                        {{ $divisiOption->vcKodeDivisi }} - {{ $divisiOption->vcNamaDivisi }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="agama" class="form-label">Agama</label>
                                <select class="form-select" id="agama" name="agama">
                                    <option value="Semua Agama" {{ $agama == 'Semua Agama' ? 'selected' : '' }}>Semua Agama</option>
                                    @foreach($agamas as $agamaOption)
                                    <option value="{{ $agamaOption }}" {{ $agama == $agamaOption ? 'selected' : '' }}>
                                        {{ $agamaOption }}
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
                                <small class="text-muted">Ketik NIK atau nama karyawan untuk mencari (bisa multiple, pisahkan dengan koma)</small>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg me-3">
                                    <i class="fas fa-print me-2"></i>Cetak
                                </button>
                                <button type="button" class="btn btn-danger btn-lg" onclick="window.location.href='{{ route('dashboard') }}'">
                                    <i class="fas fa-times me-2"></i>Keluar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let searchTimeout;
    let selectedIndex = -1;
    const searchInput = document.getElementById('search');
    const autocompleteDiv = document.getElementById('searchAutocomplete');
    const karyawanList = @json($karyawanList);

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
            const results = karyawanList.filter(k => k.search.toLowerCase().includes(currentTerm)).slice(0, 20);
            displayAutocomplete(results);
        }, 200);
    });

    function displayAutocomplete(karyawans) {
        if (karyawans.length === 0) {
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
        if (!searchInput.contains(e.target) && !autocompleteDiv.contains(e.target)) {
            autocompleteDiv.style.display = 'none';
            selectedIndex = -1;
        }
    });

    searchInput.addEventListener('keydown', function(e) {
        const items = autocompleteDiv.querySelectorAll('.autocomplete-item');
        if (items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = (selectedIndex + 1) % items.length;
            items.forEach((item, idx) => {
                item.classList.toggle('selected', idx === selectedIndex);
            });
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = selectedIndex <= 0 ? items.length - 1 : selectedIndex - 1;
            items.forEach((item, idx) => {
                item.classList.toggle('selected', idx === selectedIndex);
            });
        } else if (e.key === 'Enter' && selectedIndex >= 0) {
            e.preventDefault();
            items[selectedIndex].click();
        }
    });

    document.getElementById('formSlipThr').addEventListener('submit', function(e) {
        const tahun = document.getElementById('tahun').value;
        if (!tahun) {
            e.preventDefault();
            alert('Tahun harus dipilih!');
            return false;
        }
    });
</script>
@endpush














