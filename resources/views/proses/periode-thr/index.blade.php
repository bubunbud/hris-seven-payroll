@extends('layouts.app')

@section('title', 'Periode Closing THR')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>Periode Closing THR
                </h2>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-success" id="btnToggleForm">
                        <i class="fas fa-plus me-1"></i>Tambah Periode THR
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
                            <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Buat Periode Closing THR</h5>
                        </div>
                        <div class="card-body">
                            <form id="formPeriode">
                                <div class="mb-3">
                                    <label for="dtPeriode" class="form-label">Tahun Periode <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="dtPeriode" name="dtPeriode" 
                                               value="{{ $defaultTahun }}" maxlength="4" pattern="\d{4}" required>
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    </div>
                                    <small class="form-text text-muted">Masukkan tahun periode THR (contoh: 2025)</small>
                                </div>

                                <div class="mb-3">
                                    <label for="dtCutoffTHR" class="form-label">Tanggal Cutoff THR <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" id="dtCutoffTHR" name="dtCutoffTHR" required>
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    </div>
                                    <small class="form-text text-muted">Tanggal patokan perhitungan THR</small>
                                </div>

                                <div class="mb-3">
                                    <label for="dtKategori" class="form-label">Hari Keagamaan <span class="text-danger">*</span></label>
                                    <select class="form-select" id="dtKategori" name="dtKategori" required>
                                        <option value="">Pilih Kategori</option>
                                        <option value="Islam (Idul Fitri)">Islam (Idul Fitri)</option>
                                        <option value="Kristen (Natal)">Kristen (Natal)</option>
                                        <option value="Hindu (Nyepi)">Hindu (Nyepi)</option>
                                        <option value="Budha (Waisak)">Budha (Waisak)</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="vcNamaHariRaya" class="form-label">Nama Hari Raya</label>
                                    <input type="text" class="form-control" id="vcNamaHariRaya" name="vcNamaHariRaya" 
                                           maxlength="100" placeholder="Contoh: Idul Fitri, Natal, Nyepi, Waisak">
                                    <small class="form-text text-muted">Nama hari raya (opsional)</small>
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

                                <div class="mb-3">
                                    <label for="vcKeterangan" class="form-label">Keterangan</label>
                                    <textarea class="form-control" id="vcKeterangan" name="vcKeterangan" rows="3" 
                                              placeholder="Keterangan (opsional)"></textarea>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-calendar-plus me-2"></i>Buat Periode THR
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
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Periode Closing THR</h5>
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
                                    <th width="8%">Tahun Periode</th>
                                    <th width="12%">Tanggal Cutoff</th>
                                    <th width="18%">Kategori</th>
                                    <th width="15%">Nama Hari Raya</th>
                                    <th width="8%">Kode Divisi</th>
                                    <th width="20%">Nama Divisi</th>
                                    <th width="10%">Keterangan</th>
                                    <th width="8%" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($periodes as $periode)
                                <tr data-status="{{ $periode->vcStatus }}">
                                    <td>
                                        <input type="checkbox" class="periode-checkbox" 
                                               data-periode="{{ $periode->dtPeriode }}"
                                               data-kategori="{{ $periode->dtKategori }}"
                                               data-divisi="{{ $periode->vcKodeDivisi }}"
                                               data-status="{{ $periode->vcStatus }}"
                                               {{ $periode->vcStatus == '1' ? 'disabled' : '' }}>
                                    </td>
                                    <td><strong>{{ $periode->dtPeriode }}</strong></td>
                                    <td>{{ $periode->dtCutoffTHR ? $periode->dtCutoffTHR->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $periode->dtKategori }}</td>
                                    <td>{{ $periode->vcNamaHariRaya ?? '-' }}</td>
                                    <td><strong>{{ $periode->vcKodeDivisi }}</strong></td>
                                    <td>{{ $periode->divisi->vcNamaDivisi ?? 'N/A' }}</td>
                                    <td>
                                        <small class="text-muted">{{ $periode->vcKeterangan ?? '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        @if($periode->vcStatus == '1')
                                        <span class="badge bg-success">Sudah Diproses</span>
                                        @else
                                        <span class="badge bg-warning">Belum Diproses</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2 d-block"></i>
                                        <span class="text-muted">Belum ada data periode closing THR</span>
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
                this.innerHTML = '<i class="fas fa-plus-circle me-2"></i>Tambah Periode THR';
            }
        });
    }

    // Validasi input tahun (hanya angka, max 4 digit)
    const dtPeriodeInput = document.getElementById('dtPeriode');
    if (dtPeriodeInput) {
        dtPeriodeInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 4) {
                this.value = this.value.substring(0, 4);
            }
        });
    }

    // Auto-fill Nama Hari Raya berdasarkan pilihan Hari Keagamaan
    const dtKategoriSelect = document.getElementById('dtKategori');
    const vcNamaHariRayaInput = document.getElementById('vcNamaHariRaya');
    
    if (dtKategoriSelect && vcNamaHariRayaInput) {
        // Mapping kategori ke nama hari raya default
        const kategoriMapping = {
            'Islam (Idul Fitri)': 'Hari Raya Idul Fitri',
            'Kristen (Natal)': 'Hari Raya Natal',
            'Hindu (Nyepi)': 'Hari Raya Nyepi',
            'Budha (Waisak)': 'Hari Raya Waisak',
            'Lainnya': ''
        };

        // Flag untuk track apakah user sudah edit manual
        let isManualEdit = false;
        let lastAutoFilledValue = '';

        // Event listener untuk perubahan kategori
        dtKategoriSelect.addEventListener('change', function() {
            const selectedKategori = this.value;
            const defaultNamaHariRaya = kategoriMapping[selectedKategori] || '';
            
            // Reset flag manual edit ketika kategori berubah
            isManualEdit = false;
            
            // Auto-fill jika ada mapping
            if (defaultNamaHariRaya) {
                vcNamaHariRayaInput.value = defaultNamaHariRaya;
                lastAutoFilledValue = defaultNamaHariRaya;
            } else {
                // Jika Lainnya atau tidak ada mapping, kosongkan
                vcNamaHariRayaInput.value = '';
                lastAutoFilledValue = '';
            }
        });

        // Event listener untuk detect manual edit
        vcNamaHariRayaInput.addEventListener('input', function() {
            // Jika user mengubah value yang berbeda dari auto-filled value, set flag manual edit
            if (this.value !== lastAutoFilledValue) {
                isManualEdit = true;
            }
        });

        // Event listener untuk focus - jika user klik field, tetap bisa edit
        vcNamaHariRayaInput.addEventListener('focus', function() {
            // User bisa edit kapan saja
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

        // Validasi tahun
        const tahun = formData.get('dtPeriode');
        if (!tahun || tahun.length !== 4 || !/^\d{4}$/.test(tahun)) {
            alert('Tahun periode harus 4 digit angka!');
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

        fetch('{{ route("periode-thr.store") }}', {
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
                // Reload halaman untuk refresh data
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
        document.querySelectorAll('.periode-checkbox:not(:disabled)').forEach(cb => {
            cb.checked = this.checked;
        });
        toggleHapusButton();
    });

    // Toggle hapus button
    document.querySelectorAll('.periode-checkbox').forEach(cb => {
        cb.addEventListener('change', toggleHapusButton);
    });

    function toggleHapusButton() {
        const checked = document.querySelectorAll('.periode-checkbox:checked:not(:disabled)');
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
                // Ambil data dari attribute
                const dtPeriode = cb.getAttribute('data-periode');
                const dtKategori = cb.getAttribute('data-kategori');
                const vcKodeDivisi = cb.getAttribute('data-divisi');
                
                // Validasi data sebelum kirim
                if (!dtPeriode || !dtKategori || !vcKodeDivisi) {
                    processed++;
                    const missingFields = [];
                    if (!dtPeriode) missingFields.push('Tahun Periode');
                    if (!dtKategori) missingFields.push('Kategori');
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
                formData.append('dtPeriode', dtPeriode);
                formData.append('dtKategori', dtKategori);
                formData.append('vcKodeDivisi', vcKodeDivisi);

                // Gunakan route POST untuk menghindari masalah method spoofing dengan DELETE
                fetch('{{ route("periode-thr.destroy-post") }}', {
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


