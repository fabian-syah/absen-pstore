@php
    use Illuminate\Support\Facades\Storage;
    use Carbon\Carbon;

    $userTimezone = $attendance->user?->branch?->timezone ?? 'Asia/Jakarta';
    $checkInLocal = Carbon::parse($attendance->check_in_time)->timezone($userTimezone);
    $checkOutLocal = $attendance->check_out_time ? Carbon::parse($attendance->check_out_time)->timezone($userTimezone) : null;
    $tzLabel = str_contains($userTimezone, 'Jakarta') ? 'WIB' : (str_contains($userTimezone, 'Makassar') ? 'WITA' : 'WIT');

    $lat = $attendance->latitude ?? null;
    $lng = $attendance->longitude ?? null;
    $hasLocation = $lat && $lng;
@endphp

@extends('layout.master')

@section('title')
    Verifikasi - {{ $attendance->user?->name }}
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* ===== Back Link ===== */
    .sv-back {
        display: inline-flex;
        align-items: center;
        text-decoration: none;
        color: #57606f;
        font-weight: 600;
        font-size: 0.9rem;
        transition: color 0.2s;
    }
    .sv-back:hover { color: var(--pstore-primary); }

    /* ===== Main Layout ===== */
    .sv-wrapper {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    @media (min-width: 992px) {
        .sv-wrapper {
            flex-direction: row;
            gap: 20px;
            align-items: flex-start;
        }
    }

    /* ===== Photo Card ===== */
    .sv-photo-card {
        background: #000;
        border-radius: 20px;
        overflow: hidden;
        position: relative;
        width: 100%;
        aspect-ratio: 3/4;
        max-height: 65vh;
    }
    @media (min-width: 992px) {
        .sv-photo-card {
            flex: 1;
            max-height: 80vh;
            aspect-ratio: auto;
            min-height: 500px;
        }
    }
    .sv-photo-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .sv-photo-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 50px 20px 20px;
        background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
        color: #fff;
    }
    .sv-photo-overlay h3 {
        font-weight: 800;
        font-size: 1.5rem;
        margin-bottom: 2px;
    }
    .sv-photo-overlay h3 a {
        color: #fff;
        text-decoration: none;
    }
    .sv-photo-overlay h3 a:hover { color: #ffd700; }
    .sv-photo-overlay .sv-division {
        font-size: 0.85rem;
        opacity: 0.85;
    }

    /* ===== Detail Panel ===== */
    .sv-detail-panel {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        width: 100%;
        display: flex;
        flex-direction: column;
    }
    @media (min-width: 992px) {
        .sv-detail-panel {
            width: 420px;
            flex-shrink: 0;
            max-height: 80vh;
            overflow-y: auto;
        }
    }
    .sv-detail-body { padding: 20px; }

    /* ===== Info Row ===== */
    .sv-info-row {
        display: flex;
        gap: 12px;
        margin-bottom: 16px;
    }
    .sv-info-box {
        flex: 1;
        background: #f8fafc;
        border-radius: 12px;
        padding: 12px;
    }
    .sv-info-box label {
        display: block;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94a3b8;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .sv-info-box .sv-val {
        font-weight: 700;
        font-size: 0.95rem;
        color: #1e293b;
    }
    .sv-info-box .sv-sub {
        font-size: 0.75rem;
        color: #94a3b8;
    }

    /* ===== Map Box ===== */
    .sv-map-label {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94a3b8;
        font-weight: 700;
        margin-bottom: 6px;
    }
    #sv-map {
        width: 100%;
        height: 180px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #f1f5f9;
        z-index: 1;
    }
    .sv-map-link {
        display: block;
        text-align: center;
        margin-top: 6px;
        font-size: 0.8rem;
        color: var(--pstore-primary, #0d6efd);
        text-decoration: none;
    }
    .sv-map-link:hover { text-decoration: underline; }

    /* ===== Notes ===== */
    .sv-notes {
        background: #f8fafc;
        border-left: 3px solid var(--pstore-primary, #0d6efd);
        border-radius: 8px;
        padding: 12px 14px;
        font-size: 0.85rem;
        color: #475569;
        margin-top: 16px;
    }

    /* ===== Action Buttons ===== */
    .sv-actions {
        display: flex;
        gap: 8px;
        padding: 16px 20px;
        border-top: 1px solid #f1f5f9;
    }
    .sv-btn {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: none;
        border-radius: 14px;
        padding: 14px 8px;
        color: #fff;
        font-weight: 700;
        text-decoration: none;
        transition: transform 0.15s, box-shadow 0.15s;
        cursor: pointer;
    }
    .sv-btn:hover { transform: translateY(-3px); color: #fff; }
    .sv-btn:active { transform: scale(0.97); }
    .sv-btn i { font-size: 1.3rem; margin-bottom: 2px; }
    .sv-btn span { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.5px; }

    .sv-btn-reject { background: #ef4444; }
    .sv-btn-reject:hover { box-shadow: 0 8px 16px rgba(239,68,68,0.3); }
    .sv-btn-skip { background: #64748b; }
    .sv-btn-skip:hover { box-shadow: 0 8px 16px rgba(100,116,139,0.3); }
    .sv-btn-approve { background: #10b981; }
    .sv-btn-approve:hover { box-shadow: 0 8px 16px rgba(16,185,129,0.3); }

    .sv-status-badge {
        display: inline-block;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
    }
    .sv-status-late { background: #fef2f2; color: #dc2626; }
    .sv-status-ontime { background: #f0fdf4; color: #16a34a; }

    /* Photo zoom overlay */
    .sv-zoom-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.92);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        cursor: zoom-out;
    }
    .sv-zoom-overlay.active { display: flex; }
    .sv-zoom-overlay img {
        max-width: 95vw;
        max-height: 95vh;
        object-fit: contain;
    }
</style>
@endpush

@section('content')
{{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <a href="{{ route('audit.verify.list') }}" class="sv-back">
        <i class="mdi mdi-arrow-left me-1"></i> Kembali ke List
    </a>
    <span class="badge rounded-pill bg-light text-dark shadow-sm px-3 py-2">
        <i class="mdi mdi-account-clock-outline me-1"></i>
        Sisa: <strong>{{ $totalPending }}</strong>
    </span>
</div>

{{-- Main Content --}}
<div class="sv-wrapper">
    {{-- LEFT: Photo --}}
    <div class="sv-photo-card" onclick="toggleZoom()" style="cursor:zoom-in">
        <img src="{{ Storage::url($attendance->photo_path) }}" alt="Foto Absen">
        <div class="sv-photo-overlay">
            <h3>
                <a href="{{ route('users.show', $attendance->user?->id) }}">
                    {{ $attendance->user?->name }} <i class="mdi mdi-chevron-right"></i>
                </a>
            </h3>
            <div class="sv-division">
                <i class="mdi mdi-briefcase-outline me-1"></i>{{ $attendance->user?->division?->name ?? 'Staff' }}
            </div>
        </div>
    </div>

    {{-- RIGHT: Details --}}
    <div class="sv-detail-panel">
        <div class="sv-detail-body">
            {{-- Branch & Time --}}
            <div class="sv-info-row">
                <div class="sv-info-box">
                    <label><i class="mdi mdi-store me-1"></i>Cabang</label>
                    <div class="sv-val">{{ $attendance->user?->branch?->name ?? '-' }}</div>
                </div>
                <div class="sv-info-box">
                    <label><i class="mdi mdi-clock-outline me-1"></i>Masuk ({{ $tzLabel }})</label>
                    <div class="sv-val">{{ $checkInLocal->format('H:i') }}</div>
                    <div class="sv-sub">{{ $checkInLocal->format('d M Y') }}</div>
                </div>
            </div>

            {{-- Status --}}
            <div class="mb-3">
                @if($attendance->is_late_checkin)
                    <span class="sv-status-badge sv-status-late">🔥 Terlambat</span>
                @else
                    <span class="sv-status-badge sv-status-ontime">✅ Tepat Waktu</span>
                @endif
            </div>

            {{-- Map --}}
            @if($hasLocation)
            <div class="mb-3">
                <div class="sv-map-label"><i class="mdi mdi-map-marker me-1"></i>Lokasi Absen</div>
                <div id="sv-map"></div>
                <a href="https://maps.google.com/?q={{ $lat }},{{ $lng }}" target="_blank" class="sv-map-link">
                    <i class="mdi mdi-open-in-new me-1"></i>Buka di Google Maps
                </a>
            </div>
            @else
            <div class="mb-3">
                <div class="sv-map-label">Lokasi Absen</div>
                <div class="text-muted small">Lokasi tidak tersedia.</div>
            </div>
            @endif

            {{-- Notes --}}
            <div class="sv-notes">
                <strong class="d-block mb-1" style="font-size:0.7rem;text-transform:uppercase;color:#94a3b8;">Catatan</strong>
                @if($attendance->notes)
                    "{{ $attendance->notes }}"
                @else
                    <em class="text-muted">Tidak ada catatan.</em>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="sv-actions">
            <form action="{{ route('audit.reject', $attendance->id) }}" method="POST" class="flex-fill d-flex">
                @csrf @method('PUT')
                <button type="submit" class="sv-btn sv-btn-reject" onclick="return confirm('Yakin tolak absensi ini?')">
                    <i class="mdi mdi-close"></i><span>Tolak</span>
                </button>
            </form>
            <a href="{{ route('audit.verify.single', ['id' => $attendance->id]) }}" class="sv-btn sv-btn-skip">
                <i class="mdi mdi-skip-next"></i><span>Skip</span>
            </a>
            <form action="{{ route('audit.approve', $attendance->id) }}" method="POST" class="flex-fill d-flex">
                @csrf @method('PUT')
                <button type="submit" class="sv-btn sv-btn-approve">
                    <i class="mdi mdi-check"></i><span>Setujui</span>
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Zoom Overlay --}}
<div id="zoomOverlay" class="sv-zoom-overlay" onclick="toggleZoom()">
    <img src="{{ Storage::url($attendance->photo_path) }}" alt="Zoom">
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function toggleZoom() {
    document.getElementById('zoomOverlay').classList.toggle('active');
}

document.addEventListener('DOMContentLoaded', function() {
    @if($hasLocation)
    var lat = {{ $lat }};
    var lng = {{ $lng }};

    var map = L.map('sv-map', {
        center: [lat, lng],
        zoom: 16,
        zoomControl: true,
        scrollWheelZoom: false,
        dragging: true,
        attributionControl: false
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    L.marker([lat, lng]).addTo(map)
        .bindPopup('<b>{{ $attendance->user?->name }}</b>')
        .openPopup();

    // Fix grey tiles on initial render
    setTimeout(function() { map.invalidateSize(); }, 200);
    setTimeout(function() { map.invalidateSize(); }, 600);
    @endif
});
</script>
@endpush
