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
        'vcManagerFinAcc',
        'vcKeuangan',
        'vPPIC',
        'vcPlantManager',
        'vcHrGaManager',
        'vcSeniorFinanceManager',
        'vcGmBackOffice',
        'dtCreate',
        'dtChange'
    ];

    protected $casts = [
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime'
    ];
}
