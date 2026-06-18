<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerjalananDinasHeader extends Model
{
    use HasFactory;

    protected $table = 't_perjalanan_dinas_header';
    protected $primaryKey = 'vcNoRpd';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'vcNoRpd',
        'dtTanggalForm',
        'dtTanggalDinasDari',
        'dtTanggalDinasSampai',
        'intDurasiHari',
        'vcPemberiTugas',
        'vcJabatanPemberiTugas',
        'vcTujuanDinas',
        'vcMaksudPerjalananDinas',
        'vcMengajukan',
        'vcMenyetujui',
        'vcMengetahui',
        'dtCreate',
        'dtChange',
    ];

    protected $casts = [
        'dtTanggalForm' => 'date',
        'dtTanggalDinasDari' => 'date',
        'dtTanggalDinasSampai' => 'date',
        'intDurasiHari' => 'integer',
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];

    // Relationships
    public function karyawans()
    {
        return $this->hasMany(PerjalananDinasKaryawan::class, 'vcNoRpd', 'vcNoRpd');
    }

    public function jadwals()
    {
        return $this->hasMany(PerjalananDinasJadwal::class, 'vcNoRpd', 'vcNoRpd');
    }

    public function hotels()
    {
        return $this->hasMany(PerjalananDinasHotel::class, 'vcNoRpd', 'vcNoRpd');
    }

    public function tibaKembali()
    {
        return $this->hasOne(PerjalananDinasTibaKembali::class, 'vcNoRpd', 'vcNoRpd');
    }

    public function biayaPerjalananDinas()
    {
        return $this->hasOne(BiayaPerjalananDinasHeader::class, 'vcNoRpd', 'vcNoRpd');
    }
}
