# 1. Rancangan prosedur / bisnis proses per modul & menu

Dokumen ini merangkum **alur bisnis logis** yang terwakili oleh menu aplikasi (sesuai sidebar `resources/views/layouts/app.blade.php` dan pembatasan **`permission`** pada `routes/web.php`). Nama persis submenu dapat sedikit berbeda di layar tetapi mengikuti route yang sama.

---

## 1.1 Autentikasi & dasar akses

| Langkah bisnis | Keterangan |
|----------------|------------|
| User login dengan email/password | `AuthController` — validasi `is_active`. |
| Sesi dibuat | Middleware `TrackUserSession` mencatat heartbeat (modul sesi/login). |
| Sidebar menampilkan menu | Hanya modul dengan permission minimal satu dari daftar granularity. |
| Logout | Mencatat riwayat logout dan menghapus baris sesi pelacakan. |

---

## 1.2 Dashboard

| Menu | Bisnis proses |
|------|----------------|
| **Dashboard Harian** | Ringkas aktivitas/periode bagi user umum (`/`). |
| **Dashboard Group** | Pandangan level holding/group (aggregation terbatas oleh permission `view-dashboard-group`). |
| **Dashboard BU** | Pandangan tingkat bisnis unit (`view-dashboard-bu`). |
| **Dashboard Karyawan** | Self-service ringkas bagi karyawan (`view-dashboard-employee`). |

---

## 1.3 Master Data

Prasyarat organisasi dan referensi bagi absensi serta payroll.

| Submenu | Prosedur bisnis ringkas |
|---------|--------------------------|
| **Master Karyawan** | Pengelolaan biodata & struktur hierarki pegawai (`m_karyawan` + keluarga, pendidikan, pelatihan, mutasi, catatan internal). |
| **List Data Karyawan Aktif** | Listing/export karyawan aktif untuk operasional cepat. |
| **Master Divisi / Departemen / Bagian / Seksi** | Hierarki organisasi; digunakan filter di banyak laporan. |
| **Master Golongan** | Mengaitkan karyawan ke penempatan pangkat/skala payroll. |
| **Master Shift Kerja** | Definisi shift kerja reguler non-security. |
| **Master Tidak Masuk** | Jenis alasan tidak hadir dari master (`m_jenis_absen`). |
| **Master Ijin Keluar** | Jenis izin keluar (`m_jenis_izin`) untuk formulir Izin Keluar Kompleks. |
| **Master Jabatan** | Jabatan struktural + penomoran kode. |
| **Master Hari Libur** | Kalender libur nasional/Perusahaan; mempengaruhi perhitungan hari kerja. |
| **Group Hierarki** | Penambahan struktur hierarki lewat satu layar grouped. |

---

## 1.4 Absensi & operasional HR

| Submenu | Prosedur bisnis ringkas |
|---------|--------------------------|
| **Browse Absensi** | Lihat rekaman absensi dari `t_absen` untuk filter/periode/divisi/dept.; export/print opsional. |
| **Input/Edit Absensi** | Koreksi entri tidak melalui mesin/device (manual entry). |
| **Browse Tidak Absen** | Identifikasi hari tidak ada rekaman swipe/tap. |
| **Jadwal Shift Satpam / Master Shift Security / List Override / Browse Absensi Security** | Siklus pengelolaan jadwal & absensi kelompok satpam/security (shift fleksibel + override); laporan Shift terpisah di menu Laporan. |
| **Izin Tidak Masuk** | Pengajuan & persetujuan administratif dengan jenis tidak masuk. |
| **Izin Keluar Kompleks** | Form cuti pendek keluar kawasan; beberapa alur terkait VC counter & cetak (`IzinKeluarController`). |
| **Instruksi Kerja Lembur** | Perencanaan lembur; terhubung nominal & jenis melalui AJAX ke pemrosesan nominal. |
| **Form Perjalanan Dinas (RPD)** | Header+jadwal+karyawan+hotel+tiba/kembali; dapat memicu pembaruan rekaman absensi. |
| **Form Biaya Perjalanan Dinas (BPD)** | Administrasi pengeluaran & status form setelah ada RPD referensi. |
| **Statistik Absensi / Rekap Absensi Keterlambatan** | Agregasi performa kehadiran & telat untuk review manajemen. |
| **Saldo Cuti** | Saldo tahunan karyawan, import Excel, migrasi saldo tahun ke tahun. |
| **Tukar Hari Kerja** | Menandai pergantian antara hari kerja dengan hari libur per NIK; mempengaruhi perhitungan hari kerja payroll. |

