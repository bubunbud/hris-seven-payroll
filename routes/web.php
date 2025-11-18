<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DivisiController;
use App\Http\Controllers\DepartemenController;
use App\Http\Controllers\BagianController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\GolonganController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\JenisIjinController;
use App\Http\Controllers\JenisIzinController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\HariLiburController;
use App\Http\Controllers\GapokController;
use App\Http\Controllers\AbsenController;
use App\Http\Controllers\TidakMasukController;
use App\Http\Controllers\IzinKeluarController;
use App\Http\Controllers\StatistikAbsensiController;
use App\Http\Controllers\HutangPiutangController;
use App\Http\Controllers\RealisasiLemburController;
use App\Http\Controllers\SaldoCutiController;
use App\Http\Controllers\PeriodeGajiController;
use App\Http\Controllers\ClosingController;
use App\Http\Controllers\SlipGajiController;
use App\Http\Controllers\RekapUpahKaryawanController;
use App\Http\Controllers\UpdateClosingGajiController;
use App\Http\Controllers\HirarkiController;
use App\Http\Controllers\InstruksiKerjaLemburController;
use App\Http\Controllers\SeksiController;
use App\Http\Controllers\TarikDataAbsensiController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

// Master Data Routes
Route::resource('divisi', DivisiController::class);
Route::resource('departemen', DepartemenController::class);
Route::post('departemen/get-karyawan-by-jabatan', [DepartemenController::class, 'getKaryawanByJabatan'])->name('departemen.get-karyawan-by-jabatan');
Route::resource('bagian', BagianController::class);
Route::post('bagian/get-karyawan-by-jabatan', [BagianController::class, 'getKaryawanByJabatan'])->name('bagian.get-karyawan-by-jabatan');
Route::resource('seksi', SeksiController::class);
Route::resource('karyawan', KaryawanController::class);
Route::resource('golongan', GolonganController::class);
Route::resource('shift', ShiftController::class);
Route::resource('jenis-ijin', JenisIjinController::class);
Route::resource('jenis-izin', JenisIzinController::class);
Route::resource('jabatan', JabatanController::class);
Route::resource('hari-libur', HariLiburController::class);
Route::resource('gapok', GapokController::class);
Route::get('hirarki', [HirarkiController::class, 'index'])->name('hirarki.index');
Route::post('hirarki/dept', [HirarkiController::class, 'storeDept'])->name('hirarki.store-dept');
Route::post('hirarki/dept/delete', [HirarkiController::class, 'destroyDept'])->name('hirarki.destroy-dept');
Route::post('hirarki/bagian', [HirarkiController::class, 'storeBagian'])->name('hirarki.store-bagian');
Route::post('hirarki/bagian/delete', [HirarkiController::class, 'destroyBagian'])->name('hirarki.destroy-bagian');
Route::post('hirarki/seksi', [HirarkiController::class, 'storeSeksi'])->name('hirarki.store-seksi');
Route::post('hirarki/seksi/delete', [HirarkiController::class, 'destroySeksi'])->name('hirarki.destroy-seksi');

// Absensi routes
Route::get('absen', [AbsenController::class, 'index'])->name('absen.index');
Route::post('absen/export', [AbsenController::class, 'exportExcel'])->name('absen.export');
Route::resource('tidak-masuk', TidakMasukController::class);
Route::resource('izin-keluar', IzinKeluarController::class);
Route::get('absensi/statistik', [StatistikAbsensiController::class, 'index'])->name('absensi.statistik.index');
Route::get('tarik-data-absensi', [TarikDataAbsensiController::class, 'index'])->name('tarik-data-absensi.index');
Route::post('tarik-data-absensi/pull', [TarikDataAbsensiController::class, 'pullData'])->name('tarik-data-absensi.pull');
Route::resource('hutang-piutang', HutangPiutangController::class);
Route::get('hutang-piutang/get-nama', [HutangPiutangController::class, 'getNamaByNik'])->name('hutang-piutang.get-nama');
Route::post('hutang-piutang/upload', [HutangPiutangController::class, 'upload'])->name('hutang-piutang.upload');

