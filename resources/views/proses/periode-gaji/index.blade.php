@extends('layouts.app')

@section('title', 'Periode Closing Gaji')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>Periode Closing Gaji
                </h2>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-success" id="btnToggleForm">
                        <i class="fas fa-plus me-1"></i>Tambah Closing
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="periodePage">
        <!-- Form Periode Closing (hidden by default) -->
        <div id="formWrapper" class="d-none">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Buat Periode Closing</h5>
                        </div>
                        <div class="card-body">
                            <form id="formPeriode">
                                <div class="mb-3">
                                    <label for="dtTanggalAwal" class="form-label">Tgl. Awal <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" id="dtTanggalAwal" name="dtTanggalAwal" 
                                               value="{{ $defaultTanggalAwal }}" required>
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="dtTanggalAkhir" class="form-label">Tgl. Akhir <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" id="dtTanggalAkhir" name="dtTanggalAkhir" required>
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="dtPeriode" class="form-label">Periode <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" id="dtPeriode" name="dtPeriode" required>
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    </div>
                                    <small class="form-text text-muted">Tanggal pembayaran gaji (1 atau 15)</small>
                                </div>

                                <div class="mb-3">
                                    <label for="intPeriodeClosing" class="form-label">Periode Closing <span class="text-danger">*</span></label>
                                    <select class="form-select" id="intPeriodeClosing" name="intPeriodeClosing" required>
                                        <option value="">Pilih Periode</option>
                                        <option value="1" {{ $defaultPeriodeClosing == 1 ? 'selected' : '' }}>Periode 1 (Tanggal 1)</option>
                                        <option value="2" {{ $defaultPeriodeClosing == 2 ? 'selected' : '' }}>Periode 2 (Tanggal 15)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Pilih Divisi <span class="text-danger">*</span></label>
                                    <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                        @forelse($divisis as $divisi)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input divisi-checkbox" type="checkbox" 
                                                   name="divisi[]" value="{{ $divisi->vcKodeDivisi }}" 
                                                   id="divisi_{{ $divisi->vcKodeDivisi }}">
                                            <label class="form-check-label" for="divisi_{{ $divisi->vcKodeDivisi }}">
                                                <strong>{{ $divisi->vcKodeDivisi }}</strong> - {{ $divisi->vcNamaDivisi }}
                                            </label>
                                        </div>
                                        @empty
                                        <div class="text-muted text-center py-3">
                                            <i class="fas fa-info-circle me-2"></i>Data divisi tidak ditemukan
                                        </div>
                                        @endforelse
                                    </div>
                                    <small class="form-text text-muted">Pilih minimal 1 divisi</small>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-calendar-plus me-2"></i>Buat Periode
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Periode Closing (always visible) -->
        <div id="listWrapper">
            <div class="card">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Periode Closing</h5>
                    <button type="button" class="btn btn-sm btn-danger" id="btnHapusPeriode" style="display: none;">
                        <i class="fas fa-trash me-2"></i>Hapus Periode
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="4%">
                                        <input type="checkbox" id="checkAll">
                                    </th>
                                    <th width="10%">Periode Awal</th>
                                    <th width="10%">Periode Akhir</th>
                                    <th width="8%" class="text-center">Periode Closing</th>
                                    <th width="10%">Periode Gajian</th>
                                    <th width="10%">Kode Divisi</th>
                                    <th width="30%">Nama Divisi</th>
                                    <th width="8%" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($periodes as $periode)
                                <tr data-status="{{ $periode->vcStatus }}">
                                    <td>
                                        <input type="checkbox" class="periode-checkbox" 
                                               data-awal="{{ $periode->dtPeriodeFrom->format('Y-m-d') }}"
                                               data-akhir="{{ $periode->dtPeriodeTo->format('Y-m-d') }}"
                                               data-periode="{{ $periode->periode->format('Y-m-d') }}"
                                               data-closing="{{ $periode->intPeriodeClosing }}"
                                               data-divisi="{{ $periode->vcKodeDivisi }}"
                                               data-status="{{ $periode->vcStatus }}"
                                               {{ $periode->vcStatus == '1' ? 'disabled' : '' }}>
                                    </td>
                                    <td>{{ $periode->dtPeriodeFrom->format('d/m/Y') }}</td>
                                    <td>{{ $periode->dtPeriodeTo->format('d/m/Y') }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $periode->intPeriodeClosing }}</span>
                                    </td>
                                    <td>{{ $periode->periode->format('d/m/Y') }}</td>
                                    <td><strong>{{ $periode->vcKodeDivisi }}</strong></td>
                                    <td>{{ $periode->divisi->vcNamaDivisi ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        @if($periode->vcStatus == '1')
                                        <span class="badge bg-success">Processed</span>
                                        @else
                                        <span class="badge bg-warning">Belum Diproses</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2 d-block"></i>
                                        <span class="text-muted">Belum ada data periode closing</span>
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

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="modalHapus" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus periode yang dipilih?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Hapus</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle tampil/hidden form tambah periode
    const formWrapper = document.getElementById('formWrapper');
    const btnToggleForm = document.getElementById('btnToggleForm');
    if (btnToggleForm && formWrapper) {
        btnToggleForm.addEventListener('click', function() {
            const isHidden = formWrapper.classList.contains('d-none');
            if (isHidden) {
                formWrapper.classList.remove('d-none');
                this.innerHTML = '<i class="fas fa-minus-circle me-2"></i>Tutup Form';
            } else {
                formWrapper.classList.add('d-none');
                this.innerHTML = '<i class="fas fa-plus-circle me-2"></i>Tambah Closing';
            }
        });
    }

    // Form submit
    document.getElementById('formPeriode').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const divisiChecked = document.querySelectorAll('.divisi-checkbox:checked');
        
        if (divisiChecked.length === 0) {
            alert('Pilih minimal 1 divisi!');
            return;
        }

        // Clear divisi array dan tambahkan yang terpilih
        formData.delete('divisi[]');
        divisiChecked.forEach(cb => {
            formData.append('divisi[]', cb.value);
        });

        // Loading state
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';

        fetch('{{ route("periode-gaji.store") }}', {
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
                alert(data.message);
                // Reload halaman untuk refresh data (agar default values ter-update)
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Terjadi kesalahan'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan: ' + error.message);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });

    // Check all
    document.getElementById('checkAll').addEventListener('change', function() {
        document.querySelectorAll('.periode-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
        toggleHapusButton();
    });

    // Toggle hapus button
    document.querySelectorAll('.periode-checkbox').forEach(cb => {
        cb.addEventListener('change', toggleHapusButton);
    });

    function toggleHapusButton() {
        const checked = document.querySelectorAll('.periode-checkbox:checked');
        document.getElementById('btnHapusPeriode').style.display = checked.length > 0 ? 'block' : 'none';
    }

    // Hapus periode
    document.getElementById('btnHapusPeriode').addEventListener('click', function() {
        const checked = document.querySelectorAll('.periode-checkbox:checked:not(:disabled)');
        if (checked.length === 0) {
            alert('Pilih periode yang akan dihapus! Periode yang sudah diproses tidak bisa dihapus.');
            return;
        }

        // Cek apakah ada yang sudah diproses
        let adaDiproses = false;
        checked.forEach(cb => {
            const status = cb.getAttribute('data-status') || cb.dataset.status;
            if (status === '1') {
                adaDiproses = true;
            }
        });

        if (adaDiproses) {
            alert('Periode yang sudah diproses tidak bisa dihapus!');
            return;
        }

        // Konfirmasi
        const modal = new bootstrap.Modal(document.getElementById('modalHapus'));
        modal.show();

        document.getElementById('confirmDelete').onclick = function() {
            // Disable button saat proses
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menghapus...';
            
            // Hapus satu per satu
            let deleted = 0;
            let errors = [];
            let processed = 0;
            
            if (checked.length === 0) {
                modal.hide();
                alert('Tidak ada periode yang dipilih!');
                this.disabled = false;
                this.innerHTML = 'Hapus';
                return;
            }
            
            checked.forEach((cb, index) => {
                // Ambil data dari attribute (lebih reliable daripada dataset)
                const dtTanggalAwal = cb.getAttribute('data-awal');
                const dtTanggalAkhir = cb.getAttribute('data-akhir');
                const dtPeriode = cb.getAttribute('data-periode');
                const intPeriodeClosing = cb.getAttribute('data-closing');
                const vcKodeDivisi = cb.getAttribute('data-divisi');
                
                // Debug: log data yang akan dikirim
                console.log('Deleting periode:', {
                    dtTanggalAwal,
                    dtTanggalAkhir,
                    dtPeriode,
                    intPeriodeClosing,
                    vcKodeDivisi
                });
                
                // Validasi data sebelum kirim
                if (!dtTanggalAwal || !dtTanggalAkhir || !dtPeriode || !intPeriodeClosing || !vcKodeDivisi) {
                    processed++;
                    const missingFields = [];
                    if (!dtTanggalAwal) missingFields.push('Tanggal Awal');
                    if (!dtTanggalAkhir) missingFields.push('Tanggal Akhir');
                    if (!dtPeriode) missingFields.push('Periode');
                    if (!intPeriodeClosing) missingFields.push('Periode Closing');
                    if (!vcKodeDivisi) missingFields.push('Kode Divisi');
                    errors.push('Data periode tidak lengkap: ' + missingFields.join(', '));
                    if (processed === checked.length) {
                        modal.hide();
                        alert(`Hapus selesai. Berhasil: ${deleted}, Gagal: ${errors.length}\n${errors.join('\n')}`);
                        location.reload();
                    }
                    return;
                }
                
                const formData = new FormData();
                formData.append('dtTanggalAwal', dtTanggalAwal);
                formData.append('dtTanggalAkhir', dtTanggalAkhir);
                formData.append('dtPeriode', dtPeriode);
                formData.append('intPeriodeClosing', intPeriodeClosing);
                formData.append('vcKodeDivisi', vcKodeDivisi);

                // Gunakan route POST untuk menghindari masalah method spoofing dengan DELETE
                fetch('{{ route("periode-gaji.destroy-post") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(r => {
                    if (!r.ok) {
                        return r.json().then(err => {
                            throw new Error(err.message || 'HTTP Error: ' + r.status);
                        });
                    }
                    return r.json();
                })
                .then(data => {
                    processed++;
                    if (data.success) {
                        deleted++;
                    } else {
                        errors.push(data.message || 'Gagal menghapus periode');
                    }
                    
                    // Jika sudah semua selesai
                    if (processed === checked.length) {
                        modal.hide();
                        let message = `Hapus selesai. Berhasil: ${deleted}`;
                        if (errors.length > 0) {
                            message += `, Gagal: ${errors.length}`;
                            if (errors.length <= 3) {
                                message += '\n' + errors.join('\n');
                            }
                        }
                        alert(message);
                        location.reload();
                    }
                })
                .catch(error => {
                    processed++;
                    console.error('Error deleting periode:', error);
                    errors.push(error.message || 'Terjadi kesalahan saat menghapus');
                    
                    if (processed === checked.length) {
                        modal.hide();
                        let message = `Hapus selesai. Berhasil: ${deleted}`;
                        if (errors.length > 0) {
                            message += `, Gagal: ${errors.length}`;
                            if (errors.length <= 3) {
                                message += '\n' + errors.join('\n');
                            }
                        }
                        alert(message);
                        location.reload();
                    }
                });
            });
        };
    });
});
</script>
@endsection

