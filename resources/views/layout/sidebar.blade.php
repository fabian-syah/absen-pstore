<nav class="sidebar sidebar-offcanvas" id="sidebar" style="background: #ffffff; border-right: 1px solid #f0f0f0; box-shadow: 10px 0 30px rgba(0,0,0,0.02);">
    <ul class="nav">
        {{-- =================================== --}}
        {{--       DASHBOARD (SEMUA ROLE)        --}}
        {{-- =================================== --}}
        <li class="nav-item">
            <a class="nav-link" href="/" style="border-radius: 12px; margin: 5px 15px; transition: all 0.3s;">
                <i class="mdi mdi-grid-large menu-icon" style="color: #4B49AC;"></i>
                <span class="menu-title" style="font-weight: 600;">Dashboard</span>
            </a>
        </li>

        {{-- =================================== --}}
        {{--   RIWAYAT ABSENSI (EXCEPT GAJI)     --}}
        {{-- =================================== --}}
        @if (auth()->user()->role != 'admin_gaji')
            <li class="nav-item nav-category" style="margin-left: 25px; color: #adb5bd; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px;">Aktivitas Saya</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('attendance.history') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="mdi mdi-history menu-icon" style="color: #57B657;"></i>
                    <span class="menu-title">Riwayat Absensi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('attendance.summary') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="mdi mdi-text-box-multiple-outline menu-icon" style="color: #FFC107;"></i>
                    <span class="menu-title">Ringkasan Tahunan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('leave-requests.personal-history') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="mdi mdi-calendar-check menu-icon" style="color: #FF4747;"></i>
                    <span class="menu-title">Riwayat Izin</span>
                </a>
            </li>
        @endif

        {{-- =================================== --}}
        {{--    MENU UMUM (EXCEPT ADMIN GAJI)    --}}
        {{-- =================================== --}}
        @if (auth()->user()->role != 'admin_gaji')
            <li class="nav-item nav-category" style="margin-left: 25px; color: #adb5bd; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px;">Menu Umum</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('inventory.index') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="menu-icon mdi mdi-package-variant" style="color: #248AFD;"></i>
                    <span class="menu-title">Inventaris</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('job-targets.index') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="menu-icon mdi mdi-clipboard-list" style="color: #8E44AD;"></i>
                    <span class="menu-title">Job Desk / Target</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('employment-history.index') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="menu-icon mdi mdi-history" style="color: #1ABC9C;"></i>
                    <span class="menu-title">Riwayat Divisi / Cabang</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('violations.index') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="menu-icon mdi mdi-alert-circle-outline" style="color: #E74C3C;"></i>
                    <span class="menu-title">Riwayat Pelanggaran</span>
                </a>
            </li>
        @endif

        {{-- =================================== --}}
        {{--         GAJI KU (SEMUA ROLE)         --}}
        {{-- =================================== --}}
        @if (auth()->user()->role != 'admin')
            <li class="nav-item nav-category" style="margin-left: 25px; color: #adb5bd; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px;">Keuangan</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('my-salary.index') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="menu-icon mdi mdi-wallet-outline" style="color: #2ECC71;"></i>
                    <span class="menu-title">Gaji Ku</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('salary-summary.index') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="menu-icon mdi mdi-file-chart-outline" style="color: #3498DB;"></i>
                    <span class="menu-title">Ringkasan Gaji</span>
                </a>
            </li>
        @endif

        {{-- =================================== --}}
        {{--             MENU KASBON              --}}
        {{-- =================================== --}}
        @if (auth()->user()->role != 'admin')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('kasbon.index') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="menu-icon mdi mdi-cash-multiple" style="color: #F39C12;"></i>
                    <span class="menu-title">
                        {{ in_array(auth()->user()->role, ['admin_gaji']) ? 'Data Kasbon' : 'Kasbon Saya' }}
                    </span>
                </a>
            </li>
        @elseif (auth()->user()->role == 'admin_gaji')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('kasbon.index') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="menu-icon mdi mdi-cash-multiple" style="color: #F39C12;"></i>
                    <span class="menu-title">Data Kasbon</span>
                </a>
            </li>
        @endif

        @if(auth()->user()->role === 'admin_gaji')
            @php
                $pendingCount = \App\Models\CashAdvanceInstallment::where('status', 'pending')->count();
            @endphp
            <li class="nav-item">
                <a class="nav-link" href="{{ route('kasbon.verification') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="menu-icon mdi mdi-cash-check" style="color: #E67E22;"></i>
                    <span class="menu-title">Verifikasi Bayar</span>
                    @if($pendingCount > 0)
                        <span class="badge badge-danger rounded-pill ms-auto" style="font-size: 10px; padding: 4px 8px;">{{ $pendingCount }}</span>
                    @endif
                </a>
            </li>
        @endif

        {{-- =================================== --}}
        {{--    MANAJEMEN GAJI (ADMIN & GAJI)    --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'admin_gaji')
            <li class="nav-item nav-category" style="margin-left: 25px; color: #adb5bd; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px;">Admin Gaji</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('employee-salaries.index') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="menu-icon mdi mdi-bank-outline" style="color: #7F8C8D;"></i>
                    <span class="menu-title">Master Gaji User</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('branch-salary.index') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="menu-icon mdi mdi-cash-register" style="color: #34495E;"></i>
                    <span class="menu-title">Penggajian Cabang</span>
                </a>
            </li>
        @endif

        {{-- =================================== --}}
        {{--      MENU KHUSUS SUPER ADMIN        --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'admin' || auth()->user()->role == 'audit')
            <li class="nav-item nav-category" style="margin-left: 25px; color: #adb5bd; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px;">Menu Cabang</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('branches.index') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="menu-icon mdi mdi-domain" style="color: #3498DB;"></i>
                    <span class="menu-title">Data Cabang</span>
                </a>
            </li>
        @endif

        @if (auth()->user()->role == 'admin')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.correction.index') }}" style="border-radius: 12px; margin: 2px 15px; background: rgba(231, 76, 60, 0.05);">
                    <i class="menu-icon mdi mdi-eraser" style="color: #E74C3C;"></i>
                    <span class="menu-title">Koreksi Absensi</span>
                    <span class="badge badge-danger ms-2" style="font-size: 0.5rem; letter-spacing: 1px;">CORE</span>
                </a>
            </li>
        @endif

        {{-- =================================== --}}
        {{--      MANAJEMEN TIM (ADMIN/AUDIT)    --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'admin')
            <li class="nav-item nav-category" style="margin-left: 25px; color: #adb5bd; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px;">Manajemen Tim</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('divisions.index') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="menu-icon mdi mdi-sitemap" style="color: #9B59B6;"></i>
                    <span class="menu-title">Data Divisi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('users.index') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="menu-icon mdi mdi-account-group" style="color: #34495E;"></i>
                    <span class="menu-title">Data User</span>
                </a>
            </li>
        @endif

        @if (auth()->user()->role == 'audit')
            <li class="nav-item nav-category" style="margin-left: 25px; color: #adb5bd; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px;">Manajemen Tim</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('users.index') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="menu-icon mdi mdi-account-group" style="color: #34495E;"></i>
                    <span class="menu-title">Data User</span>
                </a>
            </li>
        @endif

        {{-- =================================== --}}
        {{--      VERIFIKASI (ADMIN & AUDIT)      --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'audit' || auth()->user()->role == 'admin')
            <li class="nav-item nav-category" style="margin-left: 25px; color: #adb5bd; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px;">Verifikasi</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('audit.verify.list') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="menu-icon mdi mdi-checkbox-marked-outline" style="color: #16A085;"></i>
                    <span class="menu-title">Verifikasi Absensi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('leave-requests.index') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="menu-icon mdi mdi-clock-alert-outline" style="color: #D35400;"></i>
                    <span class="menu-title">Daftar Izin / Telat</span>
                </a>
            </li>

            @if (auth()->user()->role == 'admin')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('users.photo-requests') }}" style="border-radius: 12px; margin: 2px 15px;">
                        <i class="menu-icon mdi mdi-camera-retake-outline" style="color: #2980B9;"></i>
                        <span class="menu-title">Permintaan Foto</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('users.ktp-requests') }}" style="border-radius: 12px; margin: 2px 15px;">
                        <i class="menu-icon mdi mdi-card-account-details-outline" style="color: #2C3E50;"></i>
                        <span class="menu-title">Req. Ganti KTP</span>
                        @php $ktpPendingCount = \App\Models\User::where('ktp_request_status', 'pending')->count(); @endphp
                        @if ($ktpPendingCount > 0)
                            <span class="badge badge-danger ms-2" style="font-size: 9px;">{{ $ktpPendingCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('inventory-returns.index') }}" style="border-radius: 12px; margin: 2px 15px;">
                        <i class="menu-icon mdi mdi-package-variant-minus" style="color: #7F8C8D;"></i>
                        <span class="menu-title">History Kembali</span>
                    </a>
                </li>
            @endif
        @endif

        {{-- =================================== --}}
        {{--           MENU SECURITY              --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'security' || auth()->user()->role == 'admin')
            <li class="nav-item nav-category" style="margin-left: 25px; color: #adb5bd; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px;">Security</li>
            @if (auth()->user()->role == 'security')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('security.scan') }}" style="border-radius: 12px; margin: 2px 15px; background: rgba(75, 73, 172, 0.1);">
                        <i class="menu-icon mdi mdi-qrcode-scan" style="color: #4B49AC;"></i>
                        <span class="menu-title" style="font-weight: 700;">Pindai Absensi</span>
                    </a>
                </li>
            @endif
            <li class="nav-item">
                <a class="nav-link" href="{{ route('security.history') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="menu-icon mdi mdi-history" style="color: #95A5A6;"></i>
                    <span class="menu-title">Riwayat Scan</span>
                </a>
            </li>
        @endif

        {{-- =================================== --}}
        {{--      MENU PENGGUNA (TEAM/BRANCH)    --}}
        {{-- =================================== --}}
        @if (in_array(auth()->user()->role, ['user_biasa', 'leader', 'audit', 'security', 'admin']))
            <li class="nav-item nav-category" style="margin-left: 25px; color: #adb5bd; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px;">Wilayah & Tim</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('team.index') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="menu-icon mdi mdi-account-multiple-outline" style="color: #5D6D7E;"></i>
                    <span class="menu-title">Tim Saya</span>
                </a>
            </li>
            @if (in_array(auth()->user()->role, ['audit', 'leader', 'admin']))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('team.my-branches') }}" style="border-radius: 12px; margin: 2px 15px;">
                        <i class="menu-icon mdi mdi-office-building-marker" style="color: #E67E22;"></i>
                        <span class="menu-title">Cabang Saya</span>
                    </a>
                </li>
            @endif
        @endif

        {{-- =================================== --}}
        {{--    MONITORING (ADMIN, AUDIT, LEADER) --}}
        {{-- =================================== --}}
        @if (in_array(auth()->user()->role, ['admin', 'audit', 'leader']))
            <li class="nav-item nav-category" style="margin-left: 25px; color: #adb5bd; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px;">Monitoring</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('branch-leaderboard.index') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="menu-icon mdi mdi-trophy-award" style="color: #F1C40F;"></i>
                    <span class="menu-title">Leaderboard Cabang</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('inventory.branches') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="menu-icon mdi mdi-package-variant-closed" style="color: #95A5A6;"></i>
                    <span class="menu-title">Inventaris Cabang</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('branch-targets.index') }}" style="border-radius: 12px; margin: 2px 15px;">
                    <i class="menu-icon mdi mdi-target" style="color: #C0392B;"></i>
                    <span class="menu-title">Target Cabang</span>
                </a>
            </li>
        @endif
    </ul>
</nav>

<style>
    .sidebar .nav .nav-item .nav-link:hover {
        background: rgba(75, 73, 172, 0.05) !important;
        transform: translateX(5px);
    }
    .sidebar .nav .nav-item.active .nav-link {
        background: #4B49AC !important;
        box-shadow: 0 4px 15px rgba(75, 73, 172, 0.3);
    }
    .sidebar .nav .nav-item.active .nav-link i, 
    .sidebar .nav .nav-item.active .nav-link .menu-title {
        color: #ffffff !important;
    }
</style>