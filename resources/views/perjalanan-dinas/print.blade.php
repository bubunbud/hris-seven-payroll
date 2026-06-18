<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Perjalanan Dinas - {{ $header->vcNoRpd }}</title>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            @page {
                /* Margin diperkecil agar area cetak lebih luas */
                margin: 0.5cm 0.2cm;
                size: A4 portrait;
            }

            body {
                margin: 0;
                padding: 0;
            }

            .page-break {
                page-break-after: always;
            }
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Calibri', Arial, sans-serif;
            font-size: 11pt;
            margin: 0;
            /* Padding diperkecil agar area konten lebih lebar */
            padding: 10px;
            background-color: #fff;
            line-height: 1.4;
        }

        .print-container {
            max-width: 21cm;
            margin: 0 auto;
            background-color: #fff;
        }

        /* Header Section */
        .header {
            /* Kotak header dihilangkan, hanya menyisakan layout tanpa border luar */
            padding: 10px 5px 5px 5px;
            margin-bottom: 0px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            /* Jarak antara nama perusahaan dan judul form diperkecil */
            margin-bottom: 2px;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-img {
            /* Logo diperkecil ~40% */
            height: 25px;
            width: auto;
            object-fit: contain;
            margin-top: 5px;
        }

        .company-info {
            flex: 1;
            text-align: left;
            padding-left: 180px;
        }

        .company-name {
            /* Sedikit diperbesar */
            font-size: 20pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0;
        }

        .form-title-section {
            text-align: center;
            /* Hanya satu garis di bawah judul form */
            border-bottom: 2px solid #000;
            padding: 1px 0 4px 0;
            /* Judul form dinaikkan, lebih dekat ke nama perusahaan */
            margin: 2px 0 12px 0;
        }

        .form-title {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0;
        }

        .form-number {
            text-align: right;
            font-size: 11pt;
            font-weight: bold;
            margin-top: 10px;
        }

        /* Form Content */
        .form-content {
            margin-top: 5px;
        }

        .form-group {
            margin-bottom: 10px;
        }

        .form-row {
            display: flex;
            margin-bottom: 10px;
            align-items: flex-start;
        }

        .form-label {
            width: 200px;
            font-weight: bold;
            flex-shrink: 0;
            padding-right: 10px;
        }

        .form-label::after {
            content: ":";
        }

        .form-value {
            flex: 1;
            border-bottom: 1px solid #000;
            min-height: 20px;
            padding-bottom: 2px;
            padding-left: 5px;
        }

        .form-value-full {
            width: 100%;
            border-bottom: 1px solid #000;
            min-height: 20px;
            padding-bottom: 2px;
            padding-left: 5px;
        }

        /* Section Title */
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 12px;
            margin-bottom: 6px;
            padding-bottom: 3px;
            border-bottom: 1px solid #000;
            text-transform: uppercase;
        }

        /* Table Styles */
        .table-print {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 8px;
            font-size: 10pt;
        }

        .table-print th,
        .table-print td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        .table-print th {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
            font-size: 10pt;
        }

        .table-print td {
            font-size: 10pt;
        }

        .table-print .text-center {
            text-align: center;
        }

        .table-print .text-right {
            text-align: right;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .signature-box {
            text-align: center;
            width: 30%;
            min-width: 150px;
        }

        .signature-label {
            font-weight: bold;
            margin-bottom: 60px;
            font-size: 11pt;
        }

        .signature-sub-label {
            font-size: 9pt;
            margin-top: 3px;
            color: #333;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 0;
            padding-top: 0;
            min-height: 0;
            height: 1px;
        }

        .signature-name {
            font-size: 10pt;
            margin-bottom: 0px;
            padding-bottom: 5px;
        }

        /* Buttons */
        .no-print {
            text-align: center;
            margin-top: 20px;
        }

        .btn-print {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
        }

        .btn-print:hover {
            background-color: #0056b3;
        }

        .btn-close {
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-close:hover {
            background-color: #5a6268;
        }

        /* Additional Styles */
        .info-box {
            border: 1px solid #000;
            padding: 10px;
            margin: 15px 0;
            background-color: #f9f9f9;
        }

        .text-bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="print-container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div class="logo-section">
                    <img src="{{ asset('img/logo-abn.png') }}" alt="Logo" class="logo-img" onerror="this.style.display='none'">
                </div>
                <div class="company-info">
                    <div class="company-name">ABN GROUP</div>
                </div>
            </div>

            <div class="form-title-section">
                <h1 class="form-title">Form Perjalanan Dinas</h1>
            </div>
        </div>

        <!-- Form Content -->
        <div class="form-content">
            <!-- Informasi Umum -->
            <div class="form-group">
                <div style="text-align: right; margin-bottom: 10px; font-weight: bold; font-size: 11pt;">
                    No. RPD: <span class="text-bold">{{ $header->vcNoRpd }}</span>
                </div>
                <div class="form-row">
                    <div class="form-label">Tanggal Mulai Dinas</div>
                    <div class="form-value">{{ $header->dtTanggalDinasDari ? $header->dtTanggalDinasDari->format('d F Y') : '-' }}</div>
                </div>
                <div class="form-row">
                    <div class="form-label">Tanggal Sampai Dinas</div>
                    <div class="form-value">{{ $header->dtTanggalDinasSampai ? $header->dtTanggalDinasSampai->format('d F Y') : '-' }}</div>
                </div>
                <div class="form-row">
                    <div class="form-label">Durasi</div>
                    <div class="form-value">{{ $header->intDurasiHari ?? 0 }} hari</div>
                </div>
                <div class="form-row">
                    <div class="form-label">Pemberi Tugas</div>
                    <div class="form-value">{{ $header->vcPemberiTugas ?? '-' }}</div>
                </div>
                <div class="form-row">
                    <div class="form-label">Jabatan Pemberi Tugas</div>
                    <div class="form-value">{{ $header->vcJabatanPemberiTugas ?? '-' }}</div>
                </div>
                <div class="form-row">
                    <div class="form-label">Tujuan Dinas</div>
                    <div class="form-value">{{ $header->vcTujuanDinas ?? '-' }}</div>
                </div>
                <div class="form-group" style="margin-top: 10px;">
                    <div class="form-label" style="width: 100%; margin-bottom: 5px;">Maksud / Uraian Perjalanan Dinas:</div>
                    <div class="form-value-full" style="min-height: 40px;">{{ $header->vcMaksudPerjalananDinas ?? '-' }}</div>
                </div>
            </div>

            <!-- Karyawan Yang Ditugaskan -->
            <div class="section-title">1. Karyawan Yang Ditugaskan</div>
            <table class="table-print">
                <thead>
                    <tr>
                        <th width="4%">No</th>
                        <th width="8%">NIK</th>
                        <th width="18%">Nama</th>
                        <th width="19%">Bisnis Unit</th>
                        <th width="15%">Departemen</th>
                        <th width="17%">Jabatan</th>
                        <th width="19%">Klasifikasi Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($header->karyawans as $index => $karyawan)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $karyawan->vcNik ?? '-' }}</td>
                        <td>{{ $karyawan->vcNamaKaryawan ?? '-' }}</td>
                        <td>{{ $karyawan->karyawan->divisi->vcNamaDivisi ?? ($karyawan->karyawan->Divisi ?? '-') }}</td>
                        <td>{{ $karyawan->departemen->vcNamaDept ?? '-' }}</td>
                        <td>{{ $karyawan->jabatan->vcNamaJabatan ?? '-' }}</td>
                        <td>{{ $karyawan->vcKlasifikasiGrade ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Jadwal dan Moda Perjalanan -->
            <div class="section-title">2. Jadwal dan Moda Perjalanan</div>
            <table class="table-print">
                <thead>
                    <tr>
                        <th width="4%">No</th>
                        <th width="18%">Moda Perjalanan</th>
                        <th width="14%">Tanggal Berangkat</th>
                        <th width="9%">Jam</th>
                        <th width="14%">Tanggal Sampai</th>
                        <th width="9%">Jam</th>
                        <th width="32%">Keterangan Moda</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($header->jadwals as $index => $jadwal)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $jadwal->vcModaPerjalanan ?? '-' }}</td>
                        <td>{{ $jadwal->dtTanggalBerangkat ? \Carbon\Carbon::parse($jadwal->dtTanggalBerangkat)->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">{{ $jadwal->dtJamBerangkat ? substr($jadwal->dtJamBerangkat, 0, 5) : '-' }}</td>
                        <td>{{ $jadwal->dtTanggalKembali ? \Carbon\Carbon::parse($jadwal->dtTanggalKembali)->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">{{ $jadwal->dtJamKembali ? substr($jadwal->dtJamKembali, 0, 5) : '-' }}</td>
                        <td>{{ $jadwal->vcKeteranganBerangkat ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Hotel / Penginapan -->
            @php
            $hotels = $header->hotels ? $header->hotels->where('isMenginap', true) : collect();
            @endphp
            <div class="section-title">3. Hotel / Penginapan</div>
            <table class="table-print">
                <thead>
                    <tr>
                        <th width="4%">No</th>
                        <th width="18%">Tanggal Menginap</th>
                        <th width="25%">Kota / Provinsi / Negara</th>
                        <th width="30%">Nama Hotel</th>
                        <th width="23%">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @if($hotels->count() > 0)
                    @foreach($hotels as $index => $hotel)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $hotel->dtTanggalMenginap ? \Carbon\Carbon::parse($hotel->dtTanggalMenginap)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $hotel->vcKotaProvinsiNegara ?? '-' }}</td>
                        <td>{{ $hotel->vcNamaHotel ?? '-' }}</td>
                        <td>{{ $hotel->vcKeteranganHotel ?? '-' }}</td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td class="text-center">1</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <!-- Otorisasi / Tanda Tangan -->
            <div class="section-title">4. Otorisasi</div>
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-label">Mengajukan</div>
                    <div class="signature-name">{{ $header->vcMengajukan ?? '' }}</div>
                    <div class="signature-line"></div>
                    <div class="signature-sub-label">Penerima Tugas</div>
                </div>
                <div class="signature-box">
                    <div class="signature-label">Menyetujui</div>
                    <div class="signature-name">{{ $header->vcMenyetujui ?? '' }}</div>
                    <div class="signature-line"></div>
                    <div class="signature-sub-label">Pemberi Tugas</div>
                </div>
                <div class="signature-box">
                    <div class="signature-label">Mengetahui</div>
                    <div class="signature-name">{{ $header->vcMengetahui ?? '' }}</div>
                    <div class="signature-line"></div>
                    <div class="signature-sub-label">HRD</div>
                </div>
            </div>

            <!-- Destinasi / Tiba Kembali -->
            <div class="section-title" style="border: 2px solid #dc3545; padding: 8px; background-color: #fff5f5;">5. Tiba / Kembali (diisi oleh petugas di tempat tujuan)</div>
            <table class="table-print" style="margin-top: 10px;">
                <thead>
                    <tr>
                        <th width="18%">Hari/Tanggal:</th>
                        <th width="12%">Jam:</th>
                        <th width="40%">Keterangan</th>
                        <th width="30%">Tanda Tangan/Cap</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Tiba</strong></td>
                        <td></td>
                        <td rowspan="4" style="vertical-align: top;">
                            @if($header->tibaKembali && $header->tibaKembali->vcKeteranganKedatangan)
                            {{ $header->tibaKembali->vcKeteranganKedatangan }}
                            @endif
                        </td>
                        <td rowspan="4" style="vertical-align: top; text-align: center; position: relative;">
                            <!-- Space untuk tanda tangan di atas -->
                            <div style="min-height: 80px; margin-bottom: 5px;">
                                <!-- Space kosong untuk tanda tangan -->
                            </div>
                            <!-- Garis untuk tanda tangan -->
                            <div style="border-top: 1px solid #000; margin-top: 0; padding-top: 5px; font-size: 9pt;">
                                @if($header->tibaKembali && $header->tibaKembali->vcTandaTanganPihakBerwenang)
                                {{ $header->tibaKembali->vcTandaTanganPihakBerwenang }}
                                @else
                                Nama Jelas, TTD & Cap Perusahaan
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            @if($header->tibaKembali && $header->tibaKembali->dtTanggalTiba)
                            {{ $header->tibaKembali->vcHariTiba ?? '' }} / {{ $header->tibaKembali->dtTanggalTiba->format('d/m/Y') }}
                            @endif
                        </td>
                        <td class="text-center">
                            @if($header->tibaKembali && $header->tibaKembali->dtJamTiba)
                            {{ substr($header->tibaKembali->dtJamTiba, 0, 5) }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Kembali</strong></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>
                            @if($header->tibaKembali && $header->tibaKembali->dtTanggalKembali)
                            {{ $header->tibaKembali->vcHariKembali ?? '' }} / {{ $header->tibaKembali->dtTanggalKembali->format('d/m/Y') }}
                            @endif
                        </td>
                        <td class="text-center">
                            @if($header->tibaKembali && $header->tibaKembali->dtJamKembali)
                            {{ substr($header->tibaKembali->dtJamKembali, 0, 5) }}
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Print
        </button>
        <button class="btn-close" onclick="window.close()">
            <i class="fas fa-times"></i> Close
        </button>
    </div>
</body>

</html>