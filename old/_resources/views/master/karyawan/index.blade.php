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
                                    <div class="border rounded p-4 mb-2 position-relative" id="photoPreview" style="width: 120px; height: 120px; margin: 0 auto; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                        <img id="photoImage" src="" alt="Foto Karyawan" style="max-width: 100%; max-height: 100%; display: none; object-fit: cover;">
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
                                <button class="nav-link" id="pendidikan-tab" data-bs-toggle="tab" data-bs-target="#pendidikan" type="button" role="tab">
                                    Pendidikan
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="keluarga-tab" data-bs-toggle="tab" data-bs-target="#keluarga" type="button" role="tab">
                                    Keluarga
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
                                            <input type="text" class="form-control" id="seksi" name="vcKodeSeksi" maxlength="25">
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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentNik = null;
        let isEditMode = false;
        let familyMembers = [];
        let pendidikanMembers = [];
        let currentFamilyEditIndex = null;
        let currentPendidikanEditIndex = null;

        // Hierarchical dropdown elements
        const divisiSelect = document.getElementById('divisi');
        const departemenSelect = document.getElementById('departemen');
        const bagianSelect = document.getElementById('bagian');

        // Modal instances
        const familyModalEl = document.getElementById('addFamilyModal');
        const pendidikanModalEl = document.getElementById('addPendidikanModal');
        const familyModal = familyModalEl ? new bootstrap.Modal(familyModalEl) : null;
        const pendidikanModal = pendidikanModalEl ? new bootstrap.Modal(pendidikanModalEl) : null;

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

        // Initialize
        initializeForm();

        // New button
        document.getElementById('newBtn').addEventListener('click', function() {
            resetForm();
            enableForm();
            isEditMode = false;
            currentNik = null;
            // Update Copy Data button state
            updateCopyDataButton();
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
            fetch(`/karyawan/${currentNik}/copy-data`, {
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
            if (isEditing && familyMembers[currentFamilyEditIndex]) {
                familyMembers[currentFamilyEditIndex] = {
                    ...familyMembers[currentFamilyEditIndex],
                    ...familyData
                };
            } else {
                familyMembers.push(familyData);
            }
            currentFamilyEditIndex = null;
            updateFamilyTable();

            // Reset form and close modal
            this.reset();
            familyModal?.hide();
        });

        // Search functionality
        document.getElementById('search').addEventListener('input', function() {
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
        });

        // Row click to load karyawan data
        document.querySelectorAll('.karyawan-row').forEach(row => {
            row.addEventListener('click', function() {
                // Remove active class from all rows
                document.querySelectorAll('.karyawan-row').forEach(r => r.classList.remove('table-active'));
                // Add active class to clicked row
                this.classList.add('table-active');

                const nik = this.getAttribute('data-nik');
                loadKaryawanData(nik);
            });
        });

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
            fetch('/karyawan/generate-nik', {
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
                fetch('/karyawan/generate-nik', {
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

        function enableForm() {
            document.getElementById('saveBtn').disabled = false;
            document.getElementById('cancelBtn').disabled = false;
            document.getElementById('deleteBtn').disabled = false;

            // Copy Data button: hanya enabled jika ada currentNik (record dipilih) dan tidak aktif
            updateCopyDataButton();

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
        }

        function disableForm() {
            document.getElementById('saveBtn').disabled = true;
            document.getElementById('cancelBtn').disabled = true;
            document.getElementById('deleteBtn').disabled = true;
            document.getElementById('copyDataBtn').disabled = true;

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
            currentFamilyEditIndex = null;
            currentPendidikanEditIndex = null;

            // Reset hierarchical dropdowns
            departemenSelect.innerHTML = '<option value="">Pilih Divisi terlebih dahulu</option>';
            departemenSelect.disabled = true;
            bagianSelect.innerHTML = '<option value="">Pilih Departemen terlebih dahulu</option>';
            bagianSelect.disabled = true;

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
            fetch(`/karyawan/${nik}`, {
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
            fetch(`/karyawan/${nik}/keluarga`, {
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
            if (confirm('Apakah Anda yakin ingin menghapus anggota keluarga ini?')) {
                familyMembers.splice(index, 1);
                updateFamilyTable();
            }
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
                let url = `/karyawan/${currentNik}/pendidikan`;
                let method = 'POST';

                if (isEditing && existingData && (existingData._key || existingData.education_level)) {
                    const key = encodeURIComponent(existingData._key || existingData.education_level);
                    url = `/karyawan/${currentNik}/pendidikan/${key}`;
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
            fetch(`/karyawan/${nik}/pendidikan`, {
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
                fetch(`/karyawan/${currentNik}/pendidikan/${key}`, {
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
            return fetch('/karyawan/copy-keluarga', {
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
            return fetch('/karyawan/copy-pendidikan', {
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
                        formData.append(input.name, input.value);
                    }
                }
            });

            // Add flag to remove photo if needed
            if (photoToRemove) {
                formData.append('remove_photo', '1');
            }

            // Catatan: Data keluarga tidak dikirim di sini karena tab Keluarga punya CRUD sendiri
            // Data keluarga dikelola melalui endpoint terpisah di tab Keluarga

            const url = isEditMode ? `/karyawan/${currentNik}` : '/karyawan';
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
                                    savePendidikanMembers(oldNik, newNik)
                                ])
                                .then(([keluargaResult, pendidikanResult]) => {
                                    let info = [];
                                    if (keluargaResult.copied > 0) {
                                        info.push(`keluarga (${keluargaResult.copied} anggota)`);
                                    }
                                    if (pendidikanResult.copied > 0) {
                                        info.push(`pendidikan (${pendidikanResult.copied} record)`);
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
            fetch(`/karyawan/${nik}`, {
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

        // Hierarchical dropdown functionality
        // When divisi changes, load departemens
        divisiSelect.addEventListener('change', function() {
            const divisiKode = this.value;

            // Reset departemen and bagian
            departemenSelect.innerHTML = '<option value="">Pilih Departemen</option>';
            departemenSelect.disabled = !divisiKode;
            bagianSelect.innerHTML = '<option value="">Pilih Bagian</option>';
            bagianSelect.disabled = true;

            if (divisiKode) {
                loadDepartemens(divisiKode);
            }
        });

        // When departemen changes, load bagians
        departemenSelect.addEventListener('change', function() {
            const divisiKode = divisiSelect.value;
            const deptKode = this.value;

            // Reset bagian
            bagianSelect.innerHTML = '<option value="">Pilih Bagian</option>';
            bagianSelect.disabled = !deptKode;

            if (divisiKode && deptKode) {
                loadBagians(divisiKode, deptKode);
            }
        });

        function loadDepartemens(divisiKode, callback) {
            departemenSelect.disabled = true;
            departemenSelect.innerHTML = '<option value="">Memuat...</option>';

            fetch('/karyawan/get-departemens', {
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
                        departemenSelect.disabled = false;
                    } else {
                        departemenSelect.innerHTML = '<option value="">Tidak ada data</option>';
                    }
                    // Panggil callback jika ada
                    if (callback) callback();
                })
                .catch(error => {
                    console.error('Error loading departemens:', error);
                    departemenSelect.innerHTML = '<option value="">Error memuat data</option>';
                    // Panggil callback jika ada
                    if (callback) callback();
                });
        }

        function loadBagians(divisiKode, deptKode, callback) {
            bagianSelect.disabled = true;
            bagianSelect.innerHTML = '<option value="">Memuat...</option>';

            fetch('/karyawan/get-bagians', {
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
                        bagianSelect.disabled = false;
                    } else {
                        bagianSelect.innerHTML = '<option value="">Tidak ada data</option>';
                    }
                    // Panggil callback jika ada
                    if (callback) callback();
                })
                .catch(error => {
                    console.error('Error loading bagians:', error);
                    bagianSelect.innerHTML = '<option value="">Error memuat data</option>';
                    // Panggil callback jika ada
                    if (callback) callback();
                });
        }

        // Override populateForm to handle hierarchical dropdowns
        const originalPopulateForm = populateForm;
        populateForm = function(karyawan) {
            // Call original populateForm first
            originalPopulateForm(karyawan);

            // Handle hierarchical dropdowns dengan callback pattern
            if (karyawan.Divisi) {
                // Set divisi first
                divisiSelect.value = karyawan.Divisi;

                // Load departemens dengan callback untuk set departemen setelah ter-load
                if (karyawan.dept) {
                    loadDepartemens(karyawan.Divisi, function() {
                        // Set departemen setelah departemens ter-load
                        if (departemenSelect && karyawan.dept) {
                            departemenSelect.value = karyawan.dept;

                            // Load bagians dengan callback untuk set bagian setelah ter-load
                            if (karyawan.vcKodeBagian) {
                                loadBagians(karyawan.Divisi, karyawan.dept, function() {
                                    // Set bagian setelah bagians ter-load
                                    if (bagianSelect && karyawan.vcKodeBagian) {
                                        bagianSelect.value = karyawan.vcKodeBagian;
                                    }
                                });
                            }
                        }
                    });
                } else {
                    // Jika tidak ada departemen, tetap load departemens
                    loadDepartemens(karyawan.Divisi);
                }
            }
        };
    });
</script>
@endpush