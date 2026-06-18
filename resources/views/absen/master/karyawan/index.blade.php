@extends('layouts.app')

@section('title', 'Master Karyawan - HRIS Seven Payroll')

@section('content')
<style>
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #f8f9fa;
    }

    .table-responsive {
        border-radius: 0.375rem;
    }

    .karyawan-row:hover {
        cursor: pointer;
        background-color: #f8f9fa;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-users me-2"></i>Master Karyawan
                </h2>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-success" id="newBtn">
                        <i class="fas fa-file me-1"></i>Baru
                    </button>
                    <button type="button" class="btn btn-info" id="copyDataBtn" disabled>
                        <i class="fas fa-copy me-1"></i>Copy Data
                    </button>
                    <button type="button" class="btn btn-primary" id="saveBtn" disabled>
                        <i class="fas fa-save me-1"></i>Simpan
                    </button>
                    <button type="button" class="btn btn-secondary" id="cancelBtn" disabled>
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-danger" id="deleteBtn" disabled>
                        <i class="fas fa-trash me-1"></i>Hapus
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="printBiodataBtn" disabled title="Pratinjau &amp; cetak biodata (arsip CV)">
                        <i class="fas fa-file-alt me-1"></i>Biodata Cetak
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Panel - Employee Details -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form id="karyawanForm">
                        <!-- Header Section -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="nik" class="form-label">NIK <span class="text-danger">*</span> <small class="text-muted">(Otomatis jika kosong)</small></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="nik" name="Nik" maxlength="24" placeholder="Akan di-generate otomatis" readonly>
                                        <div class="input-group-text">
                                            <input class="form-check-input" type="checkbox" id="aktif" name="vcAktif" value="1">
                                            <label class="form-check-label ms-1" for="aktif">Aktif</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span> <small class="text-muted">(Otomatis)</small></label>
                                    <input type="text" class="form-control" id="nama_lengkap" name="Nama" required maxlength="150" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="no_ktp" class="form-label">No KTP</label>
                                    <input type="text" class="form-control" id="no_ktp" name="intNoBadge" maxlength="30">
                                </div>
                            </div>
                        </div>

                        <!-- Photo Placeholder -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="border rounded mb-2 position-relative" id="photoPreview" style="width: 120px; height: 150px; margin: 0 auto; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 0;">
                                        <img id="photoImage" src="" alt="Foto Karyawan" style="width: 100%; height: 100%; display: none; object-fit: contain;">
                                        <i class="fas fa-user fa-3x text-muted" id="photoIcon"></i>
                                    </div>
                                    <input type="file" id="photoInput" name="photo" accept="image/*" style="display: none;">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="changePhotoBtn">CHANGE</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="removePhotoBtn" style="display: none;">HAPUS</button>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Navigation -->
                        <ul class="nav nav-tabs" id="karyawanTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">
                                    Personal
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="fisik-tab" data-bs-toggle="tab" data-bs-target="#fisik" type="button" role="tab">
                                    Fisik
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pekerjaan-tab" data-bs-toggle="tab" data-bs-target="#pekerjaan" type="button" role="tab">
                                    Pekerjaan
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="mutasi-tab" data-bs-toggle="tab" data-bs-target="#mutasi" type="button" role="tab">
                                    Mutasi
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pendidikan-tab" data-bs-toggle="tab" data-bs-target="#pendidikan" type="button" role="tab">
                                    Pendidikan
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="keluarga-tab" data-bs-toggle="tab" data-bs-target="#keluarga" type="button" role="tab">
                                    Keluarga
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pelatihan-tab" data-bs-toggle="tab" data-bs-target="#pelatihan" type="button" role="tab">
                                    Pelatihan
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="catatan-karyawan-tab" data-bs-toggle="tab" data-bs-target="#catatan-karyawan" type="button" role="tab">
                                    Catatan Karyawan
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content mt-3" id="karyawanTabContent">
                            <!-- Personal Tab -->
                            <div class="tab-pane fade show active" id="personal" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="nama_depan" class="form-label">Nama Depan</label>
                                            <input type="text" class="form-control" id="nama_depan" name="Nama_Depan" maxlength="75">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="nama_tengah" class="form-label">Nama Tengah</label>
                                            <input type="text" class="form-control" id="nama_tengah" name="Nama_Tengah" maxlength="75">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="nama_akhir" class="form-label">Nama Akhir</label>
                                            <input type="text" class="form-control" id="nama_akhir" name="Nama_Akhir" maxlength="75">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                                            <input type="text" class="form-control" id="tempat_lahir" name="Tempat_lahir" maxlength="75">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                            <input type="date" class="form-control" id="tanggal_lahir" name="TTL">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="status_kawin" class="form-label">Status Kawin</label>
                                            <select class="form-select" id="status_kawin" name="Status_Kawin">
                                                <option value="">Pilih Status</option>
                                                <option value="Belum Kawin">Belum Kawin</option>
                                                <option value="Kawin">Kawin</option>
                                                <option value="Cerai">Cerai</option>
                                                <option value="Janda">Janda</option>
                                                <option value="Duda">Duda</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                            <select class="form-select" id="jenis_kelamin" name="Jenis_Kelamin">
                                                <option value="">Pilih Jenis Kelamin</option>
                                                <option value="Laki-laki">Laki-laki</option>
                                                <option value="Perempuan">Perempuan</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="agama" class="form-label">Agama</label>
                                            <select class="form-select" id="agama" name="Agama">
                                                <option value="">Pilih Agama</option>
                                                <option value="Islam">Islam</option>
                                                <option value="Kristen">Kristen</option>
                                                <option value="Katolik">Katolik</option>
                                                <option value="Hindu">Hindu</option>
                                                <option value="Buddha">Buddha</option>
                                                <option value="Konghucu">Konghucu</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="warga_negara" class="form-label">Warga Negara</label>
                                            <select class="form-select" id="warga_negara" name="Warga_Negara">
                                                <option value="">Pilih Warga Negara</option>
                                                <option value="Indonesia">Indonesia</option>
                                                <option value="Asing">Asing</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="alamat" class="form-label">Alamat</label>
                                            <textarea class="form-control" id="alamat" name="Alamat" rows="3" maxlength="150"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="kecamatan" class="form-label">Kecamatan</label>
                                            <input type="text" class="form-control" id="kecamatan" name="Kecamatan" maxlength="150">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="kota" class="form-label">Kota</label>
                                            <input type="text" class="form-control" id="kota" name="Kota" maxlength="75">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="kode_pos" class="form-label">Kode Pos</label>
                                            <input type="text" class="form-control" id="kode_pos" name="Kode_pos" maxlength="255">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="telepon" class="form-label">Telepon</label>
                                            <input type="text" class="form-control" id="telepon" name="Telp" maxlength="75">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="hp1" class="form-label">No HP 1</label>
                                            <input type="text" class="form-control" id="hp1" name="Cell_Phone1" maxlength="45">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="hp2" class="form-label">No HP 2</label>
                                            <input type="text" class="form-control" id="hp2" name="Cell_Phone2" maxlength="45">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="Personal_Email" maxlength="75">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="no_rekening" class="form-label">No. Rekening</label>
                                            <input type="text" class="form-control" id="no_rekening" name="intNorek">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Fisik Tab -->
                            <div class="tab-pane fade" id="fisik" role="tabpanel">
                                <h6 class="mb-3">Info Medis</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="tinggi_badan" class="form-label">Tinggi Badan (cm)</label>
                                            <input type="text" class="form-control" id="tinggi_badan" name="Tinggi_bdn" maxlength="255">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="berat_badan" class="form-label">Berat Badan (kg)</label>
                                            <input type="text" class="form-control" id="berat_badan" name="Berat_bdn" maxlength="255">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="golongan_darah" class="form-label">Gol. Darah</label>
                                            <select class="form-select" id="golongan_darah" name="Gol_Darah">
                                                <option value="">Pilih Golongan Darah</option>
                                                <option value="A">A</option>
                                                <option value="B">B</option>
                                                <option value="AB">AB</option>
                                                <option value="O">O</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="berkacamata" name="Berkacamata" value="1">
                                            <label class="form-check-label" for="berkacamata">Berkacamata</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="buta_warna" name="Buta_Warna" value="1">
                                            <label class="form-check-label" for="buta_warna">Buta Warna</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="cacat_fisik" name="Cacat_Fisik" value="1">
                                            <label class="form-check-label" for="cacat_fisik">Cacat Fisik</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pekerjaan Tab -->
                            <div class="tab-pane fade" id="pekerjaan" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="divisi" class="form-label">Divisi</label>
                                            <select class="form-select" id="divisi" name="Divisi">
                                                <option value="">Pilih Divisi</option>
                                                @foreach($divisis as $divisi)
                                                <option value="{{ $divisi->vcKodeDivisi }}">{{ $divisi->vcNamaDivisi }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="departemen" class="form-label">Departemen</label>
                                            <select class="form-select" id="departemen" name="dept" disabled>
                                                <option value="">Pilih Divisi terlebih dahulu</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="bagian" class="form-label">Bagian</label>
                                            <select class="form-select" id="bagian" name="vcKodeBagian" disabled>
                                                <option value="">Pilih Departemen terlebih dahulu</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="seksi" class="form-label">Seksi</label>
                                            <select class="form-select" id="seksi" name="vcKodeSeksi" disabled>
                                                <option value="">Pilih Bagian terlebih dahulu</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="golongan" class="form-label">Golongan</label>
                                            <select class="form-select" id="golongan" name="Gol">
                                                <option value="">Pilih Golongan</option>
                                                @foreach($golongans as $golongan)
                                                <option value="{{ $golongan->vcKodeGolongan }}">
                                                    {{ $golongan->vcKodeGolongan }} - {{ $golongan->vcNamaGolongan }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="jabatan" class="form-label">Jabatan</label>
                                            <select class="form-select" id="jabatan" name="Jabat">
                                                <option value="">Pilih Jabatan</option>
                                                @foreach($jabatans as $jabatan)
                                                <option value="{{ $jabatan->vcKodeJabatan }}">
                                                    {{ $jabatan->vcKodeJabatan }} - {{ $jabatan->vcNamaJabatan }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="group_pegawai" class="form-label">Group Pegawai</label>
                                            <select class="form-select" id="group_pegawai" name="Group_pegawai">
                                                <option value="">Pilih Group Pegawai</option>
                                                @foreach($groupPegawais as $group)
                                                <option value="{{ $group }}">{{ ucfirst($group) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="status_pegawai" class="form-label">Status Pegawai</label>
                                            <select class="form-select" id="status_pegawai" name="Status_Pegawai">
                                                <option value="">Pilih Status Pegawai</option>
                                                @foreach($statusPegawais as $status)
                                                <option value="{{ $status }}">{{ $status }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="shift" class="form-label">Shift</label>
                                            <select class="form-select" id="shift" name="vcShift">
                                                <option value="">Pilih Shift</option>
                                                @foreach($shifts as $shift)
                                                <option value="{{ $shift->vcShift }}">
                                                    {{ $shift->vcShift }} - {{ $shift->vcMasuk ? \Carbon\Carbon::parse($shift->vcMasuk)->format('H:i') : '' }} - {{ $shift->vcPulang ? \Carbon\Carbon::parse($shift->vcPulang)->format('H:i') : '' }}
                                                    @if($shift->vcKeterangan) ({{ $shift->vcKeterangan }}) @endif
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                                            <input type="date" class="form-control" id="tanggal_masuk" name="Tgl_Masuk">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tanggal_berhenti" class="form-label">Tanggal Berhenti</label>
                                            <input type="date" class="form-control" id="tanggal_berhenti" name="Tgl_Berhenti">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mutasi Tab -->
                            <div class="tab-pane fade" id="mutasi" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Riwayat Mutasi</h6>
                                    <button type="button" class="btn btn-primary btn-sm" id="addMutasiBtn">
                                        <i class="fas fa-plus me-1"></i>Tambah Mutasi
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="mutasiTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="10%">No. SK</th>
                                                <th width="9%">Tgl SK</th>
                                                <th width="11%">Divisi</th>
                                                <th width="11%">Dept</th>
                                                <th width="10%">Bagian</th>
                                                <th width="10%">Seksi</th>
                                                <th width="11%">Jabatan</th>
                                                <th width="8%">Dok. SK</th>
                                                <th width="10%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="mutasiTableBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Pendidikan Tab -->
                            <div class="tab-pane fade" id="pendidikan" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Riwayat Pendidikan</h6>
                                    <button type="button" class="btn btn-primary btn-sm" id="addPendidikanBtn">
                                        <i class="fas fa-plus me-1"></i>Tambah Pendidikan
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered" id="pendidikanTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="15%">Jenjang Pendidikan</th>
                                                <th width="25%">Nama Sekolah</th>
                                                <th width="15%">Jurusan</th>
                                                <th width="10%">Tahun Masuk</th>
                                                <th width="10%">Tahun Selesai</th>
                                                <th width="10%">IPK</th>
                                                <th width="10%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="pendidikanTableBody">
                                            <!-- Pendidikan records will be added here dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Keluarga Tab -->
                            <div class="tab-pane fade" id="keluarga" role="tabpanel">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nama_ayah" class="form-label">Nama Ayah</label>
                                            <input type="text" class="form-control" id="nama_ayah" name="nama_ayah" maxlength="150">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nama_ibu" class="form-label">Nama Ibu</label>
                                            <input type="text" class="form-control" id="nama_ibu" name="nama_ibu" maxlength="150">
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Anggota Keluarga</h6>
                                    <button type="button" class="btn btn-primary btn-sm" id="addFamilyBtn">
                                        <i class="fas fa-plus me-1"></i>Tambah Anggota Keluarga
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered" id="familyTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="15%">Hubungan</th>
                                                <th width="25%">Nama</th>
                                                <th width="10%">Jenis Kelamin</th>
                                                <th width="15%">Tempat Lahir</th>
                                                <th width="15%">Tanggal Lahir</th>
                                                <th width="10%">Gol. Darah</th>
                                                <th width="10%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="familyTableBody">
                                            <!-- Family members will be added here dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Pelatihan Tab -->
                            <div class="tab-pane fade" id="pelatihan" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Riwayat Pelatihan</h6>
                                    <button type="button" class="btn btn-primary btn-sm" id="addPelatihanBtn">
                                        <i class="fas fa-plus me-1"></i>Tambah Pelatihan
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered" id="pelatihanTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="22%">Nama Pelatihan</th>
                                                <th width="15%">Penyelenggara</th>
                                                <th width="15%">Lokasi</th>
                                                <th width="10%">Tgl Pelatihan</th>
                                                <th width="10%">Tgl Selesai</th>
                                                <th width="8%">Sertifikat</th>
                                                <th width="12%">Keterangan</th>
                                                <th width="8%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="pelatihanTableBody">
                                            <!-- Pelatihan records will be added here dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Catatan Karyawan Tab (disiplin, penghargaan, catatan HR) -->
                            <div class="tab-pane fade" id="catatan-karyawan" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="mb-0">Riwayat Catatan Karyawan</h6>
                                        <small class="text-muted">SP, teguran, penghargaan, dan catatan HR lainnya</small>
                                    </div>
                                    <button type="button" class="btn btn-primary btn-sm" id="addCatatanKaryawanBtn">
                                        <i class="fas fa-plus me-1"></i>Tambah Catatan
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm" id="catatanKaryawanTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Jenis</th>
                                                <th>Kategori</th>
                                                <th>Judul</th>
                                                <th>Level</th>
                                                <th>Status</th>
                                                <th>No. Dokumen</th>
                                                <th>File</th>
                                                <th width="9%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="catatanKaryawanTableBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Panel - Search and List -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="search" class="form-label">Pencarian...</label>
                        <input type="text" class="form-control" id="search" placeholder="Masukkan NIK atau Nama">
                    </div>

                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover" id="karyawanListTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="10%">No.</th>
                                    <th width="30%">NIK</th>
                                    <th width="60%">Nama</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($karyawans as $index => $karyawan)
                                <tr data-nik="{{ $karyawan->Nik }}" class="karyawan-row">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $karyawan->Nik }}</td>
                                    <td>{{ $karyawan->Nama }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Tidak ada data karyawan</p>
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

<!-- Modal Tambah Anggota Keluarga -->
<div class="modal fade" id="addFamilyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Anggota Keluarga</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addFamilyForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="hubungan_keluarga" class="form-label">Hubungan <span class="text-danger">*</span></label>
                        <select class="form-select" id="hubungan_keluarga" name="hubKeluarga" required>
                            <option value="">Pilih Hubungan</option>
                            <option value="SPOUSE">Suami/Istri</option>
                            <option value="CHILD">Anak</option>
                            <option value="PARENT">Orang Tua</option>
                            <option value="SIBLING">Saudara</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="nama_keluarga" class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_keluarga" name="NamaKeluarga" required maxlength="25">
                    </div>
                    <div class="mb-3">
                        <label for="jenis_kelamin_keluarga" class="form-label">Jenis Kelamin</label>
                        <select class="form-select" id="jenis_kelamin_keluarga" name="jenKelamin">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="Male">Laki-laki</option>
                            <option value="Female">Perempuan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tempat_lahir_keluarga" class="form-label">Tempat Lahir</label>
                        <input type="text" class="form-control" id="tempat_lahir_keluarga" name="temLahir" maxlength="25">
                    </div>
                    <div class="mb-3">
                        <label for="tanggal_lahir_keluarga" class="form-label">Tanggal Lahir</label>
                        <input type="date" class="form-control" id="tanggal_lahir_keluarga" name="tglLahir">
                    </div>
                    <div class="mb-3">
                        <label for="golongan_darah_keluarga" class="form-label">Golongan Darah</label>
                        <select class="form-select" id="golongan_darah_keluarga" name="golDarah">
                            <option value="">Pilih Golongan Darah</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="AB">AB</option>
                            <option value="O">O</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Pendidikan -->
<div class="modal fade" id="addPendidikanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Riwayat Pendidikan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPendidikanForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="pendidikan_education_level" class="form-label">Jenjang Pendidikan <span class="text-danger">*</span></label>
                        <select class="form-select" id="pendidikan_education_level" name="education_level" required>
                            <option value="">Pilih Jenjang Pendidikan</option>
                            <option value="SD">SD</option>
                            <option value="SMP">SMP</option>
                            <option value="SMA/SMK">SMA/SMK</option>
                            <option value="D1">D1</option>
                            <option value="D2">D2</option>
                            <option value="D3">D3</option>
                            <option value="S1">S1</option>
                            <option value="S2">S2</option>
                            <option value="S3">S3</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="pendidikan_institution_name" class="form-label">Nama Sekolah</label>
                        <input type="text" class="form-control" id="pendidikan_institution_name" name="institution_name" maxlength="150" placeholder="Nama sekolah/universitas">
                    </div>
                    <div class="mb-3">
                        <label for="pendidikan_major" class="form-label">Jurusan</label>
                        <input type="text" class="form-control" id="pendidikan_major" name="major" maxlength="75" placeholder="Jurusan/Program Studi">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pendidikan_start_year" class="form-label">Tahun Masuk</label>
                                <input type="text" class="form-control" id="pendidikan_start_year" name="start_year" maxlength="4" placeholder="YYYY">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pendidikan_end_year" class="form-label">Tahun Selesai</label>
                                <input type="text" class="form-control" id="pendidikan_end_year" name="end_year" maxlength="4" placeholder="YYYY">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="pendidikan_gpa" class="form-label">IPK</label>
                        <input type="number" step="0.01" min="0" max="4" class="form-control" id="pendidikan_gpa" name="gpa" placeholder="0.00 - 4.00">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Pelatihan -->
<div class="modal fade" id="addPelatihanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Pelatihan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPelatihanForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="pelatihan_training_name" class="form-label">Nama Pelatihan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="pelatihan_training_name" name="nm_pelatihan" maxlength="150" required>
                    </div>
                    <div class="mb-3">
                        <label for="pelatihan_provider" class="form-label">Penyelenggara</label>
                        <input type="text" class="form-control" id="pelatihan_provider" name="penyelenggara" maxlength="150">
                    </div>
                    <div class="mb-3">
                        <label for="pelatihan_location" class="form-label">Lokasi</label>
                        <input type="text" class="form-control" id="pelatihan_location" name="lokasi" maxlength="150">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pelatihan_start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="pelatihan_start_date" name="tg_pelatihan">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pelatihan_end_date" class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="pelatihan_end_date" name="tg_selesai">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="pelatihan_certificate" name="sertifikat" value="1">
                        <label class="form-check-label" for="pelatihan_certificate">Memiliki Sertifikat</label>
                    </div>
                    <div class="mb-3">
                        <label for="pelatihan_notes" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="pelatihan_notes" name="keterangan" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah / Edit Catatan Karyawan -->
<div class="modal fade" id="addCatatanKaryawanModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Catatan Karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCatatanKaryawanForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ck_tanggal" class="form-label">Tanggal kejadian / dokumen</label>
                                <input type="date" class="form-control" id="ck_tanggal" name="tanggal">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ck_status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="ck_status" name="status" required>
                                    <option value="Aktif">Aktif</option>
                                    <option value="Selesai">Selesai</option>
                                    <option value="Dibatalkan">Dibatalkan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ck_jenis" class="form-label">Jenis <span class="text-danger">*</span></label>
                                <select class="form-select" id="ck_jenis" name="jenis" required>
                                    <option value="SP">SP</option>
                                    <option value="Teguran">Teguran</option>
                                    <option value="Peringatan Lisan">Peringatan Lisan</option>
                                    <option value="Penghargaan">Penghargaan</option>
                                    <option value="Catatan">Catatan</option>
                                    <option value="Pelanggaran">Pelanggaran</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ck_kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select" id="ck_kategori" name="kategori" required>
                                    <option value="Disiplin">Disiplin</option>
                                    <option value="Penghargaan">Penghargaan</option>
                                    <option value="Informasi">Informasi</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="ck_judul" class="form-label">Judul <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ck_judul" name="judul" maxlength="255" required>
                    </div>
                    <div class="mb-3">
                        <label for="ck_deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="ck_deskripsi" name="deskripsi" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ck_level" class="form-label">Level</label>
                                <select class="form-select" id="ck_level" name="level">
                                    <option value="Non-SP">Non-SP</option>
                                    <option value="SP1">SP1</option>
                                    <option value="SP2">SP2</option>
                                    <option value="SP3">SP3</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ck_no_dokumen" class="form-label">No. dokumen / surat</label>
                                <input type="text" class="form-control" id="ck_no_dokumen" name="no_dokumen" maxlength="100">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ck_tanggal_berlaku" class="form-label">Tanggal berlaku</label>
                                <input type="date" class="form-control" id="ck_tanggal_berlaku" name="tanggal_berlaku">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ck_tanggal_berakhir" class="form-label">Tanggal berakhir (khusus SP)</label>
                                <input type="date" class="form-control" id="ck_tanggal_berakhir" name="tanggal_berakhir">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="ck_file_lampiran" class="form-label">Lampiran (PDF / JPG / PNG, maks. 5 MB)</label>
                        <input type="file" class="form-control" id="ck_file_lampiran" name="file_lampiran" accept=".pdf,.jpg,.jpeg,.png">
                        <div id="ck_file_existing_info" class="form-text mt-1"></div>
                        <div class="form-check mt-2 d-none" id="ck_remove_file_wrap">
                            <input class="form-check-input" type="checkbox" id="ck_remove_file" name="remove_file_lampiran" value="1">
                            <label class="form-check-label text-danger" for="ck_remove_file">Hapus lampiran yang tersimpan</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah / Edit Mutasi (t_mutasi: NoSK, vcTglSK, organisasi, vcJabatan, vcFileSK) -->
<div class="modal fade" id="addMutasiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Mutasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addMutasiForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mutasi_no_sk" class="form-label">No. SK <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="mutasi_no_sk" name="no_sk" maxlength="20" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mutasi_vc_tgl_sk" class="form-label">Tgl SK (vcTglSK)</label>
                                <input type="date" class="form-control" id="mutasi_vc_tgl_sk" name="vc_tgl_sk">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mutasi_vc_divisi" class="form-label">Divisi</label>
                                <select class="form-select" id="mutasi_vc_divisi" name="vc_divisi">
                                    <option value="">Pilih Divisi</option>
                                    @foreach($divisis as $divisi)
                                    <option value="{{ $divisi->vcKodeDivisi }}">{{ $divisi->vcNamaDivisi }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mutasi_vc_dept" class="form-label">Departemen</label>
                                <select class="form-select" id="mutasi_vc_dept" name="vc_dept" disabled>
                                    <option value="">Pilih Divisi terlebih dahulu</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mutasi_vcbagian" class="form-label">Bagian</label>
                                <select class="form-select" id="mutasi_vcbagian" name="vcbagian" disabled>
                                    <option value="">Pilih Departemen terlebih dahulu</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mutasi_vc_seksi" class="form-label">Seksi</label>
                                <select class="form-select" id="mutasi_vc_seksi" name="vc_seksi" disabled>
                                    <option value="">Pilih Bagian terlebih dahulu</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="mutasi_vc_jabatan" class="form-label">Jabatan</label>
                                <select class="form-select" id="mutasi_vc_jabatan" name="vc_jabatan" disabled>
                                    <option value="">Pilih Divisi terlebih dahulu</option>
                                </select>
                                <div class="form-text">Daftar mengikuti divisi (kode jabatan JRMA / JSIA / JSMU).</div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="mutasi_dokumen_sk" class="form-label">Dokumen SK (PDF / JPG / PNG, maks. 5 MB)</label>
                        <input type="file" class="form-control" id="mutasi_dokumen_sk" name="dokumen_sk" accept=".pdf,.jpg,.jpeg,.png">
                        <div id="mutasi_sk_existing_info" class="form-text mt-1"></div>
                        <div class="form-check mt-2 d-none" id="mutasi_remove_sk_wrap">
                            <input class="form-check-input" type="checkbox" id="mutasi_remove_sk_file" name="remove_sk_file" value="1">
                            <label class="form-check-label text-danger" for="mutasi_remove_sk_file">Hapus dokumen SK yang tersimpan</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Base path untuk subfolder support
        // Extract path dari URL (exclude domain dan port)
        // Contoh: 'http://localhost:8000' -> '', 'http://192.168.10.40/hris-seven-payroll' -> '/hris-seven-payroll'
        const fullUrl = '{{ url("/") }}';
        // Regex: hapus protocol (http:// atau https://) dan domain:port, sisakan path saja
        const basePath = fullUrl.replace(/^https?:\/\/[^\/]+/, '') || '';

        // Helper function untuk membuat URL dengan basePath
        function makeUrl(path) {
            // Hapus leading slash dari path jika ada
            const cleanPath = path.startsWith('/') ? path.substring(1) : path;
            // Jika basePath kosong (localhost), gunakan path relatif dengan leading slash
            // Jika basePath ada (subfolder), gabungkan dengan basePath
            if (!basePath) {
                return `/${cleanPath}`;
            }
            // Pastikan basePath tidak double slash
            const cleanBasePath = basePath.endsWith('/') ? basePath.slice(0, -1) : basePath;
            return `${cleanBasePath}/${cleanPath}`;
        }

        let currentNik = null;
        let isEditMode = false;
        let familyMembers = [];
        let pendidikanMembers = [];
        let pelatihanMembers = [];
        let mutasiMembers = [];
        let catatanMembers = [];
        let currentFamilyEditIndex = null;
        let currentPendidikanEditIndex = null;
        let currentPelatihanEditIndex = null;
        let currentMutasiEditIndex = null;
        let currentCatatanEditIndex = null;

        // Hierarchical dropdown elements
        const divisiSelect = document.getElementById('divisi');
        const departemenSelect = document.getElementById('departemen');
        const bagianSelect = document.getElementById('bagian');
        const jabatanSelect = document.getElementById('jabatan');
        
        // Store initial jabatans for reset functionality
        const initialJabatans = Array.from(jabatanSelect.options).map(opt => ({
            value: opt.value,
            text: opt.text
        }));

        // Modal instances
        const familyModalEl = document.getElementById('addFamilyModal');
        const pendidikanModalEl = document.getElementById('addPendidikanModal');
        const pelatihanModalEl = document.getElementById('addPelatihanModal');
        const mutasiModalEl = document.getElementById('addMutasiModal');
        const familyModal = familyModalEl ? new bootstrap.Modal(familyModalEl) : null;
        const pendidikanModal = pendidikanModalEl ? new bootstrap.Modal(pendidikanModalEl) : null;
        const pelatihanModal = pelatihanModalEl ? new bootstrap.Modal(pelatihanModalEl) : null;
        const mutasiModal = mutasiModalEl ? new bootstrap.Modal(mutasiModalEl) : null;
        const catatanModalEl = document.getElementById('addCatatanKaryawanModal');
        const catatanModal = catatanModalEl ? new bootstrap.Modal(catatanModalEl) : null;

        const mutasiDivisiSelect = document.getElementById('mutasi_vc_divisi');
        const mutasiDepartemenSelect = document.getElementById('mutasi_vc_dept');
        const mutasiBagianSelect = document.getElementById('mutasi_vcbagian');
        const mutasiJabatanSelect = document.getElementById('mutasi_vc_jabatan');
        const mutasiSeksiSelect = document.getElementById('mutasi_vc_seksi');
        const MUTASI_DIVISI_LABELS = @json($divisis->pluck('vcNamaDivisi', 'vcKodeDivisi'));
        const MUTASI_JABATAN_LABELS = @json($jabatans->pluck('vcNamaJabatan', 'vcKodeJabatan'));
        const MUTASI_SEKSI_LABELS = @json(\App\Models\Seksi::query()->orderBy('vcKodeseksi')->pluck('vcNamaseksi', 'vcKodeseksi'));

        function formatDateForInput(value) {
            if (!value) return '';
            const date = new Date(value);
            if (!isNaN(date.getTime())) {
                return date.toISOString().split('T')[0];
            }
            if (typeof value === 'string') {
                if (value.includes('T')) {
                    return value.split('T')[0];
                }
                if (value.includes(' ')) {
                    return value.split(' ')[0];
                }
                return value.substring(0, 10);
            }
            return '';
        }

        function resetFamilyModalState() {
            currentFamilyEditIndex = null;
            const modalTitle = familyModalEl?.querySelector('.modal-title');
            const submitBtn = document.querySelector('#addFamilyForm button[type="submit"]');
            if (modalTitle) modalTitle.textContent = 'Tambah Anggota Keluarga';
            if (submitBtn) submitBtn.textContent = 'Simpan';
        }

        function resetPendidikanModalState() {
            currentPendidikanEditIndex = null;
            const modalTitle = pendidikanModalEl?.querySelector('.modal-title');
            const submitBtn = document.querySelector('#addPendidikanForm button[type="submit"]');
            if (modalTitle) modalTitle.textContent = 'Tambah Riwayat Pendidikan';
            if (submitBtn) submitBtn.textContent = 'Simpan';
        }

        function resetPelatihanModalState() {
            currentPelatihanEditIndex = null;
            const modalTitle = pelatihanModalEl?.querySelector('.modal-title');
            const submitBtn = document.querySelector('#addPelatihanForm button[type="submit"]');
            if (modalTitle) modalTitle.textContent = 'Tambah Pelatihan';
            if (submitBtn) submitBtn.textContent = 'Simpan';
        }

        function resetMutasiOrganisasiDropdowns() {
            if (mutasiDepartemenSelect && mutasiBagianSelect) {
                mutasiDepartemenSelect.innerHTML = '<option value="">Pilih Divisi terlebih dahulu</option>';
                mutasiDepartemenSelect.disabled = true;
                mutasiBagianSelect.innerHTML = '<option value="">Pilih Departemen terlebih dahulu</option>';
                mutasiBagianSelect.disabled = true;
            }
            if (mutasiJabatanSelect) {
                mutasiJabatanSelect.innerHTML = '<option value="">Pilih Divisi terlebih dahulu</option>';
                mutasiJabatanSelect.disabled = true;
            }
            if (mutasiSeksiSelect) {
                mutasiSeksiSelect.innerHTML = '<option value="">Pilih Bagian terlebih dahulu</option>';
                mutasiSeksiSelect.disabled = true;
            }
        }

        function syncMutasiOrganisasiFieldsEnabled() {
            if (!mutasiDivisiSelect) return;
            const hasDiv = !!mutasiDivisiSelect.value;
            const hasDept = mutasiDepartemenSelect && !!mutasiDepartemenSelect.value;
            const hasBag = mutasiBagianSelect && !!mutasiBagianSelect.value;
            if (mutasiDepartemenSelect) mutasiDepartemenSelect.disabled = !hasDiv;
            if (mutasiBagianSelect) mutasiBagianSelect.disabled = !(hasDiv && hasDept);
            if (mutasiSeksiSelect) mutasiSeksiSelect.disabled = !(hasDiv && hasDept && hasBag);
            if (mutasiJabatanSelect) mutasiJabatanSelect.disabled = !hasDiv;
        }

        function ensureMutasiSelectOption(select, value, label) {
            if (!select || value === null || value === undefined || String(value).trim() === '') return;
            const v = String(value);
            const exists = Array.from(select.options).some(o => o.value === v);
            if (!exists) {
                const opt = document.createElement('option');
                opt.value = v;
                opt.textContent = label || (v + ' (data lama)');
                select.appendChild(opt);
            }
        }

        function loadMutasiDepartemens(divisiKode, callback) {
            if (!mutasiDepartemenSelect) return;
            mutasiDepartemenSelect.disabled = true;
            mutasiDepartemenSelect.innerHTML = '<option value="">Memuat...</option>';

            fetch(makeUrl('karyawan/get-departemens'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ divisi: divisiKode })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mutasiDepartemenSelect.innerHTML = '<option value="">Pilih Departemen</option>';
                        data.departemens.forEach(dept => {
                            const option = document.createElement('option');
                            option.value = dept.vcKodeDept;
                            option.textContent = dept.vcKodeDept + ' - ' + dept.vcNamaDept;
                            mutasiDepartemenSelect.appendChild(option);
                        });
                        mutasiDepartemenSelect.disabled = false;
                    } else {
                        mutasiDepartemenSelect.innerHTML = '<option value="">Tidak ada data</option>';
                        mutasiDepartemenSelect.disabled = true;
                    }
                    if (callback) callback();
                })
                .catch(error => {
                    console.error('Error loading mutasi departemens:', error);
                    mutasiDepartemenSelect.innerHTML = '<option value="">Error memuat data</option>';
                    mutasiDepartemenSelect.disabled = true;
                    if (callback) callback();
                });
        }

        function loadMutasiBagians(divisiKode, deptKode, callback) {
            if (!mutasiBagianSelect) return;
            mutasiBagianSelect.disabled = true;
            mutasiBagianSelect.innerHTML = '<option value="">Memuat...</option>';

            fetch(makeUrl('karyawan/get-bagians'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ divisi: divisiKode, departemen: deptKode })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mutasiBagianSelect.innerHTML = '<option value="">Pilih Bagian</option>';
                        data.bagians.forEach(bagian => {
                            const option = document.createElement('option');
                            option.value = bagian.vcKodeBagian;
                            option.textContent = bagian.vcKodeBagian + ' - ' + bagian.vcNamaBagian;
                            mutasiBagianSelect.appendChild(option);
                        });
                        mutasiBagianSelect.disabled = false;
                    } else {
                        mutasiBagianSelect.innerHTML = '<option value="">Tidak ada data</option>';
                        mutasiBagianSelect.disabled = true;
                    }
                    if (callback) callback();
                })
                .catch(error => {
                    console.error('Error loading mutasi bagians:', error);
                    mutasiBagianSelect.innerHTML = '<option value="">Error memuat data</option>';
                    mutasiBagianSelect.disabled = true;
                    if (callback) callback();
                });
        }

        function loadMutasiSeksis(divisiKode, deptKode, bagianKode, callback) {
            if (!mutasiSeksiSelect) return;
            mutasiSeksiSelect.disabled = true;
            mutasiSeksiSelect.innerHTML = '<option value="">Memuat...</option>';

            fetch(makeUrl('karyawan/get-seksis'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        divisi: divisiKode,
                        departemen: deptKode,
                        bagian: bagianKode
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mutasiSeksiSelect.innerHTML = '<option value="">Pilih Seksi</option>';
                        data.seksis.forEach(seksi => {
                            const option = document.createElement('option');
                            option.value = seksi.vcKodeSeksi;
                            option.textContent = seksi.vcKodeSeksi + ' - ' + seksi.vcNamaSeksi;
                            mutasiSeksiSelect.appendChild(option);
                        });
                        mutasiSeksiSelect.disabled = false;
                    } else {
                        mutasiSeksiSelect.innerHTML = '<option value="">Tidak ada data</option>';
                        mutasiSeksiSelect.disabled = true;
                    }
                    if (callback) callback();
                })
                .catch(error => {
                    console.error('Error loading mutasi seksis:', error);
                    mutasiSeksiSelect.innerHTML = '<option value="">Error memuat data</option>';
                    mutasiSeksiSelect.disabled = true;
                    if (callback) callback();
                });
        }

        function loadMutasiJabatans(divisiKode, callback) {
            if (!mutasiJabatanSelect) return;
            mutasiJabatanSelect.disabled = true;
            mutasiJabatanSelect.innerHTML = '<option value="">Memuat...</option>';

            fetch(makeUrl('karyawan/get-jabatans'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ divisi: divisiKode })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mutasiJabatanSelect.innerHTML = '<option value="">Pilih Jabatan</option>';
                        data.jabatans.forEach(j => {
                            const option = document.createElement('option');
                            option.value = j.vcKodeJabatan;
                            option.textContent = j.vcKodeJabatan + ' - ' + j.vcNamaJabatan;
                            mutasiJabatanSelect.appendChild(option);
                        });
                        mutasiJabatanSelect.disabled = false;
                    } else {
                        mutasiJabatanSelect.innerHTML = '<option value="">Tidak ada data</option>';
                        mutasiJabatanSelect.disabled = true;
                    }
                    if (callback) callback();
                })
                .catch(error => {
                    console.error('Error loading mutasi jabatans:', error);
                    mutasiJabatanSelect.innerHTML = '<option value="">Error memuat data</option>';
                    mutasiJabatanSelect.disabled = true;
                    if (callback) callback();
                });
        }

        function resetMutasiModalState() {
            currentMutasiEditIndex = null;
            const modalTitle = mutasiModalEl?.querySelector('.modal-title');
            const submitBtn = document.querySelector('#addMutasiForm button[type="submit"]');
            if (modalTitle) modalTitle.textContent = 'Tambah Mutasi';
            if (submitBtn) submitBtn.textContent = 'Simpan';
            const skInfo = document.getElementById('mutasi_sk_existing_info');
            if (skInfo) skInfo.innerHTML = '';
            const removeWrap = document.getElementById('mutasi_remove_sk_wrap');
            if (removeWrap) removeWrap.classList.add('d-none');
            const removeCb = document.getElementById('mutasi_remove_sk_file');
            if (removeCb) removeCb.checked = false;
            resetMutasiOrganisasiDropdowns();
        }

        function normalizeMutasiRow(m) {
            const noSk = m.NoSK ?? m.no_sk ?? '';
            return {
                no_sk: noSk,
                original_no_sk: noSk,
                vc_tgl_sk: formatDateForInput(m.vcTglSK ?? m.vc_tgl_sk),
                vc_divisi: m.vcDivisi ?? m.vc_divisi ?? '',
                vc_dept: m.vcDept ?? m.vc_dept ?? '',
                vcbagian: m.vcbagian ?? '',
                vc_seksi: m.vcSeksi ?? m.vc_seksi ?? '',
                vc_jabatan: m.vcJabatan ?? m.vc_jabatan ?? '',
                vc_file_sk: m.vcFileSK ?? m.vc_file_sk ?? '',
                sk_file_url: m.sk_file_url || null,
            };
        }

        function updateMutasiTable() {
            const tbody = document.getElementById('mutasiTableBody');
            if (!tbody) return;
            tbody.innerHTML = '';

            if (!mutasiMembers.length) {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="9" class="text-center text-muted">Belum ada data mutasi.</td>`;
                tbody.appendChild(tr);
                return;
            }

            mutasiMembers.forEach((item, index) => {
                const tr = document.createElement('tr');
                const skLink = item.sk_file_url
                    ? `<a href="${item.sk_file_url}" target="_blank" rel="noopener">Unduh</a>`
                    : '—';
                const divLabel = (item.vc_divisi && MUTASI_DIVISI_LABELS[item.vc_divisi]) ? MUTASI_DIVISI_LABELS[item.vc_divisi] : (item.vc_divisi || '—');
                const jabLabel = (item.vc_jabatan && MUTASI_JABATAN_LABELS[item.vc_jabatan]) ? MUTASI_JABATAN_LABELS[item.vc_jabatan] : (item.vc_jabatan || '—');
                const sekLabel = (item.vc_seksi && MUTASI_SEKSI_LABELS[item.vc_seksi]) ? MUTASI_SEKSI_LABELS[item.vc_seksi] : (item.vc_seksi || '—');
                tr.innerHTML = `
                    <td>${item.no_sk || '-'}</td>
                    <td>${item.vc_tgl_sk || '-'}</td>
                    <td>${divLabel}</td>
                    <td>${item.vc_dept || '-'}</td>
                    <td>${item.vcbagian || '-'}</td>
                    <td>${sekLabel}</td>
                    <td>${jabLabel}</td>
                    <td class="text-center">${skLink}</td>
                    <td class="text-center">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="editMutasiMember(${index}); return false;"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="removeMutasiMember(${index}); return false;"><i class="fas fa-trash me-2"></i>Hapus</a></li>
                            </ul>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function loadMutasiData(nik) {
            if (!nik) {
                mutasiMembers = [];
                updateMutasiTable();
                return;
            }
            fetch(makeUrl(`karyawan/${nik}/mutasi`), {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        mutasiMembers = (data.mutasi || []).map(normalizeMutasiRow);
                        updateMutasiTable();
                    } else {
                        mutasiMembers = [];
                        updateMutasiTable();
                    }
                })
                .catch(err => {
                    console.error('Error loading mutasi:', err);
                    mutasiMembers = [];
                    updateMutasiTable();
                });
        }

        function removeMutasiMember(index) {
            const row = mutasiMembers[index];
            if (!row || !currentNik) {
                if (row) mutasiMembers.splice(index, 1);
                updateMutasiTable();
                return;
            }

            if (!row.original_no_sk && !row.no_sk) {
                mutasiMembers.splice(index, 1);
                updateMutasiTable();
                return;
            }

            if (!confirm('Hapus data mutasi ini?')) return;

            const sk = encodeURIComponent(row.original_no_sk || row.no_sk);
            fetch(makeUrl(`karyawan/${currentNik}/mutasi/${sk}`), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        mutasiMembers.splice(index, 1);
                        updateMutasiTable();
                    } else {
                        alert('Gagal menghapus data mutasi: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error deleting mutasi:', error);
                    alert('Gagal menghapus data mutasi. Silakan coba lagi.');
                });
        }

        function editMutasiMember(index) {
            const row = mutasiMembers[index];
            if (!row) return;
            currentMutasiEditIndex = index;
            const form = document.getElementById('addMutasiForm');
            form.no_sk.value = row.no_sk || '';
            form.vc_tgl_sk.value = row.vc_tgl_sk || '';
            const divVal = row.vc_divisi || '';
            const deptVal = row.vc_dept || '';
            const bagVal = row.vcbagian || '';
            const sekVal = row.vc_seksi || '';
            const jabVal = row.vc_jabatan || '';
            if (mutasiDivisiSelect) {
                ensureMutasiSelectOption(mutasiDivisiSelect, divVal, divVal);
                mutasiDivisiSelect.value = divVal;
            }
            if (!divVal) {
                resetMutasiOrganisasiDropdowns();
            } else {
                loadMutasiJabatans(divVal, () => {
                    ensureMutasiSelectOption(mutasiJabatanSelect, jabVal, jabVal);
                    if (mutasiJabatanSelect) mutasiJabatanSelect.value = jabVal || '';
                });
                loadMutasiDepartemens(divVal, () => {
                    ensureMutasiSelectOption(mutasiDepartemenSelect, deptVal, deptVal);
                    if (mutasiDepartemenSelect) mutasiDepartemenSelect.value = deptVal || '';
                    if (divVal && deptVal) {
                        loadMutasiBagians(divVal, deptVal, () => {
                            ensureMutasiSelectOption(mutasiBagianSelect, bagVal, bagVal);
                            if (mutasiBagianSelect) mutasiBagianSelect.value = bagVal || '';
                            if (divVal && deptVal && bagVal) {
                                loadMutasiSeksis(divVal, deptVal, bagVal, () => {
                                    ensureMutasiSelectOption(mutasiSeksiSelect, sekVal, sekVal);
                                    if (mutasiSeksiSelect) mutasiSeksiSelect.value = sekVal || '';
                                });
                            } else if (mutasiSeksiSelect) {
                                mutasiSeksiSelect.innerHTML = '<option value="">Pilih Bagian terlebih dahulu</option>';
                                mutasiSeksiSelect.disabled = true;
                            }
                        });
                    } else if (mutasiBagianSelect) {
                        mutasiBagianSelect.innerHTML = '<option value="">Pilih Bagian</option>';
                        mutasiBagianSelect.disabled = true;
                        if (mutasiSeksiSelect) {
                            mutasiSeksiSelect.innerHTML = '<option value="">Pilih Bagian terlebih dahulu</option>';
                            mutasiSeksiSelect.disabled = true;
                        }
                    }
                });
            }
            form.dokumen_sk.value = '';
            const skInfo = document.getElementById('mutasi_sk_existing_info');
            const removeWrap = document.getElementById('mutasi_remove_sk_wrap');
            const removeCb = document.getElementById('mutasi_remove_sk_file');
            if (removeCb) removeCb.checked = false;
            if (row.sk_file_url) {
                if (skInfo) skInfo.innerHTML = 'Dokumen saat ini: <a href="' + row.sk_file_url + '" target="_blank" rel="noopener">buka / unduh</a>';
                if (removeWrap) removeWrap.classList.remove('d-none');
            } else {
                if (skInfo) skInfo.innerHTML = '';
                if (removeWrap) removeWrap.classList.add('d-none');
            }

            const modalTitle = mutasiModalEl?.querySelector('.modal-title');
            const submitBtn = form.querySelector('button[type="submit"]');
            if (modalTitle) modalTitle.textContent = 'Edit Mutasi';
            if (submitBtn) submitBtn.textContent = 'Update';

            mutasiModal?.show();
        }

        function saveMutasiMembers(nikLama, nikBaru) {
            return fetch(makeUrl('karyawan/copy-mutasi'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        nik_lama: nikLama,
                        nik_baru: nikBaru
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Gagal copy data mutasi');
                    }
                    return data;
                });
        }

        function resetCatatanModalState() {
            currentCatatanEditIndex = null;
            const modalTitle = catatanModalEl?.querySelector('.modal-title');
            const submitBtn = document.querySelector('#addCatatanKaryawanForm button[type="submit"]');
            if (modalTitle) modalTitle.textContent = 'Tambah Catatan Karyawan';
            if (submitBtn) submitBtn.textContent = 'Simpan';
            const fileInfo = document.getElementById('ck_file_existing_info');
            if (fileInfo) fileInfo.innerHTML = '';
            const removeWrap = document.getElementById('ck_remove_file_wrap');
            if (removeWrap) removeWrap.classList.add('d-none');
            const removeCb = document.getElementById('ck_remove_file');
            if (removeCb) removeCb.checked = false;
        }

        function normalizeCatatanRow(c) {
            const id = c.id != null ? Number(c.id) : null;
            return {
                id,
                tanggal: formatDateForInput(c.tanggal),
                jenis: c.jenis || '',
                kategori: c.kategori || '',
                judul: c.judul || '',
                deskripsi: c.deskripsi || '',
                level: c.level || 'Non-SP',
                status: c.status || 'Aktif',
                no_dokumen: c.no_dokumen || '',
                file_lampiran: c.file_lampiran || '',
                file_url: c.file_url || null,
                tanggal_berlaku: formatDateForInput(c.tanggal_berlaku),
                tanggal_berakhir: formatDateForInput(c.tanggal_berakhir),
            };
        }

        function updateCatatanTable() {
            const tbody = document.getElementById('catatanKaryawanTableBody');
            if (!tbody) return;
            tbody.innerHTML = '';

            if (!catatanMembers.length) {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="9" class="text-center text-muted">Belum ada catatan karyawan.</td>`;
                tbody.appendChild(tr);
                return;
            }

            catatanMembers.forEach((item, index) => {
                const tr = document.createElement('tr');
                const fileLink = item.file_url
                    ? `<a href="${item.file_url}" target="_blank" rel="noopener">Unduh</a>`
                    : '—';
                tr.innerHTML = `
                    <td>${item.tanggal || '—'}</td>
                    <td>${item.jenis || '—'}</td>
                    <td>${item.kategori || '—'}</td>
                    <td>${item.judul || '—'}</td>
                    <td>${item.level || '—'}</td>
                    <td>${item.status || '—'}</td>
                    <td>${item.no_dokumen || '—'}</td>
                    <td class="text-center">${fileLink}</td>
                    <td class="text-center">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="editCatatanMember(${index}); return false;"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="removeCatatanMember(${index}); return false;"><i class="fas fa-trash me-2"></i>Hapus</a></li>
                            </ul>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function loadCatatanData(nik) {
            if (!nik) {
                catatanMembers = [];
                updateCatatanTable();
                return;
            }
            fetch(makeUrl(`karyawan/${encodeURIComponent(nik)}/catatan-karyawan`), {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        catatanMembers = (data.catatan || []).map(normalizeCatatanRow);
                        updateCatatanTable();
                    } else {
                        catatanMembers = [];
                        updateCatatanTable();
                    }
                })
                .catch(err => {
                    console.error('Error loading catatan karyawan:', err);
                    catatanMembers = [];
                    updateCatatanTable();
                });
        }

        function removeCatatanMember(index) {
            const row = catatanMembers[index];
            if (!row || !currentNik) {
                if (row) catatanMembers.splice(index, 1);
                updateCatatanTable();
                return;
            }
            if (row.id == null) {
                catatanMembers.splice(index, 1);
                updateCatatanTable();
                return;
            }
            if (!confirm('Hapus catatan ini?')) return;

            fetch(makeUrl(`karyawan/${encodeURIComponent(currentNik)}/catatan-karyawan/${row.id}`), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        catatanMembers.splice(index, 1);
                        updateCatatanTable();
                    } else {
                        alert('Gagal menghapus catatan: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error deleting catatan:', error);
                    alert('Gagal menghapus catatan. Silakan coba lagi.');
                });
        }

        function editCatatanMember(index) {
            const row = catatanMembers[index];
            if (!row) return;
            currentCatatanEditIndex = index;
            const form = document.getElementById('addCatatanKaryawanForm');
            if (!form) return;

            form.tanggal.value = row.tanggal || '';
            form.status.value = row.status || 'Aktif';
            form.jenis.value = row.jenis || 'SP';
            form.kategori.value = row.kategori || 'Disiplin';
            form.judul.value = row.judul || '';
            form.deskripsi.value = row.deskripsi || '';
            form.level.value = row.level || 'Non-SP';
            form.no_dokumen.value = row.no_dokumen || '';
            form.tanggal_berlaku.value = row.tanggal_berlaku || '';
            form.tanggal_berakhir.value = row.tanggal_berakhir || '';

            const fileInput = document.getElementById('ck_file_lampiran');
            if (fileInput) fileInput.value = '';
            const fileInfo = document.getElementById('ck_file_existing_info');
            const removeWrap = document.getElementById('ck_remove_file_wrap');
            const removeCb = document.getElementById('ck_remove_file');
            if (removeCb) removeCb.checked = false;
            if (row.file_url) {
                if (fileInfo) fileInfo.innerHTML = 'Lampiran saat ini: <a href="' + row.file_url + '" target="_blank" rel="noopener">buka / unduh</a>';
                if (removeWrap) removeWrap.classList.remove('d-none');
            } else {
                if (fileInfo) fileInfo.innerHTML = '';
                if (removeWrap) removeWrap.classList.add('d-none');
            }

            const modalTitle = catatanModalEl?.querySelector('.modal-title');
            const submitBtn = form.querySelector('button[type="submit"]');
            if (modalTitle) modalTitle.textContent = 'Edit Catatan Karyawan';
            if (submitBtn) submitBtn.textContent = 'Update';

            catatanModal?.show();
        }

        function saveCatatanKaryawanMembers(nikLama, nikBaru) {
            return fetch(makeUrl('karyawan/copy-catatan-karyawan'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        nik_lama: nikLama,
                        nik_baru: nikBaru
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Gagal copy catatan karyawan');
                    }
                    return data;
                });
        }

        function updatePelatihanTable() {
            const tbody = document.getElementById('pelatihanTableBody');
            if (!tbody) return;
            tbody.innerHTML = '';

            if (!pelatihanMembers.length) {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="8" class="text-center text-muted">Belum ada data pelatihan.</td>`;
                tbody.appendChild(tr);
                return;
            }

            pelatihanMembers.forEach((item, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${item.nm_pelatihan || '-'}</td>
                    <td>${item.penyelenggara || '-'}</td>
                    <td>${item.lokasi || '-'}</td>
                    <td>${formatDateForInput(item.tg_pelatihan) || '-'}</td>
                    <td>${formatDateForInput(item.tg_selesai) || '-'}</td>
                    <td class="text-center">
                        <span class="badge ${item.sertifikat ? 'bg-success' : 'bg-secondary'}">
                            ${item.sertifikat ? 'Ya' : 'Tidak'}
                        </span>
                    </td>
                    <td>${item.keterangan || '-'}</td>
                    <td class="text-center">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="editPelatihanMember(${index}); return false;"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="removePelatihanMember(${index}); return false;"><i class="fas fa-trash me-2"></i>Hapus</a></li>
                            </ul>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function loadPelatihanData(nik) {
            if (!nik) {
                pelatihanMembers = [];
                updatePelatihanTable();
                return;
            }
            fetch(makeUrl(`karyawan/${nik}/pelatihan`), {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        pelatihanMembers = (data.pelatihan || []).map(p => ({
                            ...p,
                            _key: p.nm_pelatihan, // key edit/delete
                            sertifikat: (p.sertifikat ?? p.Sertifikasi ?? 0) ? 1 : 0,
                            keterangan: p.keterangan ?? p.Keterangan ?? ''
                        }));
                        updatePelatihanTable();
                    } else {
                        pelatihanMembers = [];
                        updatePelatihanTable();
                    }
                })
                .catch(err => {
                    console.error('Error loading pelatihan:', err);
                    pelatihanMembers = [];
                    updatePelatihanTable();
                });
        }

        function removePelatihanMember(index) {
            const pelatihan = pelatihanMembers[index];
            if (!pelatihan || !currentNik) {
                pelatihanMembers.splice(index, 1);
                updatePelatihanTable();
                return;
            }

            if (!confirm('Hapus data pelatihan ini?')) return;

            const key = encodeURIComponent(pelatihan._key || pelatihan.nm_pelatihan);
            fetch(makeUrl(`karyawan/${currentNik}/pelatihan/${key}`), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        pelatihanMembers.splice(index, 1);
                        updatePelatihanTable();
                    } else {
                        alert('Gagal menghapus data pelatihan: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error deleting pelatihan:', error);
                    alert('Gagal menghapus data pelatihan. Silakan coba lagi.');
                });
        }

        function editPelatihanMember(index) {
            const pel = pelatihanMembers[index];
            if (!pel) return;
            currentPelatihanEditIndex = index;
            const form = document.getElementById('addPelatihanForm');
            form.nm_pelatihan.value = pel.nm_pelatihan || '';
            form.penyelenggara.value = pel.penyelenggara || '';
            form.lokasi.value = pel.lokasi || '';
            form.tg_pelatihan.value = formatDateForInput(pel.tg_pelatihan);
            form.tg_selesai.value = formatDateForInput(pel.tg_selesai);
            form.sertifikat.checked = !!pel.sertifikat;
            form.keterangan.value = pel.keterangan || '';

            const modalTitle = pelatihanModalEl?.querySelector('.modal-title');
            const submitBtn = form.querySelector('button[type="submit"]');
            if (modalTitle) modalTitle.textContent = 'Edit Pelatihan';
            if (submitBtn) submitBtn.textContent = 'Update';

            pelatihanModal?.show();
        }

        function savePelatihanMembers(nikLama, nikBaru) {
            // Copy pelatihan data from old NIK to new NIK using batch copy endpoint
            return fetch(makeUrl('karyawan/copy-pelatihan'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        nik_lama: nikLama,
                        nik_baru: nikBaru
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Gagal copy data pelatihan');
                    }
                    return data;
                });
        }

        function mapHubunganValue(value) {
            if (!value) return '';
            const normalized = value.toString().trim().toUpperCase();
            if (['SPOUSE', 'SUAMI/ISTRI', 'SUAMIISTRI', 'SUAMI', 'ISTRI', 'PASANGAN'].includes(normalized)) {
                return 'SPOUSE';
            }
            if (['CHILD', 'ANAK', 'ANAK1', 'ANAK2', 'ANAK3', 'SON', 'DAUGHTER'].includes(normalized)) {
                return 'CHILD';
            }
            if (['PARENT', 'ORANGTUA', 'ORANG TUA', 'AYAH', 'IBU'].includes(normalized)) {
                return 'PARENT';
            }
            if (['SIBLING', 'SAUDARA', 'SAUDARA KANDUNG'].includes(normalized)) {
                return 'SIBLING';
            }
            return normalized || '';
        }

        function mapGenderValue(value) {
            if (!value) return '';
            const normalized = value.toString().trim().toUpperCase();
            if (['MALE', 'LAKI-LAKI', 'LAKI', 'PRIA', 'M', 'L'].includes(normalized)) {
                return 'Male';
            }
            if (['FEMALE', 'PEREMPUAN', 'WANITA', 'F', 'P'].includes(normalized)) {
                return 'Female';
            }
            return value;
        }

        familyModalEl?.addEventListener('hidden.bs.modal', () => {
            document.getElementById('addFamilyForm').reset();
            resetFamilyModalState();
        });

        pendidikanModalEl?.addEventListener('hidden.bs.modal', () => {
            document.getElementById('addPendidikanForm').reset();
            resetPendidikanModalState();
        });

        pelatihanModalEl?.addEventListener('hidden.bs.modal', () => {
            document.getElementById('addPelatihanForm').reset();
            resetPelatihanModalState();
        });

        mutasiModalEl?.addEventListener('hidden.bs.modal', () => {
            document.getElementById('addMutasiForm').reset();
            resetMutasiModalState();
        });

        catatanModalEl?.addEventListener('hidden.bs.modal', () => {
            document.getElementById('addCatatanKaryawanForm')?.reset();
            resetCatatanModalState();
        });

        mutasiDivisiSelect?.addEventListener('change', function() {
            const divisiKode = this.value;
            if (mutasiDepartemenSelect) {
                mutasiDepartemenSelect.innerHTML = '<option value="">Pilih Departemen</option>';
                mutasiDepartemenSelect.disabled = !divisiKode;
            }
            if (mutasiBagianSelect) {
                mutasiBagianSelect.innerHTML = '<option value="">Pilih Bagian</option>';
                mutasiBagianSelect.disabled = true;
            }
            if (mutasiSeksiSelect) {
                mutasiSeksiSelect.innerHTML = '<option value="">Pilih Bagian terlebih dahulu</option>';
                mutasiSeksiSelect.disabled = true;
            }
            if (mutasiJabatanSelect) {
                mutasiJabatanSelect.innerHTML = '<option value="">Memuat...</option>';
                mutasiJabatanSelect.disabled = true;
            }
            if (divisiKode) {
                loadMutasiDepartemens(divisiKode);
                loadMutasiJabatans(divisiKode);
            } else if (mutasiJabatanSelect) {
                mutasiJabatanSelect.innerHTML = '<option value="">Pilih Divisi terlebih dahulu</option>';
                mutasiJabatanSelect.disabled = true;
            }
        });

        mutasiDepartemenSelect?.addEventListener('change', function() {
            const divisiKode = mutasiDivisiSelect ? mutasiDivisiSelect.value : '';
            const deptKode = this.value;
            if (mutasiBagianSelect) {
                mutasiBagianSelect.innerHTML = '<option value="">Pilih Bagian</option>';
                mutasiBagianSelect.disabled = !deptKode;
            }
            if (mutasiSeksiSelect) {
                mutasiSeksiSelect.innerHTML = '<option value="">Pilih Bagian terlebih dahulu</option>';
                mutasiSeksiSelect.disabled = true;
            }
            if (divisiKode && deptKode) {
                loadMutasiBagians(divisiKode, deptKode);
            }
        });

        mutasiBagianSelect?.addEventListener('change', function() {
            const divisiKode = mutasiDivisiSelect ? mutasiDivisiSelect.value : '';
            const deptKode = mutasiDepartemenSelect ? mutasiDepartemenSelect.value : '';
            const bagianKode = this.value;
            if (mutasiSeksiSelect) {
                mutasiSeksiSelect.innerHTML = '<option value="">Pilih Seksi</option>';
                mutasiSeksiSelect.disabled = !bagianKode;
            }
            if (divisiKode && deptKode && bagianKode) {
                loadMutasiSeksis(divisiKode, deptKode, bagianKode);
            } else if (mutasiSeksiSelect) {
                mutasiSeksiSelect.innerHTML = '<option value="">Pilih Bagian terlebih dahulu</option>';
                mutasiSeksiSelect.disabled = true;
            }
        });

        // Initialize
        initializeForm();

        // New button
        document.getElementById('newBtn').addEventListener('click', function() {
            resetForm();
            isEditMode = false;
            currentNik = null;
            enableForm();
            updateCopyDataButton();
            updatePrintBiodataButton();
        });

        document.getElementById('printBiodataBtn')?.addEventListener('click', function() {
            if (!currentNik) return;
            window.open(makeUrl(`karyawan/${encodeURIComponent(currentNik)}/biodata-cetak`), '_blank', 'noopener');
        });

        // Auto-generate NIK when Tgl_Masuk is filled (only in new mode)
        document.getElementById('tanggal_masuk').addEventListener('change', function() {
            if (!isEditMode && this.value) {
                generateNikFromTahunMasuk(this.value);
            }
        });

        // Update Copy Data button when aktif checkbox changes
        document.getElementById('aktif').addEventListener('change', function() {
            if (isEditMode && currentNik) {
                updateCopyDataButton();
            }
        });

        // Pelatihan - add button
        const addPelatihanBtn = document.getElementById('addPelatihanBtn');
        addPelatihanBtn?.addEventListener('click', function() {
            if (!currentNik) {
                alert('Silakan pilih atau simpan karyawan terlebih dahulu.');
                return;
            }
            document.getElementById('addPelatihanForm').reset();
            resetPelatihanModalState();
            pelatihanModal?.show();
        });

        // Pelatihan - submit form
        const addPelatihanForm = document.getElementById('addPelatihanForm');
        addPelatihanForm?.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!currentNik) {
                alert('Silakan pilih atau simpan karyawan terlebih dahulu.');
                return;
            }

            const form = e.target;
            const formData = new FormData(form);
            const payload = {
                nm_pelatihan: formData.get('nm_pelatihan'),
                penyelenggara: formData.get('penyelenggara'),
                lokasi: formData.get('lokasi'),
                tg_pelatihan: formData.get('tg_pelatihan'),
                tg_selesai: formData.get('tg_selesai'),
                sertifikat: formData.get('sertifikat') ? 1 : 0,
                keterangan: formData.get('keterangan'),
            };

            let url = makeUrl(`karyawan/${currentNik}/pelatihan`);
            let method = 'POST';
            let pelId = null;

            if (currentPelatihanEditIndex !== null && pelatihanMembers[currentPelatihanEditIndex]) {
                const existingData = pelatihanMembers[currentPelatihanEditIndex];
                pelId = encodeURIComponent(existingData._key || existingData.nm_pelatihan);
                url = makeUrl(`karyawan/${currentNik}/pelatihan/${pelId}`);
                method = 'PUT';
            }

            fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        pelatihanModal?.hide();
                        // Reload list
                        loadPelatihanData(currentNik);
                    } else {
                        alert(data.message || 'Gagal menyimpan data pelatihan');
                    }
                })
                .catch(err => {
                    console.error('Error saving pelatihan:', err);
                    alert('Terjadi kesalahan saat menyimpan data pelatihan');
                });
        });

        // Mutasi - add button
        document.getElementById('addMutasiBtn')?.addEventListener('click', function() {
            if (!currentNik) {
                alert('Silakan pilih atau simpan karyawan terlebih dahulu.');
                return;
            }
            document.getElementById('addMutasiForm').reset();
            resetMutasiModalState();
            mutasiModal?.show();
        });

        // Mutasi - submit form
        document.getElementById('addMutasiForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!currentNik) {
                alert('Silakan pilih atau simpan karyawan terlebih dahulu.');
                return;
            }

            const form = e.target;
            const formData = new FormData(form);
            formData.set('no_sk', (formData.get('no_sk') || '').trim());

            let url = makeUrl(`karyawan/${currentNik}/mutasi`);
            let useMethodOverride = false;

            if (currentMutasiEditIndex !== null && mutasiMembers[currentMutasiEditIndex]) {
                const orig = mutasiMembers[currentMutasiEditIndex].original_no_sk || mutasiMembers[currentMutasiEditIndex].no_sk;
                if (orig) {
                    url = makeUrl(`karyawan/${currentNik}/mutasi/${encodeURIComponent(orig)}`);
                    useMethodOverride = true;
                    formData.append('_method', 'PUT');
                }
            }

            if (!useMethodOverride && formData.has('remove_sk_file')) {
                formData.delete('remove_sk_file');
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
                        mutasiModal?.hide();
                        loadMutasiData(currentNik);
                    } else {
                        const msg = (data.errors && typeof data.errors === 'object')
                            ? Object.values(data.errors).flat().join('\n')
                            : (data.message || 'Gagal menyimpan data mutasi');
                        alert(msg);
                    }
                })
                .catch(err => {
                    console.error('Error saving mutasi:', err);
                    alert('Terjadi kesalahan saat menyimpan data mutasi');
                });
        });

        document.getElementById('addCatatanKaryawanBtn')?.addEventListener('click', function() {
            if (!currentNik) {
                alert('Silakan pilih atau simpan karyawan terlebih dahulu.');
                return;
            }
            document.getElementById('addCatatanKaryawanForm')?.reset();
            resetCatatanModalState();
            catatanModal?.show();
        });

        document.getElementById('addCatatanKaryawanForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!currentNik) {
                alert('Silakan pilih atau simpan karyawan terlebih dahulu.');
                return;
            }

            const form = e.target;
            const formData = new FormData(form);
            const judul = (formData.get('judul') || '').toString().trim();
            formData.set('judul', judul);

            let url = makeUrl(`karyawan/${encodeURIComponent(currentNik)}/catatan-karyawan`);
            let useMethodOverride = false;

            if (currentCatatanEditIndex !== null && catatanMembers[currentCatatanEditIndex]) {
                const cid = catatanMembers[currentCatatanEditIndex].id;
                if (cid != null) {
                    url = makeUrl(`karyawan/${encodeURIComponent(currentNik)}/catatan-karyawan/${cid}`);
                    useMethodOverride = true;
                    formData.append('_method', 'PUT');
                }
            }

            if (!useMethodOverride) {
                formData.delete('remove_file_lampiran');
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
                        catatanModal?.hide();
                        loadCatatanData(currentNik);
                    } else {
                        const msg = (data.errors && typeof data.errors === 'object')
                            ? Object.values(data.errors).flat().join('\n')
                            : (data.message || 'Gagal menyimpan catatan karyawan');
                        alert(msg);
                    }
                })
                .catch(err => {
                    console.error('Error saving catatan karyawan:', err);
                    alert('Terjadi kesalahan saat menyimpan catatan karyawan');
                });
        });

        // Auto-fill Nama Lengkap from Nama Depan + Nama Tengah + Nama Akhir
        const namaDepanField = document.getElementById('nama_depan');
        const namaTengahField = document.getElementById('nama_tengah');
        const namaAkhirField = document.getElementById('nama_akhir');
        const namaLengkapField = document.getElementById('nama_lengkap');

        function updateNamaLengkap() {
            const namaDepan = (namaDepanField.value || '').trim();
            const namaTengah = (namaTengahField.value || '').trim();
            const namaAkhir = (namaAkhirField.value || '').trim();

            let namaLengkap = namaDepan;
            if (namaTengah) {
                namaLengkap += (namaLengkap ? ' ' : '') + namaTengah;
            }
            if (namaAkhir) {
                namaLengkap += (namaLengkap ? ' ' : '') + namaAkhir;
            }

            namaLengkapField.value = namaLengkap;
        }

        namaDepanField.addEventListener('input', updateNamaLengkap);
        namaDepanField.addEventListener('change', updateNamaLengkap);
        namaTengahField.addEventListener('input', updateNamaLengkap);
        namaTengahField.addEventListener('change', updateNamaLengkap);
        namaAkhirField.addEventListener('input', updateNamaLengkap);
        namaAkhirField.addEventListener('change', updateNamaLengkap);

        // Photo upload functionality
        const photoInput = document.getElementById('photoInput');
        const photoImage = document.getElementById('photoImage');
        const photoIcon = document.getElementById('photoIcon');
        const photoPreview = document.getElementById('photoPreview');
        const changePhotoBtn = document.getElementById('changePhotoBtn');
        const removePhotoBtn = document.getElementById('removePhotoBtn');
        let photoToRemove = false;

        changePhotoBtn.addEventListener('click', function() {
            photoInput.click();
        });

        removePhotoBtn.addEventListener('click', function() {
            photoInput.value = '';
            photoImage.src = '';
            photoImage.style.display = 'none';
            photoIcon.style.display = 'block';
            removePhotoBtn.style.display = 'none';
            photoToRemove = true;
        });

        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    alert('File harus berupa gambar');
                    photoInput.value = '';
                    return;
                }

                // Validate file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file maksimal 2MB');
                    photoInput.value = '';
                    return;
                }

                // Preview image
                const reader = new FileReader();
                reader.onload = function(e) {
                    photoImage.src = e.target.result;
                    photoImage.style.display = 'block';
                    photoIcon.style.display = 'none';
                    removePhotoBtn.style.display = 'inline-block';
                    photoToRemove = false;
                };
                reader.readAsDataURL(file);
            }
        });

        // Save button
        document.getElementById('saveBtn').addEventListener('click', function() {
            saveKaryawan();
        });

        // Cancel button
        document.getElementById('cancelBtn').addEventListener('click', function() {
            resetForm();
            disableForm();
            // Update Copy Data button state
            updateCopyDataButton();
        });

        // Delete button
        document.getElementById('deleteBtn').addEventListener('click', function() {
            if (currentNik && confirm('Apakah Anda yakin ingin menghapus karyawan ini?')) {
                deleteKaryawan(currentNik);
            }
        });

        // Function to update Copy Data button state
        function updateCopyDataButton() {
            const copyDataBtn = document.getElementById('copyDataBtn');
            if (currentNik && isEditMode) {
                // Cek status aktif dari checkbox
                const aktifCheckbox = document.getElementById('aktif');
                const isAktif = aktifCheckbox && aktifCheckbox.checked;

                // Hanya enable jika tidak aktif (unchecked)
                if (!isAktif) {
                    copyDataBtn.disabled = false;
                    copyDataBtn.title = 'Copy data dari NIK ' + currentNik + ' ke NIK baru';
                } else {
                    copyDataBtn.disabled = true;
                    copyDataBtn.title = 'Karyawan harus tidak aktif (uncheck) untuk bisa di-copy';
                }
            } else {
                copyDataBtn.disabled = true;
                copyDataBtn.title = 'Pilih karyawan dari daftar untuk di-copy';
            }
        }

        // Copy Data button - copy dari current record yang dipilih
        document.getElementById('copyDataBtn').addEventListener('click', function() {
            if (!currentNik) {
                alert('Pilih karyawan terlebih dahulu dari daftar untuk di-copy');
                return;
            }

            // Validasi: pastikan status tidak aktif (unchecked)
            const aktifCheckbox = document.getElementById('aktif');
            if (aktifCheckbox && aktifCheckbox.checked) {
                alert('Karyawan harus tidak aktif (uncheck checkbox Aktif) sebelum bisa di-copy.\n\n' +
                    'Pastikan checkbox "Aktif" tidak dicentang pada record yang akan di-copy.');
                return;
            }

            // Konfirmasi sebelum copy
            const confirmMessage = `Apakah Anda yakin ingin menyalin data dari NIK ${currentNik} ke NIK baru?\n\n` +
                `Data akan di-copy dan NIK baru akan di-generate otomatis.`;

            if (!confirm(confirmMessage)) {
                return;
            }

            // Disable button saat proses
            const copyBtn = document.getElementById('copyDataBtn');
            const originalText = copyBtn.innerHTML;
            copyBtn.disabled = true;
            copyBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';

            // Call API to get data from current NIK
            fetch(makeUrl(`karyawan/${currentNik}/copy-data`), {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reset form dan enable untuk mode new
                        resetForm();
                        enableForm();
                        isEditMode = false;
                        const oldNik = currentNik;
                        currentNik = null;

                        // Populate form with copied data (excluding NIK)
                        populateFormFromCopy(data.karyawan);

                        // Copy keluarga data jika ada
                        if (data.keluarga && data.keluarga.length > 0) {
                            familyMembers = data.keluarga;
                            updateFamilyTable();
                        }
                        // Copy pendidikan data jika ada
                        if (data.pendidikan && data.pendidikan.length > 0) {
                            pendidikanMembers = data.pendidikan;
                            updatePendidikanTable();
                        }

                        // Store old NIK untuk copy keluarga nanti
                        document.getElementById('karyawanForm').setAttribute('data-old-nik', oldNik);

                        // Generate NIK baru dan tunggu hasilnya
                        generateNikFromCurrentYear()
                            .then(newNik => {
                                // Show success message dengan NIK baru
                                const keluargaInfo = data.keluarga && data.keluarga.length > 0 ?
                                    `\nData keluarga (${data.keluarga.length} anggota) akan di-copy saat simpan.` :
                                    '';
                                alert(`Data berhasil di-copy dari NIK ${oldNik} ke NIK baru ${newNik}.${keluargaInfo}\n\n` +
                                    `Silakan periksa dan lengkapi data yang diperlukan, kemudian klik "Simpan" untuk menyimpan.\n` +
                                    `Data keluarga akan otomatis di-copy ke NIK baru saat simpan.`);

                                // Re-enable copy button
                                copyBtn.disabled = false;
                                copyBtn.innerHTML = originalText;
                            })
                            .catch(error => {
                                console.error('Error generating NIK:', error);
                                const keluargaInfo = data.keluarga && data.keluarga.length > 0 ?
                                    `\nData keluarga (${data.keluarga.length} anggota) akan di-copy saat simpan.` :
                                    '';
                                alert(`Data berhasil di-copy dari NIK ${oldNik}.${keluargaInfo}\n\n` +
                                    `Terjadi kesalahan saat generate NIK baru. Silakan generate NIK secara manual atau isi NIK baru.\n\n` +
                                    `Kemudian klik "Simpan" untuk menyimpan.`);

                                // Re-enable copy button
                                copyBtn.disabled = false;
                                copyBtn.innerHTML = originalText;
                            });
                    } else {
                        alert(data.message || 'Gagal mengambil data dari NIK lama');
                        copyBtn.disabled = false;
                        copyBtn.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengambil data: ' + error.message);
                    copyBtn.disabled = false;
                    copyBtn.innerHTML = originalText;
                });
        });

        // Add family button
        document.getElementById('addFamilyBtn').addEventListener('click', function() {
            document.getElementById('addFamilyForm').reset();
            resetFamilyModalState();
            familyModal?.show();
        });

        // Add family form submission
        document.getElementById('addFamilyForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (!currentNik) {
                alert('Silakan pilih atau simpan karyawan terlebih dahulu.');
                return;
            }

            const formData = new FormData(this);
            const familyData = Object.fromEntries(formData);

            // Clean empty date values - convert empty string to null
            if (familyData.tglLahir === '' || !familyData.tglLahir) {
                familyData.tglLahir = null;
            }

            // Clean empty string values to null for optional fields
            if (familyData.temLahir === '') {
                familyData.temLahir = null;
            }
            if (familyData.golDarah === '') {
                familyData.golDarah = null;
            }
            if (familyData.jenKelamin === '') {
                familyData.jenKelamin = null;
            }

            const isEditing = currentFamilyEditIndex !== null;
            let url, method, payload;

            if (isEditing && familyMembers[currentFamilyEditIndex]) {
                // Edit mode - update existing
                const existingData = familyMembers[currentFamilyEditIndex];
                const oldHubKeluarga = existingData.hubKeluarga;
                payload = {
                    ...familyData,
                    oldNamaKeluarga: existingData.NamaKeluarga // For composite key lookup
                };
                url = makeUrl(`karyawan/${currentNik}/keluarga/${encodeURIComponent(oldHubKeluarga)}`);
                method = 'PUT';
            } else {
                // Add mode - create new
                payload = familyData;
                url = makeUrl(`karyawan/${currentNik}/keluarga`);
                method = 'POST';
            }

            fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // Reload family data from server
                        loadFamilyData(currentNik);
                        // Reset form and close modal
                        this.reset();
                        resetFamilyModalState();
                        familyModal?.hide();
                    } else {
                        alert(data.message || 'Gagal menyimpan data anggota keluarga');
                    }
                })
                .catch(err => {
                    console.error('Error saving family member:', err);
                    alert('Terjadi kesalahan saat menyimpan data anggota keluarga');
                });
        });

        // Search functionality
        const searchInput = document.getElementById('search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                try {
                    const searchTerm = this.value.toLowerCase();
                    const rows = document.querySelectorAll('.karyawan-row');

                    rows.forEach(row => {
                        const nik = row.cells[1].textContent.toLowerCase();
                        const nama = row.cells[2].textContent.toLowerCase();

                        if (nik.includes(searchTerm) || nama.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                } catch (error) {
                    console.error('Error in search:', error);
                }
            });
        }

        // Row click to load karyawan data
        const karyawanRows = document.querySelectorAll('.karyawan-row');
        if (karyawanRows && karyawanRows.length > 0) {
            karyawanRows.forEach(row => {
                row.addEventListener('click', function() {
                    try {
                        // Remove active class from all rows
                        document.querySelectorAll('.karyawan-row').forEach(r => r.classList.remove('table-active'));
                        // Add active class to clicked row
                        this.classList.add('table-active');

                        const nik = this.getAttribute('data-nik');
                        if (nik && typeof loadKaryawanData === 'function') {
                            loadKaryawanData(nik);
                        } else {
                            console.error('loadKaryawanData is not a function or NIK is missing');
                        }
                    } catch (error) {
                        console.error('Error clicking karyawan row:', error);
                        alert('Terjadi kesalahan saat memuat data karyawan: ' + error.message);
                    }
                });
            });
        }

        // Auto-load karyawan from URL parameter (for link from List Karyawan Aktif)
        const urlParams = new URLSearchParams(window.location.search);
        const nikParam = urlParams.get('nik');
        if (nikParam) {
            // Find and highlight the row
            const targetRow = document.querySelector(`.karyawan-row[data-nik="${nikParam}"]`);
            if (targetRow) {
                // Remove active class from all rows
                document.querySelectorAll('.karyawan-row').forEach(r => r.classList.remove('table-active'));
                // Add active class to target row
                targetRow.classList.add('table-active');
                // Scroll to the row
                targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                // Load the karyawan data
                loadKaryawanData(nikParam);
            } else {
                // If row not found (maybe not loaded yet), try to load data anyway
                loadKaryawanData(nikParam);
            }
        }

        function initializeForm() {
            disableForm();
        }

        function generateNikFromTahunMasuk(tglMasuk) {
            if (!tglMasuk) return;

            // Extract year from date (YYYY-MM-DD format)
            const tahun = new Date(tglMasuk).getFullYear();

            if (!tahun || tahun < 2000 || tahun > 2099) {
                return;
            }

            // Only generate if NIK field is empty
            const nikField = document.getElementById('nik');
            if (nikField.value && nikField.value.trim() !== '') {
                return;
            }

            // Call API to generate NIK
            fetch(makeUrl('karyawan/generate-nik'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        tahun: tahun
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.nik) {
                        nikField.value = data.nik;
                    }
                })
                .catch(error => {
                    console.error('Error generating NIK:', error);
                });
        }

        function generateNikFromCurrentYear() {
            return new Promise((resolve, reject) => {
                // Get current year
                const tahun = new Date().getFullYear();

                if (!tahun || tahun < 2000 || tahun > 2099) {
                    reject(new Error('Tahun tidak valid'));
                    return;
                }

                // Only generate if NIK field is empty
                const nikField = document.getElementById('nik');
                if (nikField.value && nikField.value.trim() !== '') {
                    resolve(nikField.value);
                    return;
                }

                // Call API to generate NIK based on current year
                fetch(makeUrl('karyawan/generate-nik'), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            tahun: tahun
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.nik) {
                            nikField.value = data.nik;
                            resolve(data.nik);
                        } else {
                            reject(new Error('Gagal generate NIK'));
                        }
                    })
                    .catch(error => {
                        console.error('Error generating NIK:', error);
                        reject(error);
                    });
            });
        }

        function updatePrintBiodataButton() {
            const btn = document.getElementById('printBiodataBtn');
            if (!btn) return;
            btn.disabled = !(isEditMode && currentNik);
        }

        function enableForm() {
            document.getElementById('saveBtn').disabled = false;
            document.getElementById('cancelBtn').disabled = false;
            document.getElementById('deleteBtn').disabled = false;

            // Copy Data button: hanya enabled jika ada currentNik (record dipilih) dan tidak aktif
            updateCopyDataButton();
            updatePrintBiodataButton();

            // Enable all form inputs
            const inputs = document.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (input.id !== 'search') {
                    input.disabled = false;
                }
            });

            // Keep NIK and Nama Lengkap readonly (auto-generated)
            document.getElementById('nik').readOnly = true;
            document.getElementById('nama_lengkap').readOnly = true;

            syncMutasiOrganisasiFieldsEnabled();
        }

        function disableForm() {
            document.getElementById('saveBtn').disabled = true;
            document.getElementById('cancelBtn').disabled = true;
            document.getElementById('deleteBtn').disabled = true;
            document.getElementById('copyDataBtn').disabled = true;
            const printB = document.getElementById('printBiodataBtn');
            if (printB) printB.disabled = true;

            // Disable all form inputs
            const inputs = document.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (input.id !== 'search') {
                    input.disabled = true;
                }
            });
        }

        function resetForm() {
            document.getElementById('karyawanForm').reset();
            // Remove old NIK attribute
            document.getElementById('karyawanForm').removeAttribute('data-old-nik');
            familyMembers = [];
            updateFamilyTable();
            pendidikanMembers = [];
            updatePendidikanTable();
            pelatihanMembers = [];
            updatePelatihanTable();
            mutasiMembers = [];
            updateMutasiTable();
            catatanMembers = [];
            updateCatatanTable();
            currentFamilyEditIndex = null;
            currentPendidikanEditIndex = null;
            currentPelatihanEditIndex = null;
            currentMutasiEditIndex = null;
            currentCatatanEditIndex = null;

            // Reset hierarchical dropdowns
            departemenSelect.innerHTML = '<option value="">Pilih Divisi terlebih dahulu</option>';
            departemenSelect.disabled = true;
            bagianSelect.innerHTML = '<option value="">Pilih Departemen terlebih dahulu</option>';
            bagianSelect.disabled = true;
            const seksiSelect = document.getElementById('seksi');
            seksiSelect.innerHTML = '<option value="">Pilih Bagian terlebih dahulu</option>';
            seksiSelect.disabled = true;
            
            // Reset jabatan to show all options
            loadAllJabatans();

            // Reset Nama Lengkap
            document.getElementById('nama_lengkap').value = '';

            // Reset NIK
            document.getElementById('nik').value = '';

            // Reset photo
            document.getElementById('photoInput').value = '';
            document.getElementById('photoImage').src = '';
            document.getElementById('photoImage').style.display = 'none';
            document.getElementById('photoIcon').style.display = 'block';
            document.getElementById('removePhotoBtn').style.display = 'none';
            photoToRemove = false;
        }

        function loadKaryawanData(nik) {
            fetch(makeUrl(`karyawan/${nik}`), {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        populateForm(data.karyawan);
                        loadFamilyData(nik);
                        loadPendidikanData(nik);
                        loadPelatihanData(nik);
                        loadMutasiData(nik);
                        loadCatatanData(nik);
                        enableForm();
                        isEditMode = true;
                        currentNik = nik;
                        // Update Copy Data button state setelah load data
                        updateCopyDataButton();
                    } else {
                        alert('Gagal memuat data karyawan');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memuat data: ' + error.message);
                });
        }

        function populateForm(karyawan) {
            // Populate all form fields
            Object.keys(karyawan).forEach(key => {
                const element = document.querySelector(`[name="${key}"]`);
                if (element) {
                    // Skip file input - cannot be set programmatically
                    if (element.type === 'file') {
                        return;
                    }

                    if (element.type === 'checkbox') {
                        element.checked = karyawan[key] == 1;
                    } else if (element.tagName === 'SELECT') {
                        // Handle select/dropdown
                        // Enable sementara jika disabled untuk bisa set value
                        const wasDisabled = element.disabled;
                        if (wasDisabled) {
                            element.disabled = false;
                        }
                        element.value = karyawan[key] || '';
                        if (wasDisabled) {
                            element.disabled = true;
                        }
                    } else {
                        // If field is a date input, normalize to YYYY-MM-DD
                        if (element.type === 'date' && karyawan[key]) {
                            element.value = toYMD(karyawan[key]);
                        } else {
                            element.value = karyawan[key] || '';
                        }
                    }
                }
            });

            // Update Nama Lengkap after populating form
            updateNamaLengkap();

            // Update photo preview if exists
            if (karyawan.photo) {
                const photoUrl = `/storage/photos/${karyawan.photo}`;
                photoImage.src = photoUrl;
                photoImage.style.display = 'block';
                photoIcon.style.display = 'none';
                removePhotoBtn.style.display = 'inline-block';
            } else {
                photoImage.src = '';
                photoImage.style.display = 'none';
                photoIcon.style.display = 'block';
                removePhotoBtn.style.display = 'none';
            }
            photoToRemove = false;

            // Handle hierarchical dropdowns (Divisi -> Departemen -> Bagian -> Seksi)
            // Also handle Jabatan filtering based on Divisi
            const divisiValue = karyawan['Divisi'];
            const deptValue = karyawan['dept'];
            const bagianValue = karyawan['vcKodeBagian'];
            const seksiValue = karyawan['vcKodeSeksi'];
            const jabatanValue = karyawan['Jabat'];

            console.log('Populating hierarchical fields:', {
                divisi: divisiValue,
                dept: deptValue,
                bagian: bagianValue,
                seksi: seksiValue,
                jabatan: jabatanValue
            });

            if (divisiValue) {
                const divisiElement = document.querySelector('[name="Divisi"]');
                if (divisiElement) {
                    divisiElement.value = divisiValue;
                    // Load jabatans filtered by divisi dengan callback untuk set value setelah ter-load
                    loadJabatans(divisiValue, function() {
                        // Set jabatan value setelah jabatans ter-load
                        if (jabatanValue) {
                            const jabatanElement = document.querySelector('[name="Jabat"]');
                            if (jabatanElement) {
                                jabatanElement.value = jabatanValue;
                                console.log('Set jabatan value:', jabatanValue);
                            }
                        }
                    });
                    
                    // Load departemens dengan callback untuk set value setelah ter-load
                    loadDepartemens(divisiValue, function() {
                        // Set departemen value setelah departemens ter-load
                        if (deptValue) {
                            const deptElement = document.querySelector('[name="dept"]');
                            if (deptElement) {
                                // Enable sementara untuk set value
                                if (deptElement.disabled) {
                                    deptElement.disabled = false;
                                }
                                deptElement.value = deptValue;
                                console.log('Set dept value:', deptValue);
                                
                                // Load bagians dengan callback untuk set value setelah ter-load
                                loadBagians(divisiValue, deptValue, function() {
                                    // Set bagian value setelah bagians ter-load
                                    if (bagianValue) {
                                        const bagianElement = document.querySelector('[name="vcKodeBagian"]');
                                        if (bagianElement) {
                                            // Enable sementara untuk set value
                                            if (bagianElement.disabled) {
                                                bagianElement.disabled = false;
                                            }
                                            bagianElement.value = bagianValue;
                                            console.log('Set bagian value:', bagianValue);
                                            
                                            // Load seksis dengan callback untuk set value setelah ter-load
                                            loadSeksis(divisiValue, deptValue, bagianValue, function() {
                                                // Set seksi value setelah seksis ter-load
                                                if (seksiValue) {
                                                    const seksiElement = document.querySelector('[name="vcKodeSeksi"]');
                                                    if (seksiElement) {
                                                        // Enable sementara untuk set value
                                                        if (seksiElement.disabled) {
                                                            seksiElement.disabled = false;
                                                        }
                                                        seksiElement.value = seksiValue;
                                                        console.log('Set seksi value:', seksiValue);
                                                    }
                                                }
                                            });
                                        }
                                    }
                                });
                            }
                        }
                    });
                }
            } else {
                // Jika tidak ada divisi, load all jabatans
                loadAllJabatans();
                // Set jabatan value jika ada
                if (jabatanValue) {
                    const jabatanElement = document.querySelector('[name="Jabat"]');
                    if (jabatanElement) {
                        jabatanElement.value = jabatanValue;
                    }
                }
            }
        }

        function populateFormFromCopy(karyawan) {
            // Fields to exclude when copying (these will be set fresh for new employee)
            const excludeFields = [
                'Nik', // NIK baru akan di-generate
                'dtCreate', 'dtChange', 'create_date', 'update_date', // Timestamps
                'user_create', 'user_update', // User info
                'photo', // Photo tidak di-copy
                'vcAktif', // Default aktif untuk karyawan baru
                'Tgl_Berhenti', // Tidak perlu copy tanggal berhenti
                'deleted', // Status deleted
                'Divisi', 'dept', 'vcKodeBagian' // Hierarchical dropdowns handled separately
            ];

            // Populate form fields (excluding certain fields)
            Object.keys(karyawan).forEach(key => {
                // Skip excluded fields
                if (excludeFields.includes(key)) {
                    return;
                }

                const element = document.querySelector(`[name="${key}"]`);
                if (element) {
                    // Skip file input - cannot be set programmatically
                    if (element.type === 'file') {
                        return;
                    }

                    if (element.type === 'checkbox') {
                        element.checked = karyawan[key] == 1;
                    } else if (element.tagName === 'SELECT') {
                        // Handle select/dropdown (except hierarchical ones)
                        element.value = karyawan[key] || '';
                    } else {
                        // If field is a date input, normalize to YYYY-MM-DD
                        if (element.type === 'date' && karyawan[key]) {
                            element.value = toYMD(karyawan[key]);
                        } else {
                            element.value = karyawan[key] || '';
                        }
                    }
                }
            });

            // Update Nama Lengkap after populating form
            updateNamaLengkap();

            // Handle hierarchical dropdowns (Divisi -> Departemen -> Bagian)
            const divisiValue = karyawan['Divisi'];
            const deptValue = karyawan['dept'];
            const bagianValue = karyawan['vcKodeBagian'];

            if (divisiValue) {
                const divisiElement = document.querySelector('[name="Divisi"]');
                if (divisiElement) {
                    divisiElement.value = divisiValue;
                    // Trigger change event to load departemens
                    setTimeout(() => {
                        divisiElement.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));

                        // After departemens loaded, set departemen value
                        if (deptValue) {
                            setTimeout(() => {
                                const deptElement = document.querySelector('[name="dept"]');
                                if (deptElement) {
                                    deptElement.value = deptValue;
                                    // Trigger change event to load bagians
                                    setTimeout(() => {
                                        deptElement.dispatchEvent(new Event('change', {
                                            bubbles: true
                                        }));

                                        // After bagians loaded, set bagian value
                                        if (bagianValue) {
                                            setTimeout(() => {
                                                const bagianElement = document.querySelector('[name="vcKodeBagian"]');
                                                if (bagianElement) {
                                                    bagianElement.value = bagianValue;
                                                }
                                            }, 500);
                                        }
                                    }, 500);
                                }
                            }, 500);
                        }
                    }, 100);
                }
            }

            // Generate NIK baru berdasarkan tahun saat ini (tahun copy)
            // Pastikan NIK field kosong sebelum generate
            // Note: NIK akan di-generate di event handler copy data, tidak perlu di sini

            // Reset photo (tidak di-copy)
            photoImage.src = '';
            photoImage.style.display = 'none';
            photoIcon.style.display = 'block';
            removePhotoBtn.style.display = 'none';
            photoToRemove = false;

            // Set vcAktif to checked (default aktif untuk karyawan baru)
            document.getElementById('aktif').checked = true;
        }

        function toYMD(value) {
            // Accepts: 'YYYY-MM-DD', 'YYYY-MM-DDTHH:mm:ssZ', Date, or null
            if (!value) return '';
            try {
                // If already YYYY-MM-DD, return as-is
                if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(value)) {
                    return value;
                }
                const d = new Date(value);
                if (isNaN(d)) return '';
                const yyyy = d.getFullYear();
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const dd = String(d.getDate()).padStart(2, '0');
                return `${yyyy}-${mm}-${dd}`;
            } catch (e) {
                return '';
            }
        }

        function toDMY(value) {
            // Format to DD-MM-YYYY for display
            if (!value) return '';
            try {
                // If already DD-MM-YYYY
                if (typeof value === 'string' && /^\d{2}-\d{2}-\d{4}$/.test(value)) {
                    return value;
                }
                const d = new Date(value);
                if (isNaN(d)) return '';
                const dd = String(d.getDate()).padStart(2, '0');
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const yyyy = d.getFullYear();
                return `${dd}-${mm}-${yyyy}`;
            } catch (e) {
                return '';
            }
        }

        function loadFamilyData(nik) {
            fetch(makeUrl(`karyawan/${nik}/keluarga`), {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        familyMembers = data.keluarga;
                        updateFamilyTable();
                    }
                })
                .catch(error => {
                    console.error('Error loading family data:', error);
                });
        }

        function updateFamilyTable() {
            const tbody = document.getElementById('familyTableBody');
            tbody.innerHTML = '';

            familyMembers.forEach((member, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                <td>${member.hubKeluarga}</td>
                <td>${member.NamaKeluarga}</td>
                <td>${member.jenKelamin}</td>
                <td>${member.temLahir || '-'}</td>
                <td>${toDMY(member.tglLahir) || '-'}</td>
                <td>${member.golDarah || '-'}</td>
                <td class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editFamilyMember(${index})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFamilyMember(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
                tbody.appendChild(row);
            });
        }

        function removeFamilyMember(index) {
            if (!confirm('Apakah Anda yakin ingin menghapus anggota keluarga ini?')) {
                return;
            }

            if (!currentNik) {
                alert('NIK tidak ditemukan.');
                return;
            }

            const member = familyMembers[index];
            if (!member) {
                alert('Data anggota keluarga tidak ditemukan.');
                return;
            }

            const hubKeluarga = encodeURIComponent(member.hubKeluarga);
            const namaKeluarga = encodeURIComponent(member.NamaKeluarga);
            
            // Use query parameter for NamaKeluarga since it's part of composite key
            fetch(makeUrl(`karyawan/${currentNik}/keluarga/${hubKeluarga}?NamaKeluarga=${namaKeluarga}`), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // Reload family data from server
                        loadFamilyData(currentNik);
                    } else {
                        alert(data.message || 'Gagal menghapus data anggota keluarga');
                    }
                })
                .catch(err => {
                    console.error('Error deleting family member:', err);
                    alert('Terjadi kesalahan saat menghapus data anggota keluarga');
                });
        }

        function editFamilyMember(index) {
            const member = familyMembers[index];
            if (!member) return;

            currentFamilyEditIndex = index;
            const form = document.getElementById('addFamilyForm');

            form.hubKeluarga.value = mapHubunganValue(member.hubKeluarga);
            form.NamaKeluarga.value = member.NamaKeluarga || '';
            form.jenKelamin.value = mapGenderValue(member.jenKelamin);
            form.temLahir.value = member.temLahir || '';
            form.tglLahir.value = formatDateForInput(member.tglLahir);
            form.golDarah.value = member.golDarah || '';

            const modalTitle = familyModalEl?.querySelector('.modal-title');
            const submitBtn = form.querySelector('button[type="submit"]');
            if (modalTitle) modalTitle.textContent = 'Edit Anggota Keluarga';
            if (submitBtn) submitBtn.textContent = 'Update';

            familyModal?.show();
        }

        // ========== PENDIDIKAN CRUD ==========
        // Add Pendidikan button
        document.getElementById('addPendidikanBtn').addEventListener('click', function() {
            if (!currentNik && !isEditMode) {
                alert('Silakan simpan data karyawan terlebih dahulu sebelum menambahkan pendidikan.');
                return;
            }
            // Reset form
            document.getElementById('addPendidikanForm').reset();
            resetPendidikanModalState();
            pendidikanModal?.show();
        });

        // Add Pendidikan form submission
        document.getElementById('addPendidikanForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const pendidikanData = Object.fromEntries(formData);

            // Clean empty string values to null for optional fields
            Object.keys(pendidikanData).forEach(key => {
                if (pendidikanData[key] === '') {
                    pendidikanData[key] = null;
                }
            });

            const isEditing = currentPendidikanEditIndex !== null;
            const existingData = isEditing ? pendidikanMembers[currentPendidikanEditIndex] : null;

            // Jika karyawan sudah ada (edit mode), langsung save ke database
            if (currentNik && isEditMode) {
                let url = `${basePath}/karyawan/${currentNik}/pendidikan`;
                let method = 'POST';

                if (isEditing && existingData && (existingData._key || existingData.education_level)) {
                    const key = encodeURIComponent(existingData._key || existingData.education_level);
                    url = `${basePath}/karyawan/${currentNik}/pendidikan/${key}`;
                    method = 'PUT';
                }

                fetch(url, {
                        method: method,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(pendidikanData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload pendidikan data dari server
                            loadPendidikanData(currentNik);
                            resetPendidikanModalState();
                            pendidikanModal?.hide();
                        } else {
                            alert('Gagal menyimpan data pendidikan: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error saving pendidikan:', error);
                        alert('Gagal menyimpan data pendidikan. Silakan coba lagi.');
                    });
            } else {
                // Jika belum ada karyawan, simpan ke array sementara
                if (isEditing && existingData) {
                    pendidikanMembers[currentPendidikanEditIndex] = {
                        ...existingData,
                        ...pendidikanData,
                        _key: existingData._key || existingData.tempId || pendidikanData.education_level
                    };
                } else {
                    pendidikanData.tempId = 'temp_' + Date.now();
                    pendidikanMembers.push({
                        ...pendidikanData,
                        _key: pendidikanData.tempId
                    });
                }
                updatePendidikanTable();
                resetPendidikanModalState();
                pendidikanModal?.hide();
            }

            currentPendidikanEditIndex = null;

            // Reset form
            this.reset();
        });

        function loadPendidikanData(nik) {
            console.log('Loading pendidikan data for NIK:', nik);
            fetch(makeUrl(`karyawan/${nik}/pendidikan`), {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    console.log('Pendidikan response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Pendidikan data received:', data);
                    if (data.success) {
                        pendidikanMembers = (data.pendidikan || []).map(item => ({
                            ...item,
                            _key: item.education_level // simpan key asli
                        }));
                        console.log('Pendidikan members count:', pendidikanMembers.length);
                        updatePendidikanTable();
                    } else {
                        console.warn('Pendidikan data not successful:', data);
                        pendidikanMembers = [];
                        updatePendidikanTable();
                    }
                })
                .catch(error => {
                    console.error('Error loading pendidikan data:', error);
                    pendidikanMembers = [];
                    updatePendidikanTable();
                });
        }

        function updatePendidikanTable() {
            const tbody = document.getElementById('pendidikanTableBody');
            tbody.innerHTML = '';

            console.log('Updating pendidikan table, members count:', pendidikanMembers.length);

            if (pendidikanMembers.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Belum ada data pendidikan</td></tr>';
                return;
            }

            pendidikanMembers.forEach((pendidikan, index) => {
                console.log('Rendering pendidikan row:', index, pendidikan);
                const row = document.createElement('tr');
                // Menggunakan kolom yang sesuai dengan struktur database
                const educationLevel = pendidikan.education_level || '-';
                const institutionName = pendidikan.institution_name || '-';
                const major = pendidikan.major || '-';
                const startYear = pendidikan.start_year || '-';
                const endYear = pendidikan.end_year || '-';
                const gpa = pendidikan.gpa ? parseFloat(pendidikan.gpa).toFixed(2) : '-';

                row.innerHTML = `
                <td>${educationLevel}</td>
                <td>${institutionName}</td>
                <td>${major}</td>
                <td>${startYear}</td>
                <td>${endYear}</td>
                <td>${gpa}</td>
                <td class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editPendidikanMember(${index})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePendidikanMember(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
                tbody.appendChild(row);
            });
        }

        function removePendidikanMember(index) {
            if (!confirm('Apakah Anda yakin ingin menghapus data pendidikan ini?')) {
                return;
            }

            const pendidikan = pendidikanMembers[index];
            if (!pendidikan) return;

            // Jika ada key (data sudah di database), hapus via API
            if ((pendidikan._key || pendidikan.education_level) && currentNik && isEditMode) {
                const key = encodeURIComponent(pendidikan._key || pendidikan.education_level);
                fetch(makeUrl(`karyawan/${currentNik}/pendidikan/${key}`), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadPendidikanData(currentNik);
                        } else {
                            alert('Gagal menghapus data pendidikan: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting pendidikan:', error);
                        alert('Gagal menghapus data pendidikan. Silakan coba lagi.');
                    });
            } else {
                pendidikanMembers.splice(index, 1);
                updatePendidikanTable();
            }
        }

        function editPendidikanMember(index) {
            const pendidikan = pendidikanMembers[index];
            if (!pendidikan) return;

            currentPendidikanEditIndex = index;
            const form = document.getElementById('addPendidikanForm');

            form.education_level.value = pendidikan.education_level || '';
            form.institution_name.value = pendidikan.institution_name || '';
            form.major.value = pendidikan.major || '';
            form.start_year.value = pendidikan.start_year || '';
            form.end_year.value = pendidikan.end_year || '';
            form.gpa.value = pendidikan.gpa || '';

            const modalTitle = pendidikanModalEl?.querySelector('.modal-title');
            const submitBtn = form.querySelector('button[type="submit"]');
            if (modalTitle) modalTitle.textContent = 'Edit Riwayat Pendidikan';
            if (submitBtn) submitBtn.textContent = 'Update';

            pendidikanModal?.show();
        }

        function saveFamilyMembers(nikLama, nikBaru) {
            // Copy keluarga data from old NIK to new NIK using batch copy endpoint
            return fetch(makeUrl('karyawan/copy-keluarga'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        nik_lama: nikLama,
                        nik_baru: nikBaru
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Gagal copy data keluarga');
                    }
                    return data;
                });
        }

        function savePendidikanMembers(nikLama, nikBaru) {
            // Copy pendidikan data from old NIK to new NIK using batch copy endpoint
            return fetch(makeUrl('karyawan/copy-pendidikan'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        nik_lama: nikLama,
                        nik_baru: nikBaru
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Gagal copy data pendidikan');
                    }
                    return data;
                });
        }

        function saveKaryawan() {
            const formData = new FormData();

            // Get all form data
            const inputs = document.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (input.name && input.id !== 'search') {
                    if (input.type === 'checkbox') {
                        formData.append(input.name, input.checked ? 1 : 0);
                    } else if (input.type === 'file') {
                        // Handle file upload
                        if (input.files && input.files[0]) {
                            formData.append(input.name, input.files[0]);
                        }
                    } else {
                        // Untuk select yang disabled, tetap append value-nya
                        // karena disabled element tidak mengirim value secara default
                        if (input.tagName === 'SELECT' && input.disabled) {
                            // Enable sementara untuk bisa mengambil value, lalu disable lagi
                            const wasDisabled = input.disabled;
                            input.disabled = false;
                            const value = input.value || '';
                            formData.append(input.name, value);
                            input.disabled = wasDisabled;
                            // Debug log untuk field hierarchical
                            if (input.name === 'dept' || input.name === 'vcKodeBagian' || input.name === 'vcKodeSeksi') {
                                console.log('Saving ' + input.name + ':', value);
                            }
                        } else {
                            const value = input.value || '';
                            formData.append(input.name, value);
                            // Debug log untuk field hierarchical
                            if (input.name === 'dept' || input.name === 'vcKodeBagian' || input.name === 'vcKodeSeksi') {
                                console.log('Saving ' + input.name + ':', value);
                            }
                        }
                    }
                }
            });

            // Add flag to remove photo if needed
            if (photoToRemove) {
                formData.append('remove_photo', '1');
            }

            // Catatan: Data keluarga tidak dikirim di sini karena tab Keluarga punya CRUD sendiri
            // Data keluarga dikelola melalui endpoint terpisah di tab Keluarga

            const url = isEditMode ? makeUrl(`karyawan/${currentNik}`) : makeUrl('karyawan');
            const method = isEditMode ? 'POST' : 'POST';

            // Untuk method PUT, gunakan method spoofing
            if (isEditMode) {
                formData.append('_method', 'PUT');
            }

            fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(async response => {
                    const contentType = response.headers.get('content-type');
                    let data;

                    if (contentType && contentType.includes('application/json')) {
                        data = await response.json();
                    } else {
                        const text = await response.text();
                        try {
                            data = JSON.parse(text);
                        } catch (e) {
                            // Jika bukan JSON, cek apakah response OK
                            if (response.ok) {
                                // Response OK tapi bukan JSON, anggap sukses
                                data = {
                                    success: true,
                                    message: 'Data berhasil disimpan'
                                };
                            } else {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                        }
                    }

                    if (!response.ok) {
                        // Response tidak OK, tapi sudah parse JSON
                        throw new Error(data.message || `HTTP error! status: ${response.status}`);
                    }

                    return data;
                })
                .then(data => {
                    if (data.success) {
                        // Get new NIK from response or form
                        let newNik = null;
                        if (data.karyawan && data.karyawan.Nik) {
                            newNik = data.karyawan.Nik;
                        } else {
                            newNik = document.getElementById('nik').value;
                        }

                        // Get old NIK from form attribute (set saat copy data)
                        const oldNik = document.getElementById('karyawanForm').getAttribute('data-old-nik');

                        // Copy keluarga dan pendidikan data jika ada old NIK (berarti ini dari copy data)
                        if (oldNik && newNik && newNik.trim() !== '' && oldNik !== newNik) {
                            Promise.all([
                                    saveFamilyMembers(oldNik, newNik),
                                    savePendidikanMembers(oldNik, newNik),
                                    savePelatihanMembers(oldNik, newNik),
                                    saveMutasiMembers(oldNik, newNik),
                                    saveCatatanKaryawanMembers(oldNik, newNik)
                                ])
                                .then(([keluargaResult, pendidikanResult, pelatihanResult, mutasiResult, catatanResult]) => {
                                    let info = [];
                                    if (keluargaResult.copied > 0) {
                                        info.push(`keluarga (${keluargaResult.copied} anggota)`);
                                    }
                                    if (pendidikanResult.copied > 0) {
                                        info.push(`pendidikan (${pendidikanResult.copied} record)`);
                                    }
                                    if (pelatihanResult.copied > 0) {
                                        info.push(`pelatihan (${pelatihanResult.copied} record)`);
                                    }
                                    if (mutasiResult.copied > 0) {
                                        info.push(`mutasi (${mutasiResult.copied} record)`);
                                    }
                                    if (catatanResult.copied > 0) {
                                        info.push(`catatan karyawan (${catatanResult.copied} record)`);
                                    }
                                    const infoText = info.length > 0 ? ' dan ' + info.join(', ') : '';
                                    alert(`Data karyawan${infoText} berhasil disimpan dengan NIK ${newNik}`);
                                    location.reload();
                                })
                                .catch(error => {
                                    console.error('Error copying data:', error);
                                    alert('Data karyawan berhasil disimpan dengan NIK ' + newNik + ', tapi ada kesalahan saat copy data terkait. Silakan copy data secara manual.');
                                    location.reload();
                                });
                        } else {
                            alert('Data karyawan berhasil disimpan' + (newNik ? ' dengan NIK ' + newNik : ''));
                            location.reload();
                        }
                    } else {
                        alert('Gagal menyimpan data: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Hanya tampilkan error jika benar-benar error, bukan jika data berhasil disimpan
                    if (error.message && !error.message.includes('HTTP error! status: 200')) {
                        alert('Terjadi kesalahan: ' + error.message);
                    } else {
                        // Jika tidak ada error message yang jelas, cek apakah data mungkin sudah tersimpan
                        console.log('Silakan refresh halaman untuk melihat data terbaru');
                    }
                });
        }

        function deleteKaryawan(nik) {
            fetch(makeUrl(`karyawan/${nik}`), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(async response => {
                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        // If not JSON, read as text to see error
                        const text = await response.text();
                        throw new Error(`Server error: ${response.status} - ${text.substring(0, 200)}`);
                    }
                })
                .then(data => {
                    if (data.success) {
                        alert('Karyawan berhasil dihapus');
                        location.reload();
                    } else {
                        alert('Gagal menghapus data: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghapus karyawan: ' + error.message);
                });
        }

        // Make removeFamilyMember globally accessible
        window.removeFamilyMember = removeFamilyMember;
        window.editFamilyMember = editFamilyMember;
        window.removePendidikanMember = removePendidikanMember;
        window.editPendidikanMember = editPendidikanMember;
        window.removePelatihanMember = removePelatihanMember;
        window.editPelatihanMember = editPelatihanMember;
        window.removeMutasiMember = removeMutasiMember;
        window.editMutasiMember = editMutasiMember;
        window.removeCatatanMember = removeCatatanMember;
        window.editCatatanMember = editCatatanMember;

        // Hierarchical dropdown functionality
        // When divisi changes, load departemens and filter jabatans
        divisiSelect.addEventListener('change', function() {
            const divisiKode = this.value;

            // Reset departemen, bagian, and seksi
            departemenSelect.innerHTML = '<option value="">Pilih Departemen</option>';
            departemenSelect.disabled = !divisiKode;
            bagianSelect.innerHTML = '<option value="">Pilih Bagian</option>';
            bagianSelect.disabled = true;
            const seksiSelect = document.getElementById('seksi');
            seksiSelect.innerHTML = '<option value="">Pilih Bagian terlebih dahulu</option>';
            seksiSelect.disabled = true;

            // Filter jabatans based on divisi
            if (divisiKode) {
                loadJabatans(divisiKode);
                loadDepartemens(divisiKode);
            } else {
                // If no divisi selected, show all jabatans
                loadAllJabatans();
            }
        });

        // When departemen changes, load bagians
        departemenSelect.addEventListener('change', function() {
            const divisiKode = divisiSelect.value;
            const deptKode = this.value;

            // Reset bagian and seksi
            bagianSelect.innerHTML = '<option value="">Pilih Bagian</option>';
            bagianSelect.disabled = !deptKode;
            const seksiSelect = document.getElementById('seksi');
            seksiSelect.innerHTML = '<option value="">Pilih Bagian terlebih dahulu</option>';
            seksiSelect.disabled = true;

            if (divisiKode && deptKode) {
                loadBagians(divisiKode, deptKode);
            }
        });

        // When bagian changes, load seksis
        bagianSelect.addEventListener('change', function() {
            const divisiKode = divisiSelect.value;
            const deptKode = departemenSelect.value;
            const bagianKode = this.value;

            // Reset seksi
            const seksiSelect = document.getElementById('seksi');
            seksiSelect.innerHTML = '<option value="">Pilih Seksi</option>';
            seksiSelect.disabled = !bagianKode;

            if (divisiKode && deptKode && bagianKode) {
                loadSeksis(divisiKode, deptKode, bagianKode);
            } else {
                // Jika bagian di-reset, pastikan seksi disabled
                seksiSelect.disabled = true;
            }
        });

        function loadDepartemens(divisiKode, callback) {
            departemenSelect.disabled = true;
            departemenSelect.innerHTML = '<option value="">Memuat...</option>';

            fetch(makeUrl('karyawan/get-departemens'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        divisi: divisiKode
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        departemenSelect.innerHTML = '<option value="">Pilih Departemen</option>';
                        data.departemens.forEach(dept => {
                            const option = document.createElement('option');
                            option.value = dept.vcKodeDept;
                            option.textContent = dept.vcKodeDept + ' - ' + dept.vcNamaDept;
                            departemenSelect.appendChild(option);
                        });
                        // Enable dropdown setelah data dimuat
                        departemenSelect.disabled = false;
                    } else {
                        departemenSelect.innerHTML = '<option value="">Tidak ada data</option>';
                        departemenSelect.disabled = true;
                    }
                    // Panggil callback jika ada
                    if (callback) callback();
                })
                .catch(error => {
                    console.error('Error loading departemens:', error);
                    departemenSelect.innerHTML = '<option value="">Error memuat data</option>';
                    departemenSelect.disabled = true;
                    // Panggil callback jika ada
                    if (callback) callback();
                });
        }

        function loadBagians(divisiKode, deptKode, callback) {
            bagianSelect.disabled = true;
            bagianSelect.innerHTML = '<option value="">Memuat...</option>';

            fetch(makeUrl('karyawan/get-bagians'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        divisi: divisiKode,
                        departemen: deptKode
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        bagianSelect.innerHTML = '<option value="">Pilih Bagian</option>';
                        data.bagians.forEach(bagian => {
                            const option = document.createElement('option');
                            option.value = bagian.vcKodeBagian;
                            option.textContent = bagian.vcKodeBagian + ' - ' + bagian.vcNamaBagian;
                            bagianSelect.appendChild(option);
                        });
                        // Enable dropdown setelah data dimuat
                        bagianSelect.disabled = false;
                    } else {
                        bagianSelect.innerHTML = '<option value="">Tidak ada data</option>';
                        bagianSelect.disabled = true;
                    }
                    // Panggil callback jika ada
                    if (callback) callback();
                })
                .catch(error => {
                    console.error('Error loading bagians:', error);
                    bagianSelect.innerHTML = '<option value="">Error memuat data</option>';
                    bagianSelect.disabled = true;
                    // Panggil callback jika ada
                    if (callback) callback();
                });
        }

        function loadSeksis(divisiKode, deptKode, bagianKode, callback) {
            const seksiSelect = document.getElementById('seksi');
            seksiSelect.disabled = true;
            seksiSelect.innerHTML = '<option value="">Memuat...</option>';

            fetch(makeUrl('karyawan/get-seksis'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        divisi: divisiKode,
                        departemen: deptKode,
                        bagian: bagianKode
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        seksiSelect.innerHTML = '<option value="">Pilih Seksi</option>';
                        data.seksis.forEach(seksi => {
                            const option = document.createElement('option');
                            option.value = seksi.vcKodeSeksi;
                            option.textContent = seksi.vcKodeSeksi + ' - ' + seksi.vcNamaSeksi;
                            seksiSelect.appendChild(option);
                        });
                        // Enable dropdown setelah data dimuat
                        seksiSelect.disabled = false;
                    } else {
                        seksiSelect.innerHTML = '<option value="">Tidak ada data</option>';
                        seksiSelect.disabled = true;
                    }
                    // Panggil callback jika ada
                    if (callback) callback();
                })
                .catch(error => {
                    console.error('Error loading seksis:', error);
                    seksiSelect.innerHTML = '<option value="">Error memuat data</option>';
                    seksiSelect.disabled = true;
                    // Panggil callback jika ada
                    if (callback) callback();
                });
        }

        // Load jabatans filtered by divisi
        function loadJabatans(divisiKode, callback) {
            jabatanSelect.disabled = true;
            const currentValue = jabatanSelect.value; // Save current value
            jabatanSelect.innerHTML = '<option value="">Memuat...</option>';

            fetch(makeUrl('karyawan/get-jabatans'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        divisi: divisiKode
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        jabatanSelect.innerHTML = '<option value="">Pilih Jabatan</option>';
                        data.jabatans.forEach(jabatan => {
                            const option = document.createElement('option');
                            option.value = jabatan.vcKodeJabatan;
                            option.textContent = jabatan.vcKodeJabatan + ' - ' + jabatan.vcNamaJabatan;
                            jabatanSelect.appendChild(option);
                        });
                        // Restore previous value if it exists in the filtered list
                        if (currentValue) {
                            const optionExists = Array.from(jabatanSelect.options).some(opt => opt.value === currentValue);
                            if (optionExists) {
                                jabatanSelect.value = currentValue;
                            }
                        }
                        jabatanSelect.disabled = false;
                    } else {
                        jabatanSelect.innerHTML = '<option value="">Tidak ada data</option>';
                        jabatanSelect.disabled = true;
                    }
                    // Panggil callback jika ada
                    if (callback) callback();
                })
                .catch(error => {
                    console.error('Error loading jabatans:', error);
                    jabatanSelect.innerHTML = '<option value="">Error memuat data</option>';
                    jabatanSelect.disabled = true;
                    // Panggil callback jika ada
                    if (callback) callback();
                });
        }

        // Load all jabatans (when no divisi selected)
        function loadAllJabatans(callback) {
            jabatanSelect.innerHTML = '<option value="">Pilih Jabatan</option>';
            initialJabatans.forEach(jabatan => {
                if (jabatan.value) { // Skip empty option
                    const option = document.createElement('option');
                    option.value = jabatan.value;
                    option.textContent = jabatan.text;
                    jabatanSelect.appendChild(option);
                }
            });
            // Panggil callback jika ada
            if (callback) callback();
        }


    });
</script>
@endpush