@extends('layouts.app')

@section('title', 'Group Hierarki - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-sitemap me-2"></i>Group Divisi Hierarki
                </h2>
                <button type="button" class="btn btn-danger" id="btnKeluar">
                    <i class="fas fa-times me-2"></i>Keluar
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="hirarkiTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ request('tab') != 'bagian' && request('tab') != 'seksi' ? 'active' : '' }}" id="dept-tab" data-bs-toggle="tab" data-bs-target="#dept" type="button" role="tab" aria-controls="dept" aria-selected="{{ request('tab') != 'bagian' && request('tab') != 'seksi' ? 'true' : 'false' }}">
                                Group Departement
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ request('tab') == 'bagian' ? 'active' : '' }}" id="bagian-tab" data-bs-toggle="tab" data-bs-target="#bagian" type="button" role="tab" aria-controls="bagian" aria-selected="{{ request('tab') == 'bagian' ? 'true' : 'false' }}">
                                Group Bagian
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ request('tab') == 'seksi' ? 'active' : '' }}" id="seksi-tab" data-bs-toggle="tab" data-bs-target="#seksi" type="button" role="tab" aria-controls="seksi" aria-selected="{{ request('tab') == 'seksi' ? 'true' : 'false' }}">
                                Group Seksi
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="hirarkiTabContent">
                        <!-- Tab Group Departement -->
                        <div class="tab-pane fade {{ request('tab') != 'bagian' && request('tab') != 'seksi' ? 'show active' : '' }}" id="dept" role="tabpanel" aria-labelledby="dept-tab">
                            <form method="GET" action="{{ route('hirarki.index') }}" id="formDept">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="divisi_dept" class="form-label">Divisi</label>
                                        <div class="input-group">
                                            <select class="form-select" id="divisi_dept" name="divisi" onchange="document.getElementById('formDept').submit();">
                                                <option value="">-- Pilih Divisi --</option>
                                                @foreach($divisis as $div)
                                                <option value="{{ $div->vcKodeDivisi }}" {{ $selectedDivisi == $div->vcKodeDivisi ? 'selected' : '' }}>
                                                    {{ $div->vcKodeDivisi }} -> {{ $div->vcNamaDivisi }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="departemen_dept" class="form-label">Departement</label>
                                        <div class="input-group">
                                            <select class="form-select" id="departemen_dept" name="departemen">
                                                <option value="">-- Pilih Departemen --</option>
                                                @foreach($departemens as $dept)
                                                <option value="{{ $dept->vcKodeDept }}" {{ $selectedDept == $dept->vcKodeDept ? 'selected' : '' }}>
                                                    {{ $dept->vcKodeDept }} -> {{ $dept->vcNamaDept }}
                                                </option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-primary" id="btnTambahDept">
                                                <i class="fas fa-plus me-1"></i>Tambah
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="20%">Kode Divisi</th>
                                            <th width="20%">Kode Departement</th>
                                            <th width="50%">Nama Departement</th>
                                            <th width="10%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($hirarkiDept as $item)
                                        <tr>
                                            <td>{{ $item->vcKodeDivisi }}</td>
                                            <td>{{ $item->vcKodeDept }}</td>
                                            <td>{{ $item->vcNamaDept }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteDept('{{ $item->vcKodeDivisi }}', '{{ $item->vcKodeDept }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Tidak ada data. Pilih Divisi terlebih dahulu.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab Group Bagian -->
                        <div class="tab-pane fade {{ request('tab') == 'bagian' ? 'show active' : '' }}" id="bagian" role="tabpanel" aria-labelledby="bagian-tab">
                            <form method="GET" action="{{ route('hirarki.index') }}" id="formBagian">
                                <input type="hidden" name="tab" value="bagian">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="divisi_bagian" class="form-label">Divisi</label>
                                        <select class="form-select" id="divisi_bagian" name="divisi" onchange="document.getElementById('formBagian').submit();">
                                            <option value="">-- Pilih Divisi --</option>
                                            @foreach($divisis as $div)
                                            <option value="{{ $div->vcKodeDivisi }}" {{ $selectedDivisi == $div->vcKodeDivisi ? 'selected' : '' }}>
                                                {{ $div->vcKodeDivisi }} -> {{ $div->vcNamaDivisi }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="departemen_bagian" class="form-label">Departement</label>
                                        <select class="form-select" id="departemen_bagian" name="departemen" onchange="document.getElementById('formBagian').submit();">
                                            <option value="">-- Pilih Departemen --</option>
                                            @foreach($deptsInDivisi as $dept)
                                            <option value="{{ $dept->vcKodeDept }}" {{ $selectedDept == $dept->vcKodeDept ? 'selected' : '' }}>
                                                {{ $dept->vcKodeDept }} -> {{ $dept->vcNamaDept }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="bagian_bagian" class="form-label">Bagian</label>
                                        <div class="input-group">
                                            <select class="form-select" id="bagian_bagian" name="bagian">
                                                <option value="">-- Pilih Bagian --</option>
                                                @foreach($bagians as $bag)
                                                <option value="{{ $bag->vcKodeBagian }}" {{ $selectedBagian == $bag->vcKodeBagian ? 'selected' : '' }}>
                                                    {{ $bag->vcKodeBagian }} -> {{ $bag->vcNamaBagian }}
                                                </option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-primary" id="btnTambahBagian">
                                                <i class="fas fa-plus me-1"></i>Tambah
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="15%">Kode Divisi</th>
                                            <th width="15%">Kode Departement</th>
                                            <th width="15%">Kode Bagian</th>
                                            <th width="45%">Nama Bagian</th>
                                            <th width="10%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($hirarkiBagian as $item)
                                        <tr>
                                            <td>{{ $item->vcKodeDivisi }}</td>
                                            <td>{{ $item->vcKodeDept }}</td>
                                            <td>{{ $item->vcKodeBagian }}</td>
                                            <td>{{ $item->vcNamaBagian }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteBagian('{{ $item->vcKodeDivisi }}', '{{ $item->vcKodeDept }}', '{{ $item->vcKodeBagian }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Tidak ada data. Pilih Divisi dan Departemen terlebih dahulu.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab Group Seksi -->
                        <div class="tab-pane fade {{ request('tab') == 'seksi' ? 'show active' : '' }}" id="seksi" role="tabpanel" aria-labelledby="seksi-tab">
                            <form method="GET" action="{{ route('hirarki.index') }}" id="formSeksi">
                                <input type="hidden" name="tab" value="seksi">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label for="divisi_seksi" class="form-label">Divisi</label>
                                        <select class="form-select" id="divisi_seksi" name="divisi" onchange="document.getElementById('formSeksi').submit();">
                                            <option value="">-- Pilih Divisi --</option>
                                            @foreach($divisis as $div)
                                            <option value="{{ $div->vcKodeDivisi }}" {{ $selectedDivisi == $div->vcKodeDivisi ? 'selected' : '' }}>
                                                {{ $div->vcKodeDivisi }} -> {{ $div->vcNamaDivisi }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="departemen_seksi" class="form-label">Departement</label>
                                        <select class="form-select" id="departemen_seksi" name="departemen" onchange="document.getElementById('formSeksi').submit();">
                                            <option value="">-- Pilih Departemen --</option>
                                            @foreach($deptsInDivisi as $dept)
                                            <option value="{{ $dept->vcKodeDept }}" {{ $selectedDept == $dept->vcKodeDept ? 'selected' : '' }}>
                                                {{ $dept->vcKodeDept }} -> {{ $dept->vcNamaDept }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="bagian_seksi" class="form-label">Bagian</label>
                                        <select class="form-select" id="bagian_seksi" name="bagian" onchange="document.getElementById('formSeksi').submit();">
                                            <option value="">-- Pilih Bagian --</option>
                                            @foreach($bagiansInDivisiDept as $bag)
                                            <option value="{{ $bag->vcKodeBagian }}" {{ $selectedBagian == $bag->vcKodeBagian ? 'selected' : '' }}>
                                                {{ $bag->vcKodeBagian }} -> {{ $bag->vcNamaBagian }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="seksi_seksi" class="form-label">Seksi</label>
                                        <div class="input-group">
                                            <select class="form-select" id="seksi_seksi" name="seksi">
                                                <option value="">-- Pilih Seksi --</option>
                                                @foreach($seksis as $sek)
                                                <option value="{{ $sek->vcKodeSeksi }}" {{ $selectedSeksi == $sek->vcKodeSeksi ? 'selected' : '' }}>
                                                    {{ $sek->vcKodeSeksi }} -> {{ $sek->vcNamaSeksi }}
                                                </option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-primary" id="btnTambahSeksi">
                                                <i class="fas fa-plus me-1"></i>Tambah
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="12%">Kode Divisi</th>
                                            <th width="12%">Kode Departement</th>
                                            <th width="12%">Kode Bagian</th>
                                            <th width="12%">Kode Seksi</th>
                                            <th width="42%">Nama Seksi</th>
                                            <th width="10%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($hirarkiSeksi as $item)
                                        <tr>
                                            <td>{{ $item->vcKodeDivisi }}</td>
                                            <td>{{ $item->vcKodeDept }}</td>
                                            <td>{{ $item->vcKodeBagian }}</td>
                                            <td>{{ $item->vcKodeSeksi }}</td>
                                            <td>{{ $item->vcNamaSeksi }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteSeksi('{{ $item->vcKodeDivisi }}', '{{ $item->vcKodeDept }}', '{{ $item->vcKodeBagian }}', '{{ $item->vcKodeSeksi }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Tidak ada data. Pilih Divisi, Departemen, dan Bagian terlebih dahulu.</td>
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
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Button Keluar
        document.getElementById('btnKeluar').addEventListener('click', function() {
            window.location.href = '/';
        });

        // Tambah Departemen
        document.getElementById('btnTambahDept').addEventListener('click', function() {
            const divisi = document.getElementById('divisi_dept').value;
            const departemen = document.getElementById('departemen_dept').value;

            if (!divisi) {
                alert('Pilih Divisi terlebih dahulu!');
                return;
            }

            if (!departemen) {
                alert('Pilih Departemen terlebih dahulu!');
                return;
            }

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('vcKodeDivisi', divisi);
            formData.append('vcKodeDept', departemen);

            fetch('{{ route("hirarki.store-dept") }}', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert(data.message || 'Terjadi kesalahan!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menyimpan data!');
                });
        });

        // Tambah Bagian
        document.getElementById('btnTambahBagian').addEventListener('click', function() {
            const divisi = document.getElementById('divisi_bagian').value;
            const departemen = document.getElementById('departemen_bagian').value;
            const bagian = document.getElementById('bagian_bagian').value;

            if (!divisi) {
                alert('Pilih Divisi terlebih dahulu!');
                return;
            }

            if (!departemen) {
                alert('Pilih Departemen terlebih dahulu!');
                return;
            }

            if (!bagian) {
                alert('Pilih Bagian terlebih dahulu!');
                return;
            }

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('vcKodeDivisi', divisi);
            formData.append('vcKodeDept', departemen);
            formData.append('vcKodeBagian', bagian);

            fetch('{{ route("hirarki.store-bagian") }}', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert(data.message || 'Terjadi kesalahan!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menyimpan data!');
                });
        });
    });

    // Delete Departemen
    function deleteDept(divisi, dept) {
        if (!confirm('Yakin ingin menghapus relasi ini?')) {
            return;
        }

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('vcKodeDivisi', divisi);
        formData.append('vcKodeDept', dept);

        fetch('{{ route("hirarki.destroy-dept") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Terjadi kesalahan!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus data!');
            });
    }

    // Delete Bagian
    function deleteBagian(divisi, dept, bagian) {
        if (!confirm('Yakin ingin menghapus relasi ini?')) {
            return;
        }

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('vcKodeDivisi', divisi);
        formData.append('vcKodeDept', dept);
        formData.append('vcKodeBagian', bagian);

        fetch('{{ route("hirarki.destroy-bagian") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Terjadi kesalahan!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus data!');
            });
    }

    // Tambah Seksi
    document.getElementById('btnTambahSeksi')?.addEventListener('click', function() {
        const divisi = document.getElementById('divisi_seksi').value;
        const departemen = document.getElementById('departemen_seksi').value;
        const bagian = document.getElementById('bagian_seksi').value;
        const seksi = document.getElementById('seksi_seksi').value;

        if (!divisi) {
            alert('Pilih Divisi terlebih dahulu!');
            return;
        }

        if (!departemen) {
            alert('Pilih Departemen terlebih dahulu!');
            return;
        }

        if (!bagian) {
            alert('Pilih Bagian terlebih dahulu!');
            return;
        }

        if (!seksi) {
            alert('Pilih Seksi terlebih dahulu!');
            return;
        }

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('vcKodeDivisi', divisi);
        formData.append('vcKodeDept', departemen);
        formData.append('vcKodeBagian', bagian);
        formData.append('vcKodeSeksi', seksi);

        fetch('{{ route("hirarki.store-seksi") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Terjadi kesalahan!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyimpan data!');
            });
    });

    // Delete Seksi
    function deleteSeksi(divisi, dept, bagian, seksi) {
        if (!confirm('Yakin ingin menghapus relasi ini?')) {
            return;
        }

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('vcKodeDivisi', divisi);
        formData.append('vcKodeDept', dept);
        formData.append('vcKodeBagian', bagian);
        formData.append('vcKodeSeksi', seksi);

        fetch('{{ route("hirarki.destroy-seksi") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Terjadi kesalahan!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus data!');
            });
    }
</script>
@endsection