<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisIjin extends Model
{
    use HasFactory;

    protected $table = 'm_jenis_absen';
    protected $primaryKey = 'vcKodeAbsen';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'vcKodeAbsen',
        'vcKeterangan',
        'dtCreate',
        'dtChange'
    ];

    protected $casts = [
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime'
    ];
}



