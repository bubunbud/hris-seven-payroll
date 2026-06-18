@php
    $fmtDate = function ($v) {
        if ($v === null || $v === '') {
            return '—';
        }
        try {
            return \Carbon\Carbon::parse($v)->format('d/m/Y');
        } catch (\Throwable $e) {
            return '—';
        }
    };
    $hubMap = [
        'SPOUSE' => 'Pasangan', 'SUAMI/ISTRI' => 'Pasangan', 'CHILD' => 'Anak', 'PARENT' => 'Orang tua',
        'SIBLING' => 'Saudara', 'ANAK' => 'Anak',
    ];
    $catatanHasIsi = function ($v) {
        if ($v === null) {
            return false;
        }
        if (is_string($v)) {
            return trim($v) !== '';
        }

        return true;
    };
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Biodata — {{ $karyawan->Nama }} ({{ $karyawan->Nik }})</title>
    <style>
        :root {
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --accent: #1e3a5f;
            --paper: #ffffff;
            --soft: #f8fafc;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: var(--ink);
            background: #e5e7eb;
            line-height: 1.45;
            font-size: 11pt;
        }
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            padding: 0.75rem 1rem;
            background: rgba(15, 23, 42, 0.92);
            backdrop-filter: blur(8px);
        }
        .toolbar button {
            padding: 0.5rem 1.25rem;
            border: none;
            border-radius: 0.375rem;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .toolbar .btn-print { background: #fff; color: var(--accent); }
        .toolbar .btn-close { background: transparent; color: #94a3b8; border: 1px solid #475569; }
        .wrap {
            max-width: 210mm;
            margin: 0 auto;
            padding: 12mm 14mm 18mm;
            background: var(--paper);
        }
        /* Satu dokumen berkelanjutan; hindari judul/orphan terpotong */
        .cv-section {
            margin-bottom: 1.35rem;
        }
        h2.section-title {
            page-break-after: avoid;
            break-after: avoid;
        }
        .cv-header {
            break-inside: avoid;
            page-break-inside: avoid;
        }
        dl.grid {
            break-inside: avoid;
            page-break-inside: avoid;
        }
        table.data {
            page-break-inside: auto;
            break-inside: auto;
        }
        table.data thead {
            display: table-header-group;
        }
        table.data tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .cv-header {
            display: flex;
            gap: 1.25rem;
            align-items: flex-start;
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid var(--accent);
        }
        .cv-photo {
            width: 110px;
            height: 140px;
            object-fit: contain;
            background: var(--soft);
            border: 1px solid var(--line);
            border-radius: 4px;
        }
        .cv-photo-ph {
            width: 110px;
            height: 140px;
            background: var(--soft);
            border: 1px dashed var(--line);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            font-size: 0.75rem;
            text-align: center;
            padding: 0.5rem;
        }
        h1.cv-name {
            margin: 0 0 0.25rem;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 1.65rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--accent);
        }
        .cv-sub { color: var(--muted); font-size: 0.95rem; margin: 0 0 0.5rem; }
        .section-title {
            font-family: Georgia, "Times New Roman", serif;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--accent);
            margin: 0 0 0.75rem;
            padding-bottom: 0.35rem;
            border-bottom: 1px solid var(--line);
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        dl.grid {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: 0.35rem 1rem;
            margin: 0;
        }
        dl.grid dt {
            color: var(--muted);
            font-weight: 600;
            font-size: 0.88rem;
        }
        dl.grid dd { margin: 0; }
        table.data {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.92rem;
        }
        table.data th, table.data td {
            border: 1px solid var(--line);
            padding: 0.45rem 0.55rem;
            text-align: left;
            vertical-align: top;
        }
        table.data thead th {
            background: var(--soft);
            color: var(--accent);
            font-weight: 600;
            font-size: 0.82rem;
        }
        .muted { color: var(--muted); }
        .footer-meta {
            margin-top: 1.5rem;
            font-size: 0.8rem;
            color: var(--muted);
            text-align: right;
        }
        /* Catatan karyawan: formulir vertikal per record (portrait-friendly) */
        .catatan-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .catatan-block {
            break-inside: avoid;
            page-break-inside: avoid;
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 0.65rem 0.85rem 0.75rem;
            margin-bottom: 0.9rem;
            background: var(--soft);
        }
        .catatan-block__head {
            display: flex;
            align-items: flex-start;
            gap: 0.55rem;
            margin-bottom: 0.5rem;
            padding-bottom: 0.45rem;
            border-bottom: 1px solid var(--line);
        }
        .catatan-block__num {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.65rem;
            height: 1.65rem;
            padding: 0 0.35rem;
            background: var(--accent);
            color: #fff;
            border-radius: 4px;
            font-size: 0.82rem;
            font-weight: 700;
        }
        .catatan-block__judul {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--ink);
            line-height: 1.35;
        }
        dl.catatan-dl {
            display: grid;
            grid-template-columns: minmax(110px, 32%) 1fr;
            gap: 0.35rem 0.85rem;
            margin: 0;
            font-size: 0.88rem;
        }
        dl.catatan-dl dt {
            margin: 0;
            color: var(--muted);
            font-weight: 600;
            font-size: 0.82rem;
        }
        dl.catatan-dl dd {
            margin: 0;
            color: var(--ink);
            word-break: break-word;
        }
        dl.catatan-dl dd.catatan-dl__deskripsi {
            white-space: pre-wrap;
        }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .wrap {
                max-width: none;
                margin: 0;
                padding: 10mm 12mm;
            }
            /* Data pribadi … pekerjaan di halaman 1; Riwayat Mutasi dst. mulai halaman 2 */
            .cv-section--page-2 {
                page-break-before: always;
                break-before: page;
            }
        }
        @page { margin: 12mm; size: A4 portrait; }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button type="button" class="btn-print" onclick="window.print()">Cetak / Simpan PDF</button>
        <button type="button" class="btn-close" onclick="window.close()">Tutup</button>
    </div>

    <div class="wrap">
        {{-- Biodata: satu alur; potong halaman alami, judul tidak menggantung --}}
        <section class="cv-section">
            <div class="cv-header">
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="Foto" class="cv-photo" crossorigin="anonymous">
                @else
                    <div class="cv-photo-ph">Foto<br>belum ada</div>
                @endif
                <div>
                    <h1 class="cv-name">{{ $karyawan->Nama }}</h1>
                    <p class="cv-sub">NIK: {{ $karyawan->Nik }} @if($karyawan->intNoBadge) · No. KTP: {{ $karyawan->intNoBadge }} @endif</p>
                    <p class="cv-sub" style="margin:0;">
                        @php
                            $jn = optional($karyawan->jabatan)->vcNamaJabatan;
                            $dn = optional($karyawan->divisi)->vcNamaDivisi;
                        @endphp
                        @if($jn){{ $jn }}@endif @if($jn && $dn) · @endif @if($dn){{ $dn }}@endif
                    </p>
                </div>
            </div>

            <h2 class="section-title">Data Pribadi</h2>
            <dl class="grid">
                <dt>Tempat / Tgl Lahir</dt>
                <dd>{{ $karyawan->Tempat_lahir ?? '—' }}, {{ $fmtDate($karyawan->TTL) }}</dd>
                <dt>Jenis Kelamin</dt>
                <dd>{{ $karyawan->Jenis_Kelamin ?? '—' }}</dd>
                <dt>Agama</dt>
                <dd>{{ $karyawan->Agama ?? '—' }}</dd>
                <dt>Status Perkawinan</dt>
                <dd>{{ $karyawan->Status_Kawin ?? '—' }}</dd>
                <dt>Warga Negara</dt>
                <dd>{{ $karyawan->Warga_Negara ?? '—' }}</dd>
                <dt>Alamat</dt>
                <dd>{{ $karyawan->Alamat ?? '—' }}</dd>
                <dt>Kecamatan / Kota</dt>
                <dd>{{ $karyawan->Kecamatan ?? '—' }} @if($karyawan->Kota) · {{ $karyawan->Kota }} @endif @if($karyawan->Kode_pos) · {{ $karyawan->Kode_pos }} @endif</dd>
                <dt>Telepon / HP</dt>
                <dd>{{ $karyawan->Telp ?? '—' }} @if($karyawan->Cell_Phone1) · {{ $karyawan->Cell_Phone1 }} @endif @if($karyawan->Cell_Phone2) · {{ $karyawan->Cell_Phone2 }} @endif</dd>
                <dt>Email</dt>
                <dd>{{ $karyawan->Personal_Email ?? '—' }}</dd>
                <dt>No. Rekening</dt>
                <dd>{{ $karyawan->intNorek ?? '—' }}</dd>
            </dl>
        </section>

        <section class="cv-section">
            <h2 class="section-title">Data Fisik &amp; Medis</h2>
            <dl class="grid">
                <dt>Tinggi Badan</dt>
                <dd>{{ $karyawan->Tinggi_bdn ? $karyawan->Tinggi_bdn.' cm' : '—' }}</dd>
                <dt>Berat Badan</dt>
                <dd>{{ $karyawan->Berat_bdn ? $karyawan->Berat_bdn.' kg' : '—' }}</dd>
                <dt>Golongan Darah</dt>
                <dd>{{ $karyawan->Gol_Darah ?? '—' }}</dd>
                <dt>Berkacamata</dt>
                <dd>{{ ($karyawan->Berkacamata == 1 || $karyawan->Berkacamata === '1') ? 'Ya' : 'Tidak' }}</dd>
                <dt>Buta Warna</dt>
                <dd>{{ ($karyawan->Buta_Warna == 1 || $karyawan->Buta_Warna === '1') ? 'Ya' : 'Tidak' }}</dd>
                <dt>Cacat Fisik</dt>
                <dd>{{ ($karyawan->Cacat_Fisik == 1 || $karyawan->Cacat_Fisik === '1') ? 'Ya' : 'Tidak' }}</dd>
            </dl>
        </section>

        <section class="cv-section">
            <h2 class="section-title">Data Pekerjaan</h2>
            <dl class="grid">
                <dt>Divisi</dt>
                <dd>{{ optional($karyawan->divisi)->vcNamaDivisi ?? ($karyawan->Divisi ?? '—') }}</dd>
                <dt>Departemen</dt>
                <dd>{{ optional($karyawan->departemen)->vcNamaDept ?? ($karyawan->dept ?? '—') }}</dd>
                <dt>Bagian</dt>
                <dd>{{ optional($karyawan->bagian)->vcNamaBagian ?? ($karyawan->vcKodeBagian ?? '—') }}</dd>
                <dt>Seksi</dt>
                <dd>{{ optional($seksi)->vcNamaseksi ?? ($karyawan->vcKodeSeksi ?? '—') }}</dd>
                <dt>Golongan</dt>
                <dd>@if($golongan) {{ $golongan->vcKodeGolongan }} — {{ $golongan->vcNamaGolongan }} @else {{ $karyawan->Gol ?? '—' }} @endif</dd>
                <dt>Jabatan</dt>
                <dd>{{ optional($karyawan->jabatan)->vcNamaJabatan ?? ($karyawan->Jabat ?? '—') }}</dd>
                <dt>Group Pegawai</dt>
                <dd>{{ $karyawan->Group_pegawai ?? '—' }}</dd>
                <dt>Status Pegawai</dt>
                <dd>{{ $karyawan->Status_Pegawai ?? '—' }}</dd>
                <dt>Shift</dt>
                <dd>@if($karyawan->shift) {{ $karyawan->shift->vcShift }} @else {{ $karyawan->vcShift ?? '—' }} @endif</dd>
                <dt>Tgl Masuk</dt>
                <dd>{{ $fmtDate($karyawan->Tgl_Masuk) }}</dd>
                <dt>Tgl Berhenti</dt>
                <dd>{{ $fmtDate($karyawan->Tgl_Berhenti) }}</dd>
                <dt>Status Aktif</dt>
                <dd>{{ ($karyawan->vcAktif == 1 || $karyawan->vcAktif === '1') ? 'Aktif' : 'Tidak aktif' }}</dd>
            </dl>
        </section>

        {{-- Halaman 2 onward: mulai dari Riwayat Mutasi --}}
        <section class="cv-section cv-section--page-2">
            <h2 class="section-title">Riwayat Mutasi</h2>
            @if($mutasi->isEmpty())
                <p class="muted">Belum ada data mutasi.</p>
            @else
                <table class="data">
                    <thead>
                        <tr>
                            <th>No. SK</th>
                            <th>Tgl SK</th>
                            <th>Divisi</th>
                            <th>Dept</th>
                            <th>Bagian</th>
                            <th>Seksi</th>
                            <th>Jabatan</th>
                            <th>Dok. SK</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mutasi as $m)
                            <tr>
                                <td>{{ $m->NoSK ?? '—' }}</td>
                                <td>{{ $fmtDate($m->vcTglSK ?? null) }}</td>
                                <td>{{ $m->vcDivisi ?? '—' }}</td>
                                <td>{{ $m->vcDept ?? '—' }}</td>
                                <td>{{ $m->vcbagian ?? '—' }}</td>
                                <td>{{ $m->vcSeksi ?? '—' }}</td>
                                <td>{{ $m->vcJabatan ?? '—' }}</td>
                                <td>@if(!empty($m->vcFileSK)) Ya @else — @endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <section class="cv-section">
            <h2 class="section-title">Pendidikan</h2>
            @if($pendidikan->isEmpty())
                <p class="muted">Belum ada data pendidikan.</p>
            @else
                <table class="data">
                    <thead>
                        <tr>
                            <th>Jenjang</th>
                            <th>Institusi</th>
                            <th>Jurusan</th>
                            <th>Thn Masuk</th>
                            <th>Thn Selesai</th>
                            <th>IPK</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendidikan as $p)
                            <tr>
                                <td>{{ $p->education_level ?? '—' }}</td>
                                <td>{{ $p->institution_name ?? '—' }}</td>
                                <td>{{ $p->major ?? '—' }}</td>
                                <td>{{ $p->start_year ?? '—' }}</td>
                                <td>{{ $p->end_year ?? '—' }}</td>
                                <td>{{ $p->gpa ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <section class="cv-section">
            <h2 class="section-title">Keluarga</h2>
            <dl class="grid">
                <dt>Nama Ayah</dt>
                <dd>{{ filled($namaAyahOrtu ?? null) ? $namaAyahOrtu : '—' }}</dd>
                <dt>Nama Ibu</dt>
                <dd>{{ filled($namaIbuOrtu ?? null) ? $namaIbuOrtu : '—' }}</dd>
            </dl>
            @if($keluarga->isEmpty())
                <p class="muted" style="margin-top:1rem;">Belum ada anggota keluarga tambahan (tabel).</p>
            @else
                <h3 style="font-size:0.95rem;margin:1rem 0 0.5rem;color:var(--muted);font-weight:600;">Anggota keluarga</h3>
                <table class="data">
                    <thead>
                        <tr>
                            <th>Hubungan</th>
                            <th>Nama</th>
                            <th>L/P</th>
                            <th>Tempat Lahir</th>
                            <th>Tgl Lahir</th>
                            <th>Gol. Darah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($keluarga as $k)
                            @php
                                $hub = $k->hubKeluarga ?? '';
                                $hubKey = strtoupper(preg_replace('/\s+/', '', $hub));
                                $hubLabel = $hubMap[$hubKey] ?? $hubMap[strtoupper($hub)] ?? $hub;
                            @endphp
                            <tr>
                                <td>{{ $hubLabel ?: '—' }}</td>
                                <td>{{ $k->NamaKeluarga ?? '—' }}</td>
                                <td>{{ $k->jenKelamin ?? '—' }}</td>
                                <td>{{ $k->temLahir ?? '—' }}</td>
                                <td>{{ $fmtDate($k->tglLahir ?? null) }}</td>
                                <td>{{ $k->golDarah ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <section class="cv-section">
            <h2 class="section-title">Pelatihan</h2>
            @if($pelatihan->isEmpty())
                <p class="muted">Belum ada data pelatihan.</p>
            @else
                <table class="data">
                    <thead>
                        <tr>
                            <th>Nama Pelatihan</th>
                            <th>Penyelenggara</th>
                            <th>Lokasi</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Sertifikat</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pelatihan as $pl)
                            <tr>
                                <td>{{ $pl->nm_pelatihan ?? '—' }}</td>
                                <td>{{ $pl->penyelenggara ?? '—' }}</td>
                                <td>{{ $pl->lokasi ?? '—' }}</td>
                                <td>{{ $fmtDate($pl->tg_pelatihan ?? null) }}</td>
                                <td>{{ $fmtDate($pl->tg_selesai ?? null) }}</td>
                                <td>{{ ($pl->Sertifikasi ?? $pl->sertifikat ?? 0) ? 'Ya' : 'Tidak' }}</td>
                                <td>{{ $pl->Keterangan ?? $pl->keterangan ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <section class="cv-section">
            <h2 class="section-title">Catatan Karyawan</h2>
            <p class="muted" style="margin:-0.35rem 0 0.75rem;font-size:0.88rem;">Riwayat disiplin, penghargaan, SP, teguran, dan catatan HR lainnya. Setiap entri ditampilkan sebagai blok terpisah; hanya field yang berisi yang dicetak.</p>
            @if($catatanKaryawan->isEmpty())
                <p class="muted">Belum ada catatan karyawan.</p>
            @else
                <ul class="catatan-list">
                    @foreach($catatanKaryawan as $ck)
                        <li class="catatan-block">
                            <div class="catatan-block__head">
                                <span class="catatan-block__num" aria-hidden="true">{{ $loop->iteration }}</span>
                                @if($catatanHasIsi($ck->judul ?? null))
                                    <p class="catatan-block__judul">{{ $ck->judul }}</p>
                                @endif
                            </div>
                            <dl class="catatan-dl">
                                @if($catatanHasIsi($ck->tanggal ?? null))
                                    <dt>Tanggal kejadian / dokumen</dt>
                                    <dd>{{ $fmtDate($ck->tanggal) }}</dd>
                                @endif
                                @if($catatanHasIsi($ck->jenis ?? null))
                                    <dt>Jenis</dt>
                                    <dd>{{ $ck->jenis }}</dd>
                                @endif
                                @if($catatanHasIsi($ck->kategori ?? null))
                                    <dt>Kategori</dt>
                                    <dd>{{ $ck->kategori }}</dd>
                                @endif
                                @if($catatanHasIsi($ck->status ?? null))
                                    <dt>Status</dt>
                                    <dd>{{ $ck->status }}</dd>
                                @endif
                                @if($catatanHasIsi($ck->level ?? null))
                                    <dt>Level</dt>
                                    <dd>{{ $ck->level }}</dd>
                                @endif
                                @if($catatanHasIsi($ck->no_dokumen ?? null))
                                    <dt>No. dokumen</dt>
                                    <dd>{{ $ck->no_dokumen }}</dd>
                                @endif
                                @if($catatanHasIsi($ck->tanggal_berlaku ?? null))
                                    <dt>Tanggal berlaku</dt>
                                    <dd>{{ $fmtDate($ck->tanggal_berlaku) }}</dd>
                                @endif
                                @if($catatanHasIsi($ck->tanggal_berakhir ?? null))
                                    <dt>Tanggal berakhir</dt>
                                    <dd>{{ $fmtDate($ck->tanggal_berakhir) }}</dd>
                                @endif
                                @if($catatanHasIsi($ck->deskripsi ?? null))
                                    <dt>Deskripsi</dt>
                                    <dd class="catatan-dl__deskripsi">{{ $ck->deskripsi }}</dd>
                                @endif
                                @if(!empty($ck->file_url))
                                    <dt>Lampiran</dt>
                                    <dd><a href="{{ $ck->file_url }}" target="_blank" rel="noopener">Unduh berkas</a></dd>
                                @endif
                            </dl>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <p class="footer-meta">Dicetak: {{ now()->format('d/m/Y H:i') }} · HRIS Seven Payroll — Arsip Biodata Karyawan</p>
    </div>
</body>
</html>
