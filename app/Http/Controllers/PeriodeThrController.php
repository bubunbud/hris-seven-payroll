<?php

namespace App\Http\Controllers;

use App\Models\PeriodeThr;
use App\Models\Divisi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PeriodeThrController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil semua periode THR dengan relasi divisi
        $periodes = PeriodeThr::with('divisi')
            ->orderBy('dtPeriode', 'desc')
            ->orderBy('dtKategori', 'asc')
            ->orderBy('vcKodeDivisi', 'asc')
            ->get();

        // Ambil daftar divisi untuk checkbox
        try {
            $divisis = Divisi::orderBy('vcKodeDivisi')->get();
        } catch (\Exception $e) {
            $divisis = collect(); // Jika tabel tidak ada, return empty collection
        }

        // Ambil periode terakhir untuk default tahun
        $periodeTerakhir = PeriodeThr::orderBy('dtPeriode', 'desc')
            ->orderBy('dtCutoffTHR', 'desc')
            ->first();

        $defaultTahun = null;
        if ($periodeTerakhir) {
            // Default tahun = tahun periode terakhir
            $defaultTahun = $periodeTerakhir->dtPeriode;
        } else {
            // Jika belum ada, default tahun = tahun sekarang
            $defaultTahun = Carbon::now()->format('Y');
        }

        return view('proses.periode-thr.index', compact('periodes', 'divisis', 'defaultTahun'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'dtPeriode' => 'required|string|size:4|regex:/^\d{4}$/',
            'dtCutoffTHR' => 'required|date',
            'dtKategori' => 'required|string|in:Islam (Idul Fitri),Kristen (Natal),Hindu (Nyepi),Budha (Waisak),Lainnya',
            'vcNamaHariRaya' => 'nullable|string|max:100',
            'divisi' => 'required|array|min:1',
            'divisi.*' => 'exists:m_divisi,vcKodeDivisi',
            'vcKeterangan' => 'nullable|string|max:255',
        ], [
            'dtPeriode.required' => 'Tahun periode harus diisi',
            'dtPeriode.size' => 'Tahun periode harus 4 digit',
            'dtPeriode.regex' => 'Tahun periode harus berupa angka 4 digit',
            'dtCutoffTHR.required' => 'Tanggal Cutoff THR harus diisi',
            'dtCutoffTHR.date' => 'Format tanggal Cutoff THR tidak valid',
            'dtKategori.required' => 'Kategori harus diisi',
            'dtKategori.in' => 'Kategori tidak valid',
            'divisi.required' => 'Pilih minimal 1 divisi',
            'divisi.min' => 'Pilih minimal 1 divisi',
            'divisi.*.exists' => 'Divisi yang dipilih tidak valid',
        ]);

        // Mapping field dari form ke database
        $dtPeriode = $request->dtPeriode; // Tahun, contoh: "2025"
        $dtCutoffTHR = $request->dtCutoffTHR;
        $dtKategori = $request->dtKategori;
        $vcNamaHariRaya = $request->vcNamaHariRaya;
        $vcKeterangan = $request->vcKeterangan;

        DB::beginTransaction();
        try {
            $created = 0;
            $updated = 0;

            foreach ($request->divisi as $kodeDivisi) {
                // Cek apakah periode sudah ada
                $existing = PeriodeThr::where('dtPeriode', $dtPeriode)
                    ->where('dtKategori', $dtKategori)
                    ->where('vcKodeDivisi', $kodeDivisi)
                    ->first();

                if ($existing) {
                    // Update jika sudah ada - hanya update jika belum diproses
                    if ($existing->vcStatus == '0') {
                        DB::table('t_periode_thr')
                            ->where('dtPeriode', $dtPeriode)
                            ->where('dtKategori', $dtKategori)
                            ->where('vcKodeDivisi', $kodeDivisi)
                            ->update([
                                'dtCutoffTHR' => $dtCutoffTHR,
                                'vcNamaHariRaya' => $vcNamaHariRaya,
                                'vcKeterangan' => $vcKeterangan,
                                // Status tetap '0' jika belum diproses
                            ]);
                        $updated++;
                    }
                } else {
                    // Create baru - status default = '0' (belum diproses) agar bisa dihapus
                    PeriodeThr::create([
                        'dtPeriode' => $dtPeriode,
                        'dtKategori' => $dtKategori,
                        'vcNamaHariRaya' => $vcNamaHariRaya,
                        'vcKodeDivisi' => $kodeDivisi,
                        'dtCutoffTHR' => $dtCutoffTHR,
                        'vcKeterangan' => $vcKeterangan,
                        'vcStatus' => '0', // Default: belum diproses (bisa dihapus)
                        'dtCreate' => Carbon::now(),
                    ]);
                    $created++;
                }
            }

            DB::commit();

            $message = "Periode THR berhasil dibuat. ";
            $message .= $created > 0 ? "Ditambahkan: {$created} divisi. " : "";
            $message .= $updated > 0 ? "Diupdate: {$updated} divisi." : "";

            return response()->json([
                'success' => true,
                'message' => $message,
                'created' => $created,
                'updated' => $updated,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating periode THR: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        try {
            $request->validate([
                'dtPeriode' => 'required|string|size:4|regex:/^\d{4}$/',
                'dtKategori' => 'required|string',
                'vcKodeDivisi' => 'required|string',
            ], [
                'dtPeriode.required' => 'Tahun periode harus diisi',
                'dtPeriode.size' => 'Tahun periode harus 4 digit',
                'dtPeriode.regex' => 'Tahun periode harus berupa angka 4 digit',
                'dtKategori.required' => 'Kategori harus diisi',
                'vcKodeDivisi.required' => 'Kode divisi harus diisi',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $errors)
            ], 422);
        }

        try {
            // Cek apakah periode sudah diproses (vcStatus = '1')
            $periode = PeriodeThr::where('dtPeriode', $request->dtPeriode)
                ->where('dtKategori', $request->dtKategori)
                ->where('vcKodeDivisi', $request->vcKodeDivisi)
                ->first();

            if (!$periode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data periode tidak ditemukan'
                ], 404);
            }

            if ($periode->vcStatus == '1') {
                return response()->json([
                    'success' => false,
                    'message' => 'Periode yang sudah diproses tidak bisa dihapus'
                ], 403);
            }

            // Hapus data
            $deleted = DB::table('t_periode_thr')
                ->where('dtPeriode', $request->dtPeriode)
                ->where('dtKategori', $request->dtKategori)
                ->where('vcKodeDivisi', $request->vcKodeDivisi)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Periode THR berhasil dihapus'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus data periode'
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Error deleting periode THR: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            $errorMessage = 'Terjadi kesalahan: ' . $e->getMessage();
            // Jangan tampilkan detail error di production
            if (config('app.debug')) {
                $errorMessage .= ' (File: ' . basename($e->getFile()) . ', Line: ' . $e->getLine() . ')';
            }
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 500);
        }
    }
}
