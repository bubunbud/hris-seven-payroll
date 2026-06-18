# Analisis & Rencana Solusi: Penyesuaian Jam Kerja Bulan Ramadhan

**Tanggal:** 19 Februari 2026  
**Masalah:** Karyawan pulang lebih awal 30 menit selama Ramadhan, tapi jam kerja harus tetap dianggap full sesuai shift normal  
**Kebutuhan:** Solusi tanpa mengubah sistem yang sudah ada

---

## 📋 Konfirmasi Pemahaman

**Situasi Saat Ini:**
- Setiap karyawan sudah punya jam shift masing-masing di `m_shift` (via `m_karyawan.vcShift`)
- Perhitungan jam kerja saat ini: **Jam Pulang - Jam Masuk** (dikurangi istirahat)
- Selama bulan Ramadhan: karyawan **pulang lebih awal 30 menit** dari shift normal
- **Masalah:** Sistem menghitung jam kerja aktual, jadi kurang 30 menit dari shift normal

**Kebutuhan:**
- Selama Ramadhan, meskipun karyawan pulang lebih awal 30 menit
- **Jam kerja tetap dianggap FULL sesuai shift normal** mereka
- Tidak mengubah sistem yang sudah ada (minimal impact)

---

## 🔍 Analisis Sistem Saat Ini

### **Struktur Database yang Relevan:**

1. **`t_absen`** (Tabel Absensi)
   - `dtTanggal` (DATE)
   - `vcNik` (VARCHAR) - FK ke m_karyawan
   - `dtJamMasuk` (STRING, format HH:mm:ss)
   - `dtJamKeluar` (STRING, format HH:mm:ss)
   - `intDurasiIstirahat` (INT, menit)

2. **`m_shift`** (Master Shift)
   - `vcShift` (PK, VARCHAR)
   - `vcMasuk` (DATETIME, format H:i)
   - `vcPulang` (DATETIME, format H:i)

3. **`m_karyawan`** (Master Karyawan)
   - `Nik` (PK)
   - `vcShift` (FK ke m_shift)

### **Lokasi Perhitungan Jam Kerja:**

1. **`RekapitulasiAbsensiController`** - Perhitungan jam kerja untuk rekap
2. **`StatistikAbsensiController`** - Perhitungan surplus/deficit jam
3. **`Absen` Model** - Method `getTotalJamAttribute()`
4. **`ClosingController`** - Perhitungan untuk closing gaji

**Formula Saat Ini:**
```php
$menit = $tMasuk->diffInMinutes($tKeluar, true);
// Kurangi istirahat jika melewati 12:00-13:00
if ($tMasuk->lt($lunchEnd) && $tKeluar->gt($lunchStart)) {
    $menit = max(0, $menit - 60);
}
$jamKerja = round($menit / 60, 2);
```

---

## 💡 Opsi Solusi

### **Opsi 1: Tabel Konfigurasi Periode Khusus (RECOMMENDED)**

**Konsep:**
- Buat tabel baru `m_periode_khusus` untuk menyimpan periode khusus (Ramadhan, dll)
- Tambah kolom adjustment jam di tabel ini
- Modifikasi perhitungan jam kerja untuk cek periode khusus

**Keuntungan:**
- ✅ Fleksibel untuk periode lain di masa depan
- ✅ Bisa diaktifkan/nonaktifkan per periode
- ✅ Bisa berbeda adjustment per karyawan/divisi jika perlu
- ✅ Tidak mengubah struktur tabel absensi
- ✅ Minimal impact ke sistem existing

**Struktur Tabel:**
```sql
CREATE TABLE m_periode_khusus (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vcNamaPeriode VARCHAR(100), -- "Ramadhan 2026"
    dtTanggalMulai DATE,
    dtTanggalSelesai DATE,
    intAdjustmentMenit INT DEFAULT 30, -- +30 menit untuk Ramadhan
    vcKeterangan TEXT,
    isAktif BOOLEAN DEFAULT TRUE,
    dtCreate DATETIME,
    dtChange DATETIME
);
```

