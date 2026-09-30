{{-- =========================================================================
     MODERN AESTHETIC CUSTOM SIDEBAR
     - Solid clean white background (#ffffff) with 1px border (#e2e8f0)
     - No gradients, no purple, no AI-cliché animations
     - Smooth active state with soft blue tint and 3px blue indicator
     - Seamless support for icon-only minimization & mobile drawer
     ========================================================================= --}}
<style>
    /* Modern Sidebar Base */
    .sidebar {
        background: #ffffff !important;
        border-right: 1px solid #e2e8f0 !important;
        box-shadow: none !important;
        padding-top: 8px !important;
        transition: width 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .sidebar .nav {
        padding: 4px 8px 30px 8px !important;
        margin-bottom: 0 !important;
    }

    /* Menu Category Header */
    .sidebar .nav-category {
        color: #94a3b8 !important;
        font-size: 10px !important;
        font-weight: 700 !important;
        letter-spacing: 0.08em !important;
        text-transform: uppercase !important;
        padding: 18px 12px 6px 12px !important;
        margin: 0 !important;
        line-height: 1 !important;
        border: none !important;
    }

    .sidebar .nav-category::before {
        display: none !important;
    }

    /* Nav Item Container */
    .sidebar .nav-item {
        margin: 2px 0 !important;
        border-radius: 8px !important;
        overflow: visible !important;
        list-style: none !important;
        flex-shrink: 0 !important;
        min-height: 40px !important;
    }

    .sidebar .nav-item.nav-category {
        min-height: auto !important;
    }

    /* Nav Link */
    .sidebar .nav-item .nav-link {
        display: flex !important;
        align-items: center !important;
        padding: 8.5px 12px !important;
        color: #475569 !important;
        font-weight: 500 !important;
        font-size: 13px !important;
        border-radius: 8px !important;
        background: transparent !important;
        text-decoration: none !important;
        transition: all 0.15s ease !important;
        position: relative !important;
        border-left: 3px solid transparent !important;
        min-height: 40px !important;
    }

    /* Nav Link Hover */
    .sidebar .nav-item .nav-link:hover {
        background: #f1f5f9 !important;
        color: #0f172a !important;
        transform: none !important;
        box-shadow: none !important;
    }

    .sidebar .nav-item .nav-link:hover .menu-icon {
        color: #0f172a !important;
        transform: none !important;
    }

    /* Active State */
    .sidebar .nav-item.active .nav-link,
    .sidebar .nav-item .nav-link.active {
        background: #eff6ff !important;
        color: #2563eb !important;
        font-weight: 600 !important;
        border-left: 3px solid #2563eb !important;
        box-shadow: none !important;
    }

    .sidebar .nav-item.active .nav-link::before,
    .sidebar .nav-item .nav-link.active::before {
        display: none !important;
    }

    .sidebar .nav-item.active .nav-link::after,
    .sidebar .nav-item .nav-link.active::after {
        display: none !important;
    }

    .sidebar .nav-item.active .nav-link .menu-icon,
    .sidebar .nav-item .nav-link.active .menu-icon {
        color: #2563eb !important;
        transform: none !important;
    }

    /* Menu Icons */
    .sidebar .nav-item .menu-icon {
        margin-right: 10px !important;
        font-size: 18px !important;
        color: #64748b !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 22px !important;
        line-height: 1 !important;
        flex-shrink: 0 !important;
        transition: color 0.15s ease !important;
    }

    /* Menu Title */
    .sidebar .nav-item .menu-title {
        font-size: 13px !important;
        line-height: 1.3 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        flex-grow: 1 !important;
        display: inline-block !important;
    }

    /* Clean Badges */
    .sidebar .badge {
        font-size: 10px !important;
        font-weight: 700 !important;
        padding: 2px 7px !important;
        border-radius: 9999px !important;
        margin-left: auto !important;
        line-height: 1.2 !important;
        animation: none !important;
        box-shadow: none !important;
    }

    .sidebar .badge-danger {
        background: #ef4444 !important;
        color: #ffffff !important;
    }

    .sidebar .badge-primary,
    .sidebar .badge-success {
        background: #2563eb !important;
        color: #ffffff !important;
    }

    .sidebar .badge-info {
        background: #f1f5f9 !important;
        color: #475569 !important;
        border: 1px solid #e2e8f0 !important;
    }

    .sidebar .badge-warning {
        background: #fffbeb !important;
        color: #b45309 !important;
        border: 1px solid #fef3c7 !important;
    }

    /* Sidebar Minimized (sidebar-icon-only) - Desktop Only */
    @media (min-width: 992px) {
        body.sidebar-icon-only .sidebar {
            width: 70px !important;
        }

        body.sidebar-icon-only .sidebar .nav {
            padding: 8px 4px !important;
        }

        body.sidebar-icon-only .sidebar .nav-category,
        body.sidebar-icon-only .sidebar .menu-title,
        body.sidebar-icon-only .sidebar .badge {
            display: none !important;
        }

        body.sidebar-icon-only .sidebar .nav-item .nav-link {
            padding: 10px 0 !important;
            justify-content: center !important;
            border-left: none !important;
        }

        body.sidebar-icon-only .sidebar .nav-item.active .nav-link,
        body.sidebar-icon-only .sidebar .nav-item .nav-link.active {
            background: #eff6ff !important;
            border-left: none !important;
        }

        body.sidebar-icon-only .sidebar .nav-item .menu-icon {
            margin-right: 0 !important;
            font-size: 20px !important;
        }
    }

    /* Mobile Drawer (Clean Left-Sliding Offcanvas) */
    @media (max-width: 991px) {
        .sidebar.sidebar-offcanvas {
            position: fixed !important;
            top: 0 !important;
            bottom: 0 !important;
            left: 0 !important;
            right: auto !important;
            width: 290px !important;
            max-width: 86vw !important;
            height: 100vh !important;
            max-height: 100vh !important;
            background: #ffffff !important;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.22) !important;
            border-right: 1px solid #e2e8f0 !important;
            border-left: none !important;
            z-index: 1060 !important;
            transform: translateX(-105%) !important;
            -webkit-transform: translateX(-105%) !important;
            transition: transform 0.28s cubic-bezier(0.33, 1, 0.68, 1) !important;
            display: flex !important;
            flex-direction: column !important;
            padding: 0 !important;
            margin: 0 !important;
            overflow: hidden !important;
        }

        .sidebar.sidebar-offcanvas.active {
            transform: translateX(0) !important;
            -webkit-transform: translateX(0) !important;
        }

        .sidebar.sidebar-offcanvas .nav {
            display: block !important;
            flex: 1 1 auto !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
            padding: 10px 10px 110px 10px !important;
            margin: 0 !important;
            height: auto !important;
            max-height: none !important;
        }

        /* Force All Nav Items Visible, Non-Shrinking, and Sized on Mobile */
        .sidebar.sidebar-offcanvas .nav-item,
        body.sidebar-icon-only .sidebar.sidebar-offcanvas .nav-item {
            display: block !important;
            flex-shrink: 0 !important;
            min-height: 42px !important;
            height: auto !important;
            margin: 3px 0 !important;
            border-radius: 8px !important;
            visibility: visible !important;
            opacity: 1 !important;
            overflow: visible !important;
        }

        /* Category Header on Mobile */
        .sidebar.sidebar-offcanvas .nav-item.nav-category,
        body.sidebar-icon-only .sidebar.sidebar-offcanvas .nav-item.nav-category {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            min-height: auto !important;
            padding: 18px 12px 6px 12px !important;
            font-size: 10px !important;
            font-weight: 700 !important;
            color: #94a3b8 !important;
            letter-spacing: 0.08em !important;
            text-transform: uppercase !important;
            margin: 0 !important;
        }

        /* Nav Link on Mobile */
        .sidebar.sidebar-offcanvas .nav-item .nav-link,
        .sidebar.sidebar-offcanvas .nav .nav-item .nav-link,
        body.sidebar-icon-only .sidebar.sidebar-offcanvas .nav-item .nav-link {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            text-align: left !important;
            padding: 10px 14px !important;
            font-size: 13.5px !important;
            font-weight: 500 !important;
            color: #475569 !important;
            border-radius: 8px !important;
            background: transparent !important;
            min-height: 42px !important;
            height: auto !important;
            visibility: visible !important;
            opacity: 1 !important;
            border-left: 3px solid transparent !important;
            position: relative !important;
            text-decoration: none !important;
        }

        /* Active Nav Link on Mobile */
        .sidebar.sidebar-offcanvas .nav-item.active .nav-link,
        .sidebar.sidebar-offcanvas .nav-item .nav-link.active,
        body.sidebar-icon-only .sidebar.sidebar-offcanvas .nav-item.active .nav-link,
        body.sidebar-icon-only .sidebar.sidebar-offcanvas .nav-item .nav-link.active {
            background: #eff6ff !important;
            color: #2563eb !important;
            font-weight: 600 !important;
            border-left: 3px solid #2563eb !important;
        }

        /* Menu Icons on Mobile */
        .sidebar.sidebar-offcanvas .nav-item .menu-icon,
        .sidebar.sidebar-offcanvas .menu-icon,
        body.sidebar-icon-only .sidebar.sidebar-offcanvas .nav-item .menu-icon {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 24px !important;
            height: 24px !important;
            font-size: 20px !important;
            color: #64748b !important;
            margin-right: 12px !important;
            visibility: visible !important;
            opacity: 1 !important;
            flex-shrink: 0 !important;
            line-height: 1 !important;
        }

        .sidebar.sidebar-offcanvas .nav-item.active .nav-link .menu-icon,
        .sidebar.sidebar-offcanvas .nav-item .nav-link.active .menu-icon,
        body.sidebar-icon-only .sidebar.sidebar-offcanvas .nav-item.active .nav-link .menu-icon {
            color: #2563eb !important;
        }

        /* Menu Titles on Mobile */
        .sidebar.sidebar-offcanvas .nav-item .menu-title,
        .sidebar.sidebar-offcanvas .menu-title,
        body.sidebar-icon-only .sidebar.sidebar-offcanvas .nav-item .menu-title {
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
            color: #1e293b !important;
            font-size: 13.5px !important;
            font-weight: 500 !important;
            line-height: 1.3 !important;
            flex-grow: 1 !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        .sidebar.sidebar-offcanvas .nav-item.active .nav-link .menu-title,
        .sidebar.sidebar-offcanvas .nav-item .nav-link.active .menu-title,
        body.sidebar-icon-only .sidebar.sidebar-offcanvas .nav-item.active .nav-link .menu-title {
            color: #2563eb !important;
            font-weight: 600 !important;
        }

        /* Badges on Mobile */
        .sidebar.sidebar-offcanvas .badge,
        body.sidebar-icon-only .sidebar.sidebar-offcanvas .badge {
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
            margin-left: auto !important;
            flex-shrink: 0 !important;
        }
    }

    .sidebar-drawer-close-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        padding: 0;
        transition: all 0.15s ease;
    }

    .sidebar-drawer-close-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #cbd5e1;
    }

    .sidebar-drawer-close-btn i {
        font-size: 18px;
        line-height: 1;
    }

    /* Smooth Scrollbar */
    .sidebar::-webkit-scrollbar {
        width: 4px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 9999px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

<nav class="sidebar sidebar-offcanvas" id="sidebar">
    {{-- =================================== --}}
    {{-- MOBILE DRAWER HEADER & USER PROFILE --}}
    {{-- =================================== --}}
    <div class="sidebar-mobile-header d-flex d-lg-none align-items-center justify-content-between px-3 py-3 border-bottom bg-slate-50 flex-shrink-0">
        <div class="d-flex align-items-center gap-2">
            <img src="{{ pstoreFaviconDataUri() }}" onerror="this.onerror=null; this.src='{{ asset('assets/images/logo-pstore.png') }}';" alt="PStore" style="height: 32px; width: 32px; border-radius: 8px; object-fit: cover;">
            <div>
                <div class="fw-bold text-slate-900" style="font-size: 13px; line-height: 1.2;">PStore Absensi</div>
                <span class="badge" style="font-size: 9.5px; font-weight: 700; background: #0f172a; color: #ffffff; padding: 2px 6px; border-radius: 4px;">{{ strtoupper(str_replace('_', ' ', Auth::user()->role)) }}</span>
            </div>
        </div>
        <button type="button" class="sidebar-drawer-close-btn" id="sidebarDrawerCloseBtn" aria-label="Tutup Menu">
            <i class="mdi mdi-close"></i>
        </button>
    </div>

    <div class="sidebar-mobile-user px-3 py-2.5 border-bottom bg-white d-flex d-lg-none align-items-center gap-2.5 flex-shrink-0">
        <div class="user-avatar-wrap position-relative flex-shrink-0" style="width: 36px; height: 36px; overflow: visible;">
            <div class="user-avatar-circle" style="width: 36px; height: 36px; border-radius: 50%; overflow: hidden; border: 1.5px solid #e2e8f0;">
                @if (Auth::user()->profile_photo_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url(Auth::user()->profile_photo_path) }}" alt="{{ Auth::user()->name }}" style="width: 36px; height: 36px; object-fit: cover;">
                @else
                    <div class="rounded-circle bg-slate-100 text-slate-700 d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 12px;">
                        {{ getInitials(Auth::user()->name) }}
                    </div>
                @endif
            </div>
            @if(Auth::user()->is_verified)
                <span class="user-verified-badge" style="position: absolute; bottom: -2px; right: -2px; width: 15px; height: 15px; background: #ffffff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #2563eb; font-size: 13px; line-height: 1; z-index: 5; box-shadow: 0 1px 3px rgba(0,0,0,0.25);" title="Verified Account">
                    <i class="mdi mdi-check-decagram"></i>
                </span>
            @endif
        </div>
        <div class="overflow-hidden flex-grow-1">
            <div class="fw-semibold text-slate-900 text-truncate" style="font-size: 12.5px;">{{ Auth::user()->name }}</div>
            <div class="d-flex align-items-center gap-1.5 mt-0.5">
                <span class="badge" style="font-size: 9.5px; font-weight: 700; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 1.5px 6px; border-radius: 4px;">{{ Auth::user()->division->name ?? 'Headquarters' }}</span>
            </div>
        </div>
    </div>

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
                <i class="mdi mdi-hands-pray menu-icon text-primary"></i>
                <span class="menu-title">Dzikir Online</span>
                <span class="badge badge-primary rounded-pill ms-auto">New</span>
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
                <a class="nav-link {{ request()->routeIs('leave-requests.personal-history', 'audit.late.history', 'audit.late.rejected.history') ? 'active' : '' }}" href="{{ in_array(auth()->user()->role, ['admin', 'audit']) || strtolower(trim(auth()->user()->login_id ?? '')) === 'superadmin' ? route('audit.late.history') : route('leave-requests.personal-history') }}">
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
        <li class="nav-item nav-category">Menu Umum</li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('inventory.index') }}">
                <i class="menu-icon mdi mdi-package-variant"></i>
                <span class="menu-title">Inventaris</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('inventory.history') ? 'active' : '' }}" href="{{ route('inventory.history') }}">
                <i class="menu-icon mdi mdi-history"></i>
                <span class="menu-title">Riwayat Inventaris</span>
                @php
                    $invTotal = \Illuminate\Support\Facades\Cache::remember('sb_inv_' . auth()->id(), 60, function() {
                        return \App\Models\Inventory::where('user_id', auth()->id())->count() + 
                               \App\Models\InventoryReturn::where('user_id', auth()->id())->count();
                    });
                @endphp
                @if($invTotal > 0)
                    <span class="badge badge-info rounded-pill ms-auto">{{ $invTotal }}</span>
                @endif
            </a>
        </li>
        @if (auth()->user()->role != 'admin_gaji')
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
                // Hitung jumlah cicilan yang statusnya 'pending' (Cache 60 detik)
                $pendingCount = \Illuminate\Support\Facades\Cache::remember('sb_cash_adv_pending', 60, function() {
                    return \App\Models\CashAdvanceInstallment::where('status', 'pending')->count();
                });
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
                    <i class="menu-icon mdi mdi-console text-primary"></i>
                    <span class="menu-title">Artisan Web GUI</span>
                    <span class="badge badge-info ms-2" style="font-size: 0.65rem;">Dev</span>
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
                        $incompleteCount = \Illuminate\Support\Facades\Cache::remember('sb_incomplete_docs', 120, function() {
                            return \App\Models\User::where('is_active', true)
                                ->where('role', '!=', 'admin')
                                ->where(function ($q) {
                                    $q->whereNull('profile_photo_path')
                                        ->orWhereNull('ktp_photo_path');
                                })->count();
                        });
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
        @if (auth()->user()->role == 'audit' || auth()->user()->role == 'admin' || auth()->user()->role == 'admin_gaji' || auth()->user()->isInventoryAdmin())
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
                <a class="nav-link {{ request()->routeIs('leave-requests.index', 'audit.late.history', 'audit.late.rejected.history') ? 'active' : '' }}" href="{{ route('leave-requests.index') }}">
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
                            $ktpPendingCount = \Illuminate\Support\Facades\Cache::remember('sb_ktp_pending', 60, function() {
                                return \App\Models\User::where('ktp_request_status', 'pending')->count();
                            });
                        @endphp
                        @if ($ktpPendingCount > 0)
                            <span class="badge badge-danger ms-2">{{ $ktpPendingCount }}</span>
                        @endif
                    </a>
                </li>
            @endif

            @if (auth()->user()->isInventoryAdmin() || auth()->user()->role == 'audit')
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
        @if (in_array(auth()->user()->role, ['admin', 'audit', 'leader', 'admin_gaji']))
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