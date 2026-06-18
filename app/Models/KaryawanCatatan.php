<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KaryawanCatatan extends Model
{
    protected $table = 't_karyawan_catatan';

    protected $fillable = [
        'karyawan_nik',
        'tanggal',
        'jenis',
        'kategori',
        'judul',
        'deskripsi',
        'level',
        'status',
        'no_dokumen',
        'file_lampiran',
        'tanggal_berlaku',
        'tanggal_berakhir',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_berlaku' => 'date',
        'tanggal_berakhir' => 'date',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_nik', 'Nik');
    }
}
