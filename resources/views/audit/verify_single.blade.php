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
    <div class="verify-card shadow-xl animate__animated animate__fadeInUp">
        <div class="row g-0 h-100">
            {{-- Left Side: Large Photo --}}
            <div class="col-lg-7 position-relative">
                <div class="absensi-photo-wrapper">
                    <img src="{{ Storage::url($attendance->photo_path) }}" alt="Absen Photo" class="absensi-photo">
                    <div class="photo-overlay-info">
                        <h2 class="name-overlay mb-0">
                            <a href="{{ route('users.show', $attendance->user?->id) }}" class="text-white text-decoration-none hover-profile">
                                {{ $attendance->user?->name }} <i class="mdi mdi-chevron-right fs-4"></i>
                            </a>
                        </h2>
                        <div class="division-overlay">
                            <i class="mdi mdi-briefcase-outline me-1"></i> {{ $attendance->user?->division?->name ?? 'Staff' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Side: Details & Actions --}}
            <div class="col-lg-5 d-flex flex-column bg-white">
                <div class="card-details p-4 flex-grow-1 overflow-auto">
                    <div class="detail-item mb-4">
                        <label class="detail-label">Informasi Karyawan</label>
                        <div class="d-flex align-items-center">
                            <div class="info-icon bg-soft-primary text-primary">
                                <i class="mdi mdi-store-outline"></i>
                            </div>
                            <div class="ms-3">
                                <div class="info-title">Cabang Pengambilan</div>
                                <div class="info-value">{{ $attendance->user?->branch?->name ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="detail-item">
                                <label class="detail-label">Jam Masuk ({{ $tzLabel }})</label>
                                <div class="d-flex align-items-center">
                                    <div class="info-icon bg-soft-success text-success">
                                        <i class="mdi mdi-login"></i>
                                    </div>
                                    <div class="ms-2">
                                        <div class="info-value fs-5">{{ $checkInLocal->format('H:i') }}</div>
                                        <div class="info-sub">{{ $checkInLocal->format('d M Y') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="detail-item text-end">
                                <label class="detail-label">Status Absen</label>
                                <div class="badge-status-modern {{ $attendance->is_late_checkin ? 'text-danger' : 'text-success' }}">
                                    {{ $attendance->is_late_checkin ? '🔥 Terlambat' : '✅ Tepat Waktu' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="detail-item mb-4">
                        <label class="detail-label">Lokasi Saat Absen (Maps)</label>
                        <div id="map" class="map-container rounded-4 shadow-sm border"></div>
                        <div class="mt-2 text-center">
                            <a href="https://maps.google.com/?q={{ $attendance->latitude }},{{ $attendance->longitude }}" target="_blank" class="btn btn-sm btn-link text-primary text-decoration-none">
                                <i class="mdi mdi-open-in-new me-1"></i> Buka di Google Maps
                            </a>
                        </div>
                    </div>

                    <div class="detail-item mb-4">
                        <label class="detail-label">Catatan Karyawan</label>
                        <div class="notes-bubble">
                            @if($attendance->notes)
                                <i class="mdi mdi-format-quote-open me-1 text-muted"></i>
                                {{ $attendance->notes }}
                            @else
                                <span class="text-muted italic">Tidak ada catatan karyawan.</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="action-footer p-4 border-top">
                    <div class="row g-3">
                        <div class="col-4">
                            <form action="{{ route('audit.reject', $attendance->id) }}" method="POST">
                                @csrf @method('PUT')
                                <button type="submit" class="btn-swipe btn-swipe-reject" title="Tolak">
                                    <i class="mdi mdi-close"></i>
                                    <span>Tolak</span>
                                </button>
                            </form>
                        </div>
                        <div class="col-4 text-center">
                            <a href="{{ route('audit.verify.single', ['id' => $attendance->id]) }}" class="btn-swipe btn-swipe-skip" title="Sengaja Lewati">
                                <i class="mdi mdi-skip-next"></i>
                                <span>Skip</span>
                            </a>
                        </div>
                        <div class="col-4">
                            <form action="{{ route('audit.approve', $attendance->id) }}" method="POST">
                                @csrf @method('PUT')
                                <button type="submit" class="btn-swipe btn-swipe-approve" title="Setujui">
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

{{-- Leaflet CSS & JS --}}
@push('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    :root {
        --dating-primary: #ff4757;
        --dating-success: #2ed573;
        --dating-reject: #ff6b81;
        --dating-skip: #747d8c;
        --glass-bg: rgba(255, 255, 255, 0.8);
    }

    .single-verify-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 10px;
        min-height: calc(100vh - 120px);
        display: flex;
        flex-direction: column;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        text-decoration: none;
        color: #57606f;
        font-weight: 600;
        transition: all 0.2s;
    }
    .btn-back:hover {
        color: var(--primary-color);
        transform: translateX(-3px);
    }

    .verify-card {
        background: #fff;
        border-radius: 28px;
        overflow: hidden;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        margin-bottom: 20px;
    }

    /* Ensure flex row on desktop */
    @media (min-width: 992px) {
        .verify-card {
            flex-direction: row;
            height: clamp(600px, 80vh, 900px);
        }
    }

    .absensi-photo-wrapper {
        height: 100%;
        width: 100%;
        overflow: hidden;
        position: relative;
    }

    .absensi-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .photo-overlay-info {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 40px 30px;
        background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 50%, transparent 100%);
        color: white;
    }

    .name-overlay {
        font-weight: 800;
        font-size: 2.2rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .hover-profile:hover {
        color: #ff9f43 !important;
    }

    .division-overlay {
        font-size: 1.1rem;
        opacity: 0.9;
        font-weight: 500;
        display: flex;
        align-items: center;
    }

    .detail-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #a4b0be;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }

    .info-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .bg-soft-primary { background-color: rgba(59, 130, 246, 0.1); }
    .bg-soft-success { background-color: rgba(46, 213, 115, 0.1); }

    .info-title {
        font-size: 0.8rem;
        color: #747d8c;
        font-weight: 500;
    }
    .info-value {
        font-weight: 700;
        color: #2f3542;
        font-size: 1rem;
    }
    .info-sub {
        font-size: 0.75rem;
        color: #a4b0be;
    }

    .badge-status-modern {
        font-weight: 700;
        font-size: 0.9rem;
    }

    .map-container {
        height: 180px;
        width: 100%;
        background: #f1f2f6;
    }

    .notes-bubble {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 18px;
        border-bottom-left-radius: 4px;
        color: #57606f;
        font-size: 0.95rem;
        position: relative;
    }

    .action-footer {
        background: #fff;
    }

    .btn-swipe {
        width: 100%;
        border: none;
        border-radius: 20px;
        padding: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        text-decoration: none;
    }

    .btn-swipe i {
        font-size: 1.8rem;
        margin-bottom: 4px;
    }
    .btn-swipe span {
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
    }

    .btn-swipe-reject {
        background: #fff;
        color: var(--dating-reject);
        border: 2px solid var(--dating-reject);
    }
    .btn-swipe-reject:hover {
        background: var(--dating-reject);
        color: #fff;
        transform: scale(1.05);
        box-shadow: 0 10px 20px rgba(255, 107, 129, 0.3);
    }

    .btn-swipe-approve {
        background: linear-gradient(135deg, #1dd1a1 0%, #10ac84 100%);
        color: #white;
        color: #fff;
    }
    .btn-swipe-approve:hover {
        transform: scale(1.1);
        box-shadow: 0 15px 30px rgba(29, 209, 161, 0.4);
    }

    .btn-swipe-skip {
        background: #f1f2f6;
        color: var(--dating-skip);
    }
    .btn-swipe-skip:hover {
        background: #dfe4ea;
        transform: scale(1.05);
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 991px) {
        .single-verify-container {
            padding: 0;
            min-height: auto;
        }
        .verify-card {
            border-radius: 0;
            box-shadow: none;
            margin-bottom: 0;
        }
        .absensi-photo-wrapper {
            height: 60vh;
            border-radius: 0 0 30px 30px;
        }
        .name-overlay {
            font-size: 1.6rem;
        }
        .action-footer {
            position: sticky;
            bottom: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 15px !important;
            border-radius: 20px 20px 0 0;
            box-shadow: 0 -10px 25px rgba(0,0,0,0.05);
        }
        .btn-swipe i {
            font-size: 1.4rem;
        }
    }

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
    document.addEventListener('DOMContentLoaded', function() {
        const lat = {{ $attendance->latitude ?? -6.175111 }};
        const lng = {{ $attendance->longitude ?? 106.865039 }};
        
        const map = L.map('map', {
            center: [lat, lng],
            zoom: 15,
            zoomControl: false // Manual zoom control to keep it clean
        });
        
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);
        
        const marker = L.marker([lat, lng]).addTo(map)
            .bindPopup('<b>{{ $attendance->user?->name }}</b><br>Lokasi Absen')
            .openPopup();

        // FIX: Invalidate map size after rendering to solve the "grey/half-rendered" bug
        setTimeout(() => {
            map.invalidateSize(true);
        }, 300);

        // Also invalidate on window resize
        window.addEventListener('resize', () => {
            map.invalidateSize();
        });
    });
</script>
@endpush
@endsection