**Modifikasi Perhitungan:**
```php
// Helper function untuk cek periode khusus
private function isPeriodeKhusus($tanggal) {
    return PeriodeKhusus::where('isAktif', true)
        ->where('dtTanggalMulai', '<=', $tanggal)
        ->where('dtTanggalSelesai', '>=', $tanggal)
        ->first();
}

// Di perhitungan jam kerja:
$jamKerja = round($menit / 60, 2);

// Jika dalam periode khusus, tambahkan adjustment
$periodeKhusus = $this->isPeriodeKhusus($tanggal);
if ($periodeKhusus && $jamKeluar < $shiftPulang) {
    // Tambahkan adjustment menit ke jam kerja
    $adjustmentMenit = $periodeKhusus->intAdjustmentMenit;
    $jamKerja = round(($menit + $adjustmentMenit) / 60, 2);
    
    // Cap maksimal sesuai shift normal
    $jamShiftNormal = $this->getJamShiftNormal($karyawan, $tanggal);
    $jamKerja = min($jamKerja, $jamShiftNormal);
}
```

---

### **Opsi 2: Flag di Tabel Absensi**

**Konsep:**
- Tambah kolom `isRamadan` atau `intAdjustmentMenit` di `t_absen`
- Set flag saat input/edit absensi
- Gunakan flag saat perhitungan

**Keuntungan:**
- ✅ Langsung di level record absensi
- ✅ Bisa berbeda per hari/karyawan

**Kekurangan:**
- ❌ Perlu modifikasi struktur tabel `t_absen`
- ❌ Perlu update semua record absensi Ramadhan
- ❌ Kurang fleksibel untuk periode lain

**Struktur:**
```sql
ALTER TABLE t_absen 
ADD COLUMN intAdjustmentMenit INT DEFAULT 0 COMMENT 'Adjustment menit untuk periode khusus (Ramadhan dll)';
```

---

### **Opsi 3: Master Hari Libur dengan Flag Adjustment**

**Konsep:**
- Gunakan tabel hari libur yang sudah ada (jika ada)
- Tambah kolom `intAdjustmentMenit` untuk adjustment jam kerja
- Cek hari libur saat perhitungan

**Keuntungan:**
- ✅ Reuse tabel existing (jika ada)
- ✅ Bisa dikombinasi dengan hari libur

**Kekurangan:**
- ❌ Perlu cek apakah tabel hari libur sudah ada
- ❌ Konsepnya agak berbeda (hari libur vs periode khusus)

---

### **Opsi 4: Konfigurasi Global di Settings**

**Konsep:**
- Tambah setting global "Periode Ramadhan" dengan tanggal mulai/selesai
- Tambah setting "Adjustment Jam Ramadhan" (default 30 menit)
- Gunakan setting ini di perhitungan

**Keuntungan:**
- ✅ Simple, tidak perlu tabel baru
- ✅ Mudah diaktifkan/nonaktifkan

**Kekurangan:**
- ❌ Hanya untuk 1 periode aktif
- ❌ Tidak bisa multiple periode bersamaan
- ❌ Perlu cek apakah sistem settings sudah ada

---

## 🎯 Rekomendasi: Opsi 1 (Tabel Konfigurasi Periode Khusus)

**Alasan:**
1. **Fleksibel** - Bisa untuk periode lain (Lebaran, cuti bersama, dll)
2. **Minimal Impact** - Tidak mengubah tabel existing
3. **Scalable** - Bisa dikembangkan untuk adjustment per karyawan/divisi
4. **Maintainable** - Mudah di-manage via UI

---

## 📝 Rencana Implementasi (Opsi 1)

### **Phase 1: Database & Model**

1. **Migration:**
   - Buat tabel `m_periode_khusus`
   - Tambah index untuk performa query

2. **Model:**
   - `app/Models/PeriodeKhusus.php`
   - Relationship jika perlu

3. **Seeder:**
   - Data default untuk Ramadhan 2026 (jika perlu)

### **Phase 2: Helper Function**

1. **Service/Helper:**
   - `app/Services/PeriodeKhususService.php` atau
   - Helper function di controller yang relevan
   - Method: `getPeriodeKhusus($tanggal)`, `getAdjustmentMenit($tanggal)`

### **Phase 3: Modifikasi Perhitungan**

**File yang perlu dimodifikasi:**

1. **`app/Http/Controllers/RekapitulasiAbsensiController.php`**
   - Method `index()` - Perhitungan jam kerja
   - Method `calculateJamKerja()` - Helper calculation

