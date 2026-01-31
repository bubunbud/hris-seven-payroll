<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\TukarHariKerja;
use App\Models\TukarHariKerjaDetail;
use App\Models\Karyawan;
use App\Models\Divisi;
use App\Models\Departemen;
use App\Models\Bagian;
use App\Models\HariLibur;

class TukarHariKerjaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get divisi list for filter dropdown
        $divisis = Divisi::orderBy('vcKodeDivisi')->get();

        $query = TukarHariKerja::with(['divisi', 'karyawan.divisi', 'karyawan.bagian'])
            ->orderByRaw('COALESCE(dtCreatedAt, tanggal_libur, NOW()) DESC')
            ->orderBy('tanggal_libur', 'desc')
            ->orderBy('nik', 'asc');

        // Filter by tanggal
        if ($request->filled('dari_tanggal')) {
            $query->where('tanggal_libur', '>=', $request->dari_tanggal);
        }
        if ($request->filled('sampai_tanggal')) {
            $query->where('tanggal_libur', '<=', $request->sampai_tanggal);
        }

        // Filter by scope
        if ($request->filled('scope')) {
            $query->where('vcScope', $request->scope);
        }

        // Filter by Bisnis Unit (Divisi)
        if ($request->filled('divisi')) {
            $query->whereHas('karyawan', function ($q) use ($request) {
                $q->where('Divisi', $request->divisi);
            });
        }

        // Filter by Tipe
        if ($request->filled('tipe')) {
            $query->where('vcTipeTukar', $request->tipe);
        }

        // Filter by pencarian NIK atau Nama
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('karyawan', function ($q) use ($search) {
                $q->where('Nik', 'like', '%' . $search . '%')
                  ->orWhere('Nama', 'like', '%' . $search . '%');
            });
        }

        $tukarHariKerja = $query->paginate(20)->withQueryString();

        return view('absen.tukar-hari-kerja.index', compact('tukarHariKerja', 'divisis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $divisis = Divisi::orderBy('vcKodeDivisi')->get();
        $departemens = Departemen::orderBy('vcKodeDept')->get();
        $bagians = Bagian::orderBy('vcKodeBagian')->get();

        return view('absen.tukar-hari-kerja.create', compact('divisis', 'departemens', 'bagians'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Handle karyawan_ids jika berupa JSON string
        if ($request->has('karyawan_ids') && is_string($request->karyawan_ids)) {
            $karyawanIds = json_decode($request->karyawan_ids, true);
            $request->merge(['karyawan_ids' => $karyawanIds ?? []]);
        }

        $request->validate([
            'dtTanggalLibur' => 'required|date',
            'dtTanggalKerja' => 'required|date|different:dtTanggalLibur',
            'vcTipeTukar' => 'required|in:LIBUR_KE_KERJA,KERJA_KE_LIBUR',
            'vcScope' => 'required|in:PERORANGAN,GROUP,SEMUA_BU',
            'vcKeterangan' => 'nullable|string|max:255',
            'karyawan_ids' => 'nullable',
            'vcKodeDivisi' => 'nullable',
            'vcKodeDept' => 'nullable',
            'vcKodeBagian' => 'nullable',
        ]);

        DB::beginTransaction();
        try {
            // Get karyawan list berdasarkan scope
            $karyawanList = $this->getKaryawanByScope($request);

            if ($karyawanList->isEmpty()) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Tidak ada karyawan yang ditemukan berdasarkan filter yang dipilih.');
            }

            $tanggalLibur = Carbon::parse($request->dtTanggalLibur)->format('Y-m-d');
            $tanggalKerja = Carbon::parse($request->dtTanggalKerja)->format('Y-m-d');
            $createdBy = Auth::user()->name ?? 'System';
            $createdAt = Carbon::now();

            // Create record untuk setiap karyawan (composite key: tanggal_libur, nik)
            // Otomatis membuat 2 record: satu untuk LIBUR_KE_KERJA dan satu untuk KERJA_KE_LIBUR
            // Ini memastikan tukar hari kerja bersifat bidirectional (2 arah)
            $count = 0;
            foreach ($karyawanList as $karyawan) {
                if ($request->vcTipeTukar === 'LIBUR_KE_KERJA') {
                    // Mode LIBUR_KE_KERJA: tanggal_libur menjadi hari kerja, tanggal_kerja menjadi hari libur
                    
                    // Record 1: LIBUR_KE_KERJA (tanggal_libur menjadi hari kerja)
                    $existing1 = TukarHariKerja::where('tanggal_libur', $tanggalLibur)
                        ->where('nik', $karyawan->Nik)
                        ->where('vcTipeTukar', 'LIBUR_KE_KERJA')
                        ->first();

                    if (!$existing1) {
                        TukarHariKerja::create([
                            'tanggal_libur' => $tanggalLibur,
                            'nik' => $karyawan->Nik,
                            'tanggal_kerja' => $tanggalKerja,
                            'vcKeterangan' => $request->vcKeterangan,
                            'vcTipeTukar' => 'LIBUR_KE_KERJA',
                            'vcScope' => $request->vcScope,
                            'vcKodeDivisi' => $request->vcKodeDivisi,
                            'vcKodeDept' => $request->vcKodeDept,
                            'vcKodeBagian' => $request->vcKodeBagian,
                            'dtTanggalMulai' => $request->dtTanggalMulai ?? $tanggalLibur,
                            'dtTanggalSelesai' => $request->dtTanggalSelesai ?? $tanggalKerja,
                            'vcCreatedBy' => $createdBy,
                            'dtCreatedAt' => $createdAt,
                        ]);
                        $count++;
                    }

                    // Record 2: KERJA_KE_LIBUR (tanggal_kerja menjadi hari libur) - OTOMATIS
                    // Untuk KERJA_KE_LIBUR, tanggal_libur di record adalah tanggal yang menjadi libur
                    $existing2 = TukarHariKerja::where('tanggal_libur', $tanggalKerja)
                        ->where('nik', $karyawan->Nik)
                        ->where('vcTipeTukar', 'KERJA_KE_LIBUR')
                        ->first();

                    if (!$existing2) {
                        TukarHariKerja::create([
                            'tanggal_libur' => $tanggalKerja, // Tanggal yang menjadi libur
                            'nik' => $karyawan->Nik,
                            'tanggal_kerja' => $tanggalLibur, // Tanggal pengganti
                            'vcKeterangan' => $request->vcKeterangan . ' (Otomatis - kebalikan)',
                            'vcTipeTukar' => 'KERJA_KE_LIBUR',
                            'vcScope' => $request->vcScope,
                            'vcKodeDivisi' => $request->vcKodeDivisi,
                            'vcKodeDept' => $request->vcKodeDept,
                            'vcKodeBagian' => $request->vcKodeBagian,
                            'dtTanggalMulai' => $request->dtTanggalMulai ?? $tanggalLibur,
                            'dtTanggalSelesai' => $request->dtTanggalSelesai ?? $tanggalKerja,
                            'vcCreatedBy' => $createdBy,
                            'dtCreatedAt' => $createdAt,
                        ]);
                    }
                } else {
                    // Mode KERJA_KE_LIBUR: tanggal_kerja menjadi hari libur, tanggal_libur menjadi hari kerja
                    
                    // Record 1: KERJA_KE_LIBUR (tanggal_libur menjadi hari libur)
                    $existing1 = TukarHariKerja::where('tanggal_libur', $tanggalLibur)
                        ->where('nik', $karyawan->Nik)
                        ->where('vcTipeTukar', 'KERJA_KE_LIBUR')
                        ->first();

                    if (!$existing1) {
                        TukarHariKerja::create([
                            'tanggal_libur' => $tanggalLibur, // Tanggal yang menjadi libur
                            'nik' => $karyawan->Nik,
                            'tanggal_kerja' => $tanggalKerja, // Tanggal pengganti
                            'vcKeterangan' => $request->vcKeterangan,
                            'vcTipeTukar' => 'KERJA_KE_LIBUR',
                            'vcScope' => $request->vcScope,
                            'vcKodeDivisi' => $request->vcKodeDivisi,
                            'vcKodeDept' => $request->vcKodeDept,
                            'vcKodeBagian' => $request->vcKodeBagian,
                            'dtTanggalMulai' => $request->dtTanggalMulai ?? $tanggalLibur,
                            'dtTanggalSelesai' => $request->dtTanggalSelesai ?? $tanggalKerja,
                            'vcCreatedBy' => $createdBy,
                            'dtCreatedAt' => $createdAt,
                        ]);
                        $count++;
                    }

                    // Record 2: LIBUR_KE_KERJA (tanggal_kerja menjadi hari kerja) - OTOMATIS
                    $existing2 = TukarHariKerja::where('tanggal_libur', $tanggalKerja)
                        ->where('nik', $karyawan->Nik)
                        ->where('vcTipeTukar', 'LIBUR_KE_KERJA')
                        ->first();

                    if (!$existing2) {
                        TukarHariKerja::create([
                            'tanggal_libur' => $tanggalKerja,
                            'nik' => $karyawan->Nik,
                            'tanggal_kerja' => $tanggalLibur,
                            'vcKeterangan' => $request->vcKeterangan . ' (Otomatis - kebalikan)',
                            'vcTipeTukar' => 'LIBUR_KE_KERJA',
                            'vcScope' => $request->vcScope,
                            'vcKodeDivisi' => $request->vcKodeDivisi,
                            'vcKodeDept' => $request->vcKodeDept,
                            'vcKodeBagian' => $request->vcKodeBagian,
                            'dtTanggalMulai' => $request->dtTanggalMulai ?? $tanggalLibur,
                            'dtTanggalSelesai' => $request->dtTanggalSelesai ?? $tanggalKerja,
                            'vcCreatedBy' => $createdBy,
                            'dtCreatedAt' => $createdAt,
                        ]);
                    }
                }
            }

            DB::commit();

            if ($count > 0) {
                return redirect()->route('tukar-hari-kerja.index')
                    ->with('success', 'Tukar hari kerja berhasil dibuat. Total ' . $count . ' karyawan terkena.');
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Semua karyawan yang dipilih sudah memiliki tukar hari kerja untuk tanggal tersebut.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat tukar hari kerja: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $tanggal_libur, string $nik)
    {
        $tukarHariKerja = TukarHariKerja::where('tanggal_libur', $tanggal_libur)
            ->where('nik', $nik)
            ->with(['divisi', 'karyawan.departemen', 'karyawan.bagian', 'karyawan.jabatan'])
            ->firstOrFail();
        return view('absen.tukar-hari-kerja.show', compact('tukarHariKerja'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $tanggal_libur, string $nik)
    {
        $tukarHariKerja = TukarHariKerja::where('tanggal_libur', $tanggal_libur)
            ->where('nik', $nik)
            ->with('karyawan')
            ->firstOrFail();
        $divisis = Divisi::orderBy('vcKodeDivisi')->get();
        $departemens = Departemen::orderBy('vcKodeDept')->get();
        $bagians = Bagian::orderBy('vcKodeBagian')->get();

        return view('absen.tukar-hari-kerja.edit', compact('tukarHariKerja', 'divisis', 'departemens', 'bagians'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $tanggal_libur, string $nik)
    {
        $tukarHariKerja = TukarHariKerja::where('tanggal_libur', $tanggal_libur)
            ->where('nik', $nik)
            ->firstOrFail();

        $request->validate([
            'dtTanggalLibur' => 'required|date',
            'dtTanggalKerja' => 'required|date|different:dtTanggalLibur',
            'vcKeterangan' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $newTanggalLibur = Carbon::parse($request->dtTanggalLibur)->format('Y-m-d');
            $newTanggalKerja = Carbon::parse($request->dtTanggalKerja)->format('Y-m-d');
            $oldTanggalLibur = Carbon::parse($tanggal_libur)->format('Y-m-d');
            $oldTanggalKerja = $tukarHariKerja->tanggal_kerja ? Carbon::parse($tukarHariKerja->tanggal_kerja)->format('Y-m-d') : null;
            $updatedBy = Auth::user()->name ?? 'System';
            $updatedAt = Carbon::now();

            // Simpan data lama untuk update record kebalikannya
            $oldTipeTukar = $tukarHariKerja->vcTipeTukar;
            $oldScope = $tukarHariKerja->vcScope;
            $oldKodeDivisi = $tukarHariKerja->vcKodeDivisi;
            $oldKodeDept = $tukarHariKerja->vcKodeDept;
            $oldKodeBagian = $tukarHariKerja->vcKodeBagian;
            $oldCreatedBy = $tukarHariKerja->vcCreatedBy;
            $oldCreatedAt = $tukarHariKerja->dtCreatedAt;

            // Jika tanggal_libur berubah, kita perlu delete + insert karena composite primary key
            if ($newTanggalLibur !== $oldTanggalLibur) {
                // Hapus record lama
                TukarHariKerja::where('tanggal_libur', $oldTanggalLibur)
                    ->where('nik', $nik)
                    ->delete();

                // Buat record baru dengan primary key yang baru
                TukarHariKerja::create([
                    'tanggal_libur' => $newTanggalLibur,
                    'nik' => $nik,
                    'tanggal_kerja' => $newTanggalKerja,
                    'vcKeterangan' => $request->vcKeterangan,
                    'vcTipeTukar' => $oldTipeTukar,
                    'vcScope' => $oldScope,
                    'vcKodeDivisi' => $oldKodeDivisi,
                    'vcKodeDept' => $oldKodeDept,
                    'vcKodeBagian' => $oldKodeBagian,
                    'dtTanggalMulai' => $newTanggalLibur,
                    'dtTanggalSelesai' => $newTanggalKerja,
                    'vcCreatedBy' => $oldCreatedBy,
                    'dtCreatedAt' => $oldCreatedAt,
                    'vcUpdatedBy' => $updatedBy,
                    'dtUpdatedAt' => $updatedAt,
                ]);

                // Update record kebalikannya jika ada
                if ($oldTanggalKerja) {
                    $reverseTipeTukar = $oldTipeTukar === 'LIBUR_KE_KERJA' ? 'KERJA_KE_LIBUR' : 'LIBUR_KE_KERJA';
                    $reverseRecord = TukarHariKerja::where('tanggal_libur', $oldTanggalKerja)
                        ->where('nik', $nik)
                        ->where('vcTipeTukar', $reverseTipeTukar)
                        ->first();

                    if ($reverseRecord) {
                        // Hapus record kebalikan lama
                        $reverseRecord->delete();

                        // Buat record kebalikan baru dengan tanggal_kerja yang baru
                        TukarHariKerja::create([
                            'tanggal_libur' => $newTanggalKerja,
                            'nik' => $nik,
                            'tanggal_kerja' => $newTanggalLibur,
                            'vcKeterangan' => $request->vcKeterangan . ' (Otomatis - kebalikan)',
                            'vcTipeTukar' => $reverseTipeTukar,
                            'vcScope' => $oldScope,
                            'vcKodeDivisi' => $oldKodeDivisi,
                            'vcKodeDept' => $oldKodeDept,
                            'vcKodeBagian' => $oldKodeBagian,
                            'dtTanggalMulai' => $newTanggalLibur,
                            'dtTanggalSelesai' => $newTanggalKerja,
                            'vcCreatedBy' => $oldCreatedBy,
                            'dtCreatedAt' => $oldCreatedAt,
                            'vcUpdatedBy' => $updatedBy,
                            'dtUpdatedAt' => $updatedAt,
                        ]);
                    }
                }
            } else {
                // Jika primary key tidak berubah, update field lainnya menggunakan raw query
                DB::table('t_tukar_hari_kerja')
                    ->where('tanggal_libur', $oldTanggalLibur)
                    ->where('nik', $nik)
                    ->update([
                        'tanggal_kerja' => $newTanggalKerja,
                        'vcKeterangan' => $request->vcKeterangan,
                        'dtTanggalMulai' => $newTanggalLibur,
                        'dtTanggalSelesai' => $newTanggalKerja,
                        'vcUpdatedBy' => $updatedBy,
                        'dtUpdatedAt' => $updatedAt,
                    ]);

                // Update record kebalikannya jika ada dan tanggal_kerja berubah
                if ($oldTanggalKerja && $oldTanggalKerja !== $newTanggalKerja) {
                    $reverseTipeTukar = $oldTipeTukar === 'LIBUR_KE_KERJA' ? 'KERJA_KE_LIBUR' : 'LIBUR_KE_KERJA';
                    $reverseRecord = TukarHariKerja::where('tanggal_libur', $oldTanggalKerja)
                        ->where('nik', $nik)
                        ->where('vcTipeTukar', $reverseTipeTukar)
                        ->first();

                    if ($reverseRecord) {
                        // Hapus record kebalikan lama
                        $reverseRecord->delete();

                        // Buat record kebalikan baru dengan tanggal_kerja yang baru
                        TukarHariKerja::create([
                            'tanggal_libur' => $newTanggalKerja,
                            'nik' => $nik,
                            'tanggal_kerja' => $newTanggalLibur,
                            'vcKeterangan' => $request->vcKeterangan . ' (Otomatis - kebalikan)',
                            'vcTipeTukar' => $reverseTipeTukar,
                            'vcScope' => $oldScope,
                            'vcKodeDivisi' => $oldKodeDivisi,
                            'vcKodeDept' => $oldKodeDept,
                            'vcKodeBagian' => $oldKodeBagian,
                            'dtTanggalMulai' => $newTanggalLibur,
                            'dtTanggalSelesai' => $newTanggalKerja,
                            'vcCreatedBy' => $oldCreatedBy,
                            'dtCreatedAt' => $oldCreatedAt,
                            'vcUpdatedBy' => $updatedBy,
                            'dtUpdatedAt' => $updatedAt,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('tukar-hari-kerja.index')
                ->with('success', 'Tukar hari kerja berhasil diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate tukar hari kerja: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $tanggal_libur, string $nik)
    {
        $tukarHariKerja = TukarHariKerja::where('tanggal_libur', $tanggal_libur)
            ->where('nik', $nik)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            // Delete detail (jika ada)
            TukarHariKerjaDetail::where('dtTanggalLibur', $tanggal_libur)
                ->where('vcNik', $nik)
                ->delete();
            
            // Delete header
            $tukarHariKerja->delete();

            DB::commit();

            return redirect()->route('tukar-hari-kerja.index')
                ->with('success', 'Tukar hari kerja berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus tukar hari kerja: ' . $e->getMessage());
        }
    }

    /**
     * Get karyawan berdasarkan scope
     */
    private function getKaryawanByScope(Request $request)
    {
        $query = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti');

        if ($request->vcScope === 'PERORANGAN') {
            $karyawanIds = $request->karyawan_ids;
            
            // Handle JSON string
            if (is_string($karyawanIds)) {
                $karyawanIds = json_decode($karyawanIds, true);
            }
            
            // If still string (single value), convert to array
            if (is_string($karyawanIds)) {
                $karyawanIds = [$karyawanIds];
            }
            
            if (is_array($karyawanIds) && count($karyawanIds) > 0) {
                // Filter out empty values
                $karyawanIds = array_filter($karyawanIds, function($id) {
                    return !empty(trim($id));
                });
                
                if (count($karyawanIds) > 0) {
                    $query->whereIn('Nik', $karyawanIds);
                } else {
                    return collect([]);
                }
            } else {
                return collect([]); // Return empty collection jika tidak ada karyawan yang dipilih
            }
        } elseif ($request->vcScope === 'GROUP') {
            if ($request->vcKodeDivisi) {
                $query->where('Divisi', $request->vcKodeDivisi);
            }
            if ($request->vcKodeDept) {
                $query->where('dept', $request->vcKodeDept);
            }
            if ($request->vcKodeBagian) {
                $query->where('vcKodeBagian', $request->vcKodeBagian);
            }
        } elseif ($request->vcScope === 'SEMUA_BU') {
            if ($request->vcKodeDivisi) {
                $query->where('Divisi', $request->vcKodeDivisi);
            }
        }

        return $query->get();
    }

    /**
     * API: Get karyawan berdasarkan filter
     */
    public function getKaryawan(Request $request)
    {
        $request->validate([
            'divisi' => 'nullable|string',
            'departemen' => 'nullable|string',
            'bagian' => 'nullable|string',
            'search' => 'nullable|string',
        ]);

        $query = Karyawan::where('vcAktif', '1')
            ->whereNull('Tgl_Berhenti');

        if ($request->filled('divisi')) {
            $query->where('Divisi', $request->divisi);
        }

        if ($request->filled('departemen')) {
            $query->where('dept', $request->departemen);
        }

        if ($request->filled('bagian')) {
            $query->where('vcKodeBagian', $request->bagian);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('Nik', 'like', '%' . $search . '%')
                  ->orWhere('Nama', 'like', '%' . $search . '%');
            });
        }

        $karyawans = $query->orderBy('Nama')
            ->get(['Nik', 'Nama', 'Divisi', 'dept', 'vcKodeBagian']);

        return response()->json([
            'success' => true,
            'karyawans' => $karyawans
        ]);
    }

    /**
     * Preview sebelum simpan
     */
    public function preview(Request $request)
    {
        $request->validate([
            'dtTanggalLibur' => 'required|date',
            'dtTanggalKerja' => 'required|date|different:dtTanggalLibur',
            'vcTipeTukar' => 'required|in:LIBUR_KE_KERJA,KERJA_KE_LIBUR',
            'vcScope' => 'required|in:PERORANGAN,GROUP,SEMUA_BU',
        ]);

        $karyawanList = $this->getKaryawanByScope($request);

        return response()->json([
            'success' => true,
            'total_karyawan' => $karyawanList->count(),
            'karyawans' => $karyawanList->map(function ($k) {
                return [
                    'nik' => $k->Nik,
                    'nama' => $k->Nama,
                    'divisi' => $k->divisi->vcNamaDivisi ?? '-',
                ];
            }),
        ]);
    }
}
