<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        {{-- =================================== --}}
        {{--       DASHBOARD & RIWAYAT           --}}
        {{-- =================================== --}}
        <li class="nav-item">
            <a class="nav-link" href="/">
                <i class="mdi mdi-grid-large menu-icon"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('attendance.history') }}">
                <i class="mdi mdi-history menu-icon"></i>
                <span class="menu-title">Riwayat Absensi</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('leave-requests.personal-history') }}">
                <i class="mdi mdi-history menu-icon"></i>
                <span class="menu-title">Riwayat Izin</span>
            </a>
        </li>

        {{-- =================================== --}}
        {{--    MENU UMUM (SEMUA ROLE)           --}}
        {{-- =================================== --}}
        <li class="nav-item nav-category">Menu Umum</li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('inventory.index') }}">
                <i class="menu-icon mdi mdi-package-variant"></i>
                <span class="menu-title">Inventaris</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('job-targets.index') }}">
                <i class="menu-icon mdi mdi-clipboard-list"></i>
                <span class="menu-title">Job Desk / Target</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">
                <i class="menu-icon mdi mdi-history"></i>
                <span class="menu-title">Riwayat Divisi / Cabang</span>
            </a>
        </li>

        {{-- =================================== --}}
        {{--    MONITORING HARIAN (ADMIN ONLY)   --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'admin')
            <li class="nav-item nav-category">Monitoring Harian</li>
            
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.monitoring.daily') }}">
                    <i class="menu-icon mdi mdi-monitor-dashboard"></i>
                    <span class="menu-title">Siapa Sudah Absen?</span>
                </a>
            </li>
        @endif

        {{-- =================================== --}}
        {{--    MENU KHUSUS SUPER ADMIN          --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'admin' || auth()->user()->role == 'audit')
            <li class="nav-item nav-category">Menu Cabang</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('branches.index') }}">
                    <i class="menu-icon mdi mdi-domain"></i>
                    <span class="menu-title">Data Cabang</span>
                </a>
            </li>
        @endif

        {{-- =================================== --}}
        {{--    MANAJEMEN TIM (ADMIN ONLY)       --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'admin')
            <li class="nav-item nav-category">Manajemen Tim</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('divisions.index') }}">
                    <i class="menu-icon mdi mdi-sitemap"></i>
                    <span class="menu-title">Data Divisi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('users.index') }}">
                    <i class="menu-icon mdi mdi-account-group"></i>
                    <span class="menu-title">Data User</span>
                </a>
            </li>
        @endif

        {{-- =================================== --}}
        {{--    MANAJEMEN TIM (AUDIT ONLY)       --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'audit')
            <li class="nav-item nav-category">Manajemen Tim</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('users.index') }}">
                    <i class="menu-icon mdi mdi-account-group"></i>
                    <span class="menu-title">Data User</span>
                </a>
            </li>
        @endif

        {{-- =================================== --}}
        {{--     VERIFIKASI (ADMIN & AUDIT)      --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'audit' || auth()->user()->role == 'admin')
            <li class="nav-item nav-category">Verifikasi</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('audit.verify.list') }}">
                    <i class="menu-icon mdi mdi-checkbox-marked-outline"></i>
                    <span class="menu-title">Verifikasi Absensi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('leave-requests.index') }}">
                    <i class="menu-icon mdi mdi-clock-alert-outline"></i>
                    <span class="menu-title">Daftar Izin / Telat</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('users.photo-requests') }}">
                    <i class="menu-icon mdi mdi-camera-retake-outline"></i>
                    <span class="menu-title">Permintaan Ganti Foto</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('users.ktp-requests') }}">
                    <i class="menu-icon mdi mdi-card-account-details-outline"></i>
                    <span class="menu-title">Req. Ganti KTP</span>
                    @php
                        $ktpPendingCount = \App\Models\User::where('ktp_request_status', 'pending')->count();
                    @endphp
                    @if ($ktpPendingCount > 0)
                        <span class="badge badge-danger ms-2">{{ $ktpPendingCount }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('inventory-returns.index') }}">
                    <i class="menu-icon mdi mdi-package-variant-minus"></i>
                    <span class="menu-title">History Pengembalian</span>
                </a>
            </li>
        @endif

        {{-- =================================== --}}
        {{--         MENU SECURITY               --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'security')
            <li class="nav-item nav-category">Menu Security</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('security.scan') }}">
                    <i class="menu-icon mdi mdi-qrcode-scan"></i>
                    <span class="menu-title">Pindai Absensi</span>
                </a>
            </li>
        @endif

        {{-- =================================== --}}
        {{--    MENU TIM (USER, LEADER, AUDIT)   --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'user_biasa' || auth()->user()->role == 'leader' || auth()->user()->role == 'audit' || auth()->user()->role == 'security')
            <li class="nav-item nav-category">Menu Pengguna</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('team.index') }}">
                    <i class="menu-icon mdi mdi-account-multiple-outline"></i>
                    <span class="menu-title">Tim Saya</span>
                </a>
            </li>

            @if (auth()->user()->role == 'audit')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('team.my-branches') }}">
                        <i class="menu-icon mdi mdi-office-building-marker"></i>
                        <span class="menu-title">Cabang Saya</span>
                    </a>
                </li>
            @endif
        @endif

        {{-- =================================== --}}
        {{--    MONITORING (ADMIN, AUDIT, LEADER)--}}
        {{-- =================================== --}}
        @if (in_array(auth()->user()->role, ['admin', 'audit', 'leader']))
            
            <li class="nav-item nav-category">Monitoring Wilayah</li>

            {{-- MENU INVENTARIS CABANG --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('inventory.branches') }}">
                    <i class="menu-icon mdi mdi-package-variant-closed"></i>
                    <span class="menu-title">Inventaris Cabang</span>
                </a>
            </li>

            {{-- MENU TARGET CABANG --}}
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="menu-icon mdi mdi-target"></i>
                    <span class="menu-title">Target Cabang</span>
                </a>
            </li>
        @endif

    </ul>
</nav>