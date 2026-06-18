<?php

namespace App\Http\Controllers;

use App\Models\BiayaPerjalananDinasHeader;
use App\Models\BiayaPerjalananDinasDetail;
use App\Models\PerjalananDinasHeader;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BiayaPerjalananDinasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $startDate = $request->get('dari_tanggal');
        $endDate = $request->get('sampai_tanggal');
        $search = $request->get('search'); // No BPD / No RPD
        
        // Query untuk data header
        $query = BiayaPerjalananDinasHeader::with(['perjalananDinas', 'details'])
            ->orderBy('dtCreate', 'desc');

        // Filter tanggal jika ada input dari user
        if ($startDate && $endDate) {
            $query->whereBetween('dtCreate', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        // Apply search filter
        if ($search) {
            $searchTerms = preg_split('/,\s*/', trim($search));
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    if (!empty(trim($term))) {
                        $term = trim($term);
                        $q->orWhere('vcNoBpd', 'like', '%' . $term . '%')
                            ->orWhere('vcNoRpd', 'like', '%' . $term . '%')
                            ->orWhereHas('perjalananDinas', function ($q2) use ($term) {
                                $q2->where('vcPemberiTugas', 'like', '%' . $term . '%')
                                    ->orWhere('vcTujuanDinas', 'like', '%' . $term . '%');
                            });
                    }
                }
            });
        }

        $records = $query->paginate(25);

        // Set default untuk form jika belum diisi
        if (!$startDate) {
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if (!$endDate) {
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        // Load RPD untuk dropdown (saat add mode)
        // Exclude RPD yang sudah punya BPD dengan status lengkap (complete)
        $rpdSudahLengkap = BiayaPerjalananDinasHeader::where('vcStatus', 'complete')
            ->pluck('vcNoRpd');
        $rpdList = PerjalananDinasHeader::orderBy('dtCreate', 'desc')
            ->whereNotIn('vcNoRpd', $rpdSudahLengkap)
            ->get(['vcNoRpd', 'vcPemberiTugas', 'vcTujuanDinas', 'dtTanggalDinasDari', 'dtTanggalDinasSampai']);

        return view('biaya-perjalanan-dinas.index', compact('records', 'startDate', 'endDate', 'search', 'rpdList'));
    }

    /**
     * Get RPD data untuk auto-fill
     */
    public function getRpdData($noRpd)
    {
        $rpd = PerjalananDinasHeader::with(['karyawans'])->find($noRpd);
        
        if (!$rpd) {
            return response()->json([
                'success' => false,
                'message' => 'RPD tidak ditemukan',
            ], 404);
        }

        // Ambil karyawan pertama untuk auto-fill "Melaporkan"
        $karyawanPertama = $rpd->karyawans->first();
        $melaporkan = $karyawanPertama ? $karyawanPertama->vcNamaKaryawan : '';

        return response()->json([
            'success' => true,
            'data' => [
                'vcNoRpd' => $rpd->vcNoRpd,
                'vcPemberiTugas' => $rpd->vcPemberiTugas,
                'vcMenyetujui' => $rpd->vcMenyetujui ?? $rpd->vcPemberiTugas,
                'vcMelaporkan' => $melaporkan,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * Jika is_draft=1: hanya validasi Bagian 1 (Informasi Umum) dan Bagian 2 (Kasbon).
     */
    public function store(Request $request)
    {
        $isDraft = (bool) ($request->input('is_draft', 0));

        $rules = [
            'vcNoRpd' => 'required|string|max:50|exists:t_perjalanan_dinas_header,vcNoRpd',
            'vcPemberiTugas' => 'required|string|max:100',
            'decKasbonNilai' => 'nullable|numeric|min:0',
            'vcKasbonTerbilang' => 'nullable|string|max:500',
            'vcLaporanSingkat' => 'nullable|string',
            'vcMelaporkan' => 'nullable|string|max:100',
            'vcMenyetujui' => 'nullable|string|max:100',
            'vcMengetahuiHrd' => 'nullable|string|max:100',
            'vcMengetahuiFinance' => 'nullable|string|max:100',
        ];

        if (!$isDraft) {
            $rules['details'] = 'required|array|min:1';
            $rules['details.*.vcKategoriBiaya'] = 'required|string|max:50';
            $rules['details.*.decNilai'] = 'nullable|numeric|min:0';
            $rules['details.*.decTotal'] = 'nullable|numeric|min:0';
        } else {
            $rules['details'] = 'nullable|array';
            $rules['details.*.vcKategoriBiaya'] = 'nullable|string|max:50';
            $rules['details.*.decNilai'] = 'nullable|numeric|min:0';
            $rules['details.*.decTotal'] = 'nullable|numeric|min:0';
        }

        $request->validate($rules);

        DB::beginTransaction();
        try {
            // Generate No BPD
            $noBpd = $this->generateNoBpd();

            // Calculate total pengeluaran
            $details = $request->details ?? [];
            $totalPengeluaran = 0;
            foreach ($details as $detail) {
                $total = $detail['decTotal'] ?? $detail['decNilai'] ?? 0;
                $totalPengeluaran += $total;
            }

            // Calculate kekurangan/kelebihan
            $kasbonNilai = $request->decKasbonNilai ?? 0;
            $kekuranganKelebihan = $totalPengeluaran - $kasbonNilai;

            $status = $isDraft ? 'draft' : 'complete';

            // Create Header
            $header = BiayaPerjalananDinasHeader::create([
                'vcNoBpd' => $noBpd,
                'vcNoRpd' => $request->vcNoRpd,
                'vcPemberiTugas' => substr($request->vcPemberiTugas, 0, 100),
                'decKasbonNilai' => $kasbonNilai,
                'vcKasbonTerbilang' => $request->vcKasbonTerbilang ? substr($request->vcKasbonTerbilang, 0, 500) : null,
                'decTotalPengeluaran' => $totalPengeluaran,
                'decKekuranganKelebihan' => $kekuranganKelebihan,
                'vcLaporanSingkat' => $request->vcLaporanSingkat,
                'vcMelaporkan' => $request->vcMelaporkan ? substr($request->vcMelaporkan, 0, 100) : null,
                'vcMenyetujui' => $request->vcMenyetujui ? substr($request->vcMenyetujui, 0, 100) : null,
                'vcMengetahuiHrd' => $request->vcMengetahuiHrd ? substr($request->vcMengetahuiHrd, 0, 100) : null,
                'vcMengetahuiFinance' => $request->vcMengetahuiFinance ? substr($request->vcMengetahuiFinance, 0, 100) : null,
                'vcStatus' => $status,
                'dtCreate' => Carbon::now(),
                'dtChange' => Carbon::now(),
            ]);

            // Create Details (boleh kosong untuk draft)
            $detailIndex = 0;
            foreach ($details as $detail) {
                $kategori = trim($detail['vcKategoriBiaya'] ?? '');
                if (empty($kategori)) {
                    continue;
                }
                $detailIndex++;
                $counterDetail = $this->generateCounter('detail', $noBpd, $detailIndex);
                
                $nilai = $detail['decNilai'] ?? 0;
                $total = $detail['decTotal'] ?? $nilai;

                BiayaPerjalananDinasDetail::create([
                    'vcCounterDetail' => $counterDetail,
                    'vcNoBpd' => $noBpd,
                    'vcKategoriBiaya' => substr($detail['vcKategoriBiaya'], 0, 50),
                    'vcSubKategori' => $detail['vcSubKategori'] ? substr($detail['vcSubKategori'], 0, 100) : null,
                    'dtTanggalDari' => $detail['dtTanggalDari'] ?? null,
                    'dtTanggalSampai' => $detail['dtTanggalSampai'] ?? null,
                    'decNilai' => $nilai,
                    'decTotal' => $total,
                    'vcKeterangan' => $detail['vcKeterangan'] ?? null,
                    'dtCreate' => Carbon::now(),
                    'dtChange' => Carbon::now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Form BPD berhasil disimpan',
                'data' => $header->load(['perjalananDinas', 'details']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing BPD: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan Form BPD: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $header = BiayaPerjalananDinasHeader::with([
            'perjalananDinas',
            'details'
        ])
            ->findOrFail($id);

        // Format dates untuk input type="date" (Y-m-d)
        $data = $header->toArray();
        
        // Format detail dates
        if ($header->details && $header->details->count() > 0) {
            $detailsArray = [];
            foreach ($header->details as $detail) {
                $detailData = [
                    'vcCounterDetail' => $detail->vcCounterDetail,
                    'vcKategoriBiaya' => $detail->vcKategoriBiaya,
                    'vcSubKategori' => $detail->vcSubKategori,
                    'dtTanggalDari' => $detail->dtTanggalDari ? $detail->dtTanggalDari->format('Y-m-d') : '',
                    'dtTanggalSampai' => $detail->dtTanggalSampai ? $detail->dtTanggalSampai->format('Y-m-d') : '',
                    'decNilai' => $detail->decNilai,
                    'decTotal' => $detail->decTotal,
                    'vcKeterangan' => $detail->vcKeterangan,
                ];
                $detailsArray[] = $detailData;
            }
            $data['details'] = $detailsArray;
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Jika is_draft=1: hanya validasi Bagian 1 dan 2, details boleh kosong.
     */
    public function update(Request $request, $id)
    {
        $isDraft = (bool) ($request->input('is_draft', 0));

        $rules = [
            'vcNoRpd' => 'required|string|max:50|exists:t_perjalanan_dinas_header,vcNoRpd',
            'vcPemberiTugas' => 'required|string|max:100',
            'decKasbonNilai' => 'nullable|numeric|min:0',
            'vcKasbonTerbilang' => 'nullable|string|max:500',
            'vcLaporanSingkat' => 'nullable|string',
            'vcMelaporkan' => 'nullable|string|max:100',
            'vcMenyetujui' => 'nullable|string|max:100',
            'vcMengetahuiHrd' => 'nullable|string|max:100',
            'vcMengetahuiFinance' => 'nullable|string|max:100',
        ];

        if (!$isDraft) {
            $rules['details'] = 'required|array|min:1';
            $rules['details.*.vcKategoriBiaya'] = 'required|string|max:50';
            $rules['details.*.decNilai'] = 'nullable|numeric|min:0';
            $rules['details.*.decTotal'] = 'nullable|numeric|min:0';
        } else {
            $rules['details'] = 'nullable|array';
            $rules['details.*.vcKategoriBiaya'] = 'nullable|string|max:50';
            $rules['details.*.decNilai'] = 'nullable|numeric|min:0';
            $rules['details.*.decTotal'] = 'nullable|numeric|min:0';
        }

        $request->validate($rules);

        DB::beginTransaction();
        try {
            $header = BiayaPerjalananDinasHeader::findOrFail($id);

            $details = $request->details ?? [];

            // Calculate total pengeluaran
            $totalPengeluaran = 0;
            foreach ($details as $detail) {
                $total = $detail['decTotal'] ?? $detail['decNilai'] ?? 0;
                $totalPengeluaran += $total;
            }

            // Calculate kekurangan/kelebihan
            $kasbonNilai = $request->decKasbonNilai ?? 0;
            $kekuranganKelebihan = $totalPengeluaran - $kasbonNilai;

            $status = $isDraft ? 'draft' : 'complete';

            // Update Header
            $header->update([
                'vcNoRpd' => $request->vcNoRpd,
                'vcPemberiTugas' => substr($request->vcPemberiTugas, 0, 100),
                'decKasbonNilai' => $kasbonNilai,
                'vcKasbonTerbilang' => $request->vcKasbonTerbilang ? substr($request->vcKasbonTerbilang, 0, 500) : null,
                'decTotalPengeluaran' => $totalPengeluaran,
                'decKekuranganKelebihan' => $kekuranganKelebihan,
                'vcLaporanSingkat' => $request->vcLaporanSingkat,
                'vcMelaporkan' => $request->vcMelaporkan ? substr($request->vcMelaporkan, 0, 100) : null,
                'vcMenyetujui' => $request->vcMenyetujui ? substr($request->vcMenyetujui, 0, 100) : null,
                'vcMengetahuiHrd' => $request->vcMengetahuiHrd ? substr($request->vcMengetahuiHrd, 0, 100) : null,
                'vcMengetahuiFinance' => $request->vcMengetahuiFinance ? substr($request->vcMengetahuiFinance, 0, 100) : null,
                'vcStatus' => $status,
                'dtChange' => Carbon::now(),
            ]);

            // Delete existing details
            BiayaPerjalananDinasDetail::where('vcNoBpd', $header->vcNoBpd)->delete();

            // Recreate Details (boleh kosong untuk draft)
            $detailIndex = 0;
            foreach ($details as $detail) {
                $kategori = trim($detail['vcKategoriBiaya'] ?? '');
                if (empty($kategori)) {
                    continue;
                }
                $detailIndex++;
                $counterDetail = $this->generateCounter('detail', $header->vcNoBpd, $detailIndex);
                
                $nilai = $detail['decNilai'] ?? 0;
                $total = $detail['decTotal'] ?? $nilai;

                BiayaPerjalananDinasDetail::create([
                    'vcCounterDetail' => $counterDetail,
                    'vcNoBpd' => $header->vcNoBpd,
                    'vcKategoriBiaya' => substr($detail['vcKategoriBiaya'], 0, 50),
                    'vcSubKategori' => $detail['vcSubKategori'] ? substr($detail['vcSubKategori'], 0, 100) : null,
                    'dtTanggalDari' => $detail['dtTanggalDari'] ?? null,
                    'dtTanggalSampai' => $detail['dtTanggalSampai'] ?? null,
                    'decNilai' => $nilai,
                    'decTotal' => $total,
                    'vcKeterangan' => $detail['vcKeterangan'] ?? null,
                    'dtCreate' => Carbon::now(),
                    'dtChange' => Carbon::now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Form BPD berhasil diupdate',
                'data' => $header->load(['perjalananDinas', 'details']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating BPD: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate Form BPD: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $header = BiayaPerjalananDinasHeader::findOrFail($id);
            
            // Delete all related records (cascade)
            BiayaPerjalananDinasDetail::where('vcNoBpd', $header->vcNoBpd)->delete();
            
            $header->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Form BPD berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting BPD: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus Form BPD: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print Form BPD
     */
    public function print($id)
    {
        $header = BiayaPerjalananDinasHeader::with([
            'perjalananDinas.karyawans',
            'details'
        ])->findOrFail($id);

        return view('biaya-perjalanan-dinas.print', compact('header'));
    }

    /**
     * Convert number to terbilang (API endpoint)
     */
    public function convertTerbilang($number)
    {
        try {
            $terbilang = $this->convertToTerbilang($number);
            return response()->json([
                'success' => true,
                'terbilang' => $terbilang,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengkonversi terbilang: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate No BPD
     */
    private function generateNoBpd()
    {
        $prefix = 'BPD';
        $datePart = Carbon::now()->format('Ymd');
        
        // Cari counter terakhir dengan prefix dan tanggal yang sama
        $lastBpd = BiayaPerjalananDinasHeader::where('vcNoBpd', 'like', $prefix . $datePart . '%')
            ->orderBy('vcNoBpd', 'desc')
            ->value('vcNoBpd');

        $counter = 1;
        if ($lastBpd) {
            $counterStr = substr($lastBpd, strlen($prefix . $datePart));
            $lastCounter = (int) $counterStr;
            if ($lastCounter > 0) {
                $counter = $lastCounter + 1;
            }
        }

        $counterFormatted = str_pad($counter, 3, '0', STR_PAD_LEFT);
        $noBpd = $prefix . $datePart . $counterFormatted;

        // Pastikan tidak ada duplikat
        if (BiayaPerjalananDinasHeader::where('vcNoBpd', $noBpd)->exists()) {
            $counter++;
            $counterFormatted = str_pad($counter, 3, '0', STR_PAD_LEFT);
            $noBpd = $prefix . $datePart . $counterFormatted;
        }

        return $noBpd;
    }

    /**
     * Generate Counter untuk detail
     */
    private function generateCounter($type, $noBpd, $index)
    {
        $prefix = strtoupper(substr($type, 0, 1));
        $datePart = Carbon::now()->format('Ymd');
        $indexPart = str_pad($index, 3, '0', STR_PAD_LEFT);
        
        return $prefix . $noBpd . $datePart . $indexPart;
    }

    /**
     * Convert number to terbilang (Indonesian)
     */
    public function convertToTerbilang($number)
    {
        $number = (float) $number;
        $terbilang = '';
        
        $satuan = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan'];
        $belasan = ['sepuluh', 'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas', 'enam belas', 'tujuh belas', 'delapan belas', 'sembilan belas'];
        $puluhan = ['', '', 'dua puluh', 'tiga puluh', 'empat puluh', 'lima puluh', 'enam puluh', 'tujuh puluh', 'delapan puluh', 'sembilan puluh'];
        
        if ($number == 0) {
            return 'nol';
        }
        
        // Handle rupiah (integer part)
        $rupiah = floor($number);
        $sen = round(($number - $rupiah) * 100);
        
        // Convert rupiah
        if ($rupiah >= 1000000) {
            $juta = floor($rupiah / 1000000);
            $terbilang .= $this->convertHundreds($juta, $satuan, $belasan, $puluhan) . ' juta ';
            $rupiah = $rupiah % 1000000;
        }
        
        if ($rupiah >= 1000) {
            $ribu = floor($rupiah / 1000);
            if ($ribu == 1) {
                $terbilang .= 'seribu ';
            } else {
                $terbilang .= $this->convertHundreds($ribu, $satuan, $belasan, $puluhan) . ' ribu ';
            }
            $rupiah = $rupiah % 1000;
        }
        
        if ($rupiah > 0) {
            $terbilang .= $this->convertHundreds($rupiah, $satuan, $belasan, $puluhan);
        }
        
        $terbilang = trim($terbilang);
        
        // Handle sen
        if ($sen > 0) {
            $terbilang .= ' koma ' . $this->convertHundreds($sen, $satuan, $belasan, $puluhan);
        }
        
        return ucfirst($terbilang) . ' rupiah';
    }

    private function convertHundreds($number, $satuan, $belasan, $puluhan)
    {
        $result = '';
        
        if ($number >= 100) {
            $ratus = floor($number / 100);
            if ($ratus == 1) {
                $result .= 'seratus ';
            } else {
                $result .= $satuan[$ratus] . ' ratus ';
            }
            $number = $number % 100;
        }
        
        if ($number >= 10 && $number < 20) {
            $result .= $belasan[$number - 10];
        } else {
            if ($number >= 20) {
                $result .= $puluhan[floor($number / 10)] . ' ';
                $number = $number % 10;
            }
            if ($number > 0) {
                $result .= $satuan[$number];
            }
        }
        
        return trim($result);
    }
}

