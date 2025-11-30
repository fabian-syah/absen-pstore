@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp

@extends('layout.master')

@section('title', 'Tim & Cabang Saya')
@section('heading', 'Monitoring Tim & Wilayah')

@push('styles')
    <style>
        /* --- Style Tim (Lama) --- */
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

        /* Status Online (Titik Hijau) */
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

        /* --- Style Baru untuk Cabang Saya --- */
        .branch-section-title {
            position: relative;
            padding-left: 1.5rem;
            margin-bottom: 1.5rem;
            color: #1e293b;
            font-weight: 700;
        }

        .branch-section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 6px;
            height: 24px;
            background: linear-gradient(to bottom, #667eea, #764ba2);
            border-radius: 4px;
        }

        .branch-card-item {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid #f1f5f9;
            overflow: hidden;
            height: 100%;
        }

        .branch-card-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.15);
            border-color: #c7d2fe;
        }

        .branch-icon-box {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            font-size: 24px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .branch-card-item:hover .branch-icon-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .branch-stat {
            background: #f8fafc;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
        }

        .branch-stat strong {
            display: block;
            font-size: 1.1rem;
            color: #1e293b;
            margin-bottom: 0.1rem;
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
    {{-- SECTION 1: TABEL TIM SAYA --}}
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
                                @if (count($myBranchIds) > 1)
                                    Menampilkan rekan dari {{ count($myBranchIds) }} cabang yang Anda kelola.
                                @elseif(Auth::user()->branch)
                                    Lokasi: <strong>{{ Auth::user()->branch->name }}</strong>
                                @else
                                    Monitoring kehadiran tim
                                @endif
                            </p>
                        </div>
                        <span class="team-count badge rounded-pill px-4 py-2 fs-6">
                            <i class="mdi mdi-account-group me-2"></i>{{ $myTeam->count() }} Orang
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
                                    <th class="py-3" width="25%">Status Absensi</th>
                                    <th class="py-3" width="30%">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($myTeam as $key => $member)
                                    <tr class="member-card">
                                        <td class="ps-4 py-3">
                                            <span class="badge bg-light text-dark rounded-circle"
                                                style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; font-weight: 600;">
                                                {{ $key + 1 }}
                                            </span>
                                        </td>

                                        <td class="py-3">
                                            <div class="d-flex align-items-center">
                                                @php
                                                    $attendance = $member->attendances->first();
                                                    $isOnline = $attendance && !$attendance->check_out_time;
                                                @endphp

                                                <div class="avatar-wrapper me-3 flex-shrink-0 {{ $isOnline ? '' : 'offline' }}"
                                                    style="width: 55px; height: 55px; min-width: 55px;">
                                                    
                                                    {{-- FOTO PROFIL --}}
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
                                                    {{-- NAMA MEMBER + Ikon Verifikasi --}}
                                                    <h6 class="mb-2 fw-bold text-dark d-flex align-items-center" style="font-size: 1rem;">
                                                        {{ $member->name }}
                                                        @if($member->is_verified)
                                                            <i class="mdi mdi-check-decagram text-primary ms-1" title="Terverifikasi" style="font-size: 16px;"></i>
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
                                                            @if ($loop->iteration >= 1)
                                                                @break
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="py-3">
                                            @if ($attendance)
                                                @if ($attendance->check_out_time)
                                                    <span class="status-badge bg-primary text-white">
                                                        <i class="mdi mdi-home-variant"></i> <span>Pulang
                                                            {{ $attendance->check_out_time->format('H:i') }}</span>
                                                    </span>
                                                @else
                                                    <span class="status-badge bg-success text-white">
                                                        <i class="mdi mdi-briefcase-check"></i> <span>Masuk
                                                            {{ $attendance->check_in_time->format('H:i') }}</span>
                                                    </span>
                                                @endif
                                            @elseif ($member->activeLateStatus)
                                                <span class="status-badge bg-warning text-dark">
                                                    <i class="mdi mdi-file-document"></i> <span>Izin/Sakit</span>
                                                </span>
                                            @else
                                                <span class="status-badge bg-danger text-white">
                                                    <i class="mdi mdi-close-circle"></i> <span>Belum Hadir</span>
                                                </span>
                                            @endif
                                        </td>

                                        <td class="py-3">
                                            @if ($attendance && ($attendance->photo_out_path || $attendance->photo_path))
                                                <button type="button" class="view-photo-btn btn btn-sm"
                                                    data-bs-toggle="modal" data-bs-target="#imageModal"
                                                    data-src="{{ Storage::url($attendance->photo_out_path ?? $attendance->photo_path) }}">
                                                    <div class="photo-preview">
                                                        <img src="{{ Storage::url($attendance->photo_out_path ?? $attendance->photo_path) }}"
                                                            style="width: 100%; height: 100%; object-fit: cover;">
                                                    </div>
                                                    <span>Lihat Foto</span>
                                                </button>
                                            @elseif ($member->activeLateStatus)
                                                <div class="late-message">
                                                    <i class="mdi mdi-message-text me-1"></i>
                                                    "{{ Str::limit($member->activeLateStatus->message, 30) }}"
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
                                            <p class="text-muted small mb-0">Tidak ada rekan kerja ditemukan di cabang yang
                                                Anda kelola.</p>
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

    {{-- SECTION 2: CABANG SAYA (Jika User adalah Audit) --}}
    @if(isset($controlledBranches) && $controlledBranches->count() > 0)
        <div class="row">
            <div class="col-12">
                <h4 class="branch-section-title">Cabang Saya ({{ count($controlledBranches) }})</h4>
            </div>
        </div>

        <div class="row">
            @foreach ($controlledBranches as $branch)
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="branch-card-item p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="branch-icon-box">
                                <i class="mdi mdi-storefront-outline"></i>
                            </div>
                            <span class="badge bg-light text-secondary border">
                                ID: {{ $branch->id }}
                            </span>
                        </div>

                        <h5 class="fw-bold mb-2">{{ $branch->name }}</h5>
                        <p class="text-muted small mb-3" style="min-height: 40px;">
                            <i class="mdi mdi-map-marker-outline me-1"></i>
                            {{ Str::limit($branch->address ?? 'Alamat belum diatur', 50) }}
                        </p>

                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                            <div class="branch-stat">
                                <strong>{{ $branch->users_count }}</strong>
                                Karyawan
                            </div>

                            <a href="{{ route('team.branch.detail', $branch->id) }}"
                                class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                Detail <i class="mdi mdi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Modal Image --}}
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