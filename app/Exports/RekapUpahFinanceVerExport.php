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
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class RekapUpahFinanceVerExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithCustomStartCell, WithEvents
{
    protected $groupedData;
    protected $grandTotal;
    protected $tanggalPeriode;
    protected $namaDivisi;
    protected $kodeDivisi;

    public function __construct($groupedData, $grandTotal, $tanggalPeriode, $namaDivisi, $kodeDivisi)
    {
        $this->groupedData = $groupedData;
        $this->grandTotal = $grandTotal;
        $this->tanggalPeriode = $tanggalPeriode;
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

        foreach ($this->groupedData as $divisiKode => $divisiData) {
            foreach ($divisiData['departemens'] as $deptKode => $deptData) {
                // Header Departemen
                $deptRow = array_fill(0, 23, '');
                $deptRow[0] = 'Dept. ' . $deptData['nama'];
                $data->push($deptRow);
                
                foreach ($deptData['bagians'] as $bagianKode => $bagianData) {
                    if (count($bagianData['closings']) > 0) {
                        // Header Bagian
                        $bagianRow = array_fill(0, 23, '');
                        $bagianRow[0] = 'Bagia ' . $bagianData['nama'];
                        $data->push($bagianRow);
                        
                        // Data Karyawan
                        foreach ($bagianData['closings'] as $closing) {
                            $karyawan = $closing->karyawan;
                            if (!$karyawan) continue;

                            // Mapping field
                            $premi = $closing->decPremi ?? 0;
                            $gaji = $closing->decGapok ?? 0;
                            $selisihUpah = $closing->decRapel ?? 0;
                            // JM1, JM2, JM3 mengikuti definisi laporan:
                            // JM1 = jam ke-1 lembur hari kerja normal
                            // JM2 = jam ke-2 lembur hari kerja normal + jam ke-2 lembur hari libur
                            // JM3 = jam ke-3 lembur hari libur
                            $jm1 = round($closing->decJamLemburKerja1 ?? 0, 1);
                            $jm2 = round(($closing->decJamLemburKerja2 ?? 0) + ($closing->decJamLemburLibur2 ?? 0), 1);
                            $jm3 = round($closing->decJamLemburLibur3 ?? 0, 1);
                            $lembur = ($closing->decTotallembur1 ?? 0) + 
                                      ($closing->decTotallembur2 ?? 0) + 
                                      ($closing->decTotallembur3 ?? 0);
                            $uangMakanTransport = ($closing->decUangMakan ?? 0) + ($closing->decTransport ?? 0);
                            
                            // Gunakan decPotonganBPJS* karena field ini yang selalu terisi di database
                            $bpjsKes = $closing->decPotonganBPJSKes ?? $closing->decBpjsKesehatan ?? 0;
                            $bpjsNaker = $closing->decPotonganBPJSJHT ?? $closing->decBpjsNaker ?? 0;
                            $bpjsPensiun = $closing->decPotonganBPJSJP ?? $closing->decBpjsPensiun ?? 0;
                            $tdkHdrHc = ($closing->decPotonganAbsen ?? 0) + ($closing->decPotonganHC ?? 0);
                            $koperasi = $closing->decPotonganKoperasi ?? 0;
                            $potSpn = $closing->decIuranSPN ?? 0;
                            $potDplk = $closing->decPotonganBPR ?? 0;
                            $potLainLain = $closing->decPotonganLain ?? 0;

                            // Penerimaan = Premi + Gaji + Selisih Upah + Lembur + Tot Uang Makan & Transport
                            $penerimaan = $premi + $gaji + $selisihUpah + $lembur + $uangMakanTransport;

                            // TAKEHOMEPAY = Penerimaan - (semua potongan)
                            $takehomepay = $penerimaan - ($bpjsKes + $bpjsNaker + $bpjsPensiun + $tdkHdrHc + $koperasi + $potSpn + $potDplk + $potLainLain);

                            $data->push([
                                $no++,
                                $closing->vcNik,
                                $karyawan->Nama ?? '',
                                $closing->vcKodeGolongan ?? '',
                                $premi,
                                $gaji,
                                0, // TSM
                                $jm1,
                                $jm2,
                                $jm3,
                                $selisihUpah,
                                $lembur,
                                $uangMakanTransport,
                                $bpjsKes,
                                $bpjsNaker,
                                $bpjsPensiun,
                                $tdkHdrHc,
                                $koperasi,
                                $potSpn,
                                $potDplk,
                                $potLainLain,
                                $penerimaan,
                                $takehomepay
                            ]);
                        }

                        // Total Bagian
                        $bagianTotal = $bagianData['total'];
                        $data->push([
                            '',
                            '',
                            'Total Bag. ' . $bagianData['nama'],
                            '',
                            $bagianTotal['premi'],
                            $bagianTotal['gaji'],
                            0,
                            $bagianTotal['jm1'],
                            $bagianTotal['jm2'],
                            $bagianTotal['jm3'],
                            $bagianTotal['selisih_upah'],
                            $bagianTotal['lembur'],
                            $bagianTotal['uang_makan_transport'],
                            $bagianTotal['bpjs_kes'],
                            $bagianTotal['bpjs_naker'],
                            $bagianTotal['bpjs_pensiun'],
                            $bagianTotal['tdk_hdr_hc'],
                            $bagianTotal['koperasi'],
                            $bagianTotal['pot_spn'],
                            $bagianTotal['pot_dplk'],
                            $bagianTotal['pot_lain_lain'],
                            $bagianTotal['penerimaan'],
                            $bagianTotal['takehomepay']
                        ]);
                    }
                }

                // Total Departemen
                $deptTotal = $deptData['total'];
                $data->push([
                    '',
                    '',
                    'Total Dept. ' . $deptData['nama'],
                    '',
                    $deptTotal['premi'],
                    $deptTotal['gaji'],
                    0,
                    $deptTotal['jm1'],
                    $deptTotal['jm2'],
                    $deptTotal['jm3'],
                    $deptTotal['selisih_upah'],
                    $deptTotal['lembur'],
                    $deptTotal['uang_makan_transport'],
                    $deptTotal['bpjs_kes'],
                    $deptTotal['bpjs_naker'],
                    $deptTotal['bpjs_pensiun'],
                    $deptTotal['tdk_hdr_hc'],
                    $deptTotal['koperasi'],
                    $deptTotal['pot_spn'],
                    $deptTotal['pot_dplk'],
                    $deptTotal['pot_lain_lain'],
                    $deptTotal['penerimaan'],
                    $deptTotal['takehomepay']
                ]);
            }
        }

        // Grand Total
        $data->push([
            '',
            '',
            'GRAND TOTAL',
            '',
            $this->grandTotal['premi'],
            $this->grandTotal['gaji'],
            0,
            $this->grandTotal['jm1'],
            $this->grandTotal['jm2'],
            $this->grandTotal['jm3'],
            $this->grandTotal['selisih_upah'],
            $this->grandTotal['lembur'],
            $this->grandTotal['uang_makan_transport'],
            $this->grandTotal['bpjs_kes'],
            $this->grandTotal['bpjs_naker'],
            $this->grandTotal['bpjs_pensiun'],
            $this->grandTotal['tdk_hdr_hc'],
            $this->grandTotal['koperasi'],
            $this->grandTotal['pot_spn'],
            $this->grandTotal['pot_dplk'],
            $this->grandTotal['pot_lain_lain'],
            $this->grandTotal['penerimaan'],
            $this->grandTotal['takehomepay']
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
            'NAMA',
            'GOL',
            'PREMI',
            'GAJI',
            'TSM',
            'JM1',
            'JM2',
            'JM3',
            'SELISIH UPAH',
            'LEMBUR',
            'Uang Makan + Transport',
            'BPJS KES',
            'BPJS NAKER',
            'BPJS PENSIUN',
            'TDK HDR/HC',
            'KOPERASI',
            'POT. SPN',
            'POT. DPLK',
            'POT. LAIN-LAIN',
            'PENERIMAAN',
            'TAKEHOMEPAY'
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
            'A' => 6,   // No
            'B' => 10,  // NIK
            'C' => 25,  // NAMA
            'D' => 6,   // GOL
            'E' => 12,  // PREMI
            'F' => 12,  // GAJI
            'G' => 8,   // TSM
            'H' => 8,   // JM1
            'I' => 8,   // JM2
            'J' => 8,   // JM3
            'K' => 12,  // SELISIH UPAH
            'L' => 12,  // LEMBUR
            'M' => 18,  // Uang Makan + Transport
            'N' => 12,  // BPJS KES
            'O' => 12,  // BPJS NAKER
            'P' => 12,  // BPJS PENSIUN
            'Q' => 12,  // TDK HDR/HC
            'R' => 12,  // KOPERASI
            'S' => 12,  // POT. SPN
            'T' => 12,  // POT. DPLK
            'U' => 15,  // POT. LAIN-LAIN
            'V' => 12,  // PENERIMAAN
            'W' => 12,  // TAKEHOMEPAY
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
                'font' => ['bold' => true, 'size' => 11],
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
                $sheet->setCellValue('A1', 'REKAPITULASI UPAH KARYAWAN');
                $sheet->mergeCells('A1:W1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                if ($this->kodeDivisi && $this->kodeDivisi != 'SEMUA') {
                    $sheet->setCellValue('A2', $this->kodeDivisi . ' -> ' . $this->namaDivisi);
                } else {
                    $sheet->setCellValue('A2', 'SEMUA DIVISI');
                }
                $sheet->mergeCells('A2:W2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->setCellValue('A3', 'Periode: ' . Carbon::parse($this->tanggalPeriode)->format('d F Y'));
                $sheet->mergeCells('A3:W3');
                $sheet->getStyle('A3')->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Style untuk header kolom (row 5)
                $headerRange = 'A5:W5';
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
                $dataRange = 'A5:W' . $highestRow;
                
                // Set alignment untuk kolom numerik (right align)
                // Format khusus untuk JM1, JM2, JM3 (1 desimal)
                $jmColumns = ['H', 'I', 'J']; // JM1, JM2, JM3
                foreach ($jmColumns as $col) {
                    $sheet->getStyle($col . '6:' . $col . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle($col . '6:' . $col . $highestRow)->getNumberFormat()->setFormatCode('#,##0.0');
                }
                
                // Format untuk kolom numerik lainnya (2 desimal)
                $numericColumns = ['E', 'F', 'G', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W'];
                foreach ($numericColumns as $col) {
                    $sheet->getStyle($col . '6:' . $col . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle($col . '6:' . $col . $highestRow)->getNumberFormat()->setFormatCode('#,##0.00');
                }

                // Style untuk kolom text (left align)
                $textColumns = ['A', 'B', 'C', 'D'];
                foreach ($textColumns as $col) {
                    $sheet->getStyle($col . '6:' . $col . $highestRow)->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    ]);
                }

                // Style untuk row header Dept, Bagian, dan Total
                // Loop melalui semua row dan cek isinya untuk apply styling
                $startDataRow = 6; // Data mulai dari row 6
                for ($row = $startDataRow; $row <= $highestRow; $row++) {
                    $cellC = $sheet->getCell('C' . $row)->getValue();
                    
                    if (is_string($cellC)) {
                        // Header Departemen
                        if (strpos($cellC, 'Dept.') === 0) {
                            $sheet->getStyle('A' . $row . ':W' . $row)->applyFromArray([
                                'font' => ['bold' => true],
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'D0D0D0']
                                ],
                            ]);
                            $sheet->mergeCells('A' . $row . ':W' . $row);
                        }
                        // Header Bagian
                        elseif (strpos($cellC, 'Bagia') === 0) {
                            $sheet->getStyle('A' . $row . ':W' . $row)->applyFromArray([
                                'font' => ['bold' => true],
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'E0E0E0']
                                ],
                            ]);
                            $sheet->mergeCells('A' . $row . ':W' . $row);
                        }
                        // Total Bagian
                        elseif (strpos($cellC, 'Total Bag.') === 0) {
                            $sheet->getStyle('A' . $row . ':W' . $row)->applyFromArray([
                                'font' => ['bold' => true],
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'F0F0F0']
                                ],
                            ]);
                        }
                        // Total Departemen
                        elseif (strpos($cellC, 'Total Dept.') === 0) {
                            $sheet->getStyle('A' . $row . ':W' . $row)->applyFromArray([
                                'font' => ['bold' => true],
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'E8E8E8']
                                ],
                            ]);
                        }
                    }
                }
                
                // Style untuk Grand Total (last row)
                $sheet->getStyle('A' . $highestRow . ':W' . $highestRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'C0C0C0']
                    ],
                ]);

                // Add borders to all cells
                $sheet->getStyle('A5:W' . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Auto-fit column widths (optional, bisa di-comment jika ingin manual)
                // foreach (range('A', 'W') as $col) {
                //     $sheet->getColumnDimension($col)->setAutoSize(true);
                // }
            },
        ];
    }
}

