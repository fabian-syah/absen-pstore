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
                <div class="card-body p-4">
                    
                    {{-- =============================================== --}}
                    {{-- HEADER MEWAH (GREETING & JAM)                   --}}
                    {{-- =============================================== --}}
                    <div class="mb-4 border-bottom pb-3">
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <h6 class="text-muted small mb-1 text-uppercase ls-1">Absensi {{ ucfirst($mode) }}</h6>
                                <h3 class="fw-bold mb-0 text-gradient">
                                    @php
                                        $hour = now()->hour;
                                        $greet = $hour < 11 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 19 ? 'Selamat Sore' : 'Selamat Malam'));
                                        $firstName = explode(' ', Auth::user()->name)[0];
                                    @endphp
                                    {{ $greet }}, {{ $firstName }} 👋
                                </h3>
                            </div>
                            <div class="text-end">
                                <div id="live-clock" class="fs-4 fw-black font-monospace text-dark">00:00:00</div>
                                <div class="small text-muted">{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- =============================================== --}}
                    {{-- STATUS BAR (AI MODE & GPS SIGNAL)               --}}
                    {{-- =============================================== --}}
                    <div class="d-flex justify-content-between align-items-center mb-3 bg-light rounded-pill p-2 px-3">
                        {{-- KIRI: Status AI --}}
                        @if(Auth::user()->use_face_recognition)
                            <div class="d-flex align-items-center text-success fw-bold small">
                                <i class="mdi mdi-face-recognition me-2 fs-5"></i> AI On
                            </div>
                        @else
                            <div class="d-flex align-items-center text-warning fw-bold small">
                                <i class="mdi mdi-camera-off me-2 fs-5"></i> Manual
                            </div>
                        @endif
                        
                        {{-- KANAN: Indikator Sinyal GPS Gaming --}}
                        <div class="d-flex align-items-center" title="Akurasi Lokasi">
                            <small class="text-muted me-2" id="gps-text-status">Mencari...</small>
                            <div class="signal-bars">
                                <div class="bar bar-1"></div>
                                <div class="bar bar-2"></div>
                                <div class="bar bar-3"></div>
                            </div>
                        </div>
                    </div>

                    {{-- ALERT STATUS (JIKA PULANG) --}}
                    @if ($mode == 'pulang' && isset($attendance))
                        <div class="alert alert-primary bg-primary bg-opacity-10 border-0 shadow-sm mb-3 small text-primary">
                            <i class="mdi mdi-clock-check-outline me-1"></i> Anda absen masuk jam: 
                            <strong>{{ \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') }}</strong>
                        </div>
                    @endif

                    <form class="forms-sample" action="{{ route('self.attend.store') }}" method="POST"
                        enctype="multipart/form-data" id="attendance-form">
                        @csrf
                        @if (isset($attendance) && $attendance)
                            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                        @endif

                        <input type="file" name="photo" id="photo-input-hidden" class="d-none" accept="image/jpeg">

                        {{-- === AREA KAMERA UTAMA === --}}
                        <div class="camera-wrapper position-relative overflow-hidden rounded-4 mb-3 shadow-sm">

                            {{-- 1. VIDEO FEED --}}
                            <video id="video-feed" autoplay muted playsinline width="100%" height="100%"></video>

                            {{-- 2. OVERLAY UI --}}
                            <div class="camera-overlay d-flex flex-column justify-content-between p-3">
                                <div class="text-center">
                                    <span id="face-status-badge" class="badge bg-dark bg-opacity-75 rounded-pill backdrop-blur border border-secondary py-2 px-3">
                                        <i class="mdi mdi-loading mdi-spin me-1"></i> Menunggu Kamera...
                                    </span>
                                </div>

                                {{-- Focus Frame --}}
                                @if(Auth::user()->use_face_recognition)
                                    <div class="focus-frame">
                                        <div class="corner top-left"></div>
                                        <div class="corner top-right"></div>
                                        <div class="corner bottom-left"></div>
                                        <div class="corner bottom-right"></div>
                                    </div>
                                    <div class="text-center text-white small text-shadow fw-bold" id="camera-instruction">
                                        Siapkan Wajah
                                    </div>
                                @else
                                    <div class="focus-frame border-white opacity-50"></div>
                                    <div class="text-center text-white small text-shadow fw-bold">
                                        Mode Manual
                                    </div>
                                @endif
                            </div>

                            {{-- 3. TOMBOL START SCREEN --}}
                            <div id="start-screen" class="position-absolute top-0 start-0 w-100 h-100 bg-dark d-flex flex-column justify-content-center align-items-center z-3">
                                <div class="icon-circle mb-3 bg-white bg-opacity-10 p-4 rounded-circle">
                                    <i class="mdi mdi-camera text-white fs-1"></i>
                                </div>
                                <button type="button" id="start-camera-btn" class="btn btn-light rounded-pill fw-bold px-5 py-2 shadow">
                                    Buka Kamera
                                </button>
                                <div id="model-loading-text" class="text-white-50 mt-3 small d-none">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Menyiapkan AI...
                                </div>
                            </div>

                            {{-- 4. HASIL FOTO (PREVIEW) --}}
                            <div id="result-screen" class="position-absolute top-0 start-0 w-100 h-100 bg-black d-none z-3">
                                <img id="preview-image" src="" alt="Result" class="w-100 h-100 object-fit-contain">
                                <div class="position-absolute bottom-0 w-100 p-4 d-flex gap-2 bg-gradient-black">
                                    <button type="button" id="retake-btn" class="btn btn-outline-light flex-fill rounded-pill">
                                        Ulangi
                                    </button>
                                    <div class="flex-fill text-center text-white align-self-center fw-bold">
                                        <i class="mdi mdi-check-circle text-success me-1"></i> Foto Oke
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TOMBOL SHUTTER --}}
                        <div class="d-flex flex-column align-items-center mb-4 position-relative">
                            <button type="button" id="capture-btn" class="shutter-btn" disabled>
                                <i class="mdi mdi-camera-iris text-white fs-2"></i>
                            </button>
                            <div class="mt-2 small text-muted fw-bold" id="shutter-label">Tunggu kamera...</div>
                        </div>

                        {{-- INFO LOKASI (HIDDEN TAPI ADA VISUAL DI ATAS) --}}
                        <div class="location-card bg-light rounded-3 p-3 mb-4 d-none">
                            <h6 id="coordinates-display">...</h6>
                            <div id="gps-accuracy-badge">...</div>
                        </div>

                        {{-- FORM INPUTS --}}
                        <div class="form-group mb-4">
                            <label class="small text-muted mb-2">Catatan (Opsional)</label>
                            <textarea name="notes" class="form-control bg-light border-0 rounded-3" rows="2" placeholder="Contoh: Izin pulang cepat karena sakit..."></textarea>
                        </div>

                        <input type="hidden" id="latitude" name="latitude">
                        <input type="hidden" id="longitude" name="longitude">
                        <input type="hidden" id="accuracy" name="accuracy">

                        {{-- SLIDER SUBMIT --}}
                        <div class="slide-submit-container shadow-sm">
                            <div class="slide-track disabled" id="slide-track">
                                <div class="slide-text" id="slide-text">Geser untuk Absen</div>
                                <div class="slide-thumb" id="slide-thumb"><i class="mdi mdi-chevron-right fs-4"></i></div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="{{ route('dashboard') }}" class="text-muted small text-decoration-none fw-bold">KEMBALI KE DASHBOARD</a>
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
        /* --- MEWAH STYLES --- */
        .text-gradient {
            background: linear-gradient(45deg, #2c3e50, #000000); 
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .fw-black { font-weight: 900; letter-spacing: -1px; }
        .ls-1 { letter-spacing: 1px; }
        
        /* GAMING SIGNAL BARS */
        .signal-bars { display: flex; gap: 3px; align-items: flex-end; height: 18px; width: 25px; }
        .bar { width: 5px; background-color: #e0e0e0; border-radius: 2px; transition: all 0.3s ease; }
        .bar-1 { height: 35%; }
        .bar-2 { height: 65%; }
        .bar-3 { height: 100%; }

        /* Status Warna Sinyal */
        .signal-bars.signal-searching .bar { animation: pulse 1s infinite; background-color: #bdbdbd; }
        
        .signal-bars.signal-good .bar { background-color: #00c853; box-shadow: 0 0 5px rgba(0, 200, 83, 0.5); }
        
        .signal-bars.signal-weak .bar-1, .signal-bars.signal-weak .bar-2 { background-color: #ffab00; }
        .signal-bars.signal-weak .bar-3 { background-color: #e0e0e0; }
        
        .signal-bars.signal-bad .bar-1 { background-color: #ff3d00; }
        .signal-bars.signal-bad .bar-2, .signal-bars.signal-bad .bar-3 { background-color: #e0e0e0; }

        @keyframes pulse { 0% { opacity: 0.3; } 50% { opacity: 1; } 100% { opacity: 0.3; } }

        /* --- CAMERA STYLES --- */
        .camera-wrapper { width: 100%; height: 450px; background: #000; position: relative; display: flex; align-items: center; justify-content: center; }
        #video-feed { 
            width: 100%; height: 100%; object-fit: cover; 
            transform: scaleX(-1); opacity: 0; transition: opacity 0.5s ease-in; 
        }
        #video-feed.ready { opacity: 1; }

        .camera-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 2; pointer-events: none; }
        .backdrop-blur { backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); }
        
        .focus-frame { width: 220px; height: 280px; border: 2px dashed rgba(255, 255, 255, 0.5); border-radius: 24px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); transition: all 0.3s ease; }
        .focus-frame.active { border: 3px solid #00ff88; box-shadow: 0 0 25px rgba(0, 255, 136, 0.3); }
        
        .shutter-btn { width: 80px; height: 80px; border-radius: 50%; border: 5px solid #f1f1f1; background-color: #bdbdbd; cursor: not-allowed; transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); }
        .shutter-btn.ready { background-color: #00c853; border-color: #fff; cursor: pointer; box-shadow: 0 0 20px rgba(0, 200, 83, 0.6); transform: scale(1.1); }
        .shutter-btn.ready:active { transform: scale(0.95); }
        
        .object-fit-contain { object-fit: contain; }
        .bg-gradient-black { background: linear-gradient(to top, rgba(0, 0, 0, 0.9), transparent); }
        
        /* SLIDER */
        .slide-submit-container { position: relative; width: 100%; height: 60px; background: #e9ecef; border-radius: 50px; overflow: hidden; border: 1px solid #dee2e6; }
        .slide-track { height: 100%; display: flex; align-items: center; cursor: pointer; }
        .slide-track.submitted { background: #198754; transition: 0.3s; }
        .slide-thumb { width: 50px; height: 50px; background: #fff; border-radius: 50%; margin-left: 5px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); position: relative; z-index: 2; transition: left 0.1s; color: #198754; }
        .slide-text { position: absolute; width: 100%; text-align: center; font-weight: 700; color: #6c757d; text-transform: uppercase; font-size: 0.9rem; letter-spacing: 1px; user-select: none; }
        .slide-track.submitted .slide-text { color: #fff; }
        .slide-track.disabled { pointer-events: none; opacity: 0.6; }
    </style>
@endpush

@push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js/dist/face-api.min.js"></script>

    <script>
        // 1. REAL-TIME CLOCK
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace(/\./g, ':');
            const clockEl = document.getElementById('live-clock');
            if(clockEl) clockEl.innerText = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock();

        document.addEventListener('DOMContentLoaded', function() {
            const useAI = {{ Auth::user()->use_face_recognition ? 'true' : 'false' }};

            const videoFeed = document.getElementById('video-feed');
            const startScreen = document.getElementById('start-screen');
            const resultScreen = document.getElementById('result-screen');
            const startBtn = document.getElementById('start-camera-btn');
            const captureBtn = document.getElementById('capture-btn');
            const shutterLabel = document.getElementById('shutter-label');
            const retakeBtn = document.getElementById('retake-btn');
            const previewImage = document.getElementById('preview-image');
            const photoInputHidden = document.getElementById('photo-input-hidden');
            const canvas = document.getElementById('capture-canvas');
            
            const faceStatusBadge = document.getElementById('face-status-badge');
            const focusFrame = document.querySelector('.focus-frame');
            const modelLoadingText = document.getElementById('model-loading-text');
            const cameraInstruction = document.getElementById('camera-instruction');

            const form = document.getElementById('attendance-form');
            const slideTrack = document.getElementById('slide-track');
            const slideThumb = document.getElementById('slide-thumb');
            const slideText = document.getElementById('slide-text');

            let streamRef = null;
            let detectionInterval = null;
            let isFaceValid = false;
            let modelsLoaded = false;

            // ==========================================
            // START CAMERA
            // ==========================================
            startBtn.addEventListener('click', async () => {
                if (useAI) {
                    if (!modelsLoaded) {
                        modelLoadingText.classList.remove('d-none');
                        startBtn.disabled = true;
                        try {
                            await faceapi.nets.tinyFaceDetector.loadFromUri('public/models');
                            modelsLoaded = true;
                            modelLoadingText.classList.add('d-none');
                            startCamera();
                        } catch (err) {
                            console.error("Model Error:", err);
                            alert("Gagal memuat AI. Cek koneksi/file model.");
                            startBtn.disabled = false;
                            modelLoadingText.classList.add('d-none');
                        }
                    } else {
                        startCamera();
                    }
                } else {
                    startCamera();
                }
            });

            function startCamera() {
                videoFeed.classList.remove('ready'); 

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
                        }, 100); 

                        if (useAI) {
                            startFaceDetection();
                        } else {
                            isFaceValid = true;
                            enableCaptureButton();
                            faceStatusBadge.innerHTML = '<i class="mdi mdi-camera"></i> Kamera Siap';
                            faceStatusBadge.className = 'badge bg-primary rounded-pill shadow';
                            if(cameraInstruction) cameraInstruction.innerText = "Ambil Foto Anda";
                        }
                    };
                })
                .catch(err => {
                    console.error("Camera Error:", err);
                    alert("Gagal akses kamera.");
                    startBtn.disabled = false;
                });
            }

            // ==========================================
            // FACE DETECTION
            // ==========================================
            function startFaceDetection() {
                if (detectionInterval) clearInterval(detectionInterval);
                detectionInterval = setInterval(async () => {
                    if (videoFeed.paused || videoFeed.ended) return;
                    try {
                        const detections = await faceapi.detectAllFaces(videoFeed, new faceapi.TinyFaceDetectorOptions());
                        if (detections.length > 0 && detections[0].score > 0.5) {
                            if (!isFaceValid) {
                                isFaceValid = true;
                                focusFrame.classList.add('active');
                                faceStatusBadge.innerHTML = '<i class="mdi mdi-face-recognition"></i> Wajah Terdeteksi';
                                faceStatusBadge.className = 'badge bg-success rounded-pill shadow border border-light';
                                if(cameraInstruction) cameraInstruction.innerText = "Tekan Tombol Hijau";
                                enableCaptureButton();
                            }
                        } else {
                            if (isFaceValid) {
                                isFaceValid = false;
                                focusFrame.classList.remove('active');
                                faceStatusBadge.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Mencari Wajah...';
                                faceStatusBadge.className = 'badge bg-dark bg-opacity-75 rounded-pill backdrop-blur border border-secondary';
                                if(cameraInstruction) cameraInstruction.innerText = "Posisikan Wajah";
                                disableCaptureButton();
                            }
                        }
                    } catch (e) { console.log("Skip frame"); }
                }, 200);
            }

            function enableCaptureButton() {
                captureBtn.disabled = false;
                captureBtn.classList.add('ready');
                shutterLabel.innerText = "Klik untuk ambil foto";
                shutterLabel.className = "mt-2 small text-success fw-bold";
            }

            function disableCaptureButton() {
                captureBtn.disabled = true;
                captureBtn.classList.remove('ready');
                shutterLabel.innerText = useAI ? "Wajah tidak terlihat" : "Tunggu kamera...";
                shutterLabel.className = "mt-2 small text-muted fw-bold";
            }

            // ==========================================
            // CAPTURE BUTTON
            // ==========================================
            captureBtn.addEventListener('click', async () => {
                if (!isFaceValid && useAI) return;
                if (!streamRef || !streamRef.active) return;

                captureBtn.style.transform = "scale(0.9)";
                setTimeout(() => captureBtn.style.transform = "scale(1.1)", 100);

                const MAX_WIDTH = 800;
                let width = videoFeed.videoWidth;
                let height = videoFeed.videoHeight;
                if (width > MAX_WIDTH) { height = Math.round(height * (MAX_WIDTH / width)); width = MAX_WIDTH; }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.save();
                ctx.scale(-1, 1);
                ctx.drawImage(videoFeed, -width, 0, width, height);
                ctx.restore();

                const MAX_FILE_SIZE = 200 * 1024;
                let quality = 0.9;
                let blob = null;
                const makeBlob = (q) => new Promise(resolve => canvas.toBlob(blob => resolve(blob), 'image/jpeg', q));

                shutterLabel.innerText = "Mengompresi...";
                do {
                    blob = await makeBlob(quality);
                    if (blob.size > MAX_FILE_SIZE) quality -= 0.1;
                } while (blob.size > MAX_FILE_SIZE && quality > 0.1);

                const file = new File([blob], "attendance.jpg", { type: "image/jpeg" });
                const dt = new DataTransfer();
                dt.items.add(file);
                photoInputHidden.files = dt.files;

                previewImage.src = URL.createObjectURL(blob);
                
                if (detectionInterval) clearInterval(detectionInterval);
                if (streamRef) { streamRef.getTracks().forEach(track => track.stop()); streamRef = null; }
                videoFeed.srcObject = null;

                resultScreen.classList.remove('d-none');
                checkGlobalValidity();
            });

            retakeBtn.addEventListener('click', () => {
                resultScreen.classList.add('d-none');
                startScreen.classList.remove('d-none');
                photoInputHidden.value = '';
                isFaceValid = false;
                disableCaptureButton();
                startBtn.disabled = false;
                videoFeed.srcObject = null;
                videoFeed.classList.remove('ready');
                faceStatusBadge.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Menunggu Kamera...';
                checkGlobalValidity();
            });

            // ==========================================
            // GPS WATCHER & SIGNAL BARS
            // ==========================================
            if (navigator.geolocation) {
                // Gunakan watchPosition untuk update real-time
                const barsContainer = document.querySelector('.signal-bars');
                const gpsText = document.getElementById('gps-text-status');
                barsContainer.classList.add('signal-searching');

                navigator.geolocation.watchPosition((pos) => {
                    document.getElementById('latitude').value = pos.coords.latitude;
                    document.getElementById('longitude').value = pos.coords.longitude;
                    document.getElementById('accuracy').value = pos.coords.accuracy;
                    document.getElementById('coordinates-display').innerText = `${pos.coords.latitude.toFixed(5)}, ${pos.coords.longitude.toFixed(5)}`;
                    document.getElementById('gps-accuracy-badge').innerText = `±${Math.round(pos.coords.accuracy)}m`;
                    
                    // Logic Bar Sinyal
                    const acc = pos.coords.accuracy;
                    barsContainer.classList.remove('signal-searching', 'signal-good', 'signal-weak', 'signal-bad');

                    if (acc <= 20) {
                        barsContainer.classList.add('signal-good');
                        gpsText.innerText = `Sinyal Bagus (±${Math.round(acc)}m)`;
                        gpsText.className = "small text-success fw-bold me-2";
                    } else if (acc <= 50) {
                        barsContainer.classList.add('signal-weak');
                        gpsText.innerText = `Sinyal Sedang (±${Math.round(acc)}m)`;
                        gpsText.className = "small text-warning fw-bold me-2";
                    } else {
                        barsContainer.classList.add('signal-bad');
                        gpsText.innerText = `Sinyal Buruk (±${Math.round(acc)}m)`;
                        gpsText.className = "small text-danger fw-bold me-2";
                    }

                    checkGlobalValidity();
                }, (err) => { 
                    barsContainer.classList.remove('signal-searching');
                    barsContainer.classList.add('signal-bad');
                    gpsText.innerText = "GPS Error/Off";
                }, {
                    enableHighAccuracy: true,
                    maximumAge: 0
                });
            }

            function checkGlobalValidity() {
                const hasPhoto = photoInputHidden.files.length > 0;
                const hasLoc = document.getElementById('latitude').value !== '';
                if (hasPhoto && hasLoc) {
                    slideTrack.classList.remove('disabled');
                    slideText.innerText = "GESER KE KANAN >>";
                    slideText.classList.add('text-success');
                    slideThumb.classList.add('shadow-lg');
                } else {
                    slideTrack.classList.add('disabled');
                    slideText.innerText = "Lengkapi Foto & Lokasi";
                    slideText.classList.remove('text-success');
                    slideThumb.classList.remove('shadow-lg');
                }
            }

            // Slider Logic
            let isDragging = false, startX = 0, currentX = 0, trackWidth = 0, thumbWidth = 0, maxSlide = 0;
            function initSlider() { trackWidth = slideTrack.offsetWidth; thumbWidth = slideThumb.offsetWidth; maxSlide = trackWidth - thumbWidth - 5; }
            window.addEventListener('resize', initSlider); initSlider();
            const onDragStart = (e) => { if (!slideTrack.classList.contains('disabled')) { isDragging = true; startX = (e.type === 'touchstart') ? e.touches[0].clientX : e.clientX; slideThumb.style.transition = 'none'; } };
            const onDragMove = (e) => { if (isDragging) { const clientX = (e.type === 'touchmove') ? e.touches[0].clientX : e.clientX; let moveX = clientX - startX; if (moveX < 0) moveX = 0; if (moveX > maxSlide) moveX = maxSlide; currentX = moveX; slideThumb.style.left = moveX + 'px'; slideText.style.opacity = 1 - (moveX / maxSlide); } };
            const onDragEnd = () => { if (isDragging) { isDragging = false; if (currentX >= maxSlide * 0.9) { slideThumb.style.left = maxSlide + 'px'; slideTrack.classList.add('submitted'); slideThumb.innerHTML = '<i class="mdi mdi-check"></i>'; slideText.innerText = "MENGIRIM..."; slideText.style.opacity = 1; form.submit(); } else { slideThumb.style.transition = 'left 0.3s'; slideThumb.style.left = '0px'; slideText.style.opacity = 1; } } };
            
            slideThumb.addEventListener('mousedown', onDragStart); window.addEventListener('mousemove', onDragMove); window.addEventListener('mouseup', onDragEnd);
            slideThumb.addEventListener('touchstart', onDragStart); window.addEventListener('touchmove', onDragMove); window.addEventListener('touchend', onDragEnd);
        });
    </script>
@endpush