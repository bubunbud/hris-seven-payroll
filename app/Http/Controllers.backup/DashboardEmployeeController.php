<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Karyawan;
use App\Models\Absen;
use App\Models\TidakMasuk;
use App\Models\Izin;
use App\Models\SaldoCuti;
use App\Models\Closing;
use App\Models\JadwalShiftSecurity;
use App\Models\ShiftSecurity;
use App\Traits\HariKerjaHelper;

class DashboardEmployeeController extends Controller
{
    use HariKerjaHelper;

    /**
     * Display Dashboard Employee Self Service
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();
        
        // Get semua karyawan untuk dropdown (jika admin)
        $allKaryawans = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->orderBy('Nama')
            ->get();
        
        // Tentukan karyawan yang akan ditampilkan
        $selectedNik = null;
        $karyawan = null;
        
        if ($isAdmin) {
            // Admin bisa pilih karyawan dari dropdown
            $selectedNik = $request->get('nik');
            
            if ($selectedNik) {
                $karyawan = Karyawan::where('Nik', $selectedNik)
                    ->where('vcAktif', '1')
                    ->whereNull('Tgl_Berhenti')
                    ->first();
            } else {
                // Default: karyawan pertama
                $karyawan = $allKaryawans->first();
                $selectedNik = $karyawan ? $karyawan->Nik : null;
            }
        } else {
            // Non-admin: gunakan karyawan dari user yang login
            $karyawan = $user->karyawan;
            $selectedNik = $karyawan ? $karyawan->Nik : null;
        }
        
        if (!$karyawan) {
            return redirect()->route('dashboard')
                ->with('error', 'Karyawan tidak ditemukan atau tidak aktif.');
        }

        // A. Informasi Pribadi
        $personalInfo = $this->getPersonalInfo($karyawan);

        // B. Absensi & Jadwal
        $absensiInfo = $this->getAbsensiInfo($karyawan);

        // C. Cuti & Izin
        $cutiInfo = $this->getCutiInfo($karyawan);

        // D. Payroll
        $payrollInfo = $this->getPayrollInfo($karyawan);

        return view('dashboard.employee.index', compact(
            'karyawan',
            'selectedNik',
            'allKaryawans',
            'isAdmin',
            'personalInfo',
            'absensiInfo',
            'cutiInfo',
            'payrollInfo'
        ));
    }

    /**
     * A. Informasi Pribadi
     */
    private function getPersonalInfo($karyawan)
    {
        // Profil singkat
        $profil = [
            'nik' => $karyawan->Nik,
            'nama' => $karyawan->Nama,
            'divisi' => $karyawan->divisi ? $karyawan->divisi->vcNamaDivisi : 'N/A',
            'departemen' => $karyawan->departemen ? $karyawan->departemen->vcNamaDept : 'N/A',
            'bagian' => $karyawan->bagian ? $karyawan->bagian->vcNamaBagian : 'N/A',
            'jabatan' => $karyawan->jabatan ? $karyawan->jabatan->vcNamaJabatan : 'N/A',
            'golongan' => $karyawan->Gol ?? 'N/A',
            'group_pegawai' => $karyawan->Group_pegawai ?? 'N/A',
        ];

        // Status kerja
        $statusKerja = [
            'status_pegawai' => $karyawan->Status_Pegawai ?? 'N/A',
            'tanggal_masuk' => $karyawan->Tgl_Masuk ? Carbon::parse($karyawan->Tgl_Masuk)->format('d/m/Y') : 'N/A',
            'tanggal_berhenti' => $karyawan->Tgl_Berhenti ? Carbon::parse($karyawan->Tgl_Berhenti)->format('d/m/Y') : null,
            'status_aktif' => $karyawan->vcAktif == '1' ? 'Aktif' : 'Tidak Aktif',
        ];

        // Masa kontrak (jika kontrak)
        $masaKontrak = null;
        if (trim($karyawan->Status_Pegawai ?? '') === 'Kontrak' && $karyawan->Tgl_Masuk) {
            $tanggalMasuk = Carbon::parse($karyawan->Tgl_Masuk);
            $tanggalKontrakHabis = $tanggalMasuk->copy()->addYear();
            $sisaHari = Carbon::now()->diffInDays($tanggalKontrakHabis, false);
            
            $masaKontrak = [
                'tanggal_mulai' => $tanggalMasuk->format('d/m/Y'),
                'tanggal_habis' => $tanggalKontrakHabis->format('d/m/Y'),
                'sisa_hari' => $sisaHari > 0 ? $sisaHari : 0,
                'status' => $sisaHari > 0 ? ($sisaHari <= 30 ? 'Akan Habis' : 'Aktif') : 'Habis',
            ];
        }

        // Jadwal shift (hanya untuk Security, atau shift default untuk non-Security)
        $jadwalShift = null;
        $bulanIni = Carbon::now()->startOfMonth();
        $bulanIniEnd = Carbon::now()->endOfMonth();
        
        if ($karyawan->Group_pegawai === 'Security') {
            // Cek apakah karyawan punya jadwal shift security
            $jadwalShiftSecurity = JadwalShiftSecurity::where('vcNik', $karyawan->Nik)
                ->whereBetween('dtTanggal', [$bulanIni->format('Y-m-d'), $bulanIniEnd->format('Y-m-d')])
                ->with('shiftSecurity')
                ->orderBy('dtTanggal', 'asc')
                ->get();

            if ($jadwalShiftSecurity->isNotEmpty()) {
                // Jika ada jadwal shift security, gunakan itu
                $jadwalShift = $jadwalShiftSecurity->map(function ($jadwal) {
                    $shiftInfo = 'OFF';
                    if ($jadwal->intShift && $jadwal->shiftSecurity) {
                        try {
                            $jamMasuk = $jadwal->shiftSecurity->dtJamMasuk instanceof Carbon 
                                ? $jadwal->shiftSecurity->dtJamMasuk->format('H:i')
                                : Carbon::parse($jadwal->shiftSecurity->dtJamMasuk)->format('H:i');
                            $jamPulang = $jadwal->shiftSecurity->dtJamPulang instanceof Carbon 
                                ? $jadwal->shiftSecurity->dtJamPulang->format('H:i')
                                : Carbon::parse($jadwal->shiftSecurity->dtJamPulang)->format('H:i');
                            $shiftInfo = $jadwal->shiftSecurity->vcNamaShift . ' (' . $jamMasuk . ' - ' . $jamPulang . ')';
                        } catch (\Exception $e) {
                            $shiftInfo = $jadwal->shiftSecurity->vcNamaShift ?? 'N/A';
                        }
                    } elseif ($jadwal->vcKeterangan) {
                        $shiftInfo = $jadwal->vcKeterangan;
                    }
                    
                    return [
                        'tanggal' => Carbon::parse($jadwal->dtTanggal)->format('d/m/Y'),
                        'shift' => $shiftInfo,
                    ];
                });
            }
        } else {
            // Untuk non-Security (Staf/Operator), tampilkan shift default
            if ($karyawan->shift) {
                $jadwalShift = collect([
                    [
                        'tanggal' => 'Default',
                        'shift' => $karyawan->shift->vcShift . ' (' . $karyawan->shift->vcMasuk . ' - ' . $karyawan->shift->vcKeluar . ')',
                    ]
                ]);
            }
        }

        return [
            'profil' => $profil,
            'status_kerja' => $statusKerja,
            'masa_kontrak' => $masaKontrak,
            'jadwal_shift' => $jadwalShift,
        ];
    }

