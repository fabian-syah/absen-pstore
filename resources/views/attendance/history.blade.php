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
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 w-100">
            <div class="d-flex align-items-center">
                <a href="{{ route('team.branch.detail', $employee->branch_id) }}"
                    class="btn btn-sm btn-light btn-icon me-3 rounded-circle shadow-sm">
                    <i class="mdi mdi-arrow-left text-primary"></i>
                </a>
                <div>
                    <h4 class="mb-0 fw-bold text-white">Riwayat Absensi: {{ $employee->name }}</h4>
                    <small class="text-white-50">
                        <i class="mdi mdi-domain me-1"></i> {{ $employee->division->name ?? '-' }} |
                        <i class="mdi mdi-office-building me-1"></i> {{ $employee->branch->name ?? '-' }}
                    </small>
                </div>
            </div>

            {{-- NAVIGASI KARYAWAN - VERSI FIX WARNA HITAM --}}
                    @if (isset($branchId) && (isset($prevEmployee) || isset($nextEmployee)))
                        <div class="nav-container-fixed shadow-sm">
                            {{-- Tombol Prev --}}
                            @if ($prevEmployee)
                                <a href="{{ route('team.branch.employee.history', ['branchId' => $branchId, 'employeeId' => $prevEmployee->id, 'month' => $selectedMonth, 'year' => $selectedYear]) }}"
                                    class="nav-btn-action">
                                    <i class="mdi mdi-chevron-left"></i>
                                    <span class="d-none d-lg-inline">{{ Str::limit($prevEmployee->name, 8) }}</span>
                                </a>
                            @endif

                            {{-- Indikator Angka (2/6) --}}
                            <div class="nav-number-badge">
                                {{ $currentEmployeeIndex ?? 1 }} / {{ $employeeCount ?? 1 }}
                            </div>

                            {{-- Tombol Next --}}
                            @if ($nextEmployee)
                                <a href="{{ route('team.branch.employee.history', ['branchId' => $branchId, 'employeeId' => $nextEmployee->id, 'month' => $selectedMonth, 'year' => $selectedYear]) }}"
                                    class="nav-btn-action">
                                    <span class="d-none d-lg-inline">{{ Str::limit($nextEmployee->name, 8) }}</span>
                                    <i class="mdi mdi-chevron-right"></i>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
    @else
            <h4 class="mb-0 fw-bold text-white">Riwayat Absensi Saya</h4>
        @endif
@endsection

