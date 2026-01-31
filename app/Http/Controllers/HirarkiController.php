<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\Departemen;
use App\Models\Bagian;
use App\Models\Seksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HirarkiController extends Controller
{
    /**
     * Display the hirarki management page
     */
    public function index(Request $request)
    {
        $divisis = Divisi::orderBy('vcKodeDivisi')->get();
        $departemens = Departemen::orderBy('vcKodeDept')->get();
        $bagians = Bagian::orderBy('vcKodeBagian')->get();
        $seksis = Seksi::orderBy('vcKodeseksi')->get();

        // Get selected divisi and departemen from request
        $selectedDivisi = $request->get('divisi');
        $selectedDept = $request->get('departemen');
        $selectedBagian = $request->get('bagian');
        $selectedSeksi = $request->get('seksi');

        // Get departemens in selected divisi for bagian tab
        $deptsInDivisi = collect();
        if ($selectedDivisi) {
            $deptsInDivisi = DB::table('m_hirarki_dept')
                ->join('m_dept', 'm_hirarki_dept.vcKodeDept', '=', 'm_dept.vcKodeDept')
                ->where('m_hirarki_dept.vcKodeDivisi', $selectedDivisi)
                ->select('m_dept.*')
                ->orderBy('m_dept.vcKodeDept')
                ->get();
        }

        // Get bagians in selected divisi and departemen for seksi tab
        $bagiansInDivisiDept = collect();
        if ($selectedDivisi && $selectedDept) {
            $bagiansInDivisiDept = DB::table('m_hirarki_bagian')
                ->join('m_bagian', 'm_hirarki_bagian.vcKodeBagian', '=', 'm_bagian.vcKodeBagian')
                ->where('m_hirarki_bagian.vcKodeDivisi', $selectedDivisi)
                ->where('m_hirarki_bagian.vcKodeDept', $selectedDept)
                ->select('m_bagian.*')
                ->orderBy('m_bagian.vcKodeBagian')
                ->get();
        }

        // Get hirarki departemen data
        $hirarkiDept = [];
        if ($selectedDivisi) {
            $hirarkiDept = DB::table('m_hirarki_dept')
                ->join('m_dept', 'm_hirarki_dept.vcKodeDept', '=', 'm_dept.vcKodeDept')
                ->where('m_hirarki_dept.vcKodeDivisi', $selectedDivisi)
                ->select('m_hirarki_dept.*', 'm_dept.vcNamaDept')
                ->orderBy('m_dept.vcKodeDept')
                ->get();
        }

        // Get hirarki bagian data
        $hirarkiBagian = [];
        if ($selectedDivisi && $selectedDept) {
            $hirarkiBagian = DB::table('m_hirarki_bagian')
                ->join('m_bagian', 'm_hirarki_bagian.vcKodeBagian', '=', 'm_bagian.vcKodeBagian')
                ->where('m_hirarki_bagian.vcKodeDivisi', $selectedDivisi)
                ->where('m_hirarki_bagian.vcKodeDept', $selectedDept)
                ->select('m_hirarki_bagian.*', 'm_bagian.vcNamaBagian')
                ->orderBy('m_bagian.vcKodeBagian')
                ->get();
        } else {
            // If only divisi selected, show all bagian in that divisi
            if ($selectedDivisi) {
                $hirarkiBagian = DB::table('m_hirarki_bagian')
                    ->join('m_bagian', 'm_hirarki_bagian.vcKodeBagian', '=', 'm_bagian.vcKodeBagian')
                    ->where('m_hirarki_bagian.vcKodeDivisi', $selectedDivisi)
                    ->select('m_hirarki_bagian.*', 'm_bagian.vcNamaBagian')
                    ->orderBy('m_hirarki_bagian.vcKodeDept')
                    ->orderBy('m_bagian.vcKodeBagian')
                    ->get();
            }
        }

        // Get hirarki seksi data
        $hirarkiSeksi = [];
        if ($selectedDivisi && $selectedDept && $selectedBagian) {
            $hirarkiSeksi = DB::table('m_hirarki_seksi')
                ->join('m_seksi', 'm_hirarki_seksi.vcKodeSeksi', '=', 'm_seksi.vcKodeseksi')
                ->where('m_hirarki_seksi.vcKodeDivisi', $selectedDivisi)
                ->where('m_hirarki_seksi.vcKodeDept', $selectedDept)
                ->where('m_hirarki_seksi.vcKodeBagian', $selectedBagian)
                ->select('m_hirarki_seksi.*', 'm_seksi.vcNamaseksi as vcNamaSeksi')
                ->orderBy('m_seksi.vcKodeseksi')
                ->get();
        } else {
            // If only divisi and dept selected, show all seksi in that divisi and dept
            if ($selectedDivisi && $selectedDept) {
                $hirarkiSeksi = DB::table('m_hirarki_seksi')
                    ->join('m_seksi', 'm_hirarki_seksi.vcKodeSeksi', '=', 'm_seksi.vcKodeseksi')
                    ->where('m_hirarki_seksi.vcKodeDivisi', $selectedDivisi)
                    ->where('m_hirarki_seksi.vcKodeDept', $selectedDept)
                    ->select('m_hirarki_seksi.*', 'm_seksi.vcNamaseksi as vcNamaSeksi')
                    ->orderBy('m_hirarki_seksi.vcKodeBagian')
                    ->orderBy('m_seksi.vcKodeseksi')
                    ->get();
            }
        }

        return view('master.hirarki.index', compact(
            'divisis',
            'departemens',
            'bagians',
            'seksis',
            'selectedDivisi',
            'selectedDept',
            'selectedBagian',
            'selectedSeksi',
            'hirarkiDept',
            'hirarkiBagian',
            'hirarkiSeksi',
            'deptsInDivisi',
            'bagiansInDivisiDept'
        ));
    }

    /**
     * Store hirarki departemen (link divisi with departemen)
     */
    public function storeDept(Request $request)
    {
        try {
            $request->validate([
                'vcKodeDivisi' => 'required|string',
                'vcKodeDept' => 'required|string',
            ]);

            // Check if already exists
            $exists = DB::table('m_hirarki_dept')
                ->where('vcKodeDivisi', $request->vcKodeDivisi)
                ->where('vcKodeDept', $request->vcKodeDept)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Relasi Divisi dengan Departemen ini sudah ada!'
                ], 422);
            }

            DB::table('m_hirarki_dept')->insert([
                'vcKodeDivisi' => $request->vcKodeDivisi,
                'vcKodeDept' => $request->vcKodeDept,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Relasi Divisi dengan Departemen berhasil ditambahkan!'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = [];
            foreach ($errors as $field => $messages) {
                $errorMessages = array_merge($errorMessages, $messages);
            }
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $errorMessages)
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error storing hirarki dept: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete hirarki departemen
     */
    public function destroyDept(Request $request)
    {
        $request->validate([
            'vcKodeDivisi' => 'required|string',
            'vcKodeDept' => 'required|string',
        ]);

        DB::table('m_hirarki_dept')
            ->where('vcKodeDivisi', $request->vcKodeDivisi)
            ->where('vcKodeDept', $request->vcKodeDept)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Relasi Divisi dengan Departemen berhasil dihapus!'
        ]);
    }

    /**
     * Store hirarki bagian (link divisi, departemen with bagian)
     */
    public function storeBagian(Request $request)
    {
        try {
            $request->validate([
                'vcKodeDivisi' => 'required|string',
                'vcKodeDept' => 'required|string',
                'vcKodeBagian' => 'required|string',
            ]);

            // Check if already exists
            $exists = DB::table('m_hirarki_bagian')
                ->where('vcKodeDivisi', $request->vcKodeDivisi)
                ->where('vcKodeDept', $request->vcKodeDept)
                ->where('vcKodeBagian', $request->vcKodeBagian)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Relasi Divisi, Departemen dengan Bagian ini sudah ada!'
                ], 422);
            }

            DB::table('m_hirarki_bagian')->insert([
                'vcKodeDivisi' => $request->vcKodeDivisi,
                'vcKodeDept' => $request->vcKodeDept,
                'vcKodeBagian' => $request->vcKodeBagian,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Relasi Divisi, Departemen dengan Bagian berhasil ditambahkan!'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = [];
            foreach ($errors as $field => $messages) {
                $errorMessages = array_merge($errorMessages, $messages);
            }
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $errorMessages)
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error storing hirarki bagian: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete hirarki bagian
     */
    public function destroyBagian(Request $request)
    {
        $request->validate([
            'vcKodeDivisi' => 'required|string',
            'vcKodeDept' => 'required|string',
            'vcKodeBagian' => 'required|string',
        ]);

        DB::table('m_hirarki_bagian')
            ->where('vcKodeDivisi', $request->vcKodeDivisi)
            ->where('vcKodeDept', $request->vcKodeDept)
            ->where('vcKodeBagian', $request->vcKodeBagian)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Relasi Divisi, Departemen dengan Bagian berhasil dihapus!'
        ]);
    }

    /**
     * Store hirarki seksi (link divisi, departemen, bagian with seksi)
     */
    public function storeSeksi(Request $request)
    {
        try {
            $request->validate([
                'vcKodeDivisi' => 'required|string',
                'vcKodeDept' => 'required|string',
                'vcKodeBagian' => 'required|string',
                'vcKodeSeksi' => 'required|string',
            ]);

            // Check if already exists
            $exists = DB::table('m_hirarki_seksi')
                ->where('vcKodeDivisi', $request->vcKodeDivisi)
                ->where('vcKodeDept', $request->vcKodeDept)
                ->where('vcKodeBagian', $request->vcKodeBagian)
                ->where('vcKodeSeksi', $request->vcKodeSeksi)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Relasi Divisi, Departemen, Bagian dengan Seksi ini sudah ada!'
                ], 422);
            }

            DB::table('m_hirarki_seksi')->insert([
                'vcKodeDivisi' => $request->vcKodeDivisi,
                'vcKodeDept' => $request->vcKodeDept,
                'vcKodeBagian' => $request->vcKodeBagian,
                'vcKodeSeksi' => $request->vcKodeSeksi,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Relasi Divisi, Departemen, Bagian dengan Seksi berhasil ditambahkan!'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = [];
            foreach ($errors as $field => $messages) {
                $errorMessages = array_merge($errorMessages, $messages);
            }
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $errorMessages)
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error storing hirarki seksi: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete hirarki seksi
     */
    public function destroySeksi(Request $request)
    {
        $request->validate([
            'vcKodeDivisi' => 'required|string',
            'vcKodeDept' => 'required|string',
            'vcKodeBagian' => 'required|string',
            'vcKodeSeksi' => 'required|string',
        ]);

        DB::table('m_hirarki_seksi')
            ->where('vcKodeDivisi', $request->vcKodeDivisi)
            ->where('vcKodeDept', $request->vcKodeDept)
            ->where('vcKodeBagian', $request->vcKodeBagian)
            ->where('vcKodeSeksi', $request->vcKodeSeksi)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Relasi Divisi, Departemen, Bagian dengan Seksi berhasil dihapus!'
        ]);
    }
}
