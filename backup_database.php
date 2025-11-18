<?php

/**
 * Script Backup Database Seven
 * Menggunakan koneksi Laravel untuk menghindari masalah view dan akses
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

// Ambil konfigurasi database
$dbName = Config::get('database.connections.mysql.database');
$dbHost = Config::get('database.connections.mysql.host');
$dbUser = Config::get('database.connections.mysql.username');
$dbPass = Config::get('database.connections.mysql.password');
$dbPort = Config::get('database.connections.mysql.port', 3306);

// Generate nama file backup
$timestamp = date('Ymd_His');
$backupFile = __DIR__ . "/backup_{$dbName}_{$timestamp}.sql";

echo "Starting backup of database: {$dbName}\n";
echo "Backup file: {$backupFile}\n\n";

// Path ke mysqldump
$mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';

// Build command dengan opsi untuk menghindari error view
// --skip-lock-tables: Skip LOCK TABLES (menghindari error view)
// --single-transaction: Untuk InnoDB, memastikan konsistensi data
// --routines: Include stored procedures dan functions
// --triggers: Include triggers
// --skip-add-drop-table: Skip DROP TABLE statements (opsional)
$command = sprintf(
    '"%s" -u %s -h %s -P %d --skip-lock-tables --single-transaction --routines --triggers %s > "%s" 2>&1',
    $mysqldumpPath,
    escapeshellarg($dbUser),
    escapeshellarg($dbHost),
    $dbPort,
    escapeshellarg($dbName),
    escapeshellarg($backupFile)
);

// Jika ada password, tambahkan ke command
if (!empty($dbPass)) {
    $command = sprintf(
        '"%s" -u %s -p%s -h %s -P %d --skip-lock-tables --single-transaction --routines --triggers %s > "%s" 2>&1',
        $mysqldumpPath,
        escapeshellarg($dbUser),
        escapeshellarg($dbPass),
        escapeshellarg($dbHost),
        $dbPort,
        escapeshellarg($dbName),
        escapeshellarg($backupFile)
    );
}

echo "Executing command...\n";
exec($command, $output, $returnVar);

// Cek hasil
if (file_exists($backupFile)) {
    $fileSize = filesize($backupFile);
    $fileSizeMB = round($fileSize / 1024 / 1024, 2);

    if ($fileSize > 1000) { // Minimal 1KB untuk backup yang valid
        echo "\n✓ Backup berhasil!\n";
        echo "File: {$backupFile}\n";
        echo "Size: {$fileSizeMB} MB ({$fileSize} bytes)\n";

        // Cek apakah ada error di dalam file
        $content = file_get_contents($backupFile);
        if (stripos($content, 'error') !== false || stripos($content, 'access denied') !== false) {
            echo "\n⚠ WARNING: File backup mungkin mengandung error. Silakan cek isi file.\n";
        } else {
            echo "\n✓ Backup file terlihat valid.\n";
        }
    } else {
        echo "\n✗ Backup gagal! File terlalu kecil (mungkin ada error).\n";
        echo "File size: {$fileSize} bytes\n";
        echo "Content:\n";
        echo substr($content, 0, 500) . "\n";
    }
} else {
    echo "\n✗ Backup gagal! File tidak terbuat.\n";
    if (!empty($output)) {
        echo "Error output:\n";
        print_r($output);
    }
}

if ($returnVar !== 0) {
    echo "\nReturn code: {$returnVar}\n";
}
