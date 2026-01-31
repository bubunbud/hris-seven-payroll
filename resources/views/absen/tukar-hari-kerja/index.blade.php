@extends('layouts.app')

@section('title', 'Tukar Hari Kerja - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-exchange-alt me-2"></i>Tukar Hari Kerja
                </h2>
                <a href="{{ route('tukar-hari-kerja.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Baru
                </a>
            </div>

            <!-- Filter -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('tukar-hari-kerja.index') }}">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="dari_tanggal" class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" name="dari_tanggal" value="{{ request('dari_tanggal') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="sampai_tanggal" class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" name="sampai_tanggal" value="{{ request('sampai_tanggal') }}">
                            </div>
                            <div class="col-md-1">
                                <label for="scope" class="form-label">Scope</label>
                                <select class="form-select" name="scope">
                                    <option value="">Semua</option>
                                    <option value="PERORANGAN" {{ request('scope') == 'PERORANGAN' ? 'selected' : '' }}>Perorangan</option>
                                    <option value="GROUP" {{ request('scope') == 'GROUP' ? 'selected' : '' }}>Group</option>
                                    <option value="SEMUA_BU" {{ request('scope') == 'SEMUA_BU' ? 'selected' : '' }}>Semua BU</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="divisi" class="form-label">Bisnis Unit</label>
                                <select class="form-select" name="divisi">
                                    <option value="">Semua</option>
                                    @foreach($divisis as $divisi)
                                        <option value="{{ $divisi->vcKodeDivisi }}" {{ request('divisi') == $divisi->vcKodeDivisi ? 'selected' : '' }}>
                                            {{ $divisi->vcNamaDivisi }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="tipe" class="form-label">Tipe</label>
                                <select class="form-select" name="tipe">
                                    <option value="">Semua</option>
                                    <option value="LIBUR_KE_KERJA" {{ request('tipe') == 'LIBUR_KE_KERJA' ? 'selected' : '' }}>Libur → Kerja</option>
                                    <option value="KERJA_KE_LIBUR" {{ request('tipe') == 'KERJA_KE_LIBUR' ? 'selected' : '' }}>Kerja → Libur</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="search" class="form-label">Pencarian (NIK / Nama)</label>
                                <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="NIK atau Nama Karyawan">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Cari
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="10%">NIK</th>
                                    <th width="15%">Nama Karyawan</th>
                                    <th width="12%">Bisnis Unit</th>
                                    <th width="12%">Bagian</th>
                                    <th width="10%">Tanggal Libur</th>
                                    <th width="10%">Tanggal Kerja</th>
                                    <th width="10%">Tipe</th>
                                    <th width="10%">Scope</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tukarHariKerja as $index => $thk)
                                <tr>
                                    <td>{{ $tukarHariKerja->firstItem() + $index }}</td>
                                    <td><strong>{{ $thk->nik ?? '-' }}</strong></td>
                                    <td>
                                        <strong>{{ $thk->karyawan ? $thk->karyawan->Nama : '-' }}</strong>
                                    </td>
                                    <td>{{ $thk->karyawan && $thk->karyawan->divisi ? $thk->karyawan->divisi->vcNamaDivisi : '-' }}</td>
                                    <td>{{ $thk->karyawan && $thk->karyawan->bagian ? $thk->karyawan->bagian->vcNamaBagian : '-' }}</td>
                                    <td>{{ $thk->tanggal_libur ? \Carbon\Carbon::parse($thk->tanggal_libur)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $thk->tanggal_kerja ? \Carbon\Carbon::parse($thk->tanggal_kerja)->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        @if($thk->vcTipeTukar == 'LIBUR_KE_KERJA')
                                            <span class="badge bg-success">Libur → Kerja</span>
                                        @else
                                            <span class="badge bg-warning">Kerja → Libur</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($thk->vcScope == 'PERORANGAN')
                                            <span class="badge bg-info">Perorangan</span>
                                        @elseif($thk->vcScope == 'GROUP')
                                            <span class="badge bg-primary">Group</span>
                                        @else
                                            <span class="badge bg-secondary">Semua BU</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('tukar-hari-kerja.show', ['tanggal_libur' => $thk->tanggal_libur, 'nik' => $thk->nik]) }}" class="btn btn-sm btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('tukar-hari-kerja.edit', ['tanggal_libur' => $thk->tanggal_libur, 'nik' => $thk->nik]) }}" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('tukar-hari-kerja.destroy', ['tanggal_libur' => $thk->tanggal_libur, 'nik' => $thk->nik]) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tukar hari kerja ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Tidak ada data tukar hari kerja</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $tukarHariKerja->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

