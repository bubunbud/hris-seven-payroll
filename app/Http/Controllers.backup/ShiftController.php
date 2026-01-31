<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shifts = Shift::orderBy('vcShift')->get();
        return view('master.shift.index', compact('shifts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vcShift' => 'required|string|max:5|unique:m_shift,vcShift',
            'vcMasuk' => 'required|date_format:H:i',
            'vcPulang' => 'required|date_format:H:i|after:vcMasuk',
            'vcKeterangan' => 'nullable|string|max:50',
        ]);

        $data = $request->all();
        $data['dtCreate'] = now();
        $data['dtChange'] = now();

        Shift::create($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Shift kerja berhasil ditambahkan.']);
        }

        return redirect()->route('shift.index')
            ->with('success', 'Shift kerja berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $shift = Shift::findOrFail($id);

        return response()->json(['success' => true, 'shift' => $shift]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'vcShift' => 'required|string|max:5|unique:m_shift,vcShift,' . $id . ',vcShift',
            'vcMasuk' => 'required|date_format:H:i',
            'vcPulang' => 'required|date_format:H:i|after:vcMasuk',
            'vcKeterangan' => 'nullable|string|max:50',
        ]);

        $shift = Shift::findOrFail($id);
        $data = $request->all();
        $data['dtChange'] = now();

        $shift->update($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Shift kerja berhasil diperbarui.']);
        }

        return redirect()->route('shift.index')
            ->with('success', 'Shift kerja berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $shift = Shift::findOrFail($id);

        // Check if shift is being used by any karyawan
        if ($shift->karyawans()->count() > 0) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shift tidak dapat dihapus karena masih digunakan oleh karyawan.'
                ], 422);
            }
            return redirect()->route('shift.index')
                ->with('error', 'Shift tidak dapat dihapus karena masih digunakan oleh karyawan.');
        }

        $shift->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Shift kerja berhasil dihapus.']);
        }

        return redirect()->route('shift.index')
            ->with('success', 'Shift kerja berhasil dihapus.');
    }

    /**
     * Get shift data for AJAX
     */
    public function getShift(string $id)
    {
        $shift = Shift::findOrFail($id);
        return response()->json($shift);
    }
}
