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
        body {
            background-color: #000;
            height: 100vh;
            overflow: hidden;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
        }

        .scanner-wrapper {
            position: relative;
            width: 100%;
            height: 100%;
            background: black;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
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
        }

        .scanner-header {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            padding: 20px;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.8), transparent);
            color: white;
            z-index: 20;
            text-align: center;
        }

        .scan-area {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 250px;
            height: 250px;
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
            box-shadow: 0 0 4px #00ff00;
            top: 50%;
            animation: scan 2s infinite ease-in-out;
        }

        @keyframes scan {
            0%, 100% { top: 10%; opacity: 0; }
            50% { top: 90%; opacity: 1; }
        }

        .permission-btn-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 30;
            text-align: center;
            display: none;
            width: 80%;
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
            overflow-y: auto;
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
            height: 350px;
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
        }

        .btn-absen:active { transform: scale(0.98); }
        .btn-masuk { background: linear-gradient(45deg, #00b09b, #96c93d); }
        .btn-pulang { background: linear-gradient(45deg, #ff5f6d, #ffc371); }

        .btn-capture {
            width: 100%;
            padding: 15px;
            border-radius: 12px;
            font-weight: bold;
            background: white;
            color: black;
            border: none;
        }

        .btn-retake {
            width: 100%;
            padding: 10px;
            border-radius: 12px;
            font-weight: bold;
            background: #333;
            color: white;
            border: 1px solid #555;
            margin-bottom: 10px;
        }

        /* --- STYLING BARU UNTUK RESULT OVERLAY --- */
        .result-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 100;
            display: none;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            overflow-y: auto;
        }

        .result-card {
            background: #212529;
            border-radius: 20px;
            padding: 25px;
            width: 90%;
            max-width: 400px;
            border: 1px solid #495057;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
        }

        .result-status-bar {
            position: absolute;
            top: 0; left: 0; width: 100%;
            height: 8px;
            background: #28a745; /* Default success */
        }
        
        .result-profile-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #28a745;
            margin-top: 10px;
            margin-bottom: 15px;
            background: #333;
        }

        .result-time {
            font-size: 3rem;
            font-weight: 800;
            letter-spacing: 2px;
            line-height: 1;
            margin: 15px 0;
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
            <button class="btn btn-success rounded-pill px-4" onclick="startQRScanner()">Mulai Kamera</button>
            <p class="text-white-50 mt-3 small">iOS memerlukan izin HTTPS atau Localhost</p>
        </div>
    </div>

    <div class="verification-overlay" id="verifSection">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="text-white m-0">Konfirmasi Absensi</h5>
            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="resetScan()"><i
                    class="fas fa-times me-1"></i> Batal</button>
        </div>
        <div class="profile-card">
            <img src="" id="dbPhoto" class="profile-img-db mb-3" alt="User">
            <h4 id="dbName" class="fw-bold m-0">Nama Karyawan</h4>
            <p id="dbRole" class="text-muted small m-0">Jabatan</p>
            <span id="dbBranch" class="badge bg-primary mt-2">Cabang</span>
        </div>

        <div class="text-white mb-2 fw-bold small text-uppercase"><i class="fas fa-camera me-1"></i> Ambil Foto Bukti
            (Wajib)</div>

        <div class="camera-preview-box">
            <video id="camera-stream" autoplay playsinline muted></video>
            <canvas id="camera-canvas"></canvas>
        </div>

        <div id="step-capture-btn">
            <button class="btn-capture" onclick="capturePhoto()"><i class="fas fa-camera fa-lg me-2"></i> AMBIL
                FOTO</button>
        </div>

        <div id="step-confirm-btn" style="display: none;">
            {{-- FORM CATATAN BARU --}}
            <div class="form-group mb-3 text-start">
                <label for="scanNotes" class="text-white small fw-bold mb-1">Catatan (Opsional)</label>
                <textarea id="scanNotes" class="form-control bg-dark text-white border-secondary" rows="2"
                    placeholder="Contoh: Lembur, Tugas Luar, dll..."></textarea>
            </div>

            <button class="btn-retake" onclick="retakePhoto()"><i class="fas fa-redo me-2"></i> FOTO ULANG</button>
            <div class="action-buttons">
                <button class="btn-absen btn-masuk" onclick="submitAttendance('masuk')"><i
                        class="fas fa-sign-in-alt fa-lg mb-1 d-block"></i> MASUK</button>
                <button class="btn-absen btn-pulang" onclick="submitAttendance('pulang')"><i
                        class="fas fa-sign-out-alt fa-lg mb-1 d-block"></i> PULANG</button>
            </div>
        </div>
    </div>

    <div class="result-overlay" id="resultOverlay">
        <div class="result-card">
            <div class="result-status-bar" id="resStatusBar"></div>
            
            <div class="mb-3">
                <i id="resIcon" class="fas fa-check-circle fa-2x text-success"></i>
                <span id="resTitle" class="h4 fw-bold ms-2 text-white">BERHASIL</span>
            </div>

            <img id="resProfileImg" src="" class="result-profile-img" alt="Profile">
            
            <h3 id="resName" class="fw-bold mb-1 text-white">Nama User</h3>
            <p class="text-white-50 small mb-2"><span id="resRole">Jabatan</span> | <span id="resDivision">Divisi</span></p>
            <span id="resBranch" class="badge bg-dark border border-secondary mb-3">Cabang</span>

            <hr class="border-secondary my-3">
            
            <p class="text-uppercase text-white-50 small fw-bold mb-0" id="resTypeLabel">WAKTU SCAN</p>
            <div id="resTime" class="result-time text-success">00:00</div>
            <div id="resDate" class="text-muted small">Tanggal</div>

            <p id="resMessage" class="mt-2 text-info small fst-italic"></p>

            <div class="mt-4">
                <button class="btn btn-primary w-100 py-3 rounded-pill fw-bold text-uppercase mb-3 shadow" onclick="resetScan()">
                    <i class="fas fa-qrcode me-2"></i> Scan Selanjutnya
                </button>

                <a href="{{ url('/dashboard') }}"
                    class="btn btn-outline-light w-100 py-2 rounded-pill fw-bold text-uppercase small">
                    <i class="fas fa-arrow-left me-2"></i> Dashboard
                </a>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let html5QrCode = null;
        let currentUserId = null;
        let streamRef = null;
        let capturedImageBase64 = null;

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
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0,
                formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE]
            };

            html5QrCode.start({ facingMode: "environment" }, qrConfig, onScanSuccess, onScanFailure)
                .catch(err => {
                    console.error("Gagal start scanner:", err);
                    document.getElementById('permissionBtn').style.display = 'block';
                    alert("Gagal akses kamera: " + err);
                });
        }

        function onScanSuccess(decodedText, decodedResult) {
            html5QrCode.stop().then(() => {
                html5QrCode.clear();
                checkUser(decodedText);
            }).catch(err => console.error(err));
        }

        function onScanFailure(error) {}

        function checkUser(qrCode) {
            fetch("{{ route('security.check-user') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken
                    },
                    body: JSON.stringify({ qr_code: qrCode })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        showVerificationPage(data.data);
                    } else {
                        alert(data.message);
                        startQRScanner();
                    }
                })
                .catch(err => {
                    alert('Gagal terhubung ke server.');
                    startQRScanner();
                });
        }

        function showVerificationPage(user) {
            currentUserId = user.id;
            document.getElementById('dbName').innerText = user.name;
            document.getElementById('dbRole').innerText = user.role + ' - ' + user.division;
            document.getElementById('dbBranch').innerText = user.branch;
            document.getElementById('dbPhoto').src = user.photo_url;

            retakePhoto();
            document.getElementById('scanNotes').value = '';

            document.getElementById('qrSection').style.display = 'none';
            document.getElementById('verifSection').style.display = 'flex';

            setTimeout(startCameraStream, 300);
        }

        function startCameraStream() {
            const video = document.getElementById('camera-stream');
            video.setAttribute('autoplay', '');
            video.setAttribute('muted', '');
            video.setAttribute('playsinline', '');

            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({
                        video: { facingMode: "environment", width: { ideal: 640 } }
                    })
                    .then(function(stream) {
                        streamRef = stream;
                        video.srcObject = stream;
                        video.play();
                    })
                    .catch(function(error) {
                        console.error(error);
                        alert("Gagal akses kamera bukti.");
                    });
            }
        }

        function capturePhoto() {
            const video = document.getElementById('camera-stream');
            const canvas = document.getElementById('camera-canvas');
            const context = canvas.getContext('2d');

            if (video.videoWidth === 0) {
                alert("Kamera belum siap.");
                return;
            }

            const scaleFactor = 800 / video.videoWidth;
            const newWidth = 800;
            const newHeight = video.videoHeight * scaleFactor;

            canvas.width = newWidth;
            canvas.height = newHeight;
            context.drawImage(video, 0, 0, newWidth, newHeight);
            capturedImageBase64 = canvas.toDataURL('image/jpeg', 0.6);

            video.style.display = 'none';
            canvas.style.display = 'block';

            document.getElementById('step-capture-btn').style.display = 'none';
            document.getElementById('step-confirm-btn').style.display = 'block';
        }

        function retakePhoto() {
            const video = document.getElementById('camera-stream');
            const canvas = document.getElementById('camera-canvas');

            capturedImageBase64 = null;
            video.style.display = 'block';
            canvas.style.display = 'none';

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

            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Loading...';
            document.querySelectorAll('.btn-absen, .btn-retake').forEach(b => b.disabled = true);

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
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // PASS DATA LENGKAP KE FUNGSI TAMPILAN
                        showResult('success', data.message, data.data);
                    } else {
                        alert(data.message);
                        btn.innerHTML = originalContent;
                        document.querySelectorAll('.btn-absen, .btn-retake').forEach(b => b.disabled = false);
                    }
                })
                .catch(err => {
                    alert("Error sistem.");
                    btn.innerHTML = originalContent;
                    document.querySelectorAll('.btn-absen, .btn-retake').forEach(b => b.disabled = false);
                });
        }

        // --- FUNGSI TAMPILKAN HASIL BIODATA ---
        function showResult(status, message, data) {
            const overlay = document.getElementById('resultOverlay');
            
            // Stop Camera
            if (streamRef) {
                streamRef.getTracks().forEach(track => track.stop());
            }

            overlay.style.display = 'flex';

            // Data Population
            document.getElementById('resName').innerText = data.name;
            document.getElementById('resRole').innerText = data.role;
            document.getElementById('resDivision').innerText = data.division;
            document.getElementById('resBranch').innerText = data.branch;
            
            // Foto Profil (Gunakan foto profil user dari DB, bukan foto capture agar lebih rapi)
            document.getElementById('resProfileImg').src = data.profile_photo;
            
            document.getElementById('resTime').innerText = data.time;
            document.getElementById('resDate').innerText = data.date;
            document.getElementById('resMessage').innerText = message;

            // Styling Status (Warna)
            const statusBar = document.getElementById('resStatusBar');
            const timeText = document.getElementById('resTime');
            const profileImg = document.getElementById('resProfileImg');
            const icon = document.getElementById('resIcon');
            const typeLabel = document.getElementById('resTypeLabel');

            if (data.is_late) {
                // Terlambat -> Merah
                statusBar.style.background = '#dc3545';
                timeText.className = 'result-time text-danger';
                profileImg.style.borderColor = '#dc3545';
                icon.className = 'fas fa-exclamation-circle fa-2x text-danger';
                document.getElementById('resTitle').innerText = "TERLAMBAT";
            } else if (data.is_early_checkout) {
                // Pulang Cepat -> Kuning/Orange
                statusBar.style.background = '#ffc107';
                timeText.className = 'result-time text-warning';
                profileImg.style.borderColor = '#ffc107';
                icon.className = 'fas fa-clock fa-2x text-warning';
                document.getElementById('resTitle').innerText = "PULANG CEPAT";
            } else {
                // Normal -> Hijau
                statusBar.style.background = '#28a745';
                timeText.className = 'result-time text-success';
                profileImg.style.borderColor = '#28a745';
                icon.className = 'fas fa-check-circle fa-2x text-success';
                document.getElementById('resTitle').innerText = "BERHASIL";
            }
        }

        function resetScan() {
            if (streamRef) {
                streamRef.getTracks().forEach(track => track.stop());
            }
            document.getElementById('verifSection').style.display = 'none';
            document.getElementById('resultOverlay').style.display = 'none';
            document.getElementById('qrSection').style.display = 'flex';

            document.querySelectorAll('.btn-absen, .btn-retake').forEach(b => b.disabled = false);
            document.querySelector('.btn-masuk').innerHTML = '<i class="fas fa-sign-in-alt fa-lg mb-1 d-block"></i> MASUK';
            document.querySelector('.btn-pulang').innerHTML = '<i class="fas fa-sign-out-alt fa-lg mb-1 d-block"></i> PULANG';
            document.getElementById('scanNotes').value = '';

            startQRScanner();
        }

        document.addEventListener('DOMContentLoaded', startQRScanner);
    </script>
</body>
</html>