<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Biaya Perjalanan Dinas - {{ $header->vcNoBpd }}</title>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            @page {
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
            padding: 10px 5px 5px 5px;
            margin-bottom: 0px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2px;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-img {
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
            font-size: 20pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0;
        }

        .form-title-section {
            text-align: center;
            border-bottom: 2px solid #000;
            padding: 1px 0 4px 0;
            margin: 2px 0 12px 0;
        }

        .form-title {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0;
        }

        .form-number-right {
            text-align: right;
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 10px;
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
            margin-bottom: 8px;
            align-items: flex-start;
        }

        .form-label {
            width: 180px;
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

        .text-bold {
            font-weight: bold;
        }

        .note-box {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 8px;
            margin: 10px 0;
            font-size: 9pt;
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
                <div style="font-size: 14pt; font-weight: bold;">DN</div>
            </div>

            <div class="form-title-section">
                <h1 class="form-title">{{ ($header->vcStatus ?? '') === 'complete' ? 'BIAYA PERJALANAN DINAS' : 'KASBON PERJALANAN DINAS' }}</h1>
            </div>
        </div>

        <!-- Form Content -->
        <div class="form-content">
            <!-- Informasi Umum -->
            <div class="form-group">
                <div class="form-row">
                    <div class="form-label">No. RPD</div>
                    <div class="form-value">{{ $header->vcNoRpd ?? '-' }}</div>
                </div>
                <div class="form-row">
                    <div class="form-label">Nama Penerima Tugas</div>
                    <div class="form-value">
                        @php
                            $penerimaTugas = $header->vcMelaporkan ?? '';
                            if (empty($penerimaTugas) && $header->perjalananDinas && $header->perjalananDinas->karyawans) {
                                $karyawanPertama = $header->perjalananDinas->karyawans->first();
                                $penerimaTugas = $karyawanPertama ? $karyawanPertama->vcNamaKaryawan : '';
                            }
                        @endphp
                        {{ $penerimaTugas ?: '-' }}
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-label">Tanggal Dinas</div>
                    <div class="form-value">
                        @if($header->perjalananDinas && $header->perjalananDinas->dtTanggalDinasDari && $header->perjalananDinas->dtTanggalDinasSampai)
                            {{ $header->perjalananDinas->dtTanggalDinasDari->format('d/m/Y') }} s.d {{ $header->perjalananDinas->dtTanggalDinasSampai->format('d/m/Y') }}
                        @elseif($header->perjalananDinas && $header->perjalananDinas->dtTanggalDinasDari)
                            {{ $header->perjalananDinas->dtTanggalDinasDari->format('d/m/Y') }}
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-label">Pemberi Tugas</div>
                    <div class="form-value">{{ $header->vcPemberiTugas ?? '-' }}</div>
                </div>
            </div>

            <!-- Kasbon Perjalanan Dinas -->
            <div class="section-title">Kasbon Perjalanan Dinas</div>
            <div class="form-group">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-row">
                            <div class="form-label">Nilai</div>
                            <div class="form-value">{{ $header->decKasbonNilai ? number_format($header->decKasbonNilai, 0, ',', '.') : '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-row">
                            <div class="form-label">Terbilang</div>
                            <div class="form-value" style="min-height: 40px;">{{ $header->vcKasbonTerbilang ?? '-' }}</div>
                        </div>
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                    <div style="text-align: center; width: 200px; min-width: 150px;">
                        <div style="font-weight: bold; margin-bottom: 10px; font-size: 11pt;">Dept. Keuangan*</div>
                        @if(($header->vcStatus ?? '') === 'complete')
                            @php
                                $qrContent = 'Rina Aryani | ' . ($header->vcNoRpd ?? '');
                                $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=' . urlencode($qrContent);
                            @endphp
                            <img src="{{ $qrUrl }}" alt="QR Tanda Tangan" style="width: 80px; height: 80px; display: block; margin: 0 auto;">
                        @else
                            <div style="margin-bottom: 50px;"></div>
                            <div style="border-top: 1px solid #000; margin-top: 0; padding-top: 0; min-height: 0; height: 1px;"></div>
                            <div style="font-size: 9pt; margin-top: 3px; color: #333; margin-bottom: 5px;">Tanda Tangan</div>
                        @endif
                        <div style="font-size: 8pt; margin-top: 5px; color: #666;">*) Setelah perjalanan dinas disetujui oleh Pemberi Tugas</div>
                    </div>
                </div>
            </div>

            @if(($header->vcStatus ?? '') === 'complete')
            <!-- Laporan Biaya Perjalanan Dinas -->
            <div class="section-title">Laporan Biaya Perjalanan Dinas</div>
            <div class="note-box">
                <strong>Catatan:</strong> Kuitansi dan bukti pembayaran yang sah agar dilampirkan secara tersusun
            </div>
            
            @php
            $detailsByKategori = $header->details->groupBy('vcKategoriBiaya');
            @endphp

            <table class="table-print">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="25%">Kategori / Sub Kategori</th>
                        <th width="15%">Tanggal</th>
                        <th width="15%">Nilai</th>
                        <th width="15%">Total</th>
                        <th width="25%">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rowNumber = 1; @endphp
                    
                    @foreach(['Penginapan', 'Kendaraan Umum', 'Kendaraan Dinas/Pribadi', 'Makan/Minum', 'Lain-lain'] as $kategori)
                        @if($detailsByKategori->has($kategori))
                            @foreach($detailsByKategori[$kategori] as $detail)
                            <tr>
                                <td class="text-center">{{ $rowNumber++ }}</td>
                                <td>
                                    <strong>{{ $detail->vcKategoriBiaya }}</strong>
                                    @if($detail->vcSubKategori)
                                        <br><small>{{ $detail->vcSubKategori }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($detail->dtTanggalDari && $detail->dtTanggalSampai)
                                        {{ $detail->dtTanggalDari->format('d/m/Y') }} s.d {{ $detail->dtTanggalSampai->format('d/m/Y') }}
                                    @elseif($detail->dtTanggalDari)
                                        {{ $detail->dtTanggalDari->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-right">{{ $detail->decNilai ? number_format($detail->decNilai, 0, ',', '.') : '-' }}</td>
                                <td class="text-right">{{ $detail->decTotal ? number_format($detail->decTotal, 0, ',', '.') : '-' }}</td>
                                <td>{{ $detail->vcKeterangan ?? '-' }}</td>
                            </tr>
                            @endforeach
                        @endif
                    @endforeach
                    
                    <!-- Summary Row -->
                    <tr style="background-color: #f0f0f0; font-weight: bold;">
                        <td colspan="3" class="text-center">Total Pengeluaran</td>
                        <td></td>
                        <td class="text-right">{{ $header->decTotalPengeluaran ? number_format($header->decTotalPengeluaran, 0, ',', '.') : '0' }}</td>
                        <td></td>
                    </tr>
                    <tr style="background-color: #f0f0f0; font-weight: bold;">
                        <td colspan="3" class="text-center">Kekurangan / Kelebihan</td>
                        <td></td>
                        <td class="text-right">{{ $header->decKekuranganKelebihan ? number_format($header->decKekuranganKelebihan, 0, ',', '.') : '0' }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            <!-- Laporan Singkat -->
            <div class="section-title">Laporan Singkat</div>
            <div class="form-group">
                <div style="border: 1px solid #000; min-height: 80px; padding: 8px;">
                    {{ $header->vcLaporanSingkat ?? '' }}
                </div>
            </div>

            <!-- Otorisasi -->
            <div class="section-title">Otorisasi</div>
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-label">Melaporkan</div>
                    <div class="signature-name">{{ $header->vcMelaporkan ?? '' }}</div>
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
                    <div style="display: flex; justify-content: space-between; margin-bottom: 40px;">
                        <div style="width: 48%;">
                            <div class="signature-name" style="min-height: 20px;">{{ $header->vcMengetahuiHrd ?? '' }}</div>
                            <div class="signature-line"></div>
                            <div class="signature-sub-label">Human Resources</div>
                        </div>
                        <div style="width: 48%;">
                            <div class="signature-name" style="min-height: 20px;">{{ $header->vcMengetahuiFinance ?? '' }}</div>
                            <div class="signature-line"></div>
                            <div class="signature-sub-label">Finance & Accounting</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
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



