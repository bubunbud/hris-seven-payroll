<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$nik = '20100013';

echo "Checking t_pendidikan table for NIK: {$nik}\n\n";

try {
    $exists = DB::getSchemaBuilder()->hasTable('t_pendidikan');
    echo "Table exists: " . ($exists ? 'YES' : 'NO') . "\n";
    
    if ($exists) {
        $count = DB::table('t_pendidikan')->where('nik', $nik)->count();
        echo "Records for NIK {$nik}: {$count}\n\n";
        
        if ($count > 0) {
            $data = DB::table('t_pendidikan')->where('nik', $nik)->get();
            echo "Data:\n";
            foreach ($data as $row) {
                print_r($row);
            }
        } else {
            echo "No records found for NIK {$nik}\n";
            // Check if there are any records in the table
            $totalCount = DB::table('t_pendidikan')->count();
            echo "Total records in t_pendidikan: {$totalCount}\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}


