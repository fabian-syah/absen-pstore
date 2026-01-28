<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover">
    <title>PStore - Sambut Ramadan</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&family=Amiri:wght@400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-gold: #D4AF37;
            --soft-gold: #F4C430;
            --emerald-deep: #002B1D;
            --emerald-gradient: radial-gradient(circle at center, #003d29 0%, #00150E 100%);
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(212, 175, 55, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--emerald-gradient);
            height: 100vh;
            height: -webkit-fill-available;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: #fff;
        }

        /* --- Background Elements --- */
        .scene {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 1;
        }

        /* Islamic Geometric Backdrop */
        .geometry-overlay {
            position: absolute;
            inset: 0;
            opacity: 0.05;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'%3E%3Cpath d='M40 0 L45 35 L80 40 L45 45 L40 80 L35 45 L0 40 L35 35 Z' fill='%23D4AF37'/%3E%3C/svg%3E");
            background-size: 60px 60px;
        }

        .stars {
            position: absolute;
            width: 100%;
            height: 100%;
        }

        .star {
            position: absolute;
            background: white;
            border-radius: 50%;
            animation: twinkle var(--duration) infinite ease-in-out;
        }

        @keyframes twinkle {

            0%,
            100% {
                opacity: 0.2;
                transform: scale(1);
            }

            50% {
                opacity: 1;
                transform: scale(1.3);
            }
        }

        /* Shooting Star Effect */
        .shooting-star {
            position: absolute;
            width: 2px;
            height: 2px;
            background: linear-gradient(90deg, #fff, transparent);
            animation: shooting 4s linear infinite;
        }

        @keyframes shooting {
            0% {
                transform: translateX(0) translateY(0) rotate(-45deg);
                opacity: 1;
                width: 0;
            }

            10% {
                width: 100px;
            }

            20% {
                transform: translateX(-500px) translateY(500px) rotate(-45deg);
                opacity: 0;
            }

            100% {
                opacity: 0;
            }
        }

        /* Realistik Lanterns */
        .lantern-hanging {
            position: absolute;
            top: -20px;
            z-index: 5;
            text-align: center;
            animation: swing 4s ease-in-out infinite alternate;
            transform-origin: top center;
        }

        .lantern-hanging.left {
            left: 10%;
        }

        .lantern-hanging.right {
            right: 10%;
            animation-delay: -2s;
        }

        .lantern-hanging i {
            font-size: 3rem;
            color: var(--primary-gold);
            filter: drop-shadow(0 0 15px var(--soft-gold));
        }

        .lantern-hanging::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            width: 2px;
            height: 80px;
            background: linear-gradient(to bottom, transparent, var(--primary-gold));
            transform: translateX(-50%);
        }

        @keyframes swing {
            from {
                transform: rotate(-4deg);
            }

            to {
                transform: rotate(4deg);
            }
        }

        /* --- Main UI --- */
        .container {
            position: relative;
            z-index: 10;
            width: 90%;
            max-width: 420px;
            animation: containerIn 1.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes containerIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(30px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .header .arabic {
            font-family: 'Amiri', serif;
            font-size: 1.8rem;
            color: var(--primary-gold);
            text-shadow: 0 0 10px rgba(212, 175, 55, 0.5);
            display: block;
        }

        .header h1 {
            font-weight: 800;
            font-size: 2rem;
            letter-spacing: -1px;
            color: #fff;
        }

        .card {
            background: rgba(1, 40, 27, 0.7);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid var(--glass-border);
            border-radius: 40px;
            padding: 3.5rem 2rem 2rem;
            box-shadow: 0 50px 100px rgba(0, 0, 0, 0.7), inset 0 0 20px rgba(212, 175, 55, 0.05);
            position: relative;
        }

        .logo-wrap {
            position: absolute;
            top: -45px;
            left: 50%;
            transform: translateX(-50%);
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, var(--soft-gold), var(--primary-gold));
            border: 8px solid #002B1D;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        .logo-wrap i {
            font-size: 38px;
            color: #002B1D;
        }

        /* Countdown Widget */
        .countdown-timer {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-around;
            text-align: center;
        }

        .timer-item span {
            display: block;
        }

        .timer-val {
            font-weight: 800;
            color: var(--soft-gold);
            font-size: 1.1rem;
        }

        .timer-label {
            font-size: 0.6rem;
            text-transform: uppercase;
            opacity: 0.6;
        }

        /* Inputs */
        .input-group {
            margin-bottom: 1.5rem;
        }

        .input-box {
            position: relative;
            background: rgba(0, 0, 0, 0.4);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .input-box:focus-within {
            border-color: var(--primary-gold);
            background: rgba(0, 0, 0, 0.6);
            transform: translateY(-2px);
        }

        .input-box input {
            width: 100%;
            padding: 1.2rem 1rem 1.2rem 3.5rem;
            background: transparent;
            border: none;
            color: #fff;
            font-size: 1rem;
        }

        .field-icon {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-gold);
            opacity: 0.6;
        }

        .btn-login {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(135deg, #D4AF37, #B8860B);
            border: none;
            border-radius: 20px;
            color: #002B1D;
            font-weight: 800;
            font-size: 1.1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            box-shadow: 0 15px 30px rgba(212, 175, 55, 0.2);
            transition: 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(212, 175, 55, 0.4);
        }

        /* Particles */
        .particle {
            position: absolute;
            background: var(--soft-gold);
            border-radius: 50%;
            pointer-events: none;
            opacity: 0.3;
            animation: float 10s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(0) rotate(0);
            }

            100% {
                transform: translateY(-100vh) rotate(360deg);
            }
        }

        @media (max-width: 480px) {
            .container {
                width: 90%;
            }

            .card {
                padding: 3rem 1.5rem 1.5rem;
            }

            .header .arabic {
                font-size: 1.4rem;
            }

            .header h1 {
                font-size: 1.6rem;
            }
        }
    </style>
</head>

<body>

    <div class="scene">
        <div class="geometry-overlay"></div>
        <div class="stars" id="stars-container"></div>
        <div id="particles"></div>

        <div class="lantern-hanging left"><i class="fas fa-lantern"></i></div>
        <div class="lantern-hanging right"><i class="fas fa-lantern"></i></div>

        <div
            style="position: absolute; top: 5%; right: 5%; width: 60px; height: 60px; background: radial-gradient(circle at 30% 30%, #fff 0%, #f4c430 100%); border-radius: 50%; box-shadow: 0 0 50px rgba(244, 196, 48, 0.4);">
        </div>
    </div>

    <div class="container">
        <header class="header">
            <span class="arabic">رمضان كريم</span>
            <h1>PStore Absensi</h1>
        </header>

        <div class="card">
            <div class="logo-wrap">
                <i class="fas fa-fingerprint"></i>
            </div>

            <div class="countdown-timer">
                <div class="timer-item">
                    <span class="timer-val" id="days">00</span>
                    <span class="timer-label">Hari</span>
                </div>
                <div class="timer-item">
                    <span class="timer-val" id="hours">00</span>
                    <span class="timer-label">Jam</span>
                </div>
                <div class="timer-item">
                    <span class="timer-val" id="mins">00</span>
                    <span class="timer-label">Menit</span>
                </div>
                <div
                    style="font-size: 0.7rem; color: var(--primary-gold); align-self: center; border-left: 1px solid rgba(255,255,255,0.1); padding-left: 10px;">
                    Menuju<br>Ramadan
                </div>
            </div>

            <form action="{{ route('login.submit') }}" method="POST" id="loginForm">
                @csrf
                <div class="input-group">
                    <div class="input-box">
                        <i class="fas fa-id-badge field-icon"></i>
                        <input type="text" name="login_id" placeholder="ID Pegawai" required autofocus>
                    </div>
                </div>

                <div class="input-group">
                    <div class="input-box">
                        <i class="fas fa-lock field-icon"></i>
                        <input type="password" id="password" name="password" placeholder="Kata Sandi" required>
                        <button type="button" onclick="togglePassword()"
                            style="position: absolute; right: 1.2rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #555; cursor: pointer;">
                            <i class="fas fa-eye" id="eye-icon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="submitBtn">
                    <span>MASUK SISTEM</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <footer
                style="margin-top: 2rem; text-align: center; color: rgba(255,255,255,0.2); font-size: 0.7rem; letter-spacing: 1px;">
                &copy; {{ date('Y') }} PSTORE DIGITAL HUB<br>
                Memberikan yang Terbaik
            </footer>
        </div>
    </div>

    <script>
        // Stars Generator
        function initScene() {
            const container = document.getElementById('stars-container');
            for (let i = 0; i < 50; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                const size = Math.random() * 2 + 'px';
                star.style.width = size;
                star.style.height = size;
                star.style.left = Math.random() * 100 + '%';
                star.style.top = Math.random() * 100 + '%';
                star.style.setProperty('--duration', (Math.random() * 3 + 2) + 's');
                container.appendChild(star);
            }

            // Shooting star
            setInterval(() => {
                const ss = document.createElement('div');
                ss.className = 'shooting-star';
                ss.style.top = Math.random() * 50 + '%';
                ss.style.right = '0';
                document.body.appendChild(ss);
                setTimeout(() => ss.remove(), 4000);
            }, 6000);

            // Gold Particles
            const pContainer = document.getElementById('particles');
            for (let i = 0; i < 15; i++) {
                const p = document.createElement('div');
                p.className = 'particle';
                p.style.width = Math.random() * 4 + 'px';
                p.style.height = p.style.width;
                p.style.left = Math.random() * 100 + '%';
                p.style.top = '100%';
                p.style.animationDelay = Math.random() * 10 + 's';
                pContainer.appendChild(p);
            }
        }

        // Countdown Timer Ramadan 2026 (Estimasi 1 Maret)
        function updateCountdown() {
            const ramadanDate = new Date('March 1, 2026 00:00:00').getTime();
            const now = new Date().getTime();
            const diff = ramadanDate - now;

            if (diff > 0) {
                document.getElementById('days').innerText = Math.floor(diff / (1000 * 60 * 60 * 24)).toString().padStart(2, '0');
                document.getElementById('hours').innerText = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)).toString().padStart(2, '0');
                document.getElementById('mins').innerText = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
            }
        }

        function togglePassword() {
            const passInput = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                passInput.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        document.getElementById('loginForm').onsubmit = function () {
            const btn = document.getElementById('submitBtn');
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Memproses...';
            btn.disabled = true;
        };

        initScene();
        setInterval(updateCountdown, 1000);
        updateCountdown();
    </script>
</body>

</html>