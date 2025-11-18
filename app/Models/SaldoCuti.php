<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaldoCuti extends Model
{
    use HasFactory;

    protected $table = 'm_saldo_cuti';
    // Note: Tabel ini menggunakan composite primary key (vcNik, intTahun)
    // Eloquent tidak mendukung composite key secara native, jadi kita tidak set $primaryKey
    // Gunakan DB::table() untuk operasi update/create yang memerlukan composite key
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'vcNik',
        'intTahun',
        'decTahunLalu',
        'decTahunIni',
        'decSaldoDigunakan',
        'decSaldoSisa',
        'vcKeterangan',
        'dtCreate',
        'dtChange',
    ];

    protected $casts = [
        'intTahun' => 'integer',
        'decTahunLalu' => 'integer', // Jumlah hari, pasti bulat
        'decTahunIni' => 'integer', // Jumlah hari, pasti bulat
        'decSaldoDigunakan' => 'integer', // Jumlah hari, pasti bulat
        'decSaldoSisa' => 'integer', // Jumlah hari, pasti bulat
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'vcNik', 'Nik');
    }
}
