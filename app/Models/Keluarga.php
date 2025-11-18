<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keluarga extends Model
{
    use HasFactory;

    protected $table = 't_keluarga';
    // Composite primary key: nik + hubKeluarga + NamaKeluarga
    // Ini memungkinkan beberapa anggota dengan hubungan yang sama (misalnya 3 anak dengan nama berbeda)
    protected $primaryKey = ['nik', 'hubKeluarga', 'NamaKeluarga'];
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nik',
        'hubKeluarga',
        'NamaKeluarga',
        'jenKelamin',
        'temLahir',
        'tglLahir',
        'golDarah'
    ];

    protected $casts = [
        'tglLahir' => 'date'
    ];

    // Relationship dengan Karyawan
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'Nik');
    }
}