2. **`app/Http/Controllers/StatistikAbsensiController.php`**
   - Method `index()` - Perhitungan surplus/deficit

3. **`app/Models/Absen.php`**
   - Method `getTotalJamAttribute()` - Jika digunakan

4. **`app/Http/Controllers/ClosingController.php`**
   - Method `calculateLembur()` - Jika perlu adjustment

**Logika Modifikasi:**
```php
// Sebelum:
$jamKerja = round($menit / 60, 2);

// Sesudah:
$jamKerja = round($menit / 60, 2);

// Cek periode khusus
$periodeKhusus = PeriodeKhususService::getActivePeriode($tanggal);
if ($periodeKhusus) {
    // Ambil shift normal karyawan
    $shiftNormal = $this->getShiftNormal($karyawan, $tanggal);
    $jamShiftNormal = $this->calculateJamShiftNormal($shiftNormal);
    
    // Jika jam kerja aktual kurang dari shift normal
    // dan dalam periode khusus, gunakan shift normal
    if ($jamKerja < $jamShiftNormal) {
        $jamKerja = $jamShiftNormal;
    }
}
```

### **Phase 4: UI Management (Opsional)**

1. **Master Periode Khusus:**
   - CRUD untuk manage periode khusus
   - Form: Nama, Tanggal Mulai, Tanggal Selesai, Adjustment Menit
   - List dengan filter

2. **Integration:**
   - Tambah menu di Settings atau Master Data
   - Permission: `view-master-periode-khusus`

---

## 🔧 Detail Teknis

### **1. Struktur Tabel `m_periode_khusus`**

```sql
CREATE TABLE m_periode_khusus (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vcNamaPeriode VARCHAR(100) NOT NULL COMMENT 'Nama periode: Ramadhan 2026, Lebaran 2026, dll',
    dtTanggalMulai DATE NOT NULL,
    dtTanggalSelesai DATE NOT NULL,
    intAdjustmentMenit INT DEFAULT 30 COMMENT 'Adjustment menit (biasanya +30 untuk Ramadhan)',
    vcKeterangan TEXT,
    isAktif BOOLEAN DEFAULT TRUE,
    dtCreate DATETIME,
    dtChange DATETIME,
    INDEX idx_tanggal (dtTanggalMulai, dtTanggalSelesai),
    INDEX idx_aktif (isAktif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### **2. Model `PeriodeKhusus`**

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodeKhusus extends Model
{
    protected $table = 'm_periode_khusus';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'vcNamaPeriode',
        'dtTanggalMulai',
        'dtTanggalSelesai',
        'intAdjustmentMenit',
        'vcKeterangan',
        'isAktif',
        'dtCreate',
        'dtChange',
    ];
    
    protected $casts = [
        'dtTanggalMulai' => 'date',
        'dtTanggalSelesai' => 'date',
        'intAdjustmentMenit' => 'integer',
        'isAktif' => 'boolean',
        'dtCreate' => 'datetime',
        'dtChange' => 'datetime',
    ];
    
    // Scope untuk periode aktif
    public function scopeAktif($query)
    {
        return $query->where('isAktif', true);
    }
    
    // Scope untuk cek tanggal dalam periode
    public function scopeDalamPeriode($query, $tanggal)
    {
        return $query->where('dtTanggalMulai', '<=', $tanggal)
            ->where('dtTanggalSelesai', '>=', $tanggal);
    }
}
```

### **3. Service `PeriodeKhususService`**

```php
<?php
namespace App\Services;

use App\Models\PeriodeKhusus;
use Carbon\Carbon;

class PeriodeKhususService
{
    /**
     * Cek apakah tanggal dalam periode khusus aktif
     */
    public static function isDalamPeriodeKhusus($tanggal)
    {
        $tanggal = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);
        
        return PeriodeKhusus::aktif()
            ->dalamPeriode($tanggal->format('Y-m-d'))
            ->exists();
    }
    
    /**
     * Ambil periode khusus aktif untuk tanggal tertentu
     */
    public static function getPeriodeKhusus($tanggal)
    {
        $tanggal = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);
        
        return PeriodeKhusus::aktif()
            ->dalamPeriode($tanggal->format('Y-m-d'))
            ->first();
    }
    
    /**
     * Ambil adjustment menit untuk tanggal tertentu
     */
    public static function getAdjustmentMenit($tanggal)
    {
        $periode = self::getPeriodeKhusus($tanggal);
        return $periode ? $periode->intAdjustmentMenit : 0;
    }
}
```

