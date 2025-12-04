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
        .verification-badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.7rem;
        }

        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }

        .verified-check {
            color: #28a745;
            font-size: 1.1rem;
        }

        .edited-info {
            color: #0dcaf0;
            font-size: 1.1rem;
        }

        .pending-clock {
            color: #ffc107;
            font-size: 1.1rem;
        }

        .audit-mode-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .audit-photo-thumb {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
        }

        /* Style untuk gambar yang bisa diklik */
        .img-clickable {
            cursor: pointer;
            transition: transform 0.2s;
        }

        .img-clickable:hover {
            transform: scale(1.1);
            opacity: 0.9;
        }
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
                            <p class="mb-0">Anda dapat memverifikasi, mengoreksi, dan mengubah status kehadiran karyawan.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- FILTER BULAN & TAHUN --}}
            <div class="card mb-4">
                <div class="card-body py-3">
                    <form
                        action="{{ isset($employee) ? route('team.branch.employee.history', ['branchId' => $employee->branch_id, 'employeeId' => $employee->id]) : route('attendance.history') }}"
                        method="GET" class="row align-items-center gx-2">
                        <div class="col-auto">
                            <label class="fw-bold mb-0 me-2"><i class="mdi mdi-filter-outline"></i> Filter Periode:</label>
                        </div>
                        <div class="col-auto">
                            <select name="month" class="form-select form-select-sm">
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="year" class="form-select form-select-sm">
                                @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm text-white">
                                Tampilkan
                            </button>
                        </div>
                    </form>
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

            {{-- Baris 2 (Detail Kecil) --}}
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
                                        <th>Verifikasi</th>
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
                                                        if ($att->user && $att->user->check_in_start) {
                                                            $scheduleTime = $att->user->check_in_start;
                                                        } elseif ($att->user && $att->user->workSchedule) {
                                                            $scheduleTime = $att->user->workSchedule->start_time;
                                                        }

                                                        $isRealLate = false;
                                                        $lateMinutes = 0;
                                                        if ($scheduleTime) {
                                                            $actualStr = $att->check_in_time->format('H:i');
                                                            $scheduleStr = \Carbon\Carbon::parse($scheduleTime)->format(
                                                                'H:i',
                                                            );
                                                            if ($actualStr > $scheduleStr) {
                                                                $isRealLate = true;
                                                                $actualCarbon = \Carbon\Carbon::parse($actualStr);
                                                                $scheduleCarbon = \Carbon\Carbon::parse($scheduleStr);
                                                                $lateMinutes = $scheduleCarbon->diffInMinutes(
                                                                    $actualCarbon,
                                                                );
                                                            }
                                                        }
                                                    @endphp

                                                    <div class="d-flex align-items-center mb-1">
                                                        <i
                                                            class="mdi mdi-login {{ $isRealLate ? 'text-danger' : 'text-success' }} me-2"></i>
                                                        <span
                                                            class="fw-bold {{ $isRealLate ? 'text-danger' : 'text-dark' }}">
                                                            {{ $att->check_in_time->format('H:i') }}
                                                        </span>
                                                        @if ($isRealLate)
                                                            <span class="badge bg-danger ms-1" style="font-size: 9px;">
                                                                Telat {{ $lateMinutes }}m
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if ($scheduleTime)
                                                        <small class="text-muted" style="font-size: 11px;">
                                                            <i class="mdi mdi-clock-outline me-1"></i>
                                                            Jadwal:
                                                            {{ \Carbon\Carbon::parse($scheduleTime)->format('H:i') }}
                                                        </small>
                                                    @else
                                                        <small class="text-muted fst-italic" style="font-size: 11px;">-
                                                            Fleksibel -</small>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- FOTO MASUK / BUKTI IZIN (MODIFIKASI: MENAMPILKAN NOTES) --}}
                                            <td>
                                                @php
                                                    $displayPhoto = null;

                                                    // 1. Logika Gambar
                                                    if ($att->photo_path) {
                                                        $displayPhoto = asset('storage/' . $att->photo_path);
                                                    } elseif ($att->leaveRequest && $att->leaveRequest->file_proof) {
                                                        $displayPhoto = asset(
                                                            'storage/' . $att->leaveRequest->file_proof,
                                                        );
                                                    }

                                                    // 2. Logika Label (Catatan vs Status Default)
                                                    $labelMasuk = 'Masuk'; // Default dasar

                                                    if ($att->leaveRequest) {
                                                        $labelMasuk = 'Izin/Sakit';
                                                    } elseif ($att->presence_status) {
                                                        $labelMasuk = $att->presence_status; // WFH, Dinas Luar, dll
                                                    }

                                                    // Cek apakah ada notes dari user
                                                    if (!empty($att->notes)) {
                                                        $cleanNote = $att->notes;
                                                        // Jika ada pemisah "| Pulang:", kita ambil bagian depannya saja untuk kolom ini
                                                        if (str_contains($cleanNote, '| Pulang:')) {
                                                            $parts = explode('| Pulang:', $cleanNote);
                                                            $cleanNote = trim($parts[0]);
                                                        }
                                                        // Gunakan notes user jika ada
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

                                                        {{-- TAMPILKAN LABEL / NOTES DI SINI --}}
                                                        <small class="d-block text-muted mt-1 text-wrap"
                                                            style="font-size: 10px; line-height: 1.2;">
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
                                                        <span
                                                            class="{{ $att->is_early_checkout ? 'text-warning fw-bold' : '' }}">
                                                            {{ $att->check_out_time->format('H:i') }}
                                                        </span>
                                                        @if ($att->is_early_checkout)
                                                            <span class="badge bg-warning ms-1"
                                                                style="font-size: 9px;">Cepat</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="badge bg-secondary">Belum Pulang</span>
                                                @endif
                                            </td>

                                            {{-- FOTO PULANG (MODIFIKASI: MENAMPILKAN NOTES PULANG) --}}
                                            <td>
                                                @php
                                                    // Logika Label Pulang
                                                    $labelPulang = 'Pulang'; // Default

                                                    // Cek jika notes mengandung kata kunci "Pulang:" (dari Controller)
                                                    if (!empty($att->notes) && str_contains($att->notes, 'Pulang:')) {
                                                        $parts = explode('Pulang:', $att->notes);
                                                        $labelPulang = trim(end($parts)); // Ambil bagian akhir setelah kata 'Pulang:'
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

                                                        {{-- TAMPILKAN LABEL / NOTES PULANG DI SINI --}}
                                                        <small class="d-block text-muted mt-1 text-wrap"
                                                            style="font-size: 10px; line-height: 1.2;">
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
                                                            str_contains($statusLower, 'wfh') ||
                                                                str_contains($statusLower, 'dinas')
                                                                => 'bg-info',
                                                            str_contains($statusLower, 'telat')
                                                                => 'bg-warning text-dark',
                                                            $statusLower == 'sakit' => 'bg-primary',
                                                            $statusLower == 'cuti' || $statusLower == 'izin'
                                                                => 'bg-secondary',
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

                                            {{-- KOLOM VERIFIKASI --}}
                                            <td>
                                                @if ($att->status == 'verified')
                                                    @if ($att->presence_status == 'Alpha')
                                                        <div class="d-flex align-items-center">
                                                            <i class="mdi mdi-robot text-danger me-1"></i>
                                                            <span class="badge bg-danger verification-badge">System
                                                                Auto</span>
                                                        </div>
                                                    @elseif($att->attendance_type == 'manual')
                                                        <div class="d-flex align-items-center">
                                                            <i class="mdi mdi-pencil-box-outline text-info me-1"></i>
                                                            <span
                                                                class="badge bg-info text-white verification-badge">Dikoreksi</span>
                                                        </div>
                                                    @else
                                                        <div class="d-flex align-items-center">
                                                            <i class="mdi mdi-check-circle verified-check me-1"></i>
                                                            <span
                                                                class="badge bg-success verification-badge">Terverifikasi</span>
                                                        </div>
                                                    @endif
                                                @elseif($att->status == 'pending_verification')
                                                    <div class="d-flex align-items-center">
                                                        <i class="mdi mdi-clock-outline pending-clock me-1"></i>
                                                        <span
                                                            class="badge bg-warning text-dark verification-badge">Menunggu</span>
                                                    </div>
                                                @elseif($att->status == 'rejected')
                                                    <span class="badge bg-danger verification-badge">Ditolak</span>
                                                @else
                                                    <span class="badge bg-secondary verification-badge">Belum Verif</span>
                                                @endif
                                            </td>

                                            {{-- BUKTI AUDIT --}}
                                            <td>
                                                @if ($att->audit_photo_path)
                                                    <div class="d-inline-block text-center">
                                                        <img src="{{ asset('storage/' . $att->audit_photo_path) }}"
                                                            alt="Bukti Audit"
                                                            class="audit-photo-thumb shadow-sm img-clickable"
                                                            data-bs-toggle="modal" data-bs-target="#imagePreviewModal"
                                                            data-img-src="{{ asset('storage/' . $att->audit_photo_path) }}"
                                                            data-img-title="Bukti Audit">
                                                        <small class="d-block text-center text-muted mt-1"
                                                            style="font-size: 9px;">Audit</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>

                                            {{-- METODE --}}
                                            <td>
                                                @if ($att->attendance_type == 'scan')
                                                    <span class="badge badge-outline-primary"><i
                                                            class="mdi mdi-qrcode-scan me-1"></i> Scan</span>
                                                @elseif($att->attendance_type == 'self')
                                                    <span class="badge badge-outline-info"><i
                                                            class="mdi mdi-camera-front-variant me-1"></i> Selfie</span>
                                                @elseif($att->attendance_type == 'system')
                                                    <span class="badge badge-outline-danger">System</span>
                                                @elseif($att->attendance_type == 'manual')
                                                    <span class="badge badge-outline-warning">Audit Edit</span>
                                                @endif
                                            </td>

                                            {{-- AKSI AUDIT --}}
                                            @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                                                <td class="action-buttons">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        @if ($att->status != 'verified')
                                                            <button type="button" class="btn btn-success"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#verifyModal{{ $att->id }}"
                                                                title="Verifikasi">
                                                                <i class="mdi mdi-check"></i>
                                                            </button>
                                                        @endif

                                                        @if (auth()->user()->role == 'audit')
                                                            <button type="button" class="btn btn-info text-white"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editAuditModal{{ $att->id }}"
                                                                title="Koreksi">
                                                                <i class="mdi mdi-pencil"></i>
                                                            </button>
                                                        @endif

                                                        @if ($att->status != 'verified')
                                                            <button type="button" class="btn btn-danger"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#rejectModal{{ $att->id }}"
                                                                title="Tolak">
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
                            <p class="text-muted">Tidak ada riwayat pada periode
                                {{ date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================= --}}
    {{-- MODAL PREVIEW IMAGE --}}
    {{-- ============================================================= --}}
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0 shadow-none">
                <div class="modal-header border-0 p-0 mb-2">
                    <h5 class="modal-title text-white" id="imagePreviewModalLabel">Preview</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img src="" id="previewImage" class="img-fluid rounded" alt="Preview"
                        style="max-height: 85vh; width: auto; box-shadow: 0 5px 15px rgba(0,0,0,0.5);">
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================= --}}
    {{-- MODAL SECTION --}}
    {{-- ============================================================= --}}
    @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin') && $history->count() > 0)
        @foreach ($history as $att)
            {{-- 1. Modal Verifikasi --}}
            <div class="modal fade" id="verifyModal{{ $att->id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="mdi mdi-check-circle text-success me-2"></i>
                                Verifikasi Absensi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('audit.verify.attendance', $att->id) }}" method="POST"
                            enctype="multipart/form-data">
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
                                            <option value="Masuk"
                                                {{ $att->presence_status == 'Masuk' ? 'selected' : '' }}>
                                                ✅ Masuk</option>
                                            <option value="Sakit"
                                                {{ $att->presence_status == 'Sakit' ? 'selected' : '' }}>
                                                🤒 Sakit</option>
                                            <option value="Cuti"
                                                {{ $att->presence_status == 'Cuti' ? 'selected' : '' }}>
                                                🏖️ Cuti</option>
                                            <option value="Alpha"
                                                {{ $att->presence_status == 'Alpha' ? 'selected' : '' }}>
                                                ❌ Alpha</option>
                                            <option value="Telat"
                                                {{ $att->presence_status == 'Telat' ? 'selected' : '' }}>
                                                ⏰ Telat</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Bukti Audit</label>
                                        <input type="file" name="audit_photo" class="form-control">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label>Catatan</label>
                                    <textarea name="audit_note" class="form-control">{{ $att->audit_note }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-success">Simpan Verifikasi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 2. MODAL EDIT KHUSUS AUDIT --}}
            @if (auth()->user()->role == 'audit')
                <div class="modal fade" id="editAuditModal{{ $att->id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title text-white">
                                    <i class="mdi mdi-pencil-box me-2"></i> Koreksi Data Absensi (Audit)
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('audit.update.attendance', $att->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <div class="modal-body">
                                    <div class="alert alert-warning d-flex align-items-center">
                                        <i class="mdi mdi-alert fs-4 me-2"></i>
                                        <div>
                                            <small class="fw-bold d-block">Perhatian!</small>
                                            <small class="lh-1">Anda sedang mengoreksi data. Perubahan ini akan
                                                tercatat.</small>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold small text-uppercase text-muted">Jam
                                                Masuk</label>
                                            <input type="time" name="check_in_time" class="form-control"
                                                value="{{ $att->check_in_time->format('H:i') }}" required>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold small text-uppercase text-muted">Jam
                                                Pulang</label>
                                            <input type="time" name="check_out_time" class="form-control"
                                                value="{{ $att->check_out_time ? $att->check_out_time->format('H:i') : '' }}">
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold small text-uppercase text-muted">Status
                                                Kehadiran</label>
                                            <select name="presence_status" class="form-select" required>
                                                <option value="" disabled>-- Pilih Status --</option>
                                                <optgroup label="Hadir / Bekerja">
                                                    <option value="Masuk"
                                                        {{ $att->presence_status == 'Masuk' ? 'selected' : '' }}>
                                                        ✅ Masuk (Normal)</option>
                                                    <option value="Izin Telat"
                                                        {{ $att->presence_status == 'Izin Telat' ? 'selected' : '' }}>
                                                        📨 Izin Telat</option>
                                                    <option value="WFH / Dinas Luar"
                                                        {{ stripos($att->presence_status, 'WFH') !== false || stripos($att->presence_status, 'Dinas') !== false ? 'selected' : '' }}>
                                                        🏠 WFH / Dinas Luar</option>
                                                    <option value="Telat"
                                                        {{ $att->presence_status == 'Telat' ? 'selected' : '' }}>
                                                        ⏰ Telat (Hadir)</option>
                                                </optgroup>
                                                <optgroup label="Tidak Hadir / Izin">
                                                    <option value="Alpha"
                                                        {{ $att->presence_status == 'Alpha' ? 'selected' : '' }}>
                                                        ❌ Alpha / Belum Hadir</option>
                                                    <option value="Izin"
                                                        {{ $att->presence_status == 'Izin' ? 'selected' : '' }}>
                                                        📝 Libur / Izin</option>
                                                    <option value="Sakit"
                                                        {{ $att->presence_status == 'Sakit' ? 'selected' : '' }}>
                                                        🤒 Sakit</option>
                                                    <option value="Cuti"
                                                        {{ $att->presence_status == 'Cuti' ? 'selected' : '' }}>
                                                        🏖️ Cuti</option>
                                                </optgroup>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold small text-uppercase text-muted">Status
                                                Verifikasi</label>
                                            <select name="status" class="form-select" required>
                                                <option value="verified"
                                                    {{ $att->status == 'verified' ? 'selected' : '' }}>
                                                    Disetujui (Verified)</option>
                                                <option value="pending_verification"
                                                    {{ $att->status == 'pending_verification' ? 'selected' : '' }}>
                                                    Menunggu (Pending)</option>
                                                <option value="rejected"
                                                    {{ $att->status == 'rejected' ? 'selected' : '' }}>
                                                    Ditolak</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Upload Bukti
                                            Koreksi</label>
                                        <input type="file" name="audit_photo" class="form-control" accept="image/*">
                                        <small class="text-muted">Upload bukti screenshot/foto jika diperlukan.</small>
                                        @if ($att->audit_photo_path)
                                            <div class="mt-2">
                                                <small class="text-success"><i class="mdi mdi-check me-1"></i> Bukti saat
                                                    ini tersedia.</small>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Catatan
                                            Koreksi</label>
                                        <textarea name="audit_note" class="form-control" rows="2" placeholder="Alasan perubahan data...">{{ $att->audit_note }}</textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-info text-white">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 3. Modal Tolak --}}
            <div class="modal fade" id="rejectModal{{ $att->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title text-danger"><i class="mdi mdi-close-circle me-2"></i> Tolak Absensi
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('audit.reject', $att->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <div class="modal-body">
                                <p>Yakin ingin menolak absensi <strong>{{ $employee->name }}</strong> tanggal
                                    {{ $att->check_in_time->format('d M Y') }}?</p>
                                <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Alasan penolakan..." required></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger">Tolak Absensi</button>
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
        // Auto-submit filter ketika bulan/tahun berubah
        document.addEventListener('DOMContentLoaded', function() {
            const monthSelect = document.querySelector('select[name="month"]');
            const yearSelect = document.querySelector('select[name="year"]');

            if (monthSelect && yearSelect) {
                monthSelect.addEventListener('change', function() {
                    this.form.submit();
                });

                yearSelect.addEventListener('change', function() {
                    this.form.submit();
                });
            }

            // Script untuk menangani modal preview gambar
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
