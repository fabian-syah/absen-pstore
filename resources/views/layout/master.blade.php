<!DOCTYPE html>
<html lang="en" data-theme="default">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title') </title>

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

    {{-- ========================================================= --}}
    {{-- 🎨 THEME ENGINE CSS (GLOBAL VARIABLES) --}}
    {{-- ========================================================= --}}
    <style>
        :root {
            /* --- 1. DEFAULT THEME (Light/Purple) --- */
            --bg-body: #f4f5f7;
            --bg-navbar: #ffffff;
            --bg-sidebar: #ffffff;
            --bg-footer: #f4f5f7;
            --bg-card: #ffffff;
            --text-main: #343a40;
            --text-muted: #6c757d;
            --border-color: #e9ecef;
            --active-item-bg: #f0f4ff;
            --active-item-text: #1F3BB3;
            --logo-bg: #ffffff;
        }

        /* --- 2. OCEAN BLUE THEME --- */
        [data-theme="ocean"] {
            --bg-body: #e6f7ff;
            --bg-navbar: #ffffff;
            --bg-sidebar: #f0fbff; /* Sidebar agak biru muda */
            --bg-footer: #e6f7ff;
            --bg-card: #ffffff;
            --text-main: #004085;
            --text-muted: #6c757d;
            --border-color: #b8daff;
            --active-item-bg: #cce5ff;
            --active-item-text: #0056b3;
            --logo-bg: #ffffff;
        }

        /* --- 3. MIDNIGHT (DARK MODE) --- */
        [data-theme="midnight"] {
            --bg-body: #151521;
            --bg-navbar: #1e1e2d;
            --bg-sidebar: #1e1e2d;
            --bg-footer: #151521;
            --bg-card: #2b2b40;
            --text-main: #e4e6ef; /* Text Putih */
            --text-muted: #b5b5c3;
            --border-color: #323248;
            --active-item-bg: #323248;
            --active-item-text: #ffffff;
            --logo-bg: #1e1e2d;
        }

        /* --- 4. SUNSET ORANGE --- */
        [data-theme="sunset"] {
            --bg-body: #fff5f5;
            --bg-navbar: #ffffff;
            --bg-sidebar: #fff0f0;
            --bg-footer: #fff5f5;
            --bg-card: #ffffff;
            --text-main: #4a1c1c;
            --text-muted: #6c757d;
            --border-color: #ffdce0;
            --active-item-bg: #ffe4e4;
            --active-item-text: #d63384;
            --logo-bg: #ffffff;
        }

        /* --- PENERAPAN VARIABEL KE ELEMENT TEMPLATE --- */
        
        /* Body & Layout */
        body, .container-scroller, .page-body-wrapper, .main-panel, .content-wrapper {
            background-color: var(--bg-body) !important;
            color: var(--text-main) !important;
            transition: all 0.3s ease;
        }

        /* Navbar (Header) */
        .navbar .navbar-brand-wrapper {
            background-color: var(--logo-bg) !important;
            transition: all 0.3s ease;
        }
        .navbar .navbar-menu-wrapper {
            background-color: var(--bg-navbar) !important;
            color: var(--text-main) !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .navbar .navbar-menu-wrapper .navbar-nav .nav-item .nav-link {
            color: var(--text-main) !important;
        }

        /* Sidebar */
        .sidebar {
            background-color: var(--bg-sidebar) !important;
            transition: all 0.3s ease;
        }
        .sidebar .nav .nav-item .nav-link {
            background: transparent;
            color: var(--text-main);
        }
        .sidebar .nav .nav-item .nav-link .menu-title {
            color: inherit;
        }
        .sidebar .nav .nav-item .nav-link i.menu-icon {
            color: var(--text-muted);
        }
        /* Sidebar Active State */
        .sidebar .nav .nav-item.active > .nav-link,
        .sidebar .nav .nav-item:hover > .nav-link {
            background-color: var(--active-item-bg) !important;
        }
        .sidebar .nav .nav-item.active > .nav-link .menu-title,
        .sidebar .nav .nav-item.active > .nav-link i {
            color: var(--active-item-text) !important;
        }
        
        /* Footer */
        .footer {
            background-color: var(--bg-footer) !important;
            color: var(--text-muted) !important;
            transition: all 0.3s ease;
        }

        /* Cards & Containers */
        .card {
            background-color: var(--bg-card) !important;
            color: var(--text-main) !important;
            border: 1px solid var(--border-color);
        }
        .card-title, h1, h2, h3, h4, h5, h6, .welcome-text, .welcome-sub-text {
            color: var(--text-main) !important;
        }
        .text-muted {
            color: var(--text-muted) !important;
        }

        /* Dropdown Theme Item Style */
        .theme-item { cursor: pointer; padding: 10px 20px; display: flex; align-items: center; font-weight: 500; transition: all 0.2s; }
        .theme-item:hover { background-color: rgba(0,0,0,0.05); transform: translateX(5px); }
        .theme-color-preview { width: 20px; height: 20px; border-radius: 50%; margin-right: 12px; border: 2px solid rgba(0,0,0,0.1); }
        .theme-icon-spin:hover { animation: spin 4s linear infinite; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        
        /* Dark Mode Specific Fixes */
        [data-theme="midnight"] .table { color: #e4e6ef; }
        [data-theme="midnight"] .form-control { background-color: #151521; border-color: #323248; color: #fff; }
        [data-theme="midnight"] .dropdown-menu { background-color: #2b2b40; border-color: #323248; }
        [data-theme="midnight"] .dropdown-item { color: #e4e6ef; }
        [data-theme="midnight"] .dropdown-item:hover { background-color: #323248; }
    </style>
    @stack('styles')
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

    {{-- Firebase Logic (Tetap dipertahankan) --}}
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js"></script>
    <script>
        // Konfigurasi Firebase (Sama seperti kodemu sebelumnya)
        var firebaseConfig = {
            apiKey: "{{ config('services.firebase.api_key') }}",
            authDomain: "{{ config('services.firebase.auth_domain') }}",
            projectId: "{{ config('services.firebase.project_id') }}",
            storageBucket: "{{ config('services.firebase.storage_bucket') }}",
            messagingSenderId: "{{ config('services.firebase.messaging_sender_id') }}",
            appId: "{{ config('services.firebase.app_id') }}"
        };
        if (!firebase.apps.length) { firebase.initializeApp(firebaseConfig); }
        const messaging = firebase.messaging();

        @if(auth()->check() && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
            // ... (Logic Firebase FCM Token Update & Handler Sama Persis) ...
            function sendTokenToServer(token) {
                // ... logic kirim token ...
                 const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch("{{ route('update.fcm.token') }}", { 
                    method: "POST", headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ token: token })
                }).then(r => r.json()).then(d => console.log("FCM:", d.message)).catch(e => console.log("Err:", e));
            }
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/firebase-messaging-sw.js').then(function(reg) {
                    Notification.requestPermission().then((perm) => {
                        if (perm === 'granted') {
                            messaging.getToken({ vapidKey: "{{ config('services.firebase.vapid_key') }}", serviceWorkerRegistration: reg })
                            .then((t) => { if(t) sendTokenToServer(t); });
                        }
                    });
                });
            }
            messaging.onMessage((payload) => {
                const title = payload.notification ? payload.notification.title : "Notifikasi";
                const body = payload.notification ? payload.notification.body : "Cek dashboard.";
                if (Notification.permission === 'granted') {
                    new Notification(title, { body: body });
                }
            });
        @endif
    </script>

    {{-- ========================================================= --}}
    {{-- 🎨 THEME PICKER LOGIC (JS) --}}
    {{-- ========================================================= --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Load Tema Tersimpan
            const savedTheme = localStorage.getItem('pstore_theme') || 'default';
            document.documentElement.setAttribute('data-theme', savedTheme);
        });

        // 2. Fungsi Ganti Tema
        function changeTheme(themeName) {
            document.documentElement.setAttribute('data-theme', themeName);
            localStorage.setItem('pstore_theme', themeName);
        }

        // 3. Fullscreen Logic
        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                if (document.exitFullscreen) { document.exitFullscreen(); }
            }
        }
    </script>

    @stack('scripts')
</body>
</html>