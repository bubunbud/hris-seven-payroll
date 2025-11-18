<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $table = 'm_shift';
    protected $primaryKey = 'vcShift';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'vcShift',
        'vcMasuk',
        'vcPulang',
        'vcKeterangan',
        'dtCreate',
        'dtChange'
    ];

    protected $casts = [
        'vcMasuk' => 'datetime:H:i',
        'vcPulang' => 'datetime:H:i',
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime'
    ];

    // Relationship with Karyawan
    public function karyawans()
    {
        return $this->hasMany(Karyawan::class, 'vcShift', 'vcShift');
    }
}
