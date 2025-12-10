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
                        <div class="badge badge-dark badge-pill">
                            <i class="mdi mdi-camera me-1"></i>Selfie Mode
                        </div>
                    </div>

                    {{-- ALERT STATUS --}}
                    @if ($mode == 'pulang' && isset($attendance))
                        @php
                            $checkInDate = \Carbon\Carbon::parse($attendance->check_in_time);
                            $isNextDay = !$checkInDate->isToday();
                            
                            // Visualisasi Status
                            $statusClass = 'secondary';
                            if($attendance->status == 'verified' || $attendance->status == 'present') $statusClass = 'success';
                            if($attendance->status == 'pending_verification') $statusClass = 'warning';
                            if($attendance->status == 'rejected') $statusClass = 'danger';

                            // Deteksi Tipe Masuk
                            $typeLabel = ($attendance->attendance_type == 'scan') ? 'Security Scan' : 'Selfie Mandiri';
                        @endphp

                        @if ($isNextDay)
                            <div class="alert alert-warning border-0 shadow-sm mb-4">
                                <div class="d-flex align-items-start">
                                    <i class="mdi mdi-weather-night display-4 me-3"></i>
                                    <div>
                                        <h5 class="fw-bold mb-1">Pulang Lintas Hari (Lembur)</h5>
                                        <p class="mb-1 small">
                                            Anda menutup sesi tanggal: 
                                            <span class="fw-bold">{{ $checkInDate->translatedFormat('l, d F Y') }}</span>
                                        </p>
                                        <span class="badge badge-outline-dark">Masuk via: {{ $typeLabel }}</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info border-0 shadow-sm mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="mdi mdi-information-outline me-2 fs-4"></i>
                                    <div>
                                        Anda masuk pukul <strong>{{ $checkInDate->format('H:i') }}</strong> via <strong>{{ $typeLabel }}</strong>. <br>
                                        Status saat ini: <span class="badge badge-{{ $statusClass }} ms-1">{{ strtoupper($attendance->status) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <p class="text-muted mb-4">
                            Silakan ambil foto selfie untuk melakukan absen <strong>{{ strtoupper($mode) }}</strong> hari ini.
                        </p>
                    @endif

                    <form class="forms-sample" action="{{ route('self.attend.store') }}" method="POST"
                        enctype="multipart/form-data" id="attendance-form">
                        @csrf

                        {{-- PENTING: ID Absen untuk mode Pulang (Hybrid Support) --}}
                        @if (isset($attendance) && $attendance)
                            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                        @endif

                        <div class="row mb-4">
                            {{-- KOLOM KIRI: KAMERA --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="fw-semibold mb-3">Foto Selfie <span class="text-danger">*</span></label>
                                    <div class="camera-container text-center">
                                        {{-- Preview --}}
                                        <div id="camera-preview" class="camera-preview mb-3 d-none">
                                            <img id="preview-image" src="" alt="Preview Foto" class="img-fluid rounded shadow-sm">
                                            <div class="preview-overlay">
                                                <div class="watermark-timestamp">
                                                    {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y H:i:s') }}
                                                </div>
                                            </div>
                                            <button type="button" id="retake-btn" class="btn btn-danger btn-sm mt-2 d-none">
                                                <i class="mdi mdi-camera-retake me-1"></i>Ambil Ulang
                                            </button>
                                        </div>
                                        {{-- Placeholder --}}
                                        <div id="camera-placeholder" class="camera-placeholder">
                                            <i class="mdi mdi-camera display-4 text-muted mb-3"></i>
                                            <p class="text-muted mb-3">Klik tombol untuk ambil foto</p>
                                        </div>
                                        {{-- Tombol --}}
                                        <div class="d-flex gap-2 justify-content-center">
                                            <input type="file" name="photo" id="photo-input" class="d-none" accept="image/*" capture="user" required>
                                            <button type="button" id="capture-btn" class="btn btn-dark">
                                                <i class="mdi mdi-camera me-1"></i>Buka Kamera
                                            </button>
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
                                            <i class="mdi mdi-loading mdi-spin me-2"></i>
                                            <span>Mencari GPS...</span>
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
                                <div class="slide-thumb" id="slide-thumb">
                                    <i class="mdi mdi-arrow-right"></i>
                                </div>
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
@endsection

@push('styles')
    <style>
        .camera-container { border: 2px dashed #cbd5e1; border-radius: 12px; padding: 1.5rem; background: #f8fafc; }
        .camera-preview { position: relative; overflow: hidden; border-radius: 8px; }
        .preview-overlay { position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.6); padding: 8px; }
        .watermark-timestamp { color: #fff; font-size: 0.75rem; text-align: center; }
        #preview-image { width: 100%; height: auto; object-fit: cover; }

        /* SLIDE TO SUBMIT CSS */
        .slide-submit-container {
            position: relative;
            user-select: none;
            width: 100%;
            height: 60px;
        }

        .slide-track {
            position: relative;
            width: 100%;
            height: 100%;
            background-color: #e2e8f0;
            border-radius: 30px;
            overflow: hidden;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        /* State ketika disabled (Belum ada foto/lokasi) */
        .slide-track.disabled {
            opacity: 0.6;
            pointer-events: none;
            background-color: #f1f5f9;
        }

        /* State ketika sukses (Submitted) */
        .slide-track.submitted {
            background-color: #10b981; /* Green Success */
        }

        .slide-thumb {
            position: absolute;
            top: 4px;
            left: 4px;
            width: 52px;
            height: 52px;
            background-color: #1e293b; /* Dark Color */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            cursor: grab;
            z-index: 2;
            transition: left 0.1s linear, transform 0.2s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .slide-thumb:active {
            cursor: grabbing;
            transform: scale(0.95);
        }

        .slide-text {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 1px;
            z-index: 1;
            padding-left: 20px; /* Offset text agar tidak tertutup thumb di awal */
        }

        .slide-track.submitted .slide-text {
            color: white;
            padding-left: 0;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const photoInput = document.getElementById('photo-input');
            const captureBtn = document.getElementById('capture-btn');
            const previewImage = document.getElementById('preview-image');
            const cameraPreview = document.getElementById('camera-preview');
            const cameraPlaceholder = document.getElementById('camera-placeholder');
            const retakeBtn = document.getElementById('retake-btn');
            const locationStatus = document.getElementById('location-status');
            const locationDetails = document.getElementById('location-details');
            const form = document.getElementById('attendance-form');

            // Slide Elements
            const slideTrack = document.getElementById('slide-track');
            const slideThumb = document.getElementById('slide-thumb');
            const slideText = document.getElementById('slide-text');
            const slideHint = document.getElementById('slide-hint');
            
            let isDragging = false;
            let startX = 0;
            let currentX = 0;
            let trackWidth = 0;
            let thumbWidth = 0;
            let maxSlide = 0;
            let isSubmitted = false;

            // 1. Kamera Logic
            captureBtn.addEventListener('click', () => photoInput.click());
            photoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        cameraPreview.classList.remove('d-none');
                        cameraPlaceholder.classList.add('d-none');
                        retakeBtn.classList.remove('d-none');
                        checkValidity();
                    }
                    reader.readAsDataURL(file);
                }
            });

            retakeBtn.addEventListener('click', () => {
                photoInput.value = '';
                cameraPreview.classList.add('d-none');
                cameraPlaceholder.classList.remove('d-none');
                checkValidity();
            });

            // 2. Geolocation Logic
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
                        checkValidity();
                    },
                    (err) => { locationStatus.innerHTML = `<div class="text-danger small">Gagal: ${err.message}</div>`; },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            } else { locationStatus.innerHTML = "Browser tidak support GPS."; }

            // 3. Validasi & Unlock Slider
            function checkValidity() {
                const hasPhoto = photoInput.files.length > 0;
                const hasLoc = document.getElementById('latitude').value !== '';
                
                if (hasPhoto && hasLoc) {
                    slideTrack.classList.remove('disabled');
                    slideHint.textContent = "Geser tombol ke kanan untuk konfirmasi";
                    slideHint.classList.remove('text-muted');
                    slideHint.classList.add('text-dark');
                } else {
                    slideTrack.classList.add('disabled');
                    slideHint.textContent = "Ambil foto & lokasi untuk membuka kunci";
                    slideHint.classList.add('text-muted');
                }
            }

            // 4. Slider Logic (Touch & Mouse)
            function initSlider() {
                trackWidth = slideTrack.offsetWidth;
                thumbWidth = slideThumb.offsetWidth;
                maxSlide = trackWidth - thumbWidth - 8; // 8px margin safety
            }

            // Update on resize
            window.addEventListener('resize', initSlider);
            // Init immediately
            initSlider();

            function startDrag(e) {
                if (slideTrack.classList.contains('disabled') || isSubmitted) return;
                isDragging = true;
                startX = (e.type === 'touchstart') ? e.touches[0].clientX : e.clientX;
                slideThumb.style.transition = 'none'; // Disable transition for direct follow
            }

            function onDrag(e) {
                if (!isDragging || isSubmitted) return;
                
                const clientX = (e.type === 'touchmove') ? e.touches[0].clientX : e.clientX;
                let moveX = clientX - startX;

                // Batas gerak
                if (moveX < 0) moveX = 0;
                if (moveX > maxSlide) moveX = maxSlide;

                currentX = moveX;
                slideThumb.style.left = (4 + moveX) + 'px'; // 4px is initial offset

                // Opacity text effect
                const progress = moveX / maxSlide;
                slideText.style.opacity = 1 - progress;
            }

            function endDrag() {
                if (!isDragging || isSubmitted) return;
                isDragging = false;

                // Threshold 90%
                if (currentX >= maxSlide * 0.9) {
                    // Success!
                    isSubmitted = true;
                    slideThumb.style.left = (4 + maxSlide) + 'px';
                    slideTrack.classList.add('submitted');
                    slideThumb.innerHTML = '<i class="mdi mdi-check"></i>';
                    slideThumb.style.backgroundColor = '#fff';
                    slideThumb.style.color = '#10b981';
                    slideText.style.opacity = 1;
                    slideText.innerHTML = "MEMPROSES DATA...";
                    
                    // Submit Form
                    form.submit();
                } else {
                    // Snap back
                    slideThumb.style.transition = 'left 0.3s ease';
                    slideThumb.style.left = '4px';
                    slideText.style.opacity = 1;
                }
            }

            // Mouse Events
            slideThumb.addEventListener('mousedown', startDrag);
            document.addEventListener('mousemove', onDrag);
            document.addEventListener('mouseup', endDrag);

            // Touch Events (Mobile)
            slideThumb.addEventListener('touchstart', startDrag);
            document.addEventListener('touchmove', onDrag);
            document.addEventListener('touchend', endDrag);
        });
    </script>
@endpush