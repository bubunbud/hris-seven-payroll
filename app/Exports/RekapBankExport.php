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

class RekapBankExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithCustomStartCell, WithEvents
{
    protected $closings;
    protected $tanggalAwal;
    protected $tanggalAkhir;
    protected $namaDivisi;
    protected $kodeDivisi;

    public function __construct($closings, $tanggalAwal, $tanggalAkhir, $namaDivisi, $kodeDivisi)
    {
        $this->closings = $closings;
        $this->tanggalAwal = $tanggalAwal;
        $this->tanggalAkhir = $tanggalAkhir;
        $this->namaDivisi = $namaDivisi;
        $this->kodeDivisi = $kodeDivisi;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = collect();
        $no = 1;

        foreach ($this->closings as $closing) {
            $karyawan = $closing->karyawan;
            if (!$karyawan) continue;

            // Hitung Gaji + Lembur
            $gajiLembur = ($closing->decGapok ?? 0) +
                ($closing->decUangMakan ?? 0) +
                ($closing->decTransport ?? 0) +
                ($closing->decPremi ?? 0) +
                (($closing->decTotallembur1 ?? 0) + ($closing->decTotallembur2 ?? 0) + ($closing->decTotallembur3 ?? 0)) +
                ($closing->decRapel ?? 0);

            // Hitung Pot Lain-lain = decPotonganLain + decPotonganAbsen + decPotonganHC
            $potLainLain = ($closing->decPotonganLain ?? 0) +
                ($closing->decPotonganAbsen ?? 0) +
                ($closing->decPotonganHC ?? 0);

            // Hitung total potongan
            $totalPotongan = ($closing->decPotonganBPJSKes ?? 0) +
                ($closing->decPotonganBPJSJHT ?? 0) +
                ($closing->decPotonganBPJSJP ?? 0) +
                ($closing->decIuranSPN ?? 0) +
                $potLainLain +
                ($closing->decPotonganKoperasi ?? 0) +
                ($closing->decPotonganBPR ?? 0);

            // Jumlah = Gaji + Lembur - Total Potongan
            $jumlah = $gajiLembur - $totalPotongan;

            // Format tanggal lahir
            $tglLahir = $karyawan->TTL ? Carbon::parse($karyawan->TTL)->format('d/m/Y') : '';

            // CIF dan Unit Bisnis dari divisi
            $cif = $closing->vcKodeDivisi ?? '';
            $unitBisnis = $closing->vcKodeDivisi ?? '';

            $data->push([
                $no++,
                $closing->vcNik,
                $karyawan->Nama ?? '',
                $karyawan->Jenis_Kelamin ?? '',
                $tglLahir,
                'KTP',
                $karyawan->intNoBadge ?? '',
                $karyawan->intNorek ?? '',
                $cif,
                $unitBisnis,
                $gajiLembur,
                $closing->decBebanTgi ?? 0,
                $closing->decBebanSiaExp ?? 0,
                $closing->decBebanSiaProd ?? 0,
                $closing->decBebanRma ?? 0,
                $closing->decBebanSmu ?? 0,
                $closing->decBebanAbnJkt ?? 0,
                $closing->decPotonganBPJSKes ?? 0,
                $closing->decPotonganBPJSJHT ?? 0,
                $closing->decPotonganBPJSJP ?? 0,
                $closing->decIuranSPN ?? 0,
                $potLainLain,
                $closing->decPotonganKoperasi ?? 0,
                $closing->decPotonganBPR ?? 0,
                $jumlah
            ]);
        }

        // Grand Total
        $grandTotalGajiLembur = 0;
        $grandTotalBebanTgi = 0;
        $grandTotalSiaExp = 0;
        $grandTotalSiaProd = 0;
        $grandTotalBebanRma = 0;
        $grandTotalBebanSmu = 0;
        $grandTotalAbnJkt = 0;
        $grandTotalBpjsKes = 0;
        $grandTotalBpjsNaker = 0;
        $grandTotalBpjsPen = 0;
        $grandTotalIuranSpn = 0;
        $grandTotalPotLain = 0;
        $grandTotalPotKoperasi = 0;
        $grandTotalDplk = 0;
        $grandTotalJumlah = 0;

        foreach ($this->closings as $closing) {
            $karyawan = $closing->karyawan;
            if (!$karyawan) continue;

            $gajiLembur = ($closing->decGapok ?? 0) +
                ($closing->decUangMakan ?? 0) +
                ($closing->decTransport ?? 0) +
                ($closing->decPremi ?? 0) +
                (($closing->decTotallembur1 ?? 0) + ($closing->decTotallembur2 ?? 0) + ($closing->decTotallembur3 ?? 0)) +
                ($closing->decRapel ?? 0);

            $potLainLain = ($closing->decPotonganLain ?? 0) +
                ($closing->decPotonganAbsen ?? 0) +
                ($closing->decPotonganHC ?? 0);

            $totalPotongan = ($closing->decPotonganBPJSKes ?? 0) +
                ($closing->decPotonganBPJSJHT ?? 0) +
                ($closing->decPotonganBPJSJP ?? 0) +
                ($closing->decIuranSPN ?? 0) +
                $potLainLain +
                ($closing->decPotonganKoperasi ?? 0) +
                ($closing->decPotonganBPR ?? 0);

            $jumlah = $gajiLembur - $totalPotongan;

            $grandTotalGajiLembur += $gajiLembur;
            $grandTotalBebanTgi += ($closing->decBebanTgi ?? 0);
            $grandTotalSiaExp += ($closing->decBebanSiaExp ?? 0);
            $grandTotalSiaProd += ($closing->decBebanSiaProd ?? 0);
            $grandTotalBebanRma += ($closing->decBebanRma ?? 0);
            $grandTotalBebanSmu += ($closing->decBebanSmu ?? 0);
            $grandTotalAbnJkt += ($closing->decBebanAbnJkt ?? 0);
            $grandTotalBpjsKes += ($closing->decPotonganBPJSKes ?? 0);
            $grandTotalBpjsNaker += ($closing->decPotonganBPJSJHT ?? 0);
            $grandTotalBpjsPen += ($closing->decPotonganBPJSJP ?? 0);
            $grandTotalIuranSpn += ($closing->decIuranSPN ?? 0);
            $grandTotalPotLain += $potLainLain;
            $grandTotalPotKoperasi += ($closing->decPotonganKoperasi ?? 0);
            $grandTotalDplk += ($closing->decPotonganBPR ?? 0);
            $grandTotalJumlah += $jumlah;
        }

        // Add Grand Total row
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
            '',
            $grandTotalGajiLembur,
            $grandTotalBebanTgi,
            $grandTotalSiaExp,
            $grandTotalSiaProd,
            $grandTotalBebanRma,
            $grandTotalBebanSmu,
            $grandTotalAbnJkt,
            $grandTotalBpjsKes,
            $grandTotalBpjsNaker,
            $grandTotalBpjsPen,
            $grandTotalIuranSpn,
            $grandTotalPotLain,
            $grandTotalPotKoperasi,
            $grandTotalDplk,
            $grandTotalJumlah
        ]);

        return $data;
    }

    /**
     * @return array
     */
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
            'CIF',
            'Unit Bisnis',
            'Gaji + Lembur',
            'Beban TGI',
            'SIA-EXP',
            'SIA-Prod',
            'Beban RMA',
            'Beban SMU',
            'ABN JKT',
            'BPJS Kes',
            'BPJS Naker',
            'BPJS Pen',
            'Iuran SPN',
            'Pot Lain-lain',
            'Pot. Koperasi',
            'DPLK/CAR',
            'Jumlah'
        ];
    }

    /**
     * @return string
     */
    public function startCell(): string
    {
        return 'A5'; // Start dari row 5 untuk header info di atas
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 10,  // NIK
            'C' => 20,  // Nama
            'D' => 12,  // Jenis Kelamin
            'E' => 12,  // Tgl. Lahir
            'F' => 8,   // Tipe ID
            'G' => 12,  // No. KTP
            'H' => 12,  // No. Rekening
            'I' => 8,   // CIF
            'J' => 12,  // Unit Bisnis
            'K' => 12,  // Gaji + Lembur
            'L' => 10,  // Beban TGI
            'M' => 10,  // SIA-EXP
            'N' => 10,  // SIA-Prod
            'O' => 10,  // Beban RMA
            'P' => 10,  // Beban SMU
            'Q' => 10,  // ABN JKT
            'R' => 10,  // BPJS Kes
            'S' => 10,  // BPJS Naker
            'T' => 10,  // BPJS Pen
            'U' => 10,  // Iuran SPN
            'V' => 12,  // Pot Lain-lain
            'W' => 12,  // Pot. Koperasi
            'X' => 10,  // DPLK/CAR
            'Y' => 12,  // Jumlah
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Header row (row 5)
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

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Set header info di atas (row 1-4)
                $sheet->setCellValue('A1', 'REKAP BANK');
                $sheet->mergeCells('A1:Y1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->setCellValue('A2', 'Periode: ' . Carbon::parse($this->tanggalAwal)->format('d F Y'));
                $sheet->mergeCells('A2:Y2');
                $sheet->getStyle('A2')->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                if ($this->kodeDivisi && $this->kodeDivisi != 'SEMUA') {
                    $sheet->setCellValue('A3', 'Divisi: ' . $this->kodeDivisi . ' -> ' . $this->namaDivisi);
                } else {
                    $sheet->setCellValue('A3', 'Divisi: SEMUA DIVISI');
                }
                $sheet->mergeCells('A3:Y3');
                $sheet->getStyle('A3')->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Style untuk header kolom (row 5)
                $headerRange = 'A5:Y5';
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
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Style untuk data rows
                $highestRow = $sheet->getHighestRow();
                
                // Set alignment untuk kolom numerik (right align)
                $numericColumns = ['K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y'];
                foreach ($numericColumns as $col) {
                    $sheet->getStyle($col . '6:' . $col . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle($col . '6:' . $col . $highestRow)->getNumberFormat()->setFormatCode('#,##0');
                }

                // Style untuk kolom text (left align)
                $textColumns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
                foreach ($textColumns as $col) {
                    $sheet->getStyle($col . '6:' . $col . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Style untuk Grand Total (last row)
                $sheet->getStyle('A' . $highestRow . ':Y' . $highestRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'C0C0C0']
                    ],
                ]);

                // Add borders to all cells
                $sheet->getStyle('A5:Y' . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
            },
        ];
    }
}

