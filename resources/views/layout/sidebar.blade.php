<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        {{-- Dashboard Section --}}
        <li class="nav-item">
            <a class="nav-link" href="/">
                <i class="mdi mdi-view-dashboard-outline menu-icon"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>

        {{-- Personal Attendance --}}
        @if (auth()->user()->role != 'admin_gaji')
            <li class="nav-item nav-category">Absensi Saya</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('attendance.history') }}">
                    <i class="mdi mdi-calendar-text-outline menu-icon"></i>
                    <span class="menu-title">Riwayat Absensi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('leave-requests.personal-history') }}">
                    <i class="mdi mdi-message-alert-outline menu-icon"></i>
                    <span class="menu-title">Riwayat Izin</span>
                </a>
            </li>
        @endif

        {{-- Financial Section --}}
        @if (auth()->user()->role != 'admin')
            <li class="nav-item nav-category">Keuangan</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('my-salary.index') }}">
                    <i class="mdi mdi-wallet-outline menu-icon"></i>
                    <span class="menu-title">Gaji Ku</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('kasbon.index') }}">
                    <i class="mdi mdi-cash-multiple menu-icon"></i>
                    <span class="menu-title">{{ auth()->user()->role == 'admin_gaji' ? 'Data Kasbon' : 'Kasbon Saya' }}</span>
                </a>
            </li>
        @endif

        {{-- Role Specific: ADMIN GAJI --}}
        @if (auth()->user()->role == 'admin_gaji')
            <li class="nav-item nav-category">Admin Gaji</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('employee-salaries.index') }}">
                    <i class="mdi mdi-database-outline menu-icon"></i>
                    <span class="menu-title">Master Gaji</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('kasbon.verification') }}">
                    <i class="mdi mdi-cash-check menu-icon"></i>
                    <span class="menu-title">Verifikasi Bayar</span>
                    @php $pendingCount = \App\Models\CashAdvanceInstallment::where('status', 'pending')->count(); @endphp
                    @if($pendingCount > 0)
                        <span class="badge badge-danger rounded-pill ms-auto">{{ $pendingCount }}</span>
                    @endif
                </a>
            </li>
        @endif

        {{-- Role Specific: ADMIN / AUDIT --}}
        @if (in_array(auth()->user()->role, ['admin', 'audit']))
            <li class="nav-item nav-category">Manajemen</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('audit.verify.list') }}">
                    <i class="mdi mdi-shield-check-outline menu-icon"></i>
                    <span class="menu-title">Verifikasi Absen</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('users.index') }}">
                    <i class="mdi mdi-account-group-outline menu-icon"></i>
                    <span class="menu-title">Data User</span>
                </a>
            </li>
        @endif

        {{-- Security Section --}}
        @if (auth()->user()->role == 'security' || auth()->user()->role == 'admin')
            <li class="nav-item nav-category">Keamanan</li>
            @if (auth()->user()->role == 'security')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('security.scan') }}">
                        <i class="mdi mdi-qrcode-scan menu-icon"></i>
                        <span class="menu-title">Pindai QR</span>
                    </a>
                </li>
            @endif
            <li class="nav-item">
                <a class="nav-link" href="{{ route('security.history') }}">
                    <i class="mdi mdi-history menu-icon"></i>
                    <span class="menu-title">Riwayat Scan</span>
                </a>
            </li>
        @endif
    </ul>
</nav>