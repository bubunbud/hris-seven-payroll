# 📋 Update Permission - Melengkapi Permission yang Belum Ada

**Tanggal:** 12 Februari 2026  
**Tujuan:** Melengkapi permission yang belum ada di modul Pengelolaan Role & Permission

---

## 🔍 Permission yang Ditambahkan

### **1. Modul Absensi**
- ✅ `view-perjalanan-dinas` - View Perjalanan Dinas (Form Perjalanan Dinas)
- ✅ `view-rekap-keterlambatan` - View Rekap Keterlambatan (Rekap Absensi Keterlambatan)

### **2. Modul Proses Payroll (THR)**
- ✅ `view-periode-thr` - View Periode THR (Periode Closing THR)
- ✅ `view-closing-thr` - View Closing THR
- ✅ `view-list-thr` - View List THR

### **3. Modul Laporan**
- ✅ `view-laporan-thr` - View Laporan THR
- ✅ `view-rekap-upah-finance-ver` - View Rekap Upah Finance Ver

**Total: 7 permission baru**

---

## 📁 File yang Diupdate

### **1. Seeder**
- ✅ `database/seeders/RolePermissionSeeder.php` - Menambahkan 7 permission baru
- ✅ `database/seeders/UpdatePermissionsSeeder.php` - Seeder khusus untuk update (optional)

### **2. View Settings**
- ✅ `resources/views/settings/permissions/index.blade.php` - Menambahkan label 'Dashboard' di moduleLabels
- ✅ `resources/views/settings/roles/create.blade.php` - Menambahkan label 'Dashboard' di moduleLabels
- ✅ `resources/views/settings/roles/edit.blade.php` - Menambahkan label 'Dashboard' di moduleLabels
- ✅ `resources/views/settings/roles/show.blade.php` - Menambahkan label 'Dashboard' di moduleLabels

---

## 🚀 Cara Update Permission

### **Opsi 1: Jalankan Seeder Update (Recommended)**

```bash
# Di server atau local
cd /path/to/hris-seven-payroll

# Jalankan seeder update
php artisan db:seed --class=UpdatePermissionsSeeder
```

**Expected Output:**
```
✓ Permission 'View Perjalanan Dinas' berhasil ditambahkan
✓ Permission 'View Rekap Keterlambatan' berhasil ditambahkan
✓ Permission 'View Periode THR' berhasil ditambahkan
✓ Permission 'View Closing THR' berhasil ditambahkan
✓ Permission 'View List THR' berhasil ditambahkan
✓ Permission 'View Laporan THR' berhasil ditambahkan
✓ Permission 'View Rekap Upah Finance Ver' berhasil ditambahkan

=== Summary ===
Permission baru ditambahkan: 7
Permission sudah ada (dilewati): 0
Total permission di database: XX
```

### **Opsi 2: Jalankan RolePermissionSeeder (Full)**

```bash
# Jalankan seeder lengkap (akan menambahkan semua permission yang belum ada)
php artisan db:seed --class=RolePermissionSeeder
```

**Note:** Seeder menggunakan `firstOrCreate`, jadi tidak akan membuat duplikat jika permission sudah ada.

### **Opsi 3: Manual via UI**

1. Login sebagai admin
2. Buka menu **Settings → Pengelolaan Permission**
3. Klik **Tambah Permission**
4. Isi form untuk setiap permission:
   - **Nama Permission**: View Perjalanan Dinas
   - **Slug**: view-perjalanan-dinas
   - **Module**: absensi
   - **Deskripsi**: Melihat Form Perjalanan Dinas
5. Ulangi untuk semua 7 permission

### **Opsi 4: Via SQL (Direct)**

