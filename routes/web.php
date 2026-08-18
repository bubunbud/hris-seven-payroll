<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DivisiController;
use App\Http\Controllers\DepartemenController;
use App\Http\Controllers\BagianController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\ListKaryawanAktifController;
use App\Http\Controllers\GolonganController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\JenisIjinController;
use App\Http\Controllers\JenisIzinController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\HariLiburController;
use App\Http\Controllers\GapokController;
use App\Http\Controllers\AbsenController;
use App\Http\Controllers\BrowseTidakAbsenController;
use App\Http\Controllers\EditAbsensiController;
use App\Http\Controllers\TidakMasukController;
use App\Http\Controllers\IzinKeluarController;
use App\Http\Controllers\StatistikAbsensiController;
use App\Http\Controllers\RekapitulasiAbsensiController;
use App\Http\Controllers\RekapitulasiAbsenAllController;
use App\Http\Controllers\TukarHariKerjaController;
use App\Http\Controllers\RekapitulasiCutiController;
use App\Http\Controllers\HutangPiutangController;
use App\Http\Controllers\RealisasiLemburController;
use App\Http\Controllers\SaldoCutiController;
use App\Http\Controllers\PeriodeGajiController;
use App\Http\Controllers\PeriodeThrController;
use App\Http\Controllers\ClosingController;
use App\Http\Controllers\ClosingThrController;
use App\Http\Controllers\ListThrController;
use App\Http\Controllers\SlipGajiController;
use App\Http\Controllers\SlipThrController;
use App\Http\Controllers\RekapUpahKaryawanController;
use App\Http\Controllers\RekapUpahFinanceVerController;
use App\Http\Controllers\UpdateClosingGajiController;
use App\Http\Controllers\HirarkiController;
use App\Http\Controllers\InstruksiKerjaLemburController;
use App\Http\Controllers\PerjalananDinasController;
use App\Http\Controllers\BiayaPerjalananDinasController;
use App\Http\Controllers\SeksiController;
use App\Http\Controllers\TarikDataAbsensiController;
use App\Http\Controllers\TarikDataAbsensiApiController;
use App\Http\Controllers\TarikDataAbsensiSupabaseController;
use App\Http\Controllers\TarikDataLeaveSupabaseController;
use App\Http\Controllers\MesinFingerprintController;
use App\Http\Controllers\TarikDataFingerprintController;
use App\Http\Controllers\TarikDataIzinController;
use App\Http\Controllers\TarikDataTidakMasukController;
use App\Http\Controllers\ListPengajuanCutiApiController;
use App\Http\Controllers\ListPengajuanIzinApiController;
use App\Http\Controllers\TarikDataHutangPiutangController;
use App\Http\Controllers\JadwalShiftSecurityController;
use App\Http\Controllers\RekapKeterlambatanController;
use App\Http\Controllers\MasterShiftSecurityController;
use App\Http\Controllers\OverrideJadwalSecurityController;
use App\Http\Controllers\BrowseAbsensiSecurityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\UserLoginActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardGroupController;
use App\Http\Controllers\DashboardBUController;
use App\Http\Controllers\DashboardEmployeeController;
use App\Http\Controllers\RekapUpahPerBagianDeptController;
use App\Http\Controllers\RekapUangMakanTransportPerBagianDeptController;
use App\Http\Controllers\LaporanThrController;

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

