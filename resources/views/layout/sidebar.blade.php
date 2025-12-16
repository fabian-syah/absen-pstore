<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <i class="mdi mdi-office-building-outline me-2"></i>
            <span class="fw-bold">HR System</span>
        </div>
        <div class="user-profile mt-4 mb-3">
            <div class="profile-image">
                @if(auth()->user()->photo)
                    <img src="{{ asset('storage/' . auth()->user()->photo) }}" alt="profile">
                @else
                    <div class="avatar-placeholder">
                        <i class="mdi mdi-account"></i>
                    </div>
                @endif
            </div>
            <div class="profile-info">
                <h6 class="mb-0">{{ auth()->user()->name }}</h6>
                <small class="text-muted">
                    @php
                        $roleLabels = [
                            'admin' => 'Administrator',
                            'admin_gaji' => 'Admin Gaji',
                            'audit' => 'Auditor',
                            'leader' => 'Team Leader',
                            'security' => 'Security',
                            'user_biasa' => 'Staff'
                        ];
                    @endphp
                    {{ $roleLabels[auth()->user()->role] ?? auth()->user()->role }}
                </small>
            </div>
        </div>
    </div>

    <ul class="nav flex-column">
        {{-- =================================== --}}
        {{--       DASHBOARD & RIWAYAT           --}}
        {{-- =================================== --}}
        <li class="nav-item">
            <a class="nav-link" href="/">
                <span class="nav-icon">
                    <i class="mdi mdi-view-dashboard-outline"></i>
                </span>
                <span class="nav-title">Dashboard</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link" href="{{ route('attendance.history') }}">
                <span class="nav-icon">
                    <i class="mdi mdi-clock-outline"></i>
                </span>
                <span class="nav-title">Riwayat Absensi</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link" href="{{ route('attendance.summary') }}">
                <span class="nav-icon">
                    <i class="mdi mdi-chart-bar"></i>
                </span>
                <span class="nav-title">Ringkasan Tahunan</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link" href="{{ route('leave-requests.personal-history') }}">
                <span class="nav-icon">
                    <i class="mdi mdi-calendar-clock"></i>
                </span>
                <span class="nav-title">Riwayat Izin</span>
            </a>
        </li>

        {{-- Separator --}}
        <li class="nav-section">
            <span class="section-label">Menu Utama</span>
        </li>

        {{-- =================================== --}}
        {{--     MENU UMUM (SEMUA ROLE)          --}}
        {{-- =================================== --}}
        <li class="nav-item">
            <a class="nav-link" href="{{ route('inventory.index') }}">
                <span class="nav-icon">
                    <i class="mdi mdi-archive-outline"></i>
                </span>
                <span class="nav-title">Inventaris</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link" href="{{ route('job-targets.index') }}">
                <span class="nav-icon">
                    <i class="mdi mdi-clipboard-check-outline"></i>
                </span>
                <span class="nav-title">Job Desk & Target</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link" href="{{ route('employment-history.index') }}">
                <span class="nav-icon">
                    <i class="mdi mdi-account-switch-outline"></i>
                </span>
                <span class="nav-title">Riwayat Divisi</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link" href="{{ route('violations.index') }}">
                <span class="nav-icon">
                    <i class="mdi mdi-alert-octagon-outline"></i>
                </span>
                <span class="nav-title">Riwayat Pelanggaran</span>
            </a>
        </li>

        {{-- MENU GAJI --}}
        <li class="nav-item has-submenu">
            <a class="nav-link" data-bs-toggle="collapse" href="#salaryMenu">
                <span class="nav-icon">
                    <i class="mdi mdi-cash-multiple"></i>
                </span>
                <span class="nav-title">Penggajian</span>
                <span class="nav-arrow">
                    <i class="mdi mdi-chevron-down"></i>
                </span>
            </a>
            <div class="collapse" id="salaryMenu">
                <ul class="nav flex-column submenu">
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="mdi mdi-wallet-outline"></i>
                            <span>Gaji Saya</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="mdi mdi-chart-timeline-variant"></i>
                            <span>Ringkasan Tahunan</span>
                        </a>
                    </li>
                    @if (auth()->user()->role != 'leader')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('kasbon.index') }}">
                            <i class="mdi mdi-cash-refund"></i>
                            <span>Kasbon</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </li>

        {{-- =================================== --}}
        {{--    ADMINISTRASI (ADMIN & ADMIN GAJI)--}}
        {{-- =================================== --}}
        @if (in_array(auth()->user()->role, ['admin', 'admin_gaji', 'audit']))
        <li class="nav-section">
            <span class="section-label">Administrasi</span>
            @if(auth()->user()->role == 'admin')
            <span class="badge bg-danger">Admin</span>
            @elseif(auth()->user()->role == 'admin_gaji')
            <span class="badge bg-warning">Gaji</span>
            @elseif(auth()->user()->role == 'audit')
            <span class="badge bg-info">Audit</span>
            @endif
        </li>

        {{-- Menu Cabang --}}
        @if (auth()->user()->role == 'admin' || auth()->user()->role == 'audit')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('branches.index') }}">
                <span class="nav-icon">
                    <i class="mdi mdi-domain"></i>
                </span>
                <span class="nav-title">Data Cabang</span>
            </a>
        </li>
        @endif

        {{-- Manajemen Tim --}}
        <li class="nav-item has-submenu">
            <a class="nav-link" data-bs-toggle="collapse" href="#teamManagement">
                <span class="nav-icon">
                    <i class="mdi mdi-account-group-outline"></i>
                </span>
                <span class="nav-title">Manajemen Tim</span>
                <span class="nav-arrow">
                    <i class="mdi mdi-chevron-down"></i>
                </span>
            </a>
            <div class="collapse" id="teamManagement">
                <ul class="nav flex-column submenu">
                    @if (auth()->user()->role == 'admin')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('divisions.index') }}">
                            <i class="mdi mdi-sitemap"></i>
                            <span>Data Divisi</span>
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('users.index') }}">
                            <i class="mdi mdi-account-multiple"></i>
                            <span>Data User</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- Manajemen Gaji --}}
        @if (auth()->user()->role == 'admin' || auth()->user()->role == 'admin_gaji')
        <li class="nav-item">
            <a class="nav-link" href="#">
                <span class="nav-icon">
                    <i class="mdi mdi-bank-outline"></i>
                </span>
                <span class="nav-title">Manajemen Gaji</span>
                <span class="badge bg-warning ms-auto">New</span>
            </a>
        </li>
        @endif

        {{-- Menu Koreksi Absensi --}}
        @if (auth()->user()->role == 'admin')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.correction.index') }}">
                <span class="nav-icon">
                    <i class="mdi mdi-calendar-edit"></i>
                </span>
                <span class="nav-title">Koreksi Absensi</span>
                <span class="badge bg-danger">Admin</span>
            </a>
        </li>
        @endif
        @endif

        {{-- =================================== --}}
        {{--     VERIFIKASI (ADMIN & AUDIT)      --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'audit' || auth()->user()->role == 'admin')
        <li class="nav-section">
            <span class="section-label">Verifikasi</span>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('audit.verify.list') }}">
                <span class="nav-icon">
                    <i class="mdi mdi-check-decagram-outline"></i>
                </span>
                <span class="nav-title">Verifikasi Absensi</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link" href="{{ route('leave-requests.index') }}">
                <span class="nav-icon">
                    <i class="mdi mdi-timer-sand"></i>
                </span>
                <span class="nav-title">Izin & Keterlambatan</span>
            </a>
        </li>

        {{-- Menu Admin Khusus --}}
        @if (auth()->user()->role == 'admin')
        <li class="nav-item has-submenu">
            <a class="nav-link" data-bs-toggle="collapse" href="#adminRequests">
                <span class="nav-icon">
                    <i class="mdi mdi-account-cog-outline"></i>
                </span>
                <span class="nav-title">Permintaan User</span>
                @php
                    $ktpPendingCount = \App\Models\User::where('ktp_request_status', 'pending')->count();
                    $totalPending = $ktpPendingCount;
                @endphp
                @if($totalPending > 0)
                <span class="badge bg-danger">{{ $totalPending }}</span>
                @endif
                <span class="nav-arrow">
                    <i class="mdi mdi-chevron-down"></i>
                </span>
            </a>
            <div class="collapse" id="adminRequests">
                <ul class="nav flex-column submenu">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('users.photo-requests') }}">
                            <i class="mdi mdi-camera-retake"></i>
                            <span>Ganti Foto</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('users.ktp-requests') }}">
                            <i class="mdi mdi-card-account-details"></i>
                            <span>Ganti KTP</span>
                            @if($ktpPendingCount > 0)
                            <span class="badge bg-danger">{{ $ktpPendingCount }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('inventory-returns.index') }}">
                <span class="nav-icon">
                    <i class="mdi mdi-package-down"></i>
                </span>
                <span class="nav-title">Pengembalian Inventaris</span>
            </a>
        </li>
        @endif
        @endif

        {{-- =================================== --}}
        {{--         MENU SECURITY               --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'security' || auth()->user()->role == 'admin')
        <li class="nav-section">
            <span class="section-label">Security</span>
            <span class="badge bg-success">Guard</span>
        </li>

        @if (auth()->user()->role == 'security')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('security.scan') }}">
                <span class="nav-icon">
                    <i class="mdi mdi-qrcode-scan"></i>
                </span>
                <span class="nav-title">Scan QR Code</span>
                <span class="badge bg-success pulse"></span>
            </a>
        </li>
        @endif

        <li class="nav-item">
            <a class="nav-link" href="{{ route('security.history') }}">
                <span class="nav-icon">
                    <i class="mdi mdi-history"></i>
                </span>
                <span class="nav-title">Riwayat Scan</span>
            </a>
        </li>
        @endif

        {{-- =================================== --}}
        {{--    MENU TIM & MONITORING            --}}
        {{-- =================================== --}}
        @if (in_array(auth()->user()->role, ['user_biasa', 'leader', 'audit', 'security', 'admin']))
        <li class="nav-section">
            <span class="section-label">Tim & Monitoring</span>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('team.index') }}">
                <span class="nav-icon">
                    <i class="mdi mdi-account-multiple-outline"></i>
                </span>
                <span class="nav-title">Anggota Tim</span>
            </a>
        </li>

        @if (in_array(auth()->user()->role, ['audit', 'leader', 'admin']))
        <li class="nav-item">
            <a class="nav-link" href="{{ route('team.my-branches') }}">
                <span class="nav-icon">
                    <i class="mdi mdi-office-building-cog"></i>
                </span>
                <span class="nav-title">Kelola Cabang</span>
            </a>
        </li>
        @endif
        @endif

        {{-- =================================== --}}
        {{--    MONITORING WILAYAH               --}}
        {{-- =================================== --}}
        @if (in_array(auth()->user()->role, ['admin', 'audit', 'leader']))
        <li class="nav-item has-submenu">
            <a class="nav-link" data-bs-toggle="collapse" href="#monitoringMenu">
                <span class="nav-icon">
                    <i class="mdi mdi-monitor-dashboard"></i>
                </span>
                <span class="nav-title">Monitoring</span>
                <span class="nav-arrow">
                    <i class="mdi mdi-chevron-down"></i>
                </span>
            </a>
            <div class="collapse" id="monitoringMenu">
                <ul class="nav flex-column submenu">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('branch-leaderboard.index') }}">
                            <i class="mdi mdi-trophy-outline"></i>
                            <span>Leaderboard Cabang</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('inventory.branches') }}">
                            <i class="mdi mdi-archive-eye-outline"></i>
                            <span>Inventaris Cabang</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('branch-targets.index') }}">
                            <i class="mdi mdi-target-variant"></i>
                            <span>Target Cabang</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        @endif

        {{-- Logout Button --}}
        <li class="nav-item mt-auto">
            <a class="nav-link logout-btn" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <span class="nav-icon">
                    <i class="mdi mdi-logout"></i>
                </span>
                <span class="nav-title">Keluar</span>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </li>
    </ul>

    <div class="sidebar-footer">
        <div class="system-status">
            <div class="status-indicator online"></div>
            <small class="text-muted">System Online</small>
        </div>
    </div>
