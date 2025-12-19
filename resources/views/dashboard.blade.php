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
            <h5 class="fw-bold mb-0 text-primary" id="header-clock">--:--:--</h5>
            <small class="text-muted">{{ \Carbon\Carbon::now($current_timezone)->translatedFormat('l, d F Y') }}</small>
        </div>
    </div>
@endsection

@section('content')

    {{-- BAGIAN BARU: ATTENDANCE WRAPPED (Desember Only) --}}
    @if (\Carbon\Carbon::now()->month == 12)
        <div class="row mb-4 animate-enter" style="animation-delay: 0.1s">
            <div class="col-12">
                <div class="card border-0 shadow-lg holiday-card" 
                     style="background: linear-gradient(135deg, #111827 0%, #1f2937 50%, #111827 100%); 
                            border: 2px solid rgba(255, 215, 0, 0.3);
                            position: relative;
                            overflow: hidden;">
                    
                    {{-- Glowing Effect --}}
                    <div class="holiday-glow"></div>
                    
                    {{-- Snowflakes --}}
                    <div class="snowflakes" aria-hidden="true">
                        @for($i = 0; $i < 10; $i++)
                            <div class="snowflake" style="animation-delay: {{ $i * 0.5 }}s; left: {{ $i * 10 }}%;"></div>
                        @endfor
                    </div>
                    
                    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center position-relative z-index-1 py-4">
                        <div class="d-flex align-items-center mb-3 mb-md-0">
                            <div class="me-3 d-none d-sm-block">
                                <div class="sparkle-icon">
                                    <i class="mdi mdi-sparkles text-warning"></i>
                                </div>
                            </div>
                            <div>
                                <h4 class="fw-bold text-warning mb-1" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                                    ✨ Your {{ date('Y') }} Wrapped is Here!
                                </h4>
                                <p class="mb-0 text-white-50 small">Lihat rangkuman perjalanan karirmu selama setahun ini.</p>
                            </div>
                        </div>
                        <a href="{{ route('attendance.recap') }}"
                           class="btn btn-holiday rounded-pill fw-bold shadow-lg hover-scale px-4 py-2">
                            <i class="mdi mdi-play-circle-outline me-2"></i> Putar Wrapped
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
            @php
                $adminWidgets = [
                    [
                        'delay' => '0.1s',
                        'gradient' => 'gradient-purple',
                        'icon' => 'mdi-account-multiple',
                        'label' => 'Total User',
                        'value' => $totalUsers,
                        'desc' => 'Karyawan Aktif'
                    ],
                    [
                        'delay' => '0.2s',
                        'gradient' => 'gradient-blue',
                        'icon' => 'mdi-office-building',
                        'label' => 'Total Cabang',
                        'value' => $totalBranches,
                        'desc' => 'Cabang Terdaftar'
                    ],
                    [
                        'delay' => '0.3s',
                        'gradient' => 'gradient-green',
                        'icon' => 'mdi-calendar-check',
                        'label' => 'Absensi Hari Ini',
                        'value' => $attendancesToday,
                        'desc' => 'Total absensi hari ini'
                    ],
                    [
                        'delay' => '0.4s',
                        'gradient' => 'gradient-orange',
                        'icon' => 'mdi-alert-circle-outline',
                        'label' => 'Perlu Verifikasi',
                        'value' => $pendingVerifications,
                        'desc' => 'Menunggu persetujuan'
                    ]
                ];
            @endphp
            
            @foreach($adminWidgets as $widget)
            <div class="col-md-3 grid-margin stretch-card animate-enter" style="animation-delay: {{ $widget['delay'] }}">
                <div class="card card-bank {{ $widget['gradient'] }} hover-lift">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi {{ $widget['icon'] }}"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">{{ $widget['label'] }}</p>
                            <h2 class="card-bank-value count-up" data-target="{{ $widget['value'] }}">0</h2>
                            <p class="card-bank-desc">{{ $widget['desc'] }}</p>
                        </div>
                        <div class="card-bank-pattern"></div>
                        <div class="card-bank-corner">
                            <i class="mdi {{ $widget['icon'] }}"></i>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @elseif (auth()->user()->role == 'audit')
        {{-- WIDGET AUDIT --}}
        <div class="row mb-4">
            <div class="col-md-4 grid-margin stretch-card animate-enter" style="animation-delay: 0.1s">
                <div class="card card-bank gradient-red hover-lift">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi mdi-alert-circle-outline"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Verif Absensi</p>
                            <h2 class="card-bank-value count-up" data-target="{{ $pendingVerifications }}">0</h2>
                            <p class="card-bank-desc">Absensi pending (Foto/Lokasi)</p>
                            <a href="{{ route('audit.verify.list') }}" class="btn btn-sm btn-light mt-2 shadow-sm hover-scale">
                                <i class="mdi mdi-clipboard-check me-1"></i>Lihat Daftar
                            </a>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 grid-margin stretch-card animate-enter" style="animation-delay: 0.2s">
                <div class="card card-bank gradient-blue hover-lift">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi mdi-file-document-edit-outline"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Approve Izin</p>
                            <h2 class="card-bank-value count-up" data-target="{{ $pendingLeaves }}">0</h2>
                            <p class="card-bank-desc">Izin, Sakit, Cuti, WFH, Telat</p>
                            <a href="{{ route('leave-requests.index') }}" class="btn btn-sm btn-light mt-2 shadow-sm hover-scale">
                                <i class="mdi mdi-playlist-check me-1"></i>Lihat Pengajuan
                            </a>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 grid-margin stretch-card animate-enter" style="animation-delay: 0.3s">
                <div class="card card-bank gradient-green hover-lift">
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
                <div class="card card-action hover-float bg-gradient-dark text-white">
                    <div class="card-body text-center py-5">
                        <div class="mb-4 pulse-icon-wrapper">
                            <div class="qr-scan-icon">
                                <i class="mdi mdi-qrcode-scan"></i>
                                <div class="qr-scan-line"></div>
                            </div>
                        </div>
                        <h4 class="card-title mb-3">Pindai QR User</h4>
                        <p class="text-light mb-4">Arahkan kamera ke QR Code user untuk melakukan absensi.</p>
                        <a href="{{ route('security.scan') }}" class="btn btn-light btn-lg shadow-lg hover-scale px-4">
                            <i class="mdi mdi-camera-enhance me-2"></i>Mulai Memindai
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 grid-margin stretch-card animate-enter" style="animation-delay: 0.2s">
                <div class="card card-bank gradient-dark hover-lift">
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
        @if(auth()->user()->role != 'security' && isset($leaderboard) && count($leaderboard) > 0)
        <div class="col-12">
            <div class="card border-0 shadow-lg luxury-card overflow-hidden hover-float">
                <div class="luxury-bg-glow"></div>
                <div class="luxury-bg-pattern"></div>
                
                <div class="card-body p-4 position-relative z-index-1">
                    <div class="text-center mb-5">
                        <div class="trophy-icon mb-3">
                            <i class="mdi mdi-trophy"></i>
                        </div>
                        <h3 class="fw-bold mb-1 luxury-title">
                            Top Rajin Absen
                        </h3>
                        <p class="text-muted small">
                            {{ auth()->user()->role == 'admin' ? 'Global' : 'Cabang Anda' }} - Bulan {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}
                        </p>
                    </div>

                    {{-- PODIUM CONTAINER --}}
                    <div class="podium-luxury-container d-flex justify-content-center align-items-end gap-4">
                        
                        {{-- JUARA 2 (KIRI) --}}
                        @if(isset($leaderboard[1]))
                            <div class="podium-step-container animate-enter" style="animation-delay: 0.6s">
                                <div class="podium-avatar-wrapper silver-glow">
                                    @if($leaderboard[1]->user->profile_photo_path)
                                        <img src="{{ asset('storage/'.$leaderboard[1]->user->profile_photo_path) }}" class="luxury-avatar">
                                    @else
                                        <div class="luxury-avatar-placeholder silver-gradient">
                                            {{ substr($leaderboard[1]->user->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div class="rank-circle silver">2</div>
                                </div>
                                <div class="podium-block silver-block text-center">
                                    <div class="podium-content">
                                        <h6 class="fw-bold text-dark mb-1">{{ Str::limit($leaderboard[1]->user->name, 12) }}</h6>
                                        <p class="small text-muted mb-2">{{ $leaderboard[1]->user->division->name ?? '-' }}</p>
                                        <div class="stat-pill mb-1">
                                            <i class="mdi mdi-check-circle text-success me-1"></i>{{ $leaderboard[1]->total_attendance }} Hadir
                                        </div>
                                        <div class="stat-pill">
                                            <i class="mdi mdi-clock-outline text-primary me-1"></i>{{ $leaderboard[1]->avg_arrival_display }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- JUARA 1 (TENGAH - TERTINGGI) --}}
                        @if(isset($leaderboard[0]))
                            <div class="podium-step-container main-winner animate-enter" style="animation-delay: 0.5s">
                                <div class="crown-floating">
                                    <div class="crown-wrapper">
                                        <i class="mdi mdi-crown"></i>
                                    </div>
                                </div>
                                <div class="podium-avatar-wrapper gold-glow">
                                    @if($leaderboard[0]->user->profile_photo_path)
                                        <img src="{{ asset('storage/'.$leaderboard[0]->user->profile_photo_path) }}" class="luxury-avatar">
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
                                    <div class="podium-content pt-4">
                                        <h5 class="fw-bold text-dark mb-1">{{ Str::limit($leaderboard[0]->user->name, 15) }}</h5>
                                        <p class="small text-muted mb-2">{{ $leaderboard[0]->user->division->name ?? '-' }}</p>
                                        <div class="stat-pill gold mb-2">
                                            <i class="mdi mdi-star me-1"></i>{{ $leaderboard[0]->total_attendance }} Kehadiran
                                        </div>
                                        <div class="stat-pill light">
                                            <i class="mdi mdi-timer-sand me-1"></i>Avg: {{ $leaderboard[0]->avg_arrival_display }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- JUARA 3 (KANAN) --}}
                        @if(isset($leaderboard[2]))
                            <div class="podium-step-container animate-enter" style="animation-delay: 0.7s">
                                <div class="podium-avatar-wrapper bronze-glow">
                                    @if($leaderboard[2]->user->profile_photo_path)
                                        <img src="{{ asset('storage/'.$leaderboard[2]->user->profile_photo_path) }}" class="luxury-avatar">
                                    @else
                                        <div class="luxury-avatar-placeholder bronze-gradient">
                                            {{ substr($leaderboard[2]->user->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div class="rank-circle bronze">3</div>
                                </div>
                                <div class="podium-block bronze-block text-center">
                                    <div class="podium-content">
                                        <h6 class="fw-bold text-dark mb-1">{{ Str::limit($leaderboard[2]->user->name, 12) }}</h6>
                                        <p class="small text-muted mb-2">{{ $leaderboard[2]->user->division->name ?? '-' }}</p>
                                        <div class="stat-pill mb-1">
                                            <i class="mdi mdi-check me-1"></i>{{ $leaderboard[2]->total_attendance }} Hadir
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
        @if((auth()->user()->role == 'admin' || auth()->user()->role == 'security') && isset($topScanners) && count($topScanners) > 0)
        <div class="col-12 mt-4">
             <div class="card border-0 shadow-lg luxury-card overflow-hidden hover-float">
                <div class="luxury-bg-pattern"></div>
                <div class="card-body p-4 position-relative z-index-1">
                    <div class="text-center mb-5">
                        <div class="scanner-icon mb-3">
                            <i class="mdi mdi-qrcode-scan"></i>
                        </div>
                        <h3 class="fw-bold mb-1 luxury-title">
                            Top Security Scanner
                        </h3>
                        <p class="text-muted small">Total Scan Terbanyak Bulan Ini</p>
                    </div>

                     {{-- PODIUM CONTAINER --}}
                     <div class="podium-luxury-container d-flex justify-content-center align-items-end gap-4">
                        
                        {{-- JUARA 2 --}}
                        @if(isset($topScanners[1]))
                            <div class="podium-step-container">
                                <div class="podium-avatar-wrapper silver-glow">
                                     @if($topScanners[1]->profile_photo_path)
                                        <img src="{{ asset('storage/'.$topScanners[1]->profile_photo_path) }}" class="luxury-avatar">
                                    @else
                                        <div class="luxury-avatar-placeholder silver-gradient">{{ substr($topScanners[1]->name, 0, 1) }}</div>
                                    @endif
                                    <div class="rank-circle silver">2</div>
                                </div>
                                <div class="podium-block silver-block text-center">
                                    <div class="podium-content">
                                        <h6 class="fw-bold text-dark mb-1">{{ Str::limit($topScanners[1]->name, 12) }}</h6>
                                        <div class="stat-pill mt-2">
                                            <i class="mdi mdi-qrcode me-1"></i>{{ $topScanners[1]->total_scans }} Scan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- JUARA 1 --}}
                        @if(isset($topScanners[0]))
                            <div class="podium-step-container main-winner">
                                <div class="crown-floating">
                                    <div class="crown-wrapper">
                                        <i class="mdi mdi-crown"></i>
                                    </div>
                                </div>
                                <div class="podium-avatar-wrapper gold-glow">
                                    @if($topScanners[0]->profile_photo_path)
                                        <img src="{{ asset('storage/'.$topScanners[0]->profile_photo_path) }}" class="luxury-avatar">
                                    @else
                                        <div class="luxury-avatar-placeholder gold-gradient">{{ substr($topScanners[0]->name, 0, 1) }}</div>
                                    @endif
                                    <div class="rank-circle gold">1</div>
                                </div>
                                <div class="podium-block gold-block text-center">
                                    <div class="podium-content pt-4">
                                        <h5 class="fw-bold text-dark mb-1">{{ Str::limit($topScanners[0]->name, 15) }}</h5>
                                        <div class="stat-pill gold mt-2">
                                            <i class="mdi mdi-star me-1"></i>{{ $topScanners[0]->total_scans }} Scan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- JUARA 3 --}}
                        @if(isset($topScanners[2]))
                            <div class="podium-step-container">
                                <div class="podium-avatar-wrapper bronze-glow">
                                     @if($topScanners[2]->profile_photo_path)
                                        <img src="{{ asset('storage/'.$topScanners[2]->profile_photo_path) }}" class="luxury-avatar">
                                    @else
                                        <div class="luxury-avatar-placeholder bronze-gradient">{{ substr($topScanners[2]->name, 0, 1) }}</div>
                                    @endif
                                    <div class="rank-circle bronze">3</div>
                                </div>
                                <div class="podium-block bronze-block text-center">
                                    <div class="podium-content">
                                        <h6 class="fw-bold text-dark mb-1">{{ Str::limit($topScanners[2]->name, 12) }}</h6>
                                        <div class="stat-pill mt-2">
                                            <i class="mdi mdi-qrcode me-1"></i>{{ $topScanners[2]->total_scans }} Scan
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
            <div class="d-flex align-items-center mb-3">
                <div class="section-icon">
                    <i class="mdi mdi-account-circle"></i>
                </div>
                <h4 class="card-title mb-0 ms-3">Absensi Pribadi</h4>
            </div>
        </div>
    </div>

    <div class="row animate-enter" style="animation-delay: 0.6s">
        {{-- KARTU ID & QR CODE (Quick Access) --}}
        <div class="col-md-5 grid-margin stretch-card">
            <div class="row w-100 m-0 p-0">

                {{-- ID CARD VISUAL --}}
                <div class="col-12 mb-3">
                    <div class="card card-id gradient-dark hover-lift">
                        <div class="card-id-glow"></div>
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
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
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
            <div class="card card-status hover-shadow-lg h-100">
                <div class="card-body d-flex flex-column">
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
                            <div class="clock-display">
                                <h4 class="fw-bold mb-0 font-monospace text-primary" id="realtime-clock">--:--:--</h4>
                            </div>
                            {{-- TAMPILKAN TIMEZONE --}}
                            <small class="text-muted d-block" style="font-size: 0.7rem;">
                                {{ \Carbon\Carbon::now($current_timezone)->translatedFormat('l, d F Y') }}
                                <span class="timezone-badge">{{ $current_timezone }}</span>
                            </small>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm animate-pulse-green">
                            <i class="mdi mdi-check-circle-outline me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm animate-pulse-red">
                            <i class="mdi mdi-alert-circle-outline me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show shadow-sm animate-pulse-orange">
                            <i class="mdi mdi-alert-outline me-2"></i>
                            {{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- LOGIKA TAMPILAN STATUS --}}
                    <div class="flex-grow-1 d-flex align-items-center">
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
                                <div class="status-card status-success w-100 mb-3 animate-pulse-green">
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
                                    class="status-card {{ $isCrossDay ? 'status-warning' : 'status-success' }} w-100 mb-3 position-relative overflow-hidden">


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

                                                        {{-- OPSI 1: SLIDER UNTUK FOTO (NORMAL) --}}
                                                        <div class="mb-3" id="slider-view">
                                                            <p class="text-muted small mb-1">
                                                                <i class="mdi mdi-camera me-1"></i><strong>Opsi 1:</strong>
                                                                Absen Pulang Normal (Foto)
                                                            </p>

                                                            <div class="position-relative w-100 rounded-pill d-flex align-items-center px-1 user-select-none shadow-sm"
                                                                id="slide-track"
                                                                style="height: 50px; background-color: #fde047; transition: all 0.2s;">

                                                                <div class="position-absolute w-100 text-center"
                                                                    style="pointer-events: none; left:0;">
                                                                    <span class="fw-bold text-dark small opacity-75"
                                                                        style="letter-spacing: 1px;">GESER KE KANAN >></span>
                                                                </div>

                                                                <div id="slide-thumb"
                                                                    class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center text-warning"
                                                                    style="width: 42px; height: 42px; cursor: pointer; position: absolute; left: 4px; z-index: 10;">
                                                                    <i class="mdi mdi-arrow-right fw-bold fs-5"></i>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- OPSI 2: TOMBOL LEWATI (TANPA FOTO) --}}
                                                        <form action="{{ route('self.attend.skip', $myAttendanceToday->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            <p class="text-muted small mb-1">
                                                                <i class="mdi mdi-cancel me-1"></i><strong>Opsi 2:</strong>
                                                                Lupa Absen (Tanpa Foto)
                                                            </p>
                                                            <button type="submit"
                                                                class="btn btn-outline-danger w-100 py-2 shadow-sm hover-scale"
                                                                onclick="return confirm('Pilih ini jika Anda LUPA absen pulang kemarin.\nSesi akan ditutup otomatis TANPA FOTO.\nStatus kemarin akan menjadi \'Verified/Present\' tapi ada catatan skip.\n\nLanjutkan?');">
                                                                <i class="mdi mdi-skip-forward me-2"></i>Lewati & Tutup Sesi
                                                            </button>
                                                        </form>
                                                    </div>

                                                    {{-- FASE 3: CAMERA BUTTON (Muncul setelah slide) --}}
                                                    <div id="camera-view" class="d-none text-center animate-enter mt-3">
                                                        <div class="mb-3">
                                                            <h6 class="text-primary fw-bold">Konfirmasi Pulang</h6>
                                                            <p class="text-muted small">Silahkan ambil foto selfie untuk
                                                                validasi.</p>
                                                        </div>
                                                        <a href="{{ route('self.attend.create', ['attendance_id' => $myAttendanceToday->id, 'mode' => 'pulang']) }}"
                                                            class="btn btn-primary w-100 py-3 rounded-3 shadow-sm fw-bold hover-scale">
                                                            <i class="mdi mdi-camera-party-mode me-2"></i> Ambil Foto & Pulang
                                                        </a>
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
                                                    class="btn btn-danger btn-sm w-100 shadow hover-scale py-2">
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
                            <div class="status-card status-warning w-100 mb-3 hover-shadow-lg">
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
                                        <button type="submit" class="btn btn-danger btn-sm shadow-sm hover-scale"
                                            onclick="return confirm('Batalkan pengajuan ini?')">
                                            <i class="mdi mdi-close-circle me-1"></i> Batalkan Pengajuan
                                        </button>
                                    </form>
                                </div>
                            </div>
                            {{-- 3. JIKA SUDAH DI APPROVE (HIJAU) --}}
                        @elseif(isset($myLeaveToday) && $myLeaveToday && $myLeaveToday->status == 'approved')
                            <div class="status-card status-success w-100 mb-3 hover-float">
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
                                                <button type="button" class="btn btn-sm btn-light border shadow-sm hover-scale"
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
                                            class="btn btn-primary btn-sm w-100 shadow-sm hover-scale py-2">
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
                                                class="btn btn-outline-danger btn-sm w-100 shadow-sm hover-scale py-2"
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
                                <div class="status-card status-info w-100 mb-3 hover-shadow-lg">
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
                                                    class="btn btn-outline-info shadow hover-scale py-2 px-4">
                                                    <i class="mdi mdi-fingerprint me-2"></i>Absen Shift Baru
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{-- 4. BELUM ABSEN (DEFAULT) --}}
                                <div class="status-card status-info hover-shadow-lg w-100">
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
                                                    class="btn btn-dark shadow hover-scale py-2 px-4">
                                                    <i class="mdi mdi-fingerprint me-2"></i>Absen Mandiri
                                                </a>
                                            @endif

                                            <a href="{{ route('leave-requests.create') }}"
                                                class="btn btn-outline-dark shadow-sm hover-scale py-2 px-4">
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
    </div>

    {{-- ======================================================================= --}}
    {{-- BAGIAN BARU: MENU CEPAT (QUICK ACTIONS)                               --}}
    {{-- ======================================================================= --}}
    <div class="row animate-enter mb-4" style="animation-delay: 0.7s">
        <div class="col-12">
            <div class="card shadow-sm border-0 quick-actions-card">
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
                                class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold flex-grow-1 flex-md-grow-0 hover-scale py-2">
                                <i class="mdi mdi-file-document-edit-outline me-2"></i> Ajukan Izin / Sakit
                            </a>

                            {{-- Tombol Riwayat (Opsional, agar seimbang) --}}
                            <a href="{{ route('attendance.history') }}"
                                class="btn btn-outline-secondary rounded-pill px-4 shadow-sm fw-bold flex-grow-1 flex-md-grow-0 hover-scale py-2">
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
            <div class="card shadow-sm hover-float">
                <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="chart-icon me-3">
                            <i class="mdi mdi-chart-pie"></i>
                        </div>
                        <h4 class="card-title mb-0">Statistik Absensi</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="chart-container" style="position: relative; height:300px;">
                                <canvas id="attendancePieChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-center justify-content-center">
                            <div class="text-center p-4">
                                <div class="chart-illustration mb-3">
                                    <i class="mdi mdi-chart-bar-stacked"></i>
                                </div>
                                <p class="text-muted mb-0">Analisis data kehadiran secara realtime</p>
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
                    <button type="button" class="btn btn-dark rounded-pill px-4 hover-scale" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        /* === ENHANCED ANIMATIONS === */
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

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        @keyframes pulseGlow {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }

        /* === ENHANCED CARD STYLES === */
        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .hover-lift:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15) !important;
        }

        /* === HOLIDAY CARD ENHANCEMENT === */
        .holiday-card {
            position: relative;
            overflow: hidden;
        }

        .holiday-glow {
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 215, 0, 0.1) 0%, transparent 70%);
            animation: rotateGlow 20s linear infinite;
            z-index: 0;
        }

        .snowflakes {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .snowflake {
            position: absolute;
            width: 6px;
            height: 6px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            animation: fall linear infinite;
            opacity: 0.8;
        }

        @keyframes fall {
            0% {
                transform: translateY(-100px) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(100px) rotate(360deg);
                opacity: 0;
            }
        }

        .sparkle-icon {
            position: relative;
            font-size: 48px;
            animation: float 3s ease-in-out infinite;
        }

        .btn-holiday {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #000;
            border: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-holiday:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(255, 215, 0, 0.3);
        }

        /* === ENHANCED BANK CARDS === */
        .card-bank {
            position: relative;
            min-height: 200px;
            border-radius: 20px;
            overflow: hidden;
            border: none;
        }

        .card-bank-corner {
            position: absolute;
            bottom: 20px;
            right: 20px;
            font-size: 24px;
            opacity: 0.1;
        }

        .card-bank-chip {
            width: 45px;
            height: 35px;
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            border-radius: 8px;
            position: relative;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transform: perspective(100px) rotateX(20deg);
        }

        .card-bank-chip:before {
            content: '';
            position: absolute;
            top: 5px;
            left: 5px;
            right: 5px;
            bottom: 5px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }

        .card-bank-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 56px;
            opacity: 0.15;
        }

        .card-bank-value {
            font-family: 'JetBrains Mono', 'Consolas', monospace;
            font-size: 40px;
            font-weight: 800;
            margin-bottom: 8px;
            line-height: 1;
            background: linear-gradient(135deg, #fff 0%, rgba(255,255,255,0.8) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        /* === ENHANCED LEADERBOARD === */
        .luxury-card {
            background: linear-gradient(135deg, #ffffff 0%, #f9fbfd 100%);
            position: relative;
            border-radius: 24px;
            overflow: hidden;
        }

        .luxury-bg-glow {
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 223, 0, 0.08) 0%, rgba(255, 255, 255, 0) 70%);
            animation: rotateGlow 20s linear infinite;
            z-index: 0;
        }

        .luxury-title {
            font-family: 'Playfair Display', serif;
            background: linear-gradient(135deg, #2D3748 0%, #4A5568 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2rem;
        }

        .trophy-icon, .scanner-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            box-shadow: 0 8px 20px rgba(255, 215, 0, 0.3);
        }

        .scanner-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Enhanced Avatars */
        .podium-avatar-wrapper {
            position: relative;
            margin-bottom: 20px;
            z-index: 2;
        }

        .gold-glow::before,
        .silver-glow::before,
        .bronze-glow::before {
            content: '';
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            z-index: -1;
            animation: pulseGlow 2s infinite;
        }

        .gold-glow::before {
            background: radial-gradient(circle, rgba(255, 215, 0, 0.4) 0%, transparent 70%);
        }

        .silver-glow::before {
            background: radial-gradient(circle, rgba(192, 192, 192, 0.4) 0%, transparent 70%);
        }

        .bronze-glow::before {
            background: radial-gradient(circle, rgba(205, 127, 50, 0.4) 0%, transparent 70%);
        }

        .luxury-avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #fff;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .main-winner .luxury-avatar {
            width: 120px;
            height: 120px;
            border-width: 6px;
        }

        .luxury-avatar:hover {
            transform: scale(1.05) rotate(5deg);
        }

        .crown-wrapper {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            box-shadow: 0 8px 20px rgba(255, 215, 0, 0.4);
        }

        /* Enhanced Podium Blocks */
        .podium-block {
            width: 100%;
            border-radius: 20px 20px 0 0;
            padding: 25px 15px;
            position: relative;
            backdrop-filter: blur(10px);
            background: linear-gradient(180deg, rgba(255,255,255,0.9) 0%, rgba(255,255,255,0.7) 100%);
            border: 2px solid rgba(255,255,255,0.8);
        }

        .gold-block {
            height: 200px;
            background: linear-gradient(180deg, rgba(255, 236, 179, 0.6) 0%, rgba(255, 255, 255, 0.3) 100%);
            border-top: 6px solid #FFD700;
            box-shadow: 0 15px 40px rgba(255, 215, 0, 0.2);
        }

        .silver-block {
            height: 160px;
            background: linear-gradient(180deg, rgba(245, 245, 245, 0.6) 0%, rgba(255, 255, 255, 0.3) 100%);
            border-top: 4px solid #C0C0C0;
        }

        .bronze-block {
            height: 140px;
            background: linear-gradient(180deg, rgba(239, 219, 207, 0.6) 0%, rgba(255, 255, 255, 0.3) 100%);
            border-top: 4px solid #CD7F32;
        }

        /* Enhanced Stat Pills */
        .stat-pill {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 25px;
            font-size: 12px;
            font-weight: 700;
            background: white;
            color: #555;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .stat-pill:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-pill.gold {
            background: linear-gradient(45deg, #FFD700, #FDB931);
            color: white;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
        }

        .stat-pill.light {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0,0,0,0.1);
        }

        /* === ENHANCED ID CARD === */
        .card-id {
            position: relative;
            border-radius: 24px;
            overflow: hidden;
            min-height: 240px;
        }

        .card-id-glow {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 100%);
            z-index: 1;
        }

        .id-card-img {
            width: 70px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            border: 3px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .id-card-img:hover {
            transform: scale(1.15) rotate(2deg);
            border-color: #fff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        }

        .id-card-img-placeholder {
            width: 70px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            border: 3px solid rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 28px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
        }

        .card-id-name {
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 15px;
            line-height: 1.2;
            font-family: 'JetBrains Mono', monospace;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.4);
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* === ENHANCED STATUS CARDS === */
        .status-card {
            padding: 28px;
            border-radius: 16px;
            border: 3px solid;
            background: white;
            position: relative;
            overflow: hidden;
        }

        .status-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.8), transparent);
            animation: shimmer 2s infinite;
        }

        .status-success {
            border-color: #10b981;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.1);
        }

        .status-success::before {
            background: linear-gradient(90deg, transparent, #10b981, transparent);
        }

        .status-warning {
            border-color: #f59e0b;
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.1);
        }

        .status-info {
            border-color: #3b82f6;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.1);
        }

        .status-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 24px;
            font-size: 32px;
            flex-shrink: 0;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        /* === ENHANCED BUTTONS === */
        .btn {
            border-radius: 12px;
            font-weight: 600;
            padding: 12px 28px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-dark {
            background: linear-gradient(135deg, #2D3748 0%, #4A5568 100%);
            border: none;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-dark:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        /* === ENHANCED QUICK ACTIONS === */
        .quick-actions-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .icon-box {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .icon-box:hover {
            transform: rotate(15deg) scale(1.1);
        }

        /* === ENHANCED CHART SECTION === */
        .chart-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .chart-illustration {
            font-size: 80px;
            color: #e5e7eb;
            opacity: 0.5;
        }

        /* === ENHANCED ANIMATION STATES === */
        .animate-pulse-green {
            animation: pulseGreen 2s infinite;
        }

        @keyframes pulseGreen {
            0%, 100% { box-shadow: 0 10px 30px rgba(16, 185, 129, 0.1); }
            50% { box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3); }
        }

        .animate-pulse-red {
            animation: pulseRed 2s infinite;
        }

        @keyframes pulseRed {
            0%, 100% { box-shadow: 0 10px 30px rgba(239, 68, 68, 0.1); }
            50% { box-shadow: 0 10px 30px rgba(239, 68, 68, 0.3); }
        }

        .animate-pulse-orange {
            animation: pulseOrange 2s infinite;
        }

        @keyframes pulseOrange {
            0%, 100% { box-shadow: 0 10px 30px rgba(245, 158, 11, 0.1); }
            50% { box-shadow: 0 10px 30px rgba(245, 158, 11, 0.3); }
        }

        /* === QR SCAN ICON ANIMATION === */
        .qr-scan-icon {
            position: relative;
            font-size: 64px;
            color: #fff;
        }

        .qr-scan-line {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, transparent, #fff, transparent);
            animation: scanLine 2s infinite linear;
        }

        @keyframes scanLine {
            0% { top: 0; }
            100% { top: 100%; }
        }

        /* === SECTION ICONS === */
        .section-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        /* === TIMEZONE BADGE === */
        .timezone-badge {
            display: inline-block;
            padding: 2px 8px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 4px;
            font-size: 0.65rem;
            color: #3b82f6;
        }

        /* === CLOCK DISPLAY === */
        .clock-display {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%);
            padding: 8px 16px;
            border-radius: 12px;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        /* === RESPONSIVE ENHANCEMENTS === */
        @media (max-width: 768px) {
            .card-bank-value {
                font-size: 32px;
            }
            
            .luxury-avatar {
                width: 70px;
                height: 70px;
            }
            
            .main-winner .luxury-avatar {
                width: 90px;
                height: 90px;
            }
            
            .podium-block {
                padding: 15px 10px;
            }
            
            .gold-block { height: 160px; }
            .silver-block { height: 130px; }
            .bronze-block { height: 110px; }
            
            .btn {
                padding: 10px 20px;
            }
        }

        /* === ACCESSIBILITY ENHANCEMENTS === */
        .focus-visible:focus {
            outline: 3px solid #3b82f6;
            outline-offset: 2px;
        }

        /* === PERFORMANCE OPTIMIZATIONS === */
        .will-change-transform {
            will-change: transform;
        }

        /* === DARK MODE SUPPORT (Optional) === */
        @media (prefers-color-scheme: dark) {
            .luxury-card {
                background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            }
            
            .luxury-title {
                background: linear-gradient(135deg, #fff 0%, #d1d5db 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            
            .status-card {
                background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.02) 100%);
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
            // Initialize all components with enhanced effects
            
            // --- SLIDER LOGIC (Enhanced) ---
            const track = document.getElementById('slide-track');
            const thumb = document.getElementById('slide-thumb');
            const cameraView = document.getElementById('camera-view');
            const actionsContainer = document.getElementById('cross-day-actions');

            if (track && thumb) {
                let isDragging = false;
                let startX;
                let trackWidth = track.clientWidth;
                let thumbWidth = thumb.clientWidth;
                let maxMove = trackWidth - thumbWidth - 8;

                // Update on resize
                window.addEventListener('resize', () => {
                    trackWidth = track.clientWidth;
                    maxMove = trackWidth - thumbWidth - 8;
                });

                // Start drag with improved touch support
                const startDrag = (e) => {
                    e.preventDefault();
                    isDragging = true;
                    startX = (e.type === 'touchstart') ? e.touches[0].clientX : e.clientX;
                    thumb.style.transition = 'none';
                    track.style.transition = 'background-color 0.3s ease';
                };

                // Smooth drag with velocity
                const drag = (e) => {
                    if (!isDragging) return;
                    
                    const clientX = (e.type === 'touchmove') ? e.touches[0].clientX : e.clientX;
                    const deltaX = clientX - startX;
                    
                    // Add easing for smoother movement
                    let moveX = Math.max(0, Math.min(deltaX, maxMove));
                    thumb.style.transform = `translateX(${moveX}px) translateZ(0)`;
                    
                    // Update track color based on progress
                    const progress = moveX / maxMove;
                    track.style.backgroundColor = `hsl(54, ${70 + progress * 30}%, ${70 + progress * 20}%)`;
                    
                    // Update text opacity
                    const text = track.querySelector('span');
                    if (text) {
                        text.style.opacity = 1 - progress;
                        text.style.transform = `scale(${1 - progress * 0.2})`;
                    }
                };

                // End drag with success animation
                const endDrag = () => {
                    if (!isDragging) return;
                    isDragging = false;
                    
                    const style = window.getComputedStyle(thumb);
                    const matrix = new DOMMatrix(style.transform);
                    const currentTranslateX = matrix.m41;
                    
                    // Success threshold
                    if (currentTranslateX > (maxMove * 0.75)) {
                        thumb.style.transform = `translateX(${maxMove}px) translateZ(0)`;
                        thumb.style.transition = 'transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)';
                        track.style.backgroundColor = '#86efac';
                        finishSlide();
                    } else {
                        // Reset with bounce animation
                        thumb.style.transform = 'translateX(0px) translateZ(0)';
                        thumb.style.transition = 'transform 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55)';
                        track.style.backgroundColor = '#fde047';
                        const text = track.querySelector('span');
                        if (text) {
                            text.style.opacity = 0.75;
                            text.style.transform = 'scale(1)';
                        }
                    }
                };

                // Add event listeners with passive for performance
                thumb.addEventListener('mousedown', startDrag, { passive: false });
                thumb.addEventListener('touchstart', startDrag, { passive: false });
                
                document.addEventListener('mousemove', drag, { passive: true });
                document.addEventListener('touchmove', drag, { passive: true });
                
                document.addEventListener('mouseup', endDrag);
                document.addEventListener('touchend', endDrag);

                function finishSlide() {
                    // Change icon with animation
                    thumb.innerHTML = '<i class="mdi mdi-check-circle text-success fs-4"></i>';
                    thumb.classList.add('pulse-animation');
                    
                    // Hide actions and show camera with delay
                    setTimeout(() => {
                        if (actionsContainer) actionsContainer.style.opacity = '0';
                        setTimeout(() => {
                            if (actionsContainer) actionsContainer.classList.add('d-none');
                            cameraView.classList.remove('d-none');
                            cameraView.style.opacity = '0';
                            cameraView.style.transform = 'translateY(20px)';
                            
                            // Animate camera view in
                            setTimeout(() => {
                                cameraView.style.opacity = '1';
                                cameraView.style.transform = 'translateY(0)';
                                cameraView.style.transition = 'all 0.5s ease';
                            }, 50);
                        }, 300);
                    }, 500);
                }
            }

            // --- ENHANCED LIVE CLOCK WITH TIMEZONE ---
            function updateClock() {
                const timeZone = "{{ $current_timezone }}";
                const now = new Date();
                
                try {
                    const timeString = now.toLocaleTimeString('en-US', {
                        timeZone: timeZone,
                        hour12: false,
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                    
                    // Update main clock
                    const clockElement = document.getElementById('realtime-clock');
                    if (clockElement) {
                        clockElement.textContent = timeString;
                        // Add subtle pulse animation on second change
                        clockElement.style.transform = 'scale(1.05)';
                        setTimeout(() => {
                            clockElement.style.transform = 'scale(1)';
                        }, 100);
                    }
                    
                    // Update header clock
                    const headerClock = document.getElementById('header-clock');
                    if (headerClock) headerClock.textContent = timeString;
                    
                    // Update greeting based on local time
                    const localHour = parseInt(timeString.split(':')[0]);
                    const greetingElement = document.getElementById('greeting-text');
                    
                    let greeting = 'Selamat Datang,';
                    if (localHour >= 5 && localHour < 12) greeting = 'Selamat Pagi,';
                    else if (localHour >= 12 && localHour < 15) greeting = 'Selamat Siang,';
                    else if (localHour >= 15 && localHour < 18) greeting = 'Selamat Sore,';
                    else greeting = 'Selamat Malam,';
                    
                    if (greetingElement) greetingElement.textContent = greeting;
                    
                } catch (error) {
                    console.error('Error updating clock:', error);
                }
            }
            
            // Initialize clock and update every second
            updateClock();
            setInterval(updateClock, 1000);
            
            // Smooth count-up animation with easing
            const counters = document.querySelectorAll('.count-up');
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                if (isNaN(target)) return;
                
                const duration = 1500; // Faster animation
                const startTime = Date.now();
                
                const easeOutQuart = (t) => 1 - Math.pow(1 - t, 4);
                
                const updateCounter = () => {
                    const elapsed = Date.now() - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const easedProgress = easeOutQuart(progress);
                    const current = Math.floor(easedProgress * target);
                    
                    counter.textContent = current.toLocaleString();
                    
                    if (progress < 1) {
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target.toLocaleString();
                    }
                };
                
                // Start animation with slight delay for staggered effect
                setTimeout(updateCounter, 100 + Math.random() * 200);
            });

            // --- ENHANCED QR CODE GENERATION ---
            @if (Auth::user()->qr_code_value)
                const qrValue = "{{ Auth::user()->qr_code_value }}";
                
                // Dashboard QR Code
                const dashboardQr = new QRCode(document.getElementById("dashboard-qrcode"), {
                    text: qrValue,
                    width: 70,
                    height: 70,
                    colorDark: "#1a1a1a",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
                
                // Add hover effect to QR container
                const qrContainer = document.getElementById("dashboard-qrcode");
                if (qrContainer) {
                    qrContainer.addEventListener('mouseenter', function() {
                        this.style.transform = 'scale(1.15)';
                        this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.2)';
                    });
                    
                    qrContainer.addEventListener('mouseleave', function() {
                        this.style.transform = 'scale(1)';
                        this.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
                    });
                }
                
                // Modal QR Code
                var qrModal = document.getElementById('qrModal');
                qrModal.addEventListener('show.bs.modal', function(event) {
                    var qrModalContainer = document.getElementById('qrcode-modal-display');
                    qrModalContainer.innerHTML = '';
                    
                    new QRCode(qrModalContainer, {
                        text: qrValue,
                        width: 220,
                        height: 220,
                        colorDark: "#1a1a1a",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                });
            @endif

            // --- ENHANCED CHARTS WITH ANIMATION ---
            const ctx = document.getElementById('attendancePieChart')?.getContext('2d');
            if (ctx) {
                // Configure global chart defaults
                Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
                Chart.defaults.animation.duration = 2000;
                Chart.defaults.animation.easing = 'easeOutQuart';
                
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
                                backgroundColor: [
                                    'rgba(0, 210, 91, 0.9)',
                                    'rgba(255, 171, 0, 0.9)',
                                    'rgba(252, 66, 74, 0.9)',
                                    'rgba(0, 144, 231, 0.9)',
                                    'rgba(140, 148, 163, 0.9)'
                                ],
                                borderColor: 'white',
                                borderWidth: 3,
                                hoverBorderWidth: 4,
                                hoverOffset: 15
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
                                        padding: 20,
                                        font: {
                                            size: 12,
                                            weight: '500'
                                        }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleFont: { size: 13 },
                                    bodyFont: { size: 13 },
                                    padding: 12,
                                    cornerRadius: 8
                                }
                            },
                            cutout: '70%'
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
                                backgroundColor: [
                                    'rgba(0, 210, 91, 0.9)',
                                    'rgba(255, 171, 0, 0.9)',
                                    'rgba(252, 66, 74, 0.9)'
                                ],
                                borderColor: 'white',
                                borderWidth: 3,
                                hoverBorderWidth: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '75%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 20,
                                        font: {
                                            size: 12
                                        }
                                    }
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
                                backgroundColor: [
                                    'rgba(0, 210, 91, 0.9)',
                                    'rgba(0, 144, 231, 0.9)'
                                ],
                                borderColor: 'white',
                                borderWidth: 3,
                                hoverBorderWidth: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '65%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 20
                                    }
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
                                backgroundColor: [
                                    'rgba(0, 210, 91, 0.9)',
                                    'rgba(255, 171, 0, 0.9)',
                                    'rgba(252, 66, 74, 0.9)',
                                    'rgba(140, 148, 163, 0.9)'
                                ],
                                borderColor: 'white',
                                borderWidth: 3,
                                hoverBorderWidth: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 20
                                    }
                                }
                            }
                        }
                    });
                @endif
            }

            // --- ENHANCED MODAL INTERACTIONS ---
            var profilePhotoModal = document.getElementById('profilePhotoModal');
            if (profilePhotoModal) {
                profilePhotoModal.addEventListener('show.bs.modal', function(event) {
                    var button = event.relatedTarget;
                    var src = button.getAttribute('data-src');
                    var modalImg = document.getElementById('profileModalImageSrc');
                    
                    // Add loading effect
                    modalImg.style.opacity = '0';
                    modalImg.src = src;
                    
                    // Fade in image after load
                    modalImg.onload = function() {
                        modalImg.style.transition = 'opacity 0.5s ease';
                        modalImg.style.opacity = '1';
                    };
                });
            }

            // --- ENHANCED HOVER EFFECTS FOR ALL CARDS ---
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.classList.add('will-change-transform');
                
                card.addEventListener('mouseenter', function() {
                    this.style.zIndex = '10';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.zIndex = '';
                });
            });

            // --- PARALLAX EFFECT FOR LUXURY CARDS ---
            const luxuryCards = document.querySelectorAll('.luxury-card');
            luxuryCards.forEach(card => {
                card.addEventListener('mousemove', function(e) {
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;
                    
                    const rotateY = (x - centerX) / 25;
                    const rotateX = (centerY - y) / 25;
                    
                    this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(0)`;
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateZ(0)';
                    this.style.transition = 'transform 0.5s ease';
                });
            });

            // --- ENHANCED ALERT AUTO-DISMISS ---
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert && alert.parentNode) {
                        alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateX(100%)';
                        
                        setTimeout(() => {
                            if (alert && alert.parentNode) {
                                alert.remove();
                            }
                        }, 500);
                    }
                }, 5000);
            });

            // --- SMOOTH SCROLL FOR PAGE LOAD ---
            window.addEventListener('load', () => {
                document.body.style.opacity = '0';
                document.body.style.transition = 'opacity 0.5s ease';
                
                setTimeout(() => {
                    document.body.style.opacity = '1';
                }, 100);
            });

            // --- ADD KEYBOARD NAVIGATION SUPPORT ---
            document.addEventListener('keydown', (e) => {
                // Escape key closes modals
                if (e.key === 'Escape') {
                    const modals = document.querySelectorAll('.modal.show');
                    modals.forEach(modal => {
                        const modalInstance = bootstrap.Modal.getInstance(modal);
                        if (modalInstance) modalInstance.hide();
                    });
                }
                
                // Enter key triggers primary button in focused card
                if (e.key === 'Enter' && e.target.classList.contains('card')) {
                    const primaryBtn = e.target.querySelector('.btn-primary, .btn-dark');
                    if (primaryBtn) primaryBtn.click();
                }
            });

            // --- PERFORMANCE OPTIMIZATION: Intersection Observer for lazy loading ---
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('animated');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.1,
                    rootMargin: '50px'
                });
                
                document.querySelectorAll('.animate-enter').forEach(el => {
                    observer.observe(el);
                });
            }
        });
    </script>
@endpush