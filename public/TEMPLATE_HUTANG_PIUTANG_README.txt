================================================================================
TEMPLATE CSV UPLOAD HUTANG-PIUTANG KARYAWAN
================================================================================

FORMAT FILE CSV:
----------------
File CSV harus memiliki format sebagai berikut:

Kolom 1: Periode Awal (Tanggal)
Kolom 2: Periode Akhir (Tanggal)
Kolom 3: NIK (Nomor Induk Karyawan)
Kolom 4: Jenis (Kode Jenis Hutang/Piutang)
Kolom 5: D/K (Debit atau Kredit)
Kolom 6: Jumlah (Nilai Numerik)
Kolom 7: Keterangan (Opsional)

CONTOH FORMAT:
--------------
Periode Awal,Periode Akhir,NIK,Jenis,D/K,Jumlah,Keterangan
2025-01-01,2025-01-31,19800002,0,Kredit,500000.00,Potongan Koperasi Bulan Januari
2025-01-01,2025-01-31,19910003,1,Kredit,250000.00,Potongan DPLK Bulan Januari

KETERANGAN KOLOM:
-----------------
1. Periode Awal & Periode Akhir
   - Format: YYYY-MM-DD (contoh: 2025-01-01)
   - Atau format lain: DD/MM/YYYY, DD-MM-YYYY, dll
   - Periode Akhir harus >= Periode Awal
   - WAJIB DIISI

2. NIK (Nomor Induk Karyawan)
   - Harus ada di Master Karyawan
   - Maksimal 10 karakter
   - WAJIB DIISI

3. Jenis (Kode Jenis Hutang/Piutang)
   - Kode yang ada di Master Hutang-Piutang
   - Contoh: 0, 1, 2, 3, 4
   - WAJIB DIISI
   - Daftar Jenis:
     * 0 = Potongan Koperasi
     * 1 = Potongan DPLK
     * 2 = Potongan SPN
     * 3 = Selisih Upah
     * 4 = Potongan Lain-lain

4. D/K (Debit/Kredit)
   - Nilai: "Debit", "Kredit", "0", "1", "d", atau "k" (case insensitive)
   - Contoh: "Debit", "Kredit", "0", "1", "d", "k"
   - WAJIB DIISI
   - Catatan: 
     * Debit = 0 (menambah pada formulasi)
     * Kredit = 1 (mengurangi pada formulasi)
     * Sistem akan otomatis mengkonversi teks ke kode: Debit → 0, Kredit → 1

5. Jumlah
   - Format: Angka (bisa menggunakan titik atau koma sebagai pemisah desimal)
   - Contoh: 500000.00 atau 500000,00 atau 500000
   - Harus > 0
   - WAJIB DIISI

6. Keterangan
   - Maksimal 35 karakter
   - OPSIONAL (boleh kosong)

CATATAN PENTING:
----------------
1. Baris pertama (header) akan di-skip jika opsi "Skip baris pertama" dicentang
2. Data dengan Periode Awal, Periode Akhir, NIK, dan Jenis yang sama tidak boleh duplikat
3. NIK harus ada di Master Karyawan
4. Jenis harus ada di Master Hutang-Piutang
5. Format tanggal fleksibel, sistem akan otomatis mendeteksi format yang digunakan
6. Jumlah bisa menggunakan format Indonesia (koma) atau internasional (titik)

CONTOH DATA DUMMY:
------------------
File template_hutang_piutang.csv sudah berisi contoh data dummy yang bisa digunakan
sebagai acuan untuk membuat data real.

LANGKAH UPLOAD:
---------------
1. Download template CSV dari halaman Upload
2. Buka file dengan Excel atau text editor
3. Isi data sesuai format yang ditentukan
4. Simpan sebagai CSV (UTF-8 encoding disarankan)
5. Upload file melalui form Upload di halaman Hutang-Piutang
6. Pastikan checkbox "Skip baris pertama (header)" dicentang jika file memiliki header

================================================================================

