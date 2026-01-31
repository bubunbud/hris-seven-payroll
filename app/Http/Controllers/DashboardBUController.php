<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Karyawan;
use App\Models\Divisi;
use App\Models\Departemen;
use App\Models\Absen;
use App\Models\TidakMasuk;
use App\Models\Izin;
use App\Models\Closing;
use App\Models\PeriodeGaji;
use App\Models\SaldoCuti;
use App\Models\LemburHeader;
use App\Models\LemburDetail;
use App\Traits\HariKerjaHelper;

class DashboardBUController extends Controller
{
    use HariKerjaHelper;

    /**
     * Display Dashboard Level Business Unit (BU View)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();
        
        // Get semua divisi untuk dropdown (jika admin)
        $allDivisis = Divisi::orderBy('vcNamaDivisi')->get();
        
        // Tentukan BU yang akan ditampilkan
        $buKode = null;
        $bu = null;
        
        if ($isAdmin) {
            // Admin bisa pilih BU dari dropdown
            $selectedBuKode = $request->get('bu_kode');
            
            if ($selectedBuKode) {
                $buKode = $selectedBuKode;
                $bu = Divisi::find($buKode);
            } else {
                // Default: cari BU "PT. Sugih Instrumendo Abadi, Export" atau BU pertama
                $defaultBu = Divisi::where(function($q) {
                    $q->where('vcNamaDivisi', 'like', '%Sugih Instrumendo%')
                      ->orWhere('vcNamaDivisi', 'like', '%Export%');
                })->first();
                
                if (!$defaultBu) {
                    $defaultBu = $allDivisis->first();
                }
                
                $bu = $defaultBu;
                $buKode = $bu ? $bu->vcKodeDivisi : null;
            }
        } else {
            // Non-admin: gunakan BU dari user yang login
            $karyawanUser = $user->karyawan;
            
            if (!$karyawanUser || !$karyawanUser->Divisi) {
                return redirect()->route('dashboard')
                    ->with('error', 'User tidak memiliki divisi/BU. Silakan hubungi administrator.');
            }

            $buKode = $karyawanUser->Divisi;
            $bu = Divisi::find($buKode);
        }
        
        if (!$bu) {
            return redirect()->route('dashboard')
                ->with('error', 'Divisi/BU tidak ditemukan.');
        }

        // A. Statistik SDM BU
        $sdmStats = $this->getSDMStats($buKode);

        // B. Absensi & Kehadiran
        $absensiStats = $this->getAbsensiStats($buKode);

        // C. Cuti & Izin
        $cutiStats = $this->getCutiStats($buKode);

        // D. Payroll BU
        $payrollStats = $this->getPayrollStats($buKode);

        // E. Kontrak & Masa Berlaku
        $kontrakStats = $this->getKontrakStats($buKode);

        return view('dashboard.bu.index', compact(
            'bu',
            'buKode',
            'allDivisis',
            'isAdmin',
            'sdmStats',
            'absensiStats',
            'cutiStats',
            'payrollStats',
            'kontrakStats'
        ));
    }

    /**
     * A. Statistik SDM BU
     */
    private function getSDMStats($buKode)
    {
        // Total karyawan BU
        $totalKaryawan = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->where('Divisi', $buKode)
            ->count();

        // Distribusi per departemen
        $distribusiDept = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->where('Divisi', $buKode)
            ->join('m_dept', 'm_karyawan.dept', '=', 'm_dept.vcKodeDept')
            ->select('m_dept.vcNamaDept', DB::raw('count(*) as jumlah'))
            ->groupBy('m_dept.vcKodeDept', 'm_dept.vcNamaDept')
            ->orderBy('jumlah', 'desc')
            ->get();

        // Status kepegawaian
        $statusKepegawaian = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->where('Divisi', $buKode)
            ->select('Status_Pegawai', DB::raw('count(*) as jumlah'))
            ->groupBy('Status_Pegawai')
            ->pluck('jumlah', 'Status_Pegawai')
            ->toArray();

        // Struktur jabatan
        $strukturJabatan = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->where('Divisi', $buKode)
            ->join('m_jabatan', 'm_karyawan.Jabat', '=', 'm_jabatan.vcKodeJabatan')
            ->select('m_jabatan.vcNamaJabatan', DB::raw('count(*) as jumlah'))
            ->groupBy('m_jabatan.vcKodeJabatan', 'm_jabatan.vcNamaJabatan')
            ->orderBy('jumlah', 'desc')
            ->limit(10) // Top 10 jabatan
            ->get();

        return [
            'total_karyawan' => $totalKaryawan,
            'distribusi_dept' => $distribusiDept,
            'status_kepegawaian' => $statusKepegawaian,
            'struktur_jabatan' => $strukturJabatan,
        ];
    }

