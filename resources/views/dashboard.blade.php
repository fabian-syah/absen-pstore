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

    {{-- STATISTIK (HANYA JIKA BUKAN KARYAWAN BIASA) --}}
    @if (auth()->user()->role != 'user_biasa')
        <div class="row mb-4">
            <div class="col-md-3 grid-margin stretch-card animate-enter" style="animation-delay: 0.1s">
                <div class="card card-bank gradient-purple">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi mdi-account-multiple"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Total User</p>
                            <h2 class="card-bank-value count-up" data-target="{{ $totalUsers ?? 0 }}">0</h2>
                            <p class="card-bank-desc">Karyawan Aktif</p>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
            {{-- Widget lain disederhanakan agar tidak terlalu panjang, logika tetap ada di controller --}}
        </div>
    @endif

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
                                    <p class="mb-0 text-white-50" style="font-size: 10px; letter-spacing: 1px;">NOMOR ID</p>
                                    <p class="card-id-card-number mb-0"
                                        style="font-size: 22px; letter-spacing: 2px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                                        {{ $idCardNumber ?? '000000 000000' }}
                                    </p>
                                </div>
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

                    {{-- LOGIKA TAMPILAN STATUS --}}
                    @if ($isStillWorkingOvertime && $myAttendanceToday)
                        {{-- STATUS: SEDANG LEMBUR (ACTIVE CROSS DAY) --}}
                        <div class="status-card mb-3 animate-pulse-purple" style="background: #f3e8ff; border: 2px solid #a855f7;">
                            <div class="d-flex align-items-center">
                                <div class="status-icon shadow" style="background: #9333ea; color: white;">
                                    <i class="mdi mdi-moon-waning-crescent"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1 fw-bold text-dark">Sedang Lembur</h5>
                                    <div class="small text-muted">
                                        Masuk sejak: <strong>{{ $myAttendanceToday->check_in_time->format('d M, H:i') }}</strong>
                                    </div>
                                    @if($overtimeDuration)
                                        <div class="badge bg-purple text-white mt-1" style="background: #7e22ce;">
                                            Sudah: {{ $overtimeDuration->format('%h jam %i menit') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <hr style="border-top: 1px solid #d8b4fe;">
                            
                            {{-- TOMBOL ABSEN PULANG LEMBUR --}}
                            <div class="mt-2">
                                <a href="{{ route('self.attend.create', ['attendance_id' => $myAttendanceToday->id, 'mode' => 'pulang']) }}"
                                   class="btn btn-primary btn-sm w-100 shadow hover-scale" style="background: #9333ea; border-color: #9333ea;">
                                    <i class="mdi mdi-logout me-1"></i> Absen Pulang (Lembur)
                                </a>
                                <small class="d-block mt-2 text-muted fst-italic text-center" style="font-size: 10px;">
                                    *Jam pulang akan tercatat saat Anda menekan tombol ini.
                                </small>
                            </div>
                        </div>

                    @elseif ($myAttendanceToday)
                        {{-- STATUS: MASUK HARI INI (NORMAL) --}}
                        @if ($myAttendanceToday->check_out_time || $myAttendanceToday->photo_out_path)
                            <div class="status-card status-success mb-3 animate-pulse-green">
                                <div class="d-flex align-items-center">
                                    <div class="status-icon shadow"><i class="mdi mdi-home-variant"></i></div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1 fw-bold">Anda Sudah Pulang</h5>
                                        <p class="text-muted mb-0 small">Terima kasih atas kerja keras Anda!</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="status-card status-success mb-3 position-relative overflow-hidden">
                                <div class="d-flex align-items-center position-relative z-index-1">
                                    <div class="status-icon shadow pulse-animation">
                                        <i class="mdi mdi-clock-check"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1 fw-bold">Sedang Bekerja</h5>
                                        <p class="mb-0">Masuk Pukul: <strong>{{ $myAttendanceToday->check_in_time->format('H:i') }}</strong></p>
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 border-top position-relative z-index-1">
                                    <a href="{{ route('self.attend.create', ['attendance_id' => $myAttendanceToday->id, 'mode' => 'pulang']) }}"
                                       class="btn btn-danger btn-sm w-100 shadow hover-scale">
                                        <i class="mdi mdi-logout me-1"></i> Absen Pulang Mandiri
                                    </a>
                                </div>
                            </div>
                        @endif

                    @elseif($myPendingLeave)
                        <div class="status-card status-warning mb-3 hover-shadow-lg">
                            <div class="text-center py-4">
                                <h4 class="mb-2 fw-bold text-warning">Menunggu Approve Izin</h4>
                                <p class="text-muted mb-0">{{ strtoupper($myPendingLeave->type) }}</p>
                            </div>
                        </div>

                    @elseif($justFinishedOvertime)
                         <div class="status-card status-info mb-3 hover-shadow-lg">
                            <div class="text-center py-4">
                                <i class="mdi mdi-bed-clock display-4 text-info"></i>
                                <h5 class="mb-2 fw-bold text-info">Selamat Beristirahat!</h5>
                                <p class="text-muted mb-4 px-3 small">
                                    Anda baru saja pulang lembur pukul <strong>{{ $lastOvertimeSession->check_out_time->format('H:i') }}</strong>.
                                </p>
                                <a href="{{ route('self.attend.create') }}" class="btn btn-outline-info shadow hover-scale">
                                    <i class="mdi mdi-fingerprint me-1"></i> Absen Shift Baru
                                </a>
                            </div>
                        </div>

                    @else
                        {{-- BELUM ABSEN --}}
                        <div class="status-card status-info hover-shadow-lg">
                            <div class="text-center py-4">
                                <div class="mb-3">
                                    <i class="mdi mdi-clock-alert display-4 text-primary pulse-text"></i>
                                </div>
                                <h5 class="mb-2 fw-bold">Anda Belum Absen Hari Ini</h5>
                                <div class="d-flex justify-content-center gap-2 mt-4">
                                    <a href="{{ route('self.attend.create') }}" class="btn btn-dark shadow hover-scale">
                                        <i class="mdi mdi-fingerprint me-2"></i>Absen Mandiri
                                    </a>
                                    <a href="{{ route('leave-requests.create') }}" class="btn btn-outline-dark shadow-sm hover-scale">
                                        <i class="mdi mdi-file-document-edit-outline me-2"></i>Izin
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection