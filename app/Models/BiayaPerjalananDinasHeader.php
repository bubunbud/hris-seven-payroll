<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiayaPerjalananDinasHeader extends Model
{
    use HasFactory;

    protected $table = 't_biaya_perjalanan_dinas_header';
    protected $primaryKey = 'vcNoBpd';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'vcNoBpd',
        'vcNoRpd',
        'vcPemberiTugas',
        'decKasbonNilai',
        'vcKasbonTerbilang',
        'decTotalPengeluaran',
        'decKekuranganKelebihan',
        'vcLaporanSingkat',
        'vcMelaporkan',
        'vcMenyetujui',
        'vcMengetahuiHrd',
        'vcMengetahuiFinance',
        'vcStatus',
        'dtCreate',
        'dtChange',
    ];

    protected $casts = [
        'decKasbonNilai' => 'decimal:2',
        'decTotalPengeluaran' => 'decimal:2',
        'decKekuranganKelebihan' => 'decimal:2',
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];

    // Relationships
    public function perjalananDinas()
    {
        return $this->belongsTo(PerjalananDinasHeader::class, 'vcNoRpd', 'vcNoRpd');
    }

    public function details()
    {
        return $this->hasMany(BiayaPerjalananDinasDetail::class, 'vcNoBpd', 'vcNoBpd');
    }
}







