<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterHutangPiutang extends Model
{
    use HasFactory;

    protected $table = 'm_hutang_piutang';
    protected $primaryKey = 'vcJenis';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'vcJenis',
        'vcKeterangan',
        'vcHutangPiutang',
        'dtCreate',
        'dtChange',
    ];

    protected $casts = [
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];

    // Relationship dengan HutangPiutang
    public function hutangPiutang()
    {
        return $this->hasMany(HutangPiutang::class, 'vcJenis', 'vcJenis');
    }
}




