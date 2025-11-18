<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PeriodeGaji extends Model
{
    use HasFactory;

    protected $table = 't_periode';
    protected $primaryKey = ['dtPeriodeFrom', 'dtPeriodeTo', 'periode', 'vcQuarter', 'vcKodeDivisi'];
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'dtPeriodeFrom',
        'dtPeriodeTo',
        'periode',
        'vcQuarter',
        'vcKodeDivisi',
        'vcStatus',
        'dtCreate',
    ];

    protected $casts = [
        'dtPeriodeFrom' => 'date',
        'dtPeriodeTo' => 'date',
        'periode' => 'date',
        'vcQuarter' => 'string',
        'vcStatus' => 'string',
        'dtCreate' => 'datetime',
    ];

    // Relationship dengan Divisi
    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'vcKodeDivisi', 'vcKodeDivisi');
    }

    // Accessor: format periode text
    public function getPeriodeTextAttribute(): string
    {
        $awal = $this->dtPeriodeFrom ? $this->dtPeriodeFrom->format('d/m/Y') : '-';
        $akhir = $this->dtPeriodeTo ? $this->dtPeriodeTo->format('d/m/Y') : '-';
        return $awal . ' - ' . $akhir;
    }

    // Accessor: format periode pembayaran
    public function getPeriodePembayaranAttribute(): string
    {
        return $this->periode ? $this->periode->format('d/m/Y') : '-';
    }

    // Accessor: intPeriodeClosing untuk kompatibilitas
    public function getIntPeriodeClosingAttribute(): int
    {
        return (int) $this->vcQuarter;
    }

    // Accessor: intStatus untuk kompatibilitas
    public function getIntStatusAttribute(): int
    {
        return (int) $this->vcStatus;
    }
}

