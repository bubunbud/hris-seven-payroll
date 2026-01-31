<?php

namespace App\Http\Controllers;

use App\Models\JenisIzin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JenisIzinController extends Controller
{
    public function index()
    {
        $jenisIzins = JenisIzin::orderBy('vcKodeIzin')->get();
        return view('master.jenis_izin.index', compact('jenisIzins'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vcKodeIzin' => 'required|string|max:5|unique:m_jenis_izin,vcKodeIzin',
            'vcKeterangan' => 'required|string|max:25'
        ], [
            'vcKodeIzin.required' => 'Kode Izin harus diisi',
            'vcKodeIzin.max' => 'Kode Izin maksimal 5 karakter',
            'vcKodeIzin.unique' => 'Kode Izin sudah digunakan',
            'vcKeterangan.required' => 'Keterangan harus diisi',
            'vcKeterangan.max' => 'Keterangan maksimal 25 karakter'
        ]);

        try {
            JenisIzin::create([
                'vcKodeIzin' => strtoupper($request->vcKodeIzin),
                'vcKeterangan' => $request->vcKeterangan,
                'dtCreate' => now(),
                'dtChange' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data jenis izin berhasil disimpan'
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
        $jenisIzin = JenisIzin::findOrFail($id);
        return response()->json([
            'success' => true,
            'jenisIzin' => $jenisIzin
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'vcKodeIzin' => 'required|string|max:5|unique:m_jenis_izin,vcKodeIzin,' . $id . ',vcKodeIzin',
            'vcKeterangan' => 'required|string|max:25'
        ], [
            'vcKodeIzin.required' => 'Kode Izin harus diisi',
            'vcKodeIzin.max' => 'Kode Izin maksimal 5 karakter',
            'vcKodeIzin.unique' => 'Kode Izin sudah digunakan',
            'vcKeterangan.required' => 'Keterangan harus diisi',
            'vcKeterangan.max' => 'Keterangan maksimal 25 karakter'
        ]);

        try {
            $jenisIzin = JenisIzin::findOrFail($id);
            $jenisIzin->update([
                'vcKodeIzin' => strtoupper($request->vcKodeIzin),
                'vcKeterangan' => $request->vcKeterangan,
                'dtChange' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data jenis izin berhasil diperbarui'
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
            $jenisIzin = JenisIzin::findOrFail($id);

            // Cek apakah jenis izin digunakan di tabel lain
            $usedInAbsen = DB::table('t_absen')->where('vcKodeIzin', $id)->exists();

            if ($usedInAbsen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis izin tidak dapat dihapus karena masih digunakan dalam data absensi'
                ], 400);
            }

            $jenisIzin->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data jenis izin berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
