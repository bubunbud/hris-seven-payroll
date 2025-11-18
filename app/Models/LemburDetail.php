<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LemburDetail extends Model
{
    use HasFactory;

    protected $table = 't_lembur_detail';
    protected $primaryKey = 'vcCounterDetail';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'vcCounterDetail',
        'vcCounterHeader',
        'vcNik',
        'vcNamaKaryawan',
        'vcKodeJabatan',
        'dtJamMulaiLembur',
        'dtJamSelesaiLembur',
        'decDurasiLembur',
        'intDurasiIstirahat',
        'vcDeskripsiLembur',
        'vcPenanggungBebanLembur',
        'vcPenanggungBebanLainnya',
        'dtCreate',
        'dtChange',
    ];

    protected $casts = [
        'decDurasiLembur' => 'decimal:2',
        'intDurasiIstirahat' => 'integer',
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];

    // Relationship dengan Header
    public function header()
    {
        return $this->belongsTo(LemburHeader::class, 'vcCounterHeader', 'vcCounter');
    }

    // Relationship dengan Karyawan
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'vcNik', 'Nik');
    }

    // Relationship dengan Jabatan
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'vcKodeJabatan', 'vcKodeJabatan');
    }
}