```sql
-- Tambahkan permission yang belum ada
INSERT INTO permissions (name, slug, module, description, created_at, updated_at)
VALUES 
('View Perjalanan Dinas', 'view-perjalanan-dinas', 'absensi', 'Melihat Form Perjalanan Dinas', NOW(), NOW()),
('View Rekap Keterlambatan', 'view-rekap-keterlambatan', 'absensi', 'Melihat Rekap Absensi Keterlambatan', NOW(), NOW()),
('View Periode THR', 'view-periode-thr', 'proses-gaji', 'Melihat Periode Closing THR', NOW(), NOW()),
('View Closing THR', 'view-closing-thr', 'proses-gaji', 'Melihat Closing THR', NOW(), NOW()),
('View List THR', 'view-list-thr', 'proses-gaji', 'Melihat List THR', NOW(), NOW()),
('View Laporan THR', 'view-laporan-thr', 'laporan', 'Melihat Laporan THR', NOW(), NOW()),
('View Rekap Upah Finance Ver', 'view-rekap-upah-finance-ver', 'laporan', 'Melihat Rekap Upah Finance Ver', NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();
```

---

## ✅ Verifikasi

### **1. Cek Permission di Database**

```sql
-- Cek permission yang baru ditambahkan
SELECT * FROM permissions 
WHERE slug IN (
    'view-perjalanan-dinas',
    'view-rekap-keterlambatan',
    'view-periode-thr',
    'view-closing-thr',
    'view-list-thr',
    'view-laporan-thr',
    'view-rekap-upah-finance-ver'
)
ORDER BY module, name;
```

### **2. Cek via UI**

1. Login sebagai admin
2. Buka menu **Settings → Pengelolaan Permission**
3. Filter atau search permission baru
4. Pastikan semua 7 permission muncul

### **3. Cek di Role Management**

1. Buka menu **Settings → Pengelolaan Role**
2. Edit role (misal: Admin, HR)
3. Pastikan permission baru muncul di list:
   - **Module Absensi**: View Perjalanan Dinas, View Rekap Keterlambatan
   - **Module Proses Payroll**: View Periode THR, View Closing THR, View List THR
   - **Module Laporan**: View Laporan THR, View Rekap Upah Finance Ver

---

## 🔐 Assign Permission ke Role

Setelah permission ditambahkan, assign ke role yang sesuai:

### **Via UI:**
1. Buka **Settings → Pengelolaan Role**
2. Edit role (misal: Admin, HR)
3. Centang permission baru yang ingin diberikan
4. Simpan

### **Via SQL:**
```sql
-- Assign semua permission baru ke role Admin (ganti role_id sesuai ID admin)
INSERT INTO role_permission (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.name = 'Administrator'
AND p.slug IN (
    'view-perjalanan-dinas',
    'view-rekap-keterlambatan',
    'view-periode-thr',
    'view-closing-thr',
    'view-list-thr',
    'view-laporan-thr',
    'view-rekap-upah-finance-ver'
)
ON DUPLICATE KEY UPDATE role_id = role_id;
```

---

## 📝 Catatan

1. **Seeder menggunakan `firstOrCreate`**: Permission tidak akan duplikat jika sudah ada
2. **Module Labels**: Sudah ditambahkan 'Dashboard' di semua view settings
3. **Backward Compatible**: Permission yang sudah ada tidak akan terpengaruh
4. **Role Assignment**: Permission baru perlu di-assign ke role secara manual (via UI atau SQL)

---

## 🐛 Troubleshooting

### **Error: Duplicate entry**
- Permission sudah ada, tidak perlu ditambahkan lagi
- Seeder akan skip otomatis

### **Permission tidak muncul di Role Management**
- Clear cache: `php artisan cache:clear`
- Refresh halaman browser
- Pastikan permission sudah ada di database

### **Module 'Dashboard' tidak muncul**
- Pastikan view sudah di-update
- Clear view cache: `php artisan view:clear`

---

## ✅ Checklist

- [ ] Seeder dijalankan atau permission ditambahkan manual
- [ ] Semua 7 permission muncul di database
- [ ] Permission muncul di UI Pengelolaan Permission
- [ ] Permission muncul di Role Management (create/edit)
- [ ] Permission di-assign ke role yang sesuai
- [ ] Module 'Dashboard' muncul di filter/view
- [ ] Tidak ada error di log

---

**Status:** ✅ Siap untuk Production  
**Last Updated:** 12 Februari 2026








