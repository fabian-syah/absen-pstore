@extends('layout.master')

@section('title')
    Dashboard
@endsection

@section('heading')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div class="animate-enter">
            <span class="text-muted small d-block mb-1" id="greeting-text" style="letter-spacing: 1px; font-weight: 600;">LOADING...</span>
            <h3 class="fw-bold mb-0" style="color: #1F1F1F; font-size: 1.8rem; letter-spacing: -1px;">{{ Auth::user()->name }}</h3>
        </div>
    </div>
@endsection

@section('content')

    {{-- BIRTHDAY SECTION --}}
    @if (isset($birthdayData) && $birthdayData)
        <div class="row mb-4 animate-enter">
            <div class="col-12">
                <div class="card border-0 shadow-lg overflow-hidden birthday-card-premium" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 25px;">
                    <div class="confetti-container"></div>
                    <div class="card-body position-relative py-5 px-4">
                        <div class="row align-items-center">
                            <div class="col-md-7 text-white">
                                @if ($birthdayData['is_today'])
                                    <div class="badge bg-warning text-dark mb-3 fw-bold px-3 py-2 rounded-pill shadow-sm">🎁 HARI SPESIAL ANDA</div>
                                    <h1 class="fw-black mb-2 display-4" style="letter-spacing: -2px;">Selamat Ulang Tahun!</h1>
                                    <p class="lead opacity-75">Barakallah fii umrik ke-<strong>{{ $birthdayData['age_to_be'] }}</strong>. Semoga senantiasa diberikan kesehatan dan keberkahan di PStore.</p>
                                @else
                                    <h3 class="fw-bold mb-0">Hitung Mundur Ulang Tahun 🎈</h3>
                                    <p class="opacity-75">Ulang tahun ke-{{ $birthdayData['age_to_be'] }} Anda tinggal menghitung hari!</p>
                                @endif
                            </div>
                            <div class="col-md-5 mt-4 mt-md-0">
                                @if (!$birthdayData['is_today'])
                                    <div class="d-flex justify-content-center justify-content-md-end gap-3" id="birthday-countdown">
                                        <div class="countdown-unit">
                                            <span id="cd-days" class="d-block fs-2 fw-black">{{ $birthdayData['days_left'] }}</span>
                                            <small>HARI</small>
                                        </div>
                                        <div class="countdown-unit">
                                            <span id="cd-hours" class="d-block fs-2 fw-black">00</span>
                                            <small>JAM</small>
                                        </div>
                                        <div class="countdown-unit">
                                            <span id="cd-minutes" class="d-block fs-2 fw-black">00</span>
                                            <small>MENIT</small>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center text-md-end">
                                        <button class="btn btn-white btn-rounded-lg fw-bold px-5 py-3 shadow-lg pulse-animation" onclick="confettiEffect()">
                                            KLAIM HADIAH DOA <i class="mdi mdi-heart text-danger ms-2"></i>
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

    {{-- RAMADHAN SECTION --}}
    @if (isset($ramadanData) && $ramadanData)
        <div class="row mb-5 animate-enter" style="animation-delay: 0.1s">
            <div class="col-12">
                <div class="card border-0 royal-ramadan-card shadow-premium">
                    <div class="royal-bg-pattern"></div>
                    <div class="mosque-silhouette"></div>
                    <div class="moon-glow"></div>
                    <div class="r-lantern l-big"><div class="r-lantern-light"></div></div>
                    <div class="r-lantern l-med"><div class="r-lantern-light"></div></div>
                    <div id="particles-js" class="particles-container"></div>

                    <div class="card-body position-relative py-5 px-4">
                        <div class="row align-items-center">
                            <div class="col-lg-7 text-center text-lg-start mb-4">
                                <div class="animate-float mb-3"><span class="badge-royal">🌙 RAMADHAN 1447 H</span></div>
                                <h1 class="display-3 fw-bold text-gradient-gold mb-2 font-arabic">Marhaban Ya Ramadhan</h1>
                                <p class="lead text-white-50 px-lg-0 px-4">Menyiapkan hati untuk menyambut bulan penuh ampunan dan keberkahan.</p>
                            </div>
                            <div class="col-lg-5">
                                @if (!$ramadanData['is_today'])
                                    <div class="royal-countdown-wrapper">
                                        <div class="d-flex justify-content-center gap-3">
                                            <div class="royal-timer-box"><span class="royal-time" id="royal-days">{{ $ramadanData['days_left'] }}</span><span class="royal-label">HARI</span></div>
                                            <div class="royal-timer-box"><span class="royal-time" id="royal-hours">00</span><span class="royal-label">JAM</span></div>
                                            <div class="royal-timer-box"><span class="royal-time" id="royal-minutes">00</span><span class="royal-label">MENIT</span></div>
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

    {{-- STATISTICS SECTION --}}
    <div class="row mb-4">
        @if (auth()->user()->role == 'admin')
            <div class="col-md-3 grid-margin stretch-card animate-enter" style="animation-delay: 0.2s">
                <div class="card card-bank gradient-purple shadow-premium">
                    <div class="card-body">
                        <div class="card-bank-chip shadow-sm"></div>
                        <i class="mdi mdi-account-multiple card-bank-icon"></i>
                        <div>
                            <p class="card-bank-label">JUMLAH KARYAWAN</p>
                            <h2 class="card-bank-value count-up" data-target="{{ $totalUsers }}">0</h2>
                            <p class="card-bank-desc">Karyawan Terdaftar</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card animate-enter" style="animation-delay: 0.3s">
                <div class="card card-bank gradient-blue shadow-premium">
                    <div class="card-body">
                        <div class="card-bank-chip shadow-sm"></div>
                        <i class="mdi mdi-office-building card-bank-icon"></i>
                        <div>
                            <p class="card-bank-label">TOTAL CABANG</p>
                            <h2 class="card-bank-value count-up" data-target="{{ $totalBranches }}">0</h2>
                            <p class="card-bank-desc">Unit Aktif</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card animate-enter" style="animation-delay: 0.4s">
                <div class="card card-bank gradient-green shadow-premium">
                    <div class="card-body">
                        <div class="card-bank-chip shadow-sm"></div>
                        <i class="mdi mdi-calendar-check card-bank-icon"></i>
                        <div>
                            <p class="card-bank-label">HADIR HARI INI</p>
                            <h2 class="card-bank-value count-up" data-target="{{ $attendancesToday }}">0</h2>
                            <p class="card-bank-desc">Data Real-time</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card animate-enter" style="animation-delay: 0.5s">
                <div class="card card-bank gradient-orange shadow-premium">
                    <div class="card-body">
                        <div class="card-bank-chip shadow-sm"></div>
                        <i class="mdi mdi-alert-circle-outline card-bank-icon"></i>
                        <div>
                            <p class="card-bank-label">PENDING VERIF</p>
                            <h2 class="card-bank-value count-up" data-target="{{ $pendingVerifications }}">0</h2>
                            <p class="card-bank-desc">Butuh Tindakan</p>
                        </div>
                    </div>
                </div>
            </div>
        @elseif (auth()->user()->role == 'audit')
             {{-- Audit Stats Here (Bank Card Style) --}}
             <div class="col-md-4 grid-margin stretch-card animate-enter"><div class="card card-bank gradient-red shadow-premium"><div class="card-body"><i class="mdi mdi-shield-check-outline card-bank-icon"></i><p class="card-bank-label">PENDING VERIF</p><h2 class="card-bank-value count-up" data-target="{{ $pendingVerifications }}">0</h2><p class="card-bank-desc small">Absensi Foto/Lokasi</p></div></div></div>
             <div class="col-md-4 grid-margin stretch-card animate-enter"><div class="card card-bank gradient-blue shadow-premium"><div class="card-body"><i class="mdi mdi-file-document-edit card-bank-icon"></i><p class="card-bank-label">APPROVE IZIN</p><h2 class="card-bank-value count-up" data-target="{{ $pendingLeaves }}">0</h2><p class="card-bank-desc small">Cuti/Sakit/Izin</p></div></div></div>
             <div class="col-md-4 grid-margin stretch-card animate-enter"><div class="card card-bank gradient-green shadow-premium"><div class="card-body"><i class="mdi mdi-calendar-today card-bank-icon"></i><p class="card-bank-label">HADIR HARI INI</p><h2 class="card-bank-value count-up" data-target="{{ $attendancesToday }}">0</h2><p class="card-bank-desc small">Total Cabang Anda</p></div></div></div>
        @endif
    </div>

    {{-- HALL OF FAME SECTION --}}
    @if (isset($lastMonthWinners) && $lastMonthWinners->count() > 0)
        <div class="row mb-5 animate-enter" style="animation-delay: 0.6s">
            <div class="col-12">
                <div class="card border-0 shadow-lg hall-of-fame-card overflow-hidden">
                    <div class="spotlight"></div>
                    <div class="card-body p-4 position-relative">
                        <div class="text-center mb-5 mt-3">
                            <span class="badge bg-warning text-dark fw-bold mb-2 shadow-sm px-4">THE LEGENDS</span>
                            <h2 class="fw-black text-white" style="font-size: 2.2rem; letter-spacing: -1.5px;">Bintang Absensi {{ $lastMonthName }}</h2>
                            <p class="text-white-50">Karyawan dengan integritas waktu terbaik bulan lalu.</p>
                        </div>
                        <div class="row justify-content-center g-4">
                            @foreach ($lastMonthWinners as $history)
                                <div class="col-md-4">
                                    <div class="winner-memory-card text-center shadow-lg border-0">
                                        <div class="rank-badge-mini {{ $loop->first ? 'gold' : ($loop->iteration == 2 ? 'silver' : 'bronze') }}">#{{ $loop->iteration }}</div>
                                        <div class="avatar-box mb-3">
                                            @if ($history->user->profile_photo_path)
                                                <img src="{{ asset('storage/' . $history->user->profile_photo_path) }}" class="rounded-circle grayscale-memory shadow-lg">
                                            @else
                                                <div class="profile-initial-nav-ios" style="width: 100px; height: 100px; font-size: 40px;">{{ substr($history->user->name, 0, 1) }}</div>
                                            @endif
                                        </div>
                                        <h5 class="text-white fw-bold mb-1">{{ $history->user->name }}</h5>
                                        <p class="text-warning small mb-3">{{ $history->user->division->name ?? 'Staff' }}</p>
                                        <div class="attendance-pill-custom px-4"><i class="mdi mdi-calendar-check me-2"></i>{{ $history->total_attendance }} Hari</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- PERSONAL HUB --}}
    <div class="row animate-enter" style="animation-delay: 0.7s">
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card card-id holographic shadow-premium">
                <div class="card-body py-4">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div class="photo-id-frame shadow">
                            @if (Auth::user()->profile_photo_path)
                                <img src="{{ Storage::url(Auth::user()->profile_photo_path) }}" class="id-card-img" data-bs-toggle="modal" data-bs-target="#profilePhotoModal" data-src="{{ Storage::url(Auth::user()->profile_photo_path) }}">
                            @else
                                <div class="id-card-img-placeholder">{{ getInitials(Auth::user()->name) }}</div>
                            @endif
                        </div>
                        <img src="{{ asset('assets/images/logo-pstore.png') }}" width="80" style="filter: brightness(0) invert(1);">
                    </div>
                    <div class="mt-2">
                        <small class="text-white-50 fw-bold" style="font-size: 10px;">ID KARYAWAN</small>
                        <h3 class="fw-black text-white mb-3" style="letter-spacing: -1px;">{{ strtoupper(Auth::user()->name) }}</h3>
                        <div class="d-flex gap-4">
                            <div><small class="text-white-50 d-block">DIVISI</small><span class="fw-bold">{{ Auth::user()->division->name ?? 'N/A' }}</span></div>
                            <div><small class="text-white-50 d-block">NO.ID</small><span class="fw-bold">{{ $idCardNumber ?? '000-000' }}</span></div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top border-light d-flex justify-content-between align-items-center">
                        <div id="dashboard-qrcode" class="p-2 bg-white rounded shadow-sm" data-bs-toggle="modal" data-bs-target="#qrModal"></div>
                        <div class="text-end"><small class="text-white-50 d-block">SCAN UNTUK ABSEN</small><i class="mdi mdi-line-scan fs-3 text-warning"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8 grid-margin stretch-card">
            <div class="card shadow-sm border-0" style="border-radius: 20px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title fw-black mb-0"><i class="mdi mdi-access-point me-2 text-primary"></i>Panel Absensi</h4>
                        <div class="text-end">
                            <h4 id="realtime-clock" class="fw-black text-primary mb-0" style="font-family: 'Consolas';">00:00:00</h4>
                            <small class="text-muted">{{ $current_timezone }}</small>
                        </div>
                    </div>

                    @if ($myAttendanceToday)
                         <div class="status-box-ios success p-4 text-center">
                            <div class="pulse-ring success mx-auto mb-3"></div>
                            <h4 class="fw-bold">Selamat {{ $greeting ?? 'Bekerja' }}!</h4>
                            <p class="text-muted mb-4">Anda masuk pada jam {{ $myAttendanceToday->check_in_time->format('H:i') }}</p>
                            @if (!$myAttendanceToday->check_out_time)
                                <a href="{{ route('self.attend.create', ['attendance_id' => $myAttendanceToday->id, 'mode' => 'pulang']) }}" class="btn btn-danger btn-lg rounded-pill px-5 shadow-lg">ABSEN PULANG</a>
                            @else
                                <span class="badge bg-success py-2 px-4 rounded-pill">SESI SELESAI</span>
                            @endif
                         </div>
                    @else
                        <div class="status-box-ios empty p-4 text-center">
                            <i class="mdi mdi-radar display-4 text-muted mb-3 d-block"></i>
                            <h5 class="fw-bold text-muted">Belum Ada Sesi Hari Ini</h5>
                            <div class="d-flex gap-2 justify-content-center mt-4">
                                <a href="{{ route('self.attend.create') }}" class="btn btn-primary btn-lg px-4 rounded-pill shadow-lg"><i class="mdi mdi-fingerprint me-2"></i>MULAI ABSEN</a>
                                <a href="{{ route('leave-requests.create') }}" class="btn btn-outline-dark btn-lg px-4 rounded-pill">AJUKAN IZIN</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- QUICK LINKS & STATS --}}
    <div class="row mb-5 animate-enter">
        <div class="col-12">
            <div class="card shadow-premium border-0 py-3" style="background: #fff; border-radius: 20px;">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6 border-end-md d-flex align-items-center mb-3 mb-md-0">
                            <div class="icon-box-luxury me-3 shadow-sm"><i class="mdi mdi-clock-fast text-primary"></i></div>
                            <div><h5 class="fw-bold mb-0">Statistik Bulan Ini</h5><p class="text-muted small mb-0">Total {{ $stats['total'] ?? 0 }} hari kerja tercatat</p></div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-around">
                                <div class="text-center"><h4 class="fw-bold mb-0 text-success">{{ $stats['on_time'] ?? 0 }}</h4><small class="text-muted">On-Time</small></div>
                                <div class="text-center"><h4 class="fw-bold mb-0 text-warning">{{ $stats['late'] ?? 0 }}</h4><small class="text-muted">Terlambat</small></div>
                                <div class="text-center"><h4 class="fw-bold mb-0 text-danger">{{ $stats['absent'] ?? 0 }}</h4><small class="text-muted">Alpha</small></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
    /* PREMIUN DASHBOARD STYLES */
    .shadow-premium { box-shadow: 0 20px 40px rgba(0,0,0,0.06) !important; }
    .fw-black { font-weight: 900; }
    
    /* ID CARD HOLOGRAPHIC */
    .card-id.holographic {
        background: linear-gradient(135deg, #1f1f1f 0%, #3d3d3d 100%);
        border-radius: 25px; border: 1px solid rgba(255,255,255,0.1);
        position: relative; overflow: hidden;
    }
    .card-id.holographic::before {
        content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.05), transparent);
        transform: rotate(45deg); animation: holo 5s infinite;
    }
    @keyframes holo { 0% { transform: translateX(-100%) rotate(45deg); } 100% { transform: translateX(100%) rotate(45deg); } }
    .photo-id-frame img { width: 70px; height: 85px; border-radius: 12px; object-fit: cover; border: 3px solid rgba(255,255,255,0.2); }

    /* STATUS BOX iOS STYLE */
    .status-box-ios { border-radius: 20px; transition: all 0.3s; }
    .status-box-ios.success { background: #f0fdf4; border: 1px solid #dcfce7; }
    .status-box-ios.empty { background: #f8f9fa; border: 2px dashed #dee2e6; }
    .pulse-ring {
        width: 15px; height: 15px; border-radius: 50%;
        background: #2ECC71; box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.7);
        animation: pulse-ring 1.5s infinite cubic-bezier(0.66, 0, 0, 1);
    }
    @keyframes pulse-ring { to { box-shadow: 0 0 0 15px rgba(46, 204, 113, 0); } }

    /* COUNTDOWN UNIT */
    .countdown-unit {
        background: rgba(255,255,255,0.15); backdrop-filter: blur(5px);
        padding: 15px; border-radius: 15px; text-align: center; min-width: 80px;
    }

    /* HALL OF FAME AVATAR */
    .avatar-box img { width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 5px solid rgba(255,255,255,0.1); }
</style>
@endpush