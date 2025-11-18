<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Divisi;

class DivisiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $divisis = Divisi::orderBy('vcKodeDivisi')->get();
        return view('master.divisi.index', compact('divisis'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vcKodeDivisi' => 'required|string|max:20|unique:m_divisi,vcKodeDivisi',
            'vcNamaDivisi' => 'required|string|max:100',
            'vcKeterangan' => 'nullable|string'
        ]);

        $data = $request->all();

        // Set default values for hari kerja if not provided
        $hariKerja = ['vcSenin', 'vcSelasa', 'vcRabu', 'vcKamis', 'vcJumat', 'vcSabtu', 'vcMinggu'];
        foreach ($hariKerja as $hari) {
            if (!isset($data[$hari])) {
                $data[$hari] = 0;
            }
        }

        $data['dtCreate'] = now();
        $data['dtChange'] = now();

        Divisi::create($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Divisi berhasil ditambahkan.']);
        }

        return redirect()->route('divisi.index')
            ->with('success', 'Divisi berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $divisi = Divisi::findOrFail($id);
        return response()->json($divisi);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'vcNamaDivisi' => 'required|string|max:100',
            'vcKeterangan' => 'nullable|string'
        ]);

        $divisi = Divisi::findOrFail($id);
        $data = $request->all();

        // Set default values for hari kerja if not provided
        $hariKerja = ['vcSenin', 'vcSelasa', 'vcRabu', 'vcKamis', 'vcJumat', 'vcSabtu', 'vcMinggu'];
        foreach ($hariKerja as $hari) {
            if (!isset($data[$hari])) {
                $data[$hari] = 0;
            }
        }

        $data['dtChange'] = now();

        $divisi->update($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Divisi berhasil diperbarui.']);
        }

        return redirect()->route('divisi.index')
            ->with('success', 'Divisi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $divisi = Divisi::findOrFail($id);
        $divisi->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Divisi berhasil dihapus.']);
        }

        return redirect()->route('divisi.index')
            ->with('success', 'Divisi berhasil dihapus.');
    }
}
