<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover">
    <title>Portal Masuk • PSTORE Absensi</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-page: #f4f6f8;
            --bg-surface: #ffffff;
            --bg-subtle: #f8fafc;
            --bg-section: #fafbfc;
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
            padding: 36px 20px;
            width: 100%;
        }

        /* Unified Vertical Card */
        .auth-container-card {
            width: 100%;
            max-width: 520px;
            background-color: var(--bg-surface);
            border: 1px solid var(--border-default);
            border-radius: 14px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 10px 20px -3px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* 1. Waktu Operasional Sesuai Kota (WIB, WITA, WIT) */
        .clock-header-section {
            background-color: var(--bg-subtle);
            border-bottom: 1px solid var(--border-default);
            padding: 22px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .clock-meta-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .live-clock-time {
            font-family: 'JetBrains Mono', monospace;
            font-size: 26px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.02em;
            line-height: 1.1;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tz-tag {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-primary);
            background-color: #e2e8f0;
            padding: 2px 7px;
            border-radius: 4px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            letter-spacing: 0.04em;
        }

        .live-date-desc {
            font-size: 12px;
            font-weight: 500;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        .clock-status-pill {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            background-color: #ffffff;
            border: 1px solid var(--border-default);
            padding: 5px 10px;
            border-radius: 6px;
            white-space: nowrap;
        }

        /* 2. Form Area */
        .form-body-section {
            padding: 30px 28px 24px;
        }

        .auth-heading {
            margin-bottom: 22px;
        }

        .auth-title {
            font-size: 21px;
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

        /* Alert Error */
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

        /* Checkbox & 1-Week Session Note */
        .remember-section {
            margin: 20px 0 24px 0;
        }

        .checkbox-container {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            cursor: pointer;
            user-select: none;
        }

        .checkbox-container input {
            width: 16px;
            height: 16px;
            margin-top: 2px;
            accent-color: var(--btn-solid);
            cursor: pointer;
            flex-shrink: 0;
        }

        .remember-text-block {
            display: flex;
            flex-direction: column;
        }

        .remember-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .remember-hint {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 2px;
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

        /* 3. Ketentuan Presensi DIBAWAH FORM */
        .guidelines-section {
            background-color: var(--bg-section);
            border-top: 1px solid var(--border-default);
            padding: 24px 28px;
        }

        .guideline-section-title {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 14px;
        }

        .guideline-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .guideline-card-item {
            background-color: #ffffff;
            border: 1px solid var(--border-default);
            border-radius: 8px;
            padding: 10px 14px;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .guideline-item-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .guideline-badge-name {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .guideline-badge-tag {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            background-color: var(--bg-subtle);
            padding: 1px 6px;
            border-radius: 4px;
            border: 1px solid var(--border-default);
        }

        .guideline-instruction {
            font-size: 12px;
            color: var(--text-secondary);
            line-height: 1.45;
        }

        .help-caption {
            margin-top: 16px;
            font-size: 11px;
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
        @media (max-width: 580px) {
            .page-container {
                padding: 16px 12px;
            }

            .auth-container-card {
                max-width: 100%;
                border-radius: 12px;
            }

            .clock-header-section {
                padding: 18px 20px;
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .clock-status-pill {
                align-self: flex-start;
            }

            .form-body-section {
                padding: 24px 20px 20px;
            }

            .guidelines-section {
                padding: 20px;
            }

            .site-header {
                padding: 0 16px;
            }

            .site-footer {
                padding: 0 16px;
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
        <div class="auth-container-card">

            <!-- 1. WAKTU OPERASIONAL (Dinamis Sesuai Kota: WIB, WITA, WIT) -->
            <section class="clock-header-section">
                <div>
                    <div class="clock-meta-label">Waktu Presensi Lokal</div>
                    <div class="live-clock-time">
                        <span id="liveClockText">--:--:--</span>
                        <span class="tz-tag" id="liveClockTz">WIB</span>
                    </div>
                    <div class="live-date-desc" id="liveDateText">Memuat tanggal & zona waktu...</div>
                </div>
                <div class="clock-status-pill">
                    <span class="status-dot"></span>
                    <span>Sinkron Server</span>
                </div>
            </section>

            <!-- 2. FORM MASUK KE SISTEM -->
            <section class="form-body-section">
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

                    <div class="remember-section">
                        <label class="checkbox-container">
                            <input type="checkbox" name="remember" id="remember" value="1">
                            <div class="remember-text-block">
                                <span class="remember-title">Ingat sesi di perangkat ini (1 Minggu)</span>
                                <span class="remember-hint">Sesi tetap aktif 7 hari ke depan sebelum harus masuk kembali.</span>
                            </div>
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
            </section>

            <!-- 3. KETENTUAN PRESENSI (DIBAWAH FORM) -->
            <section class="guidelines-section">
                <div class="guideline-section-title">Ketentuan Presensi Karyawan</div>
                <div class="guideline-list">
                    <div class="guideline-card-item">
                        <div class="guideline-item-header">
                            <span class="guideline-badge-name">Presensi Masuk</span>
                            <span class="guideline-badge-tag">Kedatangan</span>
                        </div>
                        <p class="guideline-instruction">Scan saat tiba di cabang via security atau lakukan presensi mandiri sesuai ketentuan.</p>
                    </div>

                    <div class="guideline-card-item">
                        <div class="guideline-item-header">
                            <span class="guideline-badge-name">Presensi Pulang</span>
                            <span class="guideline-badge-tag">Kepulangan</span>
                        </div>
                        <p class="guideline-instruction">Scan sebelum pulang via security atau lakukan presensi mandiri sebelum meninggalkan cabang.</p>
                    </div>

                    <div class="guideline-card-item">
                        <div class="guideline-item-header">
                            <span class="guideline-badge-name">Perizinan &amp; Cuti</span>
                            <span class="guideline-badge-tag">Portal Izin</span>
                        </div>
                        <p class="guideline-instruction">Ajukan permohonan izin, sakit, atau cuti kerja langsung melalui menu perizinan di portal.</p>
                    </div>
                </div>

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
        // Real-Time Clock & Date with Local Timezone Detection (WIB, WITA, WIT)
        function updateLiveDateTime() {
            const clockEl = document.getElementById('liveClockText');
            const tzEl = document.getElementById('liveClockTz');
            const dateEl = document.getElementById('liveDateText');
            if (!clockEl || !dateEl) return;

            const now = new Date();

            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');

            clockEl.textContent = `${hours}:${minutes}:${seconds}`;

            // Deteksi zona waktu perangkat (WIB, WITA, WIT)
            const offsetMinutes = -now.getTimezoneOffset(); // Selisih menit dari UTC
            let tzCode = 'WIB';
            let tzDesc = 'Waktu Indonesia Barat';

            if (offsetMinutes >= 450 && offsetMinutes < 510) { // UTC+8
                tzCode = 'WITA';
                tzDesc = 'Waktu Indonesia Tengah';
            } else if (offsetMinutes >= 510 && offsetMinutes < 570) { // UTC+9
                tzCode = 'WIT';
                tzDesc = 'Waktu Indonesia Timur';
            } else if (offsetMinutes >= 390 && offsetMinutes < 450) { // UTC+7
                tzCode = 'WIB';
                tzDesc = 'Waktu Indonesia Barat';
            } else {
                const tzStr = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
                if (tzStr.includes('Makassar') || tzStr.includes('Ujung_Pandang') || tzStr.includes('Bali') || tzStr.includes('Pontianak') === false && offsetMinutes === 480) {
                    tzCode = 'WITA';
                    tzDesc = 'Waktu Indonesia Tengah';
                } else if (tzStr.includes('Jayapura') || offsetMinutes === 540) {
                    tzCode = 'WIT';
                    tzDesc = 'Waktu Indonesia Timur';
                } else {
                    const hoursOffset = offsetMinutes / 60;
                    const sign = hoursOffset >= 0 ? '+' : '';
                    tzCode = `UTC${sign}${hoursOffset}`;
                    tzDesc = tzStr || 'Waktu Lokal';
                }
            }

            if (tzEl) tzEl.textContent = tzCode;

            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            const dayName = days[now.getDay()];
            const dateNum = now.getDate();
            const monthName = months[now.getMonth()];
            const yearNum = now.getFullYear();

            dateEl.textContent = `${dayName}, ${dateNum} ${monthName} ${yearNum} • ${tzDesc}`;
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