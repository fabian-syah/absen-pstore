<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Security Scanner - AbsenPS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #000; height: 100vh; overflow: hidden; font-family: 'Segoe UI', sans-serif; margin: 0; }
        .scanner-wrapper { position: relative; width: 100%; height: 100%; background: black; }
        #reader { width: 100%; height: 100%; object-fit: cover; }
        #reader video { width: 100% !important; height: 100% !important; object-fit: cover !important; border-radius: 0 !important; }
        .scanner-header { position: absolute; top: 0; left: 0; width: 100%; padding: 20px; background: linear-gradient(to bottom, rgba(0,0,0,0.8), transparent); color: white; z-index: 20; text-align: center; }
        .scan-area { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 260px; height: 260px; z-index: 15; pointer-events: none; }
        .corner { position: absolute; width: 40px; height: 40px; border-color: #00aa13; border-style: solid; border-width: 0; box-shadow: 0 0 10px rgba(0, 170, 19, 0.5); }
        .corner-tl { top: 0; left: 0; border-top-width: 5px; border-left-width: 5px; border-top-left-radius: 20px; }
        .corner-tr { top: 0; right: 0; border-top-width: 5px; border-right-width: 5px; border-top-right-radius: 20px; }
        .corner-bl { bottom: 0; left: 0; border-bottom-width: 5px; border-left-width: 5px; border-bottom-left-radius: 20px; }
        .corner-br { bottom: 0; right: 0; border-bottom-width: 5px; border-right-width: 5px; border-bottom-right-radius: 20px; }
        .scan-laser { position: absolute; width: 100%; height: 2px; background: rgba(0, 255, 0, 0.5); box-shadow: 0 0 4px rgba(0, 255, 0, 0.8); top: 0; animation: scan 3s infinite ease-in-out; opacity: 0.6; }
        @keyframes scan { 0%, 100% { top: 10%; opacity: 0; } 50% { top: 90%; opacity: 1; } }
        .permission-btn-container { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 30; text-align: center; display: none; }
        
        .verification-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #121212; z-index: 50; display: none; flex-direction: column; padding: 20px; overflow-y: auto; }
        .profile-card { background: #1e1e1e; border-radius: 15px; padding: 20px; text-align: center; color: white; border: 1px solid #333; margin-bottom: 20px; }
        .profile-img-db { width: 80px; height: 80px; border-radius: 50%; border: 3px solid #00aa13; object-fit: cover; }
        .camera-preview-box { width: 100%; height: 350px; background: black; border-radius: 15px; overflow: hidden; position: relative; border: 2px solid #444; margin-bottom: 20px; }
        #camera-stream { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
        .action-buttons { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .btn-absen { padding: 15px; font-weight: bold; border-radius: 12px; border: none; color: white; font-size: 1rem; transition: transform 0.1s; }
        .btn-absen:active { transform: scale(0.98); }
        .btn-masuk { background: linear-gradient(45deg, #00b09b, #96c93d); }
        .btn-pulang { background: linear-gradient(45deg, #ff5f6d, #ffc371); }
        .btn-malam { grid-column: span 2; background: linear-gradient(45deg, #4b6cb7, #182848); }
        .result-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 100; display: none; align-items: center; justify-content: center; text-align: center; color: white; }
    </style>
</head>
<body>

    {{-- SECTION 1: SCANNER UI --}}
    <div class="scanner-wrapper" id="qrSection">
        <div class="scanner-header">
            <h5 class="m-0 fw-bold"><i class="fas fa-qrcode me-2"></i>Scan Absensi</h5>
            <small class="text-white-50">Arahkan kamera ke QR Code</small>
        </div>
        <div id="reader"></div>
        <div class="scan-area">
            <div class="corner corner-tl"></div><div class="corner corner-tr"></div><div class="corner corner-bl"></div><div class="corner corner-br"></div><div class="scan-laser"></div>
        </div>
        <div class="permission-btn-container" id="permissionBtn">
            <i class="fas fa-camera-slash display-4 text-white mb-3"></i>
            <h5 class="text-white mb-3">Akses Kamera Diblokir</h5>
            <button class="btn btn-success rounded-pill px-4" onclick="startQRScanner()">Izinkan Kamera</button>
            <p class="text-white-50 mt-3 small">Pastikan Anda menggunakan HTTPS atau Localhost</p>
        </div>
    </div>

    {{-- SECTION 2: VERIFICATION UI --}}
    <div class="verification-overlay" id="verifSection">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="text-white m-0">Konfirmasi Absensi</h5>
            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="resetScan()"><i class="fas fa-times me-1"></i> Batal</button>
        </div>
        <div class="profile-card">
            <img src="" id="dbPhoto" class="profile-img-db mb-3" alt="User">
            <h4 id="dbName" class="fw-bold m-0">Nama Karyawan</h4>
            <p id="dbRole" class="text-muted small m-0">Jabatan</p>
            <span id="dbBranch" class="badge bg-primary mt-2">Cabang</span>
        </div>
        <div class="text-white mb-2 fw-bold small text-uppercase"><i class="fas fa-camera me-1"></i> Ambil Foto Bukti (Wajib)</div>
        <div class="camera-preview-box">
            <video id="camera-stream" autoplay playsinline muted></video>
            <canvas id="camera-canvas" style="display:none;"></canvas>
        </div>
        <div class="action-buttons">
            <button class="btn-absen btn-masuk" onclick="submitAttendance('masuk')"><i class="fas fa-sign-in-alt fa-lg mb-1 d-block"></i> MASUK</button>
            <button class="btn-absen btn-pulang" onclick="submitAttendance('pulang')"><i class="fas fa-sign-out-alt fa-lg mb-1 d-block"></i> PULANG</button>
        </div>
    </div>

    {{-- SECTION 3: RESULT OVERLAY --}}
    <div class="result-overlay" id="resultOverlay">
        <div class="p-4 w-100" style="max-width: 400px;">
            <div id="resultIcon" class="mb-3" style="font-size: 5rem;"></div>
            <h2 id="resultTitle" class="fw-bold mb-2"></h2>
            <p id="resultMessage" class="text-white-50 mb-4 fs-5"></p>
            <img id="capturedPhoto" src="" style="width: 200px; height: 200px; object-fit: cover; border-radius: 15px; border: 4px solid white; display: none; box-shadow: 0 10px 30px rgba(0,0,0,0.5);" class="mx-auto mb-4">
            <button class="btn btn-light w-100 py-3 rounded-pill fw-bold text-uppercase" onclick="resetScan()"><i class="fas fa-qrcode me-2"></i> Scan Selanjutnya</button>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let html5QrCode = null;
        let currentUserId = null;
        let streamRef = null;

        function startQRScanner() {
            document.getElementById('permissionBtn').style.display = 'none';
            html5QrCode = new Html5Qrcode("reader");
            
            // [FIX IOS] 
            // 1. Hapus aspectRatio: 1.0 (Biarkan default agar tidak zoom di iPhone)
            // 2. Gunakan videoConstraints yang spesifik
            const config = { 
                fps: 15, 
                qrbox: { width: 250, height: 250 },
                videoConstraints: {
                    facingMode: "environment",
                    focusMode: "continuous" // Bagus untuk Android, diabaikan iOS
                }
            };

            if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
                alert('Peringatan: Kamera mungkin tidak berjalan di HTTP. Harap gunakan HTTPS atau Localhost.');
            }

            html5QrCode.start({ facingMode: "environment" }, config, (decodedText) => {
                stopQRScanner(); checkUser(decodedText);
            }, (errorMessage) => {
                // Ignore parsing error
            }).catch((err) => {
                console.error("Camera Error:", err);
                document.getElementById('permissionBtn').style.display = 'block';
                alert("Gagal akses kamera: " + err);
            });
        }

        function stopQRScanner() {
            if(html5QrCode) { html5QrCode.stop().then(() => { html5QrCode.clear(); }).catch(err => console.log(err)); }
        }

        function checkUser(qrCode) {
            fetch("{{ route('security.check-user') }}", {
                method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
                body: JSON.stringify({ qr_code: qrCode })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') { showVerificationPage(data.data); } 
                else { alert(data.message); startQRScanner(); }
            })
            .catch(err => { alert('Gagal terhubung ke server.'); startQRScanner(); });
        }

        function showVerificationPage(user) {
            currentUserId = user.id;
            document.getElementById('dbName').innerText = user.name;
            document.getElementById('dbRole').innerText = user.role + ' - ' + user.division;
            document.getElementById('dbBranch').innerText = user.branch;
            document.getElementById('dbPhoto').src = user.photo_url;
            document.getElementById('qrSection').style.display = 'none';
            document.getElementById('verifSection').style.display = 'flex';
            startCameraStream();
        }

        function startCameraStream() {
            const video = document.getElementById('camera-stream');
            
            // [FIX IOS] Pastikan atribut video diset manual agar autoplay jalan
            video.setAttribute('autoplay', '');
            video.setAttribute('muted', '');
            video.setAttribute('playsinline', '');
            
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment", width: { ideal: 640 } } })
                .then(function (stream) { 
                    streamRef = stream; 
                    video.srcObject = stream; 
                    video.onloadedmetadata = function(e) {
                        video.play();
                    };
                })
                .catch(function (error) { 
                    console.error(error);
                    alert("Gagal akses kamera bukti. Pastikan izin kamera diberikan."); 
                });
            }
        }

        function submitAttendance(type) {
            const video = document.getElementById('camera-stream');
            const canvas = document.getElementById('camera-canvas');
            const context = canvas.getContext('2d');
            
            if (video.videoWidth === 0) { alert("Kamera belum siap."); return; }

            // 1. Resize Canvas (Downscale ke lebar 800px max)
            const scaleFactor = 800 / video.videoWidth;
            const newWidth = 800;
            const newHeight = video.videoHeight * scaleFactor;

            canvas.width = newWidth;
            canvas.height = newHeight;
            context.drawImage(video, 0, 0, newWidth, newHeight);

            // 2. Convert to JPEG Quality 60%
            const imageBase64 = canvas.toDataURL('image/jpeg', 0.6);

            const btn = document.querySelector(`.btn-${type}`);
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>';
            document.querySelectorAll('.btn-absen').forEach(b => b.disabled = true);

            fetch("{{ route('security.store-attendance') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
                body: JSON.stringify({ user_id: currentUserId, type: type, image: imageBase64 })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') { showResult('success', data.message, data.data.photo); } 
                else { alert(data.message); btn.innerHTML = originalContent; document.querySelectorAll('.btn-absen').forEach(b => b.disabled = false); }
            })
            .catch(err => { alert("Error sistem."); btn.innerHTML = originalContent; document.querySelectorAll('.btn-absen').forEach(b => b.disabled = false); });
        }

        function showResult(status, message, photoUrl = null) {
            const overlay = document.getElementById('resultOverlay');
            const icon = document.getElementById('resultIcon');
            const title = document.getElementById('resultTitle');
            const msg = document.getElementById('resultMessage');
            const img = document.getElementById('capturedPhoto');
            if(streamRef) { streamRef.getTracks().forEach(track => track.stop()); }
            overlay.style.display = 'flex'; msg.innerText = message;
            if(status === 'success') {
                icon.innerHTML = '<i class="fas fa-check-circle text-success"></i>'; title.innerText = "BERHASIL"; title.className = "fw-bold mb-2 text-success";
                if(photoUrl) { img.src = photoUrl; img.style.display = 'block'; }
            } else {
                icon.innerHTML = '<i class="fas fa-times-circle text-danger"></i>'; title.innerText = "GAGAL"; title.className = "fw-bold mb-2 text-danger"; img.style.display = 'none';
            }
        }

        function resetScan() {
            if(streamRef) { streamRef.getTracks().forEach(track => track.stop()); }
            document.getElementById('verifSection').style.display = 'none';
            document.getElementById('resultOverlay').style.display = 'none';
            document.getElementById('qrSection').style.display = 'block';
            document.querySelectorAll('.btn-absen').forEach(b => b.disabled = false);
            document.querySelector('.btn-masuk').innerHTML = '<i class="fas fa-sign-in-alt fa-lg mb-1 d-block"></i> MASUK';
            document.querySelector('.btn-pulang').innerHTML = '<i class="fas fa-sign-out-alt fa-lg mb-1 d-block"></i> PULANG';
            startQRScanner();
        }

        document.addEventListener('DOMContentLoaded', startQRScanner);
    </script>
</body>
</html>