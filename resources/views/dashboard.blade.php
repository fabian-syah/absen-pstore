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
    </div>
@endsection

@section('content')

    {{-- BAGIAN BARU: BIRTHDAY CELEBRATION --}}
    @if (isset($birthdayData) && $birthdayData)
        <div class="row mb-4 animate-enter">
            <div class="col-12">
                <div class="card border-0 shadow-sm overflow-hidden birthday-card">
                    <div class="confetti-container"></div>
                    <div class="balloon b1"></div>
                    <div class="balloon b2"></div>
                    <div class="balloon b3"></div>

                    <div class="card-body position-relative z-index-1 py-5 px-4">
                        <div class="row align-items-center">
                            <div class="col-md-7 text-white">
                                @if ($birthdayData['is_today'])
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="badge bg-warning text-dark fw-bold me-2 pulse-animation">
                                            <i class="mdi mdi-cake-variant me-1"></i> SPECIAL DAY
                                        </span>
                                        <h2 class="fw-bold mb-0">HAPPY BIRTHDAY! 🎂</h2>
                                    </div>
                                    <p class="lead mb-2 text-white-50">
                                        Selamat Ulang Tahun yang ke-<strong>{{ $birthdayData['age_to_be'] }}</strong>,
                                        {{ Auth::user()->name }}!
                                    </p>
                                    <p class="small text-white-50 mb-0">Semoga panjang umur, sehat selalu, dan karir makin cemerlang di PStore!</p>
                                @else
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="badge bg-light text-primary fw-bold me-2">
                                            <i class="mdi mdi-calendar-star me-1"></i> UPCOMING
                                        </span>
                                        <h3 class="fw-bold mb-0 text-white">Counting Down to Your Day! 🎈</h3>
                                    </div>
                                    <p class="mb-0 text-white-50">
                                        Sebentar lagi kamu ulang tahun yang
                                        ke-<strong>{{ $birthdayData['age_to_be'] }}</strong>.
                                        Siapkan harapan terbaikmu!
                                    </p>
                                @endif
                            </div>

                            <div class="col-md-5 mt-4 mt-md-0 text-center text-md-end">
                                @if (!$birthdayData['is_today'])
                                    <div class="d-flex justify-content-center justify-content-md-end gap-2"
                                        id="birthday-countdown">
                                        <div class="countdown-box">
                                            <span class="d-block fw-bold fs-3"
                                                id="cd-days">{{ $birthdayData['days_left'] }}</span>
                                            <small class="text-uppercase" style="font-size: 9px;">Hari</small>
                                        </div>
                                        <div class="countdown-box">
                                            <span class="d-block fw-bold fs-3" id="cd-hours">00</span>
                                            <small class="text-uppercase" style="font-size: 9px;">Jam</small>
                                        </div>
                                        <div class="countdown-box">
                                            <span class="d-block fw-bold fs-3" id="cd-minutes">00</span>
                                            <small class="text-uppercase" style="font-size: 9px;">Menit</small>
                                        </div>
                                        <div class="countdown-box">
                                            <span class="d-block fw-bold fs-3 text-warning" id="cd-seconds">00</span>
                                            <small class="text-uppercase" style="font-size: 9px;">Detik</small>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center">
                                        <button class="btn btn-light text-danger fw-bold shadow-lg pulse-animation"
                                            onclick="confettiEffect()">
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

    {{-- ATTENDANCE WRAPPED (DECEMBER ONLY) --}}
    @if (\Carbon\Carbon::now()->month == 12)
        <div class="row mb-4 animate-enter">
            <div class="col-12">
                <div class="card bg-dark text-white shadow-sm border-0 overflow-hidden">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="me-3 d-none d-sm-block">
                                <i class="mdi mdi-sparkles text-warning display-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-warning mb-1">✨ Your {{ date('Y') }} Wrapped is Here!</h5>
                                <p class="mb-0 text-white-50 small">Lihat rangkuman perjalanan karirmu selama setahun ini.</p>
                            </div>
                        </div>
                        <a href="{{ route('attendance.recap') }}"
                            class="btn btn-outline-light rounded-pill fw-bold px-4 shadow-sm">
                            <i class="mdi mdi-play-circle-outline me-1"></i> Putar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ADMIN/AUDIT/SECURITY DASHBOARD STATISTICS --}}
    @if (auth()->user()->role == 'admin')
        <div class="row mb-4">
            <div class="col-md-3 grid-margin stretch-card animate-enter" style="animation-delay: 0.1s">
                <div class="card stat-card stat-card-purple border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="stat-label text-muted mb-2">Total User</p>
                                <h3 class="stat-value count-up" data-target="{{ $totalUsers }}">0</h3>
                                <p class="stat-desc text-muted small mb-0">Karyawan Aktif</p>
                            </div>
                            <div class="stat-icon bg-light rounded-3 p-3">
                                <i class="mdi mdi-account-multiple text-primary fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card animate-enter" style="animation-delay: 0.2s">
                <div class="card stat-card stat-card-blue border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="stat-label text-muted mb-2">Total Cabang</p>
                                <h3 class="stat-value count-up" data-target="{{ $totalBranches }}">0</h3>
                                <p class="stat-desc text-muted small mb-0">Cabang Terdaftar</p>
                            </div>
                            <div class="stat-icon bg-light rounded-3 p-3">
                                <i class="mdi mdi-office-building text-info fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card animate-enter" style="animation-delay: 0.3s">
                <div class="card stat-card stat-card-green border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="stat-label text-muted mb-2">Absensi Hari Ini</p>
                                <h3 class="stat-value count-up" data-target="{{ $attendancesToday }}">0</h3>
                                <p class="stat-desc text-muted small mb-0">Total absensi hari ini</p>
                            </div>
                            <div class="stat-icon bg-light rounded-3 p-3">
                                <i class="mdi mdi-calendar-check text-success fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card animate-enter" style="animation-delay: 0.4s">
                <div class="card stat-card stat-card-orange border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="stat-label text-muted mb-2">Perlu Verifikasi</p>
                                <h3 class="stat-value count-up" data-target="{{ $pendingVerifications }}">0</h3>
                                <p class="stat-desc text-muted small mb-0">Menunggu persetujuan</p>
                            </div>
                            <div class="stat-icon bg-light rounded-3 p-3">
                                <i class="mdi mdi-alert-circle-outline text-warning fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif (auth()->user()->role == 'audit')
        <div class="row mb-4">
            <div class="col-md-4 grid-margin stretch-card animate-enter" style="animation-delay: 0.1s">
                <div class="card stat-card stat-card-red border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="stat-label text-muted mb-2">Verif Absensi</p>
                                <h3 class="stat-value count-up" data-target="{{ $pendingVerifications }}">0</h3>
                                <p class="stat-desc text-muted small mb-0">Absensi pending (Foto/Lokasi)</p>
                                <a href="{{ route('audit.verify.list') }}" class="btn btn-sm btn-light mt-3 shadow-sm">
                                    <i class="mdi mdi-clipboard-check me-1"></i>Lihat Daftar
                                </a>
                            </div>
                            <div class="stat-icon bg-light rounded-3 p-3">
                                <i class="mdi mdi-alert-circle-outline text-danger fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 grid-margin stretch-card animate-enter" style="animation-delay: 0.2s">
                <div class="card stat-card stat-card-blue border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="stat-label text-muted mb-2">Approve Izin</p>
                                <h3 class="stat-value count-up" data-target="{{ $pendingLeaves }}">0</h3>
                                <p class="stat-desc text-muted small mb-0">Izin, Sakit, Cuti, WFH, Telat</p>
                                <a href="{{ route('leave-requests.index') }}" class="btn btn-sm btn-light mt-3 shadow-sm">
                                    <i class="mdi mdi-playlist-check me-1"></i>Lihat Pengajuan
                                </a>
                            </div>
                            <div class="stat-icon bg-light rounded-3 p-3">
                                <i class="mdi mdi-file-document-edit-outline text-info fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 grid-margin stretch-card animate-enter" style="animation-delay: 0.3s">
                <div class="card stat-card stat-card-green border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="stat-label text-muted mb-2">Hadir Hari Ini</p>
                                <h3 class="stat-value count-up" data-target="{{ $attendancesToday }}">0</h3>
                                <p class="stat-desc text-muted small mb-0">Total kehadiran di cabang Anda</p>
                            </div>
                            <div class="stat-icon bg-light rounded-3 p-3">
                                <i class="mdi mdi-calendar-check text-success fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif (auth()->user()->role == 'security')
        <div class="row mb-4">
            <div class="col-md-6 grid-margin stretch-card animate-enter" style="animation-delay: 0.1s">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="mdi mdi-qrcode-scan display-1 text-primary"></i>
                        </div>
                        <h5 class="card-title mb-2 fw-bold">Pindai QR User</h5>
                        <p class="text-muted mb-4 small">Arahkan kamera ke QR Code user untuk melakukan absensi.</p>
                        <a href="{{ route('security.scan') }}" class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm">
                            <i class="mdi mdi-camera-enhance me-2"></i>Mulai Memindai
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 grid-margin stretch-card animate-enter" style="animation-delay: 0.2s">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="stat-label text-muted mb-2">Pindaian Hari Ini</p>
                                <h3 class="stat-value count-up" data-target="{{ $myScansToday }}">0</h3>
                                <p class="stat-desc text-muted small">Total pindaian QR hari ini</p>
                            </div>
                            <div class="stat-icon bg-light rounded-3 p-3">
                                <i class="mdi mdi-chart-bar text-info fs-5"></i>
                            </div>
                        </div>
                        <hr class="my-3">
                        <p class="stat-label text-muted mb-2">User Aktif</p>
                        <h3 class="stat-value count-up" data-target="{{ $totalUsers }}">0</h3>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- HALL OF FAME (LAST MONTH WINNERS) --}}
    @if (isset($lastMonthWinners) && $lastMonthWinners->count() > 0)
        <div class="row mb-5 animate-enter" style="animation-delay: 0.3s">
            <div class="col-12">
                <div class="card border-0 shadow-sm overflow-hidden hall-of-fame-card">
                    <div class="card-body p-5">
                        <div class="text-center mb-5">
                            <span class="badge bg-warning text-dark fw-bold mb-2 shadow-sm">HALL OF FAME</span>
                            <h3 class="fw-bold text-dark mb-1">
                                <i class="mdi mdi-auto-fix text-warning me-2"></i>Pahlawan Absensi {{ $lastMonthName }}
                            </h3>
                            <p class="text-muted small">Apresiasi untuk dedikasi luar biasa di bulan lalu</p>
                        </div>

                        <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center">
                            @foreach ($lastMonthWinners as $history)
                                <div class="col">
                                    <div class="winner-card text-center p-4 h-100">
                                        <div class="position-relative mb-3 mt-2">
                                            <div class="rank-badge {{ $loop->first ? 'gold' : ($loop->iteration == 2 ? 'silver' : 'bronze') }}">
                                                #{{ $loop->iteration }}
                                            </div>

                                            @if ($history->user->profile_photo_path)
                                                <img src="{{ asset('storage/' . $history->user->profile_photo_path) }}"
                                                    class="rounded-circle shadow border border-3 border-white"
                                                    style="width: 90px; height: 90px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white shadow"
                                                    style="width: 90px; height: 90px; font-size: 28px;">
                                                    {{ substr($history->user->name, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>

                                        <h6 class="text-dark fw-bold mb-1">{{ Str::limit($history->user->name, 15) }}</h6>
                                        <p class="text-muted small mb-3" style="font-size: 11px; letter-spacing: 0.5px;">
                                            {{ strtoupper($history->user->division->name ?? 'Staff') }}
                                        </p>

                                        <div class="mt-auto">
                                            <span class="badge bg-light text-dark py-2 px-3"
                                                style="font-size: 12px; border-radius: 20px;">
                                                <i class="mdi mdi-calendar-check me-1"></i>{{ $history->total_attendance }}
                                                Hari
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- LEADERBOARD PODIUM (TOP 3) --}}
    <div class="row mb-5 animate-enter" style="animation-delay: 0.4s">
        @if (auth()->user()->role != 'security' && isset($leaderboard) && count($leaderboard) > 0)
            <div class="col-12">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-body p-5">
                        <div class="text-center mb-5">
                            <h4 class="fw-bold text-dark mb-1">
                                <i class="mdi mdi-trophy text-warning me-2"></i>Top Rajin Absen
                            </h4>
                            <p class="text-muted small">
                                {{ auth()->user()->role == 'admin' ? 'Global' : 'Cabang Anda' }} - Bulan
                                {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}
                            </p>
                        </div>

                        <div class="podium-container d-flex justify-content-center align-items-end gap-4">
                            {{-- RANK 2 (LEFT) --}}
                            @if (isset($leaderboard[1]))
                                <div class="podium-item animate-enter" style="animation-delay: 0.6s">
                                    <div class="podium-avatar-wrapper">
                                        @if ($leaderboard[1]->user->profile_photo_path)
                                            <img src="{{ asset('storage/' . $leaderboard[1]->user->profile_photo_path) }}"
                                                class="podium-avatar">
                                        @else
                                            <div class="podium-avatar-placeholder bg-secondary text-white">
                                                {{ substr($leaderboard[1]->user->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="rank-number silver">2</div>
                                    </div>
                                    <div class="podium-block podium-silver text-center">
                                        <h6 class="fw-bold text-dark mb-1">
                                            {{ Str::limit($leaderboard[1]->user->name, 14) }}</h6>
                                        <p class="small text-muted mb-2">
                                            {{ $leaderboard[1]->user->division->name ?? '-' }}</p>
                                        <div class="badge bg-light text-dark small">
                                            {{ $leaderboard[1]->total_attendance }} Hari
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- RANK 1 (CENTER) --}}
                            @if (isset($leaderboard[0]))
                                <div class="podium-item main-winner animate-enter" style="animation-delay: 0.5s">
                                    <div class="podium-avatar-wrapper gold-glow">
                                        <div class="crown-icon">👑</div>
                                        @if ($leaderboard[0]->user->profile_photo_path)
                                            <img src="{{ asset('storage/' . $leaderboard[0]->user->profile_photo_path) }}"
                                                class="podium-avatar podium-avatar-lg">
                                        @else
                                            <div class="podium-avatar-placeholder gold-bg text-white">
                                                {{ substr($leaderboard[0]->user->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="rank-number gold">1</div>
                                    </div>
                                    <div class="podium-block podium-gold text-center">
                                        <h5 class="fw-bold text-dark mb-1">
                                            {{ Str::limit($leaderboard[0]->user->name, 16) }}</h5>
                                        <p class="small text-muted mb-2">
                                            {{ $leaderboard[0]->user->division->name ?? '-' }}</p>
                                        <div class="badge bg-warning text-dark small fw-bold">
                                            <i class="mdi mdi-star me-1"></i>{{ $leaderboard[0]->total_attendance }}
                                            Kehadiran
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- RANK 3 (RIGHT) --}}
                            @if (isset($leaderboard[2]))
                                <div class="podium-item animate-enter" style="animation-delay: 0.7s">
                                    <div class="podium-avatar-wrapper">
                                        @if ($leaderboard[2]->user->profile_photo_path)
                                            <img src="{{ asset('storage/' . $leaderboard[2]->user->profile_photo_path) }}"
                                                class="podium-avatar">
                                        @else
                                            <div class="podium-avatar-placeholder bg-bronze text-white">
                                                {{ substr($leaderboard[2]->user->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="rank-number bronze">3</div>
                                    </div>
                                    <div class="podium-block podium-bronze text-center">
                                        <h6 class="fw-bold text-dark mb-1">
                                            {{ Str::limit($leaderboard[2]->user->name, 14) }}</h6>
                                        <p class="small text-muted mb-2">
                                            {{ $leaderboard[2]->user->division->name ?? '-' }}</p>
                                        <div class="badge bg-light text-dark small">
                                            {{ $leaderboard[2]->total_attendance }} Hari
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- TOP SECURITY SCANNERS --}}
        @if (
            (auth()->user()->role == 'admin' || auth()->user()->role == 'security') &&
                isset($topScanners) &&
                count($topScanners) > 0)
            <div class="col-12 mt-4">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-body p-5">
                        <div class="text-center mb-5">
                            <h4 class="fw-bold text-dark mb-1">
                                <i class="mdi mdi-qrcode-scan text-primary me-2"></i>Top Security Scanner
                            </h4>
                            <p class="text-muted small">Total Scan Terbanyak Bulan Ini</p>
                        </div>

                        <div class="podium-container d-flex justify-content-center align-items-end gap-4">
                            {{-- RANK 2 --}}
                            @if (isset($topScanners[1]))
                                <div class="podium-item">
                                    <div class="podium-avatar-wrapper">
                                        @if ($topScanners[1]->profile_photo_path)
                                            <img src="{{ asset('storage/' . $topScanners[1]->profile_photo_path) }}"
                                                class="podium-avatar">
                                        @else
                                            <div class="podium-avatar-placeholder bg-secondary text-white">
                                                {{ substr($topScanners[1]->name, 0, 1) }}</div>
                                        @endif
                                        <div class="rank-number silver">2</div>
                                    </div>
                                    <div class="podium-block podium-silver text-center">
                                        <h6 class="fw-bold text-dark mb-1">{{ Str::limit($topScanners[1]->name, 14) }}
                                        </h6>
                                        <div class="badge bg-light text-dark small">
                                            {{ $topScanners[1]->total_scans }} Scan
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- RANK 1 --}}
                            @if (isset($topScanners[0]))
                                <div class="podium-item main-winner">
                                    <div class="podium-avatar-wrapper gold-glow">
                                        <div class="crown-icon">👑</div>
                                        @if ($topScanners[0]->profile_photo_path)
                                            <img src="{{ asset('storage/' . $topScanners[0]->profile_photo_path) }}"
                                                class="podium-avatar podium-avatar-lg">
                                        @else
                                            <div class="podium-avatar-placeholder gold-bg text-white">
                                                {{ substr($topScanners[0]->name, 0, 1) }}</div>
                                        @endif
                                        <div class="rank-number gold">1</div>
                                    </div>
                                    <div class="podium-block podium-gold text-center">
                                        <h5 class="fw-bold text-dark mb-1">{{ Str::limit($topScanners[0]->name, 16) }}
                                        </h5>
                                        <div class="badge bg-warning text-dark small fw-bold">
                                            <i class="mdi mdi-star me-1"></i>{{ $topScanners[0]->total_scans }} Scan
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- RANK 3 --}}
                            @if (isset($topScanners[2]))
                                <div class="podium-item">
                                    <div class="podium-avatar-wrapper">
                                        @if ($topScanners[2]->profile_photo_path)
                                            <img src="{{ asset('storage/' . $topScanners[2]->profile_photo_path) }}"
                                                class="podium-avatar">
                                        @else
                                            <div class="podium-avatar-placeholder bg-bronze text-white">
                                                {{ substr($topScanners[2]->name, 0, 1) }}</div>
                                        @endif
                                        <div class="rank-number bronze">3</div>
                                    </div>
                                    <div class="podium-block podium-bronze text-center">
                                        <h6 class="fw-bold text-dark mb-1">{{ Str::limit($topScanners[2]->name, 14) }}
                                        </h6>
                                        <div class="badge bg-light text-dark small">
                                            {{ $topScanners[2]->total_scans }} Scan
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

    {{-- GALLERY SECTION --}}
    <div class="row mt-4 mb-5 animate-enter" style="animation-delay: 0.5s">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between mb-4 px-2">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="mdi mdi-camera-iris text-danger me-2"></i>Lembaran Cerita {{ $currentMonthName }}
                    </h5>
                    <p class="text-muted small mb-0">Mengenang setiap tetes keringat dan senyummu bulan ini.</p>
                </div>
            </div>
            <div class="gallery-scroll-container">
                <div class="d-flex gap-3 pb-3">
                    @forelse ($attendanceGallery as $item)
                        @if ($item->photo_path)
                            <div class="gallery-item-wrapper">
                                <div class="gallery-card shadow-sm"
                                    onclick="previewGalleryImage('{{ Storage::url($item->photo_path) }}', 'Momen Masuk Kerja', '{{ $item->check_in_time->translatedFormat('l, d F Y - H:i') }}')">
                                    <img src="{{ Storage::url($item->photo_path) }}" class="gallery-img">
                                    <div class="gallery-badge bg-success">MASUK</div>
                                    <div class="gallery-date">{{ $item->check_in_time->format('d M') }}</div>
                                </div>
                            </div>
                        @endif

                        @if ($item->photo_out_path)
                            <div class="gallery-item-wrapper">
                                <div class="gallery-card shadow-sm"
                                    onclick="previewGalleryImage('{{ Storage::url($item->photo_out_path) }}', 'Momen Pulang Kerja', '{{ $item->check_out_time ? $item->check_out_time->translatedFormat('l, d F Y - H:i') : '-' }}')">
                                    <img src="{{ Storage::url($item->photo_out_path) }}" class="gallery-img">
                                    <div class="gallery-badge bg-danger">PULANG</div>
                                    <div class="gallery-date">{{ $item->check_in_time->format('d M') }}</div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="col-12 text-center py-5 bg-light rounded-3 border-dashed w-100"
                            style="min-width: 300px;">
                            <i class="mdi mdi-image-filter-hdr display-4 text-muted opacity-25"></i>
                            <p class="text-muted mt-2">Belum ada potret cerita untuk bulan ini...</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- PERSONAL ATTENDANCE SECTION --}}
    <div class="row animate-enter" style="animation-delay: 0.5s">
        <div class="col-12">
            <h5 class="fw-bold mb-4 text-dark"><i class="mdi mdi-account-circle me-2"></i>Absensi Pribadi</h5>
        </div>
    </div>

    <div class="row animate-enter" style="animation-delay: 0.6s">
        {{-- ID CARD & QR CODE --}}
        <div class="col-md-5 grid-margin stretch-card mb-4 mb-md-0">
            <div class="row w-100 m-0 p-0">
                {{-- ID CARD --}}
                <div class="col-12 mb-3">
                    <div class="card id-card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div class="id-photo-wrapper">
                                    @if (Auth::user()->profile_photo_path)
                                        <img src="{{ Storage::url(Auth::user()->profile_photo_path) }}" alt="Profile"
                                            class="id-photo" data-bs-toggle="modal"
                                            data-bs-target="#profilePhotoModal"
                                            data-src="{{ Storage::url(Auth::user()->profile_photo_path) }}"
                                            title="Klik untuk memperbesar">
                                    @else
                                        <div class="id-photo-placeholder text-white fw-bold">
                                            {{ substr(Auth::user()->name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <i class="mdi mdi-credit-card-outline text-white" style="font-size: 28px;"></i>
                                    <p class="small text-white-50 mb-0">ID Card</p>
                                </div>
                            </div>
                            <div class="mb-4">
                                <p class="id-label">NAMA</p>
                                <h4 class="id-name text-truncate text-white fw-bold">{{ strtoupper(Auth::user()->name) }}</h4>
                                <p class="id-label mt-3 mb-2">DIVISI</p>
                                <h5 class="id-division text-white-50">
                                    {{ strtoupper(Auth::user()->division->name ?? 'BELUM ADA DIVISI') }}
                                </h5>
                            </div>
                            <div class="text-end pt-3 border-top border-white-50">
                                <p class="mb-0 text-white-50 small">NOMOR ID</p>
                                <p class="id-number text-white fw-bold mb-0">
                                    {{ $idCardNumber ?? '000000 000000' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- QR CODE CARD --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="fw-bold mb-1">QR Code Absensi</h6>
                                <p class="text-muted small mb-0">Klik QR untuk memperbesar</p>
                            </div>
                            <div class="bg-light p-3 rounded shadow-sm cursor-pointer" id="dashboard-qrcode"
                                data-bs-toggle="modal" data-bs-target="#qrModal">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ATTENDANCE STATUS --}}
        <div class="col-md-7 grid-margin stretch-card">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h5 class="fw-bold mb-1">
                                <i class="mdi mdi-calendar-today me-2"></i>Status Absensi
                            </h5>
                            <span class="badge bg-light text-dark border shadow-sm small">
                                <i class="mdi mdi-clock-outline me-1"></i>Jadwal: {{ $todaySchedule }}
                            </span>
                        </div>
                        <div class="text-end">
                            <h5 class="fw-bold mb-1 text-primary font-monospace" id="realtime-clock">--:--:--</h5>
                            <small class="text-muted d-block">
                                {{ \Carbon\Carbon::now($current_timezone)->translatedFormat('l, d F Y') }}
                            </small>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3">
                            <i class="mdi mdi-check-circle-outline me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3">
                            <i class="mdi mdi-alert-circle-outline me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show shadow-sm mb-3">
                            <i class="mdi mdi-alert-outline me-2"></i>
                            {{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- STATUS LOGIC --}}
                    @if ($myAttendanceToday)
                        @php
                            $isCrossDay = false;
                            if (!$myAttendanceToday->check_out_time) {
                                $isCrossDay =
                                    $myAttendanceToday->check_in_time->format('Y-m-d') !==
                                    \Carbon\Carbon::now($current_timezone)->format('Y-m-d');
                            }
                            $sourceLabel =
                                $myAttendanceToday->attendance_type == 'scan' ? 'Security Scan' : 'Selfie Mandiri';
                        @endphp

                        @if ($myAttendanceToday->check_out_time || $myAttendanceToday->photo_out_path)
                            <div class="status-box status-success mb-3">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="status-icon bg-success text-white rounded-circle p-3 me-3">
                                        <i class="mdi mdi-home-variant"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0">Anda Sudah Pulang</h6>
                                        <p class="text-muted small mb-0">Terima kasih atas kerja keras Anda!</p>
                                    </div>
                                </div>
                                <div class="row text-center g-0">
                                    <div class="col-6 border-end">
                                        <small class="text-muted d-block">JAM MASUK</small>
                                        <h5 class="fw-bold text-success mb-0">
                                            {{ $myAttendanceToday->check_in_time->format('H:i') }}
                                        </h5>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">JAM PULANG</small>
                                        <h5 class="fw-bold text-primary mb-0">
                                            {{ $myAttendanceToday->check_out_time ? $myAttendanceToday->check_out_time->format('H:i') : '-' }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="status-box {{ $isCrossDay ? 'status-warning' : 'status-success' }} mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="status-icon {{ $isCrossDay ? 'bg-warning' : 'bg-success' }} text-white rounded-circle p-3 me-3 pulse-animation">
                                        <i class="mdi {{ $isCrossDay ? 'mdi-clock-alert-outline' : 'mdi-clock-check' }}"></i>
                                    </div>
                                    <div>
                                        @if ($isCrossDay)
                                            <h6 class="fw-bold mb-1 text-danger">Lembur Lintas Hari</h6>
                                            <p class="text-muted small mb-0">
                                                Masuk: {{ $myAttendanceToday->check_in_time->format('d M, H:i') }}
                                            </p>
                                        @else
                                            <h6 class="fw-bold mb-0">Sedang Bekerja</h6>
                                            <p class="text-muted small mb-0">
                                                Masuk: <strong>{{ $myAttendanceToday->check_in_time->format('H:i') }}</strong> via {{ $sourceLabel }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                @if ($isCrossDay)
                                    <div class="mt-4 pt-3 border-top">
                                        <div id="cross-day-actions">
                                            @if (!$myAttendanceToday->is_extended_shift)
                                                <div id="confirmation-wrapper">
                                                    <div class="mb-3" id="slider-view">
                                                        <p class="text-muted small mb-2">
                                                            <i class="mdi mdi-camera me-1"></i><strong>Opsi 1:</strong>
                                                            Absen Pulang Normal (Foto)
                                                        </p>
                                                        <div class="position-relative w-100 rounded-pill d-flex align-items-center px-2 user-select-none shadow-sm bg-warning"
                                                            id="slide-track" style="height: 50px;">
                                                            <div class="position-absolute w-100 text-center" style="pointer-events: none;">
                                                                <span class="fw-bold text-dark small">GESER KE KANAN >></span>
                                                            </div>
                                                            <div id="slide-thumb"
                                                                class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center text-warning"
                                                                style="width: 42px; height: 42px; cursor: pointer; position: absolute; left: 4px; z-index: 10;">
                                                                <i class="mdi mdi-arrow-right fw-bold"></i>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <form action="{{ route('self.attend.skip', $myAttendanceToday->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <p class="text-muted small mb-2">
                                                            <i class="mdi mdi-cancel me-1"></i><strong>Opsi 2:</strong>
                                                            Lupa Absen (Tanpa Foto)
                                                        </p>
                                                        <button type="submit"
                                                            class="btn btn-outline-danger w-100 py-2 shadow-sm"
                                                            onclick="return confirm('Pilih ini jika Anda LUPA absen pulang kemarin.\nSesi akan ditutup otomatis TANPA FOTO.\n\nLanjutkan?');">
                                                            <i class="mdi mdi-skip-forward me-2"></i>Lewati & Tutup Sesi
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif

                                            <div id="camera-view"
                                                class="{{ $myAttendanceToday->is_extended_shift ? '' : 'd-none' }} text-center mt-3">
                                                <h6 class="text-primary fw-bold mb-2">Konfirmasi Pulang</h6>
                                                <p class="text-muted small mb-3">Silahkan ambil foto selfie untuk validasi.</p>
                                                <a href="{{ route('self.attend.create', ['attendance_id' => $myAttendanceToday->id, 'mode' => 'pulang']) }}"
                                                    class="btn btn-primary w-100 py-2 rounded-3 shadow-sm fw-bold">
                                                    <i class="mdi mdi-camera-party-mode me-2"></i> Ambil Foto & Pulang
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-4 pt-3 border-top">
                                        @if (Auth::user()->only_security_scan)
                                            <button class="btn btn-secondary btn-sm w-100 shadow-sm" disabled>
                                                <i class="mdi mdi-lock me-1"></i> Absen Pulang Mandiri Dikunci
                                            </button>
                                            <small class="text-danger d-block text-center mt-2 small">
                                                Silahkan Scan QR Code ke Security untuk Pulang
                                            </small>
                                        @else
                                            <a href="{{ route('self.attend.create', ['attendance_id' => $myAttendanceToday->id, 'mode' => 'pulang']) }}"
                                                class="btn btn-danger btn-sm w-100 shadow-sm">
                                                <i class="mdi mdi-logout me-1"></i>
                                                Absen Pulang Mandiri
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    @elseif($myPendingLeave)
                        <div class="status-box status-warning mb-3">
                            <div class="text-center py-4">
                                <div class="mb-3">
                                    <i class="mdi mdi-timer-sand display-4 text-warning pulse-animation"></i>
                                </div>
                                <h5 class="fw-bold mb-2">Menunggu Approve dari Audit</h5>
                                <p class="text-muted small mb-3">
                                    Pengajuan <strong>{{ strtoupper($myPendingLeave->type) }}</strong> Anda sedang diproses.
                                </p>
                                <div class="bg-light p-3 rounded border small mb-3">
                                    <em>"{{ $myPendingLeave->reason }}"</em>
                                </div>
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
                    @elseif(isset($myLeaveToday) && $myLeaveToday && $myLeaveToday->status == 'approved')
                        <div class="status-box status-success mb-3">
                            <div class="d-flex align-items-start mb-3">
                                <div class="status-icon bg-success text-white rounded-circle p-3 me-3">
                                    <i class="mdi mdi-check-decagram"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0">Pengajuan Disetujui</h6>
                                    <p class="text-muted small mb-0">
                                        Status: <strong>{{ strtoupper($myLeaveToday->type) }}</strong>
                                    </p>
                                </div>
                            </div>
                            <div class="bg-light p-3 rounded border small mb-3">
                                <em>"{{ $myLeaveToday->reason }}"</em>
                            </div>
                            @if ($myLeaveToday->file_proof)
                                <button type="button" class="btn btn-sm btn-light border shadow-sm mb-3"
                                    onclick="window.open('{{ Storage::url($myLeaveToday->file_proof) }}', '_blank')">
                                    <i class="mdi mdi-image-area me-1"></i>Lihat Bukti
                                </button>
                            @endif
                            <div class="pt-3 border-top text-center">
                                @if ($myLeaveToday->type === 'telat')
                                    <p class="small text-muted mb-2">Anda sudah sampai kantor?</p>
                                    <a href="{{ route('self.attend.create') }}"
                                        class="btn btn-primary btn-sm w-100 shadow-sm">
                                        <i class="mdi mdi-camera-account me-2"></i> Lakukan Absen Masuk
                                    </a>
                                @else
                                    <p class="small text-muted mb-2">Berubah pikiran atau sudah sampai kantor?</p>
                                    <form action="{{ route('leave-requests.finish-early', $myLeaveToday->id) }}"
                                        method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100 shadow-sm"
                                            onclick="return confirm('Apakah Anda yakin? Status izin hari ini akan dibatalkan.');">
                                            <i class="mdi mdi-map-marker-radius me-2"></i>Batalkan Izin & Absen Masuk
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @else
                        @if (isset($justFinishedOvertime) && $justFinishedOvertime)
                            <div class="status-box status-info mb-3">
                                <div class="text-center py-4">
                                    <div class="mb-3">
                                        <i class="mdi mdi-bed-clock display-4 text-info"></i>
                                    </div>
                                    <h5 class="fw-bold mb-2">Selamat Beristirahat!</h5>
                                    <p class="text-muted small mb-3">
                                        Anda baru saja pulang lembur pukul
                                        <strong>{{ $lastOvertimeSession->check_out_time->format('H:i') }}</strong>.
                                    </p>
                                    @if (Auth::user()->only_security_scan)
                                        <button class="btn btn-secondary btn-sm w-100" disabled>
                                            <i class="mdi mdi-lock me-1"></i> Absen Mandiri Dikunci
                                        </button>
                                    @else
                                        <a href="{{ route('self.attend.create') }}"
                                            class="btn btn-outline-info btn-sm shadow-sm">
                                            <i class="mdi mdi-fingerprint me-2"></i>Absen Shift Baru
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="status-box status-info">
                                <div class="text-center py-4">
                                    <div class="mb-3">
                                        <i class="mdi mdi-clock-alert display-4 text-primary"></i>
                                    </div>
                                    <h5 class="fw-bold mb-2">Anda Belum Absen Hari Ini</h5>
                                    <p class="text-muted small mb-4">Gunakan fitur ini jika Anda bekerja WFH atau Dinas Luar.</p>
                                    <div class="d-flex gap-2">
                                        @if (Auth::user()->only_security_scan)
                                            <div class="w-100">
                                                <button class="btn btn-secondary w-100 shadow-sm" disabled>
                                                    <i class="mdi mdi-lock me-1"></i> Absen Mandiri Dikunci
                                                </button>
                                                <small class="text-danger d-block text-center mt-2 small">
                                                    <i class="mdi mdi-alert-circle"></i> Wajib Scan QR ke Security
                                                </small>
                                            </div>
                                        @else
                                            <a href="{{ route('self.attend.create') }}"
                                                class="btn btn-dark shadow flex-grow-1">
                                                <i class="mdi mdi-fingerprint me-2"></i>Absen Mandiri
                                            </a>
                                            <a href="{{ route('leave-requests.create') }}"
                                                class="btn btn-outline-dark shadow-sm flex-grow-1">
                                                <i class="mdi mdi-file-document-edit-outline me-2"></i>Izin/Sakit
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- QUICK ACTIONS --}}
    <div class="row animate-enter mb-4" style="animation-delay: 0.7s">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between">
                        <div class="d-flex align-items-center mb-3 mb-md-0">
                            <div class="bg-light rounded-circle p-3 me-3 shadow-sm">
                                <i class="mdi mdi-lightning-bolt text-warning"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Menu Cepat</h6>
                                <small class="text-muted">Butuh izin untuk hari lain? Ajukan di sini.</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2 w-100 w-md-auto">
                            <a href="{{ route('leave-requests.create') }}"
                                class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold flex-grow-1 flex-md-grow-0">
                                <i class="mdi mdi-file-document-edit-outline me-2"></i> Ajukan Izin / Sakit
                            </a>
                            <a href="{{ route('attendance.history') }}"
                                class="btn btn-outline-secondary rounded-pill px-4 shadow-sm fw-bold flex-grow-1 flex-md-grow-0">
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
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="mdi mdi-chart-pie me-2"></i>Statistik Absensi</h6>
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
                                <p class="mt-2 small">Analisis data kehadiran secara realtime</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PROFILE PHOTO --}}
    <div class="modal fade" id="profilePhotoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body p-0 position-relative text-center">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3 shadow"
                        data-bs-dismiss="modal"></button>
                    <div class="p-4">
                        <img src="" id="profileModalImageSrc" class="img-fluid rounded shadow-lg"
                            style="max-height: 80vh; object-fit: contain;">
                    </div>
