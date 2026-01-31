<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OverrideJadwalSecurity extends Model
{
    use HasFactory;

    protected $table = 't_override_jadwal_security';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'vcNik',
        'dtTanggal',
        'intShiftLama',
        'intShiftBaru',
        'vcAlasan',
        'vcOverrideBy',
        'dtOverrideAt',
        'dtCreate',
    ];

    protected $casts = [
        'dtTanggal' => 'date',
        'intShiftLama' => 'integer',
        'intShiftBaru' => 'integer',
        'dtOverrideAt' => 'datetime',
        'dtCreate' => 'datetime',
    ];

    // Relationship dengan Karyawan
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'vcNik', 'Nik');
    }
}
