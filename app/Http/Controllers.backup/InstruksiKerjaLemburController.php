<?php

namespace App\Http\Controllers;

use App\Models\LemburHeader;
use App\Models\LemburDetail;
use App\Models\Karyawan;
use App\Models\Divisi;
use App\Models\Departemen;
use App\Models\Bagian;
use App\Models\Absen;
use App\Models\HariLibur;
use App\Models\Jabatan;
use App\Models\Gapok;
use App\Services\LemburCalculationService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class InstruksiKerjaLemburController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('dari_tanggal');
        $endDate = $request->get('sampai_tanggal');
        $nik = $request->get('nik');

        // Query untuk data header
        $query = LemburHeader::with(['departemen', 'bagian', 'details'])
            ->orderBy('dtCreate', 'desc');

        // Filter tanggal jika ada input dari user
        if ($startDate && $endDate) {
            $query->whereBetween('dtTanggalLembur', [$startDate, $endDate]);
        }

        // Filter NIK jika ada
        if ($nik) {
            $query->whereHas('details', function ($q) use ($nik) {
                $q->where('vcNik', 'like', '%' . $nik . '%');
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

        // Ambil data master untuk dropdown
        try {
            $divisis = Divisi::orderBy('vcKodeDivisi')->get();
        } catch (\Exception $e) {
            $divisis = collect([]);
        }

        try {
            $karyawans = Karyawan::where('vcAktif', '1')
                ->orderBy('Nama')
                ->get(['Nik', 'Nama']);
        } catch (\Exception $e) {
            $karyawans = collect([]);
        }

        return view('instruksi-kerja-lembur.index', compact('records', 'startDate', 'endDate', 'nik', 'divisis', 'karyawans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vcKodeDivisi' => 'required|string|max:10',
            'vcKodeDept' => 'required|string|max:10',
            'vcKodeBagian' => 'required|string|max:10',
            'dtTanggalLembur' => 'required|date',
            'vcDiajukanOleh' => 'required|string|max:100',
            'vcAlasanDasarLembur' => 'nullable|string|max:200',
            'details' => 'required|array|min:1',
            'details.*.vcNik' => 'required|string|max:10|exists:m_karyawan,Nik',
            'details.*.dtJamMulaiLembur' => ['required', 'string', 'date_format:H:i'],
            'details.*.dtJamSelesaiLembur' => ['required', 'string', 'date_format:H:i'],
            'details.*.decDurasiLembur' => 'nullable|numeric|min:0',
            'details.*.intDurasiIstirahat' => 'nullable|integer|min:0',
            'details.*.vcDeskripsiLembur' => 'nullable|string|max:200',
            'details.*.vcPenanggungBebanLembur' => 'nullable|string|max:20',
            'details.*.vcPenanggungBebanLainnya' => 'nullable|string|max:100',
            'freeRoleEnabled' => 'nullable|boolean',
        ], [
            'vcKodeDivisi.required' => 'Divisi harus diisi',
            'vcKodeDept.required' => 'Departemen harus diisi',
            'vcKodeBagian.required' => 'Bagian harus diisi',
            'dtTanggalLembur.required' => 'Tanggal lembur harus diisi',
            'vcDiajukanOleh.required' => 'Diajukan oleh harus diisi',
            'details.required' => 'Minimal harus ada 1 karyawan dalam detail',
            'details.min' => 'Minimal harus ada 1 karyawan dalam detail',
            'details.*.vcNik.required' => 'NIK harus diisi',
            'details.*.vcNik.exists' => 'NIK tidak ditemukan',
            'details.*.dtJamMulaiLembur.required' => 'Jam Mulai harus diisi',
            'details.*.dtJamMulaiLembur.regex' => 'Format Jam Mulai tidak valid (HH:MM)',
            'details.*.dtJamSelesaiLembur.required' => 'Jam Selesai harus diisi',
            'details.*.dtJamSelesaiLembur.regex' => 'Format Jam Selesai tidak valid (HH:MM)',
        ]);

        DB::beginTransaction();
        try {
            // Generate counter header (12 digit: YYYYMMDD + 4 digit random)
            $counterHeader = $this->generateCounterHeader($request->dtTanggalLembur);

            // Tentukan jenis lembur berdasarkan tanggal
            $jenisLembur = $this->determineJenisLembur($request->dtTanggalLembur);

            // Create Header
            $header = LemburHeader::create([
                'vcCounter' => substr($counterHeader, 0, 12),
                'vcBusinessUnit' => substr($request->vcKodeDivisi, 0, 50), // Simpan divisi di BusinessUnit
                'vcKodeDept' => substr($request->vcKodeDept, 0, 10),
                'vcKodeBagian' => substr($request->vcKodeBagian, 0, 10),
                'dtTanggalLembur' => $request->dtTanggalLembur,
                'vcJenisLembur' => $jenisLembur,
                'vcAlasanDasarLembur' => $request->vcAlasanDasarLembur ? substr($request->vcAlasanDasarLembur, 0, 200) : null,
                'decRencanaDurasiJam' => null,
                'dtRencanaDariPukul' => null,
                'dtRencanaSampaiPukul' => null,
                'vcDiajukanOleh' => substr($request->vcDiajukanOleh, 0, 100),
                'vcJabatanPengaju' => $request->vcJabatanPengaju ? substr($request->vcJabatanPengaju, 0, 10) : null,
                'vcKepalaDept' => $request->vcKepalaDept ? substr($request->vcKepalaDept, 0, 100) : null,
                'is_free_role' => $request->boolean('freeRoleEnabled'),
                'vcPenanggungBiaya' => null,
                'vcPenanggungBiayaLainnya' => null,
                'dtCreate' => Carbon::now(),
            ]);

            // Create Details
            foreach ($request->details as $index => $detail) {
                if (empty($detail['vcNik'])) continue;

                $karyawan = Karyawan::where('Nik', $detail['vcNik'])->first();
                if (!$karyawan) {
                    throw new \Exception('NIK ' . $detail['vcNik'] . ' tidak ditemukan');
                }

                $counterDetail = $this->generateCounterDetail($counterHeader, $index + 1);

                $namaKaryawan = substr($karyawan->Nama ?? '', 0, 150);

                $jabatValue = $karyawan->Jabat ?? '';
                if (strpos($jabatValue, ' -> ') !== false) {
                    $jabatValue = trim(explode(' -> ', $jabatValue)[0]);
                }
                $kodeJabatan = substr($jabatValue, 0, 10);

                // Format jam untuk detail
                $jamMulaiLembur = isset($detail['dtJamMulaiLembur']) && $detail['dtJamMulaiLembur']
                    ? (strlen($detail['dtJamMulaiLembur']) == 5 ? $detail['dtJamMulaiLembur'] . ':00' : substr($detail['dtJamMulaiLembur'], 0, 8))
                    : null;
                $jamSelesaiLembur = isset($detail['dtJamSelesaiLembur']) && $detail['dtJamSelesaiLembur']
                    ? (strlen($detail['dtJamSelesaiLembur']) == 5 ? $detail['dtJamSelesaiLembur'] . ':00' : substr($detail['dtJamSelesaiLembur'], 0, 8))
                    : null;

                // Hitung nominal lembur jika ada penanggung beban dan jam lembur
                $decLemburExternal = null;
                if (!empty($detail['vcPenanggungBebanLembur']) && $jamMulaiLembur && $jamSelesaiLembur) {
                    try {
                        // Ambil gapok karyawan
                        $gapok = Gapok::find($karyawan->Gol);
                        if ($gapok) {
                            // Hitung gapok per bulan
                            $gapokPerBulan = (float) ($gapok->upah ?? 0)
                                + (float) ($gapok->tunj_keluarga ?? 0)
                                + (float) ($gapok->tunj_masa_kerja ?? 0)
                                + (float) ($gapok->tunj_jabatan1 ?? 0)
                                + (float) ($gapok->tunj_jabatan2 ?? 0);

                            // Ambil hari libur list untuk cek apakah hari libur
                            $tanggalLembur = Carbon::parse($request->dtTanggalLembur)->format('Y-m-d');
                            $hariLiburList = HariLibur::where('dtTanggal', $tanggalLembur)
                                ->pluck('dtTanggal')
                                ->map(function ($tanggal) {
                                    return $tanggal instanceof Carbon ? $tanggal->format('Y-m-d') : Carbon::parse($tanggal)->format('Y-m-d');
                                })
                                ->toArray();

                            // Hitung total jam lembur
                            $durasiIstirahat = isset($detail['intDurasiIstirahat']) ? (int)$detail['intDurasiIstirahat'] : 0;
                            $jamMulai = substr($jamMulaiLembur, 0, 5);
                            $jamSelesai = substr($jamSelesaiLembur, 0, 5);
                            $totalJamLembur = LemburCalculationService::calculateTotalJamLembur($jamMulai, $jamSelesai, $tanggalLembur, $durasiIstirahat);

                            // Cek apakah hari libur
                            $isHariLibur = LemburCalculationService::isHariLibur($tanggalLembur, $hariLiburList);

                            // Hitung nominal lembur
                            $lemburCalculation = LemburCalculationService::calculateLemburNominal($gapokPerBulan, $totalJamLembur, $isHariLibur);
                            $decLemburExternal = $lemburCalculation['nominal'];
                        }
                    } catch (\Exception $e) {
                        Log::warning('Error calculating lembur external for NIK ' . $detail['vcNik'] . ': ' . $e->getMessage());
                        // Continue tanpa nominal jika ada error
                    }
                }

                // Prepare data untuk create
                $detailData = [
                    'vcCounterDetail' => $counterDetail,
                    'vcCounterHeader' => substr($counterHeader, 0, 12),
                    'vcNik' => substr($detail['vcNik'], 0, 8),
                    'vcNamaKaryawan' => $namaKaryawan,
                    'vcKodeJabatan' => $kodeJabatan,
                    'dtJamMulaiLembur' => $jamMulaiLembur,
                    'dtJamSelesaiLembur' => $jamSelesaiLembur,
                    'decDurasiLembur' => isset($detail['decDurasiLembur']) ? $detail['decDurasiLembur'] : null,
                    'intDurasiIstirahat' => isset($detail['intDurasiIstirahat']) ? (int)$detail['intDurasiIstirahat'] : 0,
                    'vcDeskripsiLembur' => isset($detail['vcDeskripsiLembur']) ? substr($detail['vcDeskripsiLembur'], 0, 200) : null,
                    'vcPenanggungBebanLembur' => isset($detail['vcPenanggungBebanLembur']) ? substr($detail['vcPenanggungBebanLembur'], 0, 20) : null,
                    'vcPenanggungBebanLainnya' => isset($detail['vcPenanggungBebanLainnya']) ? substr($detail['vcPenanggungBebanLainnya'], 0, 100) : null,
                    'dtCreate' => Carbon::now(),
                ];

                // Tambahkan decLemburExternal hanya jika kolom ada di database
                if (Schema::hasColumn('t_lembur_detail', 'decLemburExternal')) {
                    $detailData['decLemburExternal'] = $decLemburExternal;
                }

                LemburDetail::create($detailData);

                // Update atau create record di t_absen untuk realisasi lembur
                $tanggalLembur = Carbon::parse($request->dtTanggalLembur)->format('Y-m-d');
                $nikAbsen = substr($detail['vcNik'], 0, 8);

                // Cek apakah record sudah ada
                $absenExists = DB::table('t_absen')
                    ->where('dtTanggal', $tanggalLembur)
                    ->where('vcNik', $nikAbsen)
                    ->exists();

                // Ambil durasi istirahat dari detail
                $durasiIstirahat = isset($detail['intDurasiIstirahat']) ? (int)$detail['intDurasiIstirahat'] : 0;

                if ($absenExists) {
                    // Update existing record
                    DB::table('t_absen')
                        ->where('dtTanggal', $tanggalLembur)
                        ->where('vcNik', $nikAbsen)
                        ->update([
                            'dtJamMasukLembur' => $jamMulaiLembur,
                            'dtJamKeluarLembur' => $jamSelesaiLembur,
                            'intDurasiIstirahat' => $durasiIstirahat,
                            'vcCounter' => substr($counterHeader, 0, 12),
                            'dtChange' => Carbon::now(),
                        ]);
                } else {
                    // Create new record
                    DB::table('t_absen')
                        ->insert([
                            'dtTanggal' => $tanggalLembur,
                            'vcNik' => $nikAbsen,
                            'dtJamMasukLembur' => $jamMulaiLembur,
                            'dtJamKeluarLembur' => $jamSelesaiLembur,
                            'intDurasiIstirahat' => $durasiIstirahat,
                            'vcCounter' => substr($counterHeader, 0, 12),
                            'dtCreate' => Carbon::now(),
                            'dtChange' => Carbon::now(),
                        ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Instruksi kerja lembur berhasil ditambahkan'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving instruksi kerja lembur: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id)
    {
        $header = LemburHeader::with(['departemen', 'bagian', 'divisi', 'details.karyawan.jabatan', 'pengaju'])
            ->findOrFail($id);

        $details = [];
        $computedFreeRole = (bool) ($header->is_free_role ?? false);
        foreach ($header->details as $detail) {
            $namaJabatan = '-';
            if ($detail->jabatan) {
                $namaJabatan = $detail->jabatan->vcNamaJabatan;
            } elseif ($detail->karyawan && $detail->karyawan->jabatan) {
                $namaJabatan = $detail->karyawan->jabatan->vcNamaJabatan;
            }

            if (!$computedFreeRole && $detail->karyawan) {
                $detailDept = $detail->karyawan->dept ?? null;
                $detailBagian = $detail->karyawan->vcKodeBagian ?? null;
                if (($detailDept && $header->vcKodeDept && $detailDept !== $header->vcKodeDept) ||
                    ($detailBagian && $header->vcKodeBagian && $detailBagian !== $header->vcKodeBagian)
                ) {
                    $computedFreeRole = true;
                }
            }

            $details[] = [
                'vcNik' => $detail->vcNik,
                'vcNamaKaryawan' => $detail->vcNamaKaryawan ?? ($detail->karyawan->Nama ?? '-'),
                'vcKodeJabatan' => $detail->vcKodeJabatan,
                'namaJabatan' => $namaJabatan,
                'dtJamMulaiLembur' => $detail->dtJamMulaiLembur ? substr($detail->dtJamMulaiLembur, 0, 5) : '',
                'dtJamSelesaiLembur' => $detail->dtJamSelesaiLembur ? substr($detail->dtJamSelesaiLembur, 0, 5) : '',
                'decDurasiLembur' => $detail->decDurasiLembur ? number_format($detail->decDurasiLembur, 2, '.', '') : '',
                'intDurasiIstirahat' => $detail->intDurasiIstirahat ?? 0,
                'vcDeskripsiLembur' => $detail->vcDeskripsiLembur ?? '',
                'vcPenanggungBebanLembur' => $detail->vcPenanggungBebanLembur ?? '',
                'vcPenanggungBebanLainnya' => $detail->vcPenanggungBebanLainnya ?? '',
                'decLemburExternal' => $detail->decLemburExternal ?? null,
            ];
        }

        return response()->json([
            'success' => true,
            'record' => [
                'vcCounter' => $header->vcCounter,
                'vcKodeDivisi' => $header->vcBusinessUnit, // Divisi disimpan di BusinessUnit
                'namaDivisi' => $header->divisi ? $header->divisi->vcNamaDivisi : '',
                'vcKodeDept' => $header->vcKodeDept,
                'namaDept' => $header->departemen ? $header->departemen->vcNamaDept : '',
                'vcKodeBagian' => $header->vcKodeBagian,
                'namaBagian' => $header->bagian ? $header->bagian->vcNamaBagian : '',
                'dtTanggalLembur' => $header->dtTanggalLembur->format('Y-m-d'),
                'vcJenisLembur' => $header->vcJenisLembur ?? '',
                'vcAlasanDasarLembur' => $header->vcAlasanDasarLembur,
                'is_free_role' => $computedFreeRole,
                'decRencanaDurasiJam' => $header->decRencanaDurasiJam ? number_format($header->decRencanaDurasiJam, 2, '.', '') : '',
                'dtRencanaDariPukul' => $header->dtRencanaDariPukul ? substr($header->dtRencanaDariPukul, 0, 5) : '',
                'dtRencanaSampaiPukul' => $header->dtRencanaSampaiPukul ? substr($header->dtRencanaSampaiPukul, 0, 5) : '',
                'vcDiajukanOleh' => $header->vcDiajukanOleh,
                'namaPengaju' => $header->pengaju ? $header->pengaju->Nama : '',
                'vcJabatanPengaju' => $header->vcJabatanPengaju ?? '',
                'vcJabatanPengajuNama' => $header->vcJabatanPengaju ? (Jabatan::where('vcKodeJabatan', $header->vcJabatanPengaju)->value('vcNamaJabatan') ?? '') : '',
                'vcKepalaDept' => $header->vcKepalaDept ?? '',
                'vcPenanggungBiaya' => $header->vcPenanggungBiaya,
                'vcPenanggungBiayaLainnya' => $header->vcPenanggungBiayaLainnya,
                'details' => $details,
            ]
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'vcKodeDivisi' => 'required|string|max:10',
            'vcKodeDept' => 'required|string|max:10',
            'vcKodeBagian' => 'required|string|max:10',
            'dtTanggalLembur' => 'required|date',
            'vcDiajukanOleh' => 'required|string|max:100',
            'vcAlasanDasarLembur' => 'nullable|string|max:200',
            'details' => 'required|array|min:1',
            'details.*.vcNik' => 'required|string|max:10|exists:m_karyawan,Nik',
            'details.*.dtJamMulaiLembur' => ['required', 'string', 'date_format:H:i'],
            'details.*.dtJamSelesaiLembur' => ['required', 'string', 'date_format:H:i'],
            'details.*.decDurasiLembur' => 'nullable|numeric|min:0',
            'details.*.intDurasiIstirahat' => 'nullable|integer|min:0',
            'details.*.vcDeskripsiLembur' => 'nullable|string|max:200',
            'details.*.vcPenanggungBebanLembur' => 'nullable|string|max:20',
            'details.*.vcPenanggungBebanLainnya' => 'nullable|string|max:100',
            'freeRoleEnabled' => 'nullable|boolean',
        ], [
            'vcKodeDivisi.required' => 'Divisi harus diisi',
            'vcKodeDept.required' => 'Departemen harus diisi',
            'vcKodeBagian.required' => 'Bagian harus diisi',
            'dtTanggalLembur.required' => 'Tanggal lembur harus diisi',
            'vcDiajukanOleh.required' => 'Diajukan oleh harus diisi',
            'details.required' => 'Minimal harus ada 1 karyawan dalam detail',
            'details.min' => 'Minimal harus ada 1 karyawan dalam detail',
            'details.*.vcNik.required' => 'NIK harus diisi',
            'details.*.vcNik.exists' => 'NIK tidak ditemukan',
            'details.*.dtJamMulaiLembur.required' => 'Jam Mulai harus diisi',
            'details.*.dtJamMulaiLembur.regex' => 'Format Jam Mulai tidak valid (HH:MM)',
            'details.*.dtJamSelesaiLembur.required' => 'Jam Selesai harus diisi',
            'details.*.dtJamSelesaiLembur.regex' => 'Format Jam Selesai tidak valid (HH:MM)',
        ]);

        DB::beginTransaction();
        try {
            $header = LemburHeader::findOrFail($id);

            // Tentukan jenis lembur berdasarkan tanggal
            $jenisLembur = $this->determineJenisLembur($request->dtTanggalLembur);

            // Update Header
            $header->update([
                'vcBusinessUnit' => substr($request->vcKodeDivisi, 0, 50),
                'vcKodeDept' => $request->vcKodeDept,
                'vcKodeBagian' => $request->vcKodeBagian,
                'dtTanggalLembur' => $request->dtTanggalLembur,
                'vcJenisLembur' => $jenisLembur,
                'vcAlasanDasarLembur' => $request->vcAlasanDasarLembur,
                'decRencanaDurasiJam' => null,
                'dtRencanaDariPukul' => null,
                'dtRencanaSampaiPukul' => null,
                'vcDiajukanOleh' => $request->vcDiajukanOleh,
                'vcJabatanPengaju' => $request->vcJabatanPengaju ? substr($request->vcJabatanPengaju, 0, 10) : null,
                'vcKepalaDept' => $request->vcKepalaDept ? substr($request->vcKepalaDept, 0, 100) : null,
                'is_free_role' => $request->boolean('freeRoleEnabled'),
                'vcPenanggungBiaya' => null,
                'vcPenanggungBiayaLainnya' => null,
                'dtChange' => Carbon::now(),
            ]);

            // Hapus detail lama
            LemburDetail::where('vcCounterHeader', $header->vcCounter)->delete();

            // Clear vcCounter di t_absen untuk detail lama (jika ada)
            DB::table('t_absen')
                ->where('vcCounter', $header->vcCounter)
                ->update([
                    'vcCounter' => null,
                    'dtJamMasukLembur' => null,
                    'dtJamKeluarLembur' => null,
                    'intDurasiIstirahat' => 0,
                    'dtChange' => Carbon::now(),
                ]);

            // Create Details baru
            foreach ($request->details as $index => $detail) {
                if (empty($detail['vcNik'])) continue;

                $karyawan = Karyawan::where('Nik', $detail['vcNik'])->first();
                if (!$karyawan) {
                    throw new \Exception('NIK ' . $detail['vcNik'] . ' tidak ditemukan');
                }

                $counterDetail = $this->generateCounterDetail($header->vcCounter, $index + 1);

                $namaKaryawan = substr($karyawan->Nama ?? '', 0, 150);

                $jabatValue = $karyawan->Jabat ?? '';
                if (strpos($jabatValue, ' -> ') !== false) {
                    $jabatValue = trim(explode(' -> ', $jabatValue)[0]);
                }
                $kodeJabatan = substr($jabatValue, 0, 10);

                // Format jam untuk detail
                $jamMulaiLembur = isset($detail['dtJamMulaiLembur']) && $detail['dtJamMulaiLembur']
                    ? (strlen($detail['dtJamMulaiLembur']) == 5 ? $detail['dtJamMulaiLembur'] . ':00' : substr($detail['dtJamMulaiLembur'], 0, 8))
                    : null;
                $jamSelesaiLembur = isset($detail['dtJamSelesaiLembur']) && $detail['dtJamSelesaiLembur']
                    ? (strlen($detail['dtJamSelesaiLembur']) == 5 ? $detail['dtJamSelesaiLembur'] . ':00' : substr($detail['dtJamSelesaiLembur'], 0, 8))
                    : null;

                // Hitung nominal lembur jika ada penanggung beban dan jam lembur
                $decLemburExternal = null;
                if (!empty($detail['vcPenanggungBebanLembur']) && $jamMulaiLembur && $jamSelesaiLembur) {
                    try {
                        // Ambil gapok karyawan
                        $gapok = Gapok::find($karyawan->Gol);
                        if ($gapok) {
                            // Hitung gapok per bulan
                            $gapokPerBulan = (float) ($gapok->upah ?? 0)
                                + (float) ($gapok->tunj_keluarga ?? 0)
                                + (float) ($gapok->tunj_masa_kerja ?? 0)
                                + (float) ($gapok->tunj_jabatan1 ?? 0)
                                + (float) ($gapok->tunj_jabatan2 ?? 0);

                            // Ambil hari libur list untuk cek apakah hari libur
                            $tanggalLembur = Carbon::parse($request->dtTanggalLembur)->format('Y-m-d');
                            $hariLiburList = HariLibur::where('dtTanggal', $tanggalLembur)
                                ->pluck('dtTanggal')
                                ->map(function ($tanggal) {
                                    return $tanggal instanceof Carbon ? $tanggal->format('Y-m-d') : Carbon::parse($tanggal)->format('Y-m-d');
                                })
                                ->toArray();

                            // Hitung total jam lembur
                            $durasiIstirahat = isset($detail['intDurasiIstirahat']) ? (int)$detail['intDurasiIstirahat'] : 0;
                            $jamMulai = substr($jamMulaiLembur, 0, 5);
                            $jamSelesai = substr($jamSelesaiLembur, 0, 5);
                            $totalJamLembur = LemburCalculationService::calculateTotalJamLembur($jamMulai, $jamSelesai, $tanggalLembur, $durasiIstirahat);

                            // Cek apakah hari libur
                            $isHariLibur = LemburCalculationService::isHariLibur($tanggalLembur, $hariLiburList);

                            // Hitung nominal lembur
                            $lemburCalculation = LemburCalculationService::calculateLemburNominal($gapokPerBulan, $totalJamLembur, $isHariLibur);
                            $decLemburExternal = $lemburCalculation['nominal'];
                        }
                    } catch (\Exception $e) {
                        Log::warning('Error calculating lembur external for NIK ' . $detail['vcNik'] . ': ' . $e->getMessage());
                        // Continue tanpa nominal jika ada error
                    }
                }

                // Prepare data untuk create
                $detailData = [
                    'vcCounterDetail' => $counterDetail,
                    'vcCounterHeader' => $header->vcCounter,
                    'vcNik' => substr($detail['vcNik'], 0, 8),
                    'vcNamaKaryawan' => $namaKaryawan,
                    'vcKodeJabatan' => $kodeJabatan,
                    'dtJamMulaiLembur' => $jamMulaiLembur,
                    'dtJamSelesaiLembur' => $jamSelesaiLembur,
                    'decDurasiLembur' => isset($detail['decDurasiLembur']) ? $detail['decDurasiLembur'] : null,
                    'intDurasiIstirahat' => isset($detail['intDurasiIstirahat']) ? (int)$detail['intDurasiIstirahat'] : 0,
                    'vcDeskripsiLembur' => isset($detail['vcDeskripsiLembur']) ? substr($detail['vcDeskripsiLembur'], 0, 200) : null,
                    'vcPenanggungBebanLembur' => isset($detail['vcPenanggungBebanLembur']) ? substr($detail['vcPenanggungBebanLembur'], 0, 20) : null,
                    'vcPenanggungBebanLainnya' => isset($detail['vcPenanggungBebanLainnya']) ? substr($detail['vcPenanggungBebanLainnya'], 0, 100) : null,
                    'dtCreate' => Carbon::now(),
                ];

                // Tambahkan decLemburExternal hanya jika kolom ada di database
                if (Schema::hasColumn('t_lembur_detail', 'decLemburExternal')) {
                    $detailData['decLemburExternal'] = $decLemburExternal;
                }

                LemburDetail::create($detailData);

                // Update atau create record di t_absen untuk realisasi lembur
                $tanggalLembur = Carbon::parse($request->dtTanggalLembur)->format('Y-m-d');
                $nikAbsen = substr($detail['vcNik'], 0, 8);

                // Cek apakah record sudah ada
                $absenExists = DB::table('t_absen')
                    ->where('dtTanggal', $tanggalLembur)
                    ->where('vcNik', $nikAbsen)
                    ->exists();

                // Ambil durasi istirahat dari detail
                $durasiIstirahat = isset($detail['intDurasiIstirahat']) ? (int)$detail['intDurasiIstirahat'] : 0;

                if ($absenExists) {
                    // Update existing record
                    DB::table('t_absen')
                        ->where('dtTanggal', $tanggalLembur)
                        ->where('vcNik', $nikAbsen)
                        ->update([
                            'dtJamMasukLembur' => $jamMulaiLembur,
                            'dtJamKeluarLembur' => $jamSelesaiLembur,
                            'intDurasiIstirahat' => $durasiIstirahat,
                            'vcCounter' => $header->vcCounter,
                            'dtChange' => Carbon::now(),
                        ]);
                } else {
                    // Create new record
                    DB::table('t_absen')
                        ->insert([
                            'dtTanggal' => $tanggalLembur,
                            'vcNik' => $nikAbsen,
                            'dtJamMasukLembur' => $jamMulaiLembur,
                            'dtJamKeluarLembur' => $jamSelesaiLembur,
                            'intDurasiIstirahat' => $durasiIstirahat,
                            'vcCounter' => $header->vcCounter,
                            'dtCreate' => Carbon::now(),
                            'dtChange' => Carbon::now(),
                        ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Instruksi kerja lembur berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $header = LemburHeader::findOrFail($id);

            // Hapus detail terlebih dahulu
            LemburDetail::where('vcCounterHeader', $header->vcCounter)->delete();

            // Clear vcCounter dan jam lembur di t_absen untuk detail yang dihapus
            DB::table('t_absen')
                ->where('vcCounter', $header->vcCounter)
                ->update([
                    'vcCounter' => null,
                    'dtJamMasukLembur' => null,
                    'dtJamKeluarLembur' => null,
                    'intDurasiIstirahat' => 0,
                    'dtChange' => Carbon::now(),
                ]);

            // Hapus header
            $header->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Instruksi kerja lembur berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get departemens by divisi (based on m_hirarki_dept)
     */
    public function getDepartemensByDivisi(Request $request)
    {
        $request->validate([
            'divisi' => 'required|string',
        ]);

        $departemens = DB::table('m_hirarki_dept')
            ->join('m_dept', 'm_hirarki_dept.vcKodeDept', '=', 'm_dept.vcKodeDept')
            ->where('m_hirarki_dept.vcKodeDivisi', $request->divisi)
            ->select('m_dept.vcKodeDept', 'm_dept.vcNamaDept')
            ->orderBy('m_dept.vcKodeDept')
            ->get();

        return response()->json([
            'success' => true,
            'departemens' => $departemens
        ]);
    }

    /**
     * Get bagians by divisi and departemen (based on m_hirarki_bagian)
     */
    public function getBagiansByDivisiDept(Request $request)
    {
        $request->validate([
            'divisi' => 'required|string',
            'departemen' => 'required|string',
        ]);

        $bagians = DB::table('m_hirarki_bagian')
            ->join('m_bagian', 'm_hirarki_bagian.vcKodeBagian', '=', 'm_bagian.vcKodeBagian')
            ->where('m_hirarki_bagian.vcKodeDivisi', $request->divisi)
            ->where('m_hirarki_bagian.vcKodeDept', $request->departemen)
            ->select('m_bagian.vcKodeBagian', 'm_bagian.vcNamaBagian')
            ->orderBy('m_bagian.vcKodeBagian')
            ->get();

        return response()->json([
            'success' => true,
            'bagians' => $bagians
        ]);
    }

    /**
     * Get karyawans by bagian (filter karyawan berdasarkan bagian yang dipilih)
     * @deprecated Gunakan getKaryawansByDepartemen untuk filter berdasarkan departemen
     */
    public function getKaryawansByBagian(Request $request)
    {
        $request->validate([
            'bagian' => 'required|string',
        ]);

        $karyawans = Karyawan::where('vcKodeBagian', $request->bagian)
            ->where('vcAktif', '1')
            ->orderBy('Nama')
            ->get(['Nik', 'Nama']);

        return response()->json([
            'success' => true,
            'karyawans' => $karyawans
        ]);
    }

    /**
     * Get karyawans by departemen (filter karyawan berdasarkan departemen yang dipilih)
     */
    public function getKaryawansByDepartemen(Request $request)
    {
        $request->validate([
            'departemen' => 'required|string',
        ]);

        $karyawans = Karyawan::where('dept', $request->departemen)
            ->where('vcAktif', '1')
            ->orderBy('Nama')
            ->get(['Nik', 'Nama']);

        return response()->json([
            'success' => true,
            'karyawans' => $karyawans
        ]);
    }

    /**
     * Get karyawans by Divisi for detail section
     */
    public function getKaryawansByDivisi(Request $request)
    {
        $request->validate([
            'divisi' => 'required|string',
        ]);

        $karyawans = Karyawan::where('Divisi', $request->divisi)
            ->where('vcAktif', '1')
            ->orderBy('Nama')
            ->get(['Nik', 'Nama']);

        return response()->json([
            'success' => true,
            'karyawans' => $karyawans
        ]);
    }

    /**
     * Get all active karyawans (for Free Role mode - no filtering)
     */
    public function getAllKaryawans(Request $request)
    {
        $karyawans = Karyawan::where('vcAktif', '1')
            ->orderBy('Nama')
            ->get(['Nik', 'Nama']);

        return response()->json([
            'success' => true,
            'karyawans' => $karyawans
        ]);
    }

    /**
     * Get kepala departemen berdasarkan kode departemen
     */
    public function getKepalaDept(Request $request)
    {
        $request->validate([
            'departemen' => 'required|string',
        ]);

        // Ambil departemen dengan relasi jabatan
        $departemen = Departemen::with('jabatan')
            ->where('vcKodeDept', $request->departemen)
            ->first();

        if (!$departemen) {
            return response()->json([
                'success' => false,
                'message' => 'Departemen tidak ditemukan'
            ]);
        }

        // Prioritas 1: Gunakan vcKodeJabatan untuk mencari karyawan
        if ($departemen->vcKodeJabatan) {
            // Cari karyawan yang memiliki jabatan tersebut dan aktif
            $karyawan = Karyawan::where('vcAktif', '1')
                ->where(function ($query) use ($departemen) {
                    // Field Jabat bisa berisi kode saja atau "kode -> nama"
                    $query->where('Jabat', $departemen->vcKodeJabatan)
                        ->orWhere('Jabat', 'like', $departemen->vcKodeJabatan . ' -> %');
                })
                ->first(['Nik', 'Nama', 'Jabat']);

            if ($karyawan) {
                return response()->json([
                    'success' => true,
                    'kepalaDept' => $karyawan->Nik . ' - ' . $karyawan->Nama,
                    'nik' => $karyawan->Nik,
                    'nama' => $karyawan->Nama
                ]);
            }
        }

        // Prioritas 2: Gunakan vcPICDept jika ada (fallback)
        if ($departemen->vcPICDept) {
            // Cek apakah vcPICDept adalah NIK (8 digit) atau nama
            $pic = $departemen->vcPICDept;

            // Jika berupa NIK, cari nama karyawan
            if (strlen($pic) == 8 && is_numeric($pic)) {
                $karyawan = Karyawan::where('Nik', $pic)
                    ->where('vcAktif', '1')
                    ->first(['Nik', 'Nama']);

                if ($karyawan) {
                    return response()->json([
                        'success' => true,
                        'kepalaDept' => $karyawan->Nik . ' - ' . $karyawan->Nama,
                        'nik' => $karyawan->Nik,
                        'nama' => $karyawan->Nama
                    ]);
                }
            }

            // Jika bukan NIK atau tidak ditemukan, return sebagai nama
            return response()->json([
                'success' => true,
                'kepalaDept' => $pic,
                'nik' => '',
                'nama' => $pic
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Kepala departemen tidak ditemukan'
        ]);
    }

    /**
     * Calculate lembur nominal for preview
     */
    public function calculateLemburNominal(Request $request)
    {
        $request->validate([
            'nik' => 'required|string',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required|string|date_format:H:i',
            'jam_selesai' => 'required|string|date_format:H:i',
            'durasi_istirahat' => 'nullable|integer|min:0',
        ]);

        try {
            $karyawan = Karyawan::where('Nik', $request->nik)->first();
            if (!$karyawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Karyawan tidak ditemukan'
                ], 404);
            }

            // Ambil gapok karyawan
            $gapok = Gapok::find($karyawan->Gol);
            if (!$gapok) {
                return response()->json([
                    'success' => false,
                    'message' => 'Golongan karyawan tidak ditemukan di master gapok'
                ], 404);
            }

            // Hitung gapok per bulan
            $gapokPerBulan = (float) ($gapok->upah ?? 0)
                + (float) ($gapok->tunj_keluarga ?? 0)
                + (float) ($gapok->tunj_masa_kerja ?? 0)
                + (float) ($gapok->tunj_jabatan1 ?? 0)
                + (float) ($gapok->tunj_jabatan2 ?? 0);

            // Ambil hari libur list untuk cek apakah hari libur
            $tanggalLembur = Carbon::parse($request->tanggal)->format('Y-m-d');
            $hariLiburList = HariLibur::where('dtTanggal', $tanggalLembur)
                ->pluck('dtTanggal')
                ->map(function ($tanggal) {
                    return $tanggal instanceof Carbon ? $tanggal->format('Y-m-d') : Carbon::parse($tanggal)->format('Y-m-d');
                })
                ->toArray();

            // Hitung total jam lembur
            $durasiIstirahat = (int) ($request->durasi_istirahat ?? 0);
            $totalJamLembur = LemburCalculationService::calculateTotalJamLembur(
                $request->jam_mulai,
                $request->jam_selesai,
                $tanggalLembur,
                $durasiIstirahat
            );

            // Cek apakah hari libur
            $isHariLibur = LemburCalculationService::isHariLibur($tanggalLembur, $hariLiburList);

            // Hitung nominal lembur
            $lemburCalculation = LemburCalculationService::calculateLemburNominal($gapokPerBulan, $totalJamLembur, $isHariLibur);

            return response()->json([
                'success' => true,
                'nominal' => $lemburCalculation['nominal'],
                'total_jam' => $totalJamLembur,
                'is_hari_libur' => $isHariLibur,
                'gapok_per_bulan' => $gapokPerBulan,
                'rate_per_jam' => round($gapokPerBulan / 173, 2),
                'detail' => $lemburCalculation,
            ]);
        } catch (\Exception $e) {
            Log::error('Error calculating lembur nominal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghitung nominal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get karyawan data lengkap untuk auto-fill Divisi, Departemen, Bagian
     */
    public function getKaryawanData(Request $request)
    {
        $request->validate([
            'nik' => 'required|string',
        ]);

        $karyawan = Karyawan::with('jabatan')
            ->where('Nik', $request->nik)
            ->where('vcAktif', '1')
            ->first(['Nik', 'Nama', 'Divisi', 'dept', 'vcKodeBagian', 'Jabat']);

        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan'
            ], 404);
        }

        // Ambil kode jabatan dari field Jabat (bisa berisi kode atau kode -> nama)
        $kodeJabatan = $karyawan->Jabat ?? '';
        if (strpos($kodeJabatan, ' -> ') !== false) {
            $kodeJabatan = trim(explode(' -> ', $kodeJabatan)[0]);
        }

        // Ambil nama jabatan dari relasi
        $namaJabatan = $karyawan->jabatan ? $karyawan->jabatan->vcNamaJabatan : '';

        return response()->json([
            'success' => true,
            'karyawan' => [
                'nik' => $karyawan->Nik,
                'nama' => $karyawan->Nama,
                'divisi' => $karyawan->Divisi ?? '',
                'departemen' => $karyawan->dept ?? '',
                'bagian' => $karyawan->vcKodeBagian ?? '',
                'jabatan' => $kodeJabatan,
                'namaJabatan' => $namaJabatan
            ]
        ]);
    }

    private function generateCounterHeader($tanggal)
    {
        $date = Carbon::parse($tanggal);
        $datePart = $date->format('Ymd');
        $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

        $counter = $datePart . $random;

        $exists = LemburHeader::where('vcCounter', $counter)->exists();
        if ($exists) {
            return $this->generateCounterHeader($tanggal);
        }

        return $counter;
    }

    private function generateCounterDetail($counterHeader, $index)
    {
        return $counterHeader . str_pad($index, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Determine jenis lembur berdasarkan tanggal (Hari Kerja atau Hari Libur)
     */
    private function determineJenisLembur($tanggal)
    {
        $tanggalObj = Carbon::parse($tanggal);
        $tanggalStr = $tanggalObj->format('Y-m-d');

        // Cek apakah hari Sabtu (6) atau Minggu (0)
        $dayOfWeek = $tanggalObj->dayOfWeek;
        $isWeekend = ($dayOfWeek == 0 || $dayOfWeek == 6);

        // Cek apakah tanggal ada di tabel hari libur
        $isHoliday = HariLibur::where('dtTanggal', $tanggalStr)->exists();

        // Jika weekend atau holiday, maka Hari Libur, selain itu Hari Kerja
        if ($isWeekend || $isHoliday) {
            return 'Hari Libur';
        } else {
            return 'Hari Kerja';
        }
    }

    /**
     * API endpoint untuk check jenis lembur berdasarkan tanggal
     */
    public function checkJenisLembur(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
        ]);

        $jenisLembur = $this->determineJenisLembur($request->tanggal);

        return response()->json([
            'success' => true,
            'jenisLembur' => $jenisLembur
        ]);
    }
}
