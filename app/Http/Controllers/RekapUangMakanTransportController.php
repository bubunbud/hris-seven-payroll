<?php

namespace App\Http\Controllers;

use App\Models\Closing;
use App\Models\Divisi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RekapUangMakanTransportController extends Controller
{
    /**
     * Display form untuk cetak rekap uang makan dan transport
     */
    public function index()
    {
        $divisis = Divisi::orderBy('vcKodeDivisi')->get();

        // Default: tanggal 1 atau 15 bulan ini (tergantung tanggal hari ini)
        $hariIni = Carbon::now()->day;
        $defaultPeriode = $hariIni <= 15
            ? Carbon::now()->startOfMonth()->format('Y-m-d') // Tanggal 1
            : Carbon::now()->startOfMonth()->addDays(14)->format('Y-m-d'); // Tanggal 15

        return view('laporan.rekap-uang-makan-transport.index', compact('divisis', 'defaultPeriode'));
    }

    /**
     * Preview/Print rekap uang makan dan transport berdasarkan filter
     */
    public function preview(Request $request)
    {
        $request->validate([
            'periode' => 'required|date',
            'divisi' => 'nullable|string',
        ]);

        $tanggalPeriode = Carbon::parse($request->periode)->format('Y-m-d');
        $kodeDivisi = $request->divisi;

        // Query closing data berdasarkan periode gajian
        $query = Closing::with(['karyawan.departemen', 'karyawan.bagian', 'divisi'])
            ->where('periode', $tanggalPeriode)
            ->whereHas('karyawan', function ($q) {
                $q->where('vcAktif', '1'); // Hanya karyawan aktif
            });

        if ($kodeDivisi && $kodeDivisi != 'SEMUA') {
            $query->where('vcKodeDivisi', $kodeDivisi);
        }

        $closings = $query->orderBy('vcKodeDivisi')
            ->orderBy('vcNik')
            ->orderBy('vcClosingKe')
            ->get();

        if ($closings->isEmpty()) {
            return redirect()->route('rekap-uang-makan-transport.index')
                ->with('error', 'Tidak ada data untuk periode yang dipilih');
        }

        // Ambil tanggal awal dan akhir dari data pertama
        $tanggalAwal = $closings->first()->vcPeriodeAwal;
        $tanggalAkhir = $closings->first()->vcPeriodeAkhir;

        // Group data secara hierarkis: Divisi -> Departemen -> Bagian -> Karyawan
        $groupedData = $this->groupDataHierarchically($closings);

        // Hitung grand total
        $grandTotal = $this->calculateGrandTotal($closings);

        // Ambil data divisi untuk header dan tanda tangan
        $divisiData = null;
        if ($kodeDivisi && $kodeDivisi != 'SEMUA') {
            $divisiData = Divisi::where('vcKodeDivisi', $kodeDivisi)->first();
        }
        $namaDivisi = $divisiData ? $divisiData->vcNamaDivisi : '';

        return view('laporan.rekap-uang-makan-transport.preview', compact(
            'groupedData',
            'grandTotal',
            'tanggalAwal',
            'tanggalAkhir',
            'tanggalPeriode',
            'namaDivisi',
            'kodeDivisi',
            'divisiData'
        ));
    }

    /**
     * Group data secara hierarkis: Divisi -> Departemen -> Bagian -> Karyawan
     * Menggunakan hirarki dari m_hirarki_dept dan m_hirarki_bagian
     */
    private function groupDataHierarchically($closings)
    {
        $grouped = [];

        // Ambil semua divisi yang ada di data
        $divisiKodes = $closings->pluck('vcKodeDivisi')->unique();

        foreach ($divisiKodes as $divisiKode) {
            // Ambil departemen berdasarkan hirarki dari m_hirarki_dept
            $hirarkiDept = DB::table('m_hirarki_dept')
                ->join('m_dept', 'm_hirarki_dept.vcKodeDept', '=', 'm_dept.vcKodeDept')
                ->where('m_hirarki_dept.vcKodeDivisi', $divisiKode)
                ->select('m_hirarki_dept.vcKodeDept', 'm_dept.vcNamaDept')
                ->orderBy('m_dept.vcKodeDept')
                ->get();

            $divisi = Divisi::where('vcKodeDivisi', $divisiKode)->first();
            $grouped[$divisiKode] = [
                'kode' => $divisiKode,
                'nama' => $divisi->vcNamaDivisi ?? $divisiKode,
                'departemens' => [],
            ];

            // Loop melalui departemen berdasarkan hirarki
            foreach ($hirarkiDept as $hirarkiDeptItem) {
                $deptKode = $hirarkiDeptItem->vcKodeDept;

                // Cek apakah ada data karyawan untuk departemen ini
                $hasDataForDept = $closings->filter(function ($closing) use ($divisiKode, $deptKode) {
                    $karyawan = $closing->karyawan;
                    if (!$karyawan) return false;
                    return $closing->vcKodeDivisi == $divisiKode && ($karyawan->dept ?? '') == $deptKode;
                })->count() > 0;

                // Skip departemen yang tidak ada datanya
                if (!$hasDataForDept) {
                    continue;
                }

                // Ambil bagian berdasarkan hirarki dari m_hirarki_bagian
                $hirarkiBagian = DB::table('m_hirarki_bagian')
                    ->join('m_bagian', 'm_hirarki_bagian.vcKodeBagian', '=', 'm_bagian.vcKodeBagian')
                    ->where('m_hirarki_bagian.vcKodeDivisi', $divisiKode)
                    ->where('m_hirarki_bagian.vcKodeDept', $deptKode)
                    ->select('m_hirarki_bagian.vcKodeBagian', 'm_bagian.vcNamaBagian')
                    ->orderBy('m_bagian.vcKodeBagian')
                    ->get();

                $grouped[$divisiKode]['departemens'][$deptKode] = [
                    'kode' => $deptKode,
                    'nama' => $hirarkiDeptItem->vcNamaDept,
                    'bagians' => [],
                ];

                // Loop melalui bagian berdasarkan hirarki
                foreach ($hirarkiBagian as $hirarkiBagianItem) {
                    $bagianKode = $hirarkiBagianItem->vcKodeBagian;

                    // Cari semua closing yang sesuai dengan divisi, departemen, dan bagian ini
                    $closingsForBagian = $closings->filter(function ($closing) use ($divisiKode, $deptKode, $bagianKode) {
                        $karyawan = $closing->karyawan;
                        if (!$karyawan) return false;
                        return $closing->vcKodeDivisi == $divisiKode
                            && ($karyawan->dept ?? '') == $deptKode
                            && ($karyawan->vcKodeBagian ?? '') == $bagianKode;
                    });

                    // Skip bagian yang tidak ada datanya
                    if ($closingsForBagian->isEmpty()) {
                        continue;
                    }

                    $grouped[$divisiKode]['departemens'][$deptKode]['bagians'][$bagianKode] = [
                        'kode' => $bagianKode,
                        'nama' => $hirarkiBagianItem->vcNamaBagian,
                        'karyawans' => $closingsForBagian->values()->all(),
                    ];
                }

                // Jika departemen tidak memiliki bagian dengan data, hapus
                if (empty($grouped[$divisiKode]['departemens'][$deptKode]['bagians'])) {
                    unset($grouped[$divisiKode]['departemens'][$deptKode]);
                }
            }

            // Handle karyawan yang tidak ada di hirarki (fallback)
            $closingsWithoutHirarki = $closings->filter(function ($closing) use ($divisiKode, $grouped) {
                if ($closing->vcKodeDivisi != $divisiKode) return false;

                $karyawan = $closing->karyawan;
                if (!$karyawan) return false;

                $deptKode = $karyawan->dept ?? 'UNKNOWN';
                $bagianKode = $karyawan->vcKodeBagian ?? 'UNKNOWN';

                // Cek apakah sudah ada di grouped
                return !isset($grouped[$divisiKode]['departemens'][$deptKode]['bagians'][$bagianKode]);
            });

            foreach ($closingsWithoutHirarki as $closing) {
                $karyawan = $closing->karyawan;
                if (!$karyawan) continue;

                $deptKode = $karyawan->dept ?? 'UNKNOWN';
                $bagianKode = $karyawan->vcKodeBagian ?? 'UNKNOWN';

                // Initialize departemen jika belum ada
                if (!isset($grouped[$divisiKode]['departemens'][$deptKode])) {
                    $departemen = $karyawan->departemen;
                    $grouped[$divisiKode]['departemens'][$deptKode] = [
                        'kode' => $deptKode,
                        'nama' => $departemen->vcNamaDept ?? $deptKode,
                        'bagians' => [],
                    ];
                }

                // Initialize bagian jika belum ada
                if (!isset($grouped[$divisiKode]['departemens'][$deptKode]['bagians'][$bagianKode])) {
                    $bagian = $karyawan->bagian;
                    $grouped[$divisiKode]['departemens'][$deptKode]['bagians'][$bagianKode] = [
                        'kode' => $bagianKode,
                        'nama' => $bagian->vcNamaBagian ?? $bagianKode,
                        'karyawans' => [],
                    ];
                }

                // Add karyawan
                $grouped[$divisiKode]['departemens'][$deptKode]['bagians'][$bagianKode]['karyawans'][] = $closing;
            }

            // Jika divisi tidak memiliki departemen dengan data, hapus
            if (empty($grouped[$divisiKode]['departemens'])) {
                unset($grouped[$divisiKode]);
            }
        }

        // Calculate totals for each level
        foreach ($grouped as $divisiKode => &$divisi) {
            foreach ($divisi['departemens'] as $deptKode => &$departemen) {
                foreach ($departemen['bagians'] as $bagianKode => &$bagian) {
                    // Calculate total for bagian
                    $bagian['total'] = $this->calculateTotal($bagian['karyawans']);
                }
                // Calculate total for departemen
                $departemen['total'] = $this->calculateTotal(
                    collect($departemen['bagians'])->flatMap(function ($bagian) {
                        return $bagian['karyawans'];
                    })->all()
                );
            }
            // Calculate total for divisi
            $divisi['total'] = $this->calculateTotal(
                collect($divisi['departemens'])->flatMap(function ($departemen) {
                    return collect($departemen['bagians'])->flatMap(function ($bagian) {
                        return $bagian['karyawans'];
                    });
                })->all()
            );
        }

        return $grouped;
    }

    /**
     * Hitung total untuk sekelompok closing
     */
    private function calculateTotal($closings)
    {
        $total = [
            'makan_kerja' => 0,
            'makan_libur' => 0,
            'uang_makan' => 0,
            'transport_kerja' => 0,
            'transport_libur' => 0,
            'uang_transport' => 0,
            'alpha' => 0,
            'cuti' => 0,
            'izin_resmi' => 0,
            'izin' => 0,
            'hadir' => 0,
            'telat' => 0,
            'sakit' => 0,
            'hc' => 0,
            'indisipliner' => 0,
            'jumlah_penerimaan' => 0,
        ];

        foreach ($closings as $closing) {
            $total['makan_kerja'] += $closing->intMakanKerja ?? 0;
            $total['makan_libur'] += $closing->intMakanLibur ?? 0;
            $total['uang_makan'] += $closing->decUangMakan ?? 0;
            $total['transport_kerja'] += $closing->intTransportKerja ?? 0;
            $total['transport_libur'] += $closing->intTransportLibur ?? 0;
            $total['uang_transport'] += $closing->decTransport ?? 0;
            $total['alpha'] += $closing->intJmlAlpha ?? 0;
            $total['cuti'] += $closing->intJmlCuti ?? 0;
            $total['izin_resmi'] += $closing->intJmlIzinR ?? 0;
            $total['izin'] += $closing->intJmlIzin ?? 0;
            $total['hadir'] += $closing->intHadir ?? 0;
            $total['telat'] += $closing->intJmlTelat ?? 0;
            $total['sakit'] += $closing->intJmlSakit ?? 0;
            $total['hc'] += $closing->intHC ?? 0;
            $total['indisipliner'] += 0; // Field belum ada di t_closing
            $total['jumlah_penerimaan'] += ($closing->decUangMakan ?? 0) + ($closing->decTransport ?? 0);
        }

        return $total;
    }

    /**
     * Hitung grand total dari semua closing
     */
    private function calculateGrandTotal($closings)
    {
        $total = [
            'makan_kerja' => 0,
            'makan_libur' => 0,
            'uang_makan' => 0,
            'transport_kerja' => 0,
            'transport_libur' => 0,
            'uang_transport' => 0,
            'alpha' => 0,
            'cuti' => 0,
            'izin_resmi' => 0,
            'izin' => 0,
            'hadir' => 0,
            'telat' => 0,
            'sakit' => 0,
            'hc' => 0,
            'indisipliner' => 0,
            'jumlah_penerimaan' => 0,
        ];

        foreach ($closings as $closing) {
            $total['makan_kerja'] += $closing->intMakanKerja ?? 0;
            $total['makan_libur'] += $closing->intMakanLibur ?? 0;
            $total['uang_makan'] += $closing->decUangMakan ?? 0;
            $total['transport_kerja'] += $closing->intTransportKerja ?? 0;
            $total['transport_libur'] += $closing->intTransportLibur ?? 0;
            $total['uang_transport'] += $closing->decTransport ?? 0;
            $total['alpha'] += $closing->intJmlAlpha ?? 0;
            $total['cuti'] += $closing->intJmlCuti ?? 0;
            $total['izin_resmi'] += $closing->intJmlIzinR ?? 0;
            $total['izin'] += $closing->intJmlIzin ?? 0;
            $total['hadir'] += $closing->intHadir ?? 0;
            $total['telat'] += $closing->intJmlTelat ?? 0;
            $total['sakit'] += $closing->intJmlSakit ?? 0;
            $total['hc'] += $closing->intHC ?? 0;
            $total['indisipliner'] += 0; // Field belum ada di t_closing
            $total['jumlah_penerimaan'] += ($closing->decUangMakan ?? 0) + ($closing->decTransport ?? 0);
        }

        return $total;
    }
}
