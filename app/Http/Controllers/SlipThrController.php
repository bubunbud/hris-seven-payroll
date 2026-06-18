<?php

namespace App\Http\Controllers;

use App\Models\ClosingThr;
use App\Models\Karyawan;
use App\Models\Divisi;
use App\Models\PeriodeThr;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SlipThrController extends Controller
{
    /**
     * Display form untuk cetak slip THR
     */
    public function index(Request $request)
    {
        // Get distinct years from t_closing_thr
        $years = ClosingThr::selectRaw('YEAR(dtTanggalTHR) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->map(function ($year) {
                return (string) $year;
            })
            ->toArray();

        // Get distinct agama from t_closing_thr
        $agamas = ClosingThr::select('vcAgama')
            ->distinct()
            ->orderBy('vcAgama')
            ->pluck('vcAgama')
            ->filter()
            ->toArray();

        // Get divisi list
        $divisis = Divisi::orderBy('vcKodeDivisi')->get();

        // Get active karyawan list for autocomplete (Operator dan Security)
        $karyawanList = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->whereIn('Group_pegawai', ['Operator', 'Security'])
            ->select('Nik', 'Nama', 'Divisi', 'vcKodeBagian')
            ->with(['divisi', 'bagian'])
            ->get()
            ->map(function ($karyawan) {
                return [
                    'nik' => $karyawan->Nik,
                    'nama' => $karyawan->Nama,
                    'divisi' => $karyawan->divisi->vcNamaDivisi ?? $karyawan->Divisi,
                    'bagian' => $karyawan->bagian->vcNamaBagian ?? $karyawan->vcKodeBagian,
                    'search' => $karyawan->Nik . ' - ' . $karyawan->Nama
                ];
            });

        // Default values
        $tahun = $request->get('tahun', count($years) > 0 ? $years[0] : date('Y'));
        $divisi = $request->get('divisi', 'SEMUA');
        $agama = $request->get('agama', 'Semua Agama');
        $search = $request->get('search', '');

        return view('laporan.slip-thr.index', compact(
            'years',
            'agamas',
            'divisis',
            'karyawanList',
            'tahun',
            'divisi',
            'agama',
            'search'
        ));
    }

    /**
     * Preview/Print slip THR berdasarkan filter
     */
    public function preview(Request $request)
    {
        $request->validate([
            'tahun' => 'required|string|digits:4',
            'divisi' => 'nullable|string',
            'agama' => 'nullable|string',
            'search' => 'nullable|string',
        ]);

        $tahun = $request->tahun;
        $kodeDivisi = $request->divisi;
        $agamaFilter = $request->agama;
        $search = $request->search;

        // Query closing THR untuk Operator dan Security dengan vcAktif = 1
        $query = ClosingThr::with(['karyawan.bagian', 'divisi', 'gapok'])
            ->whereYear('dtTanggalTHR', $tahun)
            ->whereIn('vcGroupPegawai', ['Operator', 'Security'])
            ->whereHas('karyawan', function ($q) {
                $q->where('vcAktif', '1')
                  ->whereNull('Tgl_Berhenti');
            });

        // Filter divisi
        if ($kodeDivisi && $kodeDivisi != 'SEMUA') {
            $query->where('vcKodeDivisi', $kodeDivisi);
        }

        // Filter agama
        if ($agamaFilter && $agamaFilter != 'Semua Agama') {
            $query->where('vcAgama', $agamaFilter);
        }

        // Filter NIK/Nama (multi-term search)
        if ($search) {
            $searchTerms = preg_split('/[\s,]+/', trim($search));
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    // Handle format "NIK - Nama"
                    if (strpos($term, '-') !== false) {
                        $parts = explode('-', $term);
                        $nikPart = trim($parts[0]);
                        if ($nikPart) {
                            $q->orWhere('vcNik', 'like', '%' . $nikPart . '%');
                        }
                        if (isset($parts[1])) {
                            $namaPart = trim($parts[1]);
                            if ($namaPart) {
                                $q->orWhereHas('karyawan', function ($subQ) use ($namaPart) {
                                    $subQ->where('Nama', 'like', '%' . $namaPart . '%');
                                });
                            }
                        }
                    } else {
                        $q->orWhere('vcNik', 'like', '%' . $term . '%')
                          ->orWhereHas('karyawan', function ($subQ) use ($term) {
                              $subQ->where('Nama', 'like', '%' . $term . '%');
                          });
                    }
                }
            });
        }

        $closingThrs = $query->orderBy('vcKodeDivisi')
            ->orderBy('vcNik')
            ->get();

        if ($closingThrs->isEmpty()) {
            return redirect()->route('slip-thr.index')
                ->with('error', 'Tidak ada data THR untuk filter yang dipilih');
        }

        // Ambil tanggal THR dari data pertama
        $tanggalTHR = $closingThrs->first()->dtTanggalTHR;

        // Ambil data divisi untuk header
        $divisiData = null;
        if ($kodeDivisi && $kodeDivisi != 'SEMUA') {
            $divisiData = Divisi::where('vcKodeDivisi', $kodeDivisi)->first();
        }

        // Ambil vcKeterangan dan vcNamaHariRaya dari t_periode_thr untuk setiap closing THR
        foreach ($closingThrs as $closingThr) {
            $dtTanggalTHR = $closingThr->dtTanggalTHR;
            
            if ($dtTanggalTHR) {
                $tanggalTHRFormatted = $dtTanggalTHR->format('Y-m-d');
                $divisiFilter = $closingThr->vcKodeDivisi;
                
                // Cari periode THR berdasarkan tanggal cutoff dan divisi
                $periodeThr = PeriodeThr::where('dtCutoffTHR', $tanggalTHRFormatted)
                    ->where('vcKodeDivisi', $divisiFilter)
                    ->first();
                
                // Jika tidak ditemukan, coba cari berdasarkan agama/kategori
                if (!$periodeThr) {
                    $kategori = $this->mapAgamaToKategori($closingThr->vcAgama ?? '');
                    $periodeThr = PeriodeThr::where('dtCutoffTHR', $tanggalTHRFormatted)
                        ->where('dtKategori', $kategori)
                        ->where('vcKodeDivisi', $divisiFilter)
                        ->first();
                }
                
                // Jika masih tidak ditemukan, ambil yang pertama dengan tanggal yang sama
                if (!$periodeThr) {
                    $periodeThr = PeriodeThr::where('dtCutoffTHR', $tanggalTHRFormatted)->first();
                }
                
                // Set vcKeterangan dan namaHariRaya ke closing THR
                if ($periodeThr) {
                    if (!empty($periodeThr->vcKeterangan)) {
                        $closingThr->vcKeterangan = trim($periodeThr->vcKeterangan);
                    } else {
                        $closingThr->vcKeterangan = null;
                    }
                    
                    if (!empty($periodeThr->vcNamaHariRaya)) {
                        $closingThr->namaHariRaya = trim($periodeThr->vcNamaHariRaya);
                    } else {
                        $closingThr->namaHariRaya = null;
                    }
                } else {
                    $closingThr->vcKeterangan = null;
                    $closingThr->namaHariRaya = null;
                }
            }
        }

        return view('laporan.slip-thr.preview', compact(
            'closingThrs',
            'tanggalTHR',
            'tahun',
            'divisiData'
        ));
    }

    /**
     * Mapping agama ke kategori THR
     */
    private function mapAgamaToKategori($agama)
    {
        $agama = trim($agama ?? '');
        
        $mapping = [
            'Islam' => 'Islam (Idul Fitri)',
            'Kristen' => 'Kristen (Natal)',
            'Hindu' => 'Hindu (Nyepi)',
            'Budha' => 'Budha (Waisak)',
        ];
        
        return $mapping[$agama] ?? 'Lainnya';
    }

}
