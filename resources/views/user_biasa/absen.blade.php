@extends('layout.master')

@section('title')
    Absen Mandiri - {{ ucfirst($mode) }}
@endsection

@section('heading')
    Absen Mandiri
@endsection

@section('content')
<div class="absen-page-wrapper">
    {{-- TOP NAVIGATION & CONSOLE BAR --}}
    <div class="console-top-bar d-flex align-items-center justify-content-between mb-3 mb-md-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('dashboard') }}" class="btn-console-back" title="Kembali ke Dashboard">
                <i class="mdi mdi-arrow-left"></i>
            </a>
            <div class="ms-3">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    @if($mode == 'masuk')
                        <span class="mode-badge masuk">
                            <span class="pulse-indicator"></span>
                            <i class="mdi mdi-login me-1"></i> Absensi Masuk
                        </span>
                    @else
                        <span class="mode-badge pulang">
                            <span class="pulse-indicator"></span>
                            <i class="mdi mdi-logout me-1"></i> Absensi Pulang
                        </span>
                    @endif

                    @if(Auth::user()->use_face_recognition)
                        <span class="tech-badge ai" title="Deteksi Wajah AI Aktif">
                            <i class="mdi mdi-face-recognition me-1"></i> AI Face Active
                        </span>
                    @else
                        <span class="tech-badge manual" title="Mode Kamera Bebas (Tanpa AI)">
                            <i class="mdi mdi-camera-outline me-1"></i> Mode Manual
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- LIVE DIGITAL CLOCK & TIMEZONE --}}
        <div class="live-clock-card text-end d-none d-sm-block">
            <div class="clock-time" id="live-digital-clock">--:--:--</div>
            <div class="clock-date">
                <i class="mdi mdi-calendar-blank-outline me-1"></i>
                <span>{{ \Carbon\Carbon::now($branchTimezone ?? 'Asia/Jakarta')->translatedFormat('l, d F Y') }}</span>
                <span class="badge-tz ms-1">{{ strtoupper(str_replace('Asia/', '', $branchTimezone ?? 'WIB')) }}</span>
            </div>
        </div>
    </div>

    {{-- KTP WARNING BANNER (IF ANY) --}}
    @if(isset($ktpWarningMsg) && $ktpWarningMsg)
        <div class="alert ktp-warning-banner d-flex align-items-center mb-4" role="alert">
            <div class="warning-icon-box flex-shrink-0 me-3">
                <i class="mdi mdi-shield-alert-outline"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold text-amber-900 mb-0.5" style="font-size: 13.5px;">Pemberitahuan Wajib KTP</div>
                <div class="text-amber-800" style="font-size: 12px; line-height: 1.4;">{{ $ktpWarningMsg }}</div>
            </div>
            <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-warning text-dark fw-bold ms-2 flex-shrink-0" style="font-size: 12px; border-radius: 8px;">
                Upload KTP
            </a>
        </div>
    @endif

    {{-- MAIN FORM & CONSOLE GRID --}}
    <form action="{{ route('self.attend.store') }}" method="POST" enctype="multipart/form-data" id="attendance-form">
        @csrf
        @if (isset($attendance) && $attendance)
            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
        @endif
        <input type="file" name="photo" id="photo-input-hidden" class="d-none" accept="image/jpeg">
        <input type="hidden" id="latitude" name="latitude">
        <input type="hidden" id="longitude" name="longitude">
        <input type="hidden" id="accuracy" name="accuracy">

        <div class="row g-3 g-xl-4 align-items-start justify-content-center">
            {{-- ========================================================= --}}
            {{-- LEFT COLUMN: BIOMETRIC CAMERA VIEWFINDER & HUD            --}}
            {{-- ========================================================= --}}
            <div class="col-12 col-lg-6 col-xl-5">
                <div class="biometric-camera-card">
                    {{-- VIEWFINDER CONTAINER --}}
                    <div class="viewfinder-wrapper" id="camera-viewport">
                        <video id="video-feed" autoplay muted playsinline></video>

                        {{-- SCANNING HOLOGRAPHIC BEAM --}}
                        @if(Auth::user()->use_face_recognition)
                            <div class="hologram-scan-line d-none" id="ai-scan-line"></div>
                        @endif

                        {{-- BIOMETRIC FACE FRAME OVERLAY --}}
                        <div class="biometric-hud-frame" id="face-frame">
                            <svg viewBox="0 0 240 300" class="hud-svg" xmlns="http://www.w3.org/2000/svg">
                                {{-- Corner Brackets --}}
                                <path class="hud-bracket" d="M 40,20 L 20,20 L 20,50" />
                                <path class="hud-bracket" d="M 200,20 L 220,20 L 220,50" />
                                <path class="hud-bracket" d="M 20,250 L 20,280 L 40,280" />
                                <path class="hud-bracket" d="M 220,250 L 220,280 L 200,280" />
                                {{-- Center Biometric Oval Target --}}
                                <ellipse class="hud-oval" cx="120" cy="150" rx="65" ry="90" />
                            </svg>
                        </div>

                        {{-- TOP STATUS PILL HUD --}}
                        <div class="viewfinder-top-status">
                            <div class="status-pill-hud idle" id="face-status">
                                <i class="mdi mdi-camera-off"></i>
                                <span>Kamera Belum Aktif</span>
                            </div>
                        </div>

                        {{-- START SCREEN OVERLAY --}}
                        <div class="viewfinder-overlay" id="start-screen">
                            <div class="overlay-center text-center p-4">
                                <div class="lens-animation-box mb-3">
                                    <div class="lens-ring outer"></div>
                                    <div class="lens-ring middle"></div>
                                    <div class="lens-core">
                                        <i class="mdi mdi-camera-iris"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold text-white mb-1" style="font-size: 17px;">Kamera Siaga Absensi</h5>
                                <p class="text-slate-300 small mb-3" style="font-size: 12px; max-width: 280px; margin: 0 auto;">
                                    Pastikan wajah berada di tempat terang dan terlihat jelas di kamera.
                                </p>
                                <button type="button" id="start-camera-btn" class="btn btn-start-camera shadow-lg">
                                    <i class="mdi mdi-camera-outline me-1.5"></i>
                                    <span>Aktifkan Kamera</span>
                                </button>
                                <div class="loading-state-box d-none" id="loading-state">
                                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                    <span class="text-white small">Menyiapkan modul kamera & AI...</span>
                                </div>
                            </div>
                        </div>

                        {{-- PHOTO RESULT PREVIEW OVERLAY --}}
                        <div class="viewfinder-overlay d-none" id="result-screen">
                            <img id="preview-image" src="#" alt="Pratinjau Foto Absensi">
                            
                            {{-- Top Status on Photo --}}
                            <div class="result-top-bar d-flex align-items-center justify-content-between p-3 position-absolute top-0 start-0 end-0 z-3">
                                <span class="badge-verified-capture">
                                    <i class="mdi mdi-check-circle me-1"></i> Foto Siap Digunakan
                                </span>
                            </div>

                            {{-- Bottom Floating Retake Action on Photo --}}
                            <div class="result-actions-bar d-flex align-items-center justify-content-center p-3 position-absolute bottom-0 start-0 end-0 z-3">
                                <button type="button" id="retake-btn" class="btn btn-sm btn-retake shadow-lg">
                                    <i class="mdi mdi-camera-retake me-1.5"></i> Ambil Ulang Foto
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- BOTTOM SHUTTER TRIGGER BAR --}}
                    <div class="shutter-trigger-section d-flex align-items-center justify-content-center p-3">
                        {{-- Active Shutter Button when camera is running --}}
                        <button type="button" id="capture-btn" class="shutter-button" disabled title="Ambil Foto">
                            <span class="shutter-ring-outer"></span>
                            <span class="shutter-core">
                                <i class="mdi mdi-camera"></i>
                            </span>
                        </button>

                        {{-- Retake Button shown in place of shutter when photo has been taken --}}
                        <div id="captured-action-group" class="d-none d-flex align-items-center justify-content-center w-100">
                            <button type="button" id="bottom-retake-btn" class="btn btn-retake-primary shadow-sm">
                                <i class="mdi mdi-camera-retake me-1.5"></i> Ambil Ulang Foto
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========================================================= --}}
            {{-- RIGHT COLUMN: VERIFICATION, LOCATION, NOTES & SUBMIT     --}}
            {{-- ========================================================= --}}
            <div class="col-12 col-lg-6 col-xl-5">
                <div class="d-flex flex-column gap-3">

                    {{-- MOBILE TIME DISPLAY (< 576px) --}}
                    <div class="d-block d-sm-none">
                        <div class="mobile-time-card p-3 rounded-3 text-center bg-white border">
                            <div class="clock-time text-primary" style="font-size: 24px; font-weight: 800;">
                                {{ \Carbon\Carbon::now($branchTimezone ?? 'Asia/Jakarta')->translatedFormat('H:i') }} WIB
                            </div>
                            <div class="text-muted small" style="font-size: 11.5px;">
                                {{ \Carbon\Carbon::now($branchTimezone ?? 'Asia/Jakarta')->translatedFormat('l, d F Y') }}
                            </div>
                        </div>
                    </div>

                    {{-- PULANG MODE: SHIFT SUMMARY CARD --}}
                    @if ($mode == 'pulang' && isset($attendance))
                        @php
                            $checkInTime = \Carbon\Carbon::parse($attendance->check_in_time)->timezone($branchTimezone ?? 'Asia/Jakarta');
                            $nowLocal = \Carbon\Carbon::now($branchTimezone ?? 'Asia/Jakarta');
                            $diffHours = $checkInTime->diffInHours($nowLocal);
                            $diffMinutes = $checkInTime->diffInMinutes($nowLocal) % 60;
                        @endphp
                        <div class="shift-summary-card p-3 rounded-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center text-slate-700 fw-bold" style="font-size: 12.5px;">
                                    <i class="mdi mdi-clock-check-outline text-primary fs-5 me-1.5"></i>
                                    <span>Ringkasan Shift Berjalan</span>
                                </div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-0.5" style="font-size: 10px; font-weight: 700;">
                                    Aktif
                                </span>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="p-2 rounded-2 bg-white border">
                                        <div class="text-muted" style="font-size: 10.5px; font-weight: 600;">WAKTU MASUK</div>
                                        <div class="fw-bold text-slate-900" style="font-size: 15px;">
                                            {{ $checkInTime->format('H:i') }} <span style="font-size: 11px;">WIB</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 rounded-2 bg-white border">
                                        <div class="text-muted" style="font-size: 10.5px; font-weight: 600;">DURASI KERJA</div>
                                        <div class="fw-bold text-success" style="font-size: 15px;">
                                            {{ $diffHours }}j {{ $diffMinutes }}m
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- VERIFICATION CHECKLIST STEPPER --}}
                    <div class="checklist-stepper-card p-3 rounded-3 bg-white border">
                        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                            <div class="fw-bold text-slate-900" style="font-size: 12.5px;">
                                <i class="mdi mdi-checkbox-marked-circle-outline text-primary me-1"></i> Validasi Sistem
                            </div>
                            <span class="text-muted small" style="font-size: 11px;">Lengkapi 2 Langkah</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between py-1">
                            <div class="d-flex align-items-center" style="font-size: 12px;">
                                <span class="step-circle me-2" id="step-photo-circle">1</span>
                                <span class="fw-semibold text-slate-800">Foto Biometrik Wajah</span>
                            </div>
                            <span class="badge-step-status" id="step-photo-status">Belum Diambil</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between py-1 mt-1">
                            <div class="d-flex align-items-center" style="font-size: 12px;">
                                <span class="step-circle me-2" id="step-gps-circle">2</span>
                                <span class="fw-semibold text-slate-800">Geolokasi GPS Karyawan</span>
                            </div>
                            <span class="badge-step-status" id="step-gps-status">Mencari...</span>
                        </div>
                    </div>

                    {{-- GEOLOCATION RADAR CARD --}}
                    <div class="modern-console-card p-3 rounded-3 bg-white border">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                <div class="radar-icon-box text-primary me-2">
                                    <i class="mdi mdi-crosshairs-gps"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-slate-900" style="font-size: 13px;">Titik Koordinat Lokasi</h6>
                                    <small class="text-muted" style="font-size: 11px;">Penempatan: {{ Auth::user()->branch->name ?? 'Cabang Pusat' }}</small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-refresh-gps" onclick="getLocation(true)" title="Perbarui Titik Lokasi">
                                <i class="mdi mdi-refresh me-1"></i> Perbarui
                            </button>
                        </div>

                        <div class="p-2.5 rounded-2 bg-slate-50 border d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center overflow-hidden">
                                <i class="mdi mdi-map-marker text-rose-600 fs-5 me-1.5 flex-shrink-0"></i>
                                <span class="font-monospace fw-semibold text-slate-800 text-truncate" id="coordinates-display" style="font-size: 12.5px;">
                                    Mencari sinyal GPS...
                                </span>
                            </div>
                            <span class="info-badge" id="gps-accuracy-badge">
                                <i class="mdi mdi-loading mdi-spin"></i>
                            </span>
                        </div>

                        <div class="d-none text-danger mt-1.5 small" id="gps-permission-help" style="font-size: 11px;">
                            <i class="mdi mdi-alert-circle-outline me-1"></i> Izin lokasi ditolak. Silahkan aktifkan izin lokasi di ikon gembok browser (samping URL) lalu klik <strong>Perbarui</strong>.
                        </div>
                    </div>

                    {{-- NOTES CARD --}}
                    <div class="modern-console-card p-3 rounded-3 bg-white border">
                        <label for="notes-input" class="form-label d-flex align-items-center justify-content-between mb-1.5" style="font-size: 12px; font-weight: 700; color: #475569;">
                            <span><i class="mdi mdi-text-box-outline me-1"></i> Catatan Kehadiran (Opsional)</span>
                            <span class="text-muted fw-normal" style="font-size: 10.5px;">Maksimal 1000 huruf</span>
                        </label>
                        <textarea name="notes" id="notes-input" rows="2" class="form-control modern-textarea" placeholder="Contoh: Lembur pekerjaan, pergantian shift, serah terima tugas..."></textarea>
                    </div>

                    {{-- SUBMIT SWIPE SLIDER SECTION --}}
                    <div class="submit-action-card p-3 rounded-3 bg-white border">
                        <div class="slide-track disabled" id="slide-track">
                            <div class="slide-progress" id="slide-progress"></div>
                            <div class="slide-text" id="slide-text">AMBIL FOTO TERLEBIH DAHULU</div>
                            <div class="slide-thumb" id="slide-thumb">
                                <i class="mdi mdi-chevron-double-right"></i>
                            </div>
                        </div>

                        <button type="button" class="btn btn-primary w-100 py-2.5 mt-2 fw-bold d-none align-items-center justify-content-center shadow-sm" id="btn-fallback-submit" onclick="submitAttendanceNow()" style="border-radius: 12px; font-size: 13.5px;">
                            <i class="mdi mdi-check-circle me-1.5"></i> Kirim Absen Sekarang
                        </button>

                        <div class="text-center text-muted mt-2" style="font-size: 11px;">
                            <i class="mdi mdi-shield-check-outline me-1 text-primary"></i> Data kehadiran terenkripsi & diverifikasi server secara otomatis.
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>

