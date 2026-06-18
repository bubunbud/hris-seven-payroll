# Skenario Alternatif: Adjustment Jam Pulang +30 Menit untuk Ramadhan

**Tanggal:** 19 Februari 2026  
**Konsep:** Menambahkan 30 menit ke jam pulang aktual untuk perhitungan jam kerja  
**Kondisi:** Hanya untuk karyawan yang masuk kerja dan tidak ada izin pulang cepat

---

## 📋 Konsep Baru

### **Perbedaan dengan Konsep Sebelumnya:**

**Konsep Sebelumnya (Adjustment di Perhitungan):**
- Jam pulang aktual tetap: 16:30
- Perhitungan: Jika < shift normal, gunakan shift normal
- Data absensi tidak berubah

**Konsep Baru (Adjustment Jam Pulang +30 Menit):**
- Jam pulang aktual: 16:30
- Jam pulang untuk perhitungan: 16:30 + 30 menit = 17:00
- Data absensi tetap: 16:30 (tidak diubah)
- Perhitungan menggunakan jam pulang "virtual" (17:00)

---

## ✅ Keuntungan Konsep Baru

1. **Lebih Transparan:**
   - Data absensi tetap mencerminkan jam pulang sebenarnya
   - Perhitungan jelas: jam pulang + 30 menit
   - Mudah di-audit dan di-trace

2. **Lebih Fleksibel:**
   - Bisa berbeda adjustment per periode (30 menit, 1 jam, dll)
   - Bisa berbeda per karyawan/divisi jika perlu

3. **Lebih Akurat:**
   - Perhitungan jam kerja lebih presisi
   - Tidak perlu cek apakah < shift normal

4. **Lebih Mudah Dipahami:**
   - Logika sederhana: tambah 30 menit ke jam pulang
   - Tidak perlu logika kompleks untuk cek shift normal

---

## 🔍 Skenario Detail

### **Skenario 1: Ramadhan Normal (Tidak Ada Izin)**
```
Input:
- Tanggal: 2026-03-15 (dalam periode Ramadhan)
- Shift: 08:00 - 17:00
- Jam Masuk Aktual: 08:00
- Jam Pulang Aktual: 16:30 (pulang lebih awal 30 menit karena Ramadhan)
- Izin Pulang Cepat: TIDAK ADA

Perhitungan:
- Jam Pulang untuk Perhitungan: 16:30 + 30 menit = 17:00
- Jam Kerja: 17:00 - 08:00 = 9 jam
- Kurangi istirahat: 9 - 1 = 8 jam
- Jam Kerja Final: 8 jam (sesuai shift normal)

Data Absensi (t_absen):
- dtJamMasuk: 08:00 (tidak berubah)
- dtJamKeluar: 16:30 (tidak berubah, tetap aktual)
```

### **Skenario 2: Ramadhan dengan Izin Pulang Cepat**
```
Input:
- Tanggal: 2026-03-15 (dalam periode Ramadhan)
- Shift: 08:00 - 17:00
- Jam Masuk Aktual: 08:00
- Jam Pulang Aktual: 15:00 (karena izin pulang cepat)
- Izin Pulang Cepat: ADA (t_izin dengan vcTipeIzin = 'Pulang Cepat')

Perhitungan:
- Jam Pulang untuk Perhitungan: 15:00 (TIDAK ditambah, karena ada izin)
- Jam Kerja: 15:00 - 08:00 = 7 jam
- Kurangi istirahat: 7 - 1 = 6 jam
- Jam Kerja Final: 6 jam (aktual, tidak adjustment)

Data Absensi (t_absen):
- dtJamMasuk: 08:00
- dtJamKeluar: 15:00 (tidak berubah)
```

### **Skenario 3: Non-Ramadhan**
```
Input:
- Tanggal: 2026-04-15 (di luar periode Ramadhan)
- Shift: 08:00 - 17:00
- Jam Masuk Aktual: 08:00
- Jam Pulang Aktual: 16:30
- Izin Pulang Cepat: TIDAK ADA

Perhitungan:
- Jam Pulang untuk Perhitungan: 16:30 (TIDAK ditambah, karena bukan Ramadhan)
- Jam Kerja: 16:30 - 08:00 = 8.5 jam
- Kurangi istirahat: 8.5 - 1 = 7.5 jam
- Jam Kerja Final: 7.5 jam (aktual)

Data Absensi (t_absen):
- dtJamMasuk: 08:00
- dtJamKeluar: 16:30 (tidak berubah)
```

---

## 💡 Implementasi Teknis

### **Helper Function untuk Ambil Jam Pulang (dengan Adjustment)**

