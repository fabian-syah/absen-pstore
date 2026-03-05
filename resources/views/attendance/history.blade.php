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
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <a href="{{ route('team.branch.detail', $employee->branch_id) }}"
                    class="btn btn-sm btn-light btn-icon me-3 rounded-circle shadow-sm">
                    <i class="mdi mdi-arrow-left text-primary"></i>
                </a>
                <div>
                    <h4 class="mb-0 fw-bold">Riwayat Absensi: {{ $employee->name }}</h4>
                    <small class="text-muted">
                        <i class="mdi mdi-domain me-1"></i> {{ $employee->division->name ?? '-' }} | <i
                            class="mdi mdi-office-building me-1"></i> {{ $employee->branch->name ?? '-' }}
                    </small>
                </div>
            </div>
        </div>
    @else
        <h4 class="mb-0 fw-bold">Riwayat Absensi Saya</h4>
    @endif
@endsection

@push('styles')
    <style>
        .bg-teal {
            background-color: #20c997 !important;
            color: white;
        }

        .bg-purple {
            background-color: #6f42c1 !important;
            color: white;
        }

        .bg-orange {
            background-color: #fd7e14 !important;
            color: white;
        }

        .verification-badge {
            font-size: 0.7rem;
            padding: 0.35rem 0.6rem;
            border-radius: 6px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .action-buttons .btn {
            padding: 0.35rem 0.6rem;
        }

        .audit-mode-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }

        .audit-photo-thumb {
            width: 45px;
            height: 45px;
            object-fit: cover;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .img-clickable {
            cursor: pointer;
            transition: transform 0.2s ease-in-out;
        }

        .img-clickable:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table thead th {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            background-color: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
            white-space: nowrap;
            color: #495057;
        }

        .verifier-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 6px 10px;
            border: 1px solid #e9ecef;
        }

        .form-select-custom {
            background-color: #fff !important;
            color: #333 !important;
            border: 1px solid #ced4da !important;
            font-weight: 600;
            height: 38px;
        }

        /* Navigasi Karyawan Hover Fix */
        .btn-nav-emp {
            border: 1px solid #4e73df;
            color: #4e73df;
            transition: all 0.2s;
        }

        .btn-nav-emp:hover:not([disabled]) {
            background-color: #4e73df !important;
            color: #ffffff !important;
        }

        .btn-nav-emp:hover span {
            color: #ffffff !important;
        }

        /* Late Approval Styling */
        .late-approval-container {
            margin-top: 5px;
            padding: 4px 8px;
            background-color: #fff4e5;
            border-left: 3px solid #ff9800;
            border-radius: 4px;
        }

        .late-approval-badge {
            color: #e65100;
            font-size: 0.65rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .approver-name-tag {
            font-size: 0.6rem;
            color: #666;
            margin-top: 2px;
            font-style: italic;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            {{-- HEADER MODE AUDIT --}}
            @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                <div class="alert alert-info border-0 shadow-sm mb-4 d-flex align-items-center p-3 rounded-3"
                    style="background: #e3f2fd; color: #0d47a1;">
                    <i class="mdi mdi-shield-account display-6 me-3"></i>
                    <div>
                        <h5 class="alert-heading fw-bold mb-1">Mode Cross-Check Audit</h5>
                        <p class="mb-0 small opacity-75">Anda dapat memverifikasi, mengoreksi, dan mengubah status kehadiran
                            karyawan.</p>
                    </div>
                </div>
            @endif

            {{-- FILTER & NAVIGASI --}}
            <div class="card mb-4 border-0 shadow-sm rounded-4">
                <div class="card-body py-3">
                    <div class="row align-items-center justify-content-between g-3">

                        {{-- NAVIGASI KARYAWAN (KIRI) --}}
                        <div class="col-auto">
                            @if(isset($employeeCount) && $employeeCount > 1)
                                <div class="d-flex align-items-center gap-2">
                                    @if($prevEmployee)
                                        <a href="{{ route('team.branch.employee.history', ['branchId' => $branchId, 'employeeId' => $prevEmployee->id, 'month' => $selectedMonth, 'year' => $selectedYear]) }}"
                                            class="btn btn-sm btn-nav-emp rounded-pill px-3 d-flex align-items-center gap-2 shadow-sm">
                                            <i class="mdi mdi-chevron-left"></i>
                                            <span class="d-none d-lg-inline small fw-bold">{{ $prevEmployee->name }}</span>
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary rounded-circle" disabled><i
                                                class="mdi mdi-chevron-left"></i></button>
                                    @endif

                                    <div class="px-2 text-center" style="min-width: 60px;">
                                        <small class="text-muted fw-bold d-block" style="font-size: 0.6rem;">URUTAN</small>
                                        <span class="fw-bold text-primary small">{{ $currentEmployeeIndex }} /
                                            {{ $employeeCount }}</span>
                                    </div>

                                    @if($nextEmployee)
                                        <a href="{{ route('team.branch.employee.history', ['branchId' => $branchId, 'employeeId' => $nextEmployee->id, 'month' => $selectedMonth, 'year' => $selectedYear]) }}"
                                            class="btn btn-sm btn-nav-emp rounded-pill px-3 d-flex align-items-center gap-2 shadow-sm">
                                            <span class="d-none d-lg-inline small fw-bold">{{ $nextEmployee->name }}</span>
                                            <i class="mdi mdi-chevron-right"></i>
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary rounded-circle" disabled><i
                                                class="mdi mdi-chevron-right"></i></button>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- FORM FILTER PERIODE (TENGAH) --}}
                        <div class="col-auto">
                            <form
                                action="{{ isset($employee) ? route('team.branch.employee.history', ['branchId' => $employee->branch_id, 'employeeId' => $employee->id]) : route('attendance.history') }}"
                                method="GET" class="row align-items-center gx-2">
                                <div class="col-auto">
                                    <select name="month"
                                        class="form-select form-select-sm form-select-custom rounded-pill px-3 shadow-sm"
                                        style="width: 110px;" onchange="this.form.submit()">
                                        @foreach (range(1, 12) as $m)
                                            <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <select name="year"
                                        class="form-select form-select-sm form-select-custom rounded-pill px-3 shadow-sm"
                                        style="width: 85px;" onchange="this.form.submit()">
                                        @for ($y = 2025; $y <= date('Y') + 1; $y++)
                                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </form>
                        </div>

                        {{-- EXPORT PDF (KANAN) --}}
                        <div class="col-auto">
                            <a href="{{ route('attendance.export.pdf', ['month' => $selectedMonth, 'year' => $selectedYear, 'employeeId' => isset($employee) ? $employee->id : null]) }}"
                                class="btn btn-danger btn-sm text-white rounded-pill px-3 shadow-sm">
                                <i class="mdi mdi-file-pdf-box me-1"></i> PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARDS SUMMARY --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card bg-primary text-white border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                            <h6 class="text-white-50 mb-1 small text-uppercase fw-bold">Total Hari</h6>
                            <h2 class="fw-bold text-white mb-0 display-6">{{ $summary['total'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card bg-success text-white border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                            <h6 class="text-white-50 mb-1 small text-uppercase fw-bold">Hadir / WFH</h6>
                            <h2 class="fw-bold text-white mb-0 display-6">{{ $summary['present'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card bg-info text-white border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                            <h6 class="text-white-50 mb-1 small text-uppercase fw-bold">Sakit & Izin</h6>
                            <h2 class="fw-bold text-white mb-0 display-6">{{ $summary['sakit'] + $summary['izin'] }}</h2>
                            <small class="text-white-50" style="font-size: 0.7rem;">{{ $summary['sakit'] }} Sakit,
                                {{ $summary['izin'] }} Izin</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card bg-secondary text-white border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                            <h6 class="text-white-50 mb-1 small text-uppercase fw-bold">Alpha</h6>
                            <h2 class="fw-bold text-white mb-0 display-6">{{ $summary['alpha'] }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-3">
                    <div class="p-2 rounded-3 text-center border"
                        style="background: #fff3cd; color: #856404; border-color: #ffeeba!important;"><small
                            class="fw-bold d-block text-uppercase" style="font-size: 0.65rem;">Terlambat</small><span
                            class="fs-5 fw-bold">{{ $summary['telat'] }}</span></div>
                </div>
                <div class="col-3">
                    <div class="p-2 rounded-3 text-center border"
                        style="background: #f8d7da; color: #721c24; border-color: #f5c6cb!important;"><small
                            class="fw-bold d-block text-uppercase" style="font-size: 0.65rem;">Plg Cepat</small><span
                            class="fs-5 fw-bold">{{ $summary['pulang_cepat'] }}</span></div>
                </div>
                <div class="col-3">
                    <div class="p-2 rounded-3 text-center border"
                        style="background: #d4edda; color: #155724; border-color: #c3e6cb!important;"><small
                            class="fw-bold d-block text-uppercase" style="font-size: 0.65rem;">Libur</small><span
                            class="fs-5 fw-bold">{{ $summary['libur'] }}</span></div>
                </div>
                <div class="col-3">
                    <div class="p-2 rounded-3 text-center border"
                        style="background: #e2e3e5; color: #383d41; border-color: #d6d8db!important;"><small
                            class="fw-bold d-block text-uppercase" style="font-size: 0.65rem;">Pending</small><span
                            class="fs-5 fw-bold">{{ $summary['pending'] }}</span></div>
                </div>
            </div>

            {{-- DETAIL TABEL --}}
            <div class="card shadow-sm rounded-4 border-0">
                <div class="card-body p-0">
                    <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold text-primary"><i class="mdi mdi-history me-2"></i> Detail Absensi
                        </h5>
                        @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                            <span class="badge audit-mode-badge px-3 py-2 shadow-sm rounded-pill"><i
                                    class="mdi mdi-shield-check me-1"></i> Mode Audit</span>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Tanggal</th>
                                    <th>Masuk</th>
                                    <th>Lokasi</th>
                                    <th>Foto</th>
                                    <th class="border-start">Pulang</th>
                                    <th>Lokasi</th>
                                    <th>Foto</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Bukti Izin / Audit</th>
                                    <th class="text-center">Verifikasi & Petugas</th>
                                    <th class="text-center">Metode</th>
                                    @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                                        <th class="text-end pe-4">Aksi Audit</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($history as $att)
                                    @php
                                        $fixedScheduleIn = $att->scheduled_check_in ?? ($att->user->check_in_start ?? ($att->user->workSchedule->start_time ?? null));
                                        $fixedScheduleOut = $att->scheduled_check_out ?? ($att->user->check_out_start ?? ($att->user->workSchedule->end_time ?? null));

                                        $isLateApproval = $att->verified_by_user_id && $att->updated_at && $att->updated_at->gt($att->check_in_time->endOfDay());
                                        $approvalDelay = $isLateApproval ? $att->check_in_time->startOfDay()->diffInDays($att->updated_at->startOfDay()) : 0;
                                        $approverName = $att->verifier->name ?? null;
                                    @endphp
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="fw-bold text-dark">{{ $att->check_in_time->format('d M Y') }}</div>
                                            <small class="text-muted text-uppercase fw-bold"
                                                style="font-size: 0.7rem;">{{ $att->check_in_time->format('l') }}</small>
                                        </td>

                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i
                                                    class="mdi mdi-login-variant {{ $att->is_calculated_late ? 'text-danger' : 'text-success' }} me-2 fs-5"></i>
                                                <span
                                                    class="fw-bold {{ $att->is_calculated_late ? 'text-danger' : 'text-dark' }}">{{ $att->check_in_time->format('H:i') }}</span>
                                            </div>
                                            <small class="text-muted" style="font-size: 0.65rem;">Jadwal:
                                                {{ $fixedScheduleIn ? \Carbon\Carbon::parse($fixedScheduleIn)->format('H:i') : '-' }}</small>
                                        </td>

                                        <td>
                                            @if($att->attendance_type == 'scan' || $att->scanner_user_id)
                                                <span class="badge bg-light text-dark border rounded-pill px-2 fw-bold"
                                                    style="font-size: 0.65rem;">
                                                    <i class="mdi mdi-office-building text-primary"></i> Di Kantor
                                                </span>
                                            @elseif($att->latitude && $att->longitude)
                                                <a href="https://maps.google.com/?q={{ $att->latitude }},{{ $att->longitude }}"
                                                    target="_blank" class="btn btn-xs btn-info text-white rounded-pill px-2 fw-bold"
                                                    style="font-size: 0.65rem;">
                                                    <i class="mdi mdi-map-marker-radius"></i> Maps
                                                </a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>

                                        <td>
                                            @php
                                                $inPhoto = $att->photo_path ? asset('storage/' . $att->photo_path) : null;
                                            @endphp
                                            @if ($inPhoto)
                                                <div class="text-center">
                                                    <img src="{{ $inPhoto }}"
                                                        class="rounded-3 shadow-sm img-clickable border border-success"
                                                        style="width: 40px; height: 40px; object-fit: cover;" data-bs-toggle="modal"
                                                        data-bs-target="#imagePreviewModal" data-img-src="{{ $inPhoto }}"
                                                        data-img-title="Foto Absen Masuk">
                                                </div>
                                            @else
                                                <div class="rounded-3 bg-light d-flex align-items-center justify-content-center border mx-auto"
                                                    style="width: 40px; height: 40px;"><i class="mdi mdi-image-off text-muted"></i>
                                                </div>
                                            @endif
                                        </td>

                                        <td class="border-start bg-light bg-opacity-25">
                                            @if ($att->check_out_time)
                                                <div class="d-flex align-items-center">
                                                    <i class="mdi mdi-logout-variant text-primary me-2 fs-5"></i>
                                                    <span class="fw-bold text-dark">{{ $att->check_out_time->format('H:i') }}</span>
                                                </div>
                                                <small class="text-muted" style="font-size: 0.65rem;">Jadwal:
                                                    {{ $fixedScheduleOut ? \Carbon\Carbon::parse($fixedScheduleOut)->format('H:i') : '-' }}</small>
                                            @else
                                                <span class="badge bg-soft-secondary text-secondary border small">Belum Out</span>
                                            @endif
                                        </td>

                                        <td class="bg-light bg-opacity-25">
                                            @if($att->attendance_type == 'scan' || $att->scanner_user_id)
                                                <span class="badge bg-light text-dark border rounded-pill px-2 fw-bold"
                                                    style="font-size: 0.65rem;">
                                                    <i class="mdi mdi-office-building text-primary"></i> Di Kantor
                                                </span>
                                            @elseif($att->check_out_time && $att->latitude_out && $att->longitude_out)
                                                <a href="https://maps.google.com/?q={{ $att->latitude_out }},{{ $att->longitude_out }}"
                                                    target="_blank"
                                                    class="btn btn-xs btn-primary text-white rounded-pill px-2 fw-bold"
                                                    style="font-size: 0.65rem;">
                                                    <i class="mdi mdi-map-marker-radius"></i> Maps
                                                </a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>

                                        <td class="bg-light bg-opacity-25">
                                            @if ($att->photo_out_path)
                                                <img src="{{ asset('storage/' . $att->photo_out_path) }}"
                                                    class="rounded-3 shadow-sm img-clickable border"
                                                    style="width: 40px; height: 40px; object-fit: cover;" data-bs-toggle="modal"
                                                    data-bs-target="#imagePreviewModal"
                                                    data-img-src="{{ asset('storage/' . $att->photo_out_path) }}"
                                                    data-img-title="Foto Pulang">
                                            @else
                                                <div class="rounded-3 bg-light d-flex align-items-center justify-content-center border"
                                                    style="width: 40px; height: 40px;"><i class="mdi mdi-image-off text-muted"></i>
                                                </div>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            @php
                                                $statusLwr = strtolower($att->presence_status ?? '');
                                                $bClass = match (true) {
                                                    $statusLwr == 'masuk' => 'bg-success',
                                                    str_contains($statusLwr, 'wfh') || str_contains($statusLwr, 'dinas') => 'bg-info text-dark',
                                                    str_contains($statusLwr, 'telat') => 'bg-orange',
                                                    $statusLwr == 'sakit' => 'bg-primary',
                                                    $statusLwr == 'izin' => 'bg-warning text-dark',
                                                    $statusLwr == 'cuti' => 'bg-purple',
                                                    $statusLwr == 'libur' => 'bg-teal',
                                                    default => 'bg-danger',
                                                };
                                            @endphp
                                            @php
                                                $displayText = ucwords($att->presence_status ?? 'Pending');
                                                if ($displayText === 'Izin Telat') {
                                                    $displayText = 'Telat Hadir';
                                                }
                                            @endphp
                                            <span
                                                class="badge {{ $bClass }} verification-badge shadow-sm">{{ $displayText }}</span>
                                        </td>

                                        <td class="text-center">
                                            @php
                                                $leavePhoto = ($att->leaveRequest && $att->leaveRequest->file_proof) ? asset('storage/' . $att->leaveRequest->file_proof) : null;
                                            @endphp
                                            <div class="d-flex flex-column align-items-center gap-1">
                                                @if ($leavePhoto)
                                                    <div class="text-center">
                                                        <img src="{{ $leavePhoto }}"
                                                            class="rounded-3 shadow-sm img-clickable border border-warning"
                                                            style="width: 40px; height: 40px; object-fit: cover;"
                                                            data-bs-toggle="modal" data-bs-target="#imagePreviewModal"
                                                            data-img-src="{{ $leavePhoto }}" data-img-title="Bukti Izin">
                                                        <small class="d-block text-warning fw-bold mt-1"
                                                            style="font-size: 0.55rem;">BUKTI IZIN</small>
                                                    </div>
                                                @endif

                                                @if ($att->audit_photo_path)
                                                    <div class="text-center">
                                                        <img src="{{ asset('storage/' . $att->audit_photo_path) }}"
                                                            class="rounded-3 shadow-sm img-clickable border border-info"
                                                            style="width: 40px; height: 40px; object-fit: cover;"
                                                            data-bs-toggle="modal" data-bs-target="#imagePreviewModal"
                                                            data-img-src="{{ asset('storage/' . $att->audit_photo_path) }}"
                                                            data-img-title="Bukti Audit">
                                                        <small class="d-block text-info fw-bold mt-1"
                                                            style="font-size: 0.55rem;">BUKTI AUDIT</small>
                                                    </div>
                                                @endif

                                                @if (!$leavePhoto && !$att->audit_photo_path)
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </div>
                                        </td>

                                        <td>
                                            <div class="verifier-box">
                                                @if($att->verifier)
                                                    <span class="d-block fw-bold text-success small"><i
                                                            class="mdi mdi-check-decagram"></i> {{ $att->verifier->name }}</span>
                                                @endif
                                                @if($att->scanner)
                                                    <span class="d-block text-muted small" style="font-size: 0.65rem;">Scan:
                                                        {{ $att->scanner->name }}</span>
                                                @endif
                                                @if(!$att->verifier && !$att->scanner)
                                                    <span class="d-block fw-bold text-dark small">System</span>
                                                @endif
                                                <small class="text-muted d-none" style="font-size: 0.6rem;">Petugas
                                                    Verifikasi</small>

                                                @if ($isLateApproval)
                                                    <div class="late-approval-container shadow-sm">
                                                        <div class="late-approval-badge">
                                                            <i class="mdi mdi-clock-alert"></i> TELAT APPROVE (+{{ $approvalDelay }}
                                                            Hari)
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            <i
                                                class="mdi {{ $att->attendance_type == 'scan' ? 'mdi-qrcode-scan text-primary' : ($att->attendance_type == 'self' ? 'mdi-camera-front-variant text-info' : ($att->attendance_type == 'manual' ? 'mdi-pencil-box-outline text-warning' : 'mdi-cog text-secondary')) }} fs-4"></i>
                                        </td>

                                        @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                                            <td class="text-end pe-4">
                                                @if ($att->id)
                                                    <div class="btn-group btn-group-sm shadow-sm">
                                                        @if ($att->status != 'verified')
                                                            <button type="button" class="btn btn-success text-white" data-bs-toggle="modal"
                                                                data-bs-target="#verifyModal{{ $att->id }}"><i
                                                                    class="mdi mdi-check"></i></button>
                                                        @endif
                                                        <button type="button" class="btn btn-info text-white" data-bs-toggle="modal"
                                                            data-bs-target="#editAuditModal{{ $att->id }}"><i
                                                                class="mdi mdi-pencil"></i></button>
                                                    </div>
                                                @else
                                                    <button type="button" class="btn btn-info btn-sm text-white shadow-sm px-3"
                                                        data-bs-toggle="modal" data-bs-target="#createAuditModal{{ $loop->index }}"><i
                                                            class="mdi mdi-pencil"></i> Edit</button>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5">Belum ada riwayat absensi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PREVIEW IMAGE --}}
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0 shadow-none">
                <div class="modal-header border-0 p-0 mb-2">
                    <h5 class="modal-title text-white fw-bold">Preview Foto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img src="" id="previewImage" class="img-fluid rounded-4 shadow-lg border border-3 border-white"
                        style="max-height: 85vh;">
                </div>
            </div>
        </div>
    </div>

    {{-- MODALS AUDIT --}}
    @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
        @foreach ($history as $index => $att)
            @if (!$att->id)
                {{-- Modal Input Manual --}}
                <div class="modal fade" id="createAuditModal{{ $index }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content rounded-4 border-0">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title fw-bold">Input / Koreksi Absensi</h5><button type="button"
                                    class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('audit.store.attendance') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-body p-4">
                                    <input type="hidden" name="user_id" value="{{ $employee->id }}">
                                    <input type="hidden" name="date" value="{{ $att->check_in_time->format('Y-m-d') }}">
                                    <div class="row g-3 mb-3">
                                        <div class="col-6"><label class="form-label small fw-bold">Jam Masuk</label><input type="time"
                                                name="check_in_time" class="form-control"></div>
                                        <div class="col-6"><label class="form-label small fw-bold">Jam Pulang</label><input type="time"
                                                name="check_out_time" class="form-control"></div>
                                    </div>
                                    <div class="mb-3"><label class="form-label small fw-bold">Status</label>
                                        <select name="presence_status" class="form-select" required>
                                            <option value="Masuk">✅ Masuk</option>
                                            <option value="WFH">🏠 WFH</option>
                                            <!-- <option value="Dinas Luar">🚗 Dinas Luar</option> -->
                                            <option value="Sakit">🤒 Sakit</option>
                                            <option value="Izin">📝 Izin</option>
                                            <option value="Cuti">🏖️ Cuti</option>
                                            <option value="Alpha">❌ Alpha</option>
                                        </select>
                                    </div>
                                    <div class="mb-3"><label class="form-label small fw-bold">Foto Bukti</label><input type="file"
                                            name="audit_photo" class="form-control"></div>
                                    <textarea name="audit_note" class="form-control" placeholder="Alasan..."></textarea>
                                </div>
                                <div class="modal-footer pt-0"><button type="submit"
                                        class="btn btn-info text-white rounded-pill px-4 fw-bold">Simpan</button></div>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                {{-- Modal Verifikasi --}}
                <div class="modal fade" id="verifyModal{{ $att->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content rounded-4 border-0">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title fw-bold">Verifikasi Absensi</h5><button type="button"
                                    class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('audit.verify.attendance', $att->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <div class="modal-body p-4">
                                    <div class="mb-3"><label class="form-label small fw-bold">Ubah Status</label>
                                        @if($att->latitude && $att->longitude)
                                            <div class="mb-2">
                                                <a href="https://maps.google.com/?q={{ $att->latitude }},{{ $att->longitude }}"
                                                    target="_blank" class="btn btn-xs btn-info text-white rounded-pill px-2 fw-bold">
                                                    <i class="mdi mdi-map-marker-radius"></i> Lihat Lokasi di Maps
                                                </a>
                                            </div>
                                        @endif
                                        <select name="presence_status" class="form-select" required>
                                            <option value="Masuk" {{ $att->presence_status == 'Masuk' ? 'selected' : '' }}>✅ Masuk
                                            </option>
                                            <option value="WFH" {{ $att->presence_status == 'WFH' ? 'selected' : '' }}>🏠 WFH</option>
                                            <!-- <option value="Dinas Luar" {{ $att->presence_status == 'Dinas Luar' ? 'selected' : '' }}>🚗 Dinas Luar</option> -->
                                            <option value="Sakit" {{ $att->presence_status == 'Sakit' ? 'selected' : '' }}>🤒 Sakit
                                            </option>
                                            <option value="Izin" {{ $att->presence_status == 'Izin' ? 'selected' : '' }}>📝 Izin</option>
                                            <option value="Libur" {{ $att->presence_status == 'Libur' ? 'selected' : '' }}>📅 Libur (Off
                                                Day)</option>
                                            <option value="Cuti" {{ $att->presence_status == 'Cuti' ? 'selected' : '' }}>🏖️ Cuti</option>
                                        </select>
                                    </div>
                                    <div class="mb-3"><label class="form-label small">Foto Bukti Audit</label><input type="file"
                                            name="audit_photo" class="form-control"></div>
                                    <textarea name="audit_note" class="form-control"
                                        placeholder="Catatan audit...">{{ $att->audit_note }}</textarea>
                                </div>
                                <div class="modal-footer pt-0"><button type="submit"
                                        class="btn btn-success rounded-pill px-4 fw-bold">Verifikasi</button></div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Modal Koreksi Data --}}
                <div class="modal fade" id="editAuditModal{{ $att->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content rounded-4 border-0">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title fw-bold">Koreksi Data</h5><button type="button" class="btn-close btn-close-white"
                                    data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('audit.update.attendance', $att->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <div class="modal-body p-4">
                                    @if($att->latitude && $att->longitude)
                                        <div class="mb-3">
                                            <a href="https://maps.google.com/?q={{ $att->latitude }},{{ $att->longitude }}" target="_blank"
                                                class="btn btn-xs btn-info text-white rounded-pill px-2 fw-bold">
                                                <i class="mdi mdi-map-marker-radius"></i> Cek Lokasi Maps
                                            </a>
                                        </div>
                                    @endif
                                    <div class="row g-3 mb-3">
                                        <div class="col-6"><label class="form-label small fw-bold">Jam Masuk</label><input type="time"
                                                name="check_in_time" class="form-control"
                                                value="{{ $att->check_in_time->format('H:i') }}" required></div>
                                        <div class="col-6"><label class="form-label small fw-bold">Jam Pulang</label><input type="time"
                                                name="check_out_time" class="form-control"
                                                value="{{ $att->check_out_time ? $att->check_out_time->format('H:i') : '' }}"></div>
                                    </div>
                                    <div class="mb-3"><label class="form-label small fw-bold">Status</label>
                                        <select name="presence_status" class="form-select" required>
                                            <option value="Masuk" {{ $att->presence_status == 'Masuk' ? 'selected' : '' }}>✅ Masuk
                                            </option>
                                            <option value="WFH" {{ $att->presence_status == 'WFH' ? 'selected' : '' }}>🏠 WFH</option>
                                            <!-- <option value="Dinas Luar" {{ $att->presence_status == 'Dinas Luar' ? 'selected' : '' }}>🚗 Dinas Luar</option> -->
                                            <option value="Sakit" {{ $att->presence_status == 'Sakit' ? 'selected' : '' }}>🤒 Sakit
                                            </option>
                                            <option value="Izin" {{ $att->presence_status == 'Izin' ? 'selected' : '' }}>📝 Izin</option>
                                            <option value="Libur" {{ $att->presence_status == 'Libur' ? 'selected' : '' }}>📅 Libur (Off
                                                Day)</option>
                                            <option value="Cuti" {{ $att->presence_status == 'Cuti' ? 'selected' : '' }}>🏖️ Cuti</option>
                                            <option value="Alpha" {{ $att->presence_status == 'Alpha' ? 'selected' : '' }}>❌ Alpha
                                            </option>
                                        </select>
                                    </div>
                                    <div class="mb-3"><label class="form-label small fw-bold text-danger">Foto Bukti Audit</label><input
                                            type="file" name="audit_photo" class="form-control mb-2" accept="image/*"></div>
                                    <textarea name="audit_note" class="form-control"
                                        placeholder="Alasan koreksi...">{{ $att->audit_note }}</textarea>
                                    <input type="hidden" name="status" value="verified">
                                </div>
                                <div class="modal-footer pt-0"><button type="submit"
                                        class="btn btn-info text-white rounded-pill px-4 fw-bold">Simpan</button></div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var imagePreviewModal = document.getElementById('imagePreviewModal');
            if (imagePreviewModal) {
                imagePreviewModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var imgSrc = button.getAttribute('data-img-src');
                    var imgTitle = button.getAttribute('data-img-title');
                    var modalTitle = imagePreviewModal.querySelector('.modal-title');
                    var modalImg = imagePreviewModal.querySelector('#previewImage');
                    modalTitle.textContent = imgTitle;
                    modalImg.src = imgSrc;
                });
            }
        });
    </script>
@endpush