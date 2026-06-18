# Checklist Deploy: Update Permission Form Biaya Perjalanan Dinas (BPD)

**Tanggal Deploy:** _______________  
**Deployed By:** _______________  
**Server:** Ubuntu 192.168.10.40

---

## 📋 Pre-Deployment

- [ ] Backup file existing:
  - [ ] `database/seeders/RolePermissionSeeder.php`
  - [ ] `database/seeders/UpdatePermissionsSeeder.php`
  - [ ] `routes/web.php`
  - [ ] `resources/views/layouts/app.blade.php`

---

## 📁 File Upload

### Seeder Files
- [ ] `database/seeders/RolePermissionSeeder.php`
- [ ] `database/seeders/UpdatePermissionsSeeder.php`

### Routes Update
- [ ] `routes/web.php` (update middleware BPD)

### Sidebar Update
- [ ] `resources/views/layouts/app.blade.php` (update permission check)

**Total: 4 file**

---

## ⚙️ Server Configuration

- [ ] Set file permission (`chown`, `chmod`)
- [ ] Set storage & cache permission (775)
- [ ] Jalankan seeder: `php artisan db:seed --class=UpdatePermissionsSeeder`
- [ ] Clear cache:
  - [ ] `php artisan cache:clear`
  - [ ] `php artisan config:clear`
  - [ ] `php artisan route:clear`
  - [ ] `php artisan view:clear`

---

## ✅ Verification

### Database
- [ ] Permission `view-biaya-perjalanan-dinas` sudah ada di database
- [ ] Permission memiliki module `absensi`
- [ ] Permission memiliki description yang benar

### Routes
- [ ] Route BPD menggunakan middleware permission yang benar
- [ ] Route bisa diakses dengan permission baru

### UI/UX
- [ ] Permission muncul di Settings → Pengelolaan Permission
- [ ] Permission muncul di Settings → Pengelolaan Role (section Absensi → Granular)
- [ ] Menu BPD muncul di sidebar untuk user dengan permission

---

## 🧪 Testing

### Permission Management
- [ ] **Pengelolaan Permission:** Permission "View Biaya Perjalanan Dinas" muncul di list
- [ ] **Pengelolaan Role:** Permission muncul di section Absensi → Granular
- [ ] **Assign Permission:** Bisa assign permission ke role
- [ ] **Save Role:** Role tersimpan dengan permission baru

### Access Control
- [ ] **User dengan permission:** Bisa akses menu BPD
- [ ] **User tanpa permission:** Tidak bisa akses menu BPD (403 atau menu tidak muncul)
- [ ] **Route middleware:** Bekerja dengan 3 permission (view-absensi, view-perjalanan-dinas, view-biaya-perjalanan-dinas)

### Integration
- [ ] Menu BPD muncul di sidebar (di bawah Form Perjalanan Dinas)
- [ ] Permission ter-group dengan benar di module Absensi
- [ ] Backward compatibility: Permission lama masih bekerja

---

## 🐛 Issues Found

**Issue 1:**
- Description: _________________________________
- Status: [ ] Fixed [ ] Pending
- Solution: _________________________________

**Issue 2:**
- Description: _________________________________
- Status: [ ] Fixed [ ] Pending
- Solution: _________________________________

---

## ✅ Final Sign-Off

- [ ] Semua checklist sudah completed
- [ ] Tidak ada error yang blocking
- [ ] Testing berhasil semua
- [ ] Permission bisa dikelola dengan normal di Settings

**Deployed By:** _______________  
**Date:** _______________  
**Time:** _______________  
**Status:** [ ] Success [ ] Partial [ ] Failed

**Notes:**
_________________________________
_________________________________
_________________________________




