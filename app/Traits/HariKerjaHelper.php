<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\HariLibur;
use App\Models\TukarHariKerja;

trait HariKerjaHelper
{
    /**
     * Cek apakah tanggal adalah hari kerja normal untuk karyawan tertentu
     * Mempertimbangkan: weekend, hari libur, dan tukar hari kerja
     * 
     * @param string|Carbon $tanggal
     * @param string|null $nik NIK karyawan (null untuk semua karyawan)
     * @return bool
     */
    protected function isHariKerjaNormal($tanggal, $nik = null)
    {
        $tanggalStr = $tanggal instanceof Carbon 
            ? $tanggal->format('Y-m-d') 
            : Carbon::parse($tanggal)->format('Y-m-d');
        
        $tanggalObj = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);
        
        // 1. Cek weekend
        $dow = (int) $tanggalObj->format('w'); // 0=Min, 6=Sabtu
        $isWeekend = ($dow === 0 || $dow === 6);
        
        if ($isWeekend) {
            // Weekend, tapi cek apakah ada tukar hari kerja (LIBUR_KE_KERJA)
            $query = TukarHariKerja::where('tanggal_libur', $tanggalStr)
                ->where('vcTipeTukar', 'LIBUR_KE_KERJA');
            
            if ($nik) {
                $query->where('nik', $nik);
            }
            
            $tukarHariKerja = $query->exists();
            
            return $tukarHariKerja; // Jika ada tukar, maka hari kerja normal
        }
        
        // 2. Cek hari libur
        $isHariLibur = HariLibur::where('dtTanggal', $tanggalStr)->exists();
        
        if ($isHariLibur) {
            // Hari libur, tapi cek apakah ada tukar hari kerja (LIBUR_KE_KERJA)
            $query = TukarHariKerja::where('tanggal_libur', $tanggalStr)
                ->where('vcTipeTukar', 'LIBUR_KE_KERJA');
            
            if ($nik) {
                $query->where('nik', $nik);
            }
            
            $tukarHariKerja = $query->exists();
            
            return $tukarHariKerja; // Jika ada tukar, maka hari kerja normal
        }
        
        // 3. Cek apakah hari kerja normal ditukar menjadi hari libur (KERJA_KE_LIBUR)
        // Untuk KERJA_KE_LIBUR, tanggal_libur adalah tanggal yang menjadi libur
        $query = TukarHariKerja::where('tanggal_libur', $tanggalStr)
            ->where('vcTipeTukar', 'KERJA_KE_LIBUR');
        
        if ($nik) {
            $query->where('nik', $nik);
        }
        
        $tukarHariLibur = $query->exists();
        
        if ($tukarHariLibur) {
            return false; // Hari kerja normal ditukar menjadi hari libur
        }
        
        // 4. Default: hari kerja normal
        return true;
    }

    /**
     * Get daftar hari libur (termasuk yang ditukar menjadi hari kerja)
     * Exclude hari kerja yang ditukar menjadi hari libur
     * 
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @param string|null $nik NIK karyawan (null untuk semua karyawan)
     * @return array Array of date strings (Y-m-d format)
     */
    protected function getHariLiburWithTukar($startDate, $endDate, $nik = null)
    {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);
        
        // Get hari libur dari master
        $hariLibur = HariLibur::whereBetween('dtTanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->pluck('dtTanggal')
            ->map(function ($d) {
                return $d instanceof Carbon ? $d->format('Y-m-d') : Carbon::parse($d)->format('Y-m-d');
            })
            ->toArray();
        
        // Tambahkan weekend
        $current = $start->copy();
        while ($current->lte($end)) {
            $dow = (int) $current->format('w');
            if ($dow === 0 || $dow === 6) {
                $tanggalStr = $current->format('Y-m-d');
                if (!in_array($tanggalStr, $hariLibur)) {
                    $hariLibur[] = $tanggalStr;
                }
            }
            $current->addDay();
        }
        
        // Exclude hari libur yang ditukar menjadi hari kerja (LIBUR_KE_KERJA)
        $query = TukarHariKerja::whereBetween('tanggal_libur', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->where('vcTipeTukar', 'LIBUR_KE_KERJA');
        
        if ($nik) {
            $query->where('nik', $nik);
        }
        
        $tukarLiburKeKerja = $query->pluck('tanggal_libur')
            ->map(function ($d) {
                return $d instanceof Carbon ? $d->format('Y-m-d') : Carbon::parse($d)->format('Y-m-d');
            })
            ->toArray();
        
        $hariLibur = array_diff($hariLibur, $tukarLiburKeKerja);
        
        // Tambahkan hari kerja yang ditukar menjadi hari libur (KERJA_KE_LIBUR)
        // Untuk KERJA_KE_LIBUR, tanggal_libur adalah tanggal yang menjadi libur
        $query2 = TukarHariKerja::whereBetween('tanggal_libur', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->where('vcTipeTukar', 'KERJA_KE_LIBUR');
        
        if ($nik) {
            $query2->where('nik', $nik);
        }
        
        $tukarKerjaKeLibur = $query2->pluck('tanggal_libur')
            ->map(function ($d) {
                return $d instanceof Carbon ? $d->format('Y-m-d') : Carbon::parse($d)->format('Y-m-d');
            })
            ->toArray();
        
        $hariLibur = array_merge($hariLibur, $tukarKerjaKeLibur);
        
        return array_unique($hariLibur);
    }

    /**
     * Hitung jumlah hari kerja normal (exclude Sabtu/Minggu & hari libur, dengan mempertimbangkan tukar hari kerja)
     * 
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @param string|null $nik NIK karyawan (null untuk semua karyawan)
     * @return int
     */
    protected function calculateHariKerjaWithTukar($startDate, $endDate, $nik = null)
    {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);
        
        $hariLibur = $this->getHariLiburWithTukar($startDate, $endDate, $nik);
        
        $jumlahHariKerja = 0;
        $current = $start->copy();
        
        while ($current->lte($end)) {
            $tanggalStr = $current->format('Y-m-d');
            $dow = (int) $current->format('w');
            $isWeekend = ($dow === 0 || $dow === 6);
            $isHoliday = in_array($tanggalStr, $hariLibur, true);
            
            if (!$isWeekend && !$isHoliday) {
                $jumlahHariKerja++;
            }
            
            $current->addDay();
        }
        
        return $jumlahHariKerja;
    }
}