    /**
     * B. Absensi & Jadwal
     */
    private function getAbsensiInfo($karyawan)
    {
        $nik = $karyawan->Nik;
        $hariIni = Carbon::now()->format('Y-m-d');
        $bulanIni = Carbon::now()->startOfMonth();
        $bulanIniEnd = Carbon::now()->endOfMonth();

        // Kehadiran hari ini
        $kehadiranHariIni = Absen::where('vcNik', $nik)
            ->where('dtTanggal', $hariIni)
            ->with(['karyawan.shift'])
            ->first();

        $kehadiranHariIniData = null;
        if ($kehadiranHariIni) {
            $isHariKerjaNormal = $this->isHariKerjaNormal($kehadiranHariIni->dtTanggal, $nik);
            $totalJam = $this->calculateTotalJam($kehadiranHariIni->dtJamMasuk, $kehadiranHariIni->dtJamKeluar, $kehadiranHariIni->dtTanggal);
            
            $kehadiranHariIniData = [
                'jam_masuk' => $kehadiranHariIni->dtJamMasuk,
                'jam_keluar' => $kehadiranHariIni->dtJamKeluar,
                'total_jam' => $totalJam,
                'status' => $this->getStatusAbsen($kehadiranHariIni, $isHariKerjaNormal, $totalJam),
                'is_hari_kerja_normal' => $isHariKerjaNormal,
            ];
        }

        // Riwayat absensi (bulan ini, limit 30 hari terakhir)
        // Load dengan shift untuk cek telat
        $riwayatAbsensi = Absen::where('vcNik', $nik)
            ->whereBetween('dtTanggal', [$bulanIni->format('Y-m-d'), $bulanIniEnd->format('Y-m-d')])
            ->with(['karyawan.shift'])
            ->orderBy('dtTanggal', 'desc')
            ->limit(30)
            ->get()
            ->map(function ($absen) use ($nik) {
                $isHariKerjaNormal = $this->isHariKerjaNormal($absen->dtTanggal, $nik);
                $totalJam = $this->calculateTotalJam($absen->dtJamMasuk, $absen->dtJamKeluar, $absen->dtTanggal);
                
                // Cek telat: jam masuk > jam shift masuk (hanya untuk hari kerja normal)
                $isTelat = false;
                if ($absen->dtJamMasuk && $absen->karyawan && $absen->karyawan->shift && $absen->karyawan->shift->vcMasuk && $isHariKerjaNormal) {
                    try {
                        $tanggalObj = Carbon::parse($absen->dtTanggal);
                        $jamMasuk = substr((string) $absen->dtJamMasuk, 0, 5);
                        $shiftMasuk = $absen->karyawan->shift->vcMasuk instanceof Carbon
                            ? $absen->karyawan->shift->vcMasuk->format('H:i')
                            : substr((string) $absen->karyawan->shift->vcMasuk, 0, 5);
                        
                        $tMasuk = $tanggalObj->copy()->setTimeFromTimeString($jamMasuk);
                        $tShiftMasuk = $tanggalObj->copy()->setTimeFromTimeString($shiftMasuk);
                        
                        if ($tMasuk->greaterThan($tShiftMasuk)) {
                            $isTelat = true;
                        }
                    } catch (\Exception $e) {
                        // Skip jika ada error parsing waktu
                    }
                }
                
                // Tentukan status sesuai logika Browse Absensi
                $status = '';
                if (!$absen->dtJamMasuk && !$absen->dtJamKeluar) {
                    $status = 'Tidak Masuk';
                } elseif ($isTelat) {
                    // Telat: jam masuk > jam shift masuk (prioritas sebelum HKN)
                    $status = 'Telat';
                } elseif (($absen->dtJamMasuk && !$absen->dtJamKeluar) || (!$absen->dtJamMasuk && $absen->dtJamKeluar)) {
                    // Absen tidak lengkap: hanya ada satu dari jam masuk/keluar
                    $status = 'ATL';
                } elseif (!$isHariKerjaNormal && ($absen->dtJamMasuk || $absen->dtJamKeluar || $absen->dtJamMasukLembur)) {
                    // KHL: Hari libur (weekend/holiday/tukar hari kerja) dan ada jam masuk/keluar/lembur
                    $status = 'KHL';
                } elseif ($absen->dtJamMasuk && $absen->dtJamKeluar && $totalJam >= 8 && $isHariKerjaNormal) {
                    // Hari kerja normal (ada jam masuk dan keluar, minimal 8 jam)
                    $status = 'HKN';
                } elseif ($absen->dtJamMasuk && $absen->dtJamKeluar && $totalJam > 0 && $totalJam < 8 && $isHariKerjaNormal) {
                    // HC: Ada jam masuk dan keluar tapi jam kerja kurang dari 8 jam
                    $status = 'HC';
                } else {
                    // Lainnya (tidak ada jam masuk atau keluar)
                    $status = 'ATL';
                }
                
                return [
                    'tanggal' => Carbon::parse($absen->dtTanggal)->format('d/m/Y'),
                    'jam_masuk' => $absen->dtJamMasuk ?? '-',
                    'jam_keluar' => $absen->dtJamKeluar ?? '-',
                    'total_jam' => $totalJam,
                    'status' => $status,
                ];
            });

        // Riwayat Tidak Masuk (bulan ini)
        $riwayatTidakMasuk = TidakMasuk::where('t_tidak_masuk.vcNik', $nik)
            ->where(function ($q) use ($bulanIni, $bulanIniEnd) {
                $q->whereBetween('t_tidak_masuk.dtTanggalMulai', [$bulanIni->format('Y-m-d'), $bulanIniEnd->format('Y-m-d')])
                  ->orWhereBetween('t_tidak_masuk.dtTanggalSelesai', [$bulanIni->format('Y-m-d'), $bulanIniEnd->format('Y-m-d')])
                  ->orWhere(function ($qq) use ($bulanIni, $bulanIniEnd) {
                      $qq->where('t_tidak_masuk.dtTanggalMulai', '<=', $bulanIni->format('Y-m-d'))
                        ->where('t_tidak_masuk.dtTanggalSelesai', '>=', $bulanIniEnd->format('Y-m-d'));
                  });
            })
            ->join('m_jenis_absen', 't_tidak_masuk.vcKodeAbsen', '=', 'm_jenis_absen.vcKodeAbsen')
            ->select(
                't_tidak_masuk.*',
                'm_jenis_absen.vcKeterangan as jenis_absen_nama'
            )
            ->orderBy('t_tidak_masuk.dtTanggalMulai', 'desc')
            ->get()
            ->map(function ($tidakMasuk) {
                return [
                    'tanggal_mulai' => Carbon::parse($tidakMasuk->dtTanggalMulai)->format('d/m/Y'),
                    'tanggal_selesai' => Carbon::parse($tidakMasuk->dtTanggalSelesai)->format('d/m/Y'),
                    'jenis' => $tidakMasuk->jenis_absen_nama ?? 'N/A',
                    'keterangan' => $tidakMasuk->vcKeterangan ?? '-',
                ];
            });

        // Riwayat Izin Keluar Komplek (bulan ini)
        // dtDari = jam keluar, dtSampai = jam masuk
        $riwayatIzinKeluar = Izin::where('vcNik', $nik)
            ->whereBetween('dtTanggal', [$bulanIni->format('Y-m-d'), $bulanIniEnd->format('Y-m-d')])
            ->orderBy('dtTanggal', 'desc')
            ->get()
            ->map(function ($izin) {
                $jamKeluar = $izin->dtDari ?? null;
                $jamMasuk = $izin->dtSampai ?? null;
                
                // Hitung durasi dari dtDari dan dtSampai
                $durasi = 0;
                if ($jamKeluar && $jamMasuk) {
                    try {
                        $tanggal = Carbon::parse($izin->dtTanggal);
                        $dari = Carbon::parse($tanggal->format('Y-m-d') . ' ' . $jamKeluar);
                        $sampai = Carbon::parse($tanggal->format('Y-m-d') . ' ' . $jamMasuk);
                        
                        // Jika jam masuk lebih kecil dari jam keluar, berarti melewati tengah malam
                        if ($sampai->lt($dari)) {
                            $sampai->addDay();
                        }
                        
                        $durasi = round($dari->diffInHours($sampai, true), 1);
                    } catch (\Exception $e) {
                        $durasi = 0;
                    }
                }
                
                return [
                    'tanggal' => Carbon::parse($izin->dtTanggal)->format('d/m/Y'),
                    'jam_keluar' => $jamKeluar ?? '-',
                    'jam_masuk' => $jamMasuk ?? '-',
                    'durasi' => $durasi,
                    'keterangan' => $izin->vcKeterangan ?? '-',
                ];
            });

        // Jadwal shift (hanya untuk Security, akan ditampilkan di section Status Kerja)
        $jadwalShift = null;
        if ($karyawan->Group_pegawai === 'Security') {
            // Cek apakah karyawan punya jadwal shift security
            $jadwalShiftSecurity = JadwalShiftSecurity::where('vcNik', $nik)
                ->whereBetween('dtTanggal', [$bulanIni->format('Y-m-d'), $bulanIniEnd->format('Y-m-d')])
                ->with('shiftSecurity')
                ->orderBy('dtTanggal', 'asc')
                ->get();

            if ($jadwalShiftSecurity->isNotEmpty()) {
                // Jika ada jadwal shift security, gunakan itu
                $jadwalShift = $jadwalShiftSecurity->map(function ($jadwal) {
                    $shiftInfo = 'OFF';
                    if ($jadwal->intShift && $jadwal->shiftSecurity) {
                        try {
                            $jamMasuk = $jadwal->shiftSecurity->dtJamMasuk instanceof Carbon 
                                ? $jadwal->shiftSecurity->dtJamMasuk->format('H:i')
                                : Carbon::parse($jadwal->shiftSecurity->dtJamMasuk)->format('H:i');
                            $jamPulang = $jadwal->shiftSecurity->dtJamPulang instanceof Carbon 
                                ? $jadwal->shiftSecurity->dtJamPulang->format('H:i')
                                : Carbon::parse($jadwal->shiftSecurity->dtJamPulang)->format('H:i');
                            $shiftInfo = $jadwal->shiftSecurity->vcNamaShift . ' (' . $jamMasuk . ' - ' . $jamPulang . ')';
                        } catch (\Exception $e) {
                            $shiftInfo = $jadwal->shiftSecurity->vcNamaShift ?? 'N/A';
                        }
                    } elseif ($jadwal->vcKeterangan) {
                        $shiftInfo = $jadwal->vcKeterangan;
                    }
                    
                    return [
                        'tanggal' => Carbon::parse($jadwal->dtTanggal)->format('d/m/Y'),
                        'shift' => $shiftInfo,
                    ];
                });
            }
        }

        return [
            'kehadiran_hari_ini' => $kehadiranHariIniData,
            'riwayat_absensi' => $riwayatAbsensi,
            'riwayat_tidak_masuk' => $riwayatTidakMasuk,
            'riwayat_izin_keluar' => $riwayatIzinKeluar,
        ];
    }

