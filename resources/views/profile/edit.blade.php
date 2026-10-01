@extends('layout.master')

@section('title', 'Profil Saya - PStore')
@section('heading', 'Profil Saya')

@section('content')
<div class="profile-page-wrapper">
    {{-- ALERT NOTIFIKASI --}}
    @if (session('success'))
        <div class="alert profile-alert profile-alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <div class="alert-icon-box text-success flex-shrink-0">
                <i class="mdi mdi-check-circle-outline"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold text-slate-900" style="font-size: 13px;">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert profile-alert profile-alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <div class="alert-icon-box text-danger flex-shrink-0">
                <i class="mdi mdi-alert-circle-outline"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold text-slate-900" style="font-size: 13px;">{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert profile-alert profile-alert-danger alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="mdi mdi-alert-circle-outline text-danger fs-5"></i>
                <span class="fw-bold text-slate-900" style="font-size: 13px;">Terdapat beberapa kesalahan input:</span>
            </div>
            <ul class="mb-0 ps-3 text-danger small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- =========================================================================
         1. HERO PROFILE SUMMARY BANNER
         ========================================================================= --}}
    <div class="profile-hero-card mb-4">
        <div class="profile-hero-cover">
            <div class="profile-hero-pattern"></div>
        </div>
        <div class="profile-hero-body">
            <div class="d-flex flex-column flex-md-row align-items-center align-items-md-end gap-3 gap-md-4">
                {{-- AVATAR WITH BADGES & CAMERA ACTION --}}
                <div class="profile-avatar-wrapper position-relative flex-shrink-0">
                    <div class="profile-avatar-circle" data-bs-toggle="modal" data-bs-target="#profilePhotoModal" title="Klik untuk memperbesar foto">
                        @if($user->profile_photo_path)
                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                 alt="{{ $user->name }}" class="profile-avatar-img">
                        @else
                            <div class="profile-avatar-initials">
                                {{ getInitials($user->name) }}
                            </div>
                        @endif
                    </div>

                    @if($user->is_verified)
                        <span class="profile-verified-badge" title="Akun Terverifikasi">
                            <i class="mdi mdi-check-decagram"></i>
                        </span>
                    @endif

                    {{-- Camera Action Trigger --}}
                    @if(!$user->profile_photo_path)
                        <label for="profile_photo_quick" class="profile-avatar-camera-btn" title="Upload Foto Profil">
                            <i class="mdi mdi-camera-plus"></i>
                        </label>
                        <form id="quickPhotoForm" action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data" class="d-none">
                            @csrf @method('PUT')
                            <input type="file" name="profile_photo" id="profile_photo_quick" accept="image/*" onchange="document.getElementById('quickPhotoForm').submit()">
                        </form>
                    @else
                        @if($user->photo_request_status == 'pending')
                            <span class="profile-avatar-camera-btn pending" title="Pengajuan Foto Sedang Diproses">
                                <i class="mdi mdi-clock-outline"></i>
                            </span>
                        @else
                            <button type="button" class="profile-avatar-camera-btn" data-bs-toggle="modal" data-bs-target="#changeProfilePhotoModal" title="Ganti Foto Profil">
                                <i class="mdi mdi-camera"></i>
                            </button>
                        @endif
                    @endif
                </div>

                {{-- USER META INFO --}}
                <div class="profile-hero-info flex-grow-1 text-center text-md-start">
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2 mb-1">
                        <h2 class="profile-name mb-0">{{ $user->name }}</h2>
                        @if($user->is_verified)
                            <span class="badge-status-pill badge-verified">
                                <i class="mdi mdi-shield-check me-1"></i>Akun Terverifikasi
                            </span>
                        @else
                            <span class="badge-status-pill badge-unverified">
                                <i class="mdi mdi-shield-outline me-1"></i>User Biasa
                            </span>
                        @endif
                    </div>

                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2 mt-2">
                        <span class="badge-role-pill">
                            <i class="mdi mdi-badge-account-horizontal-outline me-1"></i>{{ strtoupper(str_replace('_', ' ', $user->role)) }}
                        </span>
                        <span class="badge-meta-pill">
                            <i class="mdi mdi-office-building-marker me-1"></i>{{ $user->branch->name ?? 'Pusat / Semua Cabang' }}
                        </span>
                        <span class="badge-meta-pill">
                            <i class="mdi mdi-sitemap me-1"></i>{{ $user->division->name ?? 'Headquarters' }}
                        </span>
                        <span class="badge-meta-pill">
                            <i class="mdi mdi-calendar-check me-1"></i>Bergabung {{ $user->hire_date ? \Carbon\Carbon::parse($user->hire_date)->translatedFormat('d M Y') : '-' }}
                        </span>
                    </div>
                </div>

                {{-- HERO ACTION BUTTONS --}}
                <div class="profile-hero-actions d-flex flex-wrap align-items-center justify-content-center gap-2 mt-3 mt-md-0">
                    @if(!$user->profile_photo_path)
                        <label for="profile_photo_btn" class="btn btn-sm btn-profile-action" style="cursor: pointer;">
                            <i class="mdi mdi-camera-plus me-1 text-primary"></i> Upload Foto
                        </label>
                        <form id="heroPhotoForm" action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data" class="d-none">
                            @csrf @method('PUT')
                            <input type="file" name="profile_photo" id="profile_photo_btn" accept="image/*" onchange="document.getElementById('heroPhotoForm').submit()">
                        </form>
                    @else
                        @if($user->photo_request_status == 'pending')
                            <button type="button" class="btn btn-sm btn-profile-action text-warning border-warning" disabled>
                                <i class="mdi mdi-clock-outline me-1"></i> Foto Diproses
                            </button>
                        @else
                            <button type="button" class="btn btn-sm btn-profile-action" data-bs-toggle="modal" data-bs-target="#changeProfilePhotoModal">
                                <i class="mdi mdi-camera-retake-outline me-1 text-primary"></i> Ganti Foto
                            </button>
                        @endif
                    @endif

                    <a href="{{ route('attendance.history') }}" class="btn btn-sm btn-profile-action">
                        <i class="mdi mdi-calendar-clock me-1 text-primary"></i> Riwayat Absen
                    </a>

                    <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-profile-action">
                        <i class="mdi mdi-package-variant-closed me-1 text-success"></i> Riwayat Inventaris
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================================
         2. MAIN TWO-COLUMN CONTENT GRID
         ========================================================================= --}}
    <div class="row g-4">
        {{-- ========================================================= --}}
        {{-- LEFT COLUMN: DOKUMEN KTP, PENGHARGAAN, TAUTAN CEPAT       --}}
        {{-- ========================================================= --}}
        <div class="col-lg-4 col-xl-4 order-2 order-lg-1">
            <div class="d-flex flex-column gap-4">

                {{-- CARD A: DOKUMEN KTP --}}
                <div class="modern-card">
                    <div class="modern-card-header d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="card-header-icon bg-blue-50 text-primary">
                                <i class="mdi mdi-card-account-details-outline"></i>
                            </div>
                            <div>
                                <h6 class="card-header-title mb-0">Dokumen KTP</h6>
                                <small class="text-muted" style="font-size: 11px;">Identitas resmi karyawan</small>
                            </div>
                        </div>

                        @if(!$user->ktp_photo_path)
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" style="font-size: 10px; font-weight: 600;">Belum Ada</span>
                        @elseif($user->ktp_request_status == 'pending')
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5" style="font-size: 10px; font-weight: 600;">Pending</span>
                        @elseif($user->ktp_request_status == 'rejected')
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" style="font-size: 10px; font-weight: 600;">Ditolak</span>
                        @else
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 10px; font-weight: 600;">Terverifikasi</span>
                        @endif
                    </div>

                    <div class="modern-card-body">
                        @if (!$user->ktp_photo_path)
                            {{-- State: Belum Upload KTP --}}
                            <div class="ktp-empty-state text-center p-3 border rounded-3 bg-slate-50">
                                <div class="ktp-empty-icon mb-2">
                                    <i class="mdi mdi-card-account-details text-slate-300" style="font-size: 44px;"></i>
                                </div>
                                <h6 class="text-slate-800 fw-semibold mb-1" style="font-size: 13px;">Belum Mengunggah KTP</h6>
                                <p class="text-muted small mb-3" style="font-size: 11.5px;">Unggah foto KTP Anda untuk keperluan validasi identitas dan kepegawaian resmi.</p>
                                <form action="{{ route('profile.ktp.update') }}" method="POST" enctype="multipart/form-data">
                                    @csrf @method('PUT')
                                    <label for="ktp_photo_first" class="btn btn-sm btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-1.5 py-2" style="font-size: 12.5px; cursor: pointer;">
                                        <i class="mdi mdi-upload"></i> Unggah KTP Sekarang
                                    </label>
                                    <input type="file" name="ktp_photo" id="ktp_photo_first" class="d-none" accept="image/*" onchange="this.form.submit()">
                                </form>
                            </div>
                        @else
                            {{-- State: Sudah Ada KTP --}}
                            <div class="ktp-preview-card p-3 rounded-3 mb-3 position-relative overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-1.5 text-slate-700 fw-semibold" style="font-size: 11.5px; letter-spacing: 0.02em;">
                                        <i class="mdi mdi-shield-account text-primary"></i> KARTU TANDA PENDUDUK
                                    </div>
                                    <i class="mdi mdi-chip text-warning" style="font-size: 22px;"></i>
                                </div>
                                <div class="text-slate-900 fw-bold text-truncate mb-1" style="font-size: 13.5px;">
                                    {{ $user->name }}
                                </div>
                                <div class="text-muted small" style="font-size: 11px;">
                                    Status: 
                                    @if($user->ktp_request_status == 'pending')
                                        <span class="text-warning fw-semibold">Menunggu persetujuan Admin</span>
                                    @elseif($user->ktp_request_status == 'rejected')
                                        <span class="text-danger fw-semibold">Pengajuan ganti KTP ditolak</span>
                                    @else
                                        <span class="text-success fw-semibold">Dokumen KTP Aktif & Valid</span>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex flex-column gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary w-100 d-inline-flex align-items-center justify-content-center gap-1.5 py-2" data-bs-toggle="modal" data-bs-target="#ktpModal" style="font-size: 12.5px;">
                                    <i class="mdi mdi-eye-outline"></i> Lihat KTP Saya
                                </button>

                                @if ($user->ktp_request_status == 'pending')
                                    <button class="btn btn-sm btn-secondary w-100 py-2" disabled style="font-size: 12.5px;">
                                        <i class="mdi mdi-clock-outline me-1"></i> Menunggu Verifikasi Admin
                                    </button>
                                @elseif ($user->ktp_request_status == 'rejected')
                                    <button type="button" class="btn btn-sm btn-outline-danger w-100 py-2 d-inline-flex align-items-center justify-content-center gap-1.5" data-bs-toggle="modal" data-bs-target="#changeKtpModal" style="font-size: 12.5px;">
                                        <i class="mdi mdi-refresh"></i> Ajukan Ulang KTP
                                    </button>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-secondary w-100 py-2 d-inline-flex align-items-center justify-content-center gap-1.5" data-bs-toggle="modal" data-bs-target="#changeKtpModal" style="font-size: 12.5px;">
                                        <i class="mdi mdi-file-replace-outline"></i> Ajukan Ganti KTP
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- CARD B: PENGHARGAAN & HALL OF FAME (IF AVAILABLE) --}}
                @if(isset($achievements) && $achievements->count() > 0)
                    <div class="modern-card">
                        <div class="modern-card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="card-header-icon bg-amber-50 text-warning">
                                    <i class="mdi mdi-trophy-outline"></i>
                                </div>
                                <div>
                                    <h6 class="card-header-title mb-0">Penghargaan</h6>
                                    <small class="text-muted" style="font-size: 11px;">Pencapaian kehadiran tepat waktu</small>
                                </div>
                            </div>
                            <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1" style="font-size: 10px; font-weight: 700;">
                                {{ $achievements->flatten()->count() }} Trofi
                            </span>
                        </div>

                        <div class="modern-card-body p-3">
                            <div class="achievements-scroll-area custom-dropdown-scroll" style="max-height: 280px; overflow-y: auto;">
                                @foreach($achievements as $year => $items)
                                    <div class="d-flex align-items-center gap-2 my-2">
                                        <span class="badge bg-slate-100 text-slate-700 border border-slate-200" style="font-size: 10px; font-weight: 700;">{{ $year }}</span>
                                        <div class="border-top flex-grow-1"></div>
                                    </div>
                                    @foreach($items as $award)
                                        <div class="modern-award-item rank-{{ $award->rank }} p-2.5 rounded-3 mb-2 d-flex align-items-center gap-3">
                                            <div class="award-medal-box rank-{{ $award->rank }}-bg flex-shrink-0">
                                                @if($award->rank == 1)
                                                    <i class="mdi mdi-trophy text-amber-500"></i>
                                                @elseif($award->rank == 2)
                                                    <i class="mdi mdi-medal text-slate-400"></i>
                                                @else
                                                    <i class="mdi mdi-medal-outline text-amber-700"></i>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="d-flex align-items-center justify-content-between gap-1">
                                                    <h6 class="mb-0 fw-bold text-slate-900" style="font-size: 13px;">
                                                        Juara {{ $award->rank }}
                                                    </h6>
                                                    <span class="text-muted" style="font-size: 11px;">
                                                        Bulan {{ \Carbon\Carbon::create()->month($award->month)->translatedFormat('F') }}
                                                    </span>
                                                </div>
                                                <div class="text-muted small mt-0.5 d-flex align-items-center gap-1" style="font-size: 11px;">
                                                    <i class="mdi mdi-check-circle-outline text-success"></i>
                                                    <span>{{ $award->total_attendance }} Kehadiran Tepat Waktu</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- CARD C: TAUTAN CEPAT & RIWAYAT --}}
                <div class="modern-card">
                    <div class="modern-card-header d-flex align-items-center gap-2">
                        <div class="card-header-icon bg-slate-100 text-slate-700">
                            <i class="mdi mdi-compass-outline"></i>
                        </div>
                        <div>
                            <h6 class="card-header-title mb-0">Navigasi Riwayat</h6>
                            <small class="text-muted" style="font-size: 11px;">Akses cepat riwayat data Anda</small>
                        </div>
                    </div>

                    <div class="p-2">
                        <a href="{{ route('attendance.history') }}" class="modern-quick-link d-flex align-items-center justify-content-between p-2.5 rounded-2 text-decoration-none">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="quick-link-icon bg-blue-50 text-primary">
                                    <i class="mdi mdi-calendar-clock"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-slate-900" style="font-size: 13px;">Riwayat Absensi</div>
                                    <small class="text-muted" style="font-size: 11px;">Catatan presensi harian & jam kerja</small>
                                </div>
                            </div>
                            <i class="mdi mdi-chevron-right text-slate-400 fs-5"></i>
                        </a>

                        <a href="{{ route('inventory.index') }}" class="modern-quick-link d-flex align-items-center justify-content-between p-2.5 rounded-2 text-decoration-none mt-1">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="quick-link-icon bg-emerald-50 text-emerald-600">
                                    <i class="mdi mdi-package-variant-closed"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-slate-900" style="font-size: 13px;">Riwayat Inventaris</div>
                                    <small class="text-muted" style="font-size: 11px;">Barang & perangkat operasional</small>
                                </div>
                            </div>
                            <i class="mdi mdi-chevron-right text-slate-400 fs-5"></i>
                        </a>
                    </div>
                </div>

            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- RIGHT COLUMN: FORM INFORMASI PROFIL & KEAMANAN            --}}
        {{-- ========================================================= --}}
        <div class="col-lg-8 col-xl-8 order-1 order-lg-2">
            <form action="{{ route('profile.update') }}" method="POST" id="profileUpdateForm">
                @csrf @method('PUT')
                <input type="hidden" name="use_face_recognition" value="{{ $user->use_face_recognition ? '1' : '0' }}">

                <div class="d-flex flex-column gap-4">

                    {{-- SECTION 1: DATA DIRI & LOGIN --}}
                    <div class="modern-card">
                        <div class="modern-card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="card-header-icon bg-blue-50 text-primary">
                                    <i class="mdi mdi-account-circle-outline"></i>
                                </div>
                                <div>
                                    <h6 class="card-header-title mb-0">Informasi Pribadi</h6>
                                    <small class="text-muted" style="font-size: 11px;">Data identitas karyawan dan kredensial login</small>
                                </div>
                            </div>
                            <span class="badge bg-slate-100 text-slate-600 border border-slate-200" style="font-size: 10px; font-weight: 600;">Data Diri</span>
                        </div>

                        <div class="modern-card-body p-4">
                            <div class="row g-3">
                                {{-- Nama Lengkap (Sesuai KTP) --}}
                                <div class="col-md-6">
                                    <label class="form-label modern-label d-flex align-items-center justify-content-between">
                                        <span>Nama Lengkap (Sesuai KTP)</span>
                                        <span class="badge bg-slate-100 text-slate-500 rounded-pill px-2 py-0.5" style="font-size: 9.5px;"><i class="mdi mdi-lock-outline"></i> Terkunci</span>
                                    </label>
                                    <div class="input-group modern-input-group">
                                        <span class="input-group-text"><i class="mdi mdi-account-outline"></i></span>
                                        <input type="text" class="form-control modern-input bg-slate-50" name="name" value="{{ $user->name }}" readonly>
                                    </div>
                                    <div class="form-text text-muted" style="font-size: 11px;">Nama terkunci sesuai data KTP resmi. Hubungi admin bila ada perubahan.</div>
                                </div>

                                {{-- Tanggal Lahir --}}
                                <div class="col-md-6">
                                    <label class="form-label modern-label d-flex align-items-center justify-content-between">
                                        <span>Tanggal Lahir</span>
                                        <span class="badge bg-slate-100 text-slate-500 rounded-pill px-2 py-0.5" style="font-size: 9.5px;"><i class="mdi mdi-lock-outline"></i> Terkunci</span>
                                    </label>
                                    <div class="input-group modern-input-group">
                                        <span class="input-group-text"><i class="mdi mdi-calendar-month-outline"></i></span>
                                        <input type="date" class="form-control modern-input bg-slate-50" name="birth_date" 
                                               value="{{ old('birth_date', $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format('Y-m-d') : '') }}" readonly>
                                    </div>
                                    <div class="form-text text-muted" style="font-size: 11px;">Tanggal lahir tercatat di arsip kepegawaian.</div>
                                </div>

                                {{-- Email Login --}}
                                <div class="col-md-12">
                                    <label class="form-label modern-label">
                                        <span>Email Login</span>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group modern-input-group">
                                        <span class="input-group-text"><i class="mdi mdi-email-outline"></i></span>
                                        <input type="email" class="form-control modern-input @error('email') is-invalid @enderror" 
                                               name="email" value="{{ old('email', $user->email) }}" required autocomplete="email" placeholder="contoh: user@pstore.com">
                                    </div>
                                    @error('email')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text text-muted" style="font-size: 11px;">Digunakan untuk masuk ke sistem dan menerima notifikasi broadcast.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 2: INFORMASI PEKERJAAN & PENUGASAN (TILES) --}}
                    <div class="modern-card">
                        <div class="modern-card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="card-header-icon bg-indigo-50 text-indigo-600">
                                    <i class="mdi mdi-briefcase-outline"></i>
                                </div>
                                <div>
                                    <h6 class="card-header-title mb-0">Informasi Penugasan</h6>
                                    <small class="text-muted" style="font-size: 11px;">Penempatan kerja resmi yang dikelola oleh Admin</small>
                                </div>
                            </div>
                            <span class="badge bg-slate-100 text-slate-600 border border-slate-200" style="font-size: 10px; font-weight: 600;">Status Kerja</span>
                        </div>

                        <div class="modern-card-body p-4">
                            <div class="row g-3">
                                {{-- Cabang --}}
                                <div class="col-sm-6 col-md-6">
                                    <div class="info-tile p-3 rounded-3 border bg-slate-50">
                                        <div class="d-flex align-items-center gap-2 text-muted mb-1" style="font-size: 11px; font-weight: 600;">
                                            <i class="mdi mdi-store-marker-outline text-primary fs-6"></i>
                                            <span class="text-uppercase">Lokasi Cabang</span>
                                        </div>
                                        <div class="fw-bold text-slate-900" style="font-size: 13.5px;">
                                            {{ $user->branch->name ?? 'Pusat / Semua Cabang' }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Divisi --}}
                                <div class="col-sm-6 col-md-6">
                                    <div class="info-tile p-3 rounded-3 border bg-slate-50">
                                        <div class="d-flex align-items-center gap-2 text-muted mb-1" style="font-size: 11px; font-weight: 600;">
                                            <i class="mdi mdi-sitemap-outline text-primary fs-6"></i>
                                            <span class="text-uppercase">Divisi Kerja</span>
                                        </div>
                                        <div class="fw-bold text-slate-900" style="font-size: 13.5px;">
                                            {{ $user->division->name ?? 'Headquarters' }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Tanggal Bergabung --}}
                                <div class="col-sm-6 col-md-6">
                                    <div class="info-tile p-3 rounded-3 border bg-slate-50">
                                        <div class="d-flex align-items-center gap-2 text-muted mb-1" style="font-size: 11px; font-weight: 600;">
                                            <i class="mdi mdi-calendar-check-outline text-primary fs-6"></i>
                                            <span class="text-uppercase">Tanggal Bergabung</span>
                                        </div>
                                        <div class="fw-bold text-slate-900" style="font-size: 13.5px;">
                                            {{ $user->hire_date ? \Carbon\Carbon::parse($user->hire_date)->translatedFormat('d F Y') : '-' }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Status Akun --}}
                                <div class="col-sm-6 col-md-6">
                                    <div class="info-tile p-3 rounded-3 border bg-slate-50">
                                        <div class="d-flex align-items-center gap-2 text-muted mb-1" style="font-size: 11px; font-weight: 600;">
                                            <i class="mdi mdi-shield-check-outline text-primary fs-6"></i>
                                            <span class="text-uppercase">Status Akun</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 mt-0.5">
                                            @if($user->is_active)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" style="font-size: 11px; font-weight: 700;">
                                                    <i class="mdi mdi-check-circle me-1"></i> AKTIF BEKERJA
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1" style="font-size: 11px; font-weight: 700;">
                                                    <i class="mdi mdi-close-circle me-1"></i> NON-AKTIF
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 3: KONTAK & MEDIA SOSIAL --}}
                    <div class="modern-card">
                        <div class="modern-card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="card-header-icon bg-emerald-50 text-emerald-600">
                                    <i class="mdi mdi-share-variant-outline"></i>
                                </div>
                                <div>
                                    <h6 class="card-header-title mb-0">Kontak & Media Sosial</h6>
                                    <small class="text-muted" style="font-size: 11px;">Saluran komunikasi yang dapat dihubungi</small>
                                </div>
                            </div>
                            <span class="badge bg-slate-100 text-slate-600 border border-slate-200" style="font-size: 10px; font-weight: 600;">Sosmed</span>
                        </div>

                        <div class="modern-card-body p-4">
                            <div class="row g-3">
                                {{-- WhatsApp --}}
                                <div class="col-md-6">
                                    <label class="form-label modern-label">WhatsApp</label>
                                    <div class="input-group modern-input-group">
                                        <span class="input-group-text text-emerald-600"><i class="mdi mdi-whatsapp"></i></span>
                                        <input type="text" class="form-control modern-input @error('whatsapp') is-invalid @enderror" 
                                               name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}" placeholder="Contoh: 6281234567890">
                                    </div>
                                    @error('whatsapp')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Instagram --}}
                                <div class="col-md-6">
                                    <label class="form-label modern-label">Instagram</label>
                                    <div class="input-group modern-input-group">
                                        <span class="input-group-text text-pink-600"><i class="mdi mdi-instagram"></i></span>
                                        <input type="text" class="form-control modern-input @error('instagram') is-invalid @enderror" 
                                               name="instagram" value="{{ old('instagram', $user->instagram) }}" placeholder="username">
                                    </div>
                                    @error('instagram')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- TikTok --}}
                                <div class="col-md-6">
                                    <label class="form-label modern-label">TikTok</label>
                                    <div class="input-group modern-input-group">
                                        <span class="input-group-text fw-bold text-slate-800" style="font-size: 11px;">TT</span>
                                        <input type="text" class="form-control modern-input @error('tiktok') is-invalid @enderror" 
                                               name="tiktok" value="{{ old('tiktok', $user->tiktok) }}" placeholder="username">
                                    </div>
                                    @error('tiktok')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Facebook --}}
                                <div class="col-md-6">
                                    <label class="form-label modern-label">Facebook</label>
                                    <div class="input-group modern-input-group">
                                        <span class="input-group-text text-blue-600"><i class="mdi mdi-facebook"></i></span>
                                        <input type="text" class="form-control modern-input @error('facebook') is-invalid @enderror" 
                                               name="facebook" value="{{ old('facebook', $user->facebook) }}" placeholder="username atau link profil">
                                    </div>
                                    @error('facebook')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 4: KEAMANAN & GANTI PASSWORD --}}
                    <div class="modern-card">
                        <div class="modern-card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="card-header-icon bg-rose-50 text-rose-600">
                                    <i class="mdi mdi-shield-lock-outline"></i>
                                </div>
                                <div>
                                    <h6 class="card-header-title mb-0">Keamanan Akun</h6>
                                    <small class="text-muted" style="font-size: 11px;">Pengaturan kata sandi untuk melindungi akses</small>
                                </div>
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-toggle-password" style="font-size: 12px; font-weight: 600;">
                                <i class="mdi mdi-lock-reset me-1"></i> Ganti Password
                            </button>
                        </div>

                        <div class="modern-card-body p-4 d-none" id="password-container">
                            <div class="password-notice p-3 rounded-3 bg-amber-50 border border-amber-200 mb-3 text-amber-900 d-flex align-items-start gap-2">
                                <i class="mdi mdi-information-outline fs-5 text-amber-600 flex-shrink-0 mt-0.5"></i>
                                <div style="font-size: 12px;">
                                    Gunakan kata sandi baru minimal 8 karakter. Kosongkan formulir ini jika Anda tidak ingin mengubah kata sandi akun saat ini.
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label modern-label">Password Baru</label>
                                    <div class="input-group modern-input-group">
                                        <span class="input-group-text"><i class="mdi mdi-lock-outline"></i></span>
                                        <input type="password" class="form-control modern-input" name="password" id="input-password" placeholder="Minimal 8 karakter" autocomplete="new-password">
                                        <button type="button" class="btn btn-outline-secondary modern-pwd-toggle" onclick="togglePasswordVisibility('input-password', this)" tabindex="-1">
                                            <i class="mdi mdi-eye-outline"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label modern-label">Konfirmasi Password Baru</label>
                                    <div class="input-group modern-input-group">
                                        <span class="input-group-text"><i class="mdi mdi-lock-check-outline"></i></span>
                                        <input type="password" class="form-control modern-input" name="password_confirmation" id="input-password-confirm" placeholder="Ulangi password baru" autocomplete="new-password">
                                        <button type="button" class="btn btn-outline-secondary modern-pwd-toggle" onclick="togglePasswordVisibility('input-password-confirm', this)" tabindex="-1">
                                            <i class="mdi mdi-eye-outline"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ACTIONS FOOTER BAR --}}
                    <div class="profile-actions-bar d-flex align-items-center justify-content-between p-3 rounded-3 bg-white border">
                        <span class="text-muted small d-none d-sm-inline" style="font-size: 12px;">
                            <i class="mdi mdi-information-outline me-1"></i>Pastikan seluruh informasi yang Anda ubah sudah sesuai.
                        </span>
                        <div class="d-flex align-items-center gap-2 ms-auto">
                            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary px-3 py-2" style="font-size: 13px; font-weight: 600;">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-sm btn-primary px-4 py-2 d-inline-flex align-items-center gap-1.5 shadow-sm" style="font-size: 13px; font-weight: 600;">
                                <i class="mdi mdi-check"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

