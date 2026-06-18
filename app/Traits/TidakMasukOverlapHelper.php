<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Collection;

trait TidakMasukOverlapHelper
{
    /**
     * Hitung jumlah hari unik (per tanggal) untuk satu jenis absen dalam periode closing.
     * Satu tanggal hanya dihitung sekali meskipun ada banyak record overlap.
     */
    protected function countUniqueTidakMasukDays(
        string $nik,
        string $kodeAbsen,
        Carbon $tanggalAwal,
        Carbon $tanggalAkhir,
        Collection $records = null
    ): int {
        if ($records === null) {
            $records = \App\Models\TidakMasuk::where('vcNik', $nik)
                ->where('vcKodeAbsen', $kodeAbsen)
                ->where('dtTanggalMulai', '<=', $tanggalAkhir->format('Y-m-d'))
                ->where('dtTanggalSelesai', '>=', $tanggalAwal->format('Y-m-d'))
                ->get();
        }

        $uniqueDates = [];

        foreach ($records as $record) {
            if (!$record->dtTanggalMulai || !$record->dtTanggalSelesai) {
                continue;
            }

            $mulai = Carbon::parse($record->dtTanggalMulai);
            $selesai = Carbon::parse($record->dtTanggalSelesai);

            $overlapMulai = $mulai->greaterThan($tanggalAwal) ? $mulai->copy() : $tanggalAwal->copy();
            $overlapSelesai = $selesai->lessThan($tanggalAkhir) ? $selesai->copy() : $tanggalAkhir->copy();

            if ($overlapMulai->gt($overlapSelesai)) {
                continue;
            }

            $current = $overlapMulai->copy();
            $guard = 0;
            while ($current->lte($overlapSelesai) && $guard < 366) {
                $uniqueDates[$current->format('Y-m-d')] = true;
                $current->addDay();
                $guard++;
            }
        }

        return count($uniqueDates);
    }

    /**
     * Expand t_tidak_masuk ke baris per tanggal; satu NIK + satu tanggal = satu baris.
     * Jika banyak record overlap, ambil rentang terpendek (paling spesifik).
     *
     * @param  iterable  $tidakMasukRecords
     * @param  \Illuminate\Support\Collection|array  $absenExists  key: "Y-m-d_NIK"
     * @return array<int, array<string, mixed>>
     */
    protected function expandTidakMasukUniquePerDay(
        iterable $tidakMasukRecords,
        Carbon $filterStart,
        Carbon $filterEnd,
        $absenExists
    ): array {
        $byDay = [];

        foreach ($tidakMasukRecords as $tm) {
            if (empty($tm->dtTanggalMulai) || empty($tm->dtTanggalSelesai)) {
                continue;
            }

            $current = Carbon::parse($tm->dtTanggalMulai);
            $end = Carbon::parse($tm->dtTanggalSelesai);
            $spanDays = $current->diffInDays($end);

            $guard = 0;
            while ($current->lte($end) && $guard < 366) {
                if ($current->lt($filterStart) || $current->gt($filterEnd)) {
                    $current->addDay();
                    $guard++;
                    continue;
                }

                $tanggalStr = $current->format('Y-m-d');
                $key = $tanggalStr . '_' . $tm->vcNik;

                if ($absenExists instanceof Collection ? $absenExists->has($key) : !empty($absenExists[$key])) {
                    $current->addDay();
                    $guard++;
                    continue;
                }

                $candidate = [
                    'dtTanggal' => $tanggalStr,
                    'vcNik' => $tm->vcNik,
                    'Nama' => $tm->Nama,
                    'Divisi' => $tm->Divisi,
                    'vcKodeBagian' => $tm->vcKodeBagian,
                    'vcNamaDivisi' => $tm->vcNamaDivisi,
                    'vcNamaDept' => $tm->vcNamaDept ?? null,
                    'vcNamaBagian' => $tm->vcNamaBagian,
                    'vcKodeAbsen' => $tm->vcKodeAbsen,
                    'jenis_absen_keterangan' => $tm->jenis_absen_keterangan,
                    'vcKeterangan' => $tm->vcKeterangan,
                    'source' => 'tidak_masuk',
                    '_span_days' => $spanDays,
                    '_mulai' => (string) $tm->dtTanggalMulai,
                    '_selesai' => (string) $tm->dtTanggalSelesai,
                ];

                if (!isset($byDay[$key]) || $spanDays < ($byDay[$key]['_span_days'] ?? PHP_INT_MAX)) {
                    $byDay[$key] = $candidate;
                }

                $current->addDay();
                $guard++;
            }
        }

        return array_values(array_map(function ($row) {
            unset($row['_span_days'], $row['_mulai'], $row['_selesai']);

            return $row;
        }, $byDay));
    }
}
