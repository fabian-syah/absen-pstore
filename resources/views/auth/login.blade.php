<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - PStore Absensi</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            overflow: hidden;
            position: relative;
        }

        /* Animated background orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.6;
            animation: float 8s ease-in-out infinite;
        }

        .orb-1 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 350px;
            height: 350px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            bottom: -80px;
            right: -80px;
            animation-delay: -2s;
        }

        .orb-3 {
            width: 250px;
            height: 250px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: -4s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            25% {
                transform: translate(30px, -30px) scale(1.05);
            }

            50% {
                transform: translate(-20px, 20px) scale(0.95);
            }

            75% {
                transform: translate(20px, 30px) scale(1.02);
            }
        }

        /* Glassmorphism card */
        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 48px 40px;
            box-shadow:
                0 25px 50px -12px rgba(0, 0, 0, 0.5),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Logo section */
        .logo-section {
            text-align: center;
            margin-bottom: 36px;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            }

            50% {
                transform: scale(1.02);
                box-shadow: 0 15px 40px rgba(102, 126, 234, 0.5);
            }
        }

        .logo-icon i {
            font-size: 36px;
            color: white;
        }

        .brand-name {
            font-size: 28px;
            font-weight: 800;
            color: white;
            letter-spacing: 2px;
            margin-bottom: 6px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .brand-tagline {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 400;
        }

        /* Form styles */
        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
            font-size: 18px;
            transition: color 0.3s ease;
        }

        .form-control {
            width: 100%;
            padding: 16px 18px 16px 52px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.15);
            border-radius: 14px;
            font-size: 15px;
            color: white;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .form-control:focus {
            border-color: #667eea;
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
        }

        .form-control:focus+i,
        .input-wrapper:focus-within i {
            color: #667eea;
        }

        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            font-size: 18px;
            transition: color 0.3s ease;
            padding: 0;
        }

        .password-toggle:hover {
            color: rgba(255, 255, 255, 0.8);
        }

        /* Submit button */
        .btn-login {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 700;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            position: relative;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-login i {
            margin-right: 10px;
        }

        /* Info box */
        .info-box {
            background: rgba(102, 126, 234, 0.15);
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 28px;
        }

        .info-box-title {
            display: flex;
            align-items: center;
            font-size: 13px;
            font-weight: 600;
            color: #a5b4fc;
            margin-bottom: 8px;
        }

        .info-box-title i {
            margin-right: 8px;
            font-size: 15px;
        }

        .info-box ul {
            margin: 0;
            padding-left: 24px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
        }

        .info-box li {
            margin-bottom: 4px;
        }

        .info-box li:last-child {
            margin-bottom: 0;
        }

        /* Alerts */
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            font-size: 14px;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            20% {
                transform: translateX(-5px);
            }

            40% {
                transform: translateX(5px);
            }

            60% {
                transform: translateX(-5px);
            }

            80% {
                transform: translateX(5px);
            }
        }

        .alert i {
            margin-right: 12px;
            font-size: 18px;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid rgba(34, 197, 94, 0.4);
            color: #86efac;
        }

        .alert-close {
            margin-left: auto;
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.3s ease;
            padding: 0;
        }

        .alert-close:hover {
            opacity: 1;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.4);
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 36px 24px;
            }

            .logo-icon {
                width: 64px;
                height: 64px;
            }

            .logo-icon i {
                font-size: 28px;
            }

            .brand-name {
                font-size: 24px;
            }

            .form-control {
                padding: 14px 16px 14px 48px;
            }

            .btn-login {
                padding: 16px;
                font-size: 14px;
            }
        }

        /* Loading spinner */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Particles */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: particleFloat 15s linear infinite;
        }

        @keyframes particleFloat {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                transform: translateY(-100vh) rotate(720deg);
                opacity: 0;
            }
        }
    </style>
</head>

<body>
    <!-- Background orbs -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <!-- Particles -->
    <div class="particles" id="particles"></div>

    <div class="login-container">
        <div class="login-card">
            <!-- Logo section -->
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fas fa-fingerprint"></i>
                </div>
                <h1 class="brand-name">PSTORE</h1>
                <p class="brand-tagline">Sistem Absensi Digital</p>
            </div>

            <!-- Info box -->
            <div class="info-box">
                <div class="info-box-title">
                    <i class="fas fa-info-circle"></i>
                    Cara Login
                </div>
                <ul>
                    <li>Gunakan <strong>ID Login</strong> yang diberikan</li>
                    <li>Huruf besar/kecil tidak berpengaruh</li>
                </ul>
            </div>

            <!-- Error alerts -->
            @error('login_id')
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ $message }}</span>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @enderror

            @error('password')
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ $message }}</span>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @enderror

            @if (session('status'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('status') }}</span>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            <!-- Login form -->
            <form action="{{ route('login.submit') }}" method="POST" id="loginForm">
                @csrf

                <div class="form-group">
                    <label for="login_id">ID Login</label>
                    <div class="input-wrapper">
                        <input type="text" class="form-control" id="login_id" name="login_id"
                            placeholder="Masukkan ID Login..." value="{{ old('login_id') }}" required autofocus>
                        <i class="fas fa-user"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Masukkan password..." required>
                        <i class="fas fa-lock"></i>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="password-icon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="submitBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    Masuk Sistem
                </button>
            </form>

            <!-- Footer -->
            <div class="footer">
                <p>&copy; {{ date('Y') }} PStore Absensi System. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }

        // Form submit loading state
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            const btn = document.getElementById('submitBtn');
            btn.innerHTML = '<span class="spinner"></span>Memproses...';
            btn.disabled = true;
        });

        // Clear errors when typing
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('input', () => {
                document.querySelectorAll('.alert').forEach(alert => alert.remove());
            });
        });

        // Create floating particles
        function createParticles() {
            const container = document.getElementById('particles');
            const particleCount = 30;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
                particle.style.animationDelay = Math.random() * 15 + 's';
                particle.style.width = (Math.random() * 4 + 2) + 'px';
                particle.style.height = particle.style.width;
                container.appendChild(particle);
            }
        }

        // Initialize particles
        createParticles();

        // Keyboard shortcut: Enter to submit
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                const activeElement = document.activeElement;
                if (activeElement.tagName === 'INPUT') {
                    document.getElementById('loginForm').submit();
                }
            }
        });
    </script>
</body>

</html>