<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover">
    <title>Masuk • Absensi PStore</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-page: #f8fafc;
            --bg-card: #ffffff;
            --border-card: #e2e8f0;
            --border-input: #cbd5e1;
            --border-focus: #2563eb;
            --text-heading: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --btn-primary: #2563eb;
            --btn-primary-hover: #1d4ed8;
            --btn-primary-active: #1e40af;
            --danger-bg: #fef2f2;
            --danger-border: #fecaca;
            --danger-text: #991b1b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html, body {
            min-height: 100%;
            background-color: var(--bg-page);
            color: var(--text-body);
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 32px 16px;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
        }

        .login-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 12px;
            padding: 32px 28px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04), 0 2px 4px -2px rgba(0, 0, 0, 0.03);
        }

        .card-header {
            margin-bottom: 24px;
        }

        .card-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-heading);
            letter-spacing: -0.02em;
            line-height: 1.3;
            margin-bottom: 6px;
        }

        .card-subtitle {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* Error Alert */
        .alert-error {
            background-color: var(--danger-bg);
            border: 1px solid var(--danger-border);
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 20px;
            font-size: 13px;
            color: var(--danger-text);
            line-height: 1.45;
        }

        /* Form Fields */
        .form-group {
            margin-bottom: 18px;
        }

        .label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 7px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-heading);
        }

        .caps-notice {
            font-size: 11px;
            font-weight: 600;
            color: #b45309;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .form-input {
            width: 100%;
            height: 42px;
            padding: 0 14px;
            background-color: #ffffff;
            border: 1px solid var(--border-input);
            border-radius: 8px;
            color: var(--text-heading);
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .form-input::placeholder {
            color: #94a3b8;
        }

        .form-input:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .form-input-pass {
            padding-right: 42px;
        }

        .btn-toggle-eye {
            position: absolute;
            right: 6px;
            height: 30px;
            width: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            border-radius: 6px;
            color: #64748b;
            cursor: pointer;
            transition: color 0.15s ease;
        }

        .btn-toggle-eye:hover {
            color: var(--text-heading);
        }

        .btn-toggle-eye svg {
            width: 18px;
            height: 18px;
        }

        /* Checkbox */
        .remember-group {
            margin-bottom: 22px;
        }

        .remember-label {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            cursor: pointer;
            user-select: none;
            font-size: 13px;
            color: var(--text-body);
        }

        .remember-label input {
            width: 16px;
            height: 16px;
            accent-color: var(--btn-primary);
            cursor: pointer;
        }

        /* Submit Button */
        .btn-login {
            width: 100%;
            height: 42px;
            background-color: var(--btn-primary);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background-color 0.15s ease;
        }

        .btn-login:hover {
            background-color: var(--btn-primary-hover);
        }

        .btn-login:active {
            background-color: var(--btn-primary-active);
        }

        .btn-spinner {
            display: none;
            width: 16px;
            height: 16px;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            100% { transform: rotate(360deg); }
        }

        /* Footer */
        .login-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
        }

        @media (max-width: 480px) {
            body {
                padding: 20px 14px;
            }

            .login-card {
                padding: 24px 20px;
            }

            .card-title {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="card-header">
                <h1 class="card-title">Masuk ke Akun</h1>
                <p class="card-subtitle">Silakan masukkan ID Pegawai dan kata sandi Anda.</p>
            </div>

            @if ($errors->any())
                <div class="alert-error" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST" id="loginForm" autocomplete="on">
                @csrf

                <div class="form-group">
                    <div class="label-row">
                        <label for="login_id" class="form-label">ID Pegawai</label>
                    </div>
                    <div class="input-group">
                        <input 
                            type="text" 
                            id="login_id" 
                            name="login_id" 
                            class="form-input" 
                            placeholder="Masukkan ID Login" 
                            value="{{ old('login_id') }}" 
                            required 
                            autofocus
                            autocapitalize="none"
                            autocorrect="off"
                            spellcheck="false"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <div class="label-row">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <span id="capsNotice" class="caps-notice" style="display: none;">Caps Lock Aktif</span>
                    </div>
                    <div class="input-group">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input form-input-pass" 
                            placeholder="Masukkan kata sandi" 
                            required
                        >
                        <button type="button" class="btn-toggle-eye" id="togglePassBtn" aria-label="Tampilkan kata sandi">
                            <svg id="eyeShow" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M2 10s3.5-6 8-6 8 6 8 6-3.5 6-8 6-8-6-8-6z"/>
                                <circle cx="10" cy="10" r="2.5"/>
                            </svg>
                            <svg id="eyeHide" style="display: none;" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M3 3l14 14M10 4.5c4.5 0 8 5.5 8 5.5s-1.5 2.5-3.5 4M6 6.5C3.5 8 2 10 2 10s3.5 5.5 8 5.5c1.5 0 3-.5 4-1.2"/>
                                <path d="M9 9a2 2 0 002.8 2.8"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="remember-group">
                    <label class="remember-label">
                        <input type="checkbox" name="remember" id="remember" value="1">
                        <span>Ingat sesi di perangkat ini</span>
                    </label>
                </div>

                <button type="submit" class="btn-login" id="btnLogin">
                    <span id="btnText">Masuk ke Sistem</span>
                    <svg class="btn-spinner" id="btnSpinner" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2.5" opacity="0.25"></circle>
                        <path d="M12 3a9 9 0 019 9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></path>
                    </svg>
                </button>
            </form>
        </div>

        <div class="login-footer">
            &copy; {{ date('Y') }} PT Putra Siregar Merakyat. Hak Cipta Dilindungi.
        </div>
    </div>

    <script>
        // Password Visibility Toggle
        const passwordInput = document.getElementById('password');
        const togglePassBtn = document.getElementById('togglePassBtn');
        const eyeShow = document.getElementById('eyeShow');
        const eyeHide = document.getElementById('eyeHide');

        if (togglePassBtn && passwordInput) {
            togglePassBtn.addEventListener('click', function() {
                const isPass = passwordInput.type === 'password';
                passwordInput.type = isPass ? 'text' : 'password';
                eyeShow.style.display = isPass ? 'none' : 'block';
                eyeHide.style.display = isPass ? 'block' : 'none';
                passwordInput.focus();
            });
        }

        // Caps Lock Detection
        const capsNotice = document.getElementById('capsNotice');
        if (passwordInput && capsNotice) {
            const checkCaps = (e) => {
                if (e.getModifierState && e.getModifierState('CapsLock')) {
                    capsNotice.style.display = 'block';
                } else {
                    capsNotice.style.display = 'none';
                }
            };
            passwordInput.addEventListener('keydown', checkCaps);
            passwordInput.addEventListener('keyup', checkCaps);
            passwordInput.addEventListener('blur', () => {
                capsNotice.style.display = 'none';
            });
        }

        // Form Submit Loading State
        const loginForm = document.getElementById('loginForm');
        const btnLogin = document.getElementById('btnLogin');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');

        if (loginForm && btnLogin) {
            loginForm.addEventListener('submit', function() {
                btnText.textContent = 'Memverifikasi...';
                btnSpinner.style.display = 'block';
                btnLogin.style.pointerEvents = 'none';
                btnLogin.style.opacity = '0.85';
            });
        }
    </script>
</body>
</html>