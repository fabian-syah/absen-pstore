<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>PStore - Menuju Ramadan</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&family=Amiri:wght@400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            /* Dark Mode Colors (Default) */
            --primary-gold: #D4AF37;
            --soft-gold: #F4C430;
            --deep-emerald: #013220;
            --darker-emerald: #002216;
            --glass-bg: rgba(0, 34, 22, 0.7);
            --glass-border: rgba(212, 175, 55, 0.2);
            --body-bg: radial-gradient(circle at center, #004d2e 0%, #001a0f 100%);
            --text-main: #ffffff;
            --text-muted: rgba(255, 255, 255, 0.6);
            --input-bg: rgba(0, 0, 0, 0.2);
            --footer-border: rgba(255, 255, 255, 0.05);
        }

        /* Light Mode Colors */
        body.light-mode {
            --primary-gold: #B8860B;
            --soft-gold: #D4AF37;
            --deep-emerald: #fdfbf0;
            --darker-emerald: #ffffff;
            --glass-bg: rgba(255, 255, 255, 0.9);
            --glass-border: rgba(184, 134, 11, 0.3);
            --body-bg: linear-gradient(135deg, #fff9e6 0%, #ffffff 100%);
            --text-main: #2c3e50;
            --text-muted: #5d6d7e;
            --input-bg: #f4f7f6;
            --footer-border: rgba(184, 134, 11, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
            transition: background 0.4s ease, color 0.4s ease, border-color 0.4s ease, box-shadow 0.4s ease;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--body-bg);
            height: 100vh;
            height: -webkit-fill-available;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: var(--text-main);
        }

        /* Animated Background Elements */
        .bg-ornaments {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 1;
        }

        /* Theme Toggle (Bulan/Matahari) */
        .theme-toggle {
            position: absolute;
            top: 5%;
            right: 10%;
            width: 80px;
            height: 80px;
            cursor: pointer;
            pointer-events: all;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .moon-sun-icon {
            width: 80px;
            height: 80px;
            background: radial-gradient(circle at 30% 30%, #fff 0%, #f4c430 100%);
            border-radius: 50%;
            box-shadow: 0 0 50px rgba(244, 196, 48, 0.4);
            animation: glow 4s ease-in-out infinite alternate;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Light mode matahari adjustments */
        body.light-mode .moon-sun-icon {
            background: radial-gradient(circle at 30% 30%, #ffcc00 0%, #ff8800 100%);
            box-shadow: 0 0 60px rgba(255, 165, 0, 0.6);
        }

        .moon-sun-icon i {
            color: #fff;
            font-size: 24px;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        /* Lantern Styling */
        .lantern {
            position: absolute;
            top: -20px;
            animation: swing 3s ease-in-out infinite alternate;
            transform-origin: top center;
        }

        .lantern-1 {
            left: 15%;
        }

        .lantern-2 {
            right: 15%;
            animation-delay: -1.5s;
        }

        .lantern i {
            color: var(--soft-gold);
            font-size: 2rem;
            filter: drop-shadow(0 0 10px var(--soft-gold));
        }

        .lantern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            width: 1px;
            height: 80px;
            background: var(--glass-border);
        }

        /* Main Container */
        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 90%;
            max-width: 400px;
            perspective: 1000px;
        }

        .greeting-header {
            text-align: center;
            margin-bottom: 2rem;
            animation: fadeInDown 1s ease-out;
        }

        .greeting-header span {
            font-family: 'Amiri', serif;
            color: var(--primary-gold);
            font-size: 1.2rem;
            letter-spacing: 2px;
        }

        .greeting-header h2 {
            font-weight: 800;
            font-size: 1.8rem;
            background: linear-gradient(to right, var(--text-main), var(--soft-gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Card Styling */
        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            padding: 2.5rem 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: cardEntrance 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .brand-box {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-ring {
            width: 70px;
            height: 70px;
            margin: 0 auto 1rem;
            background: var(--primary-gold);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: rotate(-10deg);
            transition: 0.5s;
        }

        .logo-ring i {
            font-size: 30px;
            color: var(--darker-emerald);
            transform: rotate(10deg);
        }

        /* Input Styling */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--primary-gold);
            margin-bottom: 0.5rem;
            margin-left: 0.5rem;
            text-transform: uppercase;
        }

        .input-box {
            position: relative;
        }

        .input-box i {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-gold);
            opacity: 0.6;
            transition: 0.3s;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            background: var(--input-bg);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            color: var(--text-main);
            font-size: 0.95rem;
            transition: 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-gold);
            background: var(--input-bg);
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.1);
        }

        /* Button Styling */
        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary-gold), #b8860b);
            border: none;
            border-radius: 15px;
            color: #fff;
            font-weight: 800;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 2rem;
        }

        body.light-mode .btn-submit {
            color: #ffffff;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(212, 175, 55, 0.3);
            filter: brightness(1.1);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Mobile Specifics */
        @media (max-width: 480px) {
            .login-card {
                padding: 2rem 1.5rem;
            }

            .greeting-header h2 {
                font-size: 1.5rem;
            }

            .theme-toggle {
                width: 60px;
                height: 60px;
                top: 2%;
                right: 5%;
            }

            .moon-sun-icon {
                width: 60px;
                height: 60px;
            }

            .lantern-1 {
                left: 5%;
            }

            .lantern-2 {
                right: 5%;
            }
        }

        /* Animations */
        @keyframes swing {
            from {
                transform: rotate(-5deg);
            }

            to {
                transform: rotate(5deg);
            }
        }

        @keyframes glow {
            from {
                opacity: 0.6;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes cardEntrance {
            from {
                opacity: 0;
                transform: translateY(30px) rotateX(-10deg);
            }

            to {
                opacity: 1;
                transform: translateY(0) rotateX(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0, 0, 0, 0.1);
            border-top: 3px solid #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Info Style */
        .pre-ramadan-badge {
            background: rgba(212, 175, 55, 0.1);
            border: 1px dashed var(--primary-gold);
            padding: 0.8rem;
            border-radius: 12px;
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 1.5rem;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="bg-ornaments">
        <div class="theme-toggle" onclick="toggleTheme()">
            <div class="moon-sun-icon" id="theme-btn">
                <i class="fas fa-moon" id="theme-icon"></i>
            </div>
        </div>

        <div class="lantern lantern-1"><i class="fas fa-kaaba"></i></div>
        <div class="lantern lantern-2"><i class="fas fa-mosque"></i></div>

        <svg width="100%" height="100%" opacity="0.05">
            <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                <path d="M 40 0 L 0 0 0 40" fill="none" stroke="currentColor" stroke-width="1" />
            </pattern>
            <rect width="100%" height="100%" fill="url(#grid)" />
        </svg>
    </div>

    <div class="login-wrapper">
        <div class="greeting-header">
            <span>Marhaban Ya Ramadan</span>
            <h2>PStore Absensi Digital</h2>
        </div>

        <div class="login-card">
            <div class="brand-box">
                <div class="logo-ring">
                    <i class="fas fa-fingerprint"></i>
                </div>
                <p style="color: var(--text-muted); font-size: 0.85rem;">Silahkan login untuk melanjutkan</p>
            </div>

            <div class="pre-ramadan-badge">
                <i class="fas fa-moon" style="margin-right: 5px; color: var(--primary-gold)"></i>
                Menyambut Bulan Suci Ramadan
            </div>

            @if(session('error'))
                <div
                    style="background: rgba(255,0,0,0.1); border-left: 3px solid #ff4d4d; padding: 10px; margin-bottom: 15px; font-size: 0.8rem; border-radius: 5px; color: #ff4d4d;">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST" id="loginForm">
                @csrf
                <div class="form-group">
                    <label>ID Login</label>
                    <div class="input-box">
                        <input type="text" name="login_id" class="form-control" placeholder="ID Pegawai" required
                            autofocus>
                        <i class="fas fa-user-tag"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-box">
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••"
                            required>
                        <i class="fas fa-key"></i>
                        <button type="button" onclick="togglePass()"
                            style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #777; cursor: pointer;">
                            <i class="fas fa-eye" id="eye-icon"
                                style="position: static; transform: none; font-size: 14px;"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="btnSumbit">
                    <span>MASUK SISTEM</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <footer
                style="margin-top: 2rem; text-align: center; font-size: 0.7rem; color: var(--text-muted); border-top: 1px solid var(--footer-border); padding-top: 1rem;">
                &copy; {{ date('Y') }} PStore Team • Crafted with Spirit
            </footer>
        </div>
    </div>

    <script>
        function togglePass() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Logic Theme Switcher
        function toggleTheme() {
            const body = document.body;
            const icon = document.getElementById('theme-icon');

            body.classList.toggle('light-mode');

            if (body.classList.contains('light-mode')) {
                icon.classList.replace('fa-moon', 'fa-sun');
            } else {
                icon.classList.replace('fa-sun', 'fa-moon');
            }
        }

        document.getElementById('loginForm').onsubmit = function () {
            const btn = document.getElementById('btnSumbit');
            btn.innerHTML = '<div class="spinner"></div>';
            btn.style.opacity = '0.7';
            btn.style.pointerEvents = 'none';
        };
    </script>
</body>

</html>