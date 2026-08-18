<?php

namespace App\Http\Controllers;

use App\Models\MesinFingerprint;
use App\Services\ZkTecoFingerprintService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TarikDataFingerprintController extends Controller
{
    private const DETAIL_LIMIT = 100;

    private const CACHE_TTL_MINUTES = 60;

    public function __construct(
        protected ZkTecoFingerprintService $zkService
    ) {}

    public function index()
    {
        return view('tarik-data-fingerprint.index', [
            'mesins' => MesinFingerprint::aktif()->orderBy('vcNama')->get(),
            'defaultDari' => now()->subDay()->format('Y-m-d'),
            'defaultSampai' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Tarik log dari mesin terpilih → preview (dry run), belum simpan ke t_absen.
     */
    public function pull(Request $request)
    {
        @set_time_limit(600);

        $request->validate([
            'mesin_ids' => 'required|array|min:1',
            'mesin_ids.*' => 'integer|exists:m_mesin_fingerprint,id',
            'dari_tanggal' => 'required|date',
            'sampai_tanggal' => 'required|date|after_or_equal:dari_tanggal',
            'nik' => 'nullable|string|max:20',
        ]);

        $mesinIds = $request->mesin_ids;
        $startDate = $request->dari_tanggal;
        $endDate = $request->sampai_tanggal;
        $nikFilter = $request->filled('nik') ? trim($request->nik) : null;

        $rawLogs = [];
        $errors = [];
        $machineMeta = [];

        foreach ($mesinIds as $mesinId) {
            $mesin = MesinFingerprint::find($mesinId);
            if (!$mesin || $mesin->vcAktif !== '1') {
                $errors[] = 'Mesin ID ' . $mesinId . ' tidak aktif atau tidak ditemukan.';
                continue;
            }

            $result = $this->zkService->pullLogs($mesin, $startDate, $endDate, $nikFilter);
            if (!($result['success'] ?? false)) {
                $errors[] = $result['message'] ?? ('Gagal tarik dari ' . $mesin->vcNama);
                continue;
            }

            $logs = $result['data'] ?? [];
            $rawLogs = array_merge($rawLogs, $logs);
            $machineMeta[] = [
                'id' => $mesin->id,
                'nama' => $mesin->vcNama,
                'ip' => $mesin->vcIp,
                'total' => count($logs),
            ];
        }

        if ($rawLogs === [] && $errors !== []) {
            return response()->json([
                'success' => false,
                'message' => implode(' ', $errors),
            ], 500);
        }

        $aggregated = $this->zkService->aggregateLogs($rawLogs);
        $previewSave = $this->buildSavePreview($aggregated);

        $batchId = (string) Str::uuid();
        Cache::put($this->cacheKey($batchId), [
            'aggregated' => $aggregated,
            'mesin_ids' => $mesinIds,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ], now()->addMinutes(self::CACHE_TTL_MINUTES));

        $message = 'Berhasil menarik ' . count($rawLogs) . ' log dari ' . count($machineMeta) . ' mesin.';
        if ($errors !== []) {
            $message .= ' Peringatan: ' . implode(' ', $errors);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'batch_id' => $batchId,
                'raw_logs' => $rawLogs,
                'raw_total' => count($rawLogs),
                'machine_meta' => $machineMeta,
                'aggregated' => $aggregated,
                'preview_save' => $previewSave,
                'errors' => $errors,
            ],
        ]);
    }

    /**
     * Simpan hasil agregasi (dari batch preview) ke t_absen.
     */
    public function save(Request $request)
    {
        @set_time_limit(600);

        $request->validate([
            'batch_id' => 'required|string|uuid',
            'skip_unknown_nik' => 'nullable|boolean',
        ]);

        $cached = Cache::get($this->cacheKey($request->batch_id));
        if (!$cached || empty($cached['aggregated'])) {
            return response()->json([
                'success' => false,
                'message' => 'Data preview tidak ditemukan atau sudah kedaluwarsa. Silakan tarik ulang.',
            ], 410);
        }

        $aggregated = $cached['aggregated'];
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
            foreach ($aggregated as $row) {
                $nik = $row['nik'] ?? null;
                $tanggal = $row['tanggal'] ?? null;
                $jamMasuk = $row['jam_masuk'] ?? null;
                $jamPulang = $row['jam_pulang'] ?? null;
                $nama = $karyawanNames[$nik] ?? null;

                $meta = ['tanggal' => $tanggal, 'nik' => $nik, 'nama' => $nama];

                if (!$nik || !$tanggal) {
                    $this->recordSkip($skipped, $skippedReasons, $skippedDetails, $skippedForList, 'data_tidak_lengkap', $meta);
                    continue;
                }

                if ($skipUnknownNik && $knownNik !== null && !$knownNik->has($nik)) {
                    $this->recordSkip($skipped, $skippedReasons, $skippedDetails, $skippedForList, 'nik_tidak_di_master', $meta);
                    continue;
                }

                $hasJam = !$this->zkService->isEmptyTime($jamMasuk) || !$this->zkService->isEmptyTime($jamPulang);
                if (!$hasJam) {
                    $this->recordSkip($skipped, $skippedReasons, $skippedDetails, $skippedForList, 'tidak_ada_jam', $meta);
                    continue;
                }

                try {
                    $existing = DB::table('t_absen')
                        ->where('dtTanggal', $tanggal)
                        ->where('vcNik', $nik)
                        ->first();

                    if ($existing) {
                        $updatePayload = [];
                        $dbJamMasuk = $existing->dtJamMasuk ?? null;
                        $dbJamKeluar = $existing->dtJamKeluar ?? null;

                        if ($this->zkService->isEmptyTime($dbJamMasuk) && !$this->zkService->isEmptyTime($jamMasuk)) {
                            $updatePayload['dtJamMasuk'] = $jamMasuk;
                        }
                        if ($this->zkService->isEmptyTime($dbJamKeluar) && !$this->zkService->isEmptyTime($jamPulang)) {
                            $updatePayload['dtJamKeluar'] = $jamPulang;
                        }

                        if ($updatePayload === []) {
                            $this->recordSkip($skipped, $skippedReasons, $skippedDetails, $skippedForList, 'jam_sudah_lengkap', $meta);
                            continue;
                        }

                        $updatePayload['dtChange'] = Carbon::now();
                        DB::table('t_absen')
                            ->where('dtTanggal', $tanggal)
                            ->where('vcNik', $nik)
                            ->update($updatePayload);
                        $updated++;
                    } else {
                        DB::table('t_absen')->insert([
                            'dtTanggal' => $tanggal,
                            'vcNik' => $nik,
                            'dtJamMasuk' => $jamMasuk,
                            'dtJamKeluar' => $jamPulang,
                            'dtCreate' => Carbon::now(),
                            'dtChange' => Carbon::now(),
                        ]);
                        $inserted++;
                    }
                } catch (\Exception $e) {
                    $humanError = mb_substr($e->getMessage(), 0, 200);
                    $errors[] = array_merge($meta, ['error' => $humanError]);
                    $errorSummary[$humanError] = ($errorSummary[$humanError] ?? 0) + 1;
                    Log::error('Error saving fingerprint attendance', [
                        'nik' => $nik,
                        'tanggal' => $tanggal,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();
            Cache::forget($this->cacheKey($request->batch_id));

            $message = "Data fingerprint berhasil disimpan. Insert: {$inserted}, Update: {$updated}";
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
                    'errors' => count($errors),
                    'error_summary' => $errorSummary,
                    'error_details' => array_slice($errors, 0, self::DETAIL_LIMIT),
                    'error_details_truncated' => count($errors) > self::DETAIL_LIMIT,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving fingerprint batch', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function buildSavePreview(array $aggregated): array
    {
        $knownNik = DB::table('m_karyawan')->pluck('Nik')->map(fn ($n) => (string) $n)->flip();
        $karyawanNames = DB::table('m_karyawan')->pluck('Nama', 'Nik');
        $preview = [];

        foreach ($aggregated as $row) {
            $nik = $row['nik'];
            $tanggal = $row['tanggal'];
            $existing = DB::table('t_absen')
                ->where('dtTanggal', $tanggal)
                ->where('vcNik', $nik)
                ->first();

            $aksi = 'Insert';
            $keterangan = 'Record baru';

            if (!$knownNik->has($nik)) {
                $aksi = 'Lewati';
                $keterangan = 'NIK tidak ada di master';
            } elseif ($this->zkService->isEmptyTime($row['jam_masuk'] ?? null) && $this->zkService->isEmptyTime($row['jam_pulang'] ?? null)) {
                $aksi = 'Lewati';
                $keterangan = 'Tidak ada jam masuk/pulang';
            } elseif ($existing) {
                $canUpdateMasuk = $this->zkService->isEmptyTime($existing->dtJamMasuk ?? null) && !$this->zkService->isEmptyTime($row['jam_masuk'] ?? null);
                $canUpdatePulang = $this->zkService->isEmptyTime($existing->dtJamKeluar ?? null) && !$this->zkService->isEmptyTime($row['jam_pulang'] ?? null);

                if ($canUpdateMasuk || $canUpdatePulang) {
                    $aksi = 'Update';
                    $keterangan = 'Isi jam kosong di database';
                } else {
                    $aksi = 'Lewati';
                    $keterangan = 'Jam di database sudah terisi';
                }
            }

            $preview[] = [
                'tanggal' => $tanggal,
                'nik' => $nik,
                'nama' => $karyawanNames[$nik] ?? '-',
                'jam_masuk' => $row['jam_masuk'] ?? '-',
                'jam_pulang' => $row['jam_pulang'] ?? '-',
                'mesin' => implode(', ', $row['mesin'] ?? []),
                'aksi' => $aksi,
                'keterangan' => $keterangan,
            ];
        }

        return $preview;
    }

    protected function cacheKey(string $batchId): string
    {
        return 'fingerprint_pull_' . $batchId;
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
        ];
    }

    protected function skipReasonLabel(string $reason): string
    {
        return match ($reason) {
            'nik_tidak_di_master' => 'NIK tidak ada di master karyawan',
            'data_tidak_lengkap' => 'Data tidak lengkap',
            'jam_sudah_lengkap' => 'Jam masuk & pulang di database sudah terisi',
            'tidak_ada_jam' => 'Tidak ada jam masuk/pulang hasil agregasi',
            default => $reason,
        };
    }
}
