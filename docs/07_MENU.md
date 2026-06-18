# 07. Dokumentasi menu sidebar (fungsi, proses, rumus, file, tabel)

Dokumen ini mengikuti **`resources/views/layouts/app.blade.php`**. Layout **`resources/views/absen/layouts/app.blade.php`** menyalin pola menu yang sama untuk subset halaman.

**Cara membaca tiap butir**

- **Fungsi & output:** apa yang dilakukan menu dan apa yang user lihat/unduh.
- **Proses:** alur utama CRUD / batch / laporan.
- **Algoritma / formulasi:** ringkasan; untuk payroll penuh selalu rujuk **`ClosingController`** + **`LemburCalculationService`** + **`03-formulasi-dan-alur-perhitungan.md`**.
- **File:** controller utama + folder view (relatif `resources/views/`); route ada di **`routes/web.php`**.
- **Tabel:** entitas DB utama (bukan daftar kolom).

---

## Umum: pola Master Data (CRUD referensi)

Untuk resource standar **index/create/edit** Laravel, proses umum: validasi input form **->** `store` / `update` **->** **`m_*`**. Tanpa rumus bisnis numerik. Detail route: `Route::resource(...)`.

---

## 1. Dashboard

### 1.1 Dashboard Harian

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Ringkasan operasional harian: absensi 3 hari terakhir, tidak masuk hari ini, izin keluar hari ini, karyawan tidak absen hari ini (halaman + paginasi). |
| **Proses** | Query agregat ke **`t_absen`**, **`t_tidak_masuk`**, **`t_izin`**, **`m_karyawan`** dengan filter tanggal dan karyawan aktif. |
| **Algoritma** | Filter tanggal + join organisasi; pagination. |
| **File** | **`app/Http/Controllers/DashboardController.php`**, view **`dashboard.blade.php`**. |
| **Tabel** | `t_absen`, `t_tidak_masuk`, `t_izin`, `m_karyawan`, `m_divisi`, `m_bagian`; referensi jenis di modul terkait. |

### 1.2 Dashboard Group / BU / Karyawan

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Ringkasan KPI/penampilan per level (holding, BU, self-service karyawan)—tepatnya mengikuti implementasi masing-masing controller. |
| **Proses** | Load data teragregasi atau terfilter permission. |
| **Algoritma** | Sesuai query di controller bersangkutan (bukan satu rumus payroll). |
| **File** | **`DashboardGroupController`**, **`DashboardBUController`**, **`DashboardEmployeeController`**; view **`dashboard/group/index.blade.php`**, **`dashboard/bu/index.blade.php`**, **`dashboard/employee/index.blade.php`**. |
| **Tabel** | Bermacam join ke `m_karyawan`, `t_absen`, dll. tergantung implementasi. |

---

## 2. Master Data

### 2.1 Master Karyawan + sub (keluarga, pendidikan, pelatihan, mutasi, catatan)

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Kelola biodata, penempatan hierarki (divisi/dept/bagian/seksi/jabatan/gol), JSON sub-form untuk keluarga, pendidikan, pelatihan, mutasi SK, catatan internal; cetak biodata. |
| **Proses** | CRUD utama pada **`m_karyawan`** + API internal `get*/add*/update*/delete*` untuk tabel anak; generate NIK; salin data antar karyawan. |
| **Algoritma** | Validasi field; relasi ke kode master; duplikasi data (copy) per sub-form. |
| **File** | **`KaryawanController.php`**; view **`karyawan/*.blade.php`**. |
| **Tabel** | **`m_karyawan`**, **`t_keluarga`**, **`t_pendidikan`**, **`t_pelatihan`**, **`t_mutasi`**, **`t_karyawan_catatan`**; referensi **`m_divisi`**, **`m_dept`**, **`m_bagian`**, **`m_seksi`**, **`m_jabatan`**, **`m_golongan`**. |

