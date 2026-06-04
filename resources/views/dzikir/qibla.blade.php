<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Qibla Compass</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(180deg, #1a4a6a 0%, #1e5f8e 20%, #2d6a4f 55%, #8b7355 85%, #b8956a 100%);
            min-height: 100vh;
            overflow-x: hidden;
            color: #fff;
        }

        .bg-mosque {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: 
                radial-gradient(ellipse at 70% 40%, rgba(45, 106, 79, 0.6) 0%, transparent 60%),
                radial-gradient(ellipse at 50% 100%, rgba(184, 149, 106, 0.5) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .app-wrapper {
            position: relative;
            z-index: 1;
            max-width: 430px;
            margin: 0 auto;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .status-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 20px 8px;
            font-size: 15px;
            font-weight: 600;
        }

        .status-icons { display: flex; gap: 6px; align-items: center; }

        .info-btn {
            position: absolute;
            top: 50px; right: 20px;
            width: 34px; height: 34px;
            border: 2px solid rgba(255,255,255,0.7);
            border-radius: 50%;
            background: transparent;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        .info-btn:hover { background: rgba(255,255,255,0.15); }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .compass-container {
            position: relative;
            width: 320px; height: 320px;
            margin: 20px auto;
        }

        .compass-outer {
            width: 100%; height: 100%;
            border: 4px solid rgba(255,255,255,0.95);
            border-radius: 50%;
            position: relative;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
            transition: transform 0.3s ease-out;
        }

        .cardinal {
            position: absolute;
            color: rgba(255,255,255,0.85);
            font-size: 18px;
            font-weight: 600;
            width: 30px; height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .cardinal.n { top: 10px; left: 50%; transform: translateX(-50%); }
        .cardinal.s { bottom: 10px; left: 50%; transform: translateX(-50%); }
        .cardinal.e { right: 10px; top: 50%; transform: translateY(-50%); }
        .cardinal.w { left: 10px; top: 50%; transform: translateY(-50%); }

        .needle {
            position: absolute;
            top: 50%; left: 50%;
            width: 4px; height: 130px;
            transform-origin: bottom center;
            transform: translate(-50%, -100%);
            transition: transform 0.5s ease-out;
        }
        .needle::before {
            content: '';
            position: absolute;
            top: 0; left: 50%;
            transform: translateX(-50%);
            width: 4px; height: 100%;
            background: linear-gradient(to bottom, #fff 0%, rgba(255,255,255,0.3) 100%);
            border-radius: 2px;
        }

        .needle-center {
            position: absolute;
            top: 50%; left: 50%;
            width: 14px; height: 14px;
            background: #fff;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
            z-index: 5;
        }

        .kaaba-marker {
            position: absolute;
            width: 52px; height: 52px;
            background: linear-gradient(135deg, #e8b878 0%, #d4a054 50%, #b8863a 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
            border: 2px solid rgba(255,255,255,0.4);
            z-index: 10;
            transition: all 0.5s ease-out;
        }
        .kaaba-marker::before {
            content: '';
            width: 32px; height: 32px;
            background: linear-gradient(135deg, #2a1810 0%, #1a0f08 100%);
            border-radius: 4px;
        }
        .kaaba-marker::after {
            content: '';
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 28px; height: 3px;
            background: #d4af37;
            border-radius: 2px;
        }

        .info-section { text-align: center; margin-top: 30px; }

        .degree-display {
            font-size: 22px;
            font-weight: 400;
            opacity: 0.95;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .qibla-title {
            font-size: 58px;
            font-weight: 300;
            letter-spacing: 3px;
            text-shadow: 0 2px 15px rgba(0,0,0,0.3);
        }

        .status-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 15px;
            font-size: 13px;
            opacity: 0.8;
        }

        .status-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        .status-dot.ok { background: #4ade80; }
        .status-dot.error { background: #ef4444; }
        .status-dot.loading { background: #fbbf24; }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }

        .bottom-bar {
            padding: 20px;
            background: linear-gradient(transparent, rgba(0,0,0,0.4));
        }

        .bottom-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .icon-btn {
            width: 44px; height: 44px;
            border: 2px solid rgba(255,255,255,0.6);
            border-radius: 12px;
            background: rgba(255,255,255,0.1);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s;
            backdrop-filter: blur(10px);
        }
        .icon-btn:hover { background: rgba(255,255,255,0.2); transform: translateY(-2px); }

        .makkah-btn {
            padding: 12px 32px;
            background: #fff;
            color: #2d6a4f;
            border: none;
            border-radius: 25px;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 1.5px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            transition: all 0.3s;
        }
        .makkah-btn:hover { transform: translateY(-2px); }

        .modal-content {
            background: rgba(30, 58, 78, 0.97);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .modal-header { border-bottom: 1px solid rgba(255,255,255,0.1); }
        .btn-close-white { filter: invert(1); }

        .permission-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.85);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }
        .permission-overlay.show { display: flex; }
        .permission-box {
            background: rgba(30, 58, 78, 0.95);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            max-width: 360px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .permission-box i { font-size: 48px; margin-bottom: 15px; color: #fbbf24; }
        .permission-box h5 { margin-bottom: 10px; }
        .permission-box p { opacity: 0.8; font-size: 14px; margin-bottom: 20px; }
        .permission-box button {
            padding: 12px 30px;
            background: #fff;
            color: #1e3a4e;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
        }

        .location-badge {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            margin-top: 10px;
            backdrop-filter: blur(10px);
        }

        @media (max-width: 380px) {
            .compass-container { width: 280px; height: 280px; }
            .qibla-title { font-size: 48px; }
        }
    </style>
</head>
<body>
    <div class="bg-mosque"></div>

    <div class="app-wrapper">
        <!-- Status Bar -->
        <div class="status-bar">
            <div class="d-flex align-items-center gap-2">
                <span id="currentTime">21.20</span>
                <i class="bi bi-geo-alt-fill" style="font-size: 14px;"></i>
            </div>
            <div class="status-icons">
                <i class="bi bi-reception-4"></i>
                <i class="bi bi-wifi"></i>
                <i class="bi bi-battery-half"></i>
            </div>
        </div>

        <!-- Info Button -->
        <button class="info-btn" onclick="showInfo()">
            <i class="bi bi-info"></i>
        </button>

        <!-- Main Content -->
        <div class="main-content">
            <div class="compass-container">
                <div class="compass-outer" id="compassOuter">
                    <div class="cardinal n">N</div>
                    <div class="cardinal e">E</div>
                    <div class="cardinal s">S</div>
                    <div class="cardinal w">W</div>
                    <div class="needle" id="needle"></div>
                    <div class="needle-center"></div>
                    <div class="kaaba-marker" id="kaabaMarker"></div>
                </div>
            </div>

            <div class="info-section">
                <div class="degree-display">
                    <span id="degreeValue">---.-</span>° N &nbsp;
                    <span id="distanceValue">---</span> km
                </div>
                <h1 class="qibla-title">Qibla</h1>
                <div class="location-badge" id="locationBadge">
                    <i class="bi bi-geo-alt"></i> <span id="locationName">Mencari lokasi...</span>
                </div>
                <div class="status-indicator">
                    <div class="status-dot loading" id="statusDot"></div>
                    <span id="statusText">Mencari lokasi Anda...</span>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="bottom-bar">
            <div class="bottom-content">
                <button class="icon-btn" onclick="toggleMap()" title="Buka Peta">
                    <i class="bi bi-map"></i>
                </button>
                <button class="makkah-btn" onclick="openMakkahLive()">
                    MAKKAH LIVE
                </button>
                <button class="icon-btn" onclick="closeApp()" title="Tutup">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Permission Overlay -->
    <div class="permission-overlay" id="permissionOverlay">
        <div class="permission-box">
            <i class="bi bi-compass"></i>
            <h5>Akses Diperlukan</h5>
            <p>Aplikasi membutuhkan akses lokasi dan sensor kompas untuk menentukan arah kiblat dari posisi Anda saat ini.</p>
            <button onclick="requestPermissions()">Izinkan</button>
        </div>
    </div>

    <!-- Info Modal -->
    <div class="modal fade" id="infoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Informasi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><strong>📍 Lokasi Anda:</strong><br><span id="infoLocation" class="text-info">-</span></div>
                    <div class="mb-3"><strong>🕋 Arah Kiblat:</strong><br><span id="infoDirection" class="text-warning">-</span>° dari Utara</div>
                    <div class="mb-3"><strong>📏 Jarak ke Mekkah:</strong><br><span id="infoDistance" class="text-success">-</span> km</div>
                    <div class="mb-3"><strong>🧭 Heading Device:</strong><br><span id="infoHeading" class="text-light">-</span>°</div>
                    <div class="mb-0"><strong>🌍 Koordinat Ka'bah:</strong><br><span class="text-light">21.4225°N, 39.8252°E</span></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ========== KONFIGURASI ==========
        const KAABA_LAT = 21.4224779;
        const KAABA_LNG = 39.8251832;
        const EARTH_RADIUS = 6371; // km

        // ========== STATE ==========
        let state = {
            userLat: null,
            userLng: null,
            qiblaBearing: 0,
            distance: 0,
            deviceHeading: 0,
            locationReady: false,
            compassReady: false
        };

        // ========== INISIALISASI ==========
        document.addEventListener('DOMContentLoaded', () => {
            updateTime();
            setInterval(updateTime, 30000);

            // Cek permission iOS 13+
            if (typeof DeviceOrientationEvent !== 'undefined' &&
                typeof DeviceOrientationEvent.requestPermission === 'function') {
                document.getElementById('permissionOverlay').classList.add('show');
            } else {
                initApp();
            }
        });

        function requestPermissions() {
            document.getElementById('permissionOverlay').classList.remove('show');
            initApp();
        }

        async function initApp() {
            await getLocation();
            initCompass();
        }

        // ========== GEOLOCATION - MENDAPATKAN LOKASI USER ==========
        function getLocation() {
            return new Promise((resolve) => {
                if (!navigator.geolocation) {
                    updateStatus('error', 'Geolocation tidak didukung');
                    setUserLocation(-6.2088, 106.8456, 'Jakarta (default)');
                    resolve();
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        setUserLocation(
                            pos.coords.latitude,
                            pos.coords.longitude,
                            `${pos.coords.latitude.toFixed(4)}, ${pos.coords.longitude.toFixed(4)}`
                        );
                        resolve();
                    },
                    (err) => {
                        console.error('Location error:', err);
                        updateStatus('error', 'Lokasi ditolak, menggunakan default');
                        setUserLocation(-6.2088, 106.8456, 'Jakarta (default)');
                        resolve();
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            });
        }

        function setUserLocation(lat, lng, label) {
            state.userLat = lat;
            state.userLng = lng;
            state.qiblaBearing = calculateQiblaBearing(lat, lng);
            state.distance = calculateDistance(lat, lng, KAABA_LAT, KAABA_LNG);
            state.locationReady = true;

            // Update UI
            document.getElementById('degreeValue').textContent = state.qiblaBearing.toFixed(1);
            document.getElementById('distanceValue').textContent = state.distance.toLocaleString('id-ID').replace(/,/g, ' ');
            document.getElementById('locationName').textContent = label;

            // Update modal info
            document.getElementById('infoLocation').textContent = label;
            document.getElementById('infoDirection').textContent = state.qiblaBearing.toFixed(2);
            document.getElementById('infoDistance').textContent = state.distance.toLocaleString('id-ID').replace(/,/g, ' ');

            updateStatus('loading', 'Menunggu kompas...');
            updateUI();
        }

        // ========== RUMUS PERHITUNGAN KIBLAT ==========
        function calculateQiblaBearing(lat, lng) {
            const phi1 = lat * Math.PI / 180;
            const phi2 = KAABA_LAT * Math.PI / 180;
            const dLambda = (KAABA_LNG - lng) * Math.PI / 180;

            const y = Math.sin(dLambda) * Math.cos(phi2);
            const x = Math.cos(phi1) * Math.sin(phi2) -
                      Math.sin(phi1) * Math.cos(phi2) * Math.cos(dLambda);

            let bearing = Math.atan2(y, x) * 180 / Math.PI;
            return (bearing + 360) % 360;
        }

        // ========== RUMUS HAVERSINE - JARAK KE MEKKAH ==========
        function calculateDistance(lat1, lng1, lat2, lng2) {
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLng = (lng2 - lng1) * Math.PI / 180;
            const a = Math.sin(dLat/2)**2 +
                      Math.cos(lat1 * Math.PI/180) * Math.cos(lat2 * Math.PI/180) *
                      Math.sin(dLng/2)**2;
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return Math.round(EARTH_RADIUS * c);
        }

        // ========== KOMPAS / DEVICE ORIENTATION ==========
        function initCompass() {
            if (typeof DeviceOrientationEvent !== 'undefined' &&
                typeof DeviceOrientationEvent.requestPermission === 'function') {
                DeviceOrientationEvent.requestPermission()
                    .then(response => {
                        if (response === 'granted') {
                            window.addEventListener('deviceorientation', handleOrientation);
                        } else {
                            updateStatus('error', 'Akses kompas ditolak');
                            updateUI();
                        }
                    })
                    .catch(console.error);
            } else {
                window.addEventListener('deviceorientationabsolute', handleOrientation, true);
                window.addEventListener('deviceorientation', handleOrientation, true);
            }

            setTimeout(() => {
                if (!state.compassReady) {
                    updateStatus('error', 'Sensor kompas tidak tersedia');
                    updateUI();
                }
            }, 5000);
        }

        function handleOrientation(event) {
            let heading = 0;

            if (event.webkitCompassHeading !== undefined) {
                heading = event.webkitCompassHeading;
            } else if (event.alpha !== null) {
                heading = (360 - event.alpha) % 360;
            } else {
                return;
            }

            state.deviceHeading = heading;
            state.compassReady = true;

            updateStatus('ok', 'Kompas aktif');
            updateUI();
        }

        // ========== UPDATE UI ==========
        function updateUI() {
            const { qiblaBearing, deviceHeading, distance } = state;

            // Putar kompas agar N selalu menghadap utara device
            const compassOuter = document.getElementById('compassOuter');
            compassOuter.style.transform = `rotate(${-deviceHeading}deg)`;

            // Posisikan icon Ka'bah di tepi lingkaran sesuai arah kiblat
            const kaabaMarker = document.getElementById('kaabaMarker');
            const radius = 160;
            const angleRad = (qiblaBearing - 90) * Math.PI / 180;
            const kaabaX = radius + radius * Math.cos(angleRad) - 26;
            const kaabaY = radius + radius * Math.sin(angleRad) - 26;
            kaabaMarker.style.left = `${kaabaX}px`;
            kaabaMarker.style.top = `${kaabaY}px`;

            // Jarum menunjuk arah kiblat relatif terhadap device
            const needle = document.getElementById('needle');
            const needleRotation = qiblaBearing - deviceHeading;
            needle.style.transform = `translate(-50%, -100%) rotate(${needleRotation}deg)`;

            // Update heading di modal
            document.getElementById('infoHeading').textContent = deviceHeading.toFixed(1);
        }

        function updateStatus(type, text) {
            const dot = document.getElementById('statusDot');
            const statusText = document.getElementById('statusText');
            dot.className = `status-dot ${type}`;
            statusText.textContent = text;
        }

        function updateTime() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('currentTime').textContent = `${h}.${m}`;
        }

        // ========== FITUR TAMBAHAN ==========
        function showInfo() {
            const modal = new bootstrap.Modal(document.getElementById('infoModal'));
            modal.show();
        }

        function toggleMap() {
            if (state.userLat) {
                const url = `https://www.google.com/maps/dir/${state.userLat},${state.userLng}/${KAABA_LAT},${KAABA_LNG}`;
                window.open(url, '_blank');
            } else {
                alert('Lokasi belum tersedia');
            }
        }

        function openMakkahLive() {
            window.open('https://www.youtube.com/watch?v=H020lXNlMxM', '_blank');
        }

        function closeApp() {
            if (confirm('Keluar dari aplikasi?')) {
                window.history.back();
            }
        }

        // Watch position untuk update real-time saat user bergerak
        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(
                (pos) => {
                    setUserLocation(
                        pos.coords.latitude,
                        pos.coords.longitude,
                        `${pos.coords.latitude.toFixed(4)}, ${pos.coords.longitude.toFixed(4)}`
                    );
                },
                () => {},
                { enableHighAccuracy: true, maximumAge: 5000 }
            );
        }
    </script>
</body>
</html>
