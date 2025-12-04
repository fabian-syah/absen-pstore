@extends('layout.master')

@section('title')
    Dashboard
@endsection

@section('heading')
    Selamat Datang, {{ Auth::user()->name }}!
@endsection

@section('content')

    {{-- ======================================================================= --}}
    {{-- BAGIAN 1: DASHBOARD PEKERJAAN (KHUSUS ADMIN, AUDIT, SECURITY) --}}
    {{-- ======================================================================= --}}

    @if (auth()->user()->role == 'admin')
        {{-- WIDGET ADMIN --}}
        <div class="row mb-4">
            <div class="col-md-3 grid-margin stretch-card">
                <div class="card card-bank gradient-purple">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi mdi-account-multiple"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Total User</p>
                            <h2 class="card-bank-value">{{ $totalUsers }}</h2>
                            <p class="card-bank-desc">Karyawan Aktif</p>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card">
                <div class="card card-bank gradient-blue">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi mdi-sitemap"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Total Divisi</p>
                            <h2 class="card-bank-value">{{ $totalDivisions }}</h2>
                            <p class="card-bank-desc">Divisi aktif</p>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card">
                <div class="card card-bank gradient-green">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi mdi-calendar-check"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Absensi Hari Ini</p>
                            <h2 class="card-bank-value">{{ $attendancesToday }}</h2>
                            <p class="card-bank-desc">Total absensi hari ini</p>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card">
                <div class="card card-bank gradient-orange">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi mdi-alert-circle-outline"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Perlu Verifikasi</p>
                            <h2 class="card-bank-value">{{ $pendingVerifications }}</h2>
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
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card card-bank gradient-red">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi mdi-alert-circle-outline"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Perlu Verifikasi</p>
                            <h2 class="card-bank-value">{{ $pendingVerifications }}</h2>
                            <p class="card-bank-desc">Absensi menunggu persetujuan</p>
                            <a href="{{ route('audit.verify.list') }}" class="btn btn-sm btn-light mt-2">
                                <i class="mdi mdi-clipboard-check me-1"></i>Lihat Daftar
                            </a>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card card-bank gradient-blue">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi mdi-account-multiple"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Anggota Tim</p>
                            <h2 class="card-bank-value">{{ $myTeamMembers }}</h2>
                            <p class="card-bank-desc">Total anggota dalam tim</p>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card card-bank gradient-green">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi mdi-calendar-check"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Absen Hari Ini</p>
                            <h2 class="card-bank-value">{{ $attendancesToday }}</h2>
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
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card card-action">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="mdi mdi-qrcode-scan display-1 text-dark"></i>
                        </div>
                        <h4 class="card-title mb-3">Pindai QR User</h4>
                        <p class="text-muted mb-4">Arahkan kamera ke QR Code user untuk melakukan absensi.</p>
                        <a href="{{ route('security.scan') }}" class="btn btn-dark btn-lg">
                            <i class="mdi mdi-camera-enhance me-2"></i>Mulai Memindai
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card card-bank gradient-dark">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi mdi-chart-bar"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Pindaian Hari Ini</p>
                            <h2 class="card-bank-value">{{ $myScansToday }}</h2>
                            <p class="card-bank-desc">Total pindaian QR hari ini</p>
                            <div class="mt-4 pt-3 border-top border-light">
                                <p class="card-bank-label mb-2">User Aktif</p>
                                <h3 class="card-bank-value mb-0">{{ $totalUsers }}</h3>
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

    <div class="row">
        <div class="col-12">
            <h4 class="card-title mb-3"><i class="mdi mdi-account-circle me-2"></i>Absensi Pribadi</h4>
        </div>
    </div>

    <div class="row">
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
                                <h3 class="card-id-name">{{ strtoupper(Auth::user()->name) }}</h3>
                                <p class="card-id-label">DIVISI</p>
                                <h4 class="card-id-division">
                                    {{ strtoupper(Auth::user()->division->name ?? 'BELUM ADA DIVISI') }}
                                </h4>
                            </div>
                            <div class="card-id-footer d-flex justify-content-end align-items-end mt-4">
                                <div class="text-end">
                                    <p class="mb-0 text-white-50" style="font-size: 10px; letter-spacing: 1px;">NOMOR ID</p>
                                    <p class="card-id-card-number mb-0"
                                        style="font-size: 22px; letter-spacing: 2px; font-weight: 700;">
                                        {{ $idCardNumber ?? '000000 000000' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- [BARU] QR CODE CARD UNTUK SCAN SECURITY --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="background: white; border-radius: 16px;">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="fw-bold mb-1">QR Code Absensi</h5>
                                <p class="text-muted small mb-0">Tunjukkan ke Security</p>
                            </div>
                            <div class="bg-light p-2 rounded" id="dashboard-qrcode" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#qrModal">
                                {{-- QR Code dirender via JS disini --}}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- KARTU STATUS ABSENSI & TOMBOL ABSEN MANDIRI --}}
        <div class="col-md-7 grid-margin stretch-card">
            <div class="card card-status">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">
                            <i class="mdi mdi-calendar-today me-2"></i>Status Absensi
                        </h4>
                        {{-- [BARU] MENAMPILKAN JAM KERJA / JADWAL --}}
                        <span class="badge bg-light text-dark border">
                            <i class="mdi mdi-clock-outline me-1"></i> Jadwal: {{ $todaySchedule }}
                        </span>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="mdi mdi-check-circle-outline me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="mdi mdi-alert-circle-outline me-2"></i>
                            {{ session('error') }}
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
                        @endphp

                        {{-- SUDAH PULANG --}}
                        @if ($myAttendanceToday->check_out_time || $myAttendanceToday->photo_out_path)
                            <div class="status-card status-success mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="status-icon"><i class="mdi mdi-home-variant"></i></div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1 fw-bold">Anda Sudah Pulang</h5>
                                        <p class="text-muted mb-0 small">Terima kasih atas kerja keras Anda!</p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row text-center">
                                    <div class="col-6 border-end">
                                        <small class="text-muted d-block">JAM MASUK</small>
                                        <h4 class="fw-bold text-success mb-0">
                                            {{ $myAttendanceToday->check_in_time->format('H:i') }}
                                        </h4>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">JAM PULANG</small>
                                        <h4 class="fw-bold text-primary mb-0">
                                            {{ $myAttendanceToday->check_out_time ? $myAttendanceToday->check_out_time->format('H:i') : '-' }}
                                        </h4>
                                    </div>
                                </div>
                            </div>

                            {{-- SEDANG BEKERJA --}}
                        @else
                            <div class="status-card {{ $isCrossDay ? 'status-warning' : 'status-success' }} mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="status-icon">
                                        <i
                                            class="mdi {{ $isCrossDay ? 'mdi-calendar-clock' : 'mdi-clock-check' }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        @if ($isCrossDay)
                                            <h5 class="mb-1 fw-bold text-danger">Lembur Lintas Hari</h5>
                                            <p class="mb-0 small text-dark">Masuk tanggal:
                                                <strong>{{ $myAttendanceToday->check_in_time->format('d M Y, H:i') }}</strong>
                                            </p>
                                        @else
                                            <h5 class="mb-1 fw-bold">Sedang Bekerja</h5>
                                            <p class="mb-0">Masuk Pukul:
                                                <strong>{{ $myAttendanceToday->check_in_time->format('H:i') }}</strong>
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                {{-- TOMBOL AKSI --}}
                                @if ($myAttendanceToday->attendance_type == 'self')
                                    <div class="mt-3 pt-3 border-top">

                                        @if ($isCrossDay)
                                            {{-- === KONDISI LINTAS HARI (ADA 2 PILIHAN) === --}}

                                            <p class="text-center text-muted mb-3 small">
                                                Anda belum absen pulang kemarin. Pilih tindakan:
                                            </p>

                                            <div class="row g-2">
                                                {{-- OPSI 1: LEMBUR (TETAP FOTO) --}}
                                                <div class="col-6">
                                                    <a href="{{ route('self.attend.create') }}"
                                                        class="btn btn-primary btn-sm w-100 h-100 d-flex align-items-center justify-content-center flex-column py-2">
                                                        <i class="mdi mdi-camera-party-mode fs-4 mb-1"></i>
                                                        <span>Pulang (Lembur)</span>
                                                    </a>
                                                </div>

                                                {{-- OPSI 2: LEWATI (LUPA ABSEN, TANPA FOTO) --}}
                                                <div class="col-6">
                                                    <form action="{{ route('self.attend.skip', $myAttendanceToday->id) }}"
                                                        method="POST" class="h-100">
                                                        @csrf
                                                        <button type="submit"
                                                            class="btn btn-warning btn-sm w-100 h-100 d-flex align-items-center justify-content-center flex-column py-2 text-dark"
                                                            onclick="return confirm('Pilih ini jika Anda KEMARIN LUPA absen pulang.\nSesi kemarin akan ditutup otomatis tanpa foto.\n\nLanjutkan?');">
                                                            <i class="mdi mdi-skip-forward fs-4 mb-1"></i>
                                                            <span>Lewati (Lupa)</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @else
                                            {{-- === KONDISI ABSEN NORMAL HARI INI === --}}
                                            <a href="{{ route('self.attend.create') }}"
                                                class="btn btn-danger btn-sm w-100">
                                                <i class="mdi mdi-logout me-1"></i>
                                                Absen Pulang Mandiri
                                            </a>
                                        @endif

                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- IZIN / SAKIT / CUTI / WFH --}}
                    @elseif(isset($myLeaveToday) && $myLeaveToday && $myLeaveToday->user_id == Auth::id())
                        @php
                            // LOGIKA WARNA & ICON KHUSUS WFH
                            if ($myLeaveToday->type == 'wfh') {
                                // WFH dianggap MASUK (Success/Primary)
                                $leaveColor = 'status-success'; 
                                $leaveIcon = 'mdi-laptop-mac';
                                $leaveTitle = 'Sedang Bekerja (WFH)';
                                $leaveDesc = 'Absensi Dinas/Remote';
                            } else {
                                // Izin/Sakit/Cuti (Warning/Info)
                                $leaveColor = $myLeaveToday->status == 'approved' ? 'status-success' : 'status-warning';
                                $leaveIcon = $myLeaveToday->type == 'sakit' ? 'mdi-hospital-box' : ($myLeaveToday->type == 'telat' ? 'mdi-clock-alert' : 'mdi-bag-suitcase');
                                $leaveTitle = 'Izin ' . ucfirst($myLeaveToday->type);
                                $leaveDesc = $myLeaveToday->type == 'telat' ? 'Hadir pukul: ' . \Carbon\Carbon::parse($myLeaveToday->start_time)->format('H:i') : 'Sampai: ' . \Carbon\Carbon::parse($myLeaveToday->end_date)->format('d M Y');
                            }
                        @endphp

                        <div class="status-card {{ $leaveColor }} mb-3">
                            <div class="d-flex align-items-start">
                                <div class="status-icon"><i class="mdi {{ $leaveIcon }}"></i></div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <h5 class="mb-1 fw-bold">{{ $leaveTitle }}</h5>
                                        <span class="badge {{ $myLeaveToday->status == 'approved' ? 'bg-success' : 'bg-warning' }}">
                                            {{ strtoupper($myLeaveToday->status) }}
                                        </span>
                                    </div>
                                    <p class="text-muted mb-2 small">
                                        {{ $leaveDesc }}
                                    </p>
                                    <div class="bg-white p-2 rounded border mb-2">
                                        <span class="fst-italic text-dark">"{{ $myLeaveToday->reason }}"</span>
                                    </div>
                                    
                                    {{-- TAMPILKAN TOMBOL LIHAT BUKTI JIKA WFH --}}
                                    @if($myLeaveToday->type == 'wfh' && $myLeaveToday->file_proof)
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-light border" 
                                                onclick="window.open('{{ Storage::url($myLeaveToday->file_proof) }}', '_blank')">
                                                <i class="mdi mdi-image-area me-1"></i>Lihat Bukti WFH
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- TOMBOL SELESAIKAN IZIN (Hanya jika status approved dan tipe BUKAN telat) --}}
                            @if ($myLeaveToday->status == 'approved' && $myLeaveToday->type != 'telat')
                                <div class="mt-3 pt-3 border-top text-center">
                                    <p class="small text-muted mb-2">Sudah kembali bekerja di kantor?</p>
                                    <form action="{{ route('leave-requests.finish-early', $myLeaveToday->id) }}"
                                        method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-primary btn-sm w-100"
                                            onclick="return confirm('Apakah Anda yakin ingin mengakhiri status ini?');">
                                            <i class="mdi mdi-briefcase-check me-2"></i>Saya Masuk Kantor Sekarang
                                        </button>
                                    </form>
                                </div>
                            @endif

                            {{-- TOMBOL IZIN TELAT --}}
                            @if ($myLeaveToday->type == 'telat' && $myLeaveToday->status == 'approved')
                                <div class="mt-3 pt-3 border-top text-center">
                                    <form action="{{ route('leave-requests.cancel', $myLeaveToday->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-dark btn-sm w-100">
                                            <i class="mdi mdi-fingerprint me-2"></i>Absen Sekarang
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        {{-- BELUM ABSEN --}}
                    @else
                        <div class="status-card status-info">
                            <div class="text-center py-4">
                                <i class="mdi mdi-clock-alert display-4 mb-3 text-primary"></i>
                                <h5 class="mb-2 fw-bold">Anda Belum Absen Hari Ini</h5>
                                <p class="text-muted mb-4">Gunakan fitur ini jika Anda bekerja WFH atau Dinas Luar.</p>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('self.attend.create') }}" class="btn btn-dark">
                                        <i class="mdi mdi-fingerprint me-2"></i>Absen Mandiri
                                    </a>
                                    <a href="{{ route('leave-requests.create') }}" class="btn btn-outline-dark">
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
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0"><i class="mdi mdi-chart-pie me-2"></i>Statistik Absensi</h4>
                    {{-- <a href="{{ route('dashboard.export-pdf') }}" class="btn btn-danger btn-sm">
                        <i class="mdi mdi-file-pdf-box me-1"></i>Export PDF
                    </a> --}}
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="chart-container" style="position: relative; height:300px;">
                                <canvas id="attendancePieChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL POPUP FOTO PROFIL --}}
    <div class="modal fade" id="profilePhotoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-body p-0 position-relative modal-image-wrapper text-center">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 shadow"
                        data-bs-dismiss="modal" aria-label="Close" style="z-index: 10;"></button>
                    
                    <div class="p-3">
                        <img src="" id="profileModalImageSrc" class="img-fluid rounded shadow" 
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
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">QR Code Saya</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="qrcode-modal-display" class="d-flex justify-content-center"></div>
                    <p class="mt-3 text-muted small">Tunjukkan ke Security untuk Scan</p>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        /* CSS Card Bank & ID Card */
        .card-bank {
            position: relative;
            min-height: 200px;
            border-radius: 16px;
            overflow: hidden;
            border: none;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            transition: all 0.3s ease;
        }

        .card-bank:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.18);
        }

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

        .card-bank-chip::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 50%;
            top: 0;
            left: 0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0) 100%);
            border-radius: 6px 6px 0 0;
        }

        .card-bank-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 48px;
            opacity: 0.2;
        }

        .card-bank-content {
            position: relative;
            z-index: 3;
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

        .gradient-indigo {
            background: linear-gradient(135deg, #5f72bd 0%, #9b23ea 100%);
        }

        .card-id {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            border: none;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
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
        }

        .card-id-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        /* [MODIFIKASI] Style untuk Foto di ID Card */
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
            cursor: pointer; /* Menunjukkan bisa diklik */
            transition: transform 0.2s ease-in-out;
        }

        .id-card-img:hover {
            transform: scale(1.05); /* Efek zoom saat hover */
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
        /* [AKHIR MODIFIKASI] */

        /* Style Wrapper Modal */
        .modal-image-wrapper { 
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%); 
            padding: 1rem; 
            border-radius: 10px;
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
            word-break: break-word;
            font-family: 'Consolas', 'Courier New', monospace;
        }

        .card-id-division {
            font-size: 16px;
            font-weight: 500;
            opacity: 0.9;
            word-break: break-word;
            font-family: 'Consolas', 'Courier New', monospace;
        }

        .card-id-footer {
            margin-top: auto;
        }

        .card-id-valid,
        .card-id-card-number {
            font-family: 'Consolas', 'Courier New', monospace;
        }

        .card-action {
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
        }

        .card-action:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-4px);
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

        .btn-dark {
            background: #000;
            border: 2px solid #000;
            border-radius: 10px;
            font-weight: 600;
            padding: 12px 28px;
            transition: all 0.3s ease;
        }

        .btn-dark:hover {
            background: #1f2937;
            border-color: #1f2937;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .btn-outline-dark {
            border: 2px solid #000;
            color: #000;
            border-radius: 10px;
            font-weight: 600;
            padding: 12px 28px;
            transition: all 0.3s ease;
        }

        .btn-outline-dark:hover {
            background: #000;
            color: white;
            transform: translateY(-2px);
        }

        .btn-light {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            color: #1f2937;
            font-weight: 600;
            border-radius: 8px;
        }

        .btn-light:hover {
            background: white;
            color: #000;
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 16px 20px;
        }

        @media (max-width: 768px) {
            .card-bank-value {
                font-size: 28px;
            }

            .card-bank {
                min-height: 180px;
            }

            .card-id {
                min-height: 200px;
            }

            .card-id .card-body {
                padding: 20px;
                gap: 10px;
            }

            .card-id-name {
                font-size: 20px;
            }

            .card-id-division {
                font-size: 14px;
            }

            .card-id-logo i {
                font-size: 32px;
            }

            .card-id-card-number {
                font-size: 18px !important;
            }
            
            /* Responsive untuk foto ID Card */
            .id-card-img, .id-card-img-placeholder {
                width: 50px;
                height: 60px;
            }
        }
    </style>
