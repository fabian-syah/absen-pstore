@extends('layout.master')

@section('title', 'Qibla Direction')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    /* =============================================
       QIBLA PAGE - IMMERSIVE FULLSCREEN
       ============================================= */

    :root {
        --qibla-bg: #0a1f14;
        --qibla-primary: #ffffff;
        --qibla-secondary: #a8e6cf;
        --qibla-accent: #f59e0b; /* Kaaba icon color */
    }

    /* ---- FULLSCREEN: hide header, sidebar, footer, mobile nav ---- */
    .navbar,
    .sidebar,
    .mobile-bottom-nav,
    footer,
    .footer {
        display: none !important;
    }
    .main-panel {
        margin-left: 0 !important;
        width: 100% !important;
        min-height: 100vh;
    }
    .page-body-wrapper {
        padding-top: 0 !important;
    }
    .content-wrapper {
        padding: 0 !important;
    }

    /* Page wrapper */
    .qibla-page {
        font-family: "Manrope", -apple-system, BlinkMacSystemFont, sans-serif;
        -webkit-font-smoothing: antialiased;
        background: var(--qibla-bg);
        min-height: 100vh;
        min-height: 100dvh;
        padding: 0 !important;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    /* Background image */
    .qibla-bg {
        position: fixed;
        top: -20px; left: -20px; right: -20px; bottom: -20px;
        background-image:
            linear-gradient(180deg, rgba(10, 31, 20, 0.4) 0%, rgba(10, 31, 20, 0.2) 40%, rgba(10, 31, 20, 0.8) 100%),
            url('{{ asset("public/images/qibla_bg.png") }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        filter: blur(4px);
        -webkit-filter: blur(4px);
        z-index: 0;
        pointer-events: none;
    }

    /* Top actions */
    .qibla-top-actions {
        position: relative;
        z-index: 10;
        display: flex;
        justify-content: space-between;
        padding: 20px 24px;
        padding-top: max(20px, env(safe-area-inset-top));
    }
    .qibla-top-actions .title {
        color: white;
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .icon-btn {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
        text-decoration: none !important;
    }
    .icon-btn:hover {
        background: rgba(255,255,255,0.1);
    }
    .icon-btn .material-symbols-outlined {
        font-size: 28px;
        font-variation-settings: "wght" 300;
    }

    /* Compass UI container */
    .compass-container {
        position: relative;
        z-index: 5;
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding-bottom: 20vh; /* space for text below */
    }

    /* Compass ring */
    .compass-ring {
        width: 300px;
        height: 300px;
        border: 3px solid rgba(255, 255, 255, 0.9);
        border-radius: 50%;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.1s linear; /* smooth rotation */
    }
    
    @media (max-width: 380px) {
        .compass-ring {
            width: 260px;
            height: 260px;
        }
    }

    /* Inner cross / lines */
    .compass-cross {
        position: absolute;
        width: 100%;
        height: 100%;
    }
    .cross-line-v, .cross-line-h {
        position: absolute;
        background: rgba(255, 255, 255, 0.4);
    }
    .cross-line-v { width: 1px; height: 100%; left: 50%; top: 0; transform: translateX(-50%); }
    .cross-line-h { height: 1px; width: 100%; top: 50%; left: 0; transform: translateY(-50%); }

    /* N E S W labels */
    .compass-label {
        position: absolute;
        color: rgba(255, 255, 255, 0.6);
        font-size: 16px;
        font-weight: 600;
    }
    .label-n { top: 30px; left: 50%; transform: translateX(-50%); }
    .label-s { bottom: 30px; left: 50%; transform: translateX(-50%); }
    .label-e { right: 30px; top: 50%; transform: translateY(-50%); }
    .label-w { left: 30px; top: 50%; transform: translateY(-50%); }

    /* Qibla Indicator Container */
    .qibla-marker-container {
        position: absolute;
        width: 100%;
        height: 100%;
        left: 0; top: 0;
        transition: transform 0.5s ease;
    }

    /* Qibla Marker (Kaaba) */
    .qibla-marker {
        position: absolute;
        width: 42px;
        height: 42px;
        background: #d98a2c;
        border-radius: 8px;
        top: -21px; /* offset by half height */
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    }
    /* Kaaba icon lines */
    .qibla-marker::before {
        content: '';
        width: 16px;
        height: 26px;
        border: 2px solid #fff;
        border-radius: 4px;
        opacity: 1;
    }
    /* Stem pointing down */
    .qibla-marker::after {
        content: '';
        position: absolute;
        width: 3px;
        height: 12px;
        background: #fff;
        bottom: -12px;
        left: 50%;
        transform: translateX(-50%);
    }

    /* Device pointer needle */
    .needle {
        position: absolute;
        width: 4px;
        height: 120px;
        background: #fff;
        border-radius: 2px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -100%);
        transform-origin: bottom center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }
    .needle::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 4px;
        height: 120px;
        background: rgba(255,255,255,0.3);
        transform: translateY(100%);
        border-radius: 2px;
    }

    /* Bottom Text Area */
    .bottom-info {
        position: absolute;
        bottom: 120px;
        left: 0;
        right: 0;
        text-align: center;
        z-index: 10;
    }
    .degree-text {
        color: rgba(255, 255, 255, 0.8);
        font-size: 16px;
        font-weight: 500;
        margin-bottom: 8px;
        letter-spacing: 0.5px;
    }
    .qibla-title {
        color: #ffffff;
        font-size: 48px;
        font-weight: 700;
        margin: 0;
        letter-spacing: -1px;
    }

    /* Bottom Nav Actions */
    .bottom-actions {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        padding: 20px 32px;
        padding-bottom: max(20px, env(safe-area-inset-bottom));
        display: flex;
        justify-content: space-between;
        align-items: center;
        z-index: 10;
    }
    .btn-outline-white {
        border: 1px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        width: 44px; height: 44px;
        display: flex; align-items: center; justify-content: center;
        color: white;
        background: rgba(0,0,0,0.2);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        text-decoration: none !important;
        transition: all 0.2s;
    }
    .btn-outline-white:hover {
        background: rgba(255,255,255,0.1);
        border-color: rgba(255,255,255,0.6);
        color: white;
    }
    
    /* Modal / Popup Info */
    .custom-modal-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.6);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .custom-modal-overlay.active {
        display: flex;
        animation: fadeIn 0.2s ease;
    }
    .custom-modal {
        background: #1a1a1a;
        border-radius: 20px;
        width: 100%;
        max-width: 360px;
        padding: 24px;
        position: relative;
        box-shadow: 0 12px 32px rgba(0,0,0,0.4);
    }
    .custom-modal-close {
        position: absolute;
        top: 16px; right: 16px;
        background: rgba(255,255,255,0.1);
        border: none;
        color: rgba(255,255,255,0.6);
        width: 30px; height: 30px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
    }
    .custom-modal-title {
        color: white;
        font-size: 18px;
        font-weight: 600;
        margin-top: 0;
        margin-bottom: 12px;
        padding-right: 30px;
        line-height: 1.4;
    }
    .custom-modal-text {
        color: rgba(255,255,255,0.7);
        font-size: 14px;
        margin-bottom: 20px;
    }
    .calibration-img-container {
        background: #4285f4;
        border-radius: 8px;
        padding: 30px 0;
        text-align: center;
        margin-bottom: 8px;
    }
    .calibration-img-container img {
        height: 120px;
    }
    .calibration-source {
        font-size: 11px;
        color: rgba(255,255,255,0.4);
        text-align: center;
        display: block;
        margin-bottom: 24px;
    }
    .modal-link {
        color: #6fcf97;
        font-size: 14px;
        font-weight: 500;
        text-align: center;
        display: block;
        text-decoration: none;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }

    /* Warning for no compass sensor */
    #sensor-warning {
        display: none;
        position: absolute;
        top: 80px;
        left: 20px; right: 20px;
        background: rgba(239, 68, 68, 0.9);
        color: white;
        padding: 12px;
        border-radius: 8px;
        text-align: center;
        font-size: 14px;
        z-index: 50;
        backdrop-filter: blur(4px);
    }
