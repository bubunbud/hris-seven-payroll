<?php

namespace App\Http\Controllers;

use App\Services\HrisApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ListPengajuanCutiApiController extends Controller
{
    public function __construct(
        protected HrisApiService $hrisApi
    ) {}

    /**
     * Tampilkan list pengajuan cuti dari API (hanya status Approved/Completed)
     * Default filter: sebulan terakhir
     */
    public function index(Request $request)
    {
        $leaves = [];
        $error = null;
        $mapping = $this->hrisApi->getLeaveTypeMapping();

        // Default: sebulan terakhir
        $endDate = $request->get('sampai_tanggal', now()->format('Y-m-d'));
        $startDate = $request->get('dari_tanggal', now()->subDays(30)->format('Y-m-d'));

        $fetchMeta = null;
        if ($request->has('fetch') && $request->fetch === '1') {
            try {
                $result = $this->hrisApi->getApprovedLeaves(null, $startDate, $endDate);
                if ($result['success'] ?? false) {
                    $leaves = collect($result['data'] ?? [])->filter(fn ($row) => is_array($row))->map(function ($item) {
                        $item['mapped_kode'] = $this->hrisApi->mapLeaveTypeToKodeAbsen($item['leave_type_name'] ?? '');

                        return $item;
                    })->all();
                    $fetchMeta = $result['meta'] ?? null;
                } else {
                    $error = $result['message'] ?? 'Permintaan ke API gagal tanpa pesan detail.';
                }
            } catch (\Throwable $e) {
                Log::error('ListPengajuanCutiApiController@index fetch', [
                    'message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                ]);
                $error = 'Terjadi kesalahan: '.$e->getMessage();
            }
        }

        return view('list-pengajuan-cuti-api.index', [
            'leaves' => $leaves,
            'error' => $error,
            'fetchMeta' => $fetchMeta,
            'leaveTypeMapping' => $mapping,
            'dariTanggal' => $startDate,
            'sampaiTanggal' => $endDate,
        ]);
    }

    /**
     * Import pengajuan cuti Approved ke t_tidak_masuk
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
        $result = $this->hrisApi->getApprovedLeaves(null, $startDate, $endDate);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 500);
        }

        $allLeaves = collect($result['data']);
        $selectedLeaves = $allLeaves->whereIn('id', $ids)->values();

        // Import hanya untuk status Approved/Completed
        $allowedStatuses = ['APPROVED', 'COMPLETED'];
        $selectedLeaves = $selectedLeaves->filter(function ($l) use ($allowedStatuses) {
            return in_array(strtoupper($l['status'] ?? ''), $allowedStatuses);
        })->values();

        if ($selectedLeaves->isEmpty()) {
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
            foreach ($selectedLeaves as $leave) {
                $vcNik = $leave['employee_nik'] ?? null;
                $startDate = $leave['start_date'] ?? null;
                $endDate = $leave['end_date'] ?? null;
                $leaveTypeName = $leave['leave_type_name'] ?? 'Cuti Tahunan';
                $reason = $leave['reason'] ?? '';

                if (!$vcNik || !$startDate || !$endDate) {
                    $errors[] = [
                        'nik' => $vcNik ?? 'N/A',
                        'error' => 'Data tidak lengkap (NIK, start_date, end_date wajib)',
                    ];
                    $skipped++;
                    continue;
                }

                $vcKodeAbsen = $this->hrisApi->mapLeaveTypeToKodeAbsen($leaveTypeName);

                // Validasi NIK ada di m_karyawan
                $karyawanExists = DB::table('m_karyawan')->where('Nik', $vcNik)->exists();
                if (!$karyawanExists) {
                    $errors[] = [
                        'nik' => $vcNik,
                        'error' => 'NIK tidak ditemukan di master karyawan',
                    ];
                    $skipped++;
                    continue;
                }

                // Validasi vcKodeAbsen ada di m_jenis_absen
                $kodeExists = DB::table('m_jenis_absen')->where('vcKodeAbsen', $vcKodeAbsen)->exists();
                if (!$kodeExists) {
                    $errors[] = [
                        'nik' => $vcNik,
                        'error' => "Kode absen {$vcKodeAbsen} tidak ada di m_jenis_absen",
                    ];
                    $skipped++;
                    continue;
                }

                $existing = DB::table('t_tidak_masuk')
                    ->where('vcNik', $vcNik)
                    ->where('vcKodeAbsen', $vcKodeAbsen)
                    ->where('dtTanggalMulai', $startDate)
                    ->where('dtTanggalSelesai', $endDate)
                    ->first();

                $data = [
                    'vcNik' => $vcNik,
                    'vcKodeAbsen' => $vcKodeAbsen,
                    'dtTanggalMulai' => $startDate,
                    'dtTanggalSelesai' => $endDate,
                    'vcKeterangan' => $reason ?: 'Import dari API HRIS',
                    'vcDibayar' => '1',
                    'dtChange' => Carbon::now(),
                ];

                if ($existing) {
                    DB::table('t_tidak_masuk')
                        ->where('vcNik', $vcNik)
                        ->where('vcKodeAbsen', $vcKodeAbsen)
                        ->where('dtTanggalMulai', $startDate)
                        ->where('dtTanggalSelesai', $endDate)
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
            Log::error('ListPengajuanCutiApi import error', [
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
