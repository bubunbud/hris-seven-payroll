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
        /* Override primary color */
        :root {
            --bs-primary: #2596be;
            --bs-primary-rgb: 37, 150, 190;
        }

        .btn-primary,
        .btn-primary:hover,
        .btn-primary:focus,
        .btn-primary:active,
        .btn-primary:disabled,
        .bg-primary {
            background-color: #2596be !important;
            border-color: #2596be !important;
        }

        .text-primary {
            color: #2596be !important;
        }

        a {
            color: #2596be;
        }

        a:hover {
            color: #1f7da1;
        }

        body {
            background-color: #f6f8fb;
        }

        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: #1f2937;
            color: #fff;
            overflow-y: auto;
        }

        .sidebar .brand {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            background: #fff;
            color: #111827;
        }

        .sidebar .brand img {
            max-height: 58px;
            /* diperbesar ~20% dari ukuran sebelumnya */
            max-width: 264px;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 6px;
            /* sudut sedikit membulat */
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
                <img src="{{ asset('images/logo.png') }}" alt="Logo">
            </div>

            @auth
            @php
            $sidebarUser = auth()->user()->loadMissing('roles');
            $primaryRole = $sidebarUser->roles->first();
            @endphp
            <div class="px-3 py-3 border-bottom border-secondary" style="border-color: rgba(255, 255, 255, 0.1) !important;">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-user text-white"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="text-white fw-semibold" style="font-size: 14px;">{{ $sidebarUser->name }}</div>
                        <div class="text-muted" style="font-size: 12px;">
                            @if($primaryRole)
                            <span class="badge bg-success">{{ $primaryRole->name }}</span>
                            @else
                            <span class="badge bg-success">{{ ucfirst($sidebarUser->role) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-light w-100" style="font-size: 12px;">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </button>
                </form>
            </div>
            @endauth
            <ul class="menu">
                @php
                // Check apakah user punya permission untuk Dashboard Group
                $hasDashboardGroupMenu = $sidebarUser->hasPermission('view-dashboard-group');
                // Check apakah user punya permission untuk Dashboard BU
                $hasDashboardBUMenu = $sidebarUser->hasPermission('view-dashboard-bu');
                // Check apakah user punya permission untuk Dashboard Employee
                $hasDashboardEmployeeMenu = $sidebarUser->hasPermission('view-dashboard-employee');
                @endphp
                @if($hasDashboardGroupMenu)
                <li class="menu-item">
                    <a href="{{ route('dashboard.group') }}" class="menu-button">
                        <span><i class="menu-icon fas fa-building"></i>Dashboard Group</span>
                    </a>
                </li>
                @endif
                @if($hasDashboardBUMenu)
                <li class="menu-item">
                    <a href="{{ route('dashboard.bu') }}" class="menu-button">
                        <span><i class="menu-icon fas fa-sitemap"></i>Dashboard BU</span>
                    </a>
                </li>
                @endif
                @if($hasDashboardEmployeeMenu)
                <li class="menu-item">
                    <a href="{{ route('dashboard.employee') }}" class="menu-button">
                        <span><i class="menu-icon fas fa-user"></i>Dashboard Karyawan</span>
                    </a>
                </li>
                @endif

                @php
                // Check apakah user punya permission untuk menu Master Data (group atau granular)
                $hasMasterDataMenu = $sidebarUser->hasMenuPermission('view-master-data', [
                'view-master-divisi',
                'view-master-departemen',
                'view-master-bagian',
                'view-master-seksi',
                'view-master-karyawan',
                'view-master-golongan',
                'view-master-shift',
                'view-master-tidak-masuk',
                'view-master-ijin-keluar',
                'view-master-jabatan',
                'view-master-hari-libur',
                'view-hirarki'
                ]);
                @endphp
                @if($hasMasterDataMenu)
                <li class="menu-item">
                    <button class="menu-button" data-bs-toggle="collapse" data-bs-target="#masterData" aria-expanded="false">
                        <span><i class="menu-icon fas fa-database"></i>Master Data</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div id="masterData" class="collapse">
                        <ul class="submenu py-2 px-3">
                            @if($sidebarUser->hasPermission('view-master-data') || $sidebarUser->hasPermission('view-master-karyawan'))
                            <li><a href="{{ route('karyawan.index') }}">Master Karyawan</a></li>
                            <li><a href="{{ route('list-karyawan-aktif.index') }}">List Data Karyawan Aktif</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-master-data') || $sidebarUser->hasPermission('view-master-divisi'))
                            <li><a href="{{ route('divisi.index') }}">Master Divisi</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-master-data') || $sidebarUser->hasPermission('view-master-departemen'))
                            <li><a href="{{ route('departemen.index') }}">Master Departemen</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-master-data') || $sidebarUser->hasPermission('view-master-bagian'))
                            <li><a href="{{ route('bagian.index') }}">Master Bagian</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-master-data') || $sidebarUser->hasPermission('view-master-seksi'))
                            <li><a href="{{ route('seksi.index') }}">Master Seksi</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-master-data') || $sidebarUser->hasPermission('view-master-golongan'))
                            <li><a href="{{ route('golongan.index') }}">Master Golongan</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-master-data') || $sidebarUser->hasPermission('view-master-shift'))
                            <li><a href="{{ route('shift.index') }}">Master Shift Kerja</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-master-data') || $sidebarUser->hasPermission('view-master-tidak-masuk'))
                            <li><a href="{{ route('jenis-ijin.index') }}">Master Tidak Masuk</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-master-data') || $sidebarUser->hasPermission('view-master-ijin-keluar'))
                            <li><a href="{{ route('jenis-izin.index') }}">Master Ijin Keluar</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-master-data') || $sidebarUser->hasPermission('view-master-jabatan'))
                            <li><a href="{{ route('jabatan.index') }}">Master Jabatan</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-master-data') || $sidebarUser->hasPermission('view-master-hari-libur'))
                            <li><a href="{{ route('hari-libur.index') }}">Master Hari Libur</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-master-data') || $sidebarUser->hasPermission('view-hirarki'))
                            <li><a href="{{ route('hirarki.index') }}">Group Hierarki</a></li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                @php
                // Check apakah user punya permission untuk menu Absensi (group atau granular)
                $hasAbsensiMenu = $sidebarUser->hasMenuPermission('view-absensi', [
                'view-browse-absensi',
                'view-browse-tidak-absen',
                'view-jadwal-shift-satpam',
                'view-override-jadwal',
                'view-tidak-masuk',
                'view-izin-keluar',
                'view-instruksi-kerja-lembur',
                'view-statistik-absensi',
                'view-saldo-cuti',
                'view-tukar-hari-kerja',
                'view-edit-absensi'
                ]);
                @endphp
                @if($hasAbsensiMenu)
                <li class="menu-item">
                    <button class="menu-button" data-bs-toggle="collapse" data-bs-target="#absensi" aria-expanded="false">
                        <span><i class="menu-icon fas fa-user-check"></i>Absensi</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div id="absensi" class="collapse">
                        <ul class="submenu py-2 px-3">
                            @if($sidebarUser->hasPermission('view-absensi') || $sidebarUser->hasPermission('view-browse-absensi'))
                            <li><a href="{{ route('absen.index') }}">Browse Absensi</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-absensi') || $sidebarUser->hasPermission('view-edit-absensi'))
                            <li><a href="{{ route('edit-absensi.index') }}">Input/Edit Absensi</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-absensi') || $sidebarUser->hasPermission('view-browse-tidak-absen'))
                            <li><a href="{{ route('browse-tidak-absen.index') }}">Browse Tidak Absen</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-absensi') || $sidebarUser->hasPermission('view-jadwal-shift-satpam'))
                            <li><a href="{{ route('jadwal-shift-security.index') }}">Jadwal Shift Satpam</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-absensi') || $sidebarUser->hasPermission('view-override-jadwal'))
                            <li><a href="{{ route('override-jadwal-security.index') }}">List Override Jadwal</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-absensi') || $sidebarUser->hasPermission('view-tidak-masuk'))
                            <li><a href="{{ route('tidak-masuk.index') }}">Izin Tidak Masuk</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-absensi') || $sidebarUser->hasPermission('view-izin-keluar'))
                            <li><a href="{{ route('izin-keluar.index') }}">Izin Keluar Komplek</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-absensi') || $sidebarUser->hasPermission('view-instruksi-kerja-lembur'))
                            <li><a href="{{ route('instruksi-kerja-lembur.index') }}">Instruksi Kerja Lembur</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-absensi') || $sidebarUser->hasPermission('view-statistik-absensi'))
                            <li><a href="{{ route('absensi.statistik.index') }}">Statistik Absensi</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-absensi') || $sidebarUser->hasPermission('view-saldo-cuti'))
                            <li><a href="{{ route('saldo-cuti.index') }}">Saldo Cuti</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-absensi') || $sidebarUser->hasPermission('view-tukar-hari-kerja'))
                            <li><a href="{{ route('tukar-hari-kerja.index') }}">Tukar Hari Kerja</a></li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                @php
                // Check apakah user punya permission untuk menu Proses Payroll (group atau granular)
                $hasProsesGajiMenu = $sidebarUser->hasMenuPermission('view-proses-gaji', [
                'view-master-gaji-pokok',
                'view-hutang-piutang',
                'view-realisasi-lembur',
                'view-periode-gaji',
                'view-closing-gaji',
                'view-update-closing-gaji',
                'view-rekap-gaji'
                ]);
                @endphp
                @if($hasProsesGajiMenu)
                <li class="menu-item">
                    <button class="menu-button" data-bs-toggle="collapse" data-bs-target="#proses" aria-expanded="false">
                        <span><i class="menu-icon fas fa-cogs"></i>Proses Payroll</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div id="proses" class="collapse">
                        <ul class="submenu py-2 px-3">
                            @if($sidebarUser->hasPermission('view-proses-gaji') || $sidebarUser->hasPermission('view-master-gaji-pokok'))
                            <li><a href="{{ route('gapok.index') }}">Master Gaji Pokok</a></li>
                            @endif
                            @if(($sidebarUser->hasPermission('view-proses-gaji') || $sidebarUser->hasPermission('view-hutang-piutang')) || ($sidebarUser->hasPermission('view-absensi') || $sidebarUser->hasPermission('view-hutang-piutang')))
                            <li><a href="{{ route('hutang-piutang.index') }}">Hutang-Piutang Karyawan</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-proses-gaji') || $sidebarUser->hasPermission('view-realisasi-lembur'))
                            <li><a href="{{ route('realisasi-lembur.index') }}">Realisasi Lembur</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-proses-gaji') || $sidebarUser->hasPermission('view-periode-gaji'))
                            <li><a href="{{ route('periode-gaji.index') }}">Periode Closing Gaji</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-proses-gaji') || $sidebarUser->hasPermission('view-closing-gaji'))
                            <li><a href="{{ route('closing.index') }}">Closing Gaji</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-proses-gaji') || $sidebarUser->hasPermission('view-update-closing-gaji'))
                            <li><a href="{{ route('update-closing-gaji.index') }}">Update Closing Gaji</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-proses-gaji') || $sidebarUser->hasPermission('view-rekap-gaji'))
                            <li><a href="{{ route('view-gaji.index') }}">View Rekap Gaji</a></li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                @php
                // Check apakah user punya permission untuk menu Laporan (group atau granular)
                $hasLaporanMenu = $sidebarUser->hasMenuPermission('view-laporan', [
                'view-slip-gaji',
                'view-rekap-upah-karyawan',
                'view-rekap-uang-makan-transport',
                'view-rekap-bank',
                'view-rekap-upah-per-bagian',
                'view-rekap-tm-tu-per-bagian',
                'view-report-jadwal-shift',
                'view-rekapitulasi-absensi',
                'view-rekapitulasi-absen-all',
                'view-rekapitulasi-cuti'
                ]);
                @endphp
                @if($hasLaporanMenu)
                <li class="menu-item">
                    <button class="menu-button" data-bs-toggle="collapse" data-bs-target="#laporan" aria-expanded="false">
                        <span><i class="menu-icon fas fa-print"></i>Laporan</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div id="laporan" class="collapse">
                        <ul class="submenu py-2 px-3">
                            @if($sidebarUser->hasPermission('view-laporan') || $sidebarUser->hasPermission('view-slip-gaji'))
                            <li><a href="{{ route('slip-gaji.index') }}">Cetak Slip Gaji</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-laporan') || $sidebarUser->hasPermission('view-rekap-upah-karyawan'))
                            <li><a href="{{ route('rekap-upah-karyawan.index') }}">Rekap Upah Karyawan</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-laporan') || $sidebarUser->hasPermission('view-rekap-uang-makan-transport'))
                            <li><a href="{{ route('rekap-uang-makan-transport.index') }}">Rekap Uang Makan & Transport</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-laporan') || $sidebarUser->hasPermission('view-rekap-bank'))
                            <li><a href="{{ route('rekap-bank.index') }}">Rekap Bank</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-laporan') || $sidebarUser->hasPermission('view-rekap-upah-per-bagian'))
                            <li><a href="{{ route('rekap-upah-per-bagian-dept.index') }}">Rekap Upah Per Bagian/Dept.</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-laporan') || $sidebarUser->hasPermission('view-rekap-tm-tu-per-bagian'))
                            <li><a href="{{ route('rekap-uang-makan-transport-per-bagian-dept.index') }}">Rekap TM, TU Per Bagian/Dept.</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-laporan') || $sidebarUser->hasPermission('view-rekap-upah-karyawan'))
                            <li><a href="{{ route('rekap-upah-finance-ver.index') }}">Rekap Upah Finance Ver</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-laporan') || $sidebarUser->hasPermission('view-report-jadwal-shift'))
                            <li><a href="{{ route('jadwal-shift-security.report') }}">Report Jadwal Shift</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-laporan') || $sidebarUser->hasPermission('view-rekapitulasi-absensi'))
                            <li><a href="{{ route('rekapitulasi-absensi.index') }}">Rekapitulasi Absensi</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-laporan') || $sidebarUser->hasPermission('view-rekapitulasi-absen-all'))
                            <li><a href="{{ route('rekapitulasi-absen-all.index') }}">Rekapitulasi Absen All</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-laporan') || $sidebarUser->hasPermission('view-rekapitulasi-cuti'))
                            <li><a href="{{ route('rekapitulasi-cuti.index') }}">Rekapitulasi Cuti</a></li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                @php
                // Check apakah user punya permission untuk menu Settings (group atau granular)
                $hasSettingsMenu = $sidebarUser->hasMenuPermission('view-settings', [
                'view-tarik-data-absensi',
                'view-tarik-data-izin',
                'view-tarik-data-tidak-masuk',
                'view-tarik-data-hutang-piutang',
                'view-logs',
                'manage-users',
                'manage-roles',
                'manage-permissions'
                ]);
                @endphp
                @if($hasSettingsMenu)
                <li class="menu-item">
                    <button class="menu-button" data-bs-toggle="collapse" data-bs-target="#settings" aria-expanded="false">
                        <span><i class="menu-icon fas fa-cog"></i>Settings</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div id="settings" class="collapse">
                        <ul class="submenu py-2 px-3">
                            @if($sidebarUser->hasPermission('view-settings') || $sidebarUser->hasPermission('view-tarik-data-absensi'))
                            <li><a href="{{ route('tarik-data-absensi.index') }}">Tarik Data Absensi</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-settings') || $sidebarUser->hasPermission('view-tarik-data-izin'))
                            <li><a href="{{ route('tarik-data-izin.index') }}">Tarik Data Izin</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-settings') || $sidebarUser->hasPermission('view-tarik-data-tidak-masuk'))
                            <li><a href="{{ route('tarik-data-tidak-masuk.index') }}">Tarik Data Tidak Masuk</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-settings') || $sidebarUser->hasPermission('view-tarik-data-hutang-piutang'))
                            <li><a href="{{ route('tarik-data-hutang-piutang.index') }}">Tarik Data Hutang Piutang</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('view-logs'))
                            <li><a href="{{ route('logs.index') }}">Activity Logs</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('manage-users'))
                            <li><a href="{{ route('users.index') }}">Pengelolaan User</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('manage-roles'))
                            <li><a href="{{ route('roles.index') }}">Pengelolaan Role</a></li>
                            @endif
                            @if($sidebarUser->hasPermission('manage-permissions'))
                            <li><a href="{{ route('permissions.index') }}">Pengelolaan Permission</a></li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif
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