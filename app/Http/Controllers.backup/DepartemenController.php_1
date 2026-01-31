<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departemen;
use App\Models\Jabatan;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Log;

class DepartemenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departemens = Departemen::with('jabatan')->orderBy('vcKodeDept')->get();
        $jabatans = Jabatan::orderBy('vcKodeJabatan')->get();
        return view('master.departemen.index', compact('departemens', 'jabatans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vcKodeDept' => 'required|string|max:10|unique:m_dept,vcKodeDept',
            'vcNamaDept' => 'required|string|max:25',
            'vcPICDept' => 'nullable|string|max:50',
            'vcKodeJabatan' => 'nullable|string|max:10|exists:m_jabatan,vcKodeJabatan'
        ]);

        $data = $request->all();
        $data['dtCreate'] = now();
        $data['dtChange'] = now();

        Departemen::create($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Departemen berhasil ditambahkan.']);
        }

        return redirect()->route('departemen.index')
            ->with('success', 'Departemen berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Log untuk debugging
            Log::info('Update departemen', [
                'id' => $id,
                'request_data' => $request->all()
            ]);

            // Validasi dengan penanganan khusus untuk vcKodeJabatan
            $rules = [
                'vcNamaDept' => 'required|string|max:25',
                'vcPICDept' => 'nullable|string|max:50',
            ];

            // Hanya validasi exists jika vcKodeJabatan tidak kosong
            $vcKodeJabatan = $request->input('vcKodeJabatan');
            if (!empty($vcKodeJabatan) && trim($vcKodeJabatan) !== '') {
                $rules['vcKodeJabatan'] = 'required|string|max:10|exists:m_jabatan,vcKodeJabatan';
            } else {
                $rules['vcKodeJabatan'] = 'nullable|string|max:10';
            }

            try {
                $request->validate($rules);
            } catch (\Illuminate\Validation\ValidationException $e) {
                Log::error('Validation error', [
                    'errors' => $e->errors(),
                    'request_data' => $request->all()
                ]);
                throw $e;
            }

            // Cari departemen dengan kode yang diberikan
            $departemen = Departemen::where('vcKodeDept', $id)->first();

            if (!$departemen) {
                Log::warning('Departemen tidak ditemukan', ['id' => $id]);
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Departemen tidak ditemukan dengan kode: ' . $id
                    ], 404);
                }
                return redirect()->route('departemen.index')
                    ->with('error', 'Departemen tidak ditemukan.');
            }

            $data = $request->only(['vcNamaDept', 'vcPICDept', 'vcKodeJabatan']);
            // Jika vcKodeJabatan kosong, set ke null
            if (isset($data['vcKodeJabatan']) && ($data['vcKodeJabatan'] === '' || $data['vcKodeJabatan'] === null)) {
                $data['vcKodeJabatan'] = null;
            }
            $data['dtChange'] = now();

            $departemen->update($data);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Departemen berhasil diperbarui.'
                ]);
            }

            return redirect()->route('departemen.index')
                ->with('success', 'Departemen berhasil diperbarui.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation exception', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'id' => $id
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                $errorMessages = [];
                foreach ($e->errors() as $field => $messages) {
                    $errorMessages[] = $field . ': ' . implode(', ', $messages);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal: ' . implode(' | ', $errorMessages),
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $departemen = Departemen::findOrFail($id);
        $departemen->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Departemen berhasil dihapus.']);
        }

        return redirect()->route('departemen.index')
            ->with('success', 'Departemen berhasil dihapus.');
    }

    /**
     * Get karyawan berdasarkan jabatan
     */
    public function getKaryawanByJabatan(Request $request)
    {
        $request->validate([
            'jabatan' => 'required|string|max:10',
        ]);

        $kodeJabatan = $request->jabatan;

        // Cari karyawan yang memiliki jabatan tersebut dan aktif
        $karyawan = Karyawan::where('vcAktif', '1')
            ->where(function ($query) use ($kodeJabatan) {
                // Field Jabat bisa berisi kode saja atau "kode -> nama"
                $query->where('Jabat', $kodeJabatan)
                    ->orWhere('Jabat', 'like', $kodeJabatan . ' -> %');
            })
            ->first(['Nik', 'Nama', 'Jabat']);

        if ($karyawan) {
            return response()->json([
                'success' => true,
                'karyawan' => [
                    'nik' => $karyawan->Nik,
                    'nama' => $karyawan->Nama,
                    'jabatan' => $karyawan->Jabat
                ],
                'picDept' => $karyawan->Nik . ' - ' . $karyawan->Nama
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Karyawan dengan jabatan tersebut tidak ditemukan atau tidak aktif'
        ]);
    }
}
