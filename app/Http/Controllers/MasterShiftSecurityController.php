<?php

namespace App\Http\Controllers;

use App\Models\ShiftSecurity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MasterShiftSecurityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shifts = ShiftSecurity::orderBy('vcKodeShift')->get();
        return view('master.shift-security.index', compact('shifts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('master.shift-security.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vcKodeShift' => 'required|integer|in:1,2,3|unique:m_shift_security,vcKodeShift',
            'vcNamaShift' => 'required|string|max:20',
            'dtJamMasuk' => 'required|date_format:H:i',
            'dtJamPulang' => 'required|date_format:H:i',
            'isCrossDay' => 'nullable|boolean',
            'intDurasiJam' => 'required|numeric|min:0|max:24',
            'intToleransiMasuk' => 'required|integer|min:0|max:120',
            'intToleransiPulang' => 'required|integer|min:0|max:120',
            'vcKeterangan' => 'nullable|string|max:100',
        ]);

        try {
            $now = Carbon::now();

            ShiftSecurity::create([
                'vcKodeShift' => $request->vcKodeShift,
                'vcNamaShift' => $request->vcNamaShift,
                'dtJamMasuk' => $request->dtJamMasuk,
                'dtJamPulang' => $request->dtJamPulang,
                'isCrossDay' => $request->boolean('isCrossDay'),
                'intDurasiJam' => $request->intDurasiJam,
                'intToleransiMasuk' => $request->intToleransiMasuk,
                'intToleransiPulang' => $request->intToleransiPulang,
                'vcKeterangan' => $request->vcKeterangan,
                'dtCreate' => $now,
                'dtChange' => $now,
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Master shift security berhasil ditambahkan!']);
            }

            return redirect()->route('master-shift-security.index')
                ->with('success', 'Master shift security berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Error storing master shift security: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 422);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource (untuk AJAX/modal).
     */
    public function show($master_shift_security)
    {
        $shiftSecurity = ShiftSecurity::findOrFail($master_shift_security);
        $data = [
            'vcKodeShift' => $shiftSecurity->vcKodeShift,
            'vcNamaShift' => $shiftSecurity->vcNamaShift,
            'dtJamMasuk' => \Carbon\Carbon::parse($shiftSecurity->dtJamMasuk)->format('H:i'),
            'dtJamPulang' => \Carbon\Carbon::parse($shiftSecurity->dtJamPulang)->format('H:i'),
            'isCrossDay' => $shiftSecurity->isCrossDay,
            'intDurasiJam' => (float) $shiftSecurity->intDurasiJam,
            'intToleransiMasuk' => $shiftSecurity->intToleransiMasuk,
            'intToleransiPulang' => $shiftSecurity->intToleransiPulang,
            'vcKeterangan' => $shiftSecurity->vcKeterangan ?? '',
        ];
        return response()->json(['success' => true, 'shift' => $data]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($master_shift_security)
    {
        $shiftSecurity = ShiftSecurity::findOrFail($master_shift_security);
        return view('master.shift-security.edit', compact('shiftSecurity'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $master_shift_security)
    {
        $shiftSecurity = ShiftSecurity::findOrFail($master_shift_security);

        $request->validate([
            'vcNamaShift' => 'required|string|max:20',
            'dtJamMasuk' => 'required|date_format:H:i',
            'dtJamPulang' => 'required|date_format:H:i',
            'isCrossDay' => 'nullable|boolean',
            'intDurasiJam' => 'required|numeric|min:0|max:24',
            'intToleransiMasuk' => 'required|integer|min:0|max:120',
            'intToleransiPulang' => 'required|integer|min:0|max:120',
            'vcKeterangan' => 'nullable|string|max:100',
        ]);

        try {
            $shiftSecurity->update([
                'vcNamaShift' => $request->vcNamaShift,
                'dtJamMasuk' => $request->dtJamMasuk,
                'dtJamPulang' => $request->dtJamPulang,
                'isCrossDay' => $request->boolean('isCrossDay'),
                'intDurasiJam' => $request->intDurasiJam,
                'intToleransiMasuk' => $request->intToleransiMasuk,
                'intToleransiPulang' => $request->intToleransiPulang,
                'vcKeterangan' => $request->vcKeterangan,
                'dtChange' => Carbon::now(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Master shift security berhasil diupdate!']);
            }

            return redirect()->route('master-shift-security.index')
                ->with('success', 'Master shift security berhasil diupdate!');
        } catch (\Exception $e) {
            Log::error('Error updating master shift security: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 422);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($master_shift_security)
    {
        try {
            $shiftSecurity = ShiftSecurity::findOrFail($master_shift_security);

            // Cek apakah shift sudah digunakan di jadwal
            $count = DB::table('t_jadwal_shift_security')
                ->where('intShift', $master_shift_security)
                ->count();

            if ($count > 0) {
                $msg = 'Shift tidak dapat dihapus karena sudah digunakan di ' . $count . ' jadwal!';
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return redirect()->back()->with('error', $msg);
            }

            $shiftSecurity->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Master shift security berhasil dihapus!']);
            }

            return redirect()->route('master-shift-security.index')
                ->with('success', 'Master shift security berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting master shift security: ' . $e->getMessage());
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
