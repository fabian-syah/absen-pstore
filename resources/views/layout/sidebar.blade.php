<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        {{-- Tombol Dashboard (Semua Role) --}}
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
        {{--     MENU UNTUK SEMUA ROLE        --}}
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
        {{--     MENU UNTUK SUPER ADMIN        --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'admin' || auth()->user()->role == 'audit')
            <li class="nav-item nav-category">Menu Cabang</li>
            {{-- MENU BARU: MASTER DATA ABSENSI --}}
            {{-- <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.attendance.all') }}">
                    <i class="menu-icon mdi mdi-database-search"></i>
                    <span class="menu-title">Semua Data Absensi</span>
                </a>
            </li> --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('branches.index') }}">
                    <i class="menu-icon mdi mdi-domain"></i>
                    <span class="menu-title">Data Cabang</span>
                </a>
            </li>
        @endif

        {{-- =================================== --}}
        {{--   MENU UNTUK SUPER ADMIN & AUDIT   --}}
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
        {{--   MENU UNTUK SUPER ADMIN & AUDIT   --}}
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
        {{--   MENU MANAGEMENT JAM KERJA BARU  --}}
        {{-- =================================== --}}
        {{-- @if (auth()->user()->role == 'admin' || auth()->user()->role == 'audit' || auth()->user()->role == 'leader')
            <li class="nav-item nav-category">Management Jam Kerja</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('work-schedules.index') }}">
                    <i class="menu-icon mdi mdi-clock-outline"></i>
                    <span class="menu-title">Jam Kerja</span>
                </a>
            </li>
        @endif --}}

        {{-- =================================== --}}
        {{--   MENU KHUSUS VERIFIKASI (Super Admin & Audit) --}}
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
            {{-- [BARU] Menu Approval Ganti Foto --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('users.photo-requests') }}">
                    <i class="menu-icon mdi mdi-camera-retake-outline"></i>
                    <span class="menu-title">Permintaan Ganti Foto</span>
                    {{-- Badge Notifikasi Jumlah Request (Opsional - Perlu Logic di View Composer) --}}
                    {{-- <span class="badge badge-warning ms-2">New</span> --}}
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('users.ktp-requests') }}">
                    <i class="menu-icon mdi mdi-card-account-details-outline"></i>
                    <span class="menu-title">Req. Ganti KTP</span>

                    {{-- Optional: Badge hitung jumlah pending (perlu View Composer) --}}
                    @php
                        $ktpPendingCount = \App\Models\User::where('ktp_request_status', 'pending')->count();
                    @endphp
                    @if ($ktpPendingCount > 0)
                        <span class="badge badge-danger ms-2">{{ $ktpPendingCount }}</span>
                    @endif
                </a>
            </li>

            <li class="nav-item">
    <a class="nav-link" href="#">
        <i class="menu-icon mdi mdi-package-variant-minus"></i>
        <span class="menu-title">Pengembalian Inventaris</span>
    </a>
</li>

        @endif

        {{-- =================================== --}}
        {{--     MENU UNTUK SECURITY         --}}
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
        {{--     MENU UNTUK LEADER, user_biasa, & AUDIT --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'user_biasa' || auth()->user()->role == 'leader' || auth()->user()->role == 'audit')
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

        @if (auth()->user()->role == 'audit' || auth()->user()->role == 'leader' || auth()->user()->role == 'admin')
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="menu-icon mdi mdi-package-variant"></i>
                    <span class="menu-title">Inventaris Cabang</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="menu-icon mdi mdi-target"></i>
                    <span class="menu-title">Target Cabang</span>
                </a>
            </li>
        @endif
    </ul>
</nav>
