<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Izin extends Model
{
    use HasFactory;

    protected $table = 't_izin';
    protected $primaryKey = 'vcCounter';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'dtTanggal',
        'vcNik',
        'vcKodeIzin',
        'dtDari',
        'dtSampai',
        'vcKeterangan',
        'vcCounter',
        'dtCreate',
        'dtChange',
    ];

    protected $casts = [
        'dtTanggal' => 'date',
        'dtDari' => 'string', // time as string
        'dtSampai' => 'string',
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'vcNik', 'Nik');
    }

    public function jenisIzin()
    {
        return $this->belongsTo(JenisIzin::class, 'vcKodeIzin', 'vcKodeIzin');
    }
}