### 2.2 List Data Karyawan Aktif

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Daftar karyawan aktif + export Excel. |
| **Proses** | Query filter aktif; export via Maatwebsite Excel. |
| **File** | **`ListKaryawanAktifController.php`**, view **`list-karyawan-aktif/*.blade.php`**. |
| **Tabel** | **`m_karyawan`**, join organisasi. |

### 2.3 Master referensi organisasi & shift & libur (pola CRUD)

| Menu | Controller | Folder view (umum) | Tabel |
|------|------------|--------------------|--------|
| Master Divisi | `DivisiController` | `divisi/` | `m_divisi` |
| Master Departemen | `DepartemenController` | `departemen/` | `m_dept` |
| Master Bagian | `BagianController` | `bagian/` | `m_bagian` |
| Master Seksi | `SeksiController` | `seksi/` | `m_seksi` |
| Master Golongan | `GolonganController` | `golongan/` | `m_golongan` |
| Master Shift Kerja | `ShiftController` | `shift/` | `m_shift` |
| Master Tidak Masuk | `JenisIjinController` | `jenis-ijin/` | `m_jenis_absen` |
| Master Ijin Keluar | `JenisIzinController` | `jenis-izin/` | `m_jenis_izin` |
| Master Jabatan | `JabatanController` | `jabatan/` | `m_jabatan` |
| Master Hari Libur | `HariLiburController` | `hari-libur/` | `m_hari_libur` |

**Proses / rumus:** CRUD; hari libur mempengaruhi perhitungan hari kerja di **`ClosingController`** / helper hari kerja.

### 2.4 Group Hierarki

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Susun hierarki dept/bagian/seksi dari satu layar. |
| **Proses** | POST store/delete per level. |
| **File** | **`HirarkiController.php`**, view **`hirarki/*.blade.php`**. |
| **Tabel** | **`m_dept`**, **`m_bagian`**, **`m_seksi`** (sesuai aksi). |

---

## 3. Absensi & operasional HR

### 3.1 Browse Absensi

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Tabel rekaman fingerprint/absensi per filter; print/export. |
| **Proses** | Query **`t_absen`** + join karyawan; filter periode. |
| **File** | **`AbsenController.php`**, view **`absen/`** atau setara. |
| **Tabel** | **`t_absen`**, **`m_karyawan`**, master organisasi. |

### 3.2 Input/Edit Absensi

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Entri/koreksi manual jam masuk/keluar. |
| **Proses** | Form **->** validasi **->** upsert **`t_absen`**. |
| **File** | **`EditAbsensiController.php`**, view **`edit-absensi/`**. |
| **Tabel** | **`t_absen`**. |

### 3.3 Browse Tidak Absen

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Daftar hari kerja tanpa rekaman absen. |
| **Proses** | Bandingkan kalender kerja vs **`t_absen`** (logika di controller). |
| **File** | **`BrowseTidakAbsenController.php`**. |
| **Tabel** | **`t_absen`**, **`m_karyawan`**, libur/tukar (helper). |

### 3.4 Jadwal Shift Satpam / Master Shift Security / Override / Browse Absensi Security

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Kelola shift security, override, impor Excel, browse absensi vs jadwal. |
| **Proses** | CRUD + copy bulan + import; **`SecurityAbsensiService`** untuk konsistensi browse. |
| **Algoritma** | Penjajaran jadwal vs **`t_absen`**; detail di service. |
| **File** | **`JadwalShiftSecurityController`**, **`MasterShiftSecurityController`**, **`OverrideJadwalSecurityController`**, **`BrowseAbsensiSecurityController`**; view sesuai nama route. |
| **Tabel** | **`m_shift_security`**, **`t_jadwal_shift_security`**, **`t_override_jadwal_security`**, **`t_absen`**, **`m_karyawan`**. |

### 3.5 Izin Tidak Masuk

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | CRUD pengajuan tidak masuk dengan jenis dari master. |
| **Proses** | Resource Laravel ke **`t_tidak_masuk`**. |
| **File** | **`TidakMasukController.php`**. |
| **Tabel** | **`t_tidak_masuk`**, **`m_jenis_absen`**, **`m_karyawan`**. |

