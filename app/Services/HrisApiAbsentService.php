<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Service untuk API HRIS Absents (Tidak Masuk Kerja)
 * Endpoint: GET /v1/absents/requests - daftar pengajuan tidak masuk (Sakit, dll)
 */
class HrisApiAbsentService
{
    protected string $baseUrl;
    protected $token = null;

    /**
     * Mapping type/absent_type dari API ke vcKodeAbsen (t_tidak_masuk)
     * Sakit: S010, Izin Pribadi: I002 (hanya status Approved/Completed)
     */
    protected array $typeMapping = [
        'SAKIT' => 'S010',
        'SICK' => 'S010',
        'SICK_LEAVE' => 'S010',
        'MEDICAL' => 'S010',
        'CUTI_SAKIT' => 'S010',
        'SURAT_SAKIT' => 'S010',
        'IZIN' => 'I002',
        'IZIN_PRIBADI' => 'I002',
        'PERSONAL_LEAVE' => 'I002',
        'TIDAK_MASUK' => 'I002',
    ];

    protected array $typeFields = [
        'type', 'absent_type', 'absent_type_name', 'reason_type', 'category',
        'purpose', 'name', 'leave_type', 'request_type',
    ];

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
            Log::error('HRIS API Absent Login HTTP exception', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return [
                'success' => false,
                'message' => 'Tidak dapat mencapai API HRIS: '.$e->getMessage().' '.HrisApiHttpFactory::sslDiagnosticsHint(),
            ];
        }

        if (!$response->successful()) {
            Log::error('HRIS API Absent Login failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return [
                'success' => false,
                'message' => HrApiOutboundInspector::loginFailureHumanMessage($response),
            ];
        }

        $data = $response->json();
        if (! is_array($data)) {
            Log::error('HRIS API Absent Login: body bukan JSON', ['body' => substr((string) $response->body(), 0, 500)]);

            return [
                'success' => false,
                'message' => HrApiOutboundInspector::nonJsonBodyAfterLogin($response),
            ];
        }

        $payload = $data['data'] ?? null;
        if (! is_array($payload)) {
            Log::warning('HRIS API Absent Login: field data tidak valid');

            return [
                'success' => false,
                'message' => 'Format respons login API tidak dikenali.',
            ];
        }

