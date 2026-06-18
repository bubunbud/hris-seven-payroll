<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_closing', function (Blueprint $table) {
            // Primary key: composite key untuk periode, nik, dan closing
            $table->date('vcPeriodeAwal');
            $table->date('vcPeriodeAkhir');
            $table->string('vcNik', 8);
            $table->date('periode'); // Periode gajian (1 atau 15)
            $table->string('vcClosingKe', 1); // Periode closing (1 atau 2)
            
            // Informasi dasar
            $table->integer('jumlahHari')->default(0)->comment('Jumlah hari kerja dalam periode');
            $table->string('vcKodeGolongan', 10)->nullable();
            $table->string('vcKodeDivisi', 10)->nullable();
            $table->string('vcStatusPegawai', 20)->nullable();
            
            // Gaji pokok dan jam kerja
            $table->decimal('decGapok', 15, 2)->default(0)->comment('Gaji Pokok (setengah bulan)');
            $table->decimal('decJamKerja', 8, 2)->default(0)->comment('Total jam kerja');
            
            // Potongan
            $table->decimal('decPotonganHC', 12, 2)->default(0)->comment('Potongan jam ijin pribadi keluar komplek');
            $table->decimal('decPotonganBPR', 12, 2)->default(0)->comment('Potongan DPLK/Asuransi');
            $table->decimal('decIuranSPN', 12, 2)->default(0)->comment('Potongan Iuran SPN');
            $table->decimal('decPotonganBPJSKes', 12, 2)->default(0)->comment('Potongan BPJS Kesehatan');
            $table->decimal('decPotonganBPJSJHT', 12, 2)->default(0)->comment('Potongan BPJS JHT');
            $table->decimal('decPotonganBPJSJP', 12, 2)->default(0)->comment('Potongan BPJS JP');
            $table->decimal('decPotonganKoperasi', 12, 2)->default(0)->comment('Potongan Koperasi');
            $table->decimal('decPotonganLain', 12, 2)->default(0)->comment('Potongan Lain-lain');
            
            // Variable tarif
            $table->decimal('decVarMakan', 10, 2)->default(0)->comment('Tarif uang makan per kali');
            $table->decimal('decVarTransport', 10, 2)->default(0)->comment('Tarif uang transport per kali');
            
            // Tambahan
            $table->decimal('decRapel', 12, 2)->default(0)->comment('Selisih upah/rapel');
            
            // Tunjangan makan dan transport
            $table->decimal('decUangMakan', 12, 2)->default(0)->comment('Total uang makan');
            $table->decimal('decTransport', 12, 2)->default(0)->comment('Total uang transport');
            $table->integer('intMakan')->default(0)->comment('Jumlah kali uang makan');
            $table->integer('intTransport')->default(0)->comment('Jumlah kali uang transport');
            
            // Absensi
            $table->integer('intHC')->default(0)->comment('Jumlah kali ijin keluar komplek pribadi');
            $table->integer('intKHL')->default(0)->comment('Jumlah kali kerja hari libur/lembur hari libur');
            $table->integer('intHadir')->default(0)->comment('Jumlah kehadiran');
            $table->integer('intTidakMasuk')->default(0)->comment('Jumlah hari tidak masuk pribadi');
            $table->integer('intJumlahHari')->default(0)->comment('Jumlah hari kerja');
            $table->integer('intJmlSakit')->default(0)->comment('Jumlah sakit');
            $table->integer('intJmlAlpha')->default(0)->comment('Jumlah alpha');
            $table->integer('intJmlIzin')->default(0)->comment('Jumlah Izin Pribadi');
            $table->integer('intJmlIzinR')->default(0)->comment('Jumlah Izin Resmi');
            $table->integer('intJmlCuti')->default(0)->comment('Jumlah Cuti');
            $table->integer('intJmlTelat')->default(0)->comment('Jumlah telat');
            
            // Premi
            $table->decimal('decPremi', 12, 2)->default(0)->comment('Premi Hadir');
            
            // Lembur hari kerja
            $table->decimal('decJamLemburKerja1', 8, 2)->default(0)->comment('Jam lembur hari kerja ke-1');
            $table->decimal('decJamLemburKerja2', 8, 2)->default(0)->comment('Jam lembur hari kerja ke-2');
            $table->decimal('decJamLemburKerja3', 8, 2)->default(0)->comment('Jam lembur hari kerja ke-3');
            $table->decimal('decLemburKerja1', 12, 2)->default(0)->comment('Nominal lembur jam ke-1 hari kerja');
            $table->decimal('decLemburKerja2', 12, 2)->default(0)->comment('Nominal lembur jam ke-2 hari kerja');
            $table->decimal('decLemburKerja3', 12, 2)->default(0)->comment('Nominal lembur jam ke-3 hari kerja');
            
            // Lembur hari libur
            $table->decimal('decJamLemburLibur2', 8, 2)->default(0)->comment('Jam lembur libur ke-2');
            $table->decimal('decJamLemburLibur3', 8, 2)->default(0)->comment('Jam lembur libur ke-3');
            $table->decimal('decLembur2', 12, 2)->default(0)->comment('Total nominal lembur jam ke-2');
            $table->decimal('decLembur3', 12, 2)->default(0)->comment('Total nominal lembur jam ke-3');
            
            // Grand total lembur
            $table->decimal('decJamLemburKerja', 8, 2)->default(0)->comment('Grand total jam lembur hari kerja');
            $table->decimal('decJamLemburLibur', 8, 2)->default(0)->comment('Grand total jam lembur hari libur');
            $table->decimal('decTotallembur1', 12, 2)->default(0)->comment('Total nominal lembur jam 1');
            $table->decimal('decTotallembur2', 12, 2)->default(0)->comment('Total nominal lembur jam 2');
            $table->decimal('decTotallembur3', 12, 2)->default(0)->comment('Total nominal lembur jam 3');
            
            // Absensi periode lalu (untuk periode 2)
            $table->integer('intCutiLalu')->default(0)->comment('Cuti periode 1');
            $table->integer('intSakitLalu')->default(0)->comment('Sakit periode 1');
            $table->integer('intHcLalu')->default(0)->comment('Ijin datang siang/pulang cepat periode 1');
            $table->integer('intIzinLalu')->default(0)->comment('Ijin pribadi periode 1');
            $table->integer('intAlphaLalu')->default(0)->comment('Alpha periode 1');
            $table->integer('intTelatLalu')->default(0)->comment('Telat periode 1');
            
            // Makan dan transport breakdown
            $table->integer('intMakanKerja')->default(0)->comment('Jumlah makan di hari kerja');
            $table->integer('intMakanLibur')->default(0)->comment('Jumlah makan di hari lembur libur');
            $table->integer('intTransportKerja')->default(0)->comment('Jumlah transport di hari kerja');
            $table->integer('intTransportLibur')->default(0)->comment('Jumlah transport di hari lembur libur');
            
            // BPJS (untuk display)
            $table->decimal('decBpjsKesehatan', 12, 2)->default(0)->comment('BPJS Kesehatan');
            $table->decimal('decBpjsNaker', 12, 2)->default(0)->comment('BPJS JHT');
            $table->decimal('decBpjsPensiun', 12, 2)->default(0)->comment('BPJS JP');
            
            // Beban lembur per rekanan
            $table->decimal('decBebanTgi', 12, 2)->default(0)->comment('Beban lembur Rekanan TGI');
            $table->decimal('decBebanSiaExp', 12, 2)->default(0)->comment('Beban lembur SIA Export');
            $table->decimal('decBebanSiaProd', 12, 2)->default(0)->comment('Beban lembur SIA Produksi');
            $table->decimal('decBebanRma', 12, 2)->default(0)->comment('Beban lembur RMA');
            $table->decimal('decBebanSmu', 12, 2)->default(0)->comment('Beban lembur Sutek');
            $table->decimal('decBebanAbnJkt', 12, 2)->default(0)->comment('Beban lembur Abadinusa Jakarta');
            
            // Timestamps
            $table->datetime('dtCreate')->nullable();
            $table->datetime('dtChange')->nullable();
            
            // Primary key composite
            $table->primary(['vcPeriodeAwal', 'vcPeriodeAkhir', 'vcNik', 'periode', 'vcClosingKe']);
            
            // Indexes
            $table->index('vcNik');
            $table->index('periode');
            $table->index('vcClosingKe');
            $table->index(['vcPeriodeAwal', 'vcPeriodeAkhir']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_closing');
    }
};
