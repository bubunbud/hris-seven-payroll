<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "t_izin:\n";
$cols = DB::select('DESCRIBE t_izin');
foreach ($cols as $c) {
    echo $c->Field . ' - ' . $c->Type . ' - ' . $c->Null . ' - ' . $c->Key . PHP_EOL;
}

$rows = DB::table('t_izin')->limit(3)->get();
foreach ($rows as $r) {
    print_r($r);
}



