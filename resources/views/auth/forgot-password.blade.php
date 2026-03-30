<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover">
    <title>Lupa Password - PStore Absensi</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap"
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
            background-image: radial-gradient(circle at 0% 100%, rgba(13, 110, 253, 0.05) 0%, transparent 50%);
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

        .login-card {
            background: var(--card-bg);
            border-radius: 28px;
            padding: 2.5rem 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.8);
            text-align: center;
        }

        .logo-box {
            width: 60px;
            height: 60px;
            margin: 0 auto 1.5rem;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-box i {
            font-size: 28px;
            color: var(--primary-blue);
        }

        h4 {
            font-weight: 800;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.25rem;
            background: var(--input-bg);
            border: 1.5px solid var(--input-border);
            border-radius: 14px;
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
            border-radius: 14px;
            color: #ffffff;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
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

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #10b981;
            color: #065f46;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            text-align: left;
        }

        .back-link {
            margin-top: 2rem;
            display: block;
            color: var(--primary-blue);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 700;
            transition: all 0.2s;
        }

        .back-link:hover {
            color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .error-text {
            color: #ef4444;
            font-size: 0.8rem;
            margin-top: 0.5rem;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="logo-box">
                <i class="mdi mdi-lock-reset"></i>
            </div>

            <h4>Reset Password</h4>
            <p class="subtitle">Masukkan email terdaftar Anda untuk menerima link instruksi reset password.</p>

            @if (session('status'))
                <div class="alert-success">
                    <i class="fas fa-check-circle me-1"></i> {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST">
                @csrf
                <div class="form-group">
                    <input type="email" class="form-control" name="email" placeholder="Email Address"
                        value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <div class="error-text"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-submit">
                    KIRIM LINK RESET
                </button>
            </form>

            <a href="{{ route('login') }}" class="back-link">
                Kembali ke Login
            </a>
        </div>
    </div>

</body>

</html>