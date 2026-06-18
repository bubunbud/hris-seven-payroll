<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seksi;
use App\Models\Divisi;

class SeksiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Mapping divisi ke prefix untuk filter
        $prefixMapping = [
            'RMA' => 'SRMA',
            'SIA-CPD' => 'SSIA',
            'SIA-P11' => 'SSIA',
            'SIA-P12' => 'SSIA',
            'SMU' => 'SSMU',
        ];

        // Get filter divisi dari request
        $filterDivisi = $request->get('filter_divisi', '');

        // Query seksi
        $query = Seksi::query();

        // Apply filter berdasarkan divisi
        if ($filterDivisi && isset($prefixMapping[$filterDivisi])) {
            $prefix = $prefixMapping[$filterDivisi];
            $query->where('vcKodeseksi', 'like', $prefix . '%');
        }

        $seksis = $query->orderBy('vcKodeseksi')->get();
        
        // Load divisi untuk dropdown (hanya yang relevan: RMA, SIA-CPD, SIA-P11, SIA-P12, SMU)
        $divisis = Divisi::whereIn('vcKodeDivisi', ['RMA', 'SIA-CPD', 'SIA-P11', 'SIA-P12', 'SMU'])
            ->orderBy('vcKodeDivisi')
            ->get(['vcKodeDivisi', 'vcNamaDivisi']);
        
        return view('master.seksi.index', compact('seksis', 'divisis', 'filterDivisi'));
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
            'vcKodeSeksi' => 'required|string|max:7|unique:m_seksi,vcKodeseksi',
            'vcNamaSeksi' => 'required|string|max:35'
        ]);

        $data = [
            'vcKodeseksi' => $request->vcKodeSeksi,
            'vcNamaseksi' => $request->vcNamaSeksi,
            'dtCreate' => now(),
            'dtChange' => now()
        ];

        Seksi::create($data);

        // Selalu return JSON untuk AJAX request
        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Data berhasil disimpan']);
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

    /**
     * Generate kode seksi otomatis berdasarkan divisi
     */
    public function generateKodeSeksi(Request $request)
    {
        $request->validate([
            'divisi' => 'required|string|max:20',
        ]);

        $kodeDivisi = $request->divisi;

        // Mapping divisi ke prefix
        $prefixMapping = [
            'RMA' => 'SRMA',
            'SIA-CPD' => 'SSIA',
            'SIA-P11' => 'SSIA',
            'SIA-P12' => 'SSIA',
            'SMU' => 'SSMU',
        ];

        // Tentukan prefix berdasarkan divisi
        $prefix = $prefixMapping[$kodeDivisi] ?? null;

        if (!$prefix) {
            return response()->json([
                'success' => false,
                'message' => 'Divisi tidak valid untuk generate kode seksi'
            ], 422);
        }

        // Cari counter terakhir dari kode seksi yang sudah ada dengan prefix yang sama
        $lastKode = Seksi::where('vcKodeseksi', 'like', $prefix . '%')
            ->orderBy('vcKodeseksi', 'desc')
            ->value('vcKodeseksi');

        // Extract counter dari kode terakhir
        $counter = 1;
        if ($lastKode) {
            // Ambil bagian counter (setelah prefix)
            $counterStr = substr($lastKode, strlen($prefix));
            // Coba parse sebagai integer
            $lastCounter = (int) $counterStr;
            if ($lastCounter > 0) {
                $counter = $lastCounter + 1;
            }
        }

        // Format counter dengan 3 digit (001, 002, dst)
        $counterFormatted = str_pad($counter, 3, '0', STR_PAD_LEFT);

        // Generate kode baru
        $newKode = $prefix . $counterFormatted;

        // Pastikan kode belum ada (safety check)
        $exists = Seksi::where('vcKodeseksi', $newKode)->exists();
        if ($exists) {
            // Jika sudah ada, cari counter berikutnya
            $counter++;
            $counterFormatted = str_pad($counter, 3, '0', STR_PAD_LEFT);
            $newKode = $prefix . $counterFormatted;
        }

        return response()->json([
            'success' => true,
            'kodeSeksi' => $newKode,
            'prefix' => $prefix,
            'counter' => $counter
        ]);
    }
}
