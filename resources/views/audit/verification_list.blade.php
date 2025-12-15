@php
    use Illuminate\Support\Facades\Storage;
@endphp

@extends('layout.master')

@section('title')
    Verifikasi Absensi
@endsection

@section('content')
    <div class="container-fluid px-3 py-4">
        
        {{-- Header Section --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h4 class="mb-1 text-dark fw-bold">Verifikasi Absensi</h4>
                <p class="text-muted mb-0 small">Tinjau dan kelola persetujuan absensi manual karyawan.</p>
            </div>
            <div>
                <div class="card shadow-sm border-0 bg-white">
                    <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
                        <div class="avatar-sm bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-clipboard-alert-outline fs-5"></i>
                        </div>
                        <div>
                            <small class="d-block text-muted text-uppercase font-size-10 fw-bold">Menunggu</small>
                            <h5 class="mb-0 fw-bold text-dark">{{ $pendingAttendances->total() }} <span class="text-muted fs-6 fw-normal">Permintaan</span></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-check-circle-outline fs-4 me-2"></i>
                    <div>{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-alert-circle-outline fs-4 me-2"></i>
                    <div>{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($pendingAttendances->count() > 0)
            
            {{-- DESKTOP TABLE VIEW --}}
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden d-none d-md-block mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-uppercase font-size-11 fw-bold text-muted border-bottom-0">Karyawan</th>
                                    <th class="py-3 text-uppercase font-size-11 fw-bold text-muted border-bottom-0">Waktu Absen</th>
                                    <th class="py-3 text-uppercase font-size-11 fw-bold text-muted border-bottom-0">Catatan</th>
                                    <th class="py-3 text-uppercase font-size-11 fw-bold text-muted border-bottom-0 text-center">Bukti Foto</th>
                                    <th class="text-end pe-4 py-3 text-uppercase font-size-11 fw-bold text-muted border-bottom-0">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingAttendances as $att)
                                    <tr class="border-bottom-light">
                                        {{-- 1. Karyawan --}}
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3">
                                                    @if($att->user)
                                                        <a href="{{ route('users.show', $att->user->id) }}" class="text-decoration-none">
                                                            <span class="avatar-title bg-soft-primary text-primary rounded-circle fw-bold shadow-sm">
                                                                {{ substr($att->user->name, 0, 1) }}
                                                            </span>
                                                        </a>
                                                    @else
                                                        <span class="avatar-title bg-soft-secondary text-secondary rounded-circle fw-bold">U</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    @if($att->user)
                                                        <a href="{{ route('users.show', $att->user->id) }}" class="text-dark fw-bold text-decoration-none hover-text-primary d-block">
                                                            {{ $att->user->name }}
                                                            @if($att->user_id == Auth::id())
                                                                <span class="badge bg-soft-info text-info ms-1 px-2 py-1 rounded-pill" style="font-size: 0.65rem;">ANDA</span>
                                                            @endif
                                                        </a>
                                                        <small class="text-muted">{{ $att->user->division->name ?? 'N/A' }} &bull; {{ $att->user->branch->name ?? 'N/A' }}</small>
                                                    @else
                                                        <span class="fw-bold text-danger">User Dihapus</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        {{-- 2. Waktu --}}
                                        <td class="py-3">
                                            <div class="d-flex flex-column gap-1">
                                                <span class="fw-semibold text-dark">{{ $att->check_in_time->format('d M Y') }}</span>
                                                <div class="d-flex gap-2 text-xs">
                                                    <span class="text-success fw-medium"><i class="mdi mdi-login me-1"></i>{{ $att->check_in_time->format('H:i') }}</span>
                                                    @if($att->check_out_time)
                                                        <span class="text-danger fw-medium border-start ps-2"><i class="mdi mdi-logout me-1"></i>{{ $att->check_out_time->format('H:i') }}</span>
                                                    @else
                                                        <span class="text-muted border-start ps-2 fst-italic">--:--</span>
                                                    @endif
                                                </div>
                                                @if($att->latitude && $att->longitude)
                                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $att->latitude }},{{ $att->longitude }}" target="_blank" class="text-decoration-none mt-1 badge bg-light text-muted border fw-normal text-start" style="width: fit-content;">
                                                        <i class="mdi mdi-map-marker text-danger me-1"></i> Lihat Lokasi
                                                    </a>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- 3. Catatan --}}
                                        <td class="py-3" style="max-width: 250px;">
                                            @if($att->notes)
                                                <div class="p-2 bg-light rounded text-muted small position-relative border-start border-3 border-primary" data-bs-toggle="tooltip" title="{{ $att->notes }}">
                                                    <span class="text-truncate-2">{{ $att->notes }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted small fst-italic">- Tidak ada catatan -</span>
                                            @endif
                                        </td>

                                        {{-- 4. Foto --}}
                                        <td class="py-3 text-center">
                                            <a href="{{ Storage::url($att->photo_path) }}" class="image-popup d-inline-block position-relative shadow-sm rounded-3 overflow-hidden" style="width: 50px; height: 50px;">
                                                <img src="{{ Storage::url($att->photo_path) }}" class="w-100 h-100 object-fit-cover" alt="Check In">
                                                <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-flex align-items-center justify-content-center opacity-0 hover-opacity-100 transition-all">
                                                    <i class="mdi mdi-magnify text-white"></i>
                                                </div>
                                            </a>
                                            @if($att->check_out_time && $att->photo_out_path)
                                                <div class="mt-1">
                                                    <a href="{{ Storage::url($att->photo_out_path) }}" class="image-popup badge bg-light text-muted border text-decoration-none">
                                                        <i class="mdi mdi-image me-1"></i>OUT
                                                    </a>
                                                </div>
                                            @endif
                                        </td>

                                        {{-- 5. Aksi --}}
                                        <td class="pe-4 py-3 text-end">
                                            <div class="d-flex justify-content-end gap-2">
                                                <form action="{{ route('audit.approve', $att->id) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-soft-success btn-icon" data-bs-toggle="tooltip" title="Setujui Absensi">
                                                        <i class="mdi mdi-check"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('audit.reject', $att->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menolak dan menghapus data ini?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-soft-danger btn-icon" data-bs-toggle="tooltip" title="Tolak Absensi">
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
                    
                    {{-- Pagination Footer Desktop --}}
                    <div class="card-footer bg-white border-top py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <small class="text-muted">Menampilkan {{ $pendingAttendances->firstItem() }} sampai {{ $pendingAttendances->lastItem() }} dari {{ $pendingAttendances->total() }} data</small>
                            <div>
                                {{ $pendingAttendances->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MOBILE CARD VIEW --}}
            <div class="d-md-none">
                @foreach ($pendingAttendances as $att)
                    <div class="card shadow-sm border-0 mb-3 rounded-3">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-sm">
                                        <span class="avatar-title bg-soft-primary text-primary rounded-circle fw-bold">
                                            {{ $att->user ? substr($att->user->name, 0, 1) : 'U' }}
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $att->user->name ?? 'User Dihapus' }}</h6>
                                        <small class="text-muted">{{ $att->user->division->name ?? '-' }}</small>
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark border">{{ $att->check_in_time->format('d M') }}</span>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="p-2 bg-soft-success rounded text-center">
                                        <span class="d-block text-uppercase font-size-10 text-success fw-bold">Masuk</span>
                                        <span class="fw-bold text-dark">{{ $att->check_in_time->format('H:i') }}</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 bg-soft-danger rounded text-center">
                                        <span class="d-block text-uppercase font-size-10 text-danger fw-bold">Pulang</span>
                                        <span class="fw-bold text-dark">{{ $att->check_out_time ? $att->check_out_time->format('H:i') : '--:--' }}</span>
                                    </div>
                                </div>
                            </div>

                            @if($att->notes)
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-1">Catatan:</small>
                                    <p class="mb-0 small bg-light p-2 rounded text-dark">{{ $att->notes }}</p>
                                </div>
                            @endif

                            <div class="d-flex gap-2">
                                <form action="{{ route('audit.approve', $att->id) }}" method="POST" class="flex-grow-1">
                                    @csrf @method('PUT')
                                    <button type="submit" class="btn btn-success btn-sm w-100 rounded-pill">
                                        <i class="mdi mdi-check me-1"></i> Terima
                                    </button>
                                </form>
                                <form action="{{ route('audit.reject', $att->id) }}" method="POST" class="flex-grow-1" onsubmit="return confirm('Hapus data ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100 rounded-pill">
                                        <i class="mdi mdi-close me-1"></i> Tolak
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Mobile Pagination --}}
                <div class="mt-3">
                    {{ $pendingAttendances->links('pagination::bootstrap-5') }}
                </div>
            </div>

        @else
            {{-- Empty State --}}
            <div class="text-center py-5">
                <div class="avatar-xl mx-auto bg-light rounded-circle d-flex align-items-center justify-content-center mb-4">
                    <i class="mdi mdi-check-all display-4 text-primary opacity-25"></i>
                </div>
                <h5 class="text-dark fw-bold">Semua Beres!</h5>
                <p class="text-muted">Tidak ada data absensi yang perlu diverifikasi saat ini.</p>
            </div>
        @endif
    </div>

    {{-- Modal Image --}}
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 bg-transparent shadow-none">
                <div class="modal-body p-0 position-relative text-center">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-index-10 shadow-lg bg-white opacity-100" data-bs-dismiss="modal" aria-label="Close"></button>
                    <img id="modalImage" src="" class="img-fluid rounded-4 shadow-lg" style="max-height: 85vh;">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Init Bootstrap Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            // Image Popup Logic
            const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
            const modalImage = document.getElementById('modalImage');

            document.querySelectorAll('.image-popup').forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    let src = this.getAttribute('href') || this.querySelector('img').src;
                    modalImage.src = src;
                    imageModal.show();
                });
            });
        });
    </script>
    
    <style>
        /* Modern Soft UI Colors */
        .bg-soft-primary { background-color: #eff6ff !important; color: #3b82f6 !important; }
        .bg-soft-success { background-color: #ecfdf5 !important; color: #10b981 !important; }
        .bg-soft-danger { background-color: #fef2f2 !important; color: #ef4444 !important; }
        .bg-soft-info { background-color: #eff6ff !important; color: #0ea5e9 !important; }
        .bg-soft-secondary { background-color: #f3f4f6 !important; color: #6b7280 !important; }

        /* Typography */
        .font-size-10 { font-size: 10px; }
        .font-size-11 { font-size: 11px; letter-spacing: 0.05em; }
        .text-xs { font-size: 0.75rem; }
        .fw-medium { font-weight: 500; }
        
        /* Table Styles */
        .table > :not(caption) > * > * { padding: 1rem 0.5rem; border-bottom-color: #f3f4f6; }
        .border-bottom-light { border-bottom: 1px solid #f3f4f6; }
        .table-hover tbody tr:hover { background-color: #f9fafb; }
        
        /* Images & Avatars */
        .object-fit-cover { object-fit: cover; }
        .avatar-title { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; }
        
        /* Buttons */
        .btn-icon { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s; }
        .btn-soft-success { background: #ecfdf5; color: #10b981; border: none; }
        .btn-soft-success:hover { background: #10b981; color: white; transform: translateY(-2px); }
        .btn-soft-danger { background: #fef2f2; color: #ef4444; border: none; }
        .btn-soft-danger:hover { background: #ef4444; color: white; transform: translateY(-2px); }

        /* Utilities */
        .hover-text-primary:hover { color: #3b82f6 !important; transition: color 0.2s; }
        .hover-opacity-100:hover { opacity: 1 !important; }
        .transition-all { transition: all 0.2s ease-in-out; }
        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Pagination Customization (Bootstrap Override if needed) */
        .page-link { border: none; color: #6b7280; margin: 0 2px; border-radius: 6px; }
        .page-item.active .page-link { background-color: #3b82f6; color: white; }
    </style>
@endpush