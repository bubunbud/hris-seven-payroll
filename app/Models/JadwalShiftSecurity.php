<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalShiftSecurity extends Model
{
    use HasFactory;

    protected $table = 't_jadwal_shift_security';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'vcNik',
        'dtTanggal',
        'intShift',
        'vcKeterangan',
        'isOverride',
        'vcOverrideBy',
        'dtOverrideAt',
        'dtCreate',
        'dtChange',
    ];

    protected $casts = [
        'dtTanggal' => 'date',
        'intShift' => 'integer',
        'isOverride' => 'boolean',
        'dtOverrideAt' => 'datetime',
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];

    // Relationship dengan Karyawan
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'vcNik', 'Nik');
    }

    // Relationship dengan ShiftSecurity
    public function shiftSecurity()
    {
        return $this->belongsTo(ShiftSecurity::class, 'intShift', 'vcKodeShift');
    }
}
