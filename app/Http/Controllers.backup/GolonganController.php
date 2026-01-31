<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Golongan;

class GolonganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $golongans = Golongan::orderBy('vcKodeGolongan')->get();
        return view('master.golongan.index', compact('golongans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vcKodeGolongan' => 'required|string|max:10|unique:m_golongan,vcKodeGolongan',
            'vcNamaGolongan' => 'required|string|max:25',
        ]);

        $data = $request->all();
        $data['dtCreate'] = now();
        $data['dtChange'] = now();

        Golongan::create($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Golongan berhasil ditambahkan.']);
        }

        return redirect()->route('golongan.index')
            ->with('success', 'Golongan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $golongan = Golongan::findOrFail($id);

        return response()->json(['success' => true, 'golongan' => $golongan]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'vcKodeGolongan' => 'required|string|max:10|unique:m_golongan,vcKodeGolongan,' . $id . ',vcKodeGolongan',
            'vcNamaGolongan' => 'required|string|max:25',
        ]);

        $golongan = Golongan::findOrFail($id);
        $data = $request->all();
        $data['dtChange'] = now();

        $golongan->update($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Golongan berhasil diperbarui.']);
        }

        return redirect()->route('golongan.index')
            ->with('success', 'Golongan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $golongan = Golongan::findOrFail($id);
        $golongan->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Golongan berhasil dihapus.']);
        }

        return redirect()->route('golongan.index')
            ->with('success', 'Golongan berhasil dihapus.');
    }

    /**
     * Get golongan data for AJAX
     */
    public function getGolongan(string $id)
    {
        $golongan = Golongan::findOrFail($id);
        return response()->json($golongan);
    }
}
