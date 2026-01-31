@extends('layouts.app')

@section('title', 'Tarik Data Tidak Masuk - HRIS Seven Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-download me-2"></i>Tarik Data Tidak Masuk
                </h2>
            </div>

            <!-- Form -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-server me-2"></i>Konfigurasi Server
                </div>
                <div class="card-body">
                    <form id="tarikDataForm">
                        <!-- Server Configuration -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="server_host" class="form-label">Host Server <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="server_host" name="server_host" value="192.168.10.40" required placeholder="192.168.10.40">
                            </div>
                            <div class="col-md-3">
                                <label for="server_port" class="form-label">Port</label>
                                <input type="number" class="form-control" id="server_port" name="server_port" value="3306" placeholder="3306">
                            </div>
                            <div class="col-md-3">
                                <label for="server_database" class="form-label">Database <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="server_database" name="server_database" value="seven" required placeholder="seven">
                            </div>
                            <div class="col-md-4">
                                <label for="server_table" class="form-label">Tabel <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="server_table" name="server_table" value="t_tidak_masuk" required placeholder="t_tidak_masuk">
                            </div>
                            <div class="col-md-4">
                                <label for="server_user" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="server_user" name="server_user" value="root" required placeholder="root">
                            </div>
                            <div class="col-md-4">
                                <label for="server_password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="server_password" name="server_password" value="root123" required placeholder="Password">
                            </div>
                        </div>

                        <!-- Field Selection -->
                        <div class="mb-4">
                            <label class="form-label">Pilih Field/Kolom yang akan diambil <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input field-checkbox" type="checkbox" id="field_vcNik" value="vcNik" checked>
                                        <label class="form-check-label" for="field_vcNik">vcNik</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input field-checkbox" type="checkbox" id="field_vcKodeAbsen" value="vcKodeAbsen" checked>
                                        <label class="form-check-label" for="field_vcKodeAbsen">vcKodeAbsen</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input field-checkbox" type="checkbox" id="field_dtTanggalMulai" value="dtTanggalMulai" checked>
                                        <label class="form-check-label" for="field_dtTanggalMulai">dtTanggalMulai</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input field-checkbox" type="checkbox" id="field_dtTanggalSelesai" value="dtTanggalSelesai" checked>
                                        <label class="form-check-label" for="field_dtTanggalSelesai">dtTanggalSelesai</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input field-checkbox" type="checkbox" id="field_vcKeterangan" value="vcKeterangan">
                                        <label class="form-check-label" for="field_vcKeterangan">vcKeterangan</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input field-checkbox" type="checkbox" id="field_vcDibayar" value="vcDibayar">
                                        <label class="form-check-label" for="field_vcDibayar">vcDibayar</label>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">Pilih minimal 1 field. Field yang dipilih akan di-insert ke tabel lokal. Field vcNik, vcKodeAbsen, dtTanggalMulai, dan dtTanggalSelesai wajib untuk composite key.</small>
                        </div>

                        <!-- Date Range -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="dari_tanggal" class="form-label">Dari Tanggal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dari_tanggal" name="dari_tanggal" required>
                            </div>
                            <div class="col-md-4">
                                <label for="sampai_tanggal" class="form-label">Sampai Tanggal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="sampai_tanggal" name="sampai_tanggal" required>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100" id="btnTarik">
                                    <i class="fas fa-download me-2"></i>Tarik Data
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Progress Bar -->
                    <div id="progressContainer" class="mt-4" style="display: none;">
                        <div class="progress" style="height: 25px;">
                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                                role="progressbar" style="width: 0%">
                                <span id="progressText">Memproses...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Result -->
                    <div id="resultContainer" class="mt-4" style="display: none;">
                        <div class="alert" id="resultAlert">
                            <div id="resultMessage"></div>
                            <div id="resultDetails" class="mt-3"></div>
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
    document.getElementById('tarikDataForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const dariTanggal = document.getElementById('dari_tanggal').value;
        const sampaiTanggal = document.getElementById('sampai_tanggal').value;
        const serverHost = document.getElementById('server_host').value;
        const serverPort = document.getElementById('server_port').value;
        const serverDatabase = document.getElementById('server_database').value;
        const serverTable = document.getElementById('server_table').value;
        const serverUser = document.getElementById('server_user').value;
        const serverPassword = document.getElementById('server_password').value;

        // Get selected fields
        const selectedFields = Array.from(document.querySelectorAll('.field-checkbox:checked')).map(cb => cb.value);

        const btnTarik = document.getElementById('btnTarik');
        const progressContainer = document.getElementById('progressContainer');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const resultContainer = document.getElementById('resultContainer');
        const resultAlert = document.getElementById('resultAlert');
        const resultMessage = document.getElementById('resultMessage');
        const resultDetails = document.getElementById('resultDetails');

        // Validasi
        if (!dariTanggal || !sampaiTanggal) {
            showAlert('error', 'Mohon isi tanggal mulai dan tanggal akhir');
            return;
        }

        if (sampaiTanggal < dariTanggal) {
            showAlert('error', 'Tanggal akhir harus lebih besar atau sama dengan tanggal mulai');
            return;
        }

        if (selectedFields.length === 0) {
            showAlert('error', 'Mohon pilih minimal 1 field yang akan diambil');
            return;
        }

        if (!serverHost || !serverDatabase || !serverTable || !serverUser || !serverPassword) {
            showAlert('error', 'Mohon lengkapi konfigurasi server');
            return;
        }

        // Disable button dan show progress
        btnTarik.disabled = true;
        btnTarik.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
        progressContainer.style.display = 'block';
        resultContainer.style.display = 'none';
        progressBar.style.width = '30%';
        progressText.textContent = 'Menghubungkan ke server remote...';

        // AJAX request
        fetch('{{ route("tarik-data-tidak-masuk.pull") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    dari_tanggal: dariTanggal,
                    sampai_tanggal: sampaiTanggal,
                    server_host: serverHost,
                    server_port: serverPort || 3306,
                    server_database: serverDatabase,
                    server_table: serverTable,
                    server_user: serverUser,
                    server_password: serverPassword,
                    fields: selectedFields
                })
            })
            .then(response => response.json())
            .then(data => {
                progressBar.style.width = '100%';
                progressText.textContent = 'Selesai';

                setTimeout(() => {
                    progressContainer.style.display = 'none';
                    resultContainer.style.display = 'block';

                    if (data.success) {
                        resultAlert.className = 'alert alert-success';
                        resultMessage.innerHTML = '<strong><i class="fas fa-check-circle me-2"></i>' + data.message + '</strong>';

                        let detailsHtml = '<div class="mt-3"><strong>Detail:</strong><ul class="mb-0">';
                        detailsHtml += '<li>Total data dari server: ' + data.data.total + ' record</li>';
                        detailsHtml += '<li>Data baru (Insert): ' + data.data.inserted + ' record</li>';
                        detailsHtml += '<li>Data di-update: ' + data.data.updated + ' record</li>';
                        detailsHtml += '<li>Data di-skip: ' + data.data.skipped + ' record</li>';
                        if (data.data.errors > 0) {
                            detailsHtml += '<li class="text-danger">Error: ' + data.data.errors + ' record</li>';
                        }
                        detailsHtml += '</ul></div>';

                        if (data.data.error_details && data.data.error_details.length > 0) {
                            detailsHtml += '<div class="mt-3"><strong>Detail Error:</strong><ul class="mb-0">';
                            data.data.error_details.forEach(error => {
                                detailsHtml += '<li class="text-danger small">NIK: ' + error.nik + ', Kode: ' + error.kode + ', Tanggal Mulai: ' + error.tanggal_mulai + ', Tanggal Selesai: ' + (error.tanggal_selesai || 'N/A') + ' - ' + error.error + '</li>';
                            });
                            detailsHtml += '</ul></div>';
                        }

                        resultDetails.innerHTML = detailsHtml;
                    } else {
                        resultAlert.className = 'alert alert-danger';
                        resultMessage.innerHTML = '<strong><i class="fas fa-exclamation-circle me-2"></i>' + data.message + '</strong>';
                        resultDetails.innerHTML = '';
                    }
                }, 500);
            })
            .catch(error => {
                console.error('Error:', error);
                progressContainer.style.display = 'none';
                resultContainer.style.display = 'block';
                resultAlert.className = 'alert alert-danger';
                resultMessage.innerHTML = '<strong><i class="fas fa-exclamation-circle me-2"></i>Terjadi kesalahan: ' + error.message + '</strong>';
                resultDetails.innerHTML = '';
            })
            .finally(() => {
                btnTarik.disabled = false;
                btnTarik.innerHTML = '<i class="fas fa-download me-2"></i>Tarik Data';
            });
    });

    // Set default tanggal (bulan ini)
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    document.getElementById('dari_tanggal').value = firstDay.toISOString().split('T')[0];
    document.getElementById('sampai_tanggal').value = lastDay.toISOString().split('T')[0];

    function showAlert(type, message) {
        const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        const alertContainer = document.createElement('div');
        alertContainer.innerHTML = alertHtml;
        document.querySelector('.card-body').prepend(alertContainer.firstElementChild);

        setTimeout(() => {
            alertContainer.firstElementChild.remove();
        }, 5000);
    }
</script>
@endpush

