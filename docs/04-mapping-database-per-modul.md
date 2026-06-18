# 4. Mapping database / tabel utama per modul

Penamaan di produksi biasanya memakai **`m_`** untuk master referensi & **`t_`** untuk transaksi. Ini **bukan** DDL lengkap; struktur kolom rinci lihat migrasi di `database/migrations/` atau dokumentasi DBA Anda.

Legenda:
- **M** Master referensi (relatif jarang berubah)
- **T** Transaksi (volume besar, terus bertambah)

---

## 4.1 Organisasi & karyawan

| Tabel | M/T | Modul utama |
|-------|-----|-------------|
| `m_divisi` | M | Master divisi / pemisah wilayah payroll. |
| `m_dept`, `m_bagian`, `m_seksi`, `m_jabatan` | M | Hirarki organisasi. |
| `m_karyawan` | M | Master karyawan (satu rekaman utama per NIK). |
| `t_keluarga` | T | Data keluarga. |
| `t_pendidikan` | T | Riwayat pendidikan. |
| `t_pelatihan` | T | Riwayat pelatihan. |
| `t_mutasi` | T | Riwayat mutasi / SK. |
| `t_karyawan_catatan` | T | Catatan internal karyawan. |

---

## 4.2 Referensi master absensi / shift

| Tabel | Modul utama |
|-------|-------------|
| `m_shift`, `m_hari_libur`, `m_golongan` | Master shift, libur, golongan. |
| `m_jenis_absen` | Master jenis tidak masuk (model `JenisIjin`). |
| `m_jenis_izin` | Master izin keluar (model `JenisIzin`). |
| `m_shift_security` | Master pola shift security / satpam. |

---

## 4.3 Operasional kehadiran

| Tabel | Modul utama |
|-------|-------------|
| `t_absen` | Browse / edit / rekapitulasi absensi. |
| `t_tidak_masuk` | Izin tidak masuk. |
| `t_izin` | Izin keluar komplek. |
| `t_jadwal_shift_security`, `t_override_jadwal_security` | Jadwal & override security. |

---

## 4.4 Lembur

| Tabel | Keterangan |
|-------|------------|
| `t_lembur_header`, `t_lembur_detail` | Instruksi / realisasi lembur (struktur utama). |
| `t_lembur` | Migrasi awal legacy; sahkan pemakaian aktual di DB produksi Anda. |

---

## 4.5 Perjalanan dinas & BPD

| Tabel |
|-------|
| `t_perjalanan_dinas_header`, `t_perjalanan_dinas_karyawan`, `t_perjalanan_dinas_jadwal`, `t_perjalanan_dinas_hotel`, `t_perjalanan_dinas_tiba_kembali` |
| `t_biaya_perjalanan_dinas_header`, `t_biaya_perjalanan_dinas_detail` |

---

## 4.6 Cuti & tukar hari kerja

| Tabel | Modul |
|-------|--------|
| `t_tukar_hari_kerja`, `t_tukar_hari_kerja_detail` | Tukar hari kerja. |
| `m_saldo_cuti` | Saldo cuti. |

---

## 4.7 Payroll & pembayaran

| Tabel | Fungsi |
|-------|--------|
| `m_gapok` | Master gaji pokok & tunjangan terkait. |
| `m_hutang_piutang` | Master/ref. hutang-piutang (model `MasterHutangPiutang`). |
| `t_hutang_piutang` | Transaksi hutang piutang karyawan per periode. |
| `t_periode` *(model `PeriodeGaji`)* | Definisi periode closing gaji. |
| **`t_closing`** | Hasil utama closing gaji per kunci komposit (NIK + periode + closing ke). |
| `t_periode_thr`, **`t_closing_thr`** | Periode & hasil closing THR. |

---

## 4.8 Pengguna, RBAC & audit

| Tabel |
|-------|
| `users` |
| `roles`, `permissions`, `user_role`, `role_permission` |
| `activity_logs` |
| `t_user_sessions`, `t_login_history` |

---

## 4.9 Laravel umum / queue

| Tabel | Keterangan |
|-------|------------|
| `personal_access_tokens` | Sanctum. |
| `password_reset_tokens` | Reset password. |
| `failed_jobs` | Standar Laravel (jika queue dipakai). |

**Catatan:** Beberapa objek bisa berasal dari impor DB di luar migrasi; selaraskan dokumentasi Anda dengan schema aktual (`SHOW TABLES`).
