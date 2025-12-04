@extends('layout.master')

@section('title')
    @if (isset($employee))
        Riwayat Absensi - {{ $employee->name }}
    @else
        Riwayat Absensi Saya
    @endif
@endsection

@section('heading')
    @if (isset($employee))
        <div class="d-flex align-items-center flex-wrap">
            <a href="{{ route('team.branch.detail', $employee->branch_id) }}" class="text-decoration-none text-muted me-3">
                <i class="mdi mdi-arrow-left fs-4"></i>
            </a>
            <div>
                <h5 class="mb-0 d-inline-block text-truncate" style="max-width: 250px;">Riwayat: {{ $employee->name }}</h5>
                <br>
                <small class="text-muted">
                    {{ $employee->division->name ?? '-' }} - {{ $employee->branch->name ?? '-' }}
                </small>
            </div>
        </div>
    @else
        Riwayat Absensi Saya
    @endif
@endsection

@push('styles')
    <style>
        /* Base Styles */
        .verification-badge { font-size: 0.7rem; padding: 0.35rem 0.5rem; white-space: nowrap; }
        .action-buttons .btn { padding: 0.25rem 0.5rem; font-size: 0.8rem; }
        .verified-check { color: #28a745; font-size: 1.1rem; }
        .edited-info { color: #0dcaf0; font-size: 1.1rem; }
        .pending-clock { color: #ffc107; font-size: 1.1rem; }
        .audit-mode-badge { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 0.8rem; }
        .audit-photo-thumb { width: 40px; height: 40px; object-fit: cover; border: 2px solid #e2e8f0; border-radius: 8px; }
        .img-clickable { cursor: pointer; transition: transform 0.2s; }
        .img-clickable:hover { transform: scale(1.1); opacity: 0.9; }

        /* Responsive Tweaks */
        @media (max-width: 576px) {
            .card-title { font-size: 1rem; }
            .badge { font-size: 0.7rem; }
            .btn-sm { font-size: 0.75rem; padding: 0.25rem 0.5rem; }
            /* Summary Cards Font Size */
            .summary-label { font-size: 0.75rem; }
            .summary-value { font-size: 1.5rem !important; }
        }

        /* Smartwatch / Ultra Small Devices (< 350px) */
        @media (max-width: 350px) {
            .summary-value { font-size: 1.2rem !important; }
            .col-6 { width: 100%; } /* Stack cards on watches */
            .btn { width: 100%; margin-bottom: 5px; } /* Stack buttons */
            .form-select { margin-bottom: 5px; }
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            
            {{-- HEADER MODE AUDIT (Responsive Alert) --}}
            @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                <div class="alert alert-info border-0 shadow-sm mb-4 p-3">
                    <div class="d-flex align-items-start">
                        <i class="mdi mdi-shield-account fs-2 me-3 mt-1"></i>
                        <div>
                            <h6 class="alert-heading mb-1 fw-bold">Mode Audit</h6>
                            <p class="mb-0 small">Anda dapat memverifikasi & mengoreksi absensi.</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- FILTER & NAVIGASI (Fully Responsive Flexbox) --}}
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body p-3">
                    {{-- Container Flex yang membungkus Kiri, Tengah, Kanan --}}
                    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                        
                        {{-- 1. NAVIGASI KIRI (Tombol <) --}}
                        <div class="order-1 order-md-1 align-self-start align-self-md-center">
                            @php
                                $prevParams = ['month' => $prevMonth, 'year' => $prevYear];
                                if(isset($employee)) {
                                    $prevRoute = route('team.branch.employee.history', array_merge(['branchId' => $employee->branch_id, 'employeeId' => $employee->id], $prevParams));
                                } else {
                                    $prevRoute = route('attendance.history', $prevParams);
                                }
                            @endphp
                            <a href="{{ $prevRoute }}" class="btn btn-outline-secondary btn-sm" title="Bulan Sebelumnya">
                                <i class="mdi mdi-chevron-left"></i> <span class="d-none d-sm-inline">Prev</span>
                            </a>
                        </div>

                        {{-- 2. FORM FILTER TENGAH (Label + Selects) --}}
                        <div class="order-3 order-md-2 w-100 w-md-auto">
                            <form action="{{ isset($employee) ? route('team.branch.employee.history', ['branchId' => $employee->branch_id, 'employeeId' => $employee->id]) : route('attendance.history') }}"
                                method="GET" class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                                
                                <div class="d-flex align-items-center bg-light rounded px-2 py-1">
                                    <i class="mdi mdi-calendar-month me-2 text-primary"></i>
                                    <span class="fw-bold small d-none d-sm-inline me-2">Periode:</span>
                                    
                                    {{-- Select Bulan --}}
                                    <select name="month" class="form-select form-select-sm border-0 bg-transparent py-0 ps-0 pe-4" style="width: auto; box-shadow: none;" onchange="this.form.submit()">
                                        @foreach (range(1, 12) as $m)
                                            <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                            </option>
                                        @endforeach
                                    </select>

                                    {{-- Select Tahun --}}
                                    <select name="year" class="form-select form-select-sm border-0 bg-transparent py-0 ps-0" style="width: auto; box-shadow: none; border-left: 1px solid #ccc !important; border-radius: 0;" onchange="this.form.submit()">
                                        @php
                                            $startYear = 2025;
                                            $currentYear = date('Y');
                                            $endYear = $currentYear + 1; 
                                        @endphp
                                        @for ($y = $startYear; $y <= $endYear; $y++)
                                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </form>
                        </div>

                        {{-- 3. NAVIGASI KANAN (Tombol > & Export) --}}
                        <div class="order-2 order-md-3 d-flex gap-2 align-self-end align-self-md-center">
                            {{-- Tombol PDF --}}
                            <a href="{{ route('attendance.export.pdf', ['month' => $selectedMonth, 'year' => $selectedYear, 'employeeId' => isset($employee) ? $employee->id : null]) }}" 
                               class="btn btn-danger btn-sm text-white">
                                <i class="mdi mdi-file-pdf-box"></i> <span class="d-none d-sm-inline">PDF</span>
                             </a>

                             {{-- Tombol Next > --}}
                            @php
                                $nextParams = ['month' => $nextMonth, 'year' => $nextYear];
                                if(isset($employee)) {
                                    $nextRoute = route('team.branch.employee.history', array_merge(['branchId' => $employee->branch_id, 'employeeId' => $employee->id], $nextParams));
                                } else {
                                    $nextRoute = route('attendance.history', $nextParams);
                                }
                            @endphp
                            <a href="{{ $nextRoute }}" class="btn btn-outline-secondary btn-sm" title="Bulan Selanjutnya">
                                <span class="d-none d-sm-inline">Next</span> <i class="mdi mdi-chevron-right"></i>
                            </a>
                        </div>

                    </div>
                </div>
            </div>

            {{-- RINGKASAN BULANAN (Grid System Optimized for Mobile) --}}
            <div class="row g-2 mb-3">
                {{-- Gunakan col-6 (setengah layar HP) bukan col-md-3 (seperempat layar PC) agar enak dilihat di HP --}}
                <div class="col-6 col-md-3">
                    <div class="card bg-primary text-white border-0 shadow-sm h-100">
                        <div class="card-body py-3 text-center d-flex flex-column justify-content-center">
                            <h6 class="text-white-50 mb-1 fw-bold summary-label">Total Hari</h6>
                            <h2 class="fw-bold text-white mb-0 summary-value">{{ $summary['total'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card bg-success text-white border-0 shadow-sm h-100">
                        <div class="card-body py-3 text-center d-flex flex-column justify-content-center">
                            <h6 class="text-white-50 mb-1 fw-bold summary-label">Hadir / WFH</h6>
                            <h2 class="fw-bold text-white mb-0 summary-value">{{ $summary['hadir'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card bg-info text-white border-0 shadow-sm h-100">
                        <div class="card-body py-3 text-center d-flex flex-column justify-content-center">
                            <h6 class="text-white-50 mb-1 fw-bold summary-label">Sakit & Izin</h6>
                            <h2 class="fw-bold text-white mb-0 summary-value">{{ $summary['sakit'] + $summary['izin'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card bg-secondary text-white border-0 shadow-sm h-100">
                        <div class="card-body py-3 text-center d-flex flex-column justify-content-center">
                            <h6 class="text-white-50 mb-1 fw-bold summary-label">Alpha / Bolos</h6>
                            <h2 class="fw-bold text-white mb-0 summary-value">{{ $summary['alpha'] }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Baris 2 (Detail Kecil) --}}
            <div class="row g-2 mb-3">
                <div class="col-4">
                    <div class="card bg-warning bg-opacity-10 border-warning shadow-sm h-100">
                        <div class="card-body py-2 text-center px-1">
                            <small class="fw-bold text-dark d-block lh-1" style="font-size: 0.7rem;">TERLAMBAT</small>
                            <span class="fs-5 fw-bold text-dark">{{ $summary['telat'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card bg-danger bg-opacity-10 border-danger shadow-sm h-100">
                        <div class="card-body py-2 text-center px-1">
                            <small class="fw-bold text-danger d-block lh-1" style="font-size: 0.7rem;">PULANG CEPAT</small>
                            <span class="fs-5 fw-bold text-danger">{{ $summary['pulang_cepat'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card bg-dark bg-opacity-10 border-dark shadow-sm h-100">
                        <div class="card-body py-2 text-center px-1">
                            <small class="fw-bold text-dark d-block lh-1" style="font-size: 0.7rem;">MENUNGGU</small>
                            <span class="fs-5 fw-bold text-dark">{{ $summary['pending'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABEL DATA --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0 p-md-3">
                    <div class="d-flex justify-content-between align-items-center p-3 p-md-0 mb-md-3">
                        <h5 class="card-title mb-0 fw-bold">
                            Detail Absensi
                            <span class="d-block d-md-inline text-muted small fw-normal">
                                {{ date('M Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) }}
                            </span>
                        </h5>

                        @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                            <span class="badge audit-mode-badge fs-6 py-2">
                                <i class="mdi mdi-shield-check"></i> <span class="d-none d-sm-inline">Audit</span>
                            </span>
                        @endif
                    </div>

                    @if ($history->count() > 0)
                        <div class="table-responsive">
                            {{-- text-nowrap PENTING agar tabel tidak hancur di HP (scroll samping) --}}
                            <table class="table table-hover align-middle text-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Tanggal</th>
                                        <th>Masuk</th>
                                        <th>Bukti Msk</th>
                                        <th>Pulang</th>
                                        <th>Bukti Plg</th>
                                        <th>Status</th>
                                        <th>Verifikasi</th>
                                        <th>Audit</th>
                                        <th>Metode</th>
                                        @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                                            <th width="100" class="text-end pe-3">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($history as $att)
                                        <tr>
                                            {{-- TANGGAL --}}
                                            <td class="ps-3">
                                                <div class="fw-bold text-dark">{{ $att->check_in_time->format('d M') }}</div>
                                                <small class="text-muted" style="font-size: 0.75rem;">{{ $att->check_in_time->format('l') }}</small>
                                            </td>

                                            {{-- JAM MASUK --}}
                                            <td>
                                                <div class="d-flex flex-column">
                                                    @php
                                                        $scheduleTime = null;
                                                        if ($att->user && $att->user->check_in_start) {
                                                            $scheduleTime = $att->user->check_in_start;
                                                        } elseif ($att->user && $att->user->workSchedule) {
                                                            $scheduleTime = $att->user->workSchedule->start_time;
                                                        }

                                                        $isRealLate = false;
                                                        $lateMinutes = 0;
                                                        if ($scheduleTime) {
                                                            $actualStr = $att->check_in_time->format('H:i');
                                                            $scheduleStr = \Carbon\Carbon::parse($scheduleTime)->format('H:i');
                                                            if ($actualStr > $scheduleStr) {
                                                                $isRealLate = true;
                                                                $actualCarbon = \Carbon\Carbon::parse($actualStr);
                                                                $scheduleCarbon = \Carbon\Carbon::parse($scheduleStr);
                                                                $lateMinutes = $scheduleCarbon->diffInMinutes($actualCarbon);
                                                            }
                                                        }
                                                    @endphp

                                                    <div class="d-flex align-items-center mb-1">
                                                        <span class="fw-bold fs-6 {{ $isRealLate ? 'text-danger' : 'text-dark' }}">
                                                            {{ $att->check_in_time->format('H:i') }}
                                                        </span>
                                                        @if ($isRealLate)
                                                            <span class="badge bg-danger ms-1 rounded-pill" style="font-size: 0.65rem;">
                                                                +{{ $lateMinutes }}m
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if ($scheduleTime)
                                                        <small class="text-muted d-none d-sm-block" style="font-size: 0.7rem;">
                                                            Jadwal: {{ \Carbon\Carbon::parse($scheduleTime)->format('H:i') }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- FOTO MASUK --}}
                                            <td>
                                                @php
                                                    $displayPhoto = null;
                                                    if ($att->photo_path) $displayPhoto = asset('storage/' . $att->photo_path);
                                                    elseif ($att->leaveRequest && $att->leaveRequest->file_proof) $displayPhoto = asset('storage/' . $att->leaveRequest->file_proof);

                                                    $labelMasuk = 'Masuk';
                                                    if ($att->leaveRequest) $labelMasuk = 'Izin/Sakit';
                                                    elseif ($att->presence_status) $labelMasuk = $att->presence_status;
                                                    
                                                    if (!empty($att->notes)) {
                                                        $cleanNote = $att->notes;
                                                        if (str_contains($cleanNote, '| Pulang:')) $cleanNote = trim(explode('| Pulang:', $cleanNote)[0]);
                                                        if (!empty($cleanNote)) $labelMasuk = $cleanNote;
                                                    }
                                                @endphp

                                                @if ($displayPhoto)
                                                    <div class="d-flex align-items-center gap-2">
                                                        <img src="{{ $displayPhoto }}" alt="In"
                                                            class="rounded shadow-sm img-clickable"
                                                            style="width: 36px; height: 36px; object-fit: cover; border: 1px solid #dee2e6;"
                                                            data-bs-toggle="modal" data-bs-target="#imagePreviewModal"
                                                            data-img-src="{{ $displayPhoto }}"
                                                            data-img-title="Bukti Masuk - {{ $att->check_in_time->format('d M Y') }}">
                                                        
                                                        <small class="text-muted d-none d-md-block text-truncate" style="max-width: 80px; font-size: 0.7rem;">
                                                            {{ $labelMasuk }}
                                                        </small>
                                                    </div>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>

                                            {{-- JAM PULANG --}}
                                            <td>
                                                @if ($att->check_out_time)
                                                    <div class="d-flex align-items-center">
                                                        <span class="fw-bold fs-6 {{ $att->is_early_checkout ? 'text-warning' : 'text-dark' }}">
                                                            {{ $att->check_out_time->format('H:i') }}
                                                        </span>
                                                        @if ($att->is_early_checkout)
                                                            <i class="mdi mdi-alert-circle text-warning ms-1" title="Pulang Cepat"></i>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="badge bg-light text-secondary border">--:--</span>
                                                @endif
                                            </td>

                                            {{-- FOTO PULANG --}}
                                            <td>
                                                @if ($att->photo_out_path)
                                                    <img src="{{ asset('storage/' . $att->photo_out_path) }}"
                                                        alt="Out" class="rounded shadow-sm img-clickable"
                                                        style="width: 36px; height: 36px; object-fit: cover; border: 1px solid #dee2e6;"
                                                        data-bs-toggle="modal" data-bs-target="#imagePreviewModal"
                                                        data-img-src="{{ asset('storage/' . $att->photo_out_path) }}"
                                                        data-img-title="Foto Pulang - {{ $att->check_in_time->format('d M Y') }}">
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>

                                            {{-- STATUS KEHADIRAN --}}
                                            <td>
                                                @if ($att->presence_status)
                                                    @php
                                                        $statusLower = strtolower($att->presence_status);
                                                        $badgeColor = match (true) {
                                                            $statusLower == 'masuk' => 'bg-success',
                                                            str_contains($statusLower, 'wfh') || str_contains($statusLower, 'dinas') => 'bg-info',
                                                            str_contains($statusLower, 'telat') => 'bg-warning text-dark',
                                                            $statusLower == 'sakit' => 'bg-primary',
                                                            $statusLower == 'cuti' || $statusLower == 'izin' => 'bg-secondary',
                                                            $statusLower == 'alpha' => 'bg-danger',
                                                            default => 'bg-dark',
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $badgeColor }} rounded-1 fw-normal">
                                                        {{ ucwords($att->presence_status) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary rounded-1 fw-normal">-</span>
                                                @endif
                                            </td>

                                            {{-- VERIFIKASI --}}
                                            <td>
                                                @if ($att->status == 'verified')
                                                    @if ($att->presence_status == 'Alpha')
                                                        <i class="mdi mdi-robot text-danger fs-5" title="System Auto"></i>
                                                    @elseif($att->attendance_type == 'manual')
                                                        <i class="mdi mdi-pencil-box-outline text-info fs-5" title="Dikoreksi"></i>
                                                    @else
                                                        <i class="mdi mdi-check-circle text-success fs-5" title="Terverifikasi"></i>
                                                    @endif
                                                @elseif($att->status == 'pending_verification')
                                                    <i class="mdi mdi-clock text-warning fs-5" title="Menunggu"></i>
                                                @elseif($att->status == 'rejected')
                                                    <i class="mdi mdi-close-circle text-danger fs-5" title="Ditolak"></i>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>

                                            {{-- BUKTI AUDIT --}}
                                            <td>
                                                @if ($att->audit_photo_path)
                                                    <img src="{{ asset('storage/' . $att->audit_photo_path) }}"
                                                        alt="Audit" class="rounded shadow-sm img-clickable"
                                                        style="width: 36px; height: 36px; object-fit: cover; border: 2px solid #667eea;"
                                                        data-bs-toggle="modal" data-bs-target="#imagePreviewModal"
                                                        data-img-src="{{ asset('storage/' . $att->audit_photo_path) }}"
                                                        data-img-title="Bukti Audit">
                                                @else
                                                    -
                                                @endif
                                            </td>

                                            {{-- METODE --}}
                                            <td>
                                                @if ($att->attendance_type == 'scan')
                                                    <i class="mdi mdi-qrcode-scan text-primary fs-5" title="Scan QR"></i>
                                                @elseif($att->attendance_type == 'self')
                                                    <i class="mdi mdi-camera-front-variant text-info fs-5" title="Selfie"></i>
                                                @elseif($att->attendance_type == 'system')
                                                    <i class="mdi mdi-server text-danger fs-5" title="System"></i>
                                                @elseif($att->attendance_type == 'manual')
                                                    <i class="mdi mdi-pencil text-warning fs-5" title="Manual Edit"></i>
                                                @elseif($att->attendance_type == 'leave')
                                                    <i class="mdi mdi-file-document text-secondary fs-5" title="Izin/Cuti"></i>
                                                @endif
                                            </td>

                                            {{-- AKSI AUDIT --}}
                                            @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                                                <td class="text-end pe-3">
                                                    <div class="dropdown">
                                                        <button class="btn btn-light btn-sm p-1" type="button" data-bs-toggle="dropdown">
                                                            <i class="mdi mdi-dots-vertical fs-5"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                            @if ($att->status != 'verified')
                                                                <li>
                                                                    <a class="dropdown-item text-success" href="#" data-bs-toggle="modal" data-bs-target="#verifyModal{{ $att->id }}">
                                                                        <i class="mdi mdi-check me-2"></i> Verifikasi
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            
                                                            @if (auth()->user()->role == 'audit')
                                                                <li>
                                                                    <a class="dropdown-item text-info" href="#" data-bs-toggle="modal" data-bs-target="#editAuditModal{{ $att->id }}">
                                                                        <i class="mdi mdi-pencil me-2"></i> Koreksi
                                                                    </a>
                                                                </li>
                                                            @endif

                                                            @if ($att->status != 'verified')
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $att->id }}">
                                                                        <i class="mdi mdi-close me-2"></i> Tolak
                                                                    </a>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 px-3">
                            <i class="mdi mdi-calendar-remove display-1 text-light-gray mb-3"></i>
                            <h6 class="text-muted">Tidak ada data absensi</h6>
                            <p class="text-muted small">Periode: {{ date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================= --}}
    {{-- MODAL PREVIEW IMAGE (Fully Responsive) --}}
    {{-- ============================================================= --}}
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down modal-lg">
            <div class="modal-content bg-dark border-0">
                <div class="modal-header border-0 p-2 position-absolute top-0 end-0 z-index-10">
                    <button type="button" class="btn-close btn-close-white bg-white rounded-circle p-2 opacity-100" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 d-flex align-items-center justify-content-center" style="min-height: 200px;">
                    <img src="" id="previewImage" class="img-fluid" alt="Preview" style="max-height: 90vh; max-width: 100%;">
                </div>
                <div class="modal-footer border-0 p-2 justify-content-center">
                    <h6 class="modal-title text-white small mb-0" id="imagePreviewModalLabel">Preview</h6>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================= --}}
    {{-- MODALS ACTION (Audit/Admin) --}}
    {{-- ============================================================= --}}
    @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin') && $history->count() > 0)
        @foreach ($history as $att)
            {{-- 1. Modal Verifikasi --}}
            <div class="modal fade" id="verifyModal{{ $att->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fs-6"><i class="mdi mdi-check-circle text-success me-2"></i> Verifikasi Absensi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('audit.verify.attendance', $att->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf @method('PUT')
                            <div class="modal-body">
                                <div class="alert alert-light border small py-2 mb-3">
                                    <div><strong>Nama:</strong> {{ $employee->name }}</div>
                                    <div><strong>Tgl:</strong> {{ $att->check_in_time->format('d M Y') }}</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Status Kehadiran</label>
                                    <select name="presence_status" class="form-select" required>
                                        <option value="Masuk" {{ $att->presence_status == 'Masuk' ? 'selected' : '' }}>✅ Masuk</option>
                                        <option value="Sakit" {{ $att->presence_status == 'Sakit' ? 'selected' : '' }}>🤒 Sakit</option>
                                        <option value="Cuti" {{ $att->presence_status == 'Cuti' ? 'selected' : '' }}>🏖️ Cuti</option>
                                        <option value="Alpha" {{ $att->presence_status == 'Alpha' ? 'selected' : '' }}>❌ Alpha</option>
                                        <option value="Telat" {{ $att->presence_status == 'Telat' ? 'selected' : '' }}>⏰ Telat</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Upload Bukti (Opsional)</label>
                                    <input type="file" name="audit_photo" class="form-control form-control-sm">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small fw-bold">Catatan Audit</label>
                                    <textarea name="audit_note" class="form-control" rows="2">{{ $att->audit_note }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-sm btn-success">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 2. MODAL EDIT --}}
            @if (auth()->user()->role == 'audit')
                <div class="modal fade" id="editAuditModal{{ $att->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title fs-6 text-white"><i class="mdi mdi-pencil-box me-2"></i> Koreksi Data</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('audit.update.attendance', $att->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <div class="modal-body">
                                    <div class="alert alert-warning small py-2 d-flex align-items-center mb-3">
                                        <i class="mdi mdi-alert fs-5 me-2"></i>
                                        <span>Perubahan data ini akan tercatat dalam sistem audit.</span>
                                    </div>

                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <label class="form-label small text-muted">Jam Masuk</label>
                                            <input type="time" name="check_in_time" class="form-control form-control-sm" value="{{ $att->check_in_time->format('H:i') }}" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small text-muted">Jam Pulang</label>
                                            <input type="time" name="check_out_time" class="form-control form-control-sm" value="{{ $att->check_out_time ? $att->check_out_time->format('H:i') : '' }}">
                                        </div>
                                    </div>

                                    <div class="row g-2 mb-3">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small text-muted">Status</label>
                                            <select name="presence_status" class="form-select form-select-sm" required>
                                                <option value="Masuk" {{ $att->presence_status == 'Masuk' ? 'selected' : '' }}>✅ Masuk</option>
                                                <option value="Izin Telat" {{ $att->presence_status == 'Izin Telat' ? 'selected' : '' }}>📨 Izin Telat</option>
                                                <option value="WFH / Dinas Luar" {{ stripos($att->presence_status, 'WFH') !== false ? 'selected' : '' }}>🏠 WFH / Dinas</option>
                                                <option value="Telat" {{ $att->presence_status == 'Telat' ? 'selected' : '' }}>⏰ Telat (Hadir)</option>
                                                <option value="Alpha" {{ $att->presence_status == 'Alpha' ? 'selected' : '' }}>❌ Alpha</option>
                                                <option value="Izin" {{ $att->presence_status == 'Izin' ? 'selected' : '' }}>📝 Izin</option>
                                                <option value="Sakit" {{ $att->presence_status == 'Sakit' ? 'selected' : '' }}>🤒 Sakit</option>
                                                <option value="Cuti" {{ $att->presence_status == 'Cuti' ? 'selected' : '' }}>🏖️ Cuti</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small text-muted">Verifikasi</label>
                                            <select name="status" class="form-select form-select-sm" required>
                                                <option value="verified" {{ $att->status == 'verified' ? 'selected' : '' }}>Disetujui</option>
                                                <option value="pending_verification" {{ $att->status == 'pending_verification' ? 'selected' : '' }}>Menunggu</option>
                                                <option value="rejected" {{ $att->status == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small text-muted">Bukti Koreksi</label>
                                        <input type="file" name="audit_photo" class="form-control form-control-sm" accept="image/*">
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label small text-muted">Alasan Koreksi</label>
                                        <textarea name="audit_note" class="form-control form-control-sm" rows="2" placeholder="Wajib diisi...">{{ $att->audit_note }}</textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-sm btn-info text-white">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 3. Modal Tolak --}}
            <div class="modal fade" id="rejectModal{{ $att->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fs-6 text-danger"><i class="mdi mdi-close-circle me-2"></i> Tolak Absensi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('audit.reject', $att->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <div class="modal-body">
                                <p class="small">Yakin ingin menolak absensi <strong>{{ $employee->name }}</strong> pada tanggal {{ $att->check_in_time->format('d M') }}?</p>
                                <textarea name="rejection_reason" class="form-control form-control-sm" rows="2" placeholder="Alasan penolakan..." required></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-sm btn-danger">Tolak</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var imagePreviewModal = document.getElementById('imagePreviewModal');
            if (imagePreviewModal) {
                imagePreviewModal.addEventListener('show.bs.modal', function(event) {
                    var button = event.relatedTarget;
                    var imgSrc = button.getAttribute('data-img-src');
                    var imgTitle = button.getAttribute('data-img-title');
                    var modalTitle = imagePreviewModal.querySelector('.modal-title');
                    var modalImg = imagePreviewModal.querySelector('#previewImage');
                    if(modalTitle) modalTitle.textContent = imgTitle;
                    if(modalImg) modalImg.src = imgSrc;
                });
            }
        });
    </script>
@endpush