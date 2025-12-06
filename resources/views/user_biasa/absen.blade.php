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
                            <i class="mdi mdi-camera me-1"></i>Selfie
                        </div>
                    </div>

                    {{-- ========================================================= --}}
                    {{-- ALERT: Jika Absen Pulang Lintas Hari --}}
                    {{-- ========================================================= --}}
                    @if ($mode == 'pulang' && isset($attendance) && !$attendance->check_in_time->isToday())
                        <div class="alert alert-warning border-0 shadow-sm mb-4">
                            <div class="d-flex align-items-start">
                                <i class="mdi mdi-calendar-clock display-4 me-3"></i>
                                <div>
                                    <h5 class="fw-bold mb-1">Menutup Sesi Sebelumnya</h5>
                                    <p class="mb-0 small">
                                        Anda sedang melakukan <strong>Absen Pulang</strong> untuk sesi masuk tanggal: <br>
                                        <span class="badge bg-warning text-dark mt-1">
                                            {{ $attendance->check_in_time->translatedFormat('l, d F Y - H:i') }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-4">
                            Ambil foto selfie untuk melakukan absen <strong>{{ strtoupper($mode) }}</strong>.
                            Pastikan lokasi Anda akurat. Foto akan otomatis ditambahkan watermark timestamp.
                        </p>
                    @endif

                    <form class="forms-sample" action="{{ route('self.attend.store') }}" method="POST"
                        enctype="multipart/form-data" id="attendance-form">
                        @csrf

                        {{-- ========================================================= --}}
                        {{-- ID ABSENSI (Jika Mode Pulang) --}}
                        {{-- ========================================================= --}}
                        @if (isset($attendance) && $attendance)
                            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                        @endif

                        <div class="row mb-4">
                            {{-- KOLOM KIRI: KAMERA --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="fw-semibold mb-3">Ambil Foto Selfie <span
                                            class="text-danger">*</span></label>
                                    <div class="camera-container text-center">
                                        {{-- Area Preview Hasil Foto --}}
                                        <div id="camera-preview" class="camera-preview mb-3 d-none">
                                            <img id="preview-image" src="" alt="Preview Foto"
                                                class="img-fluid rounded shadow-sm">
                                            <div class="preview-overlay">
                                                <div class="watermark-timestamp" id="watermark-preview">
                                                    {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y H:i:s') }}
                                                </div>
                                            </div>
                                            <button type="button" id="retake-btn"
                                                class="btn btn-danger btn-sm mt-2 d-none">
                                                <i class="mdi mdi-camera-retake me-1"></i>Ambil Ulang
                                            </button>
                                        </div>

                                        {{-- Placeholder Ikon Kamera --}}
                                        <div id="camera-placeholder" class="camera-placeholder">
                                            <i class="mdi mdi-camera display-4 text-muted mb-3"></i>
                                            <p class="text-muted mb-3">Klik tombol di bawah untuk mengambil foto</p>
                                        </div>

                                        {{-- Tombol & Input --}}
                                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                                            {{-- Input File Hidden --}}
                                            <input type="file" name="photo" id="photo-input" class="d-none"
                                                accept="image/*" capture="user" required>

                                            {{-- Tombol Trigger --}}
                                            <button type="button" id="capture-btn" class="btn btn-dark">
                                                <i class="mdi mdi-camera me-1"></i>Ambil Foto
                                            </button>
                                        </div>

                                        <small class="text-muted d-block mt-2">
                                            <i class="mdi mdi-information-outline me-1"></i>
                                            Pastikan wajah terlihat jelas.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            {{-- KOLOM KANAN: LOKASI --}}
                            <div class="col-md-6">
                                <div class="location-info p-4 rounded bg-light">
                                    <h6 class="fw-semibold mb-3">
                                        <i class="mdi mdi-map-marker-outline me-2"></i>Informasi Lokasi
                                    </h6>

                                    <div id="location-status" class="alert alert-info mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-loading mdi-spin me-2"></i>
                                            <span>Sedang mengambil lokasi...</span>
                                        </div>
                                    </div>

                                    <div id="location-details" class="d-none">
                                        <div class="mb-2">
                                            <small class="text-muted">Koordinat:</small>
                                            <div class="fw-semibold" id="coordinates-display"></div>
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted">Akurasi:</small>
                                            <div class="fw-semibold" id="accuracy-display"></div>
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted">Waktu:</small>
                                            <div class="fw-semibold" id="time-display">
                                                {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y H:i:s') }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Input Hidden Location --}}
                                    <input type="hidden" id="latitude" name="latitude">
                                    <input type="hidden" id="longitude" name="longitude">
                                    <input type="hidden" id="accuracy" name="accuracy">
                                </div>
                            </div>
                        </div>

                        {{-- INPUT NOTES (CATATAN) --}}
                        <div class="form-group mb-4">
                            <label class="fw-semibold mb-2">Catatan Tambahan (Opsional)</label>
                            <textarea name="notes" class="form-control" rows="3" 
                                placeholder="Tambahkan catatan jika diperlukan... (Contoh: Meeting di luar, dsb)">{{ old('notes') }}</textarea>
                        </div>

                        {{-- TOMBOL AKSI --}}
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" id="submit-button" class="btn btn-dark" disabled>
                                <i class="mdi mdi-send me-1"></i>Kirim Absen {{ ucfirst($mode) }}
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-dark">
                                <i class="mdi mdi-close me-1"></i>Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .camera-container {
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            padding: 2rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }
        .camera-container:hover {
            border-color: #000;
            background: #f1f5f9;
        }
        .camera-preview {
            position: relative;
            max-width: 300px;
            margin: 0 auto;
        }
        .preview-overlay {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
            padding: 1rem;
            border-radius: 0 0 12px 12px;
        }
        .watermark-timestamp {
            color: white; font-size: 12px; font-weight: 500;
            text-align: center; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
        }
        .camera-placeholder { color: #64748b; }
        .location-info { border-left: 4px solid #000; }
        .btn { border-radius: 8px; font-weight: 500; transition: all 0.3s ease; }
        .btn-dark { background: #000; border: 2px solid #000; }
        .btn-dark:hover { background: #333; border-color: #333; transform: translateY(-1px); }
        .btn-outline-dark { border: 2px solid #000; color: #000; }
        .btn-outline-dark:hover { background: #000; color: white; transform: translateY(-1px); }
        .form-control { border-radius: 8px; border: 2px solid #e2e8f0; transition: all 0.3s ease; }
        .form-control:focus { border-color: #000; box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1); }
        #preview-image { max-height: 300px; object-fit: cover; border: 3px solid #000; }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Element Selector
            const photoInput = document.getElementById('photo-input');
            const captureBtn = document.getElementById('capture-btn');
            const previewImage = document.getElementById('preview-image');
            const cameraPreview = document.getElementById('camera-preview');
            const cameraPlaceholder = document.getElementById('camera-placeholder');
            const retakeBtn = document.getElementById('retake-btn');
            const submitButton = document.getElementById('submit-button');
            const locationStatus = document.getElementById('location-status');
            const locationDetails = document.getElementById('location-details');
            const coordinatesDisplay = document.getElementById('coordinates-display');
            const form = document.getElementById('attendance-form');

            // === 1. CEK KONEKSI INTERNET (UX Improvement) ===
            function updateOnlineStatus() {
                if (!navigator.onLine) {
                    locationStatus.innerHTML = `
                        <div class="alert alert-danger mb-0">
                            <i class="mdi mdi-wifi-off me-2"></i>
                            <strong>Anda Offline!</strong> Pastikan ada koneksi internet untuk mengirim absen.
                        </div>`;
                    submitButton.disabled = true;
                }
            }
            window.addEventListener('online',  getLocation); // Coba ambil lokasi lagi pas online
            window.addEventListener('offline', updateOnlineStatus);
            updateOnlineStatus();

            // === 2. GEOLOCATION DENGAN RETRY & TIMEOUT LEBIH PANJANG ===
            function getLocation() {
                if (navigator.geolocation) {
                    locationStatus.innerHTML = `<div class="text-info"><i class="mdi mdi-loading mdi-spin me-2"></i>Mencari lokasi (Akurasi tinggi)...</div>`;
                    
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            const acc = position.coords.accuracy;

                            document.getElementById('latitude').value = lat;
                            document.getElementById('longitude').value = lng;
                            document.getElementById('accuracy').value = acc;

                            coordinatesDisplay.innerHTML = `${lat.toFixed(6)}, ${lng.toFixed(6)} <br><span class="badge badge-success mt-1">Akurasi: ${Math.round(acc)}m</span>`;
                            
                            document.getElementById('accuracy-display').innerText = `± ${Math.round(acc)} meter`;
                            
                            locationStatus.innerHTML = `<div class="text-success"><i class="mdi mdi-check-circle me-2"></i>Lokasi Terkunci!</div>`;
                            locationDetails.classList.remove('d-none');
                            checkFormValidity();
                        },
                        (error) => {
                            let msg = "Gagal deteksi lokasi.";
                            if(error.code == 1) msg = "Izin lokasi ditolak. Mohon aktifkan GPS.";
                            else if(error.code == 2) msg = "Sinyal GPS lemah/tidak tersedia. Coba geser ke area terbuka.";
                            else if(error.code == 3) msg = "Waktu habis (Timeout). Coba refresh halaman.";
                            
                            locationStatus.innerHTML = `
                                <div class="alert alert-warning">
                                    <i class="mdi mdi-alert me-1"></i> ${msg} <br>
                                    <button type="button" onclick="window.location.reload()" class="btn btn-sm btn-outline-dark mt-2">Coba Lagi</button>
                                </div>`;
                        },
                        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 } // Timeout diperpanjang jadi 15 detik
                    );
                } else {
                    locationStatus.innerHTML = "Browser tidak mendukung Geolocation.";
                }
            }

            // === 3. LOGIKA KAMERA & KOMPRESI GAMBAR (PENTING!) ===
            captureBtn.addEventListener('click', () => photoInput.click());

            photoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Tampilkan loading
                    previewImage.src = '';
                    cameraPlaceholder.innerHTML = '<i class="mdi mdi-loading mdi-spin display-4"></i><p>Memproses Foto...</p>';
                    
                    // Kompresi Gambar menggunakan Canvas
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const img = new Image();
                        img.onload = function() {
                            const canvas = document.createElement('canvas');
                            const ctx = canvas.getContext('2d');

                            // Set ukuran maksimal (misal lebar 800px) agar file ringan
                            const MAX_WIDTH = 800;
                            const scaleSize = MAX_WIDTH / img.width;
                            canvas.width = MAX_WIDTH;
                            canvas.height = img.height * scaleSize;

                            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                            // Konversi ke format JPEG kualitas 70%
                            const dataUrl = canvas.toDataURL('image/jpeg', 0.7); 
                            
                            // Tampilkan Preview
                            previewImage.src = dataUrl;
                            cameraPreview.classList.remove('d-none');
                            cameraPlaceholder.classList.add('d-none');
                            retakeBtn.classList.remove('d-none');

                            // Ganti file input asli dengan file hasil kompresi (Opsional, tapi lebih baik kirim base64 atau blob)
                            // Di sini kita biarkan input file asli, tapi kita bisa inject hidden input jika mau full JS submit.
                            // Namun, trik paling mudah untuk form standard: 
                            // Kita convert dataUrl kembali ke File object dan replace input (sedikit tricky)
                            // ATAU: Kita biarkan PHP handle resize (seperti kodemu sekarang), 
                            // TAPI resize canvas di atas memastikan user melihat preview cepat.
                            
                            checkFormValidity();
                        }
                        img.src = event.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });

            retakeBtn.addEventListener('click', () => {
                cameraPreview.classList.add('d-none');
                cameraPlaceholder.classList.remove('d-none');
                cameraPlaceholder.innerHTML = '<i class="mdi mdi-camera display-4 text-muted mb-3"></i><p class="text-muted mb-3">Klik tombol untuk foto</p>';
                retakeBtn.classList.add('d-none');
                photoInput.value = ''; // Reset input
                checkFormValidity();
            });

            // === 4. VALIDASI TOMBOL KIRIM ===
            function checkFormValidity() {
                const hasPhoto = photoInput.files.length > 0;
                const hasLocation = document.getElementById('latitude').value !== '';
                
                if (hasPhoto && hasLocation) {
                    submitButton.disabled = false;
                    submitButton.classList.remove('btn-secondary');
                    submitButton.classList.add('btn-dark');
                } else {
                    submitButton.disabled = true;
                }
            }
            
            // Initial call
            getLocation();
        });
    </script>
@endpush