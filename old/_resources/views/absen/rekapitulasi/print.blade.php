<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Absensi Karyawan - {{ $karyawan->Nama }}</title>
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
            font-size: 11pt;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
        }
        .info-karyawan {
            margin-bottom: 15px;
        }
        .info-karyawan table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-karyawan td {
            padding: 4px 8px;
            font-size: 10pt;
        }
        .info-karyawan td:first-child {
            font-weight: bold;
            width: 100px;
        }
        .info-karyawan td strong {
            font-weight: bold;
        }
        .tabel-rekapitulasi {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .tabel-rekapitulasi th {
            background-color: #4472C4;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10pt;
            border: 1px solid #333;
        }
        .tabel-rekapitulasi th:nth-child(2),
        .tabel-rekapitulasi th:nth-child(3) {
            text-align: right;
        }
        .tabel-rekapitulasi td {
            padding: 6px 8px;
            border: 1px solid #333;
            font-size: 10pt;
        }
        .tabel-rekapitulasi td:first-child {
            background-color: #D9E1F2;
            font-weight: 500;
        }
        .tabel-rekapitulasi td:nth-child(2),
        .tabel-rekapitulasi td:nth-child(3) {
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 9pt;
        }
        .btn-print {
            margin-bottom: 20px;
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
        <h2>Rekapitulasi Absensi Karyawan Periode {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} sampai {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</h2>
    </div>

    <div class="info-karyawan">
        <table>
            <tr>
                <td><strong>NIK:</strong></td>
                <td><strong>{{ $karyawan->Nik }}</strong></td>
                <td style="padding-left: 15px;"><strong>Nama:</strong></td>
                <td><strong>{{ $karyawan->Nama }}</strong></td>
                <td style="padding-left: 15px;"><strong>Tanggal Masuk:</strong></td>
                <td><strong>{{ $karyawan->Tgl_Masuk ? \Carbon\Carbon::parse($karyawan->Tgl_Masuk)->format('d/m/Y') : '-' }}</strong></td>
            </tr>
            <tr>
                <td>Divisi:</td>
                <td>{{ $karyawan->divisi->vcNamaDivisi ?? '-' }}</td>
                <td style="padding-left: 15px;">Departemen:</td>
                <td>{{ $karyawan->departemen->vcNamaDept ?? '-' }}</td>
                <td style="padding-left: 15px;">Bagian:</td>
                <td>{{ $karyawan->bagian->vcNamaBagian ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <table class="tabel-rekapitulasi">
        <thead>
            <tr>
                <th>Kriteria</th>
                <th>Jumlah</th>
                <th>Persentase</th>
                <th>Formulasi atau aturan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapitulasi as $item)
            <tr>
                <td>{{ $item['kriteria'] }}</td>
                <td>{{ number_format($item['jumlah'], 0) }}</td>
                <td>
                    @if($item['persentase'] !== null)
                        {{ str_replace('.', ',', number_format($item['persentase'], 3)) }}%
                    @else
                        -
                    @endif
                </td>
                <td>{{ $item['formulasi'] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if(count($averageJamKerjaPerBulan) > 0)
    <div style="margin-top: 30px;">
        <h3 style="font-size: 12pt; font-weight: bold; margin-bottom: 10px; color: #28a745;">
            Rata-rata Jam Kerja per Bulan
        </h3>
        <div style="background-color: #d1ecf1; padding: 8px; margin-bottom: 10px; border-left: 4px solid #17a2b8; font-size: 9pt;">
            <strong>Keterangan:</strong> Data rata-rata jam kerja di atas merupakan perhitungan berdasarkan kehadiran aktual (Hadir) saja, tidak termasuk Cuti, Sakit, atau Izin Resmi.
        </div>
        <table class="tabel-rekapitulasi" style="margin-top: 10px;">
            <thead>
                <tr>
                    <th style="background-color: #28a745;">Bulan</th>
                    <th style="background-color: #28a745; text-align: right;">Total Jam Kerja</th>
                    <th style="background-color: #28a745; text-align: right;">Jumlah Hari Kerja</th>
                    <th style="background-color: #28a745; text-align: right;">Rata-rata Jam Kerja/Hari</th>
                </tr>
            </thead>
            <tbody>
                @foreach($averageJamKerjaPerBulan as $data)
                <tr>
                    <td style="background-color: #d4edda; font-weight: 500;">{{ $data['bulan'] }}</td>
                    <td style="text-align: right;">{{ number_format($data['total_jam_kerja'], 2) }} jam</td>
                    <td style="text-align: right;">{{ number_format($data['jumlah_hari_kerja'], 0) }} hari</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($data['rata_rata_jam_kerja'], 2) }} jam</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #f8f9fa; font-weight: bold;">
                    <td style="background-color: #f8f9fa; font-weight: bold;">Rata-rata Keseluruhan</td>
                    <td style="text-align: right; font-weight: bold;">
                        {{ number_format(collect($averageJamKerjaPerBulan)->sum('total_jam_kerja'), 2) }} jam
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        {{ number_format(collect($averageJamKerjaPerBulan)->sum('jumlah_hari_kerja'), 0) }} hari
                    </td>
                    <td style="text-align: right; font-weight: bold; color: #28a745;">
                        @php
                            $totalJam = collect($averageJamKerjaPerBulan)->sum('total_jam_kerja');
                            $totalHari = collect($averageJamKerjaPerBulan)->sum('jumlah_hari_kerja');
                            $rataRataKeseluruhan = $totalHari > 0 ? round($totalJam / $totalHari, 2) : 0;
                        @endphp
                        {{ number_format($rataRataKeseluruhan, 2) }} jam
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

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

