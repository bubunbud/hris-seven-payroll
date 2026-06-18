<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Rekap Absensi Keterlambatan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            padding: 20px;
            background: white;
        }

        .print-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .print-header h3 {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .print-header h4 {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .print-header h5 {
            font-size: 10pt;
            margin-bottom: 5px;
        }

        .print-header h6 {
            font-size: 10pt;
            margin-bottom: 5px;
        }

        .table-container {
            width: 100%;
            margin-top: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
            margin-bottom: 15px;
            border-left: none;
        }

        /* Pastikan thead selalu muncul di screen dan print */
        table thead {
            display: table-header-group !important;
            visibility: visible !important;
        }

        table thead tr {
            display: table-row !important;
            visibility: visible !important;
        }

        table thead th {
            background-color: #e9ecef;
            border-top: 1px solid #000;
            border-right: 1px solid #000;
            border-bottom: 1px solid #000;
            border-left: none;
            padding: 4px 3px;
            text-align: center;
            font-weight: bold;
            font-size: 7pt;
            display: table-cell !important;
            visibility: visible !important;
        }

        table thead th:first-child {
            border-left: 1px solid #000;
        }

        table tbody tr {
            display: table-row;
        }

        table tbody td {
            border-top: 1px solid #000;
            border-right: 1px solid #000;
            border-bottom: 1px solid #000;
            border-left: none;
            padding: 3px 4px;
            text-align: center;
            font-size: 7pt;
        }

        table tbody td:first-child {
            border-left: 1px solid #000;
        }

        table tbody td.text-left {
            text-align: left;
        }

        .detail-section {
            margin: 10px 0;
            padding: 8px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }

        .detail-section h6 {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .detail-table {
            font-size: 7pt;
            width: 100%;
            border-collapse: collapse;
        }

        .detail-table thead {
            display: table-header-group !important;
            visibility: visible !important;
        }

        .detail-table thead tr {
            display: table-row !important;
            visibility: visible !important;
        }

        .detail-table thead th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 4px;
            font-size: 7pt;
            font-weight: bold;
            text-align: center;
            display: table-cell !important;
            visibility: visible !important;
        }

        .detail-table tbody td {
            border: 1px solid #000;
            padding: 3px 4px;
        }

        .detail-table tbody td {
            padding: 3px 4px;
            font-size: 7pt;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 1.5cm 1cm 1.5cm 1cm;
            }

            body {
                padding: 0;
                margin: 0;
            }

            .print-header {
                position: relative;
                background: white;
                margin-bottom: 15px;
                padding-bottom: 10px;
                page-break-after: avoid;
            }

            .table-container {
                margin-top: 10px;
            }

            /* Pastikan thead muncul di setiap halaman saat print */
            table {
                border-left: none !important;
                border: none !important;
            }

            /* CRITICAL: Pastikan thead selalu muncul dan diulang di setiap halaman */
            table thead {
                display: table-header-group !important;
                visibility: visible !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            table thead tr {
                display: table-row !important;
                visibility: visible !important;
            }

            table thead th {
                background-color: #e9ecef !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                display: table-cell !important;
                visibility: visible !important;
                opacity: 1 !important;
                color: #000 !important;
                border-top: 1px solid #000 !important;
                border-right: 1px solid #000 !important;
                border-bottom: 1px solid #000 !important;
                border-left: none !important;
                padding: 4px 3px !important;
                text-align: center !important;
                font-weight: bold !important;
                font-size: 7pt !important;
            }

            table thead th:first-child {
                border-left: 1px solid #000 !important;
            }

            table tbody {
                display: table-row-group !important;
            }

            table tbody tr {
                display: table-row !important;
                page-break-inside: avoid;
            }

            table tbody td {
                border-top: 1px solid #000 !important;
                border-right: 1px solid #000 !important;
                border-bottom: 1px solid #000 !important;
                border-left: none !important;
                padding: 3px 4px !important;
            }

            table tbody td:first-child {
                border-left: 1px solid #000 !important;
            }

            /* Detail table juga harus punya header yang muncul */
            .detail-table thead {
                display: table-header-group !important;
                visibility: visible !important;
            }

            .detail-table thead tr {
                display: table-row !important;
                visibility: visible !important;
            }

            .detail-table thead th {
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                display: table-cell !important;
                visibility: visible !important;
                opacity: 1 !important;
                border: 1px solid #000 !important;
                padding: 4px !important;
            }

            .detail-section {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Pastikan tidak ada page break di tengah row */
            tr {
                page-break-inside: avoid;
            }
        }

        .print-actions {
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            background: #f0f0f0;
        }

        .btn-print {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 12pt;
            cursor: pointer;
            border-radius: 4px;
        }

        .btn-print:hover {
            background: #0056b3;
        }

        @media print {
            .print-actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button class="btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Cetak
        </button>
    </div>

    <div class="print-header">
        <h3>REKAP ABSENSI KETERLAMBATAN</h3>
        <h4>
            @if($selectedDivisi)
                {{ $selectedDivisi->vcNamaDivisi }}
            @else
                Semua Divisi
            @endif
        </h4>
        <h5>
            @if($selectedDepartemen)
                {{ $selectedDepartemen->vcNamaDept }}
            @else
                Semua Departemen
            @endif
            @if($selectedBagian)
                , {{ $selectedBagian->vcNamaBagian }}
            @elseif(!$bagianId)
                , Semua Bagian
            @endif
        </h5>
        <h6>
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        </h6>
    </div>

    <div class="table-container">
        @if(count($rekapData) > 0)
        <table>
            <thead>
                <tr>
                    <th width="4%">No</th>
                    <th width="8%">NIK</th>
                    <th width="16%">Nama</th>
                    <th width="14%">Divisi</th>
                    <th width="16%">Departemen</th>
                    <th width="16%">Bagian</th>
                    <th width="8%">Jml Telat (hari)</th>
                    <th width="8%">Jml Menit Telat</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rekapData as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['nik'] }}</td>
                    <td class="text-left">{{ $row['nama'] }}</td>
                    <td class="text-left">{{ $row['divisi'] }}</td>
                    <td class="text-left">{{ $row['departemen'] }}</td>
                    <td class="text-left">{{ $row['bagian'] }}</td>
                    <td>{{ number_format($row['jumlah_telat'], 0, ',', '.') }}</td>
                    <td>{{ number_format($row['menit_telat'], 0, ',', '.') }}</td>
                </tr>
                @if(!empty($row['detail_telat']) && count($row['detail_telat']) > 0)
                <tr>
                    <td colspan="8" style="padding: 0; border: 0;">
                        <div class="detail-section">
                            <h6>Detail Tanggal Keterlambatan</h6>
                            <table class="detail-table">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="20%">Tanggal</th>
                                        <th width="15%">Shift Masuk</th>
                                        <th width="15%">Jam Masuk</th>
                                        <th width="15%">Menit Telat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($row['detail_telat'] as $detailIndex => $detail)
                                    <tr>
                                        <td>{{ $detailIndex + 1 }}</td>
                                        <td>{{ $detail['tanggal_formatted'] }}</td>
                                        <td>{{ $detail['shift_masuk'] }}</td>
                                        <td>{{ $detail['jam_masuk'] }}</td>
                                        <td>{{ number_format($detail['menit_telat'], 0, ',', '.') }} menit</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
        @else
        <div class="no-data">
            Tidak ada data keterlambatan untuk filter yang dipilih.
        </div>
        @endif
    </div>

    <script>
        // Auto print saat halaman dimuat (optional, bisa di-comment jika tidak diinginkan)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>