        $token = $payload['token'] ?? null;
        if (is_array($token)) {
            $token = $token['token'] ?? $token['access_token'] ?? null;
        }
        if (! ($data['success'] ?? false) || empty($token)) {
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
     * Fetch daftar absents dari API (GET /v1/absents/requests)
     * Filter: hanya Sakit (S010)
     */
    public function getAbsents(?string $token = null, ?string $startDate = null, ?string $endDate = null, bool $subordinate = false): array
    {
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

        $allAbsents = [];
        $page = 1;
        $pageSize = 100;
        $totalPage = 1;

        $path = $subordinate ? '/v1/absents/requests/subordinate' : '/v1/absents/requests';

        do {
            $params = array_filter([
                'page' => $page,
                'page_size' => $pageSize,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
            $url = $this->baseUrl . $path . '?' . http_build_query($params);

            try {
                $response = HrisApiHttpFactory::withToken($token)->get($url);
            } catch (\Throwable $e) {
                Log::error('HRIS API Get Absents exception', ['page' => $page, 'message' => $e->getMessage()]);
                return [
                    'success' => false,
                    'message' => 'Gagal HTTP absents (halaman '.$page.'): '.$e->getMessage().' '.HrisApiHttpFactory::sslDiagnosticsHint(),
                    'data' => [],
                ];
            }

            if (!$response->successful()) {
                Log::error('HRIS API Get Absents failed', [
                    'path' => $path,
                    'page' => $page,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [
                    'success' => false,
                    'message' => 'Gagal mengambil data absents (halaman ' . $page . '): ' . ($response->json('message') ?? $response->body()),
                    'data' => [],
                ];
            }

            $data = $response->json();
            $items = $data['data'] ?? $data['absent_requests'] ?? $data['results'] ?? $data['items'] ?? [];
            if (!is_array($items)) {
                $items = [];
            }
            $paging = $data['paging'] ?? [];
            $totalPage = (int) ($paging['total_page'] ?? 1);
            if ($totalPage < 1) {
                $totalPage = 1;
            }

            $allAbsents = array_merge($allAbsents, $items);
            $page++;
        } while ($page <= $totalPage);

        // Filter: Sakit (S010) dan Izin Pribadi (I002) — hanya status Approved/Completed
        $allowedKode = ['S010', 'I002'];
        $allowedStatuses = ['APPROVED', 'COMPLETED'];
        $filtered = array_filter($allAbsents, function ($item) use ($allowedKode, $allowedStatuses) {
            $kode = $this->mapAbsentToKodeAbsen($item);
            if (!in_array($kode, $allowedKode)) {
                return false;
            }
            $status = strtoupper($item['status'] ?? '');
            return in_array($status, $allowedStatuses);
        });

        return [
            'success' => true,
            'data' => array_values($this->normalizeToPermitFormat($filtered)),
            'meta' => [
                'total_fetched' => count($allAbsents),
                'total_filtered' => count($filtered),
            ],
        ];
    }

    /**
     * Map type/absent_type dari API ke vcKodeAbsen
     */
    public function mapAbsentToKodeAbsen(array $item): ?string
    {
        $valuesToCheck = [];
        foreach ($this->typeFields as $field) {
            $val = $item[$field] ?? null;
            if ($val !== null && $val !== '') {
                $val = is_string($val) ? trim($val) : (string) $val;
                if ($val !== '') {
                    $valuesToCheck[] = strtoupper($val);
                }
            }
        }
        foreach (['type', 'absent_type'] as $key) {
            $obj = $item[$key] ?? null;
            if (is_array($obj) && !empty($obj['name'])) {
                $valuesToCheck[] = strtoupper(trim((string) $obj['name']));
            }
        }

        foreach ($valuesToCheck as $val) {
            foreach ($this->typeMapping as $apiType => $kode) {
                if ($val === $apiType || stripos($val, $apiType) !== false) {
                    return $kode;
                }
            }
        }
        return null;
    }

    /**
     * Normalisasi response API ke format standar (compat dengan view)
     */
    protected function normalizeToPermitFormat(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            $start = $item['start_date'] ?? $item['date'] ?? $item['absent_date'] ?? $item['from_date'] ?? null;
            $end = $item['end_date'] ?? $item['date'] ?? $item['absent_date'] ?? $item['to_date'] ?? $start;

            if (!$start) {
                continue;
            }

            $start = is_string($start) ? substr($start, 0, 10) : $start;
            $end = is_string($end) ? substr($end, 0, 10) : $end;

            $typeVal = $item['type'] ?? $item['absent_type'] ?? $item['absent_type_name'] ?? 'Sakit';
            if (is_array($typeVal)) {
                $typeVal = $typeVal['name'] ?? $typeVal['label'] ?? 'Sakit';
            }

            $mappedKode = $this->mapAbsentToKodeAbsen($item) ?? 'S010';

            $dates = $this->dateRange($start, $end);
            $itemId = $item['id'] ?? uniqid('A');

            foreach ($dates as $d) {
                $result[] = [
                    'id' => 'A-' . $itemId . '-' . $d,
                    'employee_nik' => $item['employee_nik'] ?? $item['nik'] ?? $item['employee_id'] ?? null,
                    'first_name' => $item['first_name'] ?? $item['employee']['first_name'] ?? '',
                    'middle_name' => $item['middle_name'] ?? $item['employee']['middle_name'] ?? '',
                    'last_name' => $item['last_name'] ?? $item['employee']['last_name'] ?? '',
                    'purpose' => $typeVal,
                    'type' => $typeVal,
                    'date' => $d,
                    'date_formatted' => $d,
                    'reason' => $item['reason'] ?? $item['description'] ?? $item['notes'] ?? '',
                    'status' => $item['status'] ?? '',
                    'mapped_kode' => $mappedKode,
                    '_source' => 'absents',
                ];
            }
        }
        return $result;
    }

    protected function dateRange(string $start, string $end): array
    {
        $dates = [];
        $current = \Carbon\Carbon::parse($start);
        $endDate = \Carbon\Carbon::parse($end);
        while ($current->lte($endDate)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }
        return $dates;
    }

    public function getTypeMapping(): array
    {
        return $this->typeMapping;
    }
}
