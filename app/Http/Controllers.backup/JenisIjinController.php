<?php

namespace App\Http\Controllers;

use App\Models\JenisIjin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JenisIjinController extends Controller
{
    public function index()
    {
        $jenisIjins = JenisIjin::orderBy('vcKodeAbsen')->get();
        return view('master.jenis_ijin.index', compact('jenisIjins'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vcKodeAbsen' => 'required|string|max:5|unique:m_jenis_absen,vcKodeAbsen',
            'vcKeterangan' => 'required|string|max:25'
        ], [
            'vcKodeAbsen.required' => 'Kode Absen harus diisi',
            'vcKodeAbsen.max' => 'Kode Absen maksimal 5 karakter',
            'vcKodeAbsen.unique' => 'Kode Absen sudah digunakan',
            'vcKeterangan.required' => 'Keterangan harus diisi',
            'vcKeterangan.max' => 'Keterangan maksimal 25 karakter'
        ]);

        try {
            JenisIjin::create([
                'vcKodeAbsen' => strtoupper($request->vcKodeAbsen),
                'vcKeterangan' => $request->vcKeterangan,
                'dtCreate' => now(),
                'dtChange' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data jenis ijin berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id)
    {
        $jenisIjin = JenisIjin::findOrFail($id);
        return response()->json([
            'success' => true,
            'jenisIjin' => $jenisIjin
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'vcKodeAbsen' => 'required|string|max:5|unique:m_jenis_absen,vcKodeAbsen,' . $id . ',vcKodeAbsen',
            'vcKeterangan' => 'required|string|max:25'
        ], [
            'vcKodeAbsen.required' => 'Kode Absen harus diisi',
            'vcKodeAbsen.max' => 'Kode Absen maksimal 5 karakter',
            'vcKodeAbsen.unique' => 'Kode Absen sudah digunakan',
            'vcKeterangan.required' => 'Keterangan harus diisi',
            'vcKeterangan.max' => 'Keterangan maksimal 25 karakter'
        ]);

        try {
            $jenisIjin = JenisIjin::findOrFail($id);
            $jenisIjin->update([
                'vcKodeAbsen' => strtoupper($request->vcKodeAbsen),
                'vcKeterangan' => $request->vcKeterangan,
                'dtChange' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data jenis ijin berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $jenisIjin = JenisIjin::findOrFail($id);

            // Cek apakah jenis ijin digunakan di tabel lain
            $usedInAbsen = DB::table('t_absen')->where('vcKodeAbsen', $id)->exists();

            if ($usedInAbsen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis ijin tidak dapat dihapus karena masih digunakan dalam data absensi'
                ], 400);
            }

            $jenisIjin->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data jenis ijin berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
