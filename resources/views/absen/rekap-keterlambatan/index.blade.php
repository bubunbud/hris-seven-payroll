@extends('layouts.app')

@section('title', 'Rekap Absensi Keterlambatan')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-clock me-2"></i>Rekap Absensi Keterlambatan
                </h2>
                @if(count($rekapData) > 0)
                <a href="{{ route('rekap-keterlambatan.preview', request()->query()) }}" target="_blank" class="btn btn-primary no-print">
                    <i class="fas fa-print me-2"></i>Cetak
                </a>
                @endif
            </div>

            <!-- Print Header (hidden by default, shown only when printing) -->
            <div class="print-header" style="display: none;">
                <h3>REKAP ABSENSI KETERLAMBATAN</h3>
                @php
                    $selectedDivisi = $divisis->where('vcKodeDivisi', $divisiId)->first();
                    $selectedDepartemen = $departemens->where('vcKodeDept', $departemenId)->first();
                    $selectedBagian = $bagians->where('vcKodeBagian', $bagianId)->first();
                @endphp
                <h4>
                    @if($divisiId && $selectedDivisi)
                        {{ $selectedDivisi->vcNamaDivisi }}
                    @else
                        Semua Divisi
                    @endif
                </h4>
                <h5>
                    @if($departemenId && $selectedDepartemen)
                        {{ $selectedDepartemen->vcNamaDept }}
                    @else
                        Semua Departemen
                    @endif
                    @if($bagianId && $selectedBagian)
                        , {{ $selectedBagian->vcNamaBagian }}
                    @elseif(!$bagianId)
                        , Semua Bagian
                    @endif
                </h5>
                <h6>
                    Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                </h6>
            </div>

            <!-- Filter Section -->
            <div class="card mb-4 no-print">
                <div class="card-body">
                    <form method="GET" action="{{ route('rekap-keterlambatan.index') }}" id="filterForm">
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
                            <div class="col-md-3">
                                <label for="divisi" class="form-label">Divisi</label>
                                <select class="form-select" id="divisi" name="divisi">
                                    <option value="">Semua Divisi</option>
                                    @foreach($divisis as $div)
                                    <option value="{{ $div->vcKodeDivisi }}" {{ $divisiId == $div->vcKodeDivisi ? 'selected' : '' }}>
                                        {{ $div->vcNamaDivisi }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="departemen" class="form-label">Departemen</label>
                                <select class="form-select" id="departemen" name="departemen" {{ !$divisiId ? 'disabled' : '' }}>
                                    <option value="">Semua Departemen</option>
                                    @foreach($departemens as $dept)
                                    <option value="{{ $dept->vcKodeDept }}" {{ $departemenId == $dept->vcKodeDept ? 'selected' : '' }}>
                                        {{ $dept->vcNamaDept }}
                                    </option>
                                    @endforeach
                                </select>
                                @if(!$divisiId)
                                <small class="text-muted">Pilih Divisi terlebih dahulu</small>
                                @endif
                            </div>
                            <div class="col-md-2">
                                <label for="bagian" class="form-label">Bagian</label>
                                <select class="form-select" id="bagian" name="bagian" {{ !$departemenId ? 'disabled' : '' }} data-initial-value="{{ $bagianId }}">
                                    <option value="">Semua Bagian</option>
                                    @foreach($bagians as $bag)
                                    <option value="{{ $bag->vcKodeBagian }}" {{ $bagianId == $bag->vcKodeBagian ? 'selected' : '' }}>
                                        {{ $bag->vcNamaBagian }}
                                    </option>
                                    @endforeach
                                </select>
                                @if(!$departemenId)
                                <small class="text-muted">Pilih Departemen terlebih dahulu</small>
                                @endif
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-4">
                                <label for="search" class="form-label">NIK / Nama</label>
                                <div class="position-relative">
                                    <input type="text"
                                        class="form-control"
                                        id="search"
                                        name="search"
                                        value="{{ $search ?? '' }}"
                                        placeholder="Cari NIK atau Nama"
                                        autocomplete="off">
                                    <div id="searchAutocomplete" class="autocomplete-dropdown" style="display: none;"></div>
                                </div>
                                <small class="text-muted">Ketik NIK atau nama karyawan untuk mencari (auto complete lokal)</small>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 shadow-sm px-4">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Data Count -->
            <div class="alert alert-info d-flex justify-content-between align-items-center no-print">
                <div>
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Jumlah Karyawan: {{ number_format(count($rekapData)) }}.</strong>
                </div>
            </div>

            <!-- Tabel Rekap Keterlambatan -->
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped table-sm align-middle">
                        <thead class="table-secondary">
                            <tr class="text-center align-middle">
                                <th width="2%" class="no-print"></th>
                                <th width="3%">No</th>
                                <th width="7%">NIK</th>
                                <th width="18%">Nama</th>
                                <th width="15%">Divisi</th>
                                <th width="17%">Departemen</th>
                                <th width="17%">Bagian</th>
                                <th width="8%">Jumlah Telat (hari)</th>
                                <th width="8%">Jumlah Menit Telat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekapData as $index => $row)
                            <tr class="parent-row" data-row-index="{{ $index }}">
                                <td class="text-center no-print">
                                    @if(!empty($row['detail_telat']) && count($row['detail_telat']) > 0)
                                    <button type="button" class="btn btn-sm btn-link p-0 expand-btn" data-bs-toggle="collapse" data-bs-target="#detail-{{ $index }}" aria-expanded="false" aria-controls="detail-{{ $index }}">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                    @endif
                                </td>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="text-center">{{ $row['nik'] }}</td>
                                <td>{{ $row['nama'] }}</td>
                                <td>{{ $row['divisi'] }}</td>
                                <td>{{ $row['departemen'] }}</td>
                                <td>{{ $row['bagian'] }}</td>
                                <td class="text-center">{{ number_format($row['jumlah_telat'], 0, ',', '.') }}</td>
                                <td class="text-center">{{ number_format($row['menit_telat'], 0, ',', '.') }}</td>
                            </tr>
                            @if(!empty($row['detail_telat']) && count($row['detail_telat']) > 0)
                            <tr class="collapse detail-row" id="detail-{{ $index }}" data-parent-index="{{ $index }}">
                                <td colspan="9" class="p-0 border-0 detail-colspan">
                                    <div class="p-3 bg-light">
                                        <h6 class="mb-3">
                                            <i class="fas fa-calendar-alt me-2 no-print"></i>Detail Tanggal Keterlambatan
                                        </h6>
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr class="text-center">
                                                    <th width="5%">No</th>
                                                    <th width="20%">Tanggal</th>
                                                    <th width="15%">Shift Masuk</th>
                                                    <th width="15%">Jam Masuk</th>
                                                    <th width="15%">Menit Telat</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($row['detail_telat'] as $detailIndex => $detail)
                                                <tr>
                                                    <td class="text-center">{{ $detailIndex + 1 }}</td>
                                                    <td class="text-center">{{ $detail['tanggal_formatted'] }}</td>
                                                    <td class="text-center">{{ $detail['shift_masuk'] }}</td>
                                                    <td class="text-center">{{ $detail['jam_masuk'] }}</td>
                                                    <td class="text-center">{{ number_format($detail['menit_telat'], 0, ',', '.') }} menit</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Tidak ada data keterlambatan untuk filter yang dipilih.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
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

    .expand-btn {
        color: #0d6efd;
        text-decoration: none;
        transition: transform 0.3s ease;
    }

    .expand-btn:hover {
        color: #0a58ca;
    }

    .expand-btn i {
        transition: transform 0.3s ease;
    }

    .expand-btn[aria-expanded="true"] i {
        transform: rotate(180deg);
    }

    .detail-row {
        background-color: #f8f9fa;
    }

    .detail-row .table {
        background-color: white;
    }

    .parent-row:hover {
        background-color: #f8f9fa;
    }

    /* Print Styles */
    @media print {
        @page {
            size: A4 landscape;
            margin: 1.5cm 1cm 1.5cm 1cm;
        }

        body {
            font-size: 9pt;
            background: white;
        }

        .no-print {
            display: none !important;
            visibility: hidden !important;
        }

        /* Pastikan semua elemen tabel terlihat secara eksplisit */
        table,
        table * {
            visibility: visible !important;
        }

        /* Pastikan semua elemen tabel terlihat secara eksplisit */
        table,
        table * {
            visibility: visible !important;
        }

        /* Sembunyikan sidebar dan elemen navigasi */
        .sidebar,
        .d-lg-none,
        #toggleSidebar,
        .navbar,
        .breadcrumb {
            display: none !important;
        }

        .app-wrapper {
            display: block;
        }

        .content {
            padding: 0;
            margin: 0;
        }

        /* Print Header */
        .print-header {
            display: block !important;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            text-align: center;
            padding: 10px 0;
            border-bottom: 2px solid #000;
            background-color: #fff;
            z-index: 1000;
            margin-bottom: 15px;
        }

        .print-header h3 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
        }

        .print-header h4 {
            margin: 5px 0;
            font-size: 11pt;
            font-weight: bold;
        }

        .print-header h5 {
            margin: 5px 0;
            font-size: 10pt;
        }

        .print-header h6 {
            margin: 5px 0;
            font-size: 10pt;
        }

        /* Tabel */
        .table {
            font-size: 8pt;
            border-collapse: collapse;
            width: 100%;
            display: table !important;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 4px 6px;
            display: table-cell !important;
        }

        /* Pastikan thead selalu muncul dan terlihat */
        .table thead {
            display: table-header-group !important;
            visibility: visible !important;
        }

        .table thead tr {
            display: table-row !important;
            visibility: visible !important;
        }

        .table thead th {
            background-color: #e9ecef !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-weight: bold;
            display: table-cell !important;
            visibility: visible !important;
            color: #000 !important;
            border: 1px solid #000 !important;
            padding: 6px 4px !important;
            text-align: center !important;
        }

        /* Pastikan class table-secondary tidak menyembunyikan thead */
        .table-secondary,
        .table-secondary th {
            background-color: #e9ecef !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color: #000 !important;
            display: table-cell !important;
            visibility: visible !important;
        }

        /* Force thead untuk muncul - override semua kemungkinan */
        .table-bordered thead,
        .table-bordered thead tr,
        .table-bordered thead th {
            display: table-header-group !important;
            display: table-row !important;
            display: table-cell !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        /* Pastikan thead tidak tersembunyi oleh class apapun */
        thead.table-secondary,
        thead.table-secondary tr,
        thead.table-secondary th {
            display: table-header-group !important;
            display: table-row !important;
            display: table-cell !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        /* Pastikan tbody juga terlihat */
        .table tbody {
            display: table-row-group !important;
            visibility: visible !important;
        }

        .table tbody tr {
            display: table-row !important;
            visibility: visible !important;
        }

        .table tbody td {
            display: table-cell !important;
            visibility: visible !important;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.05);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Card styling untuk print */
        .card {
            border: none;
            box-shadow: none;
        }

        .card-body {
            padding: 0;
        }

        /* Pastikan tabel tidak terpotong */
        .table-responsive {
            overflow: visible;
        }

        /* Page break */
        .page-break {
            page-break-after: always;
        }

        /* Avoid page break inside row */
        tr {
            page-break-inside: avoid;
        }

        /* Pastikan thead muncul di setiap halaman - override semua kemungkinan hidden */
        table thead {
            display: table-header-group !important;
            visibility: visible !important;
        }

        table tbody {
            display: table-row-group !important;
            visibility: visible !important;
        }

        /* Pastikan semua elemen tabel terlihat */
        table {
            display: table !important;
            visibility: visible !important;
        }

        table thead tr {
            display: table-row !important;
            visibility: visible !important;
        }

        table thead th {
            display: table-cell !important;
            visibility: visible !important;
        }

        /* Pastikan tidak ada CSS yang menyembunyikan thead */
        .table thead,
        .table thead * {
            display: revert !important;
            visibility: visible !important;
        }

        /* Expand semua detail row saat print */
        .detail-row {
            display: table-row !important;
        }

        .detail-row.collapse {
            display: table-row !important;
        }

        /* Perbaiki colspan untuk print (kolom expand disembunyikan) */
        .detail-row .detail-colspan {
            /* Saat print, kolom expand disembunyikan jadi colspan efektif menjadi 8 */
        }

        /* Styling untuk detail table saat print */
        .detail-row td {
            padding: 8px !important;
        }

        .detail-row .table {
            font-size: 7pt;
            margin: 5px 0;
            width: 100%;
        }

        .detail-row .table th,
        .detail-row .table td {
            padding: 4px 6px;
            border: 1px solid #000;
            text-align: center;
        }

        .detail-row .table thead {
            display: table-header-group !important;
        }

        .detail-row .table thead th {
            background-color: #f0f0f0 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-weight: bold;
            color: #000 !important;
        }

        .detail-row .table tbody tr {
            background-color: #fff !important;
        }

        /* H6 untuk judul detail */
        .detail-row h6 {
            font-size: 9pt;
            font-weight: bold;
            margin: 5px 0 8px 0;
        }

        /* Background untuk detail container */
        .detail-row .bg-light {
            background-color: #f8f9fa !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            padding: 8px !important;
            border: 1px solid #ddd;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    let searchTimeout;
    let selectedIndex = -1;
    const searchInput = document.getElementById('search');
    const autocompleteDiv = document.getElementById('searchAutocomplete');
    const karyawanList = @json($karyawanList);

    function getCurrentTypingTerm() {
        const value = searchInput.value.trim();
        return value;
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
            const results = karyawanList.filter(k =>
                (k.nik && k.nik.toLowerCase().includes(currentTerm)) ||
                (k.nama && k.nama.toLowerCase().includes(currentTerm))
            ).slice(0, 20);
            displayAutocomplete(results);
        }, 200);
    });

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
                <small>Divisi: ${karyawan.divisi || '-'} | Departemen: ${karyawan.departemen || '-'} | Bagian: ${karyawan.bagian || '-'}</small>
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
        searchInput.value = `${karyawan.nik} - ${karyawan.nama}`;
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
        }
    });

    function updateSelectedItem(items, index) {
        items.forEach((item, i) => {
            item.classList.toggle('active', i === index);
        });
    }

    // Handle expand/collapse untuk detail tanggal telat
    document.addEventListener('DOMContentLoaded', function() {
        const expandButtons = document.querySelectorAll('.expand-btn');
        
        expandButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-bs-target');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    // Toggle aria-expanded
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';
                    this.setAttribute('aria-expanded', !isExpanded);
                }
            });
        });

        // Handle Bootstrap collapse events untuk update icon
        const detailRows = document.querySelectorAll('.detail-row');
        detailRows.forEach(row => {
            row.addEventListener('show.bs.collapse', function() {
                const parentIndex = this.getAttribute('data-parent-index');
                const button = document.querySelector(`[data-bs-target="#detail-${parentIndex}"]`);
                if (button) {
                    button.setAttribute('aria-expanded', 'true');
                }
            });
            
            row.addEventListener('hide.bs.collapse', function() {
                const parentIndex = this.getAttribute('data-parent-index');
                const button = document.querySelector(`[data-bs-target="#detail-${parentIndex}"]`);
                if (button) {
                    button.setAttribute('aria-expanded', 'false');
                }
            });
        });

        // Hierarki Filter: Divisi -> Departemen -> Bagian
        const divisiSelect = document.getElementById('divisi');
        const departemenSelect = document.getElementById('departemen');
        const bagianSelect = document.getElementById('bagian');

        // Handle perubahan Divisi
        divisiSelect.addEventListener('change', function() {
            const divisiId = this.value;
            
            // Reset Departemen dan Bagian
            departemenSelect.innerHTML = '<option value="">Semua Departemen</option>';
            bagianSelect.innerHTML = '<option value="">Semua Bagian</option>';
            
            if (divisiId) {
                // Enable Departemen dan load data
                departemenSelect.disabled = false;
                loadDepartemensByDivisi(divisiId);
            } else {
                // Disable Departemen dan Bagian
                departemenSelect.disabled = true;
                bagianSelect.disabled = true;
            }
        });

        // Handle perubahan Departemen
        departemenSelect.addEventListener('change', function() {
            const departemenId = this.value;
            const divisiId = divisiSelect.value;
            
            // Reset Bagian
            bagianSelect.innerHTML = '<option value="">Semua Bagian</option>';
            
            if (departemenId) {
                // Enable Bagian dan load data
                bagianSelect.disabled = false;
                loadBagiansByDepartemen(departemenId, divisiId);
            } else {
                // Disable Bagian
                bagianSelect.disabled = true;
            }
        });

        // Function untuk load Departemen berdasarkan Divisi
        function loadDepartemensByDivisi(divisiId) {
            return new Promise((resolve, reject) => {
                departemenSelect.disabled = true;
                departemenSelect.innerHTML = '<option value="">Memuat...</option>';
                
                fetch(`{{ route('rekap-keterlambatan.get-departemens') }}?divisi=${divisiId}`)
                    .then(response => response.json())
                    .then(data => {
                        departemenSelect.innerHTML = '<option value="">Semua Departemen</option>';
                        
                        if (data.success && data.departemens && data.departemens.length > 0) {
                            data.departemens.forEach(dept => {
                                const option = document.createElement('option');
                                option.value = dept.vcKodeDept;
                                option.textContent = dept.vcNamaDept;
                                departemenSelect.appendChild(option);
                            });
                        }
                        
                        departemenSelect.disabled = false;
                        resolve();
                    })
                    .catch(error => {
                        console.error('Error loading departemens:', error);
                        departemenSelect.innerHTML = '<option value="">Error loading data</option>';
                        departemenSelect.disabled = false;
                        reject(error);
                    });
            });
        }

        // Function untuk load Bagian berdasarkan Departemen
        function loadBagiansByDepartemen(departemenId, divisiId) {
            return new Promise((resolve, reject) => {
                bagianSelect.disabled = true;
                bagianSelect.innerHTML = '<option value="">Memuat...</option>';
                
                let url = `{{ route('rekap-keterlambatan.get-bagians') }}?departemen=${departemenId}`;
                if (divisiId) {
                    url += `&divisi=${divisiId}`;
                }
                
                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        bagianSelect.innerHTML = '<option value="">Semua Bagian</option>';
                        
                        if (data.success && data.bagians && data.bagians.length > 0) {
                            data.bagians.forEach(bag => {
                                const option = document.createElement('option');
                                option.value = bag.vcKodeBagian;
                                option.textContent = bag.vcNamaBagian;
                                bagianSelect.appendChild(option);
                            });
                        } else if (!data.success) {
                            console.error('Error from server:', data.message || 'Unknown error');
                            bagianSelect.innerHTML = '<option value="">Error: ' + (data.message || 'Unknown error') + '</option>';
                        }
                        
                        bagianSelect.disabled = false;
                        resolve();
                    })
                    .catch(error => {
                        console.error('Error loading bagians:', error);
                        bagianSelect.innerHTML = '<option value="">Error loading data</option>';
                        bagianSelect.disabled = false;
                        reject(error);
                    });
            });
        }

        // Load initial data jika ada nilai yang sudah dipilih
        const initialDivisiId = divisiSelect.value;
        const initialDepartemenId = departemenSelect.value;
        const initialBagianId = bagianSelect.getAttribute('data-initial-value');
        
        if (initialDivisiId) {
            // Load Departemen untuk Divisi yang sudah dipilih
            loadDepartemensByDivisi(initialDivisiId).then(() => {
                // Set kembali nilai Departemen yang dipilih
                if (initialDepartemenId) {
                    departemenSelect.value = initialDepartemenId;
                    // Load Bagian untuk Departemen yang sudah dipilih
                    loadBagiansByDepartemen(initialDepartemenId, initialDivisiId).then(() => {
                        // Set kembali nilai Bagian yang dipilih
                        if (initialBagianId) {
                            bagianSelect.value = initialBagianId;
                        }
                    });
                }
            });
        }
    });
</script>
@endpush



