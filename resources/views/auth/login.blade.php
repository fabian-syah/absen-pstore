<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - PStore Absensi</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0d0d0d 100%);
            overflow: hidden;
            position: relative;
        }

        /* Animated background shapes */
        .shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.15;
            animation: float 10s ease-in-out infinite;
        }

        .shape-1 {
            width: 500px;
            height: 500px;
            background: #ffffff;
            top: -150px;
            left: -150px;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 400px;
            height: 400px;
            background: #888888;
            bottom: -100px;
            right: -100px;
            animation-delay: -3s;
        }

        .shape-3 {
            width: 300px;
            height: 300px;
            background: #cccccc;
            top: 60%;
            left: 20%;
            animation-delay: -5s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(20px, -20px) scale(1.03); }
            50% { transform: translate(-15px, 15px) scale(0.97); }
            75% { transform: translate(15px, 20px) scale(1.02); }
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
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 48px 40px;
            box-shadow: 
                0 30px 60px -15px rgba(0, 0, 0, 0.6),
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
            margin-bottom: 40px;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ffffff 0%, #e0e0e0 100%);
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 15px 35px rgba(255, 255, 255, 0.1);
            animation: pulse 2.5s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 15px 35px rgba(255, 255, 255, 0.1); }
            50% { transform: scale(1.03); box-shadow: 0 20px 45px rgba(255, 255, 255, 0.15); }
        }

        .logo-icon i {
            font-size: 38px;
            color: #0a0a0a;
        }

        .brand-name {
            font-size: 32px;
            font-weight: 800;
            color: white;
            letter-spacing: 3px;
            margin-bottom: 8px;
            text-shadow: 0 2px 15px rgba(255, 255, 255, 0.1);
        }

        .brand-tagline {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.5);
            font-weight: 400;
            letter-spacing: 0.5px;
        }

        /* Form styles */
        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper > i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.4);
            font-size: 18px;
            transition: color 0.3s ease;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: 18px 18px 18px 54px;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            font-size: 15px;
            color: white;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.35);
        }

        .form-control:focus {
            border-color: rgba(255, 255, 255, 0.4);
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.05);
        }

        .input-wrapper:focus-within > i {
            color: white;
        }

        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.4);
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
            background: linear-gradient(135deg, #ffffff 0%, #e8e8e8 100%);
            border: none;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 700;
            color: #0a0a0a;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
            margin-top: 8px;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.05), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(255, 255, 255, 0.15);
        }

        .btn-login:active {
            transform: translateY(-1px);
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
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 28px;
        }

        .info-box-title {
            display: flex;
            align-items: center;
            font-size: 13px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 10px;
        }

        .info-box-title i {
            margin-right: 10px;
            font-size: 16px;
            color: white;
        }

        .info-box ul {
            margin: 0;
            padding-left: 28px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
        }

        .info-box li {
            margin-bottom: 5px;
        }

        .info-box li:last-child {
            margin-bottom: 0;
        }

        .info-box strong {
            color: rgba(255, 255, 255, 0.8);
        }

        /* Alerts */
        .alert {
            padding: 16px 18px;
            border-radius: 14px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            font-size: 14px;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-5px); }
            40% { transform: translateX(5px); }
            60% { transform: translateX(-5px); }
            80% { transform: translateX(5px); }
        }

        .alert i {
            margin-right: 12px;
            font-size: 18px;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.15);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff8a8a;
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.15);
            border: 1px solid rgba(40, 167, 69, 0.3);
            color: #8affb4;
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
            margin-top: 36px;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .footer p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.3);
            letter-spacing: 0.5px;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 36px 24px;
                border-radius: 20px;
            }

            .logo-icon {
                width: 68px;
                height: 68px;
            }

            .logo-icon i {
                font-size: 32px;
            }

            .brand-name {
                font-size: 26px;
            }

            .form-control {
                padding: 16px 16px 16px 48px;
            }

            .btn-login {
                padding: 16px;
                font-size: 14px;
            }
        }

        /* Loading spinner */
        .spinner {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(10, 10, 10, 0.3);
            border-radius: 50%;
            border-top-color: #0a0a0a;
            animation: spin 0.8s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Floating dots */
        .dots {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }

        .dot {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            animation: dotFloat 20s linear infinite;
        }

        @keyframes dotFloat {
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

        /* Grid pattern overlay */
        .grid-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
            background-size: 50px 50px;
            pointer-events: none;
        }
    </style>
</head>

<body>
    <!-- Background shapes -->
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>

    <!-- Grid pattern -->
    <div class="grid-pattern"></div>

    <!-- Floating dots -->
    <div class="dots" id="dots"></div>

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
                        <i class="fas fa-user"></i>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="login_id" 
                            name="login_id" 
                            placeholder="Masukkan ID Login..."
                            value="{{ old('login_id') }}"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password" 
                            name="password" 
                            placeholder="Masukkan password..."
                            required
                        >
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
                <p>&copy; {{ date('Y') }} PStore Absensi System</p>
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
        document.getElementById('loginForm').addEventListener('submit', function(e) {
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

        // Create floating dots
        function createDots() {
            const container = document.getElementById('dots');
            const dotCount = 20;

            for (let i = 0; i < dotCount; i++) {
                const dot = document.createElement('div');
                dot.className = 'dot';
                dot.style.left = Math.random() * 100 + '%';
                dot.style.animationDuration = (Math.random() * 15 + 15) + 's';
                dot.style.animationDelay = Math.random() * 20 + 's';
                dot.style.width = (Math.random() * 3 + 2) + 'px';
                dot.style.height = dot.style.width;
                container.appendChild(dot);
            }
        }

        // Initialize dots
        createDots();
    </script>
</body>

</html>