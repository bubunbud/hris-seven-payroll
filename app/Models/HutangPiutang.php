<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HutangPiutang extends Model
{
    use HasFactory;

    protected $table = 't_hutang_piutang';
    protected $primaryKey = ['dtTanggalAwal', 'dtTanggalAkhir', 'vcNik', 'vcJenis'];
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'dtTanggalAwal',
        'dtTanggalAkhir',
        'vcNik',
        'vcJenis',
        'decAmount',
        'vcPeriodik',
        'vcFlag',
        'vcKeterangan',
        'dtCreate',
        'dtChange',
    ];

    protected $casts = [
        'dtTanggalAwal' => 'date',
        'dtTanggalAkhir' => 'date',
        'decAmount' => 'decimal:2',
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];

    // Relationship dengan Karyawan
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'vcNik', 'Nik');
    }

    // Relationship dengan Master Hutang Piutang
    public function masterHutangPiutang()
    {
        return $this->belongsTo(MasterHutangPiutang::class, 'vcJenis', 'vcJenis');
    }
}

