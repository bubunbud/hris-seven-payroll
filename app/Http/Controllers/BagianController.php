<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bagian;
use App\Models\Jabatan;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Log;

class BagianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bagians = Bagian::with('jabatan')->orderBy('vcKodeBagian')->get();
        $jabatans = Jabatan::orderBy('vcKodeJabatan')->get();
        return view('master.bagian.index', compact('bagians', 'jabatans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vcKodeBagian' => 'required|string|max:7|unique:m_bagian,vcKodeBagian',
            'vcNamaBagian' => 'required|string|max:35',
            'vcPICBagian' => 'nullable|string|max:50',
            'vcKodeJabatan' => 'nullable|string|max:10|exists:m_jabatan,vcKodeJabatan'
        ]);

        $data = $request->all();
        $data['dtCreate'] = now();
        $data['dtChange'] = now();

        Bagian::create($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Bagian berhasil ditambahkan.']);
        }

        return redirect()->route('bagian.index')
            ->with('success', 'Bagian berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Log untuk debugging
            Log::info('Update bagian', [
                'id' => $id,
                'request_data' => $request->all()
            ]);

            // Validasi dengan penanganan khusus untuk vcKodeJabatan
            $rules = [
                'vcNamaBagian' => 'required|string|max:35',
                'vcPICBagian' => 'nullable|string|max:50',
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

            // Cari bagian dengan kode yang diberikan
            $bagian = Bagian::where('vcKodeBagian', $id)->first();

            if (!$bagian) {
                Log::warning('Bagian tidak ditemukan', ['id' => $id]);
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bagian tidak ditemukan dengan kode: ' . $id
                    ], 404);
                }
                return redirect()->route('bagian.index')
                    ->with('error', 'Bagian tidak ditemukan.');
            }

            $data = $request->only(['vcNamaBagian', 'vcPICBagian', 'vcKodeJabatan']);
            // Jika vcKodeJabatan kosong, set ke null
            if (isset($data['vcKodeJabatan']) && ($data['vcKodeJabatan'] === '' || $data['vcKodeJabatan'] === null)) {
                $data['vcKodeJabatan'] = null;
            }
            $data['dtChange'] = now();

            $bagian->update($data);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bagian berhasil diperbarui.'
                ]);
            }

            return redirect()->route('bagian.index')
                ->with('success', 'Bagian berhasil diperbarui.');
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
        $bagian = Bagian::findOrFail($id);
        $bagian->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Bagian berhasil dihapus.']);
        }

        return redirect()->route('bagian.index')
            ->with('success', 'Bagian berhasil dihapus.');
    }

    /**
     * Get karyawan berdasarkan jabatan dan kode bagian
     */
    public function getKaryawanByJabatan(Request $request)
    {
        $request->validate([
            'jabatan' => 'required|string|max:10',
            'kode_bagian' => 'required|string|max:7',
        ]);

        $kodeJabatan = $request->jabatan;
        $kodeBagian = $request->kode_bagian;

        // Cari karyawan yang memiliki kombinasi kode bagian dan jabatan tersebut, dan aktif
        $karyawan = Karyawan::where('vcAktif', '1')
            ->where('vcKodeBagian', $kodeBagian)
            ->where(function ($query) use ($kodeJabatan) {
                // Field Jabat bisa berisi kode saja atau "kode -> nama"
                $query->where('Jabat', $kodeJabatan)
                    ->orWhere('Jabat', 'like', $kodeJabatan . ' -> %');
            })
            ->first(['Nik', 'Nama', 'Jabat', 'vcKodeBagian']);

        if ($karyawan) {
            return response()->json([
                'success' => true,
                'karyawan' => [
                    'nik' => $karyawan->Nik,
                    'nama' => $karyawan->Nama,
                    'jabatan' => $karyawan->Jabat,
                    'kode_bagian' => $karyawan->vcKodeBagian
                ],
                'picBagian' => $karyawan->Nik . ' - ' . $karyawan->Nama
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Karyawan dengan kombinasi kode bagian dan jabatan tersebut tidak ditemukan atau tidak aktif'
        ]);
    }
}
