<?php

namespace App\Http\Controllers;

use App\Services\SupabaseLeaveRequestService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TarikDataLeaveSupabaseController extends Controller
{
    private const DETAIL_LIMIT = 100;

    private const CACHE_TTL_MINUTES = 60;

    public function __construct(
        protected SupabaseLeaveRequestService $leaveService
    ) {}

    public function index()
    {
        return view('tarik-data-leave-supabase.index', [
            'apiBaseUrl' => $this->leaveService->getBaseRestUrl(),
            'typeMapping' => SupabaseLeaveRequestService::TYPE_TO_KODE,
            'defaultDari' => now()->subDays(30)->format('Y-m-d'),
            'defaultSampai' => now()->format('Y-m-d'),
        ]);
    }

    public function pull(Request $request)
    {
        @set_time_limit(600);

        $request->validate([
            'dari_tanggal' => 'required|date',
            'sampai_tanggal' => 'required|date|after_or_equal:dari_tanggal',
            'nik' => 'nullable|string|max:20',
            'types' => 'nullable|array',
            'types.*' => 'in:sakit,izin,cuti',
        ]);

        $types = $request->filled('types') ? $request->types : ['sakit', 'izin', 'cuti'];

        $result = $this->leaveService->pullLeaveRequests(
            $request->dari_tanggal,
            $request->sampai_tanggal,
            $request->filled('nik') ? trim($request->nik) : null,
            $types
        );

        if (!($result['success'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal menarik data dari Supabase.',
            ], 500);
        }

        $rows = $result['data'] ?? [];
        $previewSave = $this->buildSavePreview($rows);

        $batchId = (string) Str::uuid();
        Cache::put($this->cacheKey($batchId), [
            'rows' => $rows,
            'start_date' => $request->dari_tanggal,
            'end_date' => $request->sampai_tanggal,
        ], now()->addMinutes(self::CACHE_TTL_MINUTES));

        $meta = $result['meta'] ?? [];
        $apiRawTotal = (int) ($meta['total_raw'] ?? count($rows));

        $message = 'Berhasil menarik ' . count($rows) . ' pengajuan (raw API: ' . $apiRawTotal . ' baris, status approved).';
        if ($apiRawTotal === 0) {
            $message = 'Koneksi Supabase berhasil, tetapi tidak ada leave_requests approved untuk periode '
                . $request->dari_tanggal . ' s/d ' . $request->sampai_tanggal . '.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'batch_id' => $batchId,
                'raw_rows' => $rows,
                'raw_total' => count($rows),
                'api_meta' => $meta,
                'preview_save' => $previewSave,
                'empty_reason' => $apiRawTotal === 0 ? 'no_data_from_api' : null,
            ],
        ]);
    }

    public function save(Request $request)
    {
        @set_time_limit(600);

        $request->validate([
            'batch_id' => 'required|string|uuid',
            'skip_unknown_nik' => 'nullable|boolean',
        ]);

        $cached = Cache::get($this->cacheKey($request->batch_id));
        if (!$cached || empty($cached['rows'])) {
            return response()->json([
                'success' => false,
                'message' => 'Data preview tidak ditemukan atau sudah kedaluwarsa. Silakan tarik ulang.',
            ], 410);
        }

        $rows = $cached['rows'];
        $skipUnknownNik = $request->boolean('skip_unknown_nik', true);

        $knownNik = null;
        if ($skipUnknownNik) {
            $knownNik = DB::table('m_karyawan')->pluck('Nik')->map(fn ($n) => (string) $n)->flip();
        }

        $karyawanNames = DB::table('m_karyawan')->pluck('Nama', 'Nik');

        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $skippedReasons = [];
        $skippedDetails = [];
        $skippedForList = 0;
        $errors = [];
        $errorSummary = [];

        DB::beginTransaction();

        try {
            foreach ($rows as $row) {
                $nik = $row['nik'] ?? null;
                $kode = $row['vcKodeAbsen'] ?? null;
                $mulai = $row['dtTanggalMulai'] ?? null;
                $selesai = $row['dtTanggalSelesai'] ?? null;
                $nama = $karyawanNames[$nik] ?? ($row['nama'] ?? null);

                $meta = [
                    'nik' => $nik,
                    'nama' => $nama,
                    'kode' => $kode,
                    'tanggal_mulai' => $mulai,
                    'tanggal_selesai' => $selesai,
                ];

                if (!$nik || !$kode || !$mulai || !$selesai) {
                    $this->recordSkip($skipped, $skippedReasons, $skippedDetails, $skippedForList, 'data_tidak_lengkap', $meta);
                    continue;
                }

                if ($skipUnknownNik && $knownNik !== null && !$knownNik->has($nik)) {
                    $this->recordSkip($skipped, $skippedReasons, $skippedDetails, $skippedForList, 'nik_tidak_di_master', $meta);
                    continue;
                }

                $kodeExists = DB::table('m_jenis_absen')->where('vcKodeAbsen', $kode)->exists();
                if (!$kodeExists) {
                    $this->recordSkip($skipped, $skippedReasons, $skippedDetails, $skippedForList, 'kode_tidak_di_master', $meta);
                    continue;
                }

                try {
                    $existing = DB::table('t_tidak_masuk')
                        ->where('vcNik', $nik)
                        ->where('vcKodeAbsen', $kode)
                        ->where('dtTanggalMulai', $mulai)
                        ->where('dtTanggalSelesai', $selesai)
                        ->first();

                    $payload = [
                        'vcNik' => $nik,
                        'vcKodeAbsen' => $kode,
                        'dtTanggalMulai' => $mulai,
                        'dtTanggalSelesai' => $selesai,
                        'vcKeterangan' => $row['vcKeterangan'] ?? 'Import dari Supabase',
                        'vcDibayar' => '1',
                        'dtChange' => Carbon::now(),
                    ];

                    if ($existing) {
                        DB::table('t_tidak_masuk')
                            ->where('vcNik', $nik)
                            ->where('vcKodeAbsen', $kode)
                            ->where('dtTanggalMulai', $mulai)
                            ->where('dtTanggalSelesai', $selesai)
                            ->update($payload);
                        $updated++;
                    } else {
                        $payload['dtCreate'] = Carbon::now();
                        DB::table('t_tidak_masuk')->insert($payload);
                        $inserted++;
                    }
                } catch (\Exception $e) {
                    $humanError = mb_substr($e->getMessage(), 0, 200);
                    $errors[] = array_merge($meta, ['error' => $humanError]);
                    $errorSummary[$humanError] = ($errorSummary[$humanError] ?? 0) + 1;
                    Log::error('Error saving Supabase leave request', [
                        'nik' => $nik,
                        'kode' => $kode,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();
            Cache::forget($this->cacheKey($request->batch_id));

            $message = "Data izin/sakit/cuti berhasil disimpan. Insert: {$inserted}, Update: {$updated}";
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
                    'errors' => count($errors),
                    'error_summary' => $errorSummary,
                    'error_details' => array_slice($errors, 0, self::DETAIL_LIMIT),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Supabase leave batch', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function buildSavePreview(array $rows): array
    {
        $knownNik = DB::table('m_karyawan')->pluck('Nik')->map(fn ($n) => (string) $n)->flip();
        $karyawanNames = DB::table('m_karyawan')->pluck('Nama', 'Nik');
        $jenisLabels = DB::table('m_jenis_absen')->pluck('vcKeterangan', 'vcKodeAbsen');
        $preview = [];

        foreach ($rows as $row) {
            $nik = $row['nik'];
            $kode = $row['vcKodeAbsen'];
            $mulai = $row['dtTanggalMulai'];
            $selesai = $row['dtTanggalSelesai'];

            $existing = DB::table('t_tidak_masuk')
                ->where('vcNik', $nik)
                ->where('vcKodeAbsen', $kode)
                ->where('dtTanggalMulai', $mulai)
                ->where('dtTanggalSelesai', $selesai)
                ->exists();

            $aksi = 'Insert';
            $keterangan = 'Record baru di t_tidak_masuk';

            if (!$knownNik->has($nik)) {
                $aksi = 'Lewati';
                $keterangan = 'NIK tidak ada di master karyawan';
            } elseif (!DB::table('m_jenis_absen')->where('vcKodeAbsen', $kode)->exists()) {
                $aksi = 'Lewati';
                $keterangan = 'Kode absen tidak ada di m_jenis_absen';
            } elseif ($existing) {
                $aksi = 'Update';
                $keterangan = 'Record sama sudah ada — akan di-update';
            }

            $preview[] = [
                'supabase_id' => $row['supabase_id'] ?? '-',
                'nik' => $nik,
                'nama' => $karyawanNames[$nik] ?? ($row['nama'] ?? '-'),
                'type' => $row['type_label'] ?? $row['type'],
                'vcKodeAbsen' => $kode,
                'jenis_absen' => $jenisLabels[$kode] ?? $kode,
                'dtTanggalMulai' => $mulai,
                'dtTanggalSelesai' => $selesai,
                'jumlah_hari' => $row['jumlah_hari'] ?? '-',
                'vcKeterangan' => $row['vcKeterangan'] ?? '-',
                'status' => $row['status'] ?? '-',
                'aksi' => $aksi,
                'keterangan' => $keterangan,
            ];
        }

        return $preview;
    }

    protected function cacheKey(string $batchId): string
    {
        return 'supabase_leave_pull_' . $batchId;
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
        $skippedForList++;

        if (count($skippedDetails) >= self::DETAIL_LIMIT) {
            return;
        }

        $skippedDetails[] = [
            'nik' => $meta['nik'] ?? '-',
            'nama' => $meta['nama'] ?? '-',
            'kode' => $meta['kode'] ?? '-',
            'tanggal_mulai' => $meta['tanggal_mulai'] ?? '-',
            'tanggal_selesai' => $meta['tanggal_selesai'] ?? '-',
            'alasan' => $this->skipReasonLabel($reason),
        ];
    }

    protected function skipReasonLabel(string $reason): string
    {
        return match ($reason) {
            'nik_tidak_di_master' => 'NIK tidak ada di master karyawan',
            'kode_tidak_di_master' => 'Kode absen tidak ada di master',
            'data_tidak_lengkap' => 'Data tidak lengkap',
            default => $reason,
        };
    }
}
