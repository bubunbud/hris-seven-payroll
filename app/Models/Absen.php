<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absen extends Model
{
    use HasFactory;

    protected $table = 't_absen';
    protected $primaryKey = ['dtTanggal', 'vcNik'];
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'dtTanggal',
        'vcNik',
        'dtJamMasuk',
        'dtJamKeluar',
        'dtJamMasukLembur',
        'dtJamKeluarLembur',
        'intDurasiIstirahat',
        'vcCounter',
        'vcCfmLembur',
        'vcketerangan',
        'dtCreate',
        'dtChange',
    ];

    protected $casts = [
        'dtTanggal' => 'date',
        // Simpan kolom jam sebagai string untuk menghindari tanggal ganda dari cast
        'dtJamMasuk' => 'string',
        'dtJamKeluar' => 'string',
        'dtJamMasukLembur' => 'string',
        'dtJamKeluarLembur' => 'string',
        'intDurasiIstirahat' => 'integer',
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];

    // Relationship with Karyawan
    public function karyawan()
    {
        // Relasi ke m_karyawan: t_absen.vcNik -> m_karyawan.Nik
        return $this->belongsTo(Karyawan::class, 'vcNik', 'Nik');
    }

    // Relationship with LemburHeader
    public function lemburHeader()
    {
        // Relasi ke t_lembur_header: t_absen.vcCounter -> t_lembur_header.vcCounter
        return $this->belongsTo(LemburHeader::class, 'vcCounter', 'vcCounter');
    }

    // Helper method to calculate total hours
    public function getTotalJamAttribute()
    {
        if (!$this->dtJamMasuk || !$this->dtJamKeluar) {
            return 0;
        }

        $tanggal = $this->dtTanggal instanceof \Carbon\Carbon
            ? $this->dtTanggal->copy()
            : \Carbon\Carbon::parse($this->dtTanggal);

        $masuk = $tanggal->copy()->setTimeFromTimeString((string) $this->dtJamMasuk);
        $keluar = $tanggal->copy()->setTimeFromTimeString((string) $this->dtJamKeluar);

        // Handle overnight work
        if ($keluar->lessThan($masuk)) {
            $keluar->addDay();
        }

        return round($masuk->diffInHours($keluar, true), 1);
    }

    // Helper method to get status
    public function getStatusAttribute()
    {
        if (!$this->dtJamMasuk && !$this->dtJamKeluar) {
            return 'Tidak Masuk';
        }

        if (!$this->dtJamMasuk || !$this->dtJamKeluar) {
            return 'Absen Tidak Lengkap';
        }

        $totalJam = $this->total_jam;
        if ($totalJam >= 8) {
            return 'HKN'; // Hari Kerja Normal
        } elseif ($totalJam > 0) {
            return 'Absen Tidak Lengkap';
        } else {
            return 'Tidak Masuk';
        }
    }
}
