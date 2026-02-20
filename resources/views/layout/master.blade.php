<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title') | PStore System</title>

    {{-- Meta Token & Manifest --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.json">

    {{-- CSS Assets --}}
    <link rel="stylesheet" href="{{ asset('assets/vendors/feather/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/typicons/typicons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/simple-line-icons/css/simple-line-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/js/select.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />

    @stack('styles')

    <style>
        /* ==========================================================
            MODERN DESIGN SYSTEM & UI ENHANCEMENTS
           ========================================================== */

        /* CSS Variables - Design Tokens */
        :root {
            /* ==========================================================
               RAMADAN THEME - SPECIAL EDITION
               ========================================================== */

            /* Primary Colors - Emerald Green */
            --pstore-primary: #00693E;
            /* Deep Emerald */
            --pstore-primary-dark: #004d2e;
            --pstore-primary-darker: #00331f;
            --pstore-primary-light: rgba(0, 105, 62, 0.1);

            /* Accents - Gold */
            --pstore-accent: #D4AF37;
            /* Metallic Gold */
            --pstore-accent-light: #F4C430;

            /* Gradients */
            --gradient-primary: linear-gradient(135deg, #00693E 0%, #004d2e 100%);
            /* Green Depth */
            --gradient-gold: linear-gradient(135deg, #FFD700 0%, #D4AF37 100%);
            /* Gold Shine */
            --gradient-soft: linear-gradient(135deg, rgba(0, 105, 62, 0.08) 0%, rgba(212, 175, 55, 0.1) 100%);
            --gradient-bg: linear-gradient(180deg, #FAFAF5 0%, #EFF2E6 100%);
            /* Warm Islamic Cream */

            /* Neutrals */
            --pstore-dark: #1A2E22;
            /* Dark Green-Black */
            --pstore-gray-900: #1b2620;
            /* Soft Black */
            --pstore-gray-700: #495057;
            --pstore-gray-500: #6c757d;
            --pstore-gray-300: #dee2e6;
            --pstore-gray-100: #f8f9fa;

            /* Spacing Scale */
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;
            --spacing-2xl: 3rem;

            /* Border Radius */
            --radius-sm: 6px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-full: 9999px;

            /* Shadows */
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
            --shadow-xl: 0 12px 40px rgba(0, 0, 0, 0.15);
            --shadow-primary: 0 4px 16px rgba(0, 105, 62, 0.25);

            /* Layout */
            --sidebar-width: 245px;
            --header-height: 70px;

            /* Transitions */
            --transition-fast: 0.15s ease;
            --transition-base: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Global Styles */
        * {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body {
            background: var(--gradient-bg);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: var(--pstore-gray-900);
            overflow-x: hidden;
        }

        /* Layout Structure */
        @media (min-width: 992px) {

            .container-scroller,
            .page-body-wrapper {
                overflow: visible !important;
            }

            .sidebar {
                position: fixed !important;
                top: var(--header-height);
                height: calc(100vh - var(--header-height));
                overflow-y: auto;
                z-index: 1000;
                width: var(--sidebar-width);
                transition: width var(--transition-base);
            }

            .main-panel {
                margin-left: var(--sidebar-width);
                width: calc(100% - var(--sidebar-width));
                min-height: calc(100vh - var(--header-height));
                display: flex;
                flex-direction: column;
                transition: margin-left var(--transition-base), width var(--transition-base);
            }

            body.sidebar-icon-only .sidebar {
                width: 70px;
            }

            body.sidebar-icon-only .main-panel {
                margin-left: 70px;
                width: calc(100% - 70px);
            }
        }

        /* Content Wrapper */
        .content-wrapper {
            padding: var(--spacing-lg) var(--spacing-xl) !important;
            background: transparent;
            flex-grow: 1;
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Modern Card Styling */
        .card {
            border: none !important;
            background: #ffffff;
            box-shadow: var(--shadow-md) !important;
            border-radius: var(--radius-md) !important;
            transition: all var(--transition-base);
            overflow: hidden;
            position: relative;
        }

        .card:hover {
            box-shadow: var(--shadow-lg) !important;
            transform: translateY(-2px);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-primary);
            opacity: 0;
            transition: opacity var(--transition-base);
        }

        .card:hover::before {
            opacity: 1;
        }

        .card-header {
            background: #ffffff !important;
            border-bottom: 1px solid var(--pstore-gray-300) !important;
            padding: var(--spacing-lg) var(--spacing-xl) !important;
        }

        .card-body {
            padding: var(--spacing-xl) !important;
        }

        .card-title {
            font-weight: 700 !important;
            color: var(--pstore-gray-900) !important;
            margin-bottom: var(--spacing-md) !important;
            font-size: 1.25rem !important;
        }

        /* Custom Scrollbar - Global */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.03);
            border-radius: var(--radius-full);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--gradient-primary);
            border-radius: var(--radius-full);
            transition: background var(--transition-base);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--pstore-primary-dark), var(--pstore-primary-darker));
        }

        /* Enhanced Buttons */
        .btn-primary {
            background: var(--gradient-primary) !important;
            border: none !important;
            box-shadow: var(--shadow-primary) !important;
            font-weight: 600 !important;
            transition: all var(--transition-base) !important;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13, 110, 253, 0.3) !important;
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Form Inputs */
        .form-control:focus {
            border-color: var(--pstore-primary) !important;
            box-shadow: 0 0 0 0.2rem var(--pstore-primary-light) !important;
        }

        /* Tables */
        .table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .table thead th {
            background: var(--gradient-soft) !important;
            color: var(--pstore-gray-900) !important;
            font-weight: 600 !important;
            border: none !important;
            padding: var(--spacing-md) var(--spacing-lg) !important;
            font-size: 0.875rem !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table tbody tr {
            transition: all var(--transition-fast);
        }

        .table tbody tr:hover {
            background: var(--pstore-gray-100);
            transform: scale(1.01);
        }

        /* Badges */
        .badge {
            padding: 0.35rem 0.75rem !important;
            border-radius: var(--radius-sm) !important;
            font-weight: 600 !important;
            font-size: 0.75rem !important;
        }

        .badge-primary {
            background: var(--gradient-primary) !important;
        }

        /* Alerts */
        .alert {
            border: none !important;
            border-radius: var(--radius-md) !important;
            box-shadow: var(--shadow-sm) !important;
        }

        /* Page Title Animation */
        .page-header {
            animation: slideInDown 0.5s ease-out;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Loading States */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s ease-in-out infinite;
            border-radius: var(--radius-sm);
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        /* Mobile Responsiveness */
        @media (max-width: 991px) {
            .content-wrapper {
                padding: var(--spacing-md) !important;
                padding-bottom: 90px !important;
            }

            .card-body {
                padding: var(--spacing-lg) !important;
            }
        }

        /* ============================================ */
        /* MOBILE BOTTOM NAVIGATION BAR                  */
        /* ============================================ */
        .mobile-bottom-nav {
            display: none;
        }

        @media (max-width: 991px) {
            .mobile-bottom-nav {
                display: flex;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                z-index: 1050;
                background: #ffffff;
                border-top: 1px solid rgba(0, 105, 62, 0.15);
                box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
                padding: 6px 0 max(6px, env(safe-area-inset-bottom));
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
            }

            .mobile-bottom-nav .nav-item {
                flex: 1;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 4px 2px;
                text-decoration: none;
                color: #8a9bad;
                font-size: 10px;
                font-weight: 500;
                line-height: 1.2;
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                position: relative;
                gap: 3px;
                -webkit-tap-highlight-color: transparent;
            }

            .mobile-bottom-nav .nav-item i {
                font-size: 22px;
                transition: all 0.2s ease;
                display: block;
            }

            .mobile-bottom-nav .nav-item span {
                transition: all 0.2s ease;
            }

            .mobile-bottom-nav .nav-item:hover {
                color: var(--pstore-primary);
            }

            .mobile-bottom-nav .nav-item:hover i {
                transform: translateY(-2px);
            }

            .mobile-bottom-nav .nav-item.active {
                color: var(--pstore-primary);
            }

            .mobile-bottom-nav .nav-item.active i {
                transform: translateY(-2px);
                filter: drop-shadow(0 2px 4px rgba(0, 105, 62, 0.3));
            }

            .mobile-bottom-nav .nav-item.active span {
                font-weight: 700;
            }

            /* Active indicator dot */
            .mobile-bottom-nav .nav-item.active::before {
                content: '';
                position: absolute;
                top: 0;
                left: 50%;
                transform: translateX(-50%);
                width: 32px;
                height: 3px;
                background: linear-gradient(90deg, var(--pstore-primary), var(--pstore-accent));
                border-radius: 0 0 4px 4px;
            }

            /* Absen button - special center button */
            .mobile-bottom-nav .nav-item.nav-absen {
                position: relative;
            }

            .mobile-bottom-nav .nav-item.nav-absen .absen-bubble {
                width: 50px;
                height: 50px;
                background: linear-gradient(135deg, var(--pstore-primary) 0%, var(--pstore-primary-dark) 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 14px rgba(0, 105, 62, 0.45);
                margin-bottom: 2px;
                transition: all 0.2s ease;
                margin-top: -12px;
                border: 3px solid #fff;
            }

            .mobile-bottom-nav .nav-item.nav-absen .absen-bubble i {
                font-size: 22px;
                color: #fff;
                transform: none !important;
            }

            .mobile-bottom-nav .nav-item.nav-absen:hover .absen-bubble,
            .mobile-bottom-nav .nav-item.nav-absen.active .absen-bubble {
                transform: scale(1.08);
                box-shadow: 0 6px 18px rgba(0, 105, 62, 0.55);
            }

            .mobile-bottom-nav .nav-item.nav-absen span {
                color: #8a9bad;
                font-size: 10px;
            }

            .mobile-bottom-nav .nav-item.nav-absen.active span {
                color: var(--pstore-primary);
                font-weight: 700;
            }

            /* Ramadhan gold color */
            .mobile-bottom-nav .nav-item.nav-ramadhan.active {
                color: var(--pstore-accent);
            }

            .mobile-bottom-nav .nav-item.nav-ramadhan.active::before {
                background: linear-gradient(90deg, var(--pstore-accent), #D4AF37);
            }
        }

        /* Utility Classes */
        .gradient-text {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .hover-lift {
            transition: transform var(--transition-base);
        }

        .hover-lift:hover {
            transform: translateY(-4px);
        }
    </style>
</head>

<body class="with-welcome-text">
    <div class="container-scroller">

        {{-- Include Header --}}
        @include('layout.header')

        <div class="container-fluid page-body-wrapper">

            {{-- Include Sidebar --}}
            @include('layout.sidebar')

            <div class="main-panel">
                <div class="content-wrapper">
                    {{-- Konten Utama Halaman --}}
                    @yield('content')
                </div>

                {{-- Include Footer --}}
                @include('layout.footer')
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MOBILE BOTTOM NAVIGATION BAR --}}
    {{-- ============================================ --}}
    @php
        $currentUrl = request()->url();
        $isHome = request()->is('/') || request()->routeIs('dashboard');
        $isAbsen = request()->routeIs('attendance.*');
        $isIzin = request()->routeIs('leave-requests.*');
        $isRamadhan = false; // Tidak ada halaman khusus, bisa disesuaikan
        $isProfile = request()->routeIs('profile.*');
    @endphp
    <nav class="mobile-bottom-nav">
        {{-- Home --}}
        <a href="{{ url('/') }}" class="nav-item {{ $isHome ? 'active' : '' }}">
            <i class="mdi mdi-home{{ $isHome ? '' : '-outline' }}"></i>
            <span>Home</span>
        </a>

        {{-- Absen (Center Floating Button) --}}
        <a href="{{ route('attendance.history') }}" class="nav-item nav-absen {{ $isAbsen ? 'active' : '' }}">
            <div class="absen-bubble">
                <i class="mdi mdi-fingerprint"></i>
            </div>
            <span>Absen</span>
        </a>

        {{-- Izin --}}
        <a href="{{ route('leave-requests.personal-history') }}" class="nav-item {{ $isIzin ? 'active' : '' }}">
            <i class="mdi mdi-calendar-{{ $isIzin ? 'check' : 'clock-outline' }}"></i>
            <span>Izin</span>
        </a>

        {{-- Ramadhan --}}
        <a href="{{ url('/') }}#ramadhan" class="nav-item nav-ramadhan {{ $isRamadhan ? 'active' : '' }}">
            <i class="mdi mdi-star-crescent"></i>
            <span>Ramadhan</span>
        </a>

        {{-- Profile --}}
        <a href="{{ route('profile.edit') }}" class="nav-item {{ $isProfile ? 'active' : '' }}">
            <i class="mdi mdi-account{{ $isProfile ? '' : '-outline' }}"></i>
            <span>Profile</span>
        </a>
    </nav>

    {{-- JS Assets --}}
    <script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/chart.js/chart.umd.js') }}"></script>
    <script src="{{ asset('assets/vendors/progressbar.js/progressbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('assets/js/template.js') }}"></script>
    <script src="{{ asset('assets/js/settings.js') }}"></script>
    <script src="{{ asset('assets/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('assets/js/todolist.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.cookie.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>

    {{-- Firebase Notification Logic --}}
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js"></script>

    <script>
        var firebaseConfig = {
            apiKey: "{{ config('services.firebase.api_key') }}",
            authDomain: "{{ config('services.firebase.auth_domain') }}",
            projectId: "{{ config('services.firebase.project_id') }}",
            storageBucket: "{{ config('services.firebase.storage_bucket') }}",
            messagingSenderId: "{{ config('services.firebase.messaging_sender_id') }}",
            appId: "{{ config('services.firebase.app_id') }}"
        };
        console.log("🔥 Firebase Config:", firebaseConfig); // DEBUG LOG

        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }

        const messaging = firebase.messaging();

        @if(auth()->check() && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
            function sendTokenToServer(token) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch("{{ route('update.fcm.token') }}", {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ token: token })
                }).then(response => response.json())
                    .then(data => console.log("FCM Token status:", data.message))
                    .catch(err => console.log("Gagal menyimpan token.", err));
            }

            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/firebase-messaging-sw.js')
                    .then(function (registration) {
                        Notification.requestPermission().then((permission) => {
                            if (permission === 'granted') {
                                messaging.getToken({
                                    vapidKey: "{{ config('services.firebase.vapid_key') }}",
                                    serviceWorkerRegistration: registration
                                })
                                    .then((currentToken) => {
                                        if (currentToken) sendTokenToServer(currentToken);
                                    });
                            }
                        });
                    });
            }

            messaging.onMessage((payload) => {
                console.log("🔥 Foreground Message Received:", payload); // DEBUG LOG
                const title = payload.notification ? payload.notification.title : "Notifikasi Baru";
                const body = payload.notification ? payload.notification.body : "Cek dashboard.";
                const url = payload.data ? payload.data.click_action : '/';

                if (Notification.permission === 'granted') {
                    var notif = new Notification(title, {
                        body: body,
                        icon: 'https://www.gstatic.com/mobilesdk/160503_mobilesdk/logo/2x/firebase_28dp.png',
                        tag: 'audit-alert-' + Date.now()
                    });
                    notif.onclick = function () {
                        window.focus();
                        window.location.href = url;
                        this.close();
                    };
                }
            });
        @endif
    </script>

    @stack('scripts')
</body>

</html>