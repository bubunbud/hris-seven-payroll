<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Closing extends Model
{
    use HasFactory;

    protected $table = 't_closing';
    protected $primaryKey = ['vcPeriodeAwal', 'vcPeriodeAkhir', 'vcNik', 'periode', 'vcClosingKe'];
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'vcPeriodeAwal', 'vcPeriodeAkhir', 'vcNik', 'periode', 'vcClosingKe',
        'jumlahHari', 'vcKodeGolongan', 'vcKodeDivisi', 'vcStatusPegawai',
        'decGapok', 'decJamKerja',
        'decPotonganHC', 'decPotonganBPR', 'decIuranSPN',
        'decPotonganBPJSKes', 'decPotonganBPJSJHT', 'decPotonganBPJSJP',
        'decPotonganKoperasi', 'decPotonganAbsen', 'decPotonganLain',
        'decVarMakan', 'decVarTransport',
        'decRapel',
        'decUangMakan', 'decTransport',
        'intMakan', 'intTransport',
        'intHC', 'intKHL', 'intHadir', 'intTidakMasuk', 'intJumlahHari',
        'intJmlSakit', 'intJmlAlpha', 'intJmlIzin', 'intJmlIzinR', 'intJmlCuti', 'intJmlTelat',
        'decPremi',
        'decJamLemburKerja1', 'decJamLemburKerja2', 'decJamLemburKerja3',
        'decLemburKerja1', 'decLemburKerja2', 'decLemburKerja3',
        'decJamLemburLibur2', 'decJamLemburLibur3',
        'decLembur2', 'decLembur3',
        'decJamLemburKerja', 'decJamLemburLibur',
        'decTotallembur1', 'decTotallembur2', 'decTotallembur3',
        'intCutiLalu', 'intSakitLalu', 'intHcLalu', 'intIzinLalu', 'intAlphaLalu', 'intTelatLalu',
        'intMakanKerja', 'intMakanLibur', 'intTransportKerja', 'intTransportLibur',
        'decBpjsKesehatan', 'decBpjsNaker', 'decBpjsPensiun',
        'decBebanTgi', 'decBebanSiaExp', 'decBebanSiaProd', 'decBebanRma', 'decBebanSmu', 'decBebanAbnJkt',
        'dtCreate', 'dtChange',
    ];

    protected $casts = [
        'vcPeriodeAwal' => 'date',
        'vcPeriodeAkhir' => 'date',
        'periode' => 'date',
        'jumlahHari' => 'integer',
        'decGapok' => 'decimal:2',
        'decJamKerja' => 'decimal:2',
        'decPotonganHC' => 'decimal:2',
        'decPotonganBPR' => 'decimal:2',
        'decIuranSPN' => 'decimal:2',
        'decPotonganBPJSKes' => 'decimal:2',
        'decPotonganBPJSJHT' => 'decimal:2',
        'decPotonganBPJSJP' => 'decimal:2',
        'decPotonganKoperasi' => 'decimal:2',
        'decPotonganAbsen' => 'decimal:2',
        'decPotonganLain' => 'decimal:2',
        'decVarMakan' => 'decimal:2',
        'decVarTransport' => 'decimal:2',
        'decRapel' => 'decimal:2',
        'decUangMakan' => 'decimal:2',
        'decTransport' => 'decimal:2',
        'intMakan' => 'integer',
        'intTransport' => 'integer',
        'intHC' => 'integer',
        'intKHL' => 'integer',
        'intHadir' => 'integer',
        'intTidakMasuk' => 'integer',
        'intJumlahHari' => 'integer',
        'intJmlSakit' => 'integer',
        'intJmlAlpha' => 'integer',
        'intJmlIzin' => 'integer',
        'intJmlIzinR' => 'integer',
        'intJmlCuti' => 'integer',
        'intJmlTelat' => 'integer',
        'decPremi' => 'decimal:2',
        'decJamLemburKerja1' => 'decimal:2',
        'decJamLemburKerja2' => 'decimal:2',
        'decJamLemburKerja3' => 'decimal:2',
        'decLemburKerja1' => 'decimal:2',
        'decLemburKerja2' => 'decimal:2',
        'decLemburKerja3' => 'decimal:2',
        'decJamLemburLibur2' => 'decimal:2',
        'decJamLemburLibur3' => 'decimal:2',
        'decLembur2' => 'decimal:2',
        'decLembur3' => 'decimal:2',
        'decJamLemburKerja' => 'decimal:2',
        'decJamLemburLibur' => 'decimal:2',
        'decTotallembur1' => 'decimal:2',
        'decTotallembur2' => 'decimal:2',
        'decTotallembur3' => 'decimal:2',
        'intCutiLalu' => 'integer',
        'intSakitLalu' => 'integer',
        'intHcLalu' => 'integer',
        'intIzinLalu' => 'integer',
        'intAlphaLalu' => 'integer',
        'intTelatLalu' => 'integer',
        'intMakanKerja' => 'integer',
        'intMakanLibur' => 'integer',
        'intTransportKerja' => 'integer',
        'intTransportLibur' => 'integer',
        'decBpjsKesehatan' => 'decimal:2',
        'decBpjsNaker' => 'decimal:2',
        'decBpjsPensiun' => 'decimal:2',
        'decBebanTgi' => 'decimal:2',
        'decBebanSiaExp' => 'decimal:2',
        'decBebanSiaProd' => 'decimal:2',
        'decBebanRma' => 'decimal:2',
        'decBebanSmu' => 'decimal:2',
        'decBebanAbnJkt' => 'decimal:2',
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];

    // Relationship dengan Karyawan
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'vcNik', 'Nik');
    }

    // Relationship dengan Gapok
    public function gapok()
    {
        return $this->belongsTo(Gapok::class, 'vcKodeGolongan', 'vcKodeGolongan');
    }

    // Relationship dengan Divisi
    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'vcKodeDivisi', 'vcKodeDivisi');
    }
}
