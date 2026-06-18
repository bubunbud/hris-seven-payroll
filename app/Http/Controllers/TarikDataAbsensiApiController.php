<?php

namespace App\Http\Controllers;

use App\Services\HrisApiAttendanceLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TarikDataAbsensiApiController extends Controller
{
    private const DETAIL_LIMIT = 100;

    public function __construct(
        protected HrisApiAttendanceLogService $attendanceApi
    ) {}

    public function index()
    {
        return view('tarik-data-absensi-api.index', [
            'defaultDari' => now()->subDays(30)->format('Y-m-d'),
            'defaultSampai' => now()->format('Y-m-d'),
            'apiBaseUrl' => config('hris_api.base_url'),
        ]);
    }

    /**
     * Tarik log absensi dari API HRIS → sinkron ke t_absen (upsert per dtTanggal + vcNik).
     * Hanya jam masuk/keluar; tidak menarik note/shift; update hanya mengisi kolom jam yang masih kosong.
     */
    public function pullData(Request $request)
    {
        $request->validate([
            'dari_tanggal' => 'required|date',
            'sampai_tanggal' => 'required|date|after_or_equal:dari_tanggal',
            'nik' => 'nullable|string|max:20',
            'skip_unknown_nik' => 'nullable|boolean',
        ], [
            'dari_tanggal.required' => 'Tanggal mulai harus diisi',
            'sampai_tanggal.required' => 'Tanggal akhir harus diisi',
            'sampai_tanggal.after_or_equal' => 'Tanggal akhir harus lebih besar atau sama dengan tanggal mulai',
        ]);

        $startDate = $request->dari_tanggal;
        $endDate = $request->sampai_tanggal;
        $nikFilter = $request->filled('nik') ? trim($request->nik) : null;
        if ($nikFilter && str_contains($nikFilter, ' - ')) {
            $nikFilter = trim(explode(' - ', $nikFilter)[0]);
        }

        $skipUnknownNik = $request->boolean('skip_unknown_nik', true);

        try {
            $result = $this->attendanceApi->getAttendanceLogs(null, $startDate, $endDate, $nikFilter);

            if (!($result['success'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Gagal mengambil data dari API',
                ], 500);
            }

            $logs = $result['data'] ?? [];
            if ($logs === []) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data absensi dari API untuk periode yang dipilih.',
                ], 404);
            }

            $knownNik = null;
            if ($skipUnknownNik) {
                $knownNik = DB::table('m_karyawan')->pluck('Nik')->map(fn ($n) => (string) $n)->flip();
            }

            $inserted = 0;
            $updated = 0;
            $skipped = 0;
            $skippedReasons = [
                'format_tidak_valid' => 0,
                'data_tidak_lengkap' => 0,
                'nik_tidak_di_master' => 0,
                'jam_sudah_lengkap' => 0,
                'tidak_ada_jam_dari_api' => 0,
            ];
            $skippedDetails = [];
            $skippedForList = 0;
            $errors = [];
            $errorSummary = [];

            DB::beginTransaction();

            foreach ($logs as $log) {
                if (!is_array($log)) {
                    $this->recordSkip($skipped, $skippedReasons, $skippedDetails, $skippedForList, 'format_tidak_valid', [
                        'tanggal' => null,
                        'nik' => null,
                        'nama' => null,
                    ]);
                    continue;
                }

                $meta = [
                    'tanggal' => $log['date'] ?? null,
                    'nik' => $log['nik'] ?? null,
                    'nama' => $log['name'] ?? null,
                ];

                try {
                    $row = $this->attendanceApi->mapLogToAbsenRow($log);
                    if (!$row) {
                        $this->recordSkip($skipped, $skippedReasons, $skippedDetails, $skippedForList, 'data_tidak_lengkap', $meta);
                        continue;
                    }

                    if ($skipUnknownNik && $knownNik !== null && !$knownNik->has($row['vcNik'])) {
                        $this->recordSkip($skipped, $skippedReasons, $skippedDetails, $skippedForList, 'nik_tidak_di_master', array_merge($meta, [
                            'nik' => $row['vcNik'],
                            'tanggal' => $row['dtTanggal'],
                        ]));
                        continue;
                    }

                    $apiJamMasuk = $row['dtJamMasuk'];
                    $apiJamKeluar = $row['dtJamKeluar'];
                    $hasApiJam = !$this->attendanceApi->isEmptyTime($apiJamMasuk)
                        || !$this->attendanceApi->isEmptyTime($apiJamKeluar);

                    if (!$hasApiJam) {
                        $this->recordSkip($skipped, $skippedReasons, $skippedDetails, $skippedForList, 'tidak_ada_jam_dari_api', array_merge($meta, [
                            'nik' => $row['vcNik'],
                            'tanggal' => $row['dtTanggal'],
                        ]));
                        continue;
                    }

                    $existing = DB::table('t_absen')
                        ->where('dtTanggal', $row['dtTanggal'])
                        ->where('vcNik', $row['vcNik'])
                        ->first();

                    if ($existing) {
                        $updatePayload = [];
                        $dbJamMasuk = $existing->dtJamMasuk ?? null;
                        $dbJamKeluar = $existing->dtJamKeluar ?? null;

                        if ($this->attendanceApi->isEmptyTime($dbJamMasuk) && !$this->attendanceApi->isEmptyTime($apiJamMasuk)) {
                            $updatePayload['dtJamMasuk'] = $apiJamMasuk;
                        }
                        if ($this->attendanceApi->isEmptyTime($dbJamKeluar) && !$this->attendanceApi->isEmptyTime($apiJamKeluar)) {
                            $updatePayload['dtJamKeluar'] = $apiJamKeluar;
                        }

                        if ($updatePayload === []) {
                            $this->recordSkip($skipped, $skippedReasons, $skippedDetails, $skippedForList, 'jam_sudah_lengkap', array_merge($meta, [
                                'nik' => $row['vcNik'],
                                'tanggal' => $row['dtTanggal'],
                                'jam_masuk_db' => $dbJamMasuk,
                                'jam_keluar_db' => $dbJamKeluar,
                            ]));
                            continue;
                        }

                        $updatePayload['dtChange'] = Carbon::now();
                        DB::table('t_absen')
                            ->where('dtTanggal', $row['dtTanggal'])
                            ->where('vcNik', $row['vcNik'])
                            ->update($updatePayload);
                        $updated++;
                    } else {
                        DB::table('t_absen')->insert([
                            'dtTanggal' => $row['dtTanggal'],
                            'vcNik' => $row['vcNik'],
                            'dtJamMasuk' => $apiJamMasuk,
                            'dtJamKeluar' => $apiJamKeluar,
                            'dtCreate' => Carbon::now(),
                            'dtChange' => Carbon::now(),
                        ]);
                        $inserted++;
                    }
                } catch (\Exception $e) {
                    $humanError = $this->humanizeImportError($e);
                    $errors[] = [
                        'nik' => $log['nik'] ?? 'N/A',
                        'tanggal' => $log['date'] ?? 'N/A',
                        'nama' => $log['name'] ?? null,
                        'error' => $humanError,
                    ];
                    $errorSummary[$humanError] = ($errorSummary[$humanError] ?? 0) + 1;
                    Log::error('Error importing attendance log from API', [
                        'log_id' => $log['id'] ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            $message = "Data absensi API berhasil ditarik. Insert: {$inserted}, Update: {$updated}";
            if ($skipped > 0) {
                $message .= ", Lewati: {$skipped}";
            }
            if ($errors !== []) {
                $message .= ' (Error: ' . count($errors) . ' record)';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'inserted' => $inserted,
                    'updated' => $updated,
                    'skipped' => $skipped,
                    'skipped_reasons' => array_filter($skippedReasons),
                    'skipped_details' => $skippedDetails,
                    'skipped_details_total' => $skippedForList,
                    'skipped_details_truncated' => $skippedForList > count($skippedDetails),
                    'total_api' => count($logs),
                    'errors' => count($errors),
                    'error_summary' => $errorSummary,
                    'error_details' => array_slice($errors, 0, self::DETAIL_LIMIT),
                    'error_details_truncated' => count($errors) > self::DETAIL_LIMIT,
                    'meta' => $result['meta'] ?? null,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error pulling attendance from API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function recordSkip(
        int &$skipped,
        array &$skippedReasons,
        array &$skippedDetails,
        int &$skippedForList,
        string $reason,
        array $meta
    ): void {
        $skipped++;
        $skippedReasons[$reason] = ($skippedReasons[$reason] ?? 0) + 1;

        if ($reason === 'jam_sudah_lengkap') {
            return;
        }

        $skippedForList++;

        if (count($skippedDetails) >= self::DETAIL_LIMIT) {
            return;
        }

        $skippedDetails[] = [
            'tanggal' => $meta['tanggal'] ?? '-',
            'nik' => $meta['nik'] ?? '-',
            'nama' => $meta['nama'] ?? '-',
            'alasan' => $this->skipReasonLabel($reason),
            'alasan_kode' => $reason,
        ];
    }

    protected function skipReasonLabel(string $reason): string
    {
        return match ($reason) {
            'nik_tidak_di_master' => 'NIK tidak ada di master karyawan',
            'data_tidak_lengkap' => 'Data API tidak lengkap (tanpa tanggal/NIK)',
            'format_tidak_valid' => 'Format data API tidak valid',
            'jam_sudah_lengkap' => 'Jam masuk & pulang di database sudah terisi',
            'tidak_ada_jam_dari_api' => 'API tidak mengirim jam masuk/pulang',
            default => $reason,
        };
    }

    protected function humanizeImportError(\Throwable $e): string
    {
        $msg = $e->getMessage();

        if (str_contains($msg, "Data too long for column 'vcNik'")) {
            return 'NIK terlalu panjang (maks. 10 karakter di database)';
        }

        if (str_contains($msg, 'Integrity constraint violation')) {
            return 'Pelanggaran aturan database (constraint)';
        }

        return mb_substr($msg, 0, 200);
    }
}
