<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover">
    <title>Masuk • Absensi PStore</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-page: #f8fafc;
            --bg-card: #ffffff;
            --bg-panel: #f1f5f9;
            --border-light: #e2e8f0;
            --border-input: #cbd5e1;
            --border-focus: #2563eb;
            --text-heading: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --text-subtle: #94a3b8;
            --btn-primary: #2563eb;
            --btn-primary-hover: #1d4ed8;
            --btn-primary-active: #1e40af;
            --danger-bg: #fef2f2;
            --danger-border: #fecaca;
            --danger-text: #991b1b;
            --status-green: #10b981;
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
            flex-direction: column;
            min-height: 100vh;
        }

        /* Top Navigation Header */
        .top-navbar {
            height: 64px;
            background-color: #ffffff;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            flex-shrink: 0;
        }

        .brand-text-block {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-title {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 0.08em;
            color: var(--text-heading);
            text-transform: uppercase;
        }

        .brand-divider {
            width: 1px;
            height: 18px;
            background-color: var(--border-input);
        }

        .brand-subtitle {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .navbar-meta {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .clock-widget {
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            color: var(--text-muted);
            background-color: var(--bg-panel);
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid var(--border-light);
        }

        .live-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: var(--status-green);
        }

        /* Main Workspace Split Layout */
        .main-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
            width: 100%;
            max-width: 1240px;
            margin: 0 auto;
        }

        .split-grid {
            display: grid;
            grid-template-columns: 1fr 440px;
            gap: 60px;
            width: 100%;
            align-items: center;
        }

        /* Left Editorial Column */
        .showcase-column {
            padding-right: 20px;
        }

        .tag-lead {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            color: var(--btn-primary);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .headline-main {
            font-size: 34px;
            font-weight: 800;
            color: var(--text-heading);
            letter-spacing: -0.03em;
            line-height: 1.25;
            margin-bottom: 16px;
        }

        .desc-main {
            font-size: 15px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 32px;
            max-width: 540px;
        }

        /* Feature Pillars List */
        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            background-color: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: 10px;
            padding: 16px 20px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .feature-item:hover {
            border-color: var(--border-input);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .feature-icon-box {
            width: 38px;
            height: 38px;
            background-color: var(--bg-panel);
            border: 1px solid var(--border-light);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--btn-primary);
            flex-shrink: 0;
            margin-top: 2px;
        }

        .feature-icon-box svg {
            width: 18px;
            height: 18px;
        }

        .feature-text-block h3 {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-heading);
            margin-bottom: 4px;
        }

        .feature-text-block p {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* Right Form Card */
        .form-column {
            width: 100%;
        }

        .auth-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 14px;
            padding: 36px 32px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04), 0 10px 15px -3px rgba(0, 0, 0, 0.03);
        }

        .card-header-block {
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

        /* Alert Box */
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

        /* Form Controls */
        .field-group {
            margin-bottom: 20px;
        }

        .label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .field-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-heading);
        }

        .caps-warning {
            font-size: 11px;
            font-weight: 600;
            color: #b45309;
            font-family: 'JetBrains Mono', monospace;
        }

        .input-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-left {
            position: absolute;
            left: 14px;
            color: var(--text-subtle);
            pointer-events: none;
            display: flex;
            align-items: center;
        }

        .input-icon-left svg {
            width: 17px;
            height: 17px;
        }

        .form-input {
            width: 100%;
            height: 44px;
            padding: 0 14px 0 40px;
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
            color: var(--text-subtle);
        }

        .form-input:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .form-input-pass {
            padding-right: 44px;
        }

        .btn-toggle-eye {
            position: absolute;
            right: 8px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            border-radius: 6px;
            color: var(--text-muted);
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

        /* Remember Checkbox */
        .remember-group {
            margin-bottom: 24px;
        }

        .remember-label {
            display: inline-flex;
            align-items: center;
            gap: 10px;
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

        /* Action Button */
        .btn-submit {
            width: 100%;
            height: 44px;
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
            transition: background-color 0.15s ease, transform 0.05s ease;
        }

        .btn-submit:hover {
            background-color: var(--btn-primary-hover);
        }

        .btn-submit:active {
            background-color: var(--btn-primary-active);
            transform: scale(0.99);
        }

        .btn-submit svg.arrow-icon {
            width: 16px;
            height: 16px;
            transition: transform 0.15s ease;
        }

        .btn-submit:hover svg.arrow-icon {
            transform: translateX(2px);
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

        /* Form Security Note */
        .security-badge {
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.4;
        }

        .security-badge svg {
            width: 16px;
            height: 16px;
            color: var(--text-muted);
            flex-shrink: 0;
        }

        /* Page Footer */
        .site-footer {
            height: 56px;
            border-top: 1px solid var(--border-light);
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            font-size: 12px;
            color: var(--text-muted);
            flex-shrink: 0;
        }

        .footer-note {
            color: var(--text-muted);
        }

        /* Responsive Breakpoints */
        @media (max-width: 992px) {
            .split-grid {
                grid-template-columns: 1fr;
                gap: 40px;
                max-width: 480px;
            }

            .showcase-column {
                padding-right: 0;
                text-align: center;
            }

            .desc-main {
                margin: 0 auto 24px;
            }

            .feature-list {
                display: none; /* Hide feature cards on smaller screens to keep focus sharp */
            }

            .top-navbar {
                padding: 0 20px;
            }

            .site-footer {
                padding: 0 20px;
                flex-direction: column;
                justify-content: center;
                gap: 6px;
                height: auto;
                padding-top: 16px;
                padding-bottom: 16px;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .top-navbar {
                padding: 0 16px;
                height: 56px;
            }

            .brand-subtitle, .brand-divider {
                display: none;
            }

            .clock-widget {
                padding: 4px 8px;
                font-size: 11px;
            }

            .main-wrapper {
                padding: 24px 16px;
            }

            .auth-card {
                padding: 26px 20px;
            }

            .headline-main {
                font-size: 26px;
            }

            .card-title {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Top Navigation Header -->
    <header class="top-navbar">
        <div class="brand-text-block">
            <span class="brand-title">PSTORE</span>
            <span class="brand-divider"></span>
            <span class="brand-subtitle">Sistem Absensi Digital</span>
        </div>
        <div class="navbar-meta">
            <div class="clock-widget" id="liveClockWidget">
                <span class="live-dot"></span>
                <span id="liveClockText">WIB: --:--:--</span>
            </div>
        </div>
    </header>

    <!-- Main Workspace Split Layout -->
    <main class="main-wrapper">
        <div class="split-grid">
            <!-- Left Showcase Column -->
            <section class="showcase-column">
                <span class="tag-lead">Portal Presensi & Kepegawaian</span>
                <h1 class="headline-main">Akses Terpadu Kehadiran Kerja Seluruh Cabang.</h1>
                <p class="desc-main">Pencatatan kehadiran digital, verifikasi shift, dan manajemen perizinan karyawan secara cepat, transparan, dan terintegrasi.</p>

                <div class="feature-list">
                    <div class="feature-item">
                        <div class="feature-icon-box">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M3 4h4v4H3V4zm10 0h4v4h-4V4zM3 12h4v4H3v-4zm7 0h3v4h-3v-4zm4 0h3v4h-3v-4zM9 4h2v6H9V4z"/>
                            </svg>
                        </div>
                        <div class="feature-text-block">
                            <h3>Presensi Real-Time & Valid</h3>
                            <p>Verifikasi absensi harian dengan akurasi jadwal dan lokasi kerja di setiap cabang.</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon-box">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="10" cy="10" r="7"/>
                                <path d="M10 6v4l2.5 2.5"/>
                            </svg>
                        </div>
                        <div class="feature-text-block">
                            <h3>Rekapitulasi Transparan</h3>
                            <p>Pantau jam kedatangan, kepulangan, riwayat cuti, dan akumulasi lembur harian.</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon-box">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M3 17h14M4 17V5a1 1 0 011-1h5a1 1 0 011 1v12m-7-9h3m-3 4h3m4 5V9a1 1 0 011-1h4a1 1 0 011 1v8m-6-5h2m-2 4h2"/>
                            </svg>
                        </div>
                        <div class="feature-text-block">
                            <h3>Seluruh Cabang Terhubung</h3>
                            <p>Sinkronisasi data langsung dengan seluruh operasional store di Indonesia.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Right Form Column -->
            <section class="form-column">
                <div class="auth-card">
                    <div class="card-header-block">
                        <h2 class="card-title">Masuk ke Akun</h2>
                        <p class="card-subtitle">Masukkan ID Pegawai dan kata sandi Anda untuk mengakses dashboard.</p>
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

                        <div class="field-group">
                            <div class="label-row">
                                <label for="login_id" class="field-label">ID Pegawai</label>
                            </div>
                            <div class="input-container">
                                <span class="input-icon-left">
                                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M16 17v-1.5A3.5 3.5 0 0012.5 12h-5A3.5 3.5 0 004 15.5V17" stroke-linecap="round"/>
                                        <circle cx="10" cy="6" r="3.5"/>
                                    </svg>
                                </span>
                                <input 
                                    type="text" 
                                    id="login_id" 
                                    name="login_id" 
                                    class="form-input" 
                                    placeholder="Masukkan ID Login Anda" 
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
                            <div class="label-row">
                                <label for="password" class="field-label">Kata Sandi</label>
                                <span id="capsNotice" class="caps-warning" style="display: none;">CAPS LOCK AKTIF</span>
                            </div>
                            <div class="input-container">
                                <span class="input-icon-left">
                                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <rect x="4" y="9" width="12" height="8.5" rx="2"/>
                                        <path d="M7 9V6a3 3 0 016 0v3"/>
                                    </svg>
                                </span>
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

                        <button type="submit" class="btn-submit" id="btnSubmit">
                            <span id="btnText">Masuk ke Sistem</span>
                            <svg class="arrow-icon" id="btnArrow" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 10h12M11 5l5 5-5 5"/>
                            </svg>
                            <svg class="btn-spinner" id="btnSpinner" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2.5" opacity="0.25"></circle>
                                <path d="M12 3a9 9 0 019 9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></path>
                            </svg>
                        </button>
                    </form>

                    <div class="security-badge">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M10 2l6 3v5c0 4.5-3 8-6 9-3-1-6-4.5-6-9V5l6-3z"/>
                            <path d="M8 10l1.5 1.5 3-3"/>
                        </svg>
                        <span>Koneksi aman dengan enkripsi tingkat server TLS 1.3</span>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Site Footer -->
    <footer class="site-footer">
        <div>
            &copy; {{ date('Y') }} PT Putra Siregar Merakyat. Hak Cipta Dilindungi.
        </div>
        <div class="footer-note">
            Portal Kepegawaian &bull; Akses Terbatas Karyawan
        </div>
    </footer>

    <script>
        // Live Real-Time Clock
        function updateLiveClock() {
            const clockEl = document.getElementById('liveClockText');
            if (!clockEl) return;
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            clockEl.textContent = `WIB: ${hours}:${minutes}:${seconds}`;
        }
        updateLiveClock();
        setInterval(updateLiveClock, 1000);

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
        const btnSubmit = document.getElementById('btnSubmit');
        const btnText = document.getElementById('btnText');
        const btnArrow = document.getElementById('btnArrow');
        const btnSpinner = document.getElementById('btnSpinner');

        if (loginForm && btnSubmit) {
            loginForm.addEventListener('submit', function() {
                btnText.textContent = 'Memverifikasi...';
                if (btnArrow) btnArrow.style.display = 'none';
                if (btnSpinner) btnSpinner.style.display = 'block';
                btnSubmit.style.pointerEvents = 'none';
                btnSubmit.style.opacity = '0.85';
            });
        }
    </script>
</body>
</html>