<?php

namespace App\Http\Controllers;

use App\Services\HrisApiAbsentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ListPengajuanIzinApiController extends Controller
{
    public function __construct(
        protected HrisApiAbsentService $absentApi
    ) {}

    /**
     * Tampilkan list pengajuan tidak masuk (Sakit) dari API Absents
     * Endpoint: GET /v1/absents/requests
     */
    public function index(Request $request)
    {
        $permits = [];
        $error = null;
        $mapping = $this->absentApi->getTypeMapping();

        $endDate = $request->get('sampai_tanggal', now()->format('Y-m-d'));
        $startDate = $request->get('dari_tanggal', now()->subDays(30)->format('Y-m-d'));
        $subordinate = $request->boolean('subordinate', false);

        $fetchMeta = null;
        if ($request->has('fetch') && $request->fetch === '1') {
            $result = $this->absentApi->getAbsents(null, $startDate, $endDate, $subordinate);
            if ($result['success']) {
                $permits = $result['data'];
                usort($permits, fn($a, $b) => strcmp($a['date_formatted'] ?? '', $b['date_formatted'] ?? ''));
                $fetchMeta = $result['meta'] ?? null;
            } else {
                $error = $result['message'];
            }
        }

        return view('list-pengajuan-izin-api.index', [
            'permits' => $permits,
            'error' => $error,
            'fetchMeta' => $fetchMeta,
            'purposeMapping' => $mapping,
            'dariTanggal' => $startDate,
            'sampaiTanggal' => $endDate,
            'subordinate' => $subordinate,
        ]);
    }

    /**
     * Import pengajuan izin ke t_tidak_masuk
     * Permits punya single date → dtTanggalMulai = dtTanggalSelesai = date
     */
    public function import(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|string',
        ]);

        $ids = $request->ids;
        $startDate = $request->input('dari_tanggal', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('sampai_tanggal', now()->format('Y-m-d'));
        $subordinate = $request->boolean('subordinate', false);

        $result = $this->absentApi->getAbsents(null, $startDate, $endDate, $subordinate);
        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 500);
        }

        $allPermits = collect($result['data']);
        $selectedPermits = $allPermits->whereIn('id', $ids)->values();

        // Import hanya untuk status Approved/Completed
        $allowedStatuses = ['APPROVED', 'COMPLETED'];
        $selectedPermits = $selectedPermits->filter(function ($p) use ($allowedStatuses) {
            return in_array(strtoupper($p['status'] ?? ''), $allowedStatuses);
        })->values();

        if ($selectedPermits->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data terpilih yang status Approved/Completed. Import hanya untuk pengajuan yang sudah disetujui.',
            ], 400);
        }

        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($selectedPermits as $permit) {
                $vcNik = $permit['employee_nik'] ?? null;
                $dateRaw = $permit['date'] ?? null;
                $reason = $permit['reason'] ?? '';

                if (!$vcNik || !$dateRaw) {
                    $errors[] = ['nik' => $vcNik ?? 'N/A', 'error' => 'Data tidak lengkap (NIK, date wajib)'];
                    $skipped++;
                    continue;
                }

                $date = is_string($dateRaw) ? substr($dateRaw, 0, 10) : $dateRaw;
                $vcKodeAbsen = $permit['mapped_kode'] ?? 'S010'; // S010 atau I002

                $karyawanExists = DB::table('m_karyawan')->where('Nik', $vcNik)->exists();
                if (!$karyawanExists) {
                    $errors[] = ['nik' => $vcNik, 'error' => 'NIK tidak ditemukan di master karyawan'];
                    $skipped++;
                    continue;
                }

                $kodeExists = DB::table('m_jenis_absen')->where('vcKodeAbsen', $vcKodeAbsen)->exists();
                if (!$kodeExists) {
                    $errors[] = ['nik' => $vcNik, 'error' => "Kode absen {$vcKodeAbsen} tidak ada di m_jenis_absen"];
                    $skipped++;
                    continue;
                }

                // Permits: single date → dtTanggalMulai = dtTanggalSelesai
                $existing = DB::table('t_tidak_masuk')
                    ->where('vcNik', $vcNik)
                    ->where('vcKodeAbsen', $vcKodeAbsen)
                    ->where('dtTanggalMulai', $date)
                    ->where('dtTanggalSelesai', $date)
                    ->first();

                $data = [
                    'vcNik' => $vcNik,
                    'vcKodeAbsen' => $vcKodeAbsen,
                    'dtTanggalMulai' => $date,
                    'dtTanggalSelesai' => $date,
                    'vcKeterangan' => $reason ?: 'Import dari API HRIS Absents (' . ($permit['purpose'] ?? $permit['type'] ?? 'Sakit') . ')',
                    'vcDibayar' => '1',
                    'dtChange' => Carbon::now(),
                ];

                if ($existing) {
                    DB::table('t_tidak_masuk')
                        ->where('vcNik', $vcNik)
                        ->where('vcKodeAbsen', $vcKodeAbsen)
                        ->where('dtTanggalMulai', $date)
                        ->where('dtTanggalSelesai', $date)
                        ->update($data);
                    $updated++;
                } else {
                    $data['dtCreate'] = Carbon::now();
                    DB::table('t_tidak_masuk')->insert($data);
                    $inserted++;
                }
            }

            DB::commit();

            $message = "Import berhasil! Insert: {$inserted}, Update: {$updated}, Skip: {$skipped}";
            if (!empty($errors)) {
                $message .= " (Error: " . count($errors) . " record)";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'inserted' => $inserted,
                    'updated' => $updated,
                    'skipped' => $skipped,
                    'errors' => $errors,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ListPengajuanIzinApi import error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

}
