<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Karyawan;
use App\Models\Divisi;
use App\Models\Closing;
use App\Models\PeriodeGaji;

class DashboardGroupController extends Controller
{
    /**
     * Display Dashboard Level Group (Holding View)
     */
    public function index()
    {
        // A. Ringkasan SDM Group
        $sdmSummary = $this->getSDMSummary();

        // B. Headcount Movement
        $headcountMovement = $this->getHeadcountMovement();

        // C. Payroll Summary Group
        $payrollSummary = $this->getPayrollSummary();

        // D. Alert & Notifikasi Eksekutif
        $executiveAlerts = $this->getExecutiveAlerts();

        return view('dashboard.group.index', compact(
            'sdmSummary',
            'headcountMovement',
            'payrollSummary',
            'executiveAlerts'
        ));
    }

    /**
     * A. Ringkasan SDM Group
     */
    private function getSDMSummary()
    {
        // Total karyawan aktif (All BU)
        $totalKaryawan = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->count();

        // Komposisi Status Pegawai (Tetap / Kontrak / Outsource)
        $komposisiStatus = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->select('Status_Pegawai', DB::raw('count(*) as jumlah'))
            ->groupBy('Status_Pegawai')
            ->pluck('jumlah', 'Status_Pegawai')
            ->toArray();

        // Komposisi Group Pegawai - menggunakan Group_pegawai
        $komposisiGroupPegawai = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->whereNotNull('Group_pegawai')
            ->where('Group_pegawai', '!=', '')
            ->select('Group_pegawai', DB::raw('count(*) as jumlah'))
            ->groupBy('Group_pegawai')
            ->pluck('jumlah', 'Group_pegawai')
            ->toArray();

        // Perbandingan jumlah karyawan per BU (Divisi)
        $karyawanPerBU = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->join('m_divisi', 'm_karyawan.Divisi', '=', 'm_divisi.vcKodeDivisi')
            ->select('m_divisi.vcNamaDivisi', DB::raw('count(*) as jumlah'))
            ->groupBy('m_divisi.vcKodeDivisi', 'm_divisi.vcNamaDivisi')
            ->orderBy('jumlah', 'desc')
            ->get();

        // Rasio gender
        $rasioGenderRaw = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->whereNotNull('Jenis_Kelamin')
            ->where('Jenis_Kelamin', '!=', '')
            ->select('Jenis_Kelamin', DB::raw('count(*) as jumlah'))
            ->groupBy('Jenis_Kelamin')
            ->get();
        
        // Normalize gender values (handle both "Laki-laki"/"Perempuan" and "L"/"P")
        $rasioGender = [
            'Laki-laki' => 0,
            'Perempuan' => 0
        ];
        
        foreach ($rasioGenderRaw as $item) {
            $jk = trim($item->Jenis_Kelamin);
            if (stripos($jk, 'laki') !== false || $jk == 'L' || $jk == 'Laki-laki') {
                $rasioGender['Laki-laki'] += $item->jumlah;
            } elseif (stripos($jk, 'perempuan') !== false || $jk == 'P' || $jk == 'Perempuan') {
                $rasioGender['Perempuan'] += $item->jumlah;
            }
        }

        // Rata-rata usia
        $karyawanWithAge = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->whereNotNull('TTL')
            ->get();

        $totalUsia = 0;
        $countUsia = 0;
        foreach ($karyawanWithAge as $karyawan) {
            if ($karyawan->TTL) {
                $usia = Carbon::parse($karyawan->TTL)->age;
                $totalUsia += $usia;
                $countUsia++;
            }
        }
        $rataRataUsia = $countUsia > 0 ? round($totalUsia / $countUsia, 1) : 0;

        // Rata-rata masa kerja (dalam tahun)
        $karyawanWithTglMasuk = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->whereNotNull('Tgl_Masuk')
            ->get();

        $totalMasaKerja = 0;
        $countMasaKerja = 0;
        foreach ($karyawanWithTglMasuk as $karyawan) {
            if ($karyawan->Tgl_Masuk) {
                $masaKerja = Carbon::parse($karyawan->Tgl_Masuk)->diffInYears(Carbon::now());
                $totalMasaKerja += $masaKerja;
                $countMasaKerja++;
            }
        }
        $rataRataMasaKerja = $countMasaKerja > 0 ? round($totalMasaKerja / $countMasaKerja, 1) : 0;

        return [
            'total_karyawan' => $totalKaryawan,
            'komposisi_status' => $komposisiStatus,
            'komposisi_group_pegawai' => $komposisiGroupPegawai,
            'karyawan_per_bu' => $karyawanPerBU,
            'rasio_gender' => $rasioGender,
            'rata_rata_usia' => $rataRataUsia,
            'rata_rata_masa_kerja' => $rataRataMasaKerja,
        ];
    }

