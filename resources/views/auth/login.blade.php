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
            --glass-bg: rgba(255, 255, 255, 0.95);
            --glass-border: rgba(184, 134, 11, 0.3);
            --body-bg: linear-gradient(135deg, #fff9e6 0%, #ffffff 100%);
            --text-main: #1a1a1a;
            --text-muted: #5d6d7e;
            --input-bg: #f4f7f6;
            --footer-border: rgba(184, 134, 11, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
            transition: background 0.4s cubic-bezier(0.4, 0, 0.2, 1), color 0.4s ease;
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

        /* --- Theme Toggle Area --- */
        .theme-toggle-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            /* Pastikan paling atas di HP */
            cursor: pointer;
            padding: 10px;
        }

        .moon-sun-icon {
            width: 70px;
            height: 70px;
            background: radial-gradient(circle at 30% 30%, #fff 0%, #f4c430 100%);
            border-radius: 50%;
            box-shadow: 0 0 40px rgba(244, 196, 48, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            animation: glow 4s ease-in-out infinite alternate;
            transition: 0.5s transform;
        }

        .moon-sun-icon:active {
            transform: scale(0.9);
        }

        body.light-mode .moon-sun-icon {
            background: radial-gradient(circle at 30% 30%, #ffcc00 0%, #ff8800 100%);
            box-shadow: 0 0 50px rgba(255, 165, 0, 0.6);
        }

        .moon-sun-icon i {
            color: #fff;
            font-size: 22px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* --- Layout --- */
        .bg-ornaments {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 1;
        }

        .lantern {
            position: absolute;
            top: -20px;
            animation: swing 3s ease-in-out infinite alternate;
            transform-origin: top center;
        }

        .lantern-1 {
            left: 10%;
        }

        .lantern-2 {
            right: 10%;
            animation-delay: -1.5s;
        }

        .lantern i {
            color: var(--soft-gold);
            font-size: 2rem;
            filter: drop-shadow(0 0 10px var(--soft-gold));
        }

        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 90%;
            max-width: 400px;
            padding-top: 40px;
        }

        .greeting-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .greeting-header span {
            font-family: 'Amiri', serif;
            color: var(--primary-gold);
            font-size: 1.3rem;
        }

        .greeting-header h2 {
            font-weight: 800;
            font-size: 1.8rem;
            margin-top: 5px;
        }

        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border);
            border-radius: 35px;
            padding: 2.5rem 1.8rem;
            box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.5);
        }

        .logo-ring {
            width: 75px;
            height: 75px;
            margin: 0 auto 1.5rem;
            background: var(--primary-gold);
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: rotate(-8deg);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .logo-ring i {
            font-size: 32px;
            color: var(--darker-emerald);
        }

        /* --- Badges & Notes --- */
        .pre-ramadan-badge {
            background: rgba(212, 175, 55, 0.1);
            border: 1px dashed var(--primary-gold);
            padding: 0.8rem;
            border-radius: 15px;
            font-size: 0.85rem;
            color: var(--text-main);
            margin-bottom: 0.8rem;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .theme-note {
            font-size: 0.75rem;
            color: var(--soft-gold);
            text-align: center;
            margin-bottom: 1.5rem;
            opacity: 0.8;
            font-weight: 500;
            animation: pulse 2s infinite;
        }

        /* --- Form --- */
        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--primary-gold);
            margin-bottom: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .input-box {
            position: relative;
        }

        .input-box i:not(.fa-eye) {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-gold);
            opacity: 0.6;
        }

        .form-control {
            width: 100%;
            padding: 1.1rem 1rem 1.1rem 3.2rem;
            background: var(--input-bg);
            border: 1px solid var(--glass-border);
            border-radius: 18px;
            color: var(--text-main);
            font-size: 1rem;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-gold);
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 1.1rem;
            background: linear-gradient(135deg, var(--primary-gold), #b8860b);
            border: none;
            border-radius: 18px;
            color: #fff;
            font-weight: 800;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        /* --- Keyframes --- */
        @keyframes swing {
            from {
                transform: rotate(-4deg);
            }

            to {
                transform: rotate(4deg);
            }
        }

        @keyframes glow {
            from {
                opacity: 0.8;
                transform: scale(1);
            }

            to {
                opacity: 1;
                transform: scale(1.05);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 0.5;
            }

            50% {
                opacity: 1;
            }
        }

        @media (max-width: 480px) {
            .moon-sun-icon {
                width: 60px;
                height: 60px;
            }

            .login-card {
                padding: 2rem 1.4rem;
            }

            .greeting-header h2 {
                font-size: 1.5rem;
            }
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>

    <div class="bg-ornaments">
        <div class="theme-toggle-container" onclick="toggleTheme()">
            <div class="moon-sun-icon">
                <i class="fas fa-moon" id="theme-icon"></i>
            </div>
        </div>

        <div class="lantern lantern-1"><i class="fas fa-kaaba"></i></div>
        <div class="lantern lantern-2"><i class="fas fa-mosque"></i></div>

        <svg width="100%" height="100%" opacity="0.03">
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
            </div>

            <div class="pre-ramadan-badge">
                <i class="fas fa-star-and-crescent"></i>
                Menyambut Berkah Ramadan
            </div>

            <div class="theme-note">
                <i class="fas fa-lightbulb"></i> Klik ikon bulan/matahari untuk ganti mode
            </div>

            <form action="{{ route('login.submit') }}" method="POST" id="loginForm">
                @csrf
                <div class="form-group">
                    <label>ID Pegawai</label>
                    <div class="input-box">
                        <input type="text" name="login_id" class="form-control" placeholder="Masukkan ID Login" required
                            autofocus>
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label>Kata Sandi</label>
                    <div class="input-box">
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••"
                            required>
                        <i class="fas fa-lock"></i>
                        <button type="button" onclick="togglePass()"
                            style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #777; cursor: pointer;">
                            <i class="fas fa-eye" id="eye-icon" style="font-size: 14px;"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="btnSubmit">
                    <span>MASUK SISTEM</span>
                    <i class="fas fa-sign-in-alt"></i>
                </button>
            </form>

            <footer
                style="margin-top: 2rem; text-align: center; font-size: 0.65rem; color: var(--text-muted); border-top: 1px solid var(--footer-border); padding-top: 1rem;">
                &copy; {{ date('Y') }} PStore Team • Build with Passion
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

        // Logic Theme Switcher yang lebih kuat untuk Mobile
        function toggleTheme() {
            const body = document.body;
            const icon = document.getElementById('theme-icon');

            body.classList.toggle('light-mode');

            if (body.classList.contains('light-mode')) {
                icon.classList.replace('fa-moon', 'fa-sun');
                // Simpan pilihan ke localStorage agar tidak reset saat refresh
                localStorage.setItem('theme', 'light');
            } else {
                icon.classList.replace('fa-sun', 'fa-moon');
                localStorage.setItem('theme', 'dark');
            }
        }

        // Cek preferensi user saat reload
        if (localStorage.getItem('theme') === 'light') {
            document.body.classList.add('light-mode');
            document.getElementById('theme-icon').classList.replace('fa-moon', 'fa-sun');
        }

        document.getElementById('loginForm').onsubmit = function () {
            const btn = document.getElementById('btnSubmit');
            btn.innerHTML = '<div class="spinner"></div>';
            btn.style.opacity = '0.7';
            btn.style.pointerEvents = 'none';
        };
    </script>
</body>

</html>