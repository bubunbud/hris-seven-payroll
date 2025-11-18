@extends('layouts.app')

@section('title', 'Browse Absensi Karyawan - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-calendar-check me-2"></i>Browse Absensi Karyawan Per Periode
                </h2>
            </div>

            <!-- Filter Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('absen.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="dari_tanggal" class="form-label">Dari Tanggal</label>
                                <div class="input-group">
                                    <input type="date" class="form-control" id="dari_tanggal" name="dari_tanggal"
                                        value="{{ $startDate }}">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="sampai_tanggal" class="form-label">Sampai Tanggal</label>
                                <div class="input-group">
                                    <input type="date" class="form-control" id="sampai_tanggal" name="sampai_tanggal"
                                        value="{{ $endDate }}">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="nik" class="form-label">NIK</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="nik" name="nik"
                                        value="{{ $nik }}" placeholder="Cari NIK">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="nama" class="form-label">Nama</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="nama" name="nama"
                                        value="{{ $nama }}" placeholder="Cari Nama">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="group" class="form-label">Group</label>
                                <select class="form-select" id="group" name="group">
                                    <option value="Semua Group" {{ $group == 'Semua Group' ? 'selected' : '' }}>Semua Group</option>
                                    @foreach($groups as $groupOption)
                                    <option value="{{ $groupOption }}" {{ $group == $groupOption ? 'selected' : '' }}>
                                        {{ $groupOption }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary w-100 shadow-sm px-4">
                                        <i class="fas fa-eye me-2"></i>Preview
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-12">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="tidak_masuk" name="tidak_masuk"
                                        value="1" {{ $tidakMasuk ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tidak_masuk">
                                        Tidak Masuk
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="absen_tidak_lengkap" name="absen_tidak_lengkap"
                                        value="1" {{ $absenTidakLengkap ? 'checked' : '' }}>
                                    <label class="form-check-label" for="absen_tidak_lengkap">
                                        Absen Tidak Lengkap
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Data Count -->
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Jumlah Data {{ number_format($totalData) }}.</strong>
            </div>

            <!-- Data Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover table-striped" id="absenTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="10%">Tanggal</th>
                                    <th width="8%">NIK</th>
                                    <th width="20%">Nama</th>
                                    <th width="15%">Divisi</th>
                                    <th width="15%">Bagian</th>
                                    <th width="10%">Jam Masuk</th>
                                    <th width="10%">Jam Pulang</th>
                                    <th width="8%">Total Jam</th>
                                    <th width="12%">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($absens as $item)
                                @php
                                // Handle data dari absen atau tidak masuk (sekarang semua array)
                                $dtTanggal = $item['dtTanggal'] ?? null;
                                $vcNik = $item['vcNik'] ?? '';
                                $Nama = $item['Nama'] ?? 'N/A';
                                $vcNamaDivisi = $item['vcNamaDivisi'] ?? 'N/A';
                                $vcNamaBagian = $item['vcNamaBagian'] ?? 'N/A';
                                $dtJamMasuk = $item['dtJamMasuk'] ?? null;
                                $dtJamKeluar = $item['dtJamKeluar'] ?? null;
                                $dtJamMasukLembur = $item['dtJamMasukLembur'] ?? null;
                                $total_jam = $item['total_jam'] ?? 0;
                                $source = $item['source'] ?? 'absen';
                                $shift_masuk = $item['shift_masuk'] ?? null;

                                // Data khusus tidak masuk
                                $vcKodeAbsen = $item['vcKodeAbsen'] ?? null;
                                $jenis_absen_keterangan = $item['jenis_absen_keterangan'] ?? null;
                                $vcKeterangan = $item['vcKeterangan'] ?? null;

                                // Cek apakah tanggal adalah hari libur (weekend atau hari libur nasional)
                                $tanggalObj = \Carbon\Carbon::parse($dtTanggal);
                                $isWeekend = in_array($tanggalObj->dayOfWeek, [0, 6]); // 0 = Minggu, 6 = Sabtu
                                $isHoliday = in_array($dtTanggal, $hariLiburList);
                                $isHariLibur = $isWeekend || $isHoliday;

                                // Hitung total jam jika ada data
                                if ($dtJamMasuk && $dtJamKeluar) {
                                $masuk = $tanggalObj->copy()->setTimeFromTimeString((string) $dtJamMasuk);
                                $keluar = $tanggalObj->copy()->setTimeFromTimeString((string) $dtJamKeluar);
                                if ($keluar->lessThan($masuk)) {
                                $keluar->addDay();
                                }
                                $total_jam = round($masuk->diffInHours($keluar, true), 1);
                                }

                                // Cek telat: jam masuk > jam shift masuk
                                $isTelat = false;
                                if ($dtJamMasuk && $shift_masuk && $source === 'absen' && !$isHariLibur) {
                                try {
                                $jamMasuk = substr((string) $dtJamMasuk, 0, 5);
                                $shiftMasuk = $shift_masuk instanceof \Carbon\Carbon
                                ? $shift_masuk->format('H:i')
                                : substr((string) $shift_masuk, 0, 5);

                                $tMasuk = $tanggalObj->copy()->setTimeFromTimeString($jamMasuk);
                                $tShiftMasuk = $tanggalObj->copy()->setTimeFromTimeString($shiftMasuk);

                                // Jika jam masuk lebih dari jam shift masuk (bahkan 1 menit pun sudah telat)
                                if ($tMasuk->greaterThan($tShiftMasuk)) {
                                $isTelat = true;
                                }
                                } catch (\Exception $e) {
                                // Skip jika ada error parsing waktu
                                }
                                }

                                // Tentukan status
                                $status = '';
                                $badgeClass = '';

                                if ($source === 'tidak_masuk') {
                                // Data dari t_tidak_masuk
                                $status = $jenis_absen_keterangan ?? $vcKodeAbsen ?? 'Tidak Masuk';
                                $badgeClass = 'bg-danger';
                                } elseif (!$dtJamMasuk && !$dtJamKeluar) {
                                // Tidak ada jam masuk dan keluar
                                $status = 'Tidak Masuk';
                                $badgeClass = 'bg-danger';
                                } elseif ($isTelat) {
                                // Telat: jam masuk > jam shift masuk
                                $status = 'Telat';
                                $badgeClass = 'bg-warning text-dark';
                                } elseif (($dtJamMasuk && !$dtJamKeluar) || (!$dtJamMasuk && $dtJamKeluar)) {
                                // Absen tidak lengkap: hanya ada satu dari jam masuk/keluar (tidak ada pasangan)
                                $status = 'ATL';
                                $badgeClass = 'bg-warning text-dark';
                                } elseif ($isHariLibur && ($dtJamMasuk || $dtJamKeluar || $dtJamMasukLembur)) {
                                // KHL: Hari libur (weekend/holiday) dan ada jam masuk/keluar/lembur
                                $status = 'KHL';
                                $badgeClass = 'bg-info';
                                } elseif ($dtJamMasuk && $dtJamKeluar && $total_jam >= 8) {
                                // Hari kerja normal (ada jam masuk dan keluar, minimal 8 jam)
                                $status = 'HKN';
                                $badgeClass = 'bg-success';
                                } elseif ($dtJamMasuk && $dtJamKeluar && $total_jam > 0 && $total_jam < 8) {
                                    // HC: Ada jam masuk dan keluar tapi jam kerja kurang dari 8 jam
                                    $status='HC' ;
                                    $badgeClass='bg-warning text-dark' ;
                                    } else {
                                    // Lainnya (tidak ada jam masuk atau keluar)
                                    $status='ATL' ;
                                    $badgeClass='bg-warning text-dark' ;
                                    }
                                    @endphp
                                    <tr>
                                    <td>
                                        <i class="fas fa-calendar text-primary me-1"></i>
                                        {{ $dtTanggal ? \Carbon\Carbon::parse($dtTanggal)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>
                                        <strong>{{ $vcNik }}</strong>
                                    </td>
                                    <td>
                                        <i class="fas fa-user text-info me-1"></i>
                                        {{ $Nama }}
                                    </td>
                                    <td>
                                        <i class="fas fa-building text-secondary me-1"></i>
                                        {{ $vcNamaDivisi }}
                                    </td>
                                    <td>
                                        <i class="fas fa-sitemap text-warning me-1"></i>
                                        {{ $vcNamaBagian }}
                                    </td>
                                    <td>
                                        @if($dtJamMasuk)
                                        <i class="fas fa-sign-in-alt text-success me-1"></i>
                                        {{ \Carbon\Carbon::parse($dtJamMasuk)->format('H:i') }}
                                        @elseif($source === 'tidak_masuk')
                                        <span class="text-muted"><small>{{ $jenis_absen_keterangan ?? $vcKodeAbsen ?? '-' }}</small></span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($dtJamKeluar)
                                        <i class="fas fa-sign-out-alt text-danger me-1"></i>
                                        {{ \Carbon\Carbon::parse($dtJamKeluar)->format('H:i') }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($total_jam > 0)
                                        <span class="badge bg-info">{{ $total_jam }} jam</span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                                        @if($source === 'tidak_masuk' && $vcKeterangan)
                                        <br><small class="text-muted">{{ strlen($vcKeterangan) > 20 ? substr($vcKeterangan, 0, 20) . '...' : $vcKeterangan }}</small>
                                        @endif
                                    </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">Tidak ada data absensi untuk periode ini</p>
                                        </td>
                                    </tr>
                                    @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($absens->hasPages())
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
                        <div class="text-muted small">
                            Menampilkan {{ $absens->firstItem() }} sampai {{ $absens->lastItem() }} dari {{ $absens->total() }} data
                        </div>
                        <nav aria-label="Navigasi halaman">
                            {{ $absens->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Legend -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-info-circle me-2"></i>Keterangan Status
                    </h6>
                    <div class="row">
                        <div class="col-md-3">
                            <span class="badge bg-success me-2">HKN</span>
                            <span class="text-muted">Hari Kerja Normal</span>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-info me-2">KHL</span>
                            <span class="text-muted">Kerja Hari Libur</span>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-warning text-dark me-2">Telat</span>
                            <span class="text-muted">Jam masuk > jam shift masuk</span>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-warning text-dark me-2">ATL</span>
                            <span class="text-muted">Absen Tidak Lengkap</span>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-warning text-dark me-2">HC</span>
                            <span class="text-muted">Jam kerja kurang dari 8 jam</span>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-danger me-2">Tidak Masuk</span>
                            <span class="text-muted">Tidak ada absensi</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        document.querySelectorAll('.alert').forEach(alert => alert.remove());
        const container = document.querySelector('.container-fluid');
        container.insertAdjacentHTML('afterbegin', alertHtml);

        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }

    // Auto-submit form on date change
    document.getElementById('dari_tanggal').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });

    document.getElementById('sampai_tanggal').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });

    // Auto-submit form on checkbox change
    document.getElementById('tidak_masuk').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });

    document.getElementById('absen_tidak_lengkap').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });

    // Auto-submit form on group change
    document.getElementById('group').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
</script>
@endpush