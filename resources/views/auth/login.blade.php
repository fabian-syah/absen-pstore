<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover">
    <title>PStore - Absensi Digital</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            /* Primary PStore Blue Theme */
            --primary-blue: #0d6efd;
            --primary-dark: #0a58ca;
            --body-bg: #f4f7fe;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --input-bg: #f8fafc;
            --input-border: #e2e8f0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--body-bg);
            background-image: radial-gradient(circle at 0% 0%, rgba(13, 110, 253, 0.05) 0%, transparent 50%),
                              radial-gradient(circle at 100% 100%, rgba(13, 110, 253, 0.05) 0%, transparent 50%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 400px;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .greeting-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .greeting-header h2 {
            font-weight: 800;
            font-size: 1.75rem;
            color: var(--text-main);
            letter-spacing: -0.5px;
        }

        .greeting-header p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .login-card {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 2.5rem 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .logo-box {
            width: 64px;
            height: 64px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-dark));
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 20px rgba(13, 110, 253, 0.2);
        }

        .logo-box i {
            font-size: 28px;
            color: #ffffff;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-box {
            position: relative;
        }

        .input-box i:not(.fa-eye):not(.fa-eye-slash) {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1rem 1rem 3.5rem;
            background: var(--input-bg);
            border: 1.5px solid var(--input-border);
            border-radius: 16px;
            color: var(--text-main);
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-blue);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-dark));
            border: none;
            border-radius: 16px;
            color: #ffffff;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(13, 110, 253, 0.15);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(13, 110, 253, 0.25);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .toggle-password {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 5px;
            z-index: 10;
        }

        footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 2rem 1.5rem;
            }
            .greeting-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>

    <div class="login-wrapper">
        <div class="greeting-header">
            <h2>PStore Absensi</h2>
            <p>Silakan masuk untuk akses dashboard.</p>
        </div>

        <div class="login-card">
            <div class="logo-box">
                <i class="fas fa-fingerprint"></i>
            </div>

            <form action="{{ route('login.submit') }}" method="POST" id="loginForm">
                @csrf
                <div class="form-group">
                    <label>ID Pegawai</label>
                    <div class="input-box">
                        <i class="far fa-user"></i>
                        <input type="text" name="login_id" class="form-control" placeholder="Masukkan ID Login" required
                            autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label>Kata Sandi</label>
                    <div class="input-box">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••"
                            required>
                        <button type="button" onclick="togglePass()" class="toggle-password">
                            <i class="far fa-eye" id="eye-icon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="btnSubmit">
                    <span>Masuk ke Sistem</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <footer>
                &copy; {{ date('Y') }} PStore Team • Absensi Digital
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

        document.getElementById('loginForm').onsubmit = function () {
            const btn = document.getElementById('btnSubmit');
            const btnText = btn.querySelector('span');
            const btnIcon = btn.querySelector('i');
            
            btnText.innerText = 'Memproses...';
            btnIcon.className = 'spinner';
            btn.style.opacity = '0.8';
            btn.style.pointerEvents = 'none';
        };
    </script>
</body>

</html>