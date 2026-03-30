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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font/css/materialdesignicons.min.css">

    <style>
        :root {
            --primary-blue: #0d6efd;
            --primary-dark: #0a58ca;
            --body-bg: #f4f7fe;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--body-bg);
            background-image: radial-gradient(circle at 100% 0%, rgba(13, 110, 253, 0.05) 0%, transparent 50%),
                              radial-gradient(circle at 0% 100%, rgba(13, 110, 253, 0.05) 0%, transparent 50%);
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
            text-align: center;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card {
            background: var(--card-bg);
            border-radius: 32px;
            padding: 3rem 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.8);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .fingerprint-sensor {
            width: 120px;
            height: 120px;
            background: #f8fafc;
            border: 2px dashed #e2e8f0;
            border-radius: 35px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 2rem;
        }

        .fingerprint-sensor i {
            font-size: 70px;
            color: #cbd5e1;
            transition: all 0.3s;
        }

        .fingerprint-sensor:hover {
            border-color: var(--primary-blue);
            transform: scale(1.05);
            background: #eff6ff;
        }

        .fingerprint-sensor:hover i {
            color: var(--primary-blue);
        }

        .scan-line {
            position: absolute;
            width: 100%;
            height: 3px;
            background: var(--primary-blue);
            top: -10px;
            box-shadow: 0 0 15px var(--primary-blue);
            opacity: 0;
            z-index: 5;
        }

        .scanning {
            border-color: var(--primary-blue) !important;
            border-style: solid;
        }

        .scanning i {
            color: var(--primary-blue) !important;
        }

        .scanning .scan-line {
            opacity: 1;
            animation: scanMove 2s infinite linear;
        }

        @keyframes scanMove {
            0% { top: 0; }
            50% { top: 100%; }
            100% { top: 0; }
        }

        h3 {
            font-weight: 800;
            margin-bottom: 0.75rem;
            color: var(--text-main);
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }

        p {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .back-link {
            margin-top: 2rem;
            color: var(--primary-blue);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .back-link:hover {
            color: var(--primary-dark);
            transform: translateX(-3px);
        }

        .success-text {
            color: #10b981 !important;
            font-weight: 700;
        }

        .error-text {
            color: #ef4444 !important;
            font-weight: 700;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 2.5rem 1.5rem;
            }
            .fingerprint-sensor {
                width: 100px;
                height: 100px;
            }
            .fingerprint-sensor i {
                font-size: 60px;
            }
        }
    </style>
</head>

<body>

    <div class="login-wrapper">
        <div class="login-card">
            <h3>Akses Biometrik</h3>
            <p>Sentuh sensor fingerprint pada perangkat Anda untuk verifikasi.</p>

            <form action="{{ route('fingerprint.authenticate') }}" method="POST" id="fingerprintForm">
                @csrf

                <div class="fingerprint-sensor" id="scan-btn">
                    <i class="mdi mdi-fingerprint"></i>
                    <div class="scan-line"></div>
                </div>

                <p id="status-text" style="height: 24px; margin-bottom: 0;">Siap memindai...</p>
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

        async function startScan() {
            if (sensor.classList.contains('scanning')) return;

            if (!window.PublicKeyCredential) {
                statusText.innerText = "Unsupported Browser";
                statusText.classList.add('error-text');
                return;
            }

            try {
                sensor.classList.add('scanning');
                statusText.innerText = "Menunggu verifikasi...";
                statusText.className = "success-text";

                const challenge = new Uint8Array(32);
                window.crypto.getRandomValues(challenge);

                const credential = await navigator.credentials.create({
                    publicKey: {
                        challenge: challenge,
                        rp: { name: "PStore Absensi" },
                        user: {
                            id: new Uint8Array(16),
                            name: "user",
                            displayName: "Karyawan PStore"
                        },
                        pubKeyCredParams: [{ alg: -7, type: "public-key" }],
                        authenticatorSelection: {
                            authenticatorAttachment: "platform",
                            userVerification: "required"
                        },
                        timeout: 60000
                    }
                });

                statusText.innerText = "Verifikasi Berhasil!";
                setTimeout(() => form.submit(), 600);

            } catch (error) {
                sensor.classList.remove('scanning');
                statusText.innerText = "Gagal. Ketuk sensor untuk mencoba lagi.";
                statusText.className = "error-text";
            }
        }

        sensor.addEventListener('click', startScan);
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(startScan, 800);
        });
    </script>
</body>

</html>