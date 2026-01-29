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
        <div class="text-end d-none d-md-block">
            @if(in_array(Auth::user()->role, ['audit', 'admin']))
                <a href="{{ route('test.notification') }}" class="btn btn-sm btn-info text-white me-2">
                    <i class="mdi mdi-bell-ring"></i> Test Notif
                </a>
            @endif
            <h5 class="fw-bold mb-0 text-primary" id="header-clock">--:--:--</h5>
            <small class="text-muted">{{ \Carbon\Carbon::now($current_timezone)->translatedFormat('l, d F Y') }}</small>
        </div>
    </div>
@endsection

@section('content')

    {{-- ======================================================================= --}}
    {{-- BAGIAN BARU: BIRTHDAY CELEBRATION (Hanya Muncul Jika H-30 atau Hari H) --}}
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
                                        Selamat Ulang Tahun yang ke-<strong>{{ $birthdayData['age_to_be'] }}</strong>,
                                        {{ Auth::user()->name }}!
                                    </p>
                                    <p class="small text-white-50 mb-0">Semoga panjang umur, sehat selalu, dan karir makin
                                        cemerlang di PStore!</p>
                                @else
                                    <div class="d-flex align-items-center mb-2">
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

    {{-- ======================================================================= --}}
    {{-- [UPGRADED] RAMADHAN COUNTDOWN: THE ROYAL ISLAMIC EDITION --}}
    {{-- ======================================================================= --}}
    @if (isset($ramadanData) && $ramadanData)
        <div class="row mb-5 animate-enter" style="animation-delay: 0.1s">
            <div class="col-12">
                <div class="card border-0 shadow-lg overflow-hidden royal-ramadan-card">

                    {{-- LAYER 1: Background Elements --}}
                    <div class="royal-bg-pattern"></div>
                    <div class="mosque-silhouette"></div> {{-- Siluet Masjid --}}
                    <div class="moon-glow"></div> {{-- Cahaya Bulan --}}

                    {{-- LAYER 2: Animated Lanterns (Gantung) --}}
                    <div class="r-lantern l-big">
                        <div class="r-lantern-light"></div>
                    </div>
                    <div class="r-lantern l-med">
                        <div class="r-lantern-light"></div>
                    </div>
                    <div class="r-lantern l-small">
                        <div class="r-lantern-light"></div>
                    </div>

                    {{-- LAYER 3: Particles (Stars) --}}
                    <div id="particles-js" class="particles-container"></div>

                    <div class="card-body position-relative z-index-10 py-5 px-4">
                        <div class="row align-items-center">

                            {{-- KIRI: TYPOGRAPHY & MESSAGE --}}
                            <div class="col-lg-7 text-center text-lg-start mb-4 mb-lg-0">
                                <div class="d-inline-block mb-3 animate-float">
                                    <span class="badge-royal">
                                        <i class="mdi mdi-star-crescent text-warning me-2"></i>
                                        1447 H / 2026 M
                                    </span>
                                </div>

                                {{-- Kaligrafi / Teks Arab Besar --}}
                                <h1 class="display-4 fw-bold text-gradient-gold mb-2 font-arabic"
                                    style="line-height: 1.2; text-shadow: 0 4px 15px rgba(0,0,0,0.5);">
                                    Marhaban Ya Ramadhan
                                </h1>

                                <p class="lead text-white-50 mb-4 px-3 px-lg-0"
                                    style="font-weight: 300; letter-spacing: 0.5px;">
                                    "Tamu agung akan segera tiba. Siapkan hati untuk bulan penuh ampunan."
                                </p>

                                {{-- Quote Box Kecil --}}
                                <div class="quote-box-royal mx-auto mx-lg-0">
                                    <i class="mdi mdi-format-quote-open text-warning me-1"></i>
                                    <span class="fst-italic small text-light">Ahlan Wa Sahlan, Bulan Suci.</span>
                                </div>
                            </div>

                            {{-- KANAN: LUXURY COUNTDOWN TIMER --}}
                            <div class="col-lg-5">
                                @if (!$ramadanData['is_today'])
                                    <div class="royal-countdown-wrapper">
                                        <div class="d-flex justify-content-center gap-3">

                                            {{-- HARI --}}
                                            <div class="royal-timer-box">
                                                <div class="timer-circle-bg"></div>
                                                <span class="royal-time" id="royal-days">{{ $ramadanData['days_left'] }}</span>
                                                <span class="royal-label">HARI</span>
                                            </div>

                                            {{-- JAM --}}
                                            <div class="royal-timer-box">
                                                <div class="timer-circle-bg"></div>
                                                <span class="royal-time" id="royal-hours">00</span>
                                                <span class="royal-label">JAM</span>
                                            </div>

                                            {{-- MENIT --}}
                                            <div class="royal-timer-box">
                                                <div class="timer-circle-bg"></div>
                                                <span class="royal-time" id="royal-minutes">00</span>
                                                <span class="royal-label">MENIT</span>
                                            </div>

                                        </div>

                                        <div class="text-center mt-4">
                                            <div class="loading-bar-container">
                                                <div class="loading-bar-fill"></div>
                                            </div>
                                            <small class="text-warning text-uppercase letter-spacing-2 mt-2 d-block">
                                                Menuju Maghrib Pertama
                                            </small>
                                        </div>
                                    </div>
                                @else
                                    {{-- TAMPILAN HARI H --}}
                                    <div class="text-center animate-pulse-gold">
                                        <img src="https://cdn-icons-png.flaticon.com/512/4358/4358661.png" width="80"
                                            class="mb-3 drop-shadow-glow">
                                        <h2 class="text-white fw-bold">Selamat Berpuasa</h2>
                                        <p class="text-warning mb-0">Semoga amal ibadah kita diterima Allah SWT.</p>
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

    {{-- ======================================================================= --}}
    {{-- MOTIVATIONAL QUOTE OF THE DAY --}}
    {{-- ======================================================================= --}}
    <div class="row mb-4 animate-enter" style="animation-delay: 0.15s">
        <div class="col-12">
            <div class="card border-0 shadow-lg overflow-hidden quote-card" id="quote-card"
                style="border-radius: 20px; min-height: 160px; position: relative;">
                {{-- Background Image Container --}}
                <div class="quote-bg-image" id="quote-bg-image"
                    style="position: absolute; inset: 0; background-size: cover; background-position: center; transition: opacity 0.5s ease;">
                </div>
                {{-- Dark Overlay for text readability --}}
                <div
                    style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.4) 100%);">
                </div>

                <div class="card-body py-4 px-4 position-relative" style="z-index: 2;">
                    <div class="row align-items-center">
                        <div class="col-lg-9">
                            <div class="d-flex align-items-start">
                                <div class="quote-icon me-3 d-none d-md-flex">
                                    <i class="mdi mdi-format-quote-open"></i>
                                </div>
                                <div>
                                    <p class="quote-text mb-2" id="motivational-quote"
                                        style="color: #ffffff !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9);">
                                        Memuat kutipan hari ini...
                                    </p>
                                    <p class="quote-author mb-0" id="quote-author"
                                        style="color: #ffffff !important; text-shadow: 1px 1px 3px rgba(0,0,0,0.9);">
                                        <span class="quote-dash" style="color: #ffffff !important;">—</span> <span
                                            id="author-name"
                                            style="color: #ffffff !important; text-shadow: 1px 1px 3px rgba(0,0,0,0.9);">Loading...</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 text-center text-lg-end mt-3 mt-lg-0">
                            <button class="btn btn-light btn-sm shadow-sm px-3 py-2" onclick="refreshQuote()"
                                style="border-radius: 50px;">
                                <i class="mdi mdi-refresh me-1"></i> Quote Baru
                            </button>
                            <p class="text-white-50 small mt-2 mb-0" style="font-size: 10px;">
                                <i class="mdi mdi-lightbulb-on-outline me-1"></i>Quote of the Day
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================================================= --}}
    {{-- POPUP PEMBERITAHUAN: TIPE LIBUR BARU --}}
    {{-- ======================================================================= --}}
    @if (!session('libur_notice_dismissed'))
        <div class="row mb-4 animate-enter" style="animation-delay: 0.18s" id="libur-notice-popup">
            <div class="col-12">
                <div class="alert shadow-lg border-0 d-flex align-items-start position-relative"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 20px 25px;">

                    {{-- Close Button --}}
                    <button type="button" class="btn-close btn-close-white position-absolute"
                        style="top: 15px; right: 15px; opacity: 0.8;" onclick="dismissLiburNotice()"
                        aria-label="Close"></button>

                    {{-- Icon --}}
                    <div class="me-3 d-none d-sm-block">
                        <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 50px; height: 50px;">
                            <i class="mdi mdi-information-outline text-white" style="font-size: 28px;"></i>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="flex-grow-1 pe-4">
                        <h5 class="text-white fw-bold mb-2">
                            <i class="mdi mdi-new-box text-warning me-1"></i> TIPE IZIN BARU: "LIBUR"
                        </h5>
                        <p class="text-white mb-2" style="opacity: 0.9; font-size: 14px;">
                            Sekarang untuk pengajuan <strong>Off Day / Hari Libur</strong>, silakan pilih tipe
                            <span class="badge bg-light text-dark shadow-sm">Libur (Off Day)</span> pada form pengajuan.
                        </p>
                        <p class="text-white-50 small mb-0" style="font-size: 12px;">
                            <i class="mdi mdi-alert-circle-outline me-1"></i>
                            Jangan gunakan tipe "Izin" lagi untuk off day ya!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================================= --}}
    {{-- SECTION HEADER: COMPANY STATISTICS --}}
    {{-- ======================================================================= --}}
    @if (auth()->user()->role == 'admin')
        <div class="section-header animate-enter" style="animation-delay: 0.2s">
            <h3>
                <span class="section-icon">
                    <i class="mdi mdi-chart-box-outline"></i>
                </span>
                Statistik Perusahaan
            </h3>
            <p class="section-subtitle">Ringkasan data dan performa organisasi</p>
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

    {{-- BAGIAN BARU: DINDING KENANGAN (PEMENANG BULAN LALU) --}}
    @if (isset($lastMonthWinners) && $lastMonthWinners->count() > 0)
        <div class="row mb-5 animate-enter" style="animation-delay: 0.3s">
            <div class="col-12">
                <div class="card border-0 shadow-lg hall-of-fame-card overflow-hidden">
                    <div class="spotlight"></div>
                    <div class="card-body p-4 position-relative z-index-1">
                        <div class="text-center mb-5">
                            <span class="badge bg-warning text-dark fw-bold mb-2 shadow-sm">HALL OF FAME</span>
                            <h3 class="fw-bold text-white mb-1" style="font-family: 'Playfair Display', serif;">
                                <i class="mdi mdi-auto-fix text-warning me-2"></i>Pahlawan Absensi {{ $lastMonthName }}
                            </h3>
                            <p class="text-white-50 small">Apresiasi untuk dedikasi luar biasa di bulan lalu</p>
                        </div>

                        {{-- Pastikan row-cols-md-3 untuk memaksa 3 kolom sejajar --}}
                        <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center">
                            @foreach ($lastMonthWinners as $history)
                                <div class="col">
                                    <div class="winner-memory-card text-center p-3 h-100 d-flex flex-column align-items-center">
                                        <div class="position-relative mb-3 mt-2">
                                            {{-- Badge Rank dinamis berdasarkan urutan hasil query --}}
                                            <div
                                                class="rank-badge-mini {{ $loop->first ? 'gold' : ($loop->iteration == 2 ? 'silver' : 'bronze') }}">
                                                #{{ $loop->iteration }}
                                            </div>

                                            @if ($history->user->profile_photo_path)
                                                <img src="{{ asset('storage/' . $history->user->profile_photo_path) }}"
                                                    class="rounded-circle shadow-lg border border-2 border-white grayscale-memory"
                                                    style="width: 85px; height: 85px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white shadow-lg mx-auto"
                                                    style="width: 85px; height: 85px; font-size: 24px;">
                                                    {{ substr($history->user->name, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>

                                        <h6 class="text-white fw-bold mb-1">{{ Str::limit($history->user->name, 15) }}
                                        </h6>
                                        <p class="text-warning small mb-3" style="font-size: 10px; letter-spacing: 1px;">
                                            {{ strtoupper($history->user->division->name ?? 'Staff') }}
                                        </p>

                                        <div class="mt-auto">
                                            <span class="badge bg-light text-dark opacity-75 py-2 px-3"
                                                style="font-size: 11px; border-radius: 50px;">
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

    {{-- ======================================================================= --}}
    {{-- BAGIAN 2: LEADERBOARD PODIUM MEWAH (TOP 3) --}}
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
                                                {{ Str::limit($leaderboard[1]->user->name, 12) }}
                                            </h6>
                                            <p class="small text-muted mb-2">
                                                {{ $leaderboard[1]->user->division->name ?? '-' }}
                                            </p>
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
                                <div class="podium-step-container main-winner animate-enter" style="animation-delay: 0.5s">
                                    <div class="crown-floating">
                                        <img src="https://cdn-icons-png.flaticon.com/512/6941/6941697.png" alt="Crown" width="50">
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
                                                {{ Str::limit($leaderboard[0]->user->name, 15) }}
                                            </h5>
                                            <p class="small text-muted mb-2">
                                                {{ $leaderboard[0]->user->division->name ?? '-' }}
                                            </p>
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
                                                {{ Str::limit($leaderboard[2]->user->name, 12) }}
                                            </h6>
                                            <p class="small text-muted mb-2">
                                                {{ $leaderboard[2]->user->division->name ?? '-' }}
                                            </p>
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
                count($topScanners) > 0
            )
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
                                                {{ substr($topScanners[1]->name, 0, 1) }}
                                            </div>
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
                                    <div class="crown-floating"><img src="https://cdn-icons-png.flaticon.com/512/6941/6941697.png"
                                            alt="Crown" width="50"></div>
                                    <div class="podium-avatar-wrapper gold-glow">
                                        @if ($topScanners[0]->profile_photo_path)
                                            <img src="{{ asset('storage/' . $topScanners[0]->profile_photo_path) }}"
                                                class="luxury-avatar">
                                        @else
                                            <div class="luxury-avatar-placeholder gold-gradient">
                                                {{ substr($topScanners[0]->name, 0, 1) }}
                                            </div>
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
                                                {{ substr($topScanners[2]->name, 0, 1) }}
                                            </div>
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
    {{-- [BARU] BAGIAN GALLERY: LEMBARAN CERITA BULAN INI (NOSTALGIA) --}}
    {{-- Hanya tampil 1 hari sebelum akhir bulan --}}
    {{-- ======================================================================= --}}
    @if(isset($showGallery) && $showGallery)
        <div class="row mt-4 mb-5 animate-enter" style="animation-delay: 0.5s">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-3 px-2">
                    <div>
                        <h4 class="fw-bold mb-0 text-dark" style="font-family: 'Playfair Display', serif;">
                            <i class="mdi mdi-camera-iris text-danger me-2"></i>Lembaran Cerita {{ $currentMonthName }}
                        </h4>
                        <p class="text-muted small mb-0">Mengenang setiap tetes keringat dan senyummu bulan ini.</p>
                    </div>
                </div>
                <div class="gallery-scroll-container">
                    <div class="d-flex gap-3 pb-3">
                        @forelse ($attendanceGallery as $item)
                            {{-- Foto Masuk --}}
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

                            {{-- Foto Pulang --}}
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
                            <div class="col-12 text-center py-5 bg-light rounded-3 border-dashed w-100" style="min-width: 300px;">
                                <i class="mdi mdi-image-filter-hdr display-4 text-muted opacity-25"></i>
                                <p class="text-muted mt-2">Belum ada potret cerita untuk bulan ini...</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif


    {{-- ======================================================================= --}}
    {{-- BAGIAN 3: DASHBOARD PERSONAL (ID CARD & ABSEN MANDIRI) --}}
    {{-- ======================================================================= --}}

    {{-- SECTION HEADER: PERSONAL DASHBOARD --}}
    <div class="section-header animate-enter" style="animation-delay: 0.4s">
        <h3>
            <span class="section-icon">
                <i class="mdi mdi-account-circle"></i>
            </span>
            Dashboard Pribadi
        </h3>
        <p class="section-subtitle">Informasi absensi dan akses cepat Anda</p>
    </div>

    <div class="row animate-enter" style="animation-delay: 0.5s">
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
                                            class="id-card-img" data-bs-toggle="modal" data-bs-target="#profilePhotoModal"
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

                            {{-- SEDANG BEKERJA (Update: Cek Status Izin/Libur Dulu) --}}
                        @else
                            @php
                                $isLeaveStatus = $myAttendanceToday->attendance_type == 'leave' || in_array(strtolower($myAttendanceToday->presence_status), ['izin', 'sakit', 'cuti', 'libur', 'dinas luar']);
                            @endphp

                            @if($isLeaveStatus)
                                {{-- CARD KHUSUS STATUS IZIN / LIBUR --}}
                                <div class="active-work-card mb-3 position-relative overflow-hidden"
                                    style="background: linear-gradient(135deg, #FF9966 0%, #FF5E62 100%); 
                                                    border-radius: 20px; border: none; box-shadow: 0 10px 40px rgba(255, 94, 98, 0.3);">

                                    {{-- Decorative --}}
                                    <div
                                        style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;">
                                    </div>

                                    <div class="card-body p-4 position-relative" style="z-index: 2;">
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="work-status-icon me-3"
                                                style="width: 56px; height: 56px; background: rgba(255,255,255,0.25); border-radius: 16px; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.3);">
                                                @if(strtolower($myAttendanceToday->presence_status) == 'libur')
                                                    <i class="mdi mdi-calendar-remove text-white" style="font-size: 28px;"></i>
                                                @elseif(strtolower($myAttendanceToday->presence_status) == 'sakit')
                                                    <i class="mdi mdi-hospital-box text-white" style="font-size: 28px;"></i>
                                                @else
                                                    <i class="mdi mdi-file-document-box-check-outline text-white"
                                                        style="font-size: 28px;"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <h4 class="fw-bold text-white mb-0">
                                                    {{ strtoupper($myAttendanceToday->presence_status) }}</h4>
                                                <p class="mb-0 text-white" style="opacity: 0.9; font-size: 0.875rem;">Status kehadiran
                                                    Anda hari ini.</p>
                                            </div>
                                        </div>

                                        <div class="work-timeline-card p-3"
                                            style="background: rgba(255,255,255,0.95); border-radius: 16px;">
                                            <div class="d-flex align-items-center">
                                                <div class="timeline-dot me-3"
                                                    style="width: 40px; height: 40px; background: #FF5E62; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                    <i class="mdi mdi-check text-white"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block uppercase" style="font-size: 0.75rem;">Sudah
                                                        Diverifikasi</small>
                                                    <h5 class="fw-bold mb-0 text-dark">Data Tercatat</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="active-work-card mb-3 position-relative overflow-hidden"
                                    style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); 
                                                                                                                                                                                                                                                                                                border-radius: 20px; border: none; box-shadow: 0 10px 40px rgba(67, 233, 123, 0.3);">

                                    {{-- Decorative Elements --}}
                                    <div
                                        style="position: absolute; top: -100px; right: -100px; width: 300px; height: 300px; 
                                                                                                                                                                                                                                                                                                    background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%); 
                                                                                                                                                                                                                                                                                                    border-radius: 50%;">
                                    </div>
                                    <div
                                        style="position: absolute; bottom: -50px; left: -50px; width: 200px; height: 200px; 
                                                                                                                                                                                                                                                                                                    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%); 
                                                                                                                                                                                                                                                                                                    border-radius: 50%;">
                                    </div>

                                    <div class="card-body p-4 position-relative" style="z-index: 2;">
                                        @if (!$isCrossDay)
                                            {{-- Header: Status & Live Indicator --}}
                                            <div class="d-flex align-items-center justify-content-between mb-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="work-status-icon me-3"
                                                        style="width: 56px; height: 56px; background: rgba(255,255,255,0.25); 
                                                                                                                                                                                                                                                                                                                                                                                                    border-radius: 16px; display: flex; align-items: center; 
                                                                                                                                                                                                                                                                                                                                                                                                    justify-content: center; backdrop-filter: blur(10px); 
                                                                                                                                                                                                                                                                                                                                                                                                    border: 1px solid rgba(255,255,255,0.3); box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                                                        <i class="mdi mdi-briefcase-check text-white" style="font-size: 28px;"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
                                                            Sedang Bekerja
                                                            <span class="live-pulse-badge"></span>
                                                        </h4>
                                                        <p class="mb-0 text-white" style="opacity: 0.9; font-size: 0.875rem;">
                                                            Anda aktif bekerja hari ini
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Work Timeline & Info --}}
                                            <div class="work-timeline-card p-3 mb-3"
                                                style="background: rgba(255,255,255,0.95); border-radius: 16px; 
                                                                                                                                                                                                                                                                                                                                                                                            box-shadow: 0 4px 20px rgba(0,0,0,0.08);">

                                                <div class="row g-3">
                                                    {{-- Check In Time --}}
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center">
                                                            <div class="timeline-dot me-3"
                                                                style="width: 40px; height: 40px; background: linear-gradient(135deg, #43e97b, #38f9d7); 
                                                                                                                                                                                                                                                                                                                                                                                                            border-radius: 50%; display: flex; align-items: center; 
                                                                                                                                                                                                                                                                                                                                                                                                            justify-content: center; box-shadow: 0 4px 12px rgba(67,233,123,0.4);">
                                                                <i class="mdi mdi-login text-white fs-5"></i>
                                                            </div>
                                                            <div>
                                                                <small class="text-muted d-block mb-1"
                                                                    style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                                                    Check In
                                                                </small>
                                                                <h5 class="fw-bold mb-0" style="color: #2d3748;">
                                                                    {{ $myAttendanceToday->check_in_time->format('H:i') }}
                                                                </h5>
                                                                <small class="text-muted" style="font-size: 0.75rem;">
                                                                    via {{ $sourceLabel }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Work Duration (Auto-calculating) --}}
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center">
                                                            <div class="timeline-dot me-3"
                                                                style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea, #764ba2); 
                                                                                                                                                                                                                                                                                                                                                                                                            border-radius: 50%; display: flex; align-items: center; 
                                                                                                                                                                                                                                                                                                                                                                                                            justify-content: center; box-shadow: 0 4px 12px rgba(102,126,234,0.4);">
                                                                <i class="mdi mdi-timer-outline text-white fs-5"></i>
                                                            </div>
                                                            <div>
                                                                <small class="text-muted d-block mb-1"
                                                                    style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                                                    Durasi Kerja
                                                                </small>
                                                                <h5 class="fw-bold mb-0" style="color: #2d3748;" id="work-duration-display">
                                                                    -
                                                                </h5>
                                                                <small class="text-muted" style="font-size: 0.75rem;">
                                                                    Live Counter
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Animated Progress Bar --}}
                                                <div class="mt-3">
                                                    <div class="progress" style="height: 6px; border-radius: 10px; background: #e9ecef;">
                                                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                            style="background: linear-gradient(90deg, #43e97b, #38f9d7); width: 100%;">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($isCrossDay)
                                            {{-- ========================================================= --}}
                                            {{-- HEADER STATUS LEMBUR LINTAS HARI --}}
                                            {{-- ========================================================= --}}
                                            <div class="d-flex align-items-center justify-content-between mb-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="work-status-icon me-3"
                                                        style="width: 56px; height: 56px; background: rgba(255,255,255,0.25); 
                                                                                                                                                                                                                                                                                                                                                                                        border-radius: 16px; display: flex; align-items: center; 
                                                                                                                                                                                                                                                                                                                                                                                        justify-content: center; backdrop-filter: blur(10px); 
                                                                                                                                                                                                                                                                                                                                                                                        border: 1px solid rgba(255,255,255,0.3); box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                                                        <i class="mdi mdi-clock-alert-outline text-white" style="font-size: 28px;"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
                                                            Lembur Lintas Hari
                                                            <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">PERLU
                                                                AKSI</span>
                                                        </h4>
                                                        <p class="mb-0 text-white" style="opacity: 0.9; font-size: 0.875rem;">
                                                            Masuk: {{ $myAttendanceToday->check_in_time->translatedFormat('l, d M') }}
                                                            {{ $myAttendanceToday->check_in_time->format('H:i') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Action Button --}}
                                        <div class="text-center">
                                            @if (!$isCrossDay)
                                                @if (Auth::user()->only_security_scan)
                                                    <button class="btn btn-light btn-lg w-100 shadow-sm" disabled
                                                        style="border-radius: 14px; padding: 1rem; cursor: not-allowed; opacity: 0.7;">
                                                        <i class="mdi mdi-lock me-2"></i>
                                                        <span class="fw-bold">Absen Pulang Mandiri Dikunci</span>
                                                    </button>
                                                    <small class="text-white d-block mt-2" style="opacity: 0.85; font-size: 0.75rem;">
                                                        Silahkan Scan QR Code ke Security untuk Pulang
                                                    </small>
                                                @else
                                                    <a href="{{ route('self.attend.create', ['attendance_id' => $myAttendanceToday->id, 'mode' => 'pulang']) }}"
                                                        class="checkout-btn btn btn-lg w-100 shadow-lg"
                                                        style="background: rgba(255,255,255,0.95); color: #ef4444; border: none; 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  border-radius: 14px; padding: 1rem; font-weight: 700; 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  transition: all 0.3s ease; backdrop-filter: blur(10px);">
                                                        <i class="mdi mdi-logout-variant me-2"></i>
                                                        Absen Pulang Mandiri
                                                    </a>
                                                @endif
                                            @else
                                                {{-- ========================================================= --}}
                                                {{-- KONDISI LEMBUR LINTAS HARI (CrossDay = True) --}}
                                                {{-- ========================================================= --}}
                                                <div class="cross-day-container"
                                                    style="background: rgba(255,255,255,0.15); border-radius: 16px; padding: 1.25rem; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.25);">

                                                    {{-- Header Info Lembur --}}
                                                    <div class="text-white mb-3">
                                                        <div class="d-flex align-items-center justify-content-center mb-2">
                                                            <i class="mdi mdi-clock-alert-outline"
                                                                style="font-size: 2rem; margin-right: 8px;"></i>
                                                            <span class="badge bg-warning text-dark fw-bold px-3 py-2">LEMBUR LINTAS
                                                                HARI</span>
                                                        </div>
                                                        <p class="small mb-0" style="opacity: 0.9;">
                                                            Anda masuk kemarin pukul
                                                            <strong>{{ $myAttendanceToday->check_in_time->format('H:i') }}</strong>
                                                            ({{ $myAttendanceToday->check_in_time->translatedFormat('l, d M') }})<br>
                                                            @if ($myAttendanceToday->is_extended_shift)
                                                                Status lembur sudah dikonfirmasi. Silakan absen pulang sekarang.
                                                            @else
                                                                dan belum absen pulang. Geser slider untuk konfirmasi:
                                                            @endif
                                                        </p>
                                                    </div>

                                                    @if ($myAttendanceToday->is_extended_shift)
                                                        {{-- LANGSUNG TAMPILKAN TOMBOL KAMERA (Sudah Konfirmasi Lembur) --}}
                                                        <a href="{{ route('self.attend.create', ['attendance_id' => $myAttendanceToday->id, 'mode' => 'pulang']) }}"
                                                            class="btn btn-light btn-lg w-100 shadow-lg"
                                                            style="border-radius: 14px; padding: 1rem; font-weight: 700; color: #10b981;">
                                                            <i class="mdi mdi-camera me-2"></i>
                                                            Absen Pulang Sekarang
                                                        </a>
                                                        <p class="text-center mt-2 mb-0"
                                                            style="color: rgba(255,255,255,0.85); font-size: 0.8rem;">
                                                            <i class="mdi mdi-check-circle me-1"></i> Lembur sudah terkonfirmasi
                                                        </p>
                                                    @else
                                                        {{-- SLIDER KONFIRMASI LEMBUR (Belum Konfirmasi) --}}
                                                        <div id="confirmation-wrapper">
                                                            <div class="mb-3" id="slider-view">
                                                                <p class="mb-2 fw-bold"
                                                                    style="color: rgba(255,255,255,0.95); font-size: 0.85rem;">
                                                                    <i class="mdi mdi-gesture-swipe-horizontal me-1"></i>
                                                                    Geser untuk Konfirmasi Lembur & Absen Pulang
                                                                </p>
                                                                <div class="overtime-slide-track" id="slide-track"
                                                                    style="
                                                                                                                                                                                                    background: rgba(255,255,255,0.95);
                                                                                                                                                                                                    height: 56px;
                                                                                                                                                                                                    border-radius: 50px;
                                                                                                                                                                                                    position: relative;
                                                                                                                                                                                                    display: flex;
                                                                                                                                                                                                    align-items: center;
                                                                                                                                                                                                    overflow: hidden;
                                                                                                                                                                                                    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);">

                                                                    <span
                                                                        style="
                                                                                                                                                                                                        position: absolute;
                                                                                                                                                                                                        left: 0; width: 100%;
                                                                                                                                                                                                        text-align: center;
                                                                                                                                                                                                        color: #10b981;
                                                                                                                                                                                                        font-weight: 700;
                                                                                                                                                                                                        font-size: 0.85rem;
                                                                                                                                                                                                        letter-spacing: 1px;
                                                                                                                                                                                                        user-select: none;
                                                                                                                                                                                                        opacity: 0.75;">
                                                                        GESER KE KANAN >>
                                                                    </span>

                                                                    <div id="slide-thumb"
                                                                        style="
                                                                                                                                                                                                        width: 48px; height: 48px;
                                                                                                                                                                                                        background: linear-gradient(135deg, #43e97b, #38f9d7);
                                                                                                                                                                                                        border-radius: 50%;
                                                                                                                                                                                                        margin-left: 4px;
                                                                                                                                                                                                        display: flex; align-items: center; justify-content: center;
                                                                                                                                                                                                        color: white; font-size: 1.4rem;
                                                                                                                                                                                                        box-shadow: 0 4px 12px rgba(67, 233, 123, 0.4);
                                                                                                                                                                                                        cursor: grab;
                                                                                                                                                                                                        transition: transform 0.2s ease-out;
                                                                                                                                                                                                        touch-action: none;">
                                                                        <i class="mdi mdi-chevron-double-right"></i>
                                                                    </div>
                                                                </div>
                                                                <p class="mt-2 mb-0" style="color: rgba(255,255,255,0.85); font-size: 0.75rem;">
                                                                    * Setelah geser, kamera akan terbuka untuk foto pulang
                                                                </p>
                                                            </div>
                                                        </div>

                                                        {{-- VIEW KAMERA (Hidden by default, muncul setelah slide) --}}
                                                        <div id="camera-view" class="d-none">
                                                            <a href="{{ route('self.attend.create', ['attendance_id' => $myAttendanceToday->id, 'mode' => 'pulang']) }}"
                                                                class="btn btn-light btn-lg w-100 shadow-lg"
                                                                style="border-radius: 14px; padding: 1rem; font-weight: 700; color: #10b981;">
                                                                <i class="mdi mdi-camera me-2"></i>
                                                                Buka Kamera Untuk Pulang
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <style>
                                    /* Live Pulse Badge */
                                    .live-pulse-badge {
                                        display: inline-block;
                                        width: 12px;
                                        height: 12px;
                                        background: #fff;
                                        border-radius: 50%;
                                        animation: pulse-live 1.5s infinite;
                                        box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7);
                                    }

                                    @keyframes pulse-live {
                                        0% {
                                            box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7);
                                        }

                                        50% {
                                            box-shadow: 0 0 0 8px rgba(255, 255, 255, 0);
                                        }

                                        100% {
                                            box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
                                        }
                                    }

                                    /* Checkout Button Hover */
                                    .checkout-btn:hover {
                                        transform: translateY(-2px);
                                        box-shadow: 0 12px 30px rgba(239, 68, 68, 0.3) !important;
                                        background: white !important;
                                    }

                                    /* Active Work Card Entrance */
                                    .active-work-card {
                                        animation: slideInUp 0.6s ease-out;
                                    }

                                    @keyframes slideInUp {
                                        from {
                                            opacity: 0;
                                            transform: translateY(30px);
                                        }

                                        to {
                                            opacity: 1;
                                            transform: translateY(0);
                                        }
                                    }
                                </style>

                                <script>
                                    // Auto-calcul                          ate work dura                         tion
                                    @if (!$isCrossDay)
                                        (function () {
                                            const checkInTime = new Date('{{ $myAttendanceToday->check_in_time->toIso8601String() }}');
                                            const durationDisplay = document.getElementById('work-duration-display');

                                            function updateWorkDuration() {
                                                const now = new Date();
                                                const diff = now - checkInTime;

                                                const hours = Math.floor(diff / (1000 * 60 * 60));
                                                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

                                                if (durationDisplay) {
                                                    durationDisplay.textContent = `${hours}j ${minutes}m`;
                                                }
                                            }

                                            updateWorkDuration();
                                            setInterval(updateWorkDuration, 60000); // Update every minute
                                        })();
                                    @endif
                                </script>
                            @endif
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
                                    <form action="{{ route('leave-requests.finish-early', $myLeaveToday->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100 shadow-sm hover-scale"
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
                                            <a href="{{ route('self.attend.create') }}" class="btn btn-outline-info shadow hover-scale">
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
                                            <a href="{{ route('self.attend.create') }}" class="btn btn-dark shadow hover-scale">
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
    {{-- BAGIAN BARU: MENU CEPAT (QUICK ACTIONS) - MODERN REDESIGN --}}
    {{-- ======================================================================= --}}
    <div class="row animate-enter mb-4" style="animation-delay: 0.7s">
        <div class="col-12">
            {{-- Container dengan gradient background --}}
            <div class="card border-0 shadow-lg overflow-hidden"
                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px;">

                {{-- Decorative elements --}}
                <div
                    style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; 
                                                                                                                                    background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%); 
                                                                                                                                    border-radius: 50%; pointer-events: none;">
                </div>

                <div class="card-body p-4">
                    <div class="row align-items-center g-3">
                        {{-- LEFT: Icon & Title --}}
                        <div class="col-lg-4">
                            <div class="d-flex align-items-center">
                                {{-- Icon dengan glassmorphism effect --}}
                                <div class="d-flex align-items-center justify-content-center me-3"
                                    style="width: 64px; height: 64px; background: rgba(255, 255, 255, 0.2); 
                                                                                                                                                    border-radius: 16px; backdrop-filter: blur(10px); 
                                                                                                                                                    border: 1px solid rgba(255, 255, 255, 0.3); box-shadow: 0 8px 20px rgba(0,0,0,0.15);">
                                    <i class="mdi mdi-lightning-bolt text-white" style="font-size: 32px;"></i>
                                </div>
                                <div>
                                    <h4 class="fw-bold mb-1 text-white">Menu Cepat</h4>
                                    <p class="mb-0 small" style="color: rgba(255, 255, 255, 0.85);">
                                        Akses cepat untuk pengajuan izin
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT: Action Buttons --}}
                        <div class="col-lg-8">
                            <div class="row g-3">
                                {{-- Button 1: Ajukan Izin --}}
                                <div class="col-md-6">
                                    <a href="{{ route('leave-requests.create') }}"
                                        class="quick-action-card d-block text-decoration-none">
                                        <div class="p-4 h-100 d-flex flex-column"
                                            style="background: rgba(255, 255, 255, 0.95); border-radius: 16px; 
                                                                                                                                                            border: 1px solid rgba(255, 255, 255, 0.5); 
                                                                                                                                                            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12); 
                                                                                                                                                            transition: all 0.3s ease; position: relative; overflow: hidden;">

                                            {{-- Hover gradient effect --}}
                                            <div style="position: absolute; inset: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                                                                                                                                                                opacity: 0; transition: opacity 0.3s ease;"
                                                class="hover-gradient"></div>

                                            <div style="position: relative; z-index: 1;">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="icon-wrapper me-3"
                                                        style="width: 48px; height: 48px; background: linear-gradient(135deg, #667eea, #764ba2); 
                                                                                                                                                                        border-radius: 12px; display: flex; align-items: center; justify-content: center; 
                                                                                                                                                                        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);">
                                                        <i class="mdi mdi-file-document-edit text-white fs-4"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="fw-bold mb-0 text-title">Ajukan Izin / Sakit</h5>
                                                    </div>
                                                </div>
                                                <p class="small mb-0 text-desc" style="color: #6c757d;">
                                                    Izin, Sakit, Cuti, WFH, atau Telat
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                {{-- Button 2: Riwayat --}}
                                <div class="col-md-6">
                                    <a href="{{ route('attendance.history') }}"
                                        class="quick-action-card d-block text-decoration-none">
                                        <div class="p-4 h-100 d-flex flex-column"
                                            style="background: rgba(255, 255, 255, 0.95); border-radius: 16px; 
                                                                                                                                                            border: 1px solid rgba(255, 255, 255, 0.5); 
                                                                                                                                                            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12); 
                                                                                                                                                            transition: all 0.3s ease; position: relative; overflow: hidden;">

                                            {{-- Hover gradient effect --}}
                                            <div style="position: absolute; inset: 0; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); 
                                                                                                                                                                opacity: 0; transition: opacity 0.3s ease;"
                                                class="hover-gradient"></div>

                                            <div style="position: relative; z-index: 1;">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="icon-wrapper me-3"
                                                        style="width: 48px; height: 48px; background: linear-gradient(135deg, #4facfe, #00f2fe); 
                                                                                                                                                                        border-radius: 12px; display: flex; align-items: center; justify-content: center; 
                                                                                                                                                                        box-shadow: 0 4px 12px rgba(79, 172, 254, 0.3);">
                                                        <i class="mdi mdi-history text-white fs-4"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="fw-bold mb-0 text-title">Riwayat Absensi</h5>
                                                    </div>
                                                </div>
                                                <p class="small mb-0 text-desc" style="color: #6c757d;">
                                                    Lihat semua riwayat kehadiran Anda
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Hover effects untuk quick action cards */
        .quick-action-card:hover .hover-gradient {
            opacity: 1;
        }

        .quick-action-card:hover {
            transform: translateY(-4px);
        }

        .quick-action-card:hover .text-title,
        .quick-action-card:hover .text-desc {
            color: white !important;
        }

        .quick-action-card:hover .icon-wrapper {
            background: white !important;
        }

        .quick-action-card:hover .icon-wrapper i {
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Motivational Quote Widget Styles */
        .quote-card {
            position: relative;
            overflow: hidden;
        }

        .quote-decoration-1 {
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -100px;
            right: -50px;
            animation: floatQuote 6s ease-in-out infinite;
        }

        .quote-decoration-2 {
            position: absolute;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            bottom: -80px;
            left: -50px;
            animation: floatQuote 8s ease-in-out infinite reverse;
        }

        @keyframes floatQuote {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            50% {
                transform: translate(10px, -10px) scale(1.05);
            }
        }

        .quote-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .quote-icon i {
            font-size: 28px;
            color: #fff;
        }

        .quote-text {
            font-size: 1.15rem;
            font-weight: 600;
            color: #fff !important;
            line-height: 1.6;
            font-style: italic;
            margin: 0;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.8), 0 1px 3px rgba(0, 0, 0, 0.9);
        }

        .quote-author {
            color: rgba(255, 255, 255, 0.95) !important;
            font-size: 0.9rem;
            font-weight: 600;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.8);
        }

        .quote-dash {
            color: rgba(255, 255, 255, 0.7);
        }

        @media (max-width: 768px) {
            .quote-text {
                font-size: 0.95rem;
            }

            .quote-icon {
                width: 40px;
                height: 40px;
            }

            .quote-icon i {
                font-size: 22px;
            }
        }
    </style>

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
                        <img src="" id="profileModalImageSrc" class="img-fluid rounded shadow-lg" alt="Profile Photo"
                            style="max-height: 80vh; max-width: 100%; object-fit: contain;">
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
        /* =================================================================
                                                                                       DASHBOARD LAYOUT IMPROVEMENTS - SECTION STYLING
                                                                                       ================================================================= */

        /* Section Headers & Separators */
        .section-header {
            margin-bottom: 1.5rem;
            margin-top: 2.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e9ecef;
            position: relative;
        }

        .section-header::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 80px;
            height: 2px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .section-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-header .section-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
        }

        .section-header p.section-subtitle {
            color: #718096;
            font-size: 0.875rem;
            margin: 0.25rem 0 0 0;
        }

        /* Section Wrappers */
        .dashboard-section {
            margin-bottom: 3rem;
        }

        .dashboard-section.hero-section {
            margin-bottom: 2rem;
        }

        .dashboard-section.personal-section {
            background: linear-gradient(to right, #f8f9fa 0%, #ffffff 100%);
            padding: 2rem 1rem;
            border-radius: 16px;
            margin-bottom: 3rem;
        }

        .dashboard-section.stats-section {
            background: #ffffff;
            padding: 2rem 1rem;
            border-radius: 16px;
            border: 1px solid #e9ecef;
            margin-bottom: 3rem;
        }

        /* Improved Spacing */
        .section-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #e9ecef, transparent);
            margin: 3rem 0;
        }

        .card-row-spacing {
            margin-bottom: 1.5rem;
        }

        /* Responsive section padding */
        @media (max-width: 768px) {

            .dashboard-section.personal-section,
            .dashboard-section.stats-section {
                padding: 1.5rem 0.75rem;
            }

            .section-header {
                margin-top: 1.5rem;
            }
        }

        /* =================================================================
                                                                                       CRITICAL FIX: TEXT VISIBILITY & PRESERVE GRADIENTS
                                                                                       ================================================================= */

        /* DON'T override backgrounds - only fix text colors */

        /* Card Bank - WHITE text on gradient backgrounds */
        .card-bank .card-bank-label,
        .card-bank .card-bank-value,
        .card-bank .card-bank-desc,
        .card-bank * {
            color: #ffffff !important;
        }

        /* Ensure gradients are applied */
        .gradient-purple {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }

        .gradient-blue {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important;
        }

        .gradient-green {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%) !important;
        }

        .gradient-orange {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%) !important;
        }

        .gradient-red {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
        }

        .gradient-dark {
            background: linear-gradient(135deg, #2c3e50 0%, #000000 100%) !important;
        }

        /* Card ID - WHITE text on dark glossy background */
        .card-id {
            background: linear-gradient(135deg, #1a1a1a 0%, #000000 100%) !important;
            color: #ffffff !important;
        }

        .card-id .card-body *,
        .card-id-label,
        .card-id-name,
        .card-id-division,
        .card-id-card-number {
            color: #ffffff !important;
        }

        /* Regular white cards - DARK text  */
        .card:not(.card-bank):not(.card-id):not(.luxury-card):not(.hall-of-fame-card):not(.royal-ramadan-card):not(.birthday-card) {
            background: #ffffff !important;
        }

        .card:not(.card-bank):not(.card-id):not(.luxury-card):not(.hall-of-fame-card):not(.royal-ramadan-card):not(.birthday-card) .card-title,
        .card:not(.card-bank):not(.card-id):not(.luxury-card):not(.hall-of-fame-card):not(.royal-ramadan-card):not(.birthday-card) h1,
        .card:not(.card-bank):not(.card-id):not(.luxury-card):not(.hall-of-fame-card):not(.royal-ramadan-card):not(.birthday-card) h2,
        .card:not(.card-bank):not(.card-id):not(.luxury-card):not(.hall-of-fame-card):not(.royal-ramadan-card):not(.birthday-card) h3,
        .card:not(.card-bank):not(.card-id):not(.luxury-card):not(.hall-of-fame-card):not(.royal-ramadan-card):not(.birthday-card) h4,
        .card:not(.card-bank):not(.card-id):not(.luxury-card):not(.hall-of-fame-card):not(.royal-ramadan-card):not(.birthday-card) h5,
        .card:not(.card-bank):not(.card-id):not(.luxury-card):not(.hall-of-fame-card):not(.royal-ramadan-card):not(.birthday-card) h6,
        .card:not(.card-bank):not(.card-id):not(.luxury-card):not(.hall-of-fame-card):not(.royal-ramadan-card):not(.birthday-card) p,
        .card:not(.card-bank):not(.card-id):not(.luxury-card):not(.hall-of-fame-card):not(.royal-ramadan-card):not(.birthday-card) span {
            color: #212529 !important;
        }

        /* Text utilities */
        .text-muted {
            color: #6c757d !important;
        }

        .text-primary {
            color: #0d6efd !important;
        }

        .text-dark {
            color: #212529 !important;
        }

        /* Ensure podium text is visible on light backgrounds */
        .podium-content h5,
        .podium-content h6,
        .podium-content p,
        .podium-content .small {
            color: #212529 !important;
        }

        /* Stat pills - semi-transparent bg with dark text */
        .stat-pill {
            background: rgba(255, 255, 255, 0.25) !important;
            color: #212529 !important;
            border: 1px solid rgba(0, 0, 0, 0.1);
            font-weight: 600 !important;
        }

        .stat-pill.gold {
            background: rgba(255, 215, 0, 0.3) !important;
            color: #856404 !important;
            border-color: rgba(255, 215, 0, 0.5);
        }

        /* Luxury card titles on light gradient backgrounds */
        .luxury-card .card-title,
        .luxury-card h3,
        .luxury-card h4 {
            color: #333 !important;
        }

        /* ROYAL RAMADHAN CARD - Ensure white/gold text on dark gradient */
        .royal-ramadan-card,
        .royal-ramadan-card * {
            color: #ffffff !important;
        }

        .royal-ramadan-card .text-gradient-gold,
        .royal-ramadan-card h1,
        .royal-ramadan-card .badge-royal {
            color: #FFD700 !important;
        }

        /* === ROYAL RAMADHAN THEME - LUXURIOUS VERSION === */
        @import url('https://fonts.googleapis.com/css2?family=Scheherazade+New:wght@700&display=swap');

        .royal-ramadan-card {
            background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%) !important;
            /* Deep Mystical Blue/Green - ENFORCED */
            position: relative;
            border-radius: 24px !important;
            border: 1px solid rgba(255, 215, 0, 0.3) !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5) !important;
        }

        .font-arabic {
            font-family: 'Scheherazade New', serif;
            /* Font style Arab/Timur Tengah */
        }

        /* Pattern Background */
        .royal-bg-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                radial-gradient(circle at 50% 50%, transparent 0%, transparent 100%),
                repeating-linear-gradient(45deg, rgba(255, 215, 0, 0.03) 0px, rgba(255, 215, 0, 0.03) 1px, transparent 1px, transparent 10px);
            opacity: 0.6;
            z-index: 1;
        }

        /* Silhouette Masjid */
        .mosque-silhouette {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 150px;
            background-image: url('https://cdn.pixabay.com/photo/2017/01/31/15/34/mosque-2025088_1280.png');
            /* Gambar Siluet Transparan */
            background-size: contain;
            background-repeat: no-repeat;
            background-position: bottom left;
            opacity: 0.15;
            z-index: 2;
            filter: invert(1);
            /* Putihkan siluet */
        }

        /* Cahaya Bulan */
        .moon-glow {
            position: absolute;
            top: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 215, 0, 0.15) 0%, transparent 70%);
            z-index: 1;
        }

        /* Teks Emas Mewah */
        .text-gradient-gold {
            background: linear-gradient(to bottom, #fff 20%, #ffd700 80%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Badge 1447 H */
        .badge-royal {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 215, 0, 0.4);
            padding: 8px 20px;
            border-radius: 50px;
            color: #ffd700;
            font-weight: 600;
            font-size: 0.9rem;
            backdrop-filter: blur(5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* Quote Box */
        .quote-box-royal {
            background: rgba(0, 0, 0, 0.2);
            border-left: 3px solid #ffd700;
            padding: 10px 15px;
            border-radius: 0 8px 8px 0;
            display: inline-block;
        }

        /* --- LANTERNS (LENTERA) --- */
        .r-lantern {
            position: absolute;
            top: -20px;
            background: #111;
            border: 2px solid #ffd700;
            border-radius: 4px;
            z-index: 5;
            transform-origin: top center;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
        }

        .r-lantern::before {
            /* Tali */
            content: '';
            position: absolute;
            top: -100px;
            left: 50%;
            width: 2px;
            height: 100px;
            background: rgba(255, 215, 0, 0.5);
            transform: translateX(-50%);
        }

        .r-lantern-light {
            position: absolute;
            top: 20%;
            left: 20%;
            right: 20%;
            bottom: 20%;
            background: radial-gradient(circle, #fff 0%, #ffd700 60%, transparent 100%);
            opacity: 0.8;
            border-radius: 50%;
            filter: blur(5px);
            animation: lanternFlicker 3s infinite alternate;
        }

        .l-big {
            right: 10%;
            width: 40px;
            height: 60px;
            animation: lanternSwing 6s ease-in-out infinite alternate;
        }

        .l-med {
            right: 20%;
            width: 30px;
            height: 45px;
            top: -10px;
            animation: lanternSwing 5s ease-in-out infinite alternate-reverse;
            opacity: 0.9;
        }

        .l-small {
            right: 5%;
            width: 25px;
            height: 35px;
            top: -5px;
            animation: lanternSwing 4s ease-in-out infinite alternate;
            opacity: 0.8;
        }

        @keyframes lanternSwing {
            from {
                transform: rotate(3deg);
            }

            to {
                transform: rotate(-3deg);
            }
        }

        @keyframes lanternFlicker {

            0%,
            100% {
                opacity: 0.6;
                transform: scale(0.9);
            }

            50% {
                opacity: 1;
                transform: scale(1.1);
            }
        }

        /* --- TIMER BOXES --- */
        .royal-countdown-wrapper {
            position: relative;
            z-index: 10;
        }

        .royal-timer-box {
            width: 80px;
            height: 100px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.02) 100%);
            border: 1px solid rgba(255, 215, 0, 0.3);
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            position: relative;
            overflow: hidden;
        }

        /* Efek Kilau di Timer */
        .royal-timer-box::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }

        .royal-time {
            font-size: 2.5rem;
            font-weight: 800;
            color: #fff;
            line-height: 1;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
            font-family: 'Consolas', monospace;
        }

        .royal-label {
            font-size: 0.7rem;
            font-weight: 700;
            color: #ffd700;
            margin-top: 5px;
            letter-spacing: 1px;
        }

        /* Loading Bar */
        .loading-bar-container {
            width: 100%;
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 10px;
        }

        .loading-bar-fill {
            height: 100%;
            background: #ffd700;
            width: 60%;
            /* Statis dulu, nanti bisa dinamis JS */
            box-shadow: 0 0 10px #ffd700;
            animation: loadProgress 2s ease-out;
        }

        @keyframes shine {
            0% {
                transform: translateX(-100%) rotate(45deg);
            }

            100% {
                transform: translateX(100%) rotate(45deg);
            }
        }

        @keyframes loadProgress {
            from {
                width: 0%;
            }

            to {
                width: 60%;
            }
        }

        /* Particles Container (Stars) */
        .particles-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        /* Manual Stars (CSS Only fallback) */
        .star {
            position: absolute;
            background: white;
            border-radius: 50%;
            box-shadow: 0 0 5px #fff;
            animation: twinkle 2s infinite ease-in-out;
        }

        .s-1 {
            width: 3px;
            height: 3px;
            top: 20%;
            left: 15%;
            animation-delay: 0s;
        }

        .s-2 {
            width: 2px;
            height: 2px;
            top: 40%;
            left: 30%;
            animation-delay: 1s;
        }

        .s-3 {
            width: 4px;
            height: 4px;
            top: 10%;
            right: 40%;
            animation-delay: 0.5s;
        }

        @keyframes twinkle {

            0%,
            100% {
                opacity: 0.3;
                transform: scale(0.8);
            }

            50% {
                opacity: 1;
                transform: scale(1.2);
            }
        }

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
            // --- ROYAL RAMADHAN JS ---
            @if (isset($ramadanData) && !$ramadanData['is_today'])
                const royalRamadanDate = new Date("{{ $ramadanData['date'] }}T00:00:00");

                function updateRoyalCountdown() {
                    const now = new Date();
                    const diff = royalRamadanDate - now;

                    if (diff <= 0) {
                        location.reload(); // Refresh halaman saat hari H
                        return;
                    }

                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

                    // Update DOM elements
                    const elDays = document.getElementById('royal-days');
                    const elHours = document.getElementById('royal-hours');
                    const elMinutes = document.getElementById('royal-minutes');

                    // Efek Animasi Angka Berubah (Optional)
                    if (elDays && elDays.innerText != days) elDays.innerText = days;
                    if (elHours) elHours.innerText = hours.toString().padStart(2, '0');
                    if (elMinutes) elMinutes.innerText = minutes.toString().padStart(2, '0');
                }

                // Jalankan setiap detik
                setInterval(updateRoyalCountdown, 1000);
                updateRoyalCountdown(); // Run immediately
            @endif

        // --- DISMISS LIBUR NOTICE POPUP ---
        function dismissLiburNotice() {
            const popup = document.getElementById('libur-notice-popup');
            if (popup) {
                popup.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                popup.style.opacity = '0';
                popup.style.transform = 'translateY(-20px)';
                setTimeout(() => popup.remove(), 300);
                // Simpan ke localStorage agar tidak muncul lagi selama 7 hari
                localStorage.setItem('libur_notice_dismissed', Date.now());
            }
        }

        // Cek localStorage saat load - sembunyikan jika sudah di-dismiss dalam 7 hari terakhir
        document.addEventListener('DOMContentLoaded', function () {
            const dismissed = localStorage.getItem('libur_notice_dismissed');
            if (dismissed) {
                const dismissedTime = parseInt(dismissed);
                const sevenDays = 7 * 24 * 60 * 60 * 1000;
                if (Date.now() - dismissedTime < sevenDays) {
                    const popup = document.getElementById('libur-notice-popup');
                    if (popup) popup.remove();
                }
            }
        });

        function previewGalleryImage(src, title, date) {
            const modal = new bootstrap.Modal(document.getElementById('galleryPreviewModal'));
            document.getElementById('galleryPreviewImg').src = src;
            document.getElementById('galleryPreviewTitle').innerText = title;
            document.getElementById('galleryPreviewDate').innerText = date;
            modal.show();
        }

        document.addEventListener('DOMContentLoaded', function () {

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
                    qrModal.addEventListener('show.bs.modal', function (event) {
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
                profilePhotoModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var src = button.getAttribute('data-src');
                    var modalImg = document.getElementById('profileModalImageSrc');
                    modalImg.src = src;
                });
            }
        });

        // ===============================================
        // MOTIVATIONAL QUOTE FUNCTIONALITY
        // ===============================================
        // ===============================================
        // MOTIVATIONAL QUOTE FUNCTIONALITY
        // ===============================================

        // Backup quotes in case API fails
        const backupQuotes = [
            { text: "Kesuksesan adalah hasil dari persiapan, kerja keras, dan belajar dari kegagalan.", author: "Colin Powell" },
            { text: "Satu-satunya cara untuk melakukan pekerjaan hebat adalah dengan mencintai apa yang kamu lakukan.", author: "Steve Jobs" },
            { text: "Jangan menunggu. Waktu tidak akan pernah tepat.", author: "Napoleon Hill" },
            { text: "Masa depan adalah milik mereka yang percaya pada keindahan mimpi-mimpi mereka.", author: "Eleanor Roosevelt" },
            { text: "Jangan biarkan kemarin mengambil terlalu banyak hari ini.", author: "Will Rogers" }
        ];

        async function fetchRandomQuote() {
            try {
                // 1. Fetch random quote from dummyjson.com (more reliable)
                const response = await fetch('https://dummyjson.com/quotes/random');
                if (!response.ok) throw new Error('Quote API response was not ok');
                const data = await response.json();

                const originalText = data.quote;
                const author = data.author;

                // 2. Translate to Indonesian
                try {
                    const translateUrl = `https://api.mymemory.translated.net/get?q=${encodeURIComponent(originalText)}&langpair=en|id`;
                    const trResponse = await fetch(translateUrl);
                    const trData = await trResponse.json();

                    if (trData && trData.responseData && trData.responseData.translatedText) {
                        return {
                            text: trData.responseData.translatedText,
                            author: author
                        };
                    }
                } catch (trError) {
                    console.warn('Translation failed, showing English:', trError);
                }

                // Fallback: Show original English if translation fails
                return {
                    text: originalText,
                    author: author
                };

            } catch (error) {
                console.error('Failed to fetch quote:', error);
                return null;
            }
        }

        async function getDailyQuote() {
            // Try to fetch a fresh quote first
            const quote = await fetchRandomQuote();
            if (quote) return quote;

            // Fallback
            const today = new Date();
            const dayOfYear = Math.floor((today - new Date(today.getFullYear(), 0, 0)) / (1000 * 60 * 60 * 24));
            const index = dayOfYear % backupQuotes.length;
            return backupQuotes[index];
        }

        async function refreshQuote() {
            // Show loading state
            const quoteEl = document.getElementById('motivational-quote');
            const authorEl = document.getElementById('author-name');

            if (quoteEl) quoteEl.style.opacity = '0.5';

            // Always try to fetch new random quote
            let quote = await fetchRandomQuote();

            // If failed, use random backup
            if (!quote) {
                const randomIndex = Math.floor(Math.random() * backupQuotes.length);
                quote = backupQuotes[randomIndex];
            }

            displayQuote(quote);
        }

        function displayQuote(quote) {
            const quoteEl = document.getElementById('motivational-quote');
            const authorEl = document.getElementById('author-name');

            if (!quoteEl || !authorEl) return;

            quoteEl.style.opacity = '0';
            authorEl.style.opacity = '0';

            setTimeout(() => {
                quoteEl.textContent = quote.text;
                authorEl.textContent = quote.author;
                quoteEl.style.opacity = '1';
                authorEl.style.opacity = '1';
            }, 300);

            // Also refresh background image
            refreshBackgroundImage();
        }

        // Nature background images from Unsplash (reliable, high-quality)
        const natureBackgrounds = [
            'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&h=400&fit=crop', // Mountains
            'https://images.unsplash.com/photo-1501854140801-50d01698950b?w=1200&h=400&fit=crop', // Forest
            'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=1200&h=400&fit=crop', // Foggy mountains
            'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=1200&h=400&fit=crop', // Forest light
            'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=1200&h=400&fit=crop', // Mountain lake
            'https://images.unsplash.com/photo-1426604966848-d7adac402bff?w=1200&h=400&fit=crop', // Valley
            'https://images.unsplash.com/photo-1472214103451-9374bd1c798e?w=1200&h=400&fit=crop', // Green hills
            'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?w=1200&h=400&fit=crop', // Lake sunset
            'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=1200&h=400&fit=crop', // Beach
            'https://images.unsplash.com/photo-1518173946687-a4c036bc8ce8?w=1200&h=400&fit=crop', // Sky clouds
            'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=1200&h=400&fit=crop', // Starry mountain
            'https://images.unsplash.com/photo-1433086966358-54859d0ed716?w=1200&h=400&fit=crop', // Waterfall
            'https://images.unsplash.com/photo-1465919292999-00f5a4e5861b?w=1200&h=400&fit=crop', // Misty forest
            'https://images.unsplash.com/photo-1491002052546-bf38f186af56?w=1200&h=400&fit=crop', // Aurora
            'https://images.unsplash.com/photo-1494500764479-0c8f2919a3d8?w=1200&h=400&fit=crop'  // Night sky
        ];

        function refreshBackgroundImage() {
            const bgEl = document.getElementById('quote-bg-image');
            if (!bgEl) return;

            bgEl.style.opacity = '0.3';

            const randomIndex = Math.floor(Math.random() * natureBackgrounds.length);
            const newImage = new Image();
            newImage.onload = function () {
                bgEl.style.backgroundImage = `url('${natureBackgrounds[randomIndex]}')`;
                bgEl.style.opacity = '1';
            };
            newImage.src = natureBackgrounds[randomIndex];
        }

        function getDailyBackgroundImage() {
            const today = new Date();
            const dayOfYear = Math.floor((today - new Date(today.getFullYear(), 0, 0)) / (1000 * 60 * 60 * 24));
            const index = dayOfYear % natureBackgrounds.length;
            return natureBackgrounds[index];
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', async function () {
            // Initial load
            const quote = await getDailyQuote();
            displayQuote(quote);

            // Initial background
            refreshBackgroundImage();
        }); // Optional: Confetti Effect Function (Placeholder)
        function confettiEffect() {
            alert("🎉 Happy Birthday! PStore wish you all the best! 🎉");
        }
    </script>
@endpush