    /**
     * B. Absensi & Kehadiran
     */
    private function getAbsensiStats($buKode)
    {
        $bulanIni = Carbon::now()->startOfMonth();
        $bulanIniEnd = Carbon::now()->endOfMonth();

        // Get NIK karyawan di BU ini
        $nikList = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->where('Divisi', $buKode)
            ->pluck('Nik')
            ->toArray();

        if (empty($nikList)) {
            return [
                'persentase_kehadiran' => 0,
                'telat' => 0,
                'alpha' => 0,
                'lembur_total_jam' => 0,
                'lembur_total_biaya' => 0,
                'absensi_tidak_lengkap' => 0,
            ];
        }

        // Hitung hari kerja normal bulan ini (dengan mempertimbangkan tukar hari kerja)
        $totalHariKerja = $this->calculateHariKerjaWithTukar($bulanIni, $bulanIniEnd);
        $totalHariKerjaPerKaryawan = $totalHariKerja * count($nikList);

        // Total hadir (HKN) bulan ini
        $totalHadir = 0;
        $absensiBulanIni = Absen::whereIn('vcNik', $nikList)
            ->whereBetween('dtTanggal', [$bulanIni->format('Y-m-d'), $bulanIniEnd->format('Y-m-d')])
            ->get();

        foreach ($absensiBulanIni as $absen) {
            if ($this->isHariKerjaNormal($absen->dtTanggal, $absen->vcNik)) {
                if ($absen->dtJamMasuk && $absen->dtJamKeluar) {
                    $totalJam = $this->calculateTotalJam($absen->dtJamMasuk, $absen->dtJamKeluar, $absen->dtTanggal);
                    if ($totalJam >= 8) {
                        $totalHadir++;
                    }
                }
            }
        }

        // Persentase kehadiran
        $persentaseKehadiran = $totalHariKerjaPerKaryawan > 0 
            ? round(($totalHadir / $totalHariKerjaPerKaryawan) * 100, 2) 
            : 0;

        // Telat (hanya untuk hari kerja normal)
        $telat = Absen::whereIn('vcNik', $nikList)
            ->whereBetween('dtTanggal', [$bulanIni->format('Y-m-d'), $bulanIniEnd->format('Y-m-d')])
            ->whereNotNull('dtJamMasuk')
            ->with(['karyawan.shift'])
            ->get()
            ->filter(function ($absen) {
                if (!$this->isHariKerjaNormal($absen->dtTanggal, $absen->vcNik)) {
                    return false;
                }
                if (!$absen->dtJamMasuk || !$absen->karyawan || !$absen->karyawan->shift || !$absen->karyawan->shift->vcMasuk) {
                    return false;
                }
                try {
                    $jamMasuk = Carbon::parse($absen->dtTanggal . ' ' . $absen->dtJamMasuk);
                    $jamShiftMasuk = Carbon::parse($absen->dtTanggal . ' ' . $absen->karyawan->shift->vcMasuk);
                    return $jamMasuk->gt($jamShiftMasuk);
                } catch (\Exception $e) {
                    return false;
                }
            })
            ->count();

        // Alpha (tidak masuk tanpa izin di hari kerja normal)
        $alpha = TidakMasuk::whereIn('vcNik', $nikList)
            ->where(function ($q) use ($bulanIni, $bulanIniEnd) {
                $q->whereBetween('dtTanggalMulai', [$bulanIni->format('Y-m-d'), $bulanIniEnd->format('Y-m-d')])
                  ->orWhereBetween('dtTanggalSelesai', [$bulanIni->format('Y-m-d'), $bulanIniEnd->format('Y-m-d')])
                  ->orWhere(function ($qq) use ($bulanIni, $bulanIniEnd) {
                      $qq->where('dtTanggalMulai', '<=', $bulanIni->format('Y-m-d'))
                        ->where('dtTanggalSelesai', '>=', $bulanIniEnd->format('Y-m-d'));
                  });
            })
            ->join('m_jenis_absen', 't_tidak_masuk.vcKodeAbsen', '=', 'm_jenis_absen.vcKodeAbsen')
            ->where('m_jenis_absen.vcKodeAbsen', 'A') // Alpha
            ->count();

        // Lembur total jam & biaya bulan ini
        // Query dari t_lembur_detail dengan join t_lembur_header
        $lemburBulanIni = DB::table('t_lembur_detail')
            ->join('t_lembur_header', 't_lembur_detail.vcCounterHeader', '=', 't_lembur_header.vcCounter')
            ->whereIn('t_lembur_detail.vcNik', $nikList)
            ->whereBetween('t_lembur_header.dtTanggalLembur', [$bulanIni->format('Y-m-d'), $bulanIniEnd->format('Y-m-d')])
            ->select(
                DB::raw('SUM(COALESCE(t_lembur_detail.decDurasiLembur, 0)) as total_jam'),
                DB::raw('SUM(COALESCE(t_lembur_detail.decLemburExternal, 0)) as total_biaya')
            )
            ->first();

        $lemburTotalJam = $lemburBulanIni->total_jam ?? 0;
        $lemburTotalBiaya = $lemburBulanIni->total_biaya ?? 0;

        // Absensi tidak lengkap (missing log) - ada jam masuk tapi tidak ada jam keluar, atau sebaliknya
        $absensiTidakLengkap = Absen::whereIn('vcNik', $nikList)
            ->whereBetween('dtTanggal', [$bulanIni->format('Y-m-d'), $bulanIniEnd->format('Y-m-d')])
            ->where(function ($q) {
                $q->where(function ($qq) {
                    $qq->whereNotNull('dtJamMasuk')->whereNull('dtJamKeluar');
                })->orWhere(function ($qq) {
                    $qq->whereNull('dtJamMasuk')->whereNotNull('dtJamKeluar');
                });
            })
            ->count();

        return [
            'persentase_kehadiran' => $persentaseKehadiran,
            'telat' => $telat,
            'alpha' => $alpha,
            'lembur_total_jam' => $lemburTotalJam,
            'lembur_total_biaya' => $lemburTotalBiaya,
            'absensi_tidak_lengkap' => $absensiTidakLengkap,
        ];
    }

