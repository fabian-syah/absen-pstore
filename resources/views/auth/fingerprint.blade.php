<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Fingerprint Login - PStore</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Using MDI for fingerprint icon compatibility -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font/css/materialdesignicons.min.css">

    <style>
        :root {
            /* Using the same dark mode colors as login page */
            --primary-gold: #D4AF37;
            --soft-gold: #F4C430;
            --deep-emerald: #013220;
            --glass-bg: rgba(0, 34, 22, 0.7);
            --glass-border: rgba(212, 175, 55, 0.2);
            --body-bg: radial-gradient(circle at center, #004d2e 0%, #001a0f 100%);
            --text-main: #ffffff;
            --text-muted: rgba(255, 255, 255, 0.6);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--body-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: var(--text-main);
            margin: 0;
        }

        .bg-ornaments {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 1;
        }

        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 90%;
            max-width: 400px;
            text-align: center;
        }

        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border);
            border-radius: 35px;
            padding: 3rem 2rem;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.4);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .fingerprint-sensor {
            width: 120px;
            height: 120px;
            border: 2px dashed rgba(212, 175, 55, 0.3);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }

        .fingerprint-sensor i {
            font-size: 80px;
            color: rgba(255, 255, 255, 0.5);
            transition: all 0.3s;
        }

        .fingerprint-sensor:hover {
            border-color: var(--primary-gold);
            transform: scale(1.05);
            background: rgba(212, 175, 55, 0.05);
        }

        .fingerprint-sensor:hover i {
            color: var(--primary-gold);
        }

        .scan-line {
            position: absolute;
            width: 100%;
            height: 4px;
            background: #00ce68;
            top: -10px;
            box-shadow: 0 0 10px #00ce68;
            opacity: 0;
        }

        .scanning {
            border-color: #00ce68 !important;
            border-style: solid;
        }

        .scanning i {
            color: #00ce68 !important;
        }

        .scanning .scan-line {
            opacity: 1;
            animation: scanMove 1.5s infinite linear;
        }

        @keyframes scanMove {
            0% {
                top: 0;
            }

            50% {
                top: 100%;
            }

            100% {
                top: 0;
            }
        }

        h3 {
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: var(--primary-gold);
        }

        p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .text-success {
            color: #00ce68 !important;
            font-weight: bold;
        }

        .back-link {
            margin-top: 2rem;
            color: var(--primary-gold);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="bg-ornaments">
        <svg width="100%" height="100%" opacity="0.04">
            <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                <path d="M 40 0 L 0 0 0 40" fill="none" stroke="currentColor" stroke-width="1" />
            </pattern>
            <rect width="100%" height="100%" fill="url(#grid)" />
        </svg>
    </div>

    <div class="login-wrapper">
        <div class="login-card">
            <h3>Fingerprint Access</h3>
            <p>Sentuh sensor untuk verifikasi identitas</p>

            <form action="{{ route('fingerprint.authenticate') }}" method="POST" id="fingerprintForm">
                @csrf

                <div class="fingerprint-sensor" id="scan-btn">
                    <i class="mdi mdi-fingerprint"></i>
                    <div class="scan-line"></div>
                </div>

                <p id="status-text" style="height: 20px; margin-bottom: 0;">Ready to Scan</p>
            </form>

            <a href="{{ route('login') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Kembali ke Login Biasa
            </a>
        </div>
    </div>

    <script>
        const statusText = document.getElementById('status-text');
        const sensor = document.getElementById('scan-btn');
        const form = document.getElementById('fingerprintForm');

        sensor.addEventListener('click', async function () {
            // Prevent double click
            if (sensor.classList.contains('scanning')) return;

            // 1. Check if Browser Supports WebAuthn
            if (!window.PublicKeyCredential) {
                alert("Browser ini tidak mendukung WebAuthn (Fingerprint/FaceID).");
                return;
            }

            try {
                // UI Update
                sensor.classList.add('scanning');
                statusText.innerText = "Mengakses Sensor Biometrik...";
                statusText.classList.add('text-success');

                // 2. Create Dummy Challenge (in real app, this comes from server)
                const challenge = new Uint8Array(32);
                window.crypto.getRandomValues(challenge);

                // 3. Trigger Native OS Biometric Prompt
                // We use 'create' here purely to force the "Verify it's you" dialog.
                // This prevents "mouse click" login because the OS intercepts it.
                const credential = await navigator.credentials.create({
                    publicKey: {
                        challenge: challenge,
                        rp: { name: "PStore Absensi" },
                        user: {
                            id: new Uint8Array(16),
                            name: "bianajah5",
                            displayName: "Fabian Syah"
                        },
                        pubKeyCredParams: [{ alg: -7, type: "public-key" }],
                        authenticatorSelection: {
                            authenticatorAttachment: "platform", // Forces built-in sensor (TouchID/Hello)
                            userVerification: "required" // Forces PIN/Biometric, can't just click OK
                        },
                        timeout: 60000
                    }
                });

                // 4. Success! (The user passed the OS Biometric check)
                console.log("Biometric Success:", credential);
                statusText.innerText = "Verifikasi Berhasil! Sedang masuk...";

                // Submit form to log in
                setTimeout(() => {
                    form.submit();
                }, 500);

            } catch (error) {
                // User cancelled or failed biometric
                console.error(error);
                sensor.classList.remove('scanning');
                statusText.innerText = "Gagal / Dibatalkan. Coba lagi.";
                statusText.classList.remove('text-success');
                statusText.classList.add('text-danger');
                alert("Verifikasi Biometrik Gagal atau Dibatalkan oleh user.");
            }
        });
    </script>
</body>

</html>