// Instruksi Kerja Lembur
Route::get('instruksi-kerja-lembur', [InstruksiKerjaLemburController::class, 'index'])->name('instruksi-kerja-lembur.index');
Route::post('instruksi-kerja-lembur', [InstruksiKerjaLemburController::class, 'store'])->name('instruksi-kerja-lembur.store');
Route::get('instruksi-kerja-lembur/{id}', [InstruksiKerjaLemburController::class, 'show'])->name('instruksi-kerja-lembur.show');
Route::put('instruksi-kerja-lembur/{id}', [InstruksiKerjaLemburController::class, 'update'])->name('instruksi-kerja-lembur.update');
Route::delete('instruksi-kerja-lembur/{id}', [InstruksiKerjaLemburController::class, 'destroy'])->name('instruksi-kerja-lembur.destroy');
Route::post('instruksi-kerja-lembur/get-departemens', [InstruksiKerjaLemburController::class, 'getDepartemensByDivisi'])->name('instruksi-kerja-lembur.get-departemens');
Route::post('instruksi-kerja-lembur/get-bagians', [InstruksiKerjaLemburController::class, 'getBagiansByDivisiDept'])->name('instruksi-kerja-lembur.get-bagians');
Route::post('instruksi-kerja-lembur/get-karyawans', [InstruksiKerjaLemburController::class, 'getKaryawansByBagian'])->name('instruksi-kerja-lembur.get-karyawans');
Route::post('instruksi-kerja-lembur/get-karyawans-by-departemen', [InstruksiKerjaLemburController::class, 'getKaryawansByDepartemen'])->name('instruksi-kerja-lembur.get-karyawans-by-departemen');
Route::post('instruksi-kerja-lembur/get-karyawans-by-divisi', [InstruksiKerjaLemburController::class, 'getKaryawansByDivisi'])->name('instruksi-kerja-lembur.get-karyawans-by-divisi');
Route::post('instruksi-kerja-lembur/get-karyawan-data', [InstruksiKerjaLemburController::class, 'getKaryawanData'])->name('instruksi-kerja-lembur.get-karyawan-data');
Route::post('instruksi-kerja-lembur/get-kepala-dept', [InstruksiKerjaLemburController::class, 'getKepalaDept'])->name('instruksi-kerja-lembur.get-kepala-dept');
Route::post('instruksi-kerja-lembur/check-jenis-lembur', [InstruksiKerjaLemburController::class, 'checkJenisLembur'])->name('instruksi-kerja-lembur.check-jenis-lembur');
Route::get('realisasi-lembur', [RealisasiLemburController::class, 'index'])->name('realisasi-lembur.index');
Route::put('realisasi-lembur/{tanggal}/{nik}', [RealisasiLemburController::class, 'update'])->name('realisasi-lembur.update');
Route::post('realisasi-lembur/bulk', [RealisasiLemburController::class, 'updateBulk'])->name('realisasi-lembur.bulk');
Route::post('realisasi-lembur/{tanggal}/{nik}/confirm', [RealisasiLemburController::class, 'confirmLembur'])->name('realisasi-lembur.confirm');
Route::post('realisasi-lembur/{tanggal}/{nik}/delete', [RealisasiLemburController::class, 'destroy'])->name('realisasi-lembur.destroy');
Route::get('saldo-cuti', [SaldoCutiController::class, 'index'])->name('saldo-cuti.index');
Route::post('saldo-cuti', [SaldoCutiController::class, 'store'])->name('saldo-cuti.store');
Route::get('saldo-cuti/{id}', [SaldoCutiController::class, 'show'])->name('saldo-cuti.show');
Route::post('saldo-cuti/migrate', [SaldoCutiController::class, 'migrateSaldo'])->name('saldo-cuti.migrate');

// Proses Routes
Route::get('periode-gaji', [PeriodeGajiController::class, 'index'])->name('periode-gaji.index');
Route::post('periode-gaji', [PeriodeGajiController::class, 'store'])->name('periode-gaji.store');
Route::delete('periode-gaji', [PeriodeGajiController::class, 'destroy'])->name('periode-gaji.destroy');
Route::post('periode-gaji/delete', [PeriodeGajiController::class, 'destroy'])->name('periode-gaji.destroy-post');
Route::get('closing', [ClosingController::class, 'index'])->name('closing.index');
Route::post('closing', [ClosingController::class, 'store'])->name('closing.store');
Route::get('closing/{periodeAwal}/{periodeAkhir}/{nik}/{periode}/{closingKe}', [ClosingController::class, 'show'])->name('closing.show');
Route::get('view-gaji', [ClosingController::class, 'viewGaji'])->name('view-gaji.index');
Route::resource('update-closing-gaji', UpdateClosingGajiController::class);
Route::post('update-closing-gaji/calculate-working-days', [UpdateClosingGajiController::class, 'calculateWorkingDays'])->name('update-closing-gaji.calculate-working-days');
Route::post('update-closing-gaji/get-gapok', [UpdateClosingGajiController::class, 'getGapokByNik'])->name('update-closing-gaji.get-gapok');
Route::post('update-closing-gaji/get-absensi-p1', [UpdateClosingGajiController::class, 'getAbsensiPeriodeSebelumnya'])->name('update-closing-gaji.get-absensi-p1');