{{-- =========================================================================
     MODALS SECTION
     ========================================================================= --}}
{{-- 1. MODAL LIHAT FOTO PROFIL PENUH --}}
<div class="modal fade" id="profilePhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modern-modal-card border-0 bg-transparent shadow-none">
            <div class="modal-body text-center p-0 position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3" data-bs-dismiss="modal" aria-label="Close"></button>
                @if($user->profile_photo_path)
                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" class="img-fluid rounded-4 shadow-lg" style="max-height: 80vh; max-width: 100%; object-fit: contain;">
                @else
                    <div class="p-5 bg-white rounded-4 shadow-lg text-center">
                        <div class="profile-avatar-initials mx-auto mb-3" style="width: 110px; height: 110px; font-size: 40px; border-radius: 50%;">
                            {{ getInitials($user->name) }}
                        </div>
                        <h5 class="fw-bold text-slate-900 mb-1">{{ $user->name }}</h5>
                        <p class="text-muted small mb-0">Belum ada foto profil yang diunggah.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- 2. MODAL REQUEST GANTI FOTO PROFIL --}}
<div class="modal fade" id="changeProfilePhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modern-modal-card">
            <div class="modal-header border-bottom px-4 py-3 bg-slate-50">
                <div class="d-flex align-items-center gap-2">
                    <div class="card-header-icon bg-blue-50 text-primary">
                        <i class="mdi mdi-camera"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-slate-900 mb-0" style="font-size: 14px;">Ganti Foto Profil</h6>
                        <small class="text-muted" style="font-size: 11px;">Foto baru akan ditinjau Admin sebelum aktif</small>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('profile.photo.request') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="file-upload-dropzone p-4 text-center rounded-3 mb-3" id="photoDropzone">
                        <div id="photoPreviewContainer" class="d-none mb-3">
                            <img id="photoPreviewImg" src="#" alt="Preview" class="rounded-circle shadow-sm" style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #2563eb;">
                        </div>
                        <i class="mdi mdi-cloud-upload-outline text-slate-400 mb-2" id="photoUploadIcon" style="font-size: 40px;"></i>
                        <h6 class="fw-semibold text-slate-800 mb-1" style="font-size: 13px;">Pilih Berkas Foto Baru</h6>
                        <p class="text-muted small mb-3" style="font-size: 11.5px;">Mendukung format JPG, PNG, atau WEBP (Maksimal 5MB).</p>
                        <label for="modalProfilePhotoInput" class="btn btn-sm btn-outline-primary px-3 py-1.5" style="font-size: 12.5px; cursor: pointer;">
                            Pilih Foto dari Perangkat
                        </label>
                        <input type="file" name="profile_photo" id="modalProfilePhotoInput" class="d-none" accept="image/*" required onchange="previewImage(this, 'photoPreviewImg', 'photoPreviewContainer', 'photoUploadIcon')">
                    </div>
                    <div class="alert alert-info py-2 px-3 small rounded-3 mb-0" style="font-size: 11.5px;">
                        <i class="mdi mdi-information-outline me-1"></i> Setelah diajukan, foto akan ditinjau oleh Admin sebelum tampil secara publik.
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3 bg-slate-50 d-flex justify-content-between align-items-center">
                    <div>
                        @if($user->profile_photo_path)
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="if(confirm('Apakah Anda yakin ingin menghapus foto profil?')) { document.getElementById('deletePhotoForm').submit(); }">
                                <i class="mdi mdi-delete-outline me-1"></i> Hapus Foto
                            </button>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-primary px-3">
                            <i class="mdi mdi-upload me-1"></i> Kirim Pengajuan Foto
                        </button>
                    </div>
                </div>
            </form>
            @if($user->profile_photo_path)
                <form id="deletePhotoForm" action="{{ route('profile.photo.delete') }}" method="POST" class="d-none">
                    @csrf @method('DELETE')
                </form>
            @endif
        </div>
    </div>