<canvas id="capture-canvas" class="d-none"></canvas>
@endsection

@push('styles')
<style>
    /* =========================================================================
       MODERN AESTHETIC BIOMETRIC ATTENDANCE CONSOLE (ABSEN MANDIRI)
       ========================================================================= */
    :root {
        --console-bg: #f8fafc;
        --console-dark: #090d16;
        --console-dark-card: #0f172a;
        --console-border: #e2e8f0;
        --console-blue: #2563eb;
        --console-emerald: #10b981;
        --console-amber: #f59e0b;
        --console-rose: #ef4444;
    }

    .absen-page-wrapper {
        width: 100%;
        max-width: 1140px;
        margin: 0 auto;
        padding-bottom: 40px;
    }

    /* Standalone Color & Margin Utilities */
    .bg-slate-50 { background-color: #f8fafc !important; }
    .bg-slate-100 { background-color: #f1f5f9 !important; }
    .text-slate-900 { color: #0f172a !important; }
    .text-slate-800 { color: #1e293b !important; }
    .text-slate-700 { color: #334155 !important; }
    .text-slate-600 { color: #475569 !important; }
    .text-slate-300 { color: #cbd5e1 !important; }
    .text-rose-600 { color: #e11d48 !important; }

    .me-1 { margin-right: 0.25rem !important; }
    .me-1\.5 { margin-right: 0.375rem !important; }
    .me-2 { margin-right: 0.5rem !important; }
    .me-3 { margin-right: 1rem !important; }

    /* --- TOP CONSOLE BAR --- */
    .btn-console-back {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        border: 1px solid var(--console-border);
        border-radius: 12px;
        color: #334155;
        font-size: 1.25rem;
        text-decoration: none !important;
        transition: all 0.15s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .btn-console-back:hover {
        background: #f1f5f9;
        color: #0f172a;
        transform: translateX(-2px);
    }

    .mode-badge {
        font-size: 12px;
        font-weight: 800;
        padding: 5px 12px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        letter-spacing: 0.02em;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .mode-badge.masuk {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }
    .mode-badge.pulang {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    .pulse-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 6px;
        background: currentColor;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulseDot 1.8s infinite;
    }
    @keyframes pulseDot {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    .tech-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
    }
    .tech-badge.ai {
        background: #0f172a;
        color: #38bdf8;
        border: 1px solid #1e293b;
    }
    .tech-badge.manual {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    /* Live Digital Clock */
    .live-clock-card {
        background: #ffffff;
        border: 1px solid var(--console-border);
        border-radius: 12px;
        padding: 6px 14px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .clock-time {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: 0.05em;
        line-height: 1.1;
    }
    .clock-date {
        font-size: 11px;
        color: #64748b;
        font-weight: 600;
    }
    .badge-tz {
        background: #f1f5f9;
        color: #475569;
        padding: 1px 5px;
        border-radius: 4px;
        font-size: 9.5px;
        font-weight: 700;
    }

    /* Warning KTP Banner */
    .ktp-warning-banner {
        background: #fffbeb;
        border: 1px solid #fcd34d;
        border-left: 4px solid #f59e0b;
        border-radius: 12px;
        padding: 12px 16px;
    }
    .warning-icon-box {
        font-size: 24px;
        color: #d97706;
        line-height: 1;
    }

    /* --- BIOMETRIC CAMERA VIEWFINDER --- */
    .biometric-camera-card {
        background: #090d16;
        border: 1px solid #1e293b;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 15px 35px -5px rgba(15, 23, 42, 0.25);
    }

    .viewfinder-wrapper {
        position: relative;
        width: 100%;
        aspect-ratio: 3/4;
        background: #020617;
        overflow: hidden;
    }

    #video-feed {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scaleX(-1);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    #video-feed.ready {
        opacity: 1;
    }

    /* Hologram Scan Line */
    .hologram-scan-line {
        position: absolute;
        left: 5%;
        right: 5%;
        height: 3px;
        background: linear-gradient(90deg, transparent, #38bdf8, #10b981, transparent);
        box-shadow: 0 0 16px #38bdf8;
        animation: holoScan 2.6s ease-in-out infinite;
        z-index: 5;
    }
    @keyframes holoScan {
        0%, 100% { top: 10%; opacity: 0; }
        15%, 85% { opacity: 1; }
        50% { top: 88%; }
    }

    /* HUD Face Frame */
    .biometric-hud-frame {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        z-index: 4;
    }
    .hud-svg {
        width: 82%;
        height: 82%;
    }
    .hud-bracket {
        fill: none;
        stroke: rgba(255, 255, 255, 0.4);
        stroke-width: 4px;
        stroke-linecap: round;
        transition: stroke 0.25s, filter 0.25s;
    }
    .hud-oval {
        fill: none;
        stroke: rgba(255, 255, 255, 0.2);
        stroke-width: 2px;
        stroke-dasharray: 6 6;
        transition: stroke 0.25s;
    }
    .biometric-hud-frame.active .hud-bracket {
        stroke: #10b981;
        filter: drop-shadow(0 0 8px rgba(16, 185, 129, 0.8));
    }
    .biometric-hud-frame.active .hud-oval {
        stroke: rgba(16, 185, 129, 0.6);
    }

    /* Viewfinder Top Status Pill */
    .viewfinder-top-status {
        position: absolute;
        top: 14px;
        left: 0;
        right: 0;
        display: flex;
        justify-content: center;
        z-index: 6;
        pointer-events: none;
    }
    .status-pill-hud {
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(8px);
        padding: 6px 14px;
        border-radius: 9999px;
        font-size: 11.5px;
        font-weight: 600;
        color: #e2e8f0;
        border: 1px solid rgba(255, 255, 255, 0.15);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        transition: all 0.25s ease;
    }
    .status-pill-hud.success {
        color: #34d399;
        border-color: rgba(52, 211, 153, 0.4);
        background: rgba(6, 78, 59, 0.75);
    }
    .status-pill-hud.warning {
        color: #fbbf24;
        border-color: rgba(251, 191, 36, 0.4);
        background: rgba(120, 53, 15, 0.75);
    }

    /* Start Screen Overlay */
    .viewfinder-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, #090d16 0%, #1e293b 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }
    .lens-animation-box {
        position: relative;
        width: 84px;
        height: 84px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .lens-ring {
        position: absolute;
        border-radius: 50%;
        border: 2px dashed rgba(56, 189, 248, 0.3);
    }
    .lens-ring.outer {
        inset: 0;
        animation: spinRight 12s linear infinite;
    }
    .lens-ring.middle {
        inset: 8px;
        border-color: rgba(16, 185, 129, 0.35);
        animation: spinLeft 8s linear infinite;
    }
    .lens-core {
        width: 52px;
        height: 52px;
        background: linear-gradient(135deg, #1e293b, #0f172a);
        border: 2px solid #38bdf8;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 26px;
        box-shadow: 0 0 16px rgba(56, 189, 248, 0.4);
    }
    @keyframes spinRight { 100% { transform: rotate(360deg); } }
    @keyframes spinLeft { 100% { transform: rotate(-360deg); } }

    .btn-start-camera {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #ffffff;
        font-weight: 700;
        font-size: 13.5px;
        padding: 10px 22px;
        border-radius: 12px;
        border: none;
        transition: all 0.2s ease;
    }
    .btn-start-camera:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        color: #ffffff;
        transform: translateY(-2px);
    }

    /* Result Preview Overlay */
    #result-screen {
        background: #020617;
    }
    #result-screen img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .btn-retake {
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(8px);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 9999px;
        font-weight: 700;
        font-size: 12.5px;
        padding: 8px 18px;
        transition: all 0.15s ease;
    }
    .btn-retake:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #ffffff;
        transform: scale(1.02);
    }
    .badge-verified-capture {
        background: #059669;
        color: #ffffff;
        font-weight: 700;
        font-size: 11.5px;
        padding: 5px 12px;
        border-radius: 9999px;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
    }

    /* Shutter Button & Bottom Trigger */
    .shutter-trigger-section {
        background: #090d16;
        border-top: 1px solid #1e293b;
        min-height: 98px;
    }
    .shutter-button {
        position: relative;
        width: 74px;
        height: 74px;
        border-radius: 50%;
        background: transparent;
        border: none;
        padding: 0;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .shutter-ring-outer {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        border: 4px solid #334155;
        transition: all 0.25s ease;
    }
    .shutter-core {
        width: 58px;
        height: 58px;
        border-radius: 50%;
        background: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 26px;
        transition: all 0.25s ease;
    }
    .shutter-button:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
    .shutter-button.ready .shutter-ring-outer {
        border-color: #38bdf8;
        box-shadow: 0 0 16px rgba(56, 189, 248, 0.5);
    }
    .shutter-button.ready .shutter-core {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
    }
    .shutter-button.ready:active {
        transform: scale(0.92);
    }

    .btn-retake-primary {
        background: #1e293b;
        border: 1px solid #475569;
        color: #ffffff;
        font-size: 13.5px;
        font-weight: 700;
        padding: 10px 24px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    .btn-retake-primary:hover {
        background: #334155;
        color: #ffffff;
        border-color: #64748b;
        transform: translateY(-1px);
    }

    /* --- RIGHT COLUMN / CONSOLE CARDS --- */
    .shift-summary-card {
        background: linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%);
        border: 1px solid #bfdbfe;
    }

    .checklist-stepper-card {
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .step-circle {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #475569;
        font-weight: 700;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .step-circle.done {
        background: #10b981;
        color: #ffffff;
    }
    .badge-step-status {
        font-size: 10.5px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 6px;
        background: #f1f5f9;
        color: #64748b;
    }
    .badge-step-status.done {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .radar-icon-box {
        width: 34px;
        height: 34px;
        background: #eff6ff;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .btn-refresh-gps {
        font-size: 11.5px;
        font-weight: 600;
        border-radius: 6px;
        padding: 4px 10px;
    }

    .info-badge {
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        background: #e2e8f0;
        color: #475569;
        flex-shrink: 0;
    }
    .info-badge.success {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }
    .info-badge.error {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .modern-textarea {
        border-color: var(--console-border);
        border-radius: 8px;
        font-size: 13px;
        transition: all 0.15s ease;
        resize: none;
    }
    .modern-textarea:focus {
        border-color: var(--console-blue);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08);
    }

    /* Swipe To Confirm Slider */
    .slide-track {
        position: relative;
        height: 56px;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 28px;
        overflow: hidden;
        user-select: none;
        touch-action: none;
        transition: all 0.25s ease;
    }
    .slide-track.disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    .slide-track.active {
        background: #eff6ff;
        border-color: #93c5fd;
    }
    .slide-track.submitted {
        background: #10b981;
        border-color: #059669;
    }

    .slide-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.06em;
        color: #64748b;
        white-space: nowrap;
        pointer-events: none;
        transition: opacity 0.2s;
    }
    .slide-track.active .slide-text {
        color: #1d4ed8;
    }
    .slide-track.submitted .slide-text {
        color: #ffffff;
    }

    .slide-progress {
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        background: linear-gradient(90deg, #3b82f6, #2563eb);
        width: 0;
        opacity: 0.35;
        border-radius: 28px;
    }

    .slide-thumb {
        position: absolute;
        top: 3px;
        left: 3px;
        width: 48px;
        height: 48px;
        background: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #2563eb;
        font-size: 20px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
        cursor: grab;
        z-index: 2;
    }
    .slide-thumb:active {
        cursor: grabbing;
    }
    .slide-track.submitted .slide-thumb {
        color: #10b981;
    }

    /* Mobile adjustments */
    @media (max-width: 575px) {
        .absen-page-wrapper {
            padding: 0 4px 30px 4px;
        }
        .console-top-bar {
            margin-bottom: 12px;
        }
        .shutter-button {
            width: 68px;
            height: 68px;
        }
        .shutter-core {
            width: 52px;
            height: 52px;
            font-size: 22px;
        }
    }
</style>
@endpush

@push('scripts')
{{-- FACE API JS --}}
<script defer src="https://cdn.jsdelivr.net/npm/face-api.js/dist/face-api.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // === CONFIGURATIONS ===
        const AI_INPUT_SIZE = 160;
        const AI_SCORE_THRESHOLD = 0.45;
        const DETECTION_INTERVAL = 200;

        const useAI = {{ Auth::user()->use_face_recognition ? 'true' : 'false' }};
        const assetPath = "{{ asset('public/models') }}";

        // DOM Elements
        const videoFeed = document.getElementById('video-feed');
        const startScreen = document.getElementById('start-screen');
        const resultScreen = document.getElementById('result-screen');
        const startBtn = document.getElementById('start-camera-btn');
        const loadingState = document.getElementById('loading-state');
        const captureBtn = document.getElementById('capture-btn');
        const capturedActionGroup = document.getElementById('captured-action-group');
        const retakeBtn = document.getElementById('retake-btn');
        const bottomRetakeBtn = document.getElementById('bottom-retake-btn');
        const previewImage = document.getElementById('preview-image');
        const photoInputHidden = document.getElementById('photo-input-hidden');
        const canvas = document.getElementById('capture-canvas');

        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const accInput = document.getElementById('accuracy');
        const coordDisplay = document.getElementById('coordinates-display');
        const accBadge = document.getElementById('gps-accuracy-badge');
        const gpsPermissionHelp = document.getElementById('gps-permission-help');

        const faceStatus = document.getElementById('face-status');
        const faceFrame = document.getElementById('face-frame');
        const aiScanLine = document.getElementById('ai-scan-line');
        const slideTrack = document.getElementById('slide-track');
        const slideThumb = document.getElementById('slide-thumb');
        const slideText = document.getElementById('slide-text');
        const slideProgress = document.getElementById('slide-progress');
        const fallbackSubmitBtn = document.getElementById('btn-fallback-submit');
        const form = document.getElementById('attendance-form');

        const stepPhotoCircle = document.getElementById('step-photo-circle');
        const stepPhotoStatus = document.getElementById('step-photo-status');
        const stepGpsCircle = document.getElementById('step-gps-circle');
        const stepGpsStatus = document.getElementById('step-gps-status');

        // State variables
        let streamRef = null;
        let isProcessingAI = false;
        let isFaceValid = false;
        let modelsLoaded = false;
        let animationFrameId = null;

        // === REAL-TIME DIGITAL CLOCK ===
        function updateLiveClock() {
            const clockEl = document.getElementById('live-digital-clock');
            if (!clockEl) return;
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            clockEl.textContent = `${h}:${m}:${s}`;
        }
        setInterval(updateLiveClock, 1000);
        updateLiveClock();

        // === PRE-LOAD AI MODEL IN BACKGROUND ===
        function tryLoadModel() {
            if (typeof faceapi !== 'undefined') {
                faceapi.nets.tinyFaceDetector.loadFromUri(assetPath)
                    .then(() => {
                        modelsLoaded = true;
                        console.log('AI face detector loaded successfully.');
                    })
                    .catch(err => {
                        console.warn('Background AI load failed, retrying on click:', err);
                    });
            }
        }

        if (useAI) {
            if (typeof faceapi !== 'undefined') {
                tryLoadModel();
            } else {
                const checkInterval = setInterval(() => {
                    if (typeof faceapi !== 'undefined') {
                        clearInterval(checkInterval);
                        tryLoadModel();
                    }
                }, 150);
            }
        }

        // === START CAMERA BUTTON CLICK ===
        startBtn.addEventListener('click', async () => {
            startBtn.classList.add('d-none');
            loadingState.classList.remove('d-none');
            getLocation(true);

            if (useAI && !modelsLoaded) {
                try {
                    await faceapi.nets.tinyFaceDetector.loadFromUri(assetPath);
                    modelsLoaded = true;
                } catch (err) {
                    console.error('AI loading error:', err);
                    alert('Gagal memuat modul AI wajah. Beralih ke mode kamera manual.');
                    initCamera(false);
                    return;
                }
            }

            initCamera(useAI && modelsLoaded);
        });

        function initCamera(enableAI = true) {
            const constraints = {
                video: {
                    facingMode: 'user',
                    width: { ideal: 1080 },
                    height: { ideal: 1440 },
                    frameRate: { ideal: 24 }
                }
            };

            navigator.mediaDevices.getUserMedia(constraints)
                .then(stream => {
                    streamRef = stream;
                    videoFeed.srcObject = stream;

                    videoFeed.onloadeddata = () => {
                        videoFeed.play();
                        startScreen.classList.add('d-none');
                        videoFeed.classList.add('ready');

                        if (enableAI) {
                            if (aiScanLine) aiScanLine.classList.remove('d-none');
                            startFaceDetectionLoop();
                        } else {
                            setReadyToCapture(true, 'Kamera Siap Digunakan', 'success');
                        }
                    };
                })
                .catch(err => {
                    alert('Tidak dapat mengakses kamera: ' + err.message + '\nPastikan izin kamera telah diberikan di browser.');
                    startBtn.classList.remove('d-none');
                    loadingState.classList.add('d-none');
                });
        }

        // === AI DETECTION LOOP ===
        function startFaceDetectionLoop() {
            const options = new faceapi.TinyFaceDetectorOptions({
                inputSize: AI_INPUT_SIZE,
                scoreThreshold: AI_SCORE_THRESHOLD
            });

            const runDetection = async () => {
                if (!streamRef || videoFeed.paused || videoFeed.ended) return;
                if (isProcessingAI) {
                    scheduleNextDetection();
                    return;
                }

                isProcessingAI = true;

                try {
                    const detections = await faceapi.detectAllFaces(videoFeed, options);

                    if (detections.length > 0 && detections[0].score > AI_SCORE_THRESHOLD) {
                        if (!isFaceValid) {
                            isFaceValid = true;
                            faceFrame.classList.add('active');
                            setReadyToCapture(true, 'Wajah Terverifikasi', 'success');
                        }
                    } else {
                        if (isFaceValid) {
                            isFaceValid = false;
                            faceFrame.classList.remove('active');
                            setReadyToCapture(false, 'Posisikan Wajah di Bingkai', 'warning');
                        }
                    }
                } catch (e) {
                    console.log('Detection step skipped');
                }

                isProcessingAI = false;
                scheduleNextDetection();
            };

            const scheduleNextDetection = () => {
                if ('requestIdleCallback' in window) {
                    requestIdleCallback(() => {
                        setTimeout(() => {
                            animationFrameId = requestAnimationFrame(runDetection);
                        }, DETECTION_INTERVAL);
                    }, { timeout: 500 });
                } else {
                    setTimeout(() => {
                        animationFrameId = requestAnimationFrame(runDetection);
                    }, DETECTION_INTERVAL);
                }
            };

            runDetection();
        }

        function setReadyToCapture(ready, message, type) {
            if (ready) {
                captureBtn.disabled = false;
                captureBtn.classList.add('ready');
                faceStatus.innerHTML = `<i class="mdi mdi-check-circle"></i><span>${message}</span>`;
                faceStatus.className = 'status-pill-hud success';
            } else {
                captureBtn.disabled = true;
                captureBtn.classList.remove('ready');
                faceStatus.innerHTML = `<i class="mdi mdi-face-recognition"></i><span>${message}</span>`;
                faceStatus.className = 'status-pill-hud ' + type;
            }
        }

        // === CAPTURE PHOTO ===
        captureBtn.addEventListener('click', () => {
            // Guard: If camera is already stopped or preview is active, trigger retake instead of capturing black screen!
            if (!streamRef || videoFeed.paused || videoFeed.ended || !resultScreen.classList.contains('d-none')) {
                triggerRetake();
                return;
            }

            if (useAI && !isFaceValid) return;
            getLocation(true);

            captureBtn.style.transform = 'scale(0.9)';
            setTimeout(() => captureBtn.style.transform = '', 100);

            const width = videoFeed.videoWidth || 1080;
            const height = videoFeed.videoHeight || 1440;
            canvas.width = width;
            canvas.height = height;

            const ctx = canvas.getContext('2d');
            ctx.save();
            ctx.scale(-1, 1);
            ctx.drawImage(videoFeed, -width, 0, width, height);
            ctx.restore();

            canvas.toBlob(blob => {
                const file = new File([blob], 'attendance_' + Date.now() + '.jpg', { type: 'image/jpeg' });
                const dt = new DataTransfer();
                dt.items.add(file);
                photoInputHidden.files = dt.files;

                previewImage.src = URL.createObjectURL(blob);
                stopCamera();
                resultScreen.classList.remove('d-none');

                // Toggle bottom shutter button to retake button
                if (captureBtn) captureBtn.classList.add('d-none');
                if (capturedActionGroup) capturedActionGroup.classList.remove('d-none');

                // Update Step 1 Status
                if (stepPhotoCircle) stepPhotoCircle.classList.add('done');
                if (stepPhotoStatus) {
                    stepPhotoStatus.textContent = 'Selesai';
                    stepPhotoStatus.className = 'badge-step-status done';
                }

                checkGlobalValidity();
            }, 'image/jpeg', 0.88);
        });

        function stopCamera() {
            if (animationFrameId) cancelAnimationFrame(animationFrameId);
            if (streamRef) {
                streamRef.getTracks().forEach(t => t.stop());
                streamRef = null;
            }
        }

        // === TRIGGER RETAKE (AMBIL ULANG FOTO) ===
        function triggerRetake() {
            resultScreen.classList.add('d-none');
            if (capturedActionGroup) capturedActionGroup.classList.add('d-none');
            if (captureBtn) {
                captureBtn.classList.remove('d-none');
                captureBtn.disabled = true;
                captureBtn.classList.remove('ready');
            }
            photoInputHidden.value = '';
            isFaceValid = false;

            // Reset Step 1 Status
            if (stepPhotoCircle) stepPhotoCircle.classList.remove('done');
            if (stepPhotoStatus) {
                stepPhotoStatus.textContent = 'Belum Diambil';
                stepPhotoStatus.className = 'badge-step-status';
            }

            initCamera(useAI && modelsLoaded);
            checkGlobalValidity();
        }

        if (retakeBtn) retakeBtn.addEventListener('click', triggerRetake);
        if (bottomRetakeBtn) bottomRetakeBtn.addEventListener('click', triggerRetake);

        // === GPS GEOLOCATION ===
        function getLocation(highAccuracy = true) {
            if (!navigator.geolocation) {
                coordDisplay.textContent = 'GPS tidak didukung perangkat';
                return;
            }

            coordDisplay.textContent = 'Mencari sinyal GPS...';
            accBadge.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i>';
            accBadge.className = 'info-badge';
            if (gpsPermissionHelp) gpsPermissionHelp.classList.add('d-none');

            navigator.geolocation.getCurrentPosition(
                pos => {
                    const { latitude, longitude, accuracy } = pos.coords;
                    latInput.value = latitude;
                    lngInput.value = longitude;
                    accInput.value = accuracy;

                    coordDisplay.textContent = `${latitude.toFixed(5)}, ${longitude.toFixed(5)}`;
                    accBadge.textContent = `±${Math.round(accuracy)}m`;
                    accBadge.className = 'info-badge success';
                    if (gpsPermissionHelp) gpsPermissionHelp.classList.add('d-none');

                    // Update Step 2 Status
                    if (stepGpsCircle) stepGpsCircle.classList.add('done');
                    if (stepGpsStatus) {
                        stepGpsStatus.textContent = 'Terkunci';
                        stepGpsStatus.className = 'badge-step-status done';
                    }

                    checkGlobalValidity();
                },
                err => {
                    if (highAccuracy && err.code === 3) {
                        getLocation(false);
                        return;
                    }
                    if (err.code === 1) {
                        coordDisplay.textContent = 'Izin GPS Ditolak di Browser';
                        accBadge.textContent = 'Izin Ditolak';
                        accBadge.className = 'info-badge error';
                        if (gpsPermissionHelp) gpsPermissionHelp.classList.remove('d-none');
                    } else {
                        coordDisplay.textContent = 'Gagal mendeteksi lokasi GPS';
                        accBadge.textContent = 'Error';
                        accBadge.className = 'info-badge error';
                    }

                    if (stepGpsStatus) {
                        stepGpsStatus.textContent = err.code === 1 ? 'Izin Ditolak' : 'Gagal';
                        stepGpsStatus.className = 'badge-step-status text-danger';
                    }
                },
                { enableHighAccuracy: highAccuracy, timeout: 15000, maximumAge: 0 }
            );
        }

        // Auto trigger GPS on initial page load
        getLocation(true);

        // === VALIDITY CHECK & SLIDER ENABLER ===
        function checkGlobalValidity() {
            const hasPhoto = photoInputHidden.files && photoInputHidden.files.length > 0;
            const hasLoc = latInput.value !== '';

            if (hasPhoto && hasLoc) {
                slideTrack.classList.remove('disabled');
                slideTrack.classList.add('active');
                slideText.textContent = 'GESER UNTUK ABSEN';
                if (fallbackSubmitBtn) fallbackSubmitBtn.classList.remove('d-none');
            } else {
                slideTrack.classList.add('disabled');
                slideTrack.classList.remove('active');
                if (fallbackSubmitBtn) fallbackSubmitBtn.classList.add('d-none');
                slideText.textContent = hasPhoto ? 'MENUNGGU LOKASI GPS...' : 'AMBIL FOTO TERLEBIH DAHULU';
            }
        }

        // === DIRECT SUBMIT FALLBACK ===
        window.submitAttendanceNow = function() {
            if (latInput.value === '') {
                alert('Lokasi GPS belum terkunci. Klik tombol "Perbarui" pada kotak lokasi atau izinkan GPS browser.');
                return;
            }
            if (!photoInputHidden.files || photoInputHidden.files.length === 0) {
                alert('Silahkan ambil foto kehadiran terlebih dahulu.');
                return;
            }
            if (!form.classList.contains('submitting')) {
                form.classList.add('submitting');
                if (fallbackSubmitBtn) {
                    fallbackSubmitBtn.disabled = true;
                    fallbackSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5"></span> Mengirim Data...';
                }
                form.submit();
            }
        };

        // === SLIDER INTERACTION (TOUCH & MOUSE) ===
        let isDragging = false, startX, currentX, maxSlide;
        const updateSliderWidth = () => { 
            if (slideTrack && slideThumb) {
                maxSlide = slideTrack.offsetWidth - slideThumb.offsetWidth - 6; 
            }
        };
        window.addEventListener('resize', () => setTimeout(updateSliderWidth, 100));
        setTimeout(updateSliderWidth, 200);

        const handleStart = e => {
            if (slideTrack.classList.contains('disabled')) return;
            isDragging = true;
            startX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
            slideThumb.style.transition = 'none';
        };

        const handleMove = e => {
            if (!isDragging) return;
            const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
            let move = clientX - startX;
            move = Math.max(0, Math.min(move, maxSlide));
            currentX = move;
            slideThumb.style.transform = `translateX(${move}px)`;
            slideProgress.style.width = `${move + 50}px`;
            slideText.style.opacity = 1 - (move / maxSlide);
        };

        const handleEnd = () => {
            if (!isDragging) return;
            isDragging = false;

            if (currentX >= maxSlide * 0.82) {
                slideThumb.style.transform = `translateX(${maxSlide}px)`;
                slideProgress.style.width = '100%';
                slideTrack.classList.add('submitted');
                slideThumb.innerHTML = '<i class="mdi mdi-check"></i>';
                slideText.textContent = 'MENGIRIM ABSENSI...';
                slideText.style.opacity = '1';

                window.submitAttendanceNow();
            } else {
                resetSlider();
            }
        };

        const resetSlider = () => {
            slideThumb.style.transition = 'transform 0.3s cubic-bezier(0.2, 0.9, 0.3, 1)';
            slideThumb.style.transform = 'translateX(0)';
            slideProgress.style.width = '0';
            slideText.style.opacity = '1';
            slideTrack.classList.remove('submitted');
            slideThumb.innerHTML = '<i class="mdi mdi-chevron-double-right"></i>';
            form.classList.remove('submitting');
        };

        slideThumb.addEventListener('mousedown', handleStart);
        slideThumb.addEventListener('touchstart', handleStart, { passive: true });
        window.addEventListener('mousemove', handleMove);
        window.addEventListener('touchmove', handleMove, { passive: true });
        window.addEventListener('mouseup', handleEnd);
        window.addEventListener('touchend', handleEnd);
    });
</script>
@endpush