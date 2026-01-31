<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Absen;
use App\Models\TidakMasuk;
use App\Models\Izin;
use App\Models\Karyawan;

class DashboardController extends Controller
{
    /**
     * Display dashboard with 4 data sections
     */
    public function index()
    {
        $today = Carbon::today();
        $twoDaysAgo = Carbon::today()->subDays(2);

        // 1. Data Karyawan absensi hari ini hingga 2 hari kebelakang
        $absenData = $this->getAbsenData($twoDaysAgo, $today);

        // 2. Data Karyawan yang tidak masuk hari ini
        $tidakMasukData = $this->getTidakMasukData($today);

        // 3. Data Karyawan yang melakukan izin keluar komplek hari ini
        $izinKeluarData = $this->getIzinKeluarData($today);

        // 4. Data Karyawan yang tidak ada data absensi (finger print) hari ini
        $tidakAbsenData = $this->getTidakAbsenData($today);

        return view('dashboard', compact(
            'absenData',
            'tidakMasukData',
            'izinKeluarData',
            'tidakAbsenData'
        ));
    }

    /**
     * Get absensi data from today to 2 days ago
     */
    private function getAbsenData($startDate, $endDate)
    {
        $query = DB::table('t_absen')
            ->join('m_karyawan', 't_absen.vcNik', '=', 'm_karyawan.Nik')
            ->leftJoin('m_divisi', 'm_karyawan.Divisi', '=', 'm_divisi.vcKodeDivisi')
            ->leftJoin('m_bagian', 'm_karyawan.vcKodeBagian', '=', 'm_bagian.vcKodeBagian')
            ->whereBetween('t_absen.dtTanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereNull('m_karyawan.Tgl_Berhenti') // Hanya karyawan yang belum berhenti
            ->where('m_karyawan.vcAktif', '1') // Hanya karyawan aktif
            ->select(
                't_absen.dtTanggal',
                't_absen.vcNik',
                't_absen.dtJamMasuk',
                't_absen.dtJamKeluar',
                'm_karyawan.Nama',
                'm_karyawan.Divisi',
                'm_karyawan.vcKodeBagian',
                'm_divisi.vcNamaDivisi',
                'm_bagian.vcNamaBagian'
            )
            ->orderBy('t_absen.dtTanggal', 'desc')
            ->orderBy('m_karyawan.Nama', 'asc');

        return $query->paginate(10, ['*'], 'absen_page');
    }

    /**
     * Get karyawan yang tidak masuk hari ini
     */
    private function getTidakMasukData($date)
    {
        $query = DB::table('t_tidak_masuk')
            ->join('m_karyawan', 't_tidak_masuk.vcNik', '=', 'm_karyawan.Nik')
            ->leftJoin('m_jenis_absen', 't_tidak_masuk.vcKodeAbsen', '=', 'm_jenis_absen.vcKodeAbsen')
            ->leftJoin('m_divisi', 'm_karyawan.Divisi', '=', 'm_divisi.vcKodeDivisi')
            ->leftJoin('m_bagian', 'm_karyawan.vcKodeBagian', '=', 'm_bagian.vcKodeBagian')
            ->whereNull('m_karyawan.Tgl_Berhenti') // Hanya karyawan yang belum berhenti
            ->where('m_karyawan.vcAktif', '1') // Hanya karyawan aktif
            ->where(function ($q) use ($date) {
                $q->whereDate('t_tidak_masuk.dtTanggalMulai', '<=', $date->format('Y-m-d'))
                  ->whereDate('t_tidak_masuk.dtTanggalSelesai', '>=', $date->format('Y-m-d'));
            })
            ->select(
                't_tidak_masuk.vcNik',
                't_tidak_masuk.vcKodeAbsen',
                't_tidak_masuk.dtTanggalMulai',
                't_tidak_masuk.dtTanggalSelesai',
                't_tidak_masuk.vcKeterangan',
                'm_karyawan.Nama',
                'm_karyawan.Divisi',
                'm_karyawan.vcKodeBagian',
                'm_divisi.vcNamaDivisi',
                'm_bagian.vcNamaBagian',
                'm_jenis_absen.vcKeterangan as jenis_absen_keterangan'
            )
            ->orderBy('m_karyawan.Nama', 'asc');

        return $query->paginate(5, ['*'], 'tidak_masuk_page');
    }

    /**
     * Get karyawan yang melakukan izin keluar komplek hari ini
     */
    private function getIzinKeluarData($date)
    {
        $query = DB::table('t_izin')
            ->join('m_karyawan', 't_izin.vcNik', '=', 'm_karyawan.Nik')
            ->leftJoin('m_jenis_izin', 't_izin.vcKodeIzin', '=', 'm_jenis_izin.vcKodeIzin')
            ->leftJoin('m_divisi', 'm_karyawan.Divisi', '=', 'm_divisi.vcKodeDivisi')
            ->leftJoin('m_bagian', 'm_karyawan.vcKodeBagian', '=', 'm_bagian.vcKodeBagian')
            ->whereDate('t_izin.dtTanggal', $date->format('Y-m-d'))
            ->whereNull('m_karyawan.Tgl_Berhenti') // Hanya karyawan yang belum berhenti
            ->where('m_karyawan.vcAktif', '1') // Hanya karyawan aktif
            ->select(
                't_izin.vcCounter',
                't_izin.vcNik',
                't_izin.vcKodeIzin',
                't_izin.vcTipeIzin',
                't_izin.dtTanggal',
                't_izin.dtDari',
                't_izin.dtSampai',
                't_izin.vcKeterangan',
                'm_karyawan.Nama',
                'm_karyawan.Divisi',
                'm_karyawan.vcKodeBagian',
                'm_divisi.vcNamaDivisi',
                'm_bagian.vcNamaBagian',
                'm_jenis_izin.vcKeterangan as jenis_izin_keterangan'
            )
            ->orderBy('m_karyawan.Nama', 'asc');

        return $query->paginate(5, ['*'], 'izin_keluar_page');
    }

    /**
     * Get karyawan yang tidak ada data absensi (finger print) hari ini
     */
    private function getTidakAbsenData($date)
    {
        // Ambil semua karyawan aktif (vcAktif=1 dan Tgl_Berhenti IS NULL)
        $karyawanAktif = DB::table('m_karyawan')
            ->whereNull('Tgl_Berhenti')
            ->where('vcAktif', '1') // Hanya karyawan aktif
            ->pluck('Nik');

        // Ambil NIK yang sudah ada absensi hari ini
        $nikSudahAbsen = DB::table('t_absen')
            ->whereDate('dtTanggal', $date->format('Y-m-d'))
            ->pluck('vcNik');

        // Ambil NIK yang tidak masuk hari ini (untuk exclude)
        $nikTidakMasuk = DB::table('t_tidak_masuk')
            ->where(function ($q) use ($date) {
                $q->whereDate('dtTanggalMulai', '<=', $date->format('Y-m-d'))
                  ->whereDate('dtTanggalSelesai', '>=', $date->format('Y-m-d'));
            })
            ->pluck('vcNik');

        // Karyawan yang tidak ada absensi dan tidak masuk
        $nikTidakAbsen = $karyawanAktif->diff($nikSudahAbsen)->diff($nikTidakMasuk);

        // Query detail karyawan yang tidak absen
        $query = DB::table('m_karyawan')
            ->leftJoin('m_divisi', 'm_karyawan.Divisi', '=', 'm_divisi.vcKodeDivisi')
            ->leftJoin('m_bagian', 'm_karyawan.vcKodeBagian', '=', 'm_bagian.vcKodeBagian')
            ->whereNull('m_karyawan.Tgl_Berhenti') // Hanya karyawan yang belum berhenti
            ->where('m_karyawan.vcAktif', '1'); // Hanya karyawan aktif

        // Jika ada NIK yang tidak absen, filter dengan whereIn
        // Jika tidak ada, return empty result dengan whereRaw yang selalu false
        if ($nikTidakAbsen->count() > 0) {
            $query->whereIn('m_karyawan.Nik', $nikTidakAbsen->toArray());
        } else {
            $query->whereRaw('1 = 0'); // Always false, return empty result
        }

        $query->select(
            'm_karyawan.Nik',
            'm_karyawan.Nama',
            'm_karyawan.Divisi',
            'm_karyawan.vcKodeBagian',
            'm_divisi.vcNamaDivisi',
            'm_bagian.vcNamaBagian'
        )
        ->orderBy('m_karyawan.Nama', 'asc');

        return $query->paginate(5, ['*'], 'tidak_absen_page');
    }
}

