@extends('layouts.app')

@section('title', 'Browse Absensi Security/Satpam - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-shield-alt me-2"></i>Browse Absensi Security / Satpam
                </h2>
            </div>

            <!-- Filter Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('browse-absensi-security.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="dari_tanggal" class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" id="dari_tanggal" name="dari_tanggal" value="{{ $startDate }}">
                            </div>
                            <div class="col-md-2">
                                <label for="sampai_tanggal" class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" id="sampai_tanggal" name="sampai_tanggal" value="{{ $endDate }}">
                            </div>
                            <div class="col-md-3">
                                <label for="search" class="form-label">NIK / Nama</label>
                                <div class="position-relative">
                                    <input type="text" class="form-control" id="search" name="search" value="{{ $search ?? '' }}"
                                        placeholder="Cari NIK atau Nama" autocomplete="off">
                                    <div id="searchAutocomplete" class="autocomplete-dropdown" style="display: none;"></div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="filter_status" class="form-label">Filter Status</label>
                                <select class="form-select" id="filter_status" name="filter_status">
                                    <option value="">Semua</option>
                                    <option value="sesuai" {{ $filterStatus == 'sesuai' ? 'selected' : '' }}>Sesuai</option>
                                    <option value="tidak_sesuai" {{ $filterStatus == 'tidak_sesuai' ? 'selected' : '' }}>Tidak Sesuai Jadwal</option>
                                    <option value="telat" {{ $filterStatus == 'telat' ? 'selected' : '' }}>Telat</option>
                                    <option value="pulang_cepat" {{ $filterStatus == 'pulang_cepat' ? 'selected' : '' }}>Pulang Cepat</option>
                                    <option value="tidak_masuk" {{ $filterStatus == 'tidak_masuk' ? 'selected' : '' }}>Tidak Masuk</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-eye me-2"></i>Preview
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-3">
                <div class="col-md-2">
                    <div class="card bg-primary text-white">
                        <div class="card-body py-2">
                            <small>Total Data</small>
                            <h5 class="mb-0">{{ number_format($summary['total']) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-success text-white">
                        <div class="card-body py-2">
                            <small>Sesuai</small>
                            <h5 class="mb-0">{{ number_format($summary['sesuai']) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-warning text-dark">
                        <div class="card-body py-2">
                            <small>Telat</small>
                            <h5 class="mb-0">{{ number_format($summary['telat']) }}</h5>
                            <small>{{ $summary['total_telat_menit'] }} menit</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-warning text-dark">
                        <div class="card-body py-2">
                            <small>Pulang Cepat</small>
                            <h5 class="mb-0">{{ number_format($summary['pulang_cepat']) }}</h5>
                            <small>{{ $summary['total_pulang_cepat_menit'] }} menit</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-danger text-white">
                        <div class="card-body py-2">
                            <small>Tidak Masuk</small>
                            <h5 class="mb-0">{{ number_format($summary['tidak_masuk']) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-secondary text-white">
                        <div class="card-body py-2">
                            <small>Tidak Sesuai Jadwal</small>
                            <h5 class="mb-0">{{ number_format($summary['tidak_sesuai']) }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover table-striped">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="8%">Tanggal</th>
                                    <th width="7%">NIK</th>
                                    <th width="15%">Nama</th>
                                    <th width="10%">Divisi</th>
                                    <th width="8%">Jam Masuk</th>
                                    <th width="8%">Jam Pulang</th>
                                    <th width="7%">Durasi (jam)</th>
                                    <th width="8%">Shift Terjadwal</th>
                                    <th width="8%">Shift Aktual</th>
                                    <th width="7%">Telat (mnt)</th>
                                    <th width="8%">Pulang Cepat (mnt)</th>
                                    <th width="12%">Kepatuhan</th>
                                    <th width="9%">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($absens as $item)
                                @php
                                    $kepatuhan = $item['kepatuhan'] ?? '-';
                                    $badgeClass = 'bg-secondary';
                                    if ($kepatuhan === 'Sesuai') $badgeClass = 'bg-success';
                                    elseif (in_array($kepatuhan, ['Telat', 'Pulang Cepat', 'Telat & Pulang Cepat'])) $badgeClass = 'bg-warning text-dark';
                                    elseif ($kepatuhan === 'Tidak Sesuai Jadwal') $badgeClass = 'bg-danger';
                                    elseif ($item['source'] === 'tidak_masuk') $badgeClass = 'bg-danger';
                                @endphp
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($item['dtTanggal'])->format('d/m/Y') }}</td>
                                    <td><strong>{{ $item['vcNik'] }}</strong></td>
                                    <td>{{ $item['Nama'] }}</td>
                                    <td>{{ $item['vcNamaDivisi'] ?? '-' }}</td>
                                    <td>
                                        @if($item['dtJamMasuk'])
                                            {{ \Carbon\Carbon::parse($item['dtJamMasuk'])->format('H:i') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item['dtJamKeluar'])
                                            {{ \Carbon\Carbon::parse($item['dtJamKeluar'])->format('H:i') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(($item['total_jam'] ?? 0) > 0)
                                            <span class="badge bg-info">{{ number_format($item['total_jam'], 1) }} jam</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($item['shift_terjadwal']))
                                            @foreach($item['shift_terjadwal'] as $s)
                                                <span class="badge bg-primary me-1">S{{ $s }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item['shift_aktual'])
                                            <span class="badge bg-info">S{{ $item['shift_aktual'] }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(($item['telat_menit'] ?? 0) > 0)
                                            <span class="badge bg-warning text-dark">{{ $item['telat_menit'] }} mnt</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(($item['pulang_cepat_menit'] ?? 0) > 0)
                                            <span class="badge bg-warning text-dark">{{ $item['pulang_cepat_menit'] }} mnt</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $badgeClass }}">{{ $kepatuhan }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $item['vcketerangan'] ?? '-' }}</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="13" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Tidak ada data absensi Security untuk periode ini</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($absens->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            Menampilkan {{ $absens->firstItem() }} sampai {{ $absens->lastItem() }} dari {{ $absens->total() }} data
                        </div>
                        {{ $absens->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Legend -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Keterangan</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <span class="badge bg-success me-2">Sesuai</span>
                            <span class="text-muted">Absensi sesuai jadwal, tidak telat, tidak pulang cepat</span>
                        </div>
                        <div class="col-md-4">
                            <span class="badge bg-warning text-dark me-2">Telat</span>
                            <span class="text-muted">Jam masuk melebihi toleransi shift (dari Master Shift Security)</span>
                        </div>
                        <div class="col-md-4">
                            <span class="badge bg-warning text-dark me-2">Pulang Cepat</span>
                            <span class="text-muted">Jam pulang sebelum batas toleransi (handle cross-day Shift 3)</span>
                        </div>
                        <div class="col-md-4 mt-2">
                            <span class="badge bg-danger me-2">Tidak Sesuai Jadwal</span>
                            <span class="text-muted">Shift aktual tidak sesuai dengan jadwal</span>
                        </div>
                        <div class="col-md-4 mt-2">
                            <span class="badge bg-danger me-2">Tidak Masuk</span>
                            <span class="text-muted">Tidak ada absensi (izin/cuti/sakit)</span>
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
    position: absolute; top: 100%; left: 0; right: 0;
    background: white; border: 1px solid #ced4da; border-radius: 0.375rem;
    max-height: 250px; overflow-y: auto; z-index: 1000;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15); margin-top: 2px;
}
.autocomplete-item { padding: 0.5rem 1rem; cursor: pointer; border-bottom: 1px solid #f0f0f0; }
.autocomplete-item:hover, .autocomplete-item.active { background-color: #f8f9fa; }
</style>
@endpush

@push('scripts')
<script>
const karyawanList = @json($karyawanList);
const searchInput = document.getElementById('search');
const autocompleteDiv = document.getElementById('searchAutocomplete');

function getCurrentTypingTerm() {
    const v = searchInput.value.trim();
    return v.split(',')[v.split(',').length - 1]?.trim() || '';
}

searchInput.addEventListener('input', function() {
    const term = getCurrentTypingTerm().toLowerCase();
    autocompleteDiv.style.display = term.length < 2 ? 'none' : 'block';
    if (term.length < 2) return;
    const results = karyawanList.filter(k => k.search.includes(term)).slice(0, 15);
    autocompleteDiv.innerHTML = results.length ? results.map(k =>
        `<div class="autocomplete-item" data-nik="${k.nik}" data-nama="${k.nama}"><strong>${k.nik}</strong> - ${k.nama}</div>`
    ).join('') : '<div class="autocomplete-item text-muted">Tidak ada</div>';
    autocompleteDiv.querySelectorAll('.autocomplete-item').forEach((el, i) => {
        if (el.dataset.nik) el.addEventListener('click', function() {
            const terms = searchInput.value.split(',').map(t=>t.trim()).filter(Boolean);
            terms.pop();
            terms.push(`${this.dataset.nik} - ${this.dataset.nama}`);
            searchInput.value = terms.join(', ');
            autocompleteDiv.style.display = 'none';
        });
    });
});

document.addEventListener('click', function(e) {
    if (!searchInput.contains(e.target) && !autocompleteDiv.contains(e.target))
        autocompleteDiv.style.display = 'none';
});

document.getElementById('filter_status').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});
</script>
@endpush
