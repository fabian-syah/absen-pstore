{{-- Modern Sidebar with Glassmorphism & Smooth Animations --}}
<style>
    /* Modern Sidebar Styling */
    .sidebar {
        background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%) !important;
        box-shadow: 2px 0 20px rgba(0, 0, 0, 0.05) !important;
        border-right: 1px solid rgba(13, 110, 253, 0.1) !important;
    }

    .sidebar .nav {
        padding: 0.5rem 0;
    }

    /* Menu Category Headers */
    .sidebar .nav-category {
        color: #6c757d !important;
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.5px !important;
        text-transform: uppercase !important;
        padding: 1.5rem 1.5rem 0.5rem 1.5rem !important;
        margin-top: 0.5rem !important;
        position: relative;
    }

    .sidebar .nav-category::before {
        content: '';
        position: absolute;
        left: 1.5rem;
        bottom: -5px;
        width: 30px;
        height: 2px;
        background: linear-gradient(90deg, #0d6efd, transparent);
        border-radius: 2px;
    }

    /* Menu Items */
    .sidebar .nav-item {
        margin: 0.2rem 0.8rem;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sidebar .nav-item .nav-link {
        display: flex;
        align-items: center;
        padding: 0.85rem 1rem !important;
        color: #495057 !important;
        font-weight: 500 !important;
        font-size: 0.875rem !important;
        border-radius: 12px;
        position: relative;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: transparent;
    }

    /* Hover Effect with Gradient Background */
    .sidebar .nav-item .nav-link:hover {
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.08) 0%, rgba(13, 110, 253, 0.03) 100%) !important;
        color: #0d6efd !important;
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.1);
    }

    /* Active State with Gradient Accent */
    .sidebar .nav-item .nav-link.active,
    .sidebar .nav-item.active .nav-link {
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.12) 0%, rgba(13, 110, 253, 0.06) 100%) !important;
        color: #0d6efd !important;
        font-weight: 600 !important;
        box-shadow: 0 2px 8px rgba(13, 110, 253, 0.15);
    }

    .sidebar .nav-item .nav-link.active::before,
    .sidebar .nav-item.active .nav-link::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, #0d6efd, #0a58ca);
        border-radius: 0 4px 4px 0;
        box-shadow: 0 0 8px rgba(13, 110, 253, 0.4);
    }

    /* Menu Icons */
    .sidebar .nav-item .menu-icon {
        margin-right: 0.85rem !important;
        font-size: 1.25rem !important;
        color: #6c757d;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
    }

    .sidebar .nav-item .nav-link:hover .menu-icon {
        color: #0d6efd !important;
        transform: scale(1.1) rotate(5deg);
    }

    .sidebar .nav-item .nav-link.active .menu-icon,
    .sidebar .nav-item.active .nav-link .menu-icon {
        color: #0d6efd !important;
        transform: scale(1.05);
    }

    /* Menu Title */
    .sidebar .nav-item .menu-title {
        font-size: 0.875rem;
        line-height: 1.4;
        transition: all 0.3s ease;
    }

    /* Badge Styling */
    .sidebar .badge {
        font-size: 0.65rem !important;
        padding: 0.25rem 0.5rem !important;
        border-radius: 6px !important;
        font-weight: 600 !important;
        margin-left: auto !important;
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }
    }

    .sidebar .badge-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
    }

    /* Ripple Effect on Click */
    .sidebar .nav-item .nav-link::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0);
        background: radial-gradient(circle, rgba(13, 110, 253, 0.3) 0%, transparent 70%);
        border-radius: 50%;
        opacity: 0;
        pointer-events: none;
        transition: all 0.5s ease;
    }

    .sidebar .nav-item .nav-link:active::after {
        transform: translate(-50%, -50%) scale(2);
        opacity: 1;
        transition: 0s;
    }

    /* Sidebar Minimized State */
    body.sidebar-icon-only .sidebar .nav-item .nav-link {
        padding: 0.85rem 0.5rem !important;
        justify-content: center;
    }

    body.sidebar-icon-only .sidebar .menu-title,
    body.sidebar-icon-only .sidebar .badge {
        display: none;
    }

    body.sidebar-icon-only .sidebar .menu-icon {
        margin-right: 0 !important;
    }

    /* Mobile Responsiveness */
    @media (max-width: 991px) {
        .sidebar {
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.1) !important;
        }

        .sidebar .nav-item {
            margin: 0.3rem 1rem;
        }

        .sidebar .nav-item .nav-link {
            padding: 1rem 1.2rem !important;
            font-size: 0.9rem !important;
        }

        .sidebar .menu-icon {
            font-size: 1.4rem !important;
        }
    }

    /* Smooth Scrollbar */
    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.02);
        border-radius: 10px;
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #0d6efd, #0a58ca);
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, #0a58ca, #084298);
    }
