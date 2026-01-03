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
            @if($employee->branch_id)
                <a href="{{ route('team.branch.detail', $employee->branch_id) }}"
                    class="btn btn-sm btn-light btn-icon me-3 rounded-circle shadow-sm">
                    <i class="mdi mdi-arrow-left text-primary"></i>
                </a>
            @endif
            <div>
                <h4 class="mb-0 fw-bold">Riwayat Absensi: {{ $employee->name }}</h4>
                <small class="text-muted">
                    <i class="mdi mdi-domain me-1"></i> {{ $employee->division->name ?? '-' }} | <i
                        class="mdi mdi-office-building me-1"></i> {{ $employee->branch->name ?? '-' }}
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
        .img-clickable:hover { transform: scale(1.05); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table thead th { font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; background-color: #f8f9fa; border-bottom: 2px solid #e9ecef; white-space: nowrap; }
        .verifier-box { background: #f8f9fa; border-radius: 8px; padding: 6px 10px; border: 1px solid #e9ecef; }
        .form-select-custom { background-color: #fff !important; color: #333 !important; border: 1px solid #ced4da !important; font-weight: 600; }
        .note-box { font-size: 0.65rem; line-height: 1.2; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 4px; margin-top: 4px; max-width: 120px; word-wrap: break-word; color: #495057; }
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
                        <p class="mb-0 small opacity-75">Anda dapat memverifikasi, mengoreksi, dan mengubah status kehadiran karyawan.</p>
                    </div>
                </div>
            @endif

            {{-- FILTER & NAVIGASI --}}
            <div class="card mb-4 border-0 shadow-sm rounded-4">
                <div class="card-body py-3">
                    <div class="row align-items-center justify-content-between g-3">
                        <div class="col-auto">
                            @php
                                $prevParams = ['month' => $prevMonth, 'year' => $prevYear];
                                $prevRoute = isset($employee) ? route('team.branch.employee.history', array_merge(['branchId' => $employee->branch_id ?? 0, 'employeeId' => $employee->id], $prevParams)) : route('attendance.history', $prevParams);
                            @endphp
                            <a href="{{ $prevRoute }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold"><i class="mdi mdi-chevron-left me-1"></i> Prev</a>
                        </div>

                        <div class="col-auto">
                            <form action="{{ isset($employee) ? route('team.branch.employee.history', ['branchId' => $employee->branch_id ?? 0, 'employeeId' => $employee->id]) : route('attendance.history') }}" method="GET" class="row align-items-center gx-2">
                                <div class="col-auto d-none d-md-block">
                                    <label class="fw-bold mb-0 text-muted small text-uppercase"><i class="mdi mdi-calendar-month me-1"></i> Periode:</label>
                                </div>
                                <div class="col-auto">
                                    <select name="month" class="form-select form-select-sm form-select-custom rounded-pill px-3" onchange="this.form.submit()">
                                        @foreach (range(1, 12) as $m)
                                            <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <select name="year" class="form-select form-select-sm form-select-custom rounded-pill px-3" onchange="this.form.submit()">
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
                                $nextRoute = isset($employee) ? route('team.branch.employee.history', array_merge(['branchId' => $employee->branch_id ?? 0, 'employeeId' => $employee->id], $nextParams)) : route('attendance.history', $nextParams);
                            @endphp
                            <a href="{{ $nextRoute }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold">Next <i class="mdi mdi-chevron-right ms-1"></i></a>
                            <a href="{{ route('attendance.export.pdf', ['month' => $selectedMonth, 'year' => $selectedYear, 'employeeId' => isset($employee) ? $employee->id : null]) }}" class="btn btn-danger btn-sm text-white ms-2 rounded-pill px-3 shadow-sm"><i class="mdi mdi-file-pdf-box me-1"></i> PDF</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RINGKASAN --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card bg-primary text-white border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                            <h6 class="text-white-50 mb-1 small text-uppercase fw-bold">Total Hari</h6>
                            <h2 class="fw-bold text-white mb-0 display-6">{{ $history->count() }}</h2>
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

            {{-- TABEL --}}
            <div class="card shadow-sm rounded-4 border-0">
                <div class="card-body p-0">
                    <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold text-primary"><i class="mdi mdi-history me-2"></i> Detail Absensi</h5>
                        @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                            <span class="badge audit-mode-badge px-3 py-2 shadow-sm rounded-pill"><i class="mdi mdi-shield-check me-1"></i> Mode Audit</span>
                        @endif
                    </div>

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
                                        $fixedScheduleIn = $att->scheduled_check_in ?? ($att->user->workSchedule->start_time ?? null);
                                        $fixedScheduleOut = $att->scheduled_check_out ?? ($att->user->workSchedule->end_time ?? null);
                                        $isRealAttendance = !in_array($att->attendance_type, ['system', 'leave']);
                                    @endphp
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="fw-bold text-dark">{{ $att->check_in_time->format('d M Y') }}</div>
                                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">{{ $att->check_in_time->format('l') }}</small>
                                        </td>
                                        <td>
                                            @if($isRealAttendance)
                                                <div class="d-flex flex-column">
                                                    <div class="d-flex align-items-center">
                                                        <i class="mdi mdi-login-variant {{ $att->is_calculated_late ? 'text-danger' : 'text-success' }} me-2 fs-5"></i>
                                                        <span class="fw-bold fs-6 {{ $att->is_calculated_late ? 'text-danger' : 'text-dark' }}">{{ $att->check_in_time->format('H:i') }}</span>
                                                    </div>
                                                    <small class="text-muted ps-4" style="font-size: 0.7rem;">Jadwal: {{ $fixedScheduleIn ? \Carbon\Carbon::parse($fixedScheduleIn)->format('H:i') : '-' }}</small>
                                                </div>
                                            @else
                                                <span class="text-muted ps-4">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $displayPhoto = null;
                                                if ($att->photo_path) {
                                                    $displayPhoto = asset('storage/' . $att->photo_path);
                                                } elseif ($att->leaveRequest && $att->leaveRequest->file_proof) {
                                                    $displayPhoto = asset('storage/' . $att->leaveRequest->file_proof);
                                                }
                                                $noteDisplay = \Illuminate\Support\Str::limit($att->notes ?? '', 25);
                                            @endphp
                                            <div class="d-flex flex-column align-items-start">
                                                @if ($displayPhoto)
                                                    <img src="{{ $displayPhoto }}" class="rounded-3 shadow-sm img-clickable border" style="width: 45px; height: 45px; object-fit: cover;" data-bs-toggle="modal" data-bs-target="#imagePreviewModal" data-img-src="{{ $displayPhoto }}" data-img-title="Masuk: {{ $att->presence_status }}">
                                                @else
                                                    <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted border" style="width: 45px; height: 45px;"><i class="mdi mdi-image-off"></i></div>
                                                @endif
                                                @if($noteDisplay)
                                                    <div class="note-box" title="{{ $att->notes }}"><i class="mdi mdi-note-text-outline me-1"></i>{{ $noteDisplay }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="border-start bg-light bg-opacity-25">
                                            @if($isRealAttendance && $att->check_out_time)
                                                <div class="d-flex flex-column">
                                                    <div class="d-flex align-items-center">
                                                        <i class="mdi mdi-logout-variant text-primary me-2 fs-5"></i>
                                                        <span class="fw-bold fs-6">{{ $att->check_out_time->format('H:i') }}</span>
                                                    </div>
                                                    <small class="text-muted ps-4" style="font-size: 0.7rem;">Jadwal: {{ $fixedScheduleOut ? \Carbon\Carbon::parse($fixedScheduleOut)->format('H:i') : '-' }}</small>
                                                </div>
                                            @else
                                                <span class="text-muted ps-4">-</span>
                                            @endif
                                        </td>
                                        <td class="bg-light bg-opacity-25">
                                            @if($isRealAttendance && $att->photo_out_path)
                                                <img src="{{ asset('storage/' . $att->photo_out_path) }}" class="rounded-3 shadow-sm img-clickable border" style="width: 45px; height: 45px; object-fit: cover;" data-bs-toggle="modal" data-bs-target="#imagePreviewModal" data-img-src="{{ asset('storage/' . $att->photo_out_path) }}" data-img-title="Pulang">
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusLower = strtolower($att->presence_status ?? 'alpha');
                                                $badgeClass = match(true){
                                                    $statusLower == 'masuk' => 'bg-success',
                                                    in_array($statusLower, ['sakit','izin','cuti','izin telat','offday']) => 'bg-info',
                                                    $statusLower == 'alpha' => 'bg-danger',
                                                    default => 'bg-dark'
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }} verification-badge shadow-sm">{{ ucwords($att->presence_status ?? 'Alpha') }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($isRealAttendance && $att->latitude)
                                                <a href="https://www.google.com/maps?q={{ $att->latitude }},{{ $att->longitude }}" target="_blank" class="btn btn-outline-info btn-sm btn-icon rounded-circle"><i class="mdi mdi-map-marker-radius"></i></a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-2">
                                                @if ($att->attendance_type == 'leave')
                                                    <div class="verifier-box d-flex align-items-center">
                                                        <div class="badge bg-secondary text-white p-1 me-2 rounded-1" style="min-width: 35px;">IZIN</div>
                                                        <div class="lh-sm">
                                                            <span class="d-block fw-bold text-dark small">{{ $att->verifier->name ?? 'System' }}</span>
                                                            <small class="text-muted" style="font-size: 0.65rem;">Approved By</small>
                                                        </div>
                                                    </div>
                                                @elseif ($isRealAttendance)
                                                    @if ($att->verifier)
                                                        <div class="verifier-box d-flex align-items-center">
                                                            <div class="badge bg-success text-white p-1 me-2 rounded-1" style="min-width: 35px;">OK</div>
                                                            <div class="lh-sm">
                                                                <span class="d-block fw-bold text-dark small">{{ $att->verifier->name }}</span>
                                                                <small class="text-muted" style="font-size: 0.65rem;">Verified By</small>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="text-muted small ps-2">-</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if ($att->attendance_type == 'scan') <i class="mdi mdi-qrcode-scan text-primary fs-4" title="Scan QR"></i>
                                            @elseif($att->attendance_type == 'self') <i class="mdi mdi-camera-front-variant text-info fs-4" title="Selfie"></i>
                                            @elseif($att->attendance_type == 'manual') <i class="mdi mdi-pencil-box-outline text-warning fs-4" title="Manual Edit"></i>
                                            @elseif($att->attendance_type == 'leave') <i class="mdi mdi-file-document-outline text-secondary fs-4" title="Izin/Cuti"></i>
                                            @else <i class="mdi mdi-cog text-secondary fs-4" title="System Auto"></i>
                                            @endif
                                        </td>
                                        @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                                            <td class="text-end pe-4">
                                                @if($att->id && is_numeric($att->id))
                                                    <div class="btn-group btn-group-sm shadow-sm">
                                                        @if ($att->status != 'verified')
                                                            <button type="button" class="btn btn-success text-white" data-bs-toggle="modal" data-bs-target="#verifyModal{{ $att->id }}"><i class="mdi mdi-check"></i></button>
                                                        @endif
                                                        <button type="button" class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#editAuditModal{{ $att->id }}"><i class="mdi mdi-pencil"></i></button>
                                                    </div>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PREVIEW --}}
    <div class="modal fade" id="imagePreviewModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0 shadow-none">
                <div class="modal-header border-0 p-0 mb-2">
                    <h5 class="modal-title text-white fw-bold">Preview Foto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img src="" id="previewImage" class="img-fluid rounded-4 shadow-lg border border-3 border-white" style="max-height: 85vh; width: auto;">
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL AUDIT (Verifikasi & Edit) --}}
    @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
        @foreach ($history as $att)
            @if($att->id && is_numeric($att->id))
                {{-- Modal Verifikasi --}}
                <div class="modal fade" id="verifyModal{{ $att->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content rounded-4">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title fw-bold">Verifikasi Absensi</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('audit.verify.attendance', $att->id) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="modal-body">
                                    <p>Verifikasi absensi <strong>{{ $employee->name }}</strong> tanggal <strong>{{ $att->check_in_time->format('d M Y') }}</strong>?</p>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="presence_status" class="form-select">
                                            <option value="Masuk" {{ $att->presence_status == 'Masuk' ? 'selected' : '' }}>Masuk</option>
                                            <option value="Sakit" {{ $att->presence_status == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                            <option value="Izin" {{ $att->presence_status == 'Izin' ? 'selected' : '' }}>Izin</option>
                                            <option value="Alpha" {{ $att->presence_status == 'Alpha' ? 'selected' : '' }}>Alpha</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-success">Verifikasi</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Modal Edit Audit --}}
                <div class="modal fade" id="editAuditModal{{ $att->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content rounded-4">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title fw-bold">Edit Absensi (Audit Mode)</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('audit.update.attendance', $att->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <div class="modal-body">
                                    <div class="row mb-3">
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
                                            <option value="Masuk" {{ $att->presence_status == 'Masuk' ? 'selected' : '' }}>Masuk</option>
                                            <option value="Sakit" {{ $att->presence_status == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                            <option value="Izin" {{ $att->presence_status == 'Izin' ? 'selected' : '' }}>Izin</option>
                                            <option value="Cuti" {{ $att->presence_status == 'Cuti' ? 'selected' : '' }}>Cuti</option>
                                            <option value="Alpha" {{ $att->presence_status == 'Alpha' ? 'selected' : '' }}>Alpha</option>
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
                                        <label class="form-label small fw-bold text-danger">Upload Bukti Koreksi (Wajib)</label>
                                        <input type="file" name="audit_photo" class="form-control" {{ $att->audit_photo_path ? '' : 'required' }}>
                                        @if($att->audit_photo_path)
                                            <small class="text-muted">Bukti sudah ada, upload ulang jika ingin mengganti.</small>
                                        @endif
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small fw-bold">Catatan Audit</label>
                                        <textarea name="audit_note" class="form-control" rows="2" placeholder="Alasan koreksi...">{{ $att->audit_note }}</textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-info text-white fw-bold">Simpan Perubahan</button>
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
        document.addEventListener('DOMContentLoaded', function() {
            var imagePreviewModal = document.getElementById('imagePreviewModal');
            if (imagePreviewModal) {
                imagePreviewModal.addEventListener('show.bs.modal', function(event) {
                    var button = event.relatedTarget;
                    var imgSrc = button.getAttribute('data-img-src');
                    var imgTitle = button.getAttribute('data-img-title');
                    imagePreviewModal.querySelector('.modal-title').textContent = imgTitle;
                    imagePreviewModal.querySelector('#previewImage').src = imgSrc;
                });
            }
        });
    </script>
@endpush