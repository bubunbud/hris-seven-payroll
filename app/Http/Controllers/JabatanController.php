<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JabatanController extends Controller
{
    public function index()
    {
        $jabatans = Jabatan::orderBy('vcKodeJabatan')->get();
        return view('master.jabatan.index', compact('jabatans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vcKodeJabatan' => 'required|string|max:7|unique:m_jabatan,vcKodeJabatan',
            'vcNamaJabatan' => 'required|string|max:50',
            'vcGrade' => 'nullable|string|max:10|regex:/^[A-Za-z0-9\s\-_]+$/'
        ], [
            'vcKodeJabatan.required' => 'Kode Jabatan harus diisi',
            'vcKodeJabatan.max' => 'Kode Jabatan maksimal 7 karakter',
            'vcKodeJabatan.unique' => 'Kode Jabatan sudah digunakan',
            'vcNamaJabatan.required' => 'Nama Jabatan harus diisi',
            'vcNamaJabatan.max' => 'Nama Jabatan maksimal 50 karakter',
            'vcGrade.max' => 'Grade maksimal 10 karakter',
            'vcGrade.regex' => 'Grade hanya boleh berisi huruf, angka, spasi, tanda hubung, dan underscore'
        ]);

        try {
            Jabatan::create([
                'vcKodeJabatan' => strtoupper($request->vcKodeJabatan),
                'vcNamaJabatan' => $request->vcNamaJabatan,
                'vcGrade' => $request->vcGrade,
                'dtCreate' => now(),
                'dtChange' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data jabatan berhasil disimpan'
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
        $jabatan = Jabatan::findOrFail($id);
        return response()->json([
            'success' => true,
            'jabatan' => $jabatan
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'vcKodeJabatan' => 'required|string|max:7|unique:m_jabatan,vcKodeJabatan,' . $id . ',vcKodeJabatan',
            'vcNamaJabatan' => 'required|string|max:50',
            'vcGrade' => 'nullable|string|max:10|regex:/^[A-Za-z0-9\s\-_]+$/'
        ], [
            'vcKodeJabatan.required' => 'Kode Jabatan harus diisi',
            'vcKodeJabatan.max' => 'Kode Jabatan maksimal 7 karakter',
            'vcKodeJabatan.unique' => 'Kode Jabatan sudah digunakan',
            'vcNamaJabatan.required' => 'Nama Jabatan harus diisi',
            'vcNamaJabatan.max' => 'Nama Jabatan maksimal 50 karakter',
            'vcGrade.max' => 'Grade maksimal 10 karakter',
            'vcGrade.regex' => 'Grade hanya boleh berisi huruf, angka, spasi, tanda hubung, dan underscore'
        ]);

        try {
            $jabatan = Jabatan::findOrFail($id);
            $jabatan->update([
                'vcKodeJabatan' => strtoupper($request->vcKodeJabatan),
                'vcNamaJabatan' => $request->vcNamaJabatan,
                'vcGrade' => $request->vcGrade,
                'dtChange' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data jabatan berhasil diperbarui'
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
            $jabatan = Jabatan::findOrFail($id);

            // Cek apakah jabatan digunakan di tabel karyawan
            $usedInKaryawan = DB::table('m_karyawan')->where('vcJabatan', $id)->exists();

            if ($usedInKaryawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jabatan tidak dapat dihapus karena masih digunakan dalam data karyawan'
                ], 400);
            }

            $jabatan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data jabatan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
