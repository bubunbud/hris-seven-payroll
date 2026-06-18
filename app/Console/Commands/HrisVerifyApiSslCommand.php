<?php

namespace App\Console\Commands;

use App\Services\HrisApiHttpFactory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

class HrisVerifyApiSslCommand extends Command
{
    protected $signature = 'hris:verify-api-ssl {--skip-http : Hanya tampilkan konfigurasi CA, tanpa request HTTPS}';

    protected $description = 'Diagnosis SSL/CA untuk koneksi ke HRIS API eksternal (cURL error 60)';

    public function handle(): int
    {
        $report = HrisApiHttpFactory::sslDiagnosticReport();
        $base = rtrim((string) config('hris_api.base_url', ''), '/');

        $this->newLine();
        $this->info('HRIS API base_url: '.$base);
        $this->line('verify_ssl (config): '.($report['verify_ssl_config'] ? 'true' : 'false'));
        $this->line('Bundle CA utama (pemilihan pertama): '.($report['chosen_base'] ?? '(tidak ada)'));
        $this->line('File verify efektif (gabung rantai jika HRIS_API_SSL_CHAIN_APPEND di-set): '
            .($report['chosen'] ?? '(tidak ada — default Guzzle/PHP)'));
        $append = env('HRIS_API_SSL_CHAIN_APPEND');
        $appendLine = '.env HRIS_API_SSL_CHAIN_APPEND: '.(($append ?? '') !== '' ? $append : '(kosong)');
        if (($append ?? '') !== '') {
            $appendLine .= ' — pembacaan file: '.(($report['chain_append_ok'] ?? false) ? 'ya' : 'tidak');
        }
        $this->line($appendLine);
        $cbc = env('HRIS_API_CURL_CA_BUNDLE');
        $this->line('.env HRIS_API_CURL_CA_BUNDLE: '.($cbc !== null && $cbc !== '' ? $cbc : '(kosong)'));
        $this->line('Proses CURL_CA_BUNDLE: '.(getenv('CURL_CA_BUNDLE') ?: '(kosong)'));
        $this->newLine();
        $this->table(
            ['Urutan', 'Path', 'Ada', 'Baca', 'Ukuran'],
            collect($report['candidates'])->values()->map(function ($row, $i) {
                return [
                    (string) ($i + 1),
                    $row['path'],
                    $row['exists'] ? 'ya' : 'tidak',
                    $row['readable'] ? 'ya' : 'tidak',
                    $row['bytes'] !== null ? (string) number_format($row['bytes']) : '-',
                ];
            })->all()
        );

        if ($this->option('skip-http')) {
            $this->newLine();
            $this->comment('Jika masih cURL 60 dengan mozilla: ambil chain (di server): echo | openssl s_client -showcerts -servername hris-api.abadinusagroup.com -connect hris-api.abadinusagroup.com:443 2>/dev/null | sed -ne \'/-BEGIN CERTIFICATE-/,/-END CERTIFICATE-/p\' > storage/app/cacerts/hris-api-chain.pem lalu set HRIS_API_SSL_CHAIN_APPEND ke path itu, chown www-data, config:clear.');

            return self::SUCCESS;
        }

        if ($base === '') {
            $this->error('HRIS_API_BASE_URL kosong.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Percobaan GET (timeout 25s): '.$base.'/');

        try {
            $response = HrisApiHttpFactory::base()->timeout(25)->withHeaders([
                'Accept' => '*/*',
            ])->get($base.'/');
            $this->info('Berhasil handshake HTTP. HTTP status: '.$response->status());
        } catch (Throwable $e) {
            $this->error('Gagal: '.$e->getMessage());
            $this->line(HrisApiHttpFactory::sslDiagnosticsHint());
            $this->newLine();
            $this->comment('Ujicoba sama sekali tanpa verify SSL (hanya tes jaringan):');
            try {
                $r = Http::timeout(25)->withoutVerifying()->withHeaders(['Accept' => '*/*'])->get($base.'/');
                $this->warn('Tanpa verify: HTTP '.$r->status().' → jaringan ke host OK; perbaikan hanya pada trust CA atau matikan verify lewat HRIS_API_VERIFY_SSL=false.');
            } catch (Throwable $e2) {
                $this->error('Tanpa verify pun gagal: '.$e2->getMessage());
            }

            return self::FAILURE;
        }

        $this->newLine();

        return self::SUCCESS;
    }
}