### 3.6 Izin Keluar Komplek

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Form izin keluar; cetak; aturan tipe izin / jam. |
| **Proses** | CRUD **`t_izin`**; validasi bisnis spesifik (lihat controller). |
| **File** | **`IzinKeluarController.php`**. |
| **Tabel** | **`t_izin`**, **`m_jenis_izin`**, **`m_karyawan`**. |

### 3.7 Instruksi Kerja Lembur

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Rencana lembur; hitung nominal via AJAX. |
| **Proses** | Simpan header/detail lembur; **`calculateLemburNominal`**. |
| **Algoritma** | Tarif & jenis lembur (detail di controller + integrasi **`LemburCalculationService`** saat closing). |
| **File** | **`InstruksiKerjaLemburController.php`**. |
| **Tabel** | **`t_lembur_header`**, **`t_lembur_detail`** (dan terkait). |

### 3.8 Form Perjalanan Dinas (RPD)

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Header, karyawan, jadwal, hotel, tiba-kembali; cetak; opsi update absensi. |
| **Proses** | Multi-step save ke beberapa tabel; print blade. |
| **File** | **`PerjalananDinasController.php`**. |
| **Tabel** | **`t_perjalanan_dinas_header`**, **`_karyawan`**, **`_jadwal`**, **`_hotel`**, **`_tiba_kembali`**. |

### 3.9 Form Biaya Perjalanan Dinas (BPD)

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Biaya dinas terkait RPD; status form; cetak; terbilang. |
| **Proses** | CRUD header/detail; link ke no RPD. |
| **File** | **`BiayaPerjalananDinasController.php`**. |
| **Tabel** | **`t_biaya_perjalanan_dinas_header`**, **`t_biaya_perjalanan_dinas_detail`**. |

### 3.10 Statistik Absensi

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Agregasi kehadiran/izin/telat untuk review manajemen. |
| **Proses** | Query agregat multi sumber absensi. |
| **File** | **`StatistikAbsensiController.php`**. |
| **Tabel** | **`t_absen`**, **`t_tidak_masuk`**, **`t_izin`**, master. |

### 3.11 Saldo Cuti

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Kelola saldo cuti; import; migrasi saldo antar tahun. |
| **Proses** | CRUD **`m_saldo_cuti`**; import Excel. |
| **File** | **`SaldoCutiController.php`**. |
| **Tabel** | **`m_saldo_cuti`**, **`m_karyawan`**. |

### 3.12 Tukar Hari Kerja

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Tukar hari libur dengan hari kerja per NIK; mempengaruhi hari kerja payroll. |
| **Proses** | CRUD header + detail. |
| **Algoritma** | Digabung di **`ClosingController`** / **`HariKerjaHelper`** saat hitung hari kerja. |
| **File** | **`TukarHariKerjaController.php`**. |
| **Tabel** | **`t_tukar_hari_kerja`**, **`t_tukar_hari_kerja_detail`**. |

### 3.13 Hutang-Piutang Karyawan (juga di menu Absensi jika permission)

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Catat hutang/piutang untuk potongan gaji. |
| **Proses** | Resource + upload; potongan dihitung saat closing (lihat **`calculatePotonganHutangPiutang`**). |
| **File** | **`HutangPiutangController.php`**. |
| **Tabel** | **`t_hutang_piutang`**, **`m_hutang_piutang`**, **`m_karyawan`**. |

---

## 4. Proses Payroll

### 4.1 Master Gaji Pokok

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Tarif per golongan: upah, tunjangan, uang makan/transport, premi, dsb. |
| **Proses** | CRUD **`m_gapok`**. |
| **Algoritma** | Dipakai closing: `gapokPerBulan` = jumlah komponen di **`ClosingController::calculateEmployeePayroll`** lalu setengah bulan untuk **`decGapok`**. |
| **File** | **`GapokController.php`**, view **`gapok/`**. |
| **Tabel** | **`m_gapok`**. |