    /**
     * C. Cuti & Izin
     */
    private function getCutiInfo($karyawan)
    {
        $nik = $karyawan->Nik;
        $tahunIni = Carbon::now()->year;

        // Sisa cuti
        $sisaCuti = SaldoCuti::where('vcNik', $nik)
            ->where('intTahun', $tahunIni)
            ->first();

        $sisaCutiData = [
            'tahun' => $tahunIni,
            'saldo_tahun_lalu' => $sisaCuti ? $sisaCuti->decTahunLalu : 0,
            'saldo_tahun_ini' => $sisaCuti ? $sisaCuti->decTahunIni : 0,
            'saldo_digunakan' => $sisaCuti ? $sisaCuti->decSaldoDigunakan : 0,
            'saldo_sisa' => $sisaCuti ? $sisaCuti->decSaldoSisa : 0,
        ];

        // Riwayat cuti (tahun ini)
        $riwayatCuti = TidakMasuk::where('t_tidak_masuk.vcNik', $nik)
            ->where(function ($q) {
                $q->where('t_tidak_masuk.vcKodeAbsen', 'C010') // Cuti Tahunan
                  ->orWhere('t_tidak_masuk.vcKodeAbsen', 'C012'); // Cuti Melahirkan
            })
            ->whereYear('t_tidak_masuk.dtTanggalMulai', $tahunIni)
            ->join('m_jenis_absen', 't_tidak_masuk.vcKodeAbsen', '=', 'm_jenis_absen.vcKodeAbsen')
            ->select(
                't_tidak_masuk.*',
                'm_jenis_absen.vcKeterangan as jenis_absen_nama'
            )
            ->orderBy('t_tidak_masuk.dtTanggalMulai', 'desc')
            ->get()
            ->map(function ($cuti) {
                // Hitung durasi cuti (jumlah hari)
                $tanggalMulai = Carbon::parse($cuti->dtTanggalMulai);
                $tanggalSelesai = Carbon::parse($cuti->dtTanggalSelesai);
                $durasi = $tanggalMulai->diffInDays($tanggalSelesai) + 1; // +1 untuk inklusif
                
                return [
                    'tanggal_mulai' => $tanggalMulai->format('d/m/Y'),
                    'tanggal_selesai' => $tanggalSelesai->format('d/m/Y'),
                    'durasi' => $durasi,
                    'jenis' => $cuti->jenis_absen_nama ?? 'N/A',
                    'keterangan' => $cuti->vcKeterangan ?? '-',
                ];
            });

        // Pengajuan cuti (pending approval - placeholder, perlu disesuaikan dengan logika approval)
        $pengajuanCuti = []; // Placeholder

        return [
            'sisa_cuti' => $sisaCutiData,
            'riwayat_cuti' => $riwayatCuti,
            'pengajuan_cuti' => $pengajuanCuti,
        ];
    }

    /**
     * D. Payroll
     */
    private function getPayrollInfo($karyawan)
    {
        $nik = $karyawan->Nik;

        // Slip gaji terbaru (periode terakhir)
        $slipGajiTerbaru = DB::table('t_closing')
            ->where('vcNik', $nik)
            ->orderBy('periode', 'desc')
            ->orderBy('vcClosingKe', 'desc')
            ->first();

        $slipGajiTerbaruData = null;
        if ($slipGajiTerbaru) {
            $totalGaji = ($slipGajiTerbaru->decGapok ?? 0) 
                + ($slipGajiTerbaru->decUangMakan ?? 0)
                + ($slipGajiTerbaru->decTransport ?? 0)
                + ($slipGajiTerbaru->decTotallembur1 ?? 0)
                + ($slipGajiTerbaru->decTotallembur2 ?? 0)
                + ($slipGajiTerbaru->decTotallembur3 ?? 0);
            
            $totalPotongan = ($slipGajiTerbaru->decPotonganHC ?? 0)
                + ($slipGajiTerbaru->decPotonganBPR ?? 0)
                + ($slipGajiTerbaru->decIuranSPN ?? 0)
                + ($slipGajiTerbaru->decPotonganBPJSKes ?? 0)
                + ($slipGajiTerbaru->decPotonganBPJSJHT ?? 0)
                + ($slipGajiTerbaru->decPotonganBPJSJP ?? 0)
                + ($slipGajiTerbaru->decPotonganKoperasi ?? 0)
                + ($slipGajiTerbaru->decPotonganAbsen ?? 0)
                + ($slipGajiTerbaru->decPotonganLain ?? 0);
            
            $slipGajiTerbaruData = [
                'periode_awal' => Carbon::parse($slipGajiTerbaru->vcPeriodeAwal)->format('d/m/Y'),
                'periode_akhir' => Carbon::parse($slipGajiTerbaru->vcPeriodeAkhir)->format('d/m/Y'),
                'periode' => Carbon::parse($slipGajiTerbaru->periode)->format('d/m/Y'),
                'closing_ke' => $slipGajiTerbaru->vcClosingKe,
                'gaji_pokok' => $slipGajiTerbaru->decGapok ?? 0,
                'uang_makan' => $slipGajiTerbaru->decUangMakan ?? 0,
                'transport' => $slipGajiTerbaru->decTransport ?? 0,
                'lembur' => ($slipGajiTerbaru->decTotallembur1 ?? 0) + ($slipGajiTerbaru->decTotallembur2 ?? 0) + ($slipGajiTerbaru->decTotallembur3 ?? 0),
                'total_gaji' => $totalGaji,
                'total_potongan' => $totalPotongan,
                'take_home_pay' => $totalGaji - $totalPotongan,
            ];
        }

        // Riwayat slip gaji (12 bulan terakhir)
        $riwayatSlipGaji = DB::table('t_closing')
            ->where('vcNik', $nik)
            ->orderBy('periode', 'desc')
            ->orderBy('vcClosingKe', 'desc')
            ->limit(12)
            ->get()
            ->map(function ($slip) {
                $totalGaji = ($slip->decGapok ?? 0) 
                    + ($slip->decUangMakan ?? 0)
                    + ($slip->decTransport ?? 0)
                    + ($slip->decTotallembur1 ?? 0)
                    + ($slip->decTotallembur2 ?? 0)
                    + ($slip->decTotallembur3 ?? 0);
                
                $totalPotongan = ($slip->decPotonganHC ?? 0)
                    + ($slip->decPotonganBPR ?? 0)
                    + ($slip->decIuranSPN ?? 0)
                    + ($slip->decPotonganBPJSKes ?? 0)
                    + ($slip->decPotonganBPJSJHT ?? 0)
                    + ($slip->decPotonganBPJSJP ?? 0)
                    + ($slip->decPotonganKoperasi ?? 0)
                    + ($slip->decPotonganAbsen ?? 0)
                    + ($slip->decPotonganLain ?? 0);
                
                return [
                    'periode_awal' => Carbon::parse($slip->vcPeriodeAwal)->format('d/m/Y'),
                    'periode_akhir' => Carbon::parse($slip->vcPeriodeAkhir)->format('d/m/Y'),
                    'periode' => Carbon::parse($slip->periode)->format('d/m/Y'),
                    'closing_ke' => $slip->vcClosingKe,
                    'take_home_pay' => $totalGaji - $totalPotongan,
                ];
            });

        // THR / Bonus (placeholder - perlu disesuaikan dengan struktur data THR/Bonus jika ada)
        $thrBonus = []; // Placeholder

        return [
            'slip_gaji_terbaru' => $slipGajiTerbaruData,
            'riwayat_slip_gaji' => $riwayatSlipGaji,
            'thr_bonus' => $thrBonus,
        ];
    }

    /**
     * Helper: Calculate total jam kerja
     */
    private function calculateTotalJam($dtJamMasuk, $dtJamKeluar, $dtTanggal)
    {
        if (!$dtJamMasuk || !$dtJamKeluar) {
            return 0;
        }

        try {
            $tanggal = Carbon::parse($dtTanggal);
            $masuk = $tanggal->copy()->setTimeFromTimeString((string) $dtJamMasuk);
            $keluar = $tanggal->copy()->setTimeFromTimeString((string) $dtJamKeluar);

            if ($keluar->lessThan($masuk)) {
                $keluar->addDay();
            }

            return round($masuk->diffInHours($keluar, true), 1);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Helper: Get status absen
     */
    private function getStatusAbsen($absen, $isHariKerjaNormal, $totalJam)
    {
        if (!$absen->dtJamMasuk && !$absen->dtJamKeluar) {
            return 'Tidak Masuk';
        }

        if (!$absen->dtJamMasuk || !$absen->dtJamKeluar) {
            return 'ATL';
        }

        if ($isHariKerjaNormal) {
            if ($totalJam >= 8) {
                return 'HKN';
            } elseif ($totalJam > 0) {
                return 'HC';
            }
        } else {
            if ($absen->dtJamMasuk || $absen->dtJamKeluar) {
                return 'KHL';
            }
        }

        return 'ATL';
    }
}