</style>

<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        {{-- =================================== --}}
        {{-- DASHBOARD (SEMUA ROLE) --}}
        {{-- =================================== --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->is('/') || request()->routeIs('dashboard*') ? 'active' : '' }}" href="/">
                <i class="mdi mdi-grid-large menu-icon"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>

        {{-- DZIKIR ONLINE (SEMUA ROLE) --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dzikir*') ? 'active' : '' }}" href="{{ route('dzikir.index') }}">
                <i class="mdi mdi-hands-pray menu-icon" style="color: #0d6efd;"></i>
                <span class="menu-title">Dzikir Online</span>
                <span class="badge badge-success rounded-pill ms-auto" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">New</span>
            </a>
        </li>

        {{-- =================================== --}}
        {{-- MENU RIWAYAT (GABUNGAN) --}}
        {{-- =================================== --}}
{{-- =================================== --}}
        {{-- MENU RIWAYAT (GABUNGAN) --}}
        {{-- =================================== --}}
        <li class="nav-item nav-category">Riwayat Lengkap</li>
        
        @if (auth()->user()->role != 'admin_gaji')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('self.attend.history') ? 'active' : '' }}" href="{{ route('attendance.history') }}">
                    <i class="menu-icon mdi mdi-clock-outline"></i>
                    <span class="menu-title">Riwayat Absensi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('leave-requests.personal-history') ? 'active' : '' }}" href="{{ route('leave-requests.personal-history') }}">
                    <i class="menu-icon mdi mdi-hospital-box-outline"></i>
                    <span class="menu-title">Riwayat Izin/Sakit</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('leave-requests.cuti-history') ? 'active' : '' }}" href="{{ route('leave-requests.cuti-history') }}">
                    <i class="menu-icon mdi mdi-wallet-travel"></i>
                    <span class="menu-title">Riwayat Cuti</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('employment-history.*') ? 'active' : '' }}" href="{{ route('employment-history.index') }}">
                    <i class="menu-icon mdi mdi-source-branch"></i>
                    <span class="menu-title">Riwayat Divisi/Cabang</span>
                </a>
            </li>
        @endif
        
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('violations.*') ? 'active' : '' }}" href="{{ route('violations.index') }}">
                <i class="menu-icon mdi mdi-alert-circle-outline"></i>
                <span class="menu-title">Riwayat Pelanggaran</span>
            </a>
        </li>
        
        @if (auth()->user()->role == 'security' || auth()->user()->role == 'admin')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('security.history') ? 'active' : '' }}" href="{{ route('security.history') }}">
                    <i class="menu-icon mdi mdi-qrcode-scan"></i>
                    <span class="menu-title">Riwayat Scan</span>
                </a>
            </li>
        @endif
        
        @if (in_array(auth()->user()->role, ['audit', 'leader', 'admin']))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('employee-evaluations.history') ? 'active' : '' }}" href="{{ route('employee-evaluations.history') }}">
                    <i class="menu-icon mdi mdi-clipboard-text-clock-outline"></i>
                    <span class="menu-title">Riwayat Evaluasi (Tim)</span>
                </a>
            </li>
        @endif

        {{-- RINGKASAN TAHUNAN --}}
        @if (auth()->user()->role != 'admin_gaji')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('attendance.summary') }}">
                    <i class="mdi mdi-text-box-multiple-outline menu-icon"></i>
                    <span class="menu-title">Ringkasan Tahunan</span>
                </a>
            </li>
        @endif
        @if (auth()->user()->role != 'admin_gaji')
            

            {{-- FORMS --}}
            <!-- <li class="nav-item">
                                                    <a class="nav-link" href="{{ route('leave-requests.create') }}">
                                                        <i class="menu-icon mdi mdi-file-document-edit-outline"></i>
                                                        <span class="menu-title">Form Izin / Telat</span>
                                                    </a>
                                                </li> -->
            <!-- <li class="nav-item">
                                                <a class="nav-link" href="{{ route('leave-requests.create-cuti') }}">
                                                    <i class="menu-icon mdi mdi-wallet-travel"></i>
                                                    <span class="menu-title">Form Pengajuan Cuti</span>
                                                </a>
                                            </li> -->
        @endif

        {{-- =================================== --}}
        {{-- MENU UMUM (EXCEPT ADMIN GAJI) --}}
        {{-- =================================== --}}
        @if (auth()->user()->role != 'admin_gaji')
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
                <a class="nav-link" href="{{ route('employee-evaluations.my-history') }}">
                    <i class="menu-icon mdi mdi-clipboard-text-clock-outline"></i>
                    <span class="menu-title">Rapor Saya</span>
                </a>
            </li>
            
            
        @endif

        {{-- RIWAYAT PELANGGARAN (KHUSUS ADMIN GAJI) --}}
        @if (auth()->user()->role == 'admin_gaji')
            
        @endif

        {{-- =================================== --}}
        {{-- GAJI KU (SEMUA ROLE) --}}
        {{-- =================================== --}}
        @if (auth()->user()->role != 'admin')
            <li class="nav-item nav-category">Keuangan</li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('my-salary.index') }}">
                    <i class="menu-icon mdi mdi-wallet-outline"></i>
                    <span class="menu-title">Gaji Ku</span>
                </a>
            </li>
        @endif

        @if (auth()->user()->role != 'admin')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('salary-summary.index') }}">
                    <i class="menu-icon mdi mdi-file-chart-outline"></i>
                    <span class="menu-title">Ringkasan Gaji Tahunan</span>
                </a>
            </li>
        @endif

        {{-- =================================== --}}
        {{-- MENU KASBON --}}
        {{-- =================================== --}}

        {{-- 1. Menu Utama Kasbon (Semua Role bisa akses untuk pengajuan/lihat data) --}}
        @if (auth()->user()->role != 'admin')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('kasbon.index') }}">
                    <i class="menu-icon mdi mdi-cash-multiple"></i>
                    <span class="menu-title">
                        {{ in_array(auth()->user()->role, ['admin_gaji']) ? 'Data Kasbon' : 'Kasbon Saya' }}
                    </span>
                </a>
            </li>
        @elseif (auth()->user()->role == 'admin_gaji')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('kasbon.index') }}">
                    <i class="menu-icon mdi mdi-cash-multiple"></i>
                    <span class="menu-title">Data Kasbon</span>
                </a>
            </li>
        @endif

        {{-- 2. Menu Verifikasi Pembayaran (HANYA ADMIN GAJI) --}}
        @if(auth()->user()->role === 'admin_gaji')
            @php
                // Hitung jumlah cicilan yang statusnya 'pending'
                $pendingCount = \App\Models\CashAdvanceInstallment::where('status', 'pending')->count();
            @endphp
            <li class="nav-item">
                <a class="nav-link" href="{{ route('kasbon.verification') }}">
                    <i class="menu-icon mdi mdi-cash-check"></i>
                    <span class="menu-title">Verifikasi Bayar</span>

                    {{-- Badge Merah jika ada yang pending --}}
                    @if($pendingCount > 0)
                        <span class="badge badge-danger rounded-pill ms-auto">{{ $pendingCount }}</span>
                    @endif
                </a>
            </li>
        @endif

        {{-- =================================== --}}
        {{-- MANAJEMEN GAJI (ADMIN & GAJI) --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'admin_gaji')
            <li class="nav-item nav-category">Admin Gaji</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('employee-salaries.index') }}">
                    <i class="menu-icon mdi mdi-bank-outline"></i>
                    <span class="menu-title">Master Gaji User</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin-gaji.employee-salaries.index') }}">
                    <i class="menu-icon mdi mdi-account-star-outline"></i>
                    <span class="menu-title">Master Gaji Non Karyawan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('branch-salary.index') }}">
                    <i class="menu-icon mdi mdi-cash-register"></i>
                    <span class="menu-title">Penggajian Cabang</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin-gaji.salary-summary') }}">
                    <i class="menu-icon mdi mdi-table-large"></i>
                    <span class="menu-title">Ringkasan Gaji Cabang</span>
                </a>
            </li>
        {{-- DATA USER KHUSUS ADMIN GAJI --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('users.index') }}">
                    <i class="menu-icon mdi mdi-account-group"></i>
                    <span class="menu-title">Data User (Reguler)</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin-gaji.users.index') }}">
                    <i class="menu-icon mdi mdi-account-group-outline"></i>
                    <span class="menu-title">Data User Non Karyawan</span>
                </a>
            </li>
        @endif

        {{-- =================================== --}}
        {{-- MENU KHUSUS SUPER ADMIN --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'admin')
            <li class="nav-item nav-category">Menu Cabang</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('branches.index') }}">
                    <i class="menu-icon mdi mdi-domain"></i>
                    <span class="menu-title">Data Cabang</span>
                </a>
            </li>
            
            <li class="nav-item nav-category">Admin Dzikir</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dzikir.index', 'admin.dzikir.create', 'admin.dzikir.edit') ? 'active' : '' }}" href="{{ route('admin.dzikir.index') }}">
                    <i class="menu-icon mdi mdi-hands-pray"></i>
                    <span class="menu-title">Setting Dzikir</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dzikir.stats') ? 'active' : '' }}" href="{{ route('admin.dzikir.stats') }}">
                    <i class="menu-icon mdi mdi-chart-bar"></i>
                    <span class="menu-title">Statistik Dzikir User</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dzikir-campaign.*') ? 'active' : '' }}" href="{{ route('admin.dzikir-campaign.index') }}">
                    <i class="menu-icon mdi mdi-bullhorn-outline"></i>
                    <span class="menu-title">Campaign Zikir</span>
                </a>
            </li>
        @endif

        @if (auth()->user()->role === 'admin' || auth()->user()->role === 'admin_gaji')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.correction.index') ? 'active' : '' }}" href="{{ route('admin.correction.index') }}">
                    <i class="menu-icon mdi mdi-eraser"></i>
                    <span class="menu-title">Koreksi Absensi</span>
                    <span class="badge badge-danger ms-2" style="font-size: 0.6rem;">Admin</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.audit-monitor.index') ? 'active' : '' }}" href="{{ route('admin.audit-monitor.index') }}">
                    <i class="menu-icon mdi mdi-shield-search"></i>
                    <span class="menu-title">Monitor Edit Audit</span>
                    <span class="badge badge-warning ms-2" style="font-size: 0.6rem;">Super Admin</span>
                </a>
            </li>
        @endif

        @if (auth()->user()->role == 'admin')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('push-broadcast.*') ? 'active' : '' }}" href="{{ route('push-broadcast.create') }}">
                    <i class="menu-icon mdi mdi-bell-ring" style="color: #198754;"></i>
                    <span class="menu-title">Push Notification</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.artisan.index') ? 'active' : '' }}" href="{{ route('admin.artisan.index') }}">
                    <i class="menu-icon mdi mdi-console" style="color: #0d6efd;"></i>
                    <span class="menu-title">Artisan Web GUI</span>
                    <span class="badge badge-success ms-2" style="font-size: 0.6rem; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;">Dev</span>
                </a>
            </li>
        @endif

        {{-- =================================== --}}
        {{-- MANAJEMEN TIM (ADMIN ONLY) --}}
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

            <li class="nav-item">
                <a class="nav-link" href="{{ route('users.document-uploads') }}">
                    <i class="menu-icon mdi mdi-file-document-check-outline"></i>
                    <span class="menu-title">Monitor Upload Dokumen</span>
                    @php
                        $incompleteCount = \App\Models\User::where('is_active', true)
                            ->where('role', '!=', 'admin')
                            ->where(function ($q) {
                                $q->whereNull('profile_photo_path')
                                    ->orWhereNull('ktp_photo_path');
                            })->count();
                    @endphp
                    @if ($incompleteCount > 0)
                        <span class="badge badge-danger ms-2">{{ $incompleteCount }}</span>
                    @endif
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.ktp.download-pdf') }}">
                    <i class="menu-icon mdi mdi-file-download-outline"></i>
                    <span class="menu-title">Download Data KTP</span>
                </a>
            </li>
        @endif

        {{-- =================================== --}}
        {{-- MANAJEMEN TIM (AUDIT ONLY) --}}
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
        {{-- VERIFIKASI (ADMIN & AUDIT) --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'audit' || auth()->user()->role == 'admin' || auth()->user()->role == 'admin_gaji')
            <li class="nav-item nav-category">Verifikasi</li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('audit.verify.*') ? 'active' : '' }}" href="{{ route('audit.verify.list') }}">
                    <i class="menu-icon mdi mdi-checkbox-marked-outline"></i>
                    <span class="menu-title">Verifikasi Absensi</span>
                </a>
            </li>
            {{-- [NEW] Monitoring Cuti --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('leave-requests.admin-summary') ? 'active' : '' }}" href="{{ route('leave-requests.admin-summary') }}">
                    <i class="menu-icon mdi mdi-account-search"></i>
                    <span class="menu-title">Monitoring Cuti</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('leave-requests.active') ? 'active' : '' }}" href="{{ route('leave-requests.active') }}">
                    <i class="menu-icon mdi mdi-account-clock-outline"></i>
                    <span class="menu-title">User Aktif Cuti</span>
                    <span class="badge badge-success rounded-pill ms-auto">Today</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('leave-requests.index') ? 'active' : '' }}" href="{{ route('leave-requests.index') }}">
                    <i class="menu-icon mdi mdi-clock-alert-outline"></i>
                    <span class="menu-title">Daftar Izin / Telat</span>
                </a>
            </li>
            {{-- [NEW] Persetujuan Cuti --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('leave-requests.approvals') ? 'active' : '' }}" href="{{ route('leave-requests.approvals') }}">
                    <i class="menu-icon mdi mdi-check-decagram"></i>
                    <span class="menu-title">Persetujuan Cuti</span>
                </a>
            </li>

            {{-- Menu KHUSUS ADMIN --}}
            @if (auth()->user()->role == 'admin')
            {{-- 
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.voice-notes.index') ? 'active' : '' }}" href="{{ route('admin.voice-notes.index') }}">
                        <i class="menu-icon mdi mdi-microphone-outline"></i>
                        <span class="menu-title">Bukti VN Suara</span>
                        <span class="badge badge-danger ms-2" style="font-size: 0.6rem;">Admin</span>
                    </a>
                </li>
            --}}
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
        @endif

        {{-- =================================== --}}
        {{-- MENU SECURITY --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'security' || auth()->user()->role == 'admin')
            <li class="nav-item nav-category">Menu Security</li>

            @if (auth()->user()->role == 'security')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('security.scan') }}">
                        <i class="menu-icon mdi mdi-qrcode-scan"></i>
                        <span class="menu-title">Pindai Absensi</span>
                    </a>
                </li>
            @endif

            

            


        @endif

        {{-- =================================== --}}
        {{-- MENU PENGGUNA (TEAM/BRANCH) --}}
        {{-- =================================== --}}
        @if (in_array(auth()->user()->role, ['user_biasa', 'leader', 'audit', 'security', 'admin']))

            <li class="nav-item nav-category">Menu Pengguna</li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('team.index') }}">
                    <i class="menu-icon mdi mdi-account-multiple-outline"></i>
                    <span class="menu-title">Tim Saya</span>
                </a>
            </li>

            @if (in_array(auth()->user()->role, ['audit', 'leader', 'admin']))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('team.my-branches') }}">
                        <i class="menu-icon mdi mdi-office-building-marker"></i>
                        <span class="menu-title">Cabang Saya</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('employee-evaluations.index') }}">
                        <i class="menu-icon mdi mdi-clipboard-check-outline"></i>
                        <span class="menu-title">Rapor Karyawan</span>
                    </a>
                </li>
                
            @endif
        @endif

        {{-- =================================== --}}
        {{-- MONITORING (ADMIN, AUDIT, LEADER) --}}
        {{-- =================================== --}}
        @if (in_array(auth()->user()->role, ['admin', 'audit', 'leader']))
            <li class="nav-item nav-category">Monitoring Wilayah</li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('branch-leaderboard.index') }}">
                    <i class="menu-icon mdi mdi-trophy-award"></i>
                    <span class="menu-title">Top Absensi Cabang</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('inventory.branches') }}">
                    <i class="menu-icon mdi mdi-package-variant-closed"></i>
                    <span class="menu-title">Inventaris Cabang</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('branch-targets.index') }}">
                    <i class="menu-icon mdi mdi-target"></i>
                    <span class="menu-title">Target Cabang</span>
                </a>
            </li>
        @endif

    </ul>
</nav>