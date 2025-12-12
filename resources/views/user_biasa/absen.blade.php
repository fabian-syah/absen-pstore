@extends('layout.master')

@section('title')
    Absen Mandiri ({{ ucfirst($mode) }})
@endsection

@section('heading')
    Absen Mandiri ({{ ucfirst($mode) }})
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 grid-margin stretch-card">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0 fw-bold">Absen {{ ucfirst($mode) }}</h4>
                        <div class="badge badge-success rounded-pill px-3">
                            <i class="mdi mdi-face-recognition me-1"></i>AI Detect
                        </div>
                    </div>

                    {{-- ALERT STATUS (LOGIKA LAMA TETAP ADA) --}}
                    @if ($mode == 'pulang' && isset($attendance))
                        <div class="alert alert-light border shadow-sm mb-3 small">
                            <i class="mdi mdi-clock-outline me-1"></i> Masuk: <strong>{{ \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') }}</strong>
                        </div>
                    @endif

                    <form class="forms-sample" action="{{ route('self.attend.store') }}" method="POST" enctype="multipart/form-data" id="attendance-form">
                        @csrf
                        @if (isset($attendance) && $attendance)
                            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                        @endif

                        {{-- INPUT FILE HIDDEN (PENAMPUNG DATA GAMBAR) --}}
                        <input type="file" name="photo" id="photo-input-hidden" class="d-none" accept="image/jpeg">

                        {{-- === AREA KAMERA UTAMA (STYLE INSTAGRAM/NATIVE) === --}}
                        <div class="camera-wrapper position-relative overflow-hidden rounded-4 mb-3">
                            
                            {{-- 1. VIDEO FEED (MURNI TANPA KONTROL) --}}
                            <video id="video-feed" autoplay muted playsinline></video>

                            {{-- 2. OVERLAY UI (FOCUS BOX & STATUS) --}}
                            <div class="camera-overlay d-flex flex-column justify-content-between p-3">
                                {{-- Top Status --}}
                                <div class="text-center">
                                    <span id="face-status-badge" class="badge bg-dark bg-opacity-50 rounded-pill backdrop-blur">
                                        <i class="mdi mdi-loading mdi-spin me-1"></i> Mencari wajah...
                                    </span>
                                </div>

                                {{-- Focus Frame (Kotak Wajah) --}}
                                <div class="focus-frame">
                                    <div class="corner top-left"></div>
                                    <div class="corner top-right"></div>
                                    <div class="corner bottom-left"></div>
                                    <div class="corner bottom-right"></div>
                                </div>

                                {{-- Bottom (Hidden by default, shown when camera active) --}}
                                <div class="text-center text-white small text-shadow" id="camera-instruction">
                                    Tahan posisi wajah
                                </div>
                            </div>

                            {{-- 3. LAYAR HITAM (START SCREEN) --}}
                            <div id="start-screen" class="position-absolute top-0 start-0 w-100 h-100 bg-dark d-flex flex-column justify-content-center align-items-center z-3">
                                <div class="icon-circle mb-3">
                                    <i class="mdi mdi-camera text-white fs-1"></i>
                                </div>
                                <button type="button" id="start-camera-btn" class="btn btn-light rounded-pill fw-bold px-4">
                                    Buka Kamera
                                </button>
                                <div id="model-loading-text" class="text-white-50 mt-3 small d-none">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Menyiapkan AI...
                                </div>
                            </div>

                            {{-- 4. HASIL FOTO (PREVIEW) --}}
                            <div id="result-screen" class="position-absolute top-0 start-0 w-100 h-100 bg-black d-none z-3">
                                <img id="preview-image" src="" alt="Result" class="w-100 h-100 object-fit-cover">
                                <div class="position-absolute bottom-0 w-100 p-4 d-flex gap-2 bg-gradient-black">
                                    <button type="button" id="retake-btn" class="btn btn-outline-light flex-fill rounded-pill">
                                        Ulangi
                                    </button>
                                    <div class="flex-fill text-center text-white align-self-center fw-bold">
                                        <i class="mdi mdi-check-circle text-success me-1"></i> Oke
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- === END AREA KAMERA === --}}


                        {{-- TOMBOL SHUTTER (BUTTON KAMERA ASLI) --}}
                        <div class="d-flex justify-content-center mb-4 position-relative" style="height: 80px;">
                            {{-- Tombol Capture (Hanya aktif jika wajah ok) --}}
                            <button type="button" id="capture-btn" class="shutter-btn" disabled></button>
                            
                            {{-- Loading Spinner saat proses foto --}}
                            <div id="capture-loading" class="spinner-border text-primary position-absolute top-50 start-50 translate-middle d-none" role="status"></div>
                        </div>

                        {{-- INFO LOKASI --}}
                        <div class="location-card bg-light rounded-3 p-3 mb-4 d-flex align-items-center">
                            <div class="icon-box bg-white rounded-circle p-2 me-3 shadow-sm">
                                <i class="mdi mdi-map-marker text-primary"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <small class="text-muted d-block">Lokasi Anda</small>
                                <h6 class="mb-0 text-truncate fw-bold" id="coordinates-display">Mencari GPS...</h6>
                            </div>
                            <div id="gps-accuracy-badge" class="badge bg-secondary rounded-pill">...</div>
                        </div>

                        {{-- FORM INPUTS --}}
                        <div class="form-group mb-4">
                            <textarea name="notes" class="form-control bg-light border-0 rounded-3" rows="2" placeholder="Tulis catatan (Opsional)..."></textarea>
                        </div>
                        
                        <input type="hidden" id="latitude" name="latitude">
                        <input type="hidden" id="longitude" name="longitude">
                        <input type="hidden" id="accuracy" name="accuracy">

                        {{-- SLIDER SUBMIT --}}
                        <div class="slide-submit-container">
                            <div class="slide-track disabled" id="slide-track">
                                <div class="slide-text" id="slide-text">Geser untuk Absen</div>
                                <div class="slide-thumb" id="slide-thumb"><i class="mdi mdi-chevron-right"></i></div>
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <a href="{{ route('dashboard') }}" class="text-muted small text-decoration-none">Batal</a>
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
        /* === CAMERA UI STYLES === */
        .camera-wrapper {
            width: 100%;
            height: 0;
            padding-bottom: 125%; /* Aspect Ratio 4:5 (Portrait) */
            background: #000;
            position: relative;
        }

        #video-feed {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover; /* INI KUNCINYA: Biar full screen ga gepeng */
            transform: scaleX(-1); /* Mirror effect */
        }

        /* Hilangkan kontrol video bawaan browser */
        video::-webkit-media-controls { display: none !important; }
        video::-webkit-media-controls-play-button { display: none !important; }
        video::-webkit-media-controls-start-playback-button { display: none !important; }

        .camera-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: 2;
            pointer-events: none; /* Biar ga ngehalangin klik */
        }

        .backdrop-blur {
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }

        /* Focus Frame Animation */
        .focus-frame {
            width: 200px;
            height: 250px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 20px;
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            transition: all 0.3s ease;
            box-shadow: 0 0 0 9999px rgba(0,0,0,0.3); /* Gelapin area luar fokus */
        }
        
        .focus-frame.active {
            border-color: #00ff88; /* Hijau saat detect */
            box-shadow: 0 0 0 9999px rgba(0,0,0,0.1), 0 0 20px rgba(0,255,136,0.3);
        }

        /* Shutter Button (Tombol Bulet Besar) */
        .shutter-btn {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background-color: transparent;
            border: 4px solid #ccc;
            position: relative;
            cursor: pointer;
            transition: all 0.2s;
        }

        .shutter-btn::after {
            content: '';
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 54px;
            height: 54px;
            background-color: #ccc;
            border-radius: 50%;
            transition: all 0.2s;
        }

        .shutter-btn:not(:disabled):hover { border-color: #fff; }
        .shutter-btn:not(:disabled):hover::after { background-color: #fff; }
        
        /* State Aktif (Bisa Foto) */
        .shutter-btn.ready { border-color: #fff; }
        .shutter-btn.ready::after { background-color: #fff; }

        /* State ditekan */
        .shutter-btn:active::after { width: 45px; height: 45px; }

        .shutter-btn:disabled { opacity: 0.3; cursor: not-allowed; }

        .object-fit-cover { object-fit: cover; }
        .bg-gradient-black { background: linear-gradient(to top, rgba(0,0,0,0.8), transparent); }

        /* SLIDER STYLES (Cleaned up) */
        .slide-submit-container { position: relative; width: 100%; height: 55px; background: #e9ecef; border-radius: 50px; overflow: hidden; }
        .slide-track { height: 100%; display: flex; align-items: center; cursor: pointer; }
        .slide-track.submitted { background: #198754; transition: 0.3s; }
        .slide-thumb { width: 45px; height: 45px; background: #fff; border-radius: 50%; margin-left: 5px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2); position: relative; z-index: 2; transition: left 0.1s; }
        .slide-text { position: absolute; width: 100%; text-align: center; font-weight: 600; color: #6c757d; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; user-select: none; }
        .slide-track.submitted .slide-text { color: #fff; }
        .slide-track.disabled { pointer-events: none; opacity: 0.6; }
    </style>
@endpush

@push('scripts')
    {{-- LOAD FACE API --}}
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js/dist/face-api.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- DOM ELEMENTS ---
            const videoFeed = document.getElementById('video-feed');
            const startScreen = document.getElementById('start-screen');
            const resultScreen = document.getElementById('result-screen');
            const startBtn = document.getElementById('start-camera-btn');
            const captureBtn = document.getElementById('capture-btn');
            const retakeBtn = document.getElementById('retake-btn');
            const previewImage = document.getElementById('preview-image');
            const photoInputHidden = document.getElementById('photo-input-hidden');
            const canvas = document.getElementById('capture-canvas');
            
            // UI Status Elements
            const faceStatusBadge = document.getElementById('face-status-badge');
            const focusFrame = document.querySelector('.focus-frame');
            const modelLoadingText = document.getElementById('model-loading-text');
            const cameraInstruction = document.getElementById('camera-instruction');

            // Location & Slider
            const locationCard = document.querySelector('.location-card');
            const form = document.getElementById('attendance-form');
            const slideTrack = document.getElementById('slide-track');
            const slideThumb = document.getElementById('slide-thumb');
            const slideText = document.getElementById('slide-text');

            let streamRef = null;
            let detectionInterval = null;
            let isFaceValid = false;
            let modelsLoaded = false;

            // ==========================================
            // 1. INIT & LOAD MODELS
            // ==========================================
            startBtn.addEventListener('click', async () => {
                if (!modelsLoaded) {
                    modelLoadingText.classList.remove('d-none');
                    startBtn.disabled = true;
                    try {
                        await faceapi.nets.tinyFaceDetector.loadFromUri('public/models');
                        modelsLoaded = true;
                        modelLoadingText.classList.add('d-none');
                        startCamera();
                    } catch (err) {
                        console.error(err);
                        alert("Gagal memuat AI. Cek file model.");
                        startBtn.disabled = false;
                    }
                } else {
                    startCamera();
                }
            });

            // ==========================================
            // 2. CAMERA & DETECTION
            // ==========================================
            function startCamera() {
                // Request Camera (PENTING: attributes video di HTML sudah autoplay muted playsinline)
                navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: "user",
                        width: { ideal: 720 }, // Resolusi ideal
                        height: { ideal: 1280 }
                    }, 
                    audio: false 
                })
                .then(stream => {
                    streamRef = stream;
                    videoFeed.srcObject = stream;
                    
                    // Update UI
                    startScreen.classList.add('d-none');
                    captureBtn.disabled = true; // Disable tombol sampai wajah ketemu
                    
                    // Mulai Deteksi saat video play
                    videoFeed.onloadedmetadata = () => {
                        videoFeed.play();
                        startFaceDetection();
                    };
                })
                .catch(err => {
                    console.error(err);
                    alert("Gagal akses kamera. Pastikan izin browser diberikan.");
                });
            }

            function startFaceDetection() {
                // Interval 200ms (Ringan)
                detectionInterval = setInterval(async () => {
                    if (videoFeed.paused || videoFeed.ended) return;

                    const detections = await faceapi.detectAllFaces(videoFeed, new faceapi.TinyFaceDetectorOptions());

                    if (detections.length > 0 && detections[0].score > 0.5) {
                        // === WAJAH TERDETEKSI ===
                        if (!isFaceValid) { // Supaya ga render berulang kali
                            isFaceValid = true;
                            
                            // UI Feedback: Hijau
                            focusFrame.classList.add('active');
                            faceStatusBadge.className = 'badge bg-success rounded-pill shadow';
                            faceStatusBadge.innerHTML = '<i class="mdi mdi-face-recognition"></i> Wajah Oke';
                            cameraInstruction.innerText = "Siap mengambil foto";
                            
                            // Enable Shutter
                            captureBtn.disabled = false;
                            captureBtn.classList.add('ready');
                        }
                    } else {
                        // === WAJAH HILANG ===
                        if (isFaceValid) {
                            isFaceValid = false;
                            
                            // UI Feedback: Default/Merah
                            focusFrame.classList.remove('active');
                            faceStatusBadge.className = 'badge bg-dark bg-opacity-50 rounded-pill backdrop-blur';
                            faceStatusBadge.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Mencari...';
                            cameraInstruction.innerText = "Posisikan wajah di dalam kotak";

                            // Disable Shutter
                            captureBtn.disabled = true;
                            captureBtn.classList.remove('ready');
                        }
                    }
                }, 200);
            }

            // ==========================================
            // 3. CAPTURE PHOTO
            // ==========================================
            captureBtn.addEventListener('click', () => {
                if (!isFaceValid) return;

                // Visual Feedback Click
                captureBtn.style.transform = "scale(0.9)";
                setTimeout(() => captureBtn.style.transform = "scale(1)", 100);

                // Draw to Canvas
                const ctx = canvas.getContext('2d');
                canvas.width = videoFeed.videoWidth;
                canvas.height = videoFeed.videoHeight;
                
                // Mirror Effect (Sesuai preview)
                ctx.save();
                ctx.scale(-1, 1);
                ctx.drawImage(videoFeed, -canvas.width, 0, canvas.width, canvas.height);
                ctx.restore();

                // Convert to Blob
                canvas.toBlob(blob => {
                    const file = new File([blob], "attendance.jpg", { type: "image/jpeg" });
                    
                    // Set Input File Hidden
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    photoInputHidden.files = dt.files;

                    // Show Result Screen
                    const url = URL.createObjectURL(blob);
                    previewImage.src = url;
                    
                    stopCameraAndShowResult();
                    checkGlobalValidity(); // Cek apakah boleh slide
                }, 'image/jpeg', 0.85);
            });

            function stopCameraAndShowResult() {
                if (detectionInterval) clearInterval(detectionInterval);
                if (streamRef) streamRef.getTracks().forEach(t => t.stop());
                resultScreen.classList.remove('d-none');
            }

            retakeBtn.addEventListener('click', () => {
                resultScreen.classList.add('d-none');
                startScreen.classList.remove('d-none'); // Kembali ke tombol start
                photoInputHidden.value = ''; // Clear file
                isFaceValid = false;
                captureBtn.classList.remove('ready');
                captureBtn.disabled = true;
                
                // Lock slider lagi
                checkGlobalValidity();
            });

            // ==========================================
            // 4. GPS & SLIDER LOGIC
            // ==========================================
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        document.getElementById('latitude').value = pos.coords.latitude;
                        document.getElementById('longitude').value = pos.coords.longitude;
                        document.getElementById('accuracy').value = pos.coords.accuracy;
                        
                        document.getElementById('coordinates-display').innerText = `${pos.coords.latitude.toFixed(4)}, ${pos.coords.longitude.toFixed(4)}`;
                        document.getElementById('gps-accuracy-badge').innerText = `±${Math.round(pos.coords.accuracy)}m`;
                        document.getElementById('gps-accuracy-badge').className = 'badge bg-success rounded-pill';
                        
                        checkGlobalValidity();
                    },
                    (err) => { 
                        document.getElementById('coordinates-display').innerText = "Gagal Deteksi Lokasi";
                        document.getElementById('coordinates-display').classList.add('text-danger');
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
                    slideText.classList.add('text-success');
                } else {
                    slideTrack.classList.add('disabled');
                    slideText.innerText = "Lengkapi Foto & Lokasi";
                    slideText.classList.remove('text-success');
                }
            }

            // SLIDER DRAG (Standard Logic)
            let isDragging = false, startX = 0, currentX = 0, trackWidth = 0, thumbWidth = 0, maxSlide = 0, isSubmitted = false;
            
            function initSlider() { trackWidth = slideTrack.offsetWidth; thumbWidth = slideThumb.offsetWidth; maxSlide = trackWidth - thumbWidth - 5; }
            window.addEventListener('resize', initSlider); initSlider();

            const onDragStart = (e) => {
                if (slideTrack.classList.contains('disabled') || isSubmitted) return;
                isDragging = true;
                startX = (e.type === 'touchstart') ? e.touches[0].clientX : e.clientX;
                slideThumb.style.transition = 'none';
            };

            const onDragMove = (e) => {
                if (!isDragging) return;
                const clientX = (e.type === 'touchmove') ? e.touches[0].clientX : e.clientX;
                let moveX = clientX - startX;
                if (moveX < 0) moveX = 0; if (moveX > maxSlide) moveX = maxSlide;
                currentX = moveX;
                slideThumb.style.left = moveX + 'px';
                slideText.style.opacity = 1 - (moveX/maxSlide);
            };

            const onDragEnd = () => {
                if (!isDragging) return;
                isDragging = false;
                if (currentX >= maxSlide * 0.9) {
                    isSubmitted = true;
                    slideThumb.style.left = maxSlide + 'px';
                    slideTrack.classList.add('submitted');
                    slideThumb.innerHTML = '<i class="mdi mdi-check"></i>';
                    slideText.innerText = "MENGIRIM...";
                    slideText.style.opacity = 1;
                    form.submit();
                } else {
                    slideThumb.style.transition = 'left 0.3s';
                    slideThumb.style.left = '0px';
                    slideText.style.opacity = 1;
                }
            };

            slideThumb.addEventListener('mousedown', onDragStart);
            slideThumb.addEventListener('touchstart', onDragStart);
            window.addEventListener('mousemove', onDragMove);
            window.addEventListener('touchmove', onDragMove);
            window.addEventListener('mouseup', onDragEnd);
            window.addEventListener('touchend', onDragEnd);
        });
    </script>
@endpush