@endpush

@push('scripts')
    {{-- QRCode Lib --}}
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // --- SCRIPT UNTUK GENERATE QR CODE DI DASHBOARD ---
            @if(Auth::user()->qr_code_value)
                const qrValue = "{{ Auth::user()->qr_code_value }}";
                
                // 1. Tampilkan di Card Kecil (Dashboard)
                new QRCode(document.getElementById("dashboard-qrcode"), {
                    text: qrValue,
                    width: 64,
                    height: 64,
                    colorDark : "#000000",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.H
                });

                // 2. Tampilkan di Modal Besar (Saat diklik)
                var qrModal = document.getElementById('qrModal');
                qrModal.addEventListener('show.bs.modal', function (event) {
                    var qrContainer = document.getElementById('qrcode-modal-display');
                    qrContainer.innerHTML = ''; // Reset isi agar tidak double
                    new QRCode(qrContainer, {
                        text: qrValue,
                        width: 256, // Ukuran lebih besar untuk scan mudah
                        height: 256,
                    });
                });
            @endif

            // --- SCRIPT CHART (TETAP SAMA) ---
            const ctx = document.getElementById('attendancePieChart').getContext('2d');

            @if (auth()->user()->role == 'admin')
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Tepat Waktu', 'Terlambat', 'Pulang Cepat', 'Pending', 'Tidak Hadir'],
                        datasets: [{
                            data: [
                                {{ $stats['on_time'] }}, {{ $stats['late'] }},
                                {{ $stats['early'] }}, {{ $stats['pending'] }},
                                {{ $stats['absent'] }}
                            ],
                            backgroundColor: ['#00d25b', '#ffab00', '#fc424a', '#0090e7',
                                '#8c94a3'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right'
                            },
                            title: {
                                display: true,
                                text: 'Statistik Kehadiran Hari Ini'
                            }
                        },
                        cutout: '70%'
                    }
                });
            @elseif (auth()->user()->role == 'audit')
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['Terverifikasi', 'Pending', 'Terlambat'],
                        datasets: [{
                            data: [{{ $stats['verified'] }}, {{ $stats['pending'] }},
                                {{ $stats['late'] }}
                            ],
                            backgroundColor: ['#00d25b', '#ffab00', '#fc424a'],
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
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '60%',
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            title: {
                                display: true,
                                text: 'Aktivitas Scan Hari Ini'
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
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            title: {
                                display: true,
                                text: 'Performa Absensi Bulan Ini'
                            }
                        }
                    }
                });
            @endif

            // --- SCRIPT MODAL FOTO PROFIL ---
            var profilePhotoModal = document.getElementById('profilePhotoModal');
            if (profilePhotoModal) {
                profilePhotoModal.addEventListener('show.bs.modal', function (event) {
                    // Tombol (img) yang mentrigger modal
                    var button = event.relatedTarget;
                    // Ambil info dari data-* attributes
                    var src = button.getAttribute('data-src');
                    // Update isi modal
                    var modalImg = document.getElementById('profileModalImageSrc');
                    modalImg.src = src;
                });
            }
        });
    </script>
@endpush