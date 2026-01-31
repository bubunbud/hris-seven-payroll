<?php

namespace App\Http\Controllers;

use App\Models\OverrideJadwalSecurity;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OverrideJadwalSecurityController extends Controller
{
    /**
     * Display list of override jadwal security
     */
    public function index(Request $request)
    {
        $query = OverrideJadwalSecurity::with('karyawan')
            ->orderBy('dtOverrideAt', 'desc');

        // Filter by date range
        $dariTanggal = $request->get('dari_tanggal');
        $sampaiTanggal = $request->get('sampai_tanggal');

        if ($dariTanggal) {
            $query->where('dtTanggal', '>=', $dariTanggal);
        }

        if ($sampaiTanggal) {
            $query->where('dtTanggal', '<=', $sampaiTanggal);
        }

        // Filter by NIK
        $nik = $request->get('nik');
        if ($nik) {
            $query->where('vcNik', 'LIKE', '%' . $nik . '%');
        }

        // Filter by nama
        $nama = $request->get('nama');
        if ($nama) {
            $query->whereHas('karyawan', function ($q) use ($nama) {
                $q->where('Nama', 'LIKE', '%' . $nama . '%');
            });
        }

        // Default date range: last 30 days
        if (!$dariTanggal && !$sampaiTanggal) {
            $dariTanggal = Carbon::now()->subDays(30)->format('Y-m-d');
            $sampaiTanggal = Carbon::now()->format('Y-m-d');
            $query->whereBetween('dtTanggal', [$dariTanggal, $sampaiTanggal]);
        }

        $overrides = $query->paginate(20)->appends($request->query());

        // Get shift names for display
        $shiftNames = [
            1 => 'Shift 1 (06:30-14:30)',
            2 => 'Shift 2 (14:30-22:30)',
            3 => 'Shift 3 (22:30-06:30)',
        ];

        return view('override-jadwal-security.index', compact(
            'overrides',
            'shiftNames',
            'dariTanggal',
            'sampaiTanggal',
            'nik',
            'nama'
        ));
    }

    /**
     * Show detail of override
     */
    public function show($id)
    {
        $override = OverrideJadwalSecurity::with('karyawan')->findOrFail($id);

        $shiftNames = [
            1 => 'Shift 1 (06:30-14:30)',
            2 => 'Shift 2 (14:30-22:30)',
            3 => 'Shift 3 (22:30-06:30)',
        ];

        return view('override-jadwal-security.show', compact('override', 'shiftNames'));
    }
}
