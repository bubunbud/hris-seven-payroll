<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    use HasFactory;

    protected $table = 'm_hari_libur';
    protected $primaryKey = 'dtTanggal';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'dtTanggal',
        'vcKeterangan',
        'vcTipeHariLibur',
        'dtCreate',
        'dtChange',
    ];

    protected $casts = [
        'dtTanggal' => 'date',
        'dtCreate' => 'date',
        'dtChange' => 'date',
    ];
}
