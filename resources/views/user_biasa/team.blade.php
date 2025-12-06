@extends('layout.master')

@section('title', 'Tim & Cabang Saya')
@section('heading', 'Monitoring Tim & Wilayah')

@push('styles')
    {{-- Style tetap sama --}}
    <style>
        .team-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .team-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            color: white;
        }

        .team-count {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .member-card {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .member-card:hover {
            background: #f8f9ff;
            border-left-color: #667eea;
            transform: translateX(5px);
        }

        .avatar-wrapper {
            position: relative;
        }

        /* Default Hijau (Online/WFH/Masuk) */
        .avatar-wrapper::after {
            content: '';
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 14px;
            height: 14px;
            background: #10b981;
            border: 2px solid white;
            border-radius: 50%;
            z-index: 5;
        }

        /* Abu-abu (Offline/Cuti/Sakit/Telat/Belum Absen) */
        .avatar-wrapper.offline::after {
            background: #94a3b8;
        }

        .status-badge {
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .status-badge i {
            font-size: 1rem;
        }

        .division-badge {
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: #4338ca;
            border: none;
            font-weight: 500;
        }

        .branch-badge {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            font-weight: 500;
        }

        .photo-preview {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .photo-preview:hover {
            transform: scale(1.1);
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .view-photo-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .view-photo-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .late-message {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 0.75rem;
            border-radius: 8px;
            font-style: italic;
            color: #92400e;
            max-width: 250px;
        }

        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
        }

        .empty-state-icon {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .audit-pill {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 50px;
            padding: 4px 12px;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .audit-pill:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        @media (max-width: 768px) {
            .team-header {
                padding: 1.5rem;
            }

            .team-header h4 {
                font-size: 1.1rem;
            }

            .status-badge {
                padding: 0.4rem 0.8rem;
                font-size: 0.75rem;
            }

            .member-card {
                padding: 1rem !important;
            }

            .avatar-wrapper {
                width: 45px !important;
                height: 45px !important;
                min-width: 45px !important;
            }
        }

        .modal-content {
            border: none;
            border-radius: 20px;
            overflow: hidden;
        }

        .modal-image-wrapper {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            padding: 1rem;
        }
    </style>
@endpush

@section('content')
    <div class="row mb-5">
        <div class="col-12">
            <div class="card team-card">
                <div class="team-header">
                    <div
                        class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
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

                    {{-- [BARU] Bagian Tampilan Audit Penanggung Jawab --}}
                    @if(isset($assignedAudits) && $assignedAudits->count() > 0)
                        <div class="mt-3 pt-3 border-top border-white border-opacity-25">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <small class="text-white-50 fw-bold text-uppercase me-2" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    <i class="mdi mdi-shield-account me-1"></i> Audit Wilayah:
                                </small>
                                
                                @foreach($assignedAudits as $audit)
                                    <div class="audit-pill">
                                        {{-- Avatar Kecil Audit --}}
                                        @if($audit->profile_photo_path)
                                            <img src="{{ Storage::url($audit->profile_photo_path) }}" 
                                                 class="rounded-circle me-2" 
                                                 width="20" height="20" 
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
                    {{-- Akhir Bagian Audit --}}

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
                                        // 1. Cek Absensi (Masuk/Pulang)
                                        $attendance = $member->attendances->first();

                                        // 2. Cek Izin/Cuti/Sakit/WFH
                                        $leave = $member->leaveRequests->first();

                                        // Cek apakah sedang WFH
                                        $isWfh = $leave && $leave->type == 'wfh';

                                        // Status Online Hijau jika:
                                        // - Sudah Absen Masuk & Belum Pulang
                                        // - ATAU Sedang WFH (Disetujui)
                                        $isOnline = ($attendance && !$attendance->check_out_time) || $isWfh;
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
                                                {{-- 
                                                    LOGIKA CLASS:
                                                    Jika $isOnline (Masuk/WFH) -> class '' (default css hijau)
                                                    Jika tidak -> class 'offline' (css abu-abu)
                                                --}}
                                                <div class="avatar-wrapper me-3 flex-shrink-0 {{ $isOnline ? '' : 'offline' }}"
                                                    style="width: 55px; height: 55px; min-width: 55px;">

                                                    @if ($member->profile_photo_path)
                                                        <img src="{{ Storage::url($member->profile_photo_path) }}"
                                                            class="rounded-circle shadow-sm"
                                                            style="width: 55px; height: 55px; object-fit: cover; border: {{ $member->is_verified ? '3px solid #0d6efd' : '3px solid white' }};">
                                                    @else
                                                        <div class="rounded-circle bg-gradient text-white fw-bold d-flex align-items-center justify-content-center shadow-sm"
                                                            style="width: 55px; height: 55px; font-size: 22px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: {{ $member->is_verified ? '3px solid #0d6efd' : '3px solid white' }};">
                                                            {{ strtoupper(substr($member->name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </div>

                                                <div style="min-width: 0; flex: 1;">
                                                    <h6 class="mb-2 fw-bold text-dark d-flex align-items-center"
                                                        style="font-size: 1rem;">
                                                        {{ $member->name }}
                                                        @if (Auth::id() == $member->id)
                                                            <span class="badge bg-primary ms-2"
                                                                style="font-size: 0.65rem;">SAYA</span>
                                                        @endif
                                                        @if ($member->is_verified)
                                                            <i class="mdi mdi-check-decagram text-primary ms-1"
                                                                title="Terverifikasi"></i>
                                                        @endif
                                                    </h6>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <span class="branch-badge badge" style="font-size: 0.75rem;">
                                                            <i
                                                                class="mdi mdi-map-marker me-1"></i>{{ $member->branch->name ?? 'No Branch' }}
                                                        </span>
                                                        @foreach ($member->divisions as $div)
                                                            <span class="division-badge badge" style="font-size: 0.75rem;">
                                                                <i
                                                                    class="mdi mdi-briefcase-outline me-1"></i>{{ $div->name }}
                                                            </span>
                                                        @break
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- KOLOM STATUS --}}
                                    <td class="py-3">
                                        {{-- LOGIC HITUNG TELAT REALTIME --}}
                                        @php
                                            $isRealLate = false;
                                            $lateText = '';

                                            // Hanya cek telat jika ada data absensi masuk
                                            if ($attendance) {
                                                $scheduleTime = null;

                                                // 1. Cek Jadwal Personal Dulu
                                                if ($member->check_in_start) {
                                                    $scheduleTime = $member->check_in_start;
                                                }
                                                // 2. Jika tidak ada, Cek Jadwal Template (Shift)
                                                elseif ($member->workSchedule) {
                                                    $scheduleTime = $member->workSchedule->start_time;
                                                }

                                                // 3. Bandingkan Waktu
                                                if ($scheduleTime) {
                                                    $actualStr = $attendance->check_in_time->format('H:i');
                                                    $scheduleStr = \Carbon\Carbon::parse($scheduleTime)->format('H:i');

                                                    if ($actualStr > $scheduleStr) {
                                                        $isRealLate = true;
                                                        // Hitung beda menit (opsional)
                                                        $lateMinutes = \Carbon\Carbon::parse(
                                                            $scheduleStr,
                                                        )->diffInMinutes(\Carbon\Carbon::parse($actualStr));
                                                        $lateText = "Telat {$lateMinutes}m";
                                                    }
                                                }
                                            }
                                        @endphp

                                        {{-- PRIORITAS 1: ABSENSI FISIK --}}
                                        @if ($attendance)
                                            @if ($attendance->check_out_time)
                                                {{-- SUDAH PULANG (Tetap Biru, tapi bisa dikasih info jika tadi pagi telat) --}}
                                                <span class="status-badge bg-primary text-white">
                                                    <i class="mdi mdi-home-variant"></i>
                                                    <span>Pulang
                                                        {{ $attendance->check_out_time->format('H:i') }}</span>
                                                </span>
                                            @else
                                                {{-- MASIH DI KANTOR (Cek Merah/Hijau) --}}
                                                <span
                                                    class="status-badge {{ $isRealLate ? 'bg-danger' : 'bg-success' }} text-white">
                                                    <i
                                                        class="mdi {{ $isRealLate ? 'mdi-alert-circle-outline' : 'mdi-briefcase-check' }}"></i>
                                                    <span>
                                                        Masuk {{ $attendance->check_in_time->format('H:i') }}
                                                        @if ($isRealLate)
                                                            <small class="fw-bold ms-1"
                                                                style="font-size: 0.75rem;">({{ $lateText }})</small>
                                                        @endif
                                                    </span>
                                                </span>
                                            @endif

                                            {{-- PRIORITAS 2: WFH / IZIN / SAKIT --}}
                                        @elseif ($leave)
                                            @if ($leave->type == 'wfh')
                                                <span class="status-badge bg-info text-white">
                                                    <i class="mdi mdi-laptop-mac"></i>
                                                    <span>WFH / Dinas Luar</span>
                                                </span>
                                            @elseif ($leave->type == 'sakit')
                                                <span class="status-badge bg-warning text-dark">
                                                    <i class="mdi mdi-medical-bag"></i> <span>Sakit</span>
                                                </span>
                                            @elseif ($leave->type == 'cuti')
                                                <span class="status-badge bg-secondary text-white">
                                                    <i class="mdi mdi-beach"></i> <span>Cuti</span>
                                                </span>
                                            @else
                                                <span class="status-badge bg-warning text-dark">
                                                    <i class="mdi mdi-file-document-outline"></i>
                                                    <span>Izin/Libur</span>
                                                </span>
                                            @endif

                                            {{-- PRIORITAS 3: TELAT / ALPHA --}}
                                        @elseif ($member->activeLateStatus)
                                            <span class="status-badge bg-warning text-dark">
                                                <i class="mdi mdi-clock-alert"></i> <span>Izin Telat</span>
                                            </span>
                                        @else
                                            <span class="status-badge bg-danger text-white">
                                                <i class="mdi mdi-close-circle"></i> <span>Belum Hadir</span>
                                            </span>
                                        @endif
                                    </td>

                                    {{-- KOLOM BUKTI / FOTO --}}
                                    <td class="py-3">
                                        {{-- KONDISI 1: ADA ABSENSI BIASA (FOTO ABSEN) --}}
                                        @if ($attendance && $attendance->photo_path)
                                            <button type="button" class="view-photo-btn btn btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#imageModal"
                                                data-src="{{ Storage::url($attendance->photo_out_path ?? $attendance->photo_path) }}">
                                                <div class="photo-preview">
                                                    <img src="{{ Storage::url($attendance->photo_out_path ?? $attendance->photo_path) }}"
                                                        style="width: 100%; height: 100%; object-fit: cover;">
                                                </div>
                                                <span>Lihat Foto</span>
                                            </button>

                                            {{-- KONDISI 2: WFH (FOTO BUKTI WFH) --}}
                                        @elseif ($leave && $leave->type == 'wfh' && $leave->file_proof)
                                            <button type="button" class="view-photo-btn btn btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#imageModal"
                                                data-src="{{ Storage::url($leave->file_proof) }}">
                                                <div class="photo-preview">
                                                    <img src="{{ Storage::url($leave->file_proof) }}"
                                                        style="width: 100%; height: 100%; object-fit: cover;">
                                                </div>
                                                <span>Lihat Bukti WFH</span>
                                            </button>

                                            {{-- KONDISI 3: SAKIT/IZIN LAIN (KETERANGAN TEXT) --}}
                                        @elseif ($leave)
                                            <div class="text-muted small fst-italic">
                                                <i class="mdi mdi-information-outline me-1"></i>
                                                @if ($leave->type == 'sakit')
                                                    Surat Dokter Terlampir
                                                @elseif($leave->type == 'cuti')
                                                    Cuti Disetujui
                                                @else
                                                    Izin Disetujui
                                                @endif
                                            </div>

                                            {{-- KONDISI 4: IZIN TELAT --}}
                                        @elseif ($member->activeLateStatus)
                                            <div class="late-message">
                                                <i class="mdi mdi-message-text me-1"></i>
                                                "{{ \Illuminate\Support\Str::limit($member->activeLateStatus->message, 30) }}"
                                            </div>
                                        @else
                                            <span class="text-muted small"><i
                                                    class="mdi mdi-minus-circle me-1"></i>-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="empty-state">
                                        <div class="empty-state-icon"><i class="mdi mdi-account-search"></i></div>
                                        <h5 class="text-muted mb-2">Tidak Ada Data</h5>
                                        <p class="text-muted small mb-0">Belum ada data tim yang ditampilkan.</p>
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

{{-- Modal Image Tetap Sama --}}
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-body p-0 position-relative modal-image-wrapper">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 shadow"
                    data-bs-dismiss="modal" aria-label="Close" style="z-index: 10;"></button>
                <img src="" id="modalImageSrc" class="w-100 rounded" alt="Bukti Absen"
                    style="max-height: 80vh; object-fit: contain;">
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