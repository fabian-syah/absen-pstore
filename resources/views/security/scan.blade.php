<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Security Scanner - AbsenPS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* === GLOBAL RESET === */
        :root {
            --primary-color: #0d6efd;
            --bg-dark: #121212;
            --card-bg: #1e1e1e;
        }

        body {
            background-color: #000;
            height: 100vh; /* Fallback */
            height: 100dvh; /* Dynamic Height Mobile */
            width: 100vw;
            overflow: hidden;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* === SCANNER WRAPPER === */
        .scanner-wrapper {
            position: relative;
            width: 100%;
            height: 100%;
            background: black;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1; /* Paling Bawah */
        }

        #reader {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        #reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
        }

        .scanner-header {
            position: absolute;
            top: 0; left: 0; width: 100%;
            padding: 20px;
            padding-top: max(20px, env(safe-area-inset-top)); /* iPhone Notch Fix */
            background: linear-gradient(180deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0) 100%);
            color: white;
            z-index: 20;
            text-align: center;
        }

        /* === RESPONSIVE SCAN BOX === */
        .scan-area {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 70vw; 
            height: 70vw;
            max-width: 280px;
            max-height: 280px;
            z-index: 15;
            pointer-events: none;
            border: 2px solid rgba(255, 255, 255, 0.6);
            border-radius: 24px;
            box-shadow: 0 0 0 4000px rgba(0, 0, 0, 0.6);
        }

        .scan-laser {
            position: absolute;
            width: 100%; height: 3px;
            background: #00ff00;
            box-shadow: 0 0 10px #00ff00;
            top: 10%;
            animation: scan 2s infinite ease-in-out;
            border-radius: 50%;
        }

        @keyframes scan {
            0% { top: 10%; opacity: 0; }
            50% { opacity: 1; }
            100% { top: 90%; opacity: 0; }
        }

        .permission-btn-container {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            z-index: 30;
            text-align: center;
            display: none;
            width: 90%;
            max-width: 300px;
        }

        /* === VERIFICATION OVERLAY === */
        .verification-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100dvh;
            background: var(--bg-dark);
            z-index: 50; /* Di atas scanner */
            display: none;
            flex-direction: column;
            padding: 20px;
            padding-top: max(20px, env(safe-area-inset-top)); /* Notch Fix */
            overflow-y: auto;
        }

        .profile-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            color: white;
            border: 1px solid #333;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .profile-img-db {
            width: 80px; height: 80px;
            border-radius: 50%;
            border: 3px solid #28a745;
            object-fit: cover;
            background: #333;
        }

        .camera-preview-box {
            width: 100%;
            height: 0;
            padding-bottom: 100%;
            background: black;
            border-radius: 16px;
            overflow: hidden;
            position: relative;
            border: 2px solid #444;
            margin-bottom: 20px;
        }

        #camera-stream, #camera-canvas {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover;
        }

        .btn-capture {
            width: 100%; padding: 16px;
            border-radius: 50px; font-weight: 700;
            background: white; color: black; border: none;
            box-shadow: 0 4px 15px rgba(255,255,255,0.2);
        }

        .action-buttons {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 12px; margin-top: 12px;
        }

        .btn-absen {
            padding: 14px; font-weight: 700;
            border-radius: 12px; border: none;
            color: white; font-size: 0.95rem;
        }
        .btn-masuk { background: linear-gradient(135deg, #00b09b, #96c93d); }
        .btn-pulang { background: linear-gradient(135deg, #ff5f6d, #ffc371); }
        .btn-retake {
            width: 100%; padding: 10px;
            border-radius: 12px; font-weight: 600;
            background: #2c2c2c; color: #ccc; border: 1px solid #444;
        }

        /* === RESULT OVERLAY (FIXED POSITION) === */
        .result-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 100; /* Paling Atas */
            display: none;
            
            /* Agar bisa scroll jika konten panjang */
            flex-direction: column;
            overflow-y: auto; 
            
            /* Padding atas aman untuk iPhone */
            padding: 20px;
            padding-top: max(40px, env(safe-area-inset-top)); 
            padding-bottom: 40px;
        }

        .result-card {
            background: #212529;
            border-radius: 24px;
            padding: 30px 20px;
            width: 100%;
            max-width: 380px;
            border: 1px solid #495057;
            box-shadow: 0 20px 50px rgba(0,0,0,0.6);
            position: relative;
            overflow: hidden;
            
            /* TRICK: Margin auto menengahkan vertikal, tapi aman scroll */
            margin: auto; 
        }

        .result-status-bar {
            position: absolute; top: 0; left: 0; width: 100%;
            height: 8px; background: #28a745;
        }

        .result-header {
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .result-profile-img {
            width: 90px; height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #28a745;
            margin: 0 auto 10px auto;
            background: #333;
            display: block;
        }

        .result-captured-img {
            width: 100%; height: 200px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #555;
            margin: 15px 0;
            background: #000;
        }

        .result-time {
            font-size: 3rem;
            font-weight: 800;
            letter-spacing: 1px;
            line-height: 1;
            margin: 10px 0 5px 0;
        }
    </style>
</head>

<body>

    <div class="scanner-wrapper" id="qrSection">
        <div class="scanner-header">
            <h5 class="m-0 fw-bold"><i class="fas fa-qrcode me-2"></i>Scan Absensi</h5>
            <small class="text-white-50">Arahkan kamera ke QR Code Karyawan</small>
        </div>
        
        <div id="reader"></div>
        
        <div class="scan-area">
            <div class="scan-laser"></div>
        </div>

        <div class="permission-btn-container" id="permissionBtn">
            <div class="bg-dark p-4 rounded-3 border border-secondary shadow">
                <i class="fas fa-camera-slash display-4 text-danger mb-3"></i>
                <h5 class="text-white mb-3">Kamera Tidak Aktif</h5>
                <p class="text-white-50 small mb-3">Pastikan izin kamera diaktifkan di browser (Safari/Chrome).</p>
                <button class="btn btn-primary rounded-pill px-4 w-100 fw-bold" onclick="startQRScanner()">
                    <i class="fas fa-sync-alt me-2"></i> Coba Lagi
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
            <h4 id="dbName" class="fw-bold m-0 text-white">Nama Karyawan</h4>
            <p class="text-white-50 small m-0 mt-1"><span id="dbRole">Jabatan</span> | <span id="dbBranch">Cabang</span></p>
        </div>

        <div class="text-white mb-2 fw-bold small text-uppercase d-flex align-items-center">
            <i class="fas fa-camera me-2 text-warning"></i> Foto Bukti (Wajib)
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
                <label for="scanNotes" class="text-white small fw-bold mb-1">Catatan Security (Opsional)</label>
                <textarea id="scanNotes" class="form-control bg-dark text-white border-secondary" rows="2"
                    placeholder="Tulis catatan di sini..."></textarea>
            </div>

            <button class="btn-retake mb-3" onclick="retakePhoto()">
                <i class="fas fa-redo me-2"></i> Foto Ulang
            </button>
            
            <div class="action-buttons">
                <button class="btn-absen btn-masuk" onclick="submitAttendance('masuk')">
                    <i class="fas fa-sign-in-alt fa-lg mb-1 d-block"></i> MASUK
                </button>
                <button class="btn-absen btn-pulang" onclick="submitAttendance('pulang')">
                    <i class="fas fa-sign-out-alt fa-lg mb-1 d-block"></i> PULANG
                </button>
            </div>
        </div>
    </div>

    <div class="result-overlay" id="resultOverlay">
        <div class="result-card">
            <div class="result-status-bar" id="resStatusBar"></div>
            
            <div class="result-header d-flex align-items-center justify-content-center">
                <i id="resIcon" class="fas fa-check-circle fa-2x text-success"></i>
                <span id="resTitle" class="h3 fw-bold ms-2 text-white m-0">BERHASIL</span>
            </div>

            <img id="resProfileImg" src="" class="result-profile-img" alt="Profile">
            <h4 id="resName" class="fw-bold mb-0 text-white">Nama User</h4>
            
            <p class="text-white-50 small mb-2">
                <span id="resDivision">Divisi</span> | <span id="resBranch">Cabang</span>
            </p>

            <div id="resNotesContainer" class="d-none mb-3">
                <div class="p-2 bg-dark border border-secondary rounded text-warning fst-italic small text-break">
                    <i class="fas fa-sticky-note me-1"></i> "<span id="resNotes"></span>"
                </div>
            </div>

            <hr class="border-secondary my-3 opacity-25">

            <p class="text-white-50 small fw-bold mb-2 text-uppercase text-start">FOTO BUKTI</p>
            <img id="resCapturedImg" src="" class="result-captured-img" alt="Bukti Absen">

            <div id="resTime" class="result-time text-success">00:00</div>
            <div id="resDate" class="text-muted small mb-3">Tanggal</div>

            <p id="resMessage" class="text-info small fst-italic m-0"></p>

            <div class="mt-4">
                <button class="btn btn-primary w-100 py-3 rounded-pill fw-bold text-uppercase mb-2 shadow" onclick="resetScan()">
                    <i class="fas fa-qrcode me-2"></i> Scan Selanjutnya
                </button>

                <a href="{{ url('/dashboard') }}"
                    class="btn btn-outline-light w-100 py-2 rounded-pill fw-bold text-uppercase small text-decoration-none">
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
        let isProcessingScan = false; // Lock untuk mencegah scan ganda

        // --- SCANNER INIT (ASYNC/AWAIT UNTUK IOS) ---
        async function startQRScanner() {
            // Pastikan flag reset
            isProcessingScan = false;

            document.getElementById('permissionBtn').style.display = 'none';
            
            // 1. Matikan Scanner Lama Jika Ada (PENTING)
            if (html5QrCode) {
                try {
                    if (html5QrCode.isScanning) {
                        await html5QrCode.stop();
                    }
                    html5QrCode.clear();
                } catch (e) {
                    console.log("Cleanup warning:", e);
                }
            }

            // 2. Init Baru
            initScanner();
        }

        function initScanner() {
            html5QrCode = new Html5Qrcode("reader", { verbose: false });

            const qrConfig = {
                fps: 10, 
                qrbox: (viewfinderWidth, viewfinderHeight) => {
                    let minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                    let size = Math.floor(minEdge * 0.7);
                    return { width: size, height: size };
                }
            };

            const videoConstraints = { 
                facingMode: "environment",
                focusMode: "continuous"
            };

            html5QrCode.start(videoConstraints, qrConfig, onScanSuccess, onScanFailure)
                .catch(err => {
                    console.error("Camera Error:", err);
                    document.getElementById('permissionBtn').style.display = 'flex';
                });
        }

        function onScanSuccess(decodedText, decodedResult) {
            // Cek Lock
            if (isProcessingScan) return;
            isProcessingScan = true;

            // Stop scanner segera setelah scan berhasil
            html5QrCode.stop().then(() => {
                html5QrCode.clear();
                checkUser(decodedText);
            }).catch(err => {
                console.error("Stop failed", err);
                checkUser(decodedText); // Tetap lanjut meski stop gagal
            });
        }

        function onScanFailure(error) {
            // Biarkan kosong
        }

        // --- BACKEND CHECK ---
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
                        // Reset lock dan start ulang jika gagal
                        isProcessingScan = false;
                        startQRScanner();
                    }
                })
                .catch(err => {
                    alert('Gagal terhubung ke server.');
                    isProcessingScan = false;
                    startQRScanner();
                });
        }

        // --- VERIFICATION UI ---
        function showVerificationPage(user) {
            currentUserId = user.id;
            
            document.getElementById('dbName').innerText = user.name;
            document.getElementById('dbRole').innerText = user.role;
            document.getElementById('dbBranch').innerText = user.branch;
            document.getElementById('dbPhoto').src = user.photo_url;

            // Reset Form
            retakePhoto();
            document.getElementById('scanNotes').value = '';

            // Switch View
            document.getElementById('qrSection').style.display = 'none';
            document.getElementById('verifSection').style.display = 'flex'; // Flex untuk overlay

            // Delay sedikit untuk init kamera bukti
            setTimeout(startCameraStream, 400);
        }

        // --- CAMERA BUKTI (CAPTURE) ---
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
                        alert("Gagal akses kamera bukti. Cek izin browser.");
                    });
            }
        }

        function capturePhoto() {
            const video = document.getElementById('camera-stream');
            const canvas = document.getElementById('camera-canvas');
            const context = canvas.getContext('2d');

            if (video.videoWidth === 0) {
                alert("Tunggu kamera siap...");
                return;
            }

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
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
        }

        // --- SUBMIT ---
        function submitAttendance(type) {
            if (!capturedImageBase64) {
                alert("Foto bukti wajib diambil!");
                return;
            }

            const btn = document.querySelector(`.btn-${type}`);
            const originalContent = btn.innerHTML;
            const notes = document.getElementById('scanNotes').value;

            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            document.querySelectorAll('button').forEach(b => b.disabled = true);

            fetch("{{ route('security.store-attendance') }}", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
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
                        showResult('success', data.message, data.data);
                    } else {
                        alert(data.message);
                        btn.innerHTML = originalContent;
                        document.querySelectorAll('button').forEach(b => b.disabled = false);
                    }
                })
                .catch(err => {
                    alert("Terjadi kesalahan sistem.");
                    btn.innerHTML = originalContent;
                    document.querySelectorAll('button').forEach(b => b.disabled = false);
                });
        }

        // --- SHOW RESULT ---
        function showResult(status, message, data) {
            // Matikan stream kamera bukti
            if (streamRef) {
                streamRef.getTracks().forEach(track => track.stop());
            }

            // Sembunyikan verifikasi, Tampilkan Result
            document.getElementById('verifSection').style.display = 'none';
            document.getElementById('resultOverlay').style.display = 'flex'; // Flex agar centering jalan

            // ISI DATA
            document.getElementById('resName').innerText = data.name;
            document.getElementById('resDivision').innerText = data.division; 
            document.getElementById('resBranch').innerText = data.branch;

            // NOTES
            const notesContainer = document.getElementById('resNotesContainer');
            if (data.notes && data.notes.trim() !== "") {
                document.getElementById('resNotes').innerText = data.notes;
                notesContainer.classList.remove('d-none');
            } else {
                notesContainer.classList.add('d-none');
            }

            // GAMBAR
            document.getElementById('resProfileImg').src = data.profile_photo;
            document.getElementById('resCapturedImg').src = data.photo;
            
            // WAKTU
            document.getElementById('resTime').innerText = data.time;
            document.getElementById('resDate').innerText = data.date;
            document.getElementById('resMessage').innerText = message;

            // WARNA STATUS
            const statusBar = document.getElementById('resStatusBar');
            const timeText = document.getElementById('resTime');
            const profileImg = document.getElementById('resProfileImg');
            const icon = document.getElementById('resIcon');
            const title = document.getElementById('resTitle');

            if (data.is_late) {
                statusBar.style.background = '#dc3545';
                timeText.className = 'result-time text-danger';
                profileImg.style.borderColor = '#dc3545';
                icon.className = 'fas fa-exclamation-triangle fa-2x text-danger';
                title.innerText = "TERLAMBAT";
            } else if (data.is_early_checkout) {
                statusBar.style.background = '#ffc107';
                timeText.className = 'result-time text-warning';
                profileImg.style.borderColor = '#ffc107';
                icon.className = 'fas fa-walking fa-2x text-warning';
                title.innerText = "PULANG CEPAT";
            } else {
                statusBar.style.background = '#28a745';
                timeText.className = 'result-time text-success';
                profileImg.style.borderColor = '#28a745';
                icon.className = 'fas fa-check-circle fa-2x text-success';
                title.innerText = "BERHASIL";
            }
        }

        // --- RESET / NEXT SCAN (FIXED) ---
        function resetScan() {
            // 1. Matikan kamera bukti jika masih nyala
            if (streamRef) {
                streamRef.getTracks().forEach(track => track.stop());
            }
            
            // 2. Sembunyikan SEMUA Overlay (Penting!)
            document.getElementById('verifSection').style.display = 'none';
            document.getElementById('resultOverlay').style.display = 'none';
            
            // 3. Tampilkan Wrapper Scanner
            document.getElementById('qrSection').style.display = 'flex';

            // 4. Reset Form UI
            document.querySelectorAll('button').forEach(b => b.disabled = false);
            document.querySelector('.btn-masuk').innerHTML = '<i class="fas fa-sign-in-alt fa-lg mb-1 d-block"></i> MASUK';
            document.querySelector('.btn-pulang').innerHTML = '<i class="fas fa-sign-out-alt fa-lg mb-1 d-block"></i> PULANG';
            document.getElementById('scanNotes').value = '';
            document.getElementById('resNotesContainer').classList.add('d-none');

            // 5. Restart Scanner dengan delay kecil agar DOM stabil
            setTimeout(() => {
                startQRScanner();
            }, 100);
        }

        // Jalankan saat load
        document.addEventListener('DOMContentLoaded', startQRScanner);
    </script>
</body>
</html>