<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelatihan extends Model
{
    use HasFactory;

    protected $table = 't_pelatihan';
    protected $primaryKey = null; // gunakan kombinasi Nik + nm_pelatihan (unique)
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'Nik',
        'nm_pelatihan',
        'penyelenggara',
        'lokasi',
        'tg_pelatihan',
        'tg_selesai',
        'lama',
        'Sertifikasi',
        'Keterangan',
    ];

    protected $casts = [
        'Sertifikasi' => 'boolean',
        'tg_pelatihan' => 'date',
        'tg_selesai' => 'date',
        'lama' => 'integer',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'Nik', 'Nik');
    }
}

