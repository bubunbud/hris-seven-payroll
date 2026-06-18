<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * t_mutasi — PK (nik, NoSK), vcTglSK, vcDivisi, vcDept, vcbagian, vcSeksi, vcJabatan, vcFileSK (nama file di storage).
 * Akses data utama via DB::table di controller (kunci komposit).
 */
class Mutasi extends Model
{
    protected $table = 't_mutasi';

    protected $fillable = [
        'nik',
        'NoSK',
        'vcTglSK',
        'vcDivisi',
        'vcDept',
        'vcbagian',
        'vcSeksi',
        'vcJabatan',
        'vcFileSK',
    ];

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'vcTglSK' => 'date',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'Nik');
    }
}
