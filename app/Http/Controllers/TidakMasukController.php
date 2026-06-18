<?php

namespace App\Http\Controllers;

use App\Models\TidakMasuk;
use App\Models\JenisIjin; // m_jenis_absen
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TidakMasukController extends Controller
{
    /**
     * @return array{0: string, 1: string, 2: string, 3: string} nik, kode, mulai, selesai
     */
    private function decodeCompositeId(string $id): array
    {
        $decoded = base64_decode($id, true);
        if ($decoded === false) {
            abort(404);
        }

        $parts = explode('|', $decoded);
        if (count($parts) !== 4) {
            abort(404);
        }

        return $parts;
    }

    public function index(Request $request)
    {
        $startDate = $request->get('dari_tanggal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('sampai_tanggal', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        // Get filter parameters
        $search = $request->get('search'); // NIK / Nama (gabungan)
        // Backward compatibility: jika masih ada parameter nik, gunakan itu
        $nik = $request->get('nik');
        if (!$search && $nik) {
            $search = $nik;
        }

        // Load karyawan aktif untuk autocomplete lokal
        $karyawans = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti')
            ->with(['divisi', 'bagian'])
            ->orderBy('Nama')
            ->get(['Nik', 'Nama', 'Divisi', 'vcKodeBagian']);

        // Siapkan data sederhana untuk frontend (hindari logic berat di Blade)
        $karyawanList = $karyawans->map(function ($k) {
            $divisiNama = '-';
            if ($k->divisi && isset($k->divisi->vcNamaDivisi)) {
                $divisiNama = $k->divisi->vcNamaDivisi;
            } elseif ($k->Divisi) {
                $divisiNama = $k->Divisi;
            }

            $bagianNama = '-';
            if ($k->bagian && isset($k->bagian->vcNamaBagian)) {
                $bagianNama = $k->bagian->vcNamaBagian;
            }

            return [
                'nik' => $k->Nik ?: '',
                'nama' => $k->Nama ?: '',
                'divisi' => $divisiNama,
                'bagian' => $bagianNama,
                'search' => strtolower(($k->Nik ?: '') . ' ' . ($k->Nama ?: '')),
            ];
        })->values();

        // Filter berdasarkan tanggal mulai izin
        $query = TidakMasuk::with(['karyawan', 'jenisAbsen'])
            ->whereBetween('dtTanggalMulai', [$startDate, $endDate])
            ->orderBy('dtCreate', 'desc');

        // Apply search filter (multi-term support)
        if ($search) {
            $searchTerms = preg_split('/,\s*/', trim($search));
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    if (!empty(trim($term))) {
                        $term = trim($term);
                        // Jika format "NIK - Nama", ambil NIK saja
                        if (strpos($term, ' - ') !== false) {
                            $term = explode(' - ', $term)[0];
                        }
                        $q->orWhere('vcNik', 'like', '%' . $term . '%')
                            ->orWhereHas('karyawan', function ($q2) use ($term) {
                                $q2->where('Nama', 'like', '%' . $term . '%');
                            });
                    }
                }
            });
        }

        $records = $query->paginate(25);
        $jenisAbsens = JenisIjin::orderBy('vcKeterangan')->get();

        return view('absen.tidak_masuk.index', compact('records', 'jenisAbsens', 'startDate', 'endDate', 'search', 'karyawanList'));
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
        [$nik, $kode, $mulai, $selesai] = $this->decodeCompositeId($id);
        $record = TidakMasuk::with(['karyawan', 'jenisAbsen'])
            ->compositeKey($nik, $kode, $mulai, $selesai)
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

        [$nik, $kode, $mulai, $selesai] = $this->decodeCompositeId($id);

        $updated = TidakMasuk::compositeKey($nik, $kode, $mulai, $selesai)->update([
            'vcNik' => $request->vcNik,
            'vcKodeAbsen' => $request->vcKodeAbsen,
            'dtTanggalMulai' => $request->dtTanggalMulai,
            'dtTanggalSelesai' => $request->dtTanggalSelesai,
            'vcKeterangan' => $request->vcKeterangan,
            'vcDibayar' => $request->vcDibayar ?? 'N',
            'dtChange' => Carbon::now(),
        ]);

        if ($updated === 0) {
            abort(404);
        }

        return response()->json(['success' => true, 'message' => 'Izin Tidak Masuk berhasil diperbarui']);
    }

    public function destroy(string $id)
    {
        [$nik, $kode, $mulai, $selesai] = $this->decodeCompositeId($id);

        $deleted = TidakMasuk::compositeKey($nik, $kode, $mulai, $selesai)->delete();

        if ($deleted === 0) {
            abort(404);
        }

        return response()->json(['success' => true, 'message' => 'Izin Tidak Masuk berhasil dihapus']);
    }
}
