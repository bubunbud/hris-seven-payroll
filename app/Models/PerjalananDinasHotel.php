<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerjalananDinasHotel extends Model
{
    use HasFactory;

    protected $table = 't_perjalanan_dinas_hotel';
    protected $primaryKey = 'vcCounterHotel';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'vcCounterHotel',
        'vcNoRpd',
        'isMenginap',
        'dtTanggalMenginap',
        'vcKotaProvinsiNegara',
        'vcNamaHotel',
        'vcKeteranganHotel',
        'dtCreate',
        'dtChange',
    ];

    protected $casts = [
        'isMenginap' => 'boolean',
        'dtTanggalMenginap' => 'date',
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];

    // Relationships
    public function header()
    {
        return $this->belongsTo(PerjalananDinasHeader::class, 'vcNoRpd', 'vcNoRpd');
    }
}
