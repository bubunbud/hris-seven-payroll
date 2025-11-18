<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pendidikan extends Model
{
    use HasFactory;

    protected $table = 't_pendidikan';

    // Tabel tidak memiliki primary key auto-increment
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'employee_nik',
        'education_level',      // Jenjang Pendidikan
        'institution_name',      // Nama Sekolah
        'major',                 // Jurusan
        'start_year',            // Tahun Masuk
        'end_year',              // Tahun Selesai
        'gpa'                    // IPK
    ];

    // Relationship dengan Karyawan
    // Relasi: m_karyawan.Nik = t_pendidikan.employee_nik
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'employee_nik', 'Nik');
    }
}
