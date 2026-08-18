# Memory — Rebuild HRIS Seven Payroll (Diskusi)

**Tanggal diskusi:** 24–27 Juli 2026  
**Status:** **DITUNDA** — lanjutkan nanti; belum ada coding/implementasi  
**Path rencana greenfield:** `e:\projects\new-hris-seven`  
**Sistem saat ini (referensi):** `c:\xampp\htdocs\hris-seven-payroll` (Laravel monolith)

---

## Tujuan rebuild (yang sudah disepakati arahnya)

- Rebuild ulang aplikasi, bukan sekadar polish UI lama.
- Pecah menjadi **modul besar** agar bisa dijalankan di **container** masing-masing.
- Tampilan / style **seperti Odoo** (UX/pola layar), **bukan** wajib pakai platform Odoo.
- Diskusi saja dulu; implementasi ditunda.

---

## Keputusan sementara (arah pilihan)

### Opsi yang dibahas
| Opsi | Isi |
|------|-----|
| **A** | Laravel API + SPA frontend + **Odoo look** |
| B | Full Odoo custom |
| C | Stack baru total (mis. Nest/Go + React) |

### Arah yang paling realistis untuk kasus ini
**Opsi A + Odoo look** — backend Laravel (API), frontend SPA terpisah, UI mirip Odoo, DB & integrasi tetap di backend.

**Alasan singkat:**
- Logika bisnis sudah banyak di Laravel (closing, JHK, potongan, JM1–JM4, izin, rekap, permission, API HRIS/Supabase/fingerprint).
- Tidak membuang aset domain knowledge.
- Container-friendly tanpa microservice berlebihan.
- Odoo look = UX modern tanpa lisensi/kompleksitas Odoo penuh untuk payroll lokal.

---

## Arsitektur target (kasar — fase awal)

| Container / service | Peran |
|---------------------|--------|
| **frontend** | SPA (React atau Vue) — UI Odoo-like |
| **backend / API** | Laravel API — bisnis, auth, integrasi |
| **database** | MySQL atau PostgreSQL |
| **redis + worker/queue** | Closing batch, sync fingerprint/API |
| **gateway** | Nginx / Traefik |

**Jangan** pecah “setiap menu = microservice” di awal. Pecah kasar dulu.

### Pola UI “Odoo look” (bukan hanya warna)
- Sidebar modul (Master, Absensi, Payroll, Laporan, Settings)
- List view: tabel padat, filter, action Create/Import
- Form view: field bergrup, Save/Discard
- Search + filter chip
- Laporan cetak landscape + pengesahan: **tetap custom** (bukan mengandalkan Odoo)

---

## Strategi migrasi yang disarankan

**Strangler + parallel run**, bukan big bang.

1. Fase 0 — Discovery: inventaris modul, kamus data, formula closing/rekap, permission  
2. Fase 1 — Fondasi: monorepo/skeleton + Docker Compose + design kit Odoo-like + auth/RBAC  
3. Fase 2 — Modul inti berurutan: Master → Absensi/Tidak Masuk → Closing → Laporan → Integrasi  
4. Fase 3 — Parallel run: bandingkan angka closing & rekap lama vs baru  
5. Fase 4 — Cutover & retire Blade/monolith lama  

Backend baru boleh sementara baca/tulis **DB lama** (anti-corruption) sampai skema baru siap.

---

## Yang membuat rebuild sulit (bukan mustahil)

1. Logika bisnis tersebar di controller (closing, absensi, overlap izin, dinas luar, dll.).
2. Ketergantungan skema: `t_closing`, `t_absen`, `t_tidak_masuk`, `m_divisi` (+ tanda tangan/pengesahan), dll.
3. Integrasi eksternal: HRIS API, Supabase, mesin fingerprint.
4. Permission granular yang sudah ada.
5. Layout laporan cetak + pengesahan sensitif.

**Inti kesulitan = migrasi pengetahuan bisnis + data**, bukan Docker/UI.

---

## Yang perlu disiapkan sebelum coding (checklist)

- [ ] Scope v1 (payroll saja / absensi / full?)
- [ ] Strangler vs ganti total
- [ ] Definisi “Odoo look” yang cukup untuk desain
- [ ] Inventaris modul dari sidebar saat ini + prioritas
- [ ] Kamus data + formula closing/rekap (dokumentasi dari kode lama)
- [ ] Model permission target
- [ ] Kontrak API frontend–backend
- [ ] Strategi migrasi data / dual-write / cutover
- [ ] Staging + parallel run checklist
- [ ] Keputusan stack SPA: **Vue vs React**, **SPA murni vs Inertia**

---

## Pertanyaan terbuka (belum diputuskan — lanjut di sini nanti)

1. SPA **murni** (React/Vue + container sendiri) atau **Inertia** (lebih cepat, coupling lebih erat)?
2. Modul pilot pertama: **Master** atau **Closing/Rekap**?
3. DB: **tetap MySQL skema lama** dulu, atau **skema baru + migrasi**?
4. Monorepo (`apps/api` + `apps/web`) vs polyrepo?

---

## Stack usulan jika lanjut Opsi A (belum final)

- Laravel 11 API + Sanctum  
- Vue 3 atau React + Vite (SPA)  
- Tailwind + komponen custom bertema ERP/Odoo-like  
- Docker Compose: `web`, `api`, `db`, `redis`, `worker`  
- Tim kecil: Vue + Laravel sering lebih cepat onboarding  

---

## Konteks sistem lama yang relevan (saat diskusi)

- Repo aktif: Laravel HRIS/payroll monolith + banyak Blade laporan.
- Fitur padat: closing gaji, statistik/rekap absensi, fingerprint, Supabase leave/attendance, List Pengajuan Cuti/Izin API, pengesahan laporan (Manager Fin-Acc / Sr. Mgr. Fincount, dll.).
- Production: Ubuntu; deploy sering manual copy file + migrate.
- Error tipikal yang pernah muncul: trait belum ter-deploy, CSRF 419, mismatch controller vs view (`jam_lembur_jm4`).

→ Saat rebuild, **deploy lengkap + kontrak API + parallel test angka** harus jadi disiplin dari awal.

---

## Cara melanjutkan sesi nanti

Prompt singkat untuk AI/tim:

> Lanjutkan diskusi rebuild HRIS Seven dari `MEMORY_REBUILD_HRIS_SEVEN_2026_07.md`. Status: ditunda, arah Opsi A (Laravel API + SPA + Odoo look), path rencana `e:\projects\new-hris-seven`. Belum coding. Putuskan pertanyaan terbuka #1–#4 lalu buat blueprint folder di atas kertas.

---

## Catatan

- File ini **hanya memori diskusi** — tidak mengubah aplikasi production.
- Tidak ada implementasi di `e:\projects\new-hris-seven` pada saat file ini dibuat.