    /**
     * B. Headcount Movement (Per Tahun)
     */
    private function getHeadcountMovement()
    {
        $tahunIni = Carbon::now()->startOfYear();
        $tahunIniEnd = Carbon::now()->endOfYear();
        $tahunLalu = Carbon::now()->subYear()->startOfYear();
        $tahunLaluEnd = Carbon::now()->subYear()->endOfYear();

        // Join tahun ini (Tgl_Masuk dalam tahun berjalan)
        $joinTahunIni = Karyawan::where('vcAktif', '1')
            ->whereBetween('Tgl_Masuk', [$tahunIni->format('Y-m-d'), $tahunIniEnd->format('Y-m-d')])
            ->count();

        // Resign tahun ini (Tgl_Berhenti dalam tahun berjalan)
        $resignTahunIni = Karyawan::whereBetween('Tgl_Berhenti', [$tahunIni->format('Y-m-d'), $tahunIniEnd->format('Y-m-d')])
            ->count();

        // Headcount tahun lalu (akhir tahun lalu)
        $headcountTahunLalu = Karyawan::where('vcAktif', '1')
            ->where(function ($q) use ($tahunLaluEnd) {
                $q->whereNull('Tgl_Berhenti')
                  ->orWhere('Tgl_Berhenti', '>', $tahunLaluEnd->format('Y-m-d'));
            })
            ->where(function ($q) use ($tahunLaluEnd) {
                $q->whereNull('Tgl_Masuk')
                  ->orWhere('Tgl_Masuk', '<=', $tahunLaluEnd->format('Y-m-d'));
            })
            ->count();

        // Headcount tahun ini (akhir tahun ini / sekarang)
        $headcountTahunIni = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->count();

        // Turnover rate (%) = (Resign / Average Headcount) * 100
        $averageHeadcount = ($headcountTahunLalu + $headcountTahunIni) / 2;
        $turnoverRate = $averageHeadcount > 0 ? round(($resignTahunIni / $averageHeadcount) * 100, 2) : 0;

        // Net growth karyawan
        $netGrowth = $joinTahunIni - $resignTahunIni;

        return [
            'join_tahun_ini' => $joinTahunIni,
            'resign_tahun_ini' => $resignTahunIni,
            'turnover_rate' => $turnoverRate,
            'net_growth' => $netGrowth,
            'headcount_tahun_lalu' => $headcountTahunLalu,
            'headcount_tahun_ini' => $headcountTahunIni,
        ];
    }

