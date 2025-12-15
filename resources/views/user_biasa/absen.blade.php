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
                                {{-- DEBUG SCORE (Opsional: Bisa dihapus nanti) --}}
                                <div id="debug-score" class="text-white small mt-1" style="opacity: 0.7;"></div>
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
                                    <i class="mdi mdi-check-circle me-1"></i> Foto Bagus
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
                    <div class="location-widget mb-4">
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
                    </div>

                    {{-- NOTES INPUT --}}
                    <div class="form-group mb-4">
                        <label class="form-label small text-muted fw-bold text-uppercase">Catatan (Opsional)</label>
                        <textarea name="notes" class="form-control form-control-modern" rows="2" placeholder="Sedang di lokasi klien..."></textarea>
                    </div>

                    {{-- HIDDEN INPUTS --}}
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
        transform: scaleX(-1);
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
    .btn-retake { background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.3); color: white; border-radius: 30px; padding: 6px 16px; font-size: 0.85rem; backdrop-filter: blur(4px); transition: 0.2s; }
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
        animation: shine 3s linear infinite;
    }
    @keyframes shine { to { background-position: 200% center; } }
    
    .slide-thumb {
        width: 48px; height: 48px; background: #fff; border-radius: 50%;
        margin-left: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex; align-items: center; justify-content: center;
        color: var(--primary-color); font-size: 1.2rem;
        position: relative; z-index: 2; transition: transform 0.1s;
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const useAI = {{ Auth::user()->use_face_recognition ? 'true' : 'false' }};
        const assetPath = "{{ asset('public/models') }}"; // Path ke folder public/models

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
        
        // UI Indicators
        const faceStatusBadge = document.getElementById('face-status-badge');
        const focusBox = document.querySelector('.focus-box');
        const modelLoadingText = document.getElementById('model-loading-text');
        const cameraInstruction = document.getElementById('camera-instruction');
        const aiScanLine = document.getElementById('ai-scan-line');
        const slideTrack = document.getElementById('slide-track');
        const slideThumb = document.getElementById('slide-thumb');
        const slideText = document.querySelector('.slide-bg-text');
        const form = document.getElementById('attendance-form');
        const debugScore = document.getElementById('debug-score');

        // State
        let streamRef = null;
        let detectionInterval = null;
        let isFaceValid = false;
        let modelsLoaded = false;

        // --- 1. INITIALIZE ---
        startBtn.addEventListener('click', async () => {
            if (useAI) {
                if (!modelsLoaded) {
                    toggleLoadingState(true);
                    try {
                        console.log("Memuat model dari:", assetPath);
                        // === BALIK KE TINY FACE DETECTOR (RINGAN & CEPAT) ===
                        await faceapi.nets.tinyFaceDetector.loadFromUri(assetPath);
                        
                        modelsLoaded = true;
                        toggleLoadingState(false);
                        initCamera();
                    } catch (err) {
                        console.error("AI Error:", err);
                        alert("Gagal memuat AI. Pastikan file model tiny_face_detector ada.");
                        toggleLoadingState(false);
                        initCamera(); // Fallback
                    }
                } else {
                    initCamera();
                }
            } else {
                initCamera();
            }
        });

        function toggleLoadingState(isLoading) {
            startBtn.disabled = isLoading;
            startBtn.classList.toggle('d-none', isLoading);
            modelLoadingText.classList.toggle('d-none', !isLoading);
        }

        // --- 2. CAMERA HANDLING ---
        function initCamera() {
            navigator.mediaDevices.getUserMedia({
                video: { facingMode: "user", width: { ideal: 1280 }, height: { ideal: 720 } },
                audio: false
            })
            .then(stream => {
                streamRef = stream;
                videoFeed.srcObject = stream;
                
                videoFeed.onloadedmetadata = () => {
                    videoFeed.play();
                    setTimeout(() => {
                        videoFeed.classList.add('ready');
                        startScreen.classList.add('d-none');
                        if (useAI && aiScanLine) aiScanLine.classList.remove('d-none');
                    }, 300);

                    if (useAI) {
                        startFaceDetection();
                    } else {
                        setReadyToCapture(true, "Siap Foto");
                    }
                };
            })
            .catch(err => {
                alert("Tidak dapat mengakses kamera. Pastikan izin diberikan.");
                console.error(err);
            });
        }

        // --- 3. AI DETECTION (ANTI LAG & ANTI MASKER) ---
        function startFaceDetection() {
            if (detectionInterval) clearInterval(detectionInterval);

            // === SETTING PENTING AGAR TIDAK TEMBUS MASKER ===
            // inputSize: 416 (Standard YOLO) atau 512 (Lebih Akurat tapi agak berat dikit).
            // scoreThreshold: 0.80 (SANGAT KETAT). 
            // Wajah masker biasanya cuma dapet score 0.5 - 0.7.
            // Wajah asli biasanya dapet score 0.85 - 0.99.
            const options = new faceapi.TinyFaceDetectorOptions({ 
                inputSize: 416, 
                scoreThreshold: 0.80 
            });

            detectionInterval = setInterval(async () => {
                if (!streamRef || videoFeed.paused || videoFeed.ended) return;

                try {
                    const detections = await faceapi.detectAllFaces(videoFeed, options);
                    
                    if (detections.length > 0) {
                        // Ambil deteksi dengan score tertinggi
                        const bestDetection = detections[0];
                        const score = bestDetection.score;
                        
                        // Tampilkan score untuk debugging (hapus jika sudah fix)
                        // debugScore.innerText = "Confidence: " + (score * 100).toFixed(0) + "%";

                        // Karena threshold sudah di-set 0.80 di options,
                        // Maka apapun yang masuk ke sini PASTI di atas 0.80 (Wajah Valid)
                        if (!isFaceValid) {
                            isFaceValid = true;
                            focusBox.classList.add('active');
                            setReadyToCapture(true, "Wajah Terdeteksi");
                        }
                    } else {
                        // Tidak ada wajah, atau wajah ada TAPI score di bawah 0.80 (Misal Masker)
                        if (isFaceValid) {
                            handleInvalidFace();
                        }
                        // debugScore.innerText = "Mencari wajah...";
                    }
                } catch (e) { console.log(e); }
            }, 200); // 200ms = Cepat & Responsif (Tidak Lag)
        }

        function handleInvalidFace() {
            isFaceValid = false;
            focusBox.classList.remove('active');
            setReadyToCapture(false, "Buka Masker & Lihat Kamera");
        }

        function setReadyToCapture(ready, message) {
            if (ready) {
                captureBtn.disabled = false;
                captureBtn.classList.add('ready');
                faceStatusBadge.innerHTML = `<i class="mdi mdi-face-recognition"></i> ${message}`;
                faceStatusBadge.style.background = "rgba(6, 214, 160, 0.4)";
                faceStatusBadge.style.borderColor = "#00ff88";
                cameraInstruction.innerText = "Tekan tombol shutter di bawah";
            } else {
                captureBtn.disabled = true;
                captureBtn.classList.remove('ready');
                faceStatusBadge.innerHTML = `<i class="mdi mdi-account-alert"></i> ${message}`;
                faceStatusBadge.style.background = "rgba(255, 50, 50, 0.2)";
                faceStatusBadge.style.borderColor = "rgba(255,50,50,0.3)";
                cameraInstruction.innerText = "Posisikan wajah tanpa masker...";
            }
        }

        // --- 4. CAPTURE LOGIC ---
        captureBtn.addEventListener('click', async () => {
            if (useAI && !isFaceValid) return;

            // Visual Feedback
            captureBtn.style.transform = "scale(0.9)";
            setTimeout(() => captureBtn.style.transform = "scale(1.05)", 100);

            const width = videoFeed.videoWidth;
            const height = videoFeed.videoHeight;
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            
            ctx.save();
            ctx.scale(-1, 1);
            ctx.drawImage(videoFeed, -width, 0, width, height);
            ctx.restore();

            canvas.toBlob(blob => {
                const file = new File([blob], "attendance.jpg", { type: "image/jpeg" });
                const dt = new DataTransfer();
                dt.items.add(file);
                photoInputHidden.files = dt.files;
                
                previewImage.src = URL.createObjectURL(blob);
                
                stopCamera();
                resultScreen.classList.remove('d-none');
                checkGlobalValidity();
            }, 'image/jpeg', 0.85);
        });

        function stopCamera() {
            if (detectionInterval) clearInterval(detectionInterval);
            if (streamRef) {
                streamRef.getTracks().forEach(track => track.stop());
                streamRef = null;
            }
        }

        retakeBtn.addEventListener('click', () => {
            resultScreen.classList.add('d-none');
            photoInputHidden.value = '';
            initCamera(); 
            checkGlobalValidity();
        });

        // --- 5. GPS & SLIDER ---
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const { latitude, longitude, accuracy } = pos.coords;
                    document.getElementById('latitude').value = latitude;
                    document.getElementById('longitude').value = longitude;
                    document.getElementById('accuracy').value = accuracy;
                    
                    document.getElementById('coordinates-display').innerText = `${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;
                    document.getElementById('gps-accuracy-badge').innerText = `Akurasi ±${Math.round(accuracy)}m`;
                    document.getElementById('gps-accuracy-badge').className = 'badge bg-soft-success text-success border';
                    checkGlobalValidity();
                },
                (err) => {
                    document.getElementById('coordinates-display').innerText = "Gagal mendapatkan lokasi";
                    document.getElementById('gps-accuracy-badge').className = 'badge bg-danger text-white';
                },
                { enableHighAccuracy: true }
            );
        }

        function checkGlobalValidity() {
            const hasPhoto = photoInputHidden.files.length > 0;
            const hasLoc = document.getElementById('latitude').value !== '';
            
            if (hasPhoto && hasLoc) {
                slideTrack.classList.remove('disabled');
                slideText.innerText = "GESER KE KANAN >>";
                slideText.style.background = "linear-gradient(90deg, #06d6a0 0%, #fff 50%, #06d6a0 100%)";
                slideText.style.webkitBackgroundClip = "text";
            } else {
                slideTrack.classList.add('disabled');
                slideText.innerText = "LENGKAPI FOTO & LOKASI";
                slideText.style.background = "linear-gradient(90deg, #adb5bd 0%, #fff 50%, #adb5bd 100%)";
                slideText.style.webkitBackgroundClip = "text";
            }
        }

        // --- 6. SLIDER LOGIC ---
        let isDragging = false, startX, currentX, maxSlide;
        const updateSliderWidth = () => { maxSlide = slideTrack.offsetWidth - slideThumb.offsetWidth - 8; };
        window.addEventListener('resize', updateSliderWidth);
        updateSliderWidth();

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
            if (move < 0) move = 0;
            if (move > maxSlide) move = maxSlide;
            currentX = move;
            slideThumb.style.transform = `translateX(${move}px)`;
            slideText.style.opacity = 1 - (move / maxSlide);
        };

        const handleEnd = () => {
            if (!isDragging) return;
            isDragging = false;
            
            if (currentX >= maxSlide * 0.9) {
                slideThumb.style.transform = `translateX(${maxSlide}px)`;
                slideTrack.classList.add('submitted');
                slideThumb.innerHTML = '<i class="mdi mdi-check"></i>';
                slideText.innerText = "MENGIRIM...";
                slideText.style.opacity = 1;
                form.submit();
            } else {
                slideThumb.style.transition = 'transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                slideThumb.style.transform = 'translateX(0px)';
                slideText.style.opacity = 1;
            }
        };

        slideThumb.addEventListener('mousedown', handleStart);
        slideThumb.addEventListener('touchstart', handleStart);
        window.addEventListener('mousemove', handleMove);
        window.addEventListener('touchmove', handleMove);
        window.addEventListener('mouseup', handleEnd);
        window.addEventListener('touchend', handleEnd);
    });
</script>
@endpush