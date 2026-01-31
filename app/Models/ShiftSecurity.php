<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftSecurity extends Model
{
    use HasFactory;

    protected $table = 'm_shift_security';
    protected $primaryKey = 'vcKodeShift';
    protected $keyType = 'integer';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'vcKodeShift',
        'vcNamaShift',
        'dtJamMasuk',
        'dtJamPulang',
        'isCrossDay',
        'intDurasiJam',
        'intToleransiMasuk',
        'intToleransiPulang',
        'vcKeterangan',
        'dtCreate',
        'dtChange',
    ];

    protected $casts = [
        'dtJamMasuk' => 'datetime:H:i',
        'dtJamPulang' => 'datetime:H:i',
        'isCrossDay' => 'boolean',
        'intDurasiJam' => 'decimal:2',
        'intToleransiMasuk' => 'integer',
        'intToleransiPulang' => 'integer',
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];

    // Relationship dengan JadwalShiftSecurity
    public function jadwalShifts()
    {
        return $this->hasMany(JadwalShiftSecurity::class, 'intShift', 'vcKodeShift');
    }
}
