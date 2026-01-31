<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TukarHariKerjaDetail extends Model
{
    use HasFactory;

    protected $table = 't_tukar_hari_kerja_detail';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'vcKodeTukar',
        'vcNik',
        'dtTanggalLibur',
        'dtTanggalKerja',
        'vcStatus',
        'dtCreatedAt',
        'dtUpdatedAt',
    ];

    protected $casts = [
        'dtTanggalLibur' => 'date',
        'dtTanggalKerja' => 'date',
        'dtCreatedAt' => 'datetime',
        'dtUpdatedAt' => 'datetime',
    ];

    /**
     * Relationship dengan header
     */
    public function tukarHariKerja()
    {
        return $this->belongsTo(TukarHariKerja::class, 'vcKodeTukar', 'vcKodeTukar');
    }

    /**
     * Relationship dengan karyawan
     */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'vcNik', 'Nik');
    }
}
