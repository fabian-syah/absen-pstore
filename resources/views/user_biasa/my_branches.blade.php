@extends('layout.master')

@section('title', 'Cabang Saya')
@section('heading', 'Monitoring Wilayah')

@push('styles')
<style>
    .branch-section-title {
        position: relative;
        padding-left: 1.5rem;
        margin-bottom: 1.5rem;
        color: #1e293b;
        font-weight: 700;
    }

    .branch-section-title::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 6px;
        height: 24px;
        background: linear-gradient(to bottom, #667eea, #764ba2);
        border-radius: 4px;
    }

    .branch-card-item {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        border: 1px solid #f1f5f9;
        overflow: hidden;
        height: 100%;
    }

    .branch-card-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.15);
        border-color: #c7d2fe;
    }

    .branch-icon-box {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #667eea;
        font-size: 24px;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .branch-card-item:hover .branch-icon-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .branch-stat {
        background: #f8fafc;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 500;
    }

    .branch-stat strong {
        display: block;
        font-size: 1.1rem;
        color: #1e293b;
        margin-bottom: 0.1rem;
    }
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <h4 class="branch-section-title">Cabang Kelolaan ({{ count($controlledBranches) }})</h4>
        </div>
    </div>

    <div class="row">
        @forelse ($controlledBranches as $branch)
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="branch-card-item p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="branch-icon-box">
                            <i class="mdi mdi-storefront-outline"></i>
                        </div>
                        <span class="badge bg-light text-secondary border">
                            ID: {{ $branch->id }}
                        </span>
                    </div>

                    <h5 class="fw-bold mb-2">{{ $branch->name }}</h5>
                    <p class="text-muted small mb-3" style="min-height: 40px;">
                        <i class="mdi mdi-map-marker-outline me-1"></i>
                        {{ Str::limit($branch->address ?? 'Alamat belum diatur', 50) }}
                    </p>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <div class="branch-stat">
                            <strong>{{ $branch->users_count }}</strong>
                            Karyawan
                        </div>

                        <a href="{{ route('team.branch.detail', $branch->id) }}"
                            class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            Detail <i class="mdi mdi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card p-5 text-center border-0 shadow-sm">
                    <div class="text-muted">
                        <i class="mdi mdi-office-building-off" style="font-size: 3rem;"></i>
                        <p class="mt-2">Anda tidak memiliki kontrol cabang khusus.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
@endsection

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
            
            {{-- Menu yang BISA diakses Audit & Admin --}}
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

            {{-- Menu KHUSUS ADMIN (Audit tidak bisa lihat) --}}
            @if(auth()->user()->role == 'admin')
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
        {{--         MENU SECURITY               --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'security' || auth()->user()->role == 'admin')
            <li class="nav-item nav-category">Menu Security</li>

            {{-- Menu Scan (Hanya untuk Role Security) --}}
            @if (auth()->user()->role == 'security')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('security.scan') }}">
                        <i class="menu-icon mdi mdi-qrcode-scan"></i>
                        <span class="menu-title">Pindai Absensi</span>
                    </a>
                </li>
            @endif

            {{-- Menu Riwayat Scan (Security & Admin) --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('security.history') }}">
                    <i class="menu-icon mdi mdi-history"></i>
                    <span class="menu-title">Riwayat Scan</span>
                </a>
            </li>
        @endif

        {{-- =================================== --}}
        {{--    MENU TIM (USER, LEADER, AUDIT)   --}}
        {{-- =================================== --}}
        @if (auth()->user()->role == 'user_biasa' ||
                auth()->user()->role == 'leader' ||
                auth()->user()->role == 'audit' ||
                auth()->user()->role == 'security')
            <li class="nav-item nav-category">Menu Pengguna</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('team.index') }}">
                    <i class="menu-icon mdi mdi-account-multiple-outline"></i>
                    <span class="menu-title">Tim Saya</span>
                </a>
            </li>

            {{-- UPDATE: ADMIN DITAMBAHKAN DISINI --}}
            @if (in_array(auth()->user()->role, ['admin', 'audit', 'leader']))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('team.my-branches') }}">
                        <i class="menu-icon mdi mdi-office-building-marker"></i>
                        <span class="menu-title">Cabang Saya</span>
                    </a>
                </li>
            @endif
        @endif

        {{-- =================================== --}}
        {{--    MONITORING (ADMIN, AUDIT, LEADER) --}}
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