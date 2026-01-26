@php
    use Illuminate\Support\Facades\Storage;
    use Carbon\Carbon;
@endphp

@extends('layout.master')

@section('title')
    Verifikasi Absensi
@endsection

@section('content')
    <div class="container-fluid px-0">
        {{-- Premium Gradient Header --}}
        <div class="verification-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-1 fw-bold text-dark">
                        <i class="mdi mdi-shield-check-outline me-2 text-primary"></i>Verifikasi Absensi
                    </h4>
                    <p class="text-muted small mb-0">Kelola persetujuan absensi mandiri karyawan</p>
                </div>
                <div class="col-auto d-flex gap-2 align-items-center">
                    <span class="badge-pending-count">
                        <i class="mdi mdi-clock-alert-outline me-1"></i>
                        <span class="fw-bold">{{ $pendingAttendances->total() }}</span> Menunggu
                    </span>
                </div>
            </div>
        </div>

        {{-- Alert Notification --}}
        @if (session('success'))
            <div class="alert alert-success-modern alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <div class="alert-icon bg-success text-white">
                        <i class="mdi mdi-check"></i>
                    </div>
                    <div class="ms-3">{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if (session('error'))
            <div class="alert alert-danger-modern alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <div class="alert-icon bg-danger text-white">
                        <i class="mdi mdi-alert"></i>
                    </div>
                    <div class="ms-3">{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($pendingAttendances->count() > 0)
            {{-- ================================================================================= --}}
            {{-- TAMPILAN DESKTOP (LAPTOP) - TABLE MODE                                            --}}
            {{-- ================================================================================= --}}
            <div class="card card-modern d-none d-md-block">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 table-modern">
                            <thead>
                                <tr>
                                    <th class="ps-4">Karyawan</th>
                                    <th style="min-width: 160px;">Waktu & Lokasi</th>
                                    <th style="width: 200px;">Catatan</th>
                                    <th>Bukti Foto</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingAttendances as $att)
                                    @php
                                        $userTimezone = $att->user->branch->timezone ?? 'Asia/Jakarta';
                                        $checkInLocal = Carbon::parse($att->check_in_time)->timezone($userTimezone);
                                        $checkOutLocal = $att->check_out_time ? Carbon::parse($att->check_out_time)->timezone($userTimezone) : null;
                                        $tzLabel = str_contains($userTimezone, 'Jakarta') ? 'WIB' : (str_contains($userTimezone, 'Makassar') ? 'WITA' : 'WIT');
                                    @endphp
                                    <tr class="attendance-row">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-modern me-3">
                                                    @if($att->user->profile_photo_path)
                                                        <img src="{{ Storage::url($att->user->profile_photo_path) }}" alt="Avatar">
                                                    @else
                                                        {{ substr($att->user->name, 0, 1) }}
                                                    @endif
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold">{{ $att->user->name }}</h6>
                                                    <div class="text-muted small">
                                                        {{ $att->user->division->name ?? 'Staff' }}
                                                        <span class="mx-1">•</span>
                                                        <span class="text-primary">{{ $att->user->branch->name ?? '-' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="time-info">
                                                <div class="time-badge time-in">
                                                    <i class="mdi mdi-login me-1"></i>{{ $checkInLocal->format('H:i') }}
                                                </div>
                                                @if($checkOutLocal)
                                                    <div class="time-badge time-out">
                                                        <i class="mdi mdi-logout me-1"></i>{{ $checkOutLocal->format('H:i') }}
                                                    </div>
                                                @endif
                                                <small class="text-muted d-block mt-1">{{ $checkInLocal->format('d M Y') }} ({{ $tzLabel }})</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="notes-text">
                                                {{ Str::limit($att->notes ?? '-', 50) }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="photo-thumb image-popup" data-img="{{ Storage::url($att->photo_path) }}">
                                                <img src="{{ Storage::url($att->photo_path) }}" loading="lazy" alt="Foto">
                                                <div class="photo-overlay">
                                                    <i class="mdi mdi-magnify-plus"></i>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="action-buttons">
                                                <form action="{{ route('audit.approve', $att->id) }}" method="POST" class="d-inline">
                                                    @csrf @method('PUT')
                                                    <button type="submit" class="btn-action btn-approve" title="Terima">
                                                        <i class="mdi mdi-check"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn-action btn-edit" 
                                                        onclick="openEditModal({{ $att->id }}, '{{ $checkInLocal->format('H:i') }}', '{{ $att->audit_note }}', '{{ $tzLabel }}')"
                                                        title="Edit">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                <form action="{{ route('audit.reject', $att->id) }}" method="POST" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn-action btn-reject" onclick="return confirm('Hapus data absensi ini?')" title="Tolak">
                                                        <i class="mdi mdi-close"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                {{-- Pagination Desktop --}}
                <div class="card-footer bg-transparent border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Menampilkan {{ $pendingAttendances->firstItem() ?? 0 }} - {{ $pendingAttendances->lastItem() ?? 0 }} 
                            dari {{ $pendingAttendances->total() }} data
                        </small>
                        {{ $pendingAttendances->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>

            {{-- ================================================================================= --}}
            {{-- TAMPILAN MOBILE (HP / IPHONE) - CARD MODE                                         --}}
            {{-- ================================================================================= --}}
            <div class="d-md-none">
                @foreach ($pendingAttendances as $att)
                    @php
                        $userTimezone = $att->user->branch->timezone ?? 'Asia/Jakarta';
                        $checkInLocal = Carbon::parse($att->check_in_time)->timezone($userTimezone);
                        $tzLabel = str_contains($userTimezone, 'Jakarta') ? 'WIB' : (str_contains($userTimezone, 'Makassar') ? 'WITA' : 'WIT');
                    @endphp
                    <div class="card card-mobile-modern mb-3">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-modern avatar-sm me-3">
                                    @if($att->user->profile_photo_path)
                                        <img src="{{ Storage::url($att->user->profile_photo_path) }}" alt="Avatar">
                                    @else
                                        {{ substr($att->user->name, 0, 1) }}
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fw-semibold">{{ Str::limit($att->user->name, 22) }}</h6>
                                    <div class="d-flex align-items-center">
                                        <small class="text-primary fw-medium">{{ $att->user->branch->name ?? '-' }}</small>
                                        <small class="text-muted ms-1">({{ $tzLabel }})</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="time-info-mobile mb-3">
                                <div class="time-box">
                                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">MASUK</small>
                                    <span class="fw-bold text-success">{{ $checkInLocal->format('H:i') }}</span>
                                </div>
                                <div class="time-box">
                                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">TANGGAL</small>
                                    <span class="fw-bold">{{ $checkInLocal->format('d/m/y') }}</span>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-4">
                                    <div class="photo-mobile image-popup" data-img="{{ Storage::url($att->photo_path) }}">
                                        <img src="{{ Storage::url($att->photo_path) }}" loading="lazy" alt="Foto">
                                    </div>
                                </div>
                                <div class="col-8">
                                    @if($att->latitude && $att->longitude)
                                        <a href="https://maps.google.com/?q={{ $att->latitude }},{{ $att->longitude }}" target="_blank" class="btn btn-outline-info w-100 h-100 d-flex align-items-center justify-content-center btn-sm rounded-3">
                                            <i class="mdi mdi-map-marker-radius me-1"></i> Peta Lokasi
                                        </a>
                                    @else
                                        <div class="h-100 border rounded-3 d-flex align-items-center justify-content-center text-muted small bg-light">No Location</div>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <form action="{{ route('audit.approve', $att->id) }}" method="POST" class="flex-grow-1">
                                    @csrf @method('PUT')
                                    <button type="submit" class="btn btn-success btn-sm w-100 fw-bold rounded-pill py-2">
                                        <i class="mdi mdi-check me-1"></i>Terima
                                    </button>
                                </form>
                                <button type="button" class="btn btn-warning btn-sm flex-grow-1 fw-bold rounded-pill py-2" 
                                        onclick="openEditModal({{ $att->id }}, '{{ $checkInLocal->format('H:i') }}', '{{ $att->audit_note }}', '{{ $tzLabel }}')">
                                    <i class="mdi mdi-pencil me-1"></i>Edit
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- PAGINATION MOBILE --}}
                <div class="mt-4 mb-5 d-flex justify-content-center">
                    {{ $pendingAttendances->links('pagination::bootstrap-5') }}
                </div>
            </div>

        @else
            {{-- Empty State --}}
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="mdi mdi-check-decagram"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Semua Sudah Diverifikasi!</h5>
                <p class="text-muted small mb-0">Tidak ada absensi yang memerlukan verifikasi saat ini.</p>
            </div>
        @endif
    </div>

    {{-- SINGLE GLOBAL EDIT MODAL (Reusable) --}}
    <div class="modal fade" id="editGlobalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-content modal-modern">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">
                            <i class="mdi mdi-pencil-outline me-2"></i>Koreksi Absensi
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Jam Masuk <span id="tzLabelEdit"></span></label>
                            <input type="time" name="check_in_time" id="editCheckInTime" class="form-control form-control-lg" required>
                        </div>
                        <input type="hidden" name="presence_status" value="Masuk">
                        <input type="hidden" name="status" value="verified">
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Catatan Audit</label>
                            <textarea name="audit_note" id="editAuditNote" class="form-control" rows="2" placeholder="Tambahkan catatan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2 rounded-pill">
                            <i class="mdi mdi-check me-1"></i>Simpan & Verifikasi
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Global Image Modal --}}
    <div class="modal fade" id="imgGlobalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-transparent border-0 shadow-none text-center">
                <img id="imgGlobalSrc" src="" class="img-fluid rounded-4 shadow-lg" style="max-height: 80vh;">
                <div class="mt-3 text-center">
                    <button type="button" class="btn btn-light rounded-pill px-4 shadow-sm" data-bs-dismiss="modal">
                        <i class="mdi mdi-close me-1"></i>Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const editModal = new bootstrap.Modal(document.getElementById('editGlobalModal'));
        
        function openEditModal(id, checkInTime, auditNote, tzLabel) {
            document.getElementById('editForm').action = '/audit/attendance/' + id + '/update';
            document.getElementById('editCheckInTime').value = checkInTime;
            document.getElementById('editAuditNote').value = auditNote || '';
            document.getElementById('tzLabelEdit').textContent = '(' + tzLabel + ')';
            editModal.show();
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Image Handler
            const imgModal = new bootstrap.Modal(document.getElementById('imgGlobalModal'));
            const imgSrc = document.getElementById('imgGlobalSrc');

            document.querySelectorAll('.image-popup').forEach(el => {
                el.addEventListener('click', function() {
                    imgSrc.src = this.getAttribute('data-img');
                    imgModal.show();
                });
            });
        });
    </script>
    <style>
        /* Premium Variables */
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --primary-color: #3b82f6;
        }

        /* Header Styling */
        .verification-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.05);
        }

        /* Badge Count */
        .badge-pending-count {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2);
        }

        /* Modern Card */
        .card-modern {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            overflow: hidden;
        }

        /* Table Modern */
        .table-modern thead {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        }
        .table-modern thead th {
            text-transform: uppercase;
            font-size: 0.7rem;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.5px;
            padding: 1rem 0.75rem;
            border: none;
        }
        .table-modern tbody td {
            padding: 1rem 0.75rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .attendance-row:hover {
            background: #fafbfc;
        }

        /* Avatar Modern */
        .avatar-modern {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(118, 75, 162, 0.2);
        }
        .avatar-modern img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .avatar-modern.avatar-sm {
            width: 36px;
            height: 36px;
            font-size: 0.85rem;
        }

        /* Time Badge */
        .time-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-right: 0.35rem;
        }
        .time-in { background: rgba(16, 185, 129, 0.1); color: var(--success-color); }
        .time-out { background: rgba(239, 68, 68, 0.1); color: var(--danger-color); }

        /* Photo Thumbnail - Square with rounded corners matching card design */
        .photo-thumb {
            position: relative;
            width: 56px;
            height: 56px;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid #e2e8f0;
            background: #f8fafc;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.2s ease;
        }
        .photo-thumb:hover {
            transform: scale(1.08);
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
        }
        .photo-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
        }
        .photo-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.7) 0%, rgba(118, 75, 162, 0.7) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s;
            border-radius: 10px;
        }
        .photo-thumb:hover .photo-overlay {
            opacity: 1;
        }
        .photo-overlay i {
            color: white;
            font-size: 1.3rem;
        }

        /* Notes */
        .notes-text {
            font-size: 0.85rem;
            color: #64748b;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.35rem;
        }
        .btn-action {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-approve { background: rgba(16, 185, 129, 0.12); color: var(--success-color); }
        .btn-approve:hover { background: var(--success-color); color: white; transform: scale(1.05); }
        .btn-edit { background: rgba(245, 158, 11, 0.12); color: var(--warning-color); }
        .btn-edit:hover { background: var(--warning-color); color: white; transform: scale(1.05); }
        .btn-reject { background: rgba(239, 68, 68, 0.12); color: var(--danger-color); }
        .btn-reject:hover { background: var(--danger-color); color: white; transform: scale(1.05); }

        /* Mobile Card */
        .card-mobile-modern {
            border: none;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }

        /* Time Info Mobile */
        .time-info-mobile {
            display: flex;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            overflow: hidden;
        }
        .time-box {
            flex: 1;
            padding: 0.75rem;
            text-align: center;
            display: flex;
            flex-direction: column;
        }
        .time-box:first-child {
            border-right: 1px solid #e2e8f0;
        }

        /* Photo Mobile */
        .photo-mobile {
            border-radius: 10px;
            overflow: hidden;
            height: 65px;
            cursor: pointer;
        }
        .photo-mobile img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 16px;
        }
        .empty-state-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: var(--success-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        .empty-state-icon i {
            font-size: 2.5rem;
        }

        /* Modal Modern */
        .modal-modern {
            border: none;
            border-radius: 16px;
            overflow: hidden;
        }
        .modal-modern .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1.25rem 1.5rem;
        }
        .modal-modern .btn-close {
            filter: brightness(0) invert(1);
        }

        /* Alert Modern */
        .alert-success-modern, .alert-danger-modern {
            border: none;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .alert-success-modern { background: #f0fdf4; border-left: 4px solid var(--success-color); }
        .alert-danger-modern { background: #fef2f2; border-left: 4px solid var(--danger-color); }
        .alert-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@endpush