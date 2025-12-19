@extends('layout.master')

@section('title')
    Absen Mandiri - {{ ucfirst($mode) }}
@endsection

@section('heading')
    Absen Mandiri
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5 col-xl-4">
        
        {{-- CARD UTAMA --}}
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden position-relative main-card">
            
            {{-- HEADER CARD --}}
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Absen {{ ucfirst($mode) }}</h5>
                        <p class="text-muted small mb-0">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                    
                    {{-- INDICATOR MODE --}}
                    @if(Auth::user()->use_face_recognition)
                        <div class="status-pill active" data-bs-toggle="tooltip" title="AI Face Recognition Aktif">
                            <span class="dot pulse"></span> AI Detect
                        </div>
                    @else
                        <div class="status-pill manual" data-bs-toggle="tooltip" title="Mode Foto Manual">
                            <span class="dot"></span> Manual
                        </div>
                    @endif
                </div>

                {{-- ALERT INFO (JIKA ABSEN PULANG) --}}
                @if ($mode == 'pulang' && isset($attendance))
                    <div class="alert alert-soft-primary d-flex align-items-center py-2 px-3 rounded-3 mt-3 mb-0 fade show">
                        <i class="mdi mdi-clock-check-outline fs-5 me-2"></i>
                        <div class="small">
                            Masuk pukul <span class="fw-bold">{{ \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="card-body px-4 pb-4 pt-3">
                <form action="{{ route('self.attend.store') }}" method="POST" enctype="multipart/form-data" id="attendance-form">
                    @csrf
                    @if (isset($attendance) && $attendance)
                        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                    @endif
                    
                    {{-- Input File Hidden untuk hasil foto --}}
                    <input type="file" name="photo" id="photo-input-hidden" class="d-none" accept="image/jpeg">

                    {{-- === VIEW FINDER / KAMERA === --}}
                    <div class="camera-container mb-4">
                        
                        {{-- 1. VIDEO ELEMENT --}}
                        <video id="video-feed" autoplay muted playsinline></video>

                        {{-- 2. SCANNING LINE ANIMATION (AI MODE ONLY) --}}
                        @if(Auth::user()->use_face_recognition)
                            <div class="scan-line d-none" id="ai-scan-line"></div>
                        @endif

                        {{-- 3. OVERLAY UI (Status, Instructions) --}}
                        <div class="camera-overlay">
                            {{-- Top Gradient & Status --}}
                            <div class="overlay-top p-3 text-center">
                                <span id="face-status-badge" class="badge-glass">
                                    <i class="mdi mdi-camera-off me-1"></i> Kamera Belum Aktif
                                </span>
                            </div>

                            {{-- Focus Frame --}}
                            <div class="focus-area">
                                <div class="focus-box {{ Auth::user()->use_face_recognition ? 'ai-mode' : 'manual-mode' }}">
                                    <div class="corner tl"></div>
                                    <div class="corner tr"></div>
                                    <div class="corner bl"></div>
                                    <div class="corner br"></div>
                                </div>
                                <div class="instruction-text" id="camera-instruction">Ketuk tombol kamera</div>
                            </div>

                            {{-- Bottom Gradient --}}
                            <div class="overlay-bottom"></div>
                        </div>

                        {{-- 4. START SCREEN (Cover) --}}
                        <div id="start-screen" class="camera-cover">
                            <div class="content text-center">
                                <div class="icon-pulse mb-3">
                                    <i class="mdi mdi-camera-iris text-white fs-1"></i>
                                </div>
                                <button type="button" id="start-camera-btn" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm">
                                    Buka Kamera
                                </button>
                                <div id="model-loading-text" class="text-white-50 mt-3 small d-none">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Menyiapkan AI...
                                </div>
                            </div>
                        </div>

                        {{-- 5. RESULT SCREEN (Preview) --}}
                        <div id="result-screen" class="camera-result d-none">
                            <img id="preview-image" src="" alt="Capture">
                            <div class="result-actions">
                                <button type="button" id="retake-btn" class="btn-retake">
                                    <i class="mdi mdi-refresh"></i> Ulangi
                                </button>
                                <span class="badge bg-success rounded-pill px-3 py-2 shadow-sm">
                                    <i class="mdi mdi-check-circle me-1"></i> Foto Siap
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- CONTROLLER SECTION (Shutter & Inputs) --}}
                    
                    {{-- SHUTTER BUTTON --}}
                    <div class="d-flex justify-content-center mb-4 position-relative z-index-10">
                        <button type="button" id="capture-btn" class="shutter-button" disabled>
                            <div class="inner-circle"></div>
                        </button>
                    </div>

                    {{-- LOCATION WIDGET --}}
                    <div class="location-widget mb-4" onclick="getLocation(true)" style="cursor: pointer;">
                        <div class="loc-icon">
                            <i class="mdi mdi-map-marker-radius"></i>
                        </div>
                        <div class="loc-info">
                            <label>Lokasi Saat Ini</label>
                            <h6 id="coordinates-display" class="text-truncate">Mencari Koordinat...</h6>
                        </div>
                        <div class="loc-badge">
                            <span id="gps-accuracy-badge" class="badge bg-light text-dark border">...</span>
                        </div>
                        <div class="ms-2">
                             <i class="mdi mdi-refresh text-muted"></i>
                        </div>
                    </div>

                    {{-- NOTES INPUT --}}
                    <div class="form-group mb-4">
                        <label class="form-label small text-muted fw-bold text-uppercase">Catatan (Opsional)</label>
                        <textarea name="notes" class="form-control form-control-modern" rows="2" placeholder="Sedang di lokasi klien..."></textarea>
                    </div>

                    {{-- HIDDEN INPUTS (Fixed IDs) --}}
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">
                    <input type="hidden" id="accuracy" name="accuracy">

                    {{-- SLIDE TO SUBMIT --}}
                    <div class="slide-submit-wrapper">
                        <div class="slide-track disabled" id="slide-track">
                            <div class="slide-bg-text">GESER UNTUK ABSEN</div>
                            <div class="slide-thumb" id="slide-thumb">
                                <i class="mdi mdi-chevron-double-right"></i>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <a href="{{ route('dashboard') }}" class="text-muted small text-decoration-none fw-medium">Kembali ke Dashboard</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Canvas Tersembunyi untuk Capture --}}