    /**
     * C. Cuti & Izin
     */
    private function getCutiStats($buKode)
    {
        $bulanIni = Carbon::now()->startOfMonth();
        $bulanIniEnd = Carbon::now()->endOfMonth();
        $tahunIni = Carbon::now()->year;

        // Get NIK karyawan di BU ini
        $nikList = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->where('Divisi', $buKode)
            ->pluck('Nik')
            ->toArray();

        if (empty($nikList)) {
            return [
                'sisa_cuti_rata_rata' => 0,
                'cuti_diambil_bulan_ini' => 0,
                'cuti_pending_approval' => 0,
                'karyawan_minus_cuti' => 0,
            ];
        }

        // Sisa cuti rata-rata (dari saldo cuti tahun ini)
        $saldoCutiList = SaldoCuti::whereIn('vcNik', $nikList)
            ->where('intTahun', $tahunIni)
            ->get();

        $totalSisaCuti = $saldoCutiList->sum('decSaldoSisa');
        $sisaCutiRataRata = count($saldoCutiList) > 0 
            ? round($totalSisaCuti / count($saldoCutiList), 2) 
            : 0;

        // Cuti yang diambil bulan ini (C010 = Cuti Tahunan, C012 = Cuti Melahirkan)
        $cutiDiambilBulanIni = TidakMasuk::whereIn('vcNik', $nikList)
            ->where(function ($q) use ($bulanIni, $bulanIniEnd) {
                $q->whereBetween('dtTanggalMulai', [$bulanIni->format('Y-m-d'), $bulanIniEnd->format('Y-m-d')])
                  ->orWhereBetween('dtTanggalSelesai', [$bulanIni->format('Y-m-d'), $bulanIniEnd->format('Y-m-d')])
                  ->orWhere(function ($qq) use ($bulanIni, $bulanIniEnd) {
                      $qq->where('dtTanggalMulai', '<=', $bulanIni->format('Y-m-d'))
                        ->where('dtTanggalSelesai', '>=', $bulanIniEnd->format('Y-m-d'));
                  });
            })
            ->join('m_jenis_absen', 't_tidak_masuk.vcKodeAbsen', '=', 'm_jenis_absen.vcKodeAbsen')
            ->whereIn('m_jenis_absen.vcKodeAbsen', ['C010', 'C012']) // Cuti Tahunan & Cuti Melahirkan
            ->count();

        // Cuti pending approval (asumsi: jika ada field status approval, jika tidak ada maka skip)
        // Untuk sementara, kita hitung semua cuti yang belum ada di saldo cuti yang digunakan
        $cutiPendingApproval = 0; // Placeholder - perlu disesuaikan dengan logika approval yang ada

        // Karyawan minus cuti (saldo cuti negatif atau 0)
        $karyawanMinusCuti = SaldoCuti::whereIn('vcNik', $nikList)
            ->where('intTahun', $tahunIni)
            ->where(function ($q) {
                $q->where('decSaldoSisa', '<=', 0)
                  ->orWhereNull('decSaldoSisa');
            })
            ->distinct('vcNik')
            ->count('vcNik');

        return [
            'sisa_cuti_rata_rata' => $sisaCutiRataRata,
            'cuti_diambil_bulan_ini' => $cutiDiambilBulanIni,
            'cuti_pending_approval' => $cutiPendingApproval,
            'karyawan_minus_cuti' => $karyawanMinusCuti,
        ];
    }

