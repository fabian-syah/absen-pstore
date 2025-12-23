@extends('layout.master')

@section('title')
    Dashboard
@endsection

@section('heading')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            <span class="text-muted small d-block mb-1" id="greeting-text">Selamat Datang,</span>
            <h3 class="fw-bold mb-0">{{ Auth::user()->name }}!</h3>
        </div>
        {{-- <div class="text-end d-none d-md-block">
            <h5 class="fw-bold mb-0 text-primary" id="header-clock">--:--:--</h5>
            <small class="text-muted">{{ \Carbon\Carbon::now($current_timezone)->translatedFormat('l, d F Y') }}</small>
        </div> --}}
    </div>
@endsection

@section('content')

    {{-- ======================================================================= --}}
    {{-- BAGIAN BARU: BIRTHDAY CELEBRATION (Hanya Muncul Jika H-30 atau Hari H)  --}}
    {{-- ======================================================================= --}}
    @if (isset($birthdayData) && $birthdayData)
        <div class="row mb-4 animate-enter">
            <div class="col-12">
                <div class="card border-0 shadow-lg overflow-hidden birthday-card">
                    {{-- Animated Background Elements --}}
                    <div class="confetti-container"></div>
                    <div class="balloon b1"></div>
                    <div class="balloon b2"></div>
                    <div class="balloon b3"></div>

                    <div class="card-body position-relative z-index-1 py-4 px-4">
                        <div class="row align-items-center">
                            {{-- KIRI: PESAN --}}
                            <div class="col-md-7 text-white">
                                @if ($birthdayData['is_today'])
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-warning text-dark fw-bold me-2 pulse-animation">
                                            <i class="mdi mdi-cake-variant me-1"></i> SPECIAL DAY
                                        </span>
                                        <h2 class="fw-bold mb-0 text-shadow-glam">HAPPY BIRTHDAY! 🎂</h2>
                                    </div>
                                    <p class="lead mb-1 text-white-50">
                                        Selamat Ulang Tahun yang ke-<strong>{{ $birthdayData['age_to_be'] }}</strong>, {{ Auth::user()->name }}!
                                    </p>
                                    <p class="small text-white-50 mb-0">Semoga panjang umur, sehat selalu, dan karir makin cemerlang di PStore!</p>
                                @else
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-light text-primary fw-bold me-2">
                                            <i class="mdi mdi-calendar-star me-1"></i> UPCOMING
                                        </span>
                                        <h3 class="fw-bold mb-0 text-white">Counting Down to Your Day! 🎈</h3>
                                    </div>
                                    <p class="mb-0 text-white-50">
                                        Sebentar lagi kamu ulang tahun yang ke-<strong>{{ $birthdayData['age_to_be'] }}</strong>.
                                        Siapkan harapan terbaikmu!
                                    </p>
                                @endif
                            </div>

                            {{-- KANAN: COUNTDOWN TIMER --}}
                            <div class="col-md-5 mt-4 mt-md-0 text-center text-md-end">
                                @if (!$birthdayData['is_today'])
                                    <div class="d-flex justify-content-center justify-content-md-end gap-2" id="birthday-countdown">
                                        {{-- Hari --}}
                                        <div class="countdown-box glass-box">
                                            <span class="d-block fw-bold fs-3" id="cd-days">{{ $birthdayData['days_left'] }}</span>
                                            <small class="text-uppercase" style="font-size: 9px;">Hari</small>
                                        </div>
                                        {{-- Jam --}}
                                        <div class="countdown-box glass-box">
                                            <span class="d-block fw-bold fs-3" id="cd-hours">00</span>
                                            <small class="text-uppercase" style="font-size: 9px;">Jam</small>
                                        </div>
                                        {{-- Menit --}}
                                        <div class="countdown-box glass-box">
                                            <span class="d-block fw-bold fs-3" id="cd-minutes">00</span>
                                            <small class="text-uppercase" style="font-size: 9px;">Menit</small>
                                        </div>
                                        {{-- Detik --}}
                                        <div class="countdown-box glass-box">
                                            <span class="d-block fw-bold fs-3 text-warning" id="cd-seconds">00</span>
                                            <small class="text-uppercase" style="font-size: 9px;">Detik</small>
                                        </div>
                                    </div>
                                @else
                                    {{-- Jika Hari H --}}
                                    <div class="text-center">
                                         <button class="btn btn-light text-danger fw-bold shadow-lg pulse-animation" onclick="confettiEffect()">
                                            <i class="mdi mdi-party-popper me-2"></i> RAYAKAN SEKARANG!
                                         </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- BAGIAN BARU: ATTENDANCE WRAPPED (Desember Only) --}}
    @if (\Carbon\Carbon::now()->month == 12)
        <div class="row mb-4 animate-enter">
            <div class="col-12">
                <div class="card bg-gradient-warning text-white shadow-lg"
                    style="background: linear-gradient(135deg, #111 0%, #333 100%); border: 1px solid #FFD700; overflow: hidden; position: relative;">
                    <div
                        style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255, 215, 0, 0.1) 0%, transparent 70%); animation: rotateGlow 20s linear infinite; pointer-events: none;">
                    </div>
                    <div class="card-body d-flex justify-content-between align-items-center position-relative z-index-1">
                        <div class="d-flex align-items-center">
                            <div class="me-3 d-none d-sm-block">
                                <i class="mdi mdi-sparkles text-warning display-4"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-warning mb-1">✨ Your {{ date('Y') }} Wrapped is Here!</h4>
                                <p class="mb-0 text-white-50">Lihat rangkuman perjalanan karirmu selama setahun ini.</p>
                            </div>
                        </div>
                        <a href="{{ route('attendance.recap') }}"
                            class="btn btn-light rounded-pill fw-bold shadow-sm hover-scale">
                            <i class="mdi mdi-play-circle-outline me-1"></i> Putar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- BAGIAN 1: DASHBOARD STATISTIK (ADMIN/AUDIT/SECURITY) --}}
    @if (auth()->user()->role == 'admin')
        {{-- WIDGET ADMIN --}}
        <div class="row mb-4">
            <div class="col-md-3 grid-margin stretch-card animate-enter" style="animation-delay: 0.1s">
                <div class="card card-bank gradient-purple">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi mdi-account-multiple"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Total User</p>
                            <h2 class="card-bank-value count-up" data-target="{{ $totalUsers }}">0</h2>
                            <p class="card-bank-desc">Karyawan Aktif</p>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card animate-enter" style="animation-delay: 0.2s">
                <div class="card card-bank gradient-blue">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi mdi-office-building"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Total Cabang</p>
                            <h2 class="card-bank-value count-up" data-target="{{ $totalBranches }}">0</h2>
                            <p class="card-bank-desc">Cabang Terdaftar</p>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card animate-enter" style="animation-delay: 0.3s">
                <div class="card card-bank gradient-green">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi mdi-calendar-check"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Absensi Hari Ini</p>
                            <h2 class="card-bank-value count-up" data-target="{{ $attendancesToday }}">0</h2>
                            <p class="card-bank-desc">Total absensi hari ini</p>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card animate-enter" style="animation-delay: 0.4s">
                <div class="card card-bank gradient-orange">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi mdi-alert-circle-outline"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Perlu Verifikasi</p>
                            <h2 class="card-bank-value count-up" data-target="{{ $pendingVerifications }}">0</h2>
                            <p class="card-bank-desc">Menunggu persetujuan</p>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
        </div>
    @elseif (auth()->user()->role == 'audit')
        {{-- WIDGET AUDIT --}}
        <div class="row mb-4">
            <div class="col-md-4 grid-margin stretch-card animate-enter" style="animation-delay: 0.1s">
                <div class="card card-bank gradient-red">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi mdi-alert-circle-outline"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Verif Absensi</p>
                            <h2 class="card-bank-value count-up" data-target="{{ $pendingVerifications }}">0</h2>
                            <p class="card-bank-desc">Absensi pending (Foto/Lokasi)</p>
                            <a href="{{ route('audit.verify.list') }}" class="btn btn-sm btn-light mt-2 shadow-sm">
                                <i class="mdi mdi-clipboard-check me-1"></i>Lihat Daftar
                            </a>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 grid-margin stretch-card animate-enter" style="animation-delay: 0.2s">
                <div class="card card-bank gradient-blue">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi mdi-file-document-edit-outline"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Approve Izin</p>
                            <h2 class="card-bank-value count-up" data-target="{{ $pendingLeaves }}">0</h2>
                            <p class="card-bank-desc">Izin, Sakit, Cuti, WFH, Telat</p>
                            <a href="{{ route('leave-requests.index') }}" class="btn btn-sm btn-light mt-2 shadow-sm">
                                <i class="mdi mdi-playlist-check me-1"></i>Lihat Pengajuan
                            </a>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 grid-margin stretch-card animate-enter" style="animation-delay: 0.3s">
                <div class="card card-bank gradient-green">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi mdi-calendar-check"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Hadir Hari Ini</p>
                            <h2 class="card-bank-value count-up" data-target="{{ $attendancesToday }}">0</h2>
                            <p class="card-bank-desc">Total kehadiran di cabang Anda</p>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
        </div>
    @elseif (auth()->user()->role == 'security')
        {{-- WIDGET SECURITY --}}
        <div class="row mb-4">
            <div class="col-md-6 grid-margin stretch-card animate-enter" style="animation-delay: 0.1s">
                <div class="card card-action hover-float">
                    <div class="card-body text-center py-5">
                        <div class="mb-4 pulse-icon-wrapper">
                            <i class="mdi mdi-qrcode-scan display-1 text-dark"></i>
                        </div>
                        <h4 class="card-title mb-3">Pindai QR User</h4>
                        <p class="text-muted mb-4">Arahkan kamera ke QR Code user untuk melakukan absensi.</p>
                        <a href="{{ route('security.scan') }}" class="btn btn-dark btn-lg shadow-lg">
                            <i class="mdi mdi-camera-enhance me-2"></i>Mulai Memindai
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 grid-margin stretch-card animate-enter" style="animation-delay: 0.2s">
                <div class="card card-bank gradient-dark">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi mdi-chart-bar"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Pindaian Hari Ini</p>
                            <h2 class="card-bank-value count-up" data-target="{{ $myScansToday }}">0</h2>
                            <p class="card-bank-desc">Total pindaian QR hari ini</p>
                            <div class="mt-4 pt-3 border-top border-light">
                                <p class="card-bank-label mb-2">User Aktif</p>
                                <h3 class="card-bank-value mb-0 count-up" data-target="{{ $totalUsers }}">0</h3>
                            </div>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================================= --}}
    {{-- BAGIAN 2: LEADERBOARD PODIUM MEWAH (TOP 3)                              --}}
    {{-- ======================================================================= --}}

    <div class="row mb-5 animate-enter" style="animation-delay: 0.4s">

        {{-- 1. TOP 3 ABSENSI (USER/ADMIN/AUDIT/LEADER) --}}
        @if (auth()->user()->role != 'security' && isset($leaderboard) && count($leaderboard) > 0)
            <div class="col-12">
                <div class="card border-0 shadow-lg luxury-card overflow-hidden">
                    <div class="luxury-bg-glow"></div>
                    <div class="luxury-bg-pattern"></div>

                    <div class="card-body p-4 position-relative z-index-1">
                        <div class="text-center mb-5">
                            <h3 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif; color: #333;">
                                <i class="mdi mdi-trophy text-warning me-2"></i>Top Rajin Absen
                            </h3>
                            <p class="text-muted small">
                                {{ auth()->user()->role == 'admin' ? 'Global' : 'Cabang Anda' }} - Bulan
                                {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}
                            </p>
                        </div>

                        {{-- PODIUM CONTAINER --}}
                        <div class="podium-luxury-container d-flex justify-content-center align-items-end gap-3">

                            {{-- JUARA 2 (KIRI) --}}
                            @if (isset($leaderboard[1]))
                                <div class="podium-step-container animate-enter" style="animation-delay: 0.6s">
                                    <div class="podium-avatar-wrapper">
                                        @if ($leaderboard[1]->user->profile_photo_path)
                                            <img src="{{ asset('storage/' . $leaderboard[1]->user->profile_photo_path) }}"
                                                class="luxury-avatar">
                                        @else
                                            <div class="luxury-avatar-placeholder bg-secondary">
                                                {{ substr($leaderboard[1]->user->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="rank-circle silver">2</div>
                                    </div>
                                    <div class="podium-block silver-block text-center">
                                        <div class="podium-content">
                                            <h6 class="fw-bold text-dark mb-1">
                                                {{ Str::limit($leaderboard[1]->user->name, 12) }}</h6>
                                            <p class="small text-muted mb-2">
                                                {{ $leaderboard[1]->user->division->name ?? '-' }}</p>
                                            <div class="stat-pill mb-1">
                                                <i
                                                    class="mdi mdi-check-circle text-success me-1"></i>{{ $leaderboard[1]->total_attendance }}
                                                Hadir
                                            </div>
                                            <div class="stat-pill">
                                                <i
                                                    class="mdi mdi-clock-outline text-primary me-1"></i>{{ $leaderboard[1]->avg_arrival_display }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- JUARA 1 (TENGAH - TERTINGGI) --}}
                            @if (isset($leaderboard[0]))
                                <div class="podium-step-container main-winner animate-enter"
                                    style="animation-delay: 0.5s">
                                    <div class="crown-floating">
                                        <img src="https://cdn-icons-png.flaticon.com/512/6941/6941697.png" alt="Crown"
                                            width="50">
                                    </div>
                                    <div class="podium-avatar-wrapper gold-glow">
                                        @if ($leaderboard[0]->user->profile_photo_path)
                                            <img src="{{ asset('storage/' . $leaderboard[0]->user->profile_photo_path) }}"
                                                class="luxury-avatar">
                                        @else
                                            <div class="luxury-avatar-placeholder gold-gradient">
                                                {{ substr($leaderboard[0]->user->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="rank-circle gold">1</div>
                                    </div>
                                    <div class="podium-block gold-block text-center">
                                        <div class="sparkle s1"></div>
                                        <div class="sparkle s2"></div>
                                        <div class="sparkle s3"></div>
                                        <div class="podium-content pt-3">
                                            <h5 class="fw-bold text-dark mb-1">
                                                {{ Str::limit($leaderboard[0]->user->name, 15) }}</h5>
                                            <p class="small text-muted mb-2">
                                                {{ $leaderboard[0]->user->division->name ?? '-' }}</p>
                                            <div class="stat-pill gold mb-2">
                                                <i class="mdi mdi-star me-1"></i>{{ $leaderboard[0]->total_attendance }}
                                                Kehadiran
                                            </div>
                                            <div class="stat-pill">
                                                <i class="mdi mdi-timer-sand me-1"></i>Avg:
                                                {{ $leaderboard[0]->avg_arrival_display }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- JUARA 3 (KANAN) --}}
                            @if (isset($leaderboard[2]))
                                <div class="podium-step-container animate-enter" style="animation-delay: 0.7s">
                                    <div class="podium-avatar-wrapper">
                                        @if ($leaderboard[2]->user->profile_photo_path)
                                            <img src="{{ asset('storage/' . $leaderboard[2]->user->profile_photo_path) }}"
                                                class="luxury-avatar">
                                        @else
                                            <div class="luxury-avatar-placeholder" style="background: #CD7F32;">
                                                {{ substr($leaderboard[2]->user->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="rank-circle bronze">3</div>
                                    </div>
                                    <div class="podium-block bronze-block text-center">
                                        <div class="podium-content">
                                            <h6 class="fw-bold text-dark mb-1">
                                                {{ Str::limit($leaderboard[2]->user->name, 12) }}</h6>
                                            <p class="small text-muted mb-2">
                                                {{ $leaderboard[2]->user->division->name ?? '-' }}</p>
                                            <div class="stat-pill mb-1">
                                                <i class="mdi mdi-check me-1"></i>{{ $leaderboard[2]->total_attendance }}
                                                Hadir
                                            </div>
                                            <div class="stat-pill">
                                                {{ $leaderboard[2]->avg_arrival_display }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- 2. TOP SCANNER (ADMIN & SECURITY) -- SAME PODIUM STYLE --}}
        @if (
            (auth()->user()->role == 'admin' || auth()->user()->role == 'security') &&
                isset($topScanners) &&
                count($topScanners) > 0)
            <div class="col-12 mt-4">
                <div class="card border-0 shadow-lg luxury-card overflow-hidden">
                    <div class="luxury-bg-pattern"></div>
                    <div class="card-body p-4 position-relative z-index-1">
                        <div class="text-center mb-5">
                            <h3 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif; color: #333;">
                                <i class="mdi mdi-qrcode-scan text-primary me-2"></i>Top Security Scanner
                            </h3>
                            <p class="text-muted small">Total Scan Terbanyak Bulan Ini</p>
                        </div>

                        {{-- PODIUM CONTAINER --}}
                        <div class="podium-luxury-container d-flex justify-content-center align-items-end gap-3">

                            {{-- JUARA 2 --}}
                            @if (isset($topScanners[1]))
                                <div class="podium-step-container">
                                    <div class="podium-avatar-wrapper">
                                        @if ($topScanners[1]->profile_photo_path)
                                            <img src="{{ asset('storage/' . $topScanners[1]->profile_photo_path) }}"
                                                class="luxury-avatar">
                                        @else
                                            <div class="luxury-avatar-placeholder bg-secondary">
                                                {{ substr($topScanners[1]->name, 0, 1) }}</div>
                                        @endif
                                        <div class="rank-circle silver">2</div>
                                    </div>
                                    <div class="podium-block silver-block text-center">
                                        <div class="podium-content">
                                            <h6 class="fw-bold text-dark mb-1">{{ Str::limit($topScanners[1]->name, 12) }}
                                            </h6>
                                            <div class="stat-pill mt-2">
                                                {{ $topScanners[1]->total_scans }} Scan
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- JUARA 1 --}}
                            @if (isset($topScanners[0]))
                                <div class="podium-step-container main-winner">
                                    <div class="crown-floating"><img
                                            src="https://cdn-icons-png.flaticon.com/512/6941/6941697.png" alt="Crown"
                                            width="50"></div>
                                    <div class="podium-avatar-wrapper gold-glow">
                                        @if ($topScanners[0]->profile_photo_path)
                                            <img src="{{ asset('storage/' . $topScanners[0]->profile_photo_path) }}"
                                                class="luxury-avatar">
                                        @else
                                            <div class="luxury-avatar-placeholder gold-gradient">
                                                {{ substr($topScanners[0]->name, 0, 1) }}</div>
                                        @endif
                                        <div class="rank-circle gold">1</div>
                                    </div>
                                    <div class="podium-block gold-block text-center">
                                        <div class="podium-content pt-3">
                                            <h5 class="fw-bold text-dark mb-1">{{ Str::limit($topScanners[0]->name, 15) }}
                                            </h5>
                                            <div class="stat-pill gold mt-2">
                                                <i class="mdi mdi-star me-1"></i>{{ $topScanners[0]->total_scans }} Scan
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- JUARA 3 --}}
                            @if (isset($topScanners[2]))
                                <div class="podium-step-container">
                                    <div class="podium-avatar-wrapper">
                                        @if ($topScanners[2]->profile_photo_path)
                                            <img src="{{ asset('storage/' . $topScanners[2]->profile_photo_path) }}"
                                                class="luxury-avatar">
                                        @else
                                            <div class="luxury-avatar-placeholder" style="background: #CD7F32;">
                                                {{ substr($topScanners[2]->name, 0, 1) }}</div>
                                        @endif
                                        <div class="rank-circle bronze">3</div>
                                    </div>
                                    <div class="podium-block bronze-block text-center">
                                        <div class="podium-content">
                                            <h6 class="fw-bold text-dark mb-1">{{ Str::limit($topScanners[2]->name, 12) }}
                                            </h6>
                                            <div class="stat-pill mt-2">
                                                {{ $topScanners[2]->total_scans }} Scan
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>


    {{-- ======================================================================= --}}
    {{-- BAGIAN 3: DASHBOARD PERSONAL (ID CARD & ABSEN MANDIRI)                  --}}
    {{-- ======================================================================= --}}

    <div class="row animate-enter" style="animation-delay: 0.5s">
        <div class="col-12">
            <h4 class="card-title mb-3"><i class="mdi mdi-account-circle me-2"></i>Absensi Pribadi</h4>
        </div>
    </div>

    <div class="row animate-enter" style="animation-delay: 0.6s">
        {{-- KARTU ID & QR CODE (Quick Access) --}}
        <div class="col-md-5 grid-margin stretch-card">
            <div class="row w-100 m-0 p-0">

                {{-- ID CARD VISUAL --}}
                <div class="col-12 mb-3">
                    <div class="card card-id gradient-dark">
                        <div class="card-body">
                            <div class="card-id-header">
                                <div class="card-id-photo-wrapper">
                                    @if (Auth::user()->profile_photo_path)
                                        <img src="{{ Storage::url(Auth::user()->profile_photo_path) }}" alt="Profile"
                                            class="id-card-img" data-bs-toggle="modal"
                                            data-bs-target="#profilePhotoModal"
                                            data-src="{{ Storage::url(Auth::user()->profile_photo_path) }}"
                                            title="Klik untuk memperbesar">
                                    @else
                                        <div class="id-card-img-placeholder">
                                            {{ substr(Auth::user()->name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="card-id-logo">
                                    <i class="mdi mdi-credit-card-outline"></i>
                                    <span>ID Card</span>
                                </div>
                            </div>
                            <div class="card-id-details">
                                <p class="card-id-label">NAMA</p>
                                <h3 class="card-id-name text-truncate">{{ strtoupper(Auth::user()->name) }}</h3>
                                <p class="card-id-label">DIVISI</p>
                                <h4 class="card-id-division">
                                    {{ strtoupper(Auth::user()->division->name ?? 'BELUM ADA DIVISI') }}
                                </h4>
                            </div>
                            <div class="card-id-footer d-flex justify-content-end align-items-end mt-4">
                                <div class="text-end">
                                    <p class="mb-0 text-white-50" style="font-size: 10px; letter-spacing: 1px;">NOMOR ID
                                    </p>
                                    <p class="card-id-card-number mb-0"
                                        style="font-size: 22px; letter-spacing: 2px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                                        {{ $idCardNumber ?? '000000 000000' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- QR CODE CARD UNTUK SCAN SECURITY --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm hover-float" style="background: white; border-radius: 16px;">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="fw-bold mb-1">QR Code Absensi</h5>
                                <p class="text-muted small mb-0">Klik QR untuk memperbesar</p>
                            </div>
                            <div class="bg-light p-2 rounded shadow-sm scale-on-hover" id="dashboard-qrcode"
                                style="cursor: pointer; transition: transform 0.2s;" data-bs-toggle="modal"
                                data-bs-target="#qrModal">
                                {{-- QR Code dirender via JS disini --}}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- KARTU STATUS ABSENSI & TOMBOL ABSEN MANDIRI --}}
        <div class="col-md-7 grid-margin stretch-card">
            <div class="card card-status hover-shadow-lg">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h4 class="card-title mb-1">
                                <i class="mdi mdi-calendar-today me-2"></i>Status Absensi
                            </h4>
                            <span class="badge bg-light text-dark border shadow-sm mt-1">
                                <i class="mdi mdi-clock-outline me-1"></i> Jadwal: {{ $todaySchedule }}
                            </span>
                        </div>

                        <div class="text-end">
                            {{-- CLOCK ELEMENT --}}
                            <h4 class="fw-bold mb-0 font-monospace text-primary" id="realtime-clock">--:--:--</h4>
                            {{-- TAMPILKAN TIMEZONE --}}
                            <small class="text-muted d-block" style="font-size: 0.7rem;">
                                {{ \Carbon\Carbon::now($current_timezone)->translatedFormat('l, d F Y') }}
                                ({{ $current_timezone }})
                            </small>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm">
                            <i class="mdi mdi-check-circle-outline me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                            <i class="mdi mdi-alert-circle-outline me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show shadow-sm">
                            <i class="mdi mdi-alert-outline me-2"></i>
                            {{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- LOGIKA TAMPILAN STATUS --}}
                    @if ($myAttendanceToday)
                        {{-- 1. JIKA SUDAH ABSEN MASUK --}}
                        @php
                            $isCrossDay = false;
                            if (!$myAttendanceToday->check_out_time) {
                                // Timezone already handled in Controller
                                $isCrossDay =
                                    $myAttendanceToday->check_in_time->format('Y-m-d') !==
                                    \Carbon\Carbon::now($current_timezone)->format('Y-m-d');
                            }
                            $sourceLabel =
                                $myAttendanceToday->attendance_type == 'scan' ? 'Security Scan' : 'Selfie Mandiri';
                        @endphp

                        {{-- SUDAH PULANG --}}
                        @if ($myAttendanceToday->check_out_time || $myAttendanceToday->photo_out_path)
                            <div class="status-card status-success mb-3 animate-pulse-green">
                                <div class="d-flex align-items-center">
                                    <div class="status-icon shadow"><i class="mdi mdi-home-variant"></i></div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1 fw-bold">Anda Sudah Pulang</h5>
                                        <p class="text-muted mb-0 small">Terima kasih atas kerja keras Anda!</p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row text-center">
                                    <div class="col-6 border-end">
                                        <small class="text-muted d-block">JAM MASUK</small>
                                        <h4 class="fw-bold text-success mb-0 count-up-time">
                                            {{ $myAttendanceToday->check_in_time->format('H:i') }}
                                        </h4>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">JAM PULANG</small>
                                        <h4 class="fw-bold text-primary mb-0 count-up-time">
                                            {{ $myAttendanceToday->check_out_time ? $myAttendanceToday->check_out_time->format('H:i') : '-' }}
                                        </h4>
                                    </div>
                                </div>
                            </div>

                            {{-- SEDANG BEKERJA --}}
                        @else
                            <div
                                class="status-card {{ $isCrossDay ? 'status-warning' : 'status-success' }} mb-3 position-relative overflow-hidden">


                                <div class="d-flex align-items-center position-relative z-index-1">
                                    <div class="status-icon shadow pulse-animation">
                                        <i
                                            class="mdi {{ $isCrossDay ? 'mdi-clock-alert-outline' : 'mdi-clock-check' }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        @if ($isCrossDay)
                                            {{-- [MODIFIKASI] SECTION LEMBUR LINTAS HARI --}}
                                            <div class="alert alert-light border-warning mb-0 p-3"
                                                style="background-color: #fffbeb; border: 1px solid #fcd34d;">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div>
                                                        <h6 class="text-danger fw-bold mb-0 text-uppercase"
                                                            style="letter-spacing: 0.5px;">Lembur Lintas Hari Detected!
                                                        </h6>
                                                        <p class="text-muted small mb-0">
                                                            Masuk: <span
                                                                class="fw-bold text-dark">{{ $myAttendanceToday->check_in_time->format('d M, H:i') }}</span>
                                                        </p>
                                                    </div>
                                                </div>

                                                <hr class="my-3" style="border-top: 1px solid #fde68a;">

                                                {{-- CONTAINER UTAMA ACTION --}}
                                                <div id="cross-day-actions">

                                                    {{-- LOGIC: HANYA TAMPILKAN SLIDER JIKA BELUM DIKONFIRMASI --}}
                                                    @if (!$myAttendanceToday->is_extended_shift)
                                                        {{-- WRAPPER SLIDER & SKIP --}}
                                                        <div id="confirmation-wrapper">
                                                            {{-- OPSI 1: SLIDER UNTUK FOTO (NORMAL) --}}
                                                            <div class="mb-3" id="slider-view">
                                                                <p class="text-muted small mb-1">
                                                                    <i class="mdi mdi-camera me-1"></i><strong>Opsi
                                                                        1:</strong>
                                                                    Absen Pulang Normal (Foto)
                                                                </p>

                                                                <div class="position-relative w-100 rounded-pill d-flex align-items-center px-1 user-select-none shadow-sm"
                                                                    id="slide-track"
                                                                    style="height: 50px; background-color: #fde047; transition: all 0.2s;">

                                                                    <div class="position-absolute w-100 text-center"
                                                                        style="pointer-events: none; left:0;">
                                                                        <span class="fw-bold text-dark small opacity-75"
                                                                            style="letter-spacing: 1px;">GESER KE KANAN
                                                                            >></span>
                                                                    </div>

                                                                    <div id="slide-thumb"
                                                                        class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center text-warning"
                                                                        style="width: 42px; height: 42px; cursor: pointer; position: absolute; left: 4px; z-index: 10;">
                                                                        <i class="mdi mdi-arrow-right fw-bold fs-5"></i>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            {{-- OPSI 2: TOMBOL LEWATI (TANPA FOTO) --}}
                                                            <form
                                                                action="{{ route('self.attend.skip', $myAttendanceToday->id) }}"
                                                                method="POST">
                                                                @csrf
                                                                <p class="text-muted small mb-1">
                                                                    <i class="mdi mdi-cancel me-1"></i><strong>Opsi
                                                                        2:</strong>
                                                                    Lupa Absen (Tanpa Foto)
                                                                </p>
                                                                <button type="submit"
                                                                    class="btn btn-outline-danger w-100 py-2 shadow-sm"
                                                                    onclick="return confirm('Pilih ini jika Anda LUPA absen pulang kemarin.\nSesi akan ditutup otomatis TANPA FOTO.\n\nLanjutkan?');">
                                                                    <i class="mdi mdi-skip-forward me-2"></i>Lewati & Tutup
                                                                    Sesi
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @endif

                                                    {{-- FASE 3: CAMERA BUTTON --}}
                                                    {{-- Tampil otomatis jika sudah confirm (is_extended_shift == true) ATAU setelah slide selesai (via JS) --}}
                                                    <div id="camera-view"
                                                        class="{{ $myAttendanceToday->is_extended_shift ? '' : 'd-none' }} text-center animate-enter mt-3">
                                                        <div class="mb-3">
                                                            <h6 class="text-primary fw-bold">Konfirmasi Pulang</h6>
                                                            <p class="text-muted small">Silahkan ambil foto selfie untuk
                                                                validasi.</p>
                                                        </div>
                                                        <a href="{{ route('self.attend.create', ['attendance_id' => $myAttendanceToday->id, 'mode' => 'pulang']) }}"
                                                            class="btn btn-primary w-100 py-3 rounded-3 shadow-sm fw-bold">
                                                            <i class="mdi mdi-camera-party-mode me-2"></i> Ambil Foto &
                                                            Pulang
                                                        </a>
                                                    </div>

                                                </div>

                                            </div>
                                        @else
                                            {{-- STATUS NORMAL --}}
                                            <div class="d-flex align-items-center">
                                                <h5 class="mb-1 fw-bold">Sedang Bekerja</h5>
                                                <span class="live-indicator ms-2"></span>
                                            </div>
                                            <p class="mb-0">Masuk Pukul:
                                                <strong>{{ $myAttendanceToday->check_in_time->format('H:i') }}</strong> via
                                                {{ $sourceLabel }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                {{-- TOMBOL AKSI PULANG (NORMAL) --}}
                                @if (!$isCrossDay)
                                    <div class="mt-3 pt-3 border-top position-relative z-index-1">
                                        @if (Auth::user()->only_security_scan)
                                            <button class="btn btn-secondary btn-sm w-100 shadow-sm" disabled
                                                style="cursor: not-allowed; opacity: 0.7;">
                                                <i class="mdi mdi-lock me-1"></i> Absen Pulang Mandiri Dikunci
                                            </button>
                                            <small class="text-danger d-block text-center mt-1" style="font-size: 10px;">
                                                Silahkan Scan QR Code ke Security untuk Pulang
                                            </small>
                                        @else
                                            <a href="{{ route('self.attend.create', ['attendance_id' => $myAttendanceToday->id, 'mode' => 'pulang']) }}"
                                                class="btn btn-danger btn-sm w-100 shadow hover-scale">
                                                <i class="mdi mdi-logout me-1"></i>
                                                Absen Pulang Mandiri
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    @elseif($myPendingLeave)
                        {{-- 2. JIKA ADA PENGAJUAN PENDING (KUNING) --}}
                        <div class="status-card status-warning mb-3 hover-shadow-lg">
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="mdi mdi-timer-sand display-3 text-warning pulse-animation"></i>
                                </div>
                                <h4 class="mb-2 fw-bold text-warning">Sedang Menunggu Approve dari Audit</h4>
                                <p class="text-muted mb-4 px-3">
                                    Pengajuan <strong>{{ strtoupper($myPendingLeave->type) }}</strong> Anda sedang
                                    diproses.
                                </p>
                                <div class="bg-white p-3 rounded border mb-3 shadow-sm mx-4">
                                    <span class="fst-italic text-dark">"{{ $myPendingLeave->reason }}"</span>
                                </div>
                                {{-- Tombol Batalkan --}}
                                <form action="{{ route('leave-requests.cancel', $myPendingLeave->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-danger btn-sm shadow-sm"
                                        onclick="return confirm('Batalkan pengajuan ini?')">
                                        <i class="mdi mdi-close-circle me-1"></i> Batalkan Pengajuan
                                    </button>
                                </form>
                            </div>
                        </div>
                        {{-- 3. JIKA SUDAH DI APPROVE (HIJAU) --}}
                    @elseif(isset($myLeaveToday) && $myLeaveToday && $myLeaveToday->status == 'approved')
                        <div class="status-card status-success mb-3 hover-float">
                            <div class="d-flex align-items-start">
                                <div class="status-icon shadow"><i class="mdi mdi-check-decagram"></i></div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <h5 class="mb-1 fw-bold">Pengajuan Disetujui</h5>
                                    </div>
                                    <p class="text-muted mb-2 small">
                                        Status: <strong>{{ strtoupper($myLeaveToday->type) }}</strong>
                                    </p>
                                    <div class="bg-white p-2 rounded border mb-2 shadow-sm">
                                        <span class="fst-italic text-dark">"{{ $myLeaveToday->reason }}"</span>
                                    </div>

                                    @if ($myLeaveToday->file_proof)
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-light border shadow-sm"
                                                onclick="window.open('{{ Storage::url($myLeaveToday->file_proof) }}', '_blank')">
                                                <i class="mdi mdi-image-area me-1"></i>Lihat Bukti
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-3 pt-3 border-top text-center">

                                {{-- LOGIKA BARU: Jika Izin Telat, tombolnya adalah ABSEN MASUK (bukan batalkan izin) --}}
                                @if ($myLeaveToday->type === 'telat')
                                    <p class="small text-muted mb-2">Anda sudah sampai kantor?</p>
                                    <a href="{{ route('self.attend.create') }}"
                                        class="btn btn-primary btn-sm w-100 shadow-sm hover-scale">
                                        <i class="mdi mdi-camera-account me-2"></i> Lakukan Absen Masuk
                                    </a>
                                    <small class="d-block mt-2 text-muted fst-italic" style="font-size: 10px;">
                                        *Izin telat akan tetap tercatat di history Anda.
                                    </small>
                                @else
                                    {{-- LOGIKA LAMA (Untuk Sakit/Cuti/WFH yang masuk lebih awal) --}}
                                    <p class="small text-muted mb-2">Berubah pikiran atau sudah sampai kantor?</p>
                                    <form action="{{ route('leave-requests.finish-early', $myLeaveToday->id) }}"
                                        method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="btn btn-outline-danger btn-sm w-100 shadow-sm hover-scale"
                                            onclick="return confirm('Apakah Anda yakin? Status izin hari ini akan dibatalkan dan Anda bisa absen kembali.');">
                                            <i class="mdi mdi-map-marker-radius me-2"></i>Batalkan Izin & Absen Masuk
                                        </button>
                                    </form>
                                @endif

                            </div>
                        </div>
                    @else
                        {{-- [LOGIKA BARU] JIKA HABIS LEMBUR LINTAS HARI --}}
                        @if (isset($justFinishedOvertime) && $justFinishedOvertime)
                            <div class="status-card status-info mb-3 hover-shadow-lg">
                                <div class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="mdi mdi-bed-clock display-4 text-info"></i>
                                    </div>
                                    <h5 class="mb-2 fw-bold text-info">Selamat Beristirahat!</h5>
                                    <p class="text-muted mb-4 px-3 small">
                                        Anda baru saja pulang lembur pukul
                                        <strong>{{ $lastOvertimeSession->check_out_time->format('H:i') }}</strong>.
                                        <br>Sistem mencatat Anda lembur lintas hari. Anda dipersilakan masuk siang hari ini.
                                    </p>

                                    {{-- Tetap tampilkan tombol absen jika dia mau masuk lagi --}}
                                    <div class="d-flex justify-content-center gap-2">
                                        @if (Auth::user()->only_security_scan)
                                            <div class="d-flex flex-column align-items-center w-100">
                                                <button class="btn btn-secondary shadow-sm w-100" disabled
                                                    style="cursor: not-allowed; opacity: 0.7;">
                                                    <i class="mdi mdi-lock me-1"></i> Absen Mandiri Dikunci
                                                </button>
                                            </div>
                                        @else
                                            <a href="{{ route('self.attend.create') }}"
                                                class="btn btn-outline-info shadow hover-scale">
                                                <i class="mdi mdi-fingerprint me-2"></i>Absen Shift Baru
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- 4. BELUM ABSEN (DEFAULT) --}}
                            <div class="status-card status-info hover-shadow-lg">
                                <div class="text-center py-4">
                                    <div class="mb-3">
                                        <i class="mdi mdi-clock-alert display-4 text-primary pulse-text"></i>
                                    </div>
                                    <h5 class="mb-2 fw-bold">Anda Belum Absen Hari Ini</h5>
                                    <p class="text-muted mb-4">Gunakan fitur ini jika Anda bekerja WFH atau Dinas Luar.</p>
                                    <div class="d-flex justify-content-center gap-2">

                                        {{-- TOMBOL ABSEN MANDIRI --}}
                                        @if (Auth::user()->only_security_scan)
                                            <div class="d-flex flex-column align-items-center w-100">
                                                <button class="btn btn-secondary shadow-sm w-100" disabled
                                                    style="cursor: not-allowed; opacity: 0.7;">
                                                    <i class="mdi mdi-lock me-1"></i> Absen Mandiri Dikunci
                                                </button>
                                                <small class="text-danger mt-1" style="font-size: 10px;">
                                                    <i class="mdi mdi-alert-circle"></i> Wajib Scan QR ke Security
                                                </small>
                                            </div>
                                        @else
                                            <a href="{{ route('self.attend.create') }}"
                                                class="btn btn-dark shadow hover-scale">
                                                <i class="mdi mdi-fingerprint me-2"></i>Absen Mandiri
                                            </a>
                                        @endif

                                        <a href="{{ route('leave-requests.create') }}"
                                            class="btn btn-outline-dark shadow-sm hover-scale">
                                            <i class="mdi mdi-file-document-edit-outline me-2"></i>Izin/Sakit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif

                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================================================= --}}
    {{-- BAGIAN BARU: MENU CEPAT (QUICK ACTIONS)                               --}}
    {{-- ======================================================================= --}}
    <div class="row animate-enter mb-4" style="animation-delay: 0.7s">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="background: linear-gradient(to right, #ffffff, #f8f9fa);">
                <div class="card-body py-3">
                    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between">
                        <div class="d-flex align-items-center mb-3 mb-md-0">
                            <div class="icon-box bg-light text-warning rounded-circle p-2 me-3 shadow-sm">
                                <i class="mdi mdi-lightning-bolt fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Menu Cepat</h5>
                                <small class="text-muted">Butuh izin untuk hari lain? Ajukan di sini.</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2 w-100 w-md-auto">
                            {{-- Tombol Pengajuan Izin --}}
                            <a href="{{ route('leave-requests.create') }}"
                                class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold flex-grow-1 flex-md-grow-0 hover-scale">
                                <i class="mdi mdi-file-document-edit-outline me-2"></i> Ajukan Izin / Sakit
                            </a>

                            {{-- Tombol Riwayat (Opsional, agar seimbang) --}}
                            <a href="{{ route('attendance.history') }}"
                                class="btn btn-outline-secondary rounded-pill px-4 shadow-sm fw-bold flex-grow-1 flex-md-grow-0 hover-scale">
                                <i class="mdi mdi-history me-2"></i> Riwayat
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CHART SECTION --}}
    <div class="row mt-4 animate-enter" style="animation-delay: 0.8s">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom-0 py-3">
                    <h4 class="card-title mb-0"><i class="mdi mdi-chart-pie me-2"></i>Statistik Absensi</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="chart-container" style="position: relative; height:300px;">
                                <canvas id="attendancePieChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-center justify-content-center text-muted">
                            <div class="text-center">
                                <i class="mdi mdi-chart-bar-stacked display-1 opacity-25"></i>
                                <p class="mt-2">Analisis data kehadiran secara realtime</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL POPUP FOTO PROFIL --}}
    <div class="modal fade" id="profilePhotoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content glass-effect border-0">
                <div class="modal-body p-0 position-relative modal-image-wrapper text-center">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 shadow"
                        data-bs-dismiss="modal" aria-label="Close" style="z-index: 10;"></button>

                    <div class="p-3">
                        <img src="" id="profileModalImageSrc" class="img-fluid rounded shadow-lg"
                            alt="Profile Photo" style="max-height: 80vh; max-width: 100%; object-fit: contain;">
                    </div>
                    <div class="mt-2 mb-3 text-white">
                        <h5 class="mb-0">{{ Auth::user()->name }}</h5>
                        <small class="opacity-75">Foto Profil</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL POPUP QR CODE (Untuk Scan Security) --}}
    <div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 pb-0 justify-content-center">
                    <h5 class="modal-title fw-bold mt-3">QR Code Saya</h5>
                </div>
                <div class="modal-body text-center pt-2">
                    <div class="p-4 bg-light rounded-circle d-inline-block mb-3 shadow-inner">
                        <div id="qrcode-modal-display" class="d-flex justify-content-center"></div>
                    </div>
                    <p class="text-muted small mb-3">Tunjukkan ke Security untuk Scan</p>
                    <button type="button" class="btn btn-dark rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        /* === BIRTHDAY CARD STYLES === */
        .birthday-card {
            background: linear-gradient(135deg, #4c1d95 0%, #be185d 100%);
            /* Ungu ke Pink Mewah */
            position: relative;
            color: white;
        }

        /* Glassmorphism for Countdown */
        .glass-box {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 10px 15px;
            min-width: 70px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .text-shadow-glam {
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        /* Balloons Animation */
        .balloon {
            position: absolute;
            width: 60px;
            height: 70px;
            border-radius: 50% 50% 50% 50% / 40% 40% 60% 60%;
            background-color: rgba(255, 255, 255, 0.1);
            bottom: -80px;
            z-index: 0;
            animation: floatBalloon 10s infinite ease-in-out;
        }

        .balloon::before {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 20px;
            background: rgba(255, 255, 255, 0.3);
        }

        .b1 {
            left: 10%;
            background: rgba(255, 215, 0, 0.2);
            animation-duration: 8s;
            animation-delay: 0s;
            width: 50px;
            height: 60px;
        }

        .b2 {
            right: 15%;
            background: rgba(0, 255, 255, 0.15);
            animation-duration: 12s;
            animation-delay: 2s;
            width: 70px;
            height: 85px;
        }

        .b3 {
            left: 50%;
            background: rgba(255, 105, 180, 0.15);
            animation-duration: 10s;
            animation-delay: 5s;
        }

        @keyframes floatBalloon {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 0;
            }

            20% {
                opacity: 1;
            }

            100% {
                transform: translateY(-300px) rotate(20deg);
                opacity: 0;
            }
        }

        /* === SLIDER LOGIC STYLES === */
        #slide-thumb {
            transition: transform 0.1s;
        }

        #slide-thumb:active {
            cursor: grabbing !important;
        }

        .animate-enter {
            animation: fadeInUp 0.5s ease-out forwards;
        }

        /* === EXISTING STYLES === */
        /* === LUXURY LEADERBOARD STYLES (NEW) === */

        /* 1. Card Container & Background */
        .luxury-card {
            background: linear-gradient(135deg, #ffffff 0%, #f9fbfd 100%);
            position: relative;
            transition: all 0.4s ease;
        }

        .luxury-bg-glow {
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 223, 0, 0.05) 0%, rgba(255, 255, 255, 0) 70%);
            animation: rotateGlow 20s linear infinite;
            z-index: 0;
            pointer-events: none;
        }

        .luxury-bg-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: radial-gradient(#E1E1E1 1px, transparent 1px);
            background-size: 20px 20px;
            opacity: 0.3;
            z-index: 0;
        }

        @keyframes rotateGlow {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .icon-box-luxury {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #FFF8E1, #FFF3C4);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 10px rgba(255, 193, 7, 0.15);
        }

        .glass-badge {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 0, 0, 0.05);
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            color: #444;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        }

        /* 2. Podium Layout */
        .podium-luxury-container {
            min-height: 280px;
            margin-bottom: -30px;
            /* Overlap with block below */
        }

        .podium-step-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            position: relative;
            z-index: 2;
        }

        /* 3. Avatars & Glows */
        .podium-avatar-wrapper {
            position: relative;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }

        .podium-avatar-wrapper:hover {
            transform: translateY(-8px) scale(1.02);
        }

        .luxury-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .luxury-avatar-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 4px solid #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: bold;
            color: #fff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Winner Specifics */
        .main-winner {
            z-index: 10;
        }

        .main-winner .luxury-avatar,
        .main-winner .luxury-avatar-placeholder {
            width: 110px;
            height: 110px;
            border-width: 5px;
        }

        .crown-floating {
            position: absolute;
            top: -55px;
            left: 50%;
            transform: translateX(-50%) rotate(-5deg);
            animation: floatCrown 3s ease-in-out infinite;
            z-index: 20;
            filter: drop-shadow(0 5px 15px rgba(255, 215, 0, 0.4));
        }

        @keyframes floatCrown {

            0%,
            100% {
                transform: translateX(-50%) translateY(0) rotate(-5deg);
            }

            50% {
                transform: translateX(-50%) translateY(-10px) rotate(0deg);
            }
        }

        /* Glow Effects */
        .gold-glow::before {
            content: '';
            position: absolute;
            inset: -10px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 215, 0, 0.4) 0%, transparent 70%);
            z-index: -1;
            animation: pulseGlow 2s infinite;
        }

        /* Gradients */
        .gold-gradient {
            background: linear-gradient(135deg, #FFD700 0%, #FDB931 100%);
        }

        .silver-gradient {
            background: linear-gradient(135deg, #E0E0E0 0%, #BDBDBD 100%);
        }

        .bronze-gradient {
            background: linear-gradient(135deg, #CD7F32 0%, #A0522D 100%);
        }

        /* Rank Circles */
        .rank-circle {
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 14px;
            color: white;
            border: 2px solid white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 5;
        }

        .rank-circle.gold {
            background: #FDB931;
        }

        .rank-circle.silver {
            background: #A9A9A9;
        }

        .rank-circle.bronze {
            background: #A0522D;
        }

        /* 4. Podium Blocks (The Steps) */
        .podium-block {
            width: 100%;
            border-radius: 16px 16px 0 0;
            padding: 20px 10px;
            position: relative;
            margin: 0 5px;
            clip-path: polygon(0 0, 100% 0, 95% 100%, 5% 100%);
            /* Tapered shape */
            backdrop-filter: blur(5px);
        }

        .gold-block {
            height: 180px;
            /* Sedikit lebih tinggi untuk muat info tambahan */
            background: linear-gradient(180deg, rgba(255, 236, 179, 0.4) 0%, rgba(255, 255, 255, 0.1) 100%);
            border-top: 4px solid #FFD700;
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.15);
        }

        .silver-block {
            height: 140px;
            background: linear-gradient(180deg, rgba(245, 245, 245, 0.4) 0%, rgba(255, 255, 255, 0.1) 100%);
            border-top: 4px solid #C0C0C0;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }

        .bronze-block {
            height: 120px;
            background: linear-gradient(180deg, rgba(239, 219, 207, 0.4) 0%, rgba(255, 255, 255, 0.1) 100%);
            border-top: 4px solid #CD7F32;
            box-shadow: 0 10px 20px rgba(160, 82, 45, 0.05);
        }

        .podium-content {
            transform: translateY(5px);
        }

        .stat-pill {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: #fff;
            color: #555;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .stat-pill.gold {
            background: linear-gradient(45deg, #FFD700, #FDB931);
            color: #fff;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            box-shadow: 0 4px 10px rgba(255, 215, 0, 0.3);
        }

        /* 5. Runner Up List */
        .runner-up-container {
            background: #fff;
            border-radius: 16px;
            padding: 10px;
            /* border: 1px solid #f0f0f0; */
        }

        .runner-up-item {
            padding: 12px 16px;
            border-radius: 12px;
            background: #fff;
            border: 1px solid #f1f3f5;
            margin-bottom: 10px;
            transition: all 0.2s ease;
        }

        .runner-up-item:hover {
            border-color: #FFD700;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transform: translateX(5px);
        }

        .rank-number {
            font-family: 'Consolas', monospace;
            font-weight: 900;
            font-size: 18px;
            color: #d1d1d1;
            width: 30px;
        }

        .runner-avatar {
            width: 40px;
            height: 40px;
            object-fit: cover;
        }

        .runner-avatar-placeholder {
            width: 40px;
            height: 40px;
            background: #eee;
            color: #888;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* 6. Sparkles */
        .sparkle {
            position: absolute;
            background: white;
            border-radius: 50%;
            animation: twinkle 2s infinite;
            z-index: 20;
            box-shadow: 0 0 5px #fff, 0 0 10px #FFD700;
        }

        .s1 {
            width: 4px;
            height: 4px;
            top: 0;
            left: 10%;
            animation-delay: 0.5s;
        }

        .s2 {
            width: 6px;
            height: 6px;
            top: 10%;
            right: 0;
            animation-delay: 1s;
        }

        .s3 {
            width: 3px;
            height: 3px;
            bottom: 10px;
            left: -5px;
            animation-delay: 1.5s;
        }

        @keyframes twinkle {

            0%,
            100% {
                opacity: 0;
                transform: scale(0.5);
            }

            50% {
                opacity: 1;
                transform: scale(1.2);
            }
        }

        /* Small utilities */
        .small-font {
            font-size: 11px;
        }


        /* === EXISTING STYLES === */

        /* 1. Entrance Animation (Slide Up Fade) */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translate3d(0, 40px, 0);
            }

            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }

        .animate-enter {
            opacity: 0;
            /* Awal tersembunyi */
            animation: fadeInUp 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        }

        /* 2. Pulse Animation for Status Icon */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        .pulse-text {
            animation: pulseText 2s infinite;
        }

        @keyframes pulseText {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* 3. Live Indicator Dot */
        .live-indicator {
            width: 10px;
            height: 10px;
            background-color: #10b981;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 rgba(16, 185, 129, 0.4);
            animation: pulse 2s infinite;
        }

        /* 4. Hover Effects */
        .hover-float {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hover-float:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .hover-scale {
            transition: transform 0.2s ease;
        }

        .hover-scale:hover {
            transform: scale(1.02);
        }

        .scale-on-hover:hover {
            transform: scale(1.1);
        }

        .hover-shadow-lg {
            transition: box-shadow 0.3s ease;
        }

        .hover-shadow-lg:hover {
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
        }

        /* 5. Glassmorphism for Modal */
        .glass-effect {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        /* 6. Card Bank Styles */
        .card-bank {
            position: relative;
            min-height: 200px;
            border-radius: 16px;
            overflow: hidden;
            border: none;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .card-bank .card-body {
            position: relative;
            z-index: 2;
            padding: 24px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex-grow: 1;
            gap: 15px;
            background: rgba(255, 255, 255, 0.05);
        }

        .card-bank-chip {
            width: 40px;
            height: 30px;
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            border-radius: 6px;
            position: relative;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .card-bank-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 48px;
            opacity: 0.2;
        }

        .card-bank-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .card-bank-value {
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 8px;
            line-height: 1;
        }

        .card-bank-desc {
            font-size: 13px;
            opacity: 0.85;
            margin-bottom: 0;
        }

        .card-bank-pattern {
            position: absolute;
            bottom: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            z-index: 1;
        }

        /* Gradients */
        .gradient-purple {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .gradient-blue {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .gradient-green {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .gradient-orange {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .gradient-red {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .gradient-dark {
            background: linear-gradient(135deg, #2c3e50 0%, #000000 100%);
        }

        .card-id {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            color: white;
            min-height: 220px;
            display: flex;
            flex-direction: column;
            font-family: 'Roboto', sans-serif;
        }

        .card-id .card-body {
            position: relative;
            z-index: 2;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex-grow: 1;
            gap: 15px;
            background: rgba(255, 255, 255, 0.05);
        }

        .card-id-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-id-photo-wrapper {
            position: relative;
            z-index: 5;
        }

        .id-card-img {
            width: 60px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            transition: transform 0.2s ease-in-out;
        }

        .id-card-img:hover {
            transform: scale(1.1);
            border-color: #fff;
        }

        .id-card-img-placeholder {
            width: 60px;
            height: 70px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            border: 2px solid rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 24px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .card-id-logo {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            font-size: 10px;
            font-weight: 700;
            line-height: 1;
        }

        .card-id-logo i {
            font-size: 38px;
            margin-bottom: 4px;
            color: #ffed4e;
        }

        .card-id-details {
            flex-grow: 1;
        }

        .card-id-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            opacity: 0.7;
            margin-bottom: 4px;
            font-weight: 500;
        }

        .card-id-name {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
            line-height: 1.2;
            font-family: 'Consolas', 'Courier New', monospace;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .card-id-division {
            font-size: 16px;
            font-weight: 500;
            opacity: 0.9;
            font-family: 'Consolas', 'Courier New', monospace;
        }

        .card-id-footer {
            margin-top: auto;
        }

        .card-action {
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
        }

        .card-status {
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            height: 100%;
        }

        .status-card {
            padding: 24px;
            border-radius: 12px;
            border: 2px solid;
            background: #f8fafc;
            transition: all 0.3s ease;
        }

        .status-success {
            border-color: #10b981;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        }

        .status-warning {
            border-color: #f59e0b;
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        }

        .status-info {
            border-color: #3b82f6;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        }

        .status-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 28px;
            flex-shrink: 0;
        }

        .status-success .status-icon {
            background: #10b981;
            color: white;
        }

        .status-warning .status-icon {
            background: #f59e0b;
            color: white;
        }

        .status-info .status-icon {
            background: #3b82f6;
            color: white;
        }

        .badge {
            border-radius: 8px;
            font-weight: 600;
            padding: 6px 12px;
        }

        .btn {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .btn:active {
            transform: scale(0.95);
        }

        .btn-dark {
            background: #1a1a1a;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            padding: 12px 28px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-dark:hover {
            background: #000;
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-outline-dark {
            border: 2px solid #1a1a1a;
            color: #1a1a1a;
            border-radius: 12px;
            font-weight: 600;
            padding: 12px 28px;
        }

        .btn-outline-dark:hover {
            background: #1a1a1a;
            color: white;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .card-bank-value {
                font-size: 28px;
            }

            .card-bank {
                min-height: 180px;
                margin-bottom: 20px;
            }

            .card-id {
                min-height: 200px;
            }

            .card-id-name {
                font-size: 20px;
            }

            .id-card-img,
            .id-card-img-placeholder {
                width: 50px;
                height: 60px;
            }

            #greeting-text {
                font-size: 0.8rem;
            }

            h3.fw-bold {
                font-size: 1.5rem;
            }

            /* Responsive Podium */
            .luxury-avatar,
            .luxury-avatar-placeholder {
                width: 60px;
                height: 60px;
                font-size: 20px;
            }

            .main-winner .luxury-avatar,
            .main-winner .luxury-avatar-placeholder {
                width: 80px;
                height: 80px;
            }

            .crown-floating {
                top: -45px;
            }

            .crown-floating img {
                width: 40px;
            }

            .podium-block {
                height: auto;
                min-height: 80px;
            }
        }
    </style>
@endpush

@push('scripts')
    {{-- QRCode Lib & Chart --}}
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- [BARU] SLIDER LOGIC ---
            const track = document.getElementById('slide-track');
            const thumb = document.getElementById('slide-thumb');
            const sliderView = document.getElementById('slider-view');
            const cameraView = document.getElementById('camera-view');
            const actionsContainer = document.getElementById('cross-day-actions'); // Container tombol2

            if (track && thumb) {
                let isDragging = false;
                let startX;
                let trackWidth = track.clientWidth;
                let thumbWidth = thumb.clientWidth;
                let maxMove = trackWidth - thumbWidth - 8; // 8px padding adjustment

                // Init size update
                window.addEventListener('resize', () => {
                    trackWidth = track.clientWidth;
                    maxMove = trackWidth - thumbWidth - 8;
                });

                // Start
                const startDrag = (e) => {
                    isDragging = true;
                    startX = (e.type === 'touchstart') ? e.touches[0].clientX : e.clientX;
                    thumb.style.transition = 'none';
                };

                // Move
                const drag = (e) => {
                    if (!isDragging) return;

                    const clientX = (e.type === 'touchmove') ? e.touches[0].clientX : e.clientX;
                    const deltaX = clientX - startX;

                    // Limit 0 to maxMove
                    let moveX = Math.max(0, Math.min(deltaX, maxMove));
                    thumb.style.transform = `translateX(${moveX}px)`;

                    // Fade text based on percentage
                    const percentage = moveX / maxMove;
                    const text = track.querySelector('span');
                    if (text) text.style.opacity = 1 - percentage;
                };

                // End
                const endDrag = () => {
                    if (!isDragging) return;
                    isDragging = false;
                    thumb.style.transition = 'transform 0.2s ease-out';

                    const style = window.getComputedStyle(thumb);
                    const matrix = new DOMMatrix(style.transform);
                    const currentTranslateX = matrix.m41;

                    if (currentTranslateX > (maxMove * 0.8)) {
                        // SUCCESS
                        thumb.style.transform = `translateX(${maxMove}px)`;
                        finishSlide();
                    } else {
                        // RESET
                        thumb.style.transform = `translateX(0px)`;
                        const text = track.querySelector('span');
                        if (text) text.style.opacity = 0.75;
                    }
                };

                // Add Listeners
                thumb.addEventListener('mousedown', startDrag);
                thumb.addEventListener('touchstart', startDrag);

                document.addEventListener('mousemove', drag);
                document.addEventListener('touchmove', drag);

                document.addEventListener('mouseup', endDrag);
                document.addEventListener('touchend', endDrag);

                function finishSlide() {
                    // 1. UI Changes (Visual Feedback)
                    thumb.innerHTML = '<i class="mdi mdi-loading mdi-spin text-success fs-4"></i>'; // Loading icon
                    track.style.backgroundColor = '#d1fae5';

                    // 2. AJAX Request to Confirm Overtime
                    const attendanceId = "{{ $myAttendanceToday->id ?? 0 }}";

                    fetch(`/attendance/${attendanceId}/confirm-overtime`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            // Success
                            thumb.innerHTML = '<i class="mdi mdi-check text-success fs-4"></i>';

                            setTimeout(() => {
                                // Sembunyikan wrapper slider & tombol skip
                                const confirmationWrapper = document.getElementById(
                                    'confirmation-wrapper');
                                if (confirmationWrapper) confirmationWrapper.classList.add('d-none');

                                // Munculkan kamera
                                if (cameraView) cameraView.classList.remove('d-none');
                            }, 500);
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Gagal mengupdate status lembur. Silahkan refresh halaman.');
                        });
                }
            }

            // --- [FIX] LIVE CLOCK WITH BRANCH TIMEZONE ---
            function updateClock() {
                // Gunakan Timezone yang dikirim dari Controller
                const timeZone = "{{ $current_timezone }}";

                const now = new Date();
                const timeString = now.toLocaleTimeString('en-US', {
                    timeZone: timeZone, // Kunci utama: Pakai timezone cabang
                    hour12: false,
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });

                const clockElement = document.getElementById('realtime-clock');
                if (clockElement) clockElement.innerText = timeString;

                // Greeting logic (Sesuai jam lokal cabang)
                // Kita perlu ambil jam (0-23) dari string waktu lokal
                const localHour = parseInt(timeString.split(':')[0]);
                const greetingElement = document.getElementById('greeting-text');

                let greeting = 'Selamat Datang,';
                if (localHour >= 5 && localHour < 12) greeting = 'Selamat Pagi,';
                else if (localHour >= 12 && localHour < 15) greeting = 'Selamat Siang,';
                else if (localHour >= 15 && localHour < 18) greeting = 'Selamat Sore,';
                else greeting = 'Selamat Malam,';

                if (greetingElement) greetingElement.innerText = greeting;
            }

            setInterval(updateClock, 1000);
            updateClock(); // Run immediately

            // 2. [BARU] COUNT UP ANIMATION (Angka naik dari 0)
            const counters = document.querySelectorAll('.count-up');
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                const duration = 2000; // 2 detik
                const increment = target / (duration / 16); // 60fps

                let current = 0;
                const updateCounter = () => {
                    current += increment;
                    if (current < target) {
                        counter.innerText = Math.ceil(current);
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.innerText = target;
                    }
                };
                updateCounter();
            });

            // --- [BARU] BIRTHDAY COUNTDOWN SCRIPT ---
            @if (isset($birthdayData) && !$birthdayData['is_today'])
                const targetDate = new Date("{{ $birthdayData['date'] }}T00:00:00");

                function updateCountdown() {
                    const now = new Date();
                    const diff = targetDate - now;

                    if (diff <= 0) {
                        // Jika waktu habis (masuk jam 00:00 ultah), reload biar tampilan berubah jadi HARI H
                        location.reload();
                        return;
                    }

                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                    // Update DOM
                    if (document.getElementById('cd-days')) document.getElementById('cd-days').innerText = days;
                    if (document.getElementById('cd-hours')) document.getElementById('cd-hours').innerText = hours
                        .toString().padStart(2, '0');
                    if (document.getElementById('cd-minutes')) document.getElementById('cd-minutes').innerText =
                        minutes.toString().padStart(2, '0');
                    if (document.getElementById('cd-seconds')) document.getElementById('cd-seconds').innerText =
                        seconds.toString().padStart(2, '0');
                }

                setInterval(updateCountdown, 1000);
                updateCountdown();
            @endif

            // --- SCRIPT QR CODE ---
            @if (Auth::user()->qr_code_value)
                const qrValue = "{{ Auth::user()->qr_code_value }}";

                new QRCode(document.getElementById("dashboard-qrcode"), {
                    text: qrValue,
                    width: 64,
                    height: 64,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });

                var qrModal = document.getElementById('qrModal');
                qrModal.addEventListener('show.bs.modal', function(event) {
                    var qrContainer = document.getElementById('qrcode-modal-display');
                    qrContainer.innerHTML = '';
                    new QRCode(qrContainer, {
                        text: qrValue,
                        width: 256,
                        height: 256,
                    });
                });
            @endif

            // --- SCRIPT CHART ---
            const ctx = document.getElementById('attendancePieChart').getContext('2d');
            Chart.defaults.font.family = "'Inter', 'Helvetica', 'Arial', sans-serif";

            @if (auth()->user()->role == 'admin')
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Tepat Waktu', 'Terlambat', 'Pulang Cepat', 'Pending', 'Tidak Hadir'],
                        datasets: [{
                            data: [{{ $stats['on_time'] }}, {{ $stats['late'] }},
                                {{ $stats['early'] }}, {{ $stats['pending'] }},
                                {{ $stats['absent'] }}
                            ],
                            backgroundColor: ['#00d25b', '#ffab00', '#fc424a', '#0090e7',
                                '#8c94a3'
                            ],
                            borderWidth: 0,
                            hoverOffset: 10 // Efek hover keluar
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        },
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20
                                }
                            }
                        },
                        cutout: '75%'
                    }
                });
            @elseif (auth()->user()->role == 'audit')
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Terverifikasi', 'Pending', 'Terlambat'],
                        datasets: [{
                            data: [{{ $stats['verified'] }}, {{ $stats['pending'] }},
                                {{ $stats['late'] }}
                            ],
                            backgroundColor: ['#00d25b', '#ffab00', '#fc424a'],
                            borderWidth: 0,
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            @elseif (auth()->user()->role == 'security')
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Scan Masuk', 'Scan Pulang'],
                        datasets: [{
                            data: [{{ $stats['check_in_scans'] }},
                                {{ $stats['check_out_scans'] }}
                            ],
                            backgroundColor: ['#00d25b', '#0090e7'],
                            borderWidth: 0,
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '60%',
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            @else
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['Tepat Waktu', 'Terlambat', 'Pulang Cepat', 'Pending'],
                        datasets: [{
                            data: [{{ $stats['on_time'] }}, {{ $stats['late'] }},
                                {{ $stats['early'] }}, {{ $stats['pending'] }}
                            ],
                            backgroundColor: ['#00d25b', '#ffab00', '#fc424a', '#8c94a3'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            @endif

            // --- MODAL FOTO PROFIL ---
            var profilePhotoModal = document.getElementById('profilePhotoModal');
            if (profilePhotoModal) {
                profilePhotoModal.addEventListener('show.bs.modal', function(event) {
                    var button = event.relatedTarget;
                    var src = button.getAttribute('data-src');
                    var modalImg = document.getElementById('profileModalImageSrc');
                    modalImg.src = src;
                });
            }
        });

        // Optional: Confetti Effect Function (Placeholder)
        function confettiEffect() {
            alert("🎉 Happy Birthday! PStore wish you all the best! 🎉");
        }
    </script>
@endpush