<canvas id="capture-canvas" class="d-none"></canvas>
@endsection

@push('styles')
<style>
    :root {
        --primary-color: #4361ee;
        --success-color: #06d6a0;
        --bg-soft: #f8f9fa;
        --card-radius: 24px;
    }

    /* === UTILS === */
    .main-card { background: #fff; border-radius: var(--card-radius); overflow: hidden; transition: transform 0.2s; }
    .alert-soft-primary { background-color: rgba(67, 97, 238, 0.1); color: var(--primary-color); border: 1px solid rgba(67, 97, 238, 0.2); }
    
    /* === HEADER PILL === */
    .status-pill { display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; background: #f1f3f5; color: #6c757d; }
    .status-pill.active { background: rgba(6, 214, 160, 0.1); color: var(--success-color); border: 1px solid rgba(6, 214, 160, 0.2); }
    .status-pill.manual { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    .status-pill .dot { width: 8px; height: 8px; background: currentColor; border-radius: 50%; margin-right: 6px; display: inline-block; }
    .status-pill .dot.pulse { animation: pulse-green 2s infinite; }

    @keyframes pulse-green { 0% { box-shadow: 0 0 0 0 rgba(6, 214, 160, 0.4); } 70% { box-shadow: 0 0 0 6px rgba(6, 214, 160, 0); } 100% { box-shadow: 0 0 0 0 rgba(6, 214, 160, 0); } }

    /* === CAMERA CONTAINER === */
    .camera-container {
        position: relative;
        width: 100%;
        height: 420px; /* Tinggi fix agar proporsional */
        background: #000;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        transform: translateZ(0); /* Hardware accel */
    }

    #video-feed {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scaleX(-1); /* Mirror Effect */
        opacity: 0;
        transition: opacity 0.5s ease;
    }
    #video-feed.ready { opacity: 1; }

    /* AI Scanning Line */
    .scan-line {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 2px;
        background: #00ff88;
        box-shadow: 0 0 10px #00ff88, 0 0 20px #00ff88;
        z-index: 5;
        animation: scanMove 2s infinite linear;
    }
    @keyframes scanMove { 0% { top: 10%; opacity: 0; } 10% { opacity: 1; } 90% { opacity: 1; } 100% { top: 90%; opacity: 0; } }

    /* Overlay Gradient & UI */
    .camera-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 4; pointer-events: none; display: flex; flex-direction: column; justify-content: space-between; }
    .overlay-top { background: linear-gradient(to bottom, rgba(0,0,0,0.6), transparent); padding-top: 20px; }
    .overlay-bottom { height: 80px; background: linear-gradient(to top, rgba(0,0,0,0.6), transparent); }

    .badge-glass {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        padding: 6px 16px;
        border-radius: 30px;
        color: white;
        font-size: 0.8rem;
        font-weight: 500;
        border: 1px solid rgba(255,255,255,0.3);
        display: inline-block;
    }

    /* Focus Box */
    .focus-area { flex-grow: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; }
    .focus-box { width: 240px; height: 300px; position: relative; transition: all 0.3s ease; }
    
    .focus-box .corner { position: absolute; width: 20px; height: 20px; border-color: rgba(255,255,255,0.5); border-style: solid; transition: all 0.3s; }
    .focus-box .tl { top: 0; left: 0; border-width: 3px 0 0 3px; border-radius: 8px 0 0 0; }
    .focus-box .tr { top: 0; right: 0; border-width: 3px 3px 0 0; border-radius: 0 8px 0 0; }
    .focus-box .bl { bottom: 0; left: 0; border-width: 0 0 3px 3px; border-radius: 0 0 0 8px; }
    .focus-box .br { bottom: 0; right: 0; border-width: 0 3px 3px 0; border-radius: 0 0 8px 0; }

    .focus-box.active { transform: scale(1.02); }
    .focus-box.active .corner { border-color: #00ff88; box-shadow: 0 0 10px rgba(0,255,136,0.3); }

    .instruction-text {
        margin-top: 15px; color: rgba(255,255,255,0.9); font-size: 0.85rem; 
        font-weight: 500; text-shadow: 0 2px 4px rgba(0,0,0,0.5);
    }

    /* Start & Result Screens */
    .camera-cover { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: #1a1a1a; z-index: 6; display: flex; align-items: center; justify-content: center; }
    .camera-result { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: #000; z-index: 7; }
    #preview-image { width: 100%; height: 100%; object-fit: contain; }
    .result-actions { position: absolute; bottom: 20px; left: 0; width: 100%; text-align: center; display: flex; align-items: center; justify-content: center; gap: 15px; }
    .btn-retake { background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.3); color: white; border-radius: 30px; padding: 6px 16px; font-size: 0.85rem; backdrop-filter: blur(4px); transition: 0.2s; cursor: pointer; }
    .btn-retake:hover { background: rgba(255,255,255,0.2); }

    /* === SHUTTER BUTTON (iOS Style) === */
    .shutter-button {
        width: 72px; height: 72px; border-radius: 50%;
        background: transparent; border: 4px solid #e5e5e5;
        padding: 4px; cursor: pointer; outline: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex; align-items: center; justify-content: center;
    }
    .shutter-button .inner-circle { width: 100%; height: 100%; background: #e5e5e5; border-radius: 50%; transition: all 0.3s; }
    
    .shutter-button:disabled { opacity: 0.5; cursor: not-allowed; border-color: #ccc; }
    .shutter-button:disabled .inner-circle { background: #ccc; }

    .shutter-button.ready { border-color: var(--primary-color); transform: scale(1.05); }
    .shutter-button.ready .inner-circle { background: var(--primary-color); }
    .shutter-button.ready:active { transform: scale(0.95); }
    .shutter-button.ready:active .inner-circle { transform: scale(0.85); }

    /* === LOCATION WIDGET === */
    .location-widget {
        background: #f8f9fa; border-radius: 16px; padding: 12px 16px;
        display: flex; align-items: center; gap: 12px;
        border: 1px solid #e9ecef;
    }
    .loc-icon { width: 40px; height: 40px; background: #e0e7ff; color: var(--primary-color); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
    .loc-info { flex-grow: 1; overflow: hidden; }
    .loc-info label { display: block; font-size: 0.7rem; color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
    .loc-info h6 { margin: 0; font-size: 0.9rem; font-weight: 600; color: #333; }
    
    /* === FORM INPUTS === */
    .form-control-modern { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 12px; padding: 12px; resize: none; font-size: 0.9rem; transition: 0.2s; }
    .form-control-modern:focus { background: #fff; border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1); outline: none; }

    /* === SLIDE TO SUBMIT === */
    .slide-submit-wrapper { position: relative; width: 100%; height: 56px; border-radius: 50px; overflow: hidden; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05); }
    .slide-track {
        background: #e9ecef; height: 100%; width: 100%; display: flex; align-items: center; position: relative; cursor: pointer; transition: background 0.4s;
    }
    .slide-track.disabled { pointer-events: none; opacity: 0.6; filter: grayscale(1); }
    .slide-track.submitted { background: var(--success-color); }
    
    .slide-bg-text {
        position: absolute; left: 0; width: 100%; text-align: center;
        color: #adb5bd; font-weight: 700; font-size: 0.85rem; letter-spacing: 1px;
        z-index: 1; user-select: none;
        background: linear-gradient(90deg, #adb5bd 0%, #fff 50%, #adb5bd 100%);
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        transition: opacity 0.3s;
    }
    .slide-bg-text.text-active {
        background: linear-gradient(90deg, #06d6a0 0%, #00b4d8 50%, #06d6a0 100%);
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: shine 2s linear infinite;
        opacity: 1;
    }
    @keyframes shine { to { background-position: 200% center; } }
    
    .slide-thumb {
        width: 48px; height: 48px; background: #fff; border-radius: 50%;
        margin-left: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex; align-items: center; justify-content: center;
        color: var(--primary-color); font-size: 1.2rem;
        position: relative; z-index: 2; transition: transform 0.1s;
        touch-action: none;
    }
    .slide-track.submitted .slide-thumb { color: var(--success-color); }

    /* Mobile Tweaks */
    @media (max-width: 576px) {
        .card-body { padding: 1.25rem; }
        .camera-container { height: 380px; }
        .focus-box { width: 200px; height: 260px; }
    }
</style>
@endpush

@push('scripts')
{{-- LOAD FACE API --}}
<script defer src="https://cdn.jsdelivr.net/npm/face-api.js/dist/face-api.min.js"></script>
{{-- LOAD SWEETALERT (Untuk UI Loading/Error yang lebih bagus) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ==========================================
        // CONFIGURATION FOR PERFORMANCE & NETWORK
        // ==========================================
        const CONFIG = {
            AI_INTERVAL_MS: 800,      // Cek wajah tiap 800ms (Bukan Realtime 60fps) -> Hemat Baterai & CPU
            AI_INPUT_SIZE: 160,       // Resolusi input AI kecil saja (makin kecil makin cepat)
            AI_SCORE_THRESHOLD: 0.5,  // Toleransi kemiripan wajah
            MAX_RETRIES: 5,           // Coba kirim ulang 5x jika sinyal hilang
            RETRY_DELAY: 2000,        // Jeda 2 detik sebelum kirim ulang
            COMPRESSION_QUALITY: 0.7  // Kompresi foto di klien (0.7 = 70%)
        };

        const useAI = {{ Auth::user()->use_face_recognition ? 'true' : 'false' }};
        const assetPath = "{{ asset('public/models') }}"; 

        // DOM Elements
        const videoFeed = document.getElementById('video-feed');
        const startScreen = document.getElementById('start-screen');
        const resultScreen = document.getElementById('result-screen');
        const startBtn = document.getElementById('start-camera-btn');
        const captureBtn = document.getElementById('capture-btn');
        const retakeBtn = document.getElementById('retake-btn');
        const previewImage = document.getElementById('preview-image');
        const photoInputHidden = document.getElementById('photo-input-hidden');
        const canvas = document.getElementById('capture-canvas');
        
        // Location & Form
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const accInput = document.getElementById('accuracy');
        const coordDisplay = document.getElementById('coordinates-display');
        const accBadge = document.getElementById('gps-accuracy-badge');
        const form = document.getElementById('attendance-form');
        const slideTrack = document.getElementById('slide-track');
        const slideThumb = document.getElementById('slide-thumb');
        
        // UI Status
        const faceStatusBadge = document.getElementById('face-status-badge');
        const focusBox = document.querySelector('.focus-box');
        const modelLoadingText = document.getElementById('model-loading-text');

        // State Management
        let streamRef = null;
        let isProcessingAI = false; 
        let isFaceValid = false;
        let aiInterval = null; // Variable untuk menyimpan interval

        // --- 1. INITIALIZE & LOCATION ---
        getLocation(); // Get location immediately

        startBtn.addEventListener('click', async () => {
            startBtn.disabled = true;
            
            if (useAI) {
                toggleLoadingState(true);
                try {
                    // Load model (hanya TinyFaceDetector yg ringan)
                    await faceapi.nets.tinyFaceDetector.loadFromUri(assetPath);
                    toggleLoadingState(false);
                    initCamera(true);
                } catch (err) {
                    console.error("AI Load Error:", err);
                    Swal.fire("Mode AI Gagal", "Sinyal lambat meload AI, beralih ke mode manual.", "warning");
                    initCamera(false); 
                }
            } else {
                initCamera(false);
            }
        });

        function toggleLoadingState(isLoading) {
            startBtn.classList.toggle('d-none', isLoading);
            modelLoadingText.classList.toggle('d-none', !isLoading);
        }

        // --- 2. CAMERA HANDLING ---
        function initCamera(enableAI) {
            // Gunakan resolusi standar VGA agar ringan di HP Kentang
            const constraints = {
                video: { facingMode: "user", width: { ideal: 640 }, height: { ideal: 480 } },
                audio: false
            };

            navigator.mediaDevices.getUserMedia(constraints)
            .then(stream => {
                streamRef = stream;
                videoFeed.srcObject = stream;
                
                videoFeed.onloadeddata = () => {
                    videoFeed.play();
                    startScreen.classList.add('d-none');
                    videoFeed.classList.add('ready');

                    if (enableAI) {
                        document.getElementById('ai-scan-line')?.classList.remove('d-none');
                        startOptimizedDetection();
                    } else {
                        setReadyToCapture(true, "Mode Manual");
                    }
                };
            })
            .catch(err => {
                Swal.fire("Kamera Error", "Gagal membuka kamera: " + err.message, "error");
                startBtn.disabled = false;
            });
        }

        // --- 3. OPTIMIZED AI DETECTION (Anti-Lag) ---
        function startOptimizedDetection() {
            const options = new faceapi.TinyFaceDetectorOptions({ 
                inputSize: CONFIG.AI_INPUT_SIZE, // Ukuran kecil = Lebih Cepat 
                scoreThreshold: CONFIG.AI_SCORE_THRESHOLD 
            });

            // GANTI requestAnimationFrame dengan setInterval
            // Ini mencegah HP panas dan Lag karena tidak memaksa 60fps processing
            aiInterval = setInterval(async () => {
                if (videoFeed.paused || videoFeed.ended || isProcessingAI) return;

                isProcessingAI = true;
                const startT = performance.now();

                try {
                    // Detect Single Face (Lebih ringan daripada detectAllFaces)
                    const result = await faceapi.detectSingleFace(videoFeed, options);
                    
                    if (result) {
                        if (!isFaceValid) { isFaceValid = true; onFaceValid(); }
                    } else {
                        if (isFaceValid) { isFaceValid = false; onFaceInvalid(); }
                    }

                    // Performance Check: Jika deteksi butuh waktu > 500ms, matikan AI
                    const duration = performance.now() - startT;
                    if (duration > 500) {
                        console.warn("Device too slow for AI, switching to manual.");
                        stopAI();
                        setReadyToCapture(true, "Mode Ringan (Auto)");
                    }

                } catch (err) {
                    console.log("Skip frame");
                }
                isProcessingAI = false;

            }, CONFIG.AI_INTERVAL_MS);
        }

        function stopAI() {
            if (aiInterval) clearInterval(aiInterval);
            document.getElementById('ai-scan-line')?.classList.add('d-none');
        }

        function onFaceValid() {
            focusBox.classList.add('active');
            setReadyToCapture(true, "Wajah Terdeteksi");
        }

        function onFaceInvalid() {
            focusBox.classList.remove('active');
            setReadyToCapture(false, "Posisikan Wajah");
        }

        function setReadyToCapture(ready, msg) {
            captureBtn.disabled = !ready;
            if(ready) {
                captureBtn.classList.add('ready');
                faceStatusBadge.innerHTML = `<i class="mdi mdi-check-circle"></i> ${msg}`;
                faceStatusBadge.className = "badge-glass text-success border-success";
            } else {
                captureBtn.classList.remove('ready');
                faceStatusBadge.innerHTML = `<i class="mdi mdi-alert"></i> ${msg}`;
                faceStatusBadge.className = "badge-glass text-warning border-warning";
            }
        }

        // --- 4. CAPTURE & COMPRESS ---
        captureBtn.addEventListener('click', () => {
            // Ambil lokasi lagi sesaat sebelum foto (Refresh GPS)
            getLocation();

            // Animasi tombol
            captureBtn.style.transform = "scale(0.9)";
            setTimeout(() => captureBtn.style.transform = "scale(1.05)", 100);

            // Gambar ke canvas
            const w = videoFeed.videoWidth;
            const h = videoFeed.videoHeight;
            canvas.width = w;
            canvas.height = h;
            const ctx = canvas.getContext('2d');
            ctx.scale(-1, 1); 
            ctx.drawImage(videoFeed, -w, 0, w, h);
            
            // Konversi ke Blob (Compress JPG quality 0.7)
            canvas.toBlob(blob => {
                // Buat File object untuk input hidden
                const file = new File([blob], "capture.jpg", { type: "image/jpeg" });
                const dt = new DataTransfer();
                dt.items.add(file);
                photoInputHidden.files = dt.files;
                
                // Tampilkan Preview
                previewImage.src = URL.createObjectURL(blob);
                
                // Stop Kamera & AI
                stopAI();
                if (streamRef) streamRef.getTracks().forEach(track => track.stop());
                
                // Tampilkan Result Screen
                resultScreen.classList.remove('d-none');
                checkGlobalValidity();

            }, 'image/jpeg', CONFIG.COMPRESSION_QUALITY);
        });

        retakeBtn.addEventListener('click', () => {
            window.location.reload(); // Refresh paling aman untuk reset state kamera
        });

        // --- 5. ROBUST LOCATION (GPS) ---
        function getLocation() {
            if (!navigator.geolocation) {
                coordDisplay.innerText = "GPS Tidak Support";
                return;
            }
            coordDisplay.innerText = "Mencari Lokasi...";
            accBadge.innerText = "Mencari...";
            
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const { latitude, longitude, accuracy } = pos.coords;
                    latInput.value = latitude;
                    lngInput.value = longitude;
                    accInput.value = accuracy;
                    
                    coordDisplay.innerText = `${latitude.toFixed(5)}, ${longitude.toFixed(5)}`;
                    accBadge.innerText = `Akurasi: ${Math.round(accuracy)}m`;
                    accBadge.className = accuracy < 100 ? 'badge bg-light text-success' : 'badge bg-light text-warning';
                    checkGlobalValidity();
                },
                (err) => {
                    // Fallback jika GPS High Accuracy gagal, coba Low Accuracy
                    console.warn("GPS High Accuracy Failed, trying low.");
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            latInput.value = pos.coords.latitude;
                            lngInput.value = pos.coords.longitude;
                            accInput.value = pos.coords.accuracy;
                            coordDisplay.innerText = "Lokasi (Low Acc) OK";
                            checkGlobalValidity();
                        },
                        (finalErr) => {
                            coordDisplay.innerText = "Gagal Lokasi";
                            accBadge.innerText = "Error";
                            accBadge.className = 'badge bg-danger text-white';
                            Swal.fire("GPS Error", "Aktifkan GPS anda dan Refresh halaman.", "error");
                        },
                        { enableHighAccuracy: false, timeout: 20000 }
                    );
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        }

        function checkGlobalValidity() {
            const hasPhoto = photoInputHidden.files.length > 0;
            const hasLoc = latInput.value !== '';
            
            if (hasPhoto && hasLoc) {
                slideTrack.classList.remove('disabled');
                document.querySelector('.slide-bg-text').innerText = "GESER UNTUK ABSEN >>";
                document.querySelector('.slide-bg-text').classList.add('text-active');
            }
        }

        // --- 6. SLIDE & AJAX SUBMIT (NETWORK RESILIENCE) ---
        let isDragging = false, startX, maxSlide;
        const updateSliderWidth = () => { maxSlide = slideTrack.offsetWidth - slideThumb.offsetWidth - 8; };
        setTimeout(updateSliderWidth, 500);

        // Slide logic (Touch/Mouse)
        const handleStart = (e) => {
            if (slideTrack.classList.contains('disabled')) return;
            isDragging = true;
            startX = (e.type.includes('touch')) ? e.touches[0].clientX : e.clientX;
            slideThumb.style.transition = 'none';
        };

        const handleMove = (e) => {
            if (!isDragging) return;
            const clientX = (e.type.includes('touch')) ? e.touches[0].clientX : e.clientX;
            let move = clientX - startX;
            if (move < 0) move = 0; if (move > maxSlide) move = maxSlide;
            slideThumb.style.transform = `translateX(${move}px)`;
        };

        const handleEnd = () => {
            if (!isDragging) return;
            isDragging = false;
            
            // Jika geser > 90%
            if (parseFloat(slideThumb.style.transform.replace('translateX(', '')) >= maxSlide * 0.9) {
                slideThumb.style.transform = `translateX(${maxSlide}px)`;
                slideTrack.classList.add('submitted');
                
                // Submit Form via AJAX
                submitAttendanceWithRetry();
            } else {
                slideThumb.style.transition = 'transform 0.3s ease';
                slideThumb.style.transform = 'translateX(0px)';
            }
        };

        // Event Listeners Slider
        slideThumb.addEventListener('mousedown', handleStart);
        slideThumb.addEventListener('touchstart', handleStart, {passive: false});
        window.addEventListener('mousemove', handleMove);
        window.addEventListener('touchmove', handleMove, {passive: false});
        window.addEventListener('mouseup', handleEnd);
        window.addEventListener('touchend', handleEnd);

        // === CORE: FUNGSI UPLOAD TAHAN BANTING ===
        async function submitAttendanceWithRetry(retryCount = 0) {
            
            // 1. Tampilkan UI Loading
            if(retryCount === 0) {
                Swal.fire({
                    title: 'Mengirim Data...',
                    html: 'Jangan tutup halaman ini.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
            } else {
                Swal.update({
                    title: `Koneksi Buruk (Percobaan ${retryCount}/${CONFIG.MAX_RETRIES})`,
                    html: 'Sedang mencoba menyambung kembali...'
                });
            }

            // 2. Siapkan Data
            const formData = new FormData(form);

            // 3. Eksekusi Fetch
            try {
                const response = await fetch("{{ route('self.attend.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (!response.ok) throw new Error(`Server Error: ${response.status}`);

                const result = await response.json();

                if (result.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: result.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = result.redirect_url;
                    });
                } else {
                    throw new Error(result.message);
                }

            } catch (error) {
                console.error("Upload Error:", error);

                // 4. Logic Retry (Jika gagal)
                if (retryCount < CONFIG.MAX_RETRIES) {
                    // Tunggu sebentar lalu coba lagi
                    setTimeout(() => {
                        submitAttendanceWithRetry(retryCount + 1);
                    }, CONFIG.RETRY_DELAY);
                } else {
                    // Jika sudah menyerah (Limit Retry Habis)
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Mengirim',
                        text: 'Koneksi internet Anda sangat tidak stabil. Cari sinyal yang lebih baik dan tekan "Coba Lagi".',
                        showCancelButton: true,
                        confirmButtonText: 'Coba Lagi',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitAttendanceWithRetry(0); // Reset retry counter
                        } else {
                            // Reset slider jika batal
                            slideThumb.style.transform = 'translateX(0px)';
                            slideTrack.classList.remove('submitted');
                        }
                    });
                }
            }
        }
    });
</script>
@endpush