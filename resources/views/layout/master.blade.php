<!DOCTYPE html>
<html lang="en" data-theme="default">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title') </title>

    {{-- PENTING: Meta Token untuk keamanan Request AJAX ke Server --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- PENTING: Manifest untuk identitas Push Notification --}}
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
    {{-- 🎨 THEME ENGINE CSS (Dynamic Variables) --}}
    {{-- ========================================================= --}}
    <style>
        /* 1. DEFINISI VARIASI TEMA */
        :root {
            /* DEFAULT (PStore Purple/Blue) */
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --sidebar-bg: #ffffff;
            --navbar-bg: #ffffff;
            --body-bg: #f4f5f7;
            --text-color: #343a40;
            --card-bg: #ffffff;
            --active-item: #f0f4ff;
        }

        [data-theme="ocean"] {
            /* OCEAN BLUE */
            --primary-gradient: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%);
            --sidebar-bg: #f0fbff;
            --navbar-bg: #ffffff;
            --body-bg: #e6f7ff;
            --text-color: #2c3e50;
            --active-item: #d6f0ff;
        }

        [data-theme="nature"] {
            /* NATURE GREEN */
            --primary-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --sidebar-bg: #f1fdf5;
            --navbar-bg: #ffffff;
            --body-bg: #e8fcf1;
            --text-color: #1b4d3e;
            --active-item: #d1fae5;
        }

        [data-theme="midnight"] {
            /* DARK MODE (Midnight) */
            --primary-gradient: linear-gradient(135deg, #8E2DE2 0%, #4A00E0 100%);
            --sidebar-bg: #1e1e2d;
            --navbar-bg: #2b2b40;
            --body-bg: #151521;
            --text-color: #e0e0e0; /* Teks jadi terang */
            --card-bg: #2b2b40;
            --active-item: #323248;
        }

        [data-theme="sunset"] {
            /* SUNSET ORANGE */
            --primary-gradient: linear-gradient(135deg, #FF512F 0%, #DD2476 100%);
            --sidebar-bg: #fff5f5;
            --navbar-bg: #ffffff;
            --body-bg: #fff0f0;
            --text-color: #4a1c1c;
            --active-item: #ffe4e4;
        }

        /* 2. PENERAPAN GLOBAL VARIABLE KE ELEMENT */
        body, .main-panel, .content-wrapper {
            background-color: var(--body-bg) !important;
            color: var(--text-color) !important;
            transition: background-color 0.5s ease, color 0.5s ease; /* Smooth Transition */
        }

        /* Sidebar Color */
        .sidebar {
            background-color: var(--sidebar-bg) !important;
            transition: background-color 0.5s ease;
        }
        
        .sidebar .nav .nav-item.active > .nav-link, 
        .sidebar .nav .nav-item:hover > .nav-link {
            background-color: var(--active-item) !important;
        }

        /* Navbar Color */
        .navbar .navbar-menu-wrapper {
            background-color: var(--navbar-bg) !important;
            transition: background-color 0.5s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        /* Card Color (Khusus Dark Mode Support) */
        .card {
            background-color: var(--card-bg) !important;
            color: var(--text-color);
            transition: background-color 0.5s ease;
        }
        
        /* Heading & Teks di Dark Mode */
        [data-theme="midnight"] .text-dark,
        [data-theme="midnight"] .card-title,
        [data-theme="midnight"] h3, 
        [data-theme="midnight"] h4,
        [data-theme="midnight"] h5,
        [data-theme="midnight"] .navbar-brand-wrapper {
            color: #ffffff !important;
        }

        [data-theme="midnight"] .text-muted {
            color: #a0a0a0 !important;
        }

        /* Button Gradient Override */
        .btn-primary, .badge-primary {
            background: var(--primary-gradient) !important;
            border: none !important;
        }

        /* Theme Picker Icon Anim */
        .theme-icon-spin:hover {
            animation: spin 4s linear infinite;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        
        /* Dropdown Theme Item */
        .theme-item {
            cursor: pointer;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            font-weight: 500;
            transition: all 0.2s;
        }
        .theme-item:hover {
            background-color: rgba(0,0,0,0.05);
            transform: translateX(5px);
        }
        .theme-color-preview {
            width: 20px; 
            height: 20px; 
            border-radius: 50%; 
            margin-right: 12px;
            border: 2px solid rgba(0,0,0,0.1);
        }
    </style>
    @stack('styles')
</head>

<body class="with-welcome-text">
    <div class="container-scroller">
        
        {{-- ========================================================= --}}
        {{-- HEADER / NAVBAR (DENGAN THEME PICKER) --}}
        {{-- ========================================================= --}}
        <nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row w-100">
            <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
                <div class="me-3">
                    <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
                        <span class="icon-menu"></span>
                    </button>
                </div>
                <div>
                    <a class="navbar-brand brand-logo" href="{{ route('dashboard') }}">
                        <img src="{{ asset('assets/images/logo-pstore.png') }}" alt="logo"
                            style="width: 150px; height: auto;" />
                    </a>
                    <a class="navbar-brand brand-logo-mini" href="{{ route('dashboard') }}">
                        <img src="{{ asset('assets/images/logo-pstore.png') }}" alt="logo"
                            style="width: 45px; height: auto;" />
                    </a>
                </div>
            </div>
            <div class="navbar-menu-wrapper d-flex align-items-top">
                <ul class="navbar-nav">
                    <li class="nav-item fw-semibold d-none d-lg-block ms-0">
                        <h1 class="welcome-text">@yield('heading')</h1>
                        <h3 class="welcome-sub-text">{{ Auth::user()->role }} - {{ Auth::user()->division->name ?? 'N/A' }}</h3>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    {{-- 1. Fullscreen Button --}}
                    <li class="nav-item d-none d-lg-block">
                        <a class="nav-link" href="javascript:void(0)" onclick="toggleFullScreen()">
                            <i class="mdi mdi-fullscreen"></i>
                        </a>
                    </li>

                    {{-- 2. THEME PICKER DROPDOWN (FITUR BARU) --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link" id="themeDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false" title="Ganti Tema">
                            <i class="mdi mdi-palette-swatch theme-icon-spin text-primary" style="font-size: 22px;"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0" aria-labelledby="themeDropdown">
                            <div class="dropdown-header text-center">
                                <p class="mb-0 font-weight-medium">Pilih Tema</p>
                            </div>
                            <div class="dropdown-divider"></div>
                            
                            {{-- Theme Options --}}
                            <a class="dropdown-item theme-item" onclick="changeTheme('default')">
                                <div class="theme-color-preview" style="background: linear-gradient(135deg, #667eea, #764ba2);"></div>
                                PStore Default
                            </a>
                            <a class="dropdown-item theme-item" onclick="changeTheme('ocean')">
                                <div class="theme-color-preview" style="background: linear-gradient(135deg, #00c6ff, #0072ff);"></div>
                                Ocean Blue
                            </a>
                            <a class="dropdown-item theme-item" onclick="changeTheme('nature')">
                                <div class="theme-color-preview" style="background: linear-gradient(135deg, #11998e, #38ef7d);"></div>
                                Nature Green
                            </a>
                            <a class="dropdown-item theme-item" onclick="changeTheme('sunset')">
                                <div class="theme-color-preview" style="background: linear-gradient(135deg, #FF512F, #DD2476);"></div>
                                Sunset Orange
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item theme-item" onclick="changeTheme('midnight')">
                                <div class="theme-color-preview" style="background: #2b2b40; border: 1px solid #fff;"></div>
                                Midnight Dark
                            </a>
                        </div>
                    </li>

                    {{-- 3. Search (Admin/Audit) --}}
                    @if (in_array(auth()->user()->role, ['admin', 'audit']))
                        <li class="nav-item">
                            <div class="search-form position-relative">
                                <i class="icon-search position-absolute search-icon"></i>
                                <input type="search" class="form-control search-input" id="globalSearch"
                                    data-url="{{ route('search') }}" placeholder="Search users..."
                                    autocomplete="off">
                                <div class="search-results dropdown-menu" id="searchResults"></div>
                            </div>
                        </li>
                    @endif

                    {{-- 4. Broadcast Notifications --}}
                    <li class="nav-item dropdown notification-dropdown">
                        <a class="nav-link position-relative d-flex align-items-center justify-content-center" 
                           id="broadcastDropdown" 
                           href="#" 
                           data-bs-toggle="dropdown">
                            <i class="icon-bell notification-icon"></i>
                            <span class="notification-badge" id="broadcastCount" style="display: none;">0</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0"
                            aria-labelledby="broadcastDropdown" style="min-width: 380px; max-width: 400px;">
                            <div class="dropdown-header px-4 py-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0 fw-semibold">Broadcast Notifications</h6>
                                        <small class="text-muted" id="broadcastTotal">0 unread</small>
                                    </div>
                                    <i class="mdi mdi-bullhorn text-primary" style="font-size: 24px;"></i>
                                </div>
                            </div>
                            <div id="broadcastList" style="max-height: 400px; overflow-y: auto;">
                                <div class="dropdown-item text-center py-5">
                                    <div class="spinner-border text-primary mb-2" role="status" style="width: 2rem; height: 2rem;">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="text-muted mb-0">Loading broadcasts...</p>
                                </div>
                            </div>
                            <div class="dropdown-divider m-0"></div>
                            <a href="javascript:void(0)" class="dropdown-item text-center py-3 text-primary fw-medium" id="viewAllBroadcasts">
                                <i class="mdi mdi-bullhorn-outline me-1"></i>View All Broadcasts
                            </a>
                        </div>
                    </li>

                    {{-- 5. User Profile --}}
                    <li class="nav-item dropdown user-dropdown">
                        <a class="nav-link p-0" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="position-relative d-inline-block">
                                @if (Auth::user()->profile_photo_path)
                                    <img class="img-xs rounded-circle" 
                                         src="{{ Storage::url(Auth::user()->profile_photo_path) }}"
                                         alt="Profile image"
                                         style="object-fit: cover; border: {{ Auth::user()->is_verified ? '2px solid #0d6efd' : 'none' }}; padding: 1px;">
                                @else
                                    <div class="profile-initial-nav" 
                                         style="border: {{ Auth::user()->is_verified ? '2px solid #0d6efd' : 'none' }};">
                                        {{ getInitials(Auth::user()->name) }}
                                    </div>
                                @endif

                                @if(Auth::user()->is_verified)
                                    <span class="position-absolute bg-white rounded-circle d-flex align-items-center justify-content-center"
                                          style="bottom: -2px; right: -2px; width: 14px; height: 14px; border: 1px solid white;">
                                        <i class="mdi mdi-check-decagram text-primary" style="font-size: 10px;"></i>
                                    </span>
                                @endif
                            </div>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                            <div class="dropdown-header text-center">
                                <div class="position-relative d-inline-block mb-2">
                                    @if (Auth::user()->profile_photo_path)
                                        <img class="img-md rounded-circle"
                                            src="{{ Storage::url(Auth::user()->profile_photo_path) }}" alt="Profile image"
                                            style="width: 60px; height: 60px; object-fit: cover; border: {{ Auth::user()->is_verified ? '3px solid #0d6efd' : '3px solid white' }};">
                                    @else
                                        <div class="profile-initial-dropdown"
                                             style="border: {{ Auth::user()->is_verified ? '3px solid #0d6efd' : '3px solid white' }};">
                                            {{ getInitials(Auth::user()->name) }}
                                        </div>
                                    @endif

                                    @if(Auth::user()->is_verified)
                                        <span class="position-absolute bg-white rounded-circle d-flex align-items-center justify-content-center"
                                              style="bottom: 0; right: 0; width: 20px; height: 20px; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                            <i class="mdi mdi-check-decagram text-primary" style="font-size: 14px;"></i>
                                        </span>
                                    @endif
                                </div>

                                <p class="mb-1 mt-1 fw-semibold d-flex align-items-center justify-content-center gap-1">
                                    {{ Auth::user()->name }}
                                    @if(Auth::user()->is_verified)
                                        <i class="mdi mdi-check-decagram text-primary" title="Verified" style="font-size: 14px;"></i>
                                    @endif
                                </p>
                                <p class="fw-light text-muted mb-0">{{ Auth::user()->email }}</p>
                                <small class="text-muted">{{ Auth::user()->role }} -
                                    {{ Auth::user()->division->name ?? 'N/A' }}</small>
                            </div>

                            <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                <i class="dropdown-item-icon mdi mdi-account-outline text-primary me-2"></i> My Profile
                            </a>
                            <a class="dropdown-item">
                                <i class="dropdown-item-icon mdi mdi-message-text-outline text-primary me-2"></i> Messages
                            </a>
                            <a class="dropdown-item">
                                <i class="dropdown-item-icon mdi mdi-help-circle-outline text-primary me-2"></i> FAQ
                            </a>

                            <div class="dropdown-divider"></div>

                            <a href="{{ route('logout') }}" class="dropdown-item"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>Sign Out
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                </ul>
                <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
                    data-bs-toggle="offcanvas">
                    <span class="mdi mdi-menu"></span>
                </button>
            </div>
        </nav>

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

    {{-- ================================================================= --}}
    {{-- FIREBASE NOTIFICATION LOGIC (KHUSUS AUDIT & ADMIN) --}}
    {{-- ================================================================= --}}
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js"></script>

    <script>
        // 2. Konfigurasi Firebase dari Config Laravel
        var firebaseConfig = {
            apiKey: "{{ config('services.firebase.api_key') }}",
            authDomain: "{{ config('services.firebase.auth_domain') }}",
            projectId: "{{ config('services.firebase.project_id') }}",
            storageBucket: "{{ config('services.firebase.storage_bucket') }}",
            messagingSenderId: "{{ config('services.firebase.messaging_sender_id') }}",
            appId: "{{ config('services.firebase.app_id') }}"
        };

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
                }).then(response => {
                    return response.json();
                }).then(data => {
                    console.log("FCM Token status:", data.message);
                }).catch(err => {
                    console.log("Gagal menyimpan token ke server.", err);
                });
            }

            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/firebase-messaging-sw.js')
                .then(function(registration) {
                    console.log('Service Worker Registered with scope:', registration.scope);
                    Notification.requestPermission().then((permission) => {
                        if (permission === 'granted') {
                            console.log('Izin notifikasi diberikan.');
                            messaging.getToken({ 
                                vapidKey: "{{ config('services.firebase.vapid_key') }}",
                                serviceWorkerRegistration: registration 
                            })
                            .then((currentToken) => {
                                if (currentToken) {
                                    sendTokenToServer(currentToken);
                                } else {
                                    console.log('Tidak ada token tersedia.');
                                }
                            }).catch((err) => {
                                console.log('Error mengambil token:', err);
                            });
                        } else {
                            console.log('Izin notifikasi DITOLAK user.');
                        }
                    });

                }).catch(function(err) {
                    console.log('Service Worker registration failed:', err);
                });
            }

            messaging.onMessage((payload) => {
                console.log('Pesan masuk (Foreground): ', payload);
                const title = payload.notification ? payload.notification.title : "Notifikasi Baru";
                const body = payload.notification ? payload.notification.body : "Cek dashboard.";
                const icon = 'https://www.gstatic.com/mobilesdk/160503_mobilesdk/logo/2x/firebase_28dp.png';
                const url = payload.data ? payload.data.click_action : '/';

                if (Notification.permission === 'granted') {
                    var notif = new Notification(title, {
                        body: body,
                        icon: icon,
                        tag: 'audit-alert-' + Date.now()
                    });

                    notif.onclick = function() {
                        window.focus();
                        window.location.href = url;
                        this.close();
                    };
                }
            });
        @endif
    </script>

    {{-- ========================================================= --}}
    {{-- 🎨 THEME PICKER SCRIPT (LOGIC) --}}
    {{-- ========================================================= --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Cek local storage saat load
            const savedTheme = localStorage.getItem('pstore_theme') || 'default';
            document.documentElement.setAttribute('data-theme', savedTheme);
            
            // 2. Fungsi Search (Sama seperti sebelumnya)
            const searchInput = document.getElementById('globalSearch');
            const searchResults = document.getElementById('searchResults');
            let searchTimeout = null;

            if (searchInput && searchResults) {
                searchInput.addEventListener('input', function() {
                    const query = this.value;
                    const url = this.getAttribute('data-url');
                    clearTimeout(searchTimeout);

                    if (query.length < 2) {
                        searchResults.classList.remove('show');
                        searchResults.innerHTML = '';
                        return;
                    }

                    searchTimeout = setTimeout(() => {
                        fetch(`${url}?q=${encodeURIComponent(query)}`)
                            .then(response => {
                                if (!response.ok) throw new Error('Network error');
                                return response.json();
                            })
                            .then(data => {
                                renderSearchResults(data.results);
                            })
                            .catch(error => {
                                console.error('Search error:', error);
                                searchResults.innerHTML = '<div class="dropdown-item text-danger">Error loading results</div>';
                                searchResults.classList.add('show');
                            });
                    }, 500);
                });

                searchInput.addEventListener('focus', function() {
                    if (this.value.length >= 2 && searchResults.innerHTML !== '') {
                        searchResults.classList.add('show');
                    }
                });

                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                        searchResults.classList.remove('show');
                    }
                });
            }

            // Fungsi render search
            function renderSearchResults(results) {
                if (!results || results.length === 0) {
                    searchResults.innerHTML = '<div class="dropdown-item text-muted py-3 text-center">No results found</div>';
                } else {
                    let html = '';
                    results.forEach(item => {
                        html += `
                            <a href="${item.url}" class="dropdown-item py-2 border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="mdi ${item.icon} text-primary" style="font-size: 20px;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-dark" style="font-size: 14px; font-weight: 600;">${escapeHtml(item.title)}</h6>
                                        <small class="text-muted" style="font-size: 12px; white-space: normal;">${escapeHtml(item.description)}</small>
                                    </div>
                                </div>
                            </a>
                        `;
                    });
                    searchResults.innerHTML = html;
                }
                searchResults.classList.add('show');
            }

            // Helper escape html
            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // ... (Kode Broadcast Notification dll tetap sama) ...
            const broadcastDropdown = document.getElementById('broadcastDropdown');
            const broadcastList = document.getElementById('broadcastList');
            const broadcastCount = document.getElementById('broadcastCount');
            const broadcastTotal = document.getElementById('broadcastTotal');
            const viewAllBroadcasts = document.getElementById('viewAllBroadcasts');

            function loadBroadcastNotifications() {
                fetch('{{ route('broadcast.notifications') }}', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        updateBroadcastUI(data);
                    })
                    .catch(error => {
                        console.error('Error loading broadcasts:', error);
                    });
            }

            function updateBroadcastUI(data) {
                const broadcasts = data.broadcasts || [];
                const unreadCount = data.unread_count || 0;

                if (unreadCount > 0) {
                    broadcastCount.textContent = unreadCount > 99 ? '99+' : unreadCount;
                    broadcastCount.style.display = 'flex';
                    broadcastTotal.textContent = unreadCount + ' unread';
                } else {
                    broadcastCount.style.display = 'none';
                    broadcastTotal.textContent = 'No unread';
                }

                if (broadcasts.length === 0) {
                    broadcastList.innerHTML = `
                    <div class="empty-state text-center py-5">
                        <div class="empty-icon mb-3">
                            <i class="mdi mdi-bullhorn-outline"></i>
                        </div>
                        <h6 class="text-muted mb-1">No Broadcasts</h6>
                        <p class="text-muted small mb-0">You're all caught up!</p>
                    </div>
                `;
                } else {
                    const baseUrl = "{{ route('broadcast.show', ':id') }}";
                    const broadcastItems = broadcasts.map(broadcast => {
                        const detailUrl = baseUrl.replace(':id', broadcast.id);
                        return `
                        <a class="dropdown-item broadcast-item py-3 ${broadcast.is_read ? '' : 'unread'}" href="${detailUrl}">
                            <div class="d-flex align-items-start">
                                <div class="broadcast-icon me-3 ${broadcast.priority_color}">
                                    <i class="${broadcast.priority_icon}"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <h6 class="broadcast-title mb-0 fw-semibold">${escapeHtml(broadcast.title)}</h6>
                                    <p class="broadcast-message text-muted mb-0 small text-truncate">${escapeHtml(broadcast.message)}</p>
                                </div>
                            </div>
                        </a>
                        `;
                    }).join('');
                    broadcastList.innerHTML = broadcastItems;
                }
            }
            
            // Init load broadcast
            loadBroadcastNotifications();
            // Refresh broadcast interval
            setInterval(loadBroadcastNotifications, 60000);
            if (broadcastDropdown) {
                broadcastDropdown.addEventListener('click', function() {
                    loadBroadcastNotifications();
                });
            }
        });

        // 3. Fungsi Global Change Theme
        function changeTheme(themeName) {
            document.documentElement.setAttribute('data-theme', themeName);
            localStorage.setItem('pstore_theme', themeName);
        }

        // Fullscreen Toggle
        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }
    </script>

    @stack('scripts')
</body>

</html>