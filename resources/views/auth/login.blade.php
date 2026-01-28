<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>PStore - Menuju Ramadan</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-gold: #D4AF37;
            --soft-gold: #F4C430;
            --deep-emerald: #013220;
            --darker-emerald: #002216;
            --glass-bg: rgba(255, 255, 255, 0.03);
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
            background: radial-gradient(circle at center, #004d2e 0%, #001a0f 100%);
            height: 100vh;
            height: -webkit-fill-available;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: #fff;
        }

        /* Animated Background Elements */
        .bg-ornaments {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 1;
        }

        .moon {
            position: absolute;
            top: 5%;
            right: 10%;
            width: 80px;
            height: 80px;
            background: radial-gradient(circle at 30% 30%, #fff 0%, #f4c430 100%);
            border-radius: 50%;
            box-shadow: 0 0 50px rgba(244, 196, 48, 0.4);
            animation: glow 4s ease-in-out infinite alternate;
        }

        /* Lantern Styling */
        .lantern {
            position: absolute;
            top: -20px;
            animation: swing 3s ease-in-out infinite alternate;
            transform-origin: top center;
        }
        .lantern-1 { left: 15%; }
        .lantern-2 { right: 15%; animation-delay: -1.5s; }
        
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
            background: linear-gradient(to right, #fff, var(--soft-gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Card Styling */
        .login-card {
            background: rgba(0, 34, 22, 0.7);
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
            color: rgba(212, 175, 55, 0.5);
            transition: 0.3s;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            color: #fff;
            font-size: 0.95rem;
            transition: 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-gold);
            background: rgba(0, 0, 0, 0.3);
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.1);
        }

        .form-control:focus + i {
            color: var(--primary-gold);
        }

        /* Button Styling */
        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary-gold), #b8860b);
            border: none;
            border-radius: 15px;
            color: var(--darker-emerald);
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

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(212, 175, 55, 0.3);
            filter: brightness(1.1);
        }

        .btn-submit:active { transform: translateY(0); }

        /* Mobile Specifics */
        @media (max-width: 480px) {
            .login-card { padding: 2rem 1.5rem; }
            .greeting-header h2 { font-size: 1.5rem; }
            .moon { width: 60px; height: 60px; }
            .lantern-1 { left: 5%; }
            .lantern-2 { right: 5%; }
        }

        /* Animations */
        @keyframes swing {
            from { transform: rotate(-5deg); }
            to { transform: rotate(5deg); }
        }

        @keyframes glow {
            from { opacity: 0.6; }
            to { opacity: 1; }
        }

        @keyframes cardEntrance {
            from { opacity: 0; transform: translateY(30px) rotateX(-10deg); }
            to { opacity: 1; transform: translateY(0) rotateX(0); }
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0,0,0,0.1);
            border-top: 3px solid var(--darker-emerald);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* Info Style */
        .pre-ramadan-badge {
            background: rgba(212, 175, 55, 0.1);
            border: 1px dashed var(--primary-gold);
            padding: 0.8rem;
            border-radius: 12px;
            font-size: 0.8rem;
            color: #eee;
            margin-bottom: 1.5rem;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="bg-ornaments">
        <div class="moon"></div>
        <div class="lantern lantern-1"><i class="fas fa-kaaba"></i></div>
        <div class="lantern lantern-2"><i class="fas fa-mosque"></i></div>
        <svg width="100%" height="100%" opacity="0.05">
            <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1"/>
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
                <p style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Silahkan login untuk melanjutkan</p>
            </div>

            <div class="pre-ramadan-badge">
                <i class="fas fa-moon" style="margin-right: 5px; color: var(--primary-gold)"></i>
                Menyambut Bulan Suci Ramadan
            </div>

            @if(session('error'))
                <div style="background: rgba(255,0,0,0.1); border-left: 3px solid #ff4d4d; padding: 10px; margin-bottom: 15px; font-size: 0.8rem; border-radius: 5px;">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST" id="loginForm">
                @csrf
                <div class="form-group">
                    <label>ID Login</label>
                    <div class="input-box">
                        <input type="text" name="login_id" class="form-control" placeholder="ID Pegawai" required autofocus>
                        <i class="fas fa-user-tag"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-box">
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                        <i class="fas fa-key"></i>
                        <button type="button" onclick="togglePass()" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #777; cursor: pointer;">
                            <i class="fas fa-eye" id="eye-icon" style="position: static; transform: none; font-size: 14px;"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="btnSumbit">
                    <span>MASUK SISTEM</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <footer style="margin-top: 2rem; text-align: center; font-size: 0.7rem; color: rgba(255,255,255,0.3); border-top: 1px solid rgba(255,255,255,0.05); padding-top: 1rem;">
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

        document.getElementById('loginForm').onsubmit = function() {
            const btn = document.getElementById('btnSumbit');
            btn.innerHTML = '<div class="spinner"></div>';
            btn.style.opacity = '0.7';
            btn.style.pointerEvents = 'none';
        };
    </script>
</body>
</html>