</div>

{{-- 3. MODAL LIHAT KTP PENUH --}}
@if($user->ktp_photo_path)
    <div class="modal fade" id="ktpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modern-modal-card border-0 bg-transparent shadow-none">
                <div class="modal-body text-center p-0 position-relative">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    <img src="{{ asset('storage/' . $user->ktp_photo_path) }}" class="img-fluid rounded-4 shadow-lg" style="max-height: 85vh; max-width: 100%; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>
@endif

{{-- 4. MODAL AJUKAN GANTI KTP --}}
<div class="modal fade" id="changeKtpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modern-modal-card">
            <div class="modal-header border-bottom px-4 py-3 bg-slate-50">
                <div class="d-flex align-items-center gap-2">
                    <div class="card-header-icon bg-amber-50 text-warning">
                        <i class="mdi mdi-card-account-details-outline"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-slate-900 mb-0" style="font-size: 14px;">Pengajuan Ganti KTP</h6>
                        <small class="text-muted" style="font-size: 11px;">Unggah foto KTP resmi terbaru yang jelas</small>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('profile.ktp.request') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="file-upload-dropzone p-4 text-center rounded-3 mb-3" id="ktpDropzone">
                        <div id="ktpPreviewContainer" class="d-none mb-3">
                            <img id="ktpPreviewImg" src="#" alt="Preview" class="rounded-3 shadow-sm" style="max-height: 130px; max-width: 100%; object-fit: contain; border: 2px solid #2563eb;">
                        </div>
                        <i class="mdi mdi-file-image-outline text-slate-400 mb-2" id="ktpUploadIcon" style="font-size: 40px;"></i>
                        <h6 class="fw-semibold text-slate-800 mb-1" style="font-size: 13px;">Pilih Berkas KTP Baru</h6>
                        <p class="text-muted small mb-3" style="font-size: 11.5px;">Pastikan nomor NIK dan nama jelas terlihat (Maksimal 5MB).</p>
                        <label for="modalKtpPhotoInput" class="btn btn-sm btn-outline-primary px-3 py-1.5" style="font-size: 12.5px; cursor: pointer;">
                            Pilih Berkas KTP
                        </label>
                        <input type="file" name="ktp_photo" id="modalKtpPhotoInput" class="d-none" accept="image/*" required onchange="previewImage(this, 'ktpPreviewImg', 'ktpPreviewContainer', 'ktpUploadIcon')">
                    </div>
                    <div class="alert alert-warning py-2 px-3 small rounded-3 mb-0" style="font-size: 11.5px;">
                        <i class="mdi mdi-shield-alert-outline me-1"></i> Data KTP bersifat rahasia dan hanya digunakan untuk validasi legal kepegawaian.
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3 bg-slate-50 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3">
                        <i class="mdi mdi-upload me-1"></i> Ajukan KTP Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* =========================================================================
       MODERN AESTHETIC PROFILE STYLING & SELF-CONTAINED DESIGN SYSTEM
       ========================================================================= */
    :root {
        --profile-bg: #f8fafc;
        --profile-card: #ffffff;
        --profile-border: #e2e8f0;
        --profile-text: #0f172a;
        --profile-muted: #64748b;
        --profile-blue: #2563eb;
        --profile-blue-hover: #1d4ed8;
    }

    /* Standalone Utilities for Non-Tailwind Environments */
    .bg-slate-50 { background-color: #f8fafc !important; }
    .bg-slate-100 { background-color: #f1f5f9 !important; }
    .text-slate-900 { color: #0f172a !important; }
    .text-slate-800 { color: #1e293b !important; }
    .text-slate-700 { color: #334155 !important; }
    .text-slate-600 { color: #475569 !important; }
    .text-slate-500 { color: #64748b !important; }
    .text-slate-400 { color: #94a3b8 !important; }
    .text-slate-300 { color: #cbd5e1 !important; }
    .border-slate-200 { border-color: #e2e8f0 !important; }

    .bg-blue-50 { background-color: #eff6ff !important; }
    .text-blue-600 { color: #2563eb !important; }

    .bg-emerald-50 { background-color: #ecfdf5 !important; }
    .text-emerald-600 { color: #059669 !important; }

    .bg-indigo-50 { background-color: #eef2ff !important; }
    .text-indigo-600 { color: #4f46e5 !important; }

    .bg-rose-50 { background-color: #fff1f2 !important; }
    .text-rose-600 { color: #e11d48 !important; }

    .bg-amber-50 { background-color: #fffbeb !important; }
    .text-amber-500 { color: #f59e0b !important; }
    .text-amber-600 { color: #d97706 !important; }
    .text-amber-700 { color: #b45309 !important; }
    .text-amber-900 { color: #78350f !important; }
    .border-amber-200 { border-color: #fde68a !important; }

    .text-pink-600 { color: #db2777 !important; }
    .bg-success-subtle { background-color: #dcfce7 !important; color: #15803d !important; }
    .bg-warning-subtle { background-color: #fef9c3 !important; color: #a16207 !important; }
    .bg-danger-subtle { background-color: #fee2e2 !important; color: #b91c1c !important; }
    .rounded-4 { border-radius: 1rem !important; }

    .profile-page-wrapper {
        width: 100%;
        max-width: 1340px;
        margin: 0 auto;
        padding-bottom: 40px;
    }

    /* Alerts */
    .profile-alert {
        border-radius: 12px;
        border: 1px solid var(--profile-border);
        background: #ffffff;
        padding: 12px 16px;
    }
    .profile-alert-success {
        border-left: 4px solid #10b981;
        background: #f0fdf4;
    }
    .profile-alert-danger {
        border-left: 4px solid #ef4444;
        background: #fef2f2;
    }
    .alert-icon-box {
        font-size: 20px;
        line-height: 1;
    }

    /* --- HERO PROFILE CARD --- */
    .profile-hero-card {
        background: #ffffff;
        border: 1px solid var(--profile-border);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1px 4px 0 rgba(15, 23, 42, 0.05);
    }

    .profile-hero-cover {
        height: 120px;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        position: relative;
    }

    .profile-hero-pattern {
        position: absolute;
        inset: 0;
        opacity: 0.15;
        background-image: radial-gradient(circle, #ffffff 1px, transparent 1px);
        background-size: 16px 16px;
    }

    .profile-hero-body {
        padding: 0 24px 22px 24px;
        position: relative;
    }

    .profile-avatar-wrapper {
        margin-top: -60px;
        width: 114px;
        height: 114px;
    }

    .profile-avatar-circle {
        width: 114px;
        height: 114px;
        border-radius: 50%;
        border: 4px solid #ffffff;
        box-shadow: 0 8px 24px -4px rgba(15, 23, 42, 0.18);
        overflow: hidden;
        cursor: pointer;
        background: #0f172a;
        transition: transform 0.2s ease;
    }

    .profile-avatar-circle:hover {
        transform: scale(1.02);
    }

    .profile-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .profile-avatar-initials {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        font-weight: 700;
        color: #ffffff;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
    }

    .profile-verified-badge {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 28px;
        height: 28px;
        background: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #2563eb;
        font-size: 24px;
        line-height: 1;
        z-index: 5;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);
    }

    .profile-avatar-camera-btn {
        position: absolute;
        top: 0;
        right: 0;
        width: 34px;
        height: 34px;
        background: #ffffff;
        border: 1px solid var(--profile-border);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        font-size: 16px;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
        transition: all 0.15s ease;
        padding: 0;
    }

    .profile-avatar-camera-btn:hover {
        background: #f8fafc;
        color: #0f172a;
        transform: scale(1.08);
    }

    .profile-avatar-camera-btn.pending {
        color: #d97706;
        background: #fffbeb;
        border-color: #fde68a;
    }

    .profile-name {
        font-size: 22px;
        font-weight: 800;
        color: var(--profile-text);
        letter-spacing: -0.02em;
    }

    /* Badges */
    .badge-status-pill {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        line-height: 1.25;
    }

    .badge-verified {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    .badge-unverified {
        background: #f8fafc;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }

    .badge-role-pill {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 6px;
        background: #0f172a;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        line-height: 1.25;
        letter-spacing: 0.04em;
    }

    .badge-meta-pill {
        font-size: 11.5px;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 6px;
        background: #f8fafc;
        color: #475569;
        border: 1px solid var(--profile-border);
        display: inline-flex;
        align-items: center;
        line-height: 1.25;
    }

    .btn-profile-action {
        background: #ffffff;
        border: 1px solid var(--profile-border);
        color: #334155;
        border-radius: 8px;
        font-weight: 600;
        font-size: 12.5px;
        padding: 7px 14px;
        transition: all 0.15s ease;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
    }

    .btn-profile-action:hover {
        background: #f8fafc;
        color: #0f172a;
        border-color: #cbd5e1;
    }

    /* --- MODERN CARDS --- */
    .modern-card {
        background: #ffffff;
        border: 1px solid var(--profile-border);
        border-radius: 14px;
        box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .modern-card-header {
        padding: 14px 18px;
        border-bottom: 1px solid #f1f5f9;
        background: #ffffff;
    }

    .card-header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        flex-shrink: 0;
    }

    .card-header-title {
        font-size: 13.5px;
        font-weight: 700;
        color: var(--profile-text);
    }

    .modern-card-body {
        padding: 18px;
    }

    /* --- FORM ELEMENTS --- */
    .modern-label {
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 6px;
        letter-spacing: 0.01em;
    }

    .modern-input-group {
        border-radius: 8px;
        overflow: hidden;
    }

    .modern-input-group .input-group-text {
        background: #f8fafc;
        border-color: var(--profile-border);
        color: #64748b;
        font-size: 16px;
        padding: 0 12px;
    }

    .modern-input {
        height: 42px !important;
        border-color: var(--profile-border) !important;
        font-size: 13px !important;
        color: var(--profile-text) !important;
        transition: all 0.15s ease !important;
    }

    .modern-input:focus {
        border-color: var(--profile-blue) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.09) !important;
        background: #ffffff !important;
        outline: none !important;
    }

    .modern-pwd-toggle {
        border-color: var(--profile-border) !important;
        background: #f8fafc !important;
        color: #64748b !important;
        padding: 0 12px !important;
    }

    .modern-pwd-toggle:hover {
        background: #f1f5f9 !important;
        color: #0f172a !important;
    }

    /* Info Tiles */
    .info-tile {
        transition: transform 0.15s ease, border-color 0.15s ease;
    }
    .info-tile:hover {
        border-color: #cbd5e1 !important;
        transform: translateY(-1px);
    }

    /* KTP Preview Card */
    .ktp-preview-card {
        background: #f8fafc;
        border: 1px solid var(--profile-border);
    }

    /* Quick Links */
    .modern-quick-link {
        transition: all 0.15s ease;
        border: 1px solid transparent;
    }
    .modern-quick-link:hover {
        background: #f8fafc;
        border-color: var(--profile-border);
    }
    .quick-link-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        flex-shrink: 0;
    }

    /* Awards */
    .modern-award-item {
        background: #f8fafc;
        border: 1px solid var(--profile-border);
        transition: all 0.15s ease;
    }
    .modern-award-item:hover {
        border-color: #cbd5e1;
        background: #ffffff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .award-medal-box {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .rank-1-bg { background: #fffbeb; }
    .rank-2-bg { background: #f1f5f9; }
    .rank-3-bg { background: #fef3c7; }

    /* Modals */
    .modern-modal-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid var(--profile-border);
        overflow: hidden;
        box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.12);
    }

    .file-upload-dropzone {
        border: 2px dashed #cbd5e1;
        background: #f8fafc;
        transition: all 0.15s ease;
    }
    .file-upload-dropzone:hover {
        border-color: var(--profile-blue);
        background: #eff6ff;
    }

    /* Scrollbars */
    .custom-dropdown-scroll::-webkit-scrollbar {
        width: 5px;
    }
    .custom-dropdown-scroll::-webkit-scrollbar-track {
        background: #f8fafc;
    }
    .custom-dropdown-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 9999px;
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 991px) {
        .profile-hero-body {
            padding: 0 16px 18px 16px;
        }
        .profile-hero-actions {
            width: 100%;
        }
        .profile-hero-actions .btn-profile-action {
            flex: 1 1 calc(33.333% - 8px);
            justify-content: center;
            font-size: 12px;
            padding: 8px 10px;
        }
    }

    @media (max-width: 575px) {
        .profile-avatar-wrapper {
            margin-top: -50px;
            width: 100px;
            height: 100px;
        }
        .profile-avatar-circle {
            width: 100px;
            height: 100px;
        }
        .profile-avatar-initials {
            font-size: 34px;
        }
        .profile-name {
            font-size: 19px;
        }
        .profile-hero-actions .btn-profile-action {
            flex: 1 1 100%;
        }
        .profile-actions-bar {
            flex-direction: column;
            gap: 12px;
        }
        .profile-actions-bar .btn {
            width: 100%;
            justify-content: center;
        }
        .profile-actions-bar div {
            width: 100%;
        }
        .modern-input {
            font-size: 14px !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Toggle Password Visibility (Eye Icon)
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
                icon.classList.replace('mdi-eye-outline', 'mdi-eye-off-outline');
            }
        } else {
            input.type = 'password';
            if (icon) {
                icon.classList.replace('mdi-eye-off-outline', 'mdi-eye-outline');
            }
        }
    }

    // Live Image Preview before Upload
    function previewImage(input, previewImgId, containerId, iconId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById(previewImgId);
                const container = document.getElementById(containerId);
                const icon = document.getElementById(iconId);
                if (img) img.src = e.target.result;
                if (container) container.classList.remove('d-none');
                if (icon) icon.classList.add('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Toggle Change Password Panel
    document.addEventListener('DOMContentLoaded', function() {
        const btnToggle = document.getElementById('btn-toggle-password');
        const container = document.getElementById('password-container');
        if (btnToggle && container) {
            btnToggle.addEventListener('click', function() {
                container.classList.toggle('d-none');
                if (container.classList.contains('d-none')) {
                    btnToggle.innerHTML = '<i class="mdi mdi-lock-reset me-1"></i> Ganti Password';
                    btnToggle.classList.replace('btn-outline-danger', 'btn-outline-secondary');
                    const pwd = document.getElementById('input-password');
                    const pwdConfirm = document.getElementById('input-password-confirm');
                    if (pwd) pwd.value = '';
                    if (pwdConfirm) pwdConfirm.value = '';
                } else {
                    btnToggle.innerHTML = '<i class="mdi mdi-close me-1"></i> Batal Ganti Password';
                    btnToggle.classList.replace('btn-outline-secondary', 'btn-outline-danger');
                    const firstPwd = document.getElementById('input-password');
                    if (firstPwd) firstPwd.focus();
                }
            });
        }
    });
</script>
@endpush