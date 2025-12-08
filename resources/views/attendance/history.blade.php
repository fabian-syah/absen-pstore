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
        <div class="d-flex align-items-center">
            <a href="{{ route('team.branch.detail', $employee->branch_id) }}" class="text-decoration-none text-muted me-3">
                <i class="mdi mdi-arrow-left"></i>
            </a>
            <div>
                Riwayat Absensi: {{ $employee->name }}
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
        .verification-badge { font-size: 0.75rem; padding: 0.35rem 0.7rem; }
        .action-buttons .btn { padding: 0.25rem 0.5rem; font-size: 0.8rem; }
        .verified-check { color: #28a745; font-size: 1.1rem; }
        .edited-info { color: #0dcaf0; font-size: 1.1rem; }
        .pending-clock { color: #ffc107; font-size: 1.1rem; }
        .audit-mode-badge { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .audit-photo-thumb { width: 40px; height: 40px; object-fit: cover; border: 2px solid #e2e8f0; border-radius: 8px; }
        
        /* Style untuk gambar yang bisa diklik */
        .img-clickable { cursor: pointer; transition: transform 0.2s; }
        .img-clickable:hover { transform: scale(1.1); opacity: 0.9; }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            {{-- HEADER MODE AUDIT --}}
            @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                <div class="alert alert-info border-0 shadow-sm mb-4">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-shield-account display-6 me-3"></i>
                        <div>
                            <h5 class="alert-heading mb-1">Mode Cross-Check Audit</h5>
                            <p class="mb-0">Anda dapat memverifikasi, mengoreksi, dan mengubah status kehadiran karyawan.</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- FILTER BULAN & TAHUN & NAVIGASI --}}
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="row align-items-center justify-content-between">
                        
                        {{-- NAVIGASI KIRI (BULAN LALU) --}}
                        <div class="col-auto">
                            @php
                                $prevParams = ['month' => $prevMonth, 'year' => $prevYear];
                                if(isset($employee)) {
                                    $prevRoute = route('team.branch.employee.history', array_merge(['branchId' => $employee->branch_id, 'employeeId' => $employee->id], $prevParams));
                                } else {
                                    $prevRoute = route('attendance.history', $prevParams);
                                }
                            @endphp
                            <a href="{{ $prevRoute }}" class="btn btn-outline-secondary btn-sm" title="Bulan Sebelumnya">
                                <i class="mdi mdi-chevron-left"></i>
                            </a>
                        </div>

                        {{-- FORM FILTER TENGAH --}}
                        <div class="col-auto">
                            <form action="{{ isset($employee) ? route('team.branch.employee.history', ['branchId' => $employee->branch_id, 'employeeId' => $employee->id]) : route('attendance.history') }}"
                                method="GET" class="row align-items-center gx-2">
                                
                                <div class="col-auto">
                                    <label class="fw-bold mb-0 me-2"><i class="mdi mdi-calendar-month"></i> Periode:</label>
                                </div>
                                <div class="col-auto">
                                    <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
                                        @foreach (range(1, 12) as $m)
                                            <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
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

                        {{-- NAVIGASI KANAN (BULAN DEPAN) --}}
                        <div class="col-auto d-flex gap-2">
                            @php
                                $nextParams = ['month' => $nextMonth, 'year' => $nextYear];
                                if(isset($employee)) {
                                    $nextRoute = route('team.branch.employee.history', array_merge(['branchId' => $employee->branch_id, 'employeeId' => $employee->id], $nextParams));
                                } else {
                                    $nextRoute = route('attendance.history', $nextParams);
                                }
                            @endphp
                            <a href="{{ $nextRoute }}" class="btn btn-outline-secondary btn-sm" title="Bulan Selanjutnya">
                                <i class="mdi mdi-chevron-right"></i>
                            </a>

                            {{-- TOMBOL EXPORT PDF --}}
                            <a href="{{ route('attendance.export.pdf', ['month' => $selectedMonth, 'year' => $selectedYear, 'employeeId' => isset($employee) ? $employee->id : null]) }}" 
                               class="btn btn-danger btn-sm text-white ms-2">
                                <i class="mdi mdi-file-pdf-box me-1"></i> Export PDF
                             </a>
                        </div>

                    </div>
                </div>
            </div>

            {{-- RINGKASAN BULANAN --}}
            <div class="row mb-3">
                {{-- Baris 1 --}}
                <div class="col-md-3 mb-2">
                    <div class="card bg-primary text-white border-0 shadow-sm">
                        <div class="card-body py-3 text-center">
                            <h6 class="text-white mb-1 fw-bold">Total Hari</h6>
                            <h2 class="fw-bold text-white mb-0">{{ $summary['total'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="card bg-success text-white border-0 shadow-sm">
                        <div class="card-body py-3 text-center">
                            <h6 class="text-white mb-1 fw-bold">Hadir / WFH</h6>
                            <h2 class="fw-bold text-white mb-0">{{ $summary['hadir'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="card bg-info text-white border-0 shadow-sm">
                        <div class="card-body py-3 text-center">
                            <h6 class="text-white mb-1 fw-bold">Sakit & Izin</h6>
                            <h2 class="fw-bold text-white mb-0">{{ $summary['sakit'] + $summary['izin'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="card bg-secondary text-white border-0 shadow-sm">
                        <div class="card-body py-3 text-center">
                            <h6 class="text-white mb-1 fw-bold">Alpha / Bolos</h6>
                            <h2 class="fw-bold text-white mb-0">{{ $summary['alpha'] }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Baris 2 --}}
            <div class="row mb-3">
                <div class="col-md-4 mb-2">
                    <div class="card bg-warning text-white border-0 shadow-sm">
                        <div class="card-body py-2 text-center">
                            <small class="fw-bold">Terlambat: {{ $summary['telat'] }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="card bg-danger text-white border-0 shadow-sm">
                        <div class="card-body py-2 text-center">
                            <small class="fw-bold">Pulang Cepat: {{ $summary['pulang_cepat'] }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="card bg-dark text-white border-0 shadow-sm">
                        <div class="card-body py-2 text-center">
                            <small class="fw-bold">Menunggu Verifikasi: {{ $summary['pending'] }}</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABEL DATA --}}
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">
                            Detail Absensi - {{ date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) }}
                            @if (isset($employee))
                                <br><small class="text-muted">Karyawan: {{ $employee->name }}</small>
                            @endif
                        </h4>

                        @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                            <span class="badge audit-mode-badge fs-6 py-2">
                                <i class="mdi mdi-shield-check me-1"></i> Mode Cross-Check Audit
                            </span>
                        @endif
                    </div>

                    @if ($history->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jam Masuk</th>
                                        <th>Foto / Bukti</th>
                                        <th>Jam Pulang</th>
                                        <th>Foto Pulang</th>
                                        <th>Status</th>
                                        <th>Verifikasi & Petugas</th>
                                        <th>Bukti Audit</th>
                                        <th>Metode</th>
                                        @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                                            <th width="120">Aksi Audit</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($history as $att)
                                        <tr>
                                            {{-- TANGGAL --}}
                                            <td>
                                                <div class="fw-bold">{{ $att->check_in_time->format('d M Y') }}</div>
                                                <small class="text-muted">{{ $att->check_in_time->format('l') }}</small>
                                            </td>

                                            {{-- JAM MASUK --}}
                                            <td>
                                                <div class="d-flex flex-column">
                                                    @php
                                                        $scheduleTime = null;
                                                        $cutoffDate = \Carbon\Carbon::create(2025, 12, 1);
                                                        if ($att->check_in_time->gte($cutoffDate)) {
                                                            if ($att->user && $att->user->check_in_start) {
                                                                $scheduleTime = $att->user->check_in_start;
                                                            } elseif ($att->user && $att->user->workSchedule) {
                                                                $scheduleTime = $att->user->workSchedule->start_time;
                                                            }
                                                        }

                                                        $isRealLate = false;
                                                        $lateStr = ''; 

                                                        if ($scheduleTime) {
                                                            $actualStr = $att->check_in_time->format('H:i');
                                                            $scheduleStr = \Carbon\Carbon::parse($scheduleTime)->format('H:i');
                                                            
                                                            if ($actualStr > $scheduleStr) {
                                                                $isRealLate = true;
                                                                $actualCarbon = \Carbon\Carbon::parse($actualStr);
                                                                $scheduleCarbon = \Carbon\Carbon::parse($scheduleStr);
                                                                $lateMinutes = $scheduleCarbon->diffInMinutes($actualCarbon);
                                                                
                                                                $hours = floor($lateMinutes / 60);
                                                                $mins = $lateMinutes % 60;
                                                                
                                                                if ($hours > 0) {
                                                                    $lateStr = "{$hours}j {$mins}m";
                                                                } else {
                                                                    $lateStr = "{$mins}m";
                                                                }
                                                            }
                                                        }
                                                    @endphp

                                                    <div class="d-flex align-items-center mb-1">
                                                        <i class="mdi mdi-login {{ $isRealLate ? 'text-danger' : 'text-success' }} me-2"></i>
                                                        <span class="fw-bold {{ $isRealLate ? 'text-danger' : 'text-dark' }}">
                                                            {{ $att->check_in_time->format('H:i') }}
                                                        </span>
                                                        @if ($isRealLate)
                                                            <span class="badge bg-danger ms-1" style="font-size: 9px;">
                                                                Telat {{ $lateStr }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if ($scheduleTime)
                                                        <small class="text-muted" style="font-size: 11px;">
                                                            <i class="mdi mdi-clock-outline me-1"></i>
                                                            Jadwal: {{ \Carbon\Carbon::parse($scheduleTime)->format('H:i') }}
                                                        </small>
                                                    @else
                                                        <small class="text-muted fst-italic" style="font-size: 11px;">- Fleksibel -</small>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- FOTO MASUK --}}
                                            <td>
                                                @php
                                                    $displayPhoto = null;
                                                    if ($att->photo_path) {
                                                        $displayPhoto = asset('storage/' . $att->photo_path);
                                                    } elseif ($att->leaveRequest && $att->leaveRequest->file_proof) {
                                                        $displayPhoto = asset('storage/' . $att->leaveRequest->file_proof);
                                                    }

                                                    $labelMasuk = 'Masuk';
                                                    if ($att->leaveRequest) {
                                                        $labelMasuk = 'Izin/Sakit';
                                                    } elseif ($att->presence_status) {
                                                        $labelMasuk = $att->presence_status;
                                                    }
                                                    
                                                    if (!empty($att->notes)) {
                                                        $cleanNote = $att->notes;
                                                        // FIX: Hilangkan teks Pulang dari caption masuk
                                                        if (str_contains($cleanNote, '| Pulang')) {
                                                            $parts = explode('| Pulang', $cleanNote);
                                                            $cleanNote = trim($parts[0]);
                                                        }
                                                        if (!empty($cleanNote)) {
                                                            $labelMasuk = $cleanNote;
                                                        }
                                                    }
                                                @endphp

                                                @if ($displayPhoto)
                                                    <div class="d-inline-block text-center" style="max-width: 150px;">
                                                        <img src="{{ $displayPhoto }}" alt="Foto Masuk"
                                                            class="rounded shadow-sm img-clickable"
                                                            style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #e2e8f0;"
                                                            data-bs-toggle="modal" data-bs-target="#imagePreviewModal"
                                                            data-img-src="{{ $displayPhoto }}"
                                                            data-img-title="Bukti Masuk - {{ $att->check_in_time->format('d M Y') }}">

                                                        <small class="d-block text-muted mt-1 text-wrap" style="font-size: 10px; line-height: 1.2;">
                                                            {{ \Illuminate\Support\Str::limit($labelMasuk, 50) }}
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
                                                        <i class="mdi mdi-logout text-primary me-2"></i>
                                                        <span class="{{ $att->is_early_checkout ? 'text-warning fw-bold' : '' }}">
                                                            {{ $att->check_out_time->format('H:i') }}
                                                        </span>
                                                        @if ($att->is_early_checkout)
                                                            <span class="badge bg-warning ms-1" style="font-size: 9px;">Cepat</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="badge bg-secondary">Belum Pulang</span>
                                                @endif
                                            </td>

                                            {{-- FOTO PULANG --}}
                                            <td>
                                                @php
                                                    $labelPulang = 'Pulang';
                                                    if (!empty($att->notes) && str_contains($att->notes, 'Pulang')) {
                                                        $parts = explode('Pulang', $att->notes);
                                                        $labelPulang = trim(end($parts));
                                                        $labelPulang = ltrim($labelPulang, ': ');
                                                        $labelPulang = 'Pulang ' . $labelPulang;
                                                    }
                                                @endphp

                                                @if ($att->photo_out_path)
                                                    <div class="d-inline-block text-center" style="max-width: 150px;">
                                                        <img src="{{ asset('storage/' . $att->photo_out_path) }}"
                                                            alt="Pulang" class="rounded shadow-sm img-clickable"
                                                            style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #e2e8f0;"
                                                            data-bs-toggle="modal" data-bs-target="#imagePreviewModal"
                                                            data-img-src="{{ asset('storage/' . $att->photo_out_path) }}"
                                                            data-img-title="Foto Pulang - {{ $att->check_in_time->format('d M Y') }}">

                                                        <small class="d-block text-muted mt-1 text-wrap" style="font-size: 10px; line-height: 1.2;">
                                                            {{ \Illuminate\Support\Str::limit($labelPulang, 50) }}
                                                        </small>
                                                    </div>
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
                                                    <span class="badge {{ $badgeColor }}">
                                                        {{ ucwords($att->presence_status) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">Belum Diatur</span>
                                                @endif
                                            </td>

                                            {{-- KOLOM VERIFIKASI & PETUGAS (LOGIKA UTAMA DUAL VERIFIER) --}}
                                            <td>
                                                <div class="d-flex flex-column gap-2">
                                                    
                                                    {{-- === 1. INFO PETUGAS MASUK (IN) === --}}
                                                    <div class="d-flex align-items-center">
                                                        @if ($att->scanned_by_user_id && $att->scanner)
                                                            {{-- A. Masuk via SECURITY SCAN --}}
                                                            <div class="badge bg-primary bg-opacity-10 text-primary border border-primary p-1 me-2 rounded">
                                                                <i class="mdi mdi-qrcode-scan"></i> IN
                                                            </div>
                                                            <div>
                                                                <span class="d-block fw-bold text-dark" style="font-size: 11px;">{{ $att->scanner->name }}</span>
                                                                <small class="text-muted" style="font-size: 9px;">Security</small>
                                                            </div>

                                                        @elseif ($att->status == 'verified' && $att->attendance_type == 'self')
                                                            {{-- B. Masuk Mandiri + SUDAH DIVERIFIKASI AUDIT --}}
                                                            <div class="badge bg-success bg-opacity-10 text-success border border-success p-1 me-2 rounded">
                                                                <i class="mdi mdi-check-decagram"></i> IN
                                                            </div>
                                                            <div>
                                                                <span class="d-block fw-bold text-dark" style="font-size: 11px;">{{ $att->verifier->name ?? 'Audit' }}</span>
                                                                <small class="text-muted" style="font-size: 9px;">Audit</small>
                                                            </div>

                                                        @elseif ($att->status == 'pending_verification')
                                                            {{-- C. Masuk Mandiri + BELUM VERIF --}}
                                                            <div class="badge bg-warning bg-opacity-10 text-dark border border-warning p-1 me-2 rounded">
                                                                <i class="mdi mdi-clock-outline"></i> IN
                                                            </div>
                                                            <div>
                                                                <span class="d-block fw-bold text-dark" style="font-size: 11px;">Menunggu</span>
                                                                <small class="text-muted" style="font-size: 9px;">Verifikasi Audit</small>
                                                            </div>

                                                        @elseif($att->audit_photo_path || $att->attendance_type == 'manual')
                                                            {{-- D. Manual/Koreksi Audit --}}
                                                            <div class="badge bg-info bg-opacity-10 text-info border border-info p-1 me-2 rounded">
                                                                <i class="mdi mdi-pencil"></i> IN
                                                            </div>
                                                            <div>
                                                                <span class="d-block fw-bold text-dark" style="font-size: 11px;">Audit / Admin</span>
                                                                <small class="text-muted" style="font-size: 9px;">Verifikator</small>
                                                            </div>
                                                        @else
                                                            {{-- Fallback --}}
                                                            <div class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary p-1 me-2 rounded">
                                                                <i class="mdi mdi-camera-front-variant"></i> IN
                                                            </div>
                                                            <small class="text-muted" style="font-size: 10px;">Mandiri</small>
                                                        @endif
                                                    </div>

                                                    {{-- === 2. INFO PETUGAS PULANG (OUT) === --}}
                                                    @if ($att->check_out_time)
                                                        <div class="border-top my-1"></div>
                                                        <div class="d-flex align-items-center">
                                                            
                                                            {{-- LOGIKA 1: Cek Notes Security Scan DULUAN (Prioritas Tertinggi) --}}
                                                            @if (str_contains($att->notes, 'Security Scan by'))
                                                                <div class="badge bg-dark bg-opacity-10 text-dark border border-dark p-1 me-2 rounded">
                                                                    <i class="mdi mdi-logout"></i> OUT
                                                                </div>
                                                                <div>
                                                                    {{-- Ambil nama dari Notes: "Security Scan by Budi" --}}
                                                                    <span class="d-block fw-bold text-dark" style="font-size: 11px;">
                                                                        {{ Str::after($att->notes, 'Security Scan by ') }}
                                                                    </span>
                                                                    <small class="text-muted" style="font-size: 9px;">Security</small>
                                                                </div>

                                                            @elseif (str_contains($att->notes, 'Security Scan'))
                                                                {{-- Fallback jika format lama (tidak ada "by Name") --}}
                                                                <div class="badge bg-dark bg-opacity-10 text-dark border border-dark p-1 me-2 rounded">
                                                                    <i class="mdi mdi-logout"></i> OUT
                                                                </div>
                                                                <div>
                                                                    <span class="d-block fw-bold text-dark" style="font-size: 11px;">Security</span>
                                                                    <small class="text-muted" style="font-size: 9px;">Scanner</small>
                                                                </div>

                                                            @elseif (str_contains($att->notes, 'Pulang (Selfie)'))
                                                                {{-- Pulang Mandiri --}}
                                                                <div class="badge bg-info bg-opacity-10 text-info border border-info p-1 me-2 rounded">
                                                                    <i class="mdi mdi-camera-front-variant"></i> OUT
                                                                </div>
                                                                <div>
                                                                    <span class="d-block fw-bold text-dark" style="font-size: 11px;">Mandiri</span>
                                                                    <small class="text-muted" style="font-size: 9px;">Selfie</small>
                                                                </div>

                                                            @else
                                                                {{-- Default / System --}}
                                                                <div class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary p-1 me-2 rounded">
                                                                    <i class="mdi mdi-logout"></i> OUT
                                                                </div>
                                                                <div>
                                                                    <span class="d-block fw-bold text-dark" style="font-size: 11px;">
                                                                        {{ $att->verifier->name ?? 'System' }}
                                                                    </span>
                                                                    <small class="text-muted" style="font-size: 9px;">Verifikator</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif

                                                </div>
                                            </td>

                                            {{-- BUKTI AUDIT --}}
                                            <td>
                                                @if ($att->audit_photo_path)
                                                    <div class="d-inline-block text-center">
                                                        <img src="{{ asset('storage/' . $att->audit_photo_path) }}" alt="Bukti Audit"
                                                            class="audit-photo-thumb shadow-sm img-clickable"
                                                            data-bs-toggle="modal" data-bs-target="#imagePreviewModal"
                                                            data-img-src="{{ asset('storage/' . $att->audit_photo_path) }}"
                                                            data-img-title="Bukti Audit">
                                                        <small class="d-block text-center text-muted mt-1" style="font-size: 9px;">Audit</small>
                                                    </div>
                                                @else - @endif
                                            </td>

                                            {{-- METODE --}}
                                            <td>
                                                @if ($att->attendance_type == 'scan') <span class="badge badge-outline-primary"><i class="mdi mdi-qrcode-scan"></i> Scan</span>
                                                @elseif($att->attendance_type == 'self') <span class="badge badge-outline-info"><i class="mdi mdi-camera-front-variant"></i> Selfie</span>
                                                @else <span class="badge badge-outline-secondary">System</span> @endif
                                            </td>

                                            {{-- AKSI AUDIT --}}
                                            @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                                                <td class="action-buttons">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        @if ($att->status != 'verified')
                                                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#verifyModal{{ $att->id }}" title="Verifikasi">
                                                                <i class="mdi mdi-check"></i>
                                                            </button>
                                                        @endif
                                                        @if (auth()->user()->role == 'audit')
                                                            <button type="button" class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#editAuditModal{{ $att->id }}" title="Koreksi">
                                                                <i class="mdi mdi-pencil"></i>
                                                            </button>
                                                        @endif
                                                        @if ($att->status != 'verified')
                                                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $att->id }}" title="Tolak">
                                                                <i class="mdi mdi-close"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-calendar-remove display-4 text-muted"></i>
                            <h5 class="mt-3 text-muted">Tidak ada data absensi</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PREVIEW IMAGE & MODAL AUDIT SAMA SEPERTI SEBELUMNYA --}}
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0 shadow-none">
                <div class="modal-header border-0 p-0 mb-2">
                    <h5 class="modal-title text-white" id="imagePreviewModalLabel">Preview</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img src="" id="previewImage" class="img-fluid rounded" style="max-height: 85vh; width: auto;">
                </div>
            </div>
        </div>
    </div>

    @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin') && $history->count() > 0)
        @foreach ($history as $att)
            {{-- Modal Verifikasi --}}
            <div class="modal fade" id="verifyModal{{ $att->id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="mdi mdi-check-circle text-success me-2"></i> Verifikasi Absensi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('audit.verify.attendance', $att->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf @method('PUT')
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <strong>Karyawan:</strong> {{ $employee->name }}<br>
                                    <strong>Tanggal:</strong> {{ $att->check_in_time->format('d M Y') }}
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Status Kehadiran</label>
                                        <select name="presence_status" class="form-select" required>
                                            <option value="Masuk" {{ $att->presence_status == 'Masuk' ? 'selected' : '' }}>✅ Masuk</option>
                                            <option value="Sakit" {{ $att->presence_status == 'Sakit' ? 'selected' : '' }}>🤒 Sakit</option>
                                            <option value="Cuti" {{ $att->presence_status == 'Cuti' ? 'selected' : '' }}>🏖️ Cuti</option>
                                            <option value="Alpha" {{ $att->presence_status == 'Alpha' ? 'selected' : '' }}>❌ Alpha</option>
                                            <option value="Telat" {{ $att->presence_status == 'Telat' ? 'selected' : '' }}>⏰ Telat</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6"><label>Bukti Audit</label><input type="file" name="audit_photo" class="form-control"></div>
                                </div>
                                <div class="mt-3"><label>Catatan</label><textarea name="audit_note" class="form-control">{{ $att->audit_note }}</textarea></div>
                            </div>
                            <div class="modal-footer"><button type="submit" class="btn btn-success">Simpan Verifikasi</button></div>
                        </form>
                    </div>
                </div>
            </div>
            
            {{-- Modal Edit & Reject (Gunakan yang sama dengan kode sebelumnya) --}}
            {{-- ... --}}
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
                    modalTitle.textContent = imgTitle;
                    modalImg.src = imgSrc;
                });
            }
        });
    </script>
@endpush