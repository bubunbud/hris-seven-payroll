<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * GET /v1/management/attendances/logs — log absensi harian (clock in/out).
 */
class HrisApiAttendanceLogService
{
    protected string $baseUrl;

    protected ?string $token = null;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('hris_api.base_url'), '/');
    }

    public function login(): array
    {
        try {
            $response = HrisApiHttpFactory::base()->post($this->baseUrl . '/v1/auth/login', [
                'username' => config('hris_api.username'),
                'password' => config('hris_api.password'),
            ]);
        } catch (\Throwable $e) {
            Log::error('HRIS API Attendance Login HTTP exception', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return [
                'success' => false,
                'message' => 'Tidak dapat mencapai API HRIS: ' . $e->getMessage() . ' ' . HrisApiHttpFactory::sslDiagnosticsHint(),
            ];
        }

        if (!$response->successful()) {
            Log::error('HRIS API Attendance Login failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => HrApiOutboundInspector::loginFailureHumanMessage($response),
            ];
        }

        $data = $response->json();
        if (!is_array($data)) {
            return [
                'success' => false,
                'message' => HrApiOutboundInspector::nonJsonBodyAfterLogin($response),
            ];
        }

        $payload = $data['data'] ?? null;
        if (!is_array($payload)) {
            return [
                'success' => false,
                'message' => 'Format respons login API tidak dikenali.',
            ];
        }

        $token = $payload['token'] ?? null;
        if (is_array($token)) {
            $token = $token['token'] ?? $token['access_token'] ?? null;
        }

        if (!($data['success'] ?? false) || empty($token)) {
            return [
                'success' => false,
                'message' => 'Token tidak diterima dari API',
            ];
        }

        $this->token = $token;

        return [
            'success' => true,
            'token' => $token,
        ];
    }

    /**
     * @param  string|null  $nik  Filter NIK di API (query param nik)
     */
    public function getAttendanceLogs(
        ?string $token = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $nik = null
    ): array {
        $token = $token ?? $this->token;
        if (!$token) {
            $login = $this->login();
            if (!$login['success']) {
                return $login;
            }
            $token = $login['token'];
        }

        $endDate = $endDate ?? now()->format('Y-m-d');
        $startDate = $startDate ?? now()->subDays(30)->format('Y-m-d');

        $allLogs = [];
        $page = 1;
        $pageSize = 100;
        $totalPage = 1;

        do {
            $params = array_filter([
                'page' => $page,
                'page_size' => $pageSize,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'nik' => $nik ? $this->normalizeNik($nik) : null,
            ]);

            $url = $this->baseUrl . config('hris_api.attendance_logs_path', '/v1/management/attendances/logs')
                . '?' . http_build_query($params);

            try {
                $response = HrisApiHttpFactory::withToken($token)->get($url);
            } catch (\Throwable $e) {
                Log::error('HRIS API Get Attendance Logs exception', [
                    'page' => $page,
                    'message' => $e->getMessage(),
                ]);

                return [
                    'success' => false,
                    'message' => 'Gagal HTTP attendance logs (halaman ' . $page . '): ' . $e->getMessage() . ' ' . HrisApiHttpFactory::sslDiagnosticsHint(),
                    'data' => [],
                ];
            }

            if (!$response->successful()) {
                Log::error('HRIS API Get Attendance Logs failed', [
                    'page' => $page,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'message' => 'Gagal mengambil data absensi (halaman ' . $page . '): ' . ($response->json('message') ?? $response->body()),
                    'data' => [],
                ];
            }

            $data = $response->json();
            if (!is_array($data)) {
                return [
                    'success' => false,
                    'message' => 'Respons API tidak valid (bukan JSON) pada halaman ' . $page . '.',
                    'data' => [],
                ];
            }

            $items = $data['data'] ?? [];
            if (!is_array($items)) {
                $items = [];
            }

            $paging = $data['paging'] ?? [];
            $totalPage = max(1, (int) ($paging['total_page'] ?? 1));

            $allLogs = array_merge($allLogs, $items);
            $page++;
        } while ($page <= $totalPage);

        return [
            'success' => true,
            'data' => array_values($allLogs),
            'meta' => [
                'total_fetched' => count($allLogs),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'nik_filter' => $nik ? $this->normalizeNik($nik) : null,
            ],
        ];
    }

    /**
     * Map satu baris API ke kolom t_absen.
     */
    public function mapLogToAbsenRow(array $item): ?array
    {
        $tanggal = $item['date'] ?? null;
        $nik = $this->normalizeNik($item['nik'] ?? $item['employee_nik'] ?? null);

        if (!$tanggal || !$nik) {
            return null;
        }

        $tanggal = is_string($tanggal) ? substr($tanggal, 0, 10) : $tanggal;

        $row = [
            'dtTanggal' => $tanggal,
            'vcNik' => $nik,
            'dtJamMasuk' => $this->normalizeTime($item['clock_in'] ?? null),
            'dtJamKeluar' => $this->normalizeTime($item['clock_out'] ?? null),
        ];

        return $row;
    }

    public function isEmptyTime(?string $time): bool
    {
        return $time === null || trim($time) === '';
    }

    public function normalizeNik(?string $nik): ?string
    {
        if ($nik === null) {
            return null;
        }

        $nik = trim($nik);
        if ($nik === '') {
            return null;
        }

        if (ctype_digit($nik) && strlen($nik) < 8) {
            return str_pad($nik, 8, '0', STR_PAD_LEFT);
        }

        return $nik;
    }

    public function normalizeTime(?string $time): ?string
    {
        if ($time === null || trim($time) === '') {
            return null;
        }

        $time = trim($time);
        if (strlen($time) === 5) {
            return $time . ':00';
        }

        return substr($time, 0, 8);
    }
}
