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

    {{-- ======================================================================= --}}
    {{-- BAGIAN 1: DASHBOARD PEKERJAAN (KHUSUS ADMIN, AUDIT, SECURITY) --}}
    {{-- ======================================================================= --}}

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
                        <div class="card-bank-icon"><i class="mdi mdi-sitemap"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Total Divisi</p>
                            <h2 class="card-bank-value count-up" data-target="{{ $totalDivisions }}">0</h2>
                            <p class="card-bank-desc">Divisi aktif</p>
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
                            <p class="card-bank-label">Perlu Verifikasi</p>
                            <h2 class="card-bank-value count-up" data-target="{{ $pendingVerifications }}">0</h2>
                            <p class="card-bank-desc">Absensi menunggu persetujuan</p>
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
                        <div class="card-bank-icon"><i class="mdi mdi-account-multiple"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Anggota Tim</p>
                            <h2 class="card-bank-value count-up" data-target="{{ $myTeamMembers }}">0</h2>
                            <p class="card-bank-desc">Total anggota dalam tim</p>
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
                            <p class="card-bank-label">Absen Hari Ini</p>
                            <h2 class="card-bank-value count-up" data-target="{{ $attendancesToday }}">0</h2>
                            <p class="card-bank-desc">Tim sudah absen hari ini</p>
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

    {{-- ======================================================================= --}}
    {{-- BAGIAN 2: DASHBOARD PERSONAL (ID CARD & ABSEN MANDIRI) --}}
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
                                        <img src="{{ Storage::url(Auth::user()->profile_photo_path) }}" 
                                             alt="Profile" 
                                             class="id-card-img"
                                             data-bs-toggle="modal" 
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

                {{-- QR CODE CARD UNTUK SCAN SECURITY --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm hover-float" style="background: white; border-radius: 16px;">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="fw-bold mb-1">QR Code Absensi</h5>
                                <p class="text-muted small mb-0">Klik QR untuk memperbesar</p>
                            </div>
                            <div class="bg-light p-2 rounded shadow-sm scale-on-hover" id="dashboard-qrcode" style="cursor: pointer; transition: transform 0.2s;" data-bs-toggle="modal" data-bs-target="#qrModal">
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
                             <h4 class="fw-bold mb-0 font-monospace text-primary" id="realtime-clock">--:--:--</h4>
                             <small class="text-muted">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</small>
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
                        @php
                            $isCrossDay = false;
                            if (!$myAttendanceToday->check_out_time) {
                                $isCrossDay = $myAttendanceToday->check_in_time->format('Y-m-d') !== date('Y-m-d');
                            }
                            // Label hybrid
                            $sourceLabel = ($myAttendanceToday->attendance_type == 'scan') ? 'Security Scan' : 'Selfie Mandiri';
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

                            {{-- SEDANG BEKERJA --}}
                        @else
                            <div class="status-card {{ $isCrossDay ? 'status-warning' : 'status-success' }} mb-3 position-relative overflow-hidden">
                                {{-- Background Animation Blob --}}
                                <div class="blob-bg"></div>
                                
                                <div class="d-flex align-items-center position-relative z-index-1">
                                    <div class="status-icon shadow pulse-animation">
                                        <i class="mdi {{ $isCrossDay ? 'mdi-calendar-clock' : 'mdi-clock-check' }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        @if ($isCrossDay)
                                            <h5 class="mb-1 fw-bold text-danger">Lembur Lintas Hari</h5>
                                            <p class="mb-0 small text-dark">Masuk: <strong>{{ $myAttendanceToday->check_in_time->format('d M, H:i') }}</strong> via {{ $sourceLabel }}</p>
                                        @else
                                            <div class="d-flex align-items-center">
                                                <h5 class="mb-1 fw-bold">Sedang Bekerja</h5>
                                                <span class="live-indicator ms-2"></span>
                                            </div>
                                            <p class="mb-0">Masuk Pukul: <strong>{{ $myAttendanceToday->check_in_time->format('H:i') }}</strong> via {{ $sourceLabel }}</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- TOMBOL AKSI PULANG (HYBRID: Muncul walau absen masuk via SCAN) --}}
                                <div class="mt-3 pt-3 border-top position-relative z-index-1">

                                    @if ($isCrossDay)
                                        <p class="text-center text-muted mb-3 small">
                                            Anda belum absen pulang kemarin. Pilih tindakan:
                                        </p>

                                        <div class="row g-2">
                                            <div class="col-6">
                                                <a href="{{ route('self.attend.create') }}"
                                                    class="btn btn-primary btn-sm w-100 h-100 d-flex align-items-center justify-content-center flex-column py-2 shadow-sm hover-scale">
                                                    <i class="mdi mdi-camera-party-mode fs-4 mb-1"></i>
                                                    <span>Pulang (Lembur)</span>
                                                </a>
                                            </div>
                                            <div class="col-6">
                                                <form action="{{ route('self.attend.skip', $myAttendanceToday->id) }}"
                                                    method="POST" class="h-100">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-warning btn-sm w-100 h-100 d-flex align-items-center justify-content-center flex-column py-2 text-dark shadow-sm hover-scale"
                                                        onclick="return confirm('Pilih ini jika Anda KEMARIN LUPA absen pulang.\nSesi kemarin akan ditutup otomatis tanpa foto.\n\nLanjutkan?');">
                                                        <i class="mdi mdi-skip-forward fs-4 mb-1"></i>
                                                        <span>Lewati (Lupa)</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @else
                                        <a href="{{ route('self.attend.create') }}"
                                            class="btn btn-danger btn-sm w-100 shadow hover-scale">
                                            <i class="mdi mdi-logout me-1"></i>
                                            Absen Pulang Mandiri
                                        </a>
                                    @endif

                                </div>
                            </div>
                        @endif

                        {{-- IZIN / SAKIT / CUTI / WFH --}}
                    @elseif(isset($myLeaveToday) && $myLeaveToday && $myLeaveToday->user_id == Auth::id())
                        @php
                            // LOGIKA WARNA & ICON KHUSUS WFH
                            if ($myLeaveToday->type == 'wfh') {
                                $leaveColor = 'status-success'; 
                                $leaveIcon = 'mdi-laptop-mac';
                                $leaveTitle = 'Sedang Bekerja (WFH)';
                                $leaveDesc = 'Absensi Dinas/Remote';
                            } else {
                                $leaveColor = $myLeaveToday->status == 'approved' ? 'status-success' : 'status-warning';
                                $leaveIcon = $myLeaveToday->type == 'sakit' ? 'mdi-hospital-box' : ($myLeaveToday->type == 'telat' ? 'mdi-clock-alert' : 'mdi-bag-suitcase');
                                $leaveTitle = 'Izin ' . ucfirst($myLeaveToday->type);
                                $leaveDesc = $myLeaveToday->type == 'telat' ? 'Hadir pukul: ' . \Carbon\Carbon::parse($myLeaveToday->start_time)->format('H:i') : 'Sampai: ' . \Carbon\Carbon::parse($myLeaveToday->end_date)->format('d M Y');
                            }
                        @endphp

                        <div class="status-card {{ $leaveColor }} mb-3 hover-float">
                            <div class="d-flex align-items-start">
                                <div class="status-icon shadow"><i class="mdi {{ $leaveIcon }}"></i></div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <h5 class="mb-1 fw-bold">{{ $leaveTitle }}</h5>
                                        <span class="badge shadow-sm {{ $myLeaveToday->status == 'approved' ? 'bg-success' : 'bg-warning' }}">
                                            {{ strtoupper($myLeaveToday->status) }}
                                        </span>
                                    </div>
                                    <p class="text-muted mb-2 small">
                                        {{ $leaveDesc }}
                                    </p>
                                    <div class="bg-white p-2 rounded border mb-2 shadow-sm">
                                        <span class="fst-italic text-dark">"{{ $myLeaveToday->reason }}"</span>
                                    </div>
                                    
                                    @if($myLeaveToday->type == 'wfh' && $myLeaveToday->file_proof)
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-light border shadow-sm" 
                                                onclick="window.open('{{ Storage::url($myLeaveToday->file_proof) }}', '_blank')">
                                                <i class="mdi mdi-image-area me-1"></i>Lihat Bukti WFH
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if ($myLeaveToday->status == 'approved' && $myLeaveToday->type != 'telat')
                                <div class="mt-3 pt-3 border-top text-center">
                                    <p class="small text-muted mb-2">Sudah kembali bekerja di kantor?</p>
                                    <form action="{{ route('leave-requests.finish-early', $myLeaveToday->id) }}"
                                        method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-primary btn-sm w-100 shadow-sm hover-scale"
                                            onclick="return confirm('Apakah Anda yakin ingin mengakhiri status ini?');">
                                            <i class="mdi mdi-briefcase-check me-2"></i>Saya Masuk Kantor Sekarang
                                        </button>
                                    </form>
                                </div>
                            @endif

                            @if ($myLeaveToday->type == 'telat' && $myLeaveToday->status == 'approved')
                                <div class="mt-3 pt-3 border-top text-center">
                                    <form action="{{ route('leave-requests.cancel', $myLeaveToday->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-dark btn-sm w-100 shadow-sm hover-scale">
                                            <i class="mdi mdi-fingerprint me-2"></i>Absen Sekarang
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        {{-- BELUM ABSEN --}}
                    @else
                        <div class="status-card status-info hover-shadow-lg">
                            <div class="text-center py-4">
                                <div class="mb-3">
                                    <i class="mdi mdi-clock-alert display-4 text-primary pulse-text"></i>
                                </div>
                                <h5 class="mb-2 fw-bold">Anda Belum Absen Hari Ini</h5>
                                <p class="text-muted mb-4">Gunakan fitur ini jika Anda bekerja WFH atau Dinas Luar.</p>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('self.attend.create') }}" class="btn btn-dark shadow hover-scale">
                                        <i class="mdi mdi-fingerprint me-2"></i>Absen Mandiri
                                    </a>
                                    <a href="{{ route('leave-requests.create') }}" class="btn btn-outline-dark shadow-sm hover-scale">
                                        <i class="mdi mdi-file-document-edit-outline me-2"></i>Izin/Sakit
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
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
                             alt="Profile Photo"
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

