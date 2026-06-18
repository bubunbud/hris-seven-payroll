<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LemburHeader extends Model
{
    use HasFactory;

    protected $table = 't_lembur_header';
    protected $primaryKey = 'vcCounter';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'vcCounter',
        'vcBusinessUnit',
        'vcKodeDept',
        'vcKodeBagian',
        'dtTanggalLembur',
        'vcJenisLembur',
        'vcAlasanDasarLembur',
        'decRencanaDurasiJam',
        'dtRencanaDariPukul',
        'dtRencanaSampaiPukul',
        'vcDiajukanOleh',
        'vcJabatanPengaju',
        'vcKepalaDept',
        'is_free_role',
        'vcPenanggungBiaya',
        'vcPenanggungBiayaLainnya',
        'dtCreate',
        'dtChange',
    ];

    protected $casts = [
        'dtTanggalLembur' => 'date',
        'decRencanaDurasiJam' => 'decimal:2',
        'dtRencanaDariPukul' => 'string',
        'dtRencanaSampaiPukul' => 'string',
        'is_free_role' => 'boolean',
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];

    // Relationship dengan Departemen
    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'vcKodeDept', 'vcKodeDept');
    }

    public function bagian()
    {
        return $this->belongsTo(Bagian::class, 'vcKodeBagian', 'vcKodeBagian');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'vcBusinessUnit', 'vcKodeDivisi');
    }

    public function pengaju()
    {
        return $this->belongsTo(Karyawan::class, 'vcDiajukanOleh', 'Nik');
    }

    // Relationship dengan Detail
    public function details()
    {
        return $this->hasMany(LemburDetail::class, 'vcCounterHeader', 'vcCounter');
    }
}
