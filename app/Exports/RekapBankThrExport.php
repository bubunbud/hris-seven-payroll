<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class RekapBankThrExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithCustomStartCell, WithEvents
{
    protected $closingThrs;
    protected $tanggalThr;
    protected $namaDivisi;
    protected $kodeDivisi;
    protected $summaryPerDivisi;

    public function __construct($closingThrs, $tanggalThr, $namaDivisi, $kodeDivisi, $summaryPerDivisi = [])
    {
        $this->closingThrs = $closingThrs;
        $this->tanggalThr = $tanggalThr;
        $this->namaDivisi = $namaDivisi;
        $this->kodeDivisi = $kodeDivisi;
        $this->summaryPerDivisi = $summaryPerDivisi;
    }

    public function collection()
    {
        $data = collect();
        $no = 1;
        $grandTotalThr = 0;

        foreach ($this->closingThrs as $ct) {
            $karyawan = $ct->karyawan;
            if (!$karyawan) continue;

            $tglLahir = $karyawan->TTL
                ? Carbon::parse($karyawan->TTL)->format('d/m/Y')
                : '';
            $unitBisnis = $ct->vcKodeDivisi ?? '';
            $nilaiThr = $ct->decNilaiTHR ?? 0;
            $grandTotalThr += $nilaiThr;

            $data->push([
                $no++,
                $ct->vcNik,
                $karyawan->Nama ?? '',
                $karyawan->Jenis_Kelamin ?? '',
                $tglLahir,
                'KTP',
                $karyawan->intNoBadge ?? '',
                $karyawan->intNorek ?? '',
                $unitBisnis,
                $nilaiThr,
                $nilaiThr, // Jumlah (sama dengan Nilai THR)
            ]);
        }

        $data->push([
            '',
            '',
            'GRAND TOTAL',
            '',
            '',
            '',
            '',
            '',
            '',
            $grandTotalThr,
            $grandTotalThr,
        ]);

        return $data;
    }

    public function headings(): array
    {
        return [
            'No',
            'NIK',
            'Nama',
            'Jenis Kelamin',
            'Tgl. Lahir',
            'Tipe ID',
            'No. KTP',
            'No. Rekening',
            'Unit Bisnis',
            'Nilai THR',
            'Jumlah',
        ];
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 10,  // NIK
            'C' => 22,  // Nama
            'D' => 12,  // Jenis Kelamin
            'E' => 12,  // Tgl. Lahir
            'F' => 8,   // Tipe ID
            'G' => 14,  // No. KTP
            'H' => 14,  // No. Rekening
            'I' => 12,  // Unit Bisnis
            'J' => 14,  // Nilai THR
            'K' => 14,  // Jumlah
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            5 => [
                'font' => ['bold' => true, 'size' => 10],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D3D3D3']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->setCellValue('A1', 'REKAP BANK THR');
                $sheet->mergeCells('A1:K1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->setCellValue('A2', 'Tanggal THR: ' . Carbon::parse($this->tanggalThr)->format('d F Y'));
                $sheet->mergeCells('A2:K2');
                $sheet->getStyle('A2')->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                if ($this->kodeDivisi && $this->kodeDivisi != 'SEMUA') {
                    $sheet->setCellValue('A3', 'Divisi: ' . $this->kodeDivisi . ' -> ' . $this->namaDivisi);
                } else {
                    $sheet->setCellValue('A3', 'Divisi: SEMUA DIVISI');
                }
                $sheet->mergeCells('A3:K3');
                $sheet->getStyle('A3')->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->setCellValue('A4', 'Group: Operator & Security');
                $sheet->mergeCells('A4:K4');
                $sheet->getStyle('A4')->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $headerRange = 'A5:K5';
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D3D3D3']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);

                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('J6:K' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('J6:K' . $highestRow)->getNumberFormat()->setFormatCode('#,##0');

                $sheet->getStyle('A' . $highestRow . ':K' . $highestRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'C0C0C0']
                    ],
                ]);

                $sheet->getStyle('A5:K' . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);

                // RINCIAN JUMLAH (hanya jika filter SEMUA)
                if (!empty($this->summaryPerDivisi)) {
                    $startSummaryRow = $highestRow + 3;
                    $sheet->setCellValue('A' . ($startSummaryRow - 1), 'RINCIAN JUMLAH');
                    $sheet->getStyle('A' . ($startSummaryRow - 1))->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12],
                    ]);

                    $sheet->setCellValue('A' . $startSummaryRow, 'Unit Bisnis / Divisi');
                    $sheet->setCellValue('B' . $startSummaryRow, 'Jumlah');
                    $sheet->setCellValue('C' . $startSummaryRow, 'Nilai THR');
                    $sheet->setCellValue('D' . $startSummaryRow, 'Jumlah');
                    $sheet->getStyle('A' . $startSummaryRow . ':D' . $startSummaryRow)->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'D3D3D3']
                        ],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);

                    $row = $startSummaryRow + 1;
                    $totalJumlahKaryawan = 0;
                    $totalNilaiThr = 0;
                    $totalJumlah = 0;
                    foreach ($this->summaryPerDivisi as $s) {
                        $sheet->setCellValue('A' . $row, $s['kode']);
                        $sheet->setCellValue('B' . $row, $s['jumlah_karyawan']);
                        $sheet->setCellValue('C' . $row, $s['nilai_thr']);
                        $sheet->setCellValue('D' . $row, $s['jumlah']);
                        $totalJumlahKaryawan += $s['jumlah_karyawan'];
                        $totalNilaiThr += $s['nilai_thr'];
                        $totalJumlah += $s['jumlah'];
                        $row++;
                    }
                    $sheet->setCellValue('A' . $row, 'TOTAL');
                    $sheet->setCellValue('B' . $row, $totalJumlahKaryawan);
                    $sheet->setCellValue('C' . $row, $totalNilaiThr);
                    $sheet->setCellValue('D' . $row, $totalJumlah);
                    $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'C0C0C0']
                        ],
                    ]);
                    $sheet->getStyle('C' . ($startSummaryRow + 1) . ':D' . $row)->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle('A' . $startSummaryRow . ':D' . $row)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                }
            },
        ];
    }
}
