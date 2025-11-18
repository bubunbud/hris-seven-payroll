<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    use HasFactory;

    protected $table = 'm_karyawan';
    protected $primaryKey = 'Nik';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'Nik',
        'Job_ID',
        'Nama',
        'Nama_Depan',
        'Nama_Tengah',
        'Nama_Akhir',
        'dept',
        'vcKodeBagian',
        'Divisi',
        'Gelar',
        'Tgl_Masuk',
        'Gol',
        'Tgl_Berhenti',
        'Tempat_lahir',
        'Group_pegawai',
        'Jabat',
        'TTL',
        'Status_Pegawai',
        'Tinggi_bdn',
        'Berat_bdn',
        'Gol_Darah',
        'Berkacamata',
        'Buta_Warna',
        'Cacat_Fisik',
        'Status_Kawin',
        'Jenis_Kelamin',
        'Agama',
        'Warga_Negara',
        'Pendidikan_Akhir',
        'Katagori',
        'Nama_universitas',
        'Jurusan',
        'Thn_lulus',
        'Alamat',
        'Kecamatan',
        'Kota',
        'Kode_pos',
        'Telp',
        'Cell_Phone1',
        'Cell_Phone2',
        'Personal_Email',
        'Nama_Pasangan',
        'JK_Pasangan',
        'tempat_lahir_pasangan',
        'tgl_lahir_pasangan',
        'Gol_darah_pasangan',
        'Nama_anak1',
        'JK_anak1',
        'Tempat_Lahir_Anak1',
        'TTL_anak1',
        'Gol_Darah_anak1',
        'Nama_anak2',
        'JK_anak2',
        'Tempat_Lahir_Anak2',
        'TTL_anak2',
        'Gol_Darah_anak2',
        'Nama_anak3',
        'JK_anak3',
        'Tempat_Lahir_Anak3',
        'TTL_Anak3',
        'Gol_Darah_anak3',
        'Status_kerja',
        'Edit_Akhir',
        'lokasi_absen',
        'stat_pwd',
        'photo',
        'vcAktif',
        'intCuti',
        'intNoBadge',
        'intNorek',
        'vcShift',
        'vcKodeSeksi',
        'vcSK',
        'dtCreate',
        'dtChange',
        'user_create',
        'create_date',
        'user_update',
        'update_date',
        'deleted',
        'nama_ayah',
        'nama_ibu'
    ];

    protected $casts = [
        'Tgl_Masuk' => 'date',
        'Tgl_Berhenti' => 'datetime',
        'TTL' => 'datetime',
        'tgl_lahir_pasangan' => 'datetime',
        'TTL_anak1' => 'datetime',
        'TTL_anak2' => 'datetime',
        'TTL_Anak3' => 'datetime',
        'Edit_Akhir' => 'datetime',
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
        'create_date' => 'datetime',
        'update_date' => 'datetime'
    ];

    // Relationship dengan Bagian
    public function bagian()
    {
        return $this->belongsTo(Bagian::class, 'vcKodeBagian', 'vcKodeBagian');
    }

    // Relationship dengan Departemen
    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'dept', 'vcKodeDept');
    }

    // Relationship dengan Divisi
    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'Divisi', 'vcKodeDivisi');
    }

    // Relationship dengan Jabatan
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'Jabat', 'vcKodeJabatan');
    }

    // Relationship dengan Keluarga
    public function keluarga()
    {
        return $this->hasMany(Keluarga::class, 'nik', 'Nik');
    }

    // Relationship dengan Pendidikan
    // Relasi: m_karyawan.Nik = t_pendidikan.employee_nik
    public function pendidikan()
    {
        return $this->hasMany(Pendidikan::class, 'employee_nik', 'Nik');
    }

    // Relationship dengan Shift
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'vcShift', 'vcShift');
    }
}
