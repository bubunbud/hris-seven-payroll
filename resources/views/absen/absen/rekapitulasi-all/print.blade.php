<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Absen All - Print</title>
    <style>
        @media print {
            .no-print {
                display: none;
            }
            @page {
                margin: 1cm;
                size: A4 landscape;
            }
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
        }
        .header h3 {
            margin: 5px 0;
            font-size: 12pt;
            font-weight: normal;
        }
        .header p {
            margin: 3px 0;
            font-size: 10pt;
        }
        .tabel-rekapitulasi {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 8pt;
            table-layout: fixed;
        }
        .tabel-rekapitulasi th {
            background-color: #f8f9fa;
            color: #000;
            padding: 3px 2px;
            text-align: center;
            writing-mode: horizontal-tb;
            text-orientation: mixed;
            transform: none;
            white-space: nowrap;
            text-align: center;
            font-weight: bold;
            border: 1px solid #333;
            font-size: 7pt;
        }
        .tabel-rekapitulasi th[rowspan="2"] {
            vertical-align: middle;
        }
        .tabel-rekapitulasi td {
            padding: 2px;
            border: 1px solid #333;
            text-align: center;
            font-size: 7pt;
        }
        .tabel-rekapitulasi td:nth-child(3) {
            text-align: left;
        }
        .tabel-rekapitulasi td:nth-child(4),
        .tabel-rekapitulasi td:nth-child(5),
        .tabel-rekapitulasi td:nth-child(6) {
            text-align: left;
            font-size: 8pt;
        }
        .tabel-rekapitulasi td strong {
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9pt;
        }
        .btn-print {
            margin-bottom: 20px;
        }
        .keterangan {
            margin-top: 15px;
            font-size: 9pt;
        }
        .keterangan .card {
            border: 1px solid #333;
        }
        .keterangan .card-header {
            background-color: #f8f9fa;
            padding: 5px 10px;
            font-weight: bold;
            border-bottom: 1px solid #333;
        }
        .keterangan .card-body {
            padding: 10px;
        }
        .keterangan p {
            margin: 2px 0;
        }
    </style>
