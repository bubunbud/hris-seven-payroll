<?php

namespace App\Http\Controllers;

use App\Models\JadwalShiftSecurity;
use App\Models\Karyawan;
use App\Models\ShiftSecurity;
use App\Models\OverrideJadwalSecurity;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class JadwalShiftSecurityController extends Controller
{
    /**
     * Display form input jadwal shift security
     */
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        $filterNama = $request->get('filter_nama', '');

        // Get all security employees dengan filter
        $satpamsQuery = Karyawan::where('Group_pegawai', 'Security')
            ->where('vcAktif', '1');

        // Apply filter NIK/Nama jika ada
        if (!empty($filterNama)) {
            $satpamsQuery->where(function ($q) use ($filterNama) {
                $q->where('Nik', 'LIKE', '%' . $filterNama . '%')
                    ->orWhere('Nama', 'LIKE', '%' . $filterNama . '%');
            });
        }

        $satpams = $satpamsQuery->orderBy('Nama')
            ->get(['Nik', 'Nama']);

        if ($satpams->isEmpty()) {
            return view('jadwal-shift-security.index', [
                'satpams' => collect(),
                'jadwal' => [],
                'tanggalList' => [],
                'bulan' => $bulan,
                'tahun' => $tahun,
                'tanggalAwal' => Carbon::create($tahun, $bulan, 1),
                'tanggalAkhir' => Carbon::create($tahun, $bulan, 1)->endOfMonth(),
                'filterNama' => $filterNama,
            ])->with('warning', !empty($filterNama)
                ? 'Tidak ada data satpam yang sesuai dengan filter "' . $filterNama . '"'
                : 'Tidak ada data satpam dengan Group_pegawai = Security');
        }

        // Get jadwal untuk periode ini
        $tanggalAwal = Carbon::create($tahun, $bulan, 1);
        $tanggalAkhir = $tanggalAwal->copy()->endOfMonth();

        // Get jadwal untuk periode ini (bisa kosong jika belum ada data)
        $jadwalRaw = JadwalShiftSecurity::whereBetween('dtTanggal', [$tanggalAwal->format('Y-m-d'), $tanggalAkhir->format('Y-m-d')])
            ->get();

        // Group by NIK and Tanggal, collect shifts
        $jadwal = [];
        foreach ($jadwalRaw as $j) {
            $nik = $j->vcNik;
            $tanggal = $j->dtTanggal instanceof Carbon
                ? $j->dtTanggal->format('Y-m-d')
                : (is_string($j->dtTanggal) ? $j->dtTanggal : Carbon::parse($j->dtTanggal)->format('Y-m-d'));
            if (!isset($jadwal[$nik])) {
                $jadwal[$nik] = [];
            }
            if (!isset($jadwal[$nik][$tanggal])) {
                $jadwal[$nik][$tanggal] = collect();
            }
            $jadwal[$nik][$tanggal]->push($j);
        }

        // Get hari libur untuk highlight
        $hariLibur = HariLibur::whereBetween('dtTanggal', [$tanggalAwal->format('Y-m-d'), $tanggalAkhir->format('Y-m-d')])
            ->pluck('dtTanggal')
            ->map(function ($tanggal) {
                return $tanggal instanceof Carbon ? $tanggal->format('Y-m-d') : Carbon::parse($tanggal)->format('Y-m-d');
            })
            ->toArray();

        // Generate array tanggal dalam bulan
        $tanggalList = [];
        $current = $tanggalAwal->copy();
        while ($current->lte($tanggalAkhir)) {
            $tanggalList[] = [
                'tanggal' => $current->format('Y-m-d'),
                'hari' => $current->format('d'),
                'nama_hari' => $current->format('D'),
                'is_weekend' => $current->isWeekend(),
                'is_libur' => in_array($current->format('Y-m-d'), $hariLibur),
            ];
            $current->addDay();
        }

        return view('jadwal-shift-security.index', compact(
            'satpams',
            'jadwal',
            'tanggalList',
            'bulan',
            'tahun',
            'tanggalAwal',
            'tanggalAkhir',
            'filterNama'
        ));
    }

    /**
     * Store jadwal shift (bulk)
     */
    public function store(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
            'jadwal' => 'required|string', // JSON string
        ]);

        DB::beginTransaction();
        try {
            $bulan = $request->bulan;
            $tahun = $request->tahun;
            $tanggalAwal = Carbon::create($tahun, $bulan, 1);
            $tanggalAkhir = $tanggalAwal->copy()->endOfMonth();

            // Hapus jadwal lama untuk periode ini (jika ada)
            JadwalShiftSecurity::whereBetween('dtTanggal', [$tanggalAwal->format('Y-m-d'), $tanggalAkhir->format('Y-m-d')])
                ->delete();

            // Insert jadwal baru
            $now = Carbon::now();
            $dataInsert = [];

            // Parse JSON jadwal dari request
            $jadwalArray = json_decode($request->jadwal, true);

            if (!is_array($jadwalArray)) {
                throw new \Exception('Format jadwal tidak valid');
            }

            foreach ($jadwalArray as $item) {
                if (!isset($item['vcNik']) || !isset($item['dtTanggal'])) {
                    continue;
                }

                // Handle "OFF" - simpan dengan intShift = NULL dan vcKeterangan = 'OFF'
                if (isset($item['isOff']) && $item['isOff'] === true) {
                    $dataInsert[] = [
                        'vcNik' => $item['vcNik'],
                        'dtTanggal' => $item['dtTanggal'],
                        'intShift' => null,
                        'vcKeterangan' => 'OFF',
                        'isOverride' => false,
                        'vcOverrideBy' => null,
                        'dtOverrideAt' => null,
                        'dtCreate' => $now,
                        'dtChange' => $now,
                    ];
                    continue;
                }

                // Handle shift normal (1, 2, 3)
                if (!isset($item['intShift'])) {
                    continue;
                }

                $shifts = is_array($item['intShift']) ? $item['intShift'] : [$item['intShift']];
                foreach ($shifts as $shift) {
                    $shiftInt = (int)$shift;
                    if ($shiftInt >= 1 && $shiftInt <= 3) {
                        $dataInsert[] = [
                            'vcNik' => $item['vcNik'],
                            'dtTanggal' => $item['dtTanggal'],
                            'intShift' => $shiftInt,
                            'vcKeterangan' => null,
                            'isOverride' => false,
                            'vcOverrideBy' => null,
                            'dtOverrideAt' => null,
                            'dtCreate' => $now,
                            'dtChange' => $now,
                        ];
                    }
                }
            }

            if (!empty($dataInsert)) {
                DB::table('t_jadwal_shift_security')->insert($dataInsert);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal shift berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing jadwal shift security: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Override jadwal untuk kasus urgent
     */
    public function override(Request $request)
    {
        $request->validate([
            'vcNik' => 'required|string|exists:m_karyawan,Nik',
            'dtTanggal' => 'required|date',
            'intShiftLama' => 'nullable|integer|in:1,2,3',
            'intShiftBaru' => 'required|integer|in:1,2,3',
            'vcAlasan' => 'required|string|min:10|max:500',
        ], [
            'vcNik.exists' => 'NIK tidak ditemukan di database',
            'intShiftBaru.required' => 'Shift Baru harus dipilih',
            'intShiftBaru.in' => 'Shift Baru harus 1, 2, atau 3',
            'vcAlasan.required' => 'Alasan override wajib diisi',
            'vcAlasan.min' => 'Alasan override minimal 10 karakter',
            'vcAlasan.max' => 'Alasan override maksimal 500 karakter',
        ]);

        DB::beginTransaction();
        try {
            $user = auth()->user();
            $now = Carbon::now();

            // Hapus jadwal lama jika ada (bisa multiple jika sebelumnya ada multiple shift)
            if ($request->intShiftLama) {
                JadwalShiftSecurity::where('vcNik', $request->vcNik)
                    ->where('dtTanggal', $request->dtTanggal)
                    ->where('intShift', $request->intShiftLama)
                    ->delete();
            } else {
                // Jika intShiftLama kosong, berarti menambah shift baru (tidak hapus yang lama)
                // Tapi jika shift baru sudah ada, hapus dulu untuk menghindari duplikasi
                JadwalShiftSecurity::where('vcNik', $request->vcNik)
                    ->where('dtTanggal', $request->dtTanggal)
                    ->where('intShift', $request->intShiftBaru)
                    ->delete();
            }

            // Insert jadwal baru dengan flag override
            JadwalShiftSecurity::create([
                'vcNik' => $request->vcNik,
                'dtTanggal' => $request->dtTanggal,
                'intShift' => $request->intShiftBaru,
                'vcKeterangan' => 'Override: ' . $request->vcAlasan,
                'isOverride' => true,
                'vcOverrideBy' => $user->name ?? $user->email ?? 'System',
                'dtOverrideAt' => $now,
                'dtCreate' => $now,
                'dtChange' => $now,
            ]);

            // Simpan log override
            OverrideJadwalSecurity::create([
                'vcNik' => $request->vcNik,
                'dtTanggal' => $request->dtTanggal,
                'intShiftLama' => $request->intShiftLama,
                'intShiftBaru' => $request->intShiftBaru,
                'vcAlasan' => $request->vcAlasan,
                'vcOverrideBy' => $user->name ?? $user->email ?? 'System',
                'dtOverrideAt' => $now,
                'dtCreate' => $now,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil di-override'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error overriding jadwal shift security: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get jadwal by periode (API)
     */
    public function getJadwalByPeriode(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        $tanggalAwal = Carbon::create($request->tahun, $request->bulan, 1);
        $tanggalAkhir = $tanggalAwal->copy()->endOfMonth();

        $jadwal = JadwalShiftSecurity::whereBetween('dtTanggal', [$tanggalAwal->format('Y-m-d'), $tanggalAkhir->format('Y-m-d')])
            ->with('karyawan', 'shiftSecurity')
            ->get()
            ->groupBy(['vcNik', 'dtTanggal']);

        return response()->json([
            'success' => true,
            'jadwal' => $jadwal
        ]);
    }

    /**
     * Copy jadwal dari bulan sebelumnya
     */
    public function copyFromPreviousMonth(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        DB::beginTransaction();
        try {
            $bulan = $request->bulan;
            $tahun = $request->tahun;

            // Hitung bulan sebelumnya
            $tanggalBulanIni = Carbon::create($tahun, $bulan, 1);
            $tanggalBulanSebelumnya = $tanggalBulanIni->copy()->subMonth();
            $bulanSebelumnya = $tanggalBulanSebelumnya->month;
            $tahunSebelumnya = $tanggalBulanSebelumnya->year;

            // Ambil jadwal bulan sebelumnya
            $tanggalAwalSebelumnya = Carbon::create($tahunSebelumnya, $bulanSebelumnya, 1);
            $tanggalAkhirSebelumnya = $tanggalAwalSebelumnya->copy()->endOfMonth();

            $jadwalSebelumnya = JadwalShiftSecurity::whereBetween('dtTanggal', [
                $tanggalAwalSebelumnya->format('Y-m-d'),
                $tanggalAkhirSebelumnya->format('Y-m-d')
            ])->get();

            if ($jadwalSebelumnya->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada jadwal di bulan sebelumnya'
                ], 404);
            }

            // Hapus jadwal bulan ini (jika ada)
            $tanggalAwalBulanIni = Carbon::create($tahun, $bulan, 1);
            $tanggalAkhirBulanIni = $tanggalAwalBulanIni->copy()->endOfMonth();
            JadwalShiftSecurity::whereBetween('dtTanggal', [
                $tanggalAwalBulanIni->format('Y-m-d'),
                $tanggalAkhirBulanIni->format('Y-m-d')
            ])->delete();

            // Copy jadwal dengan tanggal baru
            $now = Carbon::now();
            $dataInsert = [];

            foreach ($jadwalSebelumnya as $jadwal) {
                $tanggalLama = Carbon::parse($jadwal->dtTanggal);
                $tanggalBaru = Carbon::create($tahun, $bulan, $tanggalLama->day);

                // Skip jika tanggal baru tidak valid (misal 31 Februari)
                if ($tanggalBaru->month != $bulan) {
                    continue;
                }

                $dataInsert[] = [
                    'vcNik' => $jadwal->vcNik,
                    'dtTanggal' => $tanggalBaru->format('Y-m-d'),
                    'intShift' => $jadwal->intShift,
                    'vcKeterangan' => $jadwal->vcKeterangan,
                    'isOverride' => false, // Reset override flag
                    'vcOverrideBy' => null,
                    'dtOverrideAt' => null,
                    'dtCreate' => $now,
                    'dtChange' => $now,
                ];
            }

            if (!empty($dataInsert)) {
                DB::table('t_jadwal_shift_security')->insert($dataInsert);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil di-copy dari bulan sebelumnya (' . $bulanSebelumnya . '/' . $tahunSebelumnya . ')',
                'copied_count' => count($dataInsert)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error copying jadwal from previous month: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import jadwal dari Excel/CSV
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx,xls|max:10240', // max 10MB
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ], [
            'file.required' => 'File harus dipilih',
            'file.mimes' => 'File harus berformat CSV, TXT, XLSX, atau XLS',
            'file.max' => 'Ukuran file maksimal 10MB',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $tanggalAwal = Carbon::create($tahun, $bulan, 1);
        $tanggalAkhir = $tanggalAwal->copy()->endOfMonth();

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            // Hapus jadwal lama untuk periode ini (optional, bisa di-comment jika ingin merge)
            // JadwalShiftSecurity::whereBetween('dtTanggal', [$tanggalAwal->format('Y-m-d'), $tanggalAkhir->format('Y-m-d')])->delete();

            $now = Carbon::now();
            $dataInsert = [];

            if (in_array(strtolower($extension), ['csv', 'txt'])) {
                // Parse CSV
                $handle = fopen($file->getRealPath(), 'r');
                $rowNumber = 0;
                $skipHeader = true;

                while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                    $rowNumber++;

                    // Skip header row
                    if ($skipHeader && $rowNumber === 1) {
                        continue;
                    }

                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    // Expected format: NIK, Tanggal (Y-m-d atau d/m/Y), Shift (1/2/3/OFF), Keterangan (optional)
                    if (count($row) < 3) {
                        $errors[] = "Baris $rowNumber: Kolom tidak lengkap (minimal: NIK, Tanggal, Shift)";
                        $errorCount++;
                        continue;
                    }

                    $nik = trim($row[0]);
                    $tanggalStr = trim($row[1]);
                    $shiftStr = trim($row[2]);
                    $keterangan = isset($row[3]) ? trim($row[3]) : null;

                    // Validasi NIK
                    if (empty($nik)) {
                        $errors[] = "Baris $rowNumber: NIK tidak boleh kosong";
                        $errorCount++;
                        continue;
                    }

                    $karyawan = Karyawan::where('Nik', $nik)
                        ->where('Group_pegawai', 'Security')
                        ->where('vcAktif', '1')
                        ->first();

                    if (!$karyawan) {
                        $errors[] = "Baris $rowNumber: NIK $nik tidak ditemukan atau bukan Security";
                        $errorCount++;
                        continue;
                    }

                    // Parse tanggal
                    $tanggal = null;
                    try {
                        // Coba format Y-m-d
                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalStr)) {
                            $tanggal = Carbon::createFromFormat('Y-m-d', $tanggalStr);
                        }
                        // Coba format d/m/Y
                        elseif (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $tanggalStr)) {
                            $tanggal = Carbon::createFromFormat('d/m/Y', $tanggalStr);
                        }
                        // Coba format d-m-Y
                        elseif (preg_match('/^\d{1,2}-\d{1,2}-\d{4}$/', $tanggalStr)) {
                            $tanggal = Carbon::createFromFormat('d-m-Y', $tanggalStr);
                        }
                        else {
                            $tanggal = Carbon::parse($tanggalStr);
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Baris $rowNumber: Format tanggal tidak valid: $tanggalStr";
                        $errorCount++;
                        continue;
                    }

                    // Validasi tanggal dalam periode
                    if ($tanggal->format('Y-m-d') < $tanggalAwal->format('Y-m-d') || 
                        $tanggal->format('Y-m-d') > $tanggalAkhir->format('Y-m-d')) {
                        $errors[] = "Baris $rowNumber: Tanggal di luar periode yang dipilih";
                        $errorCount++;
                        continue;
                    }

                    // Parse shift
                    $shiftStrUpper = strtoupper($shiftStr);
                    $isOff = false;
                    $shifts = [];

                    if ($shiftStrUpper === 'OFF') {
                        $isOff = true;
                    } else {
                        // Bisa single shift (1, 2, 3) atau multiple (1,2 atau 1, 2)
                        $shiftParts = preg_split('/[,\s]+/', $shiftStr);
                        foreach ($shiftParts as $shiftPart) {
                            $shiftInt = (int) trim($shiftPart);
                            if ($shiftInt >= 1 && $shiftInt <= 3) {
                                $shifts[] = $shiftInt;
                            }
                        }

                        if (empty($shifts)) {
                            $errors[] = "Baris $rowNumber: Shift tidak valid: $shiftStr (harus 1, 2, 3, atau OFF)";
                            $errorCount++;
                            continue;
                        }
                    }

                    // Insert data
                    if ($isOff) {
                        $dataInsert[] = [
                            'vcNik' => $nik,
                            'dtTanggal' => $tanggal->format('Y-m-d'),
                            'intShift' => null,
                            'vcKeterangan' => $keterangan ?: 'OFF',
                            'isOverride' => false,
                            'vcOverrideBy' => null,
                            'dtOverrideAt' => null,
                            'dtCreate' => $now,
                            'dtChange' => $now,
                        ];
                    } else {
                        foreach ($shifts as $shift) {
                            $dataInsert[] = [
                                'vcNik' => $nik,
                                'dtTanggal' => $tanggal->format('Y-m-d'),
                                'intShift' => $shift,
                                'vcKeterangan' => $keterangan,
                                'isOverride' => false,
                                'vcOverrideBy' => null,
                                'dtOverrideAt' => null,
                                'dtCreate' => $now,
                                'dtChange' => $now,
                            ];
                        }
                    }
                }

                fclose($handle);
            } else {
                // Untuk Excel, gunakan pendekatan sederhana
                // Bisa install library PhpSpreadsheet nanti jika diperlukan
                return response()->json([
                    'success' => false,
                    'message' => 'Format Excel (XLSX/XLS) belum didukung. Silakan konversi ke CSV terlebih dahulu. Format CSV: NIK, Tanggal (Y-m-d), Shift (1/2/3/OFF), Keterangan (optional)'
                ], 422);
            }

            // Insert semua data
            if (!empty($dataInsert)) {
                // Hapus duplikasi berdasarkan NIK dan Tanggal
                $uniqueData = [];
                foreach ($dataInsert as $item) {
                    $key = $item['vcNik'] . '_' . $item['dtTanggal'] . '_' . ($item['intShift'] ?? 'OFF');
                    if (!isset($uniqueData[$key])) {
                        $uniqueData[$key] = $item;
                    }
                }

                // Hapus jadwal yang akan di-insert (untuk menghindari duplikasi)
                foreach ($uniqueData as $item) {
                    JadwalShiftSecurity::where('vcNik', $item['vcNik'])
                        ->where('dtTanggal', $item['dtTanggal'])
                        ->where(function($q) use ($item) {
                            if ($item['intShift'] === null) {
                                $q->whereNull('intShift');
                            } else {
                                $q->where('intShift', $item['intShift']);
                            }
                        })
                        ->delete();
                }

                DB::table('t_jadwal_shift_security')->insert(array_values($uniqueData));
                $successCount = count($uniqueData);
            }

            DB::commit();

            $message = "Import selesai. Berhasil: $successCount, Gagal: $errorCount";
            if (!empty($errors)) {
                $message .= "\n\nError details:\n" . implode("\n", array_slice($errors, 0, 10));
                if (count($errors) > 10) {
                    $message .= "\n... dan " . (count($errors) - 10) . " error lainnya";
                }
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error importing jadwal shift security: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Report jadwal shift per periode
     */
    public function report(Request $request)
    {
        $bulanAwal = $request->get('bulan_awal', date('m'));
        $tahunAwal = $request->get('tahun_awal', date('Y'));
        $bulanAkhir = $request->get('bulan_akhir', date('m'));
        $tahunAkhir = $request->get('tahun_akhir', date('Y'));
        $filterNama = $request->get('filter_nama', '');

        $tanggalAwal = Carbon::create($tahunAwal, $bulanAwal, 1);
        $tanggalAkhir = Carbon::create($tahunAkhir, $bulanAkhir, 1)->endOfMonth();

        // Get satpams dengan filter
        $satpamsQuery = Karyawan::where('Group_pegawai', 'Security')
            ->where('vcAktif', '1');

        if (!empty($filterNama)) {
            $satpamsQuery->where(function ($q) use ($filterNama) {
                $q->where('Nik', 'LIKE', '%' . $filterNama . '%')
                    ->orWhere('Nama', 'LIKE', '%' . $filterNama . '%');
            });
        }

        $satpams = $satpamsQuery->orderBy('Nama')->get(['Nik', 'Nama']);

        // Get jadwal untuk periode
        $jadwalRaw = JadwalShiftSecurity::whereBetween('dtTanggal', [
            $tanggalAwal->format('Y-m-d'),
            $tanggalAkhir->format('Y-m-d')
        ])
        ->whereIn('vcNik', $satpams->pluck('Nik'))
        ->with('karyawan', 'shiftSecurity')
        ->orderBy('dtTanggal')
        ->orderBy('vcNik')
        ->get();

        // Group by NIK
        $jadwalGrouped = [];
        foreach ($jadwalRaw as $j) {
            $nik = $j->vcNik;
            if (!isset($jadwalGrouped[$nik])) {
                $jadwalGrouped[$nik] = [
                    'karyawan' => $j->karyawan,
                    'jadwal' => []
                ];
            }
            $jadwalGrouped[$nik]['jadwal'][] = $j;
        }

        // Export ke Excel/CSV jika diminta
        if ($request->get('export') === 'excel' || $request->get('export') === 'csv') {
            return $this->exportReport($jadwalGrouped, $tanggalAwal, $tanggalAkhir, $request->get('export'));
        }

        return view('jadwal-shift-security.report', compact(
            'satpams',
            'jadwalGrouped',
            'bulanAwal',
            'tahunAwal',
            'bulanAkhir',
            'tahunAkhir',
            'tanggalAwal',
            'tanggalAkhir',
            'filterNama'
        ));
    }

    /**
     * Export report ke Excel/CSV
     */
    private function exportReport($jadwalGrouped, $tanggalAwal, $tanggalAkhir, $format = 'csv')
    {
        $filename = 'report_jadwal_shift_' . $tanggalAwal->format('Y-m-d') . '_' . $tanggalAkhir->format('Y-m-d') . '.' . $format;

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($jadwalGrouped) {
                $file = fopen('php://output', 'w');
                
                // BOM untuk Excel UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // Header
                fputcsv($file, ['NIK', 'Nama', 'Tanggal', 'Shift', 'Keterangan', 'Override']);

                foreach ($jadwalGrouped as $nik => $data) {
                    foreach ($data['jadwal'] as $jadwal) {
                        $shift = $jadwal->intShift === null ? 'OFF' : 'Shift ' . $jadwal->intShift;
                        $override = $jadwal->isOverride ? 'Ya' : 'Tidak';
                        
                        fputcsv($file, [
                            $nik,
                            $data['karyawan']->Nama ?? '',
                            $jadwal->dtTanggal->format('Y-m-d'),
                            $shift,
                            $jadwal->vcKeterangan ?? '',
                            $override
                        ]);
                    }
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Untuk format lain (Excel) bisa ditambahkan nanti
        return response()->json(['message' => 'Format tidak didukung'], 422);
    }
}
