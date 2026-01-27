<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - PStore Absensi</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            overflow: hidden;
            height: 100%;
            width: 100%;
            position: fixed;
            touch-action: manipulation;
            background-color: #0a0a0a;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100%;
            height: 100%;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #0a0a0a;
            /* Solid fallback */
            background: #0a0a0a;
            overflow: hidden;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            -webkit-overflow-scrolling: none;
            overscroll-behavior: none;
        }

        /* Animated background shapes */
        .shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.08;
            animation: float 10s ease-in-out infinite;
            pointer-events: none;
        }

        .shape-1 {
            width: 500px;
            height: 500px;
            background: #444444;
            top: -250px;
            left: -250px;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 400px;
            height: 400px;
            background: #333333;
            bottom: -200px;
            right: -200px;
            animation-delay: -3s;
        }

        .shape-3 {
            width: 300px;
            height: 300px;
            background: #555555;
            top: 80%;
            left: -100px;
            animation-delay: -5s;
        }

        /* Hide shapes on mobile */
        @media (max-width: 768px) {
            .shape {
                display: none;
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            25% {
                transform: translate(20px, -20px) scale(1.03);
            }

            50% {
                transform: translate(-15px, 15px) scale(0.97);
            }

            75% {
                transform: translate(15px, 20px) scale(1.02);
            }
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
            background: #1a1a1a;
            /* Solid fallback for Safari */
            background: rgba(30, 30, 30, 0.95);
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

        /* Safari fallback */
        @supports not (backdrop-filter: blur(24px)) {
            .login-card {
                background: #1a1a1a;
            }
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

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 15px 35px rgba(255, 255, 255, 0.1);
            }

            50% {
                transform: scale(1.03);
                box-shadow: 0 20px 45px rgba(255, 255, 255, 0.15);
            }
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

        .input-wrapper>i {
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
            background-color: #2a2a2a;
            /* Solid fallback */
            background: rgba(50, 50, 50, 0.9);
            border: 2px solid rgba(255, 255, 255, 0.15);
            border-radius: 14px;
            font-size: 15px;
            color: #ffffff;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            outline: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .form-control:focus {
            border-color: rgba(255, 255, 255, 0.5);
            background-color: #333333;
            background: rgba(60, 60, 60, 0.95);
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.08);
        }

        .input-wrapper:focus-within>i {
            color: white;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            font-size: 16px;
            transition: color 0.3s ease;
            padding: 8px;
            line-height: 1;
            z-index: 5;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
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
            background-color: #252525;
            /* Solid fallback */
            background: rgba(40, 40, 40, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.15);
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

            0%,
            100% {
                transform: translateX(0);
            }

            20% {
                transform: translateX(-5px);
            }

            40% {
                transform: translateX(5px);
            }

            60% {
                transform: translateX(-5px);
            }

            80% {
                transform: translateX(5px);
            }
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

        /* Responsive - All Devices */

        /* Large Desktop (1400px+) */
        @media (min-width: 1400px) {
            .login-container {
                max-width: 480px;
            }

            .login-card {
                padding: 56px 48px;
            }

            .logo-icon {
                width: 90px;
                height: 90px;
            }

            .logo-icon i {
                font-size: 42px;
            }

            .brand-name {
                font-size: 36px;
            }
        }

        /* Desktop (1200px - 1399px) */
        @media (min-width: 1200px) and (max-width: 1399px) {
            .login-container {
                max-width: 460px;
            }
        }

        /* Laptop (992px - 1199px) */
        @media (min-width: 992px) and (max-width: 1199px) {
            .login-container {
                max-width: 440px;
            }

            .login-card {
                padding: 44px 36px;
            }
        }

        /* Tablet Landscape (768px - 991px) */
        @media (min-width: 768px) and (max-width: 991px) {
            .login-container {
                max-width: 420px;
            }

            .login-card {
                padding: 40px 32px;
            }

            .logo-icon {
                width: 75px;
                height: 75px;
            }

            .logo-icon i {
                font-size: 35px;
            }

            .brand-name {
                font-size: 30px;
            }
        }

        /* Tablet Portrait (600px - 767px) */
        @media (min-width: 600px) and (max-width: 767px) {
            .login-container {
                max-width: 400px;
                padding: 24px;
            }

            .login-card {
                padding: 36px 28px;
                border-radius: 24px;
            }

            .logo-section {
                margin-bottom: 32px;
            }

            .logo-icon {
                width: 72px;
                height: 72px;
            }

            .logo-icon i {
                font-size: 34px;
            }

            .brand-name {
                font-size: 28px;
                letter-spacing: 2px;
            }

            .brand-tagline {
                font-size: 13px;
            }
        }

        /* Large Mobile (481px - 599px) */
        @media (min-width: 481px) and (max-width: 599px) {
            body {
                padding: 16px;
            }

            .login-container {
                max-width: 100%;
                padding: 16px;
            }

            .login-card {
                padding: 32px 24px;
                border-radius: 22px;
            }

            .logo-section {
                margin-bottom: 28px;
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
                letter-spacing: 2px;
            }

            .brand-tagline {
                font-size: 13px;
            }

            .form-group label {
                font-size: 11px;
                letter-spacing: 1px;
            }

            .form-control {
                padding: 16px 16px 16px 48px;
                font-size: 14px;
            }

            .input-wrapper>i {
                font-size: 16px;
                left: 16px;
            }

            .password-toggle {
                right: 12px;
                font-size: 14px;
                width: 32px;
                height: 32px;
            }

            .btn-login {
                padding: 16px;
                font-size: 14px;
                letter-spacing: 1.5px;
            }

            .info-box {
                padding: 14px;
                margin-bottom: 24px;
            }

            .info-box-title {
                font-size: 12px;
            }

            .info-box ul {
                font-size: 12px;
                padding-left: 24px;
            }
        }

        /* Mobile (320px - 480px) - Android & iOS phones */
        @media (max-width: 480px) {
            html {
                font-size: 14px;
            }

            body {
                padding: 12px;
                min-height: 100vh;
                min-height: -webkit-fill-available;
                overflow-x: hidden;
            }

            .shape {
                filter: blur(60px);
            }

            .shape-1 {
                width: 250px;
                height: 250px;
                top: -80px;
                left: -80px;
            }

            .shape-2 {
                width: 200px;
                height: 200px;
                bottom: -60px;
                right: -60px;
            }

            .shape-3 {
                width: 150px;
                height: 150px;
            }

            .login-container {
                max-width: 100%;
                padding: 10px;
                width: 100%;
            }

            .login-card {
                padding: 28px 20px;
                border-radius: 20px;
                width: 100%;
            }

            .logo-section {
                margin-bottom: 24px;
            }

            .logo-icon {
                width: 60px;
                height: 60px;
                border-radius: 16px;
            }

            .logo-icon i {
                font-size: 28px;
            }

            .brand-name {
                font-size: 24px;
                letter-spacing: 1.5px;
                margin-bottom: 4px;
            }

            .brand-tagline {
                font-size: 12px;
            }

            .info-box {
                padding: 12px;
                margin-bottom: 20px;
                border-radius: 12px;
            }

            .info-box-title {
                font-size: 11px;
                margin-bottom: 8px;
            }

            .info-box-title i {
                font-size: 14px;
                margin-right: 8px;
            }

            .info-box ul {
                font-size: 11px;
                padding-left: 20px;
            }

            .info-box li {
                margin-bottom: 3px;
            }

            .form-group {
                margin-bottom: 18px;
            }

            .form-group label {
                font-size: 10px;
                margin-bottom: 8px;
                letter-spacing: 1px;
            }

            .form-control {
                padding: 14px 14px 14px 44px;
                font-size: 14px;
                border-radius: 12px;
            }

            .input-wrapper>i {
                font-size: 15px;
                left: 14px;
            }

            .password-toggle {
                right: 10px;
                font-size: 14px;
                width: 30px;
                height: 30px;
            }

            .btn-login {
                padding: 14px;
                font-size: 13px;
                border-radius: 12px;
                letter-spacing: 1px;
            }

            .btn-login i {
                margin-right: 8px;
            }

            .alert {
                padding: 12px 14px;
                font-size: 12px;
                border-radius: 10px;
                margin-bottom: 18px;
            }

            .alert i {
                font-size: 16px;
                margin-right: 10px;
            }

            .footer {
                margin-top: 24px;
                padding-top: 18px;
            }

            .footer p {
                font-size: 10px;
            }
        }

        /* Extra Small Mobile (below 360px) */
        @media (max-width: 359px) {
            body {
                padding: 8px;
            }

            .login-container {
                padding: 6px;
            }

            .login-card {
                padding: 24px 16px;
            }

            .logo-icon {
                width: 50px;
                height: 50px;
            }

            .logo-icon i {
                font-size: 24px;
            }

            .brand-name {
                font-size: 20px;
            }

            .brand-tagline {
                font-size: 11px;
            }

            .form-control {
                padding: 12px 12px 12px 40px;
                font-size: 13px;
            }

            .input-wrapper>i {
                font-size: 14px;
                left: 12px;
            }

            .btn-login {
                padding: 12px;
                font-size: 12px;
            }

            .info-box-title {
                font-size: 10px;
            }

            .info-box ul {
                font-size: 10px;
            }
        }

        /* iOS Safe Area (for notched phones like iPhone X+) */
        @supports (padding: env(safe - area - inset - top)) {
            body {
                padding-top: env(safe-area-inset-top);
                padding-bottom: env(safe-area-inset-bottom);
                padding-left: env(safe-area-inset-left);
                padding-right: env(safe-area-inset-right);
            }
        }

        /* Landscape orientation for phones */
        @media (max-height: 500px) and (orientation: landscape) {
            body {
                min-height: 100vh;
                padding: 10px 20px;
                overflow-y: auto;
            }

            .login-container {
                max-width: 400px;
            }

            .login-card {
                padding: 20px 24px;
            }

            .logo-section {
                margin-bottom: 16px;
            }

            .logo-icon {
                width: 50px;
                height: 50px;
            }

            .logo-icon i {
                font-size: 24px;
            }

            .brand-name {
                font-size: 22px;
                margin-bottom: 2px;
            }

            .brand-tagline {
                font-size: 11px;
            }

            .info-box {
                padding: 10px;
                margin-bottom: 14px;
            }

            .form-group {
                margin-bottom: 12px;
            }

            .form-control {
                padding: 12px 12px 12px 42px;
            }

            .btn-login {
                padding: 12px;
                margin-top: 4px;
            }

            .footer {
                margin-top: 14px;
                padding-top: 12px;
            }

            .shape {
                display: none;
            }
        }

        /* Touch device optimizations */
        @media (hover: none) and (pointer: coarse) {
            .form-control {
                font-size: 16px;
                /* Prevents zoom on iOS */
            }

            .btn-login:hover {
                transform: none;
            }

            .btn-login:active {
                transform: scale(0.98);
            }

            .password-toggle {
                padding: 8px;
                margin: -8px;
            }
        }

        /* High DPI / Retina displays */
        @media (-webkit-min-device-pixel-ratio: 2),
        (min-resolution: 192dpi) {
            .login-card {
                border-width: 0.5px;
            }
        }

        /* Reduce motion for accessibility */
        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }

            .shape,
            .dot {
                display: none;
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
            to {
                transform: rotate(360deg);
            }
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
                    <li>Huruf besar/kecil <strong>ID Login</strong> tidak berpengaruh</li>
                    <li>Password <strong>sensitif</strong> (huruf besar/kecil berpengaruh)</li>
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
                        <input type="text" class="form-control" id="login_id" name="login_id"
                            placeholder="Masukkan ID Login..." value="{{ old('login_id') }}" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Masukkan password..." required>
                        <button type="button" class="password-toggle" onclick="togglePassword()"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);display:flex;align-items:center;justify-content:center;background:transparent;border:none;color:rgba(255,255,255,0.5);cursor:pointer;padding:8px;width:36px;height:36px;">
                            <i class="fas fa-eye" id="password-icon" style="font-size:16px;line-height:1;"></i>
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
        document.getElementById('loginForm').addEventListener('submit', function (e) {
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