### **4. Modifikasi Perhitungan Jam Kerja**

**Contoh di `RekapitulasiAbsensiController`:**
```php
use App\Services\PeriodeKhususService;
use App\Models\Karyawan;

// Di method perhitungan jam kerja:
$jamKerja = round($menit / 60, 2);

// Cek periode khusus (Ramadhan)
$periodeKhusus = PeriodeKhususService::getPeriodeKhusus($tanggal);
if ($periodeKhusus) {
    // Ambil shift normal karyawan
    $karyawan = $absen->karyawan;
    if ($karyawan && $karyawan->shift) {
        $shiftMasuk = $karyawan->shift->vcMasuk;
        $shiftPulang = $karyawan->shift->vcPulang;
        
        // Hitung jam shift normal
        $tShiftMasuk = $tanggal->copy()->setTimeFromTimeString($shiftMasuk);
        $tShiftPulang = $tanggal->copy()->setTimeFromTimeString($shiftPulang);
        if ($tShiftPulang->lessThan($tShiftMasuk)) {
            $tShiftPulang->addDay();
        }
        
        $menitShiftNormal = $tShiftMasuk->diffInMinutes($tShiftPulang, true);
        // Kurangi istirahat jika perlu
        $lunchStart = $tanggal->copy()->setTimeFromTimeString('12:00');
        $lunchEnd = $tanggal->copy()->setTimeFromTimeString('13:00');
        if ($tShiftMasuk->lt($lunchEnd) && $tShiftPulang->gt($lunchStart)) {
            $menitShiftNormal = max(0, $menitShiftNormal - 60);
        }
        
        $jamShiftNormal = round($menitShiftNormal / 60, 2);
        
        // Jika jam kerja aktual kurang dari shift normal, gunakan shift normal
        if ($jamKerja < $jamShiftNormal) {
            $jamKerja = $jamShiftNormal;
        }
    }
}
```

---

## ✅ Checklist Implementasi

### **Phase 1: Database & Model**
- [ ] Buat migration `create_m_periode_khusus_table`
- [ ] Buat model `PeriodeKhusus`
- [ ] Jalankan migration
- [ ] Test model (CRUD basic)

### **Phase 2: Service**
- [ ] Buat `PeriodeKhususService`
- [ ] Implement method `isDalamPeriodeKhusus()`
- [ ] Implement method `getPeriodeKhusus()`
- [ ] Implement method `getAdjustmentMenit()`
- [ ] Test service dengan data dummy

### **Phase 3: Modifikasi Perhitungan**
- [ ] Modifikasi `RekapitulasiAbsensiController`
- [ ] Modifikasi `StatistikAbsensiController`
- [ ] Modifikasi `Absen` model (jika perlu)
- [ ] Modifikasi `ClosingController` (jika perlu)
- [ ] Test perhitungan dengan data Ramadhan

### **Phase 4: UI Management (Opsional)**
- [ ] Buat controller `PeriodeKhususController`
- [ ] Buat view CRUD
- [ ] Tambah route & permission
- [ ] Tambah menu di sidebar
- [ ] Test UI

### **Phase 5: Testing & Deployment**
- [ ] Test dengan data real Ramadhan
- [ ] Verifikasi perhitungan jam kerja
- [ ] Test edge cases (tanggal boundary, multiple periode)
- [ ] Dokumentasi
- [ ] Deploy ke server

---

## 🎯 Kesimpulan

**Solusi yang direkomendasikan:** **Opsi 1 - Tabel Konfigurasi Periode Khusus**

**Alasan:**
- ✅ Fleksibel untuk periode lain
- ✅ Minimal impact ke sistem existing
- ✅ Scalable dan maintainable
- ✅ Tidak mengubah struktur tabel absensi

**Langkah Selanjutnya:**
1. Konfirmasi dengan user apakah opsi ini sesuai
2. Tentukan periode Ramadhan 2026 (tanggal mulai & selesai)
3. Mulai implementasi Phase 1-3 (database, service, modifikasi perhitungan)
4. Testing dengan data real
5. Deploy

---

**Apakah solusi ini sesuai dengan kebutuhan Anda?** 🤔




