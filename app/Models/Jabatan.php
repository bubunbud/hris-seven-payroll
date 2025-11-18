<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory;

    protected $table = 'm_jabatan';
    protected $primaryKey = 'vcKodeJabatan';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'vcKodeJabatan',
        'vcNamaJabatan',
        'vcGrade',
        'dtCreate',
        'dtChange'
    ];

    protected $casts = [
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime'
    ];
}