// Laporan Routes
Route::get('slip-gaji', [SlipGajiController::class, 'index'])->name('slip-gaji.index');
Route::post('slip-gaji/preview', [SlipGajiController::class, 'preview'])->name('slip-gaji.preview');
Route::get('slip-gaji/print/{periodeAwal}/{periodeAkhir}/{nik}/{periode}/{closingKe}', [SlipGajiController::class, 'print'])->name('slip-gaji.print');

Route::get('rekap-upah-karyawan', [RekapUpahKaryawanController::class, 'index'])->name('rekap-upah-karyawan.index');
Route::post('rekap-upah-karyawan/preview', [RekapUpahKaryawanController::class, 'preview'])->name('rekap-upah-karyawan.preview');

Route::get('rekap-uang-makan-transport', [App\Http\Controllers\RekapUangMakanTransportController::class, 'index'])->name('rekap-uang-makan-transport.index');
Route::post('rekap-uang-makan-transport/preview', [App\Http\Controllers\RekapUangMakanTransportController::class, 'preview'])->name('rekap-uang-makan-transport.preview');

Route::get('rekap-bank', [App\Http\Controllers\RekapBankController::class, 'index'])->name('rekap-bank.index');
Route::post('rekap-bank/preview', [App\Http\Controllers\RekapBankController::class, 'preview'])->name('rekap-bank.preview');
Route::post('rekap-bank/export-excel', [App\Http\Controllers\RekapBankController::class, 'exportExcel'])->name('rekap-bank.export-excel');

Route::get('rekap-upah-per-bagian-dept', [App\Http\Controllers\RekapUpahPerBagianDeptController::class, 'index'])->name('rekap-upah-per-bagian-dept.index');
Route::post('rekap-upah-per-bagian-dept/preview', [App\Http\Controllers\RekapUpahPerBagianDeptController::class, 'preview'])->name('rekap-upah-per-bagian-dept.preview');

Route::get('rekap-uang-makan-transport-per-bagian-dept', [App\Http\Controllers\RekapUangMakanTransportPerBagianDeptController::class, 'index'])->name('rekap-uang-makan-transport-per-bagian-dept.index');
Route::post('rekap-uang-makan-transport-per-bagian-dept/preview', [App\Http\Controllers\RekapUangMakanTransportPerBagianDeptController::class, 'preview'])->name('rekap-uang-makan-transport-per-bagian-dept.preview');

// Additional routes for karyawan
Route::get('karyawan/{id}/keluarga', [KaryawanController::class, 'getKeluarga']);
Route::post('karyawan/{nik}/keluarga', [KaryawanController::class, 'addFamily']);
Route::put('karyawan/{nik}/keluarga/{hubKeluarga}', [KaryawanController::class, 'updateFamily']);
Route::delete('karyawan/{nik}/keluarga/{hubKeluarga}', [KaryawanController::class, 'deleteFamily']);

Route::get('karyawan/{id}/pendidikan', [KaryawanController::class, 'getPendidikan']);
Route::post('karyawan/{nik}/pendidikan', [KaryawanController::class, 'addPendidikan']);
Route::put('karyawan/{nik}/pendidikan/{education_level}', [KaryawanController::class, 'updatePendidikan']);
Route::delete('karyawan/{nik}/pendidikan/{education_level}', [KaryawanController::class, 'deletePendidikan']);
Route::post('karyawan/copy-pendidikan', [KaryawanController::class, 'copyPendidikan'])->name('karyawan.copy-pendidikan');

// Routes for hierarchical dropdown
Route::post('karyawan/get-departemens', [KaryawanController::class, 'getDepartemensByDivisi'])->name('karyawan.get-departemens');
Route::post('karyawan/get-bagians', [KaryawanController::class, 'getBagiansByDivisiDept'])->name('karyawan.get-bagians');
Route::post('karyawan/generate-nik', [KaryawanController::class, 'generateNik'])->name('karyawan.generate-nik');
Route::get('karyawan/{nik}/copy-data', [KaryawanController::class, 'getKaryawanForCopy'])->name('karyawan.copy-data');
Route::post('karyawan/copy-keluarga', [KaryawanController::class, 'copyKeluarga'])->name('karyawan.copy-keluarga');
