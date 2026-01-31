<?php

namespace App\Http\Controllers;

use App\Models\Absen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TarikDataAbsensiController extends Controller
{
    /**
     * Menampilkan form tarik data absensi
     */
    public function index()
    {
        return view('tarik-data-absensi.index');
    }

    /**
     * Proses tarik data absensi dari server remote
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
            $query = DB::connection('remote_mysql')
                ->table($request->server_table)
                ->whereBetween('dtTanggal', [$dariTanggal, $sampaiTanggal])
                ->select($fields);

            $remoteData = $query->get();

            if ($remoteData->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data absensi ditemukan untuk periode yang dipilih.'
                ], 404);
            }

            // Proses insert/update data ke database lokal
            $inserted = 0;
            $updated = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($remoteData as $remoteRow) {
                try {
                    // Cek apakah data sudah ada (berdasarkan composite key: dtTanggal + vcNik)
                    $existing = DB::table('t_absen')
                        ->where('dtTanggal', $remoteRow->dtTanggal)
                        ->where('vcNik', $remoteRow->vcNik)
                        ->first();

                    // Siapkan data untuk insert/update
                    $data = [];

                    // Tambahkan field yang dipilih dari remote data
                    foreach ($fields as $field) {
                        $data[$field] = $remoteRow->$field ?? null;
                    }

                    if ($existing) {
                        // Data sudah ada, selalu update dengan data baru dari server (sinkronisasi)
                        $updateData = ['dtChange' => Carbon::now()];

                        // Update semua field yang dipilih dari data server
                        foreach ($fields as $field) {
                            if (isset($data[$field])) {
                                $updateData[$field] = $data[$field];
                            }
                        }

                        DB::table('t_absen')
                            ->where('dtTanggal', $remoteRow->dtTanggal)
                            ->where('vcNik', $remoteRow->vcNik)
                            ->update($updateData);
                        $updated++;
                    } else {
                        // Insert data baru
                        $data['dtCreate'] = Carbon::now();
                        $data['dtChange'] = Carbon::now();

                        DB::table('t_absen')->insert($data);
                        $inserted++;
                    }
                } catch (\Exception $e) {
                    $errors[] = [
                        'nik' => $remoteRow->vcNik ?? 'N/A',
                        'tanggal' => $remoteRow->dtTanggal ?? 'N/A',
                        'error' => $e->getMessage()
                    ];
                    Log::error('Error inserting absensi data', [
                        'nik' => $remoteRow->vcNik ?? null,
                        'tanggal' => $remoteRow->dtTanggal ?? null,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            // Disconnect dari remote database
            DB::purge('remote_mysql');

            $message = "Data berhasil ditarik! Insert: {$inserted}, Update: {$updated}";
            if (!empty($errors)) {
                $message .= " (Error: " . count($errors) . " record)";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'inserted' => $inserted,
                    'updated' => $updated,
                    'total' => $remoteData->count(),
                    'errors' => count($errors),
                    'error_details' => $errors
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error pulling absensi data', [
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