</style>
@endpush

@section('content')
<div class="content-wrapper qibla-page">
    <div class="qibla-bg"></div>

    <div id="sensor-warning">
        Sensor kompas tidak terdeteksi atau izin belum diberikan.
        <br><button onclick="requestDeviceOrientation()" style="margin-top:8px; padding:6px 12px; border-radius:4px; border:none; background:white; color:#ef4444; font-weight:bold;">Minta Izin Sensor (iOS)</button>
    </div>

    <!-- Top Bar -->
    <div class="qibla-top-actions">
        <div></div> <!-- spacer -->
        <button class="icon-btn" onclick="openInfoModal()" title="Informasi">
            <span class="material-symbols-outlined">info</span>
        </button>
    </div>

    <!-- Compass -->
    <div class="compass-container">
        <div class="compass-ring" id="compassRing">
            <!-- Labels -->
            <div class="compass-label label-n">N</div>
            <div class="compass-label label-e">E</div>
            <div class="compass-label label-s">S</div>
            <div class="compass-label label-w">W</div>

            <!-- Cross lines -->
            <div class="cross-line-v"></div>
            <div class="cross-line-h"></div>

            <!-- Qibla Marker (Kaaba) -->
            <div class="qibla-marker-container" id="qiblaMarkerContainer">
                <div class="qibla-marker" id="qiblaMarker"></div>
            </div>
        </div>
        
        <!-- Fixed Needle (Phone direction) -->
        <div style="position: absolute; top: 50%; left: 50%; width: 0; height: 0;">
            <div class="needle"></div>
        </div>
    </div>

    <!-- Bottom Info -->
    <div class="bottom-info">
        <div class="degree-text"><span id="degreeValue">295.0° N</span> &nbsp; <span id="distanceValue">7927 km</span></div>
        <h1 class="qibla-title">Qibla</h1>
    </div>

    <!-- Bottom Actions -->
    <div class="bottom-actions">
        <button class="btn-outline-white" title="Map">
            <span class="material-symbols-outlined">map</span>
        </button>
        <button class="btn-outline-white" onclick="history.back()" title="Tutup">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>

    <!-- Info Modal -->
    <div class="custom-modal-overlay" id="infoModal">
        <div class="custom-modal">
            <button class="custom-modal-close" onclick="closeInfoModal()">
                <span class="material-symbols-outlined" style="font-size: 20px;">close</span>
            </button>
            <h3 class="custom-modal-title">Bagaimana cara meningkatkan akurasi kompas?</h3>
            <p class="custom-modal-text">Ulangi gerakan perangkat seperti berikut ini:</p>
            
            <div class="calibration-img-container">
                <!-- Using a simple text representation or icon for figure 8 since we don't have the gif -->
                <span class="material-symbols-outlined" style="font-size: 80px; color: white;">360</span>
            </div>
            <span class="calibration-source">Gif source: Google Maps Help</span>

            <a href="#" class="modal-link">Mengapa arah Kiblat bisa tidak akurat?</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Constants for Makkah (Kaaba)
    const KAABA_LAT = 21.422487;
    const KAABA_LNG = 39.826206;
    
    // Default Jakarta coordinates if geolocation fails
    let userLat = -6.2088;
    let userLng = 106.8456;
    let qiblaHeading = 295.1; // Default for Jakarta
    
    // UI Elements
    const compassRing = document.getElementById('compassRing');
    const qiblaMarker = document.getElementById('qiblaMarker');
    const degreeValue = document.getElementById('degreeValue');
    const distanceValue = document.getElementById('distanceValue');
    const sensorWarning = document.getElementById('sensorWarning');

    // Calculate distance using Haversine formula
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return Math.round(R * c);
    }

    // Calculate bearing (Qibla direction)
    function calculateQibla(lat, lng) {
        const latK = KAABA_LAT * Math.PI / 180;
        const lngK = KAABA_LNG * Math.PI / 180;
        const latU = lat * Math.PI / 180;
        const lngU = lng * Math.PI / 180;

        const y = Math.sin(lngK - lngU);
        const x = Math.cos(latU) * Math.tan(latK) - Math.sin(latU) * Math.cos(lngK - lngU);
        let qibla = Math.atan2(y, x) * 180 / Math.PI;
        
        return (qibla + 360) % 360;
    }

    // Initialize Location
    function initLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    userLat = position.coords.latitude;
                    userLng = position.coords.longitude;
                    qiblaHeading = calculateQibla(userLat, userLng);
                    const dist = calculateDistance(userLat, userLng, KAABA_LAT, KAABA_LNG);
                    distanceValue.innerText = dist + " km";
                    updateQiblaMarker();
                },
                (error) => {
                    console.log("Geolocation error, using default Jakarta location.");
                    updateQiblaMarker();
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        } else {
            updateQiblaMarker();
        }
    }

    function updateQiblaMarker() {
        const container = document.getElementById('qiblaMarkerContainer');
        container.style.transform = `rotate(${qiblaHeading}deg)`;
    }

    // Device Orientation for Compass
    function handleOrientation(event) {
        let alpha = event.webkitCompassHeading || Math.abs(event.alpha - 360);
        if (alpha == null) return;

        // Rotate the ring based on phone orientation
        // If phone points North (alpha=0), ring rotation is 0.
        // If phone points East (alpha=90), we rotate ring -90deg so N is on the left.
        compassRing.style.transform = `rotate(${-alpha}deg)`;
        
        degreeValue.innerText = Math.round(alpha) + "° N";
    }

    function requestDeviceOrientation() {
        if (typeof DeviceOrientationEvent !== 'undefined' && typeof DeviceOrientationEvent.requestPermission === 'function') {
            DeviceOrientationEvent.requestPermission()
                .then(permissionState => {
                    if (permissionState === 'granted') {
                        document.getElementById('sensor-warning').style.display = 'none';
                        window.addEventListener('deviceorientation', handleOrientation, true);
                    }
                })
                .catch(console.error);
        } else {
            // non iOS 13+ devices
            window.addEventListener('deviceorientationabsolute', handleOrientation, true);
        }
    }

    // Check if orientation is supported without permission (Android)
    if (window.DeviceOrientationEvent) {
        window.addEventListener("deviceorientationabsolute", handleOrientation, true);
        window.addEventListener("deviceorientation", handleOrientation, true);
    } else {
        document.getElementById('sensor-warning').style.display = 'block';
    }

    // Modal logic
    const infoModal = document.getElementById('infoModal');
    function openInfoModal() {
        infoModal.classList.add('active');
    }
    function closeInfoModal() {
        infoModal.classList.remove('active');
    }

    // Close modal on outside click
    infoModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeInfoModal();
        }
    });

    // Run init
    initLocation();

</script>
@endpush
