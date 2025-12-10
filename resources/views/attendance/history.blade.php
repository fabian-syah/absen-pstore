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
            <a href="{{ route('team.branch.detail', $employee->branch_id) }}" class="btn btn-sm btn-light btn-icon me-3 rounded-circle shadow-sm">
                <i class="mdi mdi-arrow-left text-primary"></i>
            </a>
            <div>
                <h4 class="mb-0 fw-bold">Riwayat Absensi: {{ $employee->name }}</h4>
                <small class="text-muted">
                    <i class="mdi mdi-domain me-1"></i> {{ $employee->division->name ?? '-' }} | <i class="mdi mdi-office-building me-1"></i> {{ $employee->branch->name ?? '-' }}
                </small>
            </div>
        </div>
    @else
        <h4 class="mb-0 fw-bold">Riwayat Absensi Saya</h4>
    @endif
@endsection

@push('styles')
    <style>
        .verification-badge { font-size: 0.7rem; padding: 0.35rem 0.6rem; border-radius: 6px; font-weight: 600; letter-spacing: 0.5px; }
        .action-buttons .btn { padding: 0.35rem 0.6rem; }
        .audit-mode-badge { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; }
        .audit-photo-thumb { width: 45px; height: 45px; object-fit: cover; border: 2px solid #e2e8f0; border-radius: 8px; transition: all 0.2s; }
        .img-clickable { cursor: pointer; transition: transform 0.2s ease-in-out; }
        .img-clickable:hover { transform: scale(1.05); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table thead th { font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; background-color: #f8f9fa; border-bottom: 2px solid #e9ecef; white-space: nowrap; }
        .border-dashed-start { border-left: 2px dashed #dee2e6; padding-left: 10px; }
        .verifier-box { background: #f8f9fa; border-radius: 8px; padding: 6px 10px; border: 1px solid #e9ecef; }
        
        /* Fix Select Color */
        .form-select-custom {
            background-color: #fff !important;
            color: #333 !important;
            border: 1px solid #ced4da !important;
            font-weight: 600;
        }
        
        /* Note Box Style */
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
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            {{-- HEADER MODE AUDIT --}}
            @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                <div class="alert alert-info border-0 shadow-sm mb-4 d-flex align-items-center p-3 rounded-3" style="background: #e3f2fd; color: #0d47a1;">
                    <i class="mdi mdi-shield-account display-6 me-3"></i>
                    <div>
                        <h5 class="alert-heading fw-bold mb-1">Mode Cross-Check Audit</h5>
                        <p class="mb-0 small opacity-75">Anda dapat memverifikasi, mengoreksi, dan mengubah status kehadiran karyawan.</p>
                    </div>
                </div>
            @endif

            {{-- FILTER & NAVIGASI --}}
            <div class="card mb-4 border-0 shadow-sm rounded-4">
                <div class="card-body py-3">
                    <div class="row align-items-center justify-content-between g-3">
                        
                        {{-- NAVIGASI KIRI --}}
                        <div class="col-auto">
                            @php
                                $prevParams = ['month' => $prevMonth, 'year' => $prevYear];
                                if(isset($employee)) {
                                    $prevRoute = route('team.branch.employee.history', array_merge(['branchId' => $employee->branch_id, 'employeeId' => $employee->id], $prevParams));
                                } else {
                                    $prevRoute = route('attendance.history', $prevParams);
                                }
                            @endphp
                            <a href="{{ $prevRoute }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold" title="Bulan Sebelumnya">
                                <i class="mdi mdi-chevron-left me-1"></i> Prev
                            </a>
                        </div>

                        {{-- FORM FILTER TENGAH (FIXED UI) --}}
                        <div class="col-auto">
                            <form action="{{ isset($employee) ? route('team.branch.employee.history', ['branchId' => $employee->branch_id, 'employeeId' => $employee->id]) : route('attendance.history') }}"
                                method="GET" class="row align-items-center gx-2">
                                
                                <div class="col-auto d-none d-md-block">
                                    <label class="fw-bold mb-0 text-muted small text-uppercase"><i class="mdi mdi-calendar-month me-1"></i> Periode:</label>
                                </div>
                                <div class="col-auto">
                                    <select name="month" class="form-select form-select-sm form-select-custom rounded-pill px-3" onchange="this.form.submit()">
                                        @foreach (range(1, 12) as $m)
                                            <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <select name="year" class="form-select form-select-sm form-select-custom rounded-pill px-3" onchange="this.form.submit()">
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

                        {{-- NAVIGASI KANAN --}}
                        <div class="col-auto d-flex gap-2">
                            @php
                                $nextParams = ['month' => $nextMonth, 'year' => $nextYear];
                                if(isset($employee)) {
                                    $nextRoute = route('team.branch.employee.history', array_merge(['branchId' => $employee->branch_id, 'employeeId' => $employee->id], $nextParams));
                                } else {
                                    $nextRoute = route('attendance.history', $nextParams);
                                }
                            @endphp
                            <a href="{{ $nextRoute }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold" title="Bulan Selanjutnya">
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

            {{-- RINGKASAN BULANAN --}}
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
                            <h2 class="fw-bold text-white mb-0 display-6">{{ $summary['hadir'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card bg-info text-white border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                            <h6 class="text-white-50 mb-1 small text-uppercase fw-bold">Sakit & Izin</h6>
                            <h2 class="fw-bold text-white mb-0 display-6">{{ $summary['sakit'] + $summary['izin'] }}</h2>
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
                    <div class="p-2 rounded-3 text-center border" style="background: #fff3cd; color: #856404; border-color: #ffeeba!important;">
                        <small class="fw-bold d-block text-uppercase" style="font-size: 0.65rem;">Terlambat</small>
                        <span class="fs-5 fw-bold">{{ $summary['telat'] }}</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-2 rounded-3 text-center border" style="background: #f8d7da; color: #721c24; border-color: #f5c6cb!important;">
                        <small class="fw-bold d-block text-uppercase" style="font-size: 0.65rem;">Plg Cepat</small>
                        <span class="fs-5 fw-bold">{{ $summary['pulang_cepat'] }}</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-2 rounded-3 text-center border" style="background: #e2e3e5; color: #383d41; border-color: #d6d8db!important;">
                        <small class="fw-bold d-block text-uppercase" style="font-size: 0.65rem;">Pending</small>
                        <span class="fs-5 fw-bold">{{ $summary['pending'] }}</span>
                    </div>
                </div>
            </div>

            {{-- TABEL DATA --}}
            <div class="card shadow-sm rounded-4 border-0">
                <div class="card-body p-0">
                    <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold text-primary">
                            <i class="mdi mdi-history me-2"></i> Detail Absensi
                        </h5>
                        @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                            <span class="badge audit-mode-badge px-3 py-2 shadow-sm rounded-pill">
                                <i class="mdi mdi-shield-check me-1"></i> Mode Audit
                            </span>
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
                                        <tr>
                                            {{-- TANGGAL --}}
                                            <td class="ps-4 py-3">
                                                <div class="fw-bold text-dark">{{ $att->check_in_time->format('d M Y') }}</div>
                                                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">{{ $att->check_in_time->format('l') }}</small>
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
                                                                $lateStr = ($hours > 0) ? "{$hours}j {$mins}m" : "{$mins}m";
                                                            }
                                                        }
                                                    @endphp

                                                    <div class="d-flex align-items-center">
                                                        <i class="mdi mdi-login-variant {{ $isRealLate ? 'text-danger' : 'text-success' }} me-2 fs-5"></i>
                                                        <div>
                                                            <span class="fw-bold fs-6 {{ $isRealLate ? 'text-danger' : 'text-dark' }}">
                                                                {{ $att->check_in_time->format('H:i') }}
                                                            </span>
                                                            @if ($isRealLate)
                                                                <span class="badge bg-danger rounded-pill px-2 py-0 ms-1" style="font-size: 0.6rem;">+{{ $lateStr }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <small class="text-muted ps-4" style="font-size: 0.7rem;">
                                                        {{ $scheduleTime ? 'Jadwal: '.\Carbon\Carbon::parse($scheduleTime)->format('H:i') : '- Fleksibel -' }}
                                                    </small>
                                                </div>
                                            </td>

                                            {{-- FOTO MASUK + CATATAN --}}
                                            <td>
                                                @php
                                                    $displayPhoto = null;
                                                    if ($att->photo_path) $displayPhoto = asset('storage/' . $att->photo_path);
                                                    elseif ($att->leaveRequest && $att->leaveRequest->file_proof) $displayPhoto = asset('storage/' . $att->leaveRequest->file_proof);
                                                    
                                                    $labelMasuk = 'Masuk';
                                                    if ($att->leaveRequest) $labelMasuk = 'Izin/Sakit';
                                                    elseif ($att->presence_status) $labelMasuk = $att->presence_status;
                                                    
                                                    // ---- LOGIKA BARU PARSING CATATAN MASUK ----
                                                    $rawNotes = $att->notes ?? '';
                                                    $noteMasukDisplay = '';
                                                    
                                                    // Pecah string berdasarkan separator ' | '
                                                    $noteParts = explode(' | ', $rawNotes);
                                                    
                                                    // Bagian pertama diasumsikan sebagai catatan masuk (jika bukan string kosong/system)
                                                    if (isset($noteParts[0])) {
                                                        $firstPart = trim($noteParts[0]);
                                                        // Filter kata kunci sistem agar tidak dianggap catatan user
                                                        if (!empty($firstPart) && 
                                                            !str_contains($firstPart, 'Catatan:') && 
                                                            !str_contains($firstPart, 'Security Scan') && 
                                                            !str_contains($firstPart, 'Pulang') &&
                                                            $firstPart != '-') {
                                                            $noteMasukDisplay = $firstPart;
                                                        }
                                                    }
                                                @endphp

                                                <div class="d-flex flex-column align-items-start">
                                                    @if ($displayPhoto)
                                                        <img src="{{ $displayPhoto }}" alt="In"
                                                            class="rounded-3 shadow-sm img-clickable border"
                                                            style="width: 45px; height: 45px; object-fit: cover;"
                                                            data-bs-toggle="modal" data-bs-target="#imagePreviewModal"
                                                            data-img-src="{{ $displayPhoto }}"
                                                            data-img-title="Masuk: {{ \Illuminate\Support\Str::limit($labelMasuk, 40) }}">
                                                    @else
                                                        <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted border" style="width: 45px; height: 45px;">
                                                            <i class="mdi mdi-image-off"></i>
                                                        </div>
                                                    @endif

                                                    {{-- TAMPILKAN CATATAN MASUK DI BAWAH FOTO --}}
                                                    @if(!empty($noteMasukDisplay))
                                                        <div class="note-box" title="{{ $noteMasukDisplay }}">
                                                            <i class="mdi mdi-note-text-outline me-1"></i>{{ \Illuminate\Support\Str::limit($noteMasukDisplay, 25) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- JAM PULANG --}}
                                            <td class="border-start bg-light bg-opacity-25">
                                                @if ($att->check_out_time)
                                                    <div class="d-flex align-items-center">
                                                        <i class="mdi mdi-logout-variant text-primary me-2 fs-5"></i>
                                                        <div>
                                                            <span class="fw-bold fs-6 {{ $att->is_early_checkout ? 'text-warning' : 'text-dark' }}">
                                                                {{ $att->check_out_time->format('H:i') }}
                                                            </span>
                                                            @if ($att->is_early_checkout)
                                                                <span class="badge bg-warning text-dark rounded-pill px-2 py-0 ms-1" style="font-size: 0.6rem;">Cepat</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="badge bg-light text-secondary border">Belum Pulang</span>
                                                @endif
                                            </td>

                                            {{-- FOTO PULANG + CATATAN --}}
                                            <td class="bg-light bg-opacity-25">
                                                @php
                                                    $labelPulang = 'Pulang';
                                                    if (!empty($att->notes) && str_contains($att->notes, 'Pulang')) {
                                                        $parts = explode('Pulang', $att->notes);
                                                        $labelPulang = 'Pulang ' . ltrim(trim(end($parts)), ': ');
                                                    }

                                                    // ---- LOGIKA BARU PARSING CATATAN PULANG ----
                                                    $notePulangDisplay = '';
                                                    $noteParts = explode(' | ', $att->notes ?? '');
                                                    
                                                    // Cari bagian yang mengandung "Catatan:"
                                                    foreach ($noteParts as $part) {
                                                        if (str_contains($part, 'Catatan:')) {
                                                            // Hapus label "Catatan:" dan trim
                                                            $notePulangDisplay = trim(str_replace('Catatan:', '', $part));
                                                            break; 
                                                        }
                                                    }
                                                @endphp

                                                <div class="d-flex flex-column align-items-start">
                                                    @if ($att->photo_out_path)
                                                        <img src="{{ asset('storage/' . $att->photo_out_path) }}" alt="Out"
                                                            class="rounded-3 shadow-sm img-clickable border"
                                                            style="width: 45px; height: 45px; object-fit: cover;"
                                                            data-bs-toggle="modal" data-bs-target="#imagePreviewModal"
                                                            data-img-src="{{ asset('storage/' . $att->photo_out_path) }}"
                                                            data-img-title="{{ \Illuminate\Support\Str::limit($labelPulang, 40) }}">
                                                    @else
                                                        <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted border" style="width: 45px; height: 45px;">
                                                            <i class="mdi mdi-image-off"></i>
                                                        </div>
                                                    @endif

                                                    {{-- TAMPILKAN CATATAN PULANG DI BAWAH FOTO --}}
                                                    @if(!empty($notePulangDisplay))
                                                        <div class="note-box bg-white" title="{{ $notePulangDisplay }}">
                                                            <i class="mdi mdi-note-text-outline me-1"></i>{{ \Illuminate\Support\Str::limit($notePulangDisplay, 25) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- STATUS KEHADIRAN --}}
                                            <td class="text-center">
                                                @if ($att->presence_status)
                                                    @php
                                                        $statusLower = strtolower($att->presence_status);
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
                                                    <span class="badge {{ $badgeClass }} verification-badge shadow-sm">
                                                        {{ ucwords($att->presence_status) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary verification-badge">Pending</span>
                                                @endif
                                            </td>

                                            {{-- LOKASI (MASUK & PULANG) --}}
                                            <td class="text-center">
                                                <div class="d-flex flex-column gap-2">
                                                    
                                                    {{-- LOKASI MASUK --}}
                                                    <div class="d-flex flex-column align-items-center">
                                                        <small class="text-muted fw-bold mb-1" style="font-size: 0.6rem;">MASUK</small>
                                                        @if ($att->latitude && $att->longitude)
                                                            <a href="https://www.google.com/maps/search/?api=1&query={{ $att->latitude }},{{ $att->longitude }}" target="_blank" 
                                                               class="btn btn-outline-info btn-sm btn-icon rounded-circle" title="Lokasi Masuk">
                                                                <i class="mdi mdi-map-marker-radius"></i>
                                                            </a>
                                                        @elseif(in_array($att->attendance_type, ['scan', 'manual']))
                                                            <span class="badge bg-light text-dark border">
                                                                <i class="mdi mdi-office-building"></i> Kantor
                                                            </span>
                                                        @elseif($att->attendance_type == 'leave')
                                                             <span class="text-muted small">-</span>
                                                        @else
                                                            <span class="text-muted small"><i class="mdi mdi-map-marker-off"></i></span>
                                                        @endif
                                                    </div>

                                                    {{-- LOKASI PULANG (JIKA ADA) --}}
                                                    @if($att->check_out_time)
                                                        <div class="border-top w-100 my-1"></div>
                                                        <div class="d-flex flex-column align-items-center">
                                                            <small class="text-muted fw-bold mb-1" style="font-size: 0.6rem;">PULANG</small>
                                                            @if ($att->latitude_out && $att->longitude_out)
                                                                <a href="https://www.google.com/maps/search/?api=1&query={{ $att->latitude_out }},{{ $att->longitude_out }}" target="_blank" 
                                                                   class="btn btn-outline-danger btn-sm btn-icon rounded-circle" title="Lokasi Pulang">
                                                                    <i class="mdi mdi-map-marker-radius"></i>
                                                                </a>
                                                            @elseif(in_array($att->attendance_type, ['scan', 'manual']))
                                                                <span class="badge bg-light text-dark border">
                                                                    <i class="mdi mdi-office-building"></i> Kantor
                                                                </span>
                                                            @else
                                                                <span class="text-muted small"><i class="mdi mdi-map-marker-off"></i></span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- VERIFIKASI & PETUGAS --}}
                                            <td>
                                                <div class="d-flex flex-column gap-2">
                                                    
                                                    {{-- 1. INFO MASUK / IZIN --}}
                                                    <div class="verifier-box d-flex align-items-center">
                                                        @if ($att->attendance_type == 'leave')
                                                            <div class="badge bg-secondary text-white p-1 me-2 rounded-1" style="min-width: 35px;">IZIN</div>
                                                            <div class="lh-sm">
                                                                <span class="d-block fw-bold text-dark small">{{ $att->verifier->name ?? 'System' }}</span>
                                                                <small class="text-muted" style="font-size: 0.65rem;">Approved By</small>
                                                            </div>
                                                        @elseif ($att->scanned_by_user_id && $att->scanner)
                                                            <div class="badge bg-primary text-white p-1 me-2 rounded-1" style="min-width: 35px;">IN</div>
                                                            <div class="lh-sm">
                                                                <span class="d-block fw-bold text-dark small">{{ $att->scanner->name }}</span>
                                                                <small class="text-muted" style="font-size: 0.65rem;">Security</small>
                                                            </div>
                                                        @elseif ($att->status == 'verified' && $att->attendance_type == 'self')
                                                            <div class="badge bg-success text-white p-1 me-2 rounded-1" style="min-width: 35px;">IN</div>
                                                            <div class="lh-sm">
                                                                <span class="d-block fw-bold text-dark small">{{ $att->verifier->name ?? 'System' }}</span>
                                                                <small class="text-muted" style="font-size: 0.65rem;">Audit/Admin</small>
                                                            </div>
                                                        @elseif ($att->status == 'pending_verification')
                                                            <div class="badge bg-warning text-dark p-1 me-2 rounded-1" style="min-width: 35px;">IN</div>
                                                            <div class="lh-sm">
                                                                <span class="d-block fw-bold text-dark small">Menunggu</span>
                                                                <small class="text-muted" style="font-size: 0.65rem;">Verifikasi</small>
                                                            </div>
                                                        @elseif($att->audit_photo_path || $att->attendance_type == 'manual')
                                                            <div class="badge bg-info text-white p-1 me-2 rounded-1" style="min-width: 35px;">IN</div>
                                                            <div class="lh-sm">
                                                                <span class="d-block fw-bold text-dark small">Manual</span>
                                                                <small class="text-muted" style="font-size: 0.65rem;">Audit/Admin</small>
                                                            </div>
                                                        @else
                                                            <div class="badge bg-secondary text-white p-1 me-2 rounded-1" style="min-width: 35px;">IN</div>
                                                            <div class="lh-sm">
                                                                <span class="d-block fw-bold text-dark small">Mandiri</span>
                                                                <small class="text-muted" style="font-size: 0.65rem;">Selfie</small>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    {{-- 2. INFO PULANG --}}
                                                    @if ($att->check_out_time)
                                                        <div class="verifier-box d-flex align-items-center mt-1">
                                                            @if (str_contains($att->notes, 'Security Scan by'))
                                                                <div class="badge bg-dark text-white p-1 me-2 rounded-1" style="min-width: 35px;">OUT</div>
                                                                <div class="lh-sm">
                                                                    <span class="d-block fw-bold text-dark small">{{ Str::after($att->notes, 'Security Scan by ') }}</span>
                                                                    <small class="text-muted" style="font-size: 0.65rem;">Security</small>
                                                                </div>
                                                            @elseif (str_contains($att->notes, 'Security Scan'))
                                                                <div class="badge bg-dark text-white p-1 me-2 rounded-1" style="min-width: 35px;">OUT</div>
                                                                <div class="lh-sm">
                                                                    <span class="d-block fw-bold text-dark small">Security</span>
                                                                    <small class="text-muted" style="font-size: 0.65rem;">Scanner</small>
                                                                </div>
                                                            @elseif (str_contains($att->notes, 'Pulang (Selfie)'))
                                                                <div class="badge bg-info text-white p-1 me-2 rounded-1" style="min-width: 35px;">OUT</div>
                                                                <div class="lh-sm">
                                                                    <span class="d-block fw-bold text-dark small">Mandiri</span>
                                                                    <small class="text-muted" style="font-size: 0.65rem;">Selfie</small>
                                                                </div>
                                                            @else
                                                                <div class="badge bg-secondary text-white p-1 me-2 rounded-1" style="min-width: 35px;">OUT</div>
                                                                <div class="lh-sm">
                                                                    <span class="d-block fw-bold text-dark small">{{ $att->verifier->name ?? 'System' }}</span>
                                                                    <small class="text-muted" style="font-size: 0.65rem;">Auto</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- METODE --}}
                                            <td class="text-center">
                                                @if ($att->attendance_type == 'scan')
                                                    <i class="mdi mdi-qrcode-scan text-primary fs-4" title="Scan QR"></i>
                                                @elseif($att->attendance_type == 'self')
                                                    <i class="mdi mdi-camera-front-variant text-info fs-4" title="Selfie"></i>
                                                @elseif($att->attendance_type == 'manual')
                                                    <i class="mdi mdi-pencil-box-outline text-warning fs-4" title="Manual Edit"></i>
                                                @elseif($att->attendance_type == 'leave')
                                                    <i class="mdi mdi-file-document-outline text-secondary fs-4" title="Izin/Cuti"></i>
                                                @else
                                                    <i class="mdi mdi-cog text-secondary fs-4" title="System"></i>
                                                @endif
                                                
                                                @if($att->audit_photo_path)
                                                    <div class="mt-1">
                                                        <span class="badge bg-light text-dark border pointer" 
                                                              data-bs-toggle="modal" data-bs-target="#imagePreviewModal"
                                                              data-img-src="{{ asset('storage/' . $att->audit_photo_path) }}"
                                                              data-img-title="Bukti Audit">
                                                            <i class="mdi mdi-image-outline text-info"></i> Bukti
                                                        </span>
                                                    </div>
                                                @endif
                                            </td>

                                            {{-- AKSI AUDIT --}}
                                            @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                                                <td class="text-end pe-4">
                                                    <div class="btn-group btn-group-sm shadow-sm" role="group">
                                                        @if ($att->status != 'verified')
                                                            <button type="button" class="btn btn-success text-white" 
                                                                    data-bs-toggle="modal" data-bs-target="#verifyModal{{ $att->id }}" title="Verifikasi">
                                                                <i class="mdi mdi-check"></i>
                                                            </button>
                                                        @endif
                                                        @if (auth()->user()->role == 'audit')
                                                            <button type="button" class="btn btn-info text-white" 
                                                                    data-bs-toggle="modal" data-bs-target="#editAuditModal{{ $att->id }}" title="Koreksi">
                                                                <i class="mdi mdi-pencil"></i>
                                                            </button>
                                                        @endif
                                                        @if ($att->status != 'verified')
                                                            <button type="button" class="btn btn-danger text-white" 
                                                                    data-bs-toggle="modal" data-bs-target="#rejectModal{{ $att->id }}" title="Tolak">
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
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="mdi mdi-calendar-blank text-muted" style="font-size: 40px;"></i>
                            </div>
                            <h5 class="text-muted fw-bold">Belum Ada Data</h5>
                            <p class="text-muted small mb-0">Tidak ada riwayat absensi pada periode ini.</p>
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
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img src="" id="previewImage" class="img-fluid rounded-4 shadow-lg border border-3 border-white" style="max-height: 85vh; width: auto;">
                </div>
            </div>
        </div>
    </div>

    {{-- MODALS AUDIT --}}
    @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin') && $history->count() > 0)
        @foreach ($history as $att)
            {{-- Modal Verifikasi --}}
            <div class="modal fade" id="verifyModal{{ $att->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content rounded-4 border-0">
                        <div class="modal-header bg-success text-white rounded-top-4">
                            <h5 class="modal-title fw-bold"><i class="mdi mdi-check-circle me-2"></i> Verifikasi Absensi</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('audit.verify.attendance', $att->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf @method('PUT')
                            <div class="modal-body p-4">
                                <div class="d-flex align-items-center mb-4 p-3 bg-light rounded-3">
                                    <div class="me-3">
                                        <div class="avatar-initial rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            {{ substr($employee->name, 0, 1) }}
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">{{ $employee->name }}</h6>
                                        <small class="text-muted">{{ $att->check_in_time->format('l, d F Y') }}</small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-uppercase">Status Kehadiran</label>
                                    <select name="presence_status" class="form-select form-select-lg" required>
                                        <option value="Masuk" {{ $att->presence_status == 'Masuk' ? 'selected' : '' }}>✅ Masuk</option>
                                        <option value="Sakit" {{ $att->presence_status == 'Sakit' ? 'selected' : '' }}>🤒 Sakit</option>
                                        <option value="Cuti" {{ $att->presence_status == 'Cuti' ? 'selected' : '' }}>🏖️ Cuti</option>
                                        <option value="Alpha" {{ $att->presence_status == 'Alpha' ? 'selected' : '' }}>❌ Alpha</option>
                                        <option value="Telat" {{ $att->presence_status == 'Telat' ? 'selected' : '' }}>⏰ Telat</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-uppercase">Bukti Audit (Opsional)</label>
                                    <input type="file" name="audit_photo" class="form-control">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label fw-bold small text-uppercase">Catatan</label>
                                    <textarea name="audit_note" class="form-control" rows="2" placeholder="Tambahkan catatan verifikasi...">{{ $att->audit_note }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">Simpan Verifikasi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Modal Edit Audit --}}
            @if (auth()->user()->role == 'audit')
                <div class="modal fade" id="editAuditModal{{ $att->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content rounded-4 border-0">
                            <div class="modal-header bg-info text-white rounded-top-4">
                                <h5 class="modal-title fw-bold"><i class="mdi mdi-pencil-box me-2"></i> Koreksi Data</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('audit.update.attendance', $att->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <div class="modal-body p-4">
                                    <div class="alert alert-warning d-flex align-items-center p-2 rounded-3 mb-3">
                                        <i class="mdi mdi-alert-circle fs-4 me-2"></i>
                                        <small class="lh-sm">Perubahan data akan tercatat dalam log audit.</small>
                                    </div>
                                    
                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold">Jam Masuk</label>
                                            <input type="time" name="check_in_time" class="form-control" value="{{ $att->check_in_time->format('H:i') }}" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold">Jam Pulang</label>
                                            <input type="time" name="check_out_time" class="form-control" value="{{ $att->check_out_time ? $att->check_out_time->format('H:i') : '' }}">
                                        </div>
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
                                        <label class="form-label small fw-bold">Status Verifikasi</label>
                                        <select name="status" class="form-select" required>
                                            <option value="verified" {{ $att->status == 'verified' ? 'selected' : '' }}>Verified</option>
                                            <option value="pending_verification" {{ $att->status == 'pending_verification' ? 'selected' : '' }}>Pending</option>
                                            <option value="rejected" {{ $att->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Bukti & Catatan</label>
                                        <input type="file" name="audit_photo" class="form-control mb-2">
                                        <textarea name="audit_note" class="form-control" rows="2" placeholder="Alasan koreksi...">{{ $att->audit_note }}</textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-info text-white rounded-pill px-4 fw-bold">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Modal Tolak --}}
            <div class="modal fade" id="rejectModal{{ $att->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content rounded-4 border-0">
                        <div class="modal-header bg-danger text-white rounded-top-4">
                            <h5 class="modal-title fw-bold"><i class="mdi mdi-alert-circle me-2"></i> Tolak Absensi</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('audit.reject', $att->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <div class="modal-body p-4">
                                <p class="text-center mb-3">
                                    Yakin ingin menolak absensi <strong>{{ $employee->name }}</strong> pada tanggal <strong>{{ $att->check_in_time->format('d M Y') }}</strong>?
                                </p>
                                <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Wajib isi alasan penolakan..." required></textarea>
                            </div>
                            <div class="modal-footer border-top-0 pt-0 px-4 pb-4 justify-content-center">
                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Tolak Absensi</button>
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
                    modalTitle.textContent = imgTitle;
                    modalImg.src = imgSrc;
                });
            }
        });
    </script>
@endpush