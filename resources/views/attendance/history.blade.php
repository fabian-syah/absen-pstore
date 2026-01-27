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

            {{-- NAVIGASI KARYAWAN DALAM CABANG --}}
            @if(isset($employeeCount) && $employeeCount > 1)
                <div class="d-flex align-items-center gap-2">
                    <small class="text-muted me-2">
                        {{ $currentEmployeeIndex ?? 1 }} / {{ $employeeCount }}
                    </small>

                    @if($prevEmployee)
                        <a href="{{ route('team.branch.employee.history', ['branchId' => $branchId, 'employeeId' => $prevEmployee->id, 'month' => $selectedMonth, 'year' => $selectedYear]) }}"
                            class="btn btn-sm btn-outline-primary rounded-pill shadow-sm px-3 d-flex align-items-center gap-2"
                            title="Karyawan Sebelumnya: {{ $prevEmployee->name }}">
                            <i class="mdi mdi-chevron-left"></i>
                            <span class="d-none d-md-inline small fw-bold">{{ $prevEmployee->name }}</span>
                        </a>
                    @else
                        <button class="btn btn-sm btn-outline-secondary rounded-circle" disabled>
                            <i class="mdi mdi-chevron-left"></i>
                        </button>
                    @endif

                    @if($nextEmployee)
                        <a href="{{ route('team.branch.employee.history', ['branchId' => $branchId, 'employeeId' => $nextEmployee->id, 'month' => $selectedMonth, 'year' => $selectedYear]) }}"
                            class="btn btn-sm btn-outline-primary rounded-pill shadow-sm px-3 d-flex align-items-center gap-2"
                            title="Karyawan Berikutnya: {{ $nextEmployee->name }}">
                            <span class="d-none d-md-inline small fw-bold">{{ $nextEmployee->name }}</span>
                            <i class="mdi mdi-chevron-right"></i>
                        </a>
                    @else
                        <button class="btn btn-sm btn-outline-secondary rounded-circle" disabled>
                            <i class="mdi mdi-chevron-right"></i>
                        </button>
                    @endif
                </div>
            @endif
        </div>
    @else
        <h4 class="mb-0 fw-bold">Riwayat Absensi Saya</h4>
    @endif
@endsection

