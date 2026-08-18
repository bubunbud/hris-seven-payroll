<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Absen;
use Illuminate\Support\Facades\DB;

$counter = '202607154360';
$tanggal = '2026-07-15';
$niks = ['20140433', '20231219'];

echo "=== HEADER ===\n";
$h = DB::table('t_lembur_header')->where('vcCounter', $counter)->first();
var_export($h);
echo "\n";

echo "\n=== DETAIL (all) ===\n";
$details = DB::table('t_lembur_detail')->where('vcCounterHeader', $counter)->get();
foreach ($details as $d) {
    echo "{$d->vcNik} | " . ($d->vcNamaKaryawan ?? '-') . "\n";
}

echo "\n=== KARYAWAN (2 NIK) ===\n";
foreach ($niks as $nik) {
    $k = DB::table('m_karyawan')->where('Nik', $nik)->first();
    if (!$k) {
        $k = DB::table('m_karyawan')->where('Nik', 'like', "%{$nik}%")->first();
    }
    if ($k) {
        echo "NIK {$nik}: Nama={$k->Nama}, vcAktif={$k->vcAktif}, Nik stored as [{$k->Nik}]\n";
    } else {
        echo "NIK {$nik}: NOT FOUND in m_karyawan\n";
    }
}

echo "\n=== T_ABSEN per detail NIK ===\n";
foreach ($details as $d) {
    $a = DB::table('t_absen')->where('dtTanggal', $tanggal)->where('vcNik', $d->vcNik)->first();
    if ($a) {
        echo "{$d->vcNik}: vcCounter=" . ($a->vcCounter ?? 'NULL')
            . ", masukL=" . ($a->dtJamMasukLembur ?? 'NULL')
            . ", keluarL=" . ($a->dtJamKeluarLembur ?? 'NULL')
            . ", masuk=" . ($a->dtJamMasuk ?? 'NULL')
            . ", keluar=" . ($a->dtJamKeluar ?? 'NULL') . "\n";
    } else {
        echo "{$d->vcNik}: NO t_absen row on {$tanggal}\n";
        $variants = DB::table('t_absen')->where('dtTanggal', $tanggal)->where('vcNik', 'like', '%' . substr($d->vcNik, -6) . '%')->get();
        echo "  variants found: " . $variants->count() . "\n";
        foreach ($variants as $v) {
            echo "    vcNik=[{$v->vcNik}] vcCounter=" . ($v->vcCounter ?? 'NULL') . "\n";
        }
    }
}

echo "\n=== REALISASI QUERY (controller logic, counter filter) ===\n";
$records = Absen::with(['karyawan', 'lemburHeader'])
    ->whereBetween('dtTanggal', ['2026-07-01', '2026-07-31'])
    ->whereNotNull('vcCounter')
    ->whereHas('lemburHeader')
    ->where(function ($q) {
        $q->whereNotNull('dtJamMasuk')->orWhereNotNull('dtJamKeluar')
            ->orWhereNotNull('dtJamMasukLembur')->orWhereNotNull('dtJamKeluarLembur');
    })
    ->whereHas('karyawan', fn ($q) => $q->where('vcAktif', '1'))
    ->where('vcCounter', $counter)
    ->get(['dtTanggal', 'vcNik', 'vcCounter']);
echo 'Count: ' . $records->count() . "\n";
foreach ($records as $r) {
    echo "  {$r->vcNik}\n";
}

echo "\n=== LEMBUR DETAIL jam for missing vs visible ===\n";
foreach (['20140433', '20231219', '20030016', '20231186', '20231160'] as $nik) {
    $d = DB::table('t_lembur_detail')->where('vcCounterHeader', $counter)->where('vcNik', $nik)->first();
    if ($d) {
        echo "{$nik}: mulai={$d->dtJamMulaiLembur} selesai={$d->dtJamSelesaiLembur} dtCreate={$d->dtCreate}\n";
    }
}

echo "\n=== T_ABSEN dtCreate/dtChange ===\n";
foreach (['20140433', '20231219', '20030016'] as $nik) {
    $a = DB::table('t_absen')->where('dtTanggal', $tanggal)->where('vcNik', $nik)->first();
    if ($a) {
        echo "{$nik}: dtCreate={$a->dtCreate} dtChange={$a->dtChange}\n";
    }
}

echo "\n=== NIK length in detail ===\n";
foreach (DB::table('t_lembur_detail')->where('vcCounterHeader', $counter)->get() as $d) {
    echo "[{$d->vcNik}] len=" . strlen($d->vcNik) . "\n";
}

echo "If we run UPDATE like IKL store, rows affected would set vcCounter.\n";
echo "Current gap: detail exists in t_lembur_detail but t_absen.vcCounter is NULL.\n";

foreach ($niks as $nik) {
    echo "--- NIK {$nik} ---\n";
    $absen = DB::table('t_absen')->where('dtTanggal', $tanggal)->where('vcNik', $nik)->first();
    if (!$absen) {
        echo "FAIL: no t_absen\n";
        continue;
    }
    echo "t_absen: vcCounter=" . ($absen->vcCounter ?? 'NULL') . "\n";
    if (empty($absen->vcCounter)) {
        echo "FAIL: vcCounter null\n";
    }
    $lh = DB::table('t_lembur_header')->where('vcCounter', $absen->vcCounter)->exists();
    echo "lemburHeader: " . ($lh ? 'OK' : 'FAIL') . "\n";
    $hasJam = $absen->dtJamMasuk || $absen->dtJamKeluar || $absen->dtJamMasukLembur || $absen->dtJamKeluarLembur;
    echo "has jam: " . ($hasJam ? 'OK' : 'FAIL') . "\n";
    $k = DB::table('m_karyawan')->where('Nik', $absen->vcNik)->first();
    if (!$k) {
        echo "FAIL: karyawan not found for vcNik=[{$absen->vcNik}]\n";
    } else {
        echo "karyawan: vcAktif={$k->vcAktif} " . ($k->vcAktif == '1' ? 'OK' : 'FAIL') . "\n";
    }
}
