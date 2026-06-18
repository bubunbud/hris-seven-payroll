<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PeriodeThr extends Model
{
    use HasFactory;

    protected $table = 't_periode_thr';
    protected $primaryKey = ['dtPeriode', 'dtKategori', 'vcKodeDivisi'];
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'dtPeriode',
        'dtKategori',
        'vcNamaHariRaya',
        'vcKodeDivisi',
        'dtCutoffTHR',
        'vcKeterangan',
        'vcStatus',
        'dtCreate',
    ];

    protected $casts = [
        'dtPeriode' => 'string',
        'dtKategori' => 'string',
        'vcNamaHariRaya' => 'string',
        'dtCutoffTHR' => 'date',
        'vcStatus' => 'string',
        'dtCreate' => 'datetime',
    ];

    // Relationship dengan Divisi
    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'vcKodeDivisi', 'vcKodeDivisi');
    }

    // Accessor: format status text
    public function getStatusTextAttribute(): string
    {
        return $this->vcStatus == '1' ? 'Sudah Diproses' : 'Belum Diproses';
    }

    // Accessor: format kategori text (singkat)
    public function getKategoriTextAttribute(): string
    {
        $kategori = $this->dtKategori ?? '';
        // Ambil bagian pertama sebelum kurung (jika ada)
        if (strpos($kategori, '(') !== false) {
            return trim(explode('(', $kategori)[0]);
        }
        return $kategori;
    }
}
