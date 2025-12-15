@extends('layout.master')

@section('title', 'Tim & Cabang Saya')
@section('heading', 'Monitoring Tim & Wilayah')

@push('styles')
    <style>
        /* --- CARD STATISTIK MODERN --- */
        .stat-card { border: none; border-radius: 16px; background: white; transition: all 0.3s ease; box-shadow: 0 2px 15px rgba(0, 0, 0, 0.03); }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08); }
        .stat-icon-box { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; }
        .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
        .bg-soft-warning { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; }
        .bg-soft-danger  { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }
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
        .division-badge { background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); color: #4338ca; border: none; font-weight: 500; }
        .branch-badge { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-weight: 500; }
        .photo-preview { width: 30px; height: 30px; border-radius: 6px; overflow: hidden; border: 1px solid rgba(255,255,255,0.5); }
        .view-photo-btn { border: none; padding: 0.4rem 0.8rem; border-radius: 8px; font-weight: 600; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; }
        .view-photo-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .late-message { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 0.75rem; border-radius: 8px; font-style: italic; color: #92400e; max-width: 250px; }
        .empty-state { padding: 4rem 2rem; text-align: center; }
        .empty-state-icon { font-size: 4rem; color: #cbd5e1; margin-bottom: 1rem; }
        .audit-pill { background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.25); border-radius: 50px; padding: 4px 12px; display: inline-flex; align-items: center; transition: all 0.3s ease; }
        .audit-pill:hover { background: rgba(255, 255, 255, 0.25); }
        .modal-content { border: none; border-radius: 20px; overflow: hidden; }
        .modal-image-wrapper { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); padding: 1rem; }
    </style>
@endpush

