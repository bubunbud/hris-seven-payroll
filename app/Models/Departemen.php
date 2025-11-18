<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departemen extends Model
{
    use HasFactory;

    protected $table = 'm_dept';
    protected $primaryKey = 'vcKodeDept';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'vcKodeDept',
        'vcNamaDept',
        'vcPICDept',
        'vcKodeJabatan',
        'dtCreate',
        'dtChange'
    ];

    protected $casts = [
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime'
    ];

    // Relationship dengan Divisi
    public function divisi()
    {
        return $this->belongsToMany(Divisi::class, 'm_hirarki_dept', 'vcKodeDept', 'vcKodeDivisi');
    }

    // Relationship dengan Jabatan (PIC Departemen)
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'vcKodeJabatan', 'vcKodeJabatan');
    }
}