</nav>

<style>
.sidebar {
    background: linear-gradient(180deg, #1a237e 0%, #283593 100%);
    color: white;
    min-height: 100vh;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}

.sidebar-header {
    padding: 1.5rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar-brand {
    display: flex;
    align-items: center;
    font-size: 1.2rem;
    color: white;
}

.sidebar-brand i {
    font-size: 1.5rem;
}

.user-profile {
    display: flex;
    align-items: center;
    padding: 1rem 0;
}

.profile-image {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid rgba(255,255,255,0.2);
    margin-right: 1rem;
}

.profile-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.profile-info h6 {
    color: white;
    margin-bottom: 0.25rem;
}

.profile-info small {
    font-size: 0.8rem;
    opacity: 0.8;
}

.nav-section {
    padding: 0.75rem 1rem;
    margin-top: 1rem;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.section-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: 0.7;
    font-weight: 600;
}

.nav-item {
    margin: 0.25rem 0;
}

.nav-link {
    color: rgba(255,255,255,0.8);
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    border-radius: 8px;
    margin: 0 0.5rem;
    transition: all 0.3s ease;
    position: relative;
}

.nav-link:hover {
    background: rgba(255,255,255,0.1);
    color: white;
    transform: translateX(5px);
}

.nav-link.active {
    background: rgba(255,255,255,0.15);
    color: white;
    border-left: 3px solid #4fc3f7;
}

.nav-icon {
    width: 24px;
    margin-right: 1rem;
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.nav-title {
    flex-grow: 1;
    font-size: 0.9rem;
}

.nav-arrow {
    transition: transform 0.3s ease;
}

.has-submenu .nav-link[aria-expanded="true"] .nav-arrow {
    transform: rotate(180deg);
}

.submenu {
    padding-left: 2.5rem;
    background: rgba(0,0,0,0.1);
    border-radius: 8px;
    margin: 0.5rem 0;
}

.submenu .nav-link {
    padding: 0.5rem 1rem;
    margin: 0.1rem 0;
    font-size: 0.85rem;
}

.submenu .nav-icon {
    font-size: 1rem;
    margin-right: 0.75rem;
}

.badge {
    font-size: 0.65rem;
    padding: 0.25rem 0.5rem;
    font-weight: 600;
}

.pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.logout-btn {
    margin-top: 1rem;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
}

.logout-btn:hover {
    background: rgba(255, 0, 0, 0.2);
    color: #ff6b6b;
}

.sidebar-footer {
    padding: 1rem;
    border-top: 1px solid rgba(255,255,255,0.1);
    margin-top: auto;
}

.system-status {
    display: flex;
    align-items: center;
    justify-content: center;
}

.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 0.5rem;
}

.status-indicator.online {
    background: #4CAF50;
    box-shadow: 0 0 10px #4CAF50;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Aktifkan submenu jika ada
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link[href]');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
            // Buka parent submenu jika ada
            const parentSubmenu = link.closest('.collapse');
            if (parentSubmenu) {
                parentSubmenu.classList.add('show');
                const toggleBtn = document.querySelector(`[href="#${parentSubmenu.id}"]`);
                if (toggleBtn) {
                    toggleBtn.classList.add('active');
                }
            }
        }
    });
    
    // Animasi hover
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
        });
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
});
</script>