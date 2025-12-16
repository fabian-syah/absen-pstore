@extends('layout.master')

@section('title', 'Tim & Cabang Saya')
@section('heading', 'Monitoring Tim & Wilayah')

@push('styles')
    <style>
        .stat-card { border: none; border-radius: 16px; background: white; transition: all 0.3s ease; box-shadow: 0 2px 15px rgba(0, 0, 0, 0.03); }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08); }
        .stat-icon-box { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; }
        .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
        .bg-soft-warning { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; }
        .bg-soft-danger  { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }
        .bg-soft-indigo  { background-color: rgba(102, 16, 242, 0.1); color: #6610f2; border: 1px solid rgba(102, 16, 242, 0.2); }
        .bg-soft-purple  { background-color: rgba(147, 51, 234, 0.1); color: #9333ea; border: 1px solid rgba(147, 51, 234, 0.2); }
        .team-card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); overflow: hidden; }
        .team-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2rem; color: white; }
        .team-count { background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .member-card { transition: all 0.3s ease; border-left: 4px solid transparent; }
        .member-card:hover { background: #f8f9ff; border-left-color: #667eea; transform: translateX(5px); }
        .avatar-wrapper { position: relative; }
        .avatar-wrapper::after { content: ''; position: absolute; bottom: 2px; right: 2px; width: 14px; height: 14px; background: #10b981; border: 2px solid white; border-radius: 50%; z-index: 5; }
        .avatar-wrapper.offline::after { background: #94a3b8; }
        .status-badge { font-weight: 600; padding: 0.5rem 1rem; border-radius: 50px; display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; }
        .status-badge i { font-size: 1rem; }
        .view-photo-btn { border: none; padding: 0.4rem 0.8rem; border-radius: 8px; font-weight: 600; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; }
        .view-photo-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .modal-content { border: none; border-radius: 20px; overflow: hidden; }
        .modal-image-wrapper { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); padding: 1rem; }
        
        /* Tambahan Style untuk Header Zona Waktu */
        .timezone-header {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.8rem;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 24px;
        }
    </style>
@endpush

@section('content')

    {{-- STATISTIK PANEL --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div><p class="text-uppercase fw-bold text-muted small mb-1">Total Tim</p><h3 class="mb-0 fw-bold text-dark">{{ $stats['total'] }}</h3></div>
                    <div class="stat-icon-box bg-soft-primary"><i class="mdi mdi-account-group"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div><p class="text-uppercase fw-bold text-muted small mb-1">Hadir / Lembur</p><h3 class="mb-0 fw-bold text-success">{{ $stats['hadir'] }}</h3></div>
                    <div class="stat-icon-box bg-soft-success"><i class="mdi mdi-check-circle"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div><p class="text-uppercase fw-bold text-muted small mb-1">Izin / Sakit</p><h3 class="mb-0 fw-bold text-warning">{{ $stats['izin_sakit'] }}</h3></div>
                    <div class="stat-icon-box bg-soft-warning"><i class="mdi mdi-file-document"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div><p class="text-uppercase fw-bold text-muted small mb-1">Belum Absen</p><h3 class="mb-0 fw-bold text-danger">{{ $stats['belum_hadir'] }}</h3></div>
                    <div class="stat-icon-box bg-soft-danger"><i class="mdi mdi-clock-alert"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-12">
            <div class="card team-card">
                <div class="team-header">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                        <div>
                            <h4 class="mb-2 fw-bold">
                                <i class="mdi mdi-account-multiple-outline me-2"></i>Status Rekan Tim
                            </h4>
                            <p class="mb-0 opacity-75 small">Monitoring kehadiran real-time sesuai zona waktu cabang.</p>
                        </div>
                        <span class="team-count badge rounded-pill px-4 py-2 fs-6">
                            <i class="mdi mdi-account-group me-2"></i>{{ $stats['total'] }} Orang
                        </span>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 py-3" width="5%">#</th>
                                    <th class="py-3" width="40%">Nama & Posisi</th>
                                    <th class="py-3" width="25%">Status (Zona Lokal)</th>
                                    <th class="py-3" width="30%">Bukti / Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- LOOPING PER ZONA WAKTU --}}
                                @forelse ($groupedTeam as $timezone => $members)
                                    
                                    {{-- HEADER ZONA WAKTU --}}
                                    <tr>
                                        <td colspan="4" class="timezone-header">
                                            <i class="mdi mdi-map-clock me-1"></i> ZONA WAKTU: {{ $timezone }} 
                                            <span class="float-end fw-normal small">
                                                Waktu Sekarang: {{ \Carbon\Carbon::now($timezone)->format('H:i') }}
                                            </span>
                                        </td>
                                    </tr>

                                    {{-- LOOPING MEMBER DI ZONA TERSEBUT --}}
                                    @foreach ($members as $key => $member)
                                        @php
                                            $attendance = $member->attendances->first();
                                            $leave = $member->leaveRequests->first();
                                            $isWfh = $leave && $leave->type == 'wfh';
                                            
                                            $memberTz = $timezone; // Pasti sesuai grup
                                            $now = \Carbon\Carbon::now($memberTz);
                                            $todayDate = $now->format('Y-m-d');
                                            
                                            // Variabel Status
                                            $isOvertimeYesterday = false;
                                            $isStillWorkingOvertime = false;

                                            if ($attendance) {
                                                $checkIn = \Carbon\Carbon::parse($attendance->check_in_time)->setTimezone($memberTz);
                                                $checkOut = $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->setTimezone($memberTz) : null;
                                                
                                                // Gunakan isSameDay
                                                $isToday = $checkIn->isSameDay($now);
                                                
                                                if (!$isToday) {
                                                    if ($checkOut && $checkOut->isSameDay($now)) {
                                                        $isOvertimeYesterday = true;
                                                    } elseif (!$checkOut) {
                                                        $isStillWorkingOvertime = true;
                                                    }
                                                }
                                            }
                                            $isOnline = ($attendance && !$attendance->check_out_time) || $isWfh;
                                        @endphp

                                        <tr class="member-card {{ Auth::id() == $member->id ? 'bg-light' : '' }}">
                                            <td class="ps-4 py-3">
                                                <span class="badge bg-light text-dark rounded-circle" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; font-weight: 600;">
                                                    {{ $loop->parent->iteration }}.{{ $loop->iteration }}
                                                </span>
                                            </td>
                                            <td class="py-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-wrapper me-3 flex-shrink-0 {{ $isOnline ? '' : 'offline' }}">
                                                        @if ($member->profile_photo_path)
                                                            <img src="{{ Storage::url($member->profile_photo_path) }}" class="rounded-circle shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">
                                                        @else
                                                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">{{ substr($member->name, 0, 1) }}</div>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1 fw-bold text-dark">{{ $member->name }} @if(Auth::id() == $member->id) (Saya) @endif</h6>
                                                        <small class="text-muted">{{ $member->branch->name ?? 'Pusat' }} ({{ $member->division->name ?? '-' }})</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-3">
                                                {{-- LOGIKA STATUS TEXT --}}
                                                @if ($attendance)
                                                    @if ($isOvertimeYesterday)
                                                        {{-- Pulang Lembur (Data Kemarin) --}}
                                                        <div>
                                                            <span class="status-badge bg-soft-indigo text-primary" data-bs-toggle="tooltip" title="Lembur Lintas Hari">
                                                                <i class="mdi mdi-bed-clock me-1"></i> <span>Habis Lembur</span>
                                                            </span>
                                                            <div class="mt-2">
                                                                <span class="badge bg-light text-danger border border-danger" style="font-size: 0.65rem;">
                                                                    <i class="mdi mdi-clock-alert me-1"></i>Belum Absen Shift Baru
                                                                </span>
                                                            </div>
                                                        </div>

                                                    @elseif ($isStillWorkingOvertime)
                                                        {{-- Masih Lembur --}}
                                                        <div>
                                                            <span class="status-badge bg-soft-purple text-dark">
                                                                <i class="mdi mdi-moon-waning-crescent me-1"></i> <span>Sedang Lembur</span>
                                                            </span>
                                                        </div>

                                                    @else
                                                        {{-- Normal Hari Ini --}}
                                                        @if ($attendance->check_out_time)
                                                            <div>
                                                                <span class="status-badge bg-secondary text-white">
                                                                    <i class="mdi mdi-home me-1"></i> Sudah Pulang
                                                                </span>
                                                                <div class="small text-muted mt-1">
                                                                    Masuk: {{ \Carbon\Carbon::parse($attendance->check_in_time)->setTimezone($memberTz)->format('H:i') }}
                                                                    | Pulang: {{ \Carbon\Carbon::parse($attendance->check_out_time)->setTimezone($memberTz)->format('H:i') }}
                                                                </div>
                                                            </div>
                                                        @else
                                                            <div>
                                                                <span class="status-badge bg-success text-white">
                                                                    <i class="mdi mdi-briefcase-check me-1"></i> Sedang Bekerja
                                                                </span>
                                                                <div class="small text-muted mt-1">
                                                                    Masuk: {{ \Carbon\Carbon::parse($attendance->check_in_time)->setTimezone($memberTz)->format('H:i') }}
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif

                                                @elseif ($leave)
                                                    <span class="status-badge bg-info text-white">
                                                        <i class="mdi mdi-file-document me-1"></i> {{ ucfirst($leave->type) }}
                                                    </span>
                                                @else
                                                    <span class="status-badge bg-danger text-white">
                                                        <i class="mdi mdi-close-circle me-1"></i> Belum Hadir
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3">
                                                {{-- BUKTI FOTO --}}
                                                <div class="d-flex gap-2">
                                                    @if ($attendance)
                                                        @if(!$isOvertimeYesterday && !$isStillWorkingOvertime)
                                                            @if($attendance->photo_path)
                                                                <button class="view-photo-btn bg-light text-dark border" data-bs-toggle="modal" data-bs-target="#imageModal" data-src="{{ Storage::url($attendance->photo_path) }}">
                                                                    <i class="mdi mdi-camera"></i> Masuk
                                                                </button>
                                                            @endif
                                                            @if($attendance->photo_out_path)
                                                                <button class="view-photo-btn bg-light text-dark border" data-bs-toggle="modal" data-bs-target="#imageModal" data-src="{{ Storage::url($attendance->photo_out_path) }}">
                                                                    <i class="mdi mdi-camera"></i> Pulang
                                                                </button>
                                                            @endif
                                                        @endif
                                                    @endif
                                                    
                                                    @if ($leave && $leave->type == 'wfh' && $leave->file_proof)
                                                        <button class="view-photo-btn bg-info text-white border-0" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#imageModal" 
                                                                data-src="{{ Storage::url($leave->file_proof) }}">
                                                            <i class="mdi mdi-file-document"></i> Bukti WFH
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach

                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">Tidak ada data tim.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Image --}}
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-body p-0 position-relative modal-image-wrapper">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 shadow" data-bs-dismiss="modal" aria-label="Close" style="z-index: 10;"></button>
                    <img src="" id="modalImageSrc" class="w-100 rounded" alt="Bukti" style="max-height: 80vh; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var imageModal = document.getElementById('imageModal');
        if(imageModal){
            imageModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var src = button.getAttribute('data-src');
                var modalImg = document.getElementById('modalImageSrc');
                modalImg.src = src;
            });
        }
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush