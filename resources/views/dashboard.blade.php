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
    {{-- BAGIAN BARU: MENU CEPAT (QUICK ACTIONS)                                --}}
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

    {{-- [BARU] MODAL PREVIEW GALLERY --}}
    <div class="modal fade" id="galleryPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark border-0 overflow-hidden shadow-lg" style="border-radius: 20px;">
                <div class="modal-body p-0 position-relative">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                        style="z-index: 10;" data-bs-dismiss="modal"></button>
                    <img src="" id="galleryPreviewImg" class="img-fluid w-100"
                        style="min-height: 300px; object-fit: cover;">
                    <div class="p-4 text-white"
                        style="background: linear-gradient(transparent, rgba(0,0,0,0.9)); position: absolute; bottom: 0; left: 0; right: 0;">
                        <h5 id="galleryPreviewTitle" class="fw-bold mb-1"></h5>
                        <p id="galleryPreviewDate" class="small opacity-75 mb-0"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        /* === [BARU] NOSTALGIA GALLERY STYLES === */
        .gallery-scroll-container {
            display: flex;
            overflow-x: auto;
            padding: 5px;
            scrollbar-width: none;
            /* Firefox */
        }

        .gallery-scroll-container::-webkit-scrollbar {
            display: none;
        }

        /* Chrome/Safari */

        .gallery-card {
            width: 140px;
            height: 210px;
            position: relative;
            border-radius: 18px;
            overflow: hidden;
            background: #000;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .gallery-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.7;
            filter: grayscale(80%) sepia(30%);
            /* Efek Sedih/Nostalgia */
            transition: all 0.6s ease;
        }

        .gallery-card:hover .gallery-img {
            opacity: 1;
            filter: grayscale(0%) sepia(0%);
            transform: scale(1.1);
        }

        .gallery-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            font-size: 8px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 6px;
            color: white;
            text-transform: uppercase;
        }

        .gallery-date {
            position: absolute;
            bottom: 12px;
            right: 12px;
            color: white;
            font-size: 11px;
            font-weight: bold;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
        }

        .border-dashed {
            border: 2px dashed #dee2e6;
        }

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

        /* ======================================================================= */
        /* LUXURY HALL OF FAME STYLES (MODERN VERSION)                             */
        /* ======================================================================= */

        .hall-of-fame-card {
            background: linear-gradient(145deg, #0f0f0f 0%, #1a1a1a 100%) !important;
            border-radius: 24px !important;
            border: 1px solid rgba(255, 215, 0, 0.15) !important;
            position: relative;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4) !important;
        }

        /* Efek Cahaya Spotlight di Background */
        .spotlight {
            position: absolute;
            top: -20%;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            height: 140%;
            background: radial-gradient(circle at 50% 0%, rgba(255, 215, 0, 0.08) 0%, transparent 60%);
            pointer-events: none;
            z-index: 0;
        }

        .winner-memory-card {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            padding: 1.5rem !important;
            position: relative;
            overflow: hidden;
        }

        .winner-memory-card:hover {
            background: rgba(255, 255, 255, 0.07);
            transform: translateY(-10px) scale(1.02);
            border-color: rgba(255, 215, 0, 0.4);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5);
        }

        /* Efek Grayscale-Nostalgia yang Halus */
        .grayscale-memory {
            filter: grayscale(40%) contrast(1.1);
            /* Tidak terlalu gelap agar tetap modern */
            transition: all 0.5s ease;
            border: 3px solid rgba(255, 255, 255, 0.2);
        }

        .winner-memory-card:hover .grayscale-memory {
            filter: grayscale(0%) contrast(1);
            border-color: #ffd700;
            transform: rotate(3deg);
        }

        /* Rank Badge Modern */
        .rank-badge-mini {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            /* Bentuk kotak membulat lebih modern dari lingkaran */
            font-size: 13px;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            transform: rotate(12deg);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            color: #000;
        }

        .rank-badge-mini.gold {
            background: linear-gradient(135deg, #ffd700, #b8860b);
        }

        .rank-badge-mini.silver {
            background: linear-gradient(135deg, #e0e0e0, #757575);
        }

        .rank-badge-mini.bronze {
            background: linear-gradient(135deg, #cd7f32, #8b4513);
        }

        /* Tipografi Teks */
        .winner-memory-card h6 {
            font-size: 16px;
            letter-spacing: 0.5px;
            margin-top: 10px;
            color: #ffffff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }

        .winner-memory-card .text-warning {
            font-size: 11px !important;
            font-weight: 600;
            text-transform: uppercase;
            opacity: 0.8;
        }

        /* Badge Kehadiran (Pill) */
        .attendance-pill-custom {
            background: rgba(255, 215, 0, 0.1);
            color: #ffd700;
            border: 1px solid rgba(255, 215, 0, 0.2);
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
            margin-top: 15px;
            display: inline-block;
        }

        .winner-memory-card:hover .attendance-pill-custom {
            background: #ffd700;
            color: #000;
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
        {{-- [BARU] SCRIPT PREVIEW GALLERY --}}

        function previewGalleryImage(src, title, date) {
            const modal = new bootstrap.Modal(document.getElementById('galleryPreviewModal'));
            document.getElementById('galleryPreviewImg').src = src;
            document.getElementById('galleryPreviewTitle').innerText = title;
            document.getElementById('galleryPreviewDate').innerText = date;
            modal.show();
        }

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