@section('content')

    {{-- [REVISI] PANEL STATISTIK MODERN --}}
    <div class="row g-3 mb-4">
        {{-- Card 1: Total Tim --}}
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <p class="text-uppercase fw-bold text-muted small mb-1">Total Tim</p>
                        <h3 class="mb-0 fw-bold text-dark">{{ $stats['total'] }}</h3>
                    </div>
                    <div class="stat-icon-box bg-soft-primary">
                        <i class="mdi mdi-account-group"></i>
                    </div>
                </div>
            </div>
        </div>
    
        {{-- Card 2: Hadir --}}
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <p class="text-uppercase fw-bold text-muted small mb-1">Hadir</p>
                        <h3 class="mb-0 fw-bold text-success">{{ $stats['hadir'] }}</h3>
                    </div>
                    <div class="stat-icon-box bg-soft-success">
                        <i class="mdi mdi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    
        {{-- Card 3: Izin/Sakit --}}
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <p class="text-uppercase fw-bold text-muted small mb-1">Izin / Sakit</p>
                        <h3 class="mb-0 fw-bold text-warning">{{ $stats['izin_sakit'] }}</h3>
                    </div>
                    <div class="stat-icon-box bg-soft-warning">
                        <i class="mdi mdi-file-document"></i>
                    </div>
                </div>
            </div>
        </div>
    
        {{-- Card 4: Belum Absen --}}
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <p class="text-uppercase fw-bold text-muted small mb-1">Belum Absen</p>
                        <h3 class="mb-0 fw-bold text-danger">{{ $stats['belum_hadir'] }}</h3>
                    </div>
                    <div class="stat-icon-box bg-soft-danger">
                        <i class="mdi mdi-clock-alert"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- [AKHIR] PANEL STATISTIK --}}

    <div class="row mb-5">
        <div class="col-12">
            <div class="card team-card">
                <div class="team-header">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                        <div>
                            <h4 class="mb-2 fw-bold">
                                @if (count($myBranchIds) > 1)
                                    <i class="mdi mdi-domain me-2"></i>Tim Lintas Cabang
                                @else
                                    <i class="mdi mdi-office-building me-2"></i>Rekan Satu Cabang
                                @endif
                            </h4>
                            <p class="mb-0 opacity-75 small">
                                Monitoring kehadiran tim & diri sendiri
                            </p>
                        </div>
                        <span class="team-count badge rounded-pill px-4 py-2 fs-6">
                            <i class="mdi mdi-account-group me-2"></i>{{ $myTeam->count() }} Orang
                        </span>
                    </div>

                    {{-- PROGRESS BAR KEHADIRAN --}}
                    @php
                        $percent = $stats['total'] > 0 ? round(($stats['hadir'] / $stats['total']) * 100) : 0;
                    @endphp
                    <div class="mt-4">
                        <div class="d-flex justify-content-between text-white-50 small mb-1">
                            <span>Tingkat Kehadiran Hari Ini</span>
                            <span>{{ $percent }}%</span>
                        </div>
                        <div class="progress" style="height: 6px; background: rgba(255,255,255,0.2);">
                            <div class="progress-bar bg-white" role="progressbar" 
                                 style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    {{-- Audit Penanggung Jawab --}}
                    @if(isset($assignedAudits) && $assignedAudits->count() > 0)
                        <div class="mt-3 pt-3 border-top border-white border-opacity-25">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <small class="text-white-50 fw-bold text-uppercase me-2" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    <i class="mdi mdi-shield-account me-1"></i> Audit Wilayah:
                                </small>
                                @foreach($assignedAudits as $audit)
                                    <div class="audit-pill">
                                        @if($audit->profile_photo_path)
                                            <img src="{{ Storage::url($audit->profile_photo_path) }}" 
                                                 class="rounded-circle me-2" width="20" height="20" 
                                                 style="object-fit: cover; border: 1px solid white;">
                                        @else
                                            <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center me-2 fw-bold"
                                                 style="width: 20px; height: 20px; font-size: 10px;">
                                                {{ substr($audit->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <span class="text-white small fw-bold">{{ $audit->name }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 py-3" width="5%">#</th>
                                    <th class="py-3" width="40%">Nama & Posisi</th>
                                    <th class="py-3" width="25%">Status Absensi</th>
                                    <th class="py-3" width="30%">Bukti / Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($myTeam as $key => $member)
                                    @php
                                        $attendance = $member->attendances->first();
                                        $leave = $member->leaveRequests->first();
                                        $isWfh = $leave && $leave->type == 'wfh';
                                        
                                        // Online jika belum checkout
                                        $isOnline = ($attendance && !$attendance->check_out_time) || $isWfh;
                                        
                                        // [FIX LOGIKA TAMPILAN]
                                        // Ambil timezone dari data cabang member, default Asia/Jakarta
                                        $memberTz = $member->branch->timezone ?? 'Asia/Jakarta';
                                        
                                        $isCrossDay = false;
                                        if ($attendance && !$attendance->check_out_time) {
                                            // Bandingkan tanggal CheckIn (di timezone user) dengan Tanggal Sekarang (di timezone user)
                                            // Jika tidak sama, berarti Lintas Hari (masuk kemarin, skrg sudah besok)
                                            $checkInLocal = \Carbon\Carbon::parse($attendance->check_in_time)->setTimezone($memberTz);
                                            $nowLocal = \Carbon\Carbon::now($memberTz);
                                            
                                            if (!$checkInLocal->isSameDay($nowLocal)) {
                                                $isCrossDay = true;
                                            }
                                        }
                                    @endphp
                                    <tr class="member-card {{ Auth::id() == $member->id ? 'bg-light' : '' }}">
                                        <td class="ps-4 py-3">
                                            <span class="badge bg-light text-dark rounded-circle"
                                                style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; font-weight: 600;">
                                                {{ $key + 1 }}
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-wrapper me-3 flex-shrink-0 {{ $isOnline ? '' : 'offline' }}"
                                                    style="width: 55px; height: 55px; min-width: 55px;">
                                                    @if ($member->profile_photo_path)
                                                        <img src="{{ Storage::url($member->profile_photo_path) }}" class="rounded-circle shadow-sm"
                                                            style="width: 55px; height: 55px; object-fit: cover; border: {{ $member->is_verified ? '3px solid #0d6efd' : '3px solid white' }};">
                                                    @else
                                                        <div class="rounded-circle bg-gradient text-white fw-bold d-flex align-items-center justify-content-center shadow-sm"
                                                            style="width: 55px; height: 55px; font-size: 22px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: {{ $member->is_verified ? '3px solid #0d6efd' : '3px solid white' }};">
                                                            {{ strtoupper(substr($member->name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div style="min-width: 0; flex: 1;">
                                                    <h6 class="mb-2 fw-bold text-dark d-flex align-items-center" style="font-size: 1rem;">
                                                        {{ $member->name }}
                                                        @if (Auth::id() == $member->id) <span class="badge bg-primary ms-2" style="font-size: 0.65rem;">SAYA</span> @endif
                                                        @if ($member->is_verified) <i class="mdi mdi-check-decagram text-primary ms-1" title="Terverifikasi"></i> @endif
                                                    </h6>
                                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                                        <span class="branch-badge badge" style="font-size: 0.75rem;">
                                                            <i class="mdi mdi-map-marker me-1"></i>{{ $member->branch->name ?? 'No Branch' }}
                                                        </span>
                                                        @foreach ($member->divisions as $div)
                                                            <span class="division-badge badge" style="font-size: 0.75rem;">
                                                                <i class="mdi mdi-briefcase-outline me-1"></i>{{ $div->name }}
                                                            </span>
                                                        @break
                                                    @endforeach
                                                    
                                                    {{-- TOMBOL WHATSAPP --}}
                                                    @if(!empty($member->phone) && Auth::id() != $member->id)
                                                        <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $member->phone)) }}" 
                                                           target="_blank" class="badge bg-success text-white text-decoration-none border-0" data-bs-toggle="tooltip" title="Chat WhatsApp">
                                                            <i class="mdi mdi-whatsapp"></i> Hubungi
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        {{-- LOGIC TELAT & LINTAS HARI --}}
                                        @php
                                            $isRealLate = false; $lateText = '';
                                            if ($attendance) {
                                                $scheduleTime = $member->check_in_start ?? ($member->workSchedule->start_time ?? null);
                                                if ($scheduleTime) {
                                                    // Bandingkan jam saja, karena tanggalnya sudah difilter
                                                    $actualStr = \Carbon\Carbon::parse($attendance->check_in_time)->setTimezone($memberTz)->format('H:i');
                                                    $scheduleStr = \Carbon\Carbon::parse($scheduleTime)->format('H:i');
                                                    
                                                    // Simple string comparison for Time
                                                    if ($actualStr > $scheduleStr) {
                                                        $isRealLate = true;
                                                        $lateMinutes = \Carbon\Carbon::parse($scheduleStr)->diffInMinutes(\Carbon\Carbon::parse($actualStr));
                                                        $lateText = "Telat {$lateMinutes}m";
                                                    }
                                                }
                                            }
                                        @endphp

                                        @if ($attendance)
                                            @if ($attendance->check_out_time)
                                                <span class="status-badge bg-primary text-white">
                                                    <i class="mdi mdi-home-variant"></i> <span>Pulang {{ \Carbon\Carbon::parse($attendance->check_out_time)->setTimezone($memberTz)->format('H:i') }}</span>
                                                </span>
                                            @else
                                                @if ($isCrossDay)
                                                    <span class="status-badge bg-warning text-dark border border-warning" title="Masuk dari tanggal {{ $attendance->check_in_time->format('d M') }}">
                                                        <i class="mdi mdi-moon-waning-crescent"></i> 
                                                        <span>Lembur (Masih Kerja)</span>
                                                    </span>
                                                @else
                                                    <span class="status-badge {{ $isRealLate ? 'bg-danger' : 'bg-success' }} text-white">
                                                        <i class="mdi {{ $isRealLate ? 'mdi-alert-circle-outline' : 'mdi-briefcase-check' }}"></i>
                                                        <span>Masuk {{ \Carbon\Carbon::parse($attendance->check_in_time)->setTimezone($memberTz)->format('H:i') }} @if ($isRealLate) <small class="fw-bold ms-1" style="font-size: 0.75rem;">({{ $lateText }})</small> @endif</span>
                                                    </span>
                                                @endif
                                            @endif
                                        @elseif ($leave)
                                            @if ($leave->type == 'wfh')
                                                <span class="status-badge bg-info text-white"><i class="mdi mdi-laptop-mac"></i> <span>WFH</span></span>
                                            @elseif ($leave->type == 'sakit')
                                                <span class="status-badge bg-warning text-dark"><i class="mdi mdi-medical-bag"></i> <span>Sakit</span></span>
                                            @elseif ($leave->type == 'cuti')
                                                <span class="status-badge bg-secondary text-white"><i class="mdi mdi-beach"></i> <span>Cuti</span></span>
                                            @else
                                                <span class="status-badge bg-warning text-dark"><i class="mdi mdi-file-document-outline"></i> <span>Izin</span></span>
                                            @endif
                                        @elseif ($member->activeLateStatus)
                                            <span class="status-badge bg-warning text-dark"><i class="mdi mdi-clock-alert"></i> <span>Izin Telat</span></span>
                                        @else
                                            <span class="status-badge bg-danger text-white"><i class="mdi mdi-close-circle"></i> <span>Belum Hadir</span></span>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex flex-wrap gap-2 align-items-center">
                                            @if ($attendance)
                                                {{-- 1. FOTO MASUK --}}
                                                @if($attendance->photo_path)
                                                    <button type="button" class="view-photo-btn bg-success text-white" data-bs-toggle="modal" data-bs-target="#imageModal" data-src="{{ Storage::url($attendance->photo_path) }}">
                                                        <div class="photo-preview"><img src="{{ Storage::url($attendance->photo_path) }}" style="width: 100%; height: 100%; object-fit: cover;"></div> 
                                                        <span>Masuk</span>
                                                    </button>
                                                @endif
                                                
                                                {{-- 2. FOTO PULANG (NEW) --}}
                                                @if($attendance->photo_out_path)
                                                    <button type="button" class="view-photo-btn bg-primary text-white" data-bs-toggle="modal" data-bs-target="#imageModal" data-src="{{ Storage::url($attendance->photo_out_path) }}">
                                                        <div class="photo-preview"><img src="{{ Storage::url($attendance->photo_out_path) }}" style="width: 100%; height: 100%; object-fit: cover;"></div> 
                                                        <span>Pulang</span>
                                                    </button>
                                                @endif

                                            @elseif ($leave && $leave->type == 'wfh' && $leave->file_proof)
                                                <button type="button" class="view-photo-btn bg-info text-white" data-bs-toggle="modal" data-bs-target="#imageModal" data-src="{{ Storage::url($leave->file_proof) }}">
                                                    <div class="photo-preview"><img src="{{ Storage::url($leave->file_proof) }}" style="width: 100%; height: 100%; object-fit: cover;"></div> 
                                                    <span>Bukti WFH</span>
                                                </button>
                                            @elseif ($leave)
                                                <div class="text-muted small fst-italic"><i class="mdi mdi-information-outline me-1"></i> {{ ucfirst($leave->type) }} Approved</div>
                                            @elseif ($member->activeLateStatus)
                                                <div class="late-message"><i class="mdi mdi-message-text me-1"></i> "{{ \Illuminate\Support\Str::limit($member->activeLateStatus->message, 30) }}"</div>
                                            @else
                                                <span class="text-muted small"><i class="mdi mdi-minus-circle me-1"></i>-</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="empty-state">
                                        <div class="empty-state-icon"><i class="mdi mdi-account-search"></i></div>
                                        <h5 class="text-muted mb-2">Tidak Ada Data</h5>
                                    </td>
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
        imageModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var src = button.getAttribute('data-src');
            var modalImg = document.getElementById('modalImageSrc');
            modalImg.src = src;
        });
    });
</script>
@endpush