@push('styles')
    <style>
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

        .border-dashed-start {
            border-left: 2px dashed #dee2e6;
            padding-left: 10px;
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
            padding-top: 0.2rem;
            padding-bottom: 0.2rem;
            height: auto;
        }

        .note-box {
            font-size: 0.65rem;
            line-height: 1.2;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 4px;
            margin-top: 4px;
            max-width: 120px;
            word-wrap: break-word;
            color: #495057;
        }

        .btn-outline-primary:hover span {
            color: #fff !important;
        }

        .late-approval-badge {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            font-size: 0.6rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            animation: pulse-warning 2s infinite;
        }

        @keyframes pulse-warning {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4);
            }

            50% {
                box-shadow: 0 0 0 4px rgba(245, 158, 11, 0);
            }
        }

        .approver-info {
            font-size: 0.65rem;
            color: #6b7280;
            margin-top: 2px;
        }

        .approver-name {
            font-weight: 600;
            color: #374151;
        }

        .table tbody tr {
            transition: all 0.15s ease;
        }

        .table tbody tr:hover {
            background-color: #f0f4ff !important;
        }

        .bg-success .text-white-50,
        .bg-primary .text-white-50,
        .bg-info .text-white-50,
        .bg-secondary .text-white-50 {
            color: rgba(255, 255, 255, 0.75) !important;
        }

        .verifier-box .badge {
            min-width: 38px;
            text-align: center;
        }

        .verifier-box .lh-sm span.d-block {
            color: #1f2937 !important;
        }

        .verifier-box .lh-sm small {
            color: #6b7280 !important;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
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

            <div class="card mb-4 border-0 shadow-sm rounded-4">
                <div class="card-body py-3">
                    <div class="row align-items-center justify-content-between g-3">
                        <div class="col-auto">
                            @php
                                $prevParams = ['month' => $prevMonth, 'year' => $prevYear];
                                if (isset($employee)) {
                                    $prevRoute = route('team.branch.employee.history', array_merge(['branchId' => $employee->branch_id, 'employeeId' => $employee->id], $prevParams));
                                } else {
                                    $prevRoute = route('attendance.history', $prevParams);
                                }
                            @endphp
                            <a href="{{ $prevRoute }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold">
                                <i class="mdi mdi-chevron-left me-1"></i> Prev
                            </a>
                        </div>

                        <div class="col-auto">
                            <form
                                action="{{ isset($employee) ? route('team.branch.employee.history', ['branchId' => $employee->branch_id, 'employeeId' => $employee->id]) : route('attendance.history') }}"
                                method="GET" class="row align-items-center gx-2">
                                <div class="col-auto d-none d-md-block">
                                    <label class="fw-bold mb-0 text-muted small text-uppercase"
                                        style="font-size: 0.65rem;">Periode:</label>
                                </div>
                                <div class="col-auto">
                                    <select name="month"
                                        class="form-select form-select-sm form-select-custom rounded-pill px-3 shadow-sm"
                                        style="width: 105px;" onchange="this.form.submit()">
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

                        <div class="col-auto d-flex gap-2">
                            @php
                                $nextParams = ['month' => $nextMonth, 'year' => $nextYear];
                                if (isset($employee)) {
                                    $nextRoute = route('team.branch.employee.history', array_merge(['branchId' => $employee->branch_id, 'employeeId' => $employee->id], $nextParams));
                                } else {
                                    $nextRoute = route('attendance.history', $nextParams);
                                }
                            @endphp
                            <a href="{{ $nextRoute }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold">
                                Next <i class="mdi mdi-chevron-right ms-1"></i>
                            </a>
                            <a href="{{ route('attendance.export.pdf', ['month' => $selectedMonth, 'year' => $selectedYear, 'employeeId' => isset($employee) ? $employee->id : null]) }}"
                                class="btn btn-danger btn-sm text-white ms-2 rounded-pill px-3 shadow-sm">
                                <i class="mdi mdi-file-pdf-box me-1"></i> PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>

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
                <div class="col-4">
                    <div class="p-2 rounded-3 text-center border"
                        style="background: #fff3cd; color: #856404; border-color: #ffeeba!important;"><small
                            class="fw-bold d-block text-uppercase" style="font-size: 0.65rem;">Terlambat</small><span
                            class="fs-5 fw-bold">{{ $summary['telat'] }}</span></div>
                </div>
                <div class="col-4">
                    <div class="p-2 rounded-3 text-center border"
                        style="background: #f8d7da; color: #721c24; border-color: #f5c6cb!important;"><small
                            class="fw-bold d-block text-uppercase" style="font-size: 0.65rem;">Plg Cepat</small><span
                            class="fs-5 fw-bold">{{ $summary['pulang_cepat'] }}</span></div>
                </div>
                <div class="col-4">
                    <div class="p-2 rounded-3 text-center border"
                        style="background: #e2e3e5; color: #383d41; border-color: #d6d8db!important;"><small
                            class="fw-bold d-block text-uppercase" style="font-size: 0.65rem;">Pending</small><span
                            class="fs-5 fw-bold">{{ $summary['pending'] }}</span></div>
                </div>
            </div>

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

                    @if ($history->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Tanggal</th>
                                        <th>Jam Masuk</th>
                                        <th>Foto</th>
                                        <th class="border-start">Jam Pulang</th>
                                        <th>Foto</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Lokasi (In/Out)</th>
                                        <th>Verifikasi & Petugas</th>
                                        <th class="text-center">Metode</th>
                                        @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                                            <th class="text-end pe-4">Aksi Audit</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($history as $att)
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
                                                <div class="d-flex flex-column">
                                                    <div class="d-flex align-items-center">
                                                        <i
                                                            class="mdi mdi-login-variant {{ $att->is_calculated_late ? 'text-danger' : 'text-success' }} me-2 fs-5"></i>
                                                        <div>
                                                            <span
                                                                class="fw-bold fs-6 {{ $att->is_calculated_late ? 'text-danger' : 'text-dark' }}">
                                                                {{ $att->check_in_time->format('H:i') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted ps-4"
                                                        style="font-size: 0.7rem;">{{ $fixedScheduleIn ? 'Jadwal: ' . \Carbon\Carbon::parse($fixedScheduleIn)->format('H:i') : '- Fleksibel -' }}</small>
                                                </div>
                                            </td>

                                            <td>
                                                @php
                                                    $displayPhoto = null;
                                                    if ($att->photo_path) {
                                                        $displayPhoto = asset('storage/' . $att->photo_path);
                                                    } elseif ($att->leaveRequest && $att->leaveRequest->file_proof) {
                                                        $displayPhoto = asset('storage/' . $att->leaveRequest->file_proof);
                                                    }
                                                @endphp
                                                <div class="d-flex flex-column align-items-start">
                                                    @if ($displayPhoto)
                                                        <img src="{{ $displayPhoto }}" class="rounded-3 shadow-sm img-clickable border"
                                                            style="width: 45px; height: 45px; object-fit: cover;" data-bs-toggle="modal"
                                                            data-bs-target="#imagePreviewModal" data-img-src="{{ $displayPhoto }}"
                                                            data-img-title="Masuk">
                                                    @else
                                                        <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted border"
                                                            style="width: 45px; height: 45px;"><i class="mdi mdi-image-off"></i></div>
                                                    @endif
                                                </div>
                                            </td>

                                            <td class="border-start bg-light bg-opacity-25">
                                                <div class="d-flex flex-column">
                                                    @if ($att->check_out_time)
                                                        <div class="d-flex align-items-center">
                                                            <i class="mdi mdi-logout-variant text-primary me-2 fs-5"></i>
                                                            <span
                                                                class="fw-bold fs-6 text-dark">{{ $att->check_out_time->format('H:i') }}</span>
                                                        </div>
                                                    @else
                                                        <span class="badge bg-light text-secondary border">Belum Pulang</span>
                                                    @endif
                                                    <small class="text-muted ps-4"
                                                        style="font-size: 0.7rem;">{{ $fixedScheduleOut ? 'Jadwal: ' . \Carbon\Carbon::parse($fixedScheduleOut)->format('H:i') : '- Fleksibel -' }}</small>
                                                </div>
                                            </td>

                                            <td class="bg-light bg-opacity-25">
                                                @if ($att->photo_out_path)
                                                    <img src="{{ asset('storage/' . $att->photo_out_path) }}"
                                                        class="rounded-3 shadow-sm img-clickable border"
                                                        style="width: 45px; height: 45px; object-fit: cover;" data-bs-toggle="modal"
                                                        data-bs-target="#imagePreviewModal"
                                                        data-img-src="{{ asset('storage/' . $att->photo_out_path) }}"
                                                        data-img-title="Pulang">
                                                @else
                                                    <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted border"
                                                        style="width: 45px; height: 45px;"><i class="mdi mdi-image-off"></i></div>
                                                @endif
                                            </td>

                                            <td class="text-center">
                                                @php
                                                    $statusLower = strtolower($att->presence_status ?? '');
                                                    $badgeClass = match (true) {
                                                        $statusLower == 'masuk' => 'bg-success',
                                                        str_contains($statusLower, 'wfh') || str_contains($statusLower, 'dinas') => 'bg-info',
                                                        str_contains($statusLower, 'telat') => 'bg-warning text-dark',
                                                        $statusLower == 'sakit' => 'bg-primary',
                                                        in_array($statusLower, ['cuti', 'izin']) => 'bg-secondary',
                                                        $statusLower == 'alpha' => 'bg-danger',
                                                        default => 'bg-dark',
                                                    };
                                                @endphp
                                                <span
                                                    class="badge {{ $badgeClass }} verification-badge shadow-sm">{{ ucwords($att->presence_status ?? 'Pending') }}</span>
                                            </td>

                                            <td class="text-center">
                                                <div class="d-flex flex-column align-items-center gap-1">
                                                    @if ($att->latitude && $att->longitude)
                                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $att->latitude }},{{ $att->longitude }}"
                                                            target="_blank"
                                                            class="btn btn-outline-info btn-sm btn-icon rounded-circle"><i
                                                                class="mdi mdi-map-marker-radius"></i></a>
                                                    @endif
                                                </div>
                                            </td>

                                            <td>
                                                <div class="verifier-box d-flex align-items-center">
                                                    <div class="lh-sm">
                                                        <span
                                                            class="d-block fw-bold text-dark small">{{ $att->verifier->name ?? ($att->scanner->name ?? 'System') }}</span>
                                                        <small class="text-muted" style="font-size: 0.65rem;">Petugas
                                                            Verifikasi</small>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                <i
                                                    class="mdi {{ $att->attendance_type == 'scan' ? 'mdi-qrcode-scan text-primary' : ($att->attendance_type == 'self' ? 'mdi-camera-front-variant text-info' : 'mdi-cog text-secondary') }} fs-4"></i>
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
                                                        {{-- INPUT MANUAL UNTUK DATA ALPHA --}}
                                                        <button type="button"
                                                            class="btn btn-primary btn-sm text-white shadow-sm rounded-pill"
                                                            data-bs-toggle="modal" data-bs-target="#createAuditModal{{ $loop->index }}">
                                                            <i class="mdi mdi-plus-box"></i> Input
                                                        </button>
                                                        <div class="modal fade text-start" id="createAuditModal{{ $loop->index }}"
                                                            tabindex="-1">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content rounded-4 border-0">
                                                                    <div class="modal-header bg-primary text-white">
                                                                        <h5 class="modal-title fw-bold">Input Absensi Manual
                                                                            ({{ $att->check_in_time->format('d M Y') }})</h5>
                                                                        <button type="button" class="btn-close btn-close-white"
                                                                            data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <form action="{{ route('audit.store.attendance') }}" method="POST"
                                                                        enctype="multipart/form-data">
                                                                        @csrf
                                                                        <div class="modal-body p-4">
                                                                            <input type="hidden" name="user_id" value="{{ $employee->id }}">
                                                                            <input type="hidden" name="date"
                                                                                value="{{ $att->check_in_time->format('Y-m-d') }}">
                                                                            <div class="row g-3 mb-3">
                                                                                <div class="col-6">
                                                                                    <label class="form-label small fw-bold">Jam
                                                                                        Masuk</label>
                                                                                    <input type="time" name="check_in_time"
                                                                                        class="form-control" required>
                                                                                </div>
                                                                                <div class="col-6">
                                                                                    <label class="form-label small fw-bold">Jam
                                                                                        Pulang</label>
                                                                                    <input type="time" name="check_out_time"
                                                                                        class="form-control">
                                                                                </div>
                                                                            </div>
                                                                            <div class="mb-3">
                                                                                <label class="form-label small fw-bold">Status
                                                                                    Kehadiran</label>
                                                                                <select name="presence_status" class="form-select" required>
                                                                                    <option value="Masuk">✅ Masuk</option>
                                                                                    <option value="WFH">🏠 WFH</option>
                                                                                    <option value="Dinas Luar">🚗 Dinas Luar</option>
                                                                                    <option value="Sakit">🤒 Sakit</option>
                                                                                    <option value="Izin">📝 Izin</option>
                                                                                </select>
                                                                            </div>
                                                                            <div class="mb-3">
                                                                                <label class="form-label small fw-bold">Bukti Audit &
                                                                                    Catatan</label>
                                                                                <input type="file" name="audit_photo"
                                                                                    class="form-control mb-2">
                                                                                <textarea name="audit_note" class="form-control" rows="2"
                                                                                    placeholder="Alasan..."></textarea>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer border-top-0 pt-0">
                                                                            <button type="submit"
                                                                                class="btn btn-primary rounded-pill px-4 fw-bold">Simpan
                                                                                Absensi</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                                style="width: 80px; height: 80px;"><i class="mdi mdi-calendar-blank text-muted"
                                    style="font-size: 40px;"></i></div>
                            <h5 class="text-muted fw-bold">Belum Ada Data</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PREVIEW IMAGE --}}
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0 shadow-none">
                <div class="modal-header border-0 p-0 mb-2">
                    <h5 class="modal-title text-white fw-bold" id="imagePreviewModalLabel">Preview Foto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img src="" id="previewImage" class="img-fluid rounded-4 shadow-lg border border-3 border-white"
                        style="max-height: 85vh; width: auto;">
                </div>
            </div>
        </div>
    </div>

    {{-- MODALS AUDIT --}}
    @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
        @foreach ($history as $att)
            @if ($att->id)
                <div class="modal fade" id="verifyModal{{ $att->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content rounded-4 border-0">
                            <div class="modal-header bg-success text-white rounded-top-4">
                                <h5 class="modal-title fw-bold">Verifikasi Absensi</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('audit.verify.attendance', $att->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <div class="modal-body p-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-uppercase">Status</label>
                                        <select name="presence_status" class="form-select" required>
                                            <option value="Masuk" {{ ($att->presence_status == 'Masuk') ? 'selected' : '' }}>✅ Masuk
                                            </option>
                                            <option value="WFH" {{ ($att->presence_status == 'WFH') ? 'selected' : '' }}>🏠 WFH</option>
                                            <option value="Sakit" {{ ($att->presence_status == 'Sakit') ? 'selected' : '' }}>🤒 Sakit
                                            </option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small">Bukti Audit</label>
                                        <input type="file" name="audit_photo" class="form-control">
                                    </div>
                                    <textarea name="audit_note" class="form-control" rows="2"
                                        placeholder="Catatan audit...">{{ $att->audit_note }}</textarea>
                                </div>
                                <div class="modal-footer border-top-0 pt-0">
                                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">Verifikasi</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="editAuditModal{{ $att->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content rounded-4 border-0">
                            <div class="modal-header bg-info text-white rounded-top-4">
                                <h5 class="modal-title fw-bold">Koreksi Data</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('audit.update.attendance', $att->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <div class="modal-body p-4">
                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold">Jam Masuk</label>
                                            <input type="time" name="check_in_time" class="form-control"
                                                value="{{ $att->check_in_time->format('H:i') }}" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold">Jam Pulang</label>
                                            <input type="time" name="check_out_time" class="form-control"
                                                value="{{ $att->check_out_time ? $att->check_out_time->format('H:i') : '' }}">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Status</label>
                                        <select name="presence_status" class="form-select" required>
                                            <option value="Masuk" {{ $att->presence_status == 'Masuk' ? 'selected' : '' }}>✅ Masuk
                                            </option>
                                            <option value="WFH" {{ $att->presence_status == 'WFH' ? 'selected' : '' }}>🏠 WFH</option>
                                            <option value="Dinas Luar" {{ $att->presence_status == 'Dinas Luar' ? 'selected' : '' }}>🚗
                                                Dinas Luar</option>
                                            <option value="Sakit" {{ $att->presence_status == 'Sakit' ? 'selected' : '' }}>🤒 Sakit
                                            </option>
                                            <option value="Izin" {{ $att->presence_status == 'Izin' ? 'selected' : '' }}>📝 Izin</option>
                                            <option value="Alpha" {{ $att->presence_status == 'Alpha' ? 'selected' : '' }}>❌ Alpha
                                            </option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-danger">Bukti Koreksi (Wajib)</label>
                                        <input type="file" name="audit_photo" class="form-control mb-2" {{ $att->audit_photo_path ? '' : 'required' }}>
                                    </div>
                                    <textarea name="audit_note" class="form-control" rows="2"
                                        placeholder="Alasan koreksi...">{{ $att->audit_note }}</textarea>
                                    <input type="hidden" name="status" value="verified">
                                </div>
                                <div class="modal-footer border-top-0 pt-0">
                                    <button type="submit" class="btn btn-info text-white rounded-pill px-4 fw-bold">Simpan</button>
                                </div>
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