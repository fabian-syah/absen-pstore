<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Security Scanner - AbsenPS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --safe-area-inset-bottom: env(safe-area-inset-bottom, 0px);
        }
        
        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }
        
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            touch-action: manipulation;
        }

        body {
            background-color: #000;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .scanner-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: black;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 10;
        }

        #reader {
            width: 100%;
            height: 100%;
            background: black;
            position: relative;
            overflow: hidden;
        }

        #reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            transform: scaleX(-1); /* Mirror untuk kamera depan */
        }

        .scanner-header {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            padding: 20px;
            padding-top: max(20px, env(safe-area-inset-top));
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.9), transparent);
            color: white;
            z-index: 20;
            text-align: center;
        }

        .scan-area {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: min(250px, 70vw);
            height: min(250px, 70vw);
            z-index: 15;
            pointer-events: none;
            border: 2px solid rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);
        }

        .scan-laser {
            position: absolute;
            width: 100%;
            height: 2px;
            background: #00ff00;
            box-shadow: 0 0 8px #00ff00, 0 0 16px #00ff00;
            top: 50%;
            animation: scan 2s infinite ease-in-out;
        }

        @keyframes scan {
            0%, 100% {
                top: 10%;
                opacity: 0;
            }
            50% {
                top: 90%;
                opacity: 1;
            }
        }

        .permission-btn-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 30;
            text-align: center;
            display: none;
            width: 90%;
            max-width: 300px;
            padding: 20px;
        }

        .verification-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #121212;
            z-index: 50;
            display: none;
            flex-direction: column;
            padding: 20px;
            padding-top: max(20px, env(safe-area-inset-top));
            padding-bottom: max(20px, env(safe-area-inset-bottom));
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .profile-card {
            background: #1e1e1e;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            color: white;
            border: 1px solid #333;
            margin-bottom: 20px;
        }

        .profile-img-db {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid #00aa13;
            object-fit: cover;
        }

        .camera-preview-box {
            width: 100%;
            height: 300px;
            max-height: 50vh;
            background: black;
            border-radius: 15px;
            overflow: hidden;
            position: relative;
            border: 2px solid #444;
            margin-bottom: 20px;
        }

        #camera-stream {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #camera-canvas {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 10px;
        }

        .btn-absen {
            padding: 15px;
            font-weight: bold;
            border-radius: 12px;
            border: none;
            color: white;
            font-size: 1rem;
            transition: transform 0.1s;
            min-height: 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .btn-absen:active {
            transform: scale(0.98);
        }

        .btn-masuk {
            background: linear-gradient(45deg, #00b09b, #96c93d);
        }

        .btn-pulang {
            background: linear-gradient(45deg, #ff5f6d, #ffc371);
        }

        .btn-capture {
            width: 100%;
            padding: 18px;
            border-radius: 12px;
            font-weight: bold;
            background: white;
            color: black;
            border: none;
            font-size: 1rem;
            min-height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-retake {
            width: 100%;
            padding: 15px;
            border-radius: 12px;
            font-weight: bold;
            background: #333;
            color: white;
            border: 1px solid #555;
            margin-bottom: 10px;
            min-height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .result-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.98);
            z-index: 100;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding: 20px;
            padding-top: max(20px, env(safe-area-inset-top));
            padding-bottom: max(90px, calc(20px + env(safe-area-inset-bottom))); /* Tambah space untuk tombol */
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .result-content {
            width: 100%;
            max-width: 500px;
            margin: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* NEW STYLES FOR RESULT PROFILE */
        .result-profile-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            max-width: 400px;
        }

        .result-profile-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid white;
            object-fit: cover;
            margin: 0 auto 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        }

        .user-info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.95rem;
            flex-wrap: wrap;
        }

        .user-info-label {
            color: #aaa;
            text-align: left;
            flex: 1;
            min-width: 120px;
        }

        .user-info-value {
            color: white;
            font-weight: 500;
            text-align: right;
            flex: 1;
            min-width: 150px;
        }

        .time-badge {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: white;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1.1rem;
            margin: 15px 0;
            display: inline-block;
            text-align: center;
        }

        .notes-box {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
            text-align: left;
            border-left: 4px solid #00ff00;
            width: 100%;
        }

        .notes-label {
            color: #aaa;
            font-size: 0.9rem;
            margin-bottom: 5px;
            display: block;
        }

        .notes-text {
            color: white;
            font-weight: 500;
            word-break: break-word;
        }

        /* FIXED BOTTOM BUTTONS FOR MOBILE */
        .result-actions {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 20px;
            padding-bottom: max(20px, env(safe-area-inset-bottom));
            background: linear-gradient(to top, rgba(0, 0, 0, 0.95), transparent);
            z-index: 101;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .result-actions .btn {
            height: 56px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 20px;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .scanner-header {
                padding: 15px;
                padding-top: max(15px, env(safe-area-inset-top));
            }
            
            .scanner-header h5 {
                font-size: 1.1rem;
            }
            
            .verification-overlay {
                padding: 15px;
                padding-top: max(15px, env(safe-area-inset-top));
                padding-bottom: max(15px, env(safe-area-inset-bottom));
            }
            
            .camera-preview-box {
                height: 250px;
            }
            
            .profile-card, .result-profile-card {
                padding: 15px;
            }
            
            .result-profile-img {
                width: 80px;
                height: 80px;
            }
            
            .user-info-row {
                font-size: 0.85rem;
            }
            
            .user-info-label, .user-info-value {
                min-width: 100px;
            }
            
            .btn-absen, .btn-capture, .btn-retake {
                font-size: 0.95rem;
                padding: 12px;
            }
            
            .result-overlay {
                padding: 15px;
                padding-top: max(15px, env(safe-area-inset-top));
                padding-bottom: max(80px, calc(15px + env(safe-area-inset-bottom)));
            }
            
            .result-actions {
                padding: 15px;
                padding-bottom: max(15px, env(safe-area-inset-bottom));
            }
        }

        @media (max-height: 700px) {
            .camera-preview-box {
                height: 200px;
            }
            
            .profile-card {
                margin-bottom: 15px;
                padding: 15px;
            }
            
            .profile-img-db {
                width: 60px;
                height: 60px;
            }
        }

        /* Landscape mode adjustments */
        @media (orientation: landscape) and (max-height: 500px) {
            .scanner-header {
                padding: 10px;
                padding-top: max(10px, env(safe-area-inset-top));
            }
            
            .verification-overlay {
                flex-direction: row;
                flex-wrap: wrap;
                align-items: flex-start;
            }
            
            .profile-card {
                width: 40%;
                margin-right: 10px;
                margin-bottom: 10px;
            }
            
            .camera-preview-box {
                width: 55%;
                height: 200px;
                margin-bottom: 10px;
            }
            
            #step-confirm-btn {
                width: 100%;
                margin-top: 10px;
            }
        }
    </style>
</head>

<body>

    <div class="scanner-wrapper" id="qrSection">
        <div class="scanner-header">
            <h5 class="m-0 fw-bold"><i class="fas fa-qrcode me-2"></i>Scan Absensi</h5>
            <small class="text-white-50">Arahkan kamera ke QR Code</small>
        </div>
        <div id="reader"></div>
        <div class="scan-area">
            <div class="scan-laser"></div>
        </div>
        <div class="permission-btn-container" id="permissionBtn">
            <i class="fas fa-camera-slash display-4 text-white mb-3"></i>
            <h5 class="text-white mb-3">Kamera Tidak Aktif</h5>
            <button class="btn btn-success rounded-pill px-4 py-2" onclick="startQRScanner()">
                <i class="fas fa-camera me-2"></i>Mulai Kamera
            </button>
            <p class="text-white-50 mt-3 small">iOS memerlukan izin HTTPS atau Localhost</p>
        </div>
    </div>

    <div class="verification-overlay" id="verifSection">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="text-white m-0"><i class="fas fa-user-check me-2"></i>Konfirmasi Absensi</h5>
            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="resetScan()">
                <i class="fas fa-times me-1"></i> Batal
            </button>
        </div>
        <div class="profile-card">
            <img src="" id="dbPhoto" class="profile-img-db mb-3" alt="User">
            <h4 id="dbName" class="fw-bold m-0">Nama Karyawan</h4>
            <p id="dbRole" class="text-muted small m-0">Jabatan</p>
            <span id="dbBranch" class="badge bg-primary mt-2">Cabang</span>
        </div>

        <div class="text-white mb-2 fw-bold small text-uppercase">
            <i class="fas fa-camera me-1"></i> Ambil Foto Bukti (Wajib)
        </div>

        <div class="camera-preview-box">
            <video id="camera-stream" autoplay playsinline muted></video>
            <canvas id="camera-canvas"></canvas>
        </div>

        <div id="step-capture-btn">
            <button class="btn-capture" onclick="capturePhoto()">
                <i class="fas fa-camera fa-lg me-2"></i> AMBIL FOTO
            </button>
        </div>

        <div id="step-confirm-btn" style="display: none;">
            <div class="form-group mb-3 text-start">
                <label for="scanNotes" class="text-white small fw-bold mb-1">
                    <i class="fas fa-sticky-note me-1"></i>Catatan (Opsional)
                </label>
                <textarea id="scanNotes" class="form-control bg-dark text-white border-secondary" rows="2"
                    placeholder="Contoh: Lembur, Tugas Luar, dll..."></textarea>
            </div>

            <button class="btn-retake" onclick="retakePhoto()">
                <i class="fas fa-redo me-2"></i> FOTO ULANG
            </button>
            <div class="action-buttons">
                <button class="btn-absen btn-masuk" onclick="submitAttendance('masuk')">
                    <i class="fas fa-sign-in-alt fa-lg mb-2"></i>
                    <span>MASUK</span>
                </button>
                <button class="btn-absen btn-pulang" onclick="submitAttendance('pulang')">
                    <i class="fas fa-sign-out-alt fa-lg mb-2"></i>
                    <span>PULANG</span>
                </button>
            </div>
        </div>
    </div>

    <div class="result-overlay" id="resultOverlay">
        <div class="result-content">
            <!-- Icon Status -->
            <div id="resultIcon" class="mb-3" style="font-size: 4rem;"></div>
            <h2 id="resultTitle" class="fw-bold mb-3"></h2>
            
            <!-- NEW: Profile Information Card -->
            <div class="result-profile-card" id="resultProfileCard">
                <img id="resultUserPhoto" src="" class="result-profile-img" alt="User Photo">
                <h3 id="resultUserName" class="fw-bold mb-1"></h3>
                <p id="resultUserRole" class="text-white-50 mb-3"></p>
                
                <div class="user-info-row">
                    <div class="user-info-label">Divisi</div>
                    <div class="user-info-value" id="resultUserDivision"></div>
                </div>
                <div class="user-info-row">
                    <div class="user-info-label">Cabang</div>
                    <div class="user-info-value" id="resultUserBranch"></div>
                </div>
                <div class="user-info-row">
                    <div class="user-info-label">Tipe Absen</div>
                    <div class="user-info-value" id="resultAttendanceType"></div>
                </div>
                <div class="user-info-row">
                    <div class="user-info-label">Waktu</div>
                    <div class="user-info-value" id="resultAttendanceTime"></div>
                </div>
                <div class="user-info-row">
                    <div class="user-info-label">Tanggal</div>
                    <div class="user-info-value" id="resultAttendanceDate"></div>
                </div>
                
                <!-- Status Terlambat/Pulang Cepat -->
                <div id="resultStatusBadge" class="mt-3 text-center"></div>
                
                <!-- Catatan -->
                <div id="resultNotesSection" class="notes-box" style="display: none;">
                    <div class="notes-label">Catatan:</div>
                    <div class="notes-text" id="resultNotesText"></div>
                </div>
            </div>
            
            <!-- Attendance Photo -->
            <img id="capturedPhoto" src=""
                style="width: 100%; max-width: 300px; height: 200px; object-fit: cover; border-radius: 15px; border: 4px solid white; display: none; box-shadow: 0 10px 30px rgba(0,0,0,0.5);"
                class="mx-auto mb-4">
        </div>
        
        <!-- FIXED BOTTOM BUTTONS - ALWAYS VISIBLE ON MOBILE -->
        <div class="result-actions">
            <button class="btn btn-light w-100 py-3 rounded-pill fw-bold text-uppercase" onclick="resetScan()">
                <i class="fas fa-qrcode me-2"></i> Scan Selanjutnya
            </button>
            
            <a href="{{ url('/dashboard') }}"
                class="btn btn-outline-light w-100 py-3 rounded-pill fw-bold text-uppercase">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let html5QrCode = null;
        let currentUserId = null;
        let streamRef = null;
        let capturedImageBase64 = null;
        let currentUserData = null;

        // Fungsi untuk mendeteksi device mobile
        function isMobileDevice() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }

        // Fungsi untuk mengatur height yang aman untuk mobile
        function setSafeHeight() {
            if (isMobileDevice()) {
                const vh = window.innerHeight * 0.01;
                document.documentElement.style.setProperty('--vh', `${vh}px`);
                
                // Set height untuk elemen yang perlu full height
                const fullHeightElements = document.querySelectorAll('.scanner-wrapper, .verification-overlay, .result-overlay');
                fullHeightElements.forEach(el => {
                    el.style.height = window.innerHeight + 'px';
                });
            }
        }

        // Handle orientation change dan resize
        window.addEventListener('resize', setSafeHeight);
        window.addEventListener('orientationchange', function() {
            setTimeout(setSafeHeight, 100);
        });

        // Initialize safe height
        document.addEventListener('DOMContentLoaded', function() {
            setSafeHeight();
            startQRScanner();
        });

        function startQRScanner() {
            document.getElementById('permissionBtn').style.display = 'none';
            if (html5QrCode) {
                html5QrCode.clear().catch(err => {}).finally(() => {
                    initScanner();
                });
            } else {
                initScanner();
            }
        }

        function initScanner() {
            html5QrCode = new Html5Qrcode("reader", {
                experimentalFeatures: {
                    useBarCodeDetectorIfSupported: true
                },
                verbose: false
            });
            
            const qrConfig = {
                fps: 20,
                qrbox: {
                    width: 250,
                    height: 250
                },
                aspectRatio: 1.0,
                formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE]
            };

            html5QrCode.start({
                    facingMode: "environment"
                }, qrConfig, onScanSuccess, onScanFailure)
                .catch(err => {
                    console.error("Gagal start scanner:", err);
                    document.getElementById('permissionBtn').style.display = 'block';
                    
                    // Tampilkan pesan error yang lebih user-friendly
                    let errorMsg = "Gagal mengakses kamera. ";
                    if (err.toString().includes('Permission')) {
                        errorMsg += "Mohon berikan izin akses kamera di pengaturan browser Anda.";
                    } else if (err.toString().includes('NotFound')) {
                        errorMsg += "Kamera tidak ditemukan.";
                    } else {
                        errorMsg += "Error: " + err.toString();
                    }
                    
                    document.getElementById('permissionBtn').querySelector('h5').textContent = errorMsg;
                });
        }

        function onScanSuccess(decodedText, decodedResult) {
            // Hentikan scanner terlebih dahulu
            html5QrCode.stop().then(() => {
                html5QrCode.clear();
                checkUser(decodedText);
            }).catch(err => {
                console.error("Error stopping scanner:", err);
                html5QrCode.clear();
                checkUser(decodedText);
            });
        }

        function onScanFailure(error) {
            // Biarkan kosong, scanner akan terus berjalan
        }

        function checkUser(qrCode) {
            // Tampilkan loading state
            const scannerHeader = document.querySelector('.scanner-header');
            scannerHeader.innerHTML = `
                <div class="d-flex flex-column align-items-center">
                    <div class="spinner-border text-white mb-2" role="status"></div>
                    <h5 class="m-0 fw-bold">Memproses QR Code...</h5>
                </div>
            `;

            fetch("{{ route('security.check-user') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken
                    },
                    body: JSON.stringify({
                        qr_code: qrCode
                    })
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        currentUserData = data.data;
                        showVerificationPage(data.data);
                    } else {
                        alert(data.message);
                        resetToScanner();
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('Gagal terhubung ke server. Periksa koneksi internet Anda.');
                    resetToScanner();
                });
        }

        function resetToScanner() {
            // Reset scanner header
            document.querySelector('.scanner-header').innerHTML = `
                <h5 class="m-0 fw-bold"><i class="fas fa-qrcode me-2"></i>Scan Absensi</h5>
                <small class="text-white-50">Arahkan kamera ke QR Code</small>
            `;
            startQRScanner();
        }

        function showVerificationPage(user) {
            currentUserId = user.id;
            document.getElementById('dbName').innerText = user.name;
            document.getElementById('dbRole').innerText = user.role + ' - ' + user.division;
            document.getElementById('dbBranch').innerText = user.branch;
            document.getElementById('dbPhoto').src = user.photo_url;

            // Reset UI State
            retakePhoto();
            document.getElementById('scanNotes').value = '';

            // Hide QR section, show verification
            document.getElementById('qrSection').style.display = 'none';
            document.getElementById('verifSection').style.display = 'flex';

            // Mulai kamera setelah delay kecil
            setTimeout(startCameraStream, 300);
        }

        function startCameraStream() {
            const video = document.getElementById('camera-stream');
            
            // Stop any existing stream
            if (streamRef) {
                streamRef.getTracks().forEach(track => track.stop());
            }

            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: "environment",
                            width: { ideal: 640 },
                            height: { ideal: 480 }
                        },
                        audio: false
                    })
                    .then(function(stream) {
                        streamRef = stream;
                        video.srcObject = stream;
                        
                        // Play video dengan promise untuk handle error
                        video.play()
                            .then(() => {
                                console.log("Kamera berhasil dijalankan");
                            })
                            .catch(err => {
                                console.error("Error playing video:", err);
                                alert("Kamera tidak bisa dijalankan. Mohon periksa izin kamera.");
                            });
                    })
                    .catch(function(error) {
                        console.error("Error accessing camera:", error);
                        let errorMsg = "Tidak dapat mengakses kamera. ";
                        
                        if (error.name === 'NotAllowedError') {
                            errorMsg += "Izin kamera ditolak. Mohon berikan izin di pengaturan browser.";
                        } else if (error.name === 'NotFoundError') {
                            errorMsg += "Kamera tidak ditemukan.";
                        } else if (error.name === 'NotReadableError') {
                            errorMsg += "Kamera sedang digunakan oleh aplikasi lain.";
                        } else {
                            errorMsg += error.toString();
                        }
                        
                        alert(errorMsg);
                        
                        // Fallback: kembali ke scanner
                        setTimeout(() => {
                            resetScan();
                        }, 2000);
                    });
            } else {
                alert("Browser tidak mendukung akses kamera.");
                resetScan();
            }
        }

        function capturePhoto() {
            const video = document.getElementById('camera-stream');
            const canvas = document.getElementById('camera-canvas');
            const context = canvas.getContext('2d');

            if (!video.videoWidth || !video.videoHeight) {
                alert("Kamera belum siap. Tunggu sebentar.");
                return;
            }

            // Set canvas size sesuai video
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            // Draw image ke canvas
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Convert ke base64 dengan kualitas 0.8
            capturedImageBase64 = canvas.toDataURL('image/jpeg', 0.8);

            // Ubah tampilan: sembunyikan video, tampilkan canvas
            video.style.display = 'none';
            canvas.style.display = 'block';

            // Ganti tombol
            document.getElementById('step-capture-btn').style.display = 'none';
            document.getElementById('step-confirm-btn').style.display = 'block';
            
            // Scroll ke bawah untuk memastikan tombol terlihat
            setTimeout(() => {
                window.scrollTo(0, document.body.scrollHeight);
            }, 100);
        }

        function retakePhoto() {
            const video = document.getElementById('camera-stream');
            const canvas = document.getElementById('camera-canvas');

            capturedImageBase64 = null;

            // Reset tampilan: tampilkan video, sembunyikan canvas
            video.style.display = 'block';
            canvas.style.display = 'none';

            // Reset tombol
            document.getElementById('step-capture-btn').style.display = 'block';
            document.getElementById('step-confirm-btn').style.display = 'none';
            document.getElementById('scanNotes').value = '';
        }

        function submitAttendance(type) {
            if (!capturedImageBase64) {
                alert("Silakan ambil foto terlebih dahulu.");
                return;
            }

            const btn = document.querySelector(`.btn-${type}`);
            const originalContent = btn.innerHTML;
            const notes = document.getElementById('scanNotes').value;

            // Disable semua tombol
            document.querySelectorAll('.btn-absen, .btn-retake').forEach(b => {
                b.disabled = true;
                b.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
            });

            // Kirim data ke server
            fetch("{{ route('security.store-attendance') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken
                    },
                    body: JSON.stringify({
                        user_id: currentUserId,
                        type: type,
                        image: capturedImageBase64,
                        notes: notes
                    })
                })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) {
                        throw new Error(data.message || 'Server error');
                    }
                    return data;
                })
                .then(data => {
                    if (data.status === 'success') {
                        showResult('success', data.message, data.data, type);
                    } else {
                        throw new Error(data.message);
                    }
                })
                .catch(err => {
                    alert("Error: " + err.message);
                    
                    // Reset tombol ke state semula
                    document.querySelectorAll('.btn-absen, .btn-retake').forEach(b => {
                        b.disabled = false;
                        if (b.classList.contains('btn-masuk')) {
                            b.innerHTML = '<i class="fas fa-sign-in-alt fa-lg mb-2"></i><span>MASUK</span>';
                        } else if (b.classList.contains('btn-pulang')) {
                            b.innerHTML = '<i class="fas fa-sign-out-alt fa-lg mb-2"></i><span>PULANG</span>';
                        } else {
                            b.innerHTML = '<i class="fas fa-redo me-2"></i> FOTO ULANG';
                        }
                    });
                });
        }

        function showResult(status, message, responseData, attendanceType) {
            // Stop semua stream kamera
            if (streamRef) {
                streamRef.getTracks().forEach(track => track.stop());
                streamRef = null;
            }

            const overlay = document.getElementById('resultOverlay');
            const icon = document.getElementById('resultIcon');
            const title = document.getElementById('resultTitle');
            
            // Get semua elemen untuk profile
            const resultUserPhoto = document.getElementById('resultUserPhoto');
            const resultUserName = document.getElementById('resultUserName');
            const resultUserRole = document.getElementById('resultUserRole');
            const resultUserDivision = document.getElementById('resultUserDivision');
            const resultUserBranch = document.getElementById('resultUserBranch');
            const resultAttendanceType = document.getElementById('resultAttendanceType');
            const resultAttendanceTime = document.getElementById('resultAttendanceTime');
            const resultAttendanceDate = document.getElementById('resultAttendanceDate');
            const resultStatusBadge = document.getElementById('resultStatusBadge');
            const resultNotesSection = document.getElementById('resultNotesSection');
            const resultNotesText = document.getElementById('resultNotesText');
            const capturedPhoto = document.getElementById('capturedPhoto');

            // Tampilkan overlay
            overlay.style.display = 'flex';
            
            // Scroll ke atas
            overlay.scrollTop = 0;

            if (status === 'success') {
                icon.innerHTML = '<i class="fas fa-check-circle text-success"></i>';
                title.innerText = "ABSEN BERHASIL";
                title.className = "fw-bold mb-2 text-success";
                
                // Set data profil
                if (currentUserData) {
                    resultUserPhoto.src = currentUserData.photo_url;
                    resultUserName.innerText = currentUserData.name;
                    resultUserRole.innerText = currentUserData.role + ' - ' + currentUserData.division;
                    resultUserDivision.innerText = currentUserData.division;
                    resultUserBranch.innerText = currentUserData.branch;
                }
                
                // Set informasi absen
                resultAttendanceType.innerText = attendanceType === 'masuk' ? 'MASUK' : 'PULANG';
                resultAttendanceTime.innerText = responseData.time || new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});
                resultAttendanceDate.innerText = responseData.date || new Date().toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'});
                
                // Set status badge
                let statusHtml = '';
                if (responseData.is_late) {
                    statusHtml = '<span class="badge bg-danger px-3 py-2 fw-bold"><i class="fas fa-clock me-1"></i> TERLAMBAT</span>';
                } else if (responseData.is_early_checkout) {
                    statusHtml = '<span class="badge bg-warning text-dark px-3 py-2 fw-bold"><i class="fas fa-running me-1"></i> PULANG CEPAT</span>';
                } else if (attendanceType === 'masuk') {
                    statusHtml = '<span class="badge bg-success px-3 py-2 fw-bold"><i class="fas fa-check-circle me-1"></i> TEPAT WAKTU</span>';
                } else {
                    statusHtml = '<span class="badge bg-info px-3 py-2 fw-bold"><i class="fas fa-user-clock me-1"></i> ABSEN PULANG</span>';
                }
                resultStatusBadge.innerHTML = statusHtml;
                
                // Tampilkan catatan jika ada
                if (responseData.notes && responseData.notes.trim() !== '') {
                    resultNotesSection.style.display = 'block';
                    resultNotesText.innerText = responseData.notes;
                } else {
                    resultNotesSection.style.display = 'none';
                }
                
                // Tampilkan foto bukti jika ada
                if (responseData.photo) {
                    capturedPhoto.src = responseData.photo;
                    capturedPhoto.style.display = 'block';
                } else {
                    capturedPhoto.style.display = 'none';
                }
                
                // Tampilkan kartu profil
                document.getElementById('resultProfileCard').style.display = 'block';
            } else {
                icon.innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
                title.innerText = "ABSEN GAGAL";
                title.className = "fw-bold mb-2 text-danger";
                document.getElementById('resultProfileCard').style.display = 'none';
                capturedPhoto.style.display = 'none';
            }
            
            // Pastikan tombol di bawah terlihat
            setTimeout(() => {
                overlay.scrollTop = overlay.scrollHeight;
            }, 100);
        }

        function resetScan() {
            // Stop semua stream
            if (streamRef) {
                streamRef.getTracks().forEach(track => track.stop());
                streamRef = null;
            }
            
            // Reset state
            capturedImageBase64 = null;
            currentUserData = null;
            currentUserId = null;
            
            // Reset UI state
            document.querySelectorAll('.btn-absen, .btn-retake').forEach(b => {
                b.disabled = false;
                if (b.classList.contains('btn-masuk')) {
                    b.innerHTML = '<i class="fas fa-sign-in-alt fa-lg mb-2"></i><span>MASUK</span>';
                } else if (b.classList.contains('btn-pulang')) {
                    b.innerHTML = '<i class="fas fa-sign-out-alt fa-lg mb-2"></i><span>PULANG</span>';
                }
            });
            
            document.getElementById('scanNotes').value = '';
            
            // Reset tampilan
            document.getElementById('verifSection').style.display = 'none';
            document.getElementById('resultOverlay').style.display = 'none';
            document.getElementById('qrSection').style.display = 'flex';
            
            // Mulai scanner lagi
            startQRScanner();
        }

        // Handle back button
        window.addEventListener('popstate', function(event) {
            if (document.getElementById('verifSection').style.display === 'flex' || 
                document.getElementById('resultOverlay').style.display === 'flex') {
                resetScan();
                history.pushState(null, null, window.location.href);
            }
        });

        // Prevent pull-to-refresh on mobile
        document.addEventListener('touchmove', function(e) {
            if (e.target.closest('.result-overlay') || 
                e.target.closest('.verification-overlay') ||
                e.target.closest('.scanner-wrapper')) {
                e.preventDefault();
            }
        }, { passive: false });
    </script>
</body>

</html>