    /**
     * C. Payroll Summary Group
     */
    private function getPayrollSummary()
    {
        $bulanIni = Carbon::now()->startOfMonth();
        $bulanIniEnd = Carbon::now()->endOfMonth();
        $tahunIni = Carbon::now()->startOfYear();
        $tahunIniEnd = Carbon::now()->endOfYear();

        // Total payroll bulan berjalan (Take Home Pay = Gaji Pokok + Tunjangan - Potongan)
        $payrollBulanIni = DB::table('t_closing')
            ->whereBetween('periode', [$bulanIni->format('Y-m-d'), $bulanIniEnd->format('Y-m-d')])
            ->select(DB::raw('SUM(
                COALESCE(decGapok, 0) + 
                COALESCE(decUangMakan, 0) + 
                COALESCE(decTransport, 0) + 
                COALESCE(decTotallembur1, 0) + 
                COALESCE(decTotallembur2, 0) + 
                COALESCE(decTotallembur3, 0) - 
                COALESCE(decPotonganHC, 0) - 
                COALESCE(decPotonganBPR, 0) - 
                COALESCE(decIuranSPN, 0) - 
                COALESCE(decPotonganBPJSKes, 0) - 
                COALESCE(decPotonganBPJSJHT, 0) - 
                COALESCE(decPotonganBPJSJP, 0) - 
                COALESCE(decPotonganKoperasi, 0) - 
                COALESCE(decPotonganAbsen, 0) - 
                COALESCE(decPotonganLain, 0)
            ) as total'))
            ->value('total') ?? 0;

        // Total payroll YTD (Year to Date)
        $payrollYTD = DB::table('t_closing')
            ->whereBetween('periode', [$tahunIni->format('Y-m-d'), $tahunIniEnd->format('Y-m-d')])
            ->select(DB::raw('SUM(
                COALESCE(decGapok, 0) + 
                COALESCE(decUangMakan, 0) + 
                COALESCE(decTransport, 0) + 
                COALESCE(decTotallembur1, 0) + 
                COALESCE(decTotallembur2, 0) + 
                COALESCE(decTotallembur3, 0) - 
                COALESCE(decPotonganHC, 0) - 
                COALESCE(decPotonganBPR, 0) - 
                COALESCE(decIuranSPN, 0) - 
                COALESCE(decPotonganBPJSKes, 0) - 
                COALESCE(decPotonganBPJSJHT, 0) - 
                COALESCE(decPotonganBPJSJP, 0) - 
                COALESCE(decPotonganKoperasi, 0) - 
                COALESCE(decPotonganAbsen, 0) - 
                COALESCE(decPotonganLain, 0)
            ) as total'))
            ->value('total') ?? 0;

        // Rata-rata gaji per karyawan (bulan ini)
        $totalKaryawanBulanIni = DB::table('t_closing')
            ->whereBetween('periode', [$bulanIni->format('Y-m-d'), $bulanIniEnd->format('Y-m-d')])
            ->distinct('vcNik')
            ->count('vcNik');

        $rataRataGaji = $totalKaryawanBulanIni > 0 ? round($payrollBulanIni / $totalKaryawanBulanIni, 2) : 0;

        // Payroll cost per BU (Divisi)
        $payrollPerBU = DB::table('t_closing')
            ->join('m_divisi', 't_closing.vcKodeDivisi', '=', 'm_divisi.vcKodeDivisi')
            ->whereBetween('periode', [$bulanIni->format('Y-m-d'), $bulanIniEnd->format('Y-m-d')])
            ->select(
                'm_divisi.vcNamaDivisi',
                DB::raw('SUM(
                    COALESCE(decGapok, 0) + 
                    COALESCE(decUangMakan, 0) + 
                    COALESCE(decTransport, 0) + 
                    COALESCE(decTotallembur1, 0) + 
                    COALESCE(decTotallembur2, 0) + 
                    COALESCE(decTotallembur3, 0) - 
                    COALESCE(decPotonganHC, 0) - 
                    COALESCE(decPotonganBPR, 0) - 
                    COALESCE(decIuranSPN, 0) - 
                    COALESCE(decPotonganBPJSKes, 0) - 
                    COALESCE(decPotonganBPJSJHT, 0) - 
                    COALESCE(decPotonganBPJSJP, 0) - 
                    COALESCE(decPotonganKoperasi, 0) - 
                    COALESCE(decPotonganAbsen, 0) - 
                    COALESCE(decPotonganLain, 0)
                ) as total_payroll')
            )
            ->groupBy('m_divisi.vcKodeDivisi', 'm_divisi.vcNamaDivisi')
            ->orderBy('total_payroll', 'desc')
            ->get();

        return [
            'payroll_bulan_ini' => $payrollBulanIni,
            'payroll_ytd' => $payrollYTD,
            'rata_rata_gaji' => $rataRataGaji,
            'payroll_per_bu' => $payrollPerBU,
            'total_karyawan_bulan_ini' => $totalKaryawanBulanIni,
        ];
    }

    /**
     * D. Alert & Notifikasi Eksekutif
     */
    private function getExecutiveAlerts()
    {
        $alerts = [];

        // 1. Payroll belum diproses (per BU)
        $periodeBelumDiproses = PeriodeGaji::where('vcStatus', '!=', '1')
            ->orWhereNull('vcStatus')
            ->with('divisi')
            ->get()
            ->groupBy('vcKodeDivisi');

        $payrollBelumDiproses = [];
        foreach ($periodeBelumDiproses as $kodeDivisi => $periodes) {
            $divisi = $periodes->first()->divisi;
            $payrollBelumDiproses[] = [
                'divisi' => $divisi ? $divisi->vcNamaDivisi : $kodeDivisi,
                'jumlah_periode' => $periodes->count(),
                'periodes' => $periodes->map(function ($p) {
                    return [
                        'periode' => $p->periode_text,
                        'tanggal_pembayaran' => $p->periode_pembayaran,
                    ];
                })->toArray(),
            ];
        }

        if (count($payrollBelumDiproses) > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'exclamation-triangle',
                'title' => 'Payroll Belum Diproses',
                'message' => 'Terdapat periode payroll yang belum diproses',
                'data' => $payrollBelumDiproses,
            ];
        }

        // 2. Kontrak 1 tahun yang akan habis (30 hari kedepan)
        $tanggalSekarang = Carbon::now();
        $tanggal30HariKedepan = Carbon::now()->addDays(30);
        
        // Kontrak 1 tahun = Tgl_Masuk + 1 tahun
        // Cari karyawan yang Tgl_Masuk + 1 tahun jatuh dalam 30 hari ke depan
        $karyawanKontrakHabis = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->whereNotNull('Tgl_Masuk')
            ->with('divisi')
            ->get()
            ->filter(function ($karyawan) use ($tanggalSekarang, $tanggal30HariKedepan) {
                if (!$karyawan->Tgl_Masuk) {
                    return false;
                }
                $tanggalKontrakHabis = Carbon::parse($karyawan->Tgl_Masuk)->addYear();
                // Cek apakah tanggal kontrak habis antara hari ini sampai 30 hari ke depan
                return $tanggalKontrakHabis->gte($tanggalSekarang) && $tanggalKontrakHabis->lte($tanggal30HariKedepan);
            })
            ->map(function ($karyawan) {
                $tanggalKontrakHabis = Carbon::parse($karyawan->Tgl_Masuk)->addYear();
                return [
                    'nik' => $karyawan->Nik,
                    'nama' => $karyawan->Nama,
                    'divisi' => $karyawan->divisi ? $karyawan->divisi->vcNamaDivisi : 'Tidak Diketahui',
                    'tanggal_masuk' => Carbon::parse($karyawan->Tgl_Masuk)->format('d/m/Y'),
                    'tanggal_kontrak_habis' => $tanggalKontrakHabis->format('d/m/Y'),
                    'sisa_hari' => $tanggalKontrakHabis->diffInDays(Carbon::now()),
                ];
            })
            ->groupBy('divisi');

        $kontrakHabis = [];
        foreach ($karyawanKontrakHabis as $divisi => $karyawans) {
            $kontrakHabis[] = [
                'divisi' => $divisi,
                'jumlah_karyawan' => $karyawans->count(),
                'karyawans' => $karyawans->values()->toArray(),
            ];
        }

        if (count($kontrakHabis) > 0) {
            $alerts[] = [
                'type' => 'info',
                'icon' => 'calendar-alt',
                'title' => 'Kontrak 1 Tahun Akan Habis (30 Hari Kedepan)',
                'message' => 'Terdapat karyawan dengan kontrak 1 tahun yang akan habis dalam 30 hari ke depan',
                'data' => $kontrakHabis,
            ];
        }

        // 3. Karyawan Akan Pensiun 6 bulan kedepan (usia maksimal 55 tahun)
        $tanggal6BulanKedepan = Carbon::now()->addMonths(6);
        
        // Pensiun = TTL + 55 tahun
        // Cari karyawan yang TTL + 55 tahun jatuh dalam 6 bulan ke depan
        $karyawanPensiun = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->whereNotNull('TTL')
            ->with('divisi')
            ->get()
            ->filter(function ($karyawan) use ($tanggalSekarang, $tanggal6BulanKedepan) {
                if (!$karyawan->TTL) {
                    return false;
                }
                $tanggalPensiun = Carbon::parse($karyawan->TTL)->addYears(55);
                // Cek apakah tanggal pensiun antara hari ini sampai 6 bulan ke depan
                return $tanggalPensiun->gte($tanggalSekarang) && $tanggalPensiun->lte($tanggal6BulanKedepan);
            })
            ->map(function ($karyawan) {
                $tanggalPensiun = Carbon::parse($karyawan->TTL)->addYears(55);
                $usia = Carbon::parse($karyawan->TTL)->age;
                return [
                    'nik' => $karyawan->Nik,
                    'nama' => $karyawan->Nama,
                    'divisi' => $karyawan->divisi ? $karyawan->divisi->vcNamaDivisi : 'Tidak Diketahui',
                    'tanggal_lahir' => Carbon::parse($karyawan->TTL)->format('d/m/Y'),
                    'usia' => $usia,
                    'tanggal_pensiun' => $tanggalPensiun->format('d/m/Y'),
                    'sisa_bulan' => $tanggalPensiun->diffInMonths(Carbon::now()),
                ];
            })
            ->groupBy('divisi');

        $pensiun = [];
        foreach ($karyawanPensiun as $divisi => $karyawans) {
            $pensiun[] = [
                'divisi' => $divisi,
                'jumlah_karyawan' => $karyawans->count(),
                'karyawans' => $karyawans->values()->toArray(),
            ];
        }

        if (count($pensiun) > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'user-clock',
                'title' => 'Karyawan Akan Pensiun (6 Bulan Kedepan)',
                'message' => 'Terdapat karyawan yang akan mencapai usia pensiun (55 tahun) dalam 6 bulan ke depan',
                'data' => $pensiun,
            ];
        }

        return $alerts;
    }
}

