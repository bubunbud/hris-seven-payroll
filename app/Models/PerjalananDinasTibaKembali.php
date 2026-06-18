<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerjalananDinasTibaKembali extends Model
{
    use HasFactory;

    protected $table = 't_perjalanan_dinas_tiba_kembali';
    protected $primaryKey = 'vcCounterTibaKembali';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'vcCounterTibaKembali',
        'vcNoRpd',
        'vcHariTiba',
        'dtTanggalTiba',
        'dtJamTiba',
        'vcHariKembali',
        'dtTanggalKembali',
        'dtJamKembali',
        'vcKeteranganKedatangan',
        'vcTandaTanganPihakBerwenang',
        'dtCreate',
        'dtChange',
    ];

    protected $casts = [
        'dtTanggalTiba' => 'date',
        'dtJamTiba' => 'string', // Changed from 'datetime' to 'string' to prevent Carbon parsing issues
        'dtTanggalKembali' => 'date',
        'dtJamKembali' => 'string', // Changed from 'datetime' to 'string' to prevent Carbon parsing issues
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];

    // Relationships
    public function header()
    {
        return $this->belongsTo(PerjalananDinasHeader::class, 'vcNoRpd', 'vcNoRpd');
    }
}
