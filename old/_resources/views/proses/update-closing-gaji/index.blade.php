@extends('layouts.app')

@section('title', 'Update Closing Gaji - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-edit me-2"></i>Update Closing Gaji
                </h2>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-success" id="addBtn">
                        <i class="fas fa-plus me-1"></i>Tambah
                    </button>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('update-closing-gaji.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="periode_dari" class="form-label">Periode Dari</label>
                                <input type="date" class="form-control" id="periode_dari" name="periode_dari" value="{{ $startDate }}">
                            </div>
                            <div class="col-md-3">
                                <label for="periode_sampai" class="form-label">Periode Sampai</label>
                                <input type="date" class="form-control" id="periode_sampai" name="periode_sampai" value="{{ $endDate }}">
                            </div>
                            <div class="col-md-2">
                                <label for="nik" class="form-label">NIK</label>
                                <input type="text" class="form-control" id="nik" name="nik" value="{{ $nik }}" placeholder="Cari NIK">
                            </div>
                            <div class="col-md-2">
                                <label for="divisi" class="form-label">Divisi</label>
                                <select class="form-select" id="divisi" name="divisi">
                                    <option value="SEMUA">SEMUA</option>
                                    @foreach($divisis as $d)
                                    <option value="{{ $d->vcKodeDivisi }}" {{ $divisi == $d->vcKodeDivisi ? 'selected' : '' }}>
                                        {{ $d->vcKodeDivisi }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 shadow-sm">
                                    <i class="fas fa-eye me-2"></i>Preview
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="max-height:600px; overflow-y:auto;">
                        <table class="table table-hover">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="8%">Periode</th>
                                    <th width="8%">Periode Awal</th>
                                    <th width="8%">Periode Akhir</th>
                                    <th width="6%">Closing</th>
                                    <th width="7%">NIK</th>
                                    <th width="15%">Nama</th>
                                    <th width="8%">Divisi</th>
                                    <th width="8%">Gaji Pokok</th>
                                    <th width="8%">Gaji Bersih</th>
                                    <th width="5%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($records as $row)
                                @php
                                $totalPenerimaan = $row->decGapok + $row->decUangMakan + $row->decTransport +
                                $row->decPremi + $row->decTotallembur1 + $row->decTotallembur2 +
                                $row->decTotallembur3 + $row->decRapel;
                                $totalPotongan = $row->decPotonganBPJSKes + $row->decPotonganBPJSJHT +
                                $row->decPotonganBPJSJP + $row->decIuranSPN +
                                $row->decPotonganKoperasi + $row->decPotonganBPR +
                                $row->decPotonganHC + $row->decPotonganAbsen + $row->decPotonganLain;
                                $gajiBersih = $totalPenerimaan - $totalPotongan;
                                $compositeKey = base64_encode($row->vcPeriodeAwal->format('Y-m-d') . '|' .
                                $row->vcPeriodeAkhir->format('Y-m-d') . '|' .
                                $row->vcNik . '|' .
                                $row->periode->format('Y-m-d') . '|' .
                                $row->vcClosingKe);
                                @endphp
                                <tr>
                                    <td>{{ $row->periode->format('d/m/Y') }}</td>
                                    <td>{{ $row->vcPeriodeAwal->format('d/m/Y') }}</td>
                                    <td>{{ $row->vcPeriodeAkhir->format('d/m/Y') }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $row->vcClosingKe }}</span>
                                    </td>
                                    <td><strong>{{ $row->vcNik }}</strong></td>
                                    <td>{{ $row->karyawan->Nama ?? 'N/A' }}</td>
                                    <td>{{ $row->divisi->vcKodeDivisi ?? 'N/A' }}</td>
                                    <td class="text-end">{{ number_format($row->decGapok, 0, ',', '.') }}</td>
                                    <td class="text-end"><strong>{{ number_format($gajiBersih, 0, ',', '.') }}</strong></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="editRecord('{{ $compositeKey }}')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteRecord('{{ $compositeKey }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Belum ada data</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($records->hasPages())
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
                        <div class="text-muted small">
                            Menampilkan {{ $records->firstItem() }} sampai {{ $records->lastItem() }} dari {{ $records->total() }} data
                        </div>
                        <nav aria-label="Navigasi halaman">
                            {{ $records->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="closingModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="closingModalLabel">Tambah Closing Gaji</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="closingForm">
                <input type="hidden" name="_method" id="_method" value="POST">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="accordion" id="closingAccordion">
                        <!-- Informasi Dasar -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#infoDasar">
                                    <i class="fas fa-info-circle me-2"></i>Informasi Dasar
                                </button>
                            </h2>
                            <div id="infoDasar" class="accordion-collapse collapse show" data-bs-parent="#closingAccordion">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="vcPeriodeAwal" class="form-label">Periode Awal <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="vcPeriodeAwal" name="vcPeriodeAwal" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="vcPeriodeAkhir" class="form-label">Periode Akhir <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="vcPeriodeAkhir" name="vcPeriodeAkhir" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="vcNik" class="form-label">NIK <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="vcNik" name="vcNik" maxlength="8" required>
                                                <div class="form-text" id="namaPreview"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="periode" class="form-label">Periode Gajian <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="periode" name="periode" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="vcClosingKe" class="form-label">Closing Ke <span class="text-danger">*</span></label>
                                                <select class="form-select" id="vcClosingKe" name="vcClosingKe" required>
                                                    <option value="">Pilih</option>
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="vcKodeGolongan" class="form-label">Golongan</label>
                                                <input type="text" class="form-control" id="vcKodeGolongan" name="vcKodeGolongan" maxlength="10">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="vcKodeDivisi" class="form-label">Divisi</label>
                                                <input type="text" class="form-control" id="vcKodeDivisi" name="vcKodeDivisi" maxlength="10">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="vcStatusPegawai" class="form-label">Status Pegawai</label>
                                                <input type="text" class="form-control" id="vcStatusPegawai" name="vcStatusPegawai" maxlength="20">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="jumlahHari" class="form-label">Jumlah Hari Kerja</label>
                                                <input type="number" class="form-control" id="jumlahHari" name="jumlahHari" min="0" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gaji Pokok & Jam Kerja -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#gajiPokok">
                                    <i class="fas fa-money-bill-wave me-2"></i>Gaji Pokok & Jam Kerja
                                </button>
                            </h2>
                            <div id="gajiPokok" class="accordion-collapse collapse" data-bs-parent="#closingAccordion">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="decGapok" class="form-label">Gaji Pokok</label>
                                                <input type="number" class="form-control" id="decGapok" name="decGapok" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="decJamKerja" class="form-label">Jam Kerja</label>
                                                <input type="number" class="form-control" id="decJamKerja" name="decJamKerja" step="0.01" min="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Potongan -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#potongan">
                                    <i class="fas fa-minus-circle me-2"></i>Potongan
                                </button>
                            </h2>
                            <div id="potongan" class="accordion-collapse collapse" data-bs-parent="#closingAccordion">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decPotonganHC" class="form-label">Potongan HC</label>
                                                <input type="number" class="form-control" id="decPotonganHC" name="decPotonganHC" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decPotonganBPR" class="form-label">Potongan DPLK</label>
                                                <input type="number" class="form-control" id="decPotonganBPR" name="decPotonganBPR" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decIuranSPN" class="form-label">Iuran SPN</label>
                                                <input type="number" class="form-control" id="decIuranSPN" name="decIuranSPN" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decPotonganBPJSKes" class="form-label">BPJS Kesehatan</label>
                                                <input type="number" class="form-control" id="decPotonganBPJSKes" name="decPotonganBPJSKes" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decPotonganBPJSJHT" class="form-label">BPJS JHT</label>
                                                <input type="number" class="form-control" id="decPotonganBPJSJHT" name="decPotonganBPJSJHT" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decPotonganBPJSJP" class="form-label">BPJS JP</label>
                                                <input type="number" class="form-control" id="decPotonganBPJSJP" name="decPotonganBPJSJP" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decPotonganKoperasi" class="form-label">Potongan Koperasi</label>
                                                <input type="number" class="form-control" id="decPotonganKoperasi" name="decPotonganKoperasi" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decPotonganAbsen" class="form-label">Potongan Absen</label>
                                                <input type="number" class="form-control" id="decPotonganAbsen" name="decPotonganAbsen" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decPotonganLain" class="form-label">Potongan Lain-lain</label>
                                                <input type="number" class="form-control" id="decPotonganLain" name="decPotonganLain" step="0.01" min="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tunjangan -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tunjangan">
                                    <i class="fas fa-gift me-2"></i>Tunjangan
                                </button>
                            </h2>
                            <div id="tunjangan" class="accordion-collapse collapse" data-bs-parent="#closingAccordion">
                                <div class="accordion-body">
                                    <!-- Row pertama: Jumlah Makan, Tarif Makan, Total Uang Makan -->
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="intMakan" class="form-label">Jumlah Makan</label>
                                                <input type="number" class="form-control" id="intMakan" name="intMakan" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decVarMakan" class="form-label">Tarif Makan</label>
                                                <input type="number" class="form-control" id="decVarMakan" name="decVarMakan" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decUangMakan" class="form-label">Total Uang Makan</label>
                                                <input type="number" class="form-control" id="decUangMakan" name="decUangMakan" step="0.01" min="0">
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Row kedua: Jumlah Transport, Tarif Transport, Total Uang Transport -->
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="intTransport" class="form-label">Jumlah Transport</label>
                                                <input type="number" class="form-control" id="intTransport" name="intTransport" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decVarTransport" class="form-label">Tarif Transport</label>
                                                <input type="number" class="form-control" id="decVarTransport" name="decVarTransport" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decTransport" class="form-label">Total Uang Transport</label>
                                                <input type="number" class="form-control" id="decTransport" name="decTransport" step="0.01" min="0">
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Row ketiga: Premi Hadir, Rapel/Selisih Upah -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="decPremi" class="form-label">Premi Hadir</label>
                                                <input type="number" class="form-control" id="decPremi" name="decPremi" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="decRapel" class="form-label">Rapel / Selisih Upah</label>
                                                <input type="number" class="form-control" id="decRapel" name="decRapel" step="0.01">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Absensi -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#absensi">
                                    <i class="fas fa-user-check me-2"></i>Absensi
                                </button>
                            </h2>
                            <div id="absensi" class="accordion-collapse collapse" data-bs-parent="#closingAccordion">
                                <div class="accordion-body">
                                    <h6 class="mb-3">Absensi Periode Ini (P2)</h6>
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="intHadir" class="form-label">Hadir</label>
                                                <input type="number" class="form-control" id="intHadir" name="intHadir" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="intTidakMasuk" class="form-label">Tidak Masuk</label>
                                                <input type="number" class="form-control" id="intTidakMasuk" name="intTidakMasuk" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="intJmlSakit" class="form-label">Sakit</label>
                                                <input type="number" class="form-control" id="intJmlSakit" name="intJmlSakit" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="intJmlAlpha" class="form-label">Alpha</label>
                                                <input type="number" class="form-control" id="intJmlAlpha" name="intJmlAlpha" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="intJmlIzin" class="form-label">Izin Pribadi</label>
                                                <input type="number" class="form-control" id="intJmlIzin" name="intJmlIzin" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="intJmlIzinR" class="form-label">Izin Resmi</label>
                                                <input type="number" class="form-control" id="intJmlIzinR" name="intJmlIzinR" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="intJmlCuti" class="form-label">Cuti</label>
                                                <input type="number" class="form-control" id="intJmlCuti" name="intJmlCuti" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="intJmlTelat" class="form-label">Telat</label>
                                                <input type="number" class="form-control" id="intJmlTelat" name="intJmlTelat" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="intHC" class="form-label">HC</label>
                                                <input type="number" class="form-control" id="intHC" name="intHC" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="intKHL" class="form-label">KHL</label>
                                                <input type="number" class="form-control" id="intKHL" name="intKHL" min="0">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Absensi Periode Sebelumnya (P1) - untuk Periode 2 -->
                                    <div id="absensiP1Section" class="d-none">
                                        <h6 class="mb-3">Absensi Periode Sebelumnya (P1) <small class="text-muted" id="periodeP1Info"></small></h6>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="intCutiLalu" class="form-label">Cuti (P1)</label>
                                                    <input type="number" class="form-control bg-light" id="intCutiLalu" name="intCutiLalu" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="intSakitLalu" class="form-label">Sakit (P1)</label>
                                                    <input type="number" class="form-control bg-light" id="intSakitLalu" name="intSakitLalu" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="intIzinLalu" class="form-label">Izin Pribadi (P1)</label>
                                                    <input type="number" class="form-control bg-light" id="intIzinLalu" name="intIzinLalu" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="intAlphaLalu" class="form-label">Alpha (P1)</label>
                                                    <input type="number" class="form-control bg-light" id="intAlphaLalu" name="intAlphaLalu" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="intTelatLalu" class="form-label">Telat (P1)</label>
                                                    <input type="number" class="form-control bg-light" id="intTelatLalu" name="intTelatLalu" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="intHcLalu" class="form-label">HC (P1)</label>
                                                    <input type="number" class="form-control bg-light" id="intHcLalu" name="intHcLalu" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="alert alert-info mt-2 mb-0">
                                            <small><strong>Total I+A+T+HC (P1+P2):</strong> <span id="totalIATHC">0</span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lembur -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#lembur">
                                    <i class="fas fa-clock me-2"></i>Lembur
                                </button>
                            </h2>
                            <div id="lembur" class="accordion-collapse collapse" data-bs-parent="#closingAccordion">
                                <div class="accordion-body">
                                    <h6 class="mb-3">Lembur Hari Kerja</h6>
                                    <!-- Row pertama: Jam Lembur Kerja 1, 2, 3 -->
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decJamLemburKerja1" class="form-label">Jam Lembur Kerja 1</label>
                                                <input type="number" class="form-control" id="decJamLemburKerja1" name="decJamLemburKerja1" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decJamLemburKerja2" class="form-label">Jam Lembur Kerja 2</label>
                                                <input type="number" class="form-control" id="decJamLemburKerja2" name="decJamLemburKerja2" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decJamLemburKerja3" class="form-label">Jam Lembur Kerja 3</label>
                                                <input type="number" class="form-control" id="decJamLemburKerja3" name="decJamLemburKerja3" step="0.01" min="0">
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Row kedua: Nominal Lembur Kerja 1, 2, 3 -->
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decLemburKerja1" class="form-label">Nominal Lembur Kerja 1</label>
                                                <input type="number" class="form-control" id="decLemburKerja1" name="decLemburKerja1" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decLemburKerja2" class="form-label">Nominal Lembur Kerja 2</label>
                                                <input type="number" class="form-control" id="decLemburKerja2" name="decLemburKerja2" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decLemburKerja3" class="form-label">Nominal Lembur Kerja 3</label>
                                                <input type="number" class="form-control" id="decLemburKerja3" name="decLemburKerja3" step="0.01" min="0">
                                            </div>
                                        </div>
                                    </div>
                                    <h6 class="mb-3">Lembur Hari Libur</h6>
                                    <!-- Row pertama: Jam Lembur Libur 2 dan 3 -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="decJamLemburLibur2" class="form-label">Jam Lembur Libur 2</label>
                                                <input type="number" class="form-control" id="decJamLemburLibur2" name="decJamLemburLibur2" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="decJamLemburLibur3" class="form-label">Jam Lembur Libur 3</label>
                                                <input type="number" class="form-control" id="decJamLemburLibur3" name="decJamLemburLibur3" step="0.01" min="0">
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Row kedua: Nominal Lembur 2 dan 3 -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="decLembur2" class="form-label">Nominal Lembur 2</label>
                                                <input type="number" class="form-control" id="decLembur2" name="decLembur2" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="decLembur3" class="form-label">Nominal Lembur 3</label>
                                                <input type="number" class="form-control" id="decLembur3" name="decLembur3" step="0.01" min="0">
                                            </div>
                                        </div>
                                    </div>
                                    <h6 class="mb-3">Total Lembur</h6>
                                    <!-- Row pertama: Total Jam Lembur Kerja, Libur, dan Total Nominal Lembur -->
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decJamLemburKerja" class="form-label">Total Jam Lembur Kerja</label>
                                                <input type="number" class="form-control" id="decJamLemburKerja" name="decJamLemburKerja" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decJamLemburLibur" class="form-label">Total Jam Lembur Libur</label>
                                                <input type="number" class="form-control" id="decJamLemburLibur" name="decJamLemburLibur" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decTotalNominalLembur" class="form-label">Total Nominal Lembur</label>
                                                <input type="number" class="form-control" id="decTotalNominalLembur" name="decTotalNominalLembur" step="0.01" min="0" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Row kedua: Total Nominal Lembur 1, 2, 3 -->
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decTotallembur1" class="form-label">Total Nominal Lembur 1</label>
                                                <input type="number" class="form-control" id="decTotallembur1" name="decTotallembur1" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decTotallembur2" class="form-label">Total Nominal Lembur 2</label>
                                                <input type="number" class="form-control" id="decTotallembur2" name="decTotallembur2" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="decTotallembur3" class="form-label">Total Nominal Lembur 3</label>
                                                <input type="number" class="form-control" id="decTotallembur3" name="decTotallembur3" step="0.01" min="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
@endsection

@push('scripts')
<script>
    let isEditMode = false;
    let currentId = null;

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const html = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
        document.querySelectorAll('.alert').forEach(a => a.remove());
        document.querySelector('.container-fluid').insertAdjacentHTML('afterbegin', html);
        setTimeout(() => {
            const a = document.querySelector('.alert');
            if (a) a.remove();
        }, 4000);
    }

    // Function to disable/enable fields in Informasi Dasar section
    function toggleInfoDasarFields(disabled) {
        const inputFields = [
            'vcPeriodeAwal',
            'vcPeriodeAkhir',
            'vcNik',
            'periode',
            'vcKodeGolongan',
            'vcKodeDivisi',
            'vcStatusPegawai',
            'jumlahHari' // Jumlah hari kerja (readonly, auto-calculate)
        ];

        const selectFields = [
            'vcClosingKe'
        ];

        // Set readOnly for input fields (nilai tetap terkirim saat submit)
        inputFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.readOnly = disabled;
                if (disabled) {
                    field.classList.add('bg-light');
                } else {
                    field.classList.remove('bg-light');
                }
            }
        });

        // Set style untuk select fields agar readonly (nilai tetap terkirim saat submit)
        selectFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                if (disabled) {
                    field.style.pointerEvents = 'none';
                    field.style.backgroundColor = '#f8f9fa';
                    field.style.cursor = 'not-allowed';
                } else {
                    field.style.pointerEvents = 'auto';
                    field.style.backgroundColor = '';
                    field.style.cursor = '';
                }
            }
        });
    }

    document.getElementById('addBtn').addEventListener('click', () => {
        isEditMode = false;
        currentId = null;
        document.getElementById('closingModalLabel').textContent = 'Tambah Closing Gaji';
        document.getElementById('closingForm').reset();
        document.getElementById('_method').value = 'POST';
        document.getElementById('vcNik').readOnly = false;
        toggleInfoDasarFields(false); // Enable all fields
        new bootstrap.Modal(document.getElementById('closingModal')).show();
    });

    document.getElementById('closingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const url = isEditMode ? `/update-closing-gaji/${currentId}` : '/update-closing-gaji';
        document.getElementById('_method').value = isEditMode ? 'PUT' : 'POST';
        const formData = new FormData(this);

        if (isEditMode) {
            formData.append('_method', 'PUT');
        }

        fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    bootstrap.Modal.getInstance(document.getElementById('closingModal')).hide();
                    location.reload();
                } else {
                    showAlert('error', data.message || 'Gagal menyimpan data');
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('error', 'Terjadi kesalahan saat menyimpan');
            });
    });

    function editRecord(id) {
        fetch(`/update-closing-gaji/${id}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    isEditMode = true;
                    currentId = id;
                    document.getElementById('closingModalLabel').textContent = 'Edit Closing Gaji';
                    document.getElementById('_method').value = 'PUT';

                    const record = data.record;
                    // Populate form fields
                    Object.keys(record).forEach(key => {
                        const field = document.getElementById(key);
                        if (field) {
                            if (field.type === 'checkbox') {
                                field.checked = record[key];
                            } else {
                                field.value = record[key] || '';
                            }
                        }
                    });

                    document.getElementById('vcNik').readOnly = true;
                    toggleInfoDasarFields(true); // Disable all fields in Informasi Dasar

                    // Load gapok data untuk perhitungan lembur
                    fetchGapokData(record.vcNik);

                    new bootstrap.Modal(document.getElementById('closingModal')).show();
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('error', 'Gagal memuat data');
            });
    }

    function deleteRecord(id) {
        if (!confirm('Hapus data ini?')) return;
        fetch(`/update-closing-gaji/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    _method: 'DELETE'
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    location.reload();
                } else {
                    showAlert('error', data.message || 'Gagal menghapus data');
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('error', 'Terjadi kesalahan saat menghapus');
            });
    }

    // Variable untuk menyimpan gapok per bulan
    let gapokPerBulan = 0;
    let gapokSetengahBulan = 0;
    let tarifPremi = 0;
    let absensiP1 = {
        intJmlCuti: 0,
        intJmlSakit: 0,
        intJmlIzin: 0,
        intJmlAlpha: 0,
        intJmlTelat: 0,
        intHC: 0
    };

    // Function untuk hitung hari kerja
    function calculateJumlahHariKerja() {
        const periodeAwal = document.getElementById('vcPeriodeAwal').value;
        const periodeAkhir = document.getElementById('vcPeriodeAkhir').value;

        if (!periodeAwal || !periodeAkhir) return;

        fetch('/update-closing-gaji/calculate-working-days', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    periode_awal: periodeAwal,
                    periode_akhir: periodeAkhir
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('jumlahHari').value = data.jumlah_hari;
                }
            })
            .catch(err => {
                console.error('Error calculating working days:', err);
            });
    }

    // Event listener untuk hitung hari kerja saat periode awal/akhir berubah
    document.getElementById('vcPeriodeAwal').addEventListener('change', calculateJumlahHariKerja);
    document.getElementById('vcPeriodeAkhir').addEventListener('change', calculateJumlahHariKerja);

    // Function untuk ambil gapok data
    function fetchGapokData(nik) {
        if (!nik) return;

        fetch('/update-closing-gaji/get-gapok', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    nik: nik
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Simpan gapok per bulan untuk perhitungan lembur
                    gapokPerBulan = data.gapok_per_bulan;
                    gapokSetengahBulan = data.gapok_setengah_bulan;
                    tarifPremi = data.tarif_premi || 0;

                    // Auto-fill tarif makan & transport jika belum diisi
                    if (!document.getElementById('decVarMakan').value) {
                        document.getElementById('decVarMakan').value = data.tarif_makan || 0;
                    }
                    if (!document.getElementById('decVarTransport').value) {
                        document.getElementById('decVarTransport').value = data.tarif_transport || 0;
                    }

                    // Auto-fill gaji pokok jika belum diisi
                    if (!document.getElementById('decGapok').value) {
                        document.getElementById('decGapok').value = data.gapok_setengah_bulan || 0;
                    }

                    // Recalculate total uang makan & transport jika sudah ada nilai
                    calculateTotalUangMakan();
                    calculateTotalUangTransport();

                    // Load absensi P1 jika periode 2
                    const vcClosingKe = document.getElementById('vcClosingKe').value;
                    if (vcClosingKe === '2') {
                        loadAbsensiP1(nik);
                    }
                }
            })
            .catch(err => {
                console.error('Error fetching gapok:', err);
            });
    }

    // Function untuk hitung total uang makan
    function calculateTotalUangMakan() {
        const jumlahMakan = parseFloat(document.getElementById('intMakan').value) || 0;
        const tarifMakan = parseFloat(document.getElementById('decVarMakan').value) || 0;
        const total = jumlahMakan * tarifMakan;
        document.getElementById('decUangMakan').value = total.toFixed(2);
    }

    // Function untuk hitung total uang transport
    function calculateTotalUangTransport() {
        const jumlahTransport = parseFloat(document.getElementById('intTransport').value) || 0;
        const tarifTransport = parseFloat(document.getElementById('decVarTransport').value) || 0;
        const total = jumlahTransport * tarifTransport;
        document.getElementById('decTransport').value = total.toFixed(2);
    }

    // Function untuk hitung nominal lembur kerja
    function calculateNominalLemburKerja() {
        if (!gapokPerBulan || gapokPerBulan === 0) {
            // Jika gapok belum ada, coba ambil dari decGapok (setengah bulan) * 2
            const gapokSetengah = parseFloat(document.getElementById('decGapok').value) || 0;
            gapokPerBulan = gapokSetengah * 2;
        }

        const ratePerJam = gapokPerBulan / 173;

        // Hitung nominal lembur kerja 1
        const jamKerja1 = parseFloat(document.getElementById('decJamLemburKerja1').value) || 0;
        const nominalKerja1 = jamKerja1 * 1.5 * ratePerJam;
        document.getElementById('decLemburKerja1').value = nominalKerja1.toFixed(2);

        // Hitung nominal lembur kerja 2
        const jamKerja2 = parseFloat(document.getElementById('decJamLemburKerja2').value) || 0;
        const nominalKerja2 = jamKerja2 * 2 * ratePerJam;
        document.getElementById('decLemburKerja2').value = nominalKerja2.toFixed(2);

        // Hitung nominal lembur kerja 3
        const jamKerja3 = parseFloat(document.getElementById('decJamLemburKerja3').value) || 0;
        const nominalKerja3 = jamKerja3 * 2 * ratePerJam; // Untuk hari kerja, jam ke-3 juga 2x
        document.getElementById('decLemburKerja3').value = nominalKerja3.toFixed(2);

        // Hitung total jam lembur kerja
        const totalJamKerja = jamKerja1 + jamKerja2 + jamKerja3;
        document.getElementById('decJamLemburKerja').value = totalJamKerja.toFixed(2);

        // Hitung total nominal lembur 1
        document.getElementById('decTotallembur1').value = nominalKerja1.toFixed(2);

        // Recalculate total lembur
        calculateTotalLembur();
    }

    // Function untuk hitung nominal lembur libur
    function calculateNominalLemburLibur() {
        if (!gapokPerBulan || gapokPerBulan === 0) {
            const gapokSetengah = parseFloat(document.getElementById('decGapok').value) || 0;
            gapokPerBulan = gapokSetengah * 2;
        }

        const ratePerJam = gapokPerBulan / 173;

        // Untuk hari libur: 2x (8 jam pertama), 3x (jam ke-9), 4x (jam ke-10-12)
        const jamLibur2 = parseFloat(document.getElementById('decJamLemburLibur2').value) || 0;
        const jamLibur3 = parseFloat(document.getElementById('decJamLemburLibur3').value) || 0;

        // Nominal lembur libur 2 (8 jam pertama × 2)
        const nominalLibur2 = jamLibur2 * 2 * ratePerJam;

        // Nominal lembur libur 3 
        // Jam ke-9 = 1 jam × 3, sisa (jam ke-10-12) = × 4
        // Untuk simplifikasi: jika jamLibur3 > 0, hitung 1 jam pertama × 3, sisanya × 4
        let nominalLibur3 = 0;
        if (jamLibur3 > 0) {
            const jamKe9 = Math.min(1, jamLibur3); // Maksimal 1 jam untuk rate 3x
            const jamKe10_12 = Math.max(0, jamLibur3 - jamKe9); // Sisa untuk rate 4x
            nominalLibur3 = (jamKe9 * 3 * ratePerJam) + (jamKe10_12 * 4 * ratePerJam);
        }

        // decLembur2 = nominal lembur libur 2 saja (tidak termasuk lembur kerja 2)
        document.getElementById('decLembur2').value = nominalLibur2.toFixed(2);

        // decLembur3 = nominal lembur libur 3 saja (tidak termasuk lembur kerja 3)
        document.getElementById('decLembur3').value = nominalLibur3.toFixed(2);

        // Total jam lembur libur
        const totalJamLibur = jamLibur2 + jamLibur3;
        document.getElementById('decJamLemburLibur').value = totalJamLibur.toFixed(2);

        // Recalculate total lembur
        calculateTotalLembur();
    }

    // Function untuk hitung total lembur
    function calculateTotalLembur() {
        // Total lembur 1 = hanya dari lembur kerja 1 (sudah dihitung di calculateNominalLemburKerja)

        // Total lembur 2 = lembur kerja 2 + lembur libur 2
        const lemburKerja2 = parseFloat(document.getElementById('decLemburKerja2').value) || 0;
        const lemburLibur2 = parseFloat(document.getElementById('decLembur2').value) || 0;
        document.getElementById('decTotallembur2').value = (lemburKerja2 + lemburLibur2).toFixed(2);

        // Total lembur 3 = lembur kerja 3 + lembur libur 3
        const lemburKerja3 = parseFloat(document.getElementById('decLemburKerja3').value) || 0;
        const lemburLibur3 = parseFloat(document.getElementById('decLembur3').value) || 0;
        document.getElementById('decTotallembur3').value = (lemburKerja3 + lemburLibur3).toFixed(2);

        // Total Nominal Lembur = Total Nominal Lembur 1 + 2 + 3
        const totalLembur1 = parseFloat(document.getElementById('decTotallembur1').value) || 0;
        const totalLembur2 = parseFloat(document.getElementById('decTotallembur2').value) || 0;
        const totalLembur3 = parseFloat(document.getElementById('decTotallembur3').value) || 0;
        const totalNominalLembur = totalLembur1 + totalLembur2 + totalLembur3;
        document.getElementById('decTotalNominalLembur').value = totalNominalLembur.toFixed(2);
    }

    // Autofill nama dan gapok saat NIK di-blur
    document.getElementById('vcNik').addEventListener('blur', function() {
        const nik = this.value.trim();
        if (!nik) return;

        // Ambil data karyawan
        fetch(`/karyawan/${nik}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json()).then(data => {
                document.getElementById('namaPreview').textContent = (data.success && data.karyawan?.Nama) ? 'Nama: ' + data.karyawan.Nama : '';
                if (data.success && data.karyawan) {
                    // Auto-fill golongan, divisi, status pegawai
                    if (!document.getElementById('vcKodeGolongan').value) {
                        document.getElementById('vcKodeGolongan').value = data.karyawan.Gol || '';
                    }
                    if (!document.getElementById('vcKodeDivisi').value) {
                        document.getElementById('vcKodeDivisi').value = data.karyawan.Divisi || '';
                    }
                    if (!document.getElementById('vcStatusPegawai').value) {
                        document.getElementById('vcStatusPegawai').value = data.karyawan.Status_Pegawai || '';
                    }

                    // Ambil data gapok
                    fetchGapokData(nik);
                }
            }).catch(() => {
                document.getElementById('namaPreview').textContent = '';
            });
    });

    // Event listeners untuk auto-calculation
    // Total Uang Makan
    document.getElementById('intMakan').addEventListener('input', calculateTotalUangMakan);
    document.getElementById('decVarMakan').addEventListener('input', calculateTotalUangMakan);

    // Total Uang Transport
    document.getElementById('intTransport').addEventListener('input', calculateTotalUangTransport);
    document.getElementById('decVarTransport').addEventListener('input', calculateTotalUangTransport);

    // Nominal Lembur Kerja
    document.getElementById('decJamLemburKerja1').addEventListener('input', calculateNominalLemburKerja);
    document.getElementById('decJamLemburKerja2').addEventListener('input', calculateNominalLemburKerja);
    document.getElementById('decJamLemburKerja3').addEventListener('input', calculateNominalLemburKerja);

    // Nominal Lembur Libur
    document.getElementById('decJamLemburLibur2').addEventListener('input', calculateNominalLemburLibur);
    document.getElementById('decJamLemburLibur3').addEventListener('input', calculateNominalLemburLibur);

    // Recalculate total lembur saat nominal lembur kerja berubah
    document.getElementById('decLemburKerja1').addEventListener('input', calculateTotalLembur);
    document.getElementById('decLemburKerja2').addEventListener('input', calculateTotalLembur);
    document.getElementById('decLemburKerja3').addEventListener('input', calculateTotalLembur);
    document.getElementById('decLembur2').addEventListener('input', calculateTotalLembur);
    document.getElementById('decLembur3').addEventListener('input', calculateTotalLembur);

    // Recalculate saat gaji pokok berubah (untuk perhitungan lembur)
    document.getElementById('decGapok').addEventListener('input', function() {
        const gapokSetengah = parseFloat(this.value) || 0;
        gapokPerBulan = gapokSetengah * 2;
        calculateNominalLemburKerja();
        calculateNominalLemburLibur();
    });

    // Function untuk load absensi P1
    function loadAbsensiP1(nik) {
        const periode = document.getElementById('periode').value;
        const vcKodeDivisi = document.getElementById('vcKodeDivisi').value;

        if (!periode || !vcKodeDivisi) return;

        fetch('/update-closing-gaji/get-absensi-p1', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    nik: nik,
                    periode: periode,
                    vcKodeDivisi: vcKodeDivisi
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    absensiP1 = data.absensi;
                    // Set nilai ke field int*Lalu (yang akan disimpan ke database)
                    document.getElementById('intCutiLalu').value = absensiP1.intJmlCuti || 0;
                    document.getElementById('intSakitLalu').value = absensiP1.intJmlSakit || 0;
                    document.getElementById('intIzinLalu').value = absensiP1.intJmlIzin || 0;
                    document.getElementById('intAlphaLalu').value = absensiP1.intJmlAlpha || 0;
                    document.getElementById('intTelatLalu').value = absensiP1.intJmlTelat || 0;
                    document.getElementById('intHcLalu').value = absensiP1.intHC || 0;

                    // Update info periode P1
                    const periodeAwal = new Date(data.periode_awal).toLocaleDateString('id-ID');
                    const periodeAkhir = new Date(data.periode_akhir).toLocaleDateString('id-ID');
                    document.getElementById('periodeP1Info').textContent = `(${periodeAwal} - ${periodeAkhir})`;
                    document.getElementById('absensiP1Section').classList.remove('d-none');

                    // Recalculate premi hadir
                    calculatePremiHadir();
                } else {
                    document.getElementById('absensiP1Section').classList.add('d-none');
                }
            })
            .catch(err => {
                console.error('Error loading absensi P1:', err);
                document.getElementById('absensiP1Section').classList.add('d-none');
            });
    }

    // Function untuk hitung premi hadir
    function calculatePremiHadir() {
        const vcClosingKe = document.getElementById('vcClosingKe').value;
        if (vcClosingKe !== '2') {
            document.getElementById('decPremi').value = 0;
            return;
        }

        // Total I + A + T + HC (P1 + P2)
        // P1 diambil dari field int*Lalu
        const intIzinLalu = parseFloat(document.getElementById('intIzinLalu').value) || 0;
        const intAlphaLalu = parseFloat(document.getElementById('intAlphaLalu').value) || 0;
        const intTelatLalu = parseFloat(document.getElementById('intTelatLalu').value) || 0;
        const intHcLalu = parseFloat(document.getElementById('intHcLalu').value) || 0;

        const totalIATHC = intIzinLalu + (parseFloat(document.getElementById('intJmlIzin').value) || 0) + // Izin Pribadi (P1 + P2)
            intAlphaLalu + (parseFloat(document.getElementById('intJmlAlpha').value) || 0) + // Alpha (P1 + P2)
            intTelatLalu + (parseFloat(document.getElementById('intJmlTelat').value) || 0) + // Telat (P1 + P2)
            intHcLalu + (parseFloat(document.getElementById('intHC').value) || 0); // HC (P1 + P2)

        // Update display total
        document.getElementById('totalIATHC').textContent = totalIATHC;

        // Hitung premi
        let premi = 0;
        if (totalIATHC === 0) {
            premi = tarifPremi; // Full premi
        } else if (totalIATHC === 1) {
            premi = tarifPremi / 2; // Setengah premi
        } else {
            premi = 0; // Tidak dapat premi
        }

        document.getElementById('decPremi').value = premi.toFixed(2);
    }

    // Event listeners untuk auto-calculate premi hadir
    document.getElementById('vcClosingKe').addEventListener('change', function() {
        const vcClosingKe = this.value;
        if (vcClosingKe === '2') {
            const nik = document.getElementById('vcNik').value;
            const periode = document.getElementById('periode').value;
            if (nik && periode) {
                loadAbsensiP1(nik);
            }
        } else {
            document.getElementById('absensiP1Section').classList.add('d-none');
            document.getElementById('decPremi').value = 0;
        }
    });

    document.getElementById('intJmlIzin').addEventListener('input', calculatePremiHadir);
    document.getElementById('intJmlAlpha').addEventListener('input', calculatePremiHadir);
    document.getElementById('intJmlTelat').addEventListener('input', calculatePremiHadir);
    document.getElementById('intHC').addEventListener('input', calculatePremiHadir);

    // Load absensi P1 saat periode berubah (jika periode 2)
    document.getElementById('periode').addEventListener('change', function() {
        const vcClosingKe = document.getElementById('vcClosingKe').value;
        const nik = document.getElementById('vcNik').value;
        if (vcClosingKe === '2' && nik) {
            loadAbsensiP1(nik);
        }
    });
</script>
@endpush