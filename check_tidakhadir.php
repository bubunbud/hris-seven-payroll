<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$rows = DB::select('DESCRIBE t_tidak_masuk');
foreach ($rows as $r) {
    echo $r->Field.'|'.$r->Type.'|'.$r->Null.'|'.$r->Key.PHP_EOL;
}



