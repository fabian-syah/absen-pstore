<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Work Wrapped {{ $year }}</title>
    <link rel="stylesheet" href="{{ asset('vendors/mdi/css/materialdesignicons.min.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;500;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        :root {
            --bg-dark: #0f0f0f;
            --gold: #FFD700;
            --gold-gradient: linear-gradient(135deg, #FFD700 0%, #FDB931 100%);
            --glass: rgba(255, 255, 255, 0.1);
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #000;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: white;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        #story-container {
            width: 100%;
            max-width: 450px;
            height: 100%;
            max-height: 850px;
            position: relative;
            background: #111;
            overflow: hidden;
            box-shadow: 0 0 50px rgba(255, 215, 0, 0.1);
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 40px;
            box-sizing: border-box;
            opacity: 0;
            transition: opacity 0.5s ease-in-out, transform 0.5s ease;
            transform: scale(0.95);
            z-index: 1;
            background-size: cover;
            background-position: center;
            pointer-events: none;
            /* Biar klik tembus ke navigasi */
        }

        .slide.active {
            opacity: 1;
            transform: scale(1);
            z-index: 10;
        }

        .bg-gradient-1 {
            background: radial-gradient(circle at top right, #2c3e50, #000);
        }

        .bg-gradient-2 {
            background: linear-gradient(45deg, #141E30, #243B55);
        }

        .bg-gradient-3 {
            background: radial-gradient(circle, #4B0000, #000);
        }

        .bg-final {
            background: linear-gradient(135deg, #1c1c1c 0%, #000000 100%);
        }

        h1 {
            font-size: 3rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--gold);
        }

        p {
            font-size: 1.1rem;
            opacity: 0.8;
            line-height: 1.6;
        }

        .highlight {
            color: var(--gold);
            font-weight: bold;
        }

        .big-number {
            font-size: 5rem;
            font-weight: 800;
            background: var(--gold-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 20px 0;
        }

        .avatar-glow {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid var(--gold);
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.3);
            object-fit: cover;
            margin-bottom: 30px;
        }

        .persona-badge {
            background: rgba(255, 215, 0, 0.1);
            border: 1px solid var(--gold);
            padding: 20px;
            border-radius: 20px;
            margin-top: 30px;
            backdrop-filter: blur(10px);
        }

        .progress-container {
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            display: flex;
            gap: 5px;
            padding: 0 10px;
            z-index: 20;
        }

        .progress-bar {
            flex: 1;
            height: 3px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: white;
            width: 0%;
            transition: width 0.1s linear;
        }

        /* Navigasi Tap Area */
        .tap-area {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 50%;
            z-index: 15;
            cursor: pointer;
        }

        .left {
            left: 0;
        }

        .right {
            right: 0;
        }

        /* FIXED: Tombol Share sekarang punya z-index lebih tinggi dari tap area */
        .action-btn {
            position: absolute;
            bottom: 80px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 100;
            /* Pastikan di atas tap area */
            background: var(--gold-gradient);
            color: black;
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            display: none;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            pointer-events: auto;
            /* Pastikan bisa diklik */
            white-space: nowrap;
        }

        .back-link {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            opacity: 0.5;
            text-decoration: none;
            font-size: 0.9rem;
            z-index: 100;
            /* Pastikan di atas tap area */
            pointer-events: auto;
        }

        .sparkle {
            position: absolute;
            background: white;
            border-radius: 50%;
            animation: float 3s infinite ease-in-out;
            opacity: 0;
        }

        @keyframes float {
            0% {
                transform: translateY(0) scale(0);
                opacity: 0;
            }

            50% {
                opacity: 1;
            }

            100% {
                transform: translateY(-100px) scale(1.5);
                opacity: 0;
            }
        }

        #capture-area {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-image: url('https://www.transparenttextures.com/patterns/stardust.png');
        }
    </style>
</head>

<body>

    <div id="story-container">
        <div class="progress-container">
            <div class="progress-bar">
                <div class="progress-fill" id="p1"></div>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" id="p2"></div>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" id="p3"></div>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" id="p4"></div>
            </div>
        </div>

        <div class="tap-area left" onclick="prevSlide()"></div>
        <div class="tap-area right" onclick="nextSlide()"></div>

        <button class="action-btn" onclick="downloadImage()" id="shareBtn">
            <i class="mdi mdi-share-variant"></i> Simpan / Share
        </button>

        <a href="{{ route('dashboard') }}" class="back-link">
            Kembali ke Dashboard
        </a>

        <div class="slide active bg-gradient-1" id="slide1">
            <div style="font-size: 5rem;">👋</div>
            <h1>Halo,<br>{{ explode(' ', $user->name)[0] }}!</h1>
            <p>2025 sudah berlalu.<br>Ini rekap performa kerjamu yang <b>Terverifikasi</b>.</p>
            <p style="margin-top: 50px; font-size: 0.9rem; opacity: 0.5;">Ketuk kanan untuk lanjut &rarr;</p>
        </div>

        <div class="slide bg-gradient-2" id="slide2">
            <h2 style="color:white; opacity: 0.8">Absensi Valid & Terverifikasi</h2>
            <div class="big-number">{{ $totalPresent }}</div>
            <h2>HARI KERJA</h2>
            <hr style="width: 50px; border-color: rgba(255,255,255,0.2); margin: 30px 0;">
            <p>Total dedikasi waktu kerjamu:<br><span class="highlight"
                    style="font-size: 2rem;">{{ number_format($totalHours) }} Jam</span></p>
            <small style="opacity: 0.5; font-size: 0.8rem;">(Hanya menghitung sesi Masuk & Pulang lengkap)</small>
        </div>

        <div class="slide bg-gradient-3" id="slide3">
            <h2>On-Time Score</h2>
            <div
                style="position: relative; width: 200px; height: 200px; display: flex; align-items: center; justify-content: center; margin: 30px 0;">
                <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                        fill="none" stroke="#444" stroke-width="2" />
                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                        fill="none" stroke="#FFD700" stroke-width="2"
                        stroke-dasharray="{{ $onTimePercentage }}, 100" />
                </svg>
                <div style="position: absolute; font-size: 3rem; font-weight: bold;">{{ $onTimePercentage }}%</div>
            </div>
            <p>Tingkat kedisiplinanmu.</p>
            @if ($earliestCheckIn)
                <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; margin-top: 20px;">
                    <small>Rekor Masuk Paling Pagi:</small><br>
                    <strong style="color: var(--gold); font-size: 1.5rem;">
                        {{ \Carbon\Carbon::parse($earliestCheckIn->check_in_time)->format('H:i') }}
                    </strong>
                    <br><small>{{ \Carbon\Carbon::parse($earliestCheckIn->check_in_time)->translatedFormat('d M Y') }}</small>
                </div>
            @endif
        </div>

        <div class="slide bg-final" id="slide4" style="padding: 0;">
            <div id="capture-area" style="padding: 40px; box-sizing: border-box;">
                <img src="{{ $user->profile_photo_path ? Storage::url($user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=FFD700&color=000' }}"
                    class="avatar-glow">

                <h2 style="margin:0; font-size: 1rem; opacity: 0.7; letter-spacing: 2px;">MY 2025 WORK PERSONA</h2>
                <h1 style="color: var(--gold); font-size: 2.5rem; margin-top: 10px;">{{ $persona['title'] }}</h1>

                <div class="persona-badge">
                    <i class="mdi {{ $persona['icon'] }}" style="font-size: 3rem; color: var(--gold);"></i>
                    <p style="margin-top: 10px; font-weight: 500;">"{{ $persona['desc'] }}"</p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; width: 100%; margin-top: 40px;">
                    <div style="background: rgba(255,255,255,0.05); padding: 15px; border-radius: 12px;">
                        <small style="opacity:0.5">Hari Valid</small>
                        <div style="font-size: 1.5rem; font-weight: bold;">{{ $totalPresent }}</div>
                    </div>
                    <div style="background: rgba(255,255,255,0.05); padding: 15px; border-radius: 12px;">
                        <small style="opacity:0.5">Jam Produktif</small>
                        <div style="font-size: 1.5rem; font-weight: bold;">{{ number_format($totalHours) }}</div>
                    </div>
                </div>

                <div style="margin-top: auto; opacity: 0.5; font-size: 0.8rem; letter-spacing: 1px;">
                    PSTORE ATTENDANCE WRAPPED
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const progressFills = document.querySelectorAll('.progress-fill');
        const totalSlides = slides.length;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.remove('active');
                if (i === index) slide.classList.add('active');
            });

            progressFills.forEach((fill, i) => {
                if (i < index) fill.style.width = '100%';
                else if (i === index) fill.style.width = '100%';
                else fill.style.width = '0%';
            });

            // Show share button only on last slide
            const shareBtn = document.getElementById('shareBtn');
            const backLink = document.querySelector('.back-link');

            if (index === totalSlides - 1) {
                shareBtn.style.display = 'flex';
                backLink.style.display = 'block';
            } else {
                shareBtn.style.display = 'none';
                backLink.style.display = 'none';
            }
        }

        function nextSlide() {
            if (currentSlide < totalSlides - 1) {
                currentSlide++;
                showSlide(currentSlide);
            }
        }

        function prevSlide() {
            if (currentSlide > 0) {
                currentSlide--;
                showSlide(currentSlide);
            }
        }

        function createSparkles() {
            const container = document.getElementById('slide4');
            for (let i = 0; i < 20; i++) {
                let sparkle = document.createElement('div');
                sparkle.className = 'sparkle';
                sparkle.style.width = Math.random() * 5 + 'px';
                sparkle.style.height = sparkle.style.width;
                sparkle.style.left = Math.random() * 100 + '%';
                sparkle.style.top = Math.random() * 100 + '%';
                sparkle.style.animationDelay = Math.random() * 2 + 's';
                container.appendChild(sparkle);
            }
        }
        createSparkles();

        // === BAGIAN YANG DIPERBAIKI ADA DI SINI ===
        function downloadImage() {
            const element = document.getElementById('capture-area');
            const btn = document.getElementById('shareBtn');
            btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Memproses...';

            html2canvas(element, {
                scale: 2,
                // backgroundColor: null,  <-- BARIS INI DIHAPUS AGAR BACKGROUND TIDAK TRANSPARAN
                useCORS: true
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'My-Work-Wrapped-2025.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
                btn.innerHTML = '<i class="mdi mdi-check"></i> Tersimpan!';
                setTimeout(() => {
                    btn.innerHTML = '<i class="mdi mdi-share-variant"></i> Simpan / Share';
                }, 2000);
            });
        }
        // ==========================================
    </script>
</body>

</html>
