<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Karyawan;
use App\Models\Divisi;
use App\Models\Departemen;
use App\Models\Bagian;
use Carbon\Carbon;

class ListKaryawanAktifController extends Controller
{
    /**
     * Display a listing of active employees.
     */
    public function index(Request $request)
    {
        // Get filter options
        $divisis = Divisi::orderBy('vcKodeDivisi')->get();
        $departemens = Departemen::orderBy('vcKodeDept')->get();
        $bagians = Bagian::orderBy('vcKodeBagian')->get();
        
        // Get distinct Group Pegawai values
        $groupPegawais = Karyawan::select('Group_pegawai')
            ->whereNotNull('Group_pegawai')
            ->where('Group_pegawai', '!=', '')
            ->where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->distinct()
            ->orderBy('Group_pegawai')
            ->pluck('Group_pegawai')
            ->filter(function ($value) {
                return !empty(trim($value));
            })
            ->values()
            ->toArray();

        // Build query for active employees
        $query = Karyawan::with(['divisi', 'departemen', 'bagian', 'jabatan'])
            ->where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti');

        // Apply filters
        if ($request->filled('divisi')) {
            $query->where('Divisi', $request->divisi);
        }

        if ($request->filled('departemen')) {
            $query->where('dept', $request->departemen);
        }

        if ($request->filled('bagian')) {
            $query->where('vcKodeBagian', $request->bagian);
        }

        if ($request->filled('group_pegawai')) {
            $query->where('Group_pegawai', $request->group_pegawai);
        }

        // Get filtered employees with pagination
        $karyawans = $query->orderBy('Nama')
            ->paginate(20)
            ->withQueryString();

        return view('master.list-karyawan-aktif.index', compact(
            'karyawans',
            'divisis',
            'departemens',
            'bagians',
            'groupPegawais'
        ));
    }

    /**
     * Export data karyawan aktif to Excel (CSV format)
     */
    public function exportExcel(Request $request)
    {
        // Build query for active employees (same as index but without pagination)
        $query = Karyawan::with(['divisi', 'departemen', 'bagian', 'jabatan'])
            ->where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti');

        // Apply filters
        if ($request->filled('divisi')) {
            $query->where('Divisi', $request->divisi);
        }

        if ($request->filled('departemen')) {
            $query->where('dept', $request->departemen);
        }

        if ($request->filled('bagian')) {
            $query->where('vcKodeBagian', $request->bagian);
        }

        if ($request->filled('group_pegawai')) {
            $query->where('Group_pegawai', $request->group_pegawai);
        }

        // Get all filtered employees
        $karyawans = $query->orderBy('Nama')->get();

        // Generate filename
        $filename = 'list_karyawan_aktif_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($karyawans) {
            $file = fopen('php://output', 'w');
            
            // BOM untuk Excel UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($file, [
                'No',
                'NIK',
                'Nama',
                'Bisnis Unit',
                'Departemen',
                'Bagian',
                'Jabatan',
                'Tanggal Masuk',
                'Group Pegawai'
            ]);

            // Data rows
            $no = 1;
            foreach ($karyawans as $karyawan) {
                fputcsv($file, [
                    $no++,
                    $karyawan->Nik ?? '',
                    $karyawan->Nama ?? '',
                    $karyawan->divisi->vcNamaDivisi ?? '',
                    $karyawan->departemen->vcNamaDept ?? '',
                    $karyawan->bagian->vcNamaBagian ?? '',
                    $karyawan->jabatan->vcNamaJabatan ?? '',
                    $karyawan->Tgl_Masuk ? Carbon::parse($karyawan->Tgl_Masuk)->format('d/m/Y') : '',
                    $karyawan->Group_pegawai ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

