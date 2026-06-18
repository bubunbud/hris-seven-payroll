<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerjalananDinasJadwal extends Model
{
    use HasFactory;

    protected $table = 't_perjalanan_dinas_jadwal';
    protected $primaryKey = 'vcCounterJadwal';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'vcCounterJadwal',
        'vcNoRpd',
        'vcModaPerjalanan',
        'vcHariBerangkat',
        'dtTanggalBerangkat',
        'dtJamBerangkat',
        'vcKeteranganBerangkat',
        'vcHariKembali',
        'dtTanggalKembali',
        'dtJamKembali',
        'vcKeteranganKembali',
        'dtCreate',
        'dtChange',
    ];

    protected $casts = [
        'dtTanggalBerangkat' => 'date',
        'dtJamBerangkat' => 'string', // Jangan cast sebagai datetime, biarkan sebagai string (time format)
        'dtTanggalKembali' => 'date',
        'dtJamKembali' => 'string', // Jangan cast sebagai datetime, biarkan sebagai string (time format)
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];

    // Relationships
    public function header()
    {
        return $this->belongsTo(PerjalananDinasHeader::class, 'vcNoRpd', 'vcNoRpd');
    }
}
