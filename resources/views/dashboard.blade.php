@extends('layout.master')

@section('title')
    Dashboard
@endsection

@section('heading')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            <h3 class="fw-bold mb-1 text-dark" style="font-family: 'Inter', sans-serif;">Dashboard</h3>
            <p class="text-muted small mb-0">
                <span id="greeting-text">Selamat Datang,</span> <strong>{{ Auth::user()->name }}</strong>
            </p>
        </div>
        <div class="text-end d-none d-md-block">
            <div class="bg-white px-3 py-2 rounded-pill shadow-sm border">
                <div class="d-flex align-items-center gap-2">
                    <i class="mdi mdi-clock-outline text-primary"></i>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark" id="header-clock" style="line-height: 1;">--:--:--</h6>
                        <small class="text-muted" style="font-size: 10px;">{{ \Carbon\Carbon::now($current_timezone)->translatedFormat('d M Y') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')

    {{-- BAGIAN BARU: ATTENDANCE WRAPPED (Desember Only) --}}
    @if (\Carbon\Carbon::now()->month == 12)
        <div class="row mb-4 animate-enter">
            <div class="col-12">
                <div class="card border-0 shadow-lg position-relative overflow-hidden" 
                     style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 20px;">
                    <div style="position: absolute; top: 0; right: 0; width: 300px; height: 100%; background: radial-gradient(circle, rgba(255,215,0,0.15) 0%, transparent 70%); pointer-events: none;"></div>
                    
                    <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-center position-relative z-index-1">
                        <div class="d-flex align-items-center mb-3 mb-md-0">
                            <div class="icon-box bg-warning bg-opacity-25 text-warning rounded-circle p-3 me-3">
                                <i class="mdi mdi-sparkles fs-2"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-white mb-1">Your {{ date('Y') }} Wrapped is Here!</h4>
                                <p class="mb-0 text-white-50 small">Lihat rangkuman perjalanan karir dan absensimu selama setahun ini.</p>
                            </div>
                        </div>
                        <a href="{{ route('attendance.recap') }}" class="btn btn-warning rounded-pill px-4 fw-bold shadow-lg hover-scale text-dark">
                            <i class="mdi mdi-play-circle-outline me-2"></i>Putar Rangkuman
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- LAYOUT UTAMA: GRID SYSTEM --}}
    <div class="row">
        
        {{-- KOLOM KIRI (UTAMA): STATISTIK & ABSENSI --}}
        <div class="col-xl-8 col-lg-7">
            
            {{-- 1. STATISTIK CARDS (ADMIN/AUDIT/SECURITY) --}}
            @if (auth()->user()->role == 'admin')
                <div class="row mb-4">
                    <div class="col-md-3 col-6 mb-3 mb-md-0 animate-enter" style="animation-delay: 0.1s">
                        <div class="card border-0 shadow-sm h-100 card-stat">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="text-muted small mb-1 fw-bold text-uppercase">Total User</p>
                                        <h3 class="fw-bold text-dark mb-0 count-up" data-target="{{ $totalUsers }}">0</h3>
                                    </div>
                                    <div class="icon-shape bg-primary bg-opacity-10 text-primary rounded-3 p-2">
                                        <i class="mdi mdi-account-group"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3 mb-md-0 animate-enter" style="animation-delay: 0.2s">
                        <div class="card border-0 shadow-sm h-100 card-stat">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="text-muted small mb-1 fw-bold text-uppercase">Cabang</p>
                                        <h3 class="fw-bold text-dark mb-0 count-up" data-target="{{ $totalBranches }}">0</h3>
                                    </div>
                                    <div class="icon-shape bg-info bg-opacity-10 text-info rounded-3 p-2">
                                        <i class="mdi mdi-office-building"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 animate-enter" style="animation-delay: 0.3s">
                        <div class="card border-0 shadow-sm h-100 card-stat">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="text-muted small mb-1 fw-bold text-uppercase">Hadir</p>
                                        <h3 class="fw-bold text-dark mb-0 count-up" data-target="{{ $attendancesToday }}">0</h3>
                                    </div>
                                    <div class="icon-shape bg-success bg-opacity-10 text-success rounded-3 p-2">
                                        <i class="mdi mdi-calendar-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 animate-enter" style="animation-delay: 0.4s">
                        <div class="card border-0 shadow-sm h-100 card-stat">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="text-muted small mb-1 fw-bold text-uppercase">Pending</p>
                                        <h3 class="fw-bold text-dark mb-0 count-up" data-target="{{ $pendingVerifications }}">0</h3>
                                    </div>
                                    <div class="icon-shape bg-warning bg-opacity-10 text-warning rounded-3 p-2">
                                        <i class="mdi mdi-clock-alert"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif (auth()->user()->role == 'audit')
                <div class="row mb-4">
                    <div class="col-md-4 col-12 mb-3 mb-md-0 animate-enter">
                        <div class="card border-0 shadow-sm h-100 card-stat border-start border-4 border-warning">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="text-muted small mb-1 fw-bold">Verifikasi Absen</p>
                                        <h3 class="fw-bold mb-2 count-up" data-target="{{ $pendingVerifications }}">0</h3>
                                        <a href="{{ route('audit.verify.list') }}" class="small text-warning fw-bold text-decoration-none">Review Sekarang &rarr;</a>
                                    </div>
                                    <i class="mdi mdi-clipboard-check display-4 text-warning opacity-25"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-12 mb-3 mb-md-0 animate-enter">
                        <div class="card border-0 shadow-sm h-100 card-stat border-start border-4 border-info">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="text-muted small mb-1 fw-bold">Approval Izin</p>
                                        <h3 class="fw-bold mb-2 count-up" data-target="{{ $pendingLeaves }}">0</h3>
                                        <a href="{{ route('leave-requests.index') }}" class="small text-info fw-bold text-decoration-none">Cek Pengajuan &rarr;</a>
                                    </div>
                                    <i class="mdi mdi-file-document-edit display-4 text-info opacity-25"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-12 animate-enter">
                        <div class="card border-0 shadow-sm h-100 card-stat border-start border-4 border-success">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="text-muted small mb-1 fw-bold">Hadir Hari Ini</p>
                                        <h3 class="fw-bold mb-2 count-up" data-target="{{ $attendancesToday }}">0</h3>
                                        <span class="small text-muted">Total kehadiran</span>
                                    </div>
                                    <i class="mdi mdi-account-check display-4 text-success opacity-25"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif (auth()->user()->role == 'security')
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 animate-enter">
                        <div class="card border-0 shadow-sm bg-dark text-white h-100 overflow-hidden">
                            <div class="card-body p-4 d-flex align-items-center justify-content-between position-relative">
                                <div style="position: relative; z-index: 2;">
                                    <h4 class="fw-bold mb-2">Scan QR Code</h4>
                                    <p class="text-white-50 mb-3 small">Gunakan kamera untuk memindai QR User.</p>
                                    <a href="{{ route('security.scan') }}" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm">
                                        <i class="mdi mdi-camera-enhance me-1"></i> Mulai Scan
                                    </a>
                                </div>
                                <i class="mdi mdi-qrcode-scan position-absolute text-white opacity-10" style="right: -20px; bottom: -20px; font-size: 8rem;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3 animate-enter">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold text-muted mb-0">Statistik Scan Hari Ini</h6>
                                    <span class="badge bg-success bg-opacity-10 text-success">Live</span>
                                </div>
                                <div class="d-flex align-items-end gap-3">
                                    <h2 class="fw-bold mb-0 text-dark display-4 count-up" data-target="{{ $myScansToday }}">0</h2>
                                    <span class="text-muted mb-2">Total Scan</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 2. STATUS ABSENSI & ACTION CARD (FORMERLY IN SIDEBAR/CENTER) --}}
            <div class="card border-0 shadow-sm mb-4 animate-enter" style="animation-delay: 0.5s">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="mdi mdi-fingerprint text-primary me-2"></i>Status Kehadiran</h5>
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                            <i class="mdi mdi-calendar-clock me-1"></i> Jadwal: {{ $todaySchedule }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-4">
                    
                    {{-- AREA PESAN ALERT --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show border-0 bg-success bg-opacity-10 text-success mb-4" role="alert">
                            <i class="mdi mdi-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show border-0 bg-danger bg-opacity-10 text-danger mb-4" role="alert">
                            <i class="mdi mdi-alert-circle me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show border-0 bg-warning bg-opacity-10 text-warning mb-4" role="alert">
                            <i class="mdi mdi-alert me-2"></i> {{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- LOGIKA UTAMA STATUS ABSENSI --}}
                    @if ($myAttendanceToday)
                        {{-- Logic Cross Day --}}
                        @php
                            $isCrossDay = false;
                            if (!$myAttendanceToday->check_out_time) {
                                $isCrossDay = $myAttendanceToday->check_in_time->format('Y-m-d') !== \Carbon\Carbon::now($current_timezone)->format('Y-m-d');
                            }
                            $sourceLabel = $myAttendanceToday->attendance_type == 'scan' ? 'Security Scan' : 'Selfie Mandiri';
                        @endphp

                        {{-- A. SUDAH PULANG --}}
                        @if ($myAttendanceToday->check_out_time || $myAttendanceToday->photo_out_path)
                            <div class="p-4 rounded-3 text-center" style="background-color: #f0fdf4; border: 1px dashed #16a34a;">
                                <div class="mb-3">
                                    <i class="mdi mdi-check-decagram display-3 text-success"></i>
                                </div>
                                <h4 class="fw-bold text-success">Absensi Selesai</h4>
                                <p class="text-muted mb-4">Terima kasih atas dedikasi Anda hari ini.</p>
                                
                                <div class="row justify-content-center g-3">
                                    <div class="col-6 col-md-4">
                                        <div class="bg-white p-2 rounded border shadow-sm">
                                            <small class="text-muted d-block text-uppercase" style="font-size: 10px;">Jam Masuk</small>
                                            <span class="fw-bold text-dark fs-5">{{ $myAttendanceToday->check_in_time->format('H:i') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="bg-white p-2 rounded border shadow-sm">
                                            <small class="text-muted d-block text-uppercase" style="font-size: 10px;">Jam Pulang</small>
                                            <span class="fw-bold text-dark fs-5">{{ $myAttendanceToday->check_out_time ? $myAttendanceToday->check_out_time->format('H:i') : '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        {{-- B. SEDANG BEKERJA (BELUM PULANG) --}}
                        @else
                            <div class="p-4 rounded-3 border position-relative overflow-hidden {{ $isCrossDay ? 'bg-warning bg-opacity-10 border-warning' : 'bg-primary bg-opacity-10 border-primary' }}">
                                <div class="d-flex align-items-center mb-3 position-relative z-index-1">
                                    <div class="spinner-grow {{ $isCrossDay ? 'text-warning' : 'text-primary' }} me-3" role="status" style="width: 1.5rem; height: 1.5rem;"></div>
                                    <div>
                                        <h5 class="fw-bold mb-0 text-dark">Status: {{ $isCrossDay ? 'Lembur Lintas Hari' : 'Sedang Bekerja' }}</h5>
                                        <small class="text-muted">Masuk pukul <strong>{{ $myAttendanceToday->check_in_time->format('H:i') }}</strong> via {{ $sourceLabel }}</small>
                                    </div>
                                </div>

                                {{-- ACTION BUTTONS PULANG --}}
                                @if (!$isCrossDay)
                                    <div class="bg-white p-3 rounded-3 shadow-sm border mt-3 position-relative z-index-1">
                                        @if (Auth::user()->only_security_scan)
                                            <button class="btn btn-secondary w-100 disabled" style="opacity: 0.7;">
                                                <i class="mdi mdi-lock me-1"></i> Absen Pulang Dikunci (Scan Security Wajib)
                                            </button>
                                        @else
                                            <a href="{{ route('self.attend.create', ['attendance_id' => $myAttendanceToday->id, 'mode' => 'pulang']) }}" 
                                               class="btn btn-danger w-100 py-2 fw-bold shadow-sm hover-scale">
                                                <i class="mdi mdi-logout-variant me-2"></i> Absen Pulang Sekarang
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    {{-- CROSS DAY LOGIC --}}
                                    <div class="bg-white p-3 rounded-3 shadow-sm border mt-3 border-warning position-relative z-index-1">
                                        <h6 class="text-danger fw-bold small text-uppercase mb-3"><i class="mdi mdi-alert me-1"></i> Konfirmasi Pulang Lintas Hari</h6>
                                        
                                        <div id="cross-day-actions">
                                            {{-- OPSI 1: SLIDER --}}
                                            <div class="mb-3" id="slider-view">
                                                <div class="position-relative w-100 rounded-pill d-flex align-items-center px-1 shadow-sm" id="slide-track" style="height: 50px; background-color: #fef08a; transition: all 0.2s;">
                                                    <div class="position-absolute w-100 text-center" style="pointer-events: none; left:0;">
                                                        <span class="fw-bold text-dark small opacity-50" style="letter-spacing: 1px;">GESER KE KANAN UNTUK PULANG >></span>
                                                    </div>
                                                    <div id="slide-thumb" class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center text-warning" style="width: 42px; height: 42px; cursor: pointer; position: absolute; left: 4px; z-index: 10;">
                                                        <i class="mdi mdi-arrow-right fw-bold fs-5"></i>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- OPSI 2: SKIP --}}
                                            <form action="{{ route('self.attend.skip', $myAttendanceToday->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-link text-danger btn-sm w-100 text-decoration-none" onclick="return confirm('Yakin lewati? Sesi akan ditutup tanpa foto.');">
                                                    Lupa Absen? (Tutup Sesi Tanpa Foto)
                                                </button>
                                            </form>
                                        </div>

                                        {{-- HIDDEN CAMERA BUTTON --}}
                                        <div id="camera-view" class="d-none mt-3 animate-enter">
                                            <a href="{{ route('self.attend.create', ['attendance_id' => $myAttendanceToday->id, 'mode' => 'pulang']) }}" class="btn btn-primary w-100 py-3 fw-bold rounded-3">
                                                <i class="mdi mdi-camera me-2"></i> Ambil Foto Validasi
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                    @elseif($myPendingLeave)
                        {{-- STATUS CUTI PENDING --}}
                        <div class="p-4 rounded-3 bg-warning bg-opacity-10 border border-warning text-center">
                            <i class="mdi mdi-timer-sand display-4 text-warning mb-2"></i>
                            <h5 class="fw-bold text-dark">Menunggu Persetujuan Izin</h5>
                            <p class="text-muted mb-3 small">Pengajuan <strong>{{ strtoupper($myPendingLeave->type) }}</strong> sedang diproses oleh Audit.</p>
                            <div class="bg-white p-2 rounded mb-3 border">
                                <small class="fst-italic">"{{ $myPendingLeave->reason }}"</small>
                            </div>
                            <form action="{{ route('leave-requests.cancel', $myPendingLeave->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <button class="btn btn-outline-danger btn-sm rounded-pill px-4" onclick="return confirm('Batalkan?')">Batalkan Pengajuan</button>
                            </form>
                        </div>

                    @elseif(isset($myLeaveToday) && $myLeaveToday && $myLeaveToday->status == 'approved')
                        {{-- STATUS CUTI APPROVED --}}
                        <div class="p-4 rounded-3 bg-success bg-opacity-10 border border-success text-center">
                            <i class="mdi mdi-check-decagram display-4 text-success mb-2"></i>
                            <h5 class="fw-bold text-dark">Izin Disetujui</h5>
                            <p class="text-muted mb-3 small">Status Anda hari ini: <strong>{{ strtoupper($myLeaveToday->type) }}</strong></p>
                            
                            @if ($myLeaveToday->type === 'telat')
                                <div class="bg-white p-3 rounded-3 shadow-sm mt-3">
                                    <p class="small text-muted mb-2">Sudah sampai kantor? Silahkan absen.</p>
                                    <a href="{{ route('self.attend.create') }}" class="btn btn-primary w-100 py-2">
                                        <i class="mdi mdi-fingerprint me-1"></i> Absen Masuk
                                    </a>
                                </div>
                            @else
                                <form action="{{ route('leave-requests.finish-early', $myLeaveToday->id) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-outline-danger btn-sm rounded-pill px-4 mt-2" onclick="return confirm('Batalkan izin dan masuk kerja?')">Masuk Kerja & Batalkan Izin</button>
                                </form>
                            @endif
                        </div>

                    @else
                        {{-- STATUS BELUM ABSEN (DEFAULT) --}}
                        <div class="p-5 rounded-3 border text-center bg-light">
                            <div class="mb-3">
                                <div class="d-inline-block p-3 rounded-circle bg-white shadow-sm text-primary">
                                    <i class="mdi mdi-fingerprint display-4"></i>
                                </div>
                            </div>
                            <h4 class="fw-bold text-dark mb-2">Anda Belum Absen</h4>
                            <p class="text-muted mb-4">Silahkan lakukan absensi masuk untuk memulai hari kerja Anda.</p>
                            
                            <div class="d-grid gap-2 d-md-block">
                                @if (Auth::user()->only_security_scan)
                                    <button class="btn btn-secondary px-4 py-2 disabled" style="cursor: not-allowed;">
                                        <i class="mdi mdi-lock me-1"></i> Mandiri Locked
                                    </button>
                                    <p class="text-danger small mt-2"><i class="mdi mdi-alert-circle me-1"></i>Wajib Scan QR ke Security</p>
                                @else
                                    <a href="{{ route('self.attend.create') }}" class="btn btn-primary px-5 py-2 rounded-pill shadow-sm fw-bold hover-scale">
                                        Absen Masuk Sekarang
                                    </a>
                                @endif
                                <a href="{{ route('leave-requests.create') }}" class="btn btn-link text-muted text-decoration-none small mt-2 d-block">
                                    Tidak bisa hadir? Ajukan Izin
                                </a>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- 3. CHART SECTION (DI BAWAH ABSENSI) --}}
            <div class="card border-0 shadow-sm animate-enter" style="animation-delay: 0.6s">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold fs-6">Statistik Kehadiran</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 300px; width: 100%;">
                        <canvas id="attendancePieChart"></canvas>
                    </div>
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN (SIDEBAR): ID CARD, QR, LEADERBOARD, MENU --}}
        <div class="col-xl-4 col-lg-5">
            
            {{-- 1. ID CARD DIGITAL --}}
            <div class="card border-0 shadow-sm mb-4 animate-enter" style="animation-delay: 0.2s">
                <div class="card-body p-0">
                    <div class="id-card-modern p-4 text-white position-relative overflow-hidden" 
                         style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border-radius: 16px;">
                        
                        <div style="position: absolute; top: 0; right: 0; bottom: 0; left: 0; background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 20px 20px; opacity: 0.3;"></div>

                        <div class="d-flex justify-content-between align-items-start mb-4 position-relative z-index-1">
                            <div>
                                <h5 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 2px;">ID CARD</h5>
                                <small class="opacity-75" style="font-size: 10px;">{{ config('app.name') }} EMPLOYEE</small>
                            </div>
                            <i class="mdi mdi-nfc-variant fs-3 opacity-50"></i>
                        </div>

                        <div class="d-flex align-items-center mb-4 position-relative z-index-1">
                            <div class="me-3">
                                @if (Auth::user()->profile_photo_path)
                                    <img src="{{ Storage::url(Auth::user()->profile_photo_path) }}" 
                                         class="rounded-3 border border-2 border-white shadow-sm" 
                                         style="width: 70px; height: 70px; object-fit: cover;"
                                         data-bs-toggle="modal" data-bs-target="#profilePhotoModal"
                                         data-src="{{ Storage::url(Auth::user()->profile_photo_path) }}"
                                         role="button">
                                @else
                                    <div class="rounded-3 border border-2 border-white shadow-sm bg-white text-primary d-flex align-items-center justify-content-center fw-bold fs-3"
                                         style="width: 70px; height: 70px;">
                                        {{ substr(Auth::user()->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="overflow-hidden">
                                <h5 class="fw-bold mb-0 text-truncate">{{ strtoupper(Auth::user()->name) }}</h5>
                                <p class="mb-0 opacity-75 small text-truncate">{{ strtoupper(Auth::user()->division->name ?? 'Staff') }}</p>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-end position-relative z-index-1">
                            <div>
                                <small class="d-block opacity-50" style="font-size: 9px;">ID NUMBER</small>
                                <span class="font-monospace fw-bold fs-5">{{ $idCardNumber ?? '000 000 000' }}</span>
                            </div>
                            <div class="bg-white p-1 rounded" id="dashboard-qrcode" role="button" data-bs-toggle="modal" data-bs-target="#qrModal">
                                </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. MENU CEPAT --}}
            <div class="card border-0 shadow-sm mb-4 animate-enter" style="animation-delay: 0.3s">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0">Menu Cepat</h6>
                </div>
                <div class="card-body p-2">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('leave-requests.create') }}" class="list-group-item list-group-item-action d-flex align-items-center py-3 border-0 rounded-2 hover-bg-light">
                            <div class="icon-sm bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                <i class="mdi mdi-file-document-edit"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 text-dark fw-bold" style="font-size: 14px;">Ajukan Izin</h6>
                                <small class="text-muted">Sakit, Cuti, atau Keperluan lain</small>
                            </div>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </a>
                        <a href="{{ route('attendance.history') }}" class="list-group-item list-group-item-action d-flex align-items-center py-3 border-0 rounded-2 hover-bg-light">
                            <div class="icon-sm bg-success bg-opacity-10 text-success rounded p-2 me-3">
                                <i class="mdi mdi-history"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 text-dark fw-bold" style="font-size: 14px;">Riwayat Absensi</h6>
                                <small class="text-muted">Lihat log kehadiran Anda</small>
                            </div>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- 3. LEADERBOARD (CLEAN VERSION) --}}
            @if(auth()->user()->role != 'security' && isset($leaderboard) && count($leaderboard) > 0)
                <div class="card border-0 shadow-sm animate-enter overflow-hidden" style="animation-delay: 0.4s">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">Top Rajin Bulan Ini</h6>
                        <i class="mdi mdi-trophy-variant text-warning fs-5"></i>
                    </div>
                    <div class="card-body p-0">
                        {{-- JUARA 1 --}}
                        @if(isset($leaderboard[0]))
                            <div class="p-4 text-center bg-light border-bottom position-relative">
                                <div class="position-absolute top-0 start-50 translate-middle-x bg-warning text-dark px-3 py-1 rounded-bottom shadow-sm fw-bold small" style="font-size: 10px;">
                                    CHAMPION
                                </div>
                                <div class="mt-2 mb-2 position-relative d-inline-block">
                                    @if($leaderboard[0]->user->profile_photo_path)
                                        <img src="{{ asset('storage/'.$leaderboard[0]->user->profile_photo_path) }}" class="rounded-circle shadow border border-3 border-warning" style="width: 72px; height: 72px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle shadow border border-3 border-warning bg-white d-flex align-items-center justify-content-center fw-bold fs-3 text-warning" style="width: 72px; height: 72px;">
                                            {{ substr($leaderboard[0]->user->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <span class="position-absolute bottom-0 start-50 translate-middle-x badge rounded-pill bg-warning text-dark border border-white">#1</span>
                                </div>
                                <h6 class="fw-bold text-dark mb-0">{{ Str::limit($leaderboard[0]->user->name, 20) }}</h6>
                                <small class="text-muted">{{ $leaderboard[0]->user->division->name ?? '-' }}</small>
                                <div class="mt-2 badge bg-white border text-dark shadow-sm">
                                    {{ $leaderboard[0]->total_attendance }} Kehadiran
                                </div>
                            </div>
                        @endif

                        {{-- RUNNER UPS (LIST) --}}
                        <div class="list-group list-group-flush">
                            @foreach($leaderboard as $index => $rank)
                                @if($index > 0 && $index < 5)
                                    <div class="list-group-item border-0 d-flex align-items-center py-3">
                                        <span class="fw-bold text-muted me-3" style="width: 20px;">{{ $index + 1 }}</span>
                                        <div class="me-3">
                                            @if($rank->user->profile_photo_path)
                                                <img src="{{ asset('storage/'.$rank->user->profile_photo_path) }}" class="rounded-circle border" style="width: 36px; height: 36px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle border bg-light d-flex align-items-center justify-content-center text-muted fw-bold small" style="width: 36px; height: 36px;">
                                                    {{ substr($rank->user->name, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h6 class="mb-0 text-dark fw-bold small text-truncate">{{ $rank->user->name }}</h6>
                                            <small class="text-muted d-block" style="font-size: 10px;">{{ $rank->user->division->name ?? '-' }}</small>
                                        </div>
                                        <span class="badge bg-light text-dark border">{{ $rank->total_attendance }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- TOP SCANNER (ADMIN/SECURITY) --}}
            @if((auth()->user()->role == 'admin' || auth()->user()->role == 'security') && isset($topScanners) && count($topScanners) > 0)
                <div class="card border-0 shadow-sm mt-4 animate-enter">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold mb-0">Top Security Scanner</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($topScanners as $index => $scanner)
                                @if($index < 3)
                                <div class="list-group-item border-0 d-flex align-items-center py-3">
                                    <div class="badge bg-dark rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">{{ $index + 1 }}</div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 small fw-bold">{{ $scanner->name }}</h6>
                                    </div>
                                    <span class="fw-bold text-primary small">{{ $scanner->total_scans }} Scan</span>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- MODALS --}}
    
    {{-- MODAL FOTO PROFIL --}}
    <div class="modal fade" id="profilePhotoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-transparent border-0 shadow-none">
                <div class="modal-body text-center p-0 position-relative">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    <img src="" id="profileModalImageSrc" class="img-fluid rounded-4 shadow-lg" style="max-height: 80vh;">
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL QR CODE --}}
    <div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header border-0 pb-0 justify-content-center">
                    <h6 class="modal-title fw-bold mt-3">Scan QR Code Saya</h6>
                </div>
                <div class="modal-body text-center">
                    <div class="p-3 bg-light rounded-4 d-inline-block mb-3 border">
                        <div id="qrcode-modal-display" class="d-flex justify-content-center"></div>
                    </div>
                    <p class="text-muted small mb-2">Tunjukkan kepada Security untuk melakukan Absensi.</p>
                    <button type="button" class="btn btn-light rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        /* === UTILITIES & ANIMATIONS === */
        .animate-enter {
            animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }

        .hover-scale { transition: transform 0.2s; }
        .hover-scale:hover { transform: scale(1.02); }

        .hover-bg-light:hover { background-color: #f8f9fa !important; }

        .z-index-1 { z-index: 1; }

        /* === SLIDER STYLE === */
        #slide-thumb { transition: transform 0.1s; }
        #slide-thumb:active { cursor: grabbing !important; }

        /* === CUSTOM SCROLLBAR FOR LISTS === */
        .card-stat { transition: transform 0.3s, box-shadow 0.3s; }
        .card-stat:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; }

        /* === MOBILE TWEAKS === */
        @media (max-width: 768px) {
            #header-clock { font-size: 1rem; }
            .display-3 { font-size: 2.5rem; }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // --- 1. CLOCK LOGIC (TimeZone Aware) ---
            function updateClock() {
                const timeZone = "{{ $current_timezone }}";
                const now = new Date();
                const timeString = now.toLocaleTimeString('en-US', {
                    timeZone: timeZone,
                    hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit'
                });
                
                const clockEl = document.getElementById('header-clock');
                if(clockEl) clockEl.innerText = timeString;

                // Greeting
                const hour = parseInt(timeString.split(':')[0]);
                let greet = "Selamat Pagi,";
                if(hour >= 11 && hour < 15) greet = "Selamat Siang,";
                else if(hour >= 15 && hour < 18) greet = "Selamat Sore,";
                else if(hour >= 18 || hour < 4) greet = "Selamat Malam,";
                
                const greetEl = document.getElementById('greeting-text');
                if(greetEl) greetEl.innerText = greet;
            }
            setInterval(updateClock, 1000);
            updateClock();

            // --- 2. SLIDER LOGIC (DRAG TO CONFIRM) ---
            const track = document.getElementById('slide-track');
            const thumb = document.getElementById('slide-thumb');
            const sliderView = document.getElementById('slider-view');
            const cameraView = document.getElementById('camera-view');
            const actionsContainer = document.getElementById('cross-day-actions');

            if (track && thumb) {
                let isDragging = false, startX, maxMove;
                
                const initSizes = () => {
                    maxMove = track.clientWidth - thumb.clientWidth - 8;
                };
                window.addEventListener('resize', initSizes);
                initSizes();

                const start = (e) => {
                    isDragging = true;
                    startX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
                    thumb.style.transition = 'none';
                };

                const move = (e) => {
                    if(!isDragging) return;
                    e.preventDefault(); // Prevent scroll on mobile
                    const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
                    let delta = clientX - startX;
                    delta = Math.max(0, Math.min(delta, maxMove));
                    thumb.style.transform = `translateX(${delta}px)`;
                    track.querySelector('span').style.opacity = 1 - (delta/maxMove);
                };

                const end = () => {
                    if(!isDragging) return;
                    isDragging = false;
                    thumb.style.transition = 'transform 0.2s';
                    
                    const currentX = new DOMMatrix(window.getComputedStyle(thumb).transform).m41;
                    
                    if(currentX > maxMove * 0.8) {
                        // Success
                        thumb.style.transform = `translateX(${maxMove}px)`;
                        thumb.innerHTML = '<i class="mdi mdi-check text-success fs-5"></i>';
                        track.className = 'position-relative w-100 rounded-pill d-flex align-items-center px-1 shadow-sm bg-success bg-opacity-25';
                        
                        setTimeout(() => {
                            if(actionsContainer) actionsContainer.classList.add('d-none');
                            else sliderView.classList.add('d-none');
                            cameraView.classList.remove('d-none');
                        }, 400);
                    } else {
                        // Reset
                        thumb.style.transform = `translateX(0px)`;
                        track.querySelector('span').style.opacity = 0.5;
                    }
                };

                thumb.addEventListener('mousedown', start);
                thumb.addEventListener('touchstart', start);
                window.addEventListener('mousemove', move);
                window.addEventListener('touchmove', move, {passive: false});
                window.addEventListener('mouseup', end);
                window.addEventListener('touchend', end);
            }

            // --- 3. COUNT UP ANIMATION ---
            document.querySelectorAll('.count-up').forEach(el => {
                const target = +el.getAttribute('data-target');
                const duration = 1500;
                const increment = target / (duration / 16);
                let current = 0;
                
                const update = () => {
                    current += increment;
                    if(current < target) {
                        el.innerText = Math.ceil(current);
                        requestAnimationFrame(update);
                    } else {
                        el.innerText = target;
                    }
                };
                update();
            });

            // --- 4. QR CODE GENERATION ---
            @if (Auth::user()->qr_code_value)
                const qrText = "{{ Auth::user()->qr_code_value }}";
                
                // Small QR in ID Card
                new QRCode(document.getElementById("dashboard-qrcode"), {
                    text: qrText, width: 40, height: 40,
                    colorDark: "#000", colorLight: "#fff",
                    correctLevel: QRCode.CorrectLevel.L
                });

                // Modal QR
                const qrModal = document.getElementById('qrModal');
                qrModal.addEventListener('show.bs.modal', () => {
                    const container = document.getElementById('qrcode-modal-display');
                    container.innerHTML = '';
                    new QRCode(container, {
                        text: qrText, width: 200, height: 200
                    });
                });
            @endif

            // --- 5. IMAGE MODAL ---
            const photoModal = document.getElementById('profilePhotoModal');
            if(photoModal) {
                photoModal.addEventListener('show.bs.modal', (e) => {
                    const src = e.relatedTarget.getAttribute('data-src');
                    document.getElementById('profileModalImageSrc').src = src;
                });
            }

            // --- 6. CHARTS ---
            const ctx = document.getElementById('attendancePieChart').getContext('2d');
            Chart.defaults.font.family = "'Inter', sans-serif";
            
            @if (auth()->user()->role == 'admin')
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Tepat Waktu', 'Terlambat', 'Early', 'Pending', 'Absen'],
                        datasets: [{
                            data: [{{ $stats['on_time'] }}, {{ $stats['late'] }}, {{ $stats['early'] }}, {{ $stats['pending'] }}, {{ $stats['absent'] }}],
                            backgroundColor: ['#10b981', '#f59e0b', '#f43f5e', '#3b82f6', '#94a3b8'],
                            borderWidth: 0,
                            hoverOffset: 5
                        }]
                    },
                    options: { maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'right', labels: { usePointStyle: true, boxWidth: 8 } } } }
                });
            @elseif (auth()->user()->role == 'audit')
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Verified', 'Pending', 'Late'],
                        datasets: [{
                            data: [{{ $stats['verified'] }}, {{ $stats['pending'] }}, {{ $stats['late'] }}],
                            backgroundColor: ['#10b981', '#f59e0b', '#f43f5e'],
                            borderWidth: 0
                        }]
                    },
                    options: { maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom' } } }
                });
            @elseif (auth()->user()->role == 'security')
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['Masuk', 'Pulang'],
                        datasets: [{
                            data: [{{ $stats['check_in_scans'] }}, {{ $stats['check_out_scans'] }}],
                            backgroundColor: ['#10b981', '#3b82f6'],
                            borderWidth: 2, borderColor: '#fff'
                        }]
                    },
                    options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
                });
            @else
                // User Normal
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['On Time', 'Late', 'Early', 'Pending'],
                        datasets: [{
                            data: [{{ $stats['on_time'] }}, {{ $stats['late'] }}, {{ $stats['early'] }}, {{ $stats['pending'] }}],
                            backgroundColor: ['#10b981', '#f59e0b', '#f43f5e', '#cbd5e1'],
                            borderWidth: 0
                        }]
                    },
                    options: { maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'right', labels: { usePointStyle: true } } } }
                });
            @endif
        });
    </script>
@endpush