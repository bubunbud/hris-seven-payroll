<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TukarHariKerja extends Model
{
    use HasFactory;

    protected $table = 't_tukar_hari_kerja';
    protected $primaryKey = ['tanggal_libur', 'nik']; // Composite primary key
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'tanggal_libur',
        'nik',
        'tanggal_kerja',
        'vcKeterangan',
        'vcTipeTukar',
        'vcScope',
        'vcKodeDivisi',
        'vcKodeDept',
        'vcKodeBagian',
        'dtTanggalMulai',
        'dtTanggalSelesai',
        'vcCreatedBy',
        'dtCreatedAt',
        'vcUpdatedBy',
        'dtUpdatedAt',
    ];

    protected $casts = [
        'tanggal_libur' => 'date',
        'tanggal_kerja' => 'date',
        'dtTanggalMulai' => 'date',
        'dtTanggalSelesai' => 'date',
        'dtCreatedAt' => 'datetime',
        'dtUpdatedAt' => 'datetime',
    ];

    /**
     * Relationship dengan detail
     */
    public function details()
    {
        return $this->hasMany(TukarHariKerjaDetail::class, 'tanggal_libur', 'tanggal_libur')
            ->where('nik', $this->nik);
    }

    /**
     * Relationship dengan divisi
     */
    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'vcKodeDivisi', 'vcKodeDivisi');
    }

    /**
     * Relationship dengan karyawan
     */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'Nik');
    }
}
