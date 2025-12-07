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

                    {{-- ========================================================= --}}
                    {{-- ALERT KHUSUS: LEMBUR / LINTAS HARI --}}
                    {{-- ========================================================= --}}
                    @if ($mode == 'pulang' && isset($attendance))
                        @php
                            $checkInDate = \Carbon\Carbon::parse($attendance->check_in_time);
                            $isNextDay = !$checkInDate->isToday();
                        @endphp

                        @if ($isNextDay)
                            <div class="alert alert-warning border-0 shadow-sm mb-4">
                                <div class="d-flex align-items-start">
                                    <i class="mdi mdi-weather-night display-4 me-3"></i>
                                    <div>
                                        <h5 class="fw-bold mb-1">Pulang Lintas Hari (Lembur)</h5>
                                        <p class="mb-0 small">
                                            Anda sedang melakukan absen <strong>PULANG</strong> untuk sesi kemarin.<br>
                                            Waktu Masuk: <span class="badge bg-warning text-dark">{{ $checkInDate->translatedFormat('l, d F Y - H:i') }}</span>
                                        </p>
                                        <p class="mb-0 small mt-1 text-muted">
                                            <em>Setelah klik kirim, sesi ini akan ditutup. Anda bisa absen masuk lagi untuk hari ini setelahnya.</em>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info border-0 shadow-sm mb-4">
                                <i class="mdi mdi-clock-outline me-2"></i>
                                Anda masuk pukul: <strong>{{ $attendance->check_in_time->format('H:i') }}</strong>
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

                        {{-- ID ABSENSI (Wajib untuk Pulang) --}}
                        @if (isset($attendance) && $attendance)
                            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                        @endif

                        <div class="row mb-4">
                            {{-- KOLOM KIRI: KAMERA --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="fw-semibold mb-3">Foto Selfie <span class="text-danger">*</span></label>
                                    <div class="camera-container text-center">
                                        {{-- Preview Foto --}}
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
                                            <p class="text-muted mb-3">Klik tombol di bawah untuk ambil foto</p>
                                        </div>

                                        {{-- Tombol Trigger --}}
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
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Koordinat</small>
                                            <span class="fw-bold font-monospace" id="coordinates-display">-</span>
                                        </div>
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Akurasi GPS</small>
                                            <span class="badge badge-outline-success" id="accuracy-display">-</span>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Waktu Server</small>
                                            <span class="fw-bold">{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('H:i:s') }}</span>
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
                            <textarea name="notes" class="form-control" rows="3" 
                                placeholder="Contoh: Lembur mengerjakan stok opname...">{{ old('notes') }}</textarea>
                        </div>

                        {{-- TOMBOL SUBMIT --}}
                        <div class="d-flex gap-2">
                            <button type="submit" id="submit-button" class="btn btn-dark btn-lg w-100" disabled>
                                <i class="mdi mdi-send me-2"></i>Kirim Absen {{ ucfirst($mode) }}
                            </button>
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('dashboard') }}" class="text-muted small text-decoration-none">Batal & Kembali</a>
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
            const submitButton = document.getElementById('submit-button');
            const locationStatus = document.getElementById('location-status');
            const locationDetails = document.getElementById('location-details');
            
            // 1. Logic Kamera (Canvas Resize)
            captureBtn.addEventListener('click', () => photoInput.click());
            
            photoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Preview sederhana pakai FileReader (Bisa ditambah logic compress canvas dari kode sebelumnya)
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

            // 2. Logic Geolocation
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
                    (err) => {
                        locationStatus.innerHTML = `<div class="text-danger small">Gagal: ${err.message}</div>`;
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            } else {
                locationStatus.innerHTML = "Browser tidak support GPS.";
            }

            // 3. Check Validity
            function checkValidity() {
                const hasPhoto = photoInput.files.length > 0;
                const hasLoc = document.getElementById('latitude').value !== '';
                submitButton.disabled = !(hasPhoto && hasLoc);
            }
        });
    </script>
@endpush