---

## 1.5 Proses Payroll

Alur utama: definisi pembayaran periode dan komponen pembayaran, realisasi lembur/hutang, **closing**, koreksi, lalu rekonsiliasi rekap/daftar pembayaran.

| Submenu | Prosedur bisnis ringkas |
|---------|--------------------------|
| **Master Gaji Pokok** | Tarif pangkat/skala golongan: upah tetap dan tunjangan-komponen yang dipakai `ClosingController` menghitung pokok bulanan/setengah bulan. |
| **Hutang-Piutang Karyawan** | Potongan/pembayaran kembali cicilan antara periode tertentu. |
| **Realisasi Lembur** | Verifikasi & konfirmasi jam lembur yang mengalir ke `t_lembur_*`/`t_closing` lemburnya. |
| **Periode Closing Gaji & Periode Closing THR** | Jendela tanggal pembayaran & pembatasan wilayah divisi serta kuarter closing (1 atau 2). |
| **Closing Gaji** | Menjalankan batch perhitungan `calculatePayroll` per divisi/periode untuk seluruh karyawan bersangkutan; hasil utama ke **`t_closing`**. |
| **Closing THR / List THR** | Pemrosesan & listing terpisah pembayaran THR (`t_closing_thr`). |
| **Update Closing Gaji** | Koreksi pasca-closing atas baris penyimpanan tertentu. |
| **View Rekap Gaji** | Tampilan rekapitulasi pembayaran hasil closing. |

Detail rumus pembayaran: lihat **`03-formulasi-dan-alur-perhitungan.md`** dan kode **`ClosingController`**.

---

## 1.6 Laporan

Output untuk finance, BU, hukum industri, serta export Excel atau halaman cetak.

| Kelompok | Contoh submenu | Output tipikal |
|-----------|----------------|----------------|
| Pembayaran & slip | Slip Gaji / Slip THR | PDF/visual layar. |
| Manajemen upah bank | Rekap Upah*, Rekap Bank*, Rekap Uang Makan & Transport**, Rekap THR Operator/Staff, Rekap Bank THR | Tabel/filter + Excel beberapa modul (`Maatwebsite/Excel`). |
| Absensi & cuti | Rekapitulasi Absensi**, Rekapitulasi Absen All, Rekapitulasi Cuti, Rekap Absensi Keterlambatan | Agregasi jumlah hari, status sangketa. |

`*` Rekap dapat per Bagian/BU; `**` modul bernama panjang sama dengan label menu.

Semua submenu di atas bergantung permission `view-laporan` granular ke masing-masing slug.

---

## 1.7 Settings / administrasi aplikasi & integritas data

| Submenu | Prosedur bisnis ringkas |
|---------|--------------------------|
| **Tarik Data Absensi/Izin/Tidak Masuk/Hutang Piutang** | Pull sinkron/asinkron dari sumber luar (biasanya API/feeder atau skrip ingest) menyelamatkan **`t_*`**. |
| **List Pengajuan Cuti / Izin API** | Import feeder dari **`config/hris_api.php`** (+ env) untuk penyelarasan antara HR pusat dengan payroll lokal. |
| **Login Aktif & Riwayat** | Audit siapa aktif secara sesi serta histori login/logout (tabel **`t_user_sessions`**, **`t_login_history`**). |
| **Activity Logs** | Audit aktivitas aplikasi (**`activity_logs`**). |
| **Pengelolaan User / Role / Permission** | RBAC Laravel custom `Role`, `Permission`, pivot **`user_role`**, **`role_permission`**. |

---

## Catatan akses granular

Untuk banyak grup menu utama, akses submenu dapat:
- menggunakan permission **grup terpadu** (misal `view-absensi`), **atau**
- permission granular per-fitur (`view-master-karyawan`, `view-slip-gaji`, dst.).

Konfigurasi pasti ada di **`RolePermissionSeeder`**, **`UpdatePermissionsSeeder`**, dan penetapan pada role di UI.
