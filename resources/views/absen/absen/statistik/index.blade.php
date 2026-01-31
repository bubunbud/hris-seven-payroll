@extends('layouts.app')

@section('title', 'Statistik Absensi - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    @php
        if (!function_exists('fmtDate')) {
            function fmtDate($d) {
                if (!$d || $d === '-') return '-';
                try { return \Carbon\Carbon::parse($d)->format('d-m-Y'); } catch (\Exception $e) { return $d; }
            }
        }
    @endphp
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>Statistik Absensi
                </h2>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('absensi.statistik.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="dari_tanggal" class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" id="dari_tanggal" name="dari_tanggal" value="{{ $startDate }}">
                            </div>
                            <div class="col-md-3">
                                <label for="sampai_tanggal" class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" id="sampai_tanggal" name="sampai_tanggal" value="{{ $endDate }}">
                            </div>
                            <div class="col-md-2">
                                <label for="nik" class="form-label">NIK</label>
                                <input type="text" class="form-control" id="nik" name="nik" value="{{ $nik }}" placeholder="Cari NIK">
                                @if($nik && $selectedNama)
                                <div class="form-text">Nama: {{ $selectedNama }}</div>
                                @endif
                            </div>
                            <div class="col-md-2">
                                <label for="group" class="form-label">Group Pegawai</label>
                                <select class="form-select" id="group" name="group">
                                    <option value="">Semua</option>
                                    @foreach($groups as $g)
                                    <option value="{{ $g }}" {{ $group===$g? 'selected' : '' }}>{{ $g }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-center">
                                <button type="submit" class="btn btn-primary w-100 shadow-sm">
                                    <i class="fas fa-eye me-2"></i>Preview
                                </button>
                            </div>
                        </div>
                    </form>
                    <div id="loadingBar" class="mt-3 d-none">
                        <div class="progress" role="progressbar" aria-label="Loading" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%">Menghitung statistik...</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small">Jumlah Hari Kerja Umum</div>
                            <div class="display-6 fw-bold">{{ number_format($totalHariKerja) }}</div>
                            <div class="small text-muted">Selain sabtu, minggu, dan hari libur</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small">Jumlah Hari Kerja Karyawan</div>
                            <div class="display-6 fw-bold">{{ number_format($totalJHK) }}</div>
                            <div class="small text-muted">Total JHK sesuai Tgl Masuk</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small">Kehadiran (Aktual)</div>
                            <div class="display-6 fw-bold">{{ number_format($hadir) }}</div>
                            <div class="small text-muted">Persentase: <strong>{{ number_format($persentaseKehadiranAktual, 2) }}%</strong></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small">Kehadiran (Kebijakan)</div>
                            <div class="display-6 fw-bold">{{ number_format($hadirKebijakan) }}</div>
                            <div class="small text-muted">Persentase: <strong>{{ number_format($persentaseKehadiranKebijakan, 2) }}%</strong></div>
                            <div class="small text-muted">Termasuk ketidakhadiran dibayar</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small">Jumlah Jam Izin Masuk Siang</div>
                            <div class="display-6 fw-bold">{{ number_format($totalJamMasukSiang,2) }}</div>
                            <div class="small text-muted">Total jam dari izin masuk siang</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small">Jumlah Jam Izin Keluar Komplek</div>
                            <div class="display-6 fw-bold">{{ number_format($totalJamIzinKeluarKomplek,2) }}</div>
                            <div class="small text-muted">Total jam izin keluar komplek (IB)</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small">Jumlah Jam Izin Pulang Cepat</div>
                            <div class="display-6 fw-bold">{{ number_format($totalJamIzinPulangCepat,2) }}</div>
                            <div class="small text-muted">Total jam izin pulang cepat (PC)</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm h-100 {{ $totalJamIzinKeluar != 0 ? 'border-danger' : '' }}">
                        <div class="card-body">
                            <div class="text-muted small">Jam Izin Keluar (total)</div>
                            <div class="display-6 fw-bold {{ $totalJamIzinKeluar != 0 ? 'text-danger' : '' }}">{{ number_format($totalJamIzinKeluar,2) }}</div>
                            <div class="small text-muted">Rata-rata: {{ number_format($rataJamIzinKeluar,2) }} jam/karyawan</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small">Total Jam Standar Kerja (Hari Kerja Umum)</div>
                            <div class="display-6 fw-bold">{{ number_format($totalJamStandarKerjaUmum, 2) }}</div>
                            <div class="small text-muted">Berdasarkan Jumlah Hari Kerja Umum</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small">Total Jam Standar Kerja (Hari Kerja Karyawan)</div>
                            <div class="display-6 fw-bold">{{ number_format($totalJamStandarKerjaKaryawan, 2) }}</div>
                            <div class="small text-muted">Berdasarkan Jumlah Hari Kerja Karyawan</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small">Total Jam Kerja Aktual</div>
                            <div class="display-6 fw-bold">{{ number_format($totalJamKerjaAktual, 2) }}</div>
                            <div class="small text-muted">Berdasarkan Kehadiran (Aktual)</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm h-100 {{ $surplusDefisitJamKerja >= 0 ? 'border-success' : 'border-danger' }}">
                        <div class="card-body">
                            <div class="text-muted small">Surplus/Defisit Jam Kerja</div>
                            <div class="display-6 fw-bold {{ $surplusDefisitJamKerja >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $surplusDefisitJamKerja >= 0 ? '+' : '' }}{{ number_format($surplusDefisitJamKerja, 2) }}
                            </div>
                            <div class="small text-muted">Total Jam Kerja Aktual - Total Jam Standar Kerja (Hari Kerja Karyawan)</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="text-muted">Daftar Rekap Per Karyawan</div>
                                <div class="small text-muted">Total Hari Kerja Periode: <strong>{{ $totalHariKerja }}</strong></div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="small fw-normal" style="font-size: 0.85rem;">No</th>
                                            <th class="small fw-normal" style="font-size: 0.85rem;">NIK</th>
                                            <th class="small fw-normal" style="font-size: 0.85rem;">Nama</th>
                                            <th class="small fw-normal text-center" style="font-size: 0.85rem;">Tanggal Masuk</th>
                                            <th class="small fw-normal text-center" style="font-size: 0.85rem;">Hari Kerja</th>
                                            <th class="small fw-normal text-center" style="font-size: 0.85rem;">Hadir</th>
                                            <th class="small fw-normal text-center" style="font-size: 0.85rem;">Cuti</th>
                                            <th class="small fw-normal text-center" style="font-size: 0.85rem;">Sakit</th>
                                            <th class="small fw-normal text-center" style="font-size: 0.85rem;">Izin Pribadi</th>
                                            <th class="small fw-normal text-center" style="font-size: 0.85rem;">Izin Resmi</th>
                                            <th class="small fw-normal text-center" style="font-size: 0.85rem;">Izin Organisasi</th>
                                            <th class="small fw-normal text-center" style="font-size: 0.85rem;">Telat</th>
                                            <th class="small fw-normal text-center" style="font-size: 0.85rem;">Masuk Siang</th>
                                            <th class="small fw-normal text-center" style="font-size: 0.85rem;">Keluar Komplek</th>
                                            <th class="small fw-normal text-center" style="font-size: 0.85rem;">Pulang Cepat</th>
                                            <th class="small fw-normal text-center" style="font-size: 0.85rem;">Izin Keluar (jam)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($perKaryawan as $i => $row)
                                        <tr>
                                            <td class="text-center">{{ $i+1 }}</td>
                                            <td><strong>{{ $row['nik'] }}</strong></td>
                                            <td>{{ $row['nama'] }}</td>
                                            <td class="text-center">{{ $row['tglMasuk'] ? \Carbon\Carbon::parse($row['tglMasuk'])->format('d/m/Y') : '-' }}</td>
                                            <td class="text-center">{{ $row['hariKerja'] }}</td>
                                            <td class="text-center">{{ $row['hadir'] }}</td>
                                            <td class="text-center">{{ $row['cutiTahunan'] }}</td>
                                            <td class="text-center">{{ $row['sakitSurat'] }}</td>
                                            <td class="text-center">{{ $row['izinPribadi'] }}</td>
                                            <td class="text-center">{{ $row['izinResmi'] }}</td>
                                            <td class="text-center">{{ $row['izinOrganisasi'] }}</td>
                                            <td class="text-center">{{ $row['telat'] }}</td>
                                            <td class="text-center">{{ $row['masukSiang'] }}</td>
                                            <td class="text-center">{{ $row['keluarKomplek'] }}</td>
                                            <td class="text-center">{{ $row['pulangCepat'] }}</td>
                                            <td class="text-center">{{ number_format($row['izinKeluarJam'],2) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="16" class="text-center text-muted">Tidak ada data</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small mb-2">Rangkuman Tidak Masuk</div>
                            <ul class="list-group list-group-flush">
                                @forelse($ringkasanTidakMasuk as $jenis => $hari)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $jenis }}</span>
                                    <span class="badge text-bg-secondary">{{ $hari }} hari</span>
                                </li>
                                @empty
                                <li class="list-group-item text-muted">Tidak ada data pada periode ini</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <div class="text-muted small">Telat</div>
                                    <div class="display-6 fw-bold">{{ number_format($telat) }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted small">Masuk Siang</div>
                                    <div class="display-6 fw-bold">{{ number_format($masukSiang) }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted small">Pulang Cepat</div>
                                    <div class="display-6 fw-bold">{{ number_format($pulangCepat) }}</div>
                                </div>
                            </div>
                            <div class="small text-muted mt-2">Aturan: Telat jika jam masuk > jam shift (>1 menit); Masuk Siang dari Izin Keluar Komplek dengan kode Z004 (izin masuk siang pribadi); Pulang Cepat jika jam pulang < jam shift. Izin Keluar Pribadi = Z003 + Z004.</div>
                        </div>
                    </div>
                </div>
            </div>

            @if($nik && (!empty($debugAllIzinZ003Z004) || !empty($debugMasukSiang) || !empty($debugTelat) || !empty($debugPulangCepat) || !empty($debugTidakMasuk)))
            <div class="row mt-3">
                @if(!empty($debugAllIzinZ003Z004))
                <div class="col-12 mb-3">
                    <div class="card shadow-sm border-info">
                        <div class="card-header text-white" style="background-color: #0dcaf0;">
                            <small><strong>Debug Info - Semua Izin Keluar Komplek (Z003 + Z004) untuk NIK: {{ $nik }}</strong></small>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="small">Tanggal</th>
                                            <th class="small">NIK</th>
                                            <th class="small">Nama</th>
                                            <th class="small">Kode Izin</th>
                                            <th class="small">Dari</th>
                                            <th class="small">Sampai</th>
                                            <th class="small">Durasi</th>
                                            <th class="small">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($debugAllIzinZ003Z004 as $debug)
                                        <tr>
                                            <td class="small">{{ fmtDate($debug['tanggal']) }}</td>
                                            <td class="small">{{ $debug['nik'] }}</td>
                                            <td class="small">{{ $debug['nama'] }}</td>
                                            <td class="small"><span class="badge bg-secondary">{{ $debug['vcKodeIzin'] }}</span></td>
                                            <td class="small">{{ $debug['dtDari'] ? substr($debug['dtDari'], 0, 5) : '-' }}</td>
                                            <td class="small">{{ $debug['dtSampai'] ?? '-' }}</td>
                                            <td class="small text-center">{{ $debug['durasiText'] ?? '-' }}</td>
                                            <td class="small">{{ $debug['durasi'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                @if(!empty($debugMasukSiang))
                <div class="col-12">
                    <div class="card shadow-sm border-warning">
                        <div class="card-header text-white" style="background-color: #ffc107;">
                            <small><strong>Debug Info - Data Masuk Siang Pribadi (Z004) untuk NIK: {{ $nik }}</strong></small>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="small">Tanggal</th>
                                            <th class="small">NIK</th>
                                            <th class="small">Nama</th>
                                            <th class="small">Kode Izin</th>
                                            <th class="small">Dari</th>
                                            <th class="small">Sampai</th>
                                            <th class="small">Jam Masuk Siang</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($debugMasukSiang as $debug)
                                        <tr>
                                            <td class="small">{{ fmtDate($debug['tanggal']) }}</td>
                                            <td class="small">{{ $debug['nik'] }}</td>
                                            <td class="small">{{ $debug['nama'] }}</td>
                                            <td class="small"><span class="badge bg-warning text-dark">{{ $debug['vcKodeIzin'] }}</span></td>
                                            <td class="small">{{ $debug['dtDari'] ? substr($debug['dtDari'], 0, 5) : '-' }}</td>
                                            <td class="small">{{ $debug['dtSampai'] ? substr($debug['dtSampai'], 0, 5) : '-' }}</td>
                                            <td class="small">{{ number_format($debug['jamMasukSiang'], 2) }} jam</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="row mt-3">
                @if(!empty($debugTelat))
                <div class="col-12 mb-3">
                    <div class="card shadow-sm border-danger">
                        <div class="card-header text-white" style="background-color: #dc3545;">
                            <small><strong>Debug Info - Telat untuk NIK: {{ $nik }}</strong></small>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="small">Tanggal</th>
                                            <th class="small">Jam Masuk</th>
                                            <th class="small">Jam Pulang</th>
                                            <th class="small">Shift Masuk</th>
                                            <th class="small">Shift Pulang</th>
                                            <th class="small">Menit Telat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($debugTelat as $d)
                                        <tr>
                                            <td class="small">{{ fmtDate($d['tanggal']) }}</td>
                                            <td class="small">{{ $d['jamMasuk'] }}</td>
                                            <td class="small">{{ $d['jamKeluar'] }}</td>
                                            <td class="small">{{ $d['shiftMasuk'] }}</td>
                                            <td class="small">{{ $d['shiftPulang'] }}</td>
                                            <td class="small">{{ $d['menitTelat'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if(!empty($debugPulangCepat))
                <div class="col-12 mb-3">
                    <div class="card shadow-sm border-secondary">
                        <div class="card-header text-white" style="background-color: #6c757d;">
                            <small><strong>Debug Info - Pulang Cepat untuk NIK: {{ $nik }}</strong></small>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="small">Tanggal</th>
                                            <th class="small">Jam Masuk</th>
                                            <th class="small">Jam Pulang</th>
                                            <th class="small">Shift Masuk</th>
                                            <th class="small">Shift Pulang</th>
                                            <th class="small">Menit Lebih Awal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($debugPulangCepat as $d)
                                        <tr>
                                            <td class="small">{{ fmtDate($d['tanggal']) }}</td>
                                            <td class="small">{{ $d['jamMasuk'] }}</td>
                                            <td class="small">{{ $d['jamKeluar'] }}</td>
                                            <td class="small">{{ $d['shiftMasuk'] }}</td>
                                            <td class="small">{{ $d['shiftPulang'] }}</td>
                                            <td class="small">{{ $d['menitLebihAwal'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if(!empty($debugTidakMasuk))
                <div class="col-12">
                    <div class="card shadow-sm border-dark">
                        <div class="card-header text-white" style="background-color: #212529;">
                            <small><strong>Debug Info - Tidak Masuk untuk NIK: {{ $nik }}</strong></small>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="small">Kode</th>
                                            <th class="small">Keterangan</th>
                                            <th class="small">Mulai</th>
                                            <th class="small">Selesai</th>
                                            <th class="small">Jumlah Hari</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($debugTidakMasuk as $d)
                                        <tr>
                                            <td class="small">{{ $d['kode'] }}</td>
                                            <td class="small">{{ $d['keterangan'] }}</td>
                                            <td class="small">{{ fmtDate($d['mulai']) }}</td>
                                            <td class="small">{{ fmtDate($d['selesai']) }}</td>
                                            <td class="small">{{ $d['hari'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function() {
        const form = document.getElementById('filterForm');
        const bar = document.getElementById('loadingBar');
        if (form && bar) {
            form.addEventListener('submit', function() {
                bar.classList.remove('d-none');
            });
        }
    })();
</script>
@endpush


