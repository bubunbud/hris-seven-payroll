<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class HrisApiPermitService
{
    protected string $baseUrl;
    protected $token = null;

    /**
     * Mapping purpose (permits API) ke vcKodeAbsen (t_tidak_masuk)
     * Izin: I002, Sakit: S010, Izin Resmi: I001
     */
    protected array $purposeMapping = [
        'SAKIT' => 'S010',
        'SICK' => 'S010',
        'SICK_LEAVE' => 'S010',
        'MEDICAL' => 'S010',
        'CUTI_SAKIT' => 'S010',
        'SURAT_SAKIT' => 'S010',
        'KELUAR_KOMPLEK' => 'I002',   // Izin keluar → Izin Pribadi
        'MASUK_SIANG' => 'I002',      // Izin masuk siang → Izin Pribadi
        'PULANG_CEPAT' => 'I002',     // Izin pulang cepat → Izin Pribadi
        'TIDAK_MASUK' => 'I002',
        'IZIN' => 'I002',
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
            Log::error('HRIS API Permit Login HTTP exception', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return [
                'success' => false,
                'message' => 'Tidak dapat mencapai API HRIS: '.$e->getMessage().' '.HrisApiHttpFactory::sslDiagnosticsHint(),
            ];
        }

        if (!$response->successful()) {
            Log::error('HRIS API Permit Login failed', [
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
            Log::error('HRIS API Permit Login: body bukan JSON', ['body' => substr((string) $response->body(), 0, 500)]);

            return [
                'success' => false,
                'message' => HrApiOutboundInspector::nonJsonBodyAfterLogin($response),
            ];
        }

        $payload = $data['data'] ?? null;
        if (! is_array($payload)) {
            Log::warning('HRIS API Permit Login: field data tidak valid');

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
     * Fetch daftar permits dari API (status APPROVED dan COMPLETED)
     * Endpoint: GET /v1/permits
     */
    public function getApprovedPermits(?string $token = null, ?string $startDate = null, ?string $endDate = null): array
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

        $allPermits = [];
        $page = 1;
        $pageSize = 100;
        $totalPage = 1;

        do {
            $params = [
                'page' => $page,
                'page_size' => $pageSize,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];
            $url = $this->baseUrl . '/v1/permits?' . http_build_query($params);

            try {
                $response = HrisApiHttpFactory::withToken($token)->get($url);
            } catch (\Throwable $e) {
                Log::error('HRIS API Get Permits exception', ['page' => $page, 'message' => $e->getMessage()]);
                return [
                    'success' => false,
                    'message' => 'Gagal HTTP permits (halaman '.$page.'): '.$e->getMessage().' '.HrisApiHttpFactory::sslDiagnosticsHint(),
                    'data' => [],
                ];
            }

            if (!$response->successful()) {
                Log::error('HRIS API Get Permits failed', [
                    'page' => $page,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [
                    'success' => false,
                    'message' => 'Gagal mengambil data permits (halaman ' . $page . '): ' . ($response->json('message') ?? $response->body()),
                    'data' => [],
                ];
            }

            $data = $response->json();
            $permits = $data['data'] ?? [];
            $paging = $data['paging'] ?? [];
            $totalPage = (int) ($paging['total_page'] ?? 1);

            $allPermits = array_merge($allPermits, $permits);
            $page++;
        } while ($page <= $totalPage);

        $allowedStatuses = ['APPROVED', 'COMPLETED'];
        $approved = array_filter($allPermits, fn($item) => in_array(strtoupper($item['status'] ?? ''), $allowedStatuses));

        // Filter: hanya Sakit (S010), tidak termasuk KELUAR_KOMPLEK/MASUK_SIANG/PULANG_CEPAT (I002)
        $allowedKodeAbsen = ['S010'];
        $filtered = array_filter($approved, function ($item) use ($allowedKodeAbsen) {
            $kode = $this->mapPermitToKodeAbsen($item);
            return in_array($kode, $allowedKodeAbsen);
        });

        return [
            'success' => true,
            'data' => array_values($filtered),
            'meta' => [
                'total_fetched' => count($allPermits),
                'total_approved' => count($approved),
                'total_filtered' => count($filtered),
            ],
        ];
    }

    /**
     * Fetch daftar permits dari API (SEMUA status, tidak dibatasi Approved/Completed)
     * Untuk tampilan list agar user bisa melihat Pending, Rejected, dll.
     */
    public function getAllPermits(?string $token = null, ?string $startDate = null, ?string $endDate = null): array
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

        $allPermits = [];
        $page = 1;
        $pageSize = 100;
        $totalPage = 1;

        do {
            $params = [
                'page' => $page,
                'page_size' => $pageSize,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];
            $url = $this->baseUrl . '/v1/permits?' . http_build_query($params);

            try {
                $response = HrisApiHttpFactory::withToken($token)->get($url);
            } catch (\Throwable $e) {
                Log::error('HRIS API Get Permits exception', ['page' => $page, 'message' => $e->getMessage()]);
                return [
                    'success' => false,
                    'message' => 'Gagal HTTP permits (halaman '.$page.'): '.$e->getMessage().' '.HrisApiHttpFactory::sslDiagnosticsHint(),
                    'data' => [],
                ];
            }

            if (!$response->successful()) {
                Log::error('HRIS API Get Permits failed', [
                    'page' => $page,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [
                    'success' => false,
                    'message' => 'Gagal mengambil data permits (halaman ' . $page . '): ' . ($response->json('message') ?? $response->body()),
                    'data' => [],
                ];
            }

            $data = $response->json();
            $permits = $data['data'] ?? [];
            $paging = $data['paging'] ?? [];
            $totalPage = (int) ($paging['total_page'] ?? 1);

            $allPermits = array_merge($allPermits, $permits);
            $page++;
        } while ($page <= $totalPage);

        // Filter: hanya Sakit (S010), tidak termasuk KELUAR_KOMPLEK/MASUK_SIANG/PULANG_CEPAT (I002)
        $allowedKodeAbsen = ['S010'];
        $filtered = array_filter($allPermits, function ($item) use ($allowedKodeAbsen) {
            $kode = $this->mapPermitToKodeAbsen($item);
            return in_array($kode, $allowedKodeAbsen);
        });

        return [
            'success' => true,
            'data' => array_values($filtered),
            'meta' => [
                'total_fetched' => count($allPermits),
                'total_filtered' => count($filtered),
            ],
        ];
    }

    /**
     * Daftar field yang mungkin berisi purpose/type dari API (beberapa API pakai nama berbeda)
     */
    protected array $purposeFields = [
        'purpose', 'type', 'permit_type', 'permit_type_name', 'permit_purpose',
        'category', 'name', 'request_type', 'leave_type', 'purpose_name', 'type_name',
    ];

    /**
     * Map purpose/type dari API ke vcKodeAbsen
     * Cek purpose, type, permit_type, dll (API bisa pakai field berbeda)
     */
    public function mapPermitToKodeAbsen(array $permit): ?string
    {
        $valuesToCheck = [];
        foreach ($this->purposeFields as $field) {
            $val = $permit[$field] ?? null;
            if ($val !== null && $val !== '') {
                $val = is_string($val) ? trim($val) : (string) $val;
                if ($val !== '') {
                    $valuesToCheck[] = strtoupper($val);
                }
            }
        }
        // Cek nested: permit_type.name, type.name, dll
        foreach (['permit_type', 'type', 'purpose'] as $key) {
            $obj = $permit[$key] ?? null;
            if (is_array($obj) && !empty($obj['name'])) {
                $valuesToCheck[] = strtoupper(trim((string) $obj['name']));
            }
        }

        foreach ($valuesToCheck as $val) {
            foreach ($this->purposeMapping as $apiPurpose => $kode) {
                if ($val === $apiPurpose || stripos($val, $apiPurpose) !== false) {
                    return $kode;
                }
            }
        }
        return 'I002'; // Default: Izin Pribadi
    }

    /**
     * @deprecated Gunakan mapPermitToKodeAbsen() untuk cek purpose + type
     */
    public function mapPurposeToKodeAbsen(string $purpose): ?string
    {
        return $this->mapPermitToKodeAbsen(['purpose' => $purpose]);
    }

    public function getPurposeMapping(): array
    {
        return $this->purposeMapping;
    }
}
