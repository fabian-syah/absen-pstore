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

                    {{-- ALERT STATUS --}}
                    @if ($mode == 'pulang' && isset($attendance))
                        <div class="alert alert-light border shadow-sm mb-3 small">
                            <i class="mdi mdi-clock-outline me-1"></i> Masuk:
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
                        <div class="camera-wrapper position-relative overflow-hidden rounded-4 mb-3">

                            {{-- 1. VIDEO FEED (Normal View / Tidak Zoom) --}}
                            <video id="video-feed" autoplay muted playsinline></video>

                            {{-- 2. OVERLAY UI --}}
                            <div class="camera-overlay d-flex flex-column justify-content-between p-3">
                                <div class="text-center">
                                    <span id="face-status-badge"
                                        class="badge bg-dark bg-opacity-75 rounded-pill backdrop-blur border border-secondary">
                                        <i class="mdi mdi-loading mdi-spin me-1"></i> Menunggu Kamera...
                                    </span>
                                </div>

                                {{-- Focus Frame (Kotak Wajah) --}}
                                <div class="focus-frame">
                                    <div class="corner top-left"></div>
                                    <div class="corner top-right"></div>
                                    <div class="corner bottom-left"></div>
                                    <div class="corner bottom-right"></div>
                                </div>

                                <div class="text-center text-white small text-shadow fw-bold" id="camera-instruction">
                                    Siapkan Wajah
                                </div>
                            </div>

                            {{-- 3. TOMBOL START (Layar Hitam Awal) --}}
                            <div id="start-screen"
                                class="position-absolute top-0 start-0 w-100 h-100 bg-dark d-flex flex-column justify-content-center align-items-center z-3">
                                <div class="icon-circle mb-3">
                                    <i class="mdi mdi-camera text-white fs-1"></i>
                                </div>
                                <button type="button" id="start-camera-btn"
                                    class="btn btn-light rounded-pill fw-bold px-4">
                                    Buka Kamera
                                </button>
                                <div id="model-loading-text" class="text-white-50 mt-3 small d-none">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Menyiapkan AI...
                                </div>
                            </div>

                            {{-- 4. HASIL FOTO (PREVIEW) --}}
                            <div id="result-screen" class="position-absolute top-0 start-0 w-100 h-100 bg-black d-none z-3">
                                <img id="preview-image" src="" alt="Result"
                                    class="w-100 h-100 object-fit-contain">
                                <div class="position-absolute bottom-0 w-100 p-4 d-flex gap-2 bg-gradient-black">
                                    <button type="button" id="retake-btn"
                                        class="btn btn-outline-light flex-fill rounded-pill">
                                        Ulangi
                                    </button>
                                    <div class="flex-fill text-center text-white align-self-center fw-bold">
                                        <i class="mdi mdi-check-circle text-success me-1"></i> Foto Oke
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TOMBOL SHUTTER (HIJAU MENYALA) --}}
                        <div class="d-flex flex-column align-items-center mb-4 position-relative">
                            {{-- Tombol Capture --}}
                            <button type="button" id="capture-btn" class="shutter-btn" disabled>
                                <i class="mdi mdi-camera-iris text-white fs-2"></i>
                            </button>
                            <div class="mt-2 small text-muted" id="shutter-label">Tunggu deteksi wajah...</div>
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
                            <textarea name="notes" class="form-control bg-light border-0 rounded-3" rows="2"
                                placeholder="Tulis catatan (Opsional)..."></textarea>
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
            height: 450px;
            /* FIXED HEIGHT AGAR TIDAK TERLALU PANJANG */
            background: #000;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #video-feed {
            width: 100%;
            height: 100%;
            /* KUNCI SUPAYA TIDAK ZOOM: CONTAIN = TAMPILKAN UTUH */
            object-fit: contain;
            transform: scaleX(-1);
            /* Mirror effect */
        }

        .camera-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 2;
            pointer-events: none;
        }

        .backdrop-blur {
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }

        /* Focus Frame */
        .focus-frame {
            width: 220px;
            height: 280px;
            border: 2px dashed rgba(255, 255, 255, 0.4);
            border-radius: 24px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            transition: all 0.3s ease;
        }

        .focus-frame.active {
            border: 3px solid #00ff88;
            /* Hijau saat detect */
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.2);
        }

        /* === TOMBOL SHUTTER HIJAU === */
        .shutter-btn {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 5px solid #e0e0e0;
            background-color: #9e9e9e;
            /* Abu-abu default (disabled) */
            cursor: not-allowed;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Saat Wajah Terdeteksi (Ready) */
        .shutter-btn.ready {
            background-color: #00c853;
            /* HIJAU TERANG */
            border-color: #fff;
            cursor: pointer;
            box-shadow: 0 0 20px rgba(0, 200, 83, 0.6);
            /* Efek Glow Hijau */
            transform: scale(1.1);
        }

        .shutter-btn.ready:active {
            transform: scale(0.95);
        }

        .object-fit-contain {
            object-fit: contain;
        }

        .bg-gradient-black {
            background: linear-gradient(to top, rgba(0, 0, 0, 0.9), transparent);
        }

        /* SLIDER STYLES */
        .slide-submit-container {
            position: relative;
            width: 100%;
            height: 55px;
            background: #e9ecef;
            border-radius: 50px;
            overflow: hidden;
        }

        .slide-track {
            height: 100%;
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .slide-track.submitted {
            background: #198754;
            transition: 0.3s;
        }

        .slide-thumb {
            width: 45px;
            height: 45px;
            background: #fff;
            border-radius: 50%;
            margin-left: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 2;
            transition: left 0.1s;
        }

        .slide-text {
            position: absolute;
            width: 100%;
            text-align: center;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
            user-select: none;
        }

        .slide-track.submitted .slide-text {
            color: #fff;
        }

        .slide-track.disabled {
            pointer-events: none;
            opacity: 0.6;
        }
    </style>
@endpush

@push('scripts')
    {{-- LOAD FACE API --}}
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js/dist/face-api.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ELEMENT REFERENCES
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

            // UI Status
            const faceStatusBadge = document.getElementById('face-status-badge');
            const focusFrame = document.querySelector('.focus-frame');
            const modelLoadingText = document.getElementById('model-loading-text');
            const cameraInstruction = document.getElementById('camera-instruction');

            // Location & Slider
            const form = document.getElementById('attendance-form');
            const slideTrack = document.getElementById('slide-track');
            const slideThumb = document.getElementById('slide-thumb');
            const slideText = document.getElementById('slide-text');

            let streamRef = null;
            let detectionInterval = null;
            let isFaceValid = false;
            let modelsLoaded = false;

            // 1. LOAD MODELS
            startBtn.addEventListener('click', async () => {
                if (!modelsLoaded) {
                    modelLoadingText.classList.remove('d-none');
                    startBtn.disabled = true;
                    try {
                        await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
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

            // 2. CAMERA SETUP
            function startCamera() {
                // Resolusi dimaksimalkan untuk deteksi wajah, nanti di-resize saat capture
                navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: "user",
                            width: {
                                ideal: 1280
                            },
                            height: {
                                ideal: 720
                            }
                        },
                        audio: false
                    })
                    .then(stream => {
                        streamRef = stream;
                        videoFeed.srcObject = stream;
                        startScreen.classList.add('d-none');

                        videoFeed.onloadedmetadata = () => {
                            videoFeed.play();
                            startFaceDetection();
                        };
                    })
                    .catch(err => {
                        alert("Gagal akses kamera.");
                    });
            }

            // 3. FACE DETECTION
            function startFaceDetection() {
                detectionInterval = setInterval(async () => {
                    if (videoFeed.paused || videoFeed.ended) return;

                    const detections = await faceapi.detectAllFaces(videoFeed, new faceapi
                        .TinyFaceDetectorOptions());

                    if (detections.length > 0 && detections[0].score > 0.5) {
                        if (!isFaceValid) {
                            isFaceValid = true;
                            focusFrame.classList.add('active');
                            faceStatusBadge.innerHTML =
                                '<i class="mdi mdi-face-recognition"></i> Wajah Terdeteksi';
                            faceStatusBadge.className =
                                'badge bg-success rounded-pill shadow border border-light';
                            cameraInstruction.innerText = "Tekan Tombol Hijau";
                            captureBtn.disabled = false;
                            captureBtn.classList.add('ready');
                            shutterLabel.innerText = "Klik untuk ambil foto";
                            shutterLabel.className = "mt-2 small text-success fw-bold";
                        }
                    } else {
                        if (isFaceValid) {
                            isFaceValid = false;
                            focusFrame.classList.remove('active');
                            faceStatusBadge.innerHTML =
                                '<i class="mdi mdi-loading mdi-spin"></i> Mencari Wajah...';
                            faceStatusBadge.className =
                                'badge bg-dark bg-opacity-75 rounded-pill backdrop-blur border border-secondary';
                            cameraInstruction.innerText = "Posisikan Wajah";
                            captureBtn.disabled = true;
                            captureBtn.classList.remove('ready');
                            shutterLabel.innerText = "Wajah tidak terlihat";
                            shutterLabel.className = "mt-2 small text-muted";
                        }
                    }
                }, 200);
            }

            // ==========================================
            // 4. CAPTURE & SMART COMPRESS (MAX 200KB)
            // ==========================================
            captureBtn.addEventListener('click', async () => {
                if (!isFaceValid) return;

                // Animasi Klik
                captureBtn.style.transform = "scale(0.9)";
                setTimeout(() => captureBtn.style.transform = "scale(1.1)", 100);

                // --- 1. RESIZE RESOLUSI DULU (Agar File Kecil) ---
                const MAX_WIDTH = 800; // Lebar max 800px (Cukup untuk absen)
                let width = videoFeed.videoWidth;
                let height = videoFeed.videoHeight;

                if (width > MAX_WIDTH) {
                    height = Math.round(height * (MAX_WIDTH / width));
                    width = MAX_WIDTH;
                }

                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                // Mirror effect
                ctx.save();
                ctx.scale(-1, 1);
                ctx.drawImage(videoFeed, -width, 0, width, height);
                ctx.restore();

                // --- 2. KOMPRESI ITERATIF (Target < 200KB) ---
                const MAX_FILE_SIZE = 200 * 1024; // 200KB dalam bytes
                let quality = 0.9; // Mulai dari kualitas 90%
                let blob = null;

                // Fungsi helper untuk bikin blob
                const makeBlob = (q) => new Promise(resolve => canvas.toBlob(blob => resolve(blob),
                    'image/jpeg', q));

                // Loading indikator sementara (opsional, karena prosesnya cepat)
                shutterLabel.innerText = "Mengompresi...";

                // Loop: Turunkan kualitas sampai di bawah 200KB atau kualitas mentok di 10%
                do {
                    blob = await makeBlob(quality);
                    if (blob.size > MAX_FILE_SIZE) {
                        quality -= 0.1; // Kurangi 10%
                    }
                } while (blob.size > MAX_FILE_SIZE && quality > 0.1);

                // --- 3. SET HASIL KE INPUT ---
                const file = new File([blob], "attendance.jpg", {
                    type: "image/jpeg"
                });
                const dt = new DataTransfer();
                dt.items.add(file);
                photoInputHidden.files = dt.files;

                console.log(
                    `Foto Final: ${(blob.size / 1024).toFixed(2)} KB (Quality: ${quality.toFixed(1)})`
                    );

                // Tampilkan Preview
                previewImage.src = URL.createObjectURL(blob);

                // Stop Kamera
                if (detectionInterval) clearInterval(detectionInterval);
                if (streamRef) streamRef.getTracks().forEach(t => t.stop());

                resultScreen.classList.remove('d-none');
                checkGlobalValidity();
            });

            retakeBtn.addEventListener('click', () => {
                resultScreen.classList.add('d-none');
                startScreen.classList.remove('d-none');
                photoInputHidden.value = '';
                isFaceValid = false;
                captureBtn.classList.remove('ready');
                captureBtn.disabled = true;
                shutterLabel.innerText = "Tunggu deteksi wajah...";
                shutterLabel.className = "mt-2 small text-muted";
                checkGlobalValidity();
            });

            // 5. GPS & SLIDER
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((pos) => {
                    document.getElementById('latitude').value = pos.coords.latitude;
                    document.getElementById('longitude').value = pos.coords.longitude;
                    document.getElementById('accuracy').value = pos.coords.accuracy;
                    document.getElementById('coordinates-display').innerText =
                        `${pos.coords.latitude.toFixed(4)}, ${pos.coords.longitude.toFixed(4)}`;
                    document.getElementById('gps-accuracy-badge').innerText =
                        `±${Math.round(pos.coords.accuracy)}m`;
                    document.getElementById('gps-accuracy-badge').className =
                        'badge bg-success rounded-pill';
                    checkGlobalValidity();
                }, (err) => {
                    document.getElementById('coordinates-display').innerText = "Gagal Lokasi";
                });
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

            // Slider Logic Standard
            let isDragging = false,
                startX = 0,
                currentX = 0,
                trackWidth = 0,
                thumbWidth = 0,
                maxSlide = 0;

            function initSlider() {
                trackWidth = slideTrack.offsetWidth;
                thumbWidth = slideThumb.offsetWidth;
                maxSlide = trackWidth - thumbWidth - 5;
            }
            window.addEventListener('resize', initSlider);
            initSlider();
            const onDragStart = (e) => {
                if (!slideTrack.classList.contains('disabled')) {
                    isDragging = true;
                    startX = (e.type === 'touchstart') ? e.touches[0].clientX : e.clientX;
                    slideThumb.style.transition = 'none';
                }
            };
            const onDragMove = (e) => {
                if (isDragging) {
                    const clientX = (e.type === 'touchmove') ? e.touches[0].clientX : e.clientX;
                    let moveX = clientX - startX;
                    if (moveX < 0) moveX = 0;
                    if (moveX > maxSlide) moveX = maxSlide;
                    currentX = moveX;
                    slideThumb.style.left = moveX + 'px';
                    slideText.style.opacity = 1 - (moveX / maxSlide);
                }
            };
            const onDragEnd = () => {
                if (isDragging) {
                    isDragging = false;
                    if (currentX >= maxSlide * 0.9) {
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
                }
            };

            slideThumb.addEventListener('mousedown', onDragStart);
            window.addEventListener('mousemove', onDragMove);
            window.addEventListener('mouseup', onDragEnd);
            slideThumb.addEventListener('touchstart', onDragStart);
            window.addEventListener('touchmove', onDragMove);
            window.addEventListener('touchend', onDragEnd);
        });
    </script>
@endpush
