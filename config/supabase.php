<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supabase REST API (Tarik Data Absensi)
    |--------------------------------------------------------------------------
    */
    'url' => rtrim(env('SUPABASE_URL', 'https://tvqlawdewuephgxqhkch.supabase.co'), '/'),
    'api_key' => env('SUPABASE_API_KEY', ''),
    'timeout' => (int) env('SUPABASE_TIMEOUT', 60),
    'attendance_table' => env('SUPABASE_ATTENDANCE_TABLE', 'attendance'),
    'leave_requests_table' => env('SUPABASE_LEAVE_REQUESTS_TABLE', 'leave_requests'),
    'page_size' => (int) env('SUPABASE_PAGE_SIZE', 1000),
    /*
    | Zona waktu untuk menampilkan & menyimpan jam absensi dari Supabase.
    | Timestamp di Supabase disimpan UTC; dikonversi ke timezone ini (default WIB).
    */
    'timezone' => env('SUPABASE_TIMEZONE', env('APP_TIMEZONE', 'Asia/Jakarta')),
];