// Auth Routes (Public)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes (Require Authentication)
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Dashboard Level Group (Holding View) - Permission required
    Route::middleware(['permission:view-dashboard-group'])->group(function () {
        Route::get('/dashboard/group', [DashboardGroupController::class, 'index'])->name('dashboard.group');
    });

    // Dashboard Level Business Unit (BU View) - Permission required
    Route::middleware(['permission:view-dashboard-bu'])->group(function () {
        Route::get('/dashboard/bu', [DashboardBUController::class, 'index'])->name('dashboard.bu');
    });

    // Dashboard Employee Self Service - Permission required
    Route::middleware(['permission:view-dashboard-employee'])->group(function () {
        Route::get('/dashboard/employee', [DashboardEmployeeController::class, 'index'])->name('dashboard.employee');
    });

    // Master Data Routes (permission-based: group atau granular)
    Route::middleware(['permission:view-master-data,view-master-divisi,view-master-departemen,view-master-bagian,view-master-seksi,view-master-karyawan,view-master-golongan,view-master-shift,view-master-tidak-masuk,view-master-ijin-keluar,view-master-jabatan,view-master-hari-libur,view-hirarki'])->group(function () {
        Route::middleware(['permission:view-master-data,view-master-divisi'])->group(function () {
            Route::resource('divisi', DivisiController::class);
        });
        Route::middleware(['permission:view-master-data,view-master-departemen'])->group(function () {
            Route::resource('departemen', DepartemenController::class);
            Route::post('departemen/get-karyawan-by-jabatan', [DepartemenController::class, 'getKaryawanByJabatan'])->name('departemen.get-karyawan-by-jabatan');
            Route::post('departemen/generate-kode', [DepartemenController::class, 'generateKodeDept'])->name('departemen.generate-kode');
        });
        Route::middleware(['permission:view-master-data,view-master-bagian'])->group(function () {
            Route::resource('bagian', BagianController::class);
            Route::post('bagian/get-karyawan-by-jabatan', [BagianController::class, 'getKaryawanByJabatan'])->name('bagian.get-karyawan-by-jabatan');
            Route::post('bagian/generate-kode', [BagianController::class, 'generateKodeBagian'])->name('bagian.generate-kode');
        });
        Route::middleware(['permission:view-master-data,view-master-seksi'])->group(function () {
            Route::resource('seksi', SeksiController::class);
            Route::post('seksi/generate-kode', [SeksiController::class, 'generateKodeSeksi'])->name('seksi.generate-kode');
        });
        Route::middleware(['permission:view-master-data,view-master-karyawan'])->group(function () {
            Route::get('list-karyawan-aktif', [ListKaryawanAktifController::class, 'index'])->name('list-karyawan-aktif.index');
            Route::get('list-karyawan-aktif/export', [ListKaryawanAktifController::class, 'exportExcel'])->name('list-karyawan-aktif.export');
            Route::resource('karyawan', KaryawanController::class);
            Route::get('karyawan/{id}/keluarga', [KaryawanController::class, 'getKeluarga']);
            Route::post('karyawan/{nik}/keluarga', [KaryawanController::class, 'addFamily']);
            Route::put('karyawan/{nik}/keluarga/{hubKeluarga}', [KaryawanController::class, 'updateFamily']);
            Route::delete('karyawan/{nik}/keluarga/{hubKeluarga}', [KaryawanController::class, 'deleteFamily']);
            Route::get('karyawan/{id}/pendidikan', [KaryawanController::class, 'getPendidikan']);
            Route::post('karyawan/{nik}/pendidikan', [KaryawanController::class, 'addPendidikan']);
            Route::put('karyawan/{nik}/pendidikan/{education_level}', [KaryawanController::class, 'updatePendidikan']);
            Route::delete('karyawan/{nik}/pendidikan/{education_level}', [KaryawanController::class, 'deletePendidikan']);
            Route::post('karyawan/copy-pendidikan', [KaryawanController::class, 'copyPendidikan'])->name('karyawan.copy-pendidikan');
            Route::get('karyawan/{id}/pelatihan', [KaryawanController::class, 'getPelatihan']);
            Route::post('karyawan/{nik}/pelatihan', [KaryawanController::class, 'addPelatihan']);
            Route::put('karyawan/{nik}/pelatihan/{nm_pelatihan}', [KaryawanController::class, 'updatePelatihan']);
            Route::delete('karyawan/{nik}/pelatihan/{nm_pelatihan}', [KaryawanController::class, 'deletePelatihan']);
            Route::post('karyawan/copy-pelatihan', [KaryawanController::class, 'copyPelatihan'])->name('karyawan.copy-pelatihan');
            Route::get('karyawan/{id}/mutasi', [KaryawanController::class, 'getMutasi']);
            Route::post('karyawan/{nik}/mutasi', [KaryawanController::class, 'addMutasi']);
            Route::put('karyawan/{nik}/mutasi/{noSK}', [KaryawanController::class, 'updateMutasi']);
            Route::delete('karyawan/{nik}/mutasi/{noSK}', [KaryawanController::class, 'deleteMutasi']);
            Route::post('karyawan/copy-mutasi', [KaryawanController::class, 'copyMutasi'])->name('karyawan.copy-mutasi');
            Route::get('karyawan/{id}/catatan-karyawan', [KaryawanController::class, 'getKaryawanCatatan']);
            Route::post('karyawan/{nik}/catatan-karyawan', [KaryawanController::class, 'addKaryawanCatatan']);
            Route::put('karyawan/{nik}/catatan-karyawan/{id}', [KaryawanController::class, 'updateKaryawanCatatan']);
            Route::delete('karyawan/{nik}/catatan-karyawan/{id}', [KaryawanController::class, 'deleteKaryawanCatatan']);
            Route::post('karyawan/copy-catatan-karyawan', [KaryawanController::class, 'copyKaryawanCatatan'])->name('karyawan.copy-catatan-karyawan');
            Route::post('karyawan/get-departemens', [KaryawanController::class, 'getDepartemensByDivisi'])->name('karyawan.get-departemens');
            Route::post('karyawan/get-bagians', [KaryawanController::class, 'getBagiansByDivisiDept'])->name('karyawan.get-bagians');
            Route::post('karyawan/get-seksis', [KaryawanController::class, 'getSeksisByDivisiDeptBagian'])->name('karyawan.get-seksis');
            Route::post('karyawan/get-jabatans', [KaryawanController::class, 'getJabatansByDivisi'])->name('karyawan.get-jabatans');
            Route::post('karyawan/generate-nik', [KaryawanController::class, 'generateNik'])->name('karyawan.generate-nik');
            Route::get('karyawan/{nik}/copy-data', [KaryawanController::class, 'getKaryawanForCopy'])->name('karyawan.copy-data');
            Route::get('karyawan/{nik}/biodata-cetak', [KaryawanController::class, 'biodataCetak'])->name('karyawan.biodata-cetak');
            Route::post('karyawan/copy-keluarga', [KaryawanController::class, 'copyKeluarga'])->name('karyawan.copy-keluarga');
        });
        Route::middleware(['permission:view-master-data,view-master-golongan'])->group(function () {
            Route::resource('golongan', GolonganController::class);
        });
        Route::middleware(['permission:view-master-data,view-master-shift'])->group(function () {
            Route::resource('shift', ShiftController::class);
        });
        Route::middleware(['permission:view-master-data,view-master-tidak-masuk'])->group(function () {
            Route::resource('jenis-ijin', JenisIjinController::class);
        });
        Route::middleware(['permission:view-master-data,view-master-ijin-keluar'])->group(function () {
            Route::resource('jenis-izin', JenisIzinController::class);
        });
        Route::middleware(['permission:view-master-data,view-master-jabatan'])->group(function () {
            Route::resource('jabatan', JabatanController::class);
            Route::post('jabatan/generate-kode', [JabatanController::class, 'generateKodeJabatan'])->name('jabatan.generate-kode');
        });
        Route::middleware(['permission:view-master-data,view-master-hari-libur'])->group(function () {
            Route::resource('hari-libur', HariLiburController::class);
        });
        Route::middleware(['permission:view-master-data,view-hirarki'])->group(function () {
            Route::get('hirarki', [HirarkiController::class, 'index'])->name('hirarki.index');
            Route::post('hirarki/dept', [HirarkiController::class, 'storeDept'])->name('hirarki.store-dept');
            Route::post('hirarki/dept/delete', [HirarkiController::class, 'destroyDept'])->name('hirarki.destroy-dept');
            Route::post('hirarki/bagian', [HirarkiController::class, 'storeBagian'])->name('hirarki.store-bagian');
            Route::post('hirarki/bagian/delete', [HirarkiController::class, 'destroyBagian'])->name('hirarki.destroy-bagian');
            Route::post('hirarki/seksi', [HirarkiController::class, 'storeSeksi'])->name('hirarki.store-seksi');
            Route::post('hirarki/seksi/delete', [HirarkiController::class, 'destroySeksi'])->name('hirarki.destroy-seksi');
        });
    });

    // Absensi & Operasional HR routes (permission-based: group atau granular)
    Route::middleware(['permission:view-absensi,view-browse-absensi,view-browse-tidak-absen,view-jadwal-shift-satpam,view-report-jadwal-shift,view-master-shift-security,view-override-jadwal,view-tidak-masuk,view-izin-keluar,view-instruksi-kerja-lembur,view-statistik-absensi,view-rekapitulasi-absensi,view-rekapitulasi-absen-all,view-rekapitulasi-cuti,view-saldo-cuti,view-hutang-piutang,view-edit-absensi'])->group(function () {
        Route::middleware(['permission:view-absensi,view-browse-absensi'])->group(function () {
            Route::get('absen', [AbsenController::class, 'index'])->name('absen.index');
            Route::get('absen/print', [AbsenController::class, 'print'])->name('absen.print');
            Route::post('absen/export', [AbsenController::class, 'exportExcel'])->name('absen.export');
        });
        Route::middleware(['permission:view-absensi,view-edit-absensi'])->group(function () {
            Route::get('edit-absensi', [EditAbsensiController::class, 'index'])->name('edit-absensi.index');
            Route::get('edit-absensi/create', [EditAbsensiController::class, 'create'])->name('edit-absensi.create');
            Route::post('edit-absensi/store', [EditAbsensiController::class, 'store'])->name('edit-absensi.store');
            Route::get('edit-absensi/edit', [EditAbsensiController::class, 'edit'])->name('edit-absensi.edit');
            Route::post('edit-absensi/update', [EditAbsensiController::class, 'update'])->name('edit-absensi.update');
            Route::delete('edit-absensi/delete', [EditAbsensiController::class, 'destroy'])->name('edit-absensi.destroy');
        });

        // Autocomplete karyawan (cukup login, tanpa middleware permission khusus)
        Route::get('karyawan/search', [KaryawanController::class, 'search'])
            ->name('karyawan.search');
        Route::middleware(['permission:view-absensi,view-browse-tidak-absen'])->group(function () {
            Route::get('browse-tidak-absen', [BrowseTidakAbsenController::class, 'index'])->name('browse-tidak-absen.index');
        });
        Route::middleware(['permission:view-absensi,view-tidak-masuk'])->group(function () {
            Route::resource('tidak-masuk', TidakMasukController::class);
        });
        Route::middleware(['permission:view-absensi,view-izin-keluar'])->group(function () {
            Route::resource('izin-keluar', IzinKeluarController::class);
            Route::get('izin-keluar/{id}/print', [IzinKeluarController::class, 'print'])->name('izin-keluar.print');
            Route::get('izin-keluar-print-multiple', [IzinKeluarController::class, 'printMultiple'])->name('izin-keluar.print-multiple');
        });
        Route::middleware(['permission:view-absensi,view-statistik-absensi'])->group(function () {
            Route::get('absensi/statistik', [StatistikAbsensiController::class, 'index'])->name('absensi.statistik.index');
        });
        Route::middleware(['permission:view-absensi,view-statistik-absensi'])->group(function () {
            Route::get('absensi/rekap-keterlambatan', [RekapKeterlambatanController::class, 'index'])->name('rekap-keterlambatan.index');
            Route::get('absensi/rekap-keterlambatan/preview', [RekapKeterlambatanController::class, 'preview'])->name('rekap-keterlambatan.preview');
            Route::get('absensi/rekap-keterlambatan/get-departemens', [RekapKeterlambatanController::class, 'getDepartemensByDivisi'])->name('rekap-keterlambatan.get-departemens');
            Route::get('absensi/rekap-keterlambatan/get-bagians', [RekapKeterlambatanController::class, 'getBagiansByDepartemen'])->name('rekap-keterlambatan.get-bagians');
        });
        Route::middleware(['permission:view-absensi,view-rekapitulasi-absensi'])->group(function () {
            Route::get('absensi/rekapitulasi', [RekapitulasiAbsensiController::class, 'index'])->name('rekapitulasi-absensi.index');
            Route::get('absensi/rekapitulasi/print', [RekapitulasiAbsensiController::class, 'print'])->name('rekapitulasi-absensi.print');
        });
        Route::middleware(['permission:view-absensi,view-rekapitulasi-absen-all'])->group(function () {
            Route::get('absensi/rekapitulasi-all', [RekapitulasiAbsenAllController::class, 'index'])->name('rekapitulasi-absen-all.index');
            Route::get('absensi/rekapitulasi-all/print', [RekapitulasiAbsenAllController::class, 'print'])->name('rekapitulasi-absen-all.print');
            Route::get('absensi/rekapitulasi-all/export', [RekapitulasiAbsenAllController::class, 'exportExcel'])->name('rekapitulasi-absen-all.export');
        });
        Route::middleware(['permission:view-absensi'])->group(function () {
            Route::get('tukar-hari-kerja', [TukarHariKerjaController::class, 'index'])->name('tukar-hari-kerja.index');
            Route::get('tukar-hari-kerja/create', [TukarHariKerjaController::class, 'create'])->name('tukar-hari-kerja.create');
            Route::post('tukar-hari-kerja', [TukarHariKerjaController::class, 'store'])->name('tukar-hari-kerja.store');
            Route::get('tukar-hari-kerja/{tanggal_libur}/{nik}', [TukarHariKerjaController::class, 'show'])->name('tukar-hari-kerja.show');
            Route::get('tukar-hari-kerja/{tanggal_libur}/{nik}/edit', [TukarHariKerjaController::class, 'edit'])->name('tukar-hari-kerja.edit');
            Route::put('tukar-hari-kerja/{tanggal_libur}/{nik}', [TukarHariKerjaController::class, 'update'])->name('tukar-hari-kerja.update');
            Route::delete('tukar-hari-kerja/{tanggal_libur}/{nik}', [TukarHariKerjaController::class, 'destroy'])->name('tukar-hari-kerja.destroy');
            Route::post('tukar-hari-kerja/get-karyawan', [TukarHariKerjaController::class, 'getKaryawan'])->name('tukar-hari-kerja.get-karyawan');
            Route::post('tukar-hari-kerja/preview', [TukarHariKerjaController::class, 'preview'])->name('tukar-hari-kerja.preview');
        });
        Route::middleware(['permission:view-absensi,view-rekapitulasi-cuti'])->group(function () {
            Route::get('cuti/rekapitulasi', [RekapitulasiCutiController::class, 'index'])->name('rekapitulasi-cuti.index');
            Route::get('cuti/rekapitulasi/export', [RekapitulasiCutiController::class, 'exportExcel'])->name('rekapitulasi-cuti.export');
        });
        Route::middleware(['permission:view-absensi,view-hutang-piutang,view-proses-gaji'])->group(function () {
            Route::resource('hutang-piutang', HutangPiutangController::class);
            Route::get('hutang-piutang/get-nama', [HutangPiutangController::class, 'getNamaByNik'])->name('hutang-piutang.get-nama');
            Route::post('hutang-piutang/upload', [HutangPiutangController::class, 'upload'])->name('hutang-piutang.upload');
        });

        Route::middleware(['permission:view-absensi,view-instruksi-kerja-lembur'])->group(function () {
            Route::get('instruksi-kerja-lembur', [InstruksiKerjaLemburController::class, 'index'])->name('instruksi-kerja-lembur.index');
            Route::post('instruksi-kerja-lembur', [InstruksiKerjaLemburController::class, 'store'])->name('instruksi-kerja-lembur.store');
            Route::get('instruksi-kerja-lembur/{id}', [InstruksiKerjaLemburController::class, 'show'])->name('instruksi-kerja-lembur.show');
            Route::put('instruksi-kerja-lembur/{id}', [InstruksiKerjaLemburController::class, 'update'])->name('instruksi-kerja-lembur.update');
            Route::delete('instruksi-kerja-lembur/{id}', [InstruksiKerjaLemburController::class, 'destroy'])->name('instruksi-kerja-lembur.destroy');
            Route::post('instruksi-kerja-lembur/get-departemens', [InstruksiKerjaLemburController::class, 'getDepartemensByDivisi'])->name('instruksi-kerja-lembur.get-departemens');
            Route::post('instruksi-kerja-lembur/get-bagians', [InstruksiKerjaLemburController::class, 'getBagiansByDivisiDept'])->name('instruksi-kerja-lembur.get-bagians');
            Route::post('instruksi-kerja-lembur/get-karyawans', [InstruksiKerjaLemburController::class, 'getKaryawansByBagian'])->name('instruksi-kerja-lembur.get-karyawans');
            Route::post('instruksi-kerja-lembur/get-karyawans-by-departemen', [InstruksiKerjaLemburController::class, 'getKaryawansByDepartemen'])->name('instruksi-kerja-lembur.get-karyawans-by-departemen');
            Route::post('instruksi-kerja-lembur/get-all-karyawans', [InstruksiKerjaLemburController::class, 'getAllKaryawans'])->name('instruksi-kerja-lembur.get-all-karyawans');
            Route::post('instruksi-kerja-lembur/get-karyawans-by-divisi', [InstruksiKerjaLemburController::class, 'getKaryawansByDivisi'])->name('instruksi-kerja-lembur.get-karyawans-by-divisi');
            Route::post('instruksi-kerja-lembur/get-karyawan-data', [InstruksiKerjaLemburController::class, 'getKaryawanData'])->name('instruksi-kerja-lembur.get-karyawan-data');
            Route::post('instruksi-kerja-lembur/get-kepala-dept', [InstruksiKerjaLemburController::class, 'getKepalaDept'])->name('instruksi-kerja-lembur.get-kepala-dept');
            Route::post('instruksi-kerja-lembur/check-jenis-lembur', [InstruksiKerjaLemburController::class, 'checkJenisLembur'])->name('instruksi-kerja-lembur.check-jenis-lembur');
            Route::post('instruksi-kerja-lembur/calculate-nominal', [InstruksiKerjaLemburController::class, 'calculateLemburNominal'])->name('instruksi-kerja-lembur.calculate-nominal');
        });
        Route::middleware(['permission:view-absensi,view-perjalanan-dinas'])->group(function () {
            Route::get('perjalanan-dinas', [PerjalananDinasController::class, 'index'])->name('perjalanan-dinas.index');
            Route::post('perjalanan-dinas', [PerjalananDinasController::class, 'store'])->name('perjalanan-dinas.store');
            Route::get('perjalanan-dinas/{id}', [PerjalananDinasController::class, 'show'])->name('perjalanan-dinas.show');
            Route::put('perjalanan-dinas/{id}', [PerjalananDinasController::class, 'update'])->name('perjalanan-dinas.update');
            Route::delete('perjalanan-dinas/{id}', [PerjalananDinasController::class, 'destroy'])->name('perjalanan-dinas.destroy');
            Route::get('perjalanan-dinas/{id}/print', [PerjalananDinasController::class, 'print'])->name('perjalanan-dinas.print');
            Route::get('perjalanan-dinas/get-karyawan-data', [PerjalananDinasController::class, 'getKaryawanData'])->name('perjalanan-dinas.get-karyawan-data');
            Route::post('perjalanan-dinas/{id}/update-absensi', [PerjalananDinasController::class, 'triggerUpdateAbsensi'])->name('perjalanan-dinas.trigger-update-absensi');
        });

        // Biaya Perjalanan Dinas (BPD)
        Route::middleware(['permission:view-absensi,view-perjalanan-dinas,view-biaya-perjalanan-dinas'])->group(function () {
            Route::get('biaya-perjalanan-dinas', [BiayaPerjalananDinasController::class, 'index'])->name('biaya-perjalanan-dinas.index');
            Route::post('biaya-perjalanan-dinas', [BiayaPerjalananDinasController::class, 'store'])->name('biaya-perjalanan-dinas.store');
            Route::get('biaya-perjalanan-dinas/{id}', [BiayaPerjalananDinasController::class, 'show'])->name('biaya-perjalanan-dinas.show');
            Route::put('biaya-perjalanan-dinas/{id}', [BiayaPerjalananDinasController::class, 'update'])->name('biaya-perjalanan-dinas.update');
            Route::delete('biaya-perjalanan-dinas/{id}', [BiayaPerjalananDinasController::class, 'destroy'])->name('biaya-perjalanan-dinas.destroy');
            Route::get('biaya-perjalanan-dinas/{id}/print', [BiayaPerjalananDinasController::class, 'print'])->name('biaya-perjalanan-dinas.print');
            Route::get('biaya-perjalanan-dinas/get-rpd-data/{noRpd}', [BiayaPerjalananDinasController::class, 'getRpdData'])->name('biaya-perjalanan-dinas.get-rpd-data');
            Route::get('biaya-perjalanan-dinas/convert-terbilang/{number}', [BiayaPerjalananDinasController::class, 'convertTerbilang'])->name('biaya-perjalanan-dinas.convert-terbilang');
        });
        Route::middleware(['permission:view-absensi,view-saldo-cuti'])->group(function () {
            Route::get('saldo-cuti', [SaldoCutiController::class, 'index'])->name('saldo-cuti.index');
            Route::post('saldo-cuti', [SaldoCutiController::class, 'store'])->name('saldo-cuti.store');
            Route::get('saldo-cuti/{id}', [SaldoCutiController::class, 'show'])->name('saldo-cuti.show');
            Route::post('saldo-cuti/migrate', [SaldoCutiController::class, 'migrateSaldo'])->name('saldo-cuti.migrate');
            Route::post('saldo-cuti/import', [SaldoCutiController::class, 'importExcel'])->name('saldo-cuti.import');
        });
        Route::middleware(['permission:view-absensi,view-jadwal-shift-satpam'])->group(function () {
            Route::get('browse-absensi-security', [BrowseAbsensiSecurityController::class, 'index'])->name('browse-absensi-security.index');
        });
        Route::middleware(['permission:view-absensi,view-jadwal-shift-satpam'])->group(function () {
            Route::get('jadwal-shift-security', [JadwalShiftSecurityController::class, 'index'])->name('jadwal-shift-security.index');
            Route::post('jadwal-shift-security', [JadwalShiftSecurityController::class, 'store'])->name('jadwal-shift-security.store');
            Route::post('jadwal-shift-security/override', [JadwalShiftSecurityController::class, 'override'])->name('jadwal-shift-security.override');
            Route::post('jadwal-shift-security/get-jadwal', [JadwalShiftSecurityController::class, 'getJadwalByPeriode'])->name('jadwal-shift-security.get-jadwal');
            Route::post('jadwal-shift-security/copy-previous-month', [JadwalShiftSecurityController::class, 'copyFromPreviousMonth'])->name('jadwal-shift-security.copy-previous-month');
            Route::post('jadwal-shift-security/import', [JadwalShiftSecurityController::class, 'importExcel'])->name('jadwal-shift-security.import');
        });
        Route::middleware(['permission:view-absensi,view-report-jadwal-shift'])->group(function () {
            Route::get('jadwal-shift-security/report', [JadwalShiftSecurityController::class, 'report'])->name('jadwal-shift-security.report');
        });
        Route::middleware(['permission:view-absensi,view-master-shift-security'])->group(function () {
            Route::resource('master-shift-security', MasterShiftSecurityController::class)->names([
                'index' => 'master-shift-security.index',
                'create' => 'master-shift-security.create',
                'store' => 'master-shift-security.store',
                'show' => 'master-shift-security.show',
                'edit' => 'master-shift-security.edit',
                'update' => 'master-shift-security.update',
                'destroy' => 'master-shift-security.destroy',
            ]);
        });
        Route::middleware(['permission:view-absensi,view-override-jadwal'])->group(function () {
            Route::get('override-jadwal-security', [OverrideJadwalSecurityController::class, 'index'])->name('override-jadwal-security.index');
            Route::get('override-jadwal-security/{id}', [OverrideJadwalSecurityController::class, 'show'])->name('override-jadwal-security.show');
        });
    });

    // Proses & Payroll routes (permission-based: group atau granular)
    Route::middleware(['permission:view-proses-gaji,view-master-gaji-pokok,view-hutang-piutang,view-realisasi-lembur,view-periode-gaji,view-closing-gaji,view-update-closing-gaji,view-rekap-gaji'])->group(function () {
        Route::middleware(['permission:view-proses-gaji,view-master-gaji-pokok'])->group(function () {
            Route::resource('gapok', GapokController::class);
        });
        Route::middleware(['permission:view-proses-gaji,view-realisasi-lembur'])->group(function () {
            Route::get('realisasi-lembur', [RealisasiLemburController::class, 'index'])->name('realisasi-lembur.index');
            Route::put('realisasi-lembur/{tanggal}/{nik}', [RealisasiLemburController::class, 'update'])->name('realisasi-lembur.update');
            Route::post('realisasi-lembur/bulk', [RealisasiLemburController::class, 'updateBulk'])->name('realisasi-lembur.bulk');
            Route::post('realisasi-lembur/{tanggal}/{nik}/confirm', [RealisasiLemburController::class, 'confirmLembur'])->name('realisasi-lembur.confirm');
            Route::post('realisasi-lembur/{tanggal}/{nik}/delete', [RealisasiLemburController::class, 'destroy'])->name('realisasi-lembur.destroy');
        });
        Route::middleware(['permission:view-proses-gaji,view-periode-gaji'])->group(function () {
            Route::get('periode-gaji', [PeriodeGajiController::class, 'index'])->name('periode-gaji.index');
            Route::post('periode-gaji', [PeriodeGajiController::class, 'store'])->name('periode-gaji.store');
            Route::delete('periode-gaji', [PeriodeGajiController::class, 'destroy'])->name('periode-gaji.destroy');
            Route::post('periode-gaji/delete', [PeriodeGajiController::class, 'destroy'])->name('periode-gaji.destroy-post');
        });
        Route::middleware(['permission:view-proses-gaji,view-periode-thr'])->group(function () {
            Route::get('periode-thr', [PeriodeThrController::class, 'index'])->name('periode-thr.index');
            Route::post('periode-thr', [PeriodeThrController::class, 'store'])->name('periode-thr.store');
            Route::delete('periode-thr', [PeriodeThrController::class, 'destroy'])->name('periode-thr.destroy');
            Route::post('periode-thr/delete', [PeriodeThrController::class, 'destroy'])->name('periode-thr.destroy-post');
        });
        Route::middleware(['permission:view-proses-gaji,view-closing-gaji'])->group(function () {
            Route::get('closing', [ClosingController::class, 'index'])->name('closing.index');
            Route::post('closing', [ClosingController::class, 'store'])->name('closing.store');
            Route::get('closing/{periodeAwal}/{periodeAkhir}/{nik}/{periode}/{closingKe}', [ClosingController::class, 'show'])->name('closing.show');
        });
        Route::middleware(['permission:view-proses-gaji,view-closing-thr'])->group(function () {
            Route::get('closing-thr', [ClosingThrController::class, 'index'])->name('closing-thr.index');
            Route::post('closing-thr', [ClosingThrController::class, 'store'])->name('closing-thr.store');
        });
        Route::middleware(['permission:view-proses-gaji,view-list-thr'])->group(function () {
            Route::get('list-thr', [ListThrController::class, 'index'])->name('list-thr.index');
        });
        Route::middleware(['permission:view-proses-gaji,view-update-closing-gaji'])->group(function () {
            Route::resource('update-closing-gaji', UpdateClosingGajiController::class);
            Route::post('update-closing-gaji/calculate-working-days', [UpdateClosingGajiController::class, 'calculateWorkingDays'])->name('update-closing-gaji.calculate-working-days');
            Route::post('update-closing-gaji/get-gapok', [UpdateClosingGajiController::class, 'getGapokByNik'])->name('update-closing-gaji.get-gapok');
            Route::post('update-closing-gaji/get-absensi-p1', [UpdateClosingGajiController::class, 'getAbsensiPeriodeSebelumnya'])->name('update-closing-gaji.get-absensi-p1');
        });
        Route::middleware(['permission:view-proses-gaji,view-rekap-gaji'])->group(function () {
            Route::get('view-gaji', [ClosingController::class, 'viewGaji'])->name('view-gaji.index');
        });
    });

    // Laporan Routes (permission-based: group atau granular)
    Route::middleware(['permission:view-laporan,view-slip-gaji,view-rekap-upah-karyawan,view-rekap-uang-makan-transport,view-rekap-bank,view-rekap-upah-per-bagian,view-rekap-tm-tu-per-bagian,view-laporan-thr'])->group(function () {
        Route::middleware(['permission:view-laporan,view-slip-gaji'])->group(function () {
            Route::get('slip-gaji', [SlipGajiController::class, 'index'])->name('slip-gaji.index');
            Route::post('slip-gaji/preview', [SlipGajiController::class, 'preview'])->name('slip-gaji.preview');
            Route::get('slip-gaji/print/{periodeAwal}/{periodeAkhir}/{nik}/{periode}/{closingKe}', [SlipGajiController::class, 'print'])->name('slip-gaji.print');
        });
        Route::middleware(['permission:view-laporan,view-laporan-thr'])->group(function () {
            Route::get('slip-thr', [SlipThrController::class, 'index'])->name('slip-thr.index');
            Route::post('slip-thr/preview', [SlipThrController::class, 'preview'])->name('slip-thr.preview');
        });
        Route::middleware(['permission:view-laporan,view-rekap-upah-karyawan'])->group(function () {
            Route::get('rekap-upah-karyawan', [RekapUpahKaryawanController::class, 'index'])->name('rekap-upah-karyawan.index');
            Route::post('rekap-upah-karyawan/preview', [RekapUpahKaryawanController::class, 'preview'])->name('rekap-upah-karyawan.preview');
        });
        Route::middleware(['permission:view-laporan,view-rekap-upah-karyawan'])->group(function () {
            Route::get('rekap-upah-finance-ver', [RekapUpahFinanceVerController::class, 'index'])->name('rekap-upah-finance-ver.index');
            Route::post('rekap-upah-finance-ver/preview', [RekapUpahFinanceVerController::class, 'preview'])->name('rekap-upah-finance-ver.preview');
            Route::post('rekap-upah-finance-ver/export-excel', [RekapUpahFinanceVerController::class, 'exportExcel'])->name('rekap-upah-finance-ver.export-excel');
        });
        Route::middleware(['permission:view-laporan,view-rekap-uang-makan-transport'])->group(function () {
            Route::get('rekap-uang-makan-transport', [App\Http\Controllers\RekapUangMakanTransportController::class, 'index'])->name('rekap-uang-makan-transport.index');
            Route::post('rekap-uang-makan-transport/preview', [App\Http\Controllers\RekapUangMakanTransportController::class, 'preview'])->name('rekap-uang-makan-transport.preview');
        });
        Route::middleware(['permission:view-laporan,view-rekap-bank'])->group(function () {
            Route::get('rekap-bank', [App\Http\Controllers\RekapBankController::class, 'index'])->name('rekap-bank.index');
            Route::post('rekap-bank/preview', [App\Http\Controllers\RekapBankController::class, 'preview'])->name('rekap-bank.preview');
            Route::post('rekap-bank/export-excel', [App\Http\Controllers\RekapBankController::class, 'exportExcel'])->name('rekap-bank.export-excel');
        });
        Route::middleware(['permission:view-laporan,view-laporan-thr'])->group(function () {
            Route::get('rekap-bank-thr', [App\Http\Controllers\RekapBankThrController::class, 'index'])->name('rekap-bank-thr.index');
            Route::post('rekap-bank-thr/preview', [App\Http\Controllers\RekapBankThrController::class, 'preview'])->name('rekap-bank-thr.preview');
            Route::post('rekap-bank-thr/export-excel', [App\Http\Controllers\RekapBankThrController::class, 'exportExcel'])->name('rekap-bank-thr.export-excel');
        });
        Route::middleware(['permission:view-laporan,view-rekap-upah-per-bagian'])->group(function () {
            Route::get('rekap-upah-per-bagian-dept', [App\Http\Controllers\RekapUpahPerBagianDeptController::class, 'index'])->name('rekap-upah-per-bagian-dept.index');
            Route::post('rekap-upah-per-bagian-dept/preview', [App\Http\Controllers\RekapUpahPerBagianDeptController::class, 'preview'])->name('rekap-upah-per-bagian-dept.preview');
        });
        Route::middleware(['permission:view-laporan,view-rekap-tm-tu-per-bagian'])->group(function () {
            Route::get('rekap-uang-makan-transport-per-bagian-dept', [App\Http\Controllers\RekapUangMakanTransportPerBagianDeptController::class, 'index'])->name('rekap-uang-makan-transport-per-bagian-dept.index');
            Route::post('rekap-uang-makan-transport-per-bagian-dept/preview', [App\Http\Controllers\RekapUangMakanTransportPerBagianDeptController::class, 'preview'])->name('rekap-uang-makan-transport-per-bagian-dept.preview');
        });
        Route::middleware(['permission:view-laporan,view-laporan-thr'])->group(function () {
            Route::get('laporan-thr', [LaporanThrController::class, 'index'])->name('laporan-thr.index');
            Route::post('laporan-thr/preview', [LaporanThrController::class, 'preview'])->name('laporan-thr.preview');
            Route::get('laporan-thr-staff', [LaporanThrController::class, 'indexStaff'])->name('laporan-thr-staff.index');
            Route::post('laporan-thr-staff/preview', [LaporanThrController::class, 'previewStaff'])->name('laporan-thr-staff.preview');
        });
    });

    // Settings Routes (permission-based: group atau granular)
    Route::middleware(['permission:view-settings,view-tarik-data-absensi,view-tarik-data-absensi-api,view-tarik-data-absensi-supabase,view-tarik-data-leave-supabase,view-tarik-data-fingerprint,view-mesin-fingerprint,view-tarik-data-izin,view-tarik-data-tidak-masuk,view-list-pengajuan-cuti-api,view-list-pengajuan-izin-api,view-tarik-data-hutang-piutang,view-logs,view-login-activity,manage-users,manage-roles,manage-permissions'])->group(function () {
        Route::middleware(['permission:view-settings,view-tarik-data-absensi'])->group(function () {
            Route::get('tarik-data-absensi', [TarikDataAbsensiController::class, 'index'])->name('tarik-data-absensi.index');
            Route::post('tarik-data-absensi/pull', [TarikDataAbsensiController::class, 'pullData'])->name('tarik-data-absensi.pull');
        });
        Route::middleware(['permission:view-settings,view-tarik-data-absensi-api'])->group(function () {
            Route::get('tarik-data-absensi-api', [TarikDataAbsensiApiController::class, 'index'])->name('tarik-data-absensi-api.index');
            Route::post('tarik-data-absensi-api/pull', [TarikDataAbsensiApiController::class, 'pullData'])->name('tarik-data-absensi-api.pull');
        });
        Route::middleware(['permission:view-settings,view-tarik-data-absensi-supabase'])->group(function () {
            Route::get('tarik-data-absensi-supabase', [TarikDataAbsensiSupabaseController::class, 'index'])->name('tarik-data-absensi-supabase.index');
            Route::post('tarik-data-absensi-supabase/pull', [TarikDataAbsensiSupabaseController::class, 'pull'])->name('tarik-data-absensi-supabase.pull');
            Route::post('tarik-data-absensi-supabase/save', [TarikDataAbsensiSupabaseController::class, 'save'])->name('tarik-data-absensi-supabase.save');
        });
        Route::middleware(['permission:view-settings,view-tarik-data-leave-supabase'])->group(function () {
            Route::get('tarik-data-leave-supabase', [TarikDataLeaveSupabaseController::class, 'index'])->name('tarik-data-leave-supabase.index');
            Route::post('tarik-data-leave-supabase/pull', [TarikDataLeaveSupabaseController::class, 'pull'])->name('tarik-data-leave-supabase.pull');
            Route::post('tarik-data-leave-supabase/save', [TarikDataLeaveSupabaseController::class, 'save'])->name('tarik-data-leave-supabase.save');
        });
        Route::middleware(['permission:view-settings,view-mesin-fingerprint'])->group(function () {
            Route::get('mesin-fingerprint', [MesinFingerprintController::class, 'index'])->name('mesin-fingerprint.index');
            Route::post('mesin-fingerprint', [MesinFingerprintController::class, 'store'])->name('mesin-fingerprint.store');
            Route::get('mesin-fingerprint/{id}', [MesinFingerprintController::class, 'show'])->name('mesin-fingerprint.show');
            Route::put('mesin-fingerprint/{id}', [MesinFingerprintController::class, 'update'])->name('mesin-fingerprint.update');
            Route::delete('mesin-fingerprint/{id}', [MesinFingerprintController::class, 'destroy'])->name('mesin-fingerprint.destroy');
            Route::post('mesin-fingerprint/{id}/test-connection', [MesinFingerprintController::class, 'testConnection'])->name('mesin-fingerprint.test-connection');
        });
        Route::middleware(['permission:view-settings,view-tarik-data-fingerprint'])->group(function () {
            Route::get('tarik-data-fingerprint', [TarikDataFingerprintController::class, 'index'])->name('tarik-data-fingerprint.index');
            Route::post('tarik-data-fingerprint/pull', [TarikDataFingerprintController::class, 'pull'])->name('tarik-data-fingerprint.pull');
            Route::post('tarik-data-fingerprint/save', [TarikDataFingerprintController::class, 'save'])->name('tarik-data-fingerprint.save');
        });
        Route::middleware(['permission:view-settings,view-tarik-data-izin'])->group(function () {
            Route::get('tarik-data-izin', [TarikDataIzinController::class, 'index'])->name('tarik-data-izin.index');
            Route::post('tarik-data-izin/pull', [TarikDataIzinController::class, 'pullData'])->name('tarik-data-izin.pull');
        });
        Route::middleware(['permission:view-settings,view-tarik-data-tidak-masuk'])->group(function () {
            Route::get('tarik-data-tidak-masuk', [TarikDataTidakMasukController::class, 'index'])->name('tarik-data-tidak-masuk.index');
            Route::post('tarik-data-tidak-masuk/pull', [TarikDataTidakMasukController::class, 'pullData'])->name('tarik-data-tidak-masuk.pull');
        });
        Route::middleware(['permission:view-settings,view-list-pengajuan-cuti-api'])->group(function () {
            Route::get('list-pengajuan-cuti-api', [ListPengajuanCutiApiController::class, 'index'])->name('list-pengajuan-cuti-api.index');
            Route::post('list-pengajuan-cuti-api/import', [ListPengajuanCutiApiController::class, 'import'])->name('list-pengajuan-cuti-api.import');
        });
        Route::middleware(['permission:view-settings,view-list-pengajuan-izin-api'])->group(function () {
            Route::get('list-pengajuan-izin-api', [ListPengajuanIzinApiController::class, 'index'])->name('list-pengajuan-izin-api.index');
            Route::post('list-pengajuan-izin-api/import', [ListPengajuanIzinApiController::class, 'import'])->name('list-pengajuan-izin-api.import');
        });
        Route::middleware(['permission:view-settings,view-tarik-data-hutang-piutang'])->group(function () {
            Route::get('tarik-data-hutang-piutang', [TarikDataHutangPiutangController::class, 'index'])->name('tarik-data-hutang-piutang.index');
            Route::post('tarik-data-hutang-piutang/pull', [TarikDataHutangPiutangController::class, 'pullData'])->name('tarik-data-hutang-piutang.pull');
        });

        Route::middleware('permission:manage-users')->group(function () {
            Route::resource('users', UserController::class);
        });

        Route::middleware('permission:manage-roles')->group(function () {
            Route::resource('roles', RoleController::class);
        });

        Route::middleware('permission:manage-permissions')->group(function () {
            Route::resource('permissions', PermissionController::class);
        });

        Route::middleware('permission:view-logs')->group(function () {
            Route::get('logs', [ActivityLogController::class, 'index'])->name('logs.index');
            Route::get('logs/{id}', [ActivityLogController::class, 'show'])->name('logs.show');
            Route::get('logs/export/csv', [ActivityLogController::class, 'export'])->name('logs.export');
        });

        Route::middleware(['permission:view-settings,view-login-activity'])->group(function () {
            Route::get('login-activity', [UserLoginActivityController::class, 'index'])->name('login-activity.index');
        });
    });
});
