<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Golongan extends Model
{
    use HasFactory;

    protected $table = 'm_golongan';
    protected $primaryKey = 'vcKodeGolongan';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'vcKodeGolongan',
        'vcNamaGolongan',
        'dtCreate',
        'dtChange'
    ];

    protected $casts = [
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime'
    ];
}