```php
use App\Models\Izin;
use App\Services\PeriodeKhususService;

/**
 * Ambil jam pulang untuk perhitungan (dengan adjustment jika perlu)
 * 
 * @param string $jamPulangAktual Format: HH:mm:ss
 * @param string $tanggal Format: Y-m-d
 * @param string $nik NIK karyawan
 * @return string Jam pulang untuk perhitungan (HH:mm:ss)
 */
private function getJamPulangUntukPerhitungan($jamPulangAktual, $tanggal, $nik)
{
    if (!$jamPulangAktual) {
        return null;
    }
    
    // Cek apakah dalam periode Ramadhan
    $periodeKhusus = PeriodeKhususService::getPeriodeKhusus($tanggal);
    if (!$periodeKhusus) {
        // Bukan periode khusus, gunakan jam pulang aktual
        return $jamPulangAktual;
    }
    
    // Cek apakah ada izin pulang cepat
    $izinPulangCepat = Izin::where('vcNik', $nik)
        ->where('dtTanggal', $tanggal)
        ->whereIn('vcKodeIzin', ['Z003', 'Z004'])
        ->where('vcTipeIzin', 'Pulang Cepat')
        ->exists();
    
    // Jika ada izin pulang cepat, tidak adjustment
    if ($izinPulangCepat) {
        return $jamPulangAktual;
    }
    
    // Tambahkan adjustment menit ke jam pulang
    $tanggalObj = Carbon::parse($tanggal);
    $jamPulangObj = $tanggalObj->copy()->setTimeFromTimeString($jamPulangAktual);
    $jamPulangObj->addMinutes($periodeKhusus->intAdjustmentMenit);
    
    return $jamPulangObj->format('H:i:s');
}
```

### **Modifikasi Perhitungan Jam Kerja**

**File:** `app/Http/Controllers/RekapitulasiAbsensiController.php`

```php
// Sebelumnya:
$jamMasuk = substr((string) $absen->dtJamMasuk, 0, 5);
$jamKeluar = substr((string) $absen->dtJamKeluar, 0, 5);

$tMasuk = $tanggal->copy()->setTimeFromTimeString($jamMasuk);
$tKeluar = $tanggal->copy()->setTimeFromTimeString($jamKeluar);

// Sesudah:
$jamMasuk = substr((string) $absen->dtJamMasuk, 0, 5);
$jamKeluarAktual = substr((string) $absen->dtJamKeluar, 0, 5);

// Ambil jam pulang untuk perhitungan (dengan adjustment jika perlu)
$jamKeluar = $this->getJamPulangUntukPerhitungan(
    $absen->dtJamKeluar,
    $tanggal->format('Y-m-d'),
    $absen->vcNik
);

$tMasuk = $tanggal->copy()->setTimeFromTimeString($jamMasuk);
$tKeluar = $tanggal->copy()->setTimeFromTimeString($jamKeluar);

// Lanjutkan perhitungan seperti biasa...
$menit = $tMasuk->diffInMinutes($tKeluar, true);
// Kurangi istirahat jika perlu
$lunchStart = $tanggal->copy()->setTimeFromTimeString('12:00');
$lunchEnd = $tanggal->copy()->setTimeFromTimeString('13:00');
if ($tMasuk->lt($lunchEnd) && $tKeluar->gt($lunchStart)) {
    $menit = max(0, $menit - 60);
}

$jamKerja = round($menit / 60, 2);
```

### **Modifikasi di Semua Controller yang Relevan**

File yang perlu dimodifikasi:
1. `RekapitulasiAbsensiController.php` - Perhitungan jam kerja untuk rekap
2. `StatistikAbsensiController.php` - Perhitungan surplus/deficit
3. `RekapitulasiAbsenAllController.php` - Perhitungan jam kerja
4. `ClosingController.php` - Perhitungan untuk closing gaji (jika perlu)
5. `Absen` Model - Method `getTotalJamAttribute()` (jika digunakan)

---

## 📊 Flow Diagram

```
START: Perhitungan Jam Kerja
  │
  ├─ Ambil jam masuk & jam pulang aktual dari t_absen
  │
  ├─ Cek: Apakah dalam periode Ramadhan?
  │   │
  │   ├─ NO → Gunakan jam pulang aktual (END)
  │   │
  │   └─ YES → Cek: Apakah ada izin pulang cepat?
  │       │
  │       ├─ YES → Gunakan jam pulang aktual (tidak adjustment)
  │       │
  │       └─ NO → Tambahkan 30 menit ke jam pulang
  │                 Jam pulang untuk perhitungan = jam pulang aktual + 30 menit
  │
  ├─ Hitung jam kerja: jam pulang (adjusted) - jam masuk
  │
  ├─ Kurangi istirahat jika perlu
  │
  └─ Return jam kerja final
END
```

