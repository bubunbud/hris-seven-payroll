<?php

namespace App\Http\Controllers;

use App\Models\TidakMasuk;
use App\Models\JenisIjin; // m_jenis_absen
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TidakMasukController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('dari_tanggal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('sampai_tanggal', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $nik = $request->get('nik');

        // Filter berdasarkan tanggal mulai izin
        $query = TidakMasuk::with(['karyawan', 'jenisAbsen'])
            ->whereBetween('dtTanggalMulai', [$startDate, $endDate])
            ->orderBy('dtCreate', 'desc');

        if ($nik) {
            $query->where('vcNik', 'like', '%' . $nik . '%');
        }

        $records = $query->paginate(25);
        $jenisAbsens = JenisIjin::orderBy('vcKeterangan')->get();

        return view('absen.tidak_masuk.index', compact('records', 'jenisAbsens', 'startDate', 'endDate', 'nik'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vcNik' => 'required|string|max:10',
            'vcKodeAbsen' => 'required|string|max:5|exists:m_jenis_absen,vcKodeAbsen',
            'dtTanggalMulai' => 'required|date',
            'dtTanggalSelesai' => 'required|date|after_or_equal:dtTanggalMulai',
            'vcKeterangan' => 'nullable|string|max:100',
            'vcDibayar' => 'nullable|in:Y,N',
        ], [
            'vcNik.required' => 'NIK harus diisi',
            'vcKodeAbsen.required' => 'Jenis Izin harus dipilih',
            'vcKodeAbsen.exists' => 'Jenis Izin tidak valid',
            'dtTanggalMulai.required' => 'Tanggal mulai harus diisi',
            'dtTanggalSelesai.required' => 'Tanggal selesai harus diisi',
            'dtTanggalSelesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai',
        ]);

        TidakMasuk::create([
            'vcNik' => $request->vcNik,
            'vcKodeAbsen' => $request->vcKodeAbsen,
            'dtTanggalMulai' => $request->dtTanggalMulai,
            'dtTanggalSelesai' => $request->dtTanggalSelesai,
            'vcKeterangan' => $request->vcKeterangan,
            'vcDibayar' => $request->vcDibayar ?? 'N',
            'dtCreate' => Carbon::now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Izin Tidak Masuk berhasil ditambahkan']);
    }

    public function show(string $id)
    {
        // Decode composite key: nik|kode|mulai|selesai
        $parts = explode('|', base64_decode($id));
        $record = TidakMasuk::with(['karyawan', 'jenisAbsen'])
            ->where('vcNik', $parts[0])
            ->where('vcKodeAbsen', $parts[1])
            ->where('dtTanggalMulai', $parts[2])
            ->where('dtTanggalSelesai', $parts[3])
            ->firstOrFail();

        // Pastikan tanggal dalam format Y-m-d untuk input type="date"
        $payload = $record->toArray();
        $payload['dtTanggalMulai'] = $record->dtTanggalMulai ? $record->dtTanggalMulai->format('Y-m-d') : null;
        $payload['dtTanggalSelesai'] = $record->dtTanggalSelesai ? $record->dtTanggalSelesai->format('Y-m-d') : null;

        return response()->json(['success' => true, 'record' => $payload]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'vcNik' => 'required|string|max:10',
            'vcKodeAbsen' => 'required|string|max:5|exists:m_jenis_absen,vcKodeAbsen',
            'dtTanggalMulai' => 'required|date',
            'dtTanggalSelesai' => 'required|date|after_or_equal:dtTanggalMulai',
            'vcKeterangan' => 'nullable|string|max:100',
            'vcDibayar' => 'nullable|in:Y,N',
        ]);

        // Decode composite key: nik|kode|mulai|selesai
        $parts = explode('|', base64_decode($id));
        $record = TidakMasuk::where('vcNik', $parts[0])
            ->where('vcKodeAbsen', $parts[1])
            ->where('dtTanggalMulai', $parts[2])
            ->where('dtTanggalSelesai', $parts[3])
            ->firstOrFail();
        $record->update([
            'vcNik' => $request->vcNik,
            'vcKodeAbsen' => $request->vcKodeAbsen,
            'dtTanggalMulai' => $request->dtTanggalMulai,
            'dtTanggalSelesai' => $request->dtTanggalSelesai,
            'vcKeterangan' => $request->vcKeterangan,
            'vcDibayar' => $request->vcDibayar ?? 'N',
            'dtChange' => Carbon::now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Izin Tidak Masuk berhasil diperbarui']);
    }

    public function destroy(string $id)
    {
        // Decode composite key: nik|kode|mulai|selesai
        $parts = explode('|', base64_decode($id));
        $record = TidakMasuk::where('vcNik', $parts[0])
            ->where('vcKodeAbsen', $parts[1])
            ->where('dtTanggalMulai', $parts[2])
            ->where('dtTanggalSelesai', $parts[3])
            ->firstOrFail();
        $record->delete();
        return response()->json(['success' => true, 'message' => 'Izin Tidak Masuk berhasil dihapus']);
    }
}
