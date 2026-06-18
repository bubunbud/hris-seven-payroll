<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Karyawan;
use App\Models\Keluarga;
use App\Models\Pendidikan;
use App\Models\Pelatihan;
use App\Models\Divisi;
use App\Models\Departemen;
use App\Models\Bagian;
use App\Models\Shift;
use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Seksi;
use App\Services\ActivityLogService;

class KaryawanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $karyawans = Karyawan::orderBy('Nik')->get();
        $divisis = Divisi::orderBy('vcKodeDivisi')->get();
        $departemens = Departemen::orderBy('vcKodeDept')->get();
        $bagians = Bagian::orderBy('vcKodeBagian')->get();
        $shifts = Shift::orderBy('vcShift')->get();
        $golongans = Golongan::orderBy('vcKodeGolongan')->get();
        $jabatans = Jabatan::orderBy('vcKodeJabatan')->get();

        // Get distinct Group Pegawai values from database
        $groupPegawais = Karyawan::select('Group_pegawai')
            ->whereNotNull('Group_pegawai')
            ->where('Group_pegawai', '!=', '')
            ->distinct()
            ->orderBy('Group_pegawai')
            ->pluck('Group_pegawai')
            ->filter(function ($value) {
                return !empty(trim($value));
            })
            ->values()
            ->toArray();

        // Get distinct Status Pegawai values from database
        $statusPegawais = Karyawan::select('Status_Pegawai')
            ->whereNotNull('Status_Pegawai')
            ->where('Status_Pegawai', '!=', '')
            ->distinct()
            ->orderBy('Status_Pegawai')
            ->pluck('Status_Pegawai')
            ->filter(function ($value) {
                return !empty(trim($value));
            })
            ->values()
            ->toArray();

        // Default Status Pegawai options if no data exists
        if (empty($statusPegawais)) {
            $statusPegawais = ['Tetap', 'Kontrak', 'Magang', 'Harian', 'Outsourcing'];
        }

        // Tambahkan "Management" ke Group Pegawai jika belum ada (case-insensitive)
        $hasManagement = false;
        foreach ($groupPegawais as $group) {
            if (strtolower($group) === 'management') {
                $hasManagement = true;
                break;
            }
        }
        if (!$hasManagement) {
            $groupPegawais[] = 'Management';
            sort($groupPegawais);
        }

        return view('master.karyawan.index', compact('karyawans', 'divisis', 'departemens', 'bagians', 'shifts', 'golongans', 'jabatans', 'groupPegawais', 'statusPegawais'));
    }

    /**
     * Get jabatans by divisi (filtered by prefix)
     */
    public function getJabatansByDivisi(Request $request)
    {
        $request->validate([
            'divisi' => 'required|string',
        ]);

        // Mapping kode m_divisi → awalan vcKodeJabatan (m_jabatan).
        // Renaltech JRMA | Sugih Export & Produksi 1.1/1.2 JSIA | Sutek JSMU
        $prefixMapping = [
            'RMA' => 'JRMA',       // PT Renaltech Mitra Abadi
            'SIA-CPD' => 'JSIA',   // PT Sugih Instrumendo Abadi, Export
            'SIA-P11' => 'JSIA',   // PT Sugih Instrumendo Abadi, Produksi 1.1
            'SIA-P12' => 'JSIA',   // PT Sugih Instrumendo Abadi, Produksi 1.2
            'SMU' => 'JSMU',       // PT Sutek Mitra Utama
        ];

        $divisi = $request->divisi;
        $jabatans = collect();

        // Jika divisi ada di mapping, filter berdasarkan prefix
        if (isset($prefixMapping[$divisi])) {
            $prefix = $prefixMapping[$divisi];
            $jabatans = Jabatan::where('vcKodeJabatan', 'like', $prefix . '%')
                ->orderBy('vcKodeJabatan')
                ->get(['vcKodeJabatan', 'vcNamaJabatan']);
        } else {
            // Jika divisi tidak ada di mapping, return semua jabatan
            $jabatans = Jabatan::orderBy('vcKodeJabatan')
                ->get(['vcKodeJabatan', 'vcNamaJabatan']);
        }

        return response()->json([
            'success' => true,
            'jabatans' => $jabatans
        ]);
    }

    /**
     * Generate NIK automatically based on year
     * Format: YYYY + 4 digit counter (e.g., 20250001)
     */
    public function generateNik(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer|min:2000|max:2099'
        ]);

        $tahun = $request->tahun;
        $tahunStr = str_pad($tahun, 4, '0', STR_PAD_LEFT);

        // Find the last NIK for this year
        // NIK format: YYYY + 4 digits counter
        $lastNik = Karyawan::where('Nik', 'LIKE', $tahunStr . '%')
            ->whereRaw('LENGTH(Nik) = 8')
            ->whereRaw('SUBSTRING(Nik, 1, 4) = ?', [$tahunStr])
            ->orderBy('Nik', 'desc')
            ->value('Nik');

        // Extract counter from last NIK
        $counter = 1;
        if ($lastNik && strlen($lastNik) == 8) {
            $lastCounter = (int) substr($lastNik, 4, 4);
            $counter = $lastCounter + 1;
        }

        // Generate new NIK: YYYY + counter (4 digits, zero-padded)
        $newNik = $tahunStr . str_pad($counter, 4, '0', STR_PAD_LEFT);

        // Ensure uniqueness (in case of race condition)
        while (Karyawan::where('Nik', $newNik)->exists()) {
            $counter++;
            $newNik = $tahunStr . str_pad($counter, 4, '0', STR_PAD_LEFT);
        }

        return response()->json([
            'success' => true,
            'nik' => $newNik
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'Nik' => 'nullable|string|max:24|unique:m_karyawan,Nik',
            'Nama' => 'required|string|max:150',
            'Job_ID' => 'nullable|string|max:30',
            'Tgl_Masuk' => 'nullable|date',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->all();

        // Auto-generate NIK if not provided
        if (empty($data['Nik']) || trim($data['Nik']) === '') {
            if (empty($data['Tgl_Masuk'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'NIK atau Tanggal Masuk harus diisi untuk generate NIK otomatis.'
                ], 422);
            }

            $tahunMasuk = date('Y', strtotime($data['Tgl_Masuk']));
            $tahunStr = str_pad($tahunMasuk, 4, '0', STR_PAD_LEFT);

            // Find the last NIK for this year
            $lastNik = Karyawan::where('Nik', 'LIKE', $tahunStr . '%')
                ->whereRaw('LENGTH(Nik) = 8')
                ->whereRaw('SUBSTRING(Nik, 1, 4) = ?', [$tahunStr])
                ->orderBy('Nik', 'desc')
                ->value('Nik');

            // Extract counter from last NIK
            $counter = 1;
            if ($lastNik && strlen($lastNik) == 8) {
                $lastCounter = (int) substr($lastNik, 4, 4);
                $counter = $lastCounter + 1;
            }

            // Generate new NIK: YYYY + counter (4 digits, zero-padded)
            $newNik = $tahunStr . str_pad($counter, 4, '0', STR_PAD_LEFT);

            // Ensure uniqueness (in case of race condition)
            while (Karyawan::where('Nik', $newNik)->exists()) {
                $counter++;
                $newNik = $tahunStr . str_pad($counter, 4, '0', STR_PAD_LEFT);
            }

            $data['Nik'] = $newNik;
        }

        $data['dtCreate'] = now();
        $data['dtChange'] = now();
        $data['create_date'] = now();
        $data['update_date'] = now();

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');

            // Simpan dengan nama file = NIK.ext agar konsisten dan mudah direferensikan kembali
            $extension = $photo->getClientOriginalExtension();
            $photoName = $data['Nik'] . '.' . $extension;

            // Simpan ke storage/app/public/photos -> diakses via /storage/photos/{NIK.ext}
            $photo->storeAs('public/photos', $photoName);

            $data['photo'] = $photoName;
        }

        $karyawan = Karyawan::create($data);

        // Log activity
        ActivityLogService::logCreate(
            $karyawan,
            "Menambah karyawan baru: {$karyawan->Nama} (NIK: {$karyawan->Nik})"
        );

        // Catatan: Data keluarga tidak disimpan di sini karena tab Keluarga punya CRUD sendiri
        // Gunakan endpoint terpisah: POST /karyawan/{nik}/keluarga untuk menambah keluarga

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil ditambahkan.',
                'karyawan' => $karyawan
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        return redirect()->route('karyawan.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $karyawan = Karyawan::with(['jabatan', 'shift', 'divisi', 'departemen', 'bagian'])->findOrFail($id);

        return response()->json(
            ['success' => true, 'karyawan' => $karyawan],
            200,
            [],
            JSON_INVALID_UTF8_SUBSTITUTE
        );
    }

    /**
     * Biodata karyawan untuk pratinjau cetak / arsip (layout bergaya CV, satu bagian per halaman).
     */
    public function biodataCetak(string $nik)
    {
        $karyawan = Karyawan::with(['jabatan', 'shift', 'divisi', 'departemen', 'bagian'])->find($nik);
        if (! $karyawan) {
            abort(404, 'Karyawan tidak ditemukan.');
        }

        $keluarga = Keluarga::where('nik', $nik)->orderBy('NamaKeluarga')->get();
        $pendidikan = Pendidikan::where('employee_nik', $nik)->orderBy('education_level')->get();

        $pelatihan = collect();
        if (DB::getSchemaBuilder()->hasTable('t_pelatihan')) {
            $pelatihan = Pelatihan::where('Nik', $nik)->orderByDesc('tg_pelatihan')->get();
        }

        $mutasi = collect();
        if (DB::getSchemaBuilder()->hasTable('t_mutasi')) {
            $mutasi = DB::table('t_mutasi')->where('nik', $nik)->orderByDesc('vcTglSK')->orderByDesc('NoSK')->get();
        }

        $catatanKaryawan = collect();
        if (DB::getSchemaBuilder()->hasTable('t_karyawan_catatan')) {
            try {
                $catatanKaryawan = DB::table('t_karyawan_catatan')
                    ->where('karyawan_nik', $nik)
                    ->orderByDesc('tanggal')
                    ->orderByDesc('id')
                    ->get()
                    ->map(function ($row) {
                        $row->file_url = $this->karyawanCatatanFileUrl($row->file_lampiran ?? null);

                        return $row;
                    });
            } catch (\Throwable $e) {
                Log::warning('biodataCetak catatan karyawan: '.$e->getMessage());
            }
        }

        $golongan = $karyawan->Gol ? Golongan::find($karyawan->Gol) : null;

        $seksi = null;
        if ($karyawan->vcKodeSeksi) {
            $seksi = Seksi::where('vcKodeseksi', $karyawan->vcKodeSeksi)->first();
        }

        $photoUrl = null;
        if ($karyawan->photo) {
            $photoUrl = asset('storage/photos/'.$karyawan->photo);
        }

        // Nama orang tua: eksplisit dari kolom m_karyawan.nama_ayah & m_karyawan.nama_ibu
        $namaAyahOrtu = $karyawan->nama_ayah;
        $namaIbuOrtu = $karyawan->nama_ibu;
        try {
            $sb = DB::getSchemaBuilder();
            $cols = [];
            if ($sb->hasColumn('m_karyawan', 'nama_ayah')) {
                $cols[] = 'nama_ayah';
            }
            if ($sb->hasColumn('m_karyawan', 'nama_ibu')) {
                $cols[] = 'nama_ibu';
            }
            if ($cols !== []) {
                $ortu = DB::table('m_karyawan')->where('Nik', $nik)->first($cols);
                if ($ortu) {
                    if (in_array('nama_ayah', $cols, true)) {
                        $namaAyahOrtu = $ortu->nama_ayah;
                    }
                    if (in_array('nama_ibu', $cols, true)) {
                        $namaIbuOrtu = $ortu->nama_ibu;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('biodataCetak ortu columns: '.$e->getMessage());
        }

        return view('master.karyawan.biodata-cetak', compact(
            'karyawan',
            'keluarga',
            'pendidikan',
            'pelatihan',
            'mutasi',
            'catatanKaryawan',
            'golongan',
            'seksi',
            'photoUrl',
            'namaAyahOrtu',
            'namaIbuOrtu'
        ));
    }

    /**
     * Get family members for a karyawan
     */
    public function getKeluarga(string $id)
    {
        $keluarga = Keluarga::where('nik', $id)->get();

        return response()->json(['success' => true, 'keluarga' => $keluarga]);
    }

    /**
     * Add family member for a karyawan
     */
    public function addFamily(Request $request, string $nik)
    {
        $request->validate([
            'hubKeluarga' => 'required|string|max:50',
            'NamaKeluarga' => 'required|string|max:150',
            'jenKelamin' => 'nullable|string|max:10',
            'temLahir' => 'nullable|string|max:100',
            'tglLahir' => 'nullable|date',
            'golDarah' => 'nullable|string|max:5',
        ]);

        // Check if karyawan exists
        $karyawan = Karyawan::find($nik);
        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan dengan NIK ' . $nik . ' tidak ditemukan.'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        // Check if already exists (composite key: nik + hubKeluarga + NamaKeluarga)
        $exists = DB::table('t_keluarga')
            ->where('nik', $nik)
            ->where('hubKeluarga', $request->hubKeluarga)
            ->where('NamaKeluarga', $request->NamaKeluarga)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Anggota keluarga dengan hubungan dan nama yang sama sudah ada.'
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        try {
            // Insert using DB::table() because of composite primary key
            DB::table('t_keluarga')->insert([
                'nik' => $nik,
                'hubKeluarga' => $request->hubKeluarga,
                'NamaKeluarga' => $request->NamaKeluarga,
                'jenKelamin' => $request->jenKelamin,
                'temLahir' => $request->temLahir,
                'tglLahir' => $request->tglLahir,
                'golDarah' => $request->golDarah,
            ]);

            // Get the inserted record
            $keluarga = DB::table('t_keluarga')
                ->where('nik', $nik)
                ->where('hubKeluarga', $request->hubKeluarga)
                ->where('NamaKeluarga', $request->NamaKeluarga)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Anggota keluarga berhasil ditambahkan.',
                'keluarga' => $keluarga
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error('Error adding family member: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan anggota keluarga: ' . $e->getMessage()
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Update family member for a karyawan
     */
    public function updateFamily(Request $request, string $nik, string $hubKeluarga)
    {
        $request->validate([
            'hubKeluarga' => 'required|string|max:50',
            'NamaKeluarga' => 'required|string|max:150',
            'jenKelamin' => 'nullable|string|max:10',
            'temLahir' => 'nullable|string|max:100',
            'tglLahir' => 'nullable|date',
            'golDarah' => 'nullable|string|max:5',
            'oldNamaKeluarga' => 'required|string|max:150', // Original NamaKeluarga for composite key lookup
        ]);

        // Check if karyawan exists
        $karyawan = Karyawan::find($nik);
        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan dengan NIK ' . $nik . ' tidak ditemukan.'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $oldNamaKeluarga = $request->oldNamaKeluarga;

        // Check if record exists
        $existing = DB::table('t_keluarga')
            ->where('nik', $nik)
            ->where('hubKeluarga', $hubKeluarga)
            ->where('NamaKeluarga', $oldNamaKeluarga)
            ->first();

        if (!$existing) {
            return response()->json([
                'success' => false,
                'message' => 'Data anggota keluarga tidak ditemukan.'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        // If hubKeluarga or NamaKeluarga changed, check for duplicate
        if ($request->hubKeluarga !== $hubKeluarga || $request->NamaKeluarga !== $oldNamaKeluarga) {
            // Check if the new combination already exists
            // Since we're changing the composite key, we need to check if another record
            // with the new combination exists (excluding the current record)
            $duplicate = DB::table('t_keluarga')
                ->where('nik', $nik)
                ->where('hubKeluarga', $request->hubKeluarga)
                ->where('NamaKeluarga', $request->NamaKeluarga)
                ->where(function($query) use ($hubKeluarga, $oldNamaKeluarga) {
                    // Exclude the current record: it has hubKeluarga=$hubKeluarga AND NamaKeluarga=$oldNamaKeluarga
                    // So any record that doesn't match BOTH old values is a different record
                    $query->where('hubKeluarga', '!=', $hubKeluarga)
                          ->orWhere('NamaKeluarga', '!=', $oldNamaKeluarga);
                })
                ->exists();

            if ($duplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anggota keluarga dengan hubungan dan nama yang sama sudah ada.'
                ], 422, [], JSON_UNESCAPED_UNICODE);
            }
        }

        try {
            // If composite key changed, delete old and insert new
            if ($request->hubKeluarga !== $hubKeluarga || $request->NamaKeluarga !== $oldNamaKeluarga) {
                DB::table('t_keluarga')
                    ->where('nik', $nik)
                    ->where('hubKeluarga', $hubKeluarga)
                    ->where('NamaKeluarga', $oldNamaKeluarga)
                    ->delete();

                DB::table('t_keluarga')->insert([
                    'nik' => $nik,
                    'hubKeluarga' => $request->hubKeluarga,
                    'NamaKeluarga' => $request->NamaKeluarga,
                    'jenKelamin' => $request->jenKelamin,
                    'temLahir' => $request->temLahir,
                    'tglLahir' => $request->tglLahir,
                    'golDarah' => $request->golDarah,
                ]);
            } else {
                // Update existing record
                DB::table('t_keluarga')
                    ->where('nik', $nik)
                    ->where('hubKeluarga', $hubKeluarga)
                    ->where('NamaKeluarga', $oldNamaKeluarga)
                    ->update([
                        'jenKelamin' => $request->jenKelamin,
                        'temLahir' => $request->temLahir,
                        'tglLahir' => $request->tglLahir,
                        'golDarah' => $request->golDarah,
                    ]);
            }

            // Get the updated record
            $keluarga = DB::table('t_keluarga')
                ->where('nik', $nik)
                ->where('hubKeluarga', $request->hubKeluarga)
                ->where('NamaKeluarga', $request->NamaKeluarga)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Anggota keluarga berhasil diperbarui.',
                'keluarga' => $keluarga
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error('Error updating family member: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui anggota keluarga: ' . $e->getMessage()
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Delete family member for a karyawan
     */
    public function deleteFamily(string $nik, string $hubKeluarga)
    {
        // Get NamaKeluarga from request (needed for composite key)
        // Since we can't get it from URL, we'll need to modify the route or use request body
        // For now, we'll use a query parameter or request body
        $request = request();
        $namaKeluarga = $request->input('NamaKeluarga') ?? $request->query('NamaKeluarga');

        if (!$namaKeluarga) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter NamaKeluarga diperlukan untuk menghapus data.'
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        // Check if record exists
        $existing = DB::table('t_keluarga')
            ->where('nik', $nik)
            ->where('hubKeluarga', $hubKeluarga)
            ->where('NamaKeluarga', $namaKeluarga)
            ->first();

        if (!$existing) {
            return response()->json([
                'success' => false,
                'message' => 'Data anggota keluarga tidak ditemukan.'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        try {
            $deleted = DB::table('t_keluarga')
                ->where('nik', $nik)
                ->where('hubKeluarga', $hubKeluarga)
                ->where('NamaKeluarga', $namaKeluarga)
                ->delete();

            if ($deleted > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Anggota keluarga berhasil dihapus.'
                ], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus anggota keluarga.'
                ], 500, [], JSON_UNESCAPED_UNICODE);
            }
        } catch (\Exception $e) {
            Log::error('Error deleting family member: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus anggota keluarga: ' . $e->getMessage()
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'Nama' => 'required|string|max:150',
            'Job_ID' => 'nullable|string|max:30',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $karyawan = Karyawan::findOrFail($id);
        
        // Simpan old values SEBELUM update (penting untuk logging)
        // Gunakan getAttributes() dan buat deep copy dengan json_encode/decode untuk memastikan tidak terpengaruh perubahan
        $oldValues = json_decode(json_encode($karyawan->getAttributes()), true);
        
        $data = $request->all();
        $data['dtChange'] = now();
        $data['update_date'] = now();

        // Handle photo removal
        if ($request->has('remove_photo') && $request->remove_photo == '1') {
            if ($karyawan->photo) {
                Storage::delete('public/photos/' . $karyawan->photo);
            }
            $data['photo'] = null;
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($karyawan->photo) {
                Storage::delete('public/photos/' . $karyawan->photo);
            }

            $photo = $request->file('photo');

            // Simpan dengan nama file = NIK.ext
            $extension = $photo->getClientOriginalExtension();
            $photoName = $karyawan->Nik . '.' . $extension;

            $photo->storeAs('public/photos', $photoName);
            $data['photo'] = $photoName;
        }

        $karyawan->update($data);

        // Log activity dengan old values yang sudah disimpan
        ActivityLogService::logUpdate(
            $karyawan,
            "Update data karyawan: {$karyawan->Nama} (NIK: {$karyawan->Nik})",
            $oldValues
        );

        // Catatan: Data keluarga tidak diupdate di sini karena tab Keluarga punya CRUD sendiri
        // Gunakan endpoint terpisah:
        // - POST /karyawan/{nik}/keluarga untuk menambah
        // - PUT /karyawan/{nik}/keluarga/{hubKeluarga} untuk update
        // - DELETE /karyawan/{nik}/keluarga/{hubKeluarga} untuk hapus

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil diperbarui.',
                'karyawan' => $karyawan
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        return redirect()->route('karyawan.index')
            ->with('success', 'Karyawan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $karyawan = Karyawan::findOrFail($id);

            // Log activity (sebelum delete, karena setelah delete data sudah tidak ada)
            ActivityLogService::logDelete(
                $karyawan,
                "Menghapus karyawan: {$karyawan->Nama} (NIK: {$karyawan->Nik})"
            );

            // Delete related family members using DB facade to avoid primary key issues
            try {
                DB::table('t_keluarga')->where('nik', $id)->delete();
            } catch (\Exception $e) {
                // Table mungkin tidak ada, skip
                Log::warning('Table t_keluarga tidak ditemukan atau error saat delete: ' . $e->getMessage());
            }

            // Delete related education records
            try {
                // Cek apakah tabel ada sebelum delete
                if (DB::getSchemaBuilder()->hasTable('t_pendidikan')) {
                    DB::table('t_pendidikan')->where('employee_nik', $id)->delete();
                }
            } catch (\Exception $e) {
                // Table mungkin tidak ada, skip
                Log::warning('Table t_pendidikan tidak ditemukan atau error saat delete: ' . $e->getMessage());
            }

            try {
                if (DB::getSchemaBuilder()->hasTable('t_mutasi')) {
                    $mutasiFiles = DB::table('t_mutasi')->where('nik', $id)->pluck('vcFileSK');
                    foreach ($mutasiFiles as $fn) {
                        $this->deleteMutasiSkFile($fn);
                    }
                    DB::table('t_mutasi')->where('nik', $id)->delete();
                }
            } catch (\Exception $e) {
                Log::warning('Table t_mutasi tidak ditemukan atau error saat delete: ' . $e->getMessage());
            }

            try {
                if (DB::getSchemaBuilder()->hasTable('t_karyawan_catatan')) {
                    $catatanFiles = DB::table('t_karyawan_catatan')->where('karyawan_nik', $id)->pluck('file_lampiran');
                    foreach ($catatanFiles as $fn) {
                        $this->deleteKaryawanCatatanFile($fn);
                    }
                    DB::table('t_karyawan_catatan')->where('karyawan_nik', $id)->delete();
                }
            } catch (\Exception $e) {
                Log::warning('Table t_karyawan_catatan tidak ditemukan atau error saat delete: ' . $e->getMessage());
            }

            $karyawan->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Karyawan berhasil dihapus.'
                ], 200, [], JSON_UNESCAPED_UNICODE);
            }

            return redirect()->route('karyawan.index')
                ->with('success', 'Karyawan berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error deleting karyawan: ' . $e->getMessage(), [
                'nik' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus karyawan: ' . $e->getMessage()
                ], 500, [], JSON_UNESCAPED_UNICODE);
            }

            return redirect()->route('karyawan.index')
                ->with('error', 'Gagal menghapus karyawan: ' . $e->getMessage());
        }
    }

    /**
     * Get departemens by divisi (based on m_hirarki_dept)
     */
    public function getDepartemensByDivisi(Request $request)
    {
        $request->validate([
            'divisi' => 'required|string',
        ]);

        $departemens = DB::table('m_hirarki_dept')
            ->join('m_dept', 'm_hirarki_dept.vcKodeDept', '=', 'm_dept.vcKodeDept')
            ->where('m_hirarki_dept.vcKodeDivisi', $request->divisi)
            ->select('m_dept.vcKodeDept', 'm_dept.vcNamaDept')
            ->orderBy('m_dept.vcKodeDept')
            ->get();

        return response()->json([
            'success' => true,
            'departemens' => $departemens
        ]);
    }

    /**
     * Get bagians by divisi and departemen (based on m_hirarki_bagian)
     */
    public function getBagiansByDivisiDept(Request $request)
    {
        $request->validate([
            'divisi' => 'required|string',
            'departemen' => 'required|string',
        ]);

        $bagians = DB::table('m_hirarki_bagian')
            ->join('m_bagian', 'm_hirarki_bagian.vcKodeBagian', '=', 'm_bagian.vcKodeBagian')
            ->where('m_hirarki_bagian.vcKodeDivisi', $request->divisi)
            ->where('m_hirarki_bagian.vcKodeDept', $request->departemen)
            ->select('m_bagian.vcKodeBagian', 'm_bagian.vcNamaBagian')
            ->orderBy('m_bagian.vcKodeBagian')
            ->get();

        return response()->json([
            'success' => true,
            'bagians' => $bagians
        ]);
    }

    /**
     * Get seksis by divisi, departemen, and bagian (based on m_hirarki_seksi)
     */
    public function getSeksisByDivisiDeptBagian(Request $request)
    {
        $request->validate([
            'divisi' => 'required|string',
            'departemen' => 'required|string',
            'bagian' => 'required|string',
        ]);

        $seksis = DB::table('m_hirarki_seksi')
            ->join('m_seksi', 'm_hirarki_seksi.vcKodeSeksi', '=', 'm_seksi.vcKodeseksi')
            ->where('m_hirarki_seksi.vcKodeDivisi', $request->divisi)
            ->where('m_hirarki_seksi.vcKodeDept', $request->departemen)
            ->where('m_hirarki_seksi.vcKodeBagian', $request->bagian)
            ->select('m_seksi.vcKodeseksi as vcKodeSeksi', 'm_seksi.vcNamaseksi as vcNamaSeksi')
            ->orderBy('m_seksi.vcKodeseksi')
            ->get();

        return response()->json([
            'success' => true,
            'seksis' => $seksis
        ]);
    }

    /**
     * Get karyawan data for copying to new NIK
     * This method returns karyawan data excluding certain fields that should not be copied
     * Also includes keluarga (family) data
     */
    public function getKaryawanForCopy(string $nik)
    {
        $karyawan = Karyawan::with(['keluarga', 'pendidikan'])->find($nik);

        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan dengan NIK ' . $nik . ' tidak ditemukan.'
            ], 404);
        }

        // Convert to array and exclude fields that should not be copied
        $karyawanData = $karyawan->toArray();

        // Fields to exclude (these will be set fresh for new employee)
        $excludeFields = [
            'Nik', // NIK baru akan di-generate
            'dtCreate',
            'dtChange',
            'create_date',
            'update_date', // Timestamps
            'user_create',
            'user_update', // User info
            'photo', // Photo tidak di-copy
            'vcAktif', // Default aktif untuk karyawan baru
            'Tgl_Berhenti', // Tidak perlu copy tanggal berhenti
            'deleted' // Status deleted
        ];

        // Remove excluded fields
        foreach ($excludeFields as $field) {
            unset($karyawanData[$field]);
        }

        // Get keluarga data (exclude nik field, will be set to new NIK later)
        $keluargaData = $karyawan->keluarga->map(function ($item) {
            $data = $item->toArray();
            // Remove nik from keluarga data, will be set to new NIK when saving
            unset($data['nik']);
            return $data;
        })->toArray();

        // Get pendidikan data (exclude employee_nik field, will be set to new NIK later)
        $pendidikanData = [];
        if (DB::getSchemaBuilder()->hasTable('t_pendidikan')) {
            $pendidikanData = $karyawan->pendidikan->map(function ($item) {
                $data = $item->toArray();
                // Remove employee_nik and id from pendidikan data, will be set to new NIK when saving
                unset($data['employee_nik']);
                unset($data['id']); // Remove auto-increment id if exists
                return $data;
            })->toArray();
        }

        // Get pelatihan data (exclude Nik field, will be set to new NIK later)
        $pelatihanData = [];
        if (DB::getSchemaBuilder()->hasTable('t_pelatihan')) {
            $pelatihanData = Pelatihan::where('Nik', $nik)->get()->map(function ($item) {
                $data = $item->toArray();
                unset($data['Nik']);
                unset($data['id']);
                return $data;
            })->toArray();
        }

        return response()->json([
            'success' => true,
            'karyawan' => $karyawanData,
            'keluarga' => $keluargaData,
            'pendidikan' => $pendidikanData,
            'pelatihan' => $pelatihanData,
            'message' => 'Data karyawan, keluarga, pendidikan, dan pelatihan berhasil diambil untuk di-copy.'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Copy keluarga data from old NIK to new NIK
     * This method replicates all keluarga records from old NIK to new NIK
     */
    public function copyKeluarga(Request $request)
    {
        $request->validate([
            'nik_lama' => 'required|string|exists:m_karyawan,Nik',
            'nik_baru' => 'required|string|exists:m_karyawan,Nik',
        ]);

        $nikLama = $request->nik_lama;
        $nikBaru = $request->nik_baru;

        // Get all keluarga data from old NIK
        $keluargaLama = Keluarga::where('nik', $nikLama)->get();

        if ($keluargaLama->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada data keluarga untuk di-copy.',
                'copied' => 0
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        $copied = 0;
        $errors = [];

        foreach ($keluargaLama as $keluarga) {
            try {
                // Check if already exists using 3 fields: nik + hubKeluarga + NamaKeluarga
                // Ini memungkinkan beberapa anggota dengan hubungan yang sama (misalnya 3 anak)
                $existing = Keluarga::where('nik', $nikBaru)
                    ->where('hubKeluarga', $keluarga->hubKeluarga)
                    ->where('NamaKeluarga', $keluarga->NamaKeluarga)
                    ->first();

                if ($existing) {
                    // Skip if already exists (same nik + hubKeluarga + NamaKeluarga)
                    continue;
                }

                // Create new keluarga record with new NIK
                Keluarga::create([
                    'nik' => $nikBaru,
                    'hubKeluarga' => $keluarga->hubKeluarga,
                    'NamaKeluarga' => $keluarga->NamaKeluarga,
                    'jenKelamin' => $keluarga->jenKelamin,
                    'temLahir' => $keluarga->temLahir,
                    'tglLahir' => $keluarga->tglLahir,
                    'golDarah' => $keluarga->golDarah,
                ]);

                $copied++;
            } catch (\Exception $e) {
                $errors[] = [
                    'hubKeluarga' => $keluarga->hubKeluarga,
                    'NamaKeluarga' => $keluarga->NamaKeluarga,
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Data keluarga berhasil di-copy. {$copied} record berhasil di-copy.",
            'copied' => $copied,
            'total' => $keluargaLama->count(),
            'errors' => $errors
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get pendidikan records for a karyawan
     */
    public function getPendidikan(string $id)
    {
        try {
            // Cek apakah tabel ada
            if (!DB::getSchemaBuilder()->hasTable('t_pendidikan')) {
                Log::info("Table t_pendidikan tidak ditemukan untuk NIK: {$id}");
                return response()->json(['success' => true, 'pendidikan' => []]);
            }

            // Query data pendidikan menggunakan employee_nik (bukan nik)
            $pendidikan = DB::table('t_pendidikan')
                ->where('employee_nik', $id)
                ->get();

            Log::info("Pendidikan data untuk NIK {$id}: " . json_encode($pendidikan));

            return response()->json([
                'success' => true,
                'pendidikan' => $pendidikan,
                'count' => $pendidikan->count()
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error("Error getting pendidikan for NIK {$id}: " . $e->getMessage());
            return response()->json([
                'success' => true,
                'pendidikan' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Add pendidikan record for a karyawan
     */
    public function addPendidikan(Request $request, string $nik)
    {
        // Cek apakah tabel ada
        if (!DB::getSchemaBuilder()->hasTable('t_pendidikan')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabel t_pendidikan tidak ditemukan di database.'
            ], 404);
        }

        $request->validate([
            'education_level' => 'required|string|max:50',
            'institution_name' => 'nullable|string|max:150',
            'major' => 'nullable|string|max:75',
            'start_year' => 'nullable|string|max:4',
            'end_year' => 'nullable|string|max:4',
            'gpa' => 'nullable|numeric|min:0|max:4',
        ]);

        // Check if karyawan exists
        $karyawan = Karyawan::find($nik);
        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan dengan NIK ' . $nik . ' tidak ditemukan.'
            ], 404);
        }

        // Create pendidikan data menggunakan employee_nik
        // Pastikan tidak ada duplikasi jenjang pendidikan untuk NIK yang sama
        $exists = Pendidikan::where('employee_nik', $nik)
            ->where('education_level', $request->education_level)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Jenjang pendidikan sudah ada untuk karyawan ini.'
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        $pendidikan = Pendidikan::create([
            'employee_nik' => $nik,
            'education_level' => $request->education_level,
            'institution_name' => $request->institution_name,
            'major' => $request->major,
            'start_year' => $request->start_year,
            'end_year' => $request->end_year,
            'gpa' => $request->gpa,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data pendidikan berhasil ditambahkan.',
            'pendidikan' => $pendidikan
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Update pendidikan record
     */
    public function updatePendidikan(Request $request, string $nik, string $educationLevel)
    {
        // Cek apakah tabel ada
        if (!DB::getSchemaBuilder()->hasTable('t_pendidikan')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabel t_pendidikan tidak ditemukan di database.'
            ], 404);
        }

        $request->validate([
            'education_level' => 'required|string|max:50',
            'institution_name' => 'nullable|string|max:150',
            'major' => 'nullable|string|max:75',
            'start_year' => 'nullable|string|max:4',
            'end_year' => 'nullable|string|max:4',
            'gpa' => 'nullable|numeric|min:0|max:4',
        ]);

        $pendidikan = DB::table('t_pendidikan')
            ->where('employee_nik', $nik)
            ->where('education_level', $educationLevel)
            ->first();

        if (!$pendidikan) {
            return response()->json([
                'success' => false,
                'message' => 'Data pendidikan tidak ditemukan.'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        // Jika jenjang pendidikan berubah, pastikan tidak ada duplikasi
        if ($request->education_level !== $educationLevel) {
            $exists = DB::table('t_pendidikan')
                ->where('employee_nik', $nik)
                ->where('education_level', $request->education_level)
                ->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenjang pendidikan sudah ada untuk karyawan ini.'
                ], 422, [], JSON_UNESCAPED_UNICODE);
            }
        }

        $updateData = [
            'education_level' => $request->education_level,
            'institution_name' => $request->institution_name,
            'major' => $request->major,
            'start_year' => $request->start_year,
            'end_year' => $request->end_year,
            'gpa' => $request->gpa,
        ];

        DB::table('t_pendidikan')
            ->where('employee_nik', $nik)
            ->where('education_level', $educationLevel)
            ->update($updateData);

        $pendidikan = DB::table('t_pendidikan')
            ->where('employee_nik', $nik)
            ->where('education_level', $request->education_level)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Data pendidikan berhasil diperbarui.',
            'pendidikan' => $pendidikan
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Delete pendidikan record
     */
    public function deletePendidikan(string $nik, string $educationLevel)
    {
        // Cek apakah tabel ada
        if (!DB::getSchemaBuilder()->hasTable('t_pendidikan')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabel t_pendidikan tidak ditemukan di database.'
            ], 404);
        }

        $deleted = DB::table('t_pendidikan')
            ->where('employee_nik', $nik)
            ->where('education_level', $educationLevel)
            ->delete();

        if ($deleted === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Data pendidikan tidak ditemukan.'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data pendidikan berhasil dihapus.'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Copy pendidikan data from old NIK to new NIK
     */
    public function copyPendidikan(Request $request)
    {
        $request->validate([
            'nik_lama' => 'required|string|exists:m_karyawan,Nik',
            'nik_baru' => 'required|string|exists:m_karyawan,Nik',
        ]);

        // Cek apakah tabel ada
        if (!DB::getSchemaBuilder()->hasTable('t_pendidikan')) {
            return response()->json([
                'success' => true,
                'message' => 'Tabel t_pendidikan tidak ditemukan, skip copy data pendidikan.',
                'copied' => 0
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        $nikLama = $request->nik_lama;
        $nikBaru = $request->nik_baru;

        // Get all pendidikan data from old NIK menggunakan employee_nik
        $pendidikanLama = Pendidikan::where('employee_nik', $nikLama)->get();

        if ($pendidikanLama->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada data pendidikan untuk di-copy.',
                'copied' => 0
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        $copied = 0;
        $errors = [];

        foreach ($pendidikanLama as $pendidikan) {
            try {
                // Create new pendidikan record with new NIK menggunakan employee_nik
                Pendidikan::create([
                    'employee_nik' => $nikBaru,
                    'education_level' => $pendidikan->education_level,
                    'institution_name' => $pendidikan->institution_name,
                    'major' => $pendidikan->major,
                    'start_year' => $pendidikan->start_year,
                    'end_year' => $pendidikan->end_year,
                    'gpa' => $pendidikan->gpa,
                ]);

                $copied++;
            } catch (\Exception $e) {
                $errors[] = [
                    'education_level' => $pendidikan->education_level,
                    'institution_name' => $pendidikan->institution_name,
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Data pendidikan berhasil di-copy. {$copied} record berhasil di-copy.",
            'copied' => $copied,
            'total' => $pendidikanLama->count(),
            'errors' => $errors
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get pelatihan records for a karyawan
     */
    public function getPelatihan(string $nik)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('t_pelatihan')) {
                Log::info("Table t_pelatihan tidak ditemukan untuk NIK: {$nik}");
                return response()->json(['success' => true, 'pelatihan' => []]);
            }

            $pelatihan = Pelatihan::where('Nik', $nik)->get();

            return response()->json([
                'success' => true,
                'pelatihan' => $pelatihan,
                'count' => $pelatihan->count()
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error("Error getting pelatihan for NIK {$nik}: " . $e->getMessage());
            return response()->json([
                'success' => true,
                'pelatihan' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Add pelatihan record
     */
    public function addPelatihan(Request $request, string $nik)
    {
        if (!DB::getSchemaBuilder()->hasTable('t_pelatihan')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabel t_pelatihan tidak ditemukan di database.'
            ], 404);
        }

        $request->validate([
            'nm_pelatihan' => 'required|string|max:150',
            'penyelenggara' => 'nullable|string|max:150',
            'lokasi' => 'nullable|string|max:150',
            'tg_pelatihan' => 'nullable|date',
            'tg_selesai' => 'nullable|date|after_or_equal:tg_pelatihan',
            'lama' => 'nullable|integer|min:0',
            'sertifikat' => 'nullable|boolean',
            'keterangan' => 'nullable|string'
        ]);

        $karyawan = Karyawan::find($nik);
        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan dengan NIK ' . $nik . ' tidak ditemukan.'
            ], 404);
        }

        $exists = Pelatihan::where('Nik', $nik)
            ->where('nm_pelatihan', $request->nm_pelatihan)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Pelatihan dengan nama yang sama sudah ada untuk karyawan ini.'
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        DB::table('t_pelatihan')->insert([
            'Nik' => $nik,
            'nm_pelatihan' => $request->nm_pelatihan,
            'penyelenggara' => $request->penyelenggara,
            'lokasi' => $request->lokasi,
            'tg_pelatihan' => $request->tg_pelatihan,
            'tg_selesai' => $request->tg_selesai,
            'lama' => $request->lama,
            'Sertifikasi' => $request->sertifikat ? 1 : 0,
            'Keterangan' => $request->keterangan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data pelatihan berhasil ditambahkan.',
            'pelatihan' => DB::table('t_pelatihan')
                ->where('Nik', $nik)
                ->where('nm_pelatihan', $request->nm_pelatihan)
                ->first()
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Update pelatihan record
     */
    public function updatePelatihan(Request $request, string $nik, string $nm_pelatihan_lama)
    {
        // Decode nama pelatihan jika mengandung spasi/karakter encoded
        $nm_pelatihan_lama = urldecode($nm_pelatihan_lama);
        if (!DB::getSchemaBuilder()->hasTable('t_pelatihan')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabel t_pelatihan tidak ditemukan di database.'
            ], 404);
        }

        $request->validate([
            'nm_pelatihan' => 'required|string|max:150',
            'penyelenggara' => 'nullable|string|max:150',
            'lokasi' => 'nullable|string|max:150',
            'tg_pelatihan' => 'nullable|date',
            'tg_selesai' => 'nullable|date|after_or_equal:tg_pelatihan',
            'lama' => 'nullable|integer|min:0',
            'sertifikat' => 'nullable|boolean',
            'keterangan' => 'nullable|string'
        ]);

        // Cek duplikasi jika nama pelatihan berubah
        $exists = Pelatihan::where('Nik', $nik)
            ->where('nm_pelatihan', $request->nm_pelatihan)
            ->where('nm_pelatihan', '!=', $nm_pelatihan_lama)
            ->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Pelatihan dengan nama yang sama sudah ada untuk karyawan ini.'
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        $updated = DB::table('t_pelatihan')
            ->where('Nik', $nik)
            ->where('nm_pelatihan', $nm_pelatihan_lama)
            ->update([
                'nm_pelatihan' => $request->nm_pelatihan,
                'penyelenggara' => $request->penyelenggara,
                'lokasi' => $request->lokasi,
                'tg_pelatihan' => $request->tg_pelatihan,
                'tg_selesai' => $request->tg_selesai,
                'lama' => $request->lama,
                'Sertifikasi' => $request->sertifikat ? 1 : 0,
                'Keterangan' => $request->keterangan,
            ]);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Data pelatihan tidak ditemukan.'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $pelatihanBaru = DB::table('t_pelatihan')
            ->where('Nik', $nik)
            ->where('nm_pelatihan', $request->nm_pelatihan)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Data pelatihan berhasil diperbarui.',
            'pelatihan' => $pelatihanBaru
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Delete pelatihan record
     */
    public function deletePelatihan(string $nik, string $nm_pelatihan)
    {
        // Decode nama pelatihan jika mengandung spasi/karakter encoded
        $nm_pelatihan = urldecode($nm_pelatihan);
        if (!DB::getSchemaBuilder()->hasTable('t_pelatihan')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabel t_pelatihan tidak ditemukan di database.'
            ], 404);
        }

        $deleted = DB::table('t_pelatihan')
            ->where('Nik', $nik)
            ->where('nm_pelatihan', $nm_pelatihan)
            ->delete();

        if ($deleted === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Data pelatihan tidak ditemukan.'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data pelatihan berhasil dihapus.'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Copy pelatihan data from old NIK to new NIK
     */
    public function copyPelatihan(Request $request)
    {
        $request->validate([
            'nik_lama' => 'required|string|exists:m_karyawan,Nik',
            'nik_baru' => 'required|string|exists:m_karyawan,Nik',
        ]);

        if (!DB::getSchemaBuilder()->hasTable('t_pelatihan')) {
            return response()->json([
                'success' => true,
                'message' => 'Tabel t_pelatihan tidak ditemukan, skip copy data pelatihan.',
                'copied' => 0
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        $nikLama = $request->nik_lama;
        $nikBaru = $request->nik_baru;

        $pelatihanLama = Pelatihan::where('Nik', $nikLama)->get();
        if ($pelatihanLama->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada data pelatihan untuk di-copy.',
                'copied' => 0
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        $copied = 0;
        $errors = [];

        foreach ($pelatihanLama as $item) {
            try {
                $exists = Pelatihan::where('Nik', $nikBaru)
                    ->where('training_name', $item->training_name)
                    ->exists();
                if ($exists) {
                    continue;
                }

                Pelatihan::create([
                    'Nik' => $nikBaru,
                    'nm_pelatihan' => $item->nm_pelatihan,
                    'penyelenggara' => $item->penyelenggara,
                    'lokasi' => $item->lokasi,
                    'tg_pelatihan' => $item->tg_pelatihan,
                    'tg_selesai' => $item->tg_selesai,
                    'sertifikat' => $item->sertifikat,
                    'keterangan' => $item->keterangan,
                    'dtCreate' => now(),
                    'dtChange' => now(),
                ]);
                $copied++;
            } catch (\Exception $e) {
                $errors[] = [
                    'training_name' => $item->training_name,
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Data pelatihan berhasil di-copy. {$copied} record berhasil di-copy.",
            'copied' => $copied,
            'total' => $pelatihanLama->count(),
            'errors' => $errors
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Simpan upload dokumen SK mutasi (PDF / gambar) di storage publik.
     */
    private function storeMutasiSkFile(\Illuminate\Http\UploadedFile $file, string $nik, string $noSK): string
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $safeSk = preg_replace('/[^A-Za-z0-9_-]+/', '_', $noSK);
        $safeSk = substr($safeSk, 0, 40);
        $fileName = $nik . '_' . $safeSk . '_' . time() . '_' . substr(sha1((string) microtime(true)), 0, 8) . '.' . $ext;
        $file->storeAs('public/mutasi_sk', $fileName);

        return $fileName;
    }

    private function deleteMutasiSkFile(?string $fileName): void
    {
        if (! $fileName) {
            return;
        }
        Storage::delete('public/mutasi_sk/' . $fileName);
    }

    private function mutasiSkPublicUrl(?string $fileName): ?string
    {
        if (! $fileName) {
            return null;
        }

        return Storage::disk('public')->url('mutasi_sk/' . $fileName);
    }

    /**
     * Riwayat mutasi karyawan (tab Mutasi).
     * t_mutasi: PK (nik, NoSK), vcTglSK, vcDivisi, vcDept, vcbagian, vcSeksi, vcJabatan, vcFileSK.
     */
    public function getMutasi(string $nik)
    {
        try {
            if (! DB::getSchemaBuilder()->hasTable('t_mutasi')) {
                return response()->json(['success' => true, 'mutasi' => []]);
            }

            $rows = DB::table('t_mutasi')
                ->where('nik', $nik)
                ->orderByDesc('vcTglSK')
                ->orderByDesc('NoSK')
                ->get();

            $mutasi = $rows->map(function ($row) {
                $arr = (array) $row;
                $fn = $row->vcFileSK ?? null;
                $arr['sk_file_url'] = $this->mutasiSkPublicUrl($fn);

                return $arr;
            });

            return response()->json([
                'success' => true,
                'mutasi' => $mutasi,
                'count' => $mutasi->count(),
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error("Error getMutasi NIK {$nik}: " . $e->getMessage());

            return response()->json([
                'success' => true,
                'mutasi' => [],
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function addMutasi(Request $request, string $nik)
    {
        if (! DB::getSchemaBuilder()->hasTable('t_mutasi')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabel t_mutasi tidak ditemukan di database.',
            ], 404);
        }

        $request->validate([
            'no_sk' => 'required|string|max:20',
            'vc_tgl_sk' => 'nullable|date',
            'vc_divisi' => 'nullable|string|max:100',
            'vc_dept' => 'nullable|string|max:100',
            'vcbagian' => 'nullable|string|max:100',
            'vc_seksi' => 'nullable|string|max:100',
            'vc_jabatan' => 'nullable|string|max:150',
            'dokumen_sk' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if (! Karyawan::find($nik)) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan dengan NIK ' . $nik . ' tidak ditemukan.',
            ], 404);
        }

        $dup = DB::table('t_mutasi')->where('nik', $nik)->where('NoSK', $request->no_sk)->exists();
        if ($dup) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor SK ini sudah ada untuk karyawan ini.',
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        $vcFileSK = null;
        if ($request->hasFile('dokumen_sk')) {
            $vcFileSK = $this->storeMutasiSkFile($request->file('dokumen_sk'), $nik, $request->no_sk);
        }

        DB::table('t_mutasi')->insert([
            'nik' => $nik,
            'NoSK' => $request->no_sk,
            'vcTglSK' => $request->vc_tgl_sk,
            'vcDivisi' => $request->vc_divisi,
            'vcDept' => $request->vc_dept,
            'vcbagian' => $request->vcbagian,
            'vcSeksi' => $request->vc_seksi,
            'vcJabatan' => $request->vc_jabatan,
            'vcFileSK' => $vcFileSK,
        ]);

        $record = DB::table('t_mutasi')->where('nik', $nik)->where('NoSK', $request->no_sk)->first();
        $recordArr = (array) $record;
        $recordArr['sk_file_url'] = $this->mutasiSkPublicUrl($record->vcFileSK ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Data mutasi berhasil ditambahkan.',
            'record' => $recordArr,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function updateMutasi(Request $request, string $nik, string $noSK)
    {
        if (! DB::getSchemaBuilder()->hasTable('t_mutasi')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabel t_mutasi tidak ditemukan.',
            ], 404);
        }

        $oldNoSK = rawurldecode($noSK);

        $request->validate([
            'no_sk' => 'required|string|max:20',
            'vc_tgl_sk' => 'nullable|date',
            'vc_divisi' => 'nullable|string|max:100',
            'vc_dept' => 'nullable|string|max:100',
            'vcbagian' => 'nullable|string|max:100',
            'vc_seksi' => 'nullable|string|max:100',
            'vc_jabatan' => 'nullable|string|max:150',
            'dokumen_sk' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'remove_sk_file' => 'nullable|boolean',
        ]);

        $exists = DB::table('t_mutasi')->where('nik', $nik)->where('NoSK', $oldNoSK)->first();
        if (! $exists) {
            return response()->json([
                'success' => false,
                'message' => 'Data mutasi tidak ditemukan.',
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $newNoSK = $request->no_sk;
        if ($newNoSK !== $oldNoSK) {
            $dup = DB::table('t_mutasi')->where('nik', $nik)->where('NoSK', $newNoSK)->exists();
            if ($dup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor SK ini sudah ada untuk karyawan ini.',
                ], 422, [], JSON_UNESCAPED_UNICODE);
            }
        }

        $vcFileSK = $exists->vcFileSK ?? null;
        if ($request->boolean('remove_sk_file')) {
            $this->deleteMutasiSkFile($vcFileSK);
            $vcFileSK = null;
        } elseif ($request->hasFile('dokumen_sk')) {
            $this->deleteMutasiSkFile($vcFileSK);
            $vcFileSK = $this->storeMutasiSkFile($request->file('dokumen_sk'), $nik, $newNoSK);
        }

        DB::table('t_mutasi')
            ->where('nik', $nik)
            ->where('NoSK', $oldNoSK)
            ->update([
                'NoSK' => $newNoSK,
                'vcTglSK' => $request->vc_tgl_sk,
                'vcDivisi' => $request->vc_divisi,
                'vcDept' => $request->vc_dept,
                'vcbagian' => $request->vcbagian,
                'vcSeksi' => $request->vc_seksi,
                'vcJabatan' => $request->vc_jabatan,
                'vcFileSK' => $vcFileSK,
            ]);

        $record = DB::table('t_mutasi')->where('nik', $nik)->where('NoSK', $newNoSK)->first();
        $recordArr = (array) $record;
        $recordArr['sk_file_url'] = $this->mutasiSkPublicUrl($record->vcFileSK ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Data mutasi berhasil diperbarui.',
            'record' => $recordArr,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function deleteMutasi(string $nik, string $noSK)
    {
        if (! DB::getSchemaBuilder()->hasTable('t_mutasi')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabel t_mutasi tidak ditemukan.',
            ], 404);
        }

        $noSK = rawurldecode($noSK);

        $row = DB::table('t_mutasi')
            ->where('nik', $nik)
            ->where('NoSK', $noSK)
            ->first();

        if (! $row) {
            return response()->json([
                'success' => false,
                'message' => 'Data mutasi tidak ditemukan.',
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $this->deleteMutasiSkFile($row->vcFileSK ?? null);

        DB::table('t_mutasi')
            ->where('nik', $nik)
            ->where('NoSK', $noSK)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data mutasi berhasil dihapus.',
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function copyMutasi(Request $request)
    {
        $request->validate([
            'nik_lama' => 'required|string|exists:m_karyawan,Nik',
            'nik_baru' => 'required|string|exists:m_karyawan,Nik',
        ]);

        if (! DB::getSchemaBuilder()->hasTable('t_mutasi')) {
            return response()->json([
                'success' => true,
                'message' => 'Tabel t_mutasi tidak ditemukan, skip copy mutasi.',
                'copied' => 0,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        $rows = DB::table('t_mutasi')->where('nik', $request->nik_lama)->orderBy('NoSK')->get();
        if ($rows->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada data mutasi untuk di-copy.',
                'copied' => 0,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        $copied = 0;
        foreach ($rows as $row) {
            try {
                $exists = DB::table('t_mutasi')->where('nik', $request->nik_baru)->where('NoSK', $row->NoSK)->exists();
                if ($exists) {
                    continue;
                }
                $newFileName = null;
                $oldFile = $row->vcFileSK ?? null;
                if ($oldFile && Storage::exists('public/mutasi_sk/' . $oldFile)) {
                    $ext = pathinfo($oldFile, PATHINFO_EXTENSION);
                    $safeSk = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) $row->NoSK);
                    $safeSk = substr($safeSk, 0, 40);
                    $newFileName = $request->nik_baru . '_' . $safeSk . '_' . time() . '_' . substr(sha1(uniqid((string) $row->NoSK, true)), 0, 8) . '.' . $ext;
                    Storage::copy('public/mutasi_sk/' . $oldFile, 'public/mutasi_sk/' . $newFileName);
                }
                DB::table('t_mutasi')->insert([
                    'nik' => $request->nik_baru,
                    'NoSK' => $row->NoSK,
                    'vcTglSK' => $row->vcTglSK,
                    'vcDivisi' => $row->vcDivisi,
                    'vcDept' => $row->vcDept,
                    'vcbagian' => $row->vcbagian,
                    'vcSeksi' => $row->vcSeksi,
                    'vcJabatan' => $row->vcJabatan ?? null,
                    'vcFileSK' => $newFileName,
                ]);
                $copied++;
            } catch (\Exception $e) {
                Log::warning('copyMutasi row error: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Data mutasi berhasil di-copy. {$copied} record berhasil di-copy.",
            'copied' => $copied,
            'total' => $rows->count(),
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    private function storeKaryawanCatatanFile(\Illuminate\Http\UploadedFile $file, string $nik, int $catatanId): string
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $fileName = $nik . '_catatan_' . $catatanId . '_' . time() . '_' . substr(sha1((string) microtime(true)), 0, 6) . '.' . $ext;
        $file->storeAs('public/karyawan_catatan', $fileName);

        return $fileName;
    }

    private function deleteKaryawanCatatanFile(?string $fileName): void
    {
        if (! $fileName) {
            return;
        }
        Storage::delete('public/karyawan_catatan/' . $fileName);
    }

    private function karyawanCatatanFileUrl(?string $fileName): ?string
    {
        if (! $fileName) {
            return null;
        }

        return Storage::disk('public')->url('karyawan_catatan/' . $fileName);
    }

    /**
     * Tab Catatan Karyawan — riwayat disiplin & penghargaan (t_karyawan_catatan).
     */
    public function getKaryawanCatatan(string $nik)
    {
        try {
            if (! DB::getSchemaBuilder()->hasTable('t_karyawan_catatan')) {
                return response()->json(['success' => true, 'catatan' => []]);
            }

            $rows = DB::table('t_karyawan_catatan')
                ->where('karyawan_nik', $nik)
                ->orderByDesc('tanggal')
                ->orderByDesc('id')
                ->get();

            $catatan = $rows->map(function ($row) {
                $arr = (array) $row;
                $arr['file_url'] = $this->karyawanCatatanFileUrl($row->file_lampiran ?? null);

                return $arr;
            });

            return response()->json([
                'success' => true,
                'catatan' => $catatan,
                'count' => $catatan->count(),
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error("Error getKaryawanCatatan NIK {$nik}: " . $e->getMessage());

            return response()->json([
                'success' => true,
                'catatan' => [],
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function addKaryawanCatatan(Request $request, string $nik)
    {
        if (! DB::getSchemaBuilder()->hasTable('t_karyawan_catatan')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabel t_karyawan_catatan tidak ditemukan.',
            ], 404);
        }

        if (! Karyawan::find($nik)) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan dengan NIK ini tidak ditemukan.',
            ], 404);
        }

        $request->validate([
            'tanggal' => 'nullable|date',
            'jenis' => 'required|string|in:SP,Teguran,Peringatan Lisan,Penghargaan,Catatan,Pelanggaran',
            'kategori' => 'required|string|in:Disiplin,Penghargaan,Informasi',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'level' => 'required|string|in:SP1,SP2,SP3,Non-SP',
            'status' => 'required|string|in:Aktif,Selesai,Dibatalkan',
            'no_dokumen' => 'nullable|string|max:100',
            'tanggal_berlaku' => 'nullable|date',
            'tanggal_berakhir' => 'nullable|date',
            'file_lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $id = DB::table('t_karyawan_catatan')->insertGetId([
            'karyawan_nik' => $nik,
            'tanggal' => $request->tanggal,
            'jenis' => $request->jenis,
            'kategori' => $request->kategori,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'level' => $request->level,
            'status' => $request->status,
            'no_dokumen' => $request->no_dokumen,
            'file_lampiran' => null,
            'tanggal_berlaku' => $request->tanggal_berlaku,
            'tanggal_berakhir' => $request->tanggal_berakhir,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->hasFile('file_lampiran')) {
            $fn = $this->storeKaryawanCatatanFile($request->file('file_lampiran'), $nik, $id);
            DB::table('t_karyawan_catatan')->where('id', $id)->update([
                'file_lampiran' => $fn,
                'updated_at' => now(),
            ]);
        }

        $record = DB::table('t_karyawan_catatan')->where('id', $id)->first();
        $recArr = (array) $record;
        $recArr['file_url'] = $this->karyawanCatatanFileUrl($record->file_lampiran ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Catatan berhasil ditambahkan.',
            'record' => $recArr,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function updateKaryawanCatatan(Request $request, string $nik, string $id)
    {
        if (! DB::getSchemaBuilder()->hasTable('t_karyawan_catatan')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabel t_karyawan_catatan tidak ditemukan.',
            ], 404);
        }

        $catatanId = (int) $id;
        $exists = DB::table('t_karyawan_catatan')->where('id', $catatanId)->where('karyawan_nik', $nik)->first();
        if (! $exists) {
            return response()->json([
                'success' => false,
                'message' => 'Data catatan tidak ditemukan.',
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $request->validate([
            'tanggal' => 'nullable|date',
            'jenis' => 'required|string|in:SP,Teguran,Peringatan Lisan,Penghargaan,Catatan,Pelanggaran',
            'kategori' => 'required|string|in:Disiplin,Penghargaan,Informasi',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'level' => 'required|string|in:SP1,SP2,SP3,Non-SP',
            'status' => 'required|string|in:Aktif,Selesai,Dibatalkan',
            'no_dokumen' => 'nullable|string|max:100',
            'tanggal_berlaku' => 'nullable|date',
            'tanggal_berakhir' => 'nullable|date',
            'file_lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'remove_file_lampiran' => 'nullable|boolean',
        ]);

        $fileName = $exists->file_lampiran ?? null;
        if ($request->boolean('remove_file_lampiran')) {
            $this->deleteKaryawanCatatanFile($fileName);
            $fileName = null;
        } elseif ($request->hasFile('file_lampiran')) {
            $this->deleteKaryawanCatatanFile($fileName);
            $fileName = $this->storeKaryawanCatatanFile($request->file('file_lampiran'), $nik, $catatanId);
        }

        DB::table('t_karyawan_catatan')->where('id', $catatanId)->update([
            'tanggal' => $request->tanggal,
            'jenis' => $request->jenis,
            'kategori' => $request->kategori,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'level' => $request->level,
            'status' => $request->status,
            'no_dokumen' => $request->no_dokumen,
            'file_lampiran' => $fileName,
            'tanggal_berlaku' => $request->tanggal_berlaku,
            'tanggal_berakhir' => $request->tanggal_berakhir,
            'updated_at' => now(),
        ]);

        $record = DB::table('t_karyawan_catatan')->where('id', $catatanId)->first();
        $recArr = (array) $record;
        $recArr['file_url'] = $this->karyawanCatatanFileUrl($record->file_lampiran ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Catatan berhasil diperbarui.',
            'record' => $recArr,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function deleteKaryawanCatatan(string $nik, string $id)
    {
        if (! DB::getSchemaBuilder()->hasTable('t_karyawan_catatan')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabel tidak ditemukan.',
            ], 404);
        }

        $catatanId = (int) $id;
        $row = DB::table('t_karyawan_catatan')->where('id', $catatanId)->where('karyawan_nik', $nik)->first();
        if (! $row) {
            return response()->json([
                'success' => false,
                'message' => 'Data catatan tidak ditemukan.',
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $this->deleteKaryawanCatatanFile($row->file_lampiran ?? null);
        DB::table('t_karyawan_catatan')->where('id', $catatanId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Catatan berhasil dihapus.',
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function copyKaryawanCatatan(Request $request)
    {
        $request->validate([
            'nik_lama' => 'required|string|exists:m_karyawan,Nik',
            'nik_baru' => 'required|string|exists:m_karyawan,Nik',
        ]);

        if (! DB::getSchemaBuilder()->hasTable('t_karyawan_catatan')) {
            return response()->json([
                'success' => true,
                'message' => 'Tabel catatan tidak ditemukan, skip copy.',
                'copied' => 0,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        $rows = DB::table('t_karyawan_catatan')->where('karyawan_nik', $request->nik_lama)->orderBy('id')->get();
        if ($rows->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada catatan untuk di-copy.',
                'copied' => 0,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        $copied = 0;
        foreach ($rows as $row) {
            try {
                $newId = DB::table('t_karyawan_catatan')->insertGetId([
                    'karyawan_nik' => $request->nik_baru,
                    'tanggal' => $row->tanggal,
                    'jenis' => $row->jenis,
                    'kategori' => $row->kategori,
                    'judul' => $row->judul,
                    'deskripsi' => $row->deskripsi,
                    'level' => $row->level,
                    'status' => $row->status,
                    'no_dokumen' => $row->no_dokumen,
                    'file_lampiran' => null,
                    'tanggal_berlaku' => $row->tanggal_berlaku,
                    'tanggal_berakhir' => $row->tanggal_berakhir,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $oldFile = $row->file_lampiran ?? null;
                if ($oldFile && Storage::exists('public/karyawan_catatan/' . $oldFile)) {
                    $ext = pathinfo($oldFile, PATHINFO_EXTENSION);
                    $newFile = $request->nik_baru . '_catatan_' . $newId . '_' . time() . '_' . substr(sha1(uniqid((string) $row->id, true)), 0, 8) . '.' . $ext;
                    Storage::copy('public/karyawan_catatan/' . $oldFile, 'public/karyawan_catatan/' . $newFile);
                    DB::table('t_karyawan_catatan')->where('id', $newId)->update(['file_lampiran' => $newFile, 'updated_at' => now()]);
                }
                $copied++;
            } catch (\Exception $e) {
                Log::warning('copyKaryawanCatatan row error: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Catatan karyawan berhasil di-copy. {$copied} record.",
            'copied' => $copied,
            'total' => $rows->count(),
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Search karyawan by NIK or Nama (for autocomplete)
     * Returns active employees matching the search query
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $limit = $request->get('limit', 20);

            if (empty(trim($query))) {
                return response()->json([
                    'success' => true,
                    'karyawans' => []
                ], 200, [], JSON_UNESCAPED_UNICODE);
            }

            $karyawans = Karyawan::where('vcAktif', '1')
                ->whereNull('Tgl_Berhenti')
                ->where(function ($q) use ($query) {
                    $q->where('Nik', 'LIKE', '%' . $query . '%')
                      ->orWhere('Nama', 'LIKE', '%' . $query . '%');
                })
                ->with(['divisi', 'bagian'])
                ->orderBy('Nik')
                ->limit($limit)
                ->get()
                ->map(function ($karyawan) {
                    return [
                        'nik' => $karyawan->Nik ?? '',
                        'nama' => $karyawan->Nama ?? '',
                        'divisi' => $karyawan->divisi->vcNamaDivisi ?? ($karyawan->Divisi ?? '-'),
                        'bagian' => $karyawan->bagian->vcNamaBagian ?? '-',
                    ];
                });

            return response()->json([
                'success' => true,
                'karyawans' => $karyawans
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            \Log::error('Error in KaryawanController@search: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error searching karyawan: ' . $e->getMessage(),
                'karyawans' => []
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
