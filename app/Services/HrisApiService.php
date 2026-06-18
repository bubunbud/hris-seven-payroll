<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class HrisApiService
{
    protected string $baseUrl;
    protected $token = null;

    protected array $leaveTypeMapping = [
        'Cuti Tahunan' => 'C010',
        'Cuti Umroh' => 'C013',      // Umroh → C013
        'Umroh' => 'C013',
        'Cuti Bersama' => 'C012',
        'Cuti Melahirkan' => 'C012',
        'Cuti Perkawinan' => 'I001',  // Cuti Perkawinan karyawan → Izin Resmi
        'Cuti Kematian' => 'I001',   // Cuti Kematian Orang tua/Mertua → Izin Resmi
        'Sakit' => 'S010',
        'Sick' => 'S010',
        'Sick Leave' => 'S010',
        'Surat Sakit' => 'S010',
        'Cuti Sakit' => 'S010',
        'Medical' => 'S010',
        'Izin Pribadi' => 'I002',
        'Izin Resmi' => 'I001',
    ];

    public function __construct()
    {
        $this->baseUrl = rtrim(config('hris_api.base_url'), '/');
    }

    /**
     * Login ke API HRIS dan dapatkan token
     */
    public function login(): array
    {
        try {
            $response = HrisApiHttpFactory::base()->post($this->baseUrl . '/v1/auth/login', [
                'username' => config('hris_api.username'),
                'password' => config('hris_api.password'),
            ]);
        } catch (\Throwable $e) {
            Log::error('HRIS API Login HTTP exception', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return [
                'success' => false,
                'message' => 'Tidak dapat mencapai API HRIS: '.$e->getMessage().' '.HrisApiHttpFactory::sslDiagnosticsHint(),
            ];
        }

        if (!$response->successful()) {
            Log::error('HRIS API Login failed', [
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
            Log::error('HRIS API Login: body bukan JSON', ['body' => substr((string) $response->body(), 0, 500)]);

            return [
                'success' => false,
                'message' => HrApiOutboundInspector::nonJsonBodyAfterLogin($response),
            ];
        }

        $payload = $data['data'] ?? null;
        if (! is_array($payload)) {
            Log::warning('HRIS API Login: field data hilang atau bukan objek/array', []);

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
     * Fetch daftar leaves dari API (hanya status APPROVED)
     * API menggunakan pagination: default 20/page, max ~100/page.
     * Method ini akan fetch SEMUA halaman hingga data lengkap.
     *
     * @param string|null $token
     * @param string|null $startDate Format Y-m-d (default: 30 hari lalu)
     * @param string|null $endDate Format Y-m-d (default: hari ini)
     */
    public function getApprovedLeaves(?string $token = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $token = $token ?? $this->token;
        if (!$token) {
            $login = $this->login();
            if (!$login['success']) {
                return $login;
            }
            $token = $login['token'];
        }

        // Default: sebulan terakhir (30 hari)
        $endDate = $endDate ?? now()->format('Y-m-d');
        $startDate = $startDate ?? now()->subDays(30)->format('Y-m-d');

        $allLeaves = [];
        $page = 1;
        $pageSize = 100; // Max yang didukung API
        $totalPage = 1;

        do {
            $params = [
                'page' => $page,
                'page_size' => $pageSize,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];
            $url = $this->baseUrl . '/v1/leaves/requests?' . http_build_query($params);

            try {
                $response = HrisApiHttpFactory::withToken($token)->get($url);
            } catch (\Throwable $e) {
                Log::error('HRIS API Get Leaves exception', ['page' => $page, 'message' => $e->getMessage()]);
                return [
                    'success' => false,
                    'message' => 'Gagal HTTP saat mengambil leaves (halaman '.$page.'): '.$e->getMessage().' '.HrisApiHttpFactory::sslDiagnosticsHint(),
                    'data' => [],
                ];
            }

            if (!$response->successful()) {
                Log::error('HRIS API Get Leaves failed', [
                    'page' => $page,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [
                    'success' => false,
                    'message' => 'Gagal mengambil data leaves (halaman ' . $page . '): ' . ($response->json('message') ?? $response->body()),
                    'data' => [],
                ];
            }

            $data = $response->json();
            if (! is_array($data)) {
                Log::error('HRIS API Get Leaves: body bukan JSON', [
                    'page' => $page,
                    'body' => substr((string) $response->body(), 0, 500),
                ]);

                return [
                    'success' => false,
                    'message' => 'Respons API tidak valid (bukan JSON) pada halaman '.$page.'.',
                    'data' => [],
                ];
            }

            $leaves = $data['data'] ?? [];
            $paging = $data['paging'] ?? [];
            $totalPage = (int) ($paging['total_page'] ?? 1);

            $allLeaves = array_merge($allLeaves, $leaves);
            $page++;
        } while ($page <= $totalPage);

        // Filter: APPROVED dan COMPLETED (keduanya = cuti yang sudah disetujui)
        // COMPLETED = sudah disetujui dan selesai diambil
        $allowedStatuses = ['APPROVED', 'COMPLETED'];
        $approved = array_filter($allLeaves, function ($item) use ($allowedStatuses) {
            if (! is_array($item)) {
                return false;
            }

            return in_array(strtoupper((string) ($item['status'] ?? '')), $allowedStatuses);
        });

        return [
            'success' => true,
            'data' => array_values($approved),
            'meta' => [
                'total_fetched' => count($allLeaves),
                'total_approved' => count($approved),
            ],
        ];
    }
    /**
     * Fetch daftar leaves dari API (SEMUA status, tidak dibatasi Approved/Completed)
     * Untuk tampilan list agar user bisa melihat Pending, Rejected, dll.
     */
    public function getAllLeaves(?string $token = null, ?string $startDate = null, ?string $endDate = null): array
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

        $allLeaves = [];
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
            $url = $this->baseUrl . '/v1/leaves/requests?' . http_build_query($params);

            try {
                $response = HrisApiHttpFactory::withToken($token)->get($url);
            } catch (\Throwable $e) {
                Log::error('HRIS API Get Leaves exception', ['page' => $page, 'message' => $e->getMessage()]);
                return [
                    'success' => false,
                    'message' => 'Gagal HTTP saat mengambil leaves (halaman '.$page.'): '.$e->getMessage().' '.HrisApiHttpFactory::sslDiagnosticsHint(),
                    'data' => [],
                ];
            }

            if (!$response->successful()) {
                Log::error('HRIS API Get Leaves failed', [
                    'page' => $page,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [
                    'success' => false,
                    'message' => 'Gagal mengambil data leaves (halaman ' . $page . '): ' . ($response->json('message') ?? $response->body()),
                    'data' => [],
                ];
            }

            $data = $response->json();
            if (! is_array($data)) {
                Log::error('HRIS API Get Leaves (all): body bukan JSON', [
                    'page' => $page,
                    'body' => substr((string) $response->body(), 0, 500),
                ]);

                return [
                    'success' => false,
                    'message' => 'Respons API tidak valid (bukan JSON) pada halaman '.$page.'.',
                    'data' => [],
                ];
            }

            $leaves = $data['data'] ?? [];
            $paging = $data['paging'] ?? [];
            $totalPage = (int) ($paging['total_page'] ?? 1);

            $allLeaves = array_merge($allLeaves, $leaves);
            $page++;
        } while ($page <= $totalPage);

        return [
            'success' => true,
            'data' => array_values($allLeaves),
            'meta' => [
                'total_fetched' => count($allLeaves),
            ],
        ];
    }

    /**
     * Map leave_type_name dari API ke vcKodeAbsen lokal
     */
    public function mapLeaveTypeToKodeAbsen(string $leaveTypeName): ?string
    {
        $name = trim($leaveTypeName);
        foreach ($this->leaveTypeMapping as $apiName => $kode) {
            if (stripos($name, $apiName) !== false || stripos($apiName, $name) !== false) {
                return $kode;
            }
        }
        // Default: Cuti Tahunan jika tidak diketahui
        return 'C010';
    }

    /**
     * Get mapping untuk reference
     */
    public function getLeaveTypeMapping(): array
    {
        return $this->leaveTypeMapping;
    }
}
