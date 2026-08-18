<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MesinFingerprint extends Model
{
    protected $table = 'm_mesin_fingerprint';

    public $timestamps = false;

    protected $fillable = [
        'vcNama',
        'vcMerk',
        'vcTipe',
        'vcIp',
        'intPort',
        'intCommKey',
        'vcAktif',
        'vcKeterangan',
        'dtLastPull',
        'dtCreate',
        'dtChange',
    ];

    protected $casts = [
        'intPort' => 'integer',
        'intCommKey' => 'integer',
        'dtLastPull' => 'datetime',
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];

    public function scopeAktif($query)
    {
        return $query->where('vcAktif', '1');
    }

    public function getLabelAttribute(): string
    {
        return $this->vcNama . ' (' . $this->vcIp . ')';
    }
}
