<?php

namespace App\Http\Controllers;

use App\Models\PeriodeGaji;
use App\Models\Divisi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PeriodeGajiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil semua periode dengan relasi divisi
        $periodes = PeriodeGaji::with('divisi')
            ->orderBy('periode', 'desc')
            ->orderBy('vcQuarter', 'asc')
            ->orderBy('vcKodeDivisi', 'asc')
            ->get();

        // Ambil daftar divisi untuk checkbox
        try {
            $divisis = Divisi::orderBy('vcKodeDivisi')->get();
        } catch (\Exception $e) {
            $divisis = collect(); // Jika tabel tidak ada, return empty collection
        }

        // Ambil periode terakhir untuk default tanggal awal dan periode closing
        $periodeTerakhir = PeriodeGaji::orderBy('periode', 'desc')
            ->orderBy('vcQuarter', 'desc')
            ->orderBy('dtPeriodeTo', 'desc')
            ->first();

        $defaultTanggalAwal = null;
        $defaultPeriodeClosing = null;

        if ($periodeTerakhir) {
            // Tanggal awal default = tanggal setelah tanggal akhir periode sebelumnya
            $defaultTanggalAwal = Carbon::parse($periodeTerakhir->dtPeriodeTo)->addDay()->format('Y-m-d');
            
            // Periode closing berikutnya (jika periode sebelumnya 1, maka sekarang 2, dan sebaliknya)
            $periodeClosingSebelumnya = (int) $periodeTerakhir->vcQuarter;
            $defaultPeriodeClosing = $periodeClosingSebelumnya == 1 ? 2 : 1;
        }

        return view('proses.periode-gaji.index', compact('periodes', 'divisis', 'defaultTanggalAwal', 'defaultPeriodeClosing'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'dtTanggalAwal' => 'required|date',
            'dtTanggalAkhir' => 'required|date|after_or_equal:dtTanggalAwal',
            'dtPeriode' => 'required|date',
            'intPeriodeClosing' => 'required|integer|in:1,2',
            'divisi' => 'required|array|min:1',
            'divisi.*' => 'exists:m_divisi,vcKodeDivisi',
        ]);

        // Mapping field dari form ke database
        $dtPeriodeFrom = $request->dtTanggalAwal;
        $dtPeriodeTo = $request->dtTanggalAkhir;
        $periode = $request->dtPeriode;
        $vcQuarter = (string) $request->intPeriodeClosing;

        DB::beginTransaction();
        try {
            $created = 0;
            $updated = 0;

            foreach ($request->divisi as $kodeDivisi) {
                // Cek apakah periode sudah ada
                $existing = PeriodeGaji::where('dtPeriodeFrom', $dtPeriodeFrom)
                    ->where('dtPeriodeTo', $dtPeriodeTo)
                    ->where('periode', $periode)
                    ->where('vcQuarter', $vcQuarter)
                    ->where('vcKodeDivisi', $kodeDivisi)
                    ->first();

                if ($existing) {
                    // Update jika sudah ada - hanya update status jika belum diproses (biarkan status yang sudah diproses tetap)
                    DB::table('t_periode')
                        ->where('dtPeriodeFrom', $dtPeriodeFrom)
                        ->where('dtPeriodeTo', $dtPeriodeTo)
                        ->where('periode', $periode)
                        ->where('vcQuarter', $vcQuarter)
                        ->where('vcKodeDivisi', $kodeDivisi)
                        ->update([
                            'vcStatus' => '0', // Reset ke belum diproses jika diupdate manual
                        ]);
                    $updated++;
                } else {
                    // Create baru - status default = '0' (belum diproses) agar bisa dihapus
                    PeriodeGaji::create([
                        'dtPeriodeFrom' => $dtPeriodeFrom,
                        'dtPeriodeTo' => $dtPeriodeTo,
                        'periode' => $periode,
                        'vcQuarter' => $vcQuarter,
                        'vcKodeDivisi' => $kodeDivisi,
                        'vcStatus' => '0', // Default: belum diproses (bisa dihapus)
                        'dtCreate' => Carbon::now(),
                    ]);
                    $created++;
                }
            }

            DB::commit();

            $message = "Periode gaji berhasil dibuat. ";
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
            \Log::error('Error creating periode gaji: ' . $e->getMessage());
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
                'dtTanggalAwal' => 'required|date',
                'dtTanggalAkhir' => 'required|date',
                'dtPeriode' => 'required|date',
                'intPeriodeClosing' => 'required|integer|in:1,2',
                'vcKodeDivisi' => 'required|string',
            ], [
                'dtTanggalAwal.required' => 'Tanggal awal harus diisi',
                'dtTanggalAwal.date' => 'Format tanggal awal tidak valid',
                'dtTanggalAkhir.required' => 'Tanggal akhir harus diisi',
                'dtTanggalAkhir.date' => 'Format tanggal akhir tidak valid',
                'dtPeriode.required' => 'Periode harus diisi',
                'dtPeriode.date' => 'Format periode tidak valid',
                'intPeriodeClosing.required' => 'Periode closing harus diisi',
                'intPeriodeClosing.in' => 'Periode closing harus 1 atau 2',
                'vcKodeDivisi.required' => 'Kode divisi harus diisi',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $errors)
            ], 422);
        }

        $vcQuarter = (string) $request->intPeriodeClosing;
        
        // Pastikan format tanggal konsisten
        $dtTanggalAwal = Carbon::parse($request->dtTanggalAwal)->format('Y-m-d');
        $dtTanggalAkhir = Carbon::parse($request->dtTanggalAkhir)->format('Y-m-d');
        $dtPeriode = Carbon::parse($request->dtPeriode)->format('Y-m-d');

        try {
            // Cek apakah periode sudah diproses (vcStatus = '1')
            $periode = PeriodeGaji::where('dtPeriodeFrom', $dtTanggalAwal)
                ->where('dtPeriodeTo', $dtTanggalAkhir)
                ->where('periode', $dtPeriode)
                ->where('vcQuarter', $vcQuarter)
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
            $deleted = DB::table('t_periode')
                ->where('dtPeriodeFrom', $dtTanggalAwal)
                ->where('dtPeriodeTo', $dtTanggalAkhir)
                ->where('periode', $dtPeriode)
                ->where('vcQuarter', $vcQuarter)
                ->where('vcKodeDivisi', $request->vcKodeDivisi)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Periode gaji berhasil dihapus'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus data periode'
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Error deleting periode gaji: ' . $e->getMessage(), [
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