### 4.2 Realisasi Lembur

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Verifikasi/konfirmasi jam lembur menuju perhitungan gaji. |
| **Proses** | Update baris lembur; bulk/confirm. |
| **File** | **`RealisasiLemburController.php`**. |
| **Tabel** | **`t_lembur_header`**, **`t_lembur_detail`**. |

### 4.3 Periode Closing Gaji

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Definisi jendela **`dtPeriodeFrom`/`To`**, tanggal pembayaran **`periode`**, **`vcQuarter`**, divisi, status. |
| **Proses** | Insert/hapus baris **`t_periode`**. |
| **File** | **`PeriodeGajiController.php`**. |
| **Tabel** | **`t_periode`**. |

### 4.4 Periode Closing THR

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Definisi periode THR. |
| **Proses** | CRUD **`t_periode_thr`**. |
| **File** | **`PeriodeThrController.php`**. |
| **Tabel** | **`t_periode_thr`**. |

### 4.5 Closing Gaji

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Batch hitung gaji per divisi/periode terpilih; isi **`t_closing`**; update **`t_periode.vcStatus`**. |
| **Proses** | `store` **->** `calculatePayroll` **->** per karyawan **`calculateEmployeePayroll`** (absensi, lembur, tunjangan, potongan BPJS Q1, premi Q2 dari closing P1, dll.). |
| **Algoritma** | Rumus penuh dalam **`ClosingController`** (ribuan baris); lembur dipisah **`LemburCalculationService`**; premi lihat blok `vcQuarter == '2'`. |
| **File** | **`ClosingController.php`**, view **`proses/closing/index.blade.php`**. |
| **Tabel** | **`t_closing`**, **`t_periode`**, **`m_karyawan`**, **`m_gapok`**, **`t_absen`**, **`t_lembur_*`**, **`t_hutang_piutang`**, **`m_hari_libur`**, **`t_tukar_hari_kerja*`**, dll. |

### 4.6 Closing THR / List THR

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Proses & daftar hasil THR. |
| **Proses** | **`ClosingThrController`**, **`ListThrController`**. |
| **File** | Controller di atas; view **`proses/`** / **`list-thr/`**. |
| **Tabel** | **`t_closing_thr`**, **`t_periode_thr`**. |

### 4.7 Update Closing Gaji

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Koreksi pasca-closing per karyawan/periode. |
| **Proses** | Resource + AJAX bantu (hari kerja, gapok, absensi P1). |
| **File** | **`UpdateClosingGajiController.php`**. |
| **Tabel** | **`t_closing`**, sumber baca sama seperti closing. |

### 4.8 View Rekap Gaji

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Tampilan rekap dari **`t_closing`** (bukan proses hitung ulang). |
| **Proses** | Query filter tampilan. |
| **File** | Metode di **`ClosingController`** (`viewGaji`), view terkait. |
| **Tabel** | **`t_closing`**, **`m_karyawan`**. |

---

## 5. Laporan

Pola umum: **filter periode/divisi** **->** **`preview`** (HTML) atau **`print`** / **`exportExcel`**.

