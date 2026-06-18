<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerjalananDinasKaryawan extends Model
{
    use HasFactory;

    protected $table = 't_perjalanan_dinas_karyawan';
    protected $primaryKey = 'vcCounterKaryawan';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'vcCounterKaryawan',
        'vcNoRpd',
        'vcNik',
        'vcNamaKaryawan',
        'vcKodeDept',
        'vcKodeJabatan',
        'vcKlasifikasiGrade',
        'dtCreate',
        'dtChange',
    ];

    protected $casts = [
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];

    // Relationships
    public function header()
    {
        return $this->belongsTo(PerjalananDinasHeader::class, 'vcNoRpd', 'vcNoRpd');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'vcNik', 'Nik');
    }

    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'vcKodeDept', 'vcKodeDept');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'vcKodeJabatan', 'vcKodeJabatan');
    }
}
