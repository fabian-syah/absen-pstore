<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Security Scanner - AbsenPS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --bg-dark: #121212;
            --card-bg: #1e1e1e;
        }

        body {
            background-color: #000;
            height: 100vh; /* Fallback */
            height: 100dvh; /* Mobile Modern */
            width: 100vw;
            overflow: hidden;
            font-family: 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* WRAPPER KAMERA */
        .scanner-wrapper {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: #000;
            z-index: 1;
        }

        #reader {
            width: 100%; height: 100%;
            position: absolute; top: 0; left: 0;
        }

        #reader video {
            width: 100% !important; height: 100% !important;
            object-fit: cover !important;
        }

        .scanner-header {
            position: absolute; top: 0; left: 0; width: 100%;
            padding: 20px;
            padding-top: max(20px, env(safe-area-inset-top));
            background: linear-gradient(180deg, rgba(0,0,0,0.8) 0%, transparent 100%);
            color: white; z-index: 10; text-align: center;
            pointer-events: none;
        }

        /* KOTAK FOCUS */
        .scan-area {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 70vw; height: 70vw;
            max-width: 280px; max-height: 280px;
            z-index: 10; pointer-events: none;
            border: 2px solid rgba(255, 255, 255, 0.7);
            border-radius: 20px;
            box-shadow: 0 0 0 4000px rgba(0, 0, 0, 0.6);
        }

        .scan-laser {
            position: absolute; width: 100%; height: 2px;
            background: #00ff00; box-shadow: 0 0 10px #00ff00;
            top: 50%; animation: scan 2s infinite ease-in-out;
        }

        @keyframes scan {
            0% { top: 10%; opacity: 0; }
            50% { opacity: 1; }
            100% { top: 90%; opacity: 0; }
        }

        .permission-btn-container {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            z-index: 50; text-align: center; display: none; width: 85%;
        }

        /* === VERIFIKASI OVERLAY === */
        .verification-overlay {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            background: var(--bg-dark);
            z-index: 100; display: none;
            flex-direction: column;
            padding: 20px;
            padding-top: max(20px, env(safe-area-inset-top));
            overflow-y: auto;
        }

        .profile-card {
            background: var(--card-bg);
            border-radius: 16px; padding: 20px;
            text-align: center; color: white;
            border: 1px solid #333; margin-bottom: 20px;
        }

        .profile-img-db {
            width: 80px; height: 80px;
            border-radius: 50%; object-fit: cover;
            border: 3px solid #28a745; background: #333;
        }

        .camera-preview-box {
            width: 100%; height: 0; padding-bottom: 100%;
            background: black; border-radius: 16px;
            overflow: hidden; position: relative;
            border: 2px solid #444; margin-bottom: 20px;
        }

        #camera-stream, #camera-canvas {
            position: absolute; top: 0; left: 0;
            width: 100%; height: 100%; object-fit: cover;
        }

        .btn-capture {
            width: 100%; padding: 15px;
            border-radius: 50px; font-weight: bold;
            background: white; color: black; border: none;
        }

        .action-buttons {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 10px; margin-top: 10px;
        }

        .btn-absen {
            padding: 15px; border-radius: 12px; font-weight: bold; border: none; color: white;
        }
        .btn-masuk { background: linear-gradient(135deg, #00b09b, #96c93d); }
        .btn-pulang { background: linear-gradient(135deg, #ff5f6d, #ffc371); }
        .btn-retake { width: 100%; padding: 10px; border-radius: 12px; background: #333; color: white; border: 1px solid #555; }

        /* === RESULT OVERLAY (FIXING SCROLL & CENTER) === */
        .result-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 200;
            
            /* PENTING: Gunakan display flex agar bisa scroll */
            display: none; 
            flex-direction: column;
            overflow-y: auto; /* Scroll jika konten panjang */
            -webkit-overflow-scrolling: touch;
            
            padding: 20px;
            padding-top: max(40px, env(safe-area-inset-top));
            padding-bottom: 40px; /* Space bawah agar tombol tidak kepotong */
        }

        .result-card {
            background: #212529;
            border-radius: 24px;
            padding: 30px 20px;
            width: 100%; 
            max-width: 380px;
            border: 1px solid #495057;
            position: relative;
            overflow: hidden;
            
            /* Center Horizontal & Vertical (Auto Margin) */
            margin: auto; 
            text-align: center; /* PUSATKAN SEMUA TEXT */
            
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .result-status-bar {
            position: absolute; top: 0; left: 0; width: 100%; height: 6px; background: #28a745;
        }

        .result-profile-img {
            width: 90px; height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #28a745;
            margin: 0 auto 15px auto; /* Auto margin kiri kanan */
            display: block;
            background: #333;
        }

        .result-captured-img {
            width: 100%; height: 200px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #555;
            margin: 15px 0;
            background: #000;
            display: block; /* Agar margin auto bekerja */
            margin-left: auto;
            margin-right: auto;
        }

        .result-time {
            font-size: 3rem; font-weight: 800; letter-spacing: 1px;
            line-height: 1; margin: 10px 0 5px 0;
            text-align: center;
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
            <div class="bg-dark p-4 rounded-3 border border-secondary shadow">
                <i class="fas fa-exclamation-triangle display-4 text-warning mb-3"></i>
                <h5 class="text-white mb-2">Kamera Tidak Aktif</h5>
                <p class="text-white-50 small mb-3">Mohon refresh halaman atau periksa izin kamera.</p>
                <button class="btn btn-primary rounded-pill px-4 w-100 fw-bold" onclick="forceRestartScanner()">
                    <i class="fas fa-sync me-2"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <div class="verification-overlay" id="verifSection">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="text-white m-0">Konfirmasi</h5>
            <button class="btn btn-sm btn-dark border-secondary rounded-pill px-3" onclick="resetScan()">
                <i class="fas fa-times me-1"></i> Batal
            </button>
        </div>

        <div class="profile-card">
            <img src="" id="dbPhoto" class="profile-img-db mb-3" alt="User">
            <h4 id="dbName" class="fw-bold m-0 text-white">Nama</h4>
            <p class="text-white-50 small m-0 mt-1"><span id="dbRole">Jabatan</span> | <span id="dbBranch">Cabang</span></p>
        </div>

        <div class="text-white mb-2 fw-bold small text-uppercase d-flex align-items-center justify-content-center">
            <i class="fas fa-camera me-2 text-warning"></i> Foto Bukti
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
                <label for="scanNotes" class="text-white small fw-bold mb-1">Catatan (Opsional)</label>
                <textarea id="scanNotes" class="form-control bg-dark text-white border-secondary" rows="2"
                    placeholder="Tulis catatan..."></textarea>
            </div>

            <button class="btn-retake mb-3" onclick="retakePhoto()">
                <i class="fas fa-redo me-2"></i> Foto Ulang
            </button>
            
            <div class="action-buttons">
                <button class="btn-absen btn-masuk" onclick="submitAttendance('masuk')">
                    <i class="fas fa-sign-in-alt mb-1 d-block"></i> MASUK
                </button>
                <button class="btn-absen btn-pulang" onclick="submitAttendance('pulang')">
                    <i class="fas fa-sign-out-alt mb-1 d-block"></i> PULANG
                </button>
            </div>
        </div>
    </div>

    <div class="result-overlay" id="resultOverlay">
        <div class="result-card">
            <div class="result-status-bar" id="resStatusBar"></div>
            
            <div class="d-flex align-items-center justify-content-center mb-3">
                <i id="resIcon" class="fas fa-check-circle fa-2x text-success"></i>
                <span id="resTitle" class="h3 fw-bold ms-2 text-white m-0">BERHASIL</span>
            </div>

            <img id="resProfileImg" src="" class="result-profile-img" alt="Profile">
            <h4 id="resName" class="fw-bold mb-0 text-white">Nama</h4>
            
            <p class="text-white-50 small mb-2 text-center">
                <span id="resDivision">Divisi</span> | <span id="resBranch">Cabang</span>
            </p>

            <div id="resNotesContainer" class="d-none mb-3 d-flex justify-content-center">
                <div class="p-2 bg-dark border border-secondary rounded text-warning fst-italic small text-break d-inline-block">
                    <i class="fas fa-sticky-note me-1"></i> "<span id="resNotes"></span>"
                </div>
            </div>

            <hr class="border-secondary my-3 opacity-25">

            <p class="text-white-50 small fw-bold mb-2 text-uppercase text-center">FOTO BUKTI</p>
            <img id="resCapturedImg" src="" class="result-captured-img" alt="Bukti">

            <div id="resTime" class="result-time text-success text-center">00:00</div>
            <div id="resDate" class="text-muted small mb-3 text-center">Tanggal</div>
            <p id="resMessage" class="text-info small fst-italic m-0 text-center"></p>

            <div class="mt-4">
                <button class="btn btn-primary w-100 py-3 rounded-pill fw-bold text-uppercase mb-2 shadow" onclick="resetScan()">
                    <i class="fas fa-qrcode me-2"></i> Scan Selanjutnya
                </button>
                <a href="{{ url('/dashboard') }}" class="btn btn-outline-light w-100 py-2 rounded-pill fw-bold text-uppercase small text-decoration-none">
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
        let isProcessing = false;

        async function startQRScanner() {
            if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                alert("Wajib HTTPS untuk kamera di HP!");
            }

            document.getElementById('permissionBtn').style.display = 'none';
            isProcessing = false;

            if (html5QrCode) {
                try {
                    if (html5QrCode.isScanning) { await html5QrCode.stop(); }
                    html5QrCode.clear();
                } catch (err) { console.warn(err); }
            }

            html5QrCode = new Html5Qrcode("reader");

            const config = { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 };

            try {
                await html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess, ()=>{});
            } catch (err) {
                console.error("Kamera Error:", err);
                document.getElementById('permissionBtn').style.display = 'flex';
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            if (isProcessing) return;
            isProcessing = true;

            html5QrCode.stop().then(() => {
                html5QrCode.clear();
                checkUser(decodedText);
            }).catch(err => {
                checkUser(decodedText);
            });
        }

        function forceRestartScanner() { location.reload(); }

        function checkUser(qrCode) {
            fetch("{{ route('security.check-user') }}", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
                    body: JSON.stringify({ qr_code: qrCode })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        showVerificationPage(data.data);
                    } else {
                        alert(data.message);
                        isProcessing = false;
                        startQRScanner();
                    }
                })
                .catch(err => {
                    alert('Gagal koneksi server.');
                    isProcessing = false;
                    startQRScanner();
                });
        }

        function showVerificationPage(user) {
            currentUserId = user.id;
            document.getElementById('dbName').innerText = user.name;
            document.getElementById('dbRole').innerText = user.role; 
            document.getElementById('dbBranch').innerText = user.branch;
            document.getElementById('dbPhoto').src = user.photo_url;

            retakePhoto();
            document.getElementById('scanNotes').value = '';

            document.getElementById('qrSection').style.display = 'none';
            document.getElementById('verifSection').style.display = 'flex';

            setTimeout(startCameraStream, 500);
        }

        function startCameraStream() {
            const video = document.getElementById('camera-stream');
            video.setAttribute('autoplay', '');
            video.setAttribute('muted', '');
            video.setAttribute('playsinline', '');

            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
                    .then(function(stream) {
                        streamRef = stream;
                        video.srcObject = stream;
                        video.play();
                    })
                    .catch(function(error) {
                        alert("Gagal buka kamera bukti.");
                    });
            }
        }

        function capturePhoto() {
            const video = document.getElementById('camera-stream');
            const canvas = document.getElementById('camera-canvas');
            const context = canvas.getContext('2d');

            if (video.videoWidth === 0) return alert("Tunggu kamera siap...");

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            capturedImageBase64 = canvas.toDataURL('image/jpeg', 0.7);

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
        }

        function submitAttendance(type) {
            if (!capturedImageBase64) return alert("Foto bukti wajib!");

            const btn = document.querySelector(`.btn-${type}`);
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ...';
            document.querySelectorAll('button').forEach(b => b.disabled = true);

            fetch("{{ route('security.store-attendance') }}", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
                    body: JSON.stringify({
                        user_id: currentUserId,
                        type: type,
                        image: capturedImageBase64,
                        notes: document.getElementById('scanNotes').value
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        showResult(data.message, data.data);
                    } else {
                        alert(data.message);
                        btn.innerHTML = originalContent;
                        document.querySelectorAll('button').forEach(b => b.disabled = false);
                    }
                })
                .catch(err => {
                    alert("Error sistem.");
                    btn.innerHTML = originalContent;
                    document.querySelectorAll('button').forEach(b => b.disabled = false);
                });
        }

        function showResult(message, data) {
            if (streamRef) streamRef.getTracks().forEach(track => track.stop());

            document.getElementById('verifSection').style.display = 'none';
            document.getElementById('resultOverlay').style.display = 'flex'; // FLEX agar center/scroll

            document.getElementById('resName').innerText = data.name;
            document.getElementById('resDivision').innerText = data.division; 
            document.getElementById('resBranch').innerText = data.branch;
            document.getElementById('resTime').innerText = data.time;
            document.getElementById('resDate').innerText = data.date;
            document.getElementById('resMessage').innerText = message;

            const notesEl = document.getElementById('resNotesContainer');
            if (data.notes && data.notes.trim()) {
                document.getElementById('resNotes').innerText = data.notes;
                notesEl.classList.remove('d-none');
            } else {
                notesEl.classList.add('d-none');
            }

            document.getElementById('resProfileImg').src = data.profile_photo;
            document.getElementById('resCapturedImg').src = data.photo;

            const statusBar = document.getElementById('resStatusBar');
            const timeText = document.getElementById('resTime');
            const title = document.getElementById('resTitle');
            const icon = document.getElementById('resIcon');
            const profileImg = document.getElementById('resProfileImg');

            timeText.className = 'result-time text-center'; // Pastikan center
            icon.className = 'fas fa-2x';

            if (data.is_late) {
                statusBar.style.background = '#dc3545';
                timeText.classList.add('text-danger');
                icon.classList.add('fa-exclamation-triangle', 'text-danger');
                profileImg.style.borderColor = '#dc3545';
                title.innerText = "TERLAMBAT";
            } else if (data.is_early_checkout) {
                statusBar.style.background = '#ffc107';
                timeText.classList.add('text-warning');
                icon.classList.add('fa-walking', 'text-warning');
                profileImg.style.borderColor = '#ffc107';
                title.innerText = "PULANG CEPAT";
            } else {
                statusBar.style.background = '#28a745';
                timeText.classList.add('text-success');
                icon.classList.add('fa-check-circle', 'text-success');
                profileImg.style.borderColor = '#28a745';
                title.innerText = "BERHASIL";
            }
        }

        function resetScan() {
            if (streamRef) streamRef.getTracks().forEach(track => track.stop());

            document.getElementById('resultOverlay').style.display = 'none';
            document.getElementById('verifSection').style.display = 'none';
            document.getElementById('qrSection').style.display = 'flex';

            document.querySelectorAll('button').forEach(b => b.disabled = false);
            document.querySelector('.btn-masuk').innerHTML = '<i class="fas fa-sign-in-alt mb-1 d-block"></i> MASUK';
            document.querySelector('.btn-pulang').innerHTML = '<i class="fas fa-sign-out-alt mb-1 d-block"></i> PULANG';
            
            setTimeout(startQRScanner, 300);
        }

        document.addEventListener('DOMContentLoaded', startQRScanner);
    </script>
</body>
</html>