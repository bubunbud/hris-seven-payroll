<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HRIS Seven Payroll')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f6f8fb;
        }

        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 216px;
            background: #1f2937;
            color: #fff;
            overflow-y: auto;
        }

        .sidebar .brand {
            padding: 16px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            font-weight: 600;
        }

        .sidebar a {
            color: #e5e7eb;
            text-decoration: none;
        }

        .menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .menu-item {
            border-bottom: 1px solid rgba(255, 255, 255, .04);
        }

        .menu-button {
            width: 100%;
            padding: 12px 16px;
            background: transparent;
            border: 0;
            color: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .menu-button:hover {
            background: rgba(255, 255, 255, .06);
        }

        .menu-icon {
            width: 28px;
            text-align: center;
            margin-right: 8px;
            color: #9ca3af;
        }

        .submenu {
            list-style: none;
            padding-left: 36px;
        }

        .submenu a {
            display: block;
            padding: 8px 0;
            color: #cfd4dc;
        }

        .submenu a:hover {
            color: #fff;
        }

        .content {
            flex: 1;
            padding: 24px;
        }

        .content .page-title {
            margin-bottom: 12px;
        }

        .small-muted {
            color: #6b7280;
            font-size: 12px;
        }

        @media (max-width: 992px) {
            .sidebar {
                position: fixed;
                z-index: 1040;
                transform: translateX(-100%);
                transition: transform .25s ease;
            }

            .sidebar.show {
                transform: translateX(0);
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <div class="app-wrapper">
        <nav id="sidebar" class="sidebar">
            <div class="brand d-flex align-items-center gap-2">
                <i class="fas fa-building"></i>
                <span>HRIS Seven Payroll</span>
            </div>
            <ul class="menu">
                <li class="menu-item">
                    <button class="menu-button" data-bs-toggle="collapse" data-bs-target="#masterData" aria-expanded="false">
                        <span><i class="menu-icon fas fa-database"></i>Master Data</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div id="masterData" class="collapse">
                        <ul class="submenu py-2 px-3">
                            <li><a href="{{ route('divisi.index') }}">Master Divisi</a></li>
                            <li><a href="{{ route('departemen.index') }}">Master Departemen</a></li>
                            <li><a href="{{ route('bagian.index') }}">Master Bagian</a></li>
                            <li><a href="{{ route('seksi.index') }}">Master Seksi</a></li>
                            <li><a href="{{ route('karyawan.index') }}">Master Karyawan</a></li>
                            <li><a href="{{ route('golongan.index') }}">Master Golongan</a></li>
                            <li><a href="{{ route('shift.index') }}">Master Shift Kerja</a></li>
                            <li><a href="{{ route('jenis-ijin.index') }}">Master Tidak Masuk</a></li>
                            <li><a href="{{ route('jenis-izin.index') }}">Master Ijin Keluar</a></li>
                            <li><a href="{{ route('jabatan.index') }}">Master Jabatan</a></li>
                            <li><a href="{{ route('hari-libur.index') }}">Master Hari Libur</a></li>
                            <li><a href="{{ route('hirarki.index') }}">Group Hierarki</a></li>
                        </ul>
                    </div>
                </li>

                <li class="menu-item">
                    <button class="menu-button" data-bs-toggle="collapse" data-bs-target="#absensi" aria-expanded="true">
                        <span><i class="menu-icon fas fa-user-check"></i>Absensi</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div id="absensi" class="collapse show">
                        <ul class="submenu py-2 px-3">
                            <li><a href="{{ route('absen.index') }}">Browse Absensi</a></li>
                            <li><a href="{{ route('tidak-masuk.index') }}">Input Tidak Masuk</a></li>
                            <li><a href="{{ route('izin-keluar.index') }}">Izin Keluar Komplek</a></li>
                            <li><a href="{{ route('instruksi-kerja-lembur.index') }}">Instruksi Kerja Lembur</a></li>
                            <li><a href="{{ route('realisasi-lembur.index') }}">Realisasi Lembur</a></li>
                            <li><a href="{{ route('saldo-cuti.index') }}">Saldo Cuti</a></li>
                            <li><a href="{{ route('absensi.statistik.index') }}">Statistik Absensi</a></li>
                            <li><a href="{{ route('tarik-data-absensi.index') }}">Tarik Data Absensi</a></li>
                        </ul>
                    </div>
                </li>

                <li class="menu-item">
                    <button class="menu-button" data-bs-toggle="collapse" data-bs-target="#gaji" aria-expanded="false">
                        <span><i class="menu-icon fas fa-money-bill-wave"></i>Gaji</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div id="gaji" class="collapse">
                        <ul class="submenu py-2 px-3">
                            <li><a href="{{ route('gapok.index') }}">Master Gaji Pokok</a></li>
                            <li><a href="{{ route('hutang-piutang.index') }}">Hutang-Piutang Karyawan</a></li>
                        </ul>
                    </div>
                </li>


                <li class="menu-item">
                    <button class="menu-button" data-bs-toggle="collapse" data-bs-target="#proses" aria-expanded="false">
                        <span><i class="menu-icon fas fa-cogs"></i>Proses</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div id="proses" class="collapse">
                        <ul class="submenu py-2 px-3">
                            <li><a href="{{ route('periode-gaji.index') }}">Periode Closing Gaji</a></li>
                            <li><a href="{{ route('closing.index') }}">Closing Gaji</a></li>
                            <li><a href="{{ route('update-closing-gaji.index') }}">Update Closing Gaji</a></li>
                            <li><a href="{{ route('view-gaji.index') }}">View Rekap Gaji</a></li>
                        </ul>
                    </div>
                </li>

                <li class="menu-item">
                    <button class="menu-button" data-bs-toggle="collapse" data-bs-target="#laporan" aria-expanded="false">
                        <span><i class="menu-icon fas fa-print"></i>Laporan</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div id="laporan" class="collapse">
                        <ul class="submenu py-2 px-3">
                            <li><a href="{{ route('slip-gaji.index') }}">Cetak Slip Gaji</a></li>
                            <li><a href="{{ route('rekap-upah-karyawan.index') }}">Rekap Upah Karyawan</a></li>
                            <li><a href="{{ route('rekap-uang-makan-transport.index') }}">Rekap Uang Makan & Transport</a></li>
                            <li><a href="{{ route('rekap-bank.index') }}">Rekap Bank</a></li>
                            <li><a href="{{ route('rekap-upah-per-bagian-dept.index') }}">Rekap Upah Per Bagian/Dept.</a></li>
                            <li><a href="{{ route('rekap-uang-makan-transport-per-bagian-dept.index') }}">Rekap TM, TU Per Bagian/Dept.</a></li>
                        </ul>
                    </div>
                </li>
            </ul>
            <div class="px-3 py-3 small-muted">v1.0 • {{ config('app.name') }}</div>
        </nav>

        <main class="content">
            <div class="d-lg-none mb-3">
                <button class="btn btn-outline-secondary" id="toggleSidebar">
                    <i class="fas fa-bars me-1"></i> Menu
                </button>
            </div>
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const toggle = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        if (toggle) {
            toggle.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });
        }
    </script>
    @stack('scripts')
    @yield('scripts')
</body>

</html>