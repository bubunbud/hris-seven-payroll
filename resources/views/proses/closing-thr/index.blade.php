@extends('layouts.app')

@section('title', 'Closing THR')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-calculator me-2"></i>Closing THR
                </h2>
            </div>

            <!-- Informasi Instruksi -->
            <div class="alert alert-info d-flex align-items-start mb-4">
                <i class="fas fa-info-circle fa-2x me-3 mt-1"></i>
                <div>
                    <strong>Instruksi:</strong><br>
                    Form ini digunakan untuk perhitungan THR (Tunjangan Hari Raya) karyawan Operator. Untuk memproses, buat terlebih dahulu periode THR menurut divisi dan kategori keagamaan yang akan dilakukan closing. 
                    Jika periode telah dibuat, centang periode yang akan di-closing. Perhitungan THR hanya untuk karyawan Operator dengan agama sesuai kategori periode.
                </div>
            </div>

            <!-- Tabel Periode Closing THR -->
            <div class="row">
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Periode Closing THR</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                <table class="table table-hover table-sm mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th width="8%">Tahun Periode</th>
                                            <th width="12%">Tanggal Cutoff</th>
                                            <th width="20%">Kategori</th>
                                            <th width="10%">Kode Divisi</th>
                                            <th width="30%">Nama Divisi</th>
                                            <th width="10%">Keterangan</th>
                                            <th width="5%" class="text-center">Proses</th>
                                            <th width="5%" class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($periodesWithStatus as $item)
                                        @php
                                            $periode = $item['periode'];
                                            $closingExists = $item['closing_exists'];
                                            $periodeKey = $periode->dtPeriode . '|' . 
                                                         $periode->dtKategori . '|' . 
                                                         $periode->vcKodeDivisi;
                                        @endphp
                                        <tr>
                                            <td><strong>{{ $periode->dtPeriode }}</strong></td>
                                            <td>{{ $periode->dtCutoffTHR ? $periode->dtCutoffTHR->format('d/m/Y') : '-' }}</td>
                                            <td>{{ $periode->dtKategori }}</td>
                                            <td><strong>{{ $periode->vcKodeDivisi }}</strong></td>
                                            <td>{{ $periode->divisi->vcNamaDivisi ?? 'N/A' }}</td>
                                            <td>
                                                <small class="text-muted">{{ $periode->vcKeterangan ?? '-' }}</small>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" 
                                                       class="form-check-input periode-checkbox" 
                                                       value="{{ $periodeKey }}"
                                                       {{ $closingExists ? 'disabled' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                @if($closingExists)
                                                    <span class="badge bg-success">Sudah Diproses</span>
                                                @else
                                                    <span class="badge bg-warning">Belum Diproses</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <i class="fas fa-inbox fa-2x text-muted mb-2 d-block"></i>
                                                <span class="text-muted">Belum ada periode closing THR yang tersedia</span>
                                                <br>
                                                <small class="text-muted">Silakan buat periode closing THR terlebih dahulu di menu "Periode Closing THR"</small>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Action Buttons -->
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <button type="button" class="btn btn-primary btn-lg w-100 mb-3" id="btnProsesTHR">
                                <i class="fas fa-users-cog me-2"></i>Proses THR
                            </button>
                            <button type="button" class="btn btn-danger btn-lg w-100 mb-3" onclick="window.location.href='{{ route('dashboard') ?? '/' }}'">
                                <i class="fas fa-times me-2"></i>Keluar
                            </button>
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
    const btnProsesTHR = document.getElementById('btnProsesTHR');
    const checkboxes = document.querySelectorAll('.periode-checkbox:not(:disabled)');

    // Toggle button state berdasarkan checkbox yang terpilih
    function toggleProsesButton() {
        const checked = document.querySelectorAll('.periode-checkbox:checked:not(:disabled)');
        if (btnProsesTHR) {
            btnProsesTHR.disabled = checked.length === 0;
        }
    }

    // Event listener untuk checkbox
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', toggleProsesButton);
    });

    // Initial state
    toggleProsesButton();

    // Proses THR
    if (btnProsesTHR) {
        btnProsesTHR.addEventListener('click', function() {
            const checked = document.querySelectorAll('.periode-checkbox:checked:not(:disabled)');
            if (checked.length === 0) {
                alert('Pilih minimal 1 periode yang akan diproses!');
                return;
            }

            if (!confirm(`Apakah Anda yakin ingin memproses ${checked.length} periode THR yang dipilih?`)) {
                return;
            }

            // Collect checked periode keys
            const periodes = [];
            checked.forEach(cb => {
                periodes.push(cb.value);
            });

            // Disable button dan show loading
            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';

            // Send request
            fetch('{{ route("closing-thr.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ periodes: periodes })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Reload halaman untuk refresh data
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Terjadi kesalahan'));
                    this.disabled = false;
                    this.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan: ' + error.message);
                this.disabled = false;
                this.innerHTML = originalText;
            });
        });
    }
});
</script>
@endsection

