<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Security Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');

        :root {
            --bg-dark: #000000;
            --card-dark: #1c1c1e;
            --primary-blue: #007aff;
            --success-green: #28cd41;
            --text-gray: #8e8e93;
        }

        body {
            background-color: var(--bg-dark);
            height: 100vh;
            height: 100dvh;
            width: 100vw;
            font-family: 'Inter', sans-serif;
            overflow: hidden;
            margin: 0; padding: 0;
        }

        /* === 1. SCANNER AREA === */
        .scanner-wrapper {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: black; z-index: 1;
        }
        #reader { width: 100%; height: 100%; position: absolute; }
        #reader video { object-fit: cover; width: 100% !important; height: 100% !important; }

        .scan-frame {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 280px; height: 280px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 24px;
            box-shadow: 0 0 0 4000px rgba(0,0,0,0.7); /* Darken surroundings */
            z-index: 10; pointer-events: none;
        }
        .scan-line {
            width: 100%; height: 2px; background: var(--success-green);
            position: absolute; top: 10%;
            box-shadow: 0 0 15px var(--success-green);
            animation: scanning 2s infinite ease-in-out;
        }
        @keyframes scanning { 0% {top: 10%; opacity: 0;} 50% {opacity: 1;} 100% {top: 90%; opacity: 0;} }

        /* === 2. VERIFICATION OVERLAY (PRE-SUBMIT) === */
        .verification-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: var(--bg-dark); z-index: 50; display: none;
            flex-direction: column; padding: 20px;
            padding-top: max(20px, env(safe-area-inset-top));
            overflow-y: auto;
        }
        /* ...Styling Camera Preview box sama seperti sebelumnya... */
        .camera-preview-box {
            width: 100%; height: 0; padding-bottom: 100%;
            background: #000; border-radius: 16px; overflow: hidden; position: relative;
            margin-bottom: 20px; border: 2px solid #333;
        }
        #camera-stream, #camera-canvas {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;
        }

        /* === 3. RESULT OVERLAY (TAMPILAN AKHIR) === */
        .result-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.95); z-index: 100;
            display: none; justify-content: center; align-items: center;
            padding: 20px;
        }

        .result-card {
            background: var(--card-dark);
            width: 100%; max-width: 380px;
            border-radius: 28px;
            overflow: hidden;
            text-align: center;
            position: relative;
            box-shadow: 0 20px 40px rgba(0,0,0,0.6);
            display: flex; flex-direction: column;
            padding-bottom: 30px;
            max-height: 90vh; /* Agar scrollable di layar pendek */
            overflow-y: auto;
        }

        /* Top Green Bar */
        .status-bar-top {
            height: 12px; width: 100%; background: var(--success-green);
            flex-shrink: 0;
        }

        /* Header "BERHASIL" */
        .result-header {
            padding-top: 25px; padding-bottom: 10px;
            display: flex; justify-content: center; align-items: center; gap: 10px;
        }
        .text-status { font-weight: 800; font-size: 1.4rem; color: #fff; letter-spacing: 1px; }

        /* Avatar Circle */
        .avatar-circle {
            width: 90px; height: 90px;
            background: #fff; border-radius: 50%;
            margin: 10px auto;
            display: flex; justify-content: center; align-items: center;
            font-size: 2rem; font-weight: 700; color: #333;
            border: 4px solid var(--success-green);
            overflow: hidden;
        }
        .avatar-img { width: 100%; height: 100%; object-fit: cover; }

        /* User Info */
        .user-name { font-size: 1.5rem; font-weight: 700; color: #fff; margin-bottom: 4px; }
        .user-meta { color: var(--text-gray); font-size: 0.9rem; margin-bottom: 20px; font-weight: 500; }

        /* Separator Line */
        .divider { height: 1px; background: #3a3a3c; margin: 0 30px 15px 30px; }

        /* Foto Bukti Label */
        .label-proof {
            color: var(--text-gray); font-size: 0.8rem;
            font-weight: 700; text-transform: uppercase;
            letter-spacing: 1px; margin-bottom: 10px;
        }

        /* Image Box */
        .proof-img-container {
            width: 85%; margin: 0 auto 15px auto;
            border-radius: 16px; overflow: hidden;
            border: 1px solid #333;
            height: 160px; /* Fixed height for consistency */
            background: #000;
        }
        .proof-img { width: 100%; height: 100%; object-fit: cover; }

        /* Time & Date */
        .time-display {
            font-size: 3.5rem; font-weight: 800;
            color: var(--success-green);
            line-height: 1; margin-bottom: 5px;
            font-family: 'Courier New', monospace; /* Monospace agar angka diam */
            letter-spacing: -2px;
        }
        .date-display { color: var(--text-gray); font-size: 0.95rem; margin-bottom: 5px; }
        .msg-display { color: #00d2d3; font-size: 0.9rem; font-style: italic; margin-bottom: 25px; }

        /* === BUTTONS (DIBUAT MIRIP GAMBAR) === */
        .btn-container { padding: 0 30px; display: flex; flex-direction: column; gap: 12px; }
        
        .btn-scan-next {
            background-color: var(--primary-blue);
            color: white; border: none;
            padding: 16px; border-radius: 50px;
            font-weight: 800; font-size: 0.95rem;
            text-transform: uppercase; letter-spacing: 0.5px;
            width: 100%; display: flex; justify-content: center; align-items: center; gap: 10px;
            box-shadow: 0 4px 15px rgba(0,122,255,0.3);
            transition: transform 0.1s;
        }
        .btn-scan-next:active { transform: scale(0.98); }

        .btn-dashboard {
            background-color: transparent;
            color: white;
            border: 2px solid #fff; /* Outline putih */
            padding: 14px; border-radius: 50px;
            font-weight: 700; font-size: 0.95rem;
            text-transform: uppercase;
            width: 100%; display: flex; justify-content: center; align-items: center; gap: 10px;
            text-decoration: none;
        }
        .btn-dashboard:hover { color: #fff; background: rgba(255,255,255,0.1); }

        /* Helper Classes */
        .text-warning-custom { color: #ffcc00; }
        .text-danger-custom { color: #ff3b30; }
    </style>
</head>

<body>

    <div class="scanner-wrapper" id="qrSection">
        <div id="reader"></div>
        
        <div class="scan-frame">
            <div class="scan-line"></div>
        </div>

        <div class="position-absolute top-50 start-50 translate-middle text-center w-75" id="permissionBtn" style="display:none; z-index:50;">
            <div class="bg-dark p-4 rounded-3 border border-secondary">
                <i class="fas fa-camera-slash fa-3x text-danger mb-3"></i>
                <h5 class="text-white">Kamera Error</h5>
                <button class="btn btn-light rounded-pill px-4 mt-2 fw-bold" onclick="forceReload()">Refresh</button>
            </div>
        </div>
    </div>

    <div class="verification-overlay" id="verifSection">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="text-white fw-bold m-0">Konfirmasi</h5>
            <button class="btn btn-sm btn-dark rounded-pill px-3 border border-secondary" onclick="resetScan()">
                <i class="fas fa-times"></i> Batal
            </button>
        </div>

        <div class="text-center mb-4">
            <div class="avatar-circle mx-auto mb-2" style="width:70px; height:70px; font-size:1.5rem;">
                <img src="" id="dbPhoto" class="avatar-img d-none">
                <span id="dbInitials">U</span>
            </div>
            <h4 class="text-white fw-bold mb-0" id="dbName">User Name</h4>
            <div class="text-secondary small mt-1">
                <span id="dbRole">Role</span> | <span id="dbBranch">Branch</span>
            </div>
        </div>

        <p class="text-secondary small fw-bold text-center text-uppercase mb-2">
            <i class="fas fa-camera me-1"></i> Ambil Foto Bukti
        </p>
        <div class="camera-preview-box">
            <video id="camera-stream" autoplay playsinline muted></video>
            <canvas id="camera-canvas"></canvas>
        </div>

        <div id="step-capture-btn">
            <button class="btn btn-light w-100 py-3 rounded-pill fw-bold" onclick="capturePhoto()">
                <i class="fas fa-camera fa-lg me-2"></i> JEPRET FOTO
            </button>
        </div>

        <div id="step-confirm-btn" style="display: none;">
            <div class="mb-3">
                <textarea id="scanNotes" class="form-control bg-dark text-white border-secondary" 
                    rows="2" placeholder="Catatan (Opsional)..."></textarea>
            </div>
            
            <button class="btn btn-dark w-100 py-2 rounded-pill border-secondary mb-3 text-secondary" onclick="retakePhoto()">
                <i class="fas fa-redo me-1"></i> Foto Ulang
            </button>

            <div class="row g-2">
                <div class="col-6">
                    <button class="btn btn-success w-100 py-3 rounded-4 fw-bold btn-masuk" onclick="submitAttendance('masuk')">
                        MASUK
                    </button>
                </div>
                <div class="col-6">
                    <button class="btn btn-warning w-100 py-3 rounded-4 fw-bold btn-pulang text-dark" onclick="submitAttendance('pulang')">
                        PULANG
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="result-overlay" id="resultOverlay">
        <div class="result-card">
            
            <div class="status-bar-top" id="resStatusBar"></div>

            <div class="result-header">
                <i class="fas fa-check-circle fa-lg text-success" id="resIcon"></i>
                <span class="text-status" id="resTitle">BERHASIL</span>
            </div>

            <div class="avatar-circle">
                <span id="resInitials">BI</span>
                <img id="resProfileImg" class="avatar-img d-none" src="">
            </div>

            <div class="user-name" id="resName">Nama User</div>
            <div class="user-meta">
                <span id="resDivision">Divisi</span> | <span id="resBranch">Cabang</span>
            </div>

            <div id="resNotesContainer" class="d-none mb-3 px-3">
                <div class="p-2 border border-secondary rounded bg-dark">
                    <small class="text-warning fst-italic" id="resNotes">Notes...</small>
                </div>
            </div>

            <div class="label-proof">FOTO BUKTI</div>
            <div class="proof-img-container">
                <img id="resCapturedImg" class="proof-img" src="">
            </div>

            <div class="time-display" id="resTime">00:00</div>
            <div class="date-display" id="resDate">13 Dec 2025</div>
            <div class="msg-display" id="resMessage">Absen MASUK Berhasil</div>

            <div class="btn-container">
                <button class="btn-scan-next" onclick="resetScan()">
                    <i class="fas fa-qrcode"></i> SCAN SELANJUTNYA
                </button>
                
                <a href="{{ url('/dashboard') }}" class="btn-dashboard">
                    <i class="fas fa-arrow-left"></i> DASHBOARD
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

        // --- 1. SCANNER LOGIC ---
        async function startQRScanner() {
            if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                alert("Wajib HTTPS untuk kamera HP!");
            }

            document.getElementById('permissionBtn').style.display = 'none';
            isProcessing = false;

            if (html5QrCode) {
                try {
                    if (html5QrCode.isScanning) { await html5QrCode.stop(); }
                    html5QrCode.clear();
                } catch (e) { console.warn(e); }
            }

            html5QrCode = new Html5Qrcode("reader");
            
            // Config Optimal HP
            const config = { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 };
            
            try {
                await html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess, ()=>{});
            } catch (err) {
                console.error("Cam Error:", err);
                document.getElementById('permissionBtn').style.display = 'block';
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            if (isProcessing) return;
            isProcessing = true;

            html5QrCode.stop().then(() => {
                html5QrCode.clear();
                checkUser(decodedText);
            }).catch(err => checkUser(decodedText));
        }

        function forceReload() { location.reload(); }

        // --- 2. CHECK USER ---
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
                alert('Koneksi Error');
                isProcessing = false;
                startQRScanner();
            });
        }

        // --- 3. SHOW VERIFICATION ---
        function showVerificationPage(user) {
            currentUserId = user.id;
            
            // Populate Data
            document.getElementById('dbName').innerText = user.name;
            document.getElementById('dbRole').innerText = user.role;
            document.getElementById('dbBranch').innerText = user.branch;
            
            // Initials Logic
            let initials = user.name.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase();
            document.getElementById('dbInitials').innerText = initials;

            if(user.photo_url && !user.photo_url.includes('ui-avatars')) {
                let img = document.getElementById('dbPhoto');
                img.src = user.photo_url;
                img.classList.remove('d-none');
                document.getElementById('dbInitials').classList.add('d-none');
            } else {
                document.getElementById('dbPhoto').classList.add('d-none');
                document.getElementById('dbInitials').classList.remove('d-none');
            }

            retakePhoto(); // Reset form state
            document.getElementById('scanNotes').value = '';

            document.getElementById('qrSection').style.display = 'none';
            document.getElementById('verifSection').style.display = 'flex';

            setTimeout(startCameraStream, 400);
        }

        // --- 4. CAMERA BUKTI ---
        function startCameraStream() {
            const video = document.getElementById('camera-stream');
            video.setAttribute('autoplay', '');
            video.setAttribute('muted', '');
            video.setAttribute('playsinline', '');

            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
                    .then(stream => {
                        streamRef = stream;
                        video.srcObject = stream;
                        video.play();
                    })
                    .catch(err => alert("Gagal akses kamera bukti."));
            }
        }

        function capturePhoto() {
            const video = document.getElementById('camera-stream');
            const canvas = document.getElementById('camera-canvas');
            
            if (video.videoWidth === 0) return alert("Kamera loading...");

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            
            capturedImageBase64 = canvas.toDataURL('image/jpeg', 0.6);

            video.style.display = 'none';
            canvas.style.display = 'block';
            
            document.getElementById('step-capture-btn').style.display = 'none';
            document.getElementById('step-confirm-btn').style.display = 'block';
        }

        function retakePhoto() {
            capturedImageBase64 = null;
            document.getElementById('camera-stream').style.display = 'block';
            document.getElementById('camera-canvas').style.display = 'none';
            document.getElementById('step-capture-btn').style.display = 'block';
            document.getElementById('step-confirm-btn').style.display = 'none';
        }

        // --- 5. SUBMIT ---
        function submitAttendance(type) {
            if (!capturedImageBase64) return alert("Foto bukti wajib!");

            const btnClass = type === 'masuk' ? '.btn-masuk' : '.btn-pulang';
            const btn = document.querySelector(btnClass);
            const originalText = btn.innerText;
            
            btn.innerText = 'Loading...';
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
                    resetButtons(btn, originalText);
                }
            })
            .catch(err => {
                alert("Error System");
                resetButtons(btn, originalText);
            });
        }

        function resetButtons(btn, text) {
            btn.innerText = text;
            document.querySelectorAll('button').forEach(b => b.disabled = false);
        }

        // --- 6. RESULT DISPLAY (SESUAI GAMBAR) ---
        function showResult(message, data) {
            if (streamRef) streamRef.getTracks().forEach(t => t.stop());

            document.getElementById('verifSection').style.display = 'none';
            document.getElementById('resultOverlay').style.display = 'flex';

            // Populate Data
            document.getElementById('resName').innerText = data.name;
            document.getElementById('resDivision').innerText = data.division; // DIVISI
            document.getElementById('resBranch').innerText = data.branch; // CABANG
            
            // Notes
            const notesEl = document.getElementById('resNotesContainer');
            if (data.notes && data.notes.trim()) {
                document.getElementById('resNotes').innerText = data.notes;
                notesEl.classList.remove('d-none');
            } else {
                notesEl.classList.add('d-none');
            }

            // Initials / Photo Avatar
            let initials = data.name.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase();
            document.getElementById('resInitials').innerText = initials;
            
            let profileImg = document.getElementById('resProfileImg');
            if (data.profile_photo && !data.profile_photo.includes('ui-avatars')) {
                profileImg.src = data.profile_photo;
                profileImg.classList.remove('d-none');
                document.getElementById('resInitials').classList.add('d-none');
            } else {
                profileImg.classList.add('d-none');
                document.getElementById('resInitials').classList.remove('d-none');
            }

            // Proof Image
            document.getElementById('resCapturedImg').src = data.photo;

            // Time & Msg
            document.getElementById('resTime').innerText = data.time;
            document.getElementById('resDate').innerText = data.date;
            document.getElementById('resMessage').innerText = message;

            // Warna Status Logic
            const statusBar = document.getElementById('resStatusBar');
            const icon = document.getElementById('resIcon');
            const title = document.getElementById('resTitle');
            const timeText = document.getElementById('resTime');
            const msgText = document.getElementById('resMessage');

            // Reset
            icon.className = 'fas fa-lg'; 
            
            if (data.is_late) {
                // Merah
                let color = '#ff3b30';
                statusBar.style.background = color;
                icon.classList.add('fa-exclamation-triangle');
                icon.style.color = color;
                title.innerText = "TERLAMBAT";
                timeText.style.color = color;
                msgText.style.color = color;
            } else if (data.is_early_checkout) {
                // Kuning
                let color = '#ffcc00';
                statusBar.style.background = color;
                icon.classList.add('fa-walking');
                icon.style.color = color;
                title.innerText = "PULANG CEPAT";
                timeText.style.color = color;
                msgText.style.color = color;
            } else {
                // Hijau (Default)
                let color = '#28cd41';
                statusBar.style.background = color;
                icon.classList.add('fa-check-circle');
                icon.style.color = color;
                title.innerText = "BERHASIL";
                timeText.style.color = color;
                msgText.style.color = '#00d2d3'; // Cyan for message
            }
        }

        // --- 7. RESET FLOW ---
        function resetScan() {
            if (streamRef) streamRef.getTracks().forEach(t => t.stop());

            document.getElementById('resultOverlay').style.display = 'none';
            document.getElementById('verifSection').style.display = 'none';
            document.getElementById('qrSection').style.display = 'block'; // Block biar full

            resetButtons(document.querySelector('.btn-masuk'), 'MASUK'); // Hack reset text
            document.getElementById('scanNotes').value = '';
            document.getElementById('resNotesContainer').classList.add('d-none');

            startQRScanner();
        }

        document.addEventListener('DOMContentLoaded', startQRScanner);
    </script>
</body>
</html>