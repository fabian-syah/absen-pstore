<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>My Work Wrapped {{ $year }}</title>
    
    <link rel="stylesheet" href="{{ asset('vendors/mdi/css/materialdesignicons.min.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;500;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    
    <style>
        :root {
            --bg-dark: #0f0f0f;
            --gold: #FFD700;
            --gold-gradient: linear-gradient(135deg, #FFD700 0%, #FDB931 100%);
        }
        
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        body {
            margin: 0; padding: 0;
            background-color: #000;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: white;
            overflow: hidden; 
            display: flex; justify-content: center; align-items: center;
            height: 100dvh; /* Pakai dvh biar pas di HP */
            width: 100vw;
        }

        #story-container {
            width: 100%; max-width: 480px; height: 100%;
            position: relative; background: #111;
            overflow: hidden; box-shadow: 0 0 50px rgba(255, 215, 0, 0.1);
        }

        /* --- SLIDES --- */
        .slide {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            text-align: center; padding: 30px;
            opacity: 0; pointer-events: none; z-index: 1;
            transition: opacity 0.4s ease-in-out, transform 0.4s ease;
            transform: scale(0.95);
            background-size: cover; background-position: center;
        }

        .slide.active { opacity: 1; transform: scale(1); z-index: 10; pointer-events: auto; }

        /* Backgrounds */
        .bg-gradient-1 { background: radial-gradient(circle at top right, #2c3e50, #000); }
        .bg-gradient-2 { background: linear-gradient(160deg, #141E30, #243B55); }
        .bg-gradient-3 { background: radial-gradient(circle, #4B0000, #000); }
        .bg-final { background: linear-gradient(135deg, #1c1c1c 0%, #000000 100%); }

        /* Typography */
        h1 { font-size: 2.5rem; font-weight: 800; line-height: 1.2; margin-bottom: 15px; text-transform: uppercase; }
        h2 { font-size: 1.5rem; font-weight: 700; margin-bottom: 10px; color: var(--gold); }
        p { font-size: 1rem; opacity: 0.9; line-height: 1.6; margin-bottom: 20px; }
        .big-number { font-size: 4rem; font-weight: 800; background: var(--gold-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin: 10px 0; }

        /* Components */
        .avatar-glow {
            width: 100px; height: 100px; border-radius: 50%;
            border: 3px solid var(--gold);
            box-shadow: 0 0 25px rgba(255, 215, 0, 0.4);
            object-fit: cover; margin-bottom: 20px;
        }

        .persona-badge {
            background: rgba(255, 215, 0, 0.1); border: 1px solid var(--gold);
            padding: 15px 20px; border-radius: 16px; margin-top: 20px;
            backdrop-filter: blur(10px); width: 100%;
        }

        /* Progress Bar */
        .progress-container {
            position: absolute; top: 15px; left: 0; right: 0;
            display: flex; gap: 4px; padding: 0 10px; z-index: 50;
        }
        .progress-bar { flex: 1; height: 3px; background: rgba(255,255,255,0.3); border-radius: 3px; overflow: hidden; }
        .progress-fill { height: 100%; background: white; width: 0%; transition: width 0.1s linear; }

        /* Navigation Areas */
        .tap-area { position: absolute; top: 0; bottom: 0; width: 40%; z-index: 20; }
        .tap-area.left { left: 0; }
        .tap-area.right { right: 0; }

        /* Tombol Start (Pemicu Audio) */
        .start-btn {
            background: var(--gold-gradient); color: #000;
            border: none; padding: 15px 40px; border-radius: 50px;
            font-weight: 800; font-size: 1.1rem; cursor: pointer;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.4);
            animation: pulse-btn 2s infinite; z-index: 100; position: relative;
            margin-top: 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
        }

        /* Tombol Share */
        .action-btn {
            position: absolute; bottom: 80px; left: 50%; transform: translateX(-50%);
            z-index: 100; background: var(--gold-gradient); color: black;
            border: none; padding: 14px 28px; border-radius: 50px;
            font-weight: bold; font-size: 0.95rem; cursor: pointer;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
            display: none; align-items: center; gap: 8px;
            white-space: nowrap; pointer-events: auto;
        }

        .back-link {
            position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%);
            color: rgba(255,255,255,0.6); text-decoration: none; font-size: 0.85rem;
            z-index: 100; pointer-events: auto;
        }

        /* Music Toggle */
        .music-toggle {
            position: absolute; top: 25px; right: 15px; z-index: 100;
            background: rgba(0,0,0,0.4); backdrop-filter: blur(4px);
            border: 1px solid rgba(255,255,255,0.2);
            color: white; width: 36px; height: 36px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; pointer-events: auto;
        }
        .music-toggle.muted .mdi-music { display: none; }
        .music-toggle.muted .mdi-music-off { display: block; }
        .music-toggle:not(.muted) .mdi-music { display: block; }
        .music-toggle:not(.muted) .mdi-music-off { display: none; }

        @keyframes pulse-btn {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); box-shadow: 0 0 30px rgba(255, 215, 0, 0.6); }
            100% { transform: scale(1); }
        }

        /* Sparkles */
        .sparkle {
            position: absolute; background: white; border-radius: 50%;
            animation: float 3s infinite ease-in-out; opacity: 0; pointer-events: none;
        }
        @keyframes float {
            0% { transform: translateY(0) scale(0); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: translateY(-100px) scale(1.5); opacity: 0; }
        }

        #capture-area {
            width: 100%; height: 100%; display: flex;
            flex-direction: column; justify-content: center; align-items: center;
            background-image: url('https://www.transparenttextures.com/patterns/stardust.png');
            background-color: #111; padding: 30px;
        }

        /* Mobile Tweaks */
        @media (max-height: 700px) {
            h1 { font-size: 2rem; }
            .big-number { font-size: 3rem; }
            .avatar-glow { width: 80px; height: 80px; }
            .persona-badge { padding: 10px 15px; margin-top: 15px; }
            .persona-badge i { font-size: 2rem !important; }
            .action-btn { bottom: 60px; }
            .back-link { bottom: 20px; }
        }
    </style>
</head>
<body>

    <audio id="bgMusic" loop>
        <source src="{{ asset('music/song.mp3') }}" type="audio/mpeg">
    </audio>

    <div id="story-container">
        <div class="progress-container">
            <div class="progress-bar"><div class="progress-fill" id="p1"></div></div>
            <div class="progress-bar"><div class="progress-fill" id="p2"></div></div>
            <div class="progress-bar"><div class="progress-fill" id="p3"></div></div>
            <div class="progress-bar"><div class="progress-fill" id="p4"></div></div>
        </div>

        <div class="music-toggle muted" id="musicBtn" onclick="toggleMusic()">
            <i class="mdi mdi-music" style="font-size: 18px;"></i>
            <i class="mdi mdi-music-off" style="font-size: 18px;"></i>
        </div>

        <div class="tap-area left" onclick="prevSlide()"></div>
        <div class="tap-area right" onclick="nextSlide()"></div>

        <button class="action-btn" onclick="downloadImage()" id="shareBtn">
            <i class="mdi mdi-download"></i> Simpan Gambar
        </button>
        <a href="{{ route('dashboard') }}" class="back-link">Kembali ke Dashboard</a>

        <div class="slide active bg-gradient-1" id="slide1" style="pointer-events: auto;">
            <div style="font-size: 4rem; margin-bottom: 10px;">👋</div>
            <h1>Halo,<br>{{ explode(' ', $user->name)[0] }}!</h1>
            <p>Siap melihat rekap performa<br>kerja terverifikasimu di 2025?</p>
            
            <button class="start-btn" onclick="startExperience()">
                <i class="mdi mdi-play"></i> LIHAT REKAP
            </button>
            <p style="margin-top: 15px; font-size: 0.75rem; opacity: 0.6;">(Nyalakan volume suara)</p>
        </div>

        <div class="slide bg-gradient-2" id="slide2">
            <h2 style="color:white; opacity: 0.9; font-size: 1.2rem;">Absensi Terverifikasi</h2>
            <div class="big-number">{{ $totalPresent }}</div>
            <h2 style="font-size: 1.5rem;">HARI KERJA</h2>
            <div style="width: 40px; height: 2px; background: rgba(255,255,255,0.2); margin: 20px 0;"></div>
            <p>Total dedikasi waktu:<br><span class="highlight" style="font-size: 1.8rem;">{{ number_format($totalHours) }} Jam</span></p>
        </div>

        <div class="slide bg-gradient-3" id="slide3">
            <h2>On-Time Score</h2>
            <div style="position: relative; width: 180px; height: 180px; display: flex; align-items: center; justify-content: center; margin: 30px 0;">
                <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#444" stroke-width="2"/>
                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#FFD700" stroke-width="2" stroke-dasharray="{{ $onTimePercentage }}, 100"/>
                </svg>
                <div style="position: absolute; font-size: 2.5rem; font-weight: bold;">{{ $onTimePercentage }}%</div>
            </div>
            <p>Tingkat kedisiplinanmu.</p>
            @if($earliestCheckIn)
                <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 12px; margin-top: 15px; width: 100%;">
                    <small>Rekor Masuk Pagi:</small><br>
                    <strong style="color: var(--gold); font-size: 1.4rem;">
                        {{ \Carbon\Carbon::parse($earliestCheckIn->check_in_time)->format('H:i') }}
                    </strong>
                    <br><small style="opacity:0.7">{{ \Carbon\Carbon::parse($earliestCheckIn->check_in_time)->translatedFormat('d M Y') }}</small>
                </div>
            @endif
        </div>

        <div class="slide bg-final" id="slide4" style="padding: 0;">
            <div id="capture-area">
                <img src="{{ $user->profile_photo_path ? Storage::url($user->profile_photo_path) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=FFD700&color=000' }}" 
                     class="avatar-glow">
                
                <h2 style="margin:0; font-size: 0.9rem; opacity: 0.7; letter-spacing: 2px; text-transform: uppercase;">My 2025 Work Persona</h2>
                <h1 style="color: var(--gold); font-size: 2.2rem; margin-top: 10px; line-height: 1.1;">{{ $persona['title'] }}</h1>
                
                <div class="persona-badge">
                    <i class="mdi {{ $persona['icon'] }}" style="font-size: 2.5rem; color: var(--gold); display:block; margin-bottom: 10px;"></i>
                    <p style="margin: 0; font-weight: 500; font-size: 0.95rem;">"{{ $persona['desc'] }}"</p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; width: 100%; margin-top: 30px;">
                    <div style="background: rgba(255,255,255,0.08); padding: 15px; border-radius: 12px;">
                        <small style="opacity:0.6; display:block; margin-bottom:5px;">Hari Valid</small>
                        <div style="font-size: 1.4rem; font-weight: bold;">{{ $totalPresent }}</div>
                    </div>
                    <div style="background: rgba(255,255,255,0.08); padding: 15px; border-radius: 12px;">
                        <small style="opacity:0.6; display:block; margin-bottom:5px;">Jam Produktif</small>
                        <div style="font-size: 1.4rem; font-weight: bold;">{{ number_format($totalHours) }}</div>
                    </div>
                </div>

                <div style="margin-top: auto; opacity: 0.4; font-size: 0.7rem; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 20px;">
                    PStore Attendance Wrapped
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const progressFills = document.querySelectorAll('.progress-fill');
        const totalSlides = slides.length;
        const audio = document.getElementById('bgMusic');
        const musicBtn = document.getElementById('musicBtn');
        let musicStarted = false;

        // === 1. FUNGSI UTAMA: Start Experience ===
        // Audio HANYA dipanggil disini saat user klik tombol
        function startExperience() {
            // Coba play audio
            audio.volume = 0.8;
            var playPromise = audio.play();

            if (playPromise !== undefined) {
                playPromise.then(_ => {
                    musicBtn.classList.remove('muted');
                    musicStarted = true;
                })
                .catch(error => {
                    console.log("Audio play blocked by browser:", error);
                });
            }

            // Pindah slide
            nextSlide();
        }

        // === 2. Toggle Musik Manual ===
        function toggleMusic() {
            if (audio.paused) {
                audio.play();
                musicBtn.classList.remove('muted');
            } else {
                audio.pause();
                musicBtn.classList.add('muted');
            }
        }

        // === 3. Slider Logic ===
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
            
            const shareBtn = document.getElementById('shareBtn');
            const backLink = document.querySelector('.back-link');
            
            if(index === totalSlides - 1) {
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
            if (currentSlide > 0 && currentSlide < totalSlides - 1) { // Slide 1 ga bisa diback
                currentSlide--;
                showSlide(currentSlide);
            }
        }

        // === 4. Visual Effects ===
        function createSparkles() {
            const container = document.getElementById('capture-area');
            for(let i=0; i<15; i++) {
                let sparkle = document.createElement('div');
                sparkle.className = 'sparkle';
                sparkle.style.width = Math.random() * 4 + 'px';
                sparkle.style.height = sparkle.style.width;
                sparkle.style.left = Math.random() * 100 + '%';
                sparkle.style.top = Math.random() * 100 + '%';
                sparkle.style.animationDelay = Math.random() * 2 + 's';
                container.appendChild(sparkle);
            }
        }
        createSparkles();

        // === 5. Download Image ===
        function downloadImage() {
            const element = document.getElementById('capture-area');
            const btn = document.getElementById('shareBtn');
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Loading...';
            
            setTimeout(() => {
                html2canvas(element, { 
                    scale: 3, 
                    useCORS: true 
                }).then(canvas => {
                    const link = document.createElement('a');
                    link.download = 'My-Work-Wrapped-2025.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                    
                    btn.innerHTML = '<i class="mdi mdi-check"></i> Disimpan!';
                    setTimeout(() => { btn.innerHTML = originalText; }, 2000);
                }).catch(err => {
                    console.error(err);
                    btn.innerHTML = 'Gagal :(';
                });
            }, 100);
        }
    </script>
</body>
</html>