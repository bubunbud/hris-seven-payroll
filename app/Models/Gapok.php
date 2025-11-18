<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gapok extends Model
{
    use HasFactory;

    protected $table = 'm_gapok';
    protected $primaryKey = 'vcKodeGolongan';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'vcKodeGolongan',
        'upah',
        'tunj_keluarga',
        'tunj_masa_kerja',
        'tunj_jabatan1',
        'tunj_jabatan2',
        'uang_makan',
        'uang_transport',
        'premi',
        'vcKeterangan',
        'dtCreate',
        'dtChange',
    ];

    protected $casts = [
        'upah' => 'decimal:2',
        'tunj_keluarga' => 'decimal:2',
        'tunj_masa_kerja' => 'decimal:2',
        'tunj_jabatan1' => 'decimal:2',
        'tunj_jabatan2' => 'decimal:2',
        'uang_makan' => 'decimal:2',
        'uang_transport' => 'decimal:2',
        'premi' => 'decimal:2',
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];
}