</head>
<body>
    <div class="no-print btn-print">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px;">
            <i class="fas fa-print"></i> Cetak
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; cursor: pointer; border-radius: 4px; margin-left: 10px;">
            Tutup
        </button>
    </div>

    <div class="header">
        <h2>REKAPITULASI ABSENSI KARYAWAN</h2>
        <h3>ABADINUSA GROUP OF COMPANIES</h3>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</p>
        <p><strong>Jumlah Hari Kerja Normal: {{ $jumlahHariKerja }} Hari</strong></p>
    </div>

    <table class="tabel-rekapitulasi">
        <thead>
            <tr>
                <th rowspan="2" style="width: 2.5%;">No.</th>
                <th rowspan="2" style="width: 6%;">NIK</th>
                <th rowspan="2" style="width: 9.6%;">Nama</th>
                <th rowspan="2" style="width: 10%;">Divisi</th>
                <th rowspan="2" style="width: 7%;">Dept.</th>
                <th rowspan="2" style="width: 4%;">Group</th>
                <th rowspan="2" style="width: 4.9%;">Tgl Masuk</th>
                <th colspan="13" style="text-align: center;">Absensi</th>
                <th rowspan="2" style="width: 2.5%; padding: 3px 2px; text-align: center; vertical-align: top;">JHK</th>
                <th rowspan="2" style="width: 4%; padding: 3px 2px; text-align: center; vertical-align: top;">%TW</th>
                <th rowspan="2" style="width: 4%; padding: 3px 2px; text-align: center; vertical-align: top;">%AH</th>
                <th rowspan="2" style="width: 4%; padding: 3px 2px; text-align: center; vertical-align: top;">%FAK</th>
            </tr>
            <tr>
                <th style="width: 2%; padding: 2px 1px; text-align: center;">S</th>
                <th style="width: 2%; padding: 2px 1px; text-align: center;">I</th>
                <th style="width: 2%; padding: 2px 1px; text-align: center;">A</th>
                <th style="width: 2.2%; padding: 2px 1px; text-align: center;">IR</th>
                <th style="width: 2.2%; padding: 2px 1px; text-align: center;">IO</th>
                <th style="width: 2.2%; padding: 2px 1px; text-align: center;">CT</th>
                <th style="width: 2.2%; padding: 2px 1px; text-align: center;">CM</th>
                <th style="width: 2%; padding: 2px 1px; text-align: center;">T</th>
                <th style="width: 2.2%; padding: 2px 1px; text-align: center;">MS</th>
                <th style="width: 2.2%; padding: 2px 1px; text-align: center;">IB</th>
                <th style="width: 2.2%; padding: 2px 1px; text-align: center;">PC</th>
                <th style="width: 2%; padding: 2px 1px; text-align: center;">H</th>
                <th style="width: 2.2%; padding: 2px 1px; text-align: center;">K8</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapitulasiData as $data)
            <tr>
                <td>{{ $data['no'] }}</td>
                <td><strong>{{ $data['nik'] }}</strong></td>
                <td style="text-align: left;">{{ $data['nama'] }}</td>
                <td style="text-align: left; padding: 2px; font-size: 7pt;">{{ $data['divisi'] }}</td>
                <td style="text-align: left; padding: 2px; font-size: 7pt;">{{ $data['departemen'] }}</td>
                <td style="text-align: left; padding: 2px; font-size: 7pt;">{{ $data['group'] }}</td>
                <td style="text-align: center; padding: 2px; font-size: 7pt;">{{ $data['tgl_masuk'] }}</td>
                <td style="text-align: center; padding: 2px; font-size: 7pt;">{{ $data['s'] }}</td>
                <td style="text-align: center; padding: 2px; font-size: 7pt;">{{ $data['i'] }}</td>
                <td style="text-align: center; padding: 2px; font-size: 7pt;">{{ $data['a'] }}</td>
                <td style="text-align: center; padding: 2px; font-size: 7pt;">{{ $data['ir'] }}</td>
                <td style="text-align: center; padding: 2px; font-size: 7pt;">{{ $data['io'] }}</td>
                <td style="text-align: center; padding: 2px; font-size: 7pt;">{{ $data['ct'] }}</td>
                <td style="text-align: center; padding: 2px; font-size: 7pt;">{{ $data['cm'] }}</td>
                <td style="text-align: center; padding: 2px; font-size: 7pt;">{{ $data['t'] }}</td>
                <td style="text-align: center; padding: 2px; font-size: 7pt;">{{ $data['ms'] ?? 0 }}</td>
                <td style="text-align: center; padding: 2px; font-size: 7pt;">{{ $data['ib'] ?? 0 }}</td>
                <td style="text-align: center; padding: 2px; font-size: 7pt;">{{ $data['pc'] ?? 0 }}</td>
                <td style="text-align: center; padding: 2px; font-size: 7pt; writing-mode: horizontal-tb; text-orientation: mixed; transform: none; white-space: nowrap;"><strong>{{ $data['h'] }}</strong></td>
                <td style="text-align: center; padding: 2px; font-size: 7pt;">{{ $data['k8'] ?? 0 }}</td>
                <td style="text-align: center; padding: 4px; white-space: nowrap; vertical-align: top;">{{ $data['jhk'] }}</td>
                <td style="text-align: center; padding: 4px; white-space: nowrap; vertical-align: top;">{{ str_replace('.', ',', number_format($data['persentase_tw'] ?? 0, 2)) }}%</td>
                <td style="text-align: center; padding: 4px; white-space: nowrap; vertical-align: top;">{{ str_replace('.', ',', number_format($data['persentase_ah'] ?? 0, 2)) }}%</td>
                <td style="text-align: center; padding: 4px; white-space: nowrap; vertical-align: top;">{{ str_replace('.', ',', number_format($data['persentase_fak'] ?? 0, 2)) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="keterangan">
        <div class="card">
            <div class="card-header">
                <strong>Keterangan:</strong>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-wrap: wrap;">
                    <div style="width: 50%;">
                        <p><strong>S</strong> = Sakit (S010)</p>
                        <p><strong>I</strong> = Ijin Pribadi (I002)</p>
                        <p><strong>A</strong> = Alfa (A001) atau tidak ada data absensi dan tidak ada data input tidak masuk</p>
                        <p><strong>IR</strong> = Ijin Resmi (I001)</p>
                        <p><strong>IO</strong> = Ijin Organisasi (I003)</p>
                        <p><strong>CT</strong> = Cuti Tahunan (C010)</p>
                        <p><strong>CM</strong> = Cuti Melahirkan</p>
                        <p><strong>T</strong> = Terlambat (jam masuk > jam masuk Shift-nya)</p>
                    </div>
                    <div style="width: 50%;">
                        <p><strong>MS</strong> = Ijin Keluar komplek pribadi kategori=Masuk Siang</p>
                        <p><strong>IB</strong> = Ijin Keluar komplek pribadi kategori=Izin Biasa</p>
                        <p><strong>PC</strong> = Ijin Keluar komplek pribadi kategori=Pulang Cepat</p>
                        <p><strong>H</strong> = Jumlah Hadir</p>
                        <p><strong>K8</strong> = Jam Kerja Kurang dari 8 jam</p>
                        <p><strong>%TW</strong> = Persentase Tepat Waktu</p>
                        <p><strong>%AH</strong> = Persentase Aktual Hadir</p>
                        <p><strong>%FAK</strong> = Final Absensi secara Kebijakan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <script>
        // Auto print saat halaman dibuka (opsional, bisa di-comment jika tidak diinginkan)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>
















