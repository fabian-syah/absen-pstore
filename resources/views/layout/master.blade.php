<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title') </title>

    {{-- PENTING: Meta Token untuk keamanan Request AJAX ke Server --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- PENTING: Manifest untuk identitas Push Notification di Chrome/Edge --}}
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

    {{-- ================================================================= --}}
    {{-- FIREBASE NOTIFICATION LOGIC (KHUSUS AUDIT & ADMIN) --}}
    {{-- ================================================================= --}}
    
   {{-- 1. Load Library Firebase --}}
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

        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }
        
        const messaging = firebase.messaging();

        @if(auth()->check() && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
            
            // FUNGSI: Kirim Token ke Database
            function sendTokenToServer(token) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                console.log("Mengirim token ke server..."); // Debugging

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
                    console.log("Server Response:", data.message);
                }).catch(err => {
                    console.log("Gagal update token:", err);
                });
            }

            // LOGIKA UTAMA: Jalankan setiap kali halaman dimuat
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/firebase-messaging-sw.js')
                .then(function(registration) {
                    
                    // Cek Izin
                    if (Notification.permission === 'granted') {
                        // Langsung ambil token yang sudah ada (Current Token)
                        messaging.getToken({ 
                            vapidKey: "{{ config('services.firebase.vapid_key') }}",
                            serviceWorkerRegistration: registration 
                        })
                        .then((currentToken) => {
                            if (currentToken) {
                                // PAKSA KIRIM KE SERVER SEKARANG
                                // (Walaupun browser sudah punya, server mungkin belum karena habis logout)
                                sendTokenToServer(currentToken);
                            } else {
                                console.log('Token belum tersedia, meminta izin...');
                                Notification.requestPermission();
                            }
                        }).catch((err) => {
                            console.log('Error ambil token:', err);
                        });
                    } else if (Notification.permission !== 'denied') {
                        // Jika belum ada izin, minta dulu
                        Notification.requestPermission().then((permission) => {
                            if (permission === 'granted') {
                                location.reload(); // Reload agar token terambil
                            }
                        });
                    }

                });
            }

            // HANDLER FOREGROUND (Tab Aktif)
            messaging.onMessage((payload) => {
                console.log('Pesan masuk:', payload);
                const data = payload.data || payload.notification || {};
                const title = data.title || "Notifikasi";
                const body = data.body || "Pesan baru masuk.";
                const icon = 'https://www.gstatic.com/mobilesdk/160503_mobilesdk/logo/2x/firebase_28dp.png';

                if (Notification.permission === 'granted') {
                    var notif = new Notification(title, {
                        body: body,
                        icon: icon,
                        tag: 'audit-notif-' + Date.now()
                    });
                    notif.onclick = function() {
                        window.focus();
                        window.location.href = "{{ route('audit.verify.list') }}";
                        this.close();
                    };
                }
            });
        @endif
    </script>
    @endif

    {{-- Stack Scripts untuk halaman spesifik --}}
    @stack('scripts')
</body>

</html>