@push('styles')
    <style>
        /* ============================================================
           FIX NAVIGASI: MEMAKSA WARNA HITAM PEKAT
           ============================================================ */

        .nav-container-fixed {
            display: flex !important;
            align-items: center !important;
            background-color: #ffffff !important; /* Latar Putih */
            padding: 5px 10px !important;
            border-radius: 50px !important;
            border: 2px solid #ffc107 !important; /* Border Kuning */
            z-index: 9999 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;
        }

        /* Selector ini sangat spesifik untuk menimpa warna putih template */
        .nav-container-fixed a.nav-btn-action,
        .nav-container-fixed a.nav-btn-action span,
        .nav-container-fixed a.nav-btn-action i {
            color: #000000 !important; /* HITAM PEKAT */
            text-decoration: none !important;
            font-weight: 800 !important;
            font-size: 0.85rem !important;
            opacity: 1 !important;
        }

        .nav-container-fixed a.nav-btn-action:hover {
            background-color: #f2f2f2 !important;
            border-radius: 50px !important;
        }

        .nav-number-badge {
            background-color: #ffc107 !important; /* Kuning */
            color: #000000 !important; /* Teks Hitam */
            padding: 4px 14px !important;
            border-radius: 50px !important;
            font-weight: 900 !important;
            font-size: 0.9rem !important;
            min-width: 55px !important;
            text-align: center !important;
            margin: 0 5px !important;
            border: 1px solid rgba(0, 0, 0, 0.1) !important;
        }

        /* Styling umum lainnya */
        .verification-badge {
            font-size: 0.7rem;
            padding: 0.35rem 0.6rem;
            border-radius: 6px;
            font-weight: 600;
        }

        .audit-mode-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .img-clickable {
            cursor: pointer;
            transition: transform 0.2s;
        }

        .img-clickable:hover { transform: scale(1.05); }

        .table thead th {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            background-color: #f8f9fa;
            color: #495057;
        }

        .verifier-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 6px 10px;
            border: 1px solid #e9ecef;
        }

        .note-box {
            font-size: 0.65rem;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 4px;
        }

        .late-approval-badge {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            font-size: 0.6rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            animation: pulse-warning 2s infinite;
        }

        @keyframes pulse-warning {
            0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
            50% { box-shadow: 0 0 0 4px rgba(245, 158, 11, 0); }
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
                            <form action="{{ isset($employee) ? route('team.branch.employee.history', ['branchId' => $employee->branch_id, 'employeeId' => $employee->id]) : route('attendance.history') }}"
                                method="GET" class="row align-items-center gx-2">
                                <div class="col-auto">
                                    <select name="month" class="form-select form-select-sm rounded-pill px-3" onchange="this.form.submit()">
                                        @foreach (range(1, 12) as $m)
                                            <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <select name="year" class="form-select form-select-sm rounded-pill px-3" onchange="this.form.submit()">
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
                                class="btn btn-danger btn-sm text-white rounded-pill px-3">
                                <i class="mdi mdi-file-pdf-box me-1"></i> PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SUMMARY BOXES --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card bg-primary text-white border-0 shadow-sm rounded-4">
                        <div class="card-body p-3 text-center">
                            <h6 class="text-white-50 mb-1 small fw-bold">Total Hari</h6>
                            <h2 class="fw-bold mb-0">{{ $summary['total'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card bg-success text-white border-0 shadow-sm rounded-4">
                        <div class="card-body p-3 text-center">
                            <h6 class="text-white-50 mb-1 small fw-bold">Hadir/WFH</h6>
                            <h2 class="fw-bold mb-0">{{ $summary['present'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card bg-info text-white border-0 shadow-sm rounded-4">
                        <div class="card-body p-3 text-center">
                            <h6 class="text-white-50 mb-1 small fw-bold">Sakit & Izin</h6>
                            <h2 class="fw-bold mb-0">{{ $summary['sakit'] + $summary['izin'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card bg-secondary text-white border-0 shadow-sm rounded-4">
                        <div class="card-body p-3 text-center">
                            <h6 class="text-white-50 mb-1 small fw-bold">Alpha</h6>
                            <h2 class="fw-bold mb-0">{{ $summary['alpha'] }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DETAIL ATTENDANCE --}}
            <div class="card shadow-sm rounded-4 border-0">
                <div class="card-body p-0">
                    <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold text-primary"><i class="mdi mdi-history me-2"></i> Detail Absensi</h5>
                        @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                            <span class="badge audit-mode-badge px-3 py-2 rounded-pill">Mode Audit</span>
                        @endif
                    </div>

                    @if ($history->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Tanggal</th>
                                        <th>Masuk</th>
                                        <th>Foto In</th>
                                        <th class="border-start">Pulang</th>
                                        <th>Foto Out</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($history as $att)
                                        <tr>
                                            <td class="ps-4 py-3">
                                                <div class="fw-bold">{{ $att->check_in_time->format('d M Y') }}</div>
                                                <small class="text-muted">{{ $att->check_in_time->format('l') }}</small>
                                            </td>
                                            <td><span class="fw-bold {{ $att->is_late_checkin ? 'text-danger' : '' }}">{{ $att->check_in_time->format('H:i') }}</span></td>
                                            <td>
                                                @if($att->photo_path)
                                                    <img src="{{ asset('storage/' . $att->photo_path) }}" class="rounded-3 img-clickable" style="width: 40px; height: 40px; object-fit: cover;"
                                                         data-bs-toggle="modal" data-bs-target="#imagePreviewModal" data-img-src="{{ asset('storage/' . $att->photo_path) }}" data-img-title="Masuk">
                                                @endif
                                            </td>
                                            <td class="border-start">
                                                {{ $att->check_out_time ? $att->check_out_time->format('H:i') : '-' }}
                                            </td>
                                            <td>
                                                @if($att->photo_out_path)
                                                    <img src="{{ asset('storage/' . $att->photo_out_path) }}" class="rounded-3 img-clickable" style="width: 40px; height: 40px; object-fit: cover;"
                                                         data-bs-toggle="modal" data-bs-target="#imagePreviewModal" data-img-src="{{ asset('storage/' . $att->photo_out_path) }}" data-img-title="Pulang">
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge verification-badge {{ $att->presence_status == 'Masuk' ? 'bg-success' : 'bg-info' }}">
                                                    {{ $att->presence_status ?? '-' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin') && $att->id)
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-success text-white" data-bs-toggle="modal" data-bs-target="#verifyModal{{ $att->id }}"><i class="mdi mdi-check"></i></button>
                                                        <button class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#editAuditModal{{ $att->id }}"><i class="mdi mdi-pencil"></i></button>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <h5 class="text-muted fw-bold">Belum Ada Data</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PREVIEW IMAGE --}}
    <div class="modal fade" id="imagePreviewModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-header border-0 p-0 mb-2">
                    <h5 class="modal-title text-white fw-bold">Preview</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img src="" id="previewImage" class="img-fluid rounded-4 shadow-lg border border-3 border-white">
                </div>
            </div>
        </div>
    </div>

    {{-- MODALS AUDIT LOOP --}}
    @if (isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin') && $history->count() > 0)
        @foreach ($history as $att)
            @if ($att->id)
                <div class="modal fade" id="verifyModal{{ $att->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content rounded-4 border-0">
                            <div class="modal-header bg-success text-white rounded-top-4">
                                <h5 class="modal-title fw-bold">Verifikasi Absensi</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('audit.verify.attendance', $att->id) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="modal-body p-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Status Kehadiran</label>
                                        <select name="presence_status" class="form-select" required>
                                            <option value="Masuk">✅ Masuk</option>
                                            <option value="WFH">🏠 WFH</option>
                                            <option value="Sakit">🤒 Sakit</option>
                                            <option value="Izin">📝 Izin</option>
                                        </select>
                                    </div>
                                    <textarea name="audit_note" class="form-control" rows="2" placeholder="Catatan..."></textarea>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">Simpan</button>
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
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label class="small fw-bold">Jam Masuk</label>
                                            <input type="time" name="check_in_time" class="form-control" value="{{ $att->check_in_time->format('H:i') }}" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="small fw-bold">Jam Pulang</label>
                                            <input type="time" name="check_out_time" class="form-control" value="{{ $att->check_out_time ? $att->check_out_time->format('H:i') : '' }}">
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label class="small fw-bold">Bukti Audit (Wajib)</label>
                                        <input type="file" name="audit_photo" class="form-control" required>
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-info text-white rounded-pill px-4 fw-bold">Update</button>
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
                    imagePreviewModal.querySelector('.modal-title').textContent = imgTitle;
                    imagePreviewModal.querySelector('#previewImage').src = imgSrc;
                });
            }
        });
    </script>
@endpush