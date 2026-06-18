<?php

namespace App\Models;

use Carbon\Carbon;
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

    /**
     * Primary key komposit (array) tidak didukung penuh oleh delete() bawaan Eloquent —
     * memicu "Illegal offset type" di Laravel 10. Hapus eksplisit by keempat kolom kunci.
     */
    public function delete()
    {
        if (! $this->exists) {
            return false;
        }

        $awal = $this->dtTanggalAwal instanceof Carbon
            ? $this->dtTanggalAwal->format('Y-m-d')
            : $this->dtTanggalAwal;
        $akhir = $this->dtTanggalAkhir instanceof Carbon
            ? $this->dtTanggalAkhir->format('Y-m-d')
            : $this->dtTanggalAkhir;

        $deleted = static::query()
            ->where('dtTanggalAwal', $awal)
            ->where('dtTanggalAkhir', $akhir)
            ->where('vcNik', $this->vcNik)
            ->where('vcJenis', $this->vcJenis)
            ->delete() > 0;

        if ($deleted) {
            $this->exists = false;
        }

        return $deleted;
    }
}

