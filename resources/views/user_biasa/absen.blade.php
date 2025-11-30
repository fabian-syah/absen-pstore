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
                    {{-- INFO ALERT: Jika Absen Pulang Lintas Hari (Lembur) --}}
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
                        {{-- FIX PENTING: KIRIM ID ABSENSI JIKA MODE PULANG --}}
                        {{-- Ini agar Controller tahu kita mau update sesi yg mana --}}
                        {{-- ========================================================= --}}
                        @if (isset($attendance) && $attendance)
                            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                        @endif

                        {{-- Preview Section --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="fw-semibold mb-3">Ambil Foto Selfie <span
                                            class="text-danger">*</span></label>
                                    <div class="camera-container text-center">
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

                                        <div id="camera-placeholder" class="camera-placeholder">
                                            <i class="mdi mdi-camera display-4 text-muted mb-3"></i>
                                            <p class="text-muted mb-3">Klik tombol di bawah untuk mengambil foto</p>
                                        </div>

                                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                                            {{-- Input File Hidden (Wajib ada untuk capture) --}}
                                            <input type="file" name="photo" id="photo-input" class="d-none"
                                                accept="image/*" capture="user" required>

                                            {{-- Tombol Ambil Foto Saja --}}
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

                            {{-- Location Info --}}
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

                                    {{-- Input tersembunyi untuk Lokasi --}}
                                    <input type="hidden" id="latitude" name="latitude">
                                    <input type="hidden" id="longitude" name="longitude">
                                    <input type="hidden" id="accuracy" name="accuracy">
                                </div>
                            </div>
                        </div>

                        {{-- Additional Information --}}
                        <div class="form-group mb-4">
                            <label class="fw-semibold mb-2">Catatan Tambahan (Opsional)</label>
                            <textarea name="notes" class="form-control" rows="3"
                                placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                        </div>

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
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
            padding: 1rem;
            border-radius: 0 0 12px 12px;
        }

        .watermark-timestamp {
            color: white;
            font-size: 12px;
            font-weight: 500;
            text-align: center;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
        }

        .camera-placeholder {
            color: #64748b;
        }

        .location-info {
            border-left: 4px solid #000;
        }

        .btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-dark {
            background: #000;
            border: 2px solid #000;
        }

        .btn-dark:hover {
            background: #333;
            border-color: #333;
            transform: translateY(-1px);
        }

        .btn-outline-dark {
            border: 2px solid #000;
            color: #000;
        }

        .btn-outline-dark:hover {
            background: #000;
            color: white;
            transform: translateY(-1px);
        }

        .form-control {
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #000;
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
        }

        .alert {
            border-radius: 8px;
            border: none;
        }

        #preview-image {
            max-height: 300px;
            object-fit: cover;
            border: 3px solid #000;
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
            const submitButton = document.getElementById('submit-button');
            const locationStatus = document.getElementById('location-status');
            const locationDetails = document.getElementById('location-details');
            const coordinatesDisplay = document.getElementById('coordinates-display');
            const accuracyDisplay = document.getElementById('accuracy-display');
            const timeDisplay = document.getElementById('time-display');
            const watermarkPreview = document.getElementById('watermark-preview');

            // Variable MODE dari PHP Controller
            const attendanceMode = "{{ ucfirst($mode) }}";

            // Update timestamp every second
            function updateTimestamp() {
                const now = new Date();
                const options = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    timeZone: 'Asia/Jakarta'
                };
                const formatter = new Intl.DateTimeFormat('id-ID', options);
                const timestamp = formatter.format(now);

                if (watermarkPreview) {
                    watermarkPreview.textContent = timestamp;
                }
                if (timeDisplay) {
                    timeDisplay.textContent = timestamp;
                }
            }

            setInterval(updateTimestamp, 1000);
            updateTimestamp();

            // Camera capture button
            captureBtn.addEventListener('click', function() {
                photoInput.click();
            });

            // Retake photo button
            retakeBtn.addEventListener('click', function() {
                cameraPreview.classList.add('d-none');
                cameraPlaceholder.classList.remove('d-none');
                retakeBtn.classList.add('d-none');
                photoInput.value = '';
                updateSubmitButton();
            });

            // Preview image when file is selected
            photoInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        cameraPreview.classList.remove('d-none');
                        cameraPlaceholder.classList.add('d-none');
                        retakeBtn.classList.remove('d-none');
                        updateSubmitButton();
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Get user location
            function getLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            // Success
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            const accuracy = position.coords.accuracy;

                            document.getElementById('latitude').value = lat;
                            document.getElementById('longitude').value = lng;
                            document.getElementById('accuracy').value = accuracy;

                            coordinatesDisplay.textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                            accuracyDisplay.textContent = `± ${Math.round(accuracy)} meter`;

                            locationStatus.innerHTML = `
                                <div class="d-flex align-items-center text-success">
                                    <i class="mdi mdi-check-circle-outline me-2"></i>
                                    <span>Lokasi berhasil didapat!</span>
                                </div>
                            `;
                            locationDetails.classList.remove('d-none');

                            updateSubmitButton();
                        },
                        function(error) {
                            // Error
                            let errorMessage = 'Gagal mengambil lokasi. ';
                            switch (error.code) {
                                case error.PERMISSION_DENIED:
                                    errorMessage += 'Izinkan akses lokasi di browser Anda.';
                                    break;
                                case error.POSITION_UNAVAILABLE:
                                    errorMessage += 'Informasi lokasi tidak tersedia.';
                                    break;
                                case error.TIMEOUT:
                                    errorMessage += 'Permintaan lokasi timeout.';
                                    break;
                                default:
                                    errorMessage += 'Error tidak diketahui.';
                            }

                            locationStatus.innerHTML = `
                                <div class="d-flex align-items-center text-danger">
                                    <i class="mdi mdi-alert-circle-outline me-2"></i>
                                    <span>${errorMessage}</span>
                                </div>
                            `;
                        }, {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 60000
                        }
                    );
                } else {
                    locationStatus.innerHTML = `
                        <div class="d-flex align-items-center text-danger">
                            <i class="mdi mdi-alert-circle-outline me-2"></i>
                            <span>Browser Anda tidak mendukung Geolocation.</span>
                        </div>
                    `;
                }
            }

            // Update submit button state
            function updateSubmitButton() {
                const hasPhoto = !cameraPreview.classList.contains('d-none');
                const hasLocation = document.getElementById('latitude').value !== '';

                submitButton.disabled = !(hasPhoto && hasLocation);

                const btnText = `<i class="mdi mdi-send me-1"></i>Kirim Absen ${attendanceMode}`;

                if (submitButton.disabled) {
                    submitButton.innerHTML = btnText;
                } else {
                    submitButton.innerHTML = btnText;
                }
            }

            // Form submission
            document.getElementById('attendance-form').addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i>Mengirim...';
                submitBtn.disabled = true;
            });

            // Initialize
            getLocation();
            updateSubmitButton();
        });
    </script>
@endpush