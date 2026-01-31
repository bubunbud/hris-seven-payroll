<?php

namespace App\Http\Controllers;

use App\Models\HutangPiutang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TarikDataHutangPiutangController extends Controller
{
    /**
     * Menampilkan form tarik data hutang piutang
     */
    public function index()
    {
        return view('tarik-data-hutang-piutang.index');
    }

    /**
     * Proses tarik data hutang piutang dari server remote
     */
    public function pullData(Request $request)
    {
        $request->validate([
            'dari_tanggal' => 'required|date',
            'sampai_tanggal' => 'required|date|after_or_equal:dari_tanggal',
            'server_host' => 'required|string',
            'server_database' => 'required|string',
            'server_table' => 'required|string',
            'server_user' => 'required|string',
            'server_password' => 'required|string',
            'server_port' => 'nullable|integer|min:1|max:65535',
            'fields' => 'required|array|min:1',
            'fields.*' => 'required|string',
        ], [
            'dari_tanggal.required' => 'Tanggal mulai harus diisi',
            'sampai_tanggal.required' => 'Tanggal akhir harus diisi',
            'sampai_tanggal.after_or_equal' => 'Tanggal akhir harus lebih besar atau sama dengan tanggal mulai',
            'server_host.required' => 'Host server harus diisi',
            'server_database.required' => 'Nama database harus diisi',
            'server_table.required' => 'Nama tabel harus diisi',
            'server_user.required' => 'Username harus diisi',
            'server_password.required' => 'Password harus diisi',
            'fields.required' => 'Minimal pilih 1 field',
            'fields.min' => 'Minimal pilih 1 field',
        ]);

        $dariTanggal = $request->dari_tanggal;
        $sampaiTanggal = $request->sampai_tanggal;
        $fields = $request->fields;

        try {
            // Konfigurasi database remote dari form
            $remoteConfig = [
                'host' => $request->server_host,
                'database' => $request->server_database,
                'username' => $request->server_user,
                'password' => $request->server_password,
                'port' => $request->server_port ?? 3306,
            ];

            // Koneksi ke database remote
            $remoteConnection = $this->connectToRemoteDatabase($remoteConfig);

            if (!$remoteConnection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal terhubung ke server remote. Pastikan server dapat diakses dan kredensial benar.'
                ], 500);
            }

            // Query data dari server remote - ambil field yang dipilih
            // Filter berdasarkan overlap periode (sama seperti di HutangPiutangController)
            $query = DB::connection('remote_mysql')
                ->table($request->server_table)
                ->where(function ($q) use ($dariTanggal, $sampaiTanggal) {
                    $q->where(function ($qq) use ($dariTanggal, $sampaiTanggal) {
                        // Periode awal dalam range filter
                        $qq->whereBetween('dtTanggalAwal', [$dariTanggal, $sampaiTanggal]);
                    })->orWhere(function ($qq) use ($dariTanggal, $sampaiTanggal) {
                        // Periode akhir dalam range filter
                        $qq->whereBetween('dtTanggalAkhir', [$dariTanggal, $sampaiTanggal]);
                    })->orWhere(function ($qq) use ($dariTanggal, $sampaiTanggal) {
                        // Periode mencakup seluruh range filter
                        $qq->where('dtTanggalAwal', '<=', $dariTanggal)
                           ->where('dtTanggalAkhir', '>=', $sampaiTanggal);
                    });
                })
                ->select($fields);

            $remoteData = $query->get();

            if ($remoteData->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data hutang piutang ditemukan untuk periode yang dipilih.'
                ], 404);
            }

            // Proses insert/update data ke database lokal
            $inserted = 0;
            $updated = 0;
            $skipped = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($remoteData as $remoteRow) {
                try {
                    // Siapkan data untuk insert/update
                    $data = [];

                    // Tambahkan field yang dipilih dari remote data
                    foreach ($fields as $field) {
                        $data[$field] = $remoteRow->$field ?? null;
                    }

                    // Ambil nilai composite key dari data yang sudah disiapkan
                    $dtTanggalAwal = $data['dtTanggalAwal'] ?? null;
                    $dtTanggalAkhir = $data['dtTanggalAkhir'] ?? null;
                    $vcNik = $data['vcNik'] ?? null;
                    $vcJenis = $data['vcJenis'] ?? null;

                    // Validasi composite key: hanya jika field composite key dipilih user
                    $compositeKeyFields = ['dtTanggalAwal', 'dtTanggalAkhir', 'vcNik', 'vcJenis'];
                    $missingCompositeKey = [];
                    
                    foreach ($compositeKeyFields as $keyField) {
                        // Jika field composite key dipilih user tapi nilainya kosong/null
                        if (in_array($keyField, $fields)) {
                            $value = $data[$keyField] ?? null;
                            // Cek apakah nilai kosong (null, empty string, atau string kosong setelah trim)
                            if ($value === null || $value === '' || (is_string($value) && trim($value) === '')) {
                                $missingCompositeKey[] = $keyField;
                            }
                        }
                    }

                    // Jika ada field composite key yang dipilih user tapi kosong, skip data ini
                    if (!empty($missingCompositeKey)) {
                        $errors[] = [
                            'nik' => $vcNik ?? 'N/A',
                            'jenis' => $vcJenis ?? 'N/A',
                            'tanggal_awal' => $dtTanggalAwal ?? 'N/A',
                            'tanggal_akhir' => $dtTanggalAkhir ?? 'N/A',
                            'error' => 'Field composite key yang dipilih kosong: ' . implode(', ', $missingCompositeKey)
                        ];
                        continue;
                    }

                    // Jika field composite key tidak dipilih user, tidak bisa insert/update (composite key wajib)
                    $requiredFieldsNotSelected = [];
                    foreach ($compositeKeyFields as $keyField) {
                        if (!in_array($keyField, $fields)) {
                            $requiredFieldsNotSelected[] = $keyField;
                        }
                    }

                    if (!empty($requiredFieldsNotSelected)) {
                        $errors[] = [
                            'nik' => $vcNik ?? 'N/A',
                            'jenis' => $vcJenis ?? 'N/A',
                            'tanggal_awal' => $dtTanggalAwal ?? 'N/A',
                            'tanggal_akhir' => $dtTanggalAkhir ?? 'N/A',
                            'error' => 'Field composite key wajib tidak dipilih: ' . implode(', ', $requiredFieldsNotSelected)
                        ];
                        continue;
                    }

                    // Cek apakah data sudah ada (berdasarkan composite key)
                    $existing = DB::table('t_hutang_piutang')
                        ->where('dtTanggalAwal', $dtTanggalAwal)
                        ->where('dtTanggalAkhir', $dtTanggalAkhir)
                        ->where('vcNik', $vcNik)
                        ->where('vcJenis', $vcJenis)
                        ->first();

                    if ($existing) {
                        // Data sudah ada, update
                        $updateData = ['dtChange' => Carbon::now()];

                        // Update field yang dipilih (kecuali composite key)
                        foreach ($fields as $field) {
                            if (!in_array($field, ['dtTanggalAwal', 'dtTanggalAkhir', 'vcNik', 'vcJenis']) && isset($data[$field])) {
                                $updateData[$field] = $data[$field];
                            }
                        }

                        DB::table('t_hutang_piutang')
                            ->where('dtTanggalAwal', $dtTanggalAwal)
                            ->where('dtTanggalAkhir', $dtTanggalAkhir)
                            ->where('vcNik', $vcNik)
                            ->where('vcJenis', $vcJenis)
                            ->update($updateData);
                        $updated++;
                    } else {
                        // Insert data baru
                        $data['dtCreate'] = Carbon::now();
                        $data['dtChange'] = Carbon::now();

                        DB::table('t_hutang_piutang')->insert($data);
                        $inserted++;
                    }
                } catch (\Exception $e) {
                    $errors[] = [
                        'nik' => $remoteRow->vcNik ?? 'N/A',
                        'jenis' => $remoteRow->vcJenis ?? 'N/A',
                        'tanggal_awal' => $remoteRow->dtTanggalAwal ?? 'N/A',
                        'tanggal_akhir' => $remoteRow->dtTanggalAkhir ?? 'N/A',
                        'error' => $e->getMessage()
                    ];
                    Log::error('Error inserting hutang piutang data', [
                        'nik' => $remoteRow->vcNik ?? null,
                        'jenis' => $remoteRow->vcJenis ?? null,
                        'tanggal_awal' => $remoteRow->dtTanggalAwal ?? null,
                        'tanggal_akhir' => $remoteRow->dtTanggalAkhir ?? null,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            // Disconnect dari remote database
            DB::purge('remote_mysql');

            $message = "Data berhasil ditarik! Insert: {$inserted}, Update: {$updated}, Skip: {$skipped}";
            if (!empty($errors)) {
                $message .= " (Error: " . count($errors) . " record)";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'inserted' => $inserted,
                    'updated' => $updated,
                    'skipped' => $skipped,
                    'total' => $remoteData->count(),
                    'errors' => count($errors),
                    'error_details' => $errors
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error pulling hutang piutang data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Membuat koneksi ke database remote
     */
    private function connectToRemoteDatabase($config)
    {
        try {
            // Register connection baru untuk remote database
            config([
                'database.connections.remote_mysql' => [
                    'driver' => 'mysql',
                    'host' => $config['host'],
                    'port' => $config['port'],
                    'database' => $config['database'],
                    'username' => $config['username'],
                    'password' => $config['password'],
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'strict' => true,
                    'engine' => null,
                ]
            ]);

            // Test connection
            DB::connection('remote_mysql')->getPdo();
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to connect to remote database', [
                'host' => $config['host'],
                'database' => $config['database'],
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}

