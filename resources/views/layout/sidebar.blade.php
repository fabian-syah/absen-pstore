<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        {{-- DASHBOARD --}}
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
                <i class="mdi mdi-file-document-outline menu-icon"></i>
                <span class="menu-title">Riwayat Izin</span>
            </a>
        </li>

        {{-- MENU UMUM --}}
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
        
        {{-- KHUSUS ADMIN --}}
        @if (auth()->user()->role == 'admin')
            <li class="nav-item nav-category">Monitoring Harian</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.monitoring.daily') }}">
                    <i class="menu-icon mdi mdi-monitor-dashboard"></i>
                    <span class="menu-title">Siapa Sudah Absen?</span>
                </a>
            </li>
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
        @endif

        {{-- KHUSUS AUDIT --}}
        @if (auth()->user()->role == 'audit')
             <li class="nav-item nav-category">Manajemen</li>
             <li class="nav-item">
                <a class="nav-link" href="{{ route('users.index') }}">
                    <i class="menu-icon mdi mdi-account-group"></i>
                    <span class="menu-title">Data User (Tim)</span>
                </a>
            </li>
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
        @endif

        {{-- KHUSUS SECURITY --}}
        @if (auth()->user()->role == 'security')
            <li class="nav-item nav-category">Menu Security</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('security.scan') }}">
                    <i class="menu-icon mdi mdi-qrcode-scan"></i>
                    <span class="menu-title">Pindai Absensi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('security.history') }}">
                    <i class="menu-icon mdi mdi-history"></i>
                    <span class="menu-title">Riwayat Scan</span>
                </a>
            </li>
        @endif

        {{-- MENU TIM UNTUK USER LAIN --}}
        @if (in_array(auth()->user()->role, ['user_biasa', 'leader']))
            <li class="nav-item nav-category">Menu Tim</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('team.index') }}">
                    <i class="menu-icon mdi mdi-account-multiple-outline"></i>
                    <span class="menu-title">Tim Saya</span>
                </a>
            </li>
        @endif
    </ul>
</nav>  