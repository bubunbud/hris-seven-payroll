<?php

namespace App\Http\Controllers;

use App\Models\Absen;
use App\Models\Izin;
use App\Models\TidakMasuk;
use App\Models\Karyawan;
use App\Models\JenisIzin;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatistikAbsensiController extends Controller
{
    public function index(Request $request)
    {
        // Tingkatkan timeout untuk proses yang memakan waktu lama
        set_time_limit(300); // 5 menit

        $startDate = $request->get('dari_tanggal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('sampai_tanggal', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $nik = $request->get('nik');
        $group = $request->get('group');

        // Filter karyawan scope
        $karyawanQuery = Karyawan::query()->where('vcAktif', '1');
        if ($nik) {
            $karyawanQuery->where('Nik', 'like', "%$nik%");
        }
        if ($group) {
            $karyawanQuery->where('Group_pegawai', $group);
        }
        $nikList = $karyawanQuery->pluck('Nik');
        // Jika filter NIK diisi, pakai pencocokan eksak agar agregasi hanya untuk NIK tsb.
        if ($nik) {
            $nikList = collect([$nik]);
        }
        $selectedNama = null;
        if ($nik) {
            $selectedNama = Karyawan::where('Nik', $nik)->value('Nama');
        }

        // Total hari kerja (exclude Sabtu/Minggu & hari libur nasional)
        $holidayDates = \App\Models\HariLibur::whereBetween('dtTanggal', [$startDate, $endDate])
            ->pluck('dtTanggal')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        // Kehadiran aktual: jumlah hari kerja normal yang memiliki absensi (jam masuk atau jam keluar)
        // Hanya menghitung hari kerja normal (tidak termasuk weekend, hari libur, dan lembur hari libur)
        $absenListForHadir = Absen::whereBetween('dtTanggal', [$startDate, $endDate])
            ->when($nikList->isNotEmpty(), fn($q) => $q->whereIn('vcNik', $nikList))
            ->where(function ($q) {
                $q->whereNotNull('dtJamMasuk')->orWhereNotNull('dtJamKeluar');
            })
            ->get();

        $hadir = 0;
        foreach ($absenListForHadir as $ab) {
            $tanggalStr = $ab->dtTanggal instanceof Carbon
                ? $ab->dtTanggal->format('Y-m-d')
                : Carbon::parse($ab->dtTanggal)->format('Y-m-d');
            $tanggal = Carbon::parse($tanggalStr);
            $dow = (int) $tanggal->format('w');
            $isWeekend = ($dow === 0 || $dow === 6);
            $isHoliday = in_array($tanggalStr, $holidayDates, true);

            // Hanya hitung jika hari kerja normal (bukan weekend dan bukan hari libur)
            if (!$isWeekend && !$isHoliday) {
                $hadir++;
            }
        }
        $totalHariKerja = 0;
        $cursor = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        while ($cursor->lte($end)) {
            $dow = (int) $cursor->format('w'); // 0=Min,6=Sabtu
            $isWeekend = ($dow === 0 || $dow === 6);
            $isHoliday = in_array($cursor->format('Y-m-d'), $holidayDates, true);
            if (!$isWeekend && !$isHoliday) {
                $totalHariKerja++;
            }
            $cursor->addDay();
        }

        // Tidak masuk: agregasi berdasarkan jenis absen (m_jenis_absen)
        $tidakMasuk = TidakMasuk::with('jenisAbsen')
            ->when($nikList->isNotEmpty(), fn($q) => $q->whereIn('vcNik', $nikList))
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('dtTanggalMulai', [$startDate, $endDate])
                    ->orWhereBetween('dtTanggalSelesai', [$startDate, $endDate])
                    ->orWhere(function ($qq) use ($startDate, $endDate) {
                        $qq->where('dtTanggalMulai', '<=', $startDate)
                            ->where('dtTanggalSelesai', '>=', $endDate);
                    });
            })
            ->get();

        $ringkasanTidakMasuk = [];
        $totalTidakMasukHari = 0;
        $totalResmiHari = 0; // I001, C010, C011, S010 dihitung sebagai hadir kebijakan
        foreach ($tidakMasuk as $tm) {
            $jenis = $tm->jenisAbsen->vcKeterangan ?? $tm->vcKodeAbsen;
            $hari = $tm->jumlah_hari ?? 0; // accessor dari model
            $ringkasanTidakMasuk[$jenis] = ($ringkasanTidakMasuk[$jenis] ?? 0) + $hari;
            $totalTidakMasukHari += $hari;
            if (in_array(($tm->vcKodeAbsen ?? ''), ['I001', 'C010', 'C011', 'S010'], true)) {
                $totalResmiHari += $hari;
            }
        }

        // Izin keluar komplek: total jam izin (untuk jenis izin pribadi Z003 + Z004)
        // Optimasi: gunakan join langsung untuk menghindari N+1 query
        $izinQuery = DB::table('t_izin as i')
            ->leftJoin('m_karyawan as k', 'i.vcNik', '=', 'k.Nik')
            ->leftJoin('m_shift as s', 'k.vcShift', '=', 's.vcShift')
            ->leftJoin('m_jenis_izin as j', 'i.vcKodeIzin', '=', 'j.vcKodeIzin')
            ->whereBetween('i.dtTanggal', [$startDate, $endDate])
            ->when($nikList->isNotEmpty(), fn($q) => $q->whereIn('i.vcNik', $nikList))
            ->select(
                'i.dtTanggal',
                'i.vcNik',
                'i.vcKodeIzin',
                'i.vcTipeIzin',
                'i.dtDari',
                'i.dtSampai',
                'k.Nama',
                's.vcPulang as shift_pulang'
            );

        $izinKeluar = $izinQuery->get();

        // Hitung total jam izin keluar untuk izin pribadi (Z003 + Z004)
        $totalJamIzinKeluar = 0.0;
        foreach ($izinKeluar as $iz) {
            // Hitung jika vcKodeIzin = 'Z003' (izin keluar pribadi) atau 'Z004' (izin masuk siang pribadi)
            if (!in_array($iz->vcKodeIzin, ['Z003', 'Z004'], true)) {
                continue;
            }

            if (!$iz->dtDari) continue;
            $tanggal = $iz->dtTanggal instanceof Carbon ? $iz->dtTanggal->copy() : Carbon::parse($iz->dtTanggal);
            $dari = $tanggal->copy()->setTimeFromTimeString((string) $iz->dtDari);

            // Tentukan waktu sampai: jika kosong atau 00:00 gunakan jam pulang shift
            $rawSampai = $iz->dtSampai ? (string) $iz->dtSampai : null;
            $isZero = $rawSampai && (substr($rawSampai, 0, 5) === '00:00');
            $shiftPulang = $iz->shift_pulang ? substr((string) $iz->shift_pulang, 0, 5) : null;
            $sampaiClock = (!$rawSampai || $isZero) ? $shiftPulang : substr($rawSampai, 0, 5);
            if (!$sampaiClock) continue; // tidak bisa tentukan akhir

            $sampai = $tanggal->copy()->setTimeFromTimeString($sampaiClock);
            if ($sampai->lessThan($dari)) {
                $sampai->addDay();
            }
            $menit = $dari->diffInMinutes($sampai, true);
            // Kurangi 1 jam jika interval melewati jam istirahat 12:00-13:00
            $lunchStart = $tanggal->copy()->setTimeFromTimeString('12:00');
            $lunchEnd = $tanggal->copy()->setTimeFromTimeString('13:00');
            if ($dari->lt($lunchEnd) && $sampai->gt($lunchStart)) {
                $menit = max(0, $menit - 60);
            }
            $totalJamIzinKeluar += round($menit / 60, 2);
        }

        // Telat & Pulang Cepat (aturan dasar): bandingkan dengan jam shift karyawan jika ada
        // Optimasi: gunakan join langsung untuk menghindari N+1 query
        $telat = 0;
        $pulangCepat = 0;
        $debugTelat = [];
        $debugPulangCepat = [];

        // Query dengan join untuk menghindari eager loading yang berat
        $absenQuery = DB::table('t_absen as a')
            ->leftJoin('m_karyawan as k', 'a.vcNik', '=', 'k.Nik')
            ->leftJoin('m_shift as s', 'k.vcShift', '=', 's.vcShift')
            ->whereBetween('a.dtTanggal', [$startDate, $endDate])
            ->when($nikList->isNotEmpty(), fn($q) => $q->whereIn('a.vcNik', $nikList))
            ->select(
                'a.dtTanggal',
                'a.vcNik',
                'a.dtJamMasuk',
                'a.dtJamKeluar',
                'k.Nama',
                's.vcMasuk as shift_masuk',
                's.vcPulang as shift_pulang'
            );

        $absenList = $absenQuery->get();
        // Inisialisasi variabel untuk perhitungan per NIK
        $telatPerNik = [];
        $pulangCepatPerNik = [];
        $totalSurplusJam = 0.0;
        $totalDefisitJam = 0.0;
        $totalJamStandarKerja = 0.0;
        $totalJamKerjaAktual = 0.0;

        // Loop sekali untuk semua perhitungan (optimasi)
        foreach ($absenList as $ab) {
            $jamMasuk = $ab->dtJamMasuk ? substr((string) $ab->dtJamMasuk, 0, 5) : null;
            $jamKeluar = $ab->dtJamKeluar ? substr((string) $ab->dtJamKeluar, 0, 5) : null;
            $shiftMasuk = $ab->shift_masuk ? substr((string) $ab->shift_masuk, 0, 5) : null;
            $shiftPulang = $ab->shift_pulang ? substr((string) $ab->shift_pulang, 0, 5) : null;

            $tanggal = Carbon::parse($ab->dtTanggal);
            $tanggalStr = $tanggal->format('Y-m-d');

            // Hitung telat
            if ($jamMasuk && $shiftMasuk) {
                $tMasuk = $tanggal->copy()->setTimeFromTimeString($jamMasuk);
                $tShiftMasuk = $tanggal->copy()->setTimeFromTimeString($shiftMasuk);
                if ($tMasuk->greaterThan($tShiftMasuk)) {
                    $selisih = $tShiftMasuk->diffInMinutes($tMasuk);
                    if ($selisih > 1) {
                        $telat++;
                        $telatPerNik[$ab->vcNik] = ($telatPerNik[$ab->vcNik] ?? 0) + 1;
                        // Simpan debug telat
                        $debugTelat[] = [
                            'tanggal' => $tanggalStr,
                            'nik' => $ab->vcNik,
                            'nama' => $ab->Nama ?? '-',
                            'jamMasuk' => $jamMasuk,
                            'jamKeluar' => $jamKeluar,
                            'shiftMasuk' => $shiftMasuk,
                            'shiftPulang' => $shiftPulang,
                            'menitTelat' => $selisih,
                        ];
                    }
                }
            }

            // Hitung pulang cepat
            if ($jamKeluar && $shiftPulang) {
                $tKeluar = $tanggal->copy()->setTimeFromTimeString($jamKeluar);
                $tShiftPulang = $tanggal->copy()->setTimeFromTimeString($shiftPulang);
                if ($tKeluar->lessThan($tShiftPulang)) {
                    $pulangCepat++;
                    $pulangCepatPerNik[$ab->vcNik] = ($pulangCepatPerNik[$ab->vcNik] ?? 0) + 1;
                    // Simpan debug pulang cepat
                    $debugPulangCepat[] = [
                        'tanggal' => $tanggalStr,
                        'nik' => $ab->vcNik,
                        'nama' => $ab->Nama ?? '-',
                        'jamMasuk' => $jamMasuk,
                        'jamKeluar' => $jamKeluar,
                        'shiftMasuk' => $shiftMasuk,
                        'shiftPulang' => $shiftPulang,
                        'menitLebihAwal' => $tKeluar->diffInMinutes($tShiftPulang),
                    ];
                }
            }

            // Hitung surplus/deficit (hanya jika ada jam masuk dan keluar lengkap)
            if ($jamMasuk && $jamKeluar && $shiftMasuk && $shiftPulang) {
                $tMasuk = $tanggal->copy()->setTimeFromTimeString($jamMasuk);
                $tKeluar = $tanggal->copy()->setTimeFromTimeString($jamKeluar);
                if ($tKeluar->lessThan($tMasuk)) {
                    $tKeluar->addDay();
                }
                $menitAktual = $tMasuk->diffInMinutes($tKeluar, true);
                $lunchStart = $tanggal->copy()->setTimeFromTimeString('12:00');
                $lunchEnd = $tanggal->copy()->setTimeFromTimeString('13:00');
                if ($tMasuk->lt($lunchEnd) && $tKeluar->gt($lunchStart)) {
                    $menitAktual = max(0, $menitAktual - 60);
                }

                $tShiftMasuk = $tanggal->copy()->setTimeFromTimeString($shiftMasuk);
                $tShiftPulang = $tanggal->copy()->setTimeFromTimeString($shiftPulang);
                if ($tShiftPulang->lessThan($tShiftMasuk)) {
                    $tShiftPulang->addDay();
                }
                $menitStandar = $tShiftMasuk->diffInMinutes($tShiftPulang, true);
                if ($tShiftMasuk->lt($lunchEnd) && $tShiftPulang->gt($lunchStart)) {
                    $menitStandar = max(0, $menitStandar - 60);
                }

                $totalJamStandarKerja += round($menitStandar / 60, 2);
                $totalJamKerjaAktual += round($menitAktual / 60, 2);

                $selisihMenit = $menitAktual - $menitStandar;
                $selisihJam = round($selisihMenit / 60, 2);

                if ($selisihJam > 0) {
                    $totalSurplusJam += $selisihJam;
                } elseif ($selisihJam < 0) {
                    $totalDefisitJam += abs($selisihJam);
                }
            }
        }

        // Masuk Siang (MS): dari Izin Keluar Komplek Pribadi dengan Tipe = "Masuk Siang" (sama seperti Rekapitulasi Absen All)
        // Pulang Cepat (PC): dari Izin Keluar Komplek Pribadi dengan Tipe = "Pulang Cepat" (sama seperti Rekapitulasi Absen All)
        // Izin Biasa (IB): dari Izin Keluar Komplek Pribadi dengan Tipe = "Izin Biasa" (sama seperti Rekapitulasi Absen All)
        $masukSiang = 0;
        $masukSiangPerNik = [];
        $pulangCepatPerNik = []; // Untuk per karyawan
        $totalJamMasukSiang = 0.0; // Total jam izin masuk siang
        $debugMasukSiang = []; // Untuk debug
        $debugAllIzinZ003Z004 = []; // Debug semua izin Z003 dan Z004
        foreach ($izinKeluar as $iz) {
            // Debug info untuk semua izin Z003 dan Z004
            if (in_array($iz->vcKodeIzin, ['Z003', 'Z004'], true)) {
                // Hitung durasi izin (dikurangi jam istirahat jika melewati 12:00-13:00)
                $durasiJam = 0;
                $durasiMenit = 0;
                $durasiText = '-';
                $dtSampaiDisplay = '-';

                if ($iz->dtDari) {
                    $tanggalIzin = $iz->dtTanggal instanceof Carbon ? $iz->dtTanggal->copy() : Carbon::parse($iz->dtTanggal);
                    $dariIzin = $tanggalIzin->copy()->setTimeFromTimeString((string) $iz->dtDari);

                    // Tentukan sampai: jika kosong atau 00:00 gunakan jam pulang shift
                    $rawSampaiIzin = $iz->dtSampai ? (string) $iz->dtSampai : null;
                    $isZeroIzin = $rawSampaiIzin && (substr($rawSampaiIzin, 0, 5) === '00:00');
                    $shiftPulangIzin = $iz->shift_pulang ? substr((string) $iz->shift_pulang, 0, 5) : null;
                    $sampaiClockIzin = (!$rawSampaiIzin || $isZeroIzin) ? $shiftPulangIzin : substr($rawSampaiIzin, 0, 5);

                    // Untuk display, jika menggunakan shift pulang, tampilkan dengan catatan
                    if (!$rawSampaiIzin || $isZeroIzin) {
                        $dtSampaiDisplay = $shiftPulangIzin ? $shiftPulangIzin . ' (shift)' : '-';
                    } else {
                        $dtSampaiDisplay = substr($rawSampaiIzin, 0, 5);
                    }

                    if ($sampaiClockIzin) {
                        $sampaiIzin = $tanggalIzin->copy()->setTimeFromTimeString($sampaiClockIzin);
                        if ($sampaiIzin->lessThan($dariIzin)) {
                            $sampaiIzin->addDay();
                        }
                        $menitIzin = $dariIzin->diffInMinutes($sampaiIzin, true);
                        $lunchStart = $tanggalIzin->copy()->setTimeFromTimeString('12:00');
                        $lunchEnd = $tanggalIzin->copy()->setTimeFromTimeString('13:00');
                        if ($dariIzin->lt($lunchEnd) && $sampaiIzin->gt($lunchStart)) {
                            $menitIzin = max(0, $menitIzin - 60);
                        }
                        $durasiJam = floor($menitIzin / 60);
                        $durasiMenit = $menitIzin % 60;
                        $durasiText = $durasiJam . ' jam ' . $durasiMenit . ' menit';
                    }
                }

                $debugAllIzinZ003Z004[] = [
                    'tanggal' => $iz->dtTanggal ? Carbon::parse($iz->dtTanggal)->format('Y-m-d') : '-',
                    'nik' => $iz->vcNik,
                    'nama' => $iz->Nama ?? '-',
                    'vcKodeIzin' => $iz->vcKodeIzin,
                    'dtDari' => $iz->dtDari ? substr((string) $iz->dtDari, 0, 5) : '-',
                    'dtSampai' => $dtSampaiDisplay,
                    'durasi' => ($iz->dtDari) ? 'Dihitung' : 'Tidak lengkap',
                    'durasiText' => $durasiText,
                    'durasiJam' => $durasiJam,
                    'durasiMenit' => $durasiMenit,
                ];
            }

            // Masuk Siang (MS): izin keluar komplek pribadi (Z003/Z004) dengan Tipe = "Masuk Siang" (sama seperti Rekapitulasi Absen All)
            // Hanya hitung jika hari kerja normal
            $tanggalIzin = $iz->dtTanggal instanceof Carbon
                ? $iz->dtTanggal->format('Y-m-d')
                : Carbon::parse($iz->dtTanggal)->format('Y-m-d');
            $tanggalObj = Carbon::parse($tanggalIzin);
            $dow = (int) $tanggalObj->format('w');
            $isWeekend = ($dow === 0 || $dow === 6);
            $isHoliday = in_array($tanggalIzin, $holidayDates, true);

            if (!$isWeekend && !$isHoliday) {
                $tipeIzin = $iz->vcTipeIzin ?? '';

                // MS: Tipe = "Masuk Siang"
                if ($tipeIzin === 'Masuk Siang') {
                    $masukSiang++;
                    $masukSiangPerNik[$iz->vcNik] = ($masukSiangPerNik[$iz->vcNik] ?? 0) + 1;

                    // Hitung total jam masuk siang untuk debug
                    if ($iz->dtDari) {
                        $tanggal = $iz->dtTanggal instanceof Carbon ? $iz->dtTanggal->copy() : Carbon::parse($iz->dtTanggal);
                        $dari = $tanggal->copy()->setTimeFromTimeString((string) $iz->dtDari);
                        $rawSampai = $iz->dtSampai ? (string) $iz->dtSampai : null;
                        $isZero = $rawSampai && (substr($rawSampai, 0, 5) === '00:00');
                        $shiftPulang = $iz->shift_pulang ? substr((string) $iz->shift_pulang, 0, 5) : null;
                        $sampaiClock = (!$rawSampai || $isZero) ? $shiftPulang : substr($rawSampai, 0, 5);
                        if ($sampaiClock) {
                            $sampai = $tanggal->copy()->setTimeFromTimeString($sampaiClock);
                            if ($sampai->lessThan($dari)) {
                                $sampai->addDay();
                            }
                            $menit = $dari->diffInMinutes($sampai, true);
                            $lunchStart = $tanggal->copy()->setTimeFromTimeString('12:00');
                            $lunchEnd = $tanggal->copy()->setTimeFromTimeString('13:00');
                            if ($dari->lt($lunchEnd) && $sampai->gt($lunchStart)) {
                                $menit = max(0, $menit - 60);
                            }
                            $jamMasukSiang = round($menit / 60, 2);
                            $totalJamMasukSiang += $jamMasukSiang;

                            // Debug info untuk yang masuk siang
                            $debugMasukSiang[] = [
                                'tanggal' => Carbon::parse($iz->dtTanggal)->format('Y-m-d'),
                                'nik' => $iz->vcNik,
                                'nama' => $iz->Nama ?? '-',
                                'vcKodeIzin' => $iz->vcKodeIzin,
                                'dtDari' => $iz->dtDari,
                                'dtSampai' => $sampaiClock,
                                'jamMasukSiang' => $jamMasukSiang,
                            ];
                        }
                    }
                }
            }
        }

        // Kehadiran Kebijakan: hadir + hari resmi (I001, C010, C011, S010). Parameter dibayar diabaikan.
        $hadirKebijakan = $hadir + $totalResmiHari;

        // Build ringkasan per karyawan
        $perKaryawan = [];
        $karyawanData = Karyawan::whereIn('Nik', $nikList)->where('vcAktif', '1')
            ->select('Nik', 'Nama', 'Tgl_Masuk')
            ->get()
            ->keyBy('Nik');

        $nikToNama = $karyawanData->pluck('Nama', 'Nik');
        $nikToTglMasuk = $karyawanData->pluck('Tgl_Masuk', 'Nik');

        // Kehadiran aktual per NIK: hanya hari kerja normal (tidak termasuk weekend, hari libur, dan lembur hari libur)
        $absenListForHadirPerNik = Absen::whereBetween('dtTanggal', [$startDate, $endDate])
            ->when($nikList->isNotEmpty(), fn($q) => $q->whereIn('vcNik', $nikList))
            ->where(function ($q) {
                $q->whereNotNull('dtJamMasuk')->orWhereNotNull('dtJamKeluar');
            })
            ->get();

        $hadirPerNik = [];
        foreach ($absenListForHadirPerNik as $ab) {
            $tanggalStr = $ab->dtTanggal instanceof Carbon
                ? $ab->dtTanggal->format('Y-m-d')
                : Carbon::parse($ab->dtTanggal)->format('Y-m-d');
            $tanggal = Carbon::parse($tanggalStr);
            $dow = (int) $tanggal->format('w');
            $isWeekend = ($dow === 0 || $dow === 6);
            $isHoliday = in_array($tanggalStr, $holidayDates, true);

            // Hanya hitung jika hari kerja normal (bukan weekend dan bukan hari libur)
            if (!$isWeekend && !$isHoliday) {
                $hadirPerNik[$ab->vcNik] = ($hadirPerNik[$ab->vcNik] ?? 0) + 1;
            }
        }

        $sakitSuratPerNik = [];
        $cutiTahunanPerNik = [];
        $izinResmiPerNik = [];   // I001 - sama seperti IR di Rekapitulasi Absen All
        $izinPribadiPerNik = []; // I002
        $izinOrganisasiPerNik = []; // I003 - untuk persentase Kehadiran (Kebijakan)
        foreach ($tidakMasuk as $tm) {
            if (!$tm->dtTanggalMulai || !$tm->dtTanggalSelesai) continue;

            $kode = $tm->vcKodeAbsen ?? '';
            $nikKey = $tm->vcNik;
            $mulai = Carbon::parse($tm->dtTanggalMulai);
            $selesai = Carbon::parse($tm->dtTanggalSelesai);

            // Hitung hanya hari kerja normal dalam range tidak masuk (sama seperti Rekapitulasi Absen All)
            $hariKerjaNormal = 0;
            $cursor = $mulai->copy();
            while ($cursor->lte($selesai)) {
                $tanggalStr = $cursor->format('Y-m-d');
                $dow = (int) $cursor->format('w');
                $isWeekend = ($dow === 0 || $dow === 6);
                $isHoliday = in_array($tanggalStr, $holidayDates, true);

                // Hanya hitung jika hari kerja normal dan dalam range periode
                if (
                    !$isWeekend && !$isHoliday &&
                    $tanggalStr >= $startDate && $tanggalStr <= $endDate
                ) {
                    $hariKerjaNormal++;
                }
                $cursor->addDay();
            }

            if ($kode === 'S010') {
                $sakitSuratPerNik[$nikKey] = ($sakitSuratPerNik[$nikKey] ?? 0) + $hariKerjaNormal;
            } elseif ($kode === 'C010') {
                $cutiTahunanPerNik[$nikKey] = ($cutiTahunanPerNik[$nikKey] ?? 0) + $hariKerjaNormal;
            } elseif ($kode === 'I001') {
                $izinResmiPerNik[$nikKey] = ($izinResmiPerNik[$nikKey] ?? 0) + $hariKerjaNormal;
            } elseif ($kode === 'I002') {
                $izinPribadiPerNik[$nikKey] = ($izinPribadiPerNik[$nikKey] ?? 0) + $hariKerjaNormal;
            } elseif ($kode === 'I003') {
                $izinOrganisasiPerNik[$nikKey] = ($izinOrganisasiPerNik[$nikKey] ?? 0) + $hariKerjaNormal;
            }
        }

        $izinKeluarJamPerNik = [];
        $keluarKomplekPerNik = []; // IB: Jumlah kejadian izin keluar komplek dengan Tipe = "Izin Biasa" atau kosong
        $pulangCepatPerNikIzin = []; // PC: Jumlah kejadian izin keluar komplek dengan Tipe = "Pulang Cepat" (sama seperti Rekapitulasi Absen All)
        $izinKeluarKomplekJamPerNik = []; // Total jam izin keluar komplek (IB) per NIK
        $izinPulangCepatJamPerNik = []; // Total jam izin pulang cepat (PC) per NIK
        foreach ($izinKeluar as $iz) {
            // Hitung jika vcKodeIzin = 'Z003' (izin keluar pribadi) atau 'Z004' (izin masuk siang pribadi)
            if (!in_array($iz->vcKodeIzin, ['Z003', 'Z004'], true)) {
                continue;
            }

            $tanggalStr = $iz->dtTanggal instanceof Carbon
                ? $iz->dtTanggal->format('Y-m-d')
                : Carbon::parse($iz->dtTanggal)->format('Y-m-d');
            $tanggalObj = Carbon::parse($tanggalStr);
            $dow = (int) $tanggalObj->format('w');
            $isWeekend = ($dow === 0 || $dow === 6);
            $isHoliday = in_array($tanggalStr, $holidayDates, true);

            // Hitung durasi izin (untuk semua tipe)
            $jamIzin = 0.0;
            if ($iz->dtDari) {
                $tanggal = $iz->dtTanggal instanceof Carbon ? $iz->dtTanggal->copy() : Carbon::parse($iz->dtTanggal);
                $dari = $tanggal->copy()->setTimeFromTimeString((string) $iz->dtDari);
                $rawSampai = $iz->dtSampai ? (string) $iz->dtSampai : null;
                $isZero = $rawSampai && (substr($rawSampai, 0, 5) === '00:00');
                $shiftPulang = $iz->shift_pulang ? substr((string) $iz->shift_pulang, 0, 5) : null;
                $sampaiClock = (!$rawSampai || $isZero) ? $shiftPulang : substr($rawSampai, 0, 5);
                if ($sampaiClock) {
                    $sampai = $tanggal->copy()->setTimeFromTimeString($sampaiClock);
                    if ($sampai->lessThan($dari)) {
                        $sampai->addDay();
                    }
                    $menit = $dari->diffInMinutes($sampai, true);
                    $lunchStart = $tanggal->copy()->setTimeFromTimeString('12:00');
                    $lunchEnd = $tanggal->copy()->setTimeFromTimeString('13:00');
                    if ($dari->lt($lunchEnd) && $sampai->gt($lunchStart)) {
                        $menit = max(0, $menit - 60);
                    }
                    $jamIzin = round($menit / 60, 2);
                }
            }

            // Total jam izin keluar (Z003 + Z004) untuk semua tipe
            $izinKeluarJamPerNik[$iz->vcNik] = ($izinKeluarJamPerNik[$iz->vcNik] ?? 0) + $jamIzin;

            // Hanya hitung jika hari kerja normal
            if (!$isWeekend && !$isHoliday) {
                $tipeIzin = $iz->vcTipeIzin ?? '';

                // IB (Izin Biasa): Tipe = "Izin Biasa" atau kosong (dianggap Izin Biasa)
                if ($tipeIzin === 'Izin Biasa' || $tipeIzin === '') {
                    $keluarKomplekPerNik[$iz->vcNik] = ($keluarKomplekPerNik[$iz->vcNik] ?? 0) + 1;
                    // Total jam izin keluar komplek (IB)
                    $izinKeluarKomplekJamPerNik[$iz->vcNik] = ($izinKeluarKomplekJamPerNik[$iz->vcNik] ?? 0) + $jamIzin;
                }

                // PC (Pulang Cepat): Tipe = "Pulang Cepat" (sama seperti Rekapitulasi Absen All)
                if ($tipeIzin === 'Pulang Cepat') {
                    $pulangCepatPerNikIzin[$iz->vcNik] = ($pulangCepatPerNikIzin[$iz->vcNik] ?? 0) + 1;
                    // Total jam izin pulang cepat (PC)
                    $izinPulangCepatJamPerNik[$iz->vcNik] = ($izinPulangCepatJamPerNik[$iz->vcNik] ?? 0) + $jamIzin;
                }
            }
        }

        // Perhitungan telat, pulang cepat, dan surplus/deficit sudah dilakukan di loop sebelumnya
        // Tidak perlu loop lagi karena sudah dioptimasi

        // Hitung JHK per karyawan berdasarkan Tgl_Masuk dan total JHK
        $totalJHK = 0;
        foreach ($nikList as $n) {
            $karyawan = $karyawanData[$n] ?? null;
            $jhkKaryawan = $this->calculateJHKPerKaryawan($karyawan, $startDate, $endDate, $holidayDates);
            $totalJHK += $jhkKaryawan;

            $perKaryawan[] = [
                'nik' => $n,
                'nama' => $nikToNama[$n] ?? '-',
                'tglMasuk' => $nikToTglMasuk[$n] ?? null,
                'hariKerja' => $jhkKaryawan,
                'hadir' => (int) ($hadirPerNik[$n] ?? 0),
                'cutiTahunan' => (int) ($cutiTahunanPerNik[$n] ?? 0),
                'sakitSurat' => (int) ($sakitSuratPerNik[$n] ?? 0),
                'izinPribadi' => (int) ($izinPribadiPerNik[$n] ?? 0),
                'izinResmi' => (int) ($izinResmiPerNik[$n] ?? 0),
                'izinOrganisasi' => (int) ($izinOrganisasiPerNik[$n] ?? 0), // Untuk persentase Kehadiran (Kebijakan)
                'telat' => (int) ($telatPerNik[$n] ?? 0),
                'masukSiang' => (int) ($masukSiangPerNik[$n] ?? 0),
                'keluarKomplek' => (int) ($keluarKomplekPerNik[$n] ?? 0),
                'pulangCepat' => (int) ($pulangCepatPerNikIzin[$n] ?? 0), // PC dari izin keluar komplek, bukan dari absensi
                'izinKeluarJam' => (float) ($izinKeluarJamPerNik[$n] ?? 0),
            ];
        }

        // Dropdown group pegawai
        $groups = Karyawan::select('Group_pegawai')
            ->whereNotNull('Group_pegawai')
            ->distinct()
            ->orderBy('Group_pegawai')
            ->pluck('Group_pegawai');

        // Rata-rata jam izin keluar per karyawan pada scope filter
        $jumlahKaryawan = max(1, $nikList->count());
        $rataJamIzinKeluar = round($totalJamIzinKeluar / $jumlahKaryawan, 2);

        // Debug Tidak Masuk untuk NIK terpilih
        $debugTidakMasuk = [];
        foreach ($tidakMasuk as $tm) {
            if ($nik && $tm->vcNik !== $nik) {
                continue;
            }
            $debugTidakMasuk[] = [
                'nik' => $tm->vcNik,
                'nama' => $nikToNama[$tm->vcNik] ?? '-',
                'kode' => $tm->vcKodeAbsen,
                'keterangan' => $tm->jenisAbsen->vcKeterangan ?? $tm->vcKodeAbsen,
                'mulai' => $tm->dtTanggalMulai ? Carbon::parse($tm->dtTanggalMulai)->format('Y-m-d') : '-',
                'selesai' => $tm->dtTanggalSelesai ? Carbon::parse($tm->dtTanggalSelesai)->format('Y-m-d') : '-',
                'hari' => $tm->jumlah_hari ?? 0,
            ];
        }

        // Hitung persentase Kehadiran (Aktual) dan Kehadiran (Kebijakan)
        $persentaseKehadiranAktual = $totalJHK > 0 ? round(($hadir / $totalJHK) * 100, 2) : 0;

        // Total Sakit, Cuti, Izin Resmi, Izin Organisasi untuk persentase Kehadiran (Kebijakan)
        $totalSakit = array_sum($sakitSuratPerNik);
        $totalCuti = array_sum($cutiTahunanPerNik);
        $totalIzinResmi = array_sum($izinResmiPerNik);
        $totalIzinOrganisasi = array_sum($izinOrganisasiPerNik);
        $persentaseKehadiranKebijakan = $totalJHK > 0
            ? round((($hadir + $totalSakit + $totalCuti + $totalIzinResmi + $totalIzinOrganisasi) / $totalJHK) * 100, 2)
            : 0;

        // Total jam izin keluar komplek (IB) dan pulang cepat (PC)
        $totalJamIzinKeluarKomplek = array_sum($izinKeluarKomplekJamPerNik);
        $totalJamIzinPulangCepat = array_sum($izinPulangCepatJamPerNik);

        // Hitung total jam standar kerja berdasarkan hari kerja
        // Asumsi: 8 jam per hari kerja (bisa disesuaikan jika ada data shift standar)
        $jamKerjaStandarPerHari = 8.0; // 8 jam per hari kerja standar
        $totalJamStandarKerjaUmum = $totalHariKerja * $jamKerjaStandarPerHari;
        $totalJamStandarKerjaKaryawan = $totalJHK * $jamKerjaStandarPerHari;

        // Surplus/Defisit Jam Kerja = Total Jam Kerja Aktual - Total Jam Standar Kerja (Hari Kerja Karyawan)
        $surplusDefisitJamKerja = $totalJamKerjaAktual - $totalJamStandarKerjaKaryawan;

        return view('absen.statistik.index', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'nik' => $nik,
            'group' => $group,
            'groups' => $groups,
            'hadir' => $hadir,
            'ringkasanTidakMasuk' => $ringkasanTidakMasuk,
            'totalTidakMasukHari' => $totalTidakMasukHari,
            'totalJamIzinKeluar' => $totalJamIzinKeluar,
            'rataJamIzinKeluar' => $rataJamIzinKeluar,
            'totalJamMasukSiang' => $totalJamMasukSiang,
            'totalHariKerja' => $totalHariKerja,
            'perKaryawan' => $perKaryawan,
            'telat' => $telat,
            'pulangCepat' => $pulangCepat,
            'masukSiang' => $masukSiang,
            'hadirKebijakan' => $hadirKebijakan,
            'selectedNama' => $selectedNama,
            'debugMasukSiang' => $debugMasukSiang, // Untuk debugging
            'debugAllIzinZ003Z004' => $debugAllIzinZ003Z004, // Debug semua izin Z003 dan Z004
            'debugTelat' => $debugTelat,
            'debugPulangCepat' => $debugPulangCepat,
            'debugTidakMasuk' => $debugTidakMasuk,
            'totalSurplusJam' => $totalSurplusJam,
            'totalDefisitJam' => $totalDefisitJam,
            'totalJamStandarKerja' => $totalJamStandarKerja,
            'totalJamKerjaAktual' => $totalJamKerjaAktual,
            'totalJHK' => $totalJHK, // Total JHK semua karyawan yang difilter
            'persentaseKehadiranAktual' => $persentaseKehadiranAktual,
            'persentaseKehadiranKebijakan' => $persentaseKehadiranKebijakan,
            'totalJamIzinKeluarKomplek' => $totalJamIzinKeluarKomplek, // Total jam izin keluar komplek (IB)
            'totalJamIzinPulangCepat' => $totalJamIzinPulangCepat, // Total jam izin pulang cepat (PC)
            'totalJamStandarKerjaUmum' => $totalJamStandarKerjaUmum, // Total jam standar kerja berdasarkan hari kerja umum
            'totalJamStandarKerjaKaryawan' => $totalJamStandarKerjaKaryawan, // Total jam standar kerja berdasarkan JHK karyawan
            'surplusDefisitJamKerja' => $surplusDefisitJamKerja, // Surplus/Defisit Jam Kerja = Total Jam Kerja Aktual - Total Jam Standar Kerja (Hari Kerja Karyawan)
        ]);
    }

    /**
     * Hitung JHK per karyawan berdasarkan Tgl_Masuk
     * - Jika Tgl_Masuk <= startDate: JHK = jumlah hari kerja normal (seluruh periode)
     * - Jika startDate < Tgl_Masuk <= endDate: JHK = jumlah hari kerja normal dari Tgl_Masuk sampai endDate
     * - Jika Tgl_Masuk > endDate: JHK = 0
     */
    private function calculateJHKPerKaryawan($karyawan, $startDate, $endDate, $holidayDates)
    {
        // Jika tidak ada karyawan atau Tgl_Masuk, gunakan seluruh periode
        if (!$karyawan || !$karyawan->Tgl_Masuk) {
            return $this->calculateHariKerja($startDate, $endDate, $holidayDates);
        }

        $tglMasuk = Carbon::parse($karyawan->Tgl_Masuk)->format('Y-m-d');
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Jika Tgl_Masuk <= startDate: gunakan seluruh periode
        if ($tglMasuk <= $startDate) {
            return $this->calculateHariKerja($startDate, $endDate, $holidayDates);
        }

        // Jika Tgl_Masuk > endDate: JHK = 0 (belum masuk kerja)
        if ($tglMasuk > $endDate) {
            return 0;
        }

        // Jika startDate < Tgl_Masuk <= endDate: hitung dari Tgl_Masuk sampai endDate
        $jumlahHariKerja = 0;
        $cursor = Carbon::parse($tglMasuk);
        while ($cursor->lte($end)) {
            $dow = (int) $cursor->format('w');
            $isWeekend = ($dow === 0 || $dow === 6);
            $isHoliday = in_array($cursor->format('Y-m-d'), $holidayDates, true);
            if (!$isWeekend && !$isHoliday) {
                $jumlahHariKerja++;
            }
            $cursor->addDay();
        }
        return $jumlahHariKerja;
    }

    /**
     * Hitung jumlah hari kerja normal (exclude Sabtu/Minggu & hari libur)
     */
    private function calculateHariKerja($startDate, $endDate, $holidayDates)
    {
        $jumlahHariKerja = 0;
        $cursor = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        while ($cursor->lte($end)) {
            $dow = (int) $cursor->format('w');
            $isWeekend = ($dow === 0 || $dow === 6);
            $isHoliday = in_array($cursor->format('Y-m-d'), $holidayDates, true);
            if (!$isWeekend && !$isHoliday) {
                $jumlahHariKerja++;
            }
            $cursor->addDay();
        }
        return $jumlahHariKerja;
    }
}
