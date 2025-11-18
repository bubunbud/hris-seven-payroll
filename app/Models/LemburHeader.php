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
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];

    // Relationship dengan Departemen
    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'vcKodeDept', 'vcKodeDept');
    }

    // Relationship dengan Bagian
    public function bagian()
    {
        return $this->belongsTo(Bagian::class, 'vcKodeBagian', 'vcKodeBagian');
    }

    // Relationship dengan Detail
    public function details()
    {
        return $this->hasMany(LemburDetail::class, 'vcCounterHeader', 'vcCounter');
    }
}
