<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiayaPerjalananDinasDetail extends Model
{
    use HasFactory;

    protected $table = 't_biaya_perjalanan_dinas_detail';
    protected $primaryKey = 'vcCounterDetail';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'vcCounterDetail',
        'vcNoBpd',
        'vcKategoriBiaya',
        'vcSubKategori',
        'dtTanggalDari',
        'dtTanggalSampai',
        'decNilai',
        'decTotal',
        'vcKeterangan',
        'dtCreate',
        'dtChange',
    ];

    protected $casts = [
        'dtTanggalDari' => 'date',
        'dtTanggalSampai' => 'date',
        'decNilai' => 'decimal:2',
        'decTotal' => 'decimal:2',
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];

    // Relationships
    public function header()
    {
        return $this->belongsTo(BiayaPerjalananDinasHeader::class, 'vcNoBpd', 'vcNoBpd');
    }
}







