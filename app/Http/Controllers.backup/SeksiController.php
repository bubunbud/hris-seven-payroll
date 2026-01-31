<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seksi;

class SeksiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $seksis = Seksi::orderBy('vcKodeseksi')->get();
        return view('master.seksi.index', compact('seksis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('seksi.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('seksi.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return redirect()->route('seksi.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vcKodeSeksi' => 'required|string|max:10|unique:m_seksi,vcKodeseksi',
            'vcNamaSeksi' => 'required|string|max:50'
        ]);

        $data = [
            'vcKodeseksi' => $request->vcKodeSeksi,
            'vcNamaseksi' => $request->vcNamaSeksi,
            'dtCreate' => now(),
            'dtChange' => now()
        ];

        Seksi::create($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Seksi berhasil ditambahkan.']);
        }

        return redirect()->route('seksi.index')
            ->with('success', 'Seksi berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'vcNamaSeksi' => 'required|string|max:35'
        ]);

        $seksi = Seksi::findOrFail($id);
        $data = [
            'vcNamaseksi' => $request->vcNamaSeksi,
            'dtChange' => now()
        ];

        $seksi->update($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Seksi berhasil diperbarui.']);
        }

        return redirect()->route('seksi.index')
            ->with('success', 'Seksi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $seksi = Seksi::findOrFail($id);
        $seksi->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Seksi berhasil dihapus.']);
        }

        return redirect()->route('seksi.index')
            ->with('success', 'Seksi berhasil dihapus.');
    }
}
