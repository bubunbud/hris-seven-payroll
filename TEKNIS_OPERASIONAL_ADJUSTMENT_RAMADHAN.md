# Teknis Operasional: Adjustment Jam Kerja Bulan Ramadhan

**Tanggal:** 19 Februari 2026  
**Tujuan:** Menjelaskan teknis pelaksanaan adjustment 30 menit untuk Ramadhan dan penanganan edge case izin pulang cepat

---

## 📋 Skenario yang Perlu Ditangani

### **Skenario 1: Pulang Lebih Awal karena Ramadhan (Umum)**
- **Kondisi:** Semua karyawan pulang lebih awal 30 menit selama Ramadhan
- **Tidak ada izin:** Tidak ada record di `t_izin` dengan `vcTipeIzin = 'Pulang Cepat'`
- **Perlakuan:** Jam kerja dianggap FULL sesuai shift normal (adjustment otomatis)

**Contoh:**
- Shift normal: 08:00 - 17:00 (8 jam kerja)
- Jam masuk aktual: 08:00
- Jam pulang aktual: 16:30 (pulang lebih awal 30 menit karena Ramadhan)
- **Jam kerja dihitung:** 8 jam (sesuai shift normal, bukan 7.5 jam aktual)

---

### **Skenario 2: Pulang Lebih Awal karena Izin Pulang Cepat**
- **Kondisi:** Karyawan mengajukan izin pulang cepat (ada record di `t_izin`)
- **Ada izin:** Ada record di `t_izin` dengan:
  - `vcKodeIzin` = 'Z003' atau 'Z004' (izin pribadi)
  - `vcTipeIzin` = 'Pulang Cepat'
  - `dtTanggal` = tanggal absensi
- **Perlakuan:** **TIDAK adjustment**, tetap dihitung sebagai izin pulang cepat (PC)

**Contoh:**
- Shift normal: 08:00 - 17:00 (8 jam kerja)
- Jam masuk aktual: 08:00
- Jam pulang aktual: 15:00 (karena izin pulang cepat)
- **Jam kerja dihitung:** 7 jam (aktual, tidak adjustment)
- **Status:** Terhitung sebagai PC (Pulang Cepat) di statistik/rekap

---

### **Skenario 3: Kombinasi (Ramadhan + Izin Pulang Cepat)**
- **Kondisi:** Dalam periode Ramadhan, tapi karyawan juga izin pulang cepat
- **Ada izin:** Ada record di `t_izin` dengan `vcTipeIzin = 'Pulang Cepat'`
- **Perlakuan:** **TIDAK adjustment**, tetap dihitung sebagai izin pulang cepat
- **Alasan:** Izin pulang cepat adalah keputusan individual, bukan kebijakan umum Ramadhan

**Contoh:**
- Periode: Ramadhan (adjustment aktif)
- Shift normal: 08:00 - 17:00
- Jam masuk aktual: 08:00
- Jam pulang aktual: 15:00 (karena izin pulang cepat, bukan karena Ramadhan)
- **Jam kerja dihitung:** 7 jam (aktual, tidak adjustment)
- **Status:** Terhitung sebagai PC (Pulang Cepat)

---

## 🔍 Logika Pembedaan

### **Cara Membedakan:**

1. **Cek apakah ada izin pulang cepat:**
   ```php
   $izinPulangCepat = Izin::where('vcNik', $nik)
       ->where('dtTanggal', $tanggal)
       ->whereIn('vcKodeIzin', ['Z003', 'Z004'])
       ->where('vcTipeIzin', 'Pulang Cepat')
       ->exists();
   ```

2. **Jika ADA izin pulang cepat:**
   - ❌ **TIDAK adjustment** (tetap hitung jam kerja aktual)
   - ✅ Tetap dihitung sebagai PC di statistik/rekap
   - ✅ Potongan HC tetap berlaku (jika ada)

3. **Jika TIDAK ADA izin pulang cepat:**
   - ✅ **Adjustment otomatis** (jika dalam periode Ramadhan)
   - ✅ Jam kerja = shift normal
   - ✅ Tidak terhitung sebagai PC

---

## 💡 Implementasi Teknis

### **Modifikasi Perhitungan Jam Kerja**

**File:** `app/Http/Controllers/RekapitulasiAbsensiController.php` (dan controller lain)

```php
use App\Models\Izin;
use App\Services\PeriodeKhususService;

// Di method perhitungan jam kerja:
$jamKerja = round($menit / 60, 2);

// Cek apakah dalam periode Ramadhan
$periodeKhusus = PeriodeKhususService::getPeriodeKhusus($tanggal);

if ($periodeKhusus) {
    // Cek apakah ada izin pulang cepat untuk tanggal ini
    $izinPulangCepat = Izin::where('vcNik', $absen->vcNik)
        ->where('dtTanggal', $tanggal->format('Y-m-d'))
        ->whereIn('vcKodeIzin', ['Z003', 'Z004'])
        ->where('vcTipeIzin', 'Pulang Cepat')
        ->exists();
    
    // Hanya adjustment jika TIDAK ada izin pulang cepat
    if (!$izinPulangCepat) {
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
    // Jika ada izin pulang cepat, tetap pakai jam kerja aktual (tidak adjustment)
}
```

