@extends('layouts.app')

@section('title', 'Rekapitulasi Absen All')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-table me-2"></i>Rekapitulasi Absen All</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('rekapitulasi-absen-all.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="dari_tanggal" class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" id="dari_tanggal" name="dari_tanggal" value="{{ $startDate }}">
                            </div>
                            <div class="col-md-3">
                                <label for="sampai_tanggal" class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" id="sampai_tanggal" name="sampai_tanggal" value="{{ $endDate }}">
                            </div>
                            <div class="col-md-3">
                                <label for="divisi" class="form-label">Divisi</label>
                                <select class="form-select" id="divisi" name="divisi">
                                    <option value="">Semua Divisi</option>
                                    @foreach($divisis as $div)
                                    <option value="{{ $div->vcKodeDivisi }}" {{ $divisiId == $div->vcKodeDivisi ? 'selected' : '' }}>{{ $div->vcNamaDivisi }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="departemen" class="form-label">Departemen</label>
                                <select class="form-select" id="departemen" name="departemen">
                                    <option value="">Semua Departemen</option>
                                    @foreach($departemens as $dept)
                                    <option value="{{ $dept->vcKodeDept }}" {{ $departemenId == $dept->vcKodeDept ? 'selected' : '' }}>{{ $dept->vcNamaDept }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="group_pegawai" class="form-label">Group Pegawai</label>
                                <select class="form-select" id="group_pegawai" name="group_pegawai">
                                    <option value="">Semua Group</option>
                                    @foreach($groups as $group)
                                    <option value="{{ $group }}" {{ $groupPegawai == $group ? 'selected' : '' }}>{{ $group }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary shadow-sm">
                                    <i class="fas fa-search me-2"></i>Preview
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if(count($rekapitulasiData) > 0)
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="mb-3 text-center">
                        <h4 class="mb-1"><strong>REKAPITULASI ABSENSI KARYAWAN</strong></h4>
                        <h5 class="mb-1">ABN GROUP</h5>
                        <p class="mb-1">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</p>
                        <p class="mb-0"><strong>Jumlah Hari Kerja Normal: {{ $jumlahHariKerja }} Hari</strong></p>
                    </div>
                    <div class="mb-3 text-end">
                        <a href="{{ route('rekapitulasi-absen-all.export', ['dari_tanggal' => $startDate, 'sampai_tanggal' => $endDate, 'divisi' => $divisiId, 'departemen' => $departemenId, 'group_pegawai' => $groupPegawai]) }}"
                            class="btn btn-primary shadow-sm me-2">
                            <i class="fas fa-file-excel me-2"></i>Export ke Excel
                        </a>
                        <a href="{{ route('rekapitulasi-absen-all.print', ['dari_tanggal' => $startDate, 'sampai_tanggal' => $endDate, 'divisi' => $divisiId, 'departemen' => $departemenId, 'group_pegawai' => $groupPegawai]) }}"
                            target="_blank"
                            class="btn btn-success shadow-sm">
                            <i class="fas fa-print me-2"></i>Cetak
                        </a>
                    </div>

                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-bordered table-sm" style="font-size: 0.85rem; table-layout: fixed; width: 100%;">
                            <thead class="table-light" style="position: sticky; top: 0; z-index: 10; background-color: #f8f9fa;">
                                <tr>
                                    <th rowspan="2" style="text-align: center; vertical-align: middle; width: 3%;">No.</th>
                                    <th rowspan="2" style="text-align: center; vertical-align: middle; width: 7%;">NIK</th>
                                    <th rowspan="2" style="text-align: center; vertical-align: middle; width: 13.5%;">Nama</th>
                                    <th rowspan="2" style="text-align: center; vertical-align: middle; width: 12%;">Divisi</th>
                                    <th rowspan="2" style="text-align: center; vertical-align: middle; width: 8%;">Dept.</th>
                                    <th rowspan="2" style="text-align: center; vertical-align: middle; width: 6%;">Group</th>
                                    <th rowspan="2" style="text-align: center; vertical-align: middle; width: 7.2%;">Tgl Masuk</th>
                                    <th colspan="13" style="text-align: center;">Absensi</th>
                                    <th rowspan="2" style="text-align: center; vertical-align: top; width: 3%; padding: 6px 4px;">JHK</th>
                                    <th rowspan="2" style="text-align: center; vertical-align: top; width: 6%; padding: 6px 4px;">%TW</th>
                                    <th rowspan="2" style="text-align: center; vertical-align: top; width: 6%; padding: 6px 4px;">%AH</th>
                                    <th rowspan="2" style="text-align: center; vertical-align: top; width: 6%; padding: 6px 4px;">%FAK</th>
                                </tr>
                                <tr>
                                    <th style="text-align: center; width: 3%; padding: 6px 4px;">S</th>
                                    <th style="text-align: center; width: 3%; padding: 6px 4px;">I</th>
                                    <th style="text-align: center; width: 3%; padding: 6px 4px;">A</th>
                                    <th style="text-align: center; width: 3.5%; padding: 6px 4px;">IR</th>
                                    <th style="text-align: center; width: 3.5%; padding: 6px 4px;">IO</th>
                                    <th style="text-align: center; width: 3.5%; padding: 6px 4px;">CT</th>
                                    <th style="text-align: center; width: 3.5%; padding: 6px 4px;">CM</th>
                                    <th style="text-align: center; width: 3%; padding: 6px 4px;">T</th>
                                    <th style="text-align: center; width: 3.5%; padding: 6px 4px;">MS</th>
                                    <th style="text-align: center; width: 3.5%; padding: 6px 4px;">IB</th>
                                    <th style="text-align: center; width: 3.5%; padding: 6px 4px;">PC</th>
                                    <th style="text-align: center; width: 4.5%; padding: 6px 4px;">H</th>
                                    <th style="text-align: center; width: 3.5%; padding: 6px 4px;">K8</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rekapitulasiData as $data)
                                <tr>
                                    <td style="text-align: center;">{{ $data['no'] }}</td>
                                    <td style="text-align: center;"><strong>{{ $data['nik'] }}</strong></td>
                                    <td>{{ $data['nama'] }}</td>
                                    <td style="text-align: center;">{{ $data['divisi'] }}</td>
                                    <td style="text-align: center;">{{ $data['departemen'] }}</td>
                                    <td style="text-align: center;">{{ $data['group'] }}</td>
                                    <td style="text-align: center;">{{ $data['tgl_masuk'] }}</td>
                                    <td style="text-align: center; padding: 4px 6px;">{{ $data['s'] }}</td>
                                    <td style="text-align: center; padding: 4px 6px;">{{ $data['i'] }}</td>
                                    <td style="text-align: center; padding: 4px 6px;">{{ $data['a'] }}</td>
                                    <td style="text-align: center; padding: 4px 6px;">{{ $data['ir'] }}</td>
                                    <td style="text-align: center; padding: 4px 6px;">{{ $data['io'] }}</td>
                                    <td style="text-align: center; padding: 4px 6px;">{{ $data['ct'] }}</td>
                                    <td style="text-align: center; padding: 4px 6px;">{{ $data['cm'] }}</td>
                                    <td style="text-align: center; padding: 4px 6px;">{{ $data['t'] }}</td>
                                    <td style="text-align: center; padding: 4px 6px;">{{ $data['ms'] ?? 0 }}</td>
                                    <td style="text-align: center; padding: 4px 6px;">{{ $data['ib'] ?? 0 }}</td>
                                    <td style="text-align: center; padding: 4px 6px;">{{ $data['pc'] ?? 0 }}</td>
                                    <td style="text-align: center; padding: 4px 6px; writing-mode: horizontal-tb; text-orientation: mixed; transform: none; white-space: nowrap;"><strong>{{ $data['h'] }}</strong></td>
                                    <td style="text-align: center; padding: 4px 6px;">{{ $data['k8'] ?? 0 }}</td>
                                    <td style="text-align: center; padding: 4px 6px; vertical-align: top;">{{ $data['jhk'] }}</td>
                                    <td style="text-align: center; padding: 4px 6px; vertical-align: top;">{{ str_replace('.', ',', number_format($data['persentase_tw'] ?? 0, 2)) }}%</td>
                                    <td style="text-align: center; padding: 4px 6px; vertical-align: top;">{{ str_replace('.', ',', number_format($data['persentase_ah'] ?? 0, 2)) }}%</td>
                                    <td style="text-align: center; padding: 4px 6px; vertical-align: top;">{{ str_replace('.', ',', number_format($data['persentase_fak'] ?? 0, 2)) }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <strong>Keterangan:</strong>
                                </div>
                                <div class="card-body" style="font-size: 0.85rem;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>S</strong> = Sakit (S010)</p>
                                            <p class="mb-1"><strong>I</strong> = Ijin Pribadi (I002)</p>
                                            <p class="mb-1"><strong>A</strong> = Alfa (A001) atau tidak ada data absensi dan tidak ada data input tidak masuk</p>
                                            <p class="mb-1"><strong>IR</strong> = Ijin Resmi (I001)</p>
                                            <p class="mb-1"><strong>IO</strong> = Ijin Organisasi (I003)</p>
                                            <p class="mb-1"><strong>CT</strong> = Cuti Tahunan (C010)</p>
                                            <p class="mb-1"><strong>CM</strong> = Cuti Melahirkan</p>
                                            <p class="mb-1"><strong>T</strong> = Terlambat (jam masuk > jam masuk Shift-nya)</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>MS</strong> = Ijin Keluar komplek pribadi kategori=Masuk Siang</p>
                                            <p class="mb-1"><strong>IB</strong> = Ijin Keluar komplek pribadi kategori=Izin Biasa</p>
                                            <p class="mb-1"><strong>PC</strong> = Ijin Keluar komplek pribadi kategori=Pulang Cepat</p>
                                            <p class="mb-1"><strong>H</strong> = Jumlah Hadir</p>
                                            <p class="mb-1"><strong>K8</strong> = Jam Kerja Kurang dari 8 jam</p>
                                            <p class="mb-1"><strong>%TW</strong> = Persentase Tepat Waktu</p>
                                            <p class="mb-1"><strong>%AH</strong> = Persentase Aktual Hadir</p>
                                            <p class="mb-1"><strong>%FAK</strong> = Final Absensi secara Kebijakan</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Tidak ada data untuk ditampilkan. Silakan pilih filter yang berbeda.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table th {
        font-weight: 600;
        border: 1px solid #dee2e6;
        padding: 6px 4px;
        text-align: center;
    }

    .table td {
        border: 1px solid #dee2e6;
        padding: 4px 6px;
    }

    .table-bordered {
        border: 2px solid #333;
    }

    .table-bordered th,
    .table-bordered td {
        border: 1px solid #333;
    }

    /* Pastikan kolom JHK, %TW, %AH, %FAK sejajar dengan kolom absensi */
    .table th[rowspan="2"]:last-child,
    .table th[rowspan="2"]:nth-last-child(2),
    .table th[rowspan="2"]:nth-last-child(3),
    .table th[rowspan="2"]:nth-last-child(4) {
        text-align: center !important;
        vertical-align: middle !important;
        padding: 6px 4px !important;
    }

    .table td:last-child,
    .table td:nth-last-child(2),
    .table td:nth-last-child(3),
    .table td:nth-last-child(4) {
        text-align: center !important;
        padding: 4px 6px !important;
        white-space: nowrap;
        width: auto;
    }
    
    /* Pastikan kolom JHK, %TW, %AH, %FAK sejajar */
    .table th[rowspan="2"]:nth-last-child(4),
    .table th[rowspan="2"]:nth-last-child(3),
    .table th[rowspan="2"]:nth-last-child(2),
    .table th[rowspan="2"]:nth-last-child(1) {
        padding: 6px 4px !important;
        vertical-align: top !important;
    }
    
    .table td:nth-last-child(4),
    .table td:nth-last-child(3),
    .table td:nth-last-child(2),
    .table td:nth-last-child(1) {
        padding: 4px 6px !important;
        vertical-align: top !important;
    }

    /* Pastikan semua label kolom absensi (S, I, A, IR, IO, CT, CM, T, MS, IB, PC, H, K8) center */
    .table thead tr:last-child th {
        text-align: center !important;
    }

    /* Pastikan label kolom IR, IO, CT, CM, MS, IB, PC, K8 horizontal (mendatar) */
    .table thead tr:last-child th:nth-child(4),
    .table thead tr:last-child th:nth-child(5),
    .table thead tr:last-child th:nth-child(6),
    .table thead tr:last-child th:nth-child(7),
    .table thead tr:last-child th:nth-child(9),
    .table thead tr:last-child th:nth-child(10),
    .table thead tr:last-child th:nth-child(11),
    .table thead tr:last-child th:nth-child(13) {
        text-align: center !important;
        writing-mode: horizontal-tb !important;
        text-orientation: mixed !important;
        transform: none !important;
        white-space: nowrap !important;
    }

    /* Pastikan value kolom S, I, A, IR, IO, CT, CM, T, MS, IB, PC, H, K8 horizontal alignment center */
    .table tbody tr td:nth-child(7),
    .table tbody tr td:nth-child(8),
    .table tbody tr td:nth-child(9),
    .table tbody tr td:nth-child(10),
    .table tbody tr td:nth-child(11),
    .table tbody tr td:nth-child(12),
    .table tbody tr td:nth-child(13),
    .table tbody tr td:nth-child(14),
    .table tbody tr td:nth-child(15),
    .table tbody tr td:nth-child(16),
    .table tbody tr td:nth-child(17),
    .table tbody tr td:nth-child(18),
    .table tbody tr td:nth-child(19) {
        text-align: center !important;
        padding: 4px 6px !important;
        writing-mode: horizontal-tb !important;
        text-orientation: mixed !important;
        transform: none !important;
    }

    /* Pastikan kolom H (Hadir) horizontal alignment */
    .table tbody tr td:nth-child(18) {
        text-align: center !important;
        writing-mode: horizontal-tb !important;
        text-orientation: mixed !important;
        transform: none !important;
        white-space: nowrap !important;
    }
</style>
@endpush