| Menu | Controller | Output utama | Tabel inti |
|------|------------|--------------|------------|
| Cetak Slip Gaji | `SlipGajiController` | Preview/cetak slip | `t_closing`, `m_karyawan` |
| Slip THR | `SlipThrController` | Preview slip THR | `t_closing_thr`, … |
| Rekap Upah Karyawan | `RekapUpahKaryawanController` | Tabel rekap | `t_closing`, … |
| Rekap Uang Makan & Transport | `RekapUangMakanTransportController` | Rekap TM/TU | `t_closing` |
| Rekap Bank | `RekapBankController` | Rekap + Excel | `t_closing` |
| Rekap Upah Per Bagian/Dept. | `RekapUpahPerBagianDeptController` | Agregasi per bagian | `t_closing`, `m_karyawan` |
| Rekap TM, TU Per Bagian/Dept. | `RekapUangMakanTransportPerBagianDeptController` | Agregasi TM/TU | `t_closing`, `m_karyawan` |
| Rekap Upah Finance Ver | `RekapUpahFinanceVerController` | Rekap + Excel export class | `t_closing`, … |
| Rekap THR Operator/Staff | `LaporanThrController` | Dua mode index | `t_closing_thr`, … |
| Rekap Bank THR | `RekapBankThrController` | Excel | `t_closing_thr` |
| Report Jadwal Shift | `JadwalShiftSecurityController@report` | Laporan jadwal | `t_jadwal_shift_security`, … |
| Rekapitulasi Absensi | `RekapitulasiAbsensiController` | Print/rekap | `t_absen`, … |
| Rekapitulasi Absen All | `RekapitulasiAbsenAllController` | Rekap + Excel | `t_absen`, … |
| Rekap Absensi Keterlambatan | `RekapKeterlambatanController` | Analisis telat | `t_absen` |
| Rekapitulasi Cuti | `RekapitulasiCutiController` | Rekap + export | saldo/cuti/transaksi terkait |

**Algoritma:** mayoritas agregasi SQL + format; tidak menggantikan rumus **`t_closing`** (sudah dihitung saat closing).

---

## 6. Settings

### 6.1 Tarik Data (Absensi / Izin / Tidak Masuk / Hutang Piutang)

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Ambil data dari DB remote (parametrik host/tabel/kolom) ke lokal. |
| **Proses** | Koneksi PDO dinamis; insert/update **`t_*`**. |
| **File** | **`TarikDataAbsensiController`**, **`TarikDataIzinController`**, **`TarikDataTidakMasukController`**, **`TarikDataHutangPiutangController`**; view **`tarik-data-*/`**. |
| **Tabel** | Sasaran: **`t_absen`**, **`t_izin`**, **`t_tidak_masuk`**, **`t_hutang_piutang`**. |

### 6.2 List Pengajuan Cuti / Izin API

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Impor daftar pengajuan dari API HR (`config/hris_api.php`). |
| **Proses** | HTTP client service **->** normalisasi **->** simpan. |
| **File** | **`ListPengajuanCutiApiController`**, **`ListPengajuanIzinApiController`** + **`app/Services/HrisApi*Service.php`**. |
| **Tabel** | Sesuai target impor (cek migrasi/controller). |

### 6.3 Activity Logs

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Audit jejak user; filter; export CSV. |
| **Proses** | Baca **`activity_logs`**. |
| **File** | **`ActivityLogController.php`**, view **`logs/`**. |
| **Tabel** | **`activity_logs`**. |

### 6.4 Login Aktif & Riwayat

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | Sesi aktif (heartbeat) + histori login/logout. |
| **Proses** | Middleware **`TrackUserSession`**; **`AuthController`** catat login/logout. |
| **File** | **`UserLoginActivityController`**, **`TrackUserSession`**, **`AuthController`**; view **`user-login-activity/`**. |
| **Tabel** | **`t_user_sessions`**, **`t_login_history`**. |

### 6.5 Pengelolaan User / Role / Permission

| Aspek | Penjelasan |
|--------|------------|
| **Fungsi & output** | RBAC aplikasi. |
| **Proses** | Resource CRUD + pivot. |
| **File** | **`UserController`**, **`RoleController`**, **`PermissionController`**; view **`settings/`**. |
| **Tabel** | **`users`**, **`roles`**, **`permissions`**, **`user_role`**, **`role_permission`**. |

---

## Rujukan silang

- **Peta tabel agregat:** **`04-mapping-database-per-modul.md`**
- **Alur rumus closing & lembur ringkas:** **`03-formulasi-dan-alur-perhitungan.md`**
- **Struktur folder umum:** **`02-struktur-folder-dan-file-modul.md`**
- **Layout, Blade, asset, cetak:** **`d08.md`** *(alias: `08_LAYOUT.md`)*

---

*Dokumen ini disusun untuk navigasi cepat. Modul dengan logika panjang (closing, lembur, izin keluar) wajib dilengkapi dengan trace kode saat audit bisnis.*