---

## 📊 Flow Diagram

```
START: Perhitungan Jam Kerja
  │
  ├─ Hitung jam kerja aktual (pulang - masuk)
  │
  ├─ Cek: Apakah dalam periode Ramadhan?
  │   │
  │   ├─ NO → Gunakan jam kerja aktual (END)
  │   │
  │   └─ YES → Cek: Apakah ada izin pulang cepat?
  │       │
  │       ├─ YES → Gunakan jam kerja aktual (tidak adjustment)
  │       │         Status: PC (Pulang Cepat)
  │       │
  │       └─ NO → Cek: Apakah jam kerja aktual < shift normal?
  │           │
  │           ├─ NO → Gunakan jam kerja aktual (sudah cukup)
  │           │
  │           └─ YES → Gunakan shift normal (adjustment)
  │                     Status: HKN (Hari Kerja Normal)
END
```

---

## ✅ Test Cases

### **Test Case 1: Ramadhan Normal (Tidak Ada Izin)**
```
Input:
- Tanggal: 2026-03-15 (dalam periode Ramadhan)
- Shift: 08:00 - 17:00
- Jam Masuk: 08:00
- Jam Pulang: 16:30 (pulang lebih awal 30 menit)
- Izin Pulang Cepat: TIDAK ADA

Expected Output:
- Jam Kerja: 8 jam (shift normal)
- Status: HKN (Hari Kerja Normal)
- Tidak terhitung sebagai PC
```

### **Test Case 2: Ramadhan dengan Izin Pulang Cepat**
```
Input:
- Tanggal: 2026-03-15 (dalam periode Ramadhan)
- Shift: 08:00 - 17:00
- Jam Masuk: 08:00
- Jam Pulang: 15:00 (karena izin pulang cepat)
- Izin Pulang Cepat: ADA (t_izin dengan vcTipeIzin = 'Pulang Cepat')

Expected Output:
- Jam Kerja: 7 jam (aktual, tidak adjustment)
- Status: PC (Pulang Cepat)
- Terhitung sebagai PC di statistik/rekap
```

### **Test Case 3: Non-Ramadhan dengan Izin Pulang Cepat**
```
Input:
- Tanggal: 2026-04-15 (di luar periode Ramadhan)
- Shift: 08:00 - 17:00
- Jam Masuk: 08:00
- Jam Pulang: 15:00 (karena izin pulang cepat)
- Izin Pulang Cepat: ADA

Expected Output:
- Jam Kerja: 7 jam (aktual)
- Status: PC (Pulang Cepat)
- Tidak ada adjustment (karena bukan Ramadhan)
```

### **Test Case 4: Ramadhan, Jam Kerja Sudah Cukup**
```
Input:
- Tanggal: 2026-03-15 (dalam periode Ramadhan)
- Shift: 08:00 - 17:00
- Jam Masuk: 08:00
- Jam Pulang: 17:00 (pulang tepat waktu, tidak lebih awal)
- Izin Pulang Cepat: TIDAK ADA

Expected Output:
- Jam Kerja: 8 jam (aktual, sudah sesuai shift normal)
- Status: HKN (Hari Kerja Normal)
- Tidak perlu adjustment (sudah cukup)
```

---

## 🎯 Kesimpulan

### **Prinsip Utama:**

1. **Adjustment hanya untuk Ramadhan umum** (tidak ada izin pulang cepat)
2. **Izin pulang cepat selalu dihitung aktual** (tidak adjustment), meskipun dalam periode Ramadhan
3. **Izin pulang cepat adalah keputusan individual**, bukan kebijakan umum

### **Prioritas:**

1. **Pertama:** Cek apakah ada izin pulang cepat
   - Jika ADA → Tidak adjustment, tetap hitung aktual
2. **Kedua:** Cek apakah dalam periode Ramadhan
   - Jika YA dan TIDAK ada izin → Adjustment otomatis
3. **Ketiga:** Cek apakah jam kerja aktual < shift normal
   - Jika YA → Gunakan shift normal (adjustment)
   - Jika TIDAK → Gunakan jam kerja aktual (sudah cukup)

---

## ❓ FAQ

**Q: Bagaimana jika karyawan izin pulang cepat selama Ramadhan?**
A: Tetap dihitung sebagai izin pulang cepat (tidak adjustment). Izin pulang cepat adalah keputusan individual, bukan kebijakan umum Ramadhan.

**Q: Apakah adjustment berlaku untuk semua karyawan?**
A: Ya, selama dalam periode Ramadhan dan TIDAK ada izin pulang cepat. Jika ada izin pulang cepat, tidak adjustment.

**Q: Bagaimana jika jam kerja aktual sudah lebih dari shift normal?**
A: Tetap gunakan jam kerja aktual (tidak adjustment). Adjustment hanya untuk kasus jam kerja kurang dari shift normal.

**Q: Apakah adjustment mempengaruhi perhitungan PC (Pulang Cepat) di statistik?**
A: Tidak. Jika ada izin pulang cepat, tetap terhitung sebagai PC. Adjustment hanya untuk perhitungan jam kerja, bukan untuk statistik PC.

---

**Apakah teknis operasional ini sudah jelas?** 🤔




