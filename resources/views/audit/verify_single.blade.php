@php
    use Illuminate\Support\Facades\Storage;
    use Carbon\Carbon;

    $userTimezone = $attendance->user?->branch?->timezone ?? 'Asia/Jakarta';
    $checkInLocal = Carbon::parse($attendance->check_in_time)->timezone($userTimezone);
    $checkOutLocal = $attendance->check_out_time ? Carbon::parse($attendance->check_out_time)->timezone($userTimezone) : null;
    $tzLabel = str_contains($userTimezone, 'Jakarta') ? 'WIB' : (str_contains($userTimezone, 'Makassar') ? 'WITA' : 'WIT');
@endphp

@extends('layout.master')

@section('title')
    Verifikasi - {{ $attendance->user?->name }}
@endsection

@section('content')
<div class="single-verify-container">
    {{-- Header / Progress --}}
    <div class="row align-items-center mb-3">
        <div class="col">
            <a href="{{ route('audit.verify.list') }}" class="btn-back">
                <i class="mdi mdi-arrow-left"></i> Kembali ke List
            </a>
        </div>
        <div class="col-auto text-end">
            <span class="badge rounded-pill bg-light text-dark shadow-sm px-3 py-2">
                <i class="mdi mdi-account-multiple-outline me-1"></i>
                Remaining: <strong>{{ $totalPending }}</strong>
            </span>
        </div>
    </div>

    {{-- Main Swipe-style Card --}}
    <div class="verify-card shadow-xl animate__animated animate__fadeIn">
        {{-- Photo Section --}}
        <div class="verify-photo-section">
            <div class="absensi-photo-wrapper">
                <img src="{{ Storage::url($attendance->photo_path) }}" alt="Absen Photo" class="absensi-photo" id="mainPhoto">
                <div class="photo-overlay-info">
                    <h2 class="name-overlay mb-1">
                        {{ $attendance->user?->name }}
                    </h2>
                    <div class="division-overlay">
                        <i class="mdi mdi-briefcase-outline me-1"></i> {{ $attendance->user?->division?->name ?? 'Staff' }}
                    </div>
                </div>
                <div class="photo-actions-top">
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3 shadow-sm" onclick="togglePhotoSize()">
                        <i class="mdi mdi-magnify-plus-outline me-1"></i> Zoom Foto
                    </button>
                    <a href="{{ route('users.show', $attendance->user?->id) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                        <i class="mdi mdi-account-outline"></i> Profil
                    </a>
                </div>
            </div>
        </div>

        {{-- Details Section --}}
        <div class="verify-details-section">
            <div class="details-content p-4 p-md-5">
                <div class="section-title mb-4">
                    <h5 class="fw-bold text-dark border-start border-4 border-primary ps-3">Informasi Absensi</h5>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-sm-6">
                        <div class="info-card">
                            <label class="info-label">Cabang</label>
                            <div class="d-flex align-items-center">
                                <div class="info-icon-sm bg-primary bg-opacity-10 text-primary">
                                    <i class="mdi mdi-store-outline"></i>
                                </div>
                                <div class="ms-2">
                                    <span class="info-text fw-bold text-dark">{{ $attendance->user?->branch?->name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="info-card">
                            <label class="info-label">Waktu ({{ $tzLabel }})</label>
                            <div class="d-flex align-items-center">
                                <div class="info-icon-sm bg-success bg-opacity-10 text-success">
                                    <i class="mdi mdi-clock-outline"></i>
                                </div>
                                <div class="ms-2">
                                    <span class="info-text fw-bold text-dark">{{ $checkInLocal->format('H:i') }}</span>
                                    <div class="small text-muted">{{ $checkInLocal->format('d M') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-end mb-2">
                        <label class="detail-label mb-0">Lokasi Penanda (Maps)</label>
                        <button type="button" class="btn btn-xs btn-outline-primary btn-sm rounded-pill" onclick="centerMap()">
                            <i class="mdi mdi-crosshairs-gps"></i> Fokus Lokasi
                        </button>
                    </div>
                    <div id="map" class="map-container-new"></div>
                    <div class="mt-2 text-center">
                        <a href="https://maps.google.com/?q={{ $attendance->latitude }},{{ $attendance->longitude }}" target="_blank" class="btn btn-sm btn-light w-100 rounded-3 text-primary">
                            <i class="mdi mdi-google-maps me-1"></i> Lihat di Google Maps Lengkap
                        </a>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="detail-label">Catatan Karyawan</label>
                    <div class="notes-container">
                        @if($attendance->notes)
                            <p class="mb-0 text-dark-emphasis italic">"{{ $attendance->notes }}"</p>
                        @else
                            <p class="mb-0 text-muted small"><em>Tidak ada catatan.</em></p>
                        @endif
                    </div>
                </div>

                <div class="action-buttons-group mt-auto pt-4 border-top">
                    <div class="row g-2">
                        <div class="col-4">
                            <form action="{{ route('audit.reject', $attendance->id) }}" method="POST">
                                @csrf @method('PUT')
                                <button type="submit" class="btn-swipe-v2 btn-reject-v2" onclick="return confirm('Tolak absensi ini?')">
                                    <i class="mdi mdi-close"></i>
                                    <span>Tolak</span>
                                </button>
                            </form>
                        </div>
                        <div class="col-4">
                            <a href="{{ route('audit.verify.single', ['id' => $attendance->id]) }}" class="btn-swipe-v2 btn-skip-v2">
                                <i class="mdi mdi-skip-next"></i>
                                <span>Skip</span>
                            </a>
                        </div>
                        <div class="col-4">
                            <form action="{{ route('audit.approve', $attendance->id) }}" method="POST">
                                @csrf @method('PUT')
                                <button type="submit" class="btn-swipe-v2 btn-approve-v2">
                                    <i class="mdi mdi-check"></i>
                                    <span>Setujui</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Fullscreen Image Overlay (Hidden by default) --}}
<div id="photoOverlay" class="photo-zoom-overlay" onclick="togglePhotoSize()">
    <img src="{{ Storage::url($attendance->photo_path) }}" alt="Zoomed Photo">
    <div class="close-overlay">Ketuk untuk menutup</div>
</div>

{{-- Leaflet CSS & JS --}}
@push('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .single-verify-container {
        width: 100%;
        margin: 0 auto;
        padding: 0;
        display: flex;
        flex-direction: column;
    }

    @media (min-width: 992px) {
        .single-verify-container {
            padding: 20px;
        }
    }

    .verify-card {
        background: #fff;
        display: flex;
        flex-direction: column;
        border-radius: 0;
        overflow: hidden;
        min-height: 100vh;
    }

    /* Side by side on Desktop */
    @media (min-width: 992px) {
        .verify-card {
            flex-direction: row;
            border-radius: 28px;
            min-height: 800px;
            height: calc(100vh - 180px);
        }
    }

    .verify-photo-section {
        flex: 1;
        position: relative;
        background: #f8f9fa;
        min-height: 50vh;
    }

    @media (min-width: 992px) {
        .verify-photo-section {
            min-height: 100%;
            max-width: 60%;
        }
    }

    .verify-details-section {
        background: #fff;
        width: 100%;
        display: flex;
        flex-direction: column;
        border-top: 1px solid #eee;
    }

    @media (min-width: 992px) {
        .verify-details-section {
            width: 450px;
            border-top: none;
            border-left: 1px solid #eee;
            flex-shrink: 0;
        }
    }

    .absensi-photo-wrapper {
        height: 100%;
        width: 100%;
        position: relative;
    }

    .absensi-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .photo-overlay-info {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 60px 30px 30px;
        background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, transparent 100%);
        color: white;
    }

    .photo-actions-top {
        position: absolute;
        top: 20px;
        left: 20px;
        right: 20px;
        display: flex;
        justify-content: space-between;
        z-index: 10;
    }

    .name-overlay {
        font-weight: 800;
        font-size: 1.8rem;
        letter-spacing: -0.5px;
    }

    .map-container-new {
        height: 250px;
        border-radius: 16px;
        background: #f1f2f6;
        border: 1px solid #eee;
        z-index: 5;
    }

    .notes-container {
        padding: 15px;
        background: #fcfcfc;
        border-left: 4px solid #3b82f6;
        border-radius: 8px;
    }

    .info-card {
        padding: 10px;
        background: #f8fafc;
        border-radius: 12px;
    }

    .info-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #94a3b8;
        font-weight: 700;
        display: block;
        margin-bottom: 2px;
    }

    /* Swipe Buttons V2 */
    .btn-swipe-v2 {
        width: 100%;
        height: 75px;
        border: none;
        border-radius: 18px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        color: #fff;
    }

    .btn-swipe-v2 i { font-size: 1.5rem; margin-bottom: 2px; }
    .btn-swipe-v2 span { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }

    .btn-approve-v2 { background: #10b981; box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3); }
    .btn-approve-v2:hover { transform: translateY(-5px); background: #059669; }

    .btn-reject-v2 { background: #ef4444; box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.3); }
    .btn-reject-v2:hover { transform: translateY(-5px); background: #dc2626; }

    .btn-skip-v2 { background: #64748b; box-shadow: 0 10px 15px -3px rgba(100, 116, 139, 0.3); }
    .btn-skip-v2:hover { transform: translateY(-5px); background: #475569; }

    /* Zoom Overlay */
    .photo-zoom-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.95);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        cursor: zoom-out;
    }
    .photo-zoom-overlay.active { display: flex; }
    .photo-zoom-overlay img { max-width: 95%; max-height: 95%; object-fit: contain; }
    .close-overlay { position: absolute; bottom: 30px; color: #fff; background: rgba(0,0,0,0.5); padding: 10px 20px; border-radius: 50px; }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translate3d(0, 40px, 0);
        }
        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let mainMap;
    const lat = {{ $attendance->latitude ?? -6.175111 }};
    const lng = {{ $attendance->longitude ?? 106.865039 }};

    function initMap() {
        if (mainMap) return;

        // Optimized Map Config
        mainMap = L.map('map', {
            center: [lat, lng],
            zoom: 15,
            zoomControl: true,
            scrollWheelZoom: true,
            fadeAnimation: true,
            markerZoomAnimation: true,
            inertia: true
        });

        // Using CARTO Positron (Lighter & Faster Render)
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; CARTO'
        }).addTo(mainMap);
        
        const marker = L.marker([lat, lng]).addTo(mainMap)
            .bindPopup('<b>{{ $attendance->user?->name }}</b>');

        // Expert approach: Use ResizeObserver to fix "Lag/Grey" bug instantly
        const observer = new ResizeObserver(() => {
            if (mainMap) {
                mainMap.invalidateSize();
                mainMap.setView([lat, lng]);
            }
        });
        
        observer.observe(document.getElementById('map'));
    }

    function centerMap() {
        if (mainMap) mainMap.flyTo([lat, lng], 17);
    }

    function togglePhotoSize() {
        const overlay = document.getElementById('photoOverlay');
        overlay.classList.toggle('active');
        if (overlay.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'auto';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Delay slightly to wait for browser layout to settle
        setTimeout(initMap, 100);
    });
</script>
@endpush
@endsection
