<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title') </title>

    {{-- PENTING: Meta Token untuk keamanan Request AJAX ke Server --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

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

    <link rel="manifest" href="/manifest.json">

    @stack('styles')
</head>

<body class="with-welcome-text">
    <div class="container-scroller">
        @include('layout.header')
        <div class="container-fluid page-body-wrapper">
            @include('layout.sidebar')
            <div class="main-panel">
                <div class="content-wrapper">
                    @yield('content')
                </div>
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
        // 2. Config
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

        @if (auth()->check() && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))

            // --- FUNGSI UPDATE TOKEN ---
            function sendTokenToServer(token) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch("{{ route('update.fcm.token') }}", {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        token: token
                    })
                }).then(res => console.log("Token updated")).catch(err => console.log("Token error", err));
            }

            // --- PERBAIKAN UTAMA: MANUAL REGISTER SERVICE WORKER ---
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/firebase-messaging-sw.js')
                    .then(function(registration) {
                        console.log('Service Worker berhasil didaftarkan dengan scope:', registration.scope);

                        // Setelah SW aktif, baru minta izin notifikasi
                        Notification.requestPermission().then((permission) => {
                            if (permission === 'granted') {
                                console.log('Izin notifikasi diberikan.');

                                // PENTING: Gunakan registration yang baru didaftarkan
                                messaging.getToken({
                                        vapidKey: "{{ config('services.firebase.vapid_key') }}",
                                        serviceWorkerRegistration: registration
                                    })
                                    .then((currentToken) => {
                                        if (currentToken) {
                                            console.log('Token didapat:', currentToken);
                                            sendTokenToServer(currentToken);
                                        } else {
                                            console.log('Tidak ada token tersedia.');
                                        }
                                    }).catch((err) => {
                                        console.log('Error mengambil token:', err);
                                    });
                            }
                        });

                    }).catch(function(err) {
                        console.log('Gagal mendaftarkan Service Worker:', err);
                    });
            }

            // --- HANDLER FOREGROUND AGRESIF ---
            messaging.onMessage((payload) => {
                console.log('DATA DITERIMA:', payload);

                // Ambil data langsung dari 'data' (karena kita pakai Data Message dari server)
                // Jika payload.data kosong, coba payload.notification (jaga-jaga)
                var data = payload.data || payload.notification;

                var title = data.title || "Info Absensi";
                var body = data.body || "Ada pembaruan data.";
                var icon = 'https://www.gstatic.com/mobilesdk/160503_mobilesdk/logo/2x/firebase_28dp.png';

                // 1. Tampilkan Alert Browser (Pasti muncul kalau script jalan)
                // alert("NOTIF MASUK: " + title); 

                // 2. Tampilkan Notifikasi Native
                if (Notification.permission === 'granted') {
                    new Notification(title, {
                        body: body,
                        icon: icon,
                        tag: 'audit-notif-' + Math.random()
                    });
                }
            });
        @endif
    </script>

    @stack('scripts')
</body>

</html>
