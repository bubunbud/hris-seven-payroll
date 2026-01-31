@extends('layouts.app')

@section('title', 'List Data Karyawan Aktif - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-users me-2"></i>List Data Karyawan Aktif
                </h2>
                <a href="{{ route('list-karyawan-aktif.export', request()->query()) }}" class="btn btn-success">
                    <i class="fas fa-file-excel me-2"></i>Export Excel
                </a>
            </div>

            <!-- Filter Section -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('list-karyawan-aktif.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="divisi" class="form-label">Bisnis Unit</label>
                                <select class="form-select" id="divisi" name="divisi">
                                    <option value="">Semua Bisnis Unit</option>
                                    @foreach($divisis as $divisi)
                                    <option value="{{ $divisi->vcKodeDivisi }}" {{ request('divisi') == $divisi->vcKodeDivisi ? 'selected' : '' }}>
                                        {{ $divisi->vcKodeDivisi }} - {{ $divisi->vcNamaDivisi }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="departemen" class="form-label">Departemen</label>
                                <select class="form-select" id="departemen" name="departemen">
                                    <option value="">Semua Departemen</option>
                                    @foreach($departemens as $departemen)
                                    <option value="{{ $departemen->vcKodeDept }}" {{ request('departemen') == $departemen->vcKodeDept ? 'selected' : '' }}>
                                        {{ $departemen->vcKodeDept }} - {{ $departemen->vcNamaDept }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="bagian" class="form-label">Bagian</label>
                                <select class="form-select" id="bagian" name="bagian">
                                    <option value="">Semua Bagian</option>
                                    @foreach($bagians as $bagian)
                                    <option value="{{ $bagian->vcKodeBagian }}" {{ request('bagian') == $bagian->vcKodeBagian ? 'selected' : '' }}>
                                        {{ $bagian->vcKodeBagian }} - {{ $bagian->vcNamaBagian }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="group_pegawai" class="form-label">Group Pegawai</label>
                                <select class="form-select" id="group_pegawai" name="group_pegawai">
                                    <option value="">Semua Group</option>
                                    @foreach($groupPegawais as $group)
                                    <option value="{{ $group }}" {{ request('group_pegawai') == $group ? 'selected' : '' }}>
                                        {{ $group }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Cari
                                </button>
                                <a href="{{ route('list-karyawan-aktif.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-redo me-2"></i>Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Section -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-table me-2"></i>Data Karyawan Aktif
                        <span class="badge bg-light text-dark ms-2">{{ $karyawans->total() }} karyawan</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
                            <thead class="table-primary">
                                <tr>
                                    <th width="4%">No</th>
                                    <th width="8%">NIK</th>
                                    <th width="12%">Nama</th>
                                    <th width="10%">Bisnis Unit</th>
                                    <th width="10%">Departemen</th>
                                    <th width="10%">Bagian</th>
                                    <th width="10%">Jabatan</th>
                                    <th width="10%">Tanggal Masuk</th>
                                    <th width="10%">Group Pegawai</th>
                                    <th width="8%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($karyawans as $index => $karyawan)
                                <tr>
                                    <td class="text-center">{{ $karyawans->firstItem() + $index }}</td>
                                    <td><strong>{{ $karyawan->Nik }}</strong></td>
                                    <td>{{ $karyawan->Nama ?? '-' }}</td>
                                    <td>{{ $karyawan->divisi->vcNamaDivisi ?? '-' }}</td>
                                    <td>{{ $karyawan->departemen->vcNamaDept ?? '-' }}</td>
                                    <td>{{ $karyawan->bagian->vcNamaBagian ?? '-' }}</td>
                                    <td>{{ $karyawan->jabatan->vcNamaJabatan ?? '-' }}</td>
                                    <td>{{ $karyawan->Tgl_Masuk ? \Carbon\Carbon::parse($karyawan->Tgl_Masuk)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $karyawan->Group_pegawai ?? '-' }}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-info" onclick="showDetailModal('{{ $karyawan->Nik }}')" title="Detail">
                                            <i class="fas fa-eye"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted">
                                        <i class="fas fa-info-circle me-2"></i>Tidak ada data karyawan aktif
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $karyawans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Karyawan -->
<div class="modal fade" id="detailKaryawanModal" tabindex="-1" aria-labelledby="detailKaryawanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="detailKaryawanModalLabel">
                    <i class="fas fa-user me-2"></i>Detail Karyawan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="loadingDetail" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat data karyawan...</p>
                </div>
                <div id="detailContent" style="display: none;">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs mb-3" id="detailTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal-detail" type="button" role="tab">
                                Personal
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pekerjaan-tab" data-bs-toggle="tab" data-bs-target="#pekerjaan-detail" type="button" role="tab">
                                Pekerjaan
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="detailTabContent">
                        <!-- Personal Tab -->
                        <div class="tab-pane fade show active" id="personal-detail" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="40%">NIK</th>
                                            <td id="detail-nik">-</td>
                                        </tr>
                                        <tr>
                                            <th>Nama Lengkap</th>
                                            <td id="detail-nama">-</td>
                                        </tr>
                                        <tr>
                                            <th>Nama Depan</th>
                                            <td id="detail-nama-depan">-</td>
                                        </tr>
                                        <tr>
                                            <th>Nama Tengah</th>
                                            <td id="detail-nama-tengah">-</td>
                                        </tr>
                                        <tr>
                                            <th>Nama Akhir</th>
                                            <td id="detail-nama-akhir">-</td>
                                        </tr>
                                        <tr>
                                            <th>Tempat Lahir</th>
                                            <td id="detail-tempat-lahir">-</td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal Lahir</th>
                                            <td id="detail-tanggal-lahir">-</td>
                                        </tr>
                                        <tr>
                                            <th>Jenis Kelamin</th>
                                            <td id="detail-jenis-kelamin">-</td>
                                        </tr>
                                        <tr>
                                            <th>Status Kawin</th>
                                            <td id="detail-status-kawin">-</td>
                                        </tr>
                                        <tr>
                                            <th>Agama</th>
                                            <td id="detail-agama">-</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="40%">Warga Negara</th>
                                            <td id="detail-warga-negara">-</td>
                                        </tr>
                                        <tr>
                                            <th>Alamat</th>
                                            <td id="detail-alamat">-</td>
                                        </tr>
                                        <tr>
                                            <th>Kecamatan</th>
                                            <td id="detail-kecamatan">-</td>
                                        </tr>
                                        <tr>
                                            <th>Kota</th>
                                            <td id="detail-kota">-</td>
                                        </tr>
                                        <tr>
                                            <th>Kode Pos</th>
                                            <td id="detail-kode-pos">-</td>
                                        </tr>
                                        <tr>
                                            <th>Telepon</th>
                                            <td id="detail-telp">-</td>
                                        </tr>
                                        <tr>
                                            <th>Handphone 1</th>
                                            <td id="detail-hp1">-</td>
                                        </tr>
                                        <tr>
                                            <th>Handphone 2</th>
                                            <td id="detail-hp2">-</td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td id="detail-email">-</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Pekerjaan Tab -->
                        <div class="tab-pane fade" id="pekerjaan-detail" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="40%">Bisnis Unit</th>
                                            <td id="detail-divisi">-</td>
                                        </tr>
                                        <tr>
                                            <th>Departemen</th>
                                            <td id="detail-departemen">-</td>
                                        </tr>
                                        <tr>
                                            <th>Bagian</th>
                                            <td id="detail-bagian">-</td>
                                        </tr>
                                        <tr>
                                            <th>Jabatan</th>
                                            <td id="detail-jabatan">-</td>
                                        </tr>
                                        <tr>
                                            <th>Golongan</th>
                                            <td id="detail-golongan">-</td>
                                        </tr>
                                        <tr>
                                            <th>Shift</th>
                                            <td id="detail-shift">-</td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal Masuk</th>
                                            <td id="detail-tgl-masuk">-</td>
                                        </tr>
                                        <tr>
                                            <th>Group Pegawai</th>
                                            <td id="detail-group-pegawai">-</td>
                                        </tr>
                                        <tr>
                                            <th>Status Pegawai</th>
                                            <td id="detail-status-pegawai">-</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="40%">Job ID</th>
                                            <td id="detail-job-id">-</td>
                                        </tr>
                                        <tr>
                                            <th>Status Kerja</th>
                                            <td id="detail-status-kerja">-</td>
                                        </tr>
                                        <tr>
                                            <th>Pendidikan Akhir</th>
                                            <td id="detail-pendidikan-akhir">-</td>
                                        </tr>
                                        <tr>
                                            <th>Nama Universitas</th>
                                            <td id="detail-universitas">-</td>
                                        </tr>
                                        <tr>
                                            <th>Jurusan</th>
                                            <td id="detail-jurusan">-</td>
                                        </tr>
                                        <tr>
                                            <th>Tahun Lulus</th>
                                            <td id="detail-tahun-lulus">-</td>
                                        </tr>
                                        <tr>
                                            <th>No. KTP</th>
                                            <td id="detail-no-badge">-</td>
                                        </tr>
                                        <tr>
                                            <th>No Rekening</th>
                                            <td id="detail-no-rekening">-</td>
                                        </tr>
                                        <tr>
                                            <th>Status Aktif</th>
                                            <td id="detail-status-aktif">-</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <a href="#" id="btnEditKaryawan" class="btn btn-primary" target="_blank">
                    <i class="fas fa-edit me-2"></i>Edit di Master Karyawan
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Function to show detail modal
function showDetailModal(nik) {
    const modal = new bootstrap.Modal(document.getElementById('detailKaryawanModal'));
    const loadingDiv = document.getElementById('loadingDetail');
    const contentDiv = document.getElementById('detailContent');
    
    // Reset modal
    loadingDiv.style.display = 'block';
    contentDiv.style.display = 'none';
    
    // Show modal
    modal.show();
    
    // Fetch karyawan data
    fetch(`{{ url('karyawan') }}/${nik}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.karyawan) {
            const k = data.karyawan;
            
            // Format date helper
            function formatDate(dateStr) {
                if (!dateStr) return '-';
                try {
                    const date = new Date(dateStr);
                    return date.toLocaleDateString('id-ID', { 
                        day: '2-digit', 
                        month: '2-digit', 
                        year: 'numeric' 
                    });
                } catch (e) {
                    return dateStr;
                }
            }
            
            // Populate Personal Tab
            document.getElementById('detail-nik').textContent = k.Nik || '-';
            document.getElementById('detail-nama').textContent = k.Nama || '-';
            document.getElementById('detail-nama-depan').textContent = k.Nama_Depan || '-';
            document.getElementById('detail-nama-tengah').textContent = k.Nama_Tengah || '-';
            document.getElementById('detail-nama-akhir').textContent = k.Nama_Akhir || '-';
            document.getElementById('detail-tempat-lahir').textContent = k.Tempat_lahir || '-';
            document.getElementById('detail-tanggal-lahir').textContent = formatDate(k.TTL);
            document.getElementById('detail-jenis-kelamin').textContent = k.Jenis_Kelamin || '-';
            document.getElementById('detail-status-kawin').textContent = k.Status_Kawin || '-';
            document.getElementById('detail-agama').textContent = k.Agama || '-';
            document.getElementById('detail-warga-negara').textContent = k.Warga_Negara || '-';
            document.getElementById('detail-alamat').textContent = k.Alamat || '-';
            document.getElementById('detail-kecamatan').textContent = k.Kecamatan || '-';
            document.getElementById('detail-kota').textContent = k.Kota || '-';
            document.getElementById('detail-kode-pos').textContent = k.Kode_pos || '-';
            document.getElementById('detail-telp').textContent = k.Telp || '-';
            document.getElementById('detail-hp1').textContent = k.Cell_Phone1 || '-';
            document.getElementById('detail-hp2').textContent = k.Cell_Phone2 || '-';
            document.getElementById('detail-email').textContent = k.Personal_Email || '-';
            
            // Populate Pekerjaan Tab
            document.getElementById('detail-divisi').textContent = k.divisi?.vcNamaDivisi || '-';
            document.getElementById('detail-departemen').textContent = k.departemen?.vcNamaDept || '-';
            document.getElementById('detail-bagian').textContent = k.bagian?.vcNamaBagian || '-';
            document.getElementById('detail-jabatan').textContent = k.jabatan?.vcNamaJabatan || '-';
            document.getElementById('detail-golongan').textContent = k.Gol || '-';
            document.getElementById('detail-shift').textContent = k.shift ? `${k.shift.vcShift} (${k.shift.vcMasuk} - ${k.shift.vcPulang})` : '-';
            document.getElementById('detail-tgl-masuk').textContent = formatDate(k.Tgl_Masuk);
            document.getElementById('detail-group-pegawai').textContent = k.Group_pegawai || '-';
            document.getElementById('detail-status-pegawai').textContent = k.Status_Pegawai || '-';
            document.getElementById('detail-job-id').textContent = k.Job_ID || '-';
            document.getElementById('detail-status-kerja').textContent = k.Status_kerja || '-';
            document.getElementById('detail-pendidikan-akhir').textContent = k.Pendidikan_Akhir || '-';
            document.getElementById('detail-universitas').textContent = k.Nama_universitas || '-';
            document.getElementById('detail-jurusan').textContent = k.Jurusan || '-';
            document.getElementById('detail-tahun-lulus').textContent = k.Thn_lulus || '-';
            document.getElementById('detail-no-badge').textContent = k.intNoBadge || '-';
            document.getElementById('detail-no-rekening').textContent = k.intNorek || '-';
            document.getElementById('detail-status-aktif').textContent = k.vcAktif == '1' ? 'Aktif' : 'Tidak Aktif';
            
            // Set edit button link
            document.getElementById('btnEditKaryawan').href = `{{ route('karyawan.index') }}?nik=${k.Nik}`;
            
            // Show content
            loadingDiv.style.display = 'none';
            contentDiv.style.display = 'block';
        } else {
            alert('Gagal memuat data karyawan');
            modal.hide();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memuat data: ' + error.message);
        modal.hide();
    });
}

// Auto-load departemen when divisi changes
document.getElementById('divisi')?.addEventListener('change', function() {
    const divisi = this.value;
    const departemenSelect = document.getElementById('departemen');
    const bagianSelect = document.getElementById('bagian');

    if (divisi) {
        fetch('{{ route("karyawan.get-departemens") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ divisi: divisi })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                departemenSelect.innerHTML = '<option value="">Semua Departemen</option>' +
                    data.departemens.map(d => `<option value="${d.vcKodeDept}">${d.vcKodeDept} - ${d.vcNamaDept}</option>`).join('');
            }
        });
    } else {
        departemenSelect.innerHTML = '<option value="">Semua Departemen</option>';
    }

    bagianSelect.innerHTML = '<option value="">Semua Bagian</option>';
});

// Auto-load bagian when departemen changes
document.getElementById('departemen')?.addEventListener('change', function() {
    const divisi = document.getElementById('divisi').value;
    const departemen = this.value;
    const bagianSelect = document.getElementById('bagian');

    if (divisi && departemen) {
        fetch('{{ route("karyawan.get-bagians") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ divisi: divisi, departemen: departemen })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bagianSelect.innerHTML = '<option value="">Semua Bagian</option>' +
                    data.bagians.map(b => `<option value="${b.vcKodeBagian}">${b.vcKodeBagian} - ${b.vcNamaBagian}</option>`).join('');
            }
        });
    } else {
        bagianSelect.innerHTML = '<option value="">Semua Bagian</option>';
    }
});
</script>
@endpush

