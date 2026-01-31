<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Izin Keluar Komplek - {{ $record->vcCounter }}</title>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            @page {
                margin: 1.5cm;
                size: A4 portrait;
            }

            body {
                margin: 0;
                padding: 0;
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            margin: 0;
            padding: 20px;
            background-color: #fff;
        }

        .print-container {
            max-width: 21cm;
            margin: 0 auto;
            background-color: #fff;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 10px;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-img {
            height: 40px;
            width: auto;
            object-fit: contain;
        }

        .title-section {
            flex: 1;
            text-align: center;
            margin-top: 10px;
        }

        .title {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0;
        }

        .no-section {
            text-align: right;
            margin-top: 10px;
        }

        .no-label {
            font-size: 11pt;
            font-weight: bold;
        }

        .form-section {
            margin-top: 30px;
        }

        .form-row {
            display: flex;
            margin-bottom: 15px;
            align-items: flex-start;
        }

        .form-label {
            width: 120px;
            font-weight: bold;
            flex-shrink: 0;
        }

        .form-value {
            flex: 1;
            border-bottom: 1px solid #000;
            min-height: 20px;
            padding-left: 10px;
        }

        .form-row-time {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-value-time {
            flex: 1;
            border-bottom: 1px solid #000;
            min-height: 20px;
            padding-left: 10px;
            max-width: 150px;
        }

        .time-label {
            font-size: 10pt;
            margin-left: 10px;
            white-space: nowrap;
        }

        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: flex-end;
        }

        .signature-box {
            flex: 1;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: center;
            min-height: 100px;
            gap: 10px;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 60px;
            margin-bottom: 0;
            min-width: 200px;
        }

        .signature-label {
            font-size: 10pt;
            font-weight: bold;
            margin-top: 0;
            margin-bottom: 0;
        }

        .signature-role {
            font-size: 9pt;
            margin-top: 0;
            margin-bottom: 0;
        }

        .signature-box.re-entry {
            text-align: left;
            align-items: flex-start;
        }

        .reentry-header {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .time-row {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 10pt;
        }

        .time-dash {
            display: inline-block;
            min-width: 110px;
            border-bottom: 1px dashed #666;
            height: 14px;
        }

        .footer-note {
            margin-top: 10px;
            font-size: 9pt;
            font-style: italic;
        }

        .btn-print {
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14pt;
        }

        .btn-print:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="print-container">
        <!-- Tombol Print (akan disembunyikan saat print) -->
        <div class="no-print" style="text-align: center; margin-bottom: 20px;">
            <button class="btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Print Surat Izin
            </button>
            <a href="{{ route('izin-keluar.index') }}" style="margin-left: 10px; padding: 10px 20px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 5px;">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                <img src="{{ asset('img/logo-abn.png') }}" alt="ABN" class="logo-img">
            </div>
            <div class="title-section">
                <h1 class="title">IJIN KELUAR KOMPLEK</h1>
            </div>
            <div class="no-section">
                <div class="no-label">No. : {{ $record->vcCounter }}</div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="form-section">
            @php
            // Tentukan jenis izin (Pribadi atau Dinas)
            $isPribadi = in_array($record->vcKodeIzin, ['Z003', 'Z004']);
            $jenisIzin = $isPribadi ? 'Pribadi' : 'Dinas';
            @endphp

            <div class="form-row">
                <div class="form-label">Nama</div>
                <div class="form-value">{{ $record->karyawan->Nama ?? 'N/A' }}</div>
            </div>

            <div class="form-row">
                <div class="form-label">NIK</div>
                <div class="form-value">{{ $record->vcNik }}</div>
            </div>

            <div class="form-row">
                <div class="form-label">Bagian</div>
                <div class="form-value">
                    @php
                    $bagian = $record->karyawan->bagian->vcNamaBagian ?? null;
                    $divisi = $record->karyawan->divisi->vcNamaDivisi ?? null;

                    if ($bagian && $divisi) {
                    echo $bagian . ' / ' . $divisi;
                    } elseif ($bagian) {
                    echo $bagian;
                    } elseif ($divisi) {
                    echo $divisi;
                    } else {
                    echo '-';
                    }
                    @endphp
                </div>
            </div>

            <div class="form-row">
                <div class="form-label">Jenis Izin</div>
                <div class="form-value">{{ $jenisIzin }}</div>
            </div>

            @if($isPribadi && $record->vcTipeIzin)
            <div class="form-row">
                <div class="form-label">Tipe Izin Pribadi</div>
                <div class="form-value">{{ $record->vcTipeIzin }}</div>
            </div>
            @endif

            <div class="form-row">
                <div class="form-label">Keperluan</div>
                <div class="form-value">{{ $record->vcKeterangan ?? '-' }}</div>
            </div>

            <div class="form-row">
                <div class="form-label">Tanggal</div>
                <div class="form-row-time" style="flex: 1;">
                    <div class="form-value-time">{{ $record->dtTanggal ? $record->dtTanggal->format('d/m/Y') : '-' }}</div>
                    <span class="time-label">Perkiraan Keluar : {{ $record->dtDari ? substr($record->dtDari, 0, 5) : '-' }} WIB</span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-label">Perkiraan Kembali</div>
                <div class="form-row-time" style="flex: 1;">
                    <div class="form-value-time">
                        @if($record->dtSampai)
                        {{ substr($record->dtSampai, 0, 5) }} WIB
                        @else
                        Tidak Kembali
                        @endif
                    </div>
                    <span class="time-label">/ Tidak Kembali</span>
                </div>
            </div>

            <!-- Jam Aktual (untuk diisi satpam) -->
            <div class="form-row" style="margin-top: 20px;">
                <div class="form-label">Aktual Keluar*</div>
                <div class="form-row-time" style="flex: 1;">
                    <div class="form-value-time" style="border-bottom: 1px dashed #666; color: #999; font-size: 11pt;">&nbsp;</div>
                    <span class="time-label" style="font-size: 11pt;">WIB</span>
                </div>
            </div>

            <div class="form-row" style="margin-top: 20px;">
                <div class="form-label">Aktual Kembali * : </div>
                <div class="form-row-time" style="flex: 1;">
                    <div class="form-value-time" style="border-bottom: 1px dashed #666; color: #999; font-size: 11pt;">&nbsp;</div>
                    <span class="time-label" style="font-size: 11pt;">WIB</span>
                </div>
            </div>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">&nbsp;&nbsp;
            <div class="signature-box">
                <div class="signature-label">Satpam,</div>&nbsp;
                <div class="signature-line"></div>&nbsp;&nbsp;
            </div>

            <div class="signature-box">
                <div class="signature-label">Karyawan,</div>&nbsp;
                <div class="signature-line"></div>&nbsp;
            </div>

            <div class="signature-box">
                <div class="signature-label">Mengijinkan.</div>&nbsp;
                <div class="signature-line"></div>
                <div class="signature-role">Atasan / Kepala Bagian</div>
            </div>

            <div class="signature-box">
                <div class="signature-label">Mengetahui.</div>&nbsp;&nbsp;
                <div class="signature-line"></div>
                <div class="signature-role">Bagian HR</div>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="footer-note">
            * Diisi Oleh Satpam
        </div>
    </div>

    <script>
        // Auto print saat halaman dimuat (opsional, bisa di-comment jika tidak diinginkan)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>

</html>