@endsection

@push('styles')
    <style>
        /* === NEW ANIMATIONS & INTERACTIVE STYLES === */
        
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
            opacity: 0; /* Awal tersembunyi */
            animation: fadeInUp 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        }

        /* 2. Pulse Animation for Status Icon */
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        .pulse-text {
            animation: pulseText 2s infinite;
        }
        
        @keyframes pulseText {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
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
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        
        .hover-scale {
            transition: transform 0.2s ease;
        }
        .hover-scale:hover {
            transform: scale(1.05);
        }
        
        .scale-on-hover:hover {
            transform: scale(1.1);
        }

        .hover-shadow-lg {
            transition: box-shadow 0.3s ease;
        }
        .hover-shadow-lg:hover {
            box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
        }

        /* 5. Glassmorphism for Modal */
        .glass-effect {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        /* === EXISTING & REFINED STYLES === */
        
        .card-bank {
            position: relative;
            min-height: 200px;
            border-radius: 16px;
            overflow: hidden;
            border: none;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            /* CSS 3D Tilt dihapus */
        }

        /* Transform removed */
        .card-bank .card-body {
            position: relative;
            z-index: 2;
            padding: 24px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
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
        .gradient-purple { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .gradient-blue { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .gradient-green { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .gradient-orange { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .gradient-red { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .gradient-dark { background: linear-gradient(135deg, #2c3e50 0%, #000000 100%); }

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
            /* CSS 3D Tilt dihapus */
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
            background: rgba(255,255,255,0.05); /* Sedikit tekstur */
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

        .card-id-details { flex-grow: 1; }
        .card-id-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.7; margin-bottom: 4px; font-weight: 500; }
        .card-id-name { font-size: 24px; font-weight: 700; margin-bottom: 12px; line-height: 1.2; font-family: 'Consolas', 'Courier New', monospace; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
        .card-id-division { font-size: 16px; font-weight: 500; opacity: 0.9; font-family: 'Consolas', 'Courier New', monospace; }
        .card-id-footer { margin-top: auto; }
        
        .card-action { border-radius: 16px; border: none; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; height: 100%; }
        .card-status { border-radius: 16px; border: none; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08); height: 100%; }
        
        .status-card { padding: 24px; border-radius: 12px; border: 2px solid; background: #f8fafc; transition: all 0.3s ease; }
        .status-success { border-color: #10b981; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); }
        .status-warning { border-color: #f59e0b; background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); }
        .status-info { border-color: #3b82f6; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); }
        
        .status-icon { width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 20px; font-size: 28px; flex-shrink: 0; }
        .status-success .status-icon { background: #10b981; color: white; }
        .status-warning .status-icon { background: #f59e0b; color: white; }
        .status-info .status-icon { background: #3b82f6; color: white; }

        .badge { border-radius: 8px; font-weight: 600; padding: 6px 12px; }
        
        /* Buttons Redesign */
        .btn { transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); }
        .btn:active { transform: scale(0.95); }

        .btn-dark { background: #1a1a1a; border: none; border-radius: 12px; font-weight: 600; padding: 12px 28px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn-dark:hover { background: #000; transform: translateY(-2px); box-shadow: 0 8px 15px rgba(0,0,0,0.2); }
        
        .btn-outline-dark { border: 2px solid #1a1a1a; color: #1a1a1a; border-radius: 12px; font-weight: 600; padding: 12px 28px; }
        .btn-outline-dark:hover { background: #1a1a1a; color: white; transform: translateY(-2px); }

        @media (max-width: 768px) {
            .card-bank-value { font-size: 28px; }
            .card-bank { min-height: 180px; margin-bottom: 20px; }
            .card-id { min-height: 200px; }
            .card-id-name { font-size: 20px; }
            .id-card-img, .id-card-img-placeholder { width: 50px; height: 60px; }
            #greeting-text { font-size: 0.8rem; }
            h3.fw-bold { font-size: 1.5rem; }
        }
    </style>
@endpush

@push('scripts')
    {{-- QRCode Lib --}}
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    {{-- Vanilla Tilt REMOVED --}}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // 1. [BARU] REALTIME CLOCK
            function updateClock() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
                const clockElement = document.getElementById('realtime-clock');
                if(clockElement) clockElement.innerText = timeString;
                
                // Greeting logic
                const hour = now.getHours();
                const greetingElement = document.getElementById('greeting-text');
                let greeting = 'Selamat Datang,';
                if (hour >= 5 && hour < 12) greeting = 'Selamat Pagi,';
                else if (hour >= 12 && hour < 15) greeting = 'Selamat Siang,';
                else if (hour >= 15 && hour < 18) greeting = 'Selamat Sore,';
                else greeting = 'Selamat Malam,';
                
                if(greetingElement) greetingElement.innerText = greeting;
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

            // --- SCRIPT QR CODE ---
            @if(Auth::user()->qr_code_value)
                const qrValue = "{{ Auth::user()->qr_code_value }}";
                
                new QRCode(document.getElementById("dashboard-qrcode"), {
                    text: qrValue,
                    width: 64,
                    height: 64,
                    colorDark : "#000000",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.H
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

            // Default Options untuk Chart agar lebih halus
            Chart.defaults.font.family = "'Inter', 'Helvetica', 'Arial', sans-serif";
            
            @if (auth()->user()->role == 'admin')
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Tepat Waktu', 'Terlambat', 'Pulang Cepat', 'Pending', 'Tidak Hadir'],
                        datasets: [{
                            data: [{{ $stats['on_time'] }}, {{ $stats['late'] }}, {{ $stats['early'] }}, {{ $stats['pending'] }}, {{ $stats['absent'] }}],
                            backgroundColor: ['#00d25b', '#ffab00', '#fc424a', '#0090e7', '#8c94a3'],
                            borderWidth: 0,
                            hoverOffset: 10 // Efek hover keluar
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { animateScale: true, animateRotate: true },
                        plugins: {
                            legend: { position: 'right', labels: { usePointStyle: true, padding: 20 } }
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
                            data: [{{ $stats['verified'] }}, {{ $stats['pending'] }}, {{ $stats['late'] }}],
                            backgroundColor: ['#00d25b', '#ffab00', '#fc424a'],
                            borderWidth: 0,
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            @elseif (auth()->user()->role == 'security')
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Scan Masuk', 'Scan Pulang'],
                        datasets: [{
                            data: [{{ $stats['check_in_scans'] }}, {{ $stats['check_out_scans'] }}],
                            backgroundColor: ['#00d25b', '#0090e7'],
                            borderWidth: 0,
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '60%',
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            @else
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['Tepat Waktu', 'Terlambat', 'Pulang Cepat', 'Pending'],
                        datasets: [{
                            data: [{{ $stats['on_time'] }}, {{ $stats['late'] }}, {{ $stats['early'] }}, {{ $stats['pending'] }}],
                            backgroundColor: ['#00d25b', '#ffab00', '#fc424a', '#8c94a3'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } }
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
    </script>
@endpush