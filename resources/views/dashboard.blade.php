@extends('layout.master')

@section('title')
    Dashboard
@endsection

@section('heading')
    <div class="d-flex justify-content-between align-items-center w-100 py-2">
        <div class="header-greeting-wrapper">
            <div class="d-flex align-items-center mb-1">
                <div class="greeting-dot pulse-animation-green me-2"></div>
                <span class="text-muted small fw-medium text-uppercase tracking-wider" id="greeting-text">Selamat Datang,</span>
            </div>
            <h2 class="fw-black mb-0 text-dark" style="letter-spacing: -0.5px;">{{ Auth::user()->name }}<span class="text-primary">.</span></h2>
        </div>
        <div class="d-none d-md-flex align-items-center">
            <div class="stats-mini-item border-end pe-4 me-4 text-end">
                <span class="text-muted small d-block mb-0">Status Kehadiran</span>
                <span class="badge bg-soft-success text-success fw-bold">ONLINE SYSTEM</span>
            </div>
            <div class="header-clock-wrapper text-end">
                <h4 class="fw-black mb-0 text-primary font-monospace" id="header-clock-nav">--:--:--</h4>
                <small class="text-muted fw-medium">{{ \Carbon\Carbon::now($current_timezone)->translatedFormat('l, d F Y') }}</small>
            </div>
        </div>
    </div>
@endsection

@section('content')

    {{-- ======================================================================= --}}
    {{-- BIRTHDAY CELEBRATION - PREMIUM GLASS DESIGN --}}
    {{-- ======================================================================= --}}
    @if (isset($birthdayData) && $birthdayData)
        <div class="row mb-4 animate-enter">
            <div class="col-12">
                <div class="card border-0 shadow-premium overflow-hidden birthday-card-premium">
                    <div class="confetti-container"></div>
                    <div class="luxury-overlay"></div>
                    <div class="card-body position-relative z-index-2 py-5 px-4">
                        <div class="row align-items-center">
                            <div class="col-md-7 text-white">
                                @if ($birthdayData['is_today'])
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-circle-lg bg-warning text-dark me-3 rotate-animation">
                                            <i class="mdi mdi-cake-variant fs-3"></i>
                                        </div>
                                        <div>
                                            <span class="badge bg-white text-primary fw-bold px-3 mb-1">SPECIAL ANNIVERSARY</span>
                                            <h1 class="fw-black mb-0 display-5 tracking-tighter">HAPPY BIRTHDAY!</h1>
                                        </div>
                                    </div>
                                    <p class="lead mb-4 opacity-75 fw-medium">
                                        Hari ini adalah momen spesial untukmu, <strong>{{ Auth::user()->name }}</strong>. 
                                        Semoga usia ke-{{ $birthdayData['age_to_be'] }} membawa keberkahan dan kesuksesan luar biasa.
                                    </p>
                                    <button class="btn btn-white-premium btn-lg rounded-pill" onclick="confettiEffect()">
                                        <i class="mdi mdi-party-popper me-2"></i> Rayakan Bersama PStore
                                    </button>
                                @else
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-circle-lg bg-white-20 text-white me-3">
                                            <i class="mdi mdi-calendar-star fs-3"></i>
                                        </div>
                                        <div>
                                            <span class="badge bg-soft-light text-white fw-bold px-3 mb-1">COUNTING DOWN</span>
                                            <h2 class="fw-black mb-0 display-6">Menuju Hari Spesialmu</h2>
                                        </div>
                                    </div>
                                    <p class="mb-0 opacity-75">Bersiaplah, dalam hitungan hari kamu akan memasuki usia ke-<strong>{{ $birthdayData['age_to_be'] }}</strong>.</p>
                                @endif
                            </div>

                            <div class="col-md-5 mt-4 mt-md-0">
                                @if (!$birthdayData['is_today'])
                                    <div class="d-flex justify-content-center justify-content-md-end gap-3" id="birthday-countdown">
                                        <div class="countdown-unit">
                                            <span class="value" id="cd-days">{{ $birthdayData['days_left'] }}</span>
                                            <span class="label">HARI</span>
                                        </div>
                                        <div class="countdown-unit">
                                            <span class="value" id="cd-hours">00</span>
                                            <span class="label">JAM</span>
                                        </div>
                                        <div class="countdown-unit">
                                            <span class="value" id="cd-minutes">00</span>
                                            <span class="label">MENIT</span>
                                        </div>
                                        <div class="countdown-unit highlight">
                                            <span class="value text-warning" id="cd-seconds">00</span>
                                            <span class="label">DETIK</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ATTENDANCE WRAPPED - APPLE STYLE DARK CARD --}}
    @if (\Carbon\Carbon::now()->month == 12)
        <div class="row mb-4 animate-enter">
            <div class="col-12">
                <div class="card bg-dark-modern text-white shadow-premium border-0 overflow-hidden">
                    <div class="wrapped-glow"></div>
                    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center py-4 px-4 position-relative">
                        <div class="d-flex align-items-center mb-3 mb-md-0">
                            <div class="icon-box-premium bg-gradient-gold me-4">
                                <i class="mdi mdi-sparkles fs-2 text-dark"></i>
                            </div>
                            <div>
                                <h3 class="fw-black mb-1 tracking-tighter">{{ date('Y') }} <span class="text-gold-gradient">WRAPPED</span></h3>
                                <p class="mb-0 text-white-50 small fw-medium">Kilas balik perjalanan dedikasimu sepanjang tahun ini.</p>
                            </div>
                        </div>
                        <a href="{{ route('attendance.recap') }}" class="btn btn-gold-premium rounded-pill px-5 fw-bold shadow-lg">
                            <i class="mdi mdi-play-circle me-2"></i> Buka Story
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================================= --}}
    {{-- ADMIN & AUDIT STATS - NEUMORPHIC BANK CARDS --}}
    {{-- ======================================================================= --}}
    @if (auth()->user()->role == 'admin')
        <div class="row mb-4">
            @php 
                $adminCards = [
                    ['label' => 'Total User', 'val' => $totalUsers, 'desc' => 'Karyawan Aktif', 'icon' => 'mdi-account-group', 'grad' => 'grad-deep-blue'],
                    ['label' => 'Total Cabang', 'val' => $totalBranches, 'desc' => 'Cabang Terdaftar', 'icon' => 'mdi-office-building-marker', 'grad' => 'grad-dark-indigo'],
                    ['label' => 'Absensi Hari Ini', 'val' => $attendancesToday, 'desc' => 'Total Check-in', 'icon' => 'mdi-calendar-check', 'grad' => 'grad-emerald'],
                    ['label' => 'Perlu Verifikasi', 'val' => $pendingVerifications, 'desc' => 'Menunggu Approval', 'icon' => 'mdi-shield-alert', 'grad' => 'grad-sunset']
                ];
            @endphp
            @foreach($adminCards as $index => $card)
            <div class="col-xl-3 col-md-6 mb-4 animate-enter" style="animation-delay: {{ 0.1 * ($index + 1) }}s">
                <div class="card card-bank-premium {{ $card['grad'] }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="bank-chip"></div>
                            <i class="mdi {{ $card['icon'] }} bank-icon-bg"></i>
                        </div>
                        <div class="mt-4">
                            <p class="bank-label">{{ $card['label'] }}</p>
                            <h2 class="bank-value count-up" data-target="{{ $card['val'] }}">0</h2>
                        </div>
                        <div class="bank-footer">
                            <span class="small opacity-75 fw-medium">{{ $card['desc'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @elseif (auth()->user()->role == 'audit')
        <div class="row mb-4">
            @php 
                $auditCards = [
                    ['label' => 'Verif Absensi', 'val' => $pendingVerifications, 'link' => 'audit.verify.list', 'btn' => 'Review Foto', 'icon' => 'mdi-clipboard-check', 'grad' => 'grad-crimson'],
                    ['label' => 'Approve Izin', 'val' => $pendingLeaves, 'link' => 'leave-requests.index', 'btn' => 'Cek Pengajuan', 'icon' => 'mdi-file-eye', 'grad' => 'grad-royal'],
                    ['label' => 'Hadir Cabang', 'val' => $attendancesToday, 'link' => '#', 'btn' => 'Live Log', 'icon' => 'mdi-map-marker-radius', 'grad' => 'grad-emerald']
                ];
            @endphp
            @foreach($auditCards as $index => $card)
            <div class="col-md-4 mb-4 animate-enter" style="animation-delay: {{ 0.1 * ($index + 1) }}s">
                <div class="card card-bank-premium {{ $card['grad'] }}">
                    <div class="card-body">
                        <div class="bank-chip"></div>
                        <div class="mt-4">
                            <p class="bank-label">{{ $card['label'] }}</p>
                            <h2 class="bank-value count-up" data-target="{{ $card['val'] }}">0</h2>
                            <a href="{{ $card['link'] !== '#' ? route($card['link']) : 'javascript:void(0)' }}" class="btn btn-bank-action btn-sm mt-3">
                                {{ $card['btn'] }} <i class="mdi mdi-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <i class="mdi {{ $card['icon'] }} bank-icon-bg"></i>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @elseif (auth()->user()->role == 'security')
        <div class="row mb-4">
            <div class="col-lg-7 mb-4 animate-enter">
                <div class="card border-0 shadow-premium card-scan-primary h-100 overflow-hidden">
                    <div class="card-body p-5 text-center position-relative z-index-2">
                        <div class="scan-qr-visual mb-4">
                            <div class="scan-line"></div>
                            <i class="mdi mdi-qrcode-scan display-1 text-primary"></i>
                        </div>
                        <h2 class="fw-black mb-3">TERMINAL SCANNER</h2>
                        <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">Gunakan kamera perangkat untuk memvalidasi QR Code karyawan secara real-time.</p>
                        <a href="{{ route('security.scan') }}" class="btn btn-primary btn-xl rounded-pill shadow-lg px-5">
                            <i class="mdi mdi-camera-enhance me-2"></i> BUKA KAMERA SCANNER
                        </a>
                    </div>
                    <div class="scan-bg-decoration"></div>
                </div>
            </div>
            <div class="col-lg-5 mb-4 animate-enter" style="animation-delay: 0.2s">
                <div class="card card-bank-premium grad-dark-slate h-100">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <div class="bank-chip"></div>
                            <p class="bank-label mt-4">Total Pindaian Hari Ini</p>
                            <h1 class="bank-value display-3 count-up" data-target="{{ $myScansToday }}">0</h1>
                        </div>
                        <div class="border-top border-white-10 pt-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small opacity-75">Kapasitas User</span>
                                <span class="fw-bold">{{ $totalUsers }} Karyawan</span>
                            </div>
                            <div class="progress bg-white-10 mt-2" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: {{ ($myScansToday/$totalUsers)*100 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================================= --}}
    {{-- HALL OF FAME - CINEMATIC CAROUSEL STYLE --}}
    {{-- ======================================================================= --}}
    @if (isset($lastMonthWinners) && $lastMonthWinners->count() > 0)
        <div class="row mb-5 animate-enter" style="animation-delay: 0.3s">
            <div class="col-12">
                <div class="hall-of-fame-wrapper p-4 p-md-5">
                    <div class="text-center mb-5">
                        <span class="premium-label mb-2">TITANS OF THE MONTH</span>
                        <h2 class="fw-black text-white display-6 tracking-tight">Pahlawan Absensi {{ $lastMonthName }}</h2>
                        <div class="premium-divider mx-auto"></div>
                    </div>
                    
                    <div class="row g-4 justify-content-center">
                        @foreach ($lastMonthWinners as $history)
                            <div class="col-lg-4 col-md-6">
                                <div class="titan-card">
                                    <div class="titan-rank rank-{{ $loop->iteration }}">#{{ $loop->iteration }}</div>
                                    <div class="titan-image-wrapper">
                                        @if ($history->user->profile_photo_path)
                                            <img src="{{ asset('storage/' . $history->user->profile_photo_path) }}" class="titan-img">
                                        @else
                                            <div class="titan-placeholder">{{ substr($history->user->name, 0, 1) }}</div>
                                        @endif
                                    </div>
                                    <div class="titan-info text-center mt-3">
                                        <h5 class="fw-bold text-white mb-1">{{ Str::limit($history->user->name, 20) }}</h5>
                                        <p class="text-gold small fw-bold text-uppercase mb-3">{{ $history->user->division->name ?? 'STAFF' }}</p>
                                        <div class="titan-stat">
                                            <span class="text-white-50 small">LOYALITAS</span>
                                            <div class="d-flex align-items-center justify-content-center">
                                                <i class="mdi mdi-check-decagram text-primary me-2"></i>
                                                <h4 class="mb-0 fw-black text-white">{{ $history->total_attendance }}</h4>
                                                <span class="ms-1 text-white-50">Hari</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================================= --}}
    {{-- TOP LEADERBOARD - PODIUM LUXURY 2.0 --}}
    {{-- ======================================================================= --}}
    @if (auth()->user()->role != 'security' && isset($leaderboard) && count($leaderboard) > 0)
        <div class="row mb-5 animate-enter" style="animation-delay: 0.4s">
            <div class="col-12">
                <div class="card border-0 shadow-premium-dark luxury-leaderboard-card">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex align-items-center justify-content-between mb-5">
                            <div>
                                <h3 class="fw-black mb-1">Top Attendance</h3>
                                <p class="text-muted small mb-0">Leaderboard Real-time {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
                            </div>
                            <div class="icon-box-premium bg-soft-primary text-primary">
                                <i class="mdi mdi-trophy-variant fs-3"></i>
                            </div>
                        </div>

                        <div class="podium-wrapper d-flex align-items-end justify-content-center">
                            {{-- RANK 2 --}}
                            @if(isset($leaderboard[1]))
                            <div class="podium-item rank-2">
                                <div class="podium-user">
                                    <div class="avatar-container">
                                        <img src="{{ $leaderboard[1]->user->profile_photo_path ? asset('storage/' . $leaderboard[1]->user->profile_photo_path) : 'https://ui-avatars.com/api/?name='.$leaderboard[1]->user->name }}" alt="">
                                        <div class="rank-badge">2</div>
                                    </div>
                                    <h6 class="fw-bold mt-3 mb-0 text-truncate">{{ Str::limit($leaderboard[1]->user->name, 12) }}</h6>
                                    <span class="text-muted tiny fw-bold">{{ $leaderboard[1]->total_attendance }} HADIR</span>
                                </div>
                                <div class="podium-block"></div>
                            </div>
                            @endif

                            {{-- RANK 1 --}}
                            @if(isset($leaderboard[0]))
                            <div class="podium-item rank-1">
                                <div class="podium-user">
                                    <div class="crown-wrapper">
                                        <i class="mdi mdi-crown text-warning"></i>
                                    </div>
                                    <div class="avatar-container">
                                        <img src="{{ $leaderboard[0]->user->profile_photo_path ? asset('storage/' . $leaderboard[0]->user->profile_photo_path) : 'https://ui-avatars.com/api/?name='.$leaderboard[0]->user->name }}" alt="">
                                        <div class="rank-badge">1</div>
                                    </div>
                                    <h5 class="fw-black mt-3 mb-0">{{ Str::limit($leaderboard[0]->user->name, 15) }}</h5>
                                    <span class="text-primary small fw-black">{{ $leaderboard[0]->total_attendance }} HADIR</span>
                                </div>
                                <div class="podium-block">
                                    <div class="winner-glow"></div>
                                </div>
                            </div>
                            @endif

                            {{-- RANK 3 --}}
                            @if(isset($leaderboard[2]))
                            <div class="podium-item rank-3">
                                <div class="podium-user">
                                    <div class="avatar-container">
                                        <img src="{{ $leaderboard[2]->user->profile_photo_path ? asset('storage/' . $leaderboard[2]->user->profile_photo_path) : 'https://ui-avatars.com/api/?name='.$leaderboard[2]->user->name }}" alt="">
                                        <div class="rank-badge">3</div>
                                    </div>
                                    <h6 class="fw-bold mt-3 mb-0 text-truncate">{{ Str::limit($leaderboard[2]->user->name, 12) }}</h6>
                                    <span class="text-muted tiny fw-bold">{{ $leaderboard[2]->total_attendance }} HADIR</span>
                                </div>
                                <div class="podium-block"></div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================================= --}}
    {{-- NOSTALGIA GALLERY - APPLE PHOTO STYLE --}}
    {{-- ======================================================================= --}}
    <div class="row mt-5 mb-5 animate-enter" style="animation-delay: 0.5s">
        <div class="col-12">
            <div class="gallery-header d-flex justify-content-between align-items-end mb-4 px-2">
                <div>
                    <h3 class="fw-black mb-1">Momen {{ $currentMonthName }}</h3>
                    <p class="text-muted small mb-0 fw-medium">Koleksi aktivitas harianmu bulan ini.</p>
                </div>
                <div class="gallery-nav">
                    <button class="btn btn-icon-round me-2" id="gallery-prev"><i class="mdi mdi-chevron-left"></i></button>
                    <button class="btn btn-icon-round" id="gallery-next"><i class="mdi mdi-chevron-right"></i></button>
                </div>
            </div>
            
            <div class="gallery-modern-container" id="gallery-scroll">
                <div class="gallery-modern-track">
                    @forelse ($attendanceGallery as $item)
                        @if ($item->photo_path)
                            <div class="gallery-modern-item" onclick="previewGalleryImage('{{ Storage::url($item->photo_path) }}', 'Check-in Record', '{{ $item->check_in_time->translatedFormat('l, d F Y') }}')">
                                <div class="gallery-card-v2">
                                    <img src="{{ Storage::url($item->photo_path) }}" class="img-fluid">
                                    <div class="gallery-info-v2">
                                        <span class="badge bg-success">IN</span>
                                        <span class="date">{{ $item->check_in_time->format('d M') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($item->photo_out_path)
                            <div class="gallery-modern-item" onclick="previewGalleryImage('{{ Storage::url($item->photo_out_path) }}', 'Check-out Record', '{{ $item->check_out_time ? $item->check_out_time->translatedFormat('l, d F Y') : '-' }}')">
                                <div class="gallery-card-v2">
                                    <img src="{{ Storage::url($item->photo_out_path) }}" class="img-fluid">
                                    <div class="gallery-info-v2">
                                        <span class="badge bg-danger">OUT</span>
                                        <span class="date">{{ $item->check_in_time->format('d M') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="empty-gallery p-5 text-center w-100">
                            <div class="icon-box-xl mx-auto mb-3 bg-light text-muted opacity-50">
                                <i class="mdi mdi-camera-off"></i>
                            </div>
                            <h5 class="text-muted fw-bold">Belum Ada Rekaman Visual</h5>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================================================= --}}
    {{-- PERSONAL DASHBOARD - ENTERPRISE ID CARD --}}
    {{-- ======================================================================= --}}
    <div class="row section-title mb-4 animate-enter">
        <div class="col-12">
            <div class="d-flex align-items-center">
                <div class="title-line me-3"></div>
                <h4 class="fw-black mb-0">KEHADIRAN PRIBADI</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5 mb-4 animate-enter" style="animation-delay: 0.6s">
            <div class="card card-id-enterprise border-0 shadow-premium overflow-hidden">
                <div class="id-card-pattern"></div>
                <div class="card-body p-4 position-relative z-index-2">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div class="id-photo-wrapper">
                            @if (Auth::user()->profile_photo_path)
                                <img src="{{ Storage::url(Auth::user()->profile_photo_path) }}" class="id-photo shadow-lg" data-bs-toggle="modal" data-bs-target="#profilePhotoModal" data-src="{{ Storage::url(Auth::user()->profile_photo_path) }}">
                            @else
                                <div class="id-photo-placeholder shadow-lg">{{ substr(Auth::user()->name, 0, 1) }}</div>
                            @endif
                            <div class="id-active-dot"></div>
                        </div>
                        <div class="text-end">
                            <h6 class="text-white-50 small mb-0 fw-bold">PSTORE ACCESS</h6>
                            <div class="id-card-logo-mini"><i class="mdi mdi-integrated-circuit-chip text-warning"></i></div>
                        </div>
                    </div>
                    
                    <div class="id-user-info mb-4">
                        <span class="text-white-50 tiny fw-bold text-uppercase tracking-widest">NAMA LENGKAP</span>
                        <h3 class="text-white fw-black mb-3">{{ strtoupper(Auth::user()->name) }}</h3>
                        
                        <div class="row">
                            <div class="col-6">
                                <span class="text-white-50 tiny fw-bold text-uppercase tracking-widest">DIVISI</span>
                                <p class="text-white fw-bold mb-0">{{ strtoupper(Auth::user()->division->name ?? 'STAFF') }}</p>
                            </div>
                            <div class="col-6 text-end">
                                <span class="text-white-50 tiny fw-bold text-uppercase tracking-widest">LEVEL</span>
                                <p class="text-warning fw-bold mb-0">OFFICIAL MEMBER</p>
                            </div>
                        </div>
                    </div>

                    <div class="id-footer d-flex justify-content-between align-items-center pt-3 border-top border-white-10">
                        <div class="id-qr-trigger bg-white p-2 rounded shadow-sm" data-bs-toggle="modal" data-bs-target="#qrModal">
                            <div id="dashboard-qrcode-v2"></div>
                        </div>
                        <div class="id-number text-end text-white">
                            <span class="tiny opacity-50 d-block">UNIQUE ID NUMBER</span>
                            <span class="fw-bold font-monospace letter-spacing-2">{{ $idCardNumber ?? 'PS-002931-X' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-4 animate-enter" style="animation-delay: 0.7s">
            <div class="card border-0 shadow-premium h-100 personal-status-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <div class="icon-box-premium bg-soft-primary text-primary me-3">
                                <i class="mdi mdi-clock-check-outline fs-4"></i>
                            </div>
                            <h5 class="fw-black mb-0">AKTIVITAS HARI INI</h5>
                        </div>
                        <div class="text-end">
                            <h3 class="fw-black text-primary mb-0 font-monospace" id="realtime-clock-large">--:--:--</h3>
                            <span class="badge bg-light text-muted border px-3 fw-bold">{{ $todaySchedule }}</span>
                        </div>
                    </div>

                    <div class="attendance-logic-wrapper">
                        @if (session('success'))
                            <div class="alert alert-soft-success border-0 rounded-4 mb-4">
                                <div class="d-flex">
                                    <i class="mdi mdi-check-circle fs-4 me-3"></i>
                                    <div>
                                        <p class="mb-0 fw-bold">{{ session('success') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- DYNAMIC STATUS RENDERING --}}
                        @if ($myAttendanceToday)
                            @php
                                $isCrossDay = false;
                                if (!$myAttendanceToday->check_out_time) {
                                    $isCrossDay = $myAttendanceToday->check_in_time->format('Y-m-d') !== \Carbon\Carbon::now($current_timezone)->format('Y-m-d');
                                }
                                $sourceLabel = $myAttendanceToday->attendance_type == 'scan' ? 'Terminal Scanner' : 'Selfie Mandiri';
                            @endphp

                            @if ($myAttendanceToday->check_out_time || $myAttendanceToday->photo_out_path)
                                {{-- DONE STATUS --}}
                                <div class="status-box status-box-success text-center py-4">
                                    <div class="status-icon-main bg-success shadow-lg"><i class="mdi mdi-home-heart text-white"></i></div>
                                    <h4 class="fw-black mt-3 mb-1">Shift Selesai!</h4>
                                    <p class="text-muted small mb-4">Terima kasih atas dedikasi dan kerja kerasmu hari ini.</p>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="stat-inner p-3 rounded-4 bg-light">
                                                <span class="tiny text-muted d-block mb-1">CHECK-IN</span>
                                                <h5 class="fw-black mb-0">{{ $myAttendanceToday->check_in_time->format('H:i') }}</h5>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="stat-inner p-3 rounded-4 bg-light">
                                                <span class="tiny text-muted d-block mb-1">CHECK-OUT</span>
                                                <h5 class="fw-black mb-0">{{ $myAttendanceToday->check_out_time->format('H:i') }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{-- ACTIVE STATUS --}}
                                <div class="status-box {{ $isCrossDay ? 'status-box-warning' : 'status-box-primary' }} py-4 px-4">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="status-icon-main {{ $isCrossDay ? 'bg-warning' : 'bg-primary' }} shadow-lg me-4">
                                            <i class="mdi {{ $isCrossDay ? 'mdi-clock-alert' : 'mdi-tie' }} text-white"></i>
                                        </div>
                                        <div>
                                            <h4 class="fw-black mb-0">{{ $isCrossDay ? 'Lembur Lintas Hari' : 'Sesi Kerja Aktif' }}</h4>
                                            <p class="text-muted small mb-0">Masuk pukul {{ $myAttendanceToday->check_in_time->format('H:i') }} via {{ $sourceLabel }}</p>
                                        </div>
                                    </div>

                                    @if ($isCrossDay)
                                        {{-- CROSS DAY ACTION --}}
                                        <div class="cross-day-action-pnl p-3 rounded-4 bg-white border shadow-sm">
                                            @if (!$myAttendanceToday->is_extended_shift)
                                                <div id="confirmation-wrapper">
                                                    <div id="slide-track-premium" class="mb-3">
                                                        <div id="slide-thumb-premium"><i class="mdi mdi-chevron-right"></i></div>
                                                        <span class="slide-text">GESER UNTUK ABSEN PULANG</span>
                                                    </div>
                                                    <form action="{{ route('self.attend.skip', $myAttendanceToday->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-link text-danger btn-sm w-100 fw-bold text-decoration-none">
                                                            <i class="mdi mdi-close-circle-outline me-1"></i> Lewati & Tutup Sesi
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                            
                                            <div id="camera-view" class="{{ $myAttendanceToday->is_extended_shift ? '' : 'd-none' }} text-center">
                                                <a href="{{ route('self.attend.create', ['attendance_id' => $myAttendanceToday->id, 'mode' => 'pulang']) }}" class="btn btn-primary w-100 rounded-pill py-3 fw-bold">
                                                    <i class="mdi mdi-camera-plus me-2"></i> AMBIL FOTO PULANG
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        {{-- NORMAL CHECKOUT --}}
                                        <div class="mt-2">
                                            @if (Auth::user()->only_security_scan)
                                                <div class="alert alert-soft-danger rounded-4 border-0 py-3 mb-0">
                                                    <p class="small mb-0 text-center fw-bold">
                                                        <i class="mdi mdi-lock me-1"></i> Absen pulang mandiri dikunci. Silahkan scan ke Security.
                                                    </p>
                                                </div>
                                            @else
                                                <a href="{{ route('self.attend.create', ['attendance_id' => $myAttendanceToday->id, 'mode' => 'pulang']) }}" class="btn btn-danger-premium w-100 rounded-pill py-3 fw-bold shadow-lg">
                                                    <i class="mdi mdi-logout-variant me-2"></i> KONFIRMASI PULANG MANDIRI
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @elseif($myPendingLeave)
                            {{-- PENDING LEAVE --}}
                            <div class="status-box status-box-warning text-center py-5 px-4">
                                <div class="icon-circle-xl bg-warning mx-auto mb-4 pulse-animation-yellow">
                                    <i class="mdi mdi-timer-sand fs-1 text-white"></i>
                                </div>
                                <h4 class="fw-black mb-2">Menunggu Persetujuan</h4>
                                <p class="text-muted small mb-4">Pengajuan <strong>{{ strtoupper($myPendingLeave->type) }}</strong> sedang ditinjau oleh tim Audit.</p>
                                <form action="{{ route('leave-requests.cancel', $myPendingLeave->id) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-outline-danger btn-sm rounded-pill px-4">Batalkan Pengajuan</button>
                                </form>
                            </div>
                        @else
                            {{-- DEFAULT: NOT CLOCKED IN --}}
                            <div class="status-box status-box-empty text-center py-5 px-4">
                                <div class="icon-circle-xl bg-soft-primary text-primary mx-auto mb-4">
                                    <i class="mdi mdi-fingerprint fs-1"></i>
                                </div>
                                <h4 class="fw-black mb-1">Siap Memulai Shift?</h4>
                                <p class="text-muted small mb-4">Pilih metode absensi yang sesuai dengan aktivitasmu hari ini.</p>
                                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                                    @if (Auth::user()->only_security_scan)
                                        <button class="btn btn-secondary btn-lg rounded-pill px-4 fw-bold" disabled>
                                            <i class="mdi mdi-lock me-2"></i> SCAN QR SECURITY
                                        </button>
                                    @else
                                        <a href="{{ route('self.attend.create') }}" class="btn btn-primary btn-lg rounded-pill px-4 fw-bold shadow-lg">
                                            <i class="mdi mdi-camera-account me-2"></i> SELFIE CHECK-IN
                                        </a>
                                    @endif
                                    <a href="{{ route('leave-requests.create') }}" class="btn btn-outline-dark btn-lg rounded-pill px-4 fw-bold">
                                        <i class="mdi mdi-calendar-edit me-2"></i> IZIN / SAKIT
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CHART & ANALYTICS --}}
    <div class="row mt-5 animate-enter" style="animation-delay: 0.8s">
        <div class="col-12">
            <div class="card border-0 shadow-premium card-analytics overflow-hidden">
                <div class="card-header bg-white py-4 border-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="fw-black mb-0">ANALITIK KEHADIRAN</h4>
                            <p class="text-muted tiny mb-0 fw-bold">VISUALISASI DATA REAL-TIME</p>
                        </div>
                        <div class="chart-legend-custom d-flex gap-3">
                            <div class="l-item"><span class="dot bg-success"></span> On Time</div>
                            <div class="l-item"><span class="dot bg-warning"></span> Late</div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-5 mb-4 mb-md-0">
                            <div class="chart-canvas-wrapper">
                                <canvas id="attendancePieChartModern"></canvas>
                                <div class="chart-center-info">
                                    <h2 class="fw-black mb-0">{{ round(($stats['on_time'] / max($stats['on_time']+$stats['late'], 1)) * 100) }}%</h2>
                                    <span class="tiny text-muted fw-bold">RATIO</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="row g-3">
                                @php
                                    $statItems = [
                                        ['label' => 'Tepat Waktu', 'val' => $stats['on_time'], 'color' => 'success', 'icon' => 'mdi-check-circle'],
                                        ['label' => 'Keterlambatan', 'val' => $stats['late'], 'color' => 'warning', 'icon' => 'mdi-clock-alert'],
                                        ['label' => 'Izin/Sakit', 'val' => $stats['early'] ?? 0, 'color' => 'info', 'icon' => 'mdi-file-document'],
                                        ['label' => 'Total Record', 'val' => array_sum($stats), 'color' => 'primary', 'icon' => 'mdi-database-check']
                                    ];
                                @endphp
                                @foreach($statItems as $s)
                                <div class="col-sm-6">
                                    <div class="stat-progress-card p-3 rounded-4 border">
                                        <div class="d-flex justify-content-between mb-2">
                                            <i class="mdi {{ $s['icon'] }} text-{{ $s['color'] }} fs-5"></i>
                                            <span class="fw-black fs-5">{{ $s['val'] }}</span>
                                        </div>
                                        <span class="text-muted small fw-bold d-block mb-2">{{ $s['label'] }}</span>
                                        <div class="progress" style="height: 4px;">
                                            <div class="progress-bar bg-{{ $s['color'] }}" style="width: {{ ($s['val'] / max(array_sum($stats), 1)) * 100 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- PREMIUM MODALS --}}
    <div class="modal fade" id="profilePhotoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body p-0 text-center">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
                    <img src="" id="profileModalImageSrc" class="img-fluid rounded-5 shadow-2xl border border-white border-5" style="max-height: 80vh;">
                    <div class="mt-3 text-white">
                        <h4 class="fw-black mb-0">{{ Auth::user()->name }}</h4>
                        <span class="badge bg-primary px-4 rounded-pill mt-2">USER IDENTIFICATION</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 rounded-5 shadow-2xl overflow-hidden">
                <div class="modal-header border-0 bg-dark py-4 justify-content-center">
                    <h5 class="modal-title text-white fw-black">YOUR QR ACCESS</h5>
                </div>
                <div class="modal-body p-5 text-center bg-white">
                    <div id="qrcode-modal-display-v2" class="d-inline-block p-3 border rounded-4 shadow-inner mb-4"></div>
                    <p class="text-muted small fw-medium mb-0 px-3">Tunjukkan kode ini pada terminal scanner security untuk akses gedung.</p>
                </div>
                <div class="modal-footer border-0 bg-light py-3">
                    <button type="button" class="btn btn-dark w-100 rounded-pill fw-bold" data-bs-dismiss="modal">CLOSE ACCESS</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
    :root {
        --primary-modern: #4F46E5;
        --secondary-modern: #6366F1;
        --dark-modern: #0F172A;
        --glass-bg: rgba(255, 255, 255, 0.7);
        --shadow-premium: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
        --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
    }

    .fw-black { font-weight: 900; }
    .tracking-tighter { letter-spacing: -1.5px; }
    .tracking-widest { letter-spacing: 2px; }
    .tiny { font-size: 0.65rem; }

    /* CARDS */
    .shadow-premium { box-shadow: var(--shadow-premium) !important; }
    .shadow-premium-dark { box-shadow: 0 30px 60px rgba(0,0,0,0.12) !important; }

    /* BANK CARDS */
    .card-bank-premium {
        height: 200px;
        border-radius: 24px;
        border: none;
        position: relative;
        overflow: hidden;
        color: white;
        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .card-bank-premium:hover { transform: translateY(-10px) rotate(1deg); }
    .bank-chip { 
        width: 45px; height: 32px; 
        background: linear-gradient(135deg, #ffd700, #f8c02a); 
        border-radius: 8px; position: relative;
    }
    .bank-icon-bg {
        position: absolute; right: -20px; top: -10px;
        font-size: 100px; opacity: 0.15;
    }
    .bank-label { font-size: 11px; font-weight: 800; letter-spacing: 1px; margin-bottom: 5px; opacity: 0.8; }
    .bank-value { font-weight: 900; letter-spacing: -1px; }

    /* GRADIENTS */
    .grad-deep-blue { background: linear-gradient(135deg, #1e293b, #334155); }
    .grad-dark-indigo { background: linear-gradient(135deg, #4338ca, #6366f1); }
    .grad-emerald { background: linear-gradient(135deg, #059669, #10b981); }
    .grad-sunset { background: linear-gradient(135deg, #be123c, #fb7185); }
    .grad-crimson { background: linear-gradient(135deg, #991b1b, #ef4444); }
    .grad-royal { background: linear-gradient(135deg, #5b21b6, #8b5cf6); }
    .grad-dark-slate { background: linear-gradient(135deg, #0f172a, #1e293b); }

    /* BIRTHDAY CARD */
    .birthday-card-premium {
        background: linear-gradient(135deg, #4338ca 0%, #db2777 100%);
    }
    .luxury-overlay {
        position: absolute; inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 86c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm66-3c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm-46-45c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm54 24c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM57 7c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM11 40c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm35 31c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM9 26c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm18 40c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm54-46c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm-24 8c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm-40 72c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm56-11c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm-44-21c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm42-23c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm-74 52c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm58 4c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm-42 11c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm-18-46c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm42 2c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm-3-46c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zM42 2c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm57 31c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm-74 15c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm42 6c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm-21 24c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm-22-25c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zM33 70c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm53-26c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zM44 55c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm28 31c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm-27-8c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm20-52c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm11 44c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm-44-15c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm54-53c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm-44 13c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
        opacity: 0.4;
    }
    .countdown-unit {
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 20px;
        width: 80px; height: 90px;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
    }
    .countdown-unit.highlight { background: white; }
    .countdown-unit.highlight .label { color: #888; }
    .countdown-unit .value { font-size: 1.8rem; font-weight: 900; line-height: 1; }
    .countdown-unit .label { font-size: 0.6rem; font-weight: 800; margin-top: 5px; opacity: 0.7; }

    /* TITAN CARDS (Hall of Fame) */
    .hall-of-fame-wrapper {
        background: var(--dark-modern);
        border-radius: 40px;
        position: relative;
        overflow: hidden;
    }
    .titan-card {
        background: rgba(255,255,255,0.05);
        border-radius: 30px;
        padding: 30px;
        border: 1px solid rgba(255,255,255,0.1);
        transition: all 0.3s ease;
        position: relative;
    }
    .titan-card:hover { transform: scale(1.05); background: rgba(255,255,255,0.08); border-color: #ffd700; }
    .titan-rank {
        position: absolute; top: 20px; right: 20px;
        font-weight: 900; font-size: 1.2rem;
    }
    .rank-1 { color: #ffd700; }
    .rank-2 { color: #e5e7eb; }
    .rank-3 { color: #d97706; }
    .titan-image-wrapper { width: 120px; height: 120px; margin: 0 auto; border-radius: 50%; padding: 5px; background: linear-gradient(45deg, transparent, rgba(255,215,0,0.5)); }
    .titan-img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 4px solid var(--dark-modern); }
    .titan-placeholder { width: 100%; height: 100%; border-radius: 50%; background: #444; color: white; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 900; }
    .titan-stat { background: rgba(255,255,255,0.05); border-radius: 20px; padding: 15px; }

    /* ENTERPRISE ID CARD */
    .card-id-enterprise {
        background: linear-gradient(135deg, #0f172a, #334155);
        border-radius: 32px;
        min-height: 280px;
    }
    .id-photo { width: 85px; height: 105px; border-radius: 12px; object-fit: cover; border: 3px solid rgba(255,255,255,0.2); }
    .id-photo-placeholder { width: 85px; height: 105px; border-radius: 12px; background: #4F46E5; color: white; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 900; }
    .id-photo-wrapper { position: relative; }
    .id-active-dot { width: 16px; height: 16px; background: #10b981; border: 3px solid #0f172a; border-radius: 50%; position: absolute; bottom: -5px; right: -5px; }

    /* SLIDER PREMIUM */
    #slide-track-premium {
        height: 60px; background: #f1f5f9; border-radius: 30px;
        position: relative; display: flex; align-items: center;
        padding: 0 6px; overflow: hidden;
    }
    #slide-thumb-premium {
        width: 48px; height: 48px; background: var(--primary-modern);
        border-radius: 50%; display: flex; align-items: center;
        justify-content: center; color: white; font-size: 1.5rem;
        cursor: pointer; z-index: 2; position: absolute; left: 6px;
    }
    .slide-text { width: 100%; text-align: center; font-size: 0.75rem; font-weight: 800; color: #94a3b8; letter-spacing: 1px; }

    /* GALLERY MODERN */
    .gallery-modern-container { overflow-x: auto; padding-bottom: 20px; scrollbar-width: none; cursor: grab; }
    .gallery-modern-track { display: flex; gap: 20px; }
    .gallery-card-v2 {
        min-width: 180px; height: 260px; border-radius: 24px;
        overflow: hidden; position: relative; background: #eee;
        transition: all 0.4s ease;
    }
    .gallery-card-v2:hover { transform: translateY(-10px) scale(1.02); }
    .gallery-card-v2 img { width: 100%; height: 100%; object-fit: cover; }
    .gallery-info-v2 { position: absolute; bottom: 0; left: 0; right: 0; padding: 20px; background: linear-gradient(transparent, rgba(0,0,0,0.8)); display: flex; justify-content: space-between; align-items: center; }
    .gallery-info-v2 .date { color: white; font-weight: 900; font-size: 0.8rem; }

    /* ANIMATIONS */
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    .animate-enter { animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- REAL-TIME CLOCKS ---
        const updateClocks = () => {
            const tz = "{{ $current_timezone }}";
            const now = new Date();
            const timeStr = now.toLocaleTimeString('en-US', { timeZone: tz, hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
            
            ['header-clock-nav', 'realtime-clock-large'].forEach(id => {
                const el = document.getElementById(id);
                if(el) el.innerText = timeStr;
            });
        };
        setInterval(updateClocks, 1000);
        updateClocks();

        // --- COUNT UP ANIMATION ---
        document.querySelectorAll('.count-up').forEach(c => {
            const target = +c.dataset.target;
            let current = 0;
            const inc = target / 60;
            const update = () => {
                current += inc;
                if(current < target) {
                    c.innerText = Math.ceil(current);
                    requestAnimationFrame(update);
                } else { c.innerText = target; }
            };
            update();
        });

        // --- MODERN CHART ---
        const ctx = document.getElementById('attendancePieChartModern');
        if(ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['On Time', 'Late'],
                    datasets: [{
                        data: [{{ $stats['on_time'] }}, {{ $stats['late'] }}],
                        backgroundColor: ['#10b981', '#f59e0b'],
                        hoverOffset: 20,
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '85%',
                    plugins: { legend: { display: false } },
                    animation: { animateScale: true }
                }
            });
        }

        // --- QR CODE GENERATION ---
        const qrVal = "{{ Auth::user()->qr_code_value }}";
        if(qrVal) {
            new QRCode(document.getElementById("dashboard-qrcode-v2"), { text: qrVal, width: 45, height: 45, colorDark: "#000", colorLight: "#fff", correctLevel: QRCode.CorrectLevel.H });
            
            document.getElementById('qrModal').addEventListener('show.bs.modal', function() {
                const container = document.getElementById('qrcode-modal-display-v2');
                container.innerHTML = '';
                new QRCode(container, { text: qrVal, width: 220, height: 220 });
            });
        }

        // --- PREMIUM SLIDER LOGIC ---
        const thumb = document.getElementById('slide-thumb-premium');
        const track = document.getElementById('slide-track-premium');
        if(thumb && track) {
            let isDragging = false;
            let max = track.offsetWidth - thumb.offsetWidth - 12;

            const start = () => isDragging = true;
            const end = () => {
                if(!isDragging) return;
                isDragging = false;
                const current = parseInt(thumb.style.left) || 6;
                if(current > max * 0.8) {
                    thumb.style.left = max + 'px';
                    thumb.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i>';
                    confirmOvertime();
                } else {
                    thumb.style.transition = 'left 0.3s ease';
                    thumb.style.left = '6px';
                    setTimeout(() => thumb.style.transition = '', 300);
                }
            };
            const move = (e) => {
                if(!isDragging) return;
                const x = (e.pageX || e.touches[0].pageX) - track.getBoundingClientRect().left - 24;
                thumb.style.left = Math.max(6, Math.min(x, max)) + 'px';
            };

            thumb.addEventListener('mousedown', start);
            thumb.addEventListener('touchstart', start);
            window.addEventListener('mousemove', move);
            window.addEventListener('touchmove', move);
            window.addEventListener('mouseup', end);
            window.addEventListener('touchend', end);
        }

        function confirmOvertime() {
            fetch(`/attendance/{{ $myAttendanceToday->id ?? 0 }}/confirm-overtime`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            }).then(() => location.reload());
        }
    });

    function previewGalleryImage(src, title, date) {
        const modal = new bootstrap.Modal(document.getElementById('galleryPreviewModal') || document.createElement('div'));
        // Implementation for gallery preview
        alert('Membuka foto: ' + title + '\nTanggal: ' + date);
    }
</script>
@endpush