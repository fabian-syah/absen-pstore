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
                        <p class="mb-0 small opacity-75">Halaman manajemen riwayat absensi karyawan.</p>
                    </div>
                </div>
            @endif

            {{-- FILTER & NAVIGASI --}}
            <div class="card mb-4 border-0 shadow-sm rounded-4">
                <div class="card-body py-3">
                    <div class="row align-items-center justify-content-between g-3">
                        <div class="col-auto">
                            <a href="{{ isset($employee) ? route('team.branch.employee.history', ['branchId' => $employee->branch_id ?? 0, 'employeeId' => $employee->id, 'month' => $prevMonth, 'year' => $prevYear]) : route('attendance.history', ['month' => $prevMonth, 'year' => $prevYear]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold"><i class="mdi mdi-chevron-left me-1"></i> Prev</a>
                        </div>
                        <div class="col-auto">
                            <form action="" method="GET" class="row align-items-center gx-2">
                                <input type="hidden" name="employeeId" value="{{ $employee->id ?? '' }}">
                                <div class="col-auto">
                                    <select name="month" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                                        @foreach(range(1,12) as $m)
                                            <option value="{{$m}}" {{$selectedMonth == $m ? 'selected' : ''}}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <select name="year" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                                        @for($y=2025; $y<=date('Y')+1; $y++)
                                            <option value="{{$y}}" {{$selectedYear == $y ? 'selected' : ''}}>{{$y}}</option>
                                        @endfor
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="col-auto">
                            <a href="{{ isset($employee) ? route('team.branch.employee.history', ['branchId' => $employee->branch_id ?? 0, 'employeeId' => $employee->id, 'month' => $nextMonth, 'year' => $nextYear]) : route('attendance.history', ['month' => $nextMonth, 'year' => $nextYear]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold">Next <i class="mdi mdi-chevron-right ms-1"></i></a>
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
                    <div class="p-4 border-bottom">
                        <h5 class="card-title mb-0 fw-bold text-primary"><i class="mdi mdi-history me-2"></i> Detail Absensi</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Tanggal</th>
                                    <th>Jam Masuk</th>
                                    <th>Foto / Bukti</th>
                                    <th>Jam Pulang</th>
                                    <th class="text-center">Status</th>
                                    <th>Verifikasi & Petugas</th>
                                    <th class="text-center">Metode</th>
                                    @if(isset($employee) && in_array(auth()->user()->role, ['admin','audit']))
                                    <th class="text-end pe-4">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($history as $att)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">{{ $att->check_in_time->format('d M Y') }}</div>
                                        <small class="text-muted">{{ $att->check_in_time->format('l') }}</small>
                                    </td>
                                    <td>{{ $att->attendance_type == 'leave' || $att->attendance_type == 'system' ? '-' : $att->check_in_time->format('H:i') }}</td>
                                    <td>
                                        @php
                                            $img = $att->photo_path ? asset('storage/'.$att->photo_path) : ($att->leaveRequest->file_proof ?? null ? asset('storage/'.$att->leaveRequest->file_proof) : null);
                                        @endphp
                                        @if($img)
                                            <img src="{{ $img }}" class="rounded shadow-sm img-clickable" style="width:40px; height:40px; object-fit:cover" data-bs-toggle="modal" data-bs-target="#imagePreviewModal" data-img-src="{{ $img }}" data-img-title="Bukti Tanggal {{ $att->check_in_time->format('d M') }}">
                                        @else
                                            <span class="text-muted small">No Photo</span>
                                        @endif
                                        @if($att->notes)
                                            <div class="note-box" title="{{ $att->notes }}">{{ \Illuminate\Support\Str::limit($att->notes, 20) }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $att->check_out_time ? $att->check_out_time->format('H:i') : '-' }}</td>
                                    <td class="text-center">
                                        @php
                                            $st = strtolower($att->presence_status ?? 'alpha');
                                            $color = match(true){
                                                $st == 'masuk' => 'success',
                                                in_array($st, ['sakit','izin','cuti','offday']) => 'info',
                                                $st == 'alpha' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{$color}} verification-badge">{{ ucwords($st) }}</span>
                                    </td>
                                    <td>
                                        @if($att->attendance_type == 'leave')
                                            <div class="verifier-box">
                                                <small class="d-block fw-bold">{{ $att->verifier->name ?? 'System' }}</small>
                                                <small class="text-muted" style="font-size:0.6rem">Approved Izin</small>
                                            </div>
                                        @elseif($att->verifier)
                                            <div class="verifier-box">
                                                <small class="d-block fw-bold">{{ $att->verifier->name }}</small>
                                                <small class="text-muted" style="font-size:0.6rem">Verified Audit</small>
                                            </div>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($att->attendance_type == 'scan') <i class="mdi mdi-qrcode-scan text-primary fs-5"></i>
                                        @elseif($att->attendance_type == 'leave') <i class="mdi mdi-file-document-outline text-info fs-5"></i>
                                        @elseif($att->attendance_type == 'self') <i class="mdi mdi-camera-account text-success fs-5"></i>
                                        @else <i class="mdi mdi-cog text-muted fs-5"></i> @endif
                                    </td>
                                    @if(isset($employee) && in_array(auth()->user()->role, ['admin','audit']))
                                    <td class="text-end pe-4">
                                        @if($att->id && is_numeric($att->id))
                                        <button class="btn btn-sm btn-info text-white rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#editAuditModal{{ $att->id }}"><i class="mdi mdi-pencil me-1"></i> Edit</button>
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
            <div class="modal-content bg-transparent border-0">
                <div class="modal-header border-0"><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body text-center"><img src="" id="previewImage" class="img-fluid rounded shadow-lg"></div>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT AUDIT --}}
    @if(isset($employee))
    @foreach($history as $att)
        @if($att->id && is_numeric($att->id))
        <div class="modal fade" id="editAuditModal{{ $att->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content rounded-4">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title fw-bold">Edit Absensi: {{ $att->check_in_time->format('d M') }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('audit.update.attendance', $att->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Status Kehadiran</label>
                                <select name="presence_status" class="form-select">
                                    <option value="Masuk" {{ $att->presence_status == 'Masuk' ? 'selected' : '' }}>Masuk</option>
                                    <option value="Sakit" {{ $att->presence_status == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                    <option value="Izin" {{ $att->presence_status == 'Izin' ? 'selected' : '' }}>Izin</option>
                                    <option value="Alpha" {{ $att->presence_status == 'Alpha' ? 'selected' : '' }}>Alpha</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Bukti Koreksi (Wajib)</label>
                                <input type="file" name="audit_photo" class="form-control" {{ $att->audit_photo_path ? '' : 'required' }}>
                            </div>
                            <div class="mb-0">
                                <label class="form-label small fw-bold">Catatan Audit</label>
                                <textarea name="audit_note" class="form-control" rows="2">{{ $att->audit_note }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="submit" class="btn btn-info text-white fw-bold w-100 rounded-pill">Simpan Perubahan</button>
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
                    imagePreviewModal.querySelector('#previewImage').src = imgSrc;
                });
            }
        });
    </script>
@endpush