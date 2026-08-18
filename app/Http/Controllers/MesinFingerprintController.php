<?php

namespace App\Http\Controllers;

use App\Models\MesinFingerprint;
use Illuminate\Http\Request;

class MesinFingerprintController extends Controller
{
    public function index()
    {
        $mesins = MesinFingerprint::orderBy('vcNama')->get();

        return view('mesin-fingerprint.index', compact('mesins'));
    }

    public function store(Request $request)
    {
        $data = $this->validateMesin($request);
        $data['dtCreate'] = now();
        $data['dtChange'] = now();

        MesinFingerprint::create($data);

        return $this->jsonOrRedirect($request, true, 'Mesin fingerprint berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $mesin = MesinFingerprint::findOrFail($id);

        return response()->json(['success' => true, 'mesin' => $mesin]);
    }

    public function update(Request $request, string $id)
    {
        $mesin = MesinFingerprint::findOrFail($id);
        $data = $this->validateMesin($request, $mesin->id);
        $data['dtChange'] = now();
        $mesin->update($data);

        return $this->jsonOrRedirect($request, true, 'Mesin fingerprint berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $mesin = MesinFingerprint::findOrFail($id);
        $mesin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mesin fingerprint berhasil dihapus.',
        ]);
    }

    public function testConnection(string $id)
    {
        $mesin = MesinFingerprint::findOrFail($id);
        $result = app(\App\Services\ZkTecoFingerprintService::class)->testConnection($mesin);

        return response()->json($result, ($result['success'] ?? false) ? 200 : 500);
    }

    protected function validateMesin(Request $request, ?int $ignoreId = null): array
    {
        $uniqueIp = 'unique:m_mesin_fingerprint,vcIp';
        if ($ignoreId) {
            $uniqueIp .= ',' . $ignoreId;
        }

        return $request->validate([
            'vcNama' => 'required|string|max:100',
            'vcMerk' => 'nullable|string|max:50',
            'vcTipe' => 'nullable|string|max:50',
            'vcIp' => ['required', 'ip', $uniqueIp],
            'intPort' => 'required|integer|min:1|max:65535',
            'intCommKey' => 'nullable|integer|min:0',
            'vcAktif' => 'required|in:0,1',
            'vcKeterangan' => 'nullable|string|max:255',
        ], [
            'vcNama.required' => 'Nama mesin harus diisi',
            'vcIp.required' => 'IP address harus diisi',
            'vcIp.ip' => 'Format IP address tidak valid',
            'vcIp.unique' => 'IP address sudah terdaftar',
        ]);
    }

    protected function jsonOrRedirect(Request $request, bool $success, string $message)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => $success, 'message' => $message]);
        }

        return redirect()->route('mesin-fingerprint.index')
            ->with($success ? 'success' : 'error', $message);
    }
}
