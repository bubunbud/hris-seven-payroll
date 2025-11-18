<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bagian extends Model
{
    use HasFactory;

    protected $table = 'm_bagian';
    protected $primaryKey = 'vcKodeBagian';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'vcKodeBagian',
        'vcNamaBagian',
        'vcPICBagian',
        'vcKodeJabatan',
        'dtCreate',
        'dtChange'
    ];

    protected $casts = [
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime'
    ];

    // Relationship dengan Divisi dan Departemen
    public function divisi()
    {
        return $this->belongsToMany(Divisi::class, 'm_hirarki_bagian', 'vcKodeBagian', 'vcKodeDivisi');
    }

    public function departemen()
    {
        return $this->belongsToMany(Departemen::class, 'm_hirarki_bagian', 'vcKodeBagian', 'vcKodeDept');
    }

    // Relationship dengan Jabatan (PIC Bagian)
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'vcKodeJabatan', 'vcKodeJabatan');
    }
}
