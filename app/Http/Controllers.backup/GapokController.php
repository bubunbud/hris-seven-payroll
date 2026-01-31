<?php

namespace App\Http\Controllers;

use App\Models\Gapok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GapokController extends Controller
{
    public function index()
    {
        $gapoks = Gapok::orderBy('vcKodeGolongan')->get();
        return view('master.gapok.index', compact('gapoks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vcKodeGolongan' => 'required|string|max:10|unique:m_gapok,vcKodeGolongan',
            'upah' => 'required|numeric|min:0',
            'tunj_keluarga' => 'nullable|numeric|min:0',
            'tunj_masa_kerja' => 'nullable|numeric|min:0',
            'tunj_jabatan1' => 'nullable|numeric|min:0',
            'tunj_jabatan2' => 'nullable|numeric|min:0',
            'uang_makan' => 'nullable|numeric|min:0',
            'uang_transport' => 'nullable|numeric|min:0',
            'premi' => 'nullable|numeric|min:0',
            'vcKeterangan' => 'nullable|string|max:50',
        ], [
            'vcKodeGolongan.required' => 'Kode Golongan harus diisi',
            'vcKodeGolongan.max' => 'Kode Golongan maksimal 10 karakter',
            'vcKodeGolongan.unique' => 'Kode Golongan sudah digunakan',
            'upah.required' => 'Upah harus diisi',
            'upah.numeric' => 'Upah harus berupa angka',
            'upah.min' => 'Upah tidak boleh kurang dari 0',
            'tunj_keluarga.numeric' => 'Tunjangan Keluarga harus berupa angka',
            'tunj_keluarga.min' => 'Tunjangan Keluarga tidak boleh kurang dari 0',
            'tunj_masa_kerja.numeric' => 'Tunjangan Masa Kerja harus berupa angka',
            'tunj_masa_kerja.min' => 'Tunjangan Masa Kerja tidak boleh kurang dari 0',
            'tunj_jabatan1.numeric' => 'Tunjangan Jabatan 1 harus berupa angka',
            'tunj_jabatan1.min' => 'Tunjangan Jabatan 1 tidak boleh kurang dari 0',
            'tunj_jabatan2.numeric' => 'Tunjangan Jabatan 2 harus berupa angka',
            'tunj_jabatan2.min' => 'Tunjangan Jabatan 2 tidak boleh kurang dari 0',
            'uang_makan.numeric' => 'Uang Makan harus berupa angka',
            'uang_makan.min' => 'Uang Makan tidak boleh kurang dari 0',
            'uang_transport.numeric' => 'Uang Transport harus berupa angka',
            'uang_transport.min' => 'Uang Transport tidak boleh kurang dari 0',
            'premi.numeric' => 'Premi harus berupa angka',
            'premi.min' => 'Premi tidak boleh kurang dari 0',
            'vcKeterangan.max' => 'Keterangan maksimal 50 karakter',
        ]);

        try {
            Gapok::create([
                'vcKodeGolongan' => strtoupper($request->vcKodeGolongan),
                'upah' => $request->upah,
                'tunj_keluarga' => $request->tunj_keluarga ?? 0,
                'tunj_masa_kerja' => $request->tunj_masa_kerja ?? 0,
                'tunj_jabatan1' => $request->tunj_jabatan1 ?? 0,
                'tunj_jabatan2' => $request->tunj_jabatan2 ?? 0,
                'uang_makan' => $request->uang_makan ?? 0,
                'uang_transport' => $request->uang_transport ?? 0,
                'premi' => $request->premi ?? 0,
                'vcKeterangan' => $request->vcKeterangan,
                'dtCreate' => Carbon::now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Gaji Pokok berhasil ditambahkan.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        $gapok = Gapok::findOrFail($id);
        return response()->json(['success' => true, 'gapok' => $gapok]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'vcKodeGolongan' => 'required|string|max:10|unique:m_gapok,vcKodeGolongan,' . $id . ',vcKodeGolongan',
            'upah' => 'required|numeric|min:0',
            'tunj_keluarga' => 'nullable|numeric|min:0',
            'tunj_masa_kerja' => 'nullable|numeric|min:0',
            'tunj_jabatan1' => 'nullable|numeric|min:0',
            'tunj_jabatan2' => 'nullable|numeric|min:0',
            'uang_makan' => 'nullable|numeric|min:0',
            'uang_transport' => 'nullable|numeric|min:0',
            'premi' => 'nullable|numeric|min:0',
            'vcKeterangan' => 'nullable|string|max:50',
        ], [
            'vcKodeGolongan.required' => 'Kode Golongan harus diisi',
            'vcKodeGolongan.max' => 'Kode Golongan maksimal 10 karakter',
            'vcKodeGolongan.unique' => 'Kode Golongan sudah digunakan',
            'upah.required' => 'Upah harus diisi',
            'upah.numeric' => 'Upah harus berupa angka',
            'upah.min' => 'Upah tidak boleh kurang dari 0',
            'tunj_keluarga.numeric' => 'Tunjangan Keluarga harus berupa angka',
            'tunj_keluarga.min' => 'Tunjangan Keluarga tidak boleh kurang dari 0',
            'tunj_masa_kerja.numeric' => 'Tunjangan Masa Kerja harus berupa angka',
            'tunj_masa_kerja.min' => 'Tunjangan Masa Kerja tidak boleh kurang dari 0',
            'tunj_jabatan1.numeric' => 'Tunjangan Jabatan 1 harus berupa angka',
            'tunj_jabatan1.min' => 'Tunjangan Jabatan 1 tidak boleh kurang dari 0',
            'tunj_jabatan2.numeric' => 'Tunjangan Jabatan 2 harus berupa angka',
            'tunj_jabatan2.min' => 'Tunjangan Jabatan 2 tidak boleh kurang dari 0',
            'uang_makan.numeric' => 'Uang Makan harus berupa angka',
            'uang_makan.min' => 'Uang Makan tidak boleh kurang dari 0',
            'uang_transport.numeric' => 'Uang Transport harus berupa angka',
            'uang_transport.min' => 'Uang Transport tidak boleh kurang dari 0',
            'premi.numeric' => 'Premi harus berupa angka',
            'premi.min' => 'Premi tidak boleh kurang dari 0',
            'vcKeterangan.max' => 'Keterangan maksimal 50 karakter',
        ]);

        try {
            $gapok = Gapok::findOrFail($id);
            $gapok->update([
                'vcKodeGolongan' => strtoupper($request->vcKodeGolongan),
                'upah' => $request->upah,
                'tunj_keluarga' => $request->tunj_keluarga ?? 0,
                'tunj_masa_kerja' => $request->tunj_masa_kerja ?? 0,
                'tunj_jabatan1' => $request->tunj_jabatan1 ?? 0,
                'tunj_jabatan2' => $request->tunj_jabatan2 ?? 0,
                'uang_makan' => $request->uang_makan ?? 0,
                'uang_transport' => $request->uang_transport ?? 0,
                'premi' => $request->premi ?? 0,
                'vcKeterangan' => $request->vcKeterangan,
                'dtChange' => Carbon::now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Gaji Pokok berhasil diperbarui.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            // Check if the Gapok is used in m_karyawan
            $isUsed = DB::table('m_karyawan')->where('vcKodeGolongan', $id)->exists();

            if ($isUsed) {
                return response()->json(['success' => false, 'message' => 'Gaji Pokok tidak dapat dihapus karena sudah digunakan oleh karyawan.'], 400);
            }

            $gapok = Gapok::findOrFail($id);
            $gapok->delete();
            return response()->json(['success' => true, 'message' => 'Gaji Pokok berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
