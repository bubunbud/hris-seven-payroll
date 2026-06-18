<?php

namespace App\Http\Controllers;

use App\Models\ClosingThr;
use App\Models\Karyawan;
use App\Models\Divisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ListThrController extends Controller
{
    /**
     * Display list of THR data with filters
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $tahun = $request->get('tahun');
        $agama = $request->get('agama');
        $divisi = $request->get('divisi');
        $groupPegawai = $request->get('group_pegawai', 'Semua Group');
        $search = $request->get('search'); // NIK / Nama

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

        // Get group pegawai list
        $groups = ClosingThr::select('vcGroupPegawai')
            ->distinct()
            ->orderBy('vcGroupPegawai')
            ->pluck('vcGroupPegawai')
            ->filter()
            ->toArray();

        // Load karyawan aktif untuk autocomplete
        $karyawans = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->with(['divisi'])
            ->orderBy('Nama')
            ->get(['Nik', 'Nama', 'Divisi']);

        $karyawanList = $karyawans->map(function ($k) {
            $divisiNama = '-';
            if ($k->divisi && isset($k->divisi->vcNamaDivisi)) {
                $divisiNama = $k->divisi->vcNamaDivisi;
            } elseif ($k->Divisi) {
                $divisiNama = $k->Divisi;
            }

            return [
                'nik' => $k->Nik ?: '',
                'nama' => $k->Nama ?: '',
                'divisi' => $divisiNama,
                'search' => strtolower(($k->Nik ?: '') . ' ' . ($k->Nama ?: '')),
            ];
        })->values();

        // Build query dengan Eloquent untuk bisa load relationships
        $query = ClosingThr::query();

        // Filter tahun
        if ($tahun) {
            $query->whereYear('dtTanggalTHR', $tahun);
        }

        // Filter agama
        if ($agama && $agama !== 'Semua Agama') {
            $query->where('vcAgama', $agama);
        }

        // Filter divisi
        if ($divisi && $divisi !== 'SEMUA') {
            $query->where('vcKodeDivisi', $divisi);
        }

        // Filter group pegawai
        if ($groupPegawai && $groupPegawai !== 'Semua Group') {
            $query->where('vcGroupPegawai', $groupPegawai);
        }

        // Filter NIK/Nama dengan whereHas
        if ($search) {
            // Split by comma untuk multi pencarian
            $searchTerms = preg_split('/,\s*/', trim($search));

            $query->whereHas('karyawan', function ($q) use ($searchTerms) {
                $q->where(function ($subQ) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        if (!empty(trim($term))) {
                            $term = trim($term);
                            // Jika format "NIK - Nama", ambil NIK saja
                            if (strpos($term, ' - ') !== false) {
                                $term = explode(' - ', $term)[0];
                            }
                            $subQ->orWhere('m_karyawan.Nik', 'like', '%' . $term . '%')
                                ->orWhere('m_karyawan.Nama', 'like', '%' . $term . '%');
                        }
                    }
                });
            });
        }

        // Get results dengan eager loading
        $thrData = $query->with(['karyawan', 'divisi', 'gapok'])
            ->orderBy('dtTanggalTHR', 'desc')
            ->orderBy('vcKodeDivisi', 'asc')
            ->orderBy('vcGroupPegawai', 'asc')
            ->orderBy('vcNik', 'asc')
            ->get();

        $totalData = $thrData->count();

        // Default tahun jika belum dipilih
        if (!$tahun && count($years) > 0) {
            $tahun = $years[0]; // Tahun terbaru
        }

        return view('proses.list-thr.index', compact(
            'thrData',
            'totalData',
            'years',
            'agamas',
            'divisis',
            'groups',
            'karyawanList',
            'tahun',
            'agama',
            'divisi',
            'groupPegawai',
            'search'
        ));
    }
}
