<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover">
    <title>Masuk • Absensi PStore</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-page: #09090b;
            --bg-surface: #121215;
            --bg-subtle: #18181b;
            --border-subtle: #27272a;
            --border-focus: #2563eb;
            --text-primary: #f4f4f5;
            --text-secondary: #a1a1aa;
            --text-muted: #71717a;
            --accent: #2563eb;
            --accent-hover: #1d4ed8;
            --accent-active: #1e40af;
            --danger: #ef4444;
            --danger-bg: #1c1315;
            --danger-border: #7f1d1d;
            --success: #10b981;
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
            color: var(--text-primary);
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px 16px;
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
        }

        /* Top Header */
        .auth-header {
            margin-bottom: 24px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background-color: var(--bg-subtle);
            border: 1px solid var(--border-subtle);
            border-radius: 6px;
            margin-bottom: 16px;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: var(--success);
        }

        .status-text {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            text-transform: uppercase;
        }

        .auth-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.025em;
            line-height: 1.25;
            margin-bottom: 6px;
        }

        .auth-subtitle {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* Card Form */
        .auth-card {
            background-color: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            padding: 28px 24px;
        }

        /* Alert Box */
        .alert-box {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background-color: var(--danger-bg);
            border: 1px solid var(--danger-border);
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 20px;
        }

        .alert-icon {
            width: 18px;
            height: 18px;
            color: var(--danger);
            flex-shrink: 0;
            margin-top: 1px;
        }

        .alert-content {
            font-size: 13px;
            color: #fca5a5;
            line-height: 1.45;
        }

        /* Field Groups */
        .field-group {
            margin-bottom: 18px;
        }

        .field-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .field-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .caps-warning {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            color: #fbbf24;
            font-family: 'JetBrains Mono', monospace;
        }

        .caps-warning svg {
            width: 12px;
            height: 12px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            pointer-events: none;
        }

        .input-icon svg {
            width: 18px;
            height: 18px;
        }

        .field-input {
            width: 100%;
            height: 44px;
            padding: 0 14px 0 42px;
            background-color: var(--bg-subtle);
            border: 1px solid var(--border-subtle);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .field-input::placeholder {
            color: var(--text-muted);
        }

        .field-input:hover {
            border-color: #3f3f46;
        }

        .field-input:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 1px var(--border-focus);
        }

        .field-input-action {
            padding-right: 42px;
        }

        .btn-input-action {
            position: absolute;
            right: 8px;
            height: 32px;
            width: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            border-radius: 6px;
            color: var(--text-muted);
            cursor: pointer;
            transition: color 0.15s ease, background-color 0.15s ease;
        }

        .btn-input-action:hover {
            color: var(--text-primary);
            background-color: rgba(255, 255, 255, 0.05);
        }

        .btn-input-action svg {
            width: 18px;
            height: 18px;
        }

        /* Checkbox */
        .remember-row {
            margin-bottom: 22px;
        }

        .checkbox-container {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            user-select: none;
        }

        .checkbox-container input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .checkbox-box {
            width: 18px;
            height: 18px;
            border: 1px solid var(--border-subtle);
            background-color: var(--bg-subtle);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }

        .checkbox-box svg {
            width: 12px;
            height: 12px;
            color: #ffffff;
            opacity: 0;
            transform: scale(0.7);
            transition: all 0.15s ease;
        }

        .checkbox-container input:checked ~ .checkbox-box {
            background-color: var(--accent);
            border-color: var(--accent);
        }

        .checkbox-container input:checked ~ .checkbox-box svg {
            opacity: 1;
            transform: scale(1);
        }

        .checkbox-label {
            font-size: 13px;
            color: var(--text-secondary);
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            height: 44px;
            background-color: var(--accent);
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
            transition: background-color 0.15s ease, transform 0.05s ease;
        }

        .btn-submit:hover {
            background-color: var(--accent-hover);
        }

        .btn-submit:active {
            background-color: var(--accent-active);
            transform: scale(0.99);
        }

        .btn-spinner {
            display: none;
            width: 18px;
            height: 18px;
            animation: rotate 0.8s linear infinite;
        }

        @keyframes rotate {
            100% { transform: rotate(360deg); }
        }

        /* Footer */
        .auth-footer {
            margin-top: 24px;
            text-align: center;
        }

        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .security-badge svg {
            width: 14px;
            height: 14px;
            color: var(--text-muted);
        }

        .copyright-text {
            font-size: 12px;
            color: var(--text-muted);
        }

        @media (max-width: 480px) {
            body {
                padding: 16px 12px;
            }

            .auth-card {
                padding: 24px 18px;
            }

            .auth-title {
                font-size: 21px;
            }
        }
    </style>
</head>

<body>
    <main class="auth-container">
        <header class="auth-header">
            <div class="status-pill">
                <span class="status-dot"></span>
                <span class="status-text">PORTAL ABSENSI RESMI</span>
            </div>
            <h1 class="auth-title">Masuk ke Akun</h1>
            <p class="auth-subtitle">Masukkan ID Pegawai dan kata sandi Anda untuk mengakses dashboard.</p>
        </header>

        <section class="auth-card">
            @if ($errors->any())
                <div class="alert-box" role="alert">
                    <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <div class="alert-content">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST" id="loginForm" autocomplete="on">
                @csrf

                <div class="field-group">
                    <div class="field-label-row">
                        <label for="login_id" class="field-label">ID Pegawai</label>
                    </div>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M16 17v-1.5A3.5 3.5 0 0012.5 12h-5A3.5 3.5 0 004 15.5V17" stroke-linecap="round"/>
                                <circle cx="10" cy="6" r="3.5"/>
                            </svg>
                        </span>
                        <input 
                            type="text" 
                            id="login_id" 
                            name="login_id" 
                            class="field-input" 
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

                <div class="field-group">
                    <div class="field-label-row">
                        <label for="password" class="field-label">Kata Sandi</label>
                        <span id="capsWarning" class="caps-warning" style="display: none;">
                            <svg viewBox="0 0 16 16" fill="currentColor">
                                <path d="M8 2l4.5 5h-2.5v6h-4v-6h-2.5L8 2z"/>
                            </svg>
                            CAPS LOCK AKTIF
                        </span>
                    </div>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="4" y="9" width="12" height="8.5" rx="2"/>
                                <path d="M7 9V6a3 3 0 016 0v3"/>
                            </svg>
                        </span>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="field-input field-input-action" 
                            placeholder="Masukkan kata sandi" 
                            required
                        >
                        <button type="button" class="btn-input-action" id="togglePassBtn" aria-label="Tampilkan kata sandi">
                            <svg id="eyeIconShow" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M2 10s3.5-6 8-6 8 6 8 6-3.5 6-8 6-8-6-8-6z"/>
                                <circle cx="10" cy="10" r="2.5"/>
                            </svg>
                            <svg id="eyeIconHide" style="display: none;" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M3 3l14 14M10 4.5c4.5 0 8 5.5 8 5.5s-1.5 2.5-3.5 4M6 6.5C3.5 8 2 10 2 10s3.5 5.5 8 5.5c1.5 0 3-.5 4-1.2"/>
                                <path d="M9 9a2 2 0 002.8 2.8"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="remember-row">
                    <label class="checkbox-container">
                        <input type="checkbox" name="remember" id="remember" value="1">
                        <span class="checkbox-box">
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3.5 8.5l3 3 6-6"/>
                            </svg>
                        </span>
                        <span class="checkbox-label">Ingat sesi di perangkat ini</span>
                    </label>
                </div>

                <button type="submit" class="btn-submit" id="btnSubmit">
                    <span id="btnText">Masuk ke Sistem</span>
                    <svg class="btn-spinner" id="btnSpinner" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2.5" opacity="0.25"></circle>
                        <path d="M12 3a9 9 0 019 9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></path>
                    </svg>
                </button>
            </form>
        </section>

        <footer class="auth-footer">
            <div class="security-badge">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path d="M8 1.5l5 2.5v4c0 3.5-2.5 6-5 7-2.5-1-5-3.5-5-7V4l5-2.5z"/>
                    <path d="M6 8l1.5 1.5L10.5 6"/>
                </svg>
                <span>Koneksi Aman TLS 1.3 &bull; Terenkripsi</span>
            </div>
            <p class="copyright-text">&copy; {{ date('Y') }} PT PStore Digital Kreatif. Hak Cipta Dilindungi.</p>
        </footer>
    </main>

    <script>
        // Password Visibility Toggle
        const passwordInput = document.getElementById('password');
        const togglePassBtn = document.getElementById('togglePassBtn');
        const eyeIconShow = document.getElementById('eyeIconShow');
        const eyeIconHide = document.getElementById('eyeIconHide');

        if (togglePassBtn && passwordInput) {
            togglePassBtn.addEventListener('click', function() {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                eyeIconShow.style.display = isPassword ? 'none' : 'block';
                eyeIconHide.style.display = isPassword ? 'block' : 'none';
                passwordInput.focus();
            });
        }

        // Caps Lock Detection
        const capsWarning = document.getElementById('capsWarning');
        if (passwordInput && capsWarning) {
            const checkCapsLock = (e) => {
                if (e.getModifierState && e.getModifierState('CapsLock')) {
                    capsWarning.style.display = 'inline-flex';
                } else {
                    capsWarning.style.display = 'none';
                }
            };
            passwordInput.addEventListener('keydown', checkCapsLock);
            passwordInput.addEventListener('keyup', checkCapsLock);
            passwordInput.addEventListener('blur', () => {
                capsWarning.style.display = 'none';
            });
        }

        // Form Submit Loading State
        const loginForm = document.getElementById('loginForm');
        const btnSubmit = document.getElementById('btnSubmit');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');

        if (loginForm && btnSubmit) {
            loginForm.addEventListener('submit', function() {
                btnText.textContent = 'Memverifikasi...';
                btnSpinner.style.display = 'block';
                btnSubmit.style.pointerEvents = 'none';
                btnSubmit.style.opacity = '0.85';
            });
        }
    </script>
</body>
</html>