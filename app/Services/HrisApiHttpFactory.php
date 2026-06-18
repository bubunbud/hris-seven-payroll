<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Klien HTTP untuk semua panggilan ke HRIS API (SSL/verify terpusat).
 *
 * cURL error 60 dengan mozilla/OS bundle seringnya karena HTTPS host tidak mengirim rantai lengkap —
 * pakai HRIS_API_SSL_CHAIN_APPEND atau HRIS_API_VERIFY_SSL=false (darurat).
 */
class HrisApiHttpFactory
{
    /** Path PEM untuk Guzzle (bundle dasar digabung ssl_chain_append bila ada). */
    protected static function resolveVerifyPath(): ?string
    {
        $base = static::pickFirstCaBundleCandidate();
        if ($base === null) {
            return null;
        }

        return static::maybeMergeChainAppend($base);
    }

    protected static function pickFirstCaBundleCandidate(): ?string
    {
        foreach (static::candidateCaBundlePaths() as $path) {
            if (is_string($path) && $path !== '' && is_file($path) && is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    protected static function candidateCaBundlePaths(): array
    {
        $phpDir = PHP_BINARY ? dirname(PHP_BINARY) : '';

        $paths = [
            storage_path('app/cacerts/cacert.pem'),
            '/usr/local/share/ca-certificates-custom/curl-cacert.pem',
            config('hris_api.ca_bundle'),
            self::envPath('CURL_CA_BUNDLE'),
            self::envPath('SSL_CERT_FILE'),
            $phpDir !== '' ? $phpDir.DIRECTORY_SEPARATOR.'extras'.DIRECTORY_SEPARATOR.'ssl'.DIRECTORY_SEPARATOR.'cacert.pem' : null,
            '/etc/ssl/certs/ca-certificates.crt',
            '/etc/pki/tls/certs/ca-bundle.crt',
        ];

        $filtered = array_values(array_filter($paths, static fn ($p) => is_string($p) && $p !== ''));

        return array_values(array_unique($filtered));
    }

    /** Path dari environment (process / .env), jika ada. */
    protected static function envPath(string $name): ?string
    {
        $v = $_ENV[$name] ?? getenv($name);

        return is_string($v) && $v !== '' ? $v : null;
    }

    /**
     * Gabung bundle dasar + HRIS_API_SSL_CHAIN_APPEND (intermediate / chain openssl s_client).
     */
    protected static function maybeMergeChainAppend(string $basePath): string
    {
        $append = config('hris_api.ssl_chain_append');
        if (! is_string($append) || $append === '' || ! is_file($append) || ! is_readable($append)) {
            return $basePath;
        }

        $cacheDir = storage_path('framework/cache');
        if (! is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $mtBase = @filemtime($basePath) ?: 0;
        $mtApp = @filemtime($append) ?: 0;
        $key = hash('sha256', $basePath."\0".$append."\0".$mtBase."\0".$mtApp);
        $merged = $cacheDir.DIRECTORY_SEPARATOR.'hris_ssl_merged_'.$key.'.pem';

        if (! is_file($merged)) {
            $a = @file_get_contents($basePath);
            $b = @file_get_contents($append);
            if ($a === false || $b === false) {
                return $basePath;
            }
            @file_put_contents($merged, rtrim($a)."\n".rtrim($b)."\n");
            @chmod($merged, 0644);
        }

        return $merged;
    }

    /**
     * @return array{
     *     verify_ssl_config: bool,
     *     chosen_base: string|null,
     *     chosen: string|null,
     *     chain_append_config: ?string,
     *     chain_append_ok: bool,
     *     candidates: array<int, array{path: string, exists: bool, readable: bool, bytes: int|null}>
     * }
     */
    public static function sslDiagnosticReport(): array
    {
        $candidates = [];
        foreach (static::candidateCaBundlePaths() as $path) {
            $candidates[] = [
                'path' => $path,
                'exists' => is_file($path),
                'readable' => is_readable($path),
                'bytes' => is_file($path) ? (int) filesize($path) : null,
            ];
        }

        $append = config('hris_api.ssl_chain_append');
        $appendStr = is_string($append) ? $append : '';

        return [
            'verify_ssl_config' => (bool) config('hris_api.verify_ssl', true),
            'chosen_base' => static::pickFirstCaBundleCandidate(),
            'chosen' => static::resolveVerifyPath(),
            'chain_append_config' => $appendStr !== '' ? $appendStr : null,
            'chain_append_ok' => $appendStr !== '' && is_readable($appendStr),
            'candidates' => $candidates,
        ];
    }

    public static function base(): PendingRequest
    {
        $req = Http::timeout((int) config('hris_api.timeout', 60))
            ->withHeaders(['Accept' => 'application/json']);

        if (config('hris_api.verify_ssl', true) === false) {
            return $req->withoutVerifying();
        }

        $verifyPath = static::resolveVerifyPath();
        if ($verifyPath !== null) {
            return $req->withOptions(['verify' => $verifyPath]);
        }

        return $req;
    }

    public static function withToken(string $token): PendingRequest
    {
        return static::base()->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ]);
    }

    /** Pesan singkat untuk admin bila HTTPS gagal diverifikasi (mis. cURL 60). */
    public static function sslDiagnosticsHint(): string
    {
        $pem = storage_path('app/cacerts/cacert.pem');

        return 'Jika mozilla PEM ada tetapi masih "unable to get local issuer", biasanya rantai tidak lengkap dari host API.'
            .' Tambah PEM intermediate lewat openssl s_client, set HRIS_API_SSL_CHAIN_APPEND=…; pastikan writable storage/framework/cache.'
            .' Lokasi PEM referensi: '.$pem.'. Darurat: HRIS_API_VERIFY_SSL=false.';
    }
}