---

## ✅ Test Cases

### **Test Case 1: Ramadhan Normal (Tidak Ada Izin)**
```
Input:
- Tanggal: 2026-03-15 (dalam periode Ramadhan, adjustment = 30 menit)
- Shift: 08:00 - 17:00
- Jam Masuk Aktual: 08:00
- Jam Pulang Aktual: 16:30
- Izin Pulang Cepat: TIDAK ADA

Perhitungan:
- Jam Pulang untuk Perhitungan: 16:30 + 30 menit = 17:00
- Jam Kerja: 17:00 - 08:00 = 9 jam
- Kurangi istirahat: 9 - 1 = 8 jam

Expected Output:
- Jam Kerja: 8 jam
- Status: HKN (Hari Kerja Normal)
```

### **Test Case 2: Ramadhan dengan Izin Pulang Cepat**
```
Input:
- Tanggal: 2026-03-15 (dalam periode Ramadhan)
- Shift: 08:00 - 17:00
- Jam Masuk Aktual: 08:00
- Jam Pulang Aktual: 15:00
- Izin Pulang Cepat: ADA

Perhitungan:
- Jam Pulang untuk Perhitungan: 15:00 (TIDAK ditambah)
- Jam Kerja: 15:00 - 08:00 = 7 jam
- Kurangi istirahat: 7 - 1 = 6 jam

Expected Output:
- Jam Kerja: 6 jam
- Status: PC (Pulang Cepat)
```

### **Test Case 3: Ramadhan, Pulang Tepat Waktu**
```
Input:
- Tanggal: 2026-03-15 (dalam periode Ramadhan)
- Shift: 08:00 - 17:00
- Jam Masuk Aktual: 08:00
- Jam Pulang Aktual: 17:00 (pulang tepat waktu)
- Izin Pulang Cepat: TIDAK ADA

Perhitungan:
- Jam Pulang untuk Perhitungan: 17:00 + 30 menit = 17:30
- Jam Kerja: 17:30 - 08:00 = 9.5 jam
- Kurangi istirahat: 9.5 - 1 = 8.5 jam

Expected Output:
- Jam Kerja: 8.5 jam (lebih dari shift normal, tetap dihitung)
```

### **Test Case 4: Non-Ramadhan**
```
Input:
- Tanggal: 2026-04-15 (di luar periode Ramadhan)
- Shift: 08:00 - 17:00
- Jam Masuk Aktual: 08:00
- Jam Pulang Aktual: 16:30
- Izin Pulang Cepat: TIDAK ADA

Perhitungan:
- Jam Pulang untuk Perhitungan: 16:30 (TIDAK ditambah)
- Jam Kerja: 16:30 - 08:00 = 8.5 jam
- Kurangi istirahat: 8.5 - 1 = 7.5 jam

Expected Output:
- Jam Kerja: 7.5 jam (aktual)
```

---

## 🎯 Perbandingan Konsep

| Aspek | Konsep Sebelumnya | Konsep Baru (+30 Menit) |
|-------|-------------------|-------------------------|
| **Data Absensi** | Tidak berubah | Tidak berubah |
| **Perhitungan** | Cek jika < shift normal, gunakan shift normal | Tambah 30 menit ke jam pulang |
| **Transparansi** | Kurang jelas (logika kompleks) | Lebih jelas (tambah 30 menit) |
| **Fleksibilitas** | Terbatas (harus cek shift normal) | Lebih fleksibel (bisa berbeda adjustment) |
| **Akurasi** | Bisa kurang akurat (cap di shift normal) | Lebih akurat (presisi menit) |
| **Kompleksitas** | Lebih kompleks (perlu cek shift) | Lebih sederhana (tambah menit) |

---

## ✅ Kesimpulan

**Konsep Baru (Tambahkan 30 Menit ke Jam Pulang) lebih baik karena:**

1. ✅ **Lebih Transparan:** Logika jelas, mudah di-audit
2. ✅ **Lebih Fleksibel:** Bisa berbeda adjustment per periode
3. ✅ **Lebih Akurat:** Perhitungan presisi menit
4. ✅ **Lebih Sederhana:** Tidak perlu cek shift normal
5. ✅ **Data Absensi Tetap Aktual:** Tidak mengubah data yang sudah ada

**Implementasi:**
- Buat helper function `getJamPulangUntukPerhitungan()`
- Modifikasi perhitungan di semua controller yang relevan
- Cek izin pulang cepat sebelum adjustment
- Data absensi tetap tidak berubah

---

**Apakah skenario ini sesuai dengan kebutuhan Anda?** 🤔




