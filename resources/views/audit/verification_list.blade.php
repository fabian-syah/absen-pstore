@php
    use Illuminate\Support\Facades\Storage;
@endphp

@extends('layout.master')

@section('title')
    Verifikasi Absensi
@endsection

@section('content')
    <div class="container-fluid px-0">
        {{-- Header Section --}}
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h4 class="card-title text-primary mb-1">Verifikasi Absensi</h4>
                <p class="text-muted small mb-0">Kelola persetujuan absensi manual karyawan.</p>
            </div>
            <div class="col-auto">
                <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill shadow-sm">
                    <i class="mdi mdi-clipboard-alert-outline me-1"></i>
                    {{ $pendingAttendances->count() }} Menunggu
                </span>
            </div>
        </div>

        {{-- Alert Success --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-check-circle-outline fs-4 me-2"></i>
                    <div>{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-alert-circle-outline fs-4 me-2"></i>
                    <div>{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($pendingAttendances->count() > 0)
            
            {{-- TAMPILAN DESKTOP (Table) - Hidden di Mobile --}}
            <div class="card shadow-sm border-0 d-none d-md-block">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th class="ps-4 py-3 text-uppercase font-size-11 fw-bold">Karyawan</th>
                                    <th class="py-3 text-uppercase font-size-11 fw-bold">Waktu & Lokasi</th>
                                    {{-- Kolom Catatan dengan lebar tetap --}}
                                    <th class="py-3 text-uppercase font-size-11 fw-bold" style="width: 250px; min-width: 200px;">Catatan</th>
                                    <th class="py-3 text-uppercase font-size-11 fw-bold">Bukti Foto</th>
                                    <th class="text-end pe-4 py-3 text-uppercase font-size-11 fw-bold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingAttendances as $att)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3">
                                                    @if($att->user)
                                                        <a href="{{ route('users.show', $att->user->id) }}" class="text-decoration-none">
                                                            <span class="avatar-title bg-primary bg-opacity-10 text-primary rounded-circle fw-bold hover-scale">
                                                                {{ substr($att->user->name, 0, 1) }}
                                                            </span>
                                                        </a>
                                                    @else
                                                        <span class="avatar-title bg-primary bg-opacity-10 text-primary rounded-circle fw-bold">U</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    @if($att->user)
                                                        <a href="{{ route('users.show', $att->user->id) }}" class="text-dark text-decoration-none">
                                                            <h6 class="mb-0 fw-bold hover-text-primary">
                                                                {{ $att->user->name }}
                                                                @if($att->user_id == Auth::id())
                                                                    <span class="badge bg-info ms-1" style="font-size: 0.6rem;">ANDA</span>
                                                                @endif
                                                            </h6>
                                                        </a>
                                                    @else
                                                        <h6 class="mb-0 text-dark fw-bold">User Dihapus</h6>
                                                    @endif
                                                    
                                                    <div class="small text-muted mt-1">
                                                        <span class="badge badge-soft-secondary me-1">{{ $att->user->division->name ?? 'N/A' }}</span>
                                                        <span class="text-xs">
                                                            <i class="mdi mdi-store-marker-outline text-warning"></i> 
                                                            {{ $att->user->branch->name ?? 'Non-Cabang' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold text-dark">
                                                    {{ $att->check_in_time->format('H:i') }} WIB
                                                </span>
                                                <span class="small text-muted mb-1">
                                                    {{ $att->check_in_time->format('d M Y') }}
                                                </span>
                                                
                                                @if($att->latitude && $att->longitude)
                                                    <a href="https://maps.google.com/?q={{ $att->latitude }},{{ $att->longitude }}" target="_blank" class="text-info small text-decoration-none">
                                                        <i class="mdi mdi-map-marker-radius me-1"></i>Lihat Peta
                                                    </a>
                                                @else
                                                    <span class="text-muted small"><i class="mdi mdi-map-marker-off me-1"></i>No Loc</span>
                                                @endif
                                            </div>
                                        </td>
                                        {{-- Kolom Catatan (Desktop) --}}
                                        <td>
                                            @if($att->notes)
                                                <div class="p-2 bg-light rounded border border-light position-relative">
                                                    <div class="d-flex align-items-start">
                                                        {{-- Ikon --}}
                                                        <i class="mdi mdi-note-text-outline text-primary me-2 mt-1 flex-shrink-0"></i>
                                                        
                                                        {{-- Teks (Truncated 2 baris + Tooltip) --}}
                                                        <div class="text-muted small fst-italic text-truncate-multiline" 
                                                             data-bs-toggle="tooltip" 
                                                             data-bs-placement="top" 
                                                             title="{{ $att->notes }}">
                                                            "{{ $att->notes }}"
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted small ms-2">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ Storage::url($att->photo_path) }}" class="image-popup d-inline-block position-relative">
                                                <img src="{{ Storage::url($att->photo_path) }}" alt="foto" class="rounded shadow-sm object-fit-cover" width="50" height="50">
                                                <div class="overlay-icon">
                                                    <i class="mdi mdi-magnify text-white"></i>
                                                </div>
                                            </a>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <form action="{{ route('audit.approve', $att->id) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-soft-success btn-rounded" data-bs-toggle="tooltip" title="Setujui">
                                                        <i class="mdi mdi-check"></i> Terima
                                                    </button>
                                                </form>
                                                <form action="{{ route('audit.reject', $att->id) }}" method="POST" onsubmit="return confirm('Hapus data ini?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-soft-danger btn-rounded" data-bs-toggle="tooltip" title="Tolak">
                                                        <i class="mdi mdi-close"></i> Tolak
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
            </div>

            {{-- TAMPILAN MOBILE (Cards) - Hidden di Desktop --}}
            <div class="d-md-none">
                @foreach ($pendingAttendances as $att)
                    <div class="card shadow-sm mb-3 border-0 attendance-card">
                        <div class="card-body">
                            {{-- Header Card: User Info --}}
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                @if($att->user)
                                    <a href="{{ route('users.show', $att->user->id) }}" class="d-flex align-items-center text-decoration-none text-dark flex-grow-1">
                                        <div class="avatar-sm me-3">
                                            <span class="avatar-title bg-primary bg-opacity-10 text-primary rounded-circle fw-bold">
                                                {{ substr($att->user->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-primary">
                                                {{ $att->user->name }} 
                                                @if($att->user_id == Auth::id())
                                                    <span class="badge bg-info ms-1" style="font-size: 0.6rem;">ANDA</span>
                                                @endif
                                                <i class="mdi mdi-chevron-right small text-muted"></i>
                                            </h6>
                                            <div class="small text-muted">
                                                {{ $att->user->division->name ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </a>
                                @else
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3">
                                            <span class="avatar-title bg-primary bg-opacity-10 text-primary rounded-circle fw-bold">U</span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">User Dihapus</h6>
                                            <div class="small text-muted">N/A</div>
                                        </div>
                                    </div>
                                @endif

                                <span class="badge bg-light text-dark border ms-2">
                                    {{ $att->check_in_time->format('H:i') }}
                                </span>
                            </div>

                            {{-- Info Cabang & Tanggal --}}
                            <div class="bg-light rounded p-2 mb-3 small">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted"><i class="mdi mdi-store me-1"></i>Cabang:</span>
                                    <span class="fw-semibold text-dark">{{ $att->user->branch->name ?? '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted"><i class="mdi mdi-calendar me-1"></i>Tanggal:</span>
                                    <span class="fw-semibold text-dark">{{ $att->check_in_time->format('d M Y') }}</span>
                                </div>
                            </div>

                            {{-- Info Catatan (Mobile View - Full Text) --}}
                            @if($att->notes)
                                <div class="alert alert-info border-0 bg-opacity-10 p-2 mb-3 small d-flex">
                                    <i class="mdi mdi-information-outline me-2 mt-1"></i>
                                    <div>
                                        <strong>Catatan:</strong><br>
                                        <span class="text-dark">{{ $att->notes }}</span>
                                    </div>
                                </div>
                            @endif

                            {{-- Foto & Lokasi --}}
                            <div class="row g-2 mb-3">
                                <div class="col-4">
                                    <a href="{{ Storage::url($att->photo_path) }}" class="image-popup">
                                        <img src="{{ Storage::url($att->photo_path) }}" class="img-fluid rounded border w-100" style="height: 60px; object-fit: cover;">
                                    </a>
                                </div>
                                <div class="col-8">
                                    @if($att->latitude && $att->longitude)
                                        <a href="https://maps.google.com/?q={{ $att->latitude }},{{ $att->longitude }}" target="_blank" class="btn btn-outline-info w-100 h-100 d-flex align-items-center justify-content-center btn-sm">
                                            <i class="mdi mdi-map-marker-radius me-2"></i> Buka Maps
                                        </a>
                                    @else
                                        <button disabled class="btn btn-light w-100 h-100 d-flex align-items-center justify-content-center btn-sm text-muted">
                                            <i class="mdi mdi-map-marker-off me-2"></i> No Loc
                                        </button>
                                    @endif
                                </div>
                            </div>

                            {{-- Tombol Aksi (Full Width) --}}
                            <div class="d-flex gap-2">
                                <form action="{{ route('audit.approve', $att->id) }}" method="POST" class="w-50">
                                    @csrf @method('PUT')
                                    <button type="submit" class="btn btn-success w-100 btn-sm">
                                        <i class="mdi mdi-check"></i> Terima
                                    </button>
                                </form>
                                <form action="{{ route('audit.reject', $att->id) }}" method="POST" class="w-50" onsubmit="return confirm('Hapus data ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger w-100 btn-sm">
                                        <i class="mdi mdi-close"></i> Tolak
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        @else
            {{-- Empty State --}}
            <div class="card shadow-none bg-transparent">
                <div class="card-body text-center py-5">
                    <div class="avatar-lg mx-auto bg-light rounded-circle d-flex align-items-center justify-content-center mb-4">
                        <i class="mdi mdi-check-all display-4 text-primary opacity-25"></i>
                    </div>
                    <h5 class="text-dark">Semua Beres!</h5>
                    <p class="text-muted">Tidak ada data absensi yang perlu diverifikasi saat ini.</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Modal Image --}}
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg bg-transparent">
                <div class="modal-body p-0 position-relative text-center">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-index-10" data-bs-dismiss="modal" aria-label="Close"></button>
                    <img id="modalImage" src="" class="img-fluid rounded shadow" style="max-height: 80vh;">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Tooltip Init (Wajib untuk menampilkan teks catatan penuh saat hover)
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            // Image Popup
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
        .object-fit-cover { object-fit: cover; }
        .bg-soft-primary { background-color: rgba(59, 130, 246, 0.1); }
        .badge-soft-secondary { background-color: rgba(108, 117, 125, 0.1); color: #6c757d; }
        
        .btn-rounded { border-radius: 50px; padding-left: 15px; padding-right: 15px; }
        .btn-soft-success { background-color: rgba(16, 185, 129, 0.1); color: #10b981; border: none; }
        .btn-soft-success:hover { background-color: #10b981; color: white; }
        .btn-soft-danger { background-color: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; }
        .btn-soft-danger:hover { background-color: #ef4444; color: white; }

        .image-popup { display: block; overflow: hidden; border-radius: 6px; }
        .overlay-icon {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;
            opacity: 0; transition: opacity 0.2s;
        }
        .image-popup:hover .overlay-icon { opacity: 1; }

        .hover-text-primary:hover { color: #3b82f6 !important; transition: color 0.2s; }
        .hover-scale:hover { transform: scale(1.1); transition: transform 0.2s; }

        .font-size-11 { font-size: 11px; letter-spacing: 0.5px; }
        .text-xs { font-size: 0.75rem; }

        /* CSS KHUSUS CATATAN (TRUNCATE) */
        .text-truncate-multiline {
            display: -webkit-box;
            -webkit-line-clamp: 2; /* Batasi maksimal 2 baris */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
            cursor: help; /* Kursor tanda tanya saat hover */
        }

        @media (max-width: 768px) {
            .container-fluid { padding-left: 15px; padding-right: 15px; }
            .attendance-card { border-radius: 12px; }
        }
    </style>
@endpush