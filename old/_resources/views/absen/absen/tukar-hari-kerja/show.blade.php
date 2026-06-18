@extends('layouts.app')

@section('title', 'Detail Tukar Hari Kerja - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-exchange-alt me-2"></i>Detail Tukar Hari Kerja
                </h2>
                <div>
                    <a href="{{ route('tukar-hari-kerja.edit', ['tanggal_libur' => $tukarHariKerja->tanggal_libur, 'nik' => $tukarHariKerja->nik]) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Edit
                    </a>
                    <a href="{{ route('tukar-hari-kerja.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Tukar Hari Kerja</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>NIK:</strong><br>
                            <strong>{{ $tukarHariKerja->nik ?? '-' }}</strong>
                        </div>
                        <div class="col-md-3">
                            <strong>Nama Karyawan:</strong><br>
                            {{ $tukarHariKerja->karyawan->Nama ?? '-' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Tanggal Libur:</strong><br>
                            {{ $tukarHariKerja->tanggal_libur ? \Carbon\Carbon::parse($tukarHariKerja->tanggal_libur)->format('d/m/Y') : '-' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Tanggal Kerja:</strong><br>
                            {{ $tukarHariKerja->tanggal_kerja ? \Carbon\Carbon::parse($tukarHariKerja->tanggal_kerja)->format('d/m/Y') : '-' }}
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <strong>Tipe:</strong><br>
                            @if($tukarHariKerja->vcTipeTukar == 'LIBUR_KE_KERJA')
                                <span class="badge bg-success">Libur → Kerja</span>
                            @else
                                <span class="badge bg-warning">Kerja → Libur</span>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <strong>Scope:</strong><br>
                            @if($tukarHariKerja->vcScope == 'PERORANGAN')
                                <span class="badge bg-info">Perorangan</span>
                            @elseif($tukarHariKerja->vcScope == 'GROUP')
                                <span class="badge bg-primary">Group</span>
                            @else
                                <span class="badge bg-secondary">Semua BU</span>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <strong>Divisi:</strong><br>
                            {{ $tukarHariKerja->divisi->vcNamaDivisi ?? '-' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Departemen:</strong><br>
                            {{ $tukarHariKerja->karyawan->departemen->vcNamaDept ?? '-' }}
                        </div>
                    </div>
                    @if($tukarHariKerja->vcKeterangan)
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <strong>Keterangan:</strong><br>
                            {{ $tukarHariKerja->vcKeterangan }}
                        </div>
                    </div>
                    @endif
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <strong>Dibuat Oleh:</strong><br>
                            {{ $tukarHariKerja->vcCreatedBy ?? '-' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Tanggal Dibuat:</strong><br>
                            {{ $tukarHariKerja->dtCreatedAt ? \Carbon\Carbon::parse($tukarHariKerja->dtCreatedAt)->format('d/m/Y H:i') : '-' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Diubah Oleh:</strong><br>
                            {{ $tukarHariKerja->vcUpdatedBy ?? '-' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Tanggal Diubah:</strong><br>
                            {{ $tukarHariKerja->dtUpdatedAt ? \Carbon\Carbon::parse($tukarHariKerja->dtUpdatedAt)->format('d/m/Y H:i') : '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Informasi Record Kebalikan</h5>
                </div>
                <div class="card-body">
                    @php
                        $reverseTipeTukar = $tukarHariKerja->vcTipeTukar === 'LIBUR_KE_KERJA' ? 'KERJA_KE_LIBUR' : 'LIBUR_KE_KERJA';
                        $reverseRecord = \App\Models\TukarHariKerja::where('tanggal_libur', $tukarHariKerja->tanggal_kerja)
                            ->where('nik', $tukarHariKerja->nik)
                            ->where('vcTipeTukar', $reverseTipeTukar)
                            ->first();
                    @endphp
                    @if($reverseRecord)
                        <div class="alert alert-info mb-0">
                            <strong>Record Kebalikan Ditemukan:</strong><br>
                            Tanggal Libur: {{ $reverseRecord->tanggal_libur ? \Carbon\Carbon::parse($reverseRecord->tanggal_libur)->format('d/m/Y') : '-' }}<br>
                            Tanggal Kerja: {{ $reverseRecord->tanggal_kerja ? \Carbon\Carbon::parse($reverseRecord->tanggal_kerja)->format('d/m/Y') : '-' }}<br>
                            Tipe: 
                            @if($reverseRecord->vcTipeTukar == 'LIBUR_KE_KERJA')
                                <span class="badge bg-success">Libur → Kerja</span>
                            @else
                                <span class="badge bg-warning">Kerja → Libur</span>
                            @endif
                            <br>
                            <small class="text-muted">Record ini dibuat otomatis sebagai kebalikan dari record utama.</small>
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            <strong>Record Kebalikan Tidak Ditemukan</strong><br>
                            <small class="text-muted">Record kebalikan mungkin belum dibuat atau sudah dihapus.</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



