<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover">
    <title>PStore - Menuju Ramadan</title>
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
            --emerald-gradient: linear-gradient(135deg, #002B1D 0%, #00150E 100%);
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(212, 175, 55, 0.2);
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

        /* Bintang Berkelap-kelip */
        .stars {
            position: absolute;
            width: 100%;
            height: 100%;
            background: transparent;
        }

        .star {
            position: absolute;
            background: white;
            border-radius: 50%;
            opacity: 0.5;
            animation: twinkle var(--duration) infinite ease-in-out;
        }

        @keyframes twinkle {

            0%,
            100% {
                opacity: 0.3;
                transform: scale(1);
            }

            50% {
                opacity: 1;
                transform: scale(1.2);
            }
        }

        .moon-glow {
            position: absolute;
            top: 5%;
            right: 8%;
            width: 70px;
            height: 70px;
            background: radial-gradient(circle at 30% 30%, #fff 0%, #f4c430 100%);
            border-radius: 50%;
            box-shadow: 0 0 60px rgba(244, 196, 48, 0.3);
            animation: moonPulse 4s infinite alternate;
        }

        /* Islamic Geometry Pattern */
        .geometry-pattern {
            position: absolute;
            bottom: -50px;
            left: -50px;
            width: 300px;
            height: 300px;
            opacity: 0.1;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cpath d='M50 0 L61.2 38.8 L100 50 L61.2 61.2 L50 100 L38.8 61.2 L0 50 L38.8 38.8 Z' fill='%23D4AF37'/%3E%3C/svg%3E");
            background-size: 80px 80px;
            transform: rotate(15deg);
        }

        /* --- Main UI --- */
        .container {
            position: relative;
            z-index: 10;
            width: 90%;
            max-width: 420px;
            animation: fadeInUp 1s ease-out;
        }

        .header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .header .arabic {
            font-family: 'Amiri', serif;
            font-size: 1.4rem;
            color: var(--primary-gold);
            display: block;
            margin-bottom: 5px;
        }

        .header h1 {
            font-weight: 800;
            font-size: 1.8rem;
            letter-spacing: -0.5px;
            background: linear-gradient(to bottom, #fff 40%, #ccc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .card {
            background: rgba(1, 40, 27, 0.6);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border);
            border-radius: 35px;
            padding: 3rem 2rem 2rem;
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.6);
            position: relative;
        }

        /* Floating Logo */
        .logo-wrap {
            position: absolute;
            top: -45px;
            left: 50%;
            transform: translateX(-50%);
            width: 90px;
            height: 90px;
            background: var(--primary-gold);
            border: 8px solid var(--emerald-deep);
            border-radius: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        .logo-wrap i {
            font-size: 36px;
            color: var(--emerald-deep);
        }

        /* Form Controls */
        .input-group {
            margin-bottom: 1.2rem;
        }

        .input-group label {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--primary-gold);
            text-transform: uppercase;
            margin-bottom: 0.6rem;
            margin-left: 4px;
            letter-spacing: 1px;
        }

        .input-box {
            position: relative;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: 0.3s;
        }

        .input-box:focus-within {
            border-color: var(--primary-gold);
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.15);
        }

        .input-box i.field-icon {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.3);
            font-size: 1.1rem;
        }

        .input-box input {
            width: 100%;
            padding: 1.1rem 1rem 1.1rem 3.2rem;
            background: transparent;
            border: none;
            color: #fff;
            font-size: 1rem;
            font-weight: 500;
        }

        .input-box input:focus {
            outline: none;
        }

        .btn-login {
            width: 100%;
            margin-top: 1.5rem;
            padding: 1.1rem;
            background: linear-gradient(135deg, #D4AF37, #B8860B);
            border: none;
            border-radius: 18px;
            color: #002216;
            font-weight: 800;
            font-size: 1rem;
            letter-spacing: 1px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .btn-login:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 30px rgba(212, 175, 55, 0.3);
        }

        /* --- Info Section --- */
        .ramadan-notice {
            background: rgba(212, 175, 55, 0.08);
            border: 1px solid rgba(212, 175, 55, 0.2);
            padding: 0.8rem;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* --- Animations --- */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes moonPulse {
            from {
                transform: scale(1);
                opacity: 0.8;
            }

            to {
                transform: scale(1.1);
                opacity: 1;
            }
        }

        /* Mobile Adjustments */
        @media (max-width: 480px) {
            .container {
                width: 85%;
            }

            .card {
                padding: 3rem 1.5rem 1.5rem;
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .moon-glow {
                width: 50px;
                height: 50px;
                right: 5%;
            }
        }

        /* Loading Spinner */
        .spinner {
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0, 0, 0, 0.1);
            border-top: 3px solid #002216;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>

    <div class="scene" id="scene">
        <div class="stars" id="stars-container"></div>
        <div class="moon-glow"></div>
        <div class="geometry-pattern"></div>

        <div
            style="position:absolute; top: -10px; left: 10%; opacity: 0.4; animation: swing 4s ease-in-out infinite alternate; transform-origin: top center;">
            <i class="fas fa-fan" style="color: var(--primary-gold); font-size: 1.5rem;"></i>
        </div>
    </div>

    <div class="container">
        <header class="header">
            <span class="arabic">أهلاً يا رمضان</span>
            <h1>PStore Absensi</h1>
        </header>

        <div class="card">
            <div class="logo-wrap">
                <i class="fas fa-fingerprint"></i>
            </div>

            <div class="ramadan-notice">
                <i class="fas fa-moon" style="color: var(--primary-gold)"></i>
                <span>Menyambut Berkah Ramadan</span>
            </div>

            @if(session('error'))
                <div
                    style="background: rgba(255, 77, 77, 0.1); border-left: 4px solid #ff4d4d; padding: 12px; border-radius: 10px; margin-bottom: 1.5rem; font-size: 0.8rem;">
                    <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i> {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST" id="loginForm">
                @csrf
                <div class="input-group">
                    <label>ID Login</label>
                    <div class="input-box">
                        <i class="fas fa-user-circle field-icon"></i>
                        <input type="text" name="login_id" placeholder="Masukkan ID Anda" required autofocus>
                    </div>
                </div>

                <div class="input-group">
                    <label>Kata Sandi</label>
                    <div class="input-box">
                        <i class="fas fa-shield-halved field-icon"></i>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                        <button type="button" onclick="togglePassword()"
                            style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #555; cursor: pointer; padding: 5px;">
                            <i class="fas fa-eye" id="eye-icon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="submitBtn">
                    <span>MASUK SISTEM</span>
                    <i class="fas fa-sign-in-alt"></i>
                </button>
            </form>

            <footer
                style="margin-top: 2rem; text-align: center; color: rgba(255,255,255,0.2); font-size: 0.65rem; letter-spacing: 0.5px;">
                &copy; {{ date('Y') }} PSTORE DIGITAL TECHNOLOGY<br>
                Crafted for Excellence
            </footer>
        </div>
    </div>

    <script>
        // Generate Twinkling Stars
        function createStars() {
            const container = document.getElementById('stars-container');
            const count = 40;
            for (let i = 0; i < count; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                const size = Math.random() * 3 + 'px';
                star.style.width = size;
                star.style.height = size;
                star.style.left = Math.random() * 100 + '%';
                star.style.top = Math.random() * 100 + '%';
                star.style.setProperty('--duration', (Math.random() * 3 + 2) + 's');
                container.appendChild(star);
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
            btn.innerHTML = '<div class="spinner"></div>';
            btn.style.opacity = '0.8';
            btn.style.pointerEvents = 'none';
        };

        createStars();
    </script>
</body>

</html>