    /**
     * D. Payroll BU
     */
    private function getPayrollStats($buKode)
    {
        $bulanIni = Carbon::now()->startOfMonth();
        $bulanIniEnd = Carbon::now()->endOfMonth();

        // Total gaji bruto bulan ini
        $totalGajiBruto = DB::table('t_closing')
            ->where('vcKodeDivisi', $buKode)
            ->whereBetween('periode', [$bulanIni->format('Y-m-d'), $bulanIniEnd->format('Y-m-d')])
            ->select(DB::raw('SUM(
                COALESCE(decGapok, 0) + 
                COALESCE(decUangMakan, 0) + 
                COALESCE(decTransport, 0) + 
                COALESCE(decTotallembur1, 0) + 
                COALESCE(decTotallembur2, 0) + 
                COALESCE(decTotallembur3, 0)
            ) as total'))
            ->value('total') ?? 0;

        // Total potongan bulan ini
        $totalPotongan = DB::table('t_closing')
            ->where('vcKodeDivisi', $buKode)
            ->whereBetween('periode', [$bulanIni->format('Y-m-d'), $bulanIniEnd->format('Y-m-d')])
            ->select(DB::raw('SUM(
                COALESCE(decPotonganHC, 0) + 
                COALESCE(decPotonganBPR, 0) + 
                COALESCE(decIuranSPN, 0) + 
                COALESCE(decPotonganBPJSKes, 0) + 
                COALESCE(decPotonganBPJSJHT, 0) + 
                COALESCE(decPotonganBPJSJP, 0) + 
                COALESCE(decPotonganKoperasi, 0) + 
                COALESCE(decPotonganAbsen, 0) + 
                COALESCE(decPotonganLain, 0)
            ) as total'))
            ->value('total') ?? 0;

        // Total take home pay bulan ini
        $totalTakeHomePay = DB::table('t_closing')
            ->where('vcKodeDivisi', $buKode)
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

        // Lembur & tunjangan bulan ini
        $lemburTunjangan = DB::table('t_closing')
            ->where('vcKodeDivisi', $buKode)
            ->whereBetween('periode', [$bulanIni->format('Y-m-d'), $bulanIniEnd->format('Y-m-d')])
            ->select(DB::raw('SUM(
                COALESCE(decTotallembur1, 0) + 
                COALESCE(decTotallembur2, 0) + 
                COALESCE(decTotallembur3, 0) + 
                COALESCE(decUangMakan, 0) + 
                COALESCE(decTransport, 0)
            ) as total'))
            ->value('total') ?? 0;

        // Slip gaji sudah/belum terbit
        $totalKaryawanBU = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->where('Divisi', $buKode)
            ->count();

        $karyawanSudahSlip = DB::table('t_closing')
            ->where('vcKodeDivisi', $buKode)
            ->whereBetween('periode', [$bulanIni->format('Y-m-d'), $bulanIniEnd->format('Y-m-d')])
            ->distinct('vcNik')
            ->count('vcNik');

        $karyawanBelumSlip = max(0, $totalKaryawanBU - $karyawanSudahSlip);

        return [
            'total_gaji_bruto' => $totalGajiBruto,
            'total_potongan' => $totalPotongan,
            'total_take_home_pay' => $totalTakeHomePay,
            'lembur_tunjangan' => $lemburTunjangan,
            'slip_sudah_terbit' => $karyawanSudahSlip,
            'slip_belum_terbit' => $karyawanBelumSlip,
            'total_karyawan' => $totalKaryawanBU,
        ];
    }

    /**
     * E. Kontrak & Masa Berlaku
     */
    private function getKontrakStats($buKode)
    {
        $tanggalSekarang = Carbon::now();
        $tanggal30HariKedepan = Carbon::now()->addDays(30);

        // Get NIK karyawan di BU ini
        $karyawanList = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->where('Divisi', $buKode)
            ->whereNotNull('Tgl_Masuk')
            ->get();

        // Kontrak habis < 30 hari (untuk PKWT, biasanya 1 tahun dari Tgl_Masuk)
        $kontrakHabis30Hari = $karyawanList->filter(function ($karyawan) use ($tanggalSekarang, $tanggal30HariKedepan) {
            // Asumsi: PKWT = kontrak 1 tahun, PKWTT = tidak ada masa kontrak (tetap)
            // Jika Status_Pegawai mengandung "Kontrak" atau "PKWT", maka kontrak 1 tahun
            $statusPegawai = strtoupper($karyawan->Status_Pegawai ?? '');
            if (strpos($statusPegawai, 'KONTRAK') !== false || strpos($statusPegawai, 'PKWT') !== false) {
                $tanggalKontrakHabis = Carbon::parse($karyawan->Tgl_Masuk)->addYear();
                return $tanggalKontrakHabis->gte($tanggalSekarang) && $tanggalKontrakHabis->lte($tanggal30HariKedepan);
            }
            return false;
        })->map(function ($karyawan) {
            $tanggalKontrakHabis = Carbon::parse($karyawan->Tgl_Masuk)->addYear();
            return [
                'nik' => $karyawan->Nik,
                'nama' => $karyawan->Nama,
                'tanggal_masuk' => Carbon::parse($karyawan->Tgl_Masuk)->format('d/m/Y'),
                'tanggal_kontrak_habis' => $tanggalKontrakHabis->format('d/m/Y'),
                'sisa_hari' => $tanggalKontrakHabis->diffInDays(Carbon::now()),
            ];
        })->values();

        // Group Pegawai (mengganti PKWT vs PKWTT)
        $groupPegawaiStats = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->where('Divisi', $buKode)
            ->whereNotNull('Group_pegawai')
            ->where('Group_pegawai', '!=', '')
            ->select('Group_pegawai', DB::raw('count(*) as jumlah'))
            ->groupBy('Group_pegawai')
            ->orderBy('jumlah', 'desc')
            ->get();
        
        // Ambil 2 group teratas untuk card
        $groupPegawai1 = $groupPegawaiStats->first();
        $groupPegawai2 = $groupPegawaiStats->skip(1)->first();
        
        $groupPegawai1Nama = $groupPegawai1 ? $groupPegawai1->Group_pegawai : 'N/A';
        $groupPegawai1Jumlah = $groupPegawai1 ? $groupPegawai1->jumlah : 0;
        $groupPegawai2Nama = $groupPegawai2 ? $groupPegawai2->Group_pegawai : 'N/A';
        $groupPegawai2Jumlah = $groupPegawai2 ? $groupPegawai2->jumlah : 0;

        // Reminder perpanjangan kontrak: Status_pegawai='Kontrak', status aktif, kurang dari 30 hari sebelum habis (Tgl_Masuk + 1 tahun)
        $reminderPerpanjangan = $karyawanList->filter(function ($karyawan) use ($tanggalSekarang) {
            // Exact match: Status_pegawai = 'Kontrak'
            if (trim($karyawan->Status_Pegawai ?? '') !== 'Kontrak') {
                return false;
            }
            
            // Pastikan status aktif (sudah di-filter di query awal, tapi double check)
            if ($karyawan->vcAktif != '1' || $karyawan->Tgl_Berhenti) {
                return false;
            }
            
            // Cek apakah kontrak akan habis dalam kurang dari 30 hari
            if (!$karyawan->Tgl_Masuk) {
                return false;
            }
            
            $tanggalKontrakHabis = Carbon::parse($karyawan->Tgl_Masuk)->addYear();
            
            // Cek apakah kontrak sudah habis (tanggal kontrak habis sudah lewat)
            if ($tanggalKontrakHabis->lt($tanggalSekarang)) {
                return false;
            }
            
            // Hitung sisa hari sampai kontrak habis
            $sisaHari = $tanggalSekarang->diffInDays($tanggalKontrakHabis, false);
            
            // Kurang dari 30 hari sebelum habis (sisa hari antara 1-30 hari, tidak termasuk yang sudah habis)
            return $sisaHari > 0 && $sisaHari <= 30;
        })->map(function ($karyawan) use ($tanggalSekarang) {
            $tanggalKontrakHabis = Carbon::parse($karyawan->Tgl_Masuk)->addYear();
            $sisaHari = $tanggalSekarang->diffInDays($tanggalKontrakHabis, false);
            return [
                'nik' => $karyawan->Nik,
                'nama' => $karyawan->Nama,
                'tanggal_masuk' => Carbon::parse($karyawan->Tgl_Masuk)->format('d/m/Y'),
                'tanggal_kontrak_habis' => $tanggalKontrakHabis->format('d/m/Y'),
                'sisa_hari' => $sisaHari,
            ];
        })->values();

        return [
            'kontrak_habis_30_hari' => $kontrakHabis30Hari,
            'group_pegawai_1_nama' => $groupPegawai1Nama,
            'group_pegawai_1_jumlah' => $groupPegawai1Jumlah,
            'group_pegawai_2_nama' => $groupPegawai2Nama,
            'group_pegawai_2_jumlah' => $groupPegawai2Jumlah,
            'reminder_perpanjangan' => $reminderPerpanjangan,
        ];
    }

    /**
     * Helper: Calculate total jam kerja
     */
    private function calculateTotalJam($jamMasuk, $jamKeluar, $tanggal)
    {
        try {
            $masuk = Carbon::parse($jamMasuk);
            $keluar = Carbon::parse($jamKeluar);
            
            // Jika jam keluar lebih kecil dari jam masuk, berarti melewati tengah malam
            if ($keluar->lt($masuk)) {
                $keluar->addDay();
            }
            
            return $masuk->diffInHours($keluar);
        } catch (\Exception $e) {
            return 0;
        }
    }
}

