<?php

namespace App\Services;

use App\Models\MesinFingerprint;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Mithun\PhpZkteco\Libs\ZKTeco;

class ZkTecoFingerprintService
{
    /** State/type ZK: 0 = masuk, 1 = pulang */
    public const STATE_MASUK = 0;

    public const STATE_PULANG = 1;

    public function testConnection(MesinFingerprint $mesin): array
    {
        try {
            $zk = $this->makeClient($mesin);
            if (!$zk->connect()) {
                return [
                    'success' => false,
                    'message' => 'Gagal terhubung ke mesin ' . $mesin->vcNama . ' (' . $mesin->vcIp . ':' . $mesin->intPort . ')',
                ];
            }

            $zk->disconnect();

            return [
                'success' => true,
                'message' => 'Koneksi berhasil ke ' . $mesin->vcNama,
            ];
        } catch (\Throwable $e) {
            Log::error('ZK test connection failed', [
                'mesin_id' => $mesin->id,
                'ip' => $mesin->vcIp,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error koneksi: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * @return array{success:bool,message?:string,data?:array,meta?:array}
     */
    public function pullLogs(
        MesinFingerprint $mesin,
        string $startDate,
        string $endDate,
        ?string $nikFilter = null
    ): array {
        try {
            $zk = $this->makeClient($mesin);
            if (!$zk->connect()) {
                return [
                    'success' => false,
                    'message' => 'Gagal terhubung ke mesin ' . $mesin->vcNama . ' (' . $mesin->vcIp . ')',
                    'data' => [],
                ];
            }

            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $nikFilter = $nikFilter ? $this->normalizeNik($nikFilter) : null;

            $logs = $zk->getAttendances(function (array $record) use ($start, $end, $nikFilter, $mesin) {
                $recordTime = Carbon::parse($record['record_time']);
                if ($recordTime->lt($start) || $recordTime->gt($end)) {
                    return null;
                }

                $nik = $this->normalizeNik((string) ($record['user_id'] ?? ''));
                if (!$nik) {
                    return null;
                }

                if ($nikFilter && $nik !== $nikFilter) {
                    return null;
                }

                return [
                    'mesin_id' => $mesin->id,
                    'mesin_nama' => $mesin->vcNama,
                    'nik' => $nik,
                    'tanggal' => $recordTime->format('Y-m-d'),
                    'waktu' => $recordTime->format('Y-m-d H:i:s'),
                    'state' => (int) ($record['state'] ?? -1),
                    'type_raw' => (int) ($record['type'] ?? -1),
                    'type' => (int) ($record['state'] ?? $record['type'] ?? -1),
                    'verified' => 'Yes',
                    'work_code' => 0,
                    'uid' => $record['uid'] ?? null,
                ];
            });

            $zk->disconnect();

            $mesin->update([
                'dtLastPull' => now(),
                'dtChange' => now(),
            ]);

            return [
                'success' => true,
                'data' => array_values($logs),
                'meta' => [
                    'mesin' => $mesin->vcNama,
                    'ip' => $mesin->vcIp,
                    'total' => count($logs),
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('ZK pull logs failed', [
                'mesin_id' => $mesin->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal tarik log dari ' . $mesin->vcNama . ': ' . $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Agregasi log mentah → jam masuk (type 0 paling awal) & pulang (type 1 paling akhir) per NIK+tanggal.
     *
     * Field penentu masuk/pulang = 'type_raw' (punch type mesin). Field 'state' pada
     * mesin Solution X302-S/X100-C bernilai konstan sehingga tidak dipakai.
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
                    'mesin' => [],
                ];
            }

            $timeOnly = substr($waktu, 11, 8);
            $mesinNama = $log['mesin_nama'] ?? '-';
            if (!in_array($mesinNama, $groups[$key]['mesin'], true)) {
                $groups[$key]['mesin'][] = $mesinNama;
            }

            if ($type === self::STATE_MASUK) {
                if ($groups[$key]['jam_masuk'] === null || $timeOnly < $groups[$key]['jam_masuk']) {
                    $groups[$key]['jam_masuk'] = $timeOnly;
                }
            } elseif ($type === self::STATE_PULANG) {
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

    protected function makeClient(MesinFingerprint $mesin): ZKTeco
    {
        return new ZKTeco(
            host: $mesin->vcIp,
            port: (int) $mesin->intPort,
            shouldPing: false,
            timeout: 25,
            password: (int) ($mesin->intCommKey ?? 0),
            protocol: 'tcp',
        );
    }
}
