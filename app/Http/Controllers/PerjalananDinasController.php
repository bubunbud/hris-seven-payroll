<?php

namespace App\Http\Controllers;

use App\Models\PerjalananDinasHeader;
use App\Models\PerjalananDinasKaryawan;
use App\Models\PerjalananDinasJadwal;
use App\Models\PerjalananDinasHotel;
use App\Models\PerjalananDinasTibaKembali;
use App\Models\Karyawan;
use App\Models\Divisi;
use App\Models\Departemen;
use App\Models\Bagian;
use App\Models\Jabatan;
use App\Models\Absen;
use App\Models\Shift;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerjalananDinasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $startDate = $request->get('dari_tanggal');
        $endDate = $request->get('sampai_tanggal');
        $search = $request->get('search'); // No RPD / Nama Karyawan
        
        // Load karyawan aktif untuk autocomplete lokal
        $karyawansForAutocomplete = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->with(['divisi', 'bagian'])
            ->orderBy('Nama')
            ->get(['Nik', 'Nama', 'Divisi', 'vcKodeBagian']);

        $karyawanList = $karyawansForAutocomplete->map(function ($k) {
            $divisiNama = '-';
            if ($k->divisi && isset($k->divisi->vcNamaDivisi)) {
                $divisiNama = $k->divisi->vcNamaDivisi;
            } elseif ($k->Divisi) {
                $divisiNama = $k->Divisi;
            }

            $bagianNama = '-';
            if ($k->bagian && isset($k->bagian->vcNamaBagian)) {
                $bagianNama = $k->bagian->vcNamaBagian;
            }

            return [
                'nik' => $k->Nik ?: '',
                'nama' => $k->Nama ?: '',
                'divisi' => $divisiNama,
                'bagian' => $bagianNama,
                'search' => strtolower(($k->Nik ?: '') . ' ' . ($k->Nama ?: '')),
            ];
        })->values();

        // Query untuk data header
        $query = PerjalananDinasHeader::with(['karyawans', 'jadwals', 'hotels', 'tibaKembali'])
            ->orderBy('dtCreate', 'desc');

        // Filter tanggal jika ada input dari user
        // Filter bisa berdasarkan: Tanggal Form ATAU Tanggal Dinas (lebih fleksibel)
        if ($startDate && $endDate) {
            $query->where(function($q) use ($startDate, $endDate) {
                // Filter berdasarkan Tanggal Form
                $q->whereBetween('dtTanggalForm', [$startDate, $endDate])
                  // ATAU filter berdasarkan Tanggal Dinas Dari
                  ->orWhereBetween('dtTanggalDinasDari', [$startDate, $endDate])
                  // ATAU filter berdasarkan Tanggal Dinas Sampai
                  ->orWhereBetween('dtTanggalDinasSampai', [$startDate, $endDate])
                  // ATAU jika range tanggal dinas overlap dengan range filter
                  ->orWhere(function($q2) use ($startDate, $endDate) {
                      $q2->where('dtTanggalDinasDari', '<=', $endDate)
                         ->where('dtTanggalDinasSampai', '>=', $startDate);
                  });
            });
        }

        // Apply search filter
        if ($search) {
            $searchTerms = preg_split('/,\s*/', trim($search));
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    if (!empty(trim($term))) {
                        $term = trim($term);
                        $q->orWhere('vcNoRpd', 'like', '%' . $term . '%')
                            ->orWhere('vcPemberiTugas', 'like', '%' . $term . '%')
                            ->orWhere('vcTujuanDinas', 'like', '%' . $term . '%')
                            ->orWhereHas('karyawans', function ($q2) use ($term) {
                                $q2->where('vcNik', 'like', '%' . $term . '%')
                                    ->orWhere('vcNamaKaryawan', 'like', '%' . $term . '%');
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

        return view('perjalanan-dinas.index', compact('records', 'startDate', 'endDate', 'search', 'divisis', 'karyawans', 'karyawanList'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'dtTanggalForm' => 'required|date',
            'dtTanggalDinasDari' => 'required|date',
            'dtTanggalDinasSampai' => 'required|date|after_or_equal:dtTanggalDinasDari',
            'vcPemberiTugas' => 'required|string|max:100',
            'vcJabatanPemberiTugas' => 'nullable|string|max:100',
            'vcTujuanDinas' => 'required|string|max:200',
            'vcMaksudPerjalananDinas' => 'nullable|string',
            'vcMengajukan' => 'nullable|string|max:100',
            'vcMenyetujui' => 'nullable|string|max:100',
            'vcMengetahui' => 'nullable|string|max:100',
            'karyawans' => 'required|array|min:1',
            'karyawans.*.vcNik' => 'required|string|max:10|exists:m_karyawan,Nik',
            'karyawans.*.vcKlasifikasiGrade' => 'nullable|string|max:50',
            'jadwals' => 'required|array|min:1',
            'jadwals.*.vcModaPerjalanan' => 'required|string|max:50',
            'jadwals.*.dtTanggalBerangkat' => 'nullable|date',
            'jadwals.*.dtJamBerangkat' => 'nullable|date_format:H:i',
            'jadwals.*.dtTanggalKembali' => 'nullable|date',
            'jadwals.*.dtJamKembali' => 'nullable|date_format:H:i',
            'hotels' => 'nullable|array',
            'hotels.*.isMenginap' => 'nullable|boolean',
            'hotels.*.dtTanggalMenginap' => 'nullable|date',
            'hotels.*.vcKotaProvinsiNegara' => 'nullable|string|max:200',
            'hotels.*.vcNamaHotel' => 'nullable|string|max:200',
        ], [
            'dtTanggalForm.required' => 'Tanggal Form Dinas harus diisi',
            'dtTanggalDinasDari.required' => 'Tanggal Mulai Dinas harus diisi',
            'dtTanggalDinasSampai.required' => 'Tanggal Sampai Dinas harus diisi',
            'dtTanggalDinasSampai.after_or_equal' => 'Tanggal Sampai Dinas harus sama atau setelah Tanggal Mulai Dinas',
            'vcPemberiTugas.required' => 'Pemberi Tugas harus diisi',
            'vcTujuanDinas.required' => 'Tujuan Dinas harus diisi',
            'karyawans.required' => 'Minimal harus ada 1 karyawan yang ditugaskan',
            'karyawans.min' => 'Minimal harus ada 1 karyawan yang ditugaskan',
            'jadwals.required' => 'Minimal harus ada 1 jadwal perjalanan',
            'jadwals.min' => 'Minimal harus ada 1 jadwal perjalanan',
        ]);

        DB::beginTransaction();
        try {
            // Generate No RPD
            $noRpd = $this->generateNoRpd($request->dtTanggalForm);

            // Calculate durasi hari jika tanggal dari dan sampai diisi
            $durasiHari = null;
            if ($request->dtTanggalDinasDari && $request->dtTanggalDinasSampai) {
                $tanggalDari = Carbon::parse($request->dtTanggalDinasDari);
                $tanggalSampai = Carbon::parse($request->dtTanggalDinasSampai);
                $durasiHari = $tanggalDari->diffInDays($tanggalSampai) + 1; // +1 untuk include hari pertama
            } elseif ($request->intDurasiHari) {
                $durasiHari = (int) $request->intDurasiHari;
            }

            // Create Header
            $header = PerjalananDinasHeader::create([
                'vcNoRpd' => $noRpd,
                'dtTanggalForm' => $request->dtTanggalForm,
                'dtTanggalDinasDari' => $request->dtTanggalDinasDari,
                'dtTanggalDinasSampai' => $request->dtTanggalDinasSampai,
                'intDurasiHari' => $durasiHari,
                'vcPemberiTugas' => substr($request->vcPemberiTugas, 0, 100),
                'vcJabatanPemberiTugas' => $request->vcJabatanPemberiTugas ? substr($request->vcJabatanPemberiTugas, 0, 100) : null,
                'vcTujuanDinas' => substr($request->vcTujuanDinas, 0, 200),
                'vcMaksudPerjalananDinas' => $request->vcMaksudPerjalananDinas,
                'vcMengajukan' => $request->vcMengajukan ? substr($request->vcMengajukan, 0, 100) : null,
                'vcMenyetujui' => $request->vcMenyetujui ? substr($request->vcMenyetujui, 0, 100) : null,
                'vcMengetahui' => $request->vcMengetahui ? substr($request->vcMengetahui, 0, 100) : null,
                'dtCreate' => Carbon::now(),
                'dtChange' => Carbon::now(),
            ]);

            // Create Karyawans
            foreach ($request->karyawans as $index => $karyawan) {
                if (empty($karyawan['vcNik'])) continue;

                $karyawanData = Karyawan::where('Nik', $karyawan['vcNik'])->first();
                if (!$karyawanData) {
                    throw new \Exception('NIK ' . $karyawan['vcNik'] . ' tidak ditemukan');
                }

                $counterKaryawan = $this->generateCounter('karyawan', $noRpd, $index + 1);

                PerjalananDinasKaryawan::create([
                    'vcCounterKaryawan' => $counterKaryawan,
                    'vcNoRpd' => $noRpd,
                    'vcNik' => substr($karyawan['vcNik'], 0, 10),
                    'vcNamaKaryawan' => substr($karyawanData->Nama ?? '', 0, 100),
                    'vcKodeDept' => $karyawanData->dept ?? null,
                    'vcKodeJabatan' => $karyawanData->Jabat ? (strpos($karyawanData->Jabat, ' -> ') !== false ? trim(explode(' -> ', $karyawanData->Jabat)[0]) : $karyawanData->Jabat) : null,
                    'vcKlasifikasiGrade' => $karyawan['vcKlasifikasiGrade'] ? substr($karyawan['vcKlasifikasiGrade'], 0, 50) : null,
                    'dtCreate' => Carbon::now(),
                    'dtChange' => Carbon::now(),
                ]);
            }

            // Create Jadwals
            foreach ($request->jadwals as $index => $jadwal) {
                if (empty($jadwal['vcModaPerjalanan'])) continue;

                $counterJadwal = $this->generateCounter('jadwal', $noRpd, $index + 1);

                $hariBerangkat = null;
                $hariKembali = null;
                if (!empty($jadwal['dtTanggalBerangkat'])) {
                    $hariBerangkat = Carbon::parse($jadwal['dtTanggalBerangkat'])->locale('id')->dayName;
                }
                if (!empty($jadwal['dtTanggalKembali'])) {
                    $hariKembali = Carbon::parse($jadwal['dtTanggalKembali'])->locale('id')->dayName;
                }

                PerjalananDinasJadwal::create([
                    'vcCounterJadwal' => $counterJadwal,
                    'vcNoRpd' => $noRpd,
                    'vcModaPerjalanan' => substr($jadwal['vcModaPerjalanan'], 0, 50),
                    'vcHariBerangkat' => $hariBerangkat,
                    'dtTanggalBerangkat' => $jadwal['dtTanggalBerangkat'] ?? null,
                    'dtJamBerangkat' => $jadwal['dtJamBerangkat'] ?? null,
                    'vcKeteranganBerangkat' => $jadwal['vcKeteranganBerangkat'] ? substr($jadwal['vcKeteranganBerangkat'], 0, 200) : null,
                    'vcHariKembali' => $hariKembali,
                    'dtTanggalKembali' => $jadwal['dtTanggalKembali'] ?? null,
                    'dtJamKembali' => $jadwal['dtJamKembali'] ?? null,
                    'vcKeteranganKembali' => null, // Field dihapus, selalu null
                    'dtCreate' => Carbon::now(),
                    'dtChange' => Carbon::now(),
                ]);
            }

            // Create Hotels (optional)
            if (!empty($request->hotels)) {
                foreach ($request->hotels as $index => $hotel) {
                    if (empty($hotel['isMenginap']) || !$hotel['isMenginap']) continue;

                    $counterHotel = $this->generateCounter('hotel', $noRpd, $index + 1);

                    PerjalananDinasHotel::create([
                        'vcCounterHotel' => $counterHotel,
                        'vcNoRpd' => $noRpd,
                        'isMenginap' => true,
                        'dtTanggalMenginap' => $hotel['dtTanggalMenginap'] ?? null,
                        'vcKotaProvinsiNegara' => $hotel['vcKotaProvinsiNegara'] ? substr($hotel['vcKotaProvinsiNegara'], 0, 200) : null,
                        'vcNamaHotel' => $hotel['vcNamaHotel'] ? substr($hotel['vcNamaHotel'], 0, 200) : null,
                        'vcKeteranganHotel' => $hotel['vcKeteranganHotel'] ?? null,
                        'dtCreate' => Carbon::now(),
                        'dtChange' => Carbon::now(),
                    ]);
                }
            }

            // Create Tiba Kembali / Destinasi (optional)
            if (!empty($request->tiba_kembali)) {
                $tibaKembali = $request->tiba_kembali;
                $counterTibaKembali = $this->generateCounter('tiba_kembali', $noRpd, 1);

                $hariTiba = null;
                $hariKembali = null;
                if (!empty($tibaKembali['dtTanggalTiba'])) {
                    $hariTiba = Carbon::parse($tibaKembali['dtTanggalTiba'])->locale('id')->dayName;
                }
                if (!empty($tibaKembali['dtTanggalKembali'])) {
                    $hariKembali = Carbon::parse($tibaKembali['dtTanggalKembali'])->locale('id')->dayName;
                }

                PerjalananDinasTibaKembali::create([
                    'vcCounterTibaKembali' => $counterTibaKembali,
                    'vcNoRpd' => $noRpd,
                    'vcHariTiba' => $hariTiba,
                    'dtTanggalTiba' => $tibaKembali['dtTanggalTiba'] ?? null,
                    'dtJamTiba' => $tibaKembali['dtJamTiba'] ?? null,
                    'vcHariKembali' => $hariKembali,
                    'dtTanggalKembali' => $tibaKembali['dtTanggalKembali'] ?? null,
                    'dtJamKembali' => $tibaKembali['dtJamKembali'] ?? null,
                    'vcKeteranganKedatangan' => $tibaKembali['vcKeteranganKedatangan'] ?? null,
                    'vcTandaTanganPihakBerwenang' => $tibaKembali['vcTandaTanganPihakBerwenang'] ? substr($tibaKembali['vcTandaTanganPihakBerwenang'], 0, 100) : null,
                    'dtCreate' => Carbon::now(),
                    'dtChange' => Carbon::now(),
                ]);
            }

            DB::commit();

            // Reload header untuk memastikan data terbaru tersedia
            $header->refresh();
            $header->load(['karyawans.karyawan.shift']);

            // Auto-update absensi setelah save berhasil
            try {
                $this->updateAbsensiFromPerjalananDinas($header);
            } catch (\Exception $e) {
                // Log error tapi tidak gagalkan response karena form sudah tersimpan
                Log::error('Error updating absensi from Perjalanan Dinas: ' . $e->getMessage(), [
                    'no_rpd' => $header->vcNoRpd,
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Form Perjalanan Dinas berhasil disimpan',
                'data' => $header->load(['karyawans', 'jadwals', 'hotels', 'tibaKembali']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing Perjalanan Dinas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan Form Perjalanan Dinas: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $header = PerjalananDinasHeader::with([
            'karyawans.karyawan.divisi', 
            'karyawans.karyawan', 
            'karyawans.departemen', 
            'karyawans.jabatan', 
            'jadwals', 
            'hotels', 
            'tibaKembali'
        ])
            ->findOrFail($id);

        // Format dates untuk input type="date" (Y-m-d)
        $data = $header->toArray();
        
        // Format header dates
        if ($header->dtTanggalForm) {
            $data['dtTanggalForm'] = $header->dtTanggalForm->format('Y-m-d');
        }
        if ($header->dtTanggalDinasDari) {
            $data['dtTanggalDinasDari'] = $header->dtTanggalDinasDari->format('Y-m-d');
        }
        if ($header->dtTanggalDinasSampai) {
            $data['dtTanggalDinasSampai'] = $header->dtTanggalDinasSampai->format('Y-m-d');
        }

        // Format jadwal dates and times - ambil langsung dari model untuk menghindari cast issue
        if ($header->jadwals && $header->jadwals->count() > 0) {
            $jadwalsArray = [];
            foreach ($header->jadwals as $index => $jadwal) {
                $jadwalData = [
                    'vcCounterJadwal' => $jadwal->vcCounterJadwal,
                    'vcNoRpd' => $jadwal->vcNoRpd,
                    'vcModaPerjalanan' => $jadwal->vcModaPerjalanan,
                    'dtTanggalBerangkat' => $jadwal->dtTanggalBerangkat ? $jadwal->dtTanggalBerangkat->format('Y-m-d') : '',
                    'dtTanggalKembali' => $jadwal->dtTanggalKembali ? $jadwal->dtTanggalKembali->format('Y-m-d') : '',
                    'vcKeteranganBerangkat' => $jadwal->vcKeteranganBerangkat,
                ];
                
                // Format jam - karena sudah di-cast sebagai string, langsung ambil dan format
                if ($jadwal->dtJamBerangkat) {
                    $jamBerangkat = is_string($jadwal->dtJamBerangkat) 
                        ? $jadwal->dtJamBerangkat 
                        : (string) $jadwal->dtJamBerangkat;
                    $jadwalData['dtJamBerangkat'] = substr($jamBerangkat, 0, 5);
                } else {
                    $jadwalData['dtJamBerangkat'] = '';
                }
                
                if ($jadwal->dtJamKembali) {
                    $jamKembali = is_string($jadwal->dtJamKembali) 
                        ? $jadwal->dtJamKembali 
                        : (string) $jadwal->dtJamKembali;
                    $jadwalData['dtJamKembali'] = substr($jamKembali, 0, 5);
                } else {
                    $jadwalData['dtJamKembali'] = '';
                }
                
                $jadwalsArray[] = $jadwalData;
            }
            $data['jadwals'] = $jadwalsArray;
        }

        // Format hotel dates
        if (isset($data['hotels']) && is_array($data['hotels'])) {
            foreach ($data['hotels'] as $index => $hotel) {
                if (isset($hotel['dtTanggalMenginap']) && $hotel['dtTanggalMenginap']) {
                    $data['hotels'][$index]['dtTanggalMenginap'] = is_string($hotel['dtTanggalMenginap']) 
                        ? (strpos($hotel['dtTanggalMenginap'], ' ') !== false ? explode(' ', $hotel['dtTanggalMenginap'])[0] : $hotel['dtTanggalMenginap'])
                        : Carbon::parse($hotel['dtTanggalMenginap'])->format('Y-m-d');
                }
            }
        }

        // Format tiba_kembali dates and times
        if ($header->tibaKembali) {
            $tibaKembaliData = [
                'vcCounterTibaKembali' => $header->tibaKembali->vcCounterTibaKembali,
                'vcNoRpd' => $header->tibaKembali->vcNoRpd,
                'dtTanggalTiba' => $header->tibaKembali->dtTanggalTiba ? $header->tibaKembali->dtTanggalTiba->format('Y-m-d') : '',
                'dtTanggalKembali' => $header->tibaKembali->dtTanggalKembali ? $header->tibaKembali->dtTanggalKembali->format('Y-m-d') : '',
                'vcKeteranganKedatangan' => $header->tibaKembali->vcKeteranganKedatangan,
                'vcTandaTanganPihakBerwenang' => $header->tibaKembali->vcTandaTanganPihakBerwenang,
            ];
            
            // Format jam - karena sudah di-cast sebagai string, langsung ambil dan format
            if ($header->tibaKembali->dtJamTiba) {
                $jamTiba = is_string($header->tibaKembali->dtJamTiba) 
                    ? $header->tibaKembali->dtJamTiba 
                    : (string) $header->tibaKembali->dtJamTiba;
                $tibaKembaliData['dtJamTiba'] = substr($jamTiba, 0, 5);
            } else {
                $tibaKembaliData['dtJamTiba'] = '';
            }
            
            if ($header->tibaKembali->dtJamKembali) {
                $jamKembali = is_string($header->tibaKembali->dtJamKembali) 
                    ? $header->tibaKembali->dtJamKembali 
                    : (string) $header->tibaKembali->dtJamKembali;
                $tibaKembaliData['dtJamKembali'] = substr($jamKembali, 0, 5);
            } else {
                $tibaKembaliData['dtJamKembali'] = '';
            }
            
            $data['tiba_kembali'] = $tibaKembaliData;
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'dtTanggalForm' => 'required|date',
            'dtTanggalDinasDari' => 'required|date',
            'dtTanggalDinasSampai' => 'required|date|after_or_equal:dtTanggalDinasDari',
            'intDurasiHari' => 'nullable|integer|min:0',
            'vcPemberiTugas' => 'required|string|max:100',
            'vcJabatanPemberiTugas' => 'nullable|string|max:100',
            'vcTujuanDinas' => 'required|string|max:200',
            'vcMaksudPerjalananDinas' => 'nullable|string',
            'vcMengajukan' => 'nullable|string|max:100',
            'vcMenyetujui' => 'nullable|string|max:100',
            'vcMengetahui' => 'nullable|string|max:100',
            'karyawans' => 'required|array|min:1',
            'karyawans.*.vcNik' => 'required|string|max:10|exists:m_karyawan,Nik',
            'karyawans.*.vcKlasifikasiGrade' => 'nullable|string|max:50',
            'jadwals' => 'required|array|min:1',
            'jadwals.*.vcModaPerjalanan' => 'required|string|max:50',
            'jadwals.*.dtTanggalBerangkat' => 'nullable|date',
            'jadwals.*.dtJamBerangkat' => 'nullable|date_format:H:i',
            'jadwals.*.dtTanggalKembali' => 'nullable|date',
            'jadwals.*.dtJamKembali' => 'nullable|date_format:H:i',
            'hotels' => 'nullable|array',
            'hotels.*.isMenginap' => 'nullable|boolean',
            'hotels.*.dtTanggalMenginap' => 'nullable|date',
            'hotels.*.vcKotaProvinsiNegara' => 'nullable|string|max:200',
            'hotels.*.vcNamaHotel' => 'nullable|string|max:200',
        ]);

        DB::beginTransaction();
        try {
            $header = PerjalananDinasHeader::findOrFail($id);

            // Calculate durasi hari jika tanggal dari dan sampai diisi
            $durasiHari = null;
            if ($request->dtTanggalDinasDari && $request->dtTanggalDinasSampai) {
                $tanggalDari = Carbon::parse($request->dtTanggalDinasDari);
                $tanggalSampai = Carbon::parse($request->dtTanggalDinasSampai);
                $durasiHari = $tanggalDari->diffInDays($tanggalSampai) + 1; // +1 untuk include hari pertama
            } elseif ($request->intDurasiHari) {
                $durasiHari = (int) $request->intDurasiHari;
            }

            // Update Header
            $header->update([
                'dtTanggalForm' => $request->dtTanggalForm,
                'dtTanggalDinasDari' => $request->dtTanggalDinasDari,
                'dtTanggalDinasSampai' => $request->dtTanggalDinasSampai,
                'intDurasiHari' => $durasiHari,
                'vcPemberiTugas' => substr($request->vcPemberiTugas, 0, 100),
                'vcJabatanPemberiTugas' => $request->vcJabatanPemberiTugas ? substr($request->vcJabatanPemberiTugas, 0, 100) : null,
                'vcTujuanDinas' => substr($request->vcTujuanDinas, 0, 200),
                'vcMaksudPerjalananDinas' => $request->vcMaksudPerjalananDinas,
                'vcMengajukan' => $request->vcMengajukan ? substr($request->vcMengajukan, 0, 100) : null,
                'vcMenyetujui' => $request->vcMenyetujui ? substr($request->vcMenyetujui, 0, 100) : null,
                'vcMengetahui' => $request->vcMengetahui ? substr($request->vcMengetahui, 0, 100) : null,
                'dtChange' => Carbon::now(),
            ]);

            // Delete existing details
            PerjalananDinasKaryawan::where('vcNoRpd', $header->vcNoRpd)->delete();
            PerjalananDinasJadwal::where('vcNoRpd', $header->vcNoRpd)->delete();
            PerjalananDinasHotel::where('vcNoRpd', $header->vcNoRpd)->delete();

            // Recreate Karyawans
            foreach ($request->karyawans as $index => $karyawan) {
                if (empty($karyawan['vcNik'])) continue;

                $karyawanData = Karyawan::where('Nik', $karyawan['vcNik'])->first();
                if (!$karyawanData) continue;

                $counterKaryawan = $this->generateCounter('karyawan', $header->vcNoRpd, $index + 1);

                PerjalananDinasKaryawan::create([
                    'vcCounterKaryawan' => $counterKaryawan,
                    'vcNoRpd' => $header->vcNoRpd,
                    'vcNik' => substr($karyawan['vcNik'], 0, 10),
                    'vcNamaKaryawan' => substr($karyawanData->Nama ?? '', 0, 100),
                    'vcKodeDept' => $karyawanData->dept ?? null,
                    'vcKodeJabatan' => $karyawanData->Jabat ? (strpos($karyawanData->Jabat, ' -> ') !== false ? trim(explode(' -> ', $karyawanData->Jabat)[0]) : $karyawanData->Jabat) : null,
                    'vcKlasifikasiGrade' => $karyawan['vcKlasifikasiGrade'] ? substr($karyawan['vcKlasifikasiGrade'], 0, 50) : null,
                    'dtCreate' => Carbon::now(),
                    'dtChange' => Carbon::now(),
                ]);
            }

            // Recreate Jadwals
            foreach ($request->jadwals as $index => $jadwal) {
                if (empty($jadwal['vcModaPerjalanan'])) continue;

                $counterJadwal = $this->generateCounter('jadwal', $header->vcNoRpd, $index + 1);

                $hariBerangkat = null;
                $hariKembali = null;
                if (!empty($jadwal['dtTanggalBerangkat'])) {
                    $hariBerangkat = Carbon::parse($jadwal['dtTanggalBerangkat'])->locale('id')->dayName;
                }
                if (!empty($jadwal['dtTanggalKembali'])) {
                    $hariKembali = Carbon::parse($jadwal['dtTanggalKembali'])->locale('id')->dayName;
                }

                PerjalananDinasJadwal::create([
                    'vcCounterJadwal' => $counterJadwal,
                    'vcNoRpd' => $header->vcNoRpd,
                    'vcModaPerjalanan' => substr($jadwal['vcModaPerjalanan'], 0, 50),
                    'vcHariBerangkat' => $hariBerangkat,
                    'dtTanggalBerangkat' => $jadwal['dtTanggalBerangkat'] ?? null,
                    'dtJamBerangkat' => $jadwal['dtJamBerangkat'] ?? null,
                    'vcKeteranganBerangkat' => $jadwal['vcKeteranganBerangkat'] ? substr($jadwal['vcKeteranganBerangkat'], 0, 200) : null,
                    'vcHariKembali' => $hariKembali,
                    'dtTanggalKembali' => $jadwal['dtTanggalKembali'] ?? null,
                    'dtJamKembali' => $jadwal['dtJamKembali'] ?? null,
                    'vcKeteranganKembali' => null, // Field dihapus, selalu null
                    'dtCreate' => Carbon::now(),
                    'dtChange' => Carbon::now(),
                ]);
            }

            // Recreate Hotels
            if (!empty($request->hotels)) {
                foreach ($request->hotels as $index => $hotel) {
                    if (empty($hotel['isMenginap']) || !$hotel['isMenginap']) continue;

                    $counterHotel = $this->generateCounter('hotel', $header->vcNoRpd, $index + 1);

                    PerjalananDinasHotel::create([
                        'vcCounterHotel' => $counterHotel,
                        'vcNoRpd' => $header->vcNoRpd,
                        'isMenginap' => true,
                        'dtTanggalMenginap' => $hotel['dtTanggalMenginap'] ?? null,
                        'vcKotaProvinsiNegara' => $hotel['vcKotaProvinsiNegara'] ? substr($hotel['vcKotaProvinsiNegara'], 0, 200) : null,
                        'vcNamaHotel' => $hotel['vcNamaHotel'] ? substr($hotel['vcNamaHotel'], 0, 200) : null,
                        'vcKeteranganHotel' => $hotel['vcKeteranganHotel'] ?? null,
                        'dtCreate' => Carbon::now(),
                        'dtChange' => Carbon::now(),
                    ]);
                }
            }

            // Delete existing tiba_kembali
            PerjalananDinasTibaKembali::where('vcNoRpd', $header->vcNoRpd)->delete();

            // Recreate Tiba Kembali / Destinasi (optional)
            if (!empty($request->tiba_kembali)) {
                $tibaKembali = $request->tiba_kembali;
                $counterTibaKembali = $this->generateCounter('tiba_kembali', $header->vcNoRpd, 1);

                $hariTiba = null;
                $hariKembali = null;
                if (!empty($tibaKembali['dtTanggalTiba'])) {
                    $hariTiba = Carbon::parse($tibaKembali['dtTanggalTiba'])->locale('id')->dayName;
                }
                if (!empty($tibaKembali['dtTanggalKembali'])) {
                    $hariKembali = Carbon::parse($tibaKembali['dtTanggalKembali'])->locale('id')->dayName;
                }

                PerjalananDinasTibaKembali::create([
                    'vcCounterTibaKembali' => $counterTibaKembali,
                    'vcNoRpd' => $header->vcNoRpd,
                    'vcHariTiba' => $hariTiba,
                    'dtTanggalTiba' => $tibaKembali['dtTanggalTiba'] ?? null,
                    'dtJamTiba' => $tibaKembali['dtJamTiba'] ?? null,
                    'vcHariKembali' => $hariKembali,
                    'dtTanggalKembali' => $tibaKembali['dtTanggalKembali'] ?? null,
                    'dtJamKembali' => $tibaKembali['dtJamKembali'] ?? null,
                    'vcKeteranganKedatangan' => $tibaKembali['vcKeteranganKedatangan'] ?? null,
                    'vcTandaTanganPihakBerwenang' => $tibaKembali['vcTandaTanganPihakBerwenang'] ? substr($tibaKembali['vcTandaTanganPihakBerwenang'], 0, 100) : null,
                    'dtCreate' => Carbon::now(),
                    'dtChange' => Carbon::now(),
                ]);
            }

            DB::commit();

            // Reload header untuk memastikan data terbaru tersedia
            $header->refresh();
            $header->load(['karyawans.karyawan.shift']);

            // Auto-update absensi setelah update berhasil
            try {
                $this->updateAbsensiFromPerjalananDinas($header);
            } catch (\Exception $e) {
                // Log error tapi tidak gagalkan response karena form sudah terupdate
                Log::error('Error updating absensi from Perjalanan Dinas: ' . $e->getMessage(), [
                    'no_rpd' => $header->vcNoRpd,
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Form Perjalanan Dinas berhasil diupdate',
                'data' => $header->load(['karyawans', 'jadwals', 'hotels', 'tibaKembali']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating Perjalanan Dinas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate Form Perjalanan Dinas: ' . $e->getMessage(),
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
            $header = PerjalananDinasHeader::findOrFail($id);
            
            // Delete all related records (cascade)
            PerjalananDinasKaryawan::where('vcNoRpd', $header->vcNoRpd)->delete();
            PerjalananDinasJadwal::where('vcNoRpd', $header->vcNoRpd)->delete();
            PerjalananDinasHotel::where('vcNoRpd', $header->vcNoRpd)->delete();
            PerjalananDinasTibaKembali::where('vcNoRpd', $header->vcNoRpd)->delete();
            
            $header->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Form Perjalanan Dinas berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting Perjalanan Dinas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus Form Perjalanan Dinas: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate No RPD
     */
    private function generateNoRpd($tanggalForm)
    {
        $prefix = 'RPD';
        $datePart = Carbon::parse($tanggalForm)->format('Ymd');
        
        // Cari counter terakhir dengan prefix dan tanggal yang sama
        $lastRpd = PerjalananDinasHeader::where('vcNoRpd', 'like', $prefix . $datePart . '%')
            ->orderBy('vcNoRpd', 'desc')
            ->value('vcNoRpd');

        $counter = 1;
        if ($lastRpd) {
            $counterStr = substr($lastRpd, strlen($prefix . $datePart));
            $lastCounter = (int) $counterStr;
            if ($lastCounter > 0) {
                $counter = $lastCounter + 1;
            }
        }

        $counterFormatted = str_pad($counter, 3, '0', STR_PAD_LEFT);
        $noRpd = $prefix . $datePart . $counterFormatted;

        // Pastikan tidak ada duplikat
        if (PerjalananDinasHeader::where('vcNoRpd', $noRpd)->exists()) {
            $counter++;
            $counterFormatted = str_pad($counter, 3, '0', STR_PAD_LEFT);
            $noRpd = $prefix . $datePart . $counterFormatted;
        }

        return $noRpd;
    }

    /**
     * Generate Counter untuk detail
     */
    private function generateCounter($type, $noRpd, $index)
    {
        $prefix = strtoupper(substr($type, 0, 1));
        $datePart = Carbon::now()->format('Ymd');
        $indexPart = str_pad($index, 3, '0', STR_PAD_LEFT);
        
        return $prefix . $noRpd . $datePart . $indexPart;
    }

    /**
     * Print Form Perjalanan Dinas
     */
    public function print($id)
    {
        $header = PerjalananDinasHeader::with([
            'karyawans.karyawan.divisi', 
            'karyawans.karyawan', 
            'karyawans.departemen', 
            'karyawans.jabatan', 
            'jadwals', 
            'hotels', 
            'tibaKembali'
        ])->findOrFail($id);

        return view('perjalanan-dinas.print', compact('header'));
    }

    /**
     * Get karyawan data for autocomplete
     */
    public function getKaryawanData(Request $request)
    {
        $nik = $request->get('nik');
        
        if (!$nik) {
            return response()->json([
                'success' => false,
                'message' => 'NIK harus diisi',
            ], 422);
        }

        $karyawan = Karyawan::where('Nik', $nik)
            ->with(['departemen', 'jabatan', 'bagian', 'divisi'])
            ->first();

        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'nik' => $karyawan->Nik,
                'nama' => $karyawan->Nama,
                'bisnis_unit' => $karyawan->divisi ? $karyawan->divisi->vcNamaDivisi : ($karyawan->Divisi ?? '-'),
                'kode_divisi' => $karyawan->Divisi ?? null,
                'departemen' => $karyawan->departemen ? $karyawan->departemen->vcNamaDept : ($karyawan->dept ?? '-'),
                'jabatan' => $karyawan->jabatan ? $karyawan->jabatan->vcNamaJabatan : ($karyawan->Jabat ?? '-'),
                'kode_jabatan' => $karyawan->Jabat ? (strpos($karyawan->Jabat, ' -> ') !== false ? trim(explode(' -> ', $karyawan->Jabat)[0]) : $karyawan->Jabat) : null,
                'kode_dept' => $karyawan->dept ?? null,
            ],
        ]);
    }

    /**
     * Trigger update absensi secara manual (untuk testing/debugging)
     */
    public function triggerUpdateAbsensi($id)
    {
        try {
            $header = PerjalananDinasHeader::with(['karyawans.karyawan.shift'])->findOrFail($id);
            
            $this->updateAbsensiFromPerjalananDinas($header);
            
            return response()->json([
                'success' => true,
                'message' => 'Update absensi berhasil dijalankan. Cek log untuk detail.',
                'no_rpd' => $header->vcNoRpd,
            ]);
        } catch (\Exception $e) {
            Log::error('Error triggering update absensi: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Auto-update absensi berdasarkan Form Perjalanan Dinas
     * Update t_absen.dtJamMasuk dan dtJamKeluar sesuai shift karyawan
     * Hanya untuk hari kerja (Senin-Jumat) dan karyawan aktif yang punya shift
     */
    private function updateAbsensiFromPerjalananDinas(PerjalananDinasHeader $header)
    {
        Log::info('PerjalananDinas: Method updateAbsensiFromPerjalananDinas dipanggil', [
            'no_rpd' => $header->vcNoRpd,
            'tanggal_dari' => $header->dtTanggalDinasDari,
            'tanggal_sampai' => $header->dtTanggalDinasSampai,
        ]);
        
        // Validasi: harus ada tanggal dinas dari dan sampai
        if (!$header->dtTanggalDinasDari || !$header->dtTanggalDinasSampai) {
            Log::info('PerjalananDinas: Skip update absensi - tanggal dinas tidak lengkap', [
                'no_rpd' => $header->vcNoRpd,
                'tanggal_dari' => $header->dtTanggalDinasDari,
                'tanggal_sampai' => $header->dtTanggalDinasSampai,
            ]);
            return;
        }

        $tanggalDari = Carbon::parse($header->dtTanggalDinasDari);
        $tanggalSampai = Carbon::parse($header->dtTanggalDinasSampai);
        $noRpd = $header->vcNoRpd;
        $keterangan = 'Dinas Luar ' . $noRpd;

        Log::info('PerjalananDinas: Start update absensi', [
            'no_rpd' => $noRpd,
            'tanggal_dari' => $tanggalDari->format('Y-m-d'),
            'tanggal_sampai' => $tanggalSampai->format('Y-m-d'),
        ]);

        // Ambil semua karyawan dari form perjalanan dinas
        $karyawans = PerjalananDinasKaryawan::where('vcNoRpd', $noRpd)
            ->with(['karyawan.shift'])
            ->get();

        Log::info('PerjalananDinas: Jumlah karyawan ditemukan', [
            'no_rpd' => $noRpd,
            'jumlah' => $karyawans->count(),
            'karyawans' => $karyawans->map(function($k) {
                return [
                    'nik' => $k->vcNik,
                    'nama' => $k->vcNamaKaryawan,
                    'karyawan_exists' => $k->karyawan ? true : false,
                    'karyawan_aktif' => $k->karyawan ? $k->karyawan->vcAktif : null,
                    'karyawan_shift_code' => $k->karyawan ? $k->karyawan->vcShift : null,
                    'shift_exists' => $k->karyawan && $k->karyawan->shift ? true : false,
                ];
            })->toArray(),
        ]);

        $totalUpdated = 0;
        $totalSkipped = 0;

        foreach ($karyawans as $pdKaryawan) {
            $karyawan = $pdKaryawan->karyawan;
            
            // Skip jika karyawan tidak aktif
            if (!$karyawan) {
                Log::info('PerjalananDinas: Skip - karyawan tidak ditemukan', [
                    'no_rpd' => $noRpd,
                    'nik' => $pdKaryawan->vcNik,
                ]);
                $totalSkipped++;
                continue;
            }

            if ($karyawan->vcAktif != '1') {
                Log::info('PerjalananDinas: Skip - karyawan tidak aktif', [
                    'no_rpd' => $noRpd,
                    'nik' => $karyawan->Nik,
                    'nama' => $karyawan->Nama,
                ]);
                $totalSkipped++;
                continue;
            }

            // Skip jika karyawan tidak punya shift
            if (!$karyawan->vcShift) {
                Log::info('PerjalananDinas: Skip - karyawan tidak punya shift code', [
                    'no_rpd' => $noRpd,
                    'nik' => $karyawan->Nik,
                    'nama' => $karyawan->Nama,
                ]);
                $totalSkipped++;
                continue;
            }

            // Load shift jika belum ter-load
            if (!$karyawan->relationLoaded('shift')) {
                $karyawan->load('shift');
            }

            if (!$karyawan->shift) {
                Log::info('PerjalananDinas: Skip - shift tidak ditemukan', [
                    'no_rpd' => $noRpd,
                    'nik' => $karyawan->Nik,
                    'nama' => $karyawan->Nama,
                    'vcShift' => $karyawan->vcShift,
                ]);
                $totalSkipped++;
                continue;
            }

            // Ambil jam shift (format HH:mm, tambahkan :00 untuk jadi HH:mm:ss)
            $jamMasuk = $karyawan->shift->vcMasuk;
            $jamPulang = $karyawan->shift->vcPulang;

            // Skip jika jam shift tidak ada
            if (!$jamMasuk || !$jamPulang) {
                Log::info('PerjalananDinas: Skip - jam shift tidak ada', [
                    'no_rpd' => $noRpd,
                    'nik' => $karyawan->Nik,
                    'nama' => $karyawan->Nama,
                    'jam_masuk' => $jamMasuk,
                    'jam_pulang' => $jamPulang,
                ]);
                $totalSkipped++;
                continue;
            }

            // Konversi format jam dari datetime/H:i ke string HH:mm:ss
            $jamMasukFormatted = null;
            $jamPulangFormatted = null;

            // Handle jam masuk
            if ($jamMasuk instanceof \DateTime || $jamMasuk instanceof \Carbon\Carbon) {
                $jamMasukFormatted = $jamMasuk->format('H:i:s');
            } elseif (is_string($jamMasuk)) {
                // Jika sudah format HH:mm:ss, gunakan langsung
                if (strlen($jamMasuk) >= 8 && substr_count($jamMasuk, ':') == 2) {
                    $jamMasukFormatted = $jamMasuk;
                } elseif (strlen($jamMasuk) >= 5 && substr_count($jamMasuk, ':') == 1) {
                    // Format HH:mm, tambahkan :00
                    $jamMasukFormatted = $jamMasuk . ':00';
                } else {
                    Log::info('PerjalananDinas: Skip - format jam masuk tidak valid', [
                        'no_rpd' => $noRpd,
                        'nik' => $karyawan->Nik,
                        'nama' => $karyawan->Nama,
                        'jam_masuk' => $jamMasuk,
                        'type' => gettype($jamMasuk),
                    ]);
                    $totalSkipped++;
                    continue; // Skip jika format tidak valid
                }
            } else {
                Log::info('PerjalananDinas: Skip - format jam masuk tidak valid (bukan string/datetime)', [
                    'no_rpd' => $noRpd,
                    'nik' => $karyawan->Nik,
                    'nama' => $karyawan->Nama,
                    'jam_masuk' => $jamMasuk,
                    'type' => gettype($jamMasuk),
                ]);
                $totalSkipped++;
                continue; // Skip jika format tidak valid
            }

            // Handle jam pulang
            if ($jamPulang instanceof \DateTime || $jamPulang instanceof \Carbon\Carbon) {
                $jamPulangFormatted = $jamPulang->format('H:i:s');
            } elseif (is_string($jamPulang)) {
                // Jika sudah format HH:mm:ss, gunakan langsung
                if (strlen($jamPulang) >= 8 && substr_count($jamPulang, ':') == 2) {
                    $jamPulangFormatted = $jamPulang;
                } elseif (strlen($jamPulang) >= 5 && substr_count($jamPulang, ':') == 1) {
                    // Format HH:mm, tambahkan :00
                    $jamPulangFormatted = $jamPulang . ':00';
                } else {
                    Log::info('PerjalananDinas: Skip - format jam pulang tidak valid', [
                        'no_rpd' => $noRpd,
                        'nik' => $karyawan->Nik,
                        'nama' => $karyawan->Nama,
                        'jam_pulang' => $jamPulang,
                        'type' => gettype($jamPulang),
                    ]);
                    $totalSkipped++;
                    continue; // Skip jika format tidak valid
                }
            } else {
                Log::info('PerjalananDinas: Skip - format jam pulang tidak valid (bukan string/datetime)', [
                    'no_rpd' => $noRpd,
                    'nik' => $karyawan->Nik,
                    'nama' => $karyawan->Nama,
                    'jam_pulang' => $jamPulang,
                    'type' => gettype($jamPulang),
                ]);
                $totalSkipped++;
                continue; // Skip jika format tidak valid
            }

            Log::info('PerjalananDinas: Processing karyawan', [
                'no_rpd' => $noRpd,
                'nik' => $karyawan->Nik,
                'nama' => $karyawan->Nama,
                'jam_masuk_formatted' => $jamMasukFormatted,
                'jam_pulang_formatted' => $jamPulangFormatted,
            ]);

            // Loop setiap tanggal dari tanggal dinas dari sampai tanggal dinas sampai
            $currentDate = $tanggalDari->copy();
            Log::info('PerjalananDinas: Mulai loop tanggal', [
                'no_rpd' => $noRpd,
                'nik' => $karyawan->Nik,
                'tanggal_dari' => $tanggalDari->format('Y-m-d'),
                'tanggal_sampai' => $tanggalSampai->format('Y-m-d'),
            ]);
            
            while ($currentDate->lte($tanggalSampai)) {
                // Hanya update untuk hari kerja (Senin-Jumat, dayOfWeek 1-5)
                $dayOfWeek = $currentDate->dayOfWeek; // 0=Sunday, 1=Monday, ..., 6=Saturday
                $tanggal = $currentDate->format('Y-m-d');
                $hariNama = $currentDate->locale('id')->dayName;
                
                Log::info('PerjalananDinas: Cek tanggal', [
                    'no_rpd' => $noRpd,
                    'nik' => $karyawan->Nik,
                    'tanggal' => $tanggal,
                    'hari' => $hariNama,
                    'day_of_week' => $dayOfWeek,
                    'is_weekday' => ($dayOfWeek >= 1 && $dayOfWeek <= 5),
                ]);
                
                if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                    $nik = $karyawan->Nik;

                    // Cek apakah sudah ada record absen
                    $absen = Absen::where('dtTanggal', $tanggal)
                        ->where('vcNik', $nik)
                        ->first();

                    Log::info('PerjalananDinas: Cek record absen', [
                        'no_rpd' => $noRpd,
                        'nik' => $nik,
                        'tanggal' => $tanggal,
                        'absen_exists' => $absen ? true : false,
                    ]);

                    try {
                        if ($absen) {
                            // Update existing record menggunakan query builder karena composite primary key
                            Log::info('PerjalananDinas: Mencoba update absensi', [
                                'no_rpd' => $noRpd,
                                'nik' => $nik,
                                'tanggal' => $tanggal,
                                'jam_masuk' => $jamMasukFormatted,
                                'jam_keluar' => $jamPulangFormatted,
                            ]);
                            
                            DB::table('t_absen')
                                ->where('dtTanggal', $tanggal)
                                ->where('vcNik', $nik)
                                ->update([
                                    'dtJamMasuk' => $jamMasukFormatted,
                                    'dtJamKeluar' => $jamPulangFormatted,
                                    'vcketerangan' => $keterangan,
                                    'dtChange' => Carbon::now(),
                                ]);
                            
                            $totalUpdated++;
                            Log::info('PerjalananDinas: Update absensi berhasil', [
                                'no_rpd' => $noRpd,
                                'nik' => $nik,
                                'tanggal' => $tanggal,
                                'jam_masuk' => $jamMasukFormatted,
                                'jam_keluar' => $jamPulangFormatted,
                            ]);
                        } else {
                            // Insert new record menggunakan query builder karena composite primary key
                            Log::info('PerjalananDinas: Mencoba insert absensi', [
                                'no_rpd' => $noRpd,
                                'nik' => $nik,
                                'tanggal' => $tanggal,
                                'jam_masuk' => $jamMasukFormatted,
                                'jam_keluar' => $jamPulangFormatted,
                            ]);
                            
                            DB::table('t_absen')->insert([
                                'dtTanggal' => $tanggal,
                                'vcNik' => $nik,
                                'dtJamMasuk' => $jamMasukFormatted,
                                'dtJamKeluar' => $jamPulangFormatted,
                                'dtJamMasukLembur' => null,
                                'dtJamKeluarLembur' => null,
                                'intDurasiIstirahat' => 0,
                                'vcCounter' => null,
                                'vcCfmLembur' => null,
                                'vcketerangan' => $keterangan,
                                'dtCreate' => Carbon::now(),
                                'dtChange' => Carbon::now(),
                            ]);
                            
                            $totalUpdated++;
                            Log::info('PerjalananDinas: Insert absensi berhasil', [
                                'no_rpd' => $noRpd,
                                'nik' => $nik,
                                'tanggal' => $tanggal,
                                'jam_masuk' => $jamMasukFormatted,
                                'jam_keluar' => $jamPulangFormatted,
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('PerjalananDinas: Error update/insert absensi', [
                            'no_rpd' => $noRpd,
                            'nik' => $nik,
                            'tanggal' => $tanggal,
                            'error' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                } else {
                    Log::info('PerjalananDinas: Skip - bukan hari kerja', [
                        'no_rpd' => $noRpd,
                        'nik' => $karyawan->Nik,
                        'tanggal' => $tanggal,
                        'hari' => $hariNama,
                        'day_of_week' => $dayOfWeek,
                    ]);
                }

                // Pindah ke tanggal berikutnya
                $currentDate->addDay();
            }
        }

        Log::info('PerjalananDinas: Selesai update absensi', [
            'no_rpd' => $noRpd,
            'total_updated' => $totalUpdated,
            'total_skipped' => $totalSkipped,
        ]);
        
        // Verifikasi: cek apakah data benar-benar ter-insert/update
        if ($totalUpdated > 0) {
            $verification = Absen::where('vcketerangan', 'like', 'Dinas Luar ' . $noRpd . '%')
                ->whereBetween('dtTanggal', [$tanggalDari->format('Y-m-d'), $tanggalSampai->format('Y-m-d')])
                ->count();
            
            Log::info('PerjalananDinas: Verifikasi data di database', [
                'no_rpd' => $noRpd,
                'total_updated' => $totalUpdated,
                'total_verification' => $verification,
            ]);
        }
    }
}
