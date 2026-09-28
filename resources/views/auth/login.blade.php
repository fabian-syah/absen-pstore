<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover">
    <title>Portal Masuk • PSTORE Absensi</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-page: #f4f6f8;
            --bg-surface: #ffffff;
            --bg-subtle: #f8fafc;
            --border-default: #e2e8f0;
            --border-focus: #0f172a;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --text-subtle: #94a3b8;
            --btn-solid: #0f172a;
            --btn-solid-hover: #1e293b;
            --btn-solid-active: #020617;
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
            color: var(--text-primary);
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 100vh;
        }

        /* Top Header */
        .site-header {
            height: 60px;
            background-color: var(--bg-surface);
            border-bottom: 1px solid var(--border-default);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            flex-shrink: 0;
        }

        .header-brand {
            display: flex;
            align-items: baseline;
            gap: 10px;
        }

        .brand-name {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 0.12em;
            color: var(--text-primary);
        }

        .brand-system {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .header-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-secondary);
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: var(--status-green);
        }

        /* Main Workspace Container */
        .page-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            width: 100%;
        }

        /* Unified Terminal / Dock Card */
        .dock-card {
            width: 100%;
            max-width: 860px;
            background-color: var(--bg-surface);
            border: 1px solid var(--border-default);
            border-radius: 14px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 10px 20px -3px rgba(0, 0, 0, 0.04);
            display: flex;
            overflow: hidden;
        }

        /* Left Side: Operational Info & Live Clock */
        .dock-left {
            width: 340px;
            background-color: var(--bg-subtle);
            border-right: 1px solid var(--border-default);
            padding: 36px 30px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .section-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 12px;
        }

        .clock-display-block {
            margin-bottom: 28px;
        }

        .live-clock {
            font-family: 'JetBrains Mono', monospace;
            font-size: 30px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.02em;
            line-height: 1.1;
        }

        .live-date {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-secondary);
            margin-top: 6px;
        }

        .guidelines-box {
            background-color: var(--bg-surface);
            border: 1px solid var(--border-default);
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .guideline-title {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .guideline-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 0;
            font-size: 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .guideline-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .guideline-name {
            color: var(--text-secondary);
            font-weight: 600;
        }

        .guideline-desc {
            color: var(--text-primary);
            font-weight: 500;
            text-align: right;
        }

        .dock-left-note {
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.5;
            padding-top: 16px;
            border-top: 1px solid var(--border-default);
        }

        /* Right Side: Auth Form */
        .dock-right {
            flex: 1;
            padding: 40px 38px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-heading {
            margin-bottom: 24px;
        }

        .auth-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.02em;
        }

        .auth-desc {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 4px;
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
            margin-bottom: 18px;
        }

        .field-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        .field-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-primary);
            letter-spacing: 0.01em;
        }

        .caps-alert {
            font-size: 11px;
            font-weight: 600;
            color: #b45309;
            font-family: 'JetBrains Mono', monospace;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .text-input {
            width: 100%;
            height: 44px;
            padding: 0 14px;
            background-color: var(--bg-surface);
            border: 1px solid var(--border-default);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            color: var(--text-primary);
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .text-input:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 2px rgba(15, 23, 42, 0.08);
        }

        .text-input::placeholder {
            color: var(--text-subtle);
        }

        .input-with-eye {
            padding-right: 44px;
        }

        .btn-eye-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 6px;
            color: var(--text-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-eye-toggle:hover {
            color: var(--text-primary);
        }

        .btn-eye-toggle svg {
            width: 18px;
            height: 18px;
        }

        /* Checkbox */
        .option-row {
            display: flex;
            align-items: center;
            margin: 20px 0 24px 0;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--text-secondary);
            cursor: pointer;
            user-select: none;
        }

        .checkbox-container input {
            width: 16px;
            height: 16px;
            accent-color: var(--btn-solid);
            cursor: pointer;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            height: 44px;
            background-color: var(--btn-solid);
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

        .btn-submit:hover {
            background-color: var(--btn-solid-hover);
        }

        .btn-submit:active {
            background-color: var(--btn-solid-active);
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

        .help-caption {
            margin-top: 20px;
            font-size: 12px;
            color: var(--text-subtle);
            text-align: center;
            line-height: 1.4;
        }

        /* Site Footer */
        .site-footer {
            height: 52px;
            border-top: 1px solid var(--border-default);
            background-color: var(--bg-surface);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            font-size: 12px;
            color: var(--text-muted);
            flex-shrink: 0;
        }

        /* Responsive Breakpoints */
        @media (max-width: 820px) {
            .dock-card {
                flex-direction: column;
                max-width: 440px;
            }

            .dock-left {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid var(--border-default);
                padding: 24px;
            }

            .dock-left-note {
                display: none;
            }

            .guidelines-box {
                margin-bottom: 0;
            }

            .dock-right {
                padding: 30px 24px;
            }

            .site-header {
                padding: 0 20px;
            }

            .site-footer {
                padding: 0 20px;
                flex-direction: column;
                justify-content: center;
                gap: 4px;
                height: auto;
                padding-top: 14px;
                padding-bottom: 14px;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="site-header">
        <div class="header-brand">
            <span class="brand-name">PSTORE</span>
            <span class="brand-system">Sistem Presensi Karyawan</span>
        </div>
        <div class="header-status">
            <span class="status-dot"></span>
            <span>Server Online</span>
        </div>
    </header>

    <!-- Main Workspace -->
    <main class="page-container">
        <div class="dock-card">
            <!-- Left: Operational Attendance Reference -->
            <section class="dock-left">
                <div>
                    <div class="section-label">Waktu & Kehadiran</div>
                    <div class="clock-display-block">
                        <div class="live-clock" id="liveClockDisplay">--:--:--</div>
                        <div class="live-date" id="liveDateDisplay">Memuat tanggal...</div>
                    </div>

                    <div class="guidelines-box">
                        <div class="guideline-title">Ketentuan Presensi</div>
                        <div class="guideline-item">
                            <span class="guideline-name">Presensi Masuk</span>
                            <span class="guideline-desc">Scan saat tiba di cabang</span>
                        </div>
                        <div class="guideline-item">
                            <span class="guideline-name">Presensi Pulang</span>
                            <span class="guideline-desc">Scan sebelum pulang</span>
                        </div>
                        <div class="guideline-item">
                            <span class="guideline-name">Izin &amp; Cuti</span>
                            <span class="guideline-desc">Ajukan via portal</span>
                        </div>
                    </div>
                </div>

                <div class="dock-left-note">
                    Pastikan scan kehadiran masuk &amp; pulang selalu terekam untuk validasi kehadiran kerja.
                </div>
            </section>

            <!-- Right: Authentication Form -->
            <section class="dock-right">
                <div class="auth-heading">
                    <h1 class="auth-title">Masuk ke Sistem</h1>
                    <p class="auth-desc">Gunakan ID Pegawai dan kata sandi Anda untuk mengakses portal.</p>
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
                        <div class="field-header">
                            <label for="login_id" class="field-label">ID Pegawai</label>
                        </div>
                        <div class="input-wrapper">
                            <input 
                                type="text" 
                                id="login_id" 
                                name="login_id" 
                                class="text-input" 
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
                        <div class="field-header">
                            <label for="password" class="field-label">Kata Sandi</label>
                            <span id="capsNotice" class="caps-alert" style="display: none;">CAPS LOCK AKTIF</span>
                        </div>
                        <div class="input-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="text-input input-with-eye" 
                                placeholder="Masukkan kata sandi" 
                                required
                            >
                            <button type="button" class="btn-eye-toggle" id="togglePassBtn" aria-label="Tampilkan kata sandi">
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

                    <div class="option-row">
                        <label class="checkbox-container">
                            <input type="checkbox" name="remember" id="remember" value="1">
                            <span>Ingat sesi di perangkat ini</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-submit" id="btnSubmit">
                        <span id="btnText">Masuk ke Portal</span>
                        <svg class="btn-spinner" id="btnSpinner" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2.5" opacity="0.25"></circle>
                            <path d="M12 3a9 9 0 019 9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></path>
                        </svg>
                    </button>
                </form>

                <div class="help-caption">
                    Kendala akses akun? Hubungi HRD atau IT Support cabang Anda.
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div>
            &copy; {{ date('Y') }} PT Putra Siregar Merakyat. Hak Cipta Dilindungi.
        </div>
        <div>
            Sistem Absensi Terpadu PSTORE
        </div>
    </footer>

    <script>
        // Real-Time Clock & Date in Indonesian Locale
        function updateLiveDateTime() {
            const clockEl = document.getElementById('liveClockDisplay');
            const dateEl = document.getElementById('liveDateDisplay');
            if (!clockEl || !dateEl) return;

            const now = new Date();

            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            clockEl.textContent = `${hours}:${minutes}:${seconds} WIB`;

            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            const dayName = days[now.getDay()];
            const dateNum = now.getDate();
            const monthName = months[now.getMonth()];
            const yearNum = now.getFullYear();

            dateEl.textContent = `${dayName}, ${dateNum} ${monthName} ${yearNum}`;
        }
        updateLiveDateTime();
        setInterval(updateLiveDateTime, 1000);

        // Password Visibility Toggle
        const passwordInput = document.getElementById('password');
        const togglePassBtn = document.getElementById('togglePassBtn');
        const eyeShow = document.getElementById('eyeShow');
        const eyeHide = document.getElementById('eyeHide');

        if (togglePassBtn && passwordInput) {
            togglePassBtn.addEventListener('click', function() {
                const isPass = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPass ? 'text' : 'password');
                eyeShow.style.display = isPass ? 'none' : 'block';
                eyeHide.style.display = isPass ? 'block' : 'none';
            });
        }

        // Caps Lock Warning
        const capsNotice = document.getElementById('capsNotice');
        if (passwordInput && capsNotice) {
            passwordInput.addEventListener('keyup', function(e) {
                if (e.getModifierState && e.getModifierState('CapsLock')) {
                    capsNotice.style.display = 'inline-block';
                } else {
                    capsNotice.style.display = 'none';
                }
            });
            passwordInput.addEventListener('blur', function() {
                capsNotice.style.display = 'none';
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
                if (btnSpinner) btnSpinner.style.display = 'block';
                btnSubmit.style.pointerEvents = 'none';
                btnSubmit.style.opacity = '0.85';
            });
        }
    </script>
</body>
</html>