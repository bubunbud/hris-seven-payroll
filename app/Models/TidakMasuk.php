<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TidakMasuk extends Model
{
    use HasFactory;

    protected $table = 't_tidak_masuk';
    /**
     * PK di DB = komposit (vcNik, vcKodeAbsen, dtTanggalMulai, dtTanggalSelesai).
     * Jangan pakai $record->delete() / $record->update() — gunakan scopeCompositeKey().
     */
    protected $primaryKey = 'vcNik';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public static function scopeCompositeKey($query, string $nik, string $kodeAbsen, string $tanggalMulai, string $tanggalSelesai)
    {
        return $query->where('vcNik', $nik)
            ->where('vcKodeAbsen', $kodeAbsen)
            ->where('dtTanggalMulai', $tanggalMulai)
            ->where('dtTanggalSelesai', $tanggalSelesai);
    }

    protected $fillable = [
        'vcNik',
        'vcKodeAbsen',
        'dtTanggalMulai',
        'dtTanggalSelesai',
        'vcKeterangan',
        'vcDibayar',
        'dtCreate',
        'dtChange',
    ];

    protected $casts = [
        'dtTanggalMulai' => 'date',
        'dtTanggalSelesai' => 'date',
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'vcNik', 'Nik');
    }

    public function jenisAbsen()
    {
        return $this->belongsTo(JenisIjin::class, 'vcKodeAbsen', 'vcKodeAbsen');
    }

    // Accessor: teks periode "dd/mm/YYYY s/d dd/mm/YYYY"
    public function getPeriodeTextAttribute(): string
    {
        $mulai = $this->dtTanggalMulai ? $this->dtTanggalMulai->format('d/m/Y') : '-';
        $selesai = $this->dtTanggalSelesai ? $this->dtTanggalSelesai->format('d/m/Y') : '-';
        return $mulai . ' s/d ' . $selesai;
    }

    // Accessor: jumlah hari inklusif (min 1 bila tanggal valid sama)
    public function getJumlahHariAttribute(): int
    {
        if (!$this->dtTanggalMulai || !$this->dtTanggalSelesai) {
            return 0;
        }
        $start = \Carbon\Carbon::parse($this->dtTanggalMulai);
        $end = \Carbon\Carbon::parse($this->dtTanggalSelesai);
        if ($end->lessThan($start)) {
            return 0;
        }
        // diffInDays is exclusive; tambah 1 untuk inklusif
        return $start->diffInDays($end) + 1;
    }
}
