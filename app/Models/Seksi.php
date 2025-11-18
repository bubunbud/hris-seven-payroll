<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seksi extends Model
{
    use HasFactory;

    protected $table = 'm_seksi';
    protected $primaryKey = 'vcKodeseksi';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'vcKodeseksi',
        'vcNamaseksi',
        'dtCreate',
        'dtChange'
    ];

    protected $casts = [
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime'
    ];

    // Accessor untuk kompatibilitas dengan camelCase
    public function getVcKodeSeksiAttribute()
    {
        return $this->attributes['vcKodeseksi'] ?? null;
    }

    public function getVcNamaSeksiAttribute()
    {
        return $this->attributes['vcNamaseksi'] ?? null;
    }

    // Mutator untuk kompatibilitas dengan camelCase
    public function setVcKodeSeksiAttribute($value)
    {
        $this->attributes['vcKodeseksi'] = $value;
    }

    public function setVcNamaSeksiAttribute($value)
    {
        $this->attributes['vcNamaseksi'] = $value;
    }

    // Relationship dengan Divisi, Departemen, dan Bagian
    public function divisi()
    {
        return $this->belongsToMany(Divisi::class, 'm_hirarki_seksi', 'vcKodeSeksi', 'vcKodeDivisi');
    }

    public function departemen()
    {
        return $this->belongsToMany(Departemen::class, 'm_hirarki_seksi', 'vcKodeSeksi', 'vcKodeDept');
    }

    public function bagian()
    {
        return $this->belongsToMany(Bagian::class, 'm_hirarki_seksi', 'vcKodeSeksi', 'vcKodeBagian');
    }
}
