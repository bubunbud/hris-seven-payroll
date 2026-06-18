<?php

if (! function_exists('hris_api_env_verify_ssl_enabled')) {
    /** true = pemeriksaan sertifikat HTTPS aktif. String kosong = default true (bukan perilaku PHP filter_var). */
    function hris_api_env_verify_ssl_enabled(): bool
    {
        $raw = env('HRIS_API_VERIFY_SSL');
        if ($raw === null) {
            return true;
        }
        $s = strtolower(trim((string) $raw));
        if ($s === '') {
            return true;
        }
        if (in_array($s, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }
        if (in_array($s, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        return true;
    }
}

return [
    /*
    |--------------------------------------------------------------------------
    | HRIS API Configuration (External)
    |--------------------------------------------------------------------------
    | Konfigurasi untuk koneksi ke API HRIS eksternal (feeder pengajuan cuti)
    */
    'base_url' => env('HRIS_API_BASE_URL', 'https://hris-api.abadinusagroup.com'),
    'username' => env('HRIS_API_USERNAME', 'superadmin'),
    'password' => env('HRIS_API_PASSWORD', ''),
    'timeout' => env('HRIS_API_TIMEOUT', 60),

    /*
    | SSL: cURL error 60 → penyebab umum: ca-certificates usang/apt gagal update, atau API tidak kirim rantai lengkap.
    |
    | Urutan pakai file CA oleh HrisApiHttpFactory:
    | 1. storage/app/cacerts/cacert.pem (mozilla → SCP; user web-server harus bisa baca file ini)
    | 2. /usr/local/share/ca-certificates-custom/curl-cacert.pem (alternatif pola deploy manual)
    | — lalu HRIS_API_CURL_CA_BUNDLE, CURL_CA_BUNDLE, SSL_CERT_FILE, XAMPP …, bundle OS.
    |
    | Jika mozilla + bundle OS tetap cURL 60: seringnya sertifikat API tidak mengirim intermediate lengkap.
    | Isi HRIS_API_SSL_CHAIN_APPEND dengan PEM berisi sertifikat intermediate (atau chain dari openssl s_client).
    | Aplikasi akan menggabungkan file itu dengan bundle dasar ke storage/framework/cache/hris_ssl_merged_*.pem.
    |
    | Tanggap darurat tanpa HTTPS verify: HRIS_API_VERIFY_SSL=false (hanya sampai infra diperbaiki).
    | HRIS_API_VERIFY_SSL kosong = tetap aktifkan verify.
    */
    'verify_ssl' => hris_api_env_verify_ssl_enabled(),
    'ca_bundle' => env('HRIS_API_CURL_CA_BUNDLE'),
    'ssl_chain_append' => env('HRIS_API_SSL_CHAIN_APPEND'),

    /** Path endpoint log absensi (relatif ke base_url) */
    'attendance_logs_path' => env('HRIS_API_ATTENDANCE_LOGS_PATH', '/v1/management/attendances/logs'),
];
