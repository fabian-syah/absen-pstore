@extends('layout.master')

@section('title')
    Absen Mandiri ({{ ucfirst($mode) }})
@endsection

@section('heading')
    Absen Mandiri ({{ ucfirst($mode) }})
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Form Absen Mandiri ({{ ucfirst($mode) }})</h4>
                        <div class="badge badge-success badge-pill">
                            <i class="mdi mdi-face-recognition me-1"></i>Face Detection Active
                        </div>
                    </div>

                    {{-- ALERT STATUS (TETAP SAMA SEPERTI KODE ANDA) --}}
                    @if ($mode == 'pulang' && isset($attendance))
                        @php
                            $checkInDate = \Carbon\Carbon::parse($attendance->check_in_time);
                            $isNextDay = !$checkInDate->isToday();
                            $statusClass = 'secondary';
                            if($attendance->status == 'verified' || $attendance->status == 'present') $statusClass = 'success';
                            if($attendance->status == 'pending_verification') $statusClass = 'warning';
                            if($attendance->status == 'rejected') $statusClass = 'danger';
                            $typeLabel = ($attendance->attendance_type == 'scan') ? 'Security Scan' : 'Selfie Mandiri';
                        @endphp
                        @if ($isNextDay)
                            <div class="alert alert-warning border-0 shadow-sm mb-4">
                                <strong><i class="mdi mdi-weather-night me-1"></i> Pulang Lintas Hari (Lembur)</strong>
                            </div>
                        @else
                            <div class="alert alert-info border-0 shadow-sm mb-4">
                                Masuk pukul <strong>{{ $checkInDate->format('H:i') }}</strong> via <strong>{{ $typeLabel }}</strong>.
                                Status: <span class="badge badge-{{ $statusClass }}">{{ strtoupper($attendance->status) }}</span>
                            </div>
                        @endif
                    @endif

                    <form class="forms-sample" action="{{ route('self.attend.store') }}" method="POST"
                        enctype="multipart/form-data" id="attendance-form">
                        @csrf
                        @if (isset($attendance) && $attendance)
                            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                        @endif

                        {{-- INPUT FILE HIDDEN UNTUK MENAMPUNG HASIL FOTO DARI JS --}}
                        <input type="file" name="photo" id="photo-input-hidden" class="d-none" accept="image/jpeg">

                        <div class="row mb-4">
                            {{-- KOLOM KIRI: KAMERA STREAM --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="fw-semibold mb-3">Foto Selfie <span class="text-danger">*</span></label>
                                    
                                    <div class="camera-container text-center position-relative">
                                        
                                        {{-- 1. VIDEO STREAM (KAMERA AKTIF) --}}
                                        <div id="video-container" class="d-none overflow-hidden rounded position-relative">
                                            <video id="video-feed" autoplay muted playsinline class="w-100 rounded"></video>
                                            
                                            {{-- Status Deteksi di atas Video --}}
                                            <div id="face-overlay-status" class="position-absolute bottom-0 start-0 w-100 p-2 bg-dark bg-opacity-75 text-white">
                                                <i class="mdi mdi-loading mdi-spin me-1"></i> Mencari wajah...
                                            </div>
                                        </div>

                                        {{-- 2. HASIL FOTO (PREVIEW) --}}
                                        <div id="result-container" class="d-none">
                                            <img id="preview-image" src="" alt="Preview" class="img-fluid rounded shadow-sm">
                                            <div class="mt-2 text-success fw-bold small">
                                                <i class="mdi mdi-check-circle"></i> Foto Berhasil Diambil
                                            </div>
                                            <button type="button" id="retake-btn" class="btn btn-danger btn-sm mt-2">
                                                <i class="mdi mdi-camera-retake me-1"></i>Ambil Ulang
                                            </button>
                                        </div>

                                        {{-- 3. TOMBOL START --}}
                                        <div id="start-container" class="py-5">
                                            <i class="mdi mdi-camera display-4 text-muted mb-3 d-block"></i>
                                            <button type="button" id="start-camera-btn" class="btn btn-primary btn-lg rounded-pill shadow">
                                                <i class="mdi mdi-camera me-1"></i> Buka Kamera
                                            </button>
                                            <div id="model-loading-text" class="text-muted mt-2 d-none small">
                                                <span class="spinner-border spinner-border-sm me-1"></span> Menyiapkan AI...
                                            </div>
                                        </div>

                                        {{-- 4. TOMBOL CAPTURE (MUNCUL SAAT VIDEO AKTIF) --}}
                                        <div id="capture-container" class="mt-3 d-none">
                                            <button type="button" id="capture-btn" class="btn btn-success btn-lg rounded-circle shadow-lg p-3" disabled style="width: 60px; height: 60px;">
                                                <i class="mdi mdi-camera-iris fs-4"></i>
                                            </button>
                                            <div class="small text-muted mt-1" id="capture-hint">Tunggu wajah terdeteksi...</div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            {{-- KOLOM KANAN: LOKASI --}}
                            <div class="col-md-6">
                                <div class="location-info p-4 rounded bg-light h-100">
                                    <h6 class="fw-semibold mb-3">
                                        <i class="mdi mdi-map-marker-outline me-2"></i>Lokasi Anda
                                    </h6>
                                    <div id="location-status" class="alert alert-info mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-loading mdi-spin me-2"></i> <span>Mencari GPS...</span>
                                        </div>
                                    </div>
                                    <div id="location-details" class="d-none">
                                        <div class="mb-2">
                                            <small class="text-muted d-block">Koordinat</small>
                                            <span class="fw-bold font-monospace" id="coordinates-display">-</span>
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted d-block">Akurasi GPS</small>
                                            <span class="badge badge-outline-success" id="accuracy-display">-</span>
                                        </div>
                                    </div>
                                    <input type="hidden" id="latitude" name="latitude">
                                    <input type="hidden" id="longitude" name="longitude">
                                    <input type="hidden" id="accuracy" name="accuracy">
                                </div>
                            </div>
                        </div>

                        {{-- INPUT NOTES --}}
                        <div class="form-group mb-4">
                            <label class="fw-semibold mb-2">Catatan (Opsional)</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Contoh: Lembur..."></textarea>
                        </div>

                        {{-- SLIDE TO SUBMIT --}}
                        <div class="slide-submit-container mt-4">
                            <div class="slide-track disabled" id="slide-track">
                                <div class="slide-text" id="slide-text">Geser untuk {{ ucfirst($mode) }} <i class="mdi mdi-chevron-double-right"></i></div>
                                <div class="slide-thumb" id="slide-thumb"><i class="mdi mdi-arrow-right"></i></div>
                            </div>
                            <small class="text-muted text-center d-block mt-2" id="slide-hint">Ambil foto & lokasi untuk membuka kunci</small>
                        </div>

                        <div class="text-center mt-3">
                            <a href="{{ route('dashboard') }}" class="text-muted small text-decoration-none">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    {{-- CANVAS HIDDEN UNTUK PROSES GAMBAR --}}
    <canvas id="capture-canvas" class="d-none"></canvas>
@endsection

@push('styles')
    <style>
        .camera-container { border: 2px dashed #cbd5e1; border-radius: 12px; padding: 1.5rem; background: #f8fafc; min-height: 350px; display: flex; flex-direction: column; justify-content: center; }
        #video-feed { transform: scaleX(-1); /* Mirror effect supaya natural */ object-fit: cover; }
        
        /* SLIDE TO SUBMIT CSS (SAMA SEPERTI YANG LAMA) */
        .slide-submit-container { position: relative; user-select: none; width: 100%; height: 60px; }
        .slide-track { position: relative; width: 100%; height: 100%; background-color: #e2e8f0; border-radius: 30px; overflow: hidden; cursor: pointer; transition: background-color 0.3s ease; }
        .slide-track.disabled { opacity: 0.6; pointer-events: none; background-color: #f1f5f9; }
        .slide-track.submitted { background-color: #10b981; }
        .slide-thumb { position: absolute; top: 4px; left: 4px; width: 52px; height: 52px; background-color: #1e293b; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; cursor: grab; z-index: 2; transition: left 0.1s linear, transform 0.2s ease; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .slide-thumb:active { cursor: grabbing; transform: scale(0.95); }
        .slide-text { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-weight: 600; color: #64748b; text-transform: uppercase; font-size: 14px; letter-spacing: 1px; z-index: 1; padding-left: 20px; }
        .slide-track.submitted .slide-text { color: white; padding-left: 0; }
    </style>
@endpush

@push('scripts')
    {{-- LOAD FACE API --}}
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js/dist/face-api.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ELEMENT REFERENCES
            const videoFeed = document.getElementById('video-feed');
            const videoContainer = document.getElementById('video-container');
            const resultContainer = document.getElementById('result-container');
            const startContainer = document.getElementById('start-container');
            const startBtn = document.getElementById('start-camera-btn');
            const captureContainer = document.getElementById('capture-container');
            const captureBtn = document.getElementById('capture-btn');
            const captureHint = document.getElementById('capture-hint');
            const retakeBtn = document.getElementById('retake-btn');
            const previewImage = document.getElementById('preview-image');
            const canvas = document.getElementById('capture-canvas');
            const overlayStatus = document.getElementById('face-overlay-status');
            const modelLoadingText = document.getElementById('model-loading-text');
            const photoInputHidden = document.getElementById('photo-input-hidden');
            
            // LOCATION & FORM
            const locationStatus = document.getElementById('location-status');
            const locationDetails = document.getElementById('location-details');
            const form = document.getElementById('attendance-form');
            const slideTrack = document.getElementById('slide-track');
            const slideThumb = document.getElementById('slide-thumb');
            const slideText = document.getElementById('slide-text');
            const slideHint = document.getElementById('slide-hint');

            let streamRef = null;
            let detectionInterval = null;
            let isFaceValid = false;
            let modelsLoaded = false;

            // ============================================
            // 1. LOGIC FACE RECOGNITION & KAMERA
            // ============================================
            
            async function loadModels() {
                modelLoadingText.classList.remove('d-none');
                startBtn.disabled = true;
                try {
                    // Pastikan folder /models ada di public dan berisi file shard/manifest
                    await faceapi.nets.tinyFaceDetector.loadFromUri('public/models');
                    console.log("Face API Models Loaded");
                    modelsLoaded = true;
                    modelLoadingText.classList.add('d-none');
                    startBtn.disabled = false;
                    startCamera();
                } catch (err) {
                    console.error("Error loading models:", err);
                    alert("Gagal memuat AI. Pastikan file model ada di folder public/models.");
                    modelLoadingText.innerText = "Error memuat Model AI.";
                }
            }

            startBtn.addEventListener('click', function() {
                if (!modelsLoaded) {
                    loadModels();
                } else {
                    startCamera();
                }
            });

            function startCamera() {
                startContainer.classList.add('d-none');
                videoContainer.classList.remove('d-none');
                captureContainer.classList.remove('d-none');

                navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" }, audio: false })
                    .then(stream => {
                        streamRef = stream;
                        videoFeed.srcObject = stream;
                        videoFeed.onloadedmetadata = () => {
                            videoFeed.play();
                            startDetection(); // Mulai deteksi saat video jalan
                        };
                    })
                    .catch(err => {
                        console.error("Camera Error:", err);
                        alert("Gagal membuka kamera. Pastikan izin diberikan dan menggunakan HTTPS.");
                        startContainer.classList.remove('d-none');
                        videoContainer.classList.add('d-none');
                        captureContainer.classList.add('d-none');
                    });
            }

            function startDetection() {
                // Interval deteksi setiap 200ms agar ringan
                detectionInterval = setInterval(async () => {
                    if (videoFeed.paused || videoFeed.ended) return;

                    // Deteksi wajah menggunakan TinyFaceDetector (Cepat & Ringan)
                    const detections = await faceapi.detectAllFaces(videoFeed, new faceapi.TinyFaceDetectorOptions());

                    if (detections.length > 0) {
                        // WAJAH DITEMUKAN
                        const detection = detections[0];
                        if (detection.score > 0.5) { // Akurasi > 50%
                            isFaceValid = true;
                            captureBtn.disabled = false;
                            captureBtn.classList.remove('btn-secondary');
                            captureBtn.classList.add('btn-success');
                            
                            overlayStatus.innerHTML = '<i class="mdi mdi-check-circle text-success"></i> Wajah Terdeteksi';
                            captureHint.innerText = "Silakan ambil foto";
                            captureHint.classList.add('text-success');
                        }
                    } else {
                        // WAJAH HILANG
                        isFaceValid = false;
                        captureBtn.disabled = true;
                        captureBtn.classList.add('btn-secondary');
                        captureBtn.classList.remove('btn-success');
                        
                        overlayStatus.innerHTML = '<i class="mdi mdi-alert-circle text-warning"></i> Wajah Tidak Terlihat';
                        captureHint.innerText = "Arahkan wajah ke kamera...";
                        captureHint.classList.remove('text-success');
                    }
                }, 200);
            }

            function stopCamera() {
                if (streamRef) {
                    streamRef.getTracks().forEach(track => track.stop());
                }
                if (detectionInterval) clearInterval(detectionInterval);
            }

            // TOMBOL AMBIL FOTO
            captureBtn.addEventListener('click', function() {
                if (!isFaceValid) return; // Proteksi ganda

                // Ambil gambar dari video ke canvas
                const context = canvas.getContext('2d');
                canvas.width = videoFeed.videoWidth;
                canvas.height = videoFeed.videoHeight;
                
                // Draw video (tambah scaleX -1 jika ingin hasil mirror seperti preview)
                context.save();
                context.scale(-1, 1); 
                context.drawImage(videoFeed, -canvas.width, 0, canvas.width, canvas.height);
                context.restore();

                // Convert Canvas ke Blob (File)
                canvas.toBlob(function(blob) {
                    // Buat File Object baru
                    const file = new File([blob], "selfie_attendance.jpg", { type: "image/jpeg" });
                    
                    // Masukkan ke Input File Hidden (Agar Controller tidak berubah)
                    const container = new DataTransfer();
                    container.items.add(file);
                    photoInputHidden.files = container.files;

                    // Tampilkan Preview
                    const url = URL.createObjectURL(blob);
                    previewImage.src = url;

                    // Update UI
                    videoContainer.classList.add('d-none');
                    captureContainer.classList.add('d-none');
                    resultContainer.classList.remove('d-none');
                    
                    stopCamera();
                    checkFormValidity(); // Cek slider unlock
                }, 'image/jpeg', 0.8);
            });

            retakeBtn.addEventListener('click', function() {
                resultContainer.classList.add('d-none');
                photoInputHidden.value = ''; // Reset file
                startCamera(); // Buka kamera lagi
                checkFormValidity();
            });


            // ============================================
            // 2. LOGIC LOKASI & SLIDER
            // ============================================
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        document.getElementById('latitude').value = pos.coords.latitude;
                        document.getElementById('longitude').value = pos.coords.longitude;
                        document.getElementById('accuracy').value = pos.coords.accuracy;
                        document.getElementById('coordinates-display').innerText = `${pos.coords.latitude.toFixed(5)}, ${pos.coords.longitude.toFixed(5)}`;
                        document.getElementById('accuracy-display').innerText = `± ${Math.round(pos.coords.accuracy)} m`;
                        locationStatus.innerHTML = `<div class="text-success small"><i class="mdi mdi-check-circle me-1"></i>Lokasi Terkunci</div>`;
                        locationDetails.classList.remove('d-none');
                        checkFormValidity();
                    },
                    (err) => { locationStatus.innerHTML = `<div class="text-danger small">Gagal: ${err.message}</div>`; },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            } else { locationStatus.innerHTML = "Browser tidak support GPS."; }

            function checkFormValidity() {
                const hasPhoto = photoInputHidden.files.length > 0;
                const hasLoc = document.getElementById('latitude').value !== '';
                
                if (hasPhoto && hasLoc) {
                    slideTrack.classList.remove('disabled');
                    slideHint.textContent = "Geser tombol ke kanan untuk konfirmasi";
                    slideHint.classList.remove('text-muted');
                    slideHint.classList.add('text-success', 'fw-bold');
                } else {
                    slideTrack.classList.add('disabled');
                    slideHint.textContent = "Ambil foto & lokasi untuk membuka kunci";
                    slideHint.classList.add('text-muted');
                    slideHint.classList.remove('text-success', 'fw-bold');
                }
            }

            // SLIDER LOGIC (Copy Paste yang lama, fungsi sama)
            let isDragging = false, startX = 0, currentX = 0, trackWidth = 0, thumbWidth = 0, maxSlide = 0, isSubmitted = false;
            
            function initSlider() {
                trackWidth = slideTrack.offsetWidth;
                thumbWidth = slideThumb.offsetWidth;
                maxSlide = trackWidth - thumbWidth - 8;
            }
            window.addEventListener('resize', initSlider);
            initSlider(); // Init awal

            function startDrag(e) {
                if (slideTrack.classList.contains('disabled') || isSubmitted) return;
                isDragging = true;
                startX = (e.type === 'touchstart') ? e.touches[0].clientX : e.clientX;
                slideThumb.style.transition = 'none';
            }
            function onDrag(e) {
                if (!isDragging || isSubmitted) return;
                const clientX = (e.type === 'touchmove') ? e.touches[0].clientX : e.clientX;
                let moveX = clientX - startX;
                if (moveX < 0) moveX = 0;
                if (moveX > maxSlide) moveX = maxSlide;
                currentX = moveX;
                slideThumb.style.left = (4 + moveX) + 'px';
                slideText.style.opacity = 1 - (moveX / maxSlide);
            }
            function endDrag() {
                if (!isDragging || isSubmitted) return;
                isDragging = false;
                if (currentX >= maxSlide * 0.9) {
                    isSubmitted = true;
                    slideThumb.style.left = (4 + maxSlide) + 'px';
                    slideTrack.classList.add('submitted');
                    slideThumb.innerHTML = '<i class="mdi mdi-check"></i>';
                    slideThumb.style.backgroundColor = '#fff';
                    slideThumb.style.color = '#10b981';
                    slideText.style.opacity = 1;
                    slideText.innerHTML = "MEMPROSES DATA...";
                    form.submit();
                } else {
                    slideThumb.style.transition = 'left 0.3s ease';
                    slideThumb.style.left = '4px';
                    slideText.style.opacity = 1;
                }
            }

            slideThumb.addEventListener('mousedown', startDrag);
            document.addEventListener('mousemove', onDrag);
            document.addEventListener('mouseup', endDrag);
            slideThumb.addEventListener('touchstart', startDrag);
            document.addEventListener('touchmove', onDrag);
            document.addEventListener('touchend', endDrag);
        });
    </script>
@endpush