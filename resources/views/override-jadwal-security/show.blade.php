@extends('layouts.app')

@section('title', 'Detail Override Jadwal Security - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-history me-2"></i>Detail Override Jadwal Security
                </h2>
                <a href="{{ route('override-jadwal-security.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">NIK</th>
                                    <td><strong>{{ $override->vcNik }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Nama Satpam</th>
                                    <td>{{ $override->karyawan->Nama ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Jadwal</th>
                                    <td>
                                        <i class="fas fa-calendar text-primary me-1"></i>
                                        {{ \Carbon\Carbon::parse($override->dtTanggal)->format('d F Y') }}
                                        ({{ \Carbon\Carbon::parse($override->dtTanggal)->locale('id')->dayName }})
                                    </td>
                                </tr>
                                <tr>
                                    <th>Shift Lama</th>
                                    <td>
                                        @if($override->intShiftLama)
                                        <span class="badge bg-secondary">
                                            {{ $shiftNames[$override->intShiftLama] ?? 'Shift ' . $override->intShiftLama }}
                                        </span>
                                        @else
                                        <span class="badge bg-dark">OFF / Tidak Ada Jadwal</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Shift Baru</th>
                                    <td>
                                        <span class="badge bg-success">
                                            {{ $shiftNames[$override->intShiftBaru] ?? 'Shift ' . $override->intShiftBaru }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Alasan Override</th>
                                    <td>
                                        <div class="alert alert-info mb-0">
                                            {{ $override->vcAlasan }}
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>User Override</th>
                                    <td>
                                        <i class="fas fa-user-circle text-secondary me-1"></i>
                                        {{ $override->vcOverrideBy }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Waktu Override</th>
                                    <td>
                                        <i class="fas fa-clock text-primary me-1"></i>
                                        {{ \Carbon\Carbon::parse($override->dtOverrideAt)->format('d F Y H:i:s') }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tanggal Dibuat</th>
                                    <td>
                                        @if($override->dtCreate)
                                        {{ \Carbon\Carbon::parse($override->dtCreate)->format('d F Y H:i:s') }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
