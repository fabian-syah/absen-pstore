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

            {{-- FILTER & NAVIGASI --}}
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="row align-items-center justify-content-between">
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
                        <div class="col-auto">
                            <form action="{{ isset($employee) ? route('team.branch.employee.history', ['branchId' => $employee->branch_id, 'employeeId' => $employee->id]) : route('attendance.history') }}"
                                method="GET" class="row align-items-center gx-2">
                                <div class="col-auto">
                                    <label class="fw-bold mb-0 me-2"><i class="mdi mdi-calendar-month"></i> Periode:</label>
                                </div>
                                <div class="col-auto">
                                    <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
                                        @foreach (range(1, 12) as $m)
                                            <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                                        @php $startYear = 2025; $currentYear = date('Y'); $endYear = $currentYear + 1; @endphp
                                        @for ($y = $startYear; $y <= $endYear; $y++)
                                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </form>
                        </div>
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
                            <a href="{{ route('attendance.export.pdf', ['month' => $selectedMonth, 'year' => $selectedYear, 'employeeId' => isset($employee) ? $employee->id : null]) }}" 
                               class="btn btn-danger btn-sm text-white ms-2">
                                <i class="mdi mdi-file-pdf-box me-1"></i> Export PDF
                             </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SUMMARY CARDS (Sama seperti sebelumnya) --}}
            <div class="row mb-3">
                <div class="col-md-3 mb-2"><div class="card bg-primary text-white border-0 shadow-sm"><div class="card-body py-3 text-center"><h6 class="text-white mb-1 fw-bold">Total Hari</h6><h2 class="fw-bold text-white mb-0">{{ $summary['total'] }}</h2></div></div></div>
                <div class="col-md-3 mb-2"><div class="card bg-success text-white border-0 shadow-sm"><div class="card-body py-3 text-center"><h6 class="text-white mb-1 fw-bold">Hadir / WFH</h6><h2 class="fw-bold text-white mb-0">{{ $summary['hadir'] }}</h2></div></div></div>
                <div class="col-md-3 mb-2"><div class="card bg-info text-white border-0 shadow-sm"><div class="card-body py-3 text-center"><h6 class="text-white mb-1 fw-bold">Sakit & Izin</h6><h2 class="fw-bold text-white mb-0">{{ $summary['sakit'] + $summary['izin'] }}</h2></div></div></div>
                <div class="col-md-3 mb-2"><div class="card bg-secondary text-white border-0 shadow-sm"><div class="card-body py-3 text-center"><h6 class="text-white mb-1 fw-bold">Alpha / Bolos</h6><h2 class="fw-bold text-white mb-0">{{ $summary['alpha'] }}</h2></div></div></div>
            </div>

            {{-- TABEL DATA --}}
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">
                            Detail Absensi - {{ date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) }}
                            @if (isset($employee)) <br><small class="text-muted">Karyawan: {{ $employee->name }}</small> @endif
                        </h4>
                        @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                            <span class="badge audit-mode-badge fs-6 py-2"><i class="mdi mdi-shield-check me-1"></i> Mode Cross-Check Audit</span>
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
                                                <div class="d-flex align-items-center mb-1">
                                                    <i class="mdi mdi-login text-success me-2"></i>
                                                    <span class="fw-bold text-dark">{{ $att->check_in_time->format('H:i') }}</span>
                                                    @if ($att->is_late_checkin)
                                                        <span class="badge bg-danger ms-1" style="font-size: 9px;">Telat</span>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- FOTO MASUK --}}
                                            <td>
                                                @if ($att->photo_path)
                                                    <div class="d-inline-block text-center" style="max-width: 150px;">
                                                        <img src="{{ asset('storage/' . $att->photo_path) }}" alt="Foto Masuk"
                                                            class="rounded shadow-sm img-clickable"
                                                            style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #e2e8f0;"
                                                            data-bs-toggle="modal" data-bs-target="#imagePreviewModal"
                                                            data-img-src="{{ asset('storage/' . $att->photo_path) }}"
                                                            data-img-title="Bukti Masuk">
                                                    </div>
                                                @else - @endif
                                            </td>

                                            {{-- JAM PULANG --}}
                                            <td>
                                                @if ($att->check_out_time)
                                                    <div class="d-flex align-items-center">
                                                        <i class="mdi mdi-logout text-primary me-2"></i>
                                                        <span class="fw-bold">{{ $att->check_out_time->format('H:i') }}</span>
                                                        @if ($att->is_early_checkout) <span class="badge bg-warning ms-1" style="font-size: 9px;">Cepat</span> @endif
                                                    </div>
                                                @else <span class="badge bg-secondary">Belum Pulang</span> @endif
                                            </td>

                                            {{-- FOTO PULANG --}}
                                            <td>
                                                @if ($att->photo_out_path)
                                                    <div class="d-inline-block text-center" style="max-width: 150px;">
                                                        <img src="{{ asset('storage/' . $att->photo_out_path) }}" alt="Pulang"
                                                            class="rounded shadow-sm img-clickable"
                                                            style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #e2e8f0;"
                                                            data-bs-toggle="modal" data-bs-target="#imagePreviewModal"
                                                            data-img-src="{{ asset('storage/' . $att->photo_out_path) }}"
                                                            data-img-title="Foto Pulang">
                                                    </div>
                                                @else - @endif
                                            </td>

                                            {{-- STATUS KEHADIRAN --}}
                                            <td>
                                                @if ($att->presence_status)
                                                    <span class="badge bg-dark">{{ ucwords($att->presence_status) }}</span>
                                                @else <span class="badge bg-secondary">Belum Diatur</span> @endif
                                            </td>

                                            {{-- KOLOM VERIFIKASI & PETUGAS (LOGIKA UTAMA) --}}
                                            <td>
                                                <div class="d-flex flex-column gap-2">
                                                    
                                                    {{-- === 1. INFO PETUGAS MASUK === --}}
                                                    <div class="d-flex align-items-center">
                                                        @if ($att->scanned_by_user_id && $att->scanner)
                                                            {{-- A. Masuk via SECURITY --}}
                                                            <div class="badge bg-primary bg-opacity-10 text-primary border border-primary p-1 me-2 rounded">
                                                                <i class="mdi mdi-qrcode-scan"></i> IN
                                                            </div>
                                                            <div>
                                                                <span class="d-block fw-bold text-dark" style="font-size: 11px;">{{ $att->scanner->name }}</span>
                                                                <small class="text-muted" style="font-size: 9px;">Security</small>
                                                            </div>

                                                        @elseif ($att->attendance_type == 'self')
                                                            {{-- B. Masuk via MANDIRI --}}
                                                            @if ($att->status == 'pending_verification')
                                                                {{-- Kondisi 1: Belum Diverifikasi --}}
                                                                <div class="badge bg-warning bg-opacity-10 text-dark border border-warning p-1 me-2 rounded">
                                                                    <i class="mdi mdi-clock-outline"></i> IN
                                                                </div>
                                                                <div>
                                                                    <span class="d-block fw-bold text-dark" style="font-size: 11px;">Menunggu</span>
                                                                    <small class="text-muted" style="font-size: 9px;">Verifikasi Audit</small>
                                                                </div>
                                                            @elseif ($att->status == 'verified' && $att->verifier)
                                                                {{-- Kondisi 2: SUDAH Diverifikasi Audit --}}
                                                                <div class="badge bg-success bg-opacity-10 text-success border border-success p-1 me-2 rounded">
                                                                    <i class="mdi mdi-check-decagram"></i> IN
                                                                </div>
                                                                <div>
                                                                    <span class="d-block fw-bold text-dark" style="font-size: 11px;">{{ $att->verifier->name }}</span>
                                                                    <small class="text-muted" style="font-size: 9px;">Audit</small>
                                                                </div>
                                                            @else
                                                                {{-- Fallback (Auto) --}}
                                                                <div class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary p-1 me-2 rounded">
                                                                    <i class="mdi mdi-camera-front-variant"></i> IN
                                                                </div>
                                                                <small class="text-muted" style="font-size: 10px;">Mandiri</small>
                                                            @endif

                                                        @else
                                                            {{-- C. Lainnya (Manual/System) --}}
                                                            <span class="badge bg-secondary text-dark" style="font-size: 10px;">System</span>
                                                        @endif
                                                    </div>

                                                    {{-- === 2. INFO PETUGAS PULANG === --}}
                                                    @if ($att->check_out_time)
                                                        <div class="border-top my-1"></div>
                                                        <div class="d-flex align-items-center">
                                                            @if (str_contains($att->notes, 'Security Scan'))
                                                                {{-- A. Pulang via SECURITY (Cek Notes) --}}
                                                                <div class="badge bg-dark bg-opacity-10 text-dark border border-dark p-1 me-2 rounded">
                                                                    <i class="mdi mdi-logout"></i> OUT
                                                                </div>
                                                                <div>
                                                                    <span class="d-block fw-bold text-dark" style="font-size: 11px;">
                                                                        {{ $att->verifier->name ?? 'Security' }}
                                                                    </span>
                                                                    <small class="text-muted" style="font-size: 9px;">Security</small>
                                                                </div>

                                                            @elseif (str_contains($att->notes, 'Pulang (Selfie)'))
                                                                {{-- B. Pulang via MANDIRI (Cek Notes) --}}
                                                                <div class="badge bg-info bg-opacity-10 text-info border border-info p-1 me-2 rounded">
                                                                    <i class="mdi mdi-camera-front-variant"></i> OUT
                                                                </div>
                                                                <div>
                                                                    <span class="d-block fw-bold text-dark" style="font-size: 11px;">Mandiri</span>
                                                                    <small class="text-muted" style="font-size: 9px;">Selfie</small>
                                                                </div>
                                                            @else
                                                                {{-- C. Default --}}
                                                                <div class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary p-1 me-2 rounded">
                                                                    <i class="mdi mdi-logout"></i> OUT
                                                                </div>
                                                                <small class="text-muted" style="font-size: 9px;">{{ $att->verifier->name ?? 'System' }}</small>
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

    {{-- MODAL PREVIEW IMAGE --}}
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

    {{-- MODALS AUDIT --}}
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
            {{-- Modal Edit & Reject --}}
            {{-- ... (Gunakan modal edit & reject dari kode sebelumnya, sama persis) ... --}}
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