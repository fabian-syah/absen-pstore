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

    {{-- BIRTHDAY CELEBRATION --}}
    @if (isset($birthdayData) && $birthdayData)
        <div class="row mb-4 animate-enter">
            <div class="col-12">
                <div class="card border-0 shadow-lg overflow-hidden birthday-card">
                    <div class="confetti-container"></div>
                    <div class="balloon b1"></div>
                    <div class="balloon b2"></div>
                    <div class="balloon b3"></div>
                    <div class="card-body position-relative z-index-1 py-4 px-4">
                        <div class="row align-items-center">
                            <div class="col-md-7 text-white">
                                @if ($birthdayData['is_today'])
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-warning text-dark fw-bold me-2 pulse-animation">
                                            <i class="mdi mdi-cake-variant me-1"></i> SPECIAL DAY
                                        </span>
                                        <h2 class="fw-bold mb-0 text-shadow-glam">HAPPY BIRTHDAY! 🎂</h2>
                                    </div>
                                    <p class="lead mb-1 text-white-50">Selamat Ulang Tahun yang ke-<strong>{{ $birthdayData['age_to_be'] }}</strong>, {{ Auth::user()->name }}!</p>
                                    <p class="small text-white-50 mb-0">Semoga panjang umur, sehat selalu, dan karir makin cemerlang di PStore!</p>
                                @else
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-light text-primary fw-bold me-2">
                                            <i class="mdi mdi-calendar-star me-1"></i> UPCOMING
                                        </span>
                                        <h3 class="fw-bold mb-0 text-white">Counting Down to Your Day! 🎈</h3>
                                    </div>
                                    <p class="mb-0 text-white-50">Sebentar lagi kamu ulang tahun yang ke-<strong>{{ $birthdayData['age_to_be'] }}</strong>. Siapkan harapan terbaikmu!</p>
                                @endif
                            </div>
                            <div class="col-md-5 mt-4 mt-md-0 text-center text-md-end">
                                @if (!$birthdayData['is_today'])
                                    <div class="d-flex justify-content-center justify-content-md-end gap-2" id="birthday-countdown">
                                        <div class="countdown-box glass-box">
                                            <span class="d-block fw-bold fs-3" id="cd-days">{{ $birthdayData['days_left'] }}</span>
                                            <small class="text-uppercase" style="font-size: 9px;">Hari</small>
                                        </div>
                                        <div class="countdown-box glass-box">
                                            <span class="d-block fw-bold fs-3" id="cd-hours">00</span>
                                            <small class="text-uppercase" style="font-size: 9px;">Jam</small>
                                        </div>
                                        <div class="countdown-box glass-box">
                                            <span class="d-block fw-bold fs-3" id="cd-minutes">00</span>
                                            <small class="text-uppercase" style="font-size: 9px;">Menit</small>
                                        </div>
                                        <div class="countdown-box glass-box">
                                            <span class="d-block fw-bold fs-3 text-warning" id="cd-seconds">00</span>
                                            <small class="text-uppercase" style="font-size: 9px;">Detik</small>
                                        </div>
                                    </div>
                                @else
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

    {{-- ATTENDANCE WRAPPED (Desember Only) --}}
    @if (\Carbon\Carbon::now()->month == 12)
        <div class="row mb-4 animate-enter">
            <div class="col-12">
                <div class="card bg-gradient-warning text-white shadow-lg" style="background: linear-gradient(135deg, #111 0%, #333 100%); border: 1px solid #FFD700; overflow: hidden; position: relative;">
                    <div style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255, 215, 0, 0.1) 0%, transparent 70%); animation: rotateGlow 20s linear infinite; pointer-events: none;"></div>
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
                        <a href="{{ route('attendance.recap') }}" class="btn btn-light rounded-pill fw-bold shadow-sm hover-scale">
                            <i class="mdi mdi-play-circle-outline me-1"></i> Putar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- STATS SECTION BY ROLE --}}
    @if (auth()->user()->role == 'admin')
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
    @endif

    {{-- NOSTALGIA GALLERY (MELANCHOLY STYLE) --}}
    <div class="row mt-4 mb-5 animate-enter" style="animation-delay: 0.5s">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between mb-3 px-2">
                <div class="nostalgia-header">
                    <h4 class="fw-bold mb-0 text-dark melancholy-title">
                        <i class="mdi mdi-camera-iris text-danger me-2 shadow-icon"></i>Lembaran Cerita {{ $currentMonthName }}
                    </h4>
                    <p class="text-muted small mb-0 font-italic">"Sebab setiap lelahmu adalah bagian dari sejarah yang indah..."</p>
                </div>
            </div>
            <div class="gallery-scroll-container">
                <div class="d-flex gap-3 pb-4">
                    @forelse ($attendanceGallery as $item)
                        @if ($item->photo_path)
                            <div class="gallery-item-wrapper">
                                <div class="gallery-card shadow-lg" onclick="previewGalleryImage('{{ Storage::url($item->photo_path) }}', 'Awal Sebuah Perjuangan', '{{ $item->check_in_time->translatedFormat('l, d F Y - H:i') }}')">
                                    <img src="{{ Storage::url($item->photo_path) }}" class="gallery-img">
                                    <div class="gallery-overlay"></div>
                                    <div class="gallery-badge bg-soft-green">MASUK</div>
                                    <div class="gallery-date-info">
                                        <span class="day">{{ $item->check_in_time->format('d') }}</span>
                                        <span class="month">{{ $item->check_in_time->format('M') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($item->photo_out_path)
                            <div class="gallery-item-wrapper">
                                <div class="gallery-card shadow-lg" onclick="previewGalleryImage('{{ Storage::url($item->photo_out_path) }}', 'Akhir Yang Berharga', '{{ $item->check_out_time ? $item->check_out_time->translatedFormat('l, d F Y - H:i') : '-' }}')">
                                    <img src="{{ Storage::url($item->photo_out_path) }}" class="gallery-img">
                                    <div class="gallery-overlay"></div>
                                    <div class="gallery-badge bg-soft-red">PULANG</div>
                                    <div class="gallery-date-info">
                                        <span class="day">{{ $item->check_in_time->format('d') }}</span>
                                        <span class="month">{{ $item->check_in_time->format('M') }}</span>
                                    </div>
                                    @if($item->check_out_time)
                                    <div class="work-duration-pill">
                                        <i class="mdi mdi-timer-outline me-1"></i>{{ $item->check_in_time->diff($item->check_out_time)->format('%h Jam') }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="col-12 text-center py-5 bg-light rounded-4 border-dashed w-100" style="min-width: 300px; opacity: 0.6;">
                            <i class="mdi mdi-image-filter-hdr display-4 text-muted mb-2"></i>
                            <p class="text-muted font-italic">Belum ada fragmen kenangan tersimpan di bulan ini...</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- LEADERBOARD PODIUM --}}
    <div class="row mb-5 animate-enter" style="animation-delay: 0.6s">
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
                            <p class="text-muted small">{{ auth()->user()->role == 'admin' ? 'Global' : 'Cabang Anda' }} - Bulan {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
                        </div>
                        <div class="podium-luxury-container d-flex justify-content-center align-items-end gap-3">
                            {{-- JUARA 2 --}}
                            @if (isset($leaderboard[1]))
                                <div class="podium-step-container animate-enter">
                                    <div class="podium-avatar-wrapper">
                                        <img src="{{ $leaderboard[1]->user->profile_photo_path ? asset('storage/' . $leaderboard[1]->user->profile_photo_path) : 'https://ui-avatars.com/api/?name='.urlencode($leaderboard[1]->user->name) }}" class="luxury-avatar">
                                        <div class="rank-circle silver">2</div>
                                    </div>
                                    <div class="podium-block silver-block text-center">
                                        <div class="podium-content">
                                            <h6 class="fw-bold text-dark mb-1">{{ Str::limit($leaderboard[1]->user->name, 12) }}</h6>
                                            <div class="stat-pill mb-1"><i class="mdi mdi-check-circle text-success me-1"></i>{{ $leaderboard[1]->total_attendance }} Hadir</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            {{-- JUARA 1 --}}
                            @if (isset($leaderboard[0]))
                                <div class="podium-step-container main-winner animate-enter">
                                    <div class="crown-floating"><img src="https://cdn-icons-png.flaticon.com/512/6941/6941697.png" width="50"></div>
                                    <div class="podium-avatar-wrapper gold-glow">
                                        <img src="{{ $leaderboard[0]->user->profile_photo_path ? asset('storage/' . $leaderboard[0]->user->profile_photo_path) : 'https://ui-avatars.com/api/?name='.urlencode($leaderboard[0]->user->name) }}" class="luxury-avatar">
                                        <div class="rank-circle gold">1</div>
                                    </div>
                                    <div class="podium-block gold-block text-center">
                                        <div class="podium-content pt-3">
                                            <h5 class="fw-bold text-dark mb-1">{{ Str::limit($leaderboard[0]->user->name, 15) }}</h5>
                                            <div class="stat-pill gold mb-2"><i class="mdi mdi-star me-1"></i>{{ $leaderboard[0]->total_attendance }} Kehadiran</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            {{-- JUARA 3 --}}
                            @if (isset($leaderboard[2]))
                                <div class="podium-step-container animate-enter">
                                    <div class="podium-avatar-wrapper">
                                        <img src="{{ $leaderboard[2]->user->profile_photo_path ? asset('storage/' . $leaderboard[2]->user->profile_photo_path) : 'https://ui-avatars.com/api/?name='.urlencode($leaderboard[2]->user->name) }}" class="luxury-avatar">
                                        <div class="rank-circle bronze">3</div>
                                    </div>
                                    <div class="podium-block bronze-block text-center">
                                        <div class="podium-content">
                                            <h6 class="fw-bold text-dark mb-1">{{ Str::limit($leaderboard[2]->user->name, 12) }}</h6>
                                            <div class="stat-pill mb-1"><i class="mdi mdi-check me-1"></i>{{ $leaderboard[2]->total_attendance }} Hadir</div>
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

    {{-- PERSONAL ATTENDANCE SECTION --}}
    <div class="row">
        <div class="col-md-5 grid-margin stretch-card">
            <div class="card card-id gradient-dark shadow-lg">
                <div class="card-body">
                    <div class="card-id-header">
                        <div class="card-id-photo-wrapper">
                            @if (Auth::user()->profile_photo_path)
                                <img src="{{ Storage::url(Auth::user()->profile_photo_path) }}" class="id-card-img" data-bs-toggle="modal" data-bs-target="#profilePhotoModal" data-src="{{ Storage::url(Auth::user()->profile_photo_path) }}">
                            @else
                                <div class="id-card-img-placeholder">{{ substr(Auth::user()->name, 0, 1) }}</div>
                            @endif
                        </div>
                        <div class="card-id-logo text-warning"><i class="mdi mdi-credit-card-outline"></i><span>ID Card</span></div>
                    </div>
                    <div class="card-id-details">
                        <p class="card-id-label">NAMA</p>
                        <h3 class="card-id-name text-truncate">{{ strtoupper(Auth::user()->name) }}</h3>
                        <p class="card-id-label">DIVISI</p>
                        <h4 class="card-id-division">{{ strtoupper(Auth::user()->division->name ?? 'BELUM ADA DIVISI') }}</h4>
                    </div>
                    <div class="card-id-footer text-end">
                        <p class="mb-0 text-white-50 small">NOMOR ID</p>
                        <p class="card-id-card-number fw-bold fs-4">{{ $idCardNumber ?? '000000 000000' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-7 grid-margin stretch-card">
            <div class="card card-status hover-shadow-lg">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-4">
                        <h4 class="card-title mb-0"><i class="mdi mdi-calendar-today me-2 text-primary"></i>Status Absensi</h4>
                        <div class="text-end">
                            <h4 class="fw-bold mb-0 text-primary" id="realtime-clock">--:--:--</h4>
                            <small class="text-muted d-block" style="font-size: 0.7rem;">{{ \Carbon\Carbon::now($current_timezone)->translatedFormat('l, d F Y') }}</small>
                        </div>
                    </div>

                    @if ($myAttendanceToday)
                        @php $isCrossDay = false; if (!$myAttendanceToday->check_out_time) { $isCrossDay = $myAttendanceToday->check_in_time->format('Y-m-d') !== \Carbon\Carbon::now($current_timezone)->format('Y-m-d'); } @endphp
                        @if ($myAttendanceToday->check_out_time)
                            <div class="status-card status-success mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="status-icon"><i class="mdi mdi-home-variant"></i></div>
                                    <div><h5 class="mb-1 fw-bold">Sudah Pulang</h5><p class="text-muted mb-0 small">Terima kasih atas kerja kerasnya!</p></div>
                                </div>
                            </div>
                        @else
                            <div class="status-card {{ $isCrossDay ? 'status-warning' : 'status-success' }} mb-3">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="status-icon pulse-animation"><i class="mdi mdi-clock-check"></i></div>
                                    <div><h5 class="mb-0 fw-bold">Sedang Bekerja</h5><p class="small text-muted mb-0">Masuk: {{ $myAttendanceToday->check_in_time->format('H:i') }}</p></div>
                                </div>
                                <a href="{{ route('self.attend.create', ['attendance_id' => $myAttendanceToday->id, 'mode' => 'pulang']) }}" class="btn btn-danger w-100 shadow-sm"><i class="mdi mdi-logout me-2"></i>Absen Pulang</a>
                            </div>
                        @endif
                    @else
                        <div class="status-card status-info text-center py-4">
                            <i class="mdi mdi-clock-alert display-4 text-primary mb-3"></i>
                            <h5 class="fw-bold">Belum Absen Hari Ini</h5>
                            <div class="d-flex justify-content-center gap-2 mt-4">
                                <a href="{{ route('self.attend.create') }}" class="btn btn-dark px-4 shadow"><i class="mdi mdi-fingerprint me-2"></i>Absen Mandiri</a>
                                <a href="{{ route('leave-requests.create') }}" class="btn btn-outline-dark px-4"><i class="mdi mdi-file-document-edit-outline me-2"></i>Izin</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL GALLERY PREVIEW (EMOTIONAL STYLE) --}}
    <div class="modal fade" id="galleryPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark border-0 overflow-hidden shadow-2xl" style="border-radius: 25px;">
                <div class="modal-body p-0 position-relative">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-4" style="z-index: 100;" data-bs-dismiss="modal"></button>
                    <div class="preview-img-container">
                        <img src="" id="galleryPreviewImg" class="img-fluid w-100 h-100" style="object-fit: cover; min-height: 450px;">
                    </div>
                    <div class="p-4 text-white text-center" style="background: linear-gradient(transparent, rgba(0,0,0,0.95)); position: absolute; bottom: 0; left: 0; right: 0;">
                        <h5 id="galleryPreviewTitle" class="fw-bold mb-2 melancholy-font"></h5>
                        <p id="galleryPreviewDate" class="small opacity-80 mb-0 font-monospace"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
    /* === [BARU] NOSTALGIA GALLERY STYLES === */
    .melancholy-title {
        font-family: 'Playfair Display', serif;
        animation: breathing 4s ease-in-out infinite;
    }

    @keyframes breathing {
        0%, 100% { transform: scale(1); opacity: 0.9; }
        50% { transform: scale(1.02); opacity: 1; }
    }

    .gallery-scroll-container {
        display: flex;
        overflow-x: auto;
        padding: 10px 5px;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .gallery-scroll-container::-webkit-scrollbar { display: none; }

    .gallery-card {
        width: 155px;
        height: 235px;
        position: relative;
        border-radius: 22px;
        overflow: hidden;
        background: #111;
        cursor: pointer;
        transition: all 0.5s cubic-bezier(0.25, 1, 0.5, 1);
        border: 1px solid rgba(255,255,255,0.05);
    }

    .gallery-img {
        width: 100%; height: 100%; object-fit: cover;
        opacity: 0.55; 
        filter: grayscale(100%) contrast(1.1) blur(1px); /* Efek Sedih/Lama */
        transition: all 0.7s ease;
    }

    .gallery-card:hover {
        transform: translateY(-8px) scale(1.03);
        box-shadow: 0 15px 35px rgba(0,0,0,0.4);
    }

    .gallery-card:hover .gallery-img {
        opacity: 1; 
        filter: grayscale(0%) contrast(1) blur(0px); /* Kembali Hidup saat disentuh */
    }

    .gallery-overlay {
        position: absolute; inset: 0;
        background: linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.6));
    }

    .gallery-badge {
        position: absolute; top: 15px; left: 15px;
        font-size: 7px; font-weight: 800; padding: 4px 10px;
        border-radius: 6px; color: white; letter-spacing: 1.5px;
        z-index: 10;
    }

    .bg-soft-green { background: rgba(16, 185, 129, 0.7); backdrop-filter: blur(4px); }
    .bg-soft-red { background: rgba(239, 68, 68, 0.7); backdrop-filter: blur(4px); }

    .gallery-date-info {
        position: absolute; bottom: 15px; left: 15px;
        color: white; z-index: 10; display: flex; flex-direction: column;
    }
    .gallery-date-info .day { font-size: 22px; font-weight: 900; line-height: 1; }
    .gallery-date-info .month { font-size: 10px; text-transform: uppercase; opacity: 0.8; letter-spacing: 1px; }

    .work-duration-pill {
        position: absolute; bottom: 15px; right: 15px;
        background: rgba(255,255,255,0.15); backdrop-filter: blur(8px);
        color: white; font-size: 9px; padding: 3px 8px;
        border-radius: 30px; border: 1px solid rgba(255,255,255,0.1);
        z-index: 10;
    }

    .border-dashed { border: 2px dashed #ddd; background: transparent !important; }

    /* EXISTING STYLES */
    .birthday-card { background: linear-gradient(135deg, #4c1d95 0%, #be185d 100%); color: white; }
    .glass-box { background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); border-radius: 12px; padding: 10px 15px; min-width: 70px; }
    .card-bank { min-height: 200px; border-radius: 16px; color: white; position: relative; overflow: hidden; }
    .status-card { padding: 20px; border-radius: 16px; border: 1px solid #eee; }
    .status-success { border-left: 5px solid #10b981; background: #f0fdf4; }
    .status-warning { border-left: 5px solid #f59e0b; background: #fffbeb; }
    .status-info { border-left: 5px solid #3b82f6; background: #eff6ff; }
    .pulse-animation { animation: pulse 2s infinite; }
    @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(16,185,129,0.7); } 70% { box-shadow: 0 0 0 10px rgba(16,185,129,0); } 100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); } }
    .luxury-avatar { width: 60px; height: 60px; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
</style>
@endpush

@push('scripts')
<script>
    function previewGalleryImage(src, title, date) {
        const modal = new bootstrap.Modal(document.getElementById('galleryPreviewModal'));
        const img = document.getElementById('galleryPreviewImg');
        img.style.opacity = '0';
        img.src = src;
        img.onload = function() { img.style.opacity = '1'; }; // Smooth load
        document.getElementById('galleryPreviewTitle').innerText = title;
        document.getElementById('galleryPreviewDate').innerText = date;
        modal.show();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Live Clock with Branch Timezone
        function updateClock() {
            const timeZone = "{{ $current_timezone }}";
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { timeZone: timeZone, hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
            if (document.getElementById('realtime-clock')) document.getElementById('realtime-clock').innerText = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Counter Up Animation
        document.querySelectorAll('.count-up').forEach(counter => {
            const target = +counter.getAttribute('data-target');
            let count = 0;
            const inc = target / 100;
            const update = () => {
                count += inc;
                if (count < target) { counter.innerText = Math.ceil(count); setTimeout(update, 20); }
                else { counter.innerText = target; }
            };
            update();
        });
    });
</script>
@endpush