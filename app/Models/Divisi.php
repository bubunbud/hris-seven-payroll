<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Divisi extends Model
{
    use HasFactory;

    protected $table = 'm_divisi';
    protected $primaryKey = 'vcKodeDivisi';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'vcKodeDivisi',
        'vcNamaDivisi',
        'vcSenin',
        'vcSelasa',
        'vcRabu',
        'vcKamis',
        'vcJumat',
        'vcSabtu',
        'vcMinggu',
        'vcKeterangan',
        'vcStaff',
        'vcKabag',
        'vcKeuangan',
        'vPPIC',
        'vcPlantManager',
        'dtCreate',
        'dtChange'
    ];

    protected $casts = [
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime'
    ];
}
