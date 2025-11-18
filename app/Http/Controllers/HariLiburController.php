<?php

namespace App\Http\Controllers;

use App\Models\HariLibur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HariLiburController extends Controller
{
    public function index()
    {
        $hariLiburs = HariLibur::orderBy('dtTanggal', 'desc')->get();
        return view('master.hari_libur.index', compact('hariLiburs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'dtTanggal' => 'required|date|unique:m_hari_libur,dtTanggal',
            'vcKeterangan' => 'required|string|max:35',
            'vcTipeHariLibur' => 'required|string|max:20|in:Libur Nasional,Cuti Bersama',
        ], [
            'dtTanggal.required' => 'Tanggal harus diisi',
            'dtTanggal.date' => 'Format tanggal tidak valid',
            'dtTanggal.unique' => 'Tanggal sudah ada dalam data hari libur',
            'vcKeterangan.required' => 'Keterangan harus diisi',
            'vcKeterangan.max' => 'Keterangan maksimal 35 karakter',
            'vcTipeHariLibur.required' => 'Tipe Hari Libur harus diisi',
            'vcTipeHariLibur.max' => 'Tipe Hari Libur maksimal 20 karakter',
            'vcTipeHariLibur.in' => 'Tipe Hari Libur harus Libur Nasional atau Cuti Bersama'
        ]);

        try {
            HariLibur::create([
                'dtTanggal' => $request->dtTanggal,
                'vcKeterangan' => $request->vcKeterangan,
                'vcTipeHariLibur' => $request->vcTipeHariLibur,
                'dtCreate' => Carbon::now()->toDateString(),
            ]);

            return response()->json(['success' => true, 'message' => 'Hari Libur berhasil ditambahkan.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        $hariLibur = HariLibur::findOrFail($id);
        // Ensure date is in YYYY-MM-DD format for JavaScript
        $hariLibur->dtTanggal = $hariLibur->dtTanggal ? $hariLibur->dtTanggal->format('Y-m-d') : null;
        return response()->json(['success' => true, 'hariLibur' => $hariLibur]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'dtTanggal' => 'required|date|unique:m_hari_libur,dtTanggal,' . $id . ',dtTanggal',
            'vcKeterangan' => 'required|string|max:35',
            'vcTipeHariLibur' => 'required|string|max:20|in:Libur Nasional,Cuti Bersama',
        ], [
            'dtTanggal.required' => 'Tanggal harus diisi',
            'dtTanggal.date' => 'Format tanggal tidak valid',
            'dtTanggal.unique' => 'Tanggal sudah ada dalam data hari libur',
            'vcKeterangan.required' => 'Keterangan harus diisi',
            'vcKeterangan.max' => 'Keterangan maksimal 35 karakter',
            'vcTipeHariLibur.required' => 'Tipe Hari Libur harus diisi',
            'vcTipeHariLibur.max' => 'Tipe Hari Libur maksimal 20 karakter',
            'vcTipeHariLibur.in' => 'Tipe Hari Libur harus Libur Nasional atau Cuti Bersama'
        ]);

        try {
            $hariLibur = HariLibur::findOrFail($id);
            $hariLibur->update([
                'dtTanggal' => $request->dtTanggal,
                'vcKeterangan' => $request->vcKeterangan,
                'vcTipeHariLibur' => $request->vcTipeHariLibur,
                'dtChange' => Carbon::now()->toDateString(),
            ]);

            return response()->json(['success' => true, 'message' => 'Hari Libur berhasil diperbarui.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $hariLibur = HariLibur::findOrFail($id);
            $hariLibur->delete();
            return response()->json(['success' => true, 'message' => 'Hari Libur berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
