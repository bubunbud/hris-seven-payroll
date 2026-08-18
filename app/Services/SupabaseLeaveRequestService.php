<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseLeaveRequestService
{
    public const PROFILE_EMBED = 'profiles!leave_requests_user_id_fkey';

    /** @var array<string, string> */
    public const TYPE_TO_KODE = [
        'sakit' => 'S010',
        'izin' => 'I002',
        'cuti' => 'C010',
    ];

    public function getBaseRestUrl(): string
    {
        return rtrim(config('supabase.url'), '/') . '/rest/v1';
    }

    /**
     * @return array{success:bool,message?:string,data?:array<int,array<string,mixed>>,meta?:array}
     */
    public function pullLeaveRequests(string $startDate, string $endDate, ?string $nikFilter = null, ?array $types = null): array
    {
        $apiKey = config('supabase.api_key');
        if (!$apiKey) {
            return [
                'success' => false,
                'message' => 'SUPABASE_API_KEY belum dikonfigurasi di .env',
                'data' => [],
            ];
        }

        $start = Carbon::parse($startDate)->format('Y-m-d');
        $end = Carbon::parse($endDate)->format('Y-m-d');
        $nikFilter = $nikFilter ? $this->normalizeNik($nikFilter) : null;
        $allowedTypes = $types ? array_map('strtolower', $types) : ['sakit', 'izin', 'cuti'];

        $table = config('supabase.leave_requests_table', 'leave_requests');
        $pageSize = max(1, min((int) config('supabase.page_size', 1000), 1000));
        $timeout = (int) config('supabase.timeout', 60);

        $allRows = [];
        $offset = 0;

        try {
            while (true) {
                $andParts = [
                    'start_date.lte.' . $end,
                    'end_date.gte.' . $start,
                    'status.eq.approved',
                ];

                $query = [
                    'select' => 'id,user_id,type,start_date,end_date,reason,status,cuti_category,sick_note_url,created_at,' . self::PROFILE_EMBED . '(nik,name)',
                    'and' => '(' . implode(',', $andParts) . ')',
                    'order' => 'start_date.asc',
                ];

                if ($nikFilter) {
                    $andParts[] = self::PROFILE_EMBED . '.nik.eq.' . $nikFilter;
                    $query['and'] = '(' . implode(',', $andParts) . ')';
                }

                if (count($allowedTypes) < 3) {
                    $query['type'] = 'in.(' . implode(',', $allowedTypes) . ')';
                }

                $rangeEnd = $offset + $pageSize - 1;
                $response = Http::timeout($timeout)
                    ->withHeaders([
                        'apikey' => $apiKey,
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Accept' => 'application/json',
                        'Range' => "{$offset}-{$rangeEnd}",
                    ])
                    ->get($this->getBaseRestUrl() . '/' . $table, $query);

                if (!$response->successful()) {
                    $body = $response->json();
                    $msg = is_array($body) ? ($body['message'] ?? json_encode($body)) : $response->body();

                    return [
                        'success' => false,
                        'message' => 'Gagal tarik leave_requests (HTTP ' . $response->status() . '): ' . $msg,
                        'data' => [],
                    ];
                }

                $chunk = $response->json();
                if (!is_array($chunk)) {
                    return [
                        'success' => false,
                        'message' => 'Respons Supabase tidak valid.',
                        'data' => [],
                    ];
                }

                $allRows = array_merge($allRows, $chunk);

                if (count($chunk) < $pageSize) {
                    break;
                }

                $offset += $pageSize;
            }
        } catch (\Throwable $e) {
            Log::error('Supabase leave pull failed', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Error koneksi Supabase: ' . $e->getMessage(),
                'data' => [],
            ];
        }

        $mapped = [];
        $skipped = 0;
        foreach ($allRows as $row) {
            $item = $this->mapRow($row);
            if ($item !== null) {
                if (!in_array(strtolower($item['type']), $allowedTypes, true)) {
                    continue;
                }
                $mapped[] = $item;
            } else {
                $skipped++;
            }
        }

        return [
            'success' => true,
            'data' => $mapped,
            'meta' => [
                'total_raw' => count($allRows),
                'total_mapped' => count($mapped),
                'skipped_unmapped' => $skipped,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'table' => $table,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>|null
     */
    public function mapRow(array $row): ?array
    {
        $startDate = $row['start_date'] ?? null;
        $endDate = $row['end_date'] ?? null;
        $type = strtolower(trim((string) ($row['type'] ?? '')));

        if (!$startDate || !$endDate || $type === '') {
            return null;
        }

        $profileKey = self::PROFILE_EMBED;
        $profiles = $row[$profileKey] ?? $row['profiles'] ?? null;
        $nikRaw = is_array($profiles) ? ($profiles['nik'] ?? null) : null;
        $nik = $this->normalizeNik($nikRaw ? (string) $nikRaw : null);
        if (!$nik) {
            return null;
        }

        $vcKodeAbsen = $this->mapTypeToKodeAbsen($type, $row['cuti_category'] ?? null);
        if (!$vcKodeAbsen) {
            return null;
        }

        $reason = trim((string) ($row['reason'] ?? ''));
        if ($reason === '') {
            $reason = 'Import dari Supabase (' . $this->typeLabel($type) . ')';
        }

        return [
            'supabase_id' => $row['id'] ?? null,
            'user_id' => $row['user_id'] ?? null,
            'nik' => $nik,
            'nama' => is_array($profiles) ? ($profiles['name'] ?? null) : null,
            'type' => $type,
            'type_label' => $this->typeLabel($type),
            'cuti_category' => $row['cuti_category'] ?? null,
            'status' => $row['status'] ?? null,
            'vcKodeAbsen' => $vcKodeAbsen,
            'dtTanggalMulai' => Carbon::parse($startDate)->format('Y-m-d'),
            'dtTanggalSelesai' => Carbon::parse($endDate)->format('Y-m-d'),
            'jumlah_hari' => Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1,
            'vcKeterangan' => mb_substr($reason, 0, 250),
            'sick_note_url' => $row['sick_note_url'] ?? null,
            'created_at' => $row['created_at'] ?? null,
        ];
    }

    public function mapTypeToKodeAbsen(string $type, ?string $cutiCategory = null): ?string
    {
        $type = strtolower(trim($type));

        if ($type === 'cuti') {
            $cat = strtolower(trim((string) $cutiCategory));

            return match ($cat) {
                'melahirkan' => 'C012',
                default => 'C010',
            };
        }

        return self::TYPE_TO_KODE[$type] ?? null;
    }

    public function typeLabel(string $type): string
    {
        return match (strtolower($type)) {
            'sakit' => 'Sakit',
            'izin' => 'Izin Pribadi',
            'cuti' => 'Cuti',
            default => ucfirst($type),
        };
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
}
