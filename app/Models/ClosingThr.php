<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClosingThr extends Model
{
    use HasFactory;

    protected $table = 't_closing_thr';
    protected $primaryKey = ['dtTanggalTHR', 'vcNik', 'vcAgama'];
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'dtTanggalTHR',
        'vcNik',
        'vcAgama',
        'vcKodeDivisi',
        'vcGroupPegawai',
        'vcGolongan',
        'decGajiPokok',
        'dtTanggalMasuk',
        'vcMasaKerja',
        'intMasaKerjaHari',
        'decMasaKerjaBulan',
        'decMasaKerjaTahun',
        'decXGaji',
        'decNilaiTHR',
        'vcKeterangan',
        'dtCreate',
        'dtChange',
    ];

    protected $casts = [
        'dtTanggalTHR' => 'date',
        'dtTanggalMasuk' => 'date',
        'intMasaKerjaHari' => 'integer',
        'decMasaKerjaBulan' => 'decimal:2',
        'decMasaKerjaTahun' => 'decimal:2',
        'decGajiPokok' => 'decimal:2',
        'decXGaji' => 'decimal:2',
        'decNilaiTHR' => 'decimal:2',
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];

    // Relationship dengan Karyawan
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'vcNik', 'Nik');
    }

    // Relationship dengan Gapok
    public function gapok()
    {
        return $this->belongsTo(Gapok::class, 'vcGolongan', 'vcKodeGolongan');
    }

    // Relationship dengan Divisi
    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'vcKodeDivisi', 'vcKodeDivisi');
    }
}
