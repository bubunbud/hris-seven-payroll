<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseAttendanceService
{
  /** type Supabase: 0 = masuk, 1 = pulang */
  public const TYPE_MASUK = 0;

  public const TYPE_PULANG = 1;

  public function getBaseRestUrl(): string
  {
    return rtrim(config('supabase.url'), '/') . '/rest/v1';
  }

  /**
   * @return array{success:bool,message?:string,data?:array<int,array<string,mixed>>,meta?:array}
   */
  public function pullLogs(string $startDate, string $endDate, ?string $nikFilter = null): array
  {
    $apiKey = config('supabase.api_key');
    if (!$apiKey) {
      return [
        'success' => false,
        'message' => 'SUPABASE_API_KEY belum dikonfigurasi di .env',
        'data' => [],
      ];
    }

    $start = $this->parseLocalDate($startDate)->startOfDay();
    $end = $this->parseLocalDate($endDate)->endOfDay();
    $nikFilter = $nikFilter ? $this->normalizeNik($nikFilter) : null;

    $table = config('supabase.attendance_table', 'attendance');
    $pageSize = max(1, min((int) config('supabase.page_size', 1000), 1000));
    $timeout = (int) config('supabase.timeout', 60);

    $allRows = [];
    $offset = 0;

    try {
      while (true) {
        $query = [
          'select' => 'id,user_id,type,timestamp,profiles(nik,name)',
          'order' => 'timestamp.asc',
        ];

        $andParts = [
          'timestamp.gte.' . $start->toIso8601String(),
          'timestamp.lte.' . $end->toIso8601String(),
        ];
        if ($nikFilter) {
          $andParts[] = 'profiles.nik.eq.' . $nikFilter;
          $query['select'] = 'id,user_id,type,timestamp,profiles!inner(nik,name)';
        }
        $query['and'] = '(' . implode(',', $andParts) . ')';

        $rangeEnd = $offset + $pageSize - 1;
        $response = Http::timeout($timeout)
          ->withHeaders([
            'apikey' => $apiKey,
            'Authorization' => 'Bearer ' . $apiKey,
            'Accept' => 'application/json',
            'Range' => "{$offset}-{$rangeEnd}",
            'Prefer' => 'count=exact',
          ])
          ->get($this->getBaseRestUrl() . '/' . $table, $query);

        if (!$response->successful()) {
          $body = $response->json();
          $msg = is_array($body) ? ($body['message'] ?? json_encode($body)) : $response->body();

          return [
            'success' => false,
            'message' => 'Gagal tarik data Supabase (HTTP ' . $response->status() . '): ' . $msg,
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
      Log::error('Supabase pull failed', ['error' => $e->getMessage()]);

      return [
        'success' => false,
        'message' => 'Error koneksi Supabase: ' . $e->getMessage(),
        'data' => [],
      ];
    }

    $mapped = [];
    $skippedNoNik = 0;
    foreach ($allRows as $row) {
      $normalized = $this->mapRow($row);
      if ($normalized !== null) {
        $mapped[] = $normalized;
      } else {
        $skippedNoNik++;
      }
    }

    return [
      'success' => true,
      'data' => $mapped,
      'meta' => [
        'total_raw' => count($allRows),
        'total_mapped' => count($mapped),
        'skipped_no_nik' => $skippedNoNik,
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
  protected function mapRow(array $row): ?array
  {
    $timestamp = $row['timestamp'] ?? null;
    if (!$timestamp) {
      return null;
    }

    $profiles = $row['profiles'] ?? null;
    $nikRaw = is_array($profiles) ? ($profiles['nik'] ?? null) : null;

    // Fallback: user_id bisa berisi NIK langsung jika tabel profiles belum terisi
    if (!$nikRaw && isset($row['user_id']) && $this->looksLikeNik($row['user_id'])) {
      $nikRaw = (string) $row['user_id'];
    }

    $nik = $this->normalizeNik($nikRaw ? (string) $nikRaw : null);
    if (!$nik) {
      return null;
    }

    $recordTime = $this->parseTimestampToLocal($timestamp);
    $typeRaw = $this->resolveType($row['type'] ?? null);

    return [
      'supabase_id' => $row['id'] ?? null,
      'user_id' => $row['user_id'] ?? null,
      'nik' => $nik,
      'nama' => is_array($profiles) ? ($profiles['name'] ?? null) : null,
      'tanggal' => $recordTime->format('Y-m-d'),
      'waktu' => $recordTime->format('Y-m-d H:i:s'),
      'type_raw' => $typeRaw,
      'type' => $typeRaw,
      'sumber' => 'Supabase',
    ];
  }

  protected function resolveType(mixed $type): int
  {
    if (is_numeric($type)) {
      return (int) $type;
    }

    $s = strtolower(trim((string) $type));

    return match ($s) {
      'check_in', 'in', 'masuk', 'clock_in' => self::TYPE_MASUK,
      'check_out', 'out', 'pulang', 'clock_out' => self::TYPE_PULANG,
      default => -1,
    };
  }

  /**
   * Agregasi log mentah → jam masuk (type 0 paling awal) & pulang (type 1 paling akhir) per NIK+tanggal.
   *
   * @param  array<int, array<string, mixed>>  $rawLogs
   * @return array<int, array<string, mixed>>
   */
  public function aggregateLogs(array $rawLogs): array
  {
    $groups = [];

    foreach ($rawLogs as $log) {
      $nik = $log['nik'] ?? null;
      $tanggal = $log['tanggal'] ?? null;
      $waktu = $log['waktu'] ?? null;
      $type = (int) ($log['type_raw'] ?? $log['type'] ?? -1);

      if (!$nik || !$tanggal || !$waktu) {
        continue;
      }

      $key = $tanggal . '|' . $nik;
      if (!isset($groups[$key])) {
        $groups[$key] = [
          'tanggal' => $tanggal,
          'nik' => $nik,
          'jam_masuk' => null,
          'jam_pulang' => null,
          'sumber' => ['Supabase'],
        ];
      }

      $timeOnly = substr($waktu, 11, 8);

      if ($type === self::TYPE_MASUK) {
        if ($groups[$key]['jam_masuk'] === null || $timeOnly < $groups[$key]['jam_masuk']) {
          $groups[$key]['jam_masuk'] = $timeOnly;
        }
      } elseif ($type === self::TYPE_PULANG) {
        if ($groups[$key]['jam_pulang'] === null || $timeOnly > $groups[$key]['jam_pulang']) {
          $groups[$key]['jam_pulang'] = $timeOnly;
        }
      }
    }

    $result = array_values($groups);
    usort($result, fn ($a, $b) => [$a['tanggal'], $a['nik']] <=> [$b['tanggal'], $b['nik']]);

    return $result;
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

  public function isEmptyTime(?string $time): bool
  {
    return $time === null || trim($time) === '';
  }

  protected function looksLikeNik(mixed $value): bool
  {
    $s = trim((string) $value);

    return $s !== '' && ctype_digit($s) && strlen($s) >= 4 && strlen($s) <= 20;
  }

  protected function localTimezone(): string
  {
    return (string) config('supabase.timezone', config('app.timezone', 'Asia/Jakarta'));
  }

  protected function parseLocalDate(string $date): Carbon
  {
    return Carbon::parse($date, $this->localTimezone());
  }

  /**
   * Supabase menyimpan timestamptz UTC; konversi ke WIB (atau timezone aplikasi) sebelum simpan ke t_absen.
   */
  protected function parseTimestampToLocal(mixed $timestamp): Carbon
  {
    return Carbon::parse($timestamp)->timezone($this->localTimezone());
  }
}
