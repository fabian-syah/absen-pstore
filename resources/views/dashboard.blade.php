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
    
    {{-- 1. Load Library Firebase dari Google CDN --}}
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js"></script>

    <script>
        // 2. Konfigurasi Firebase (Mengambil dari Laravel Config services.php)
        // Pastikan Anda sudah mengatur .env dan config/services.php
        var firebaseConfig = {
            apiKey: "{{ config('services.firebase.api_key') }}",
            authDomain: "{{ config('services.firebase.auth_domain') }}",
            projectId: "{{ config('services.firebase.project_id') }}",
            storageBucket: "{{ config('services.firebase.storage_bucket') }}",
            messagingSenderId: "{{ config('services.firebase.messaging_sender_id') }}",
            appId: "{{ config('services.firebase.app_id') }}"
        };

        // 3. Initialize Firebase jika belum ada instance
        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }
        
        const messaging = firebase.messaging();

        // 4. Logic Khusus: Hanya jalankan jika User Login adalah AUDIT atau ADMIN
        @if(auth()->check() && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
            
            // Fungsi: Kirim Token FCM ke Database Laravel via AJAX
            function sendTokenToServer(token) {
                // Ambil CSRF token dari meta tag di atas
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

            // A. Meminta Izin Notifikasi Browser
            Notification.requestPermission().then((permission) => {
                if (permission === 'granted') {
                    console.log('Izin notifikasi diberikan.');
                    
                    // B. Ambil Token FCM Device ini
                    messaging.getToken({ vapidKey: "{{ config('services.firebase.vapid_key') }}" })
                    .then((currentToken) => {
                        if (currentToken) {
                            // C. Simpan token ke database user ini
                            sendTokenToServer(currentToken);
                        } else {
                            console.log('Tidak ada registration token yang tersedia.');
                        }
                    }).catch((err) => {
                        console.log('Terjadi error saat mengambil token.', err);
                    });
                } else {
                    console.log('Izin notifikasi ditolak.');
                }
            });

            // D. Listener Pesan saat Aplikasi Dibuka (Foreground)
            messaging.onMessage((payload) => {
                console.log('Notifikasi masuk (Foreground): ', payload);
                
                const notificationTitle = payload.notification.title;
                const notificationOptions = {
                    body: payload.notification.body,
                    icon: '/assets/images/favicon.png' // Ganti dengan path logo Anda
                };

                // Tampilkan notifikasi browser manual
                if (Notification.permission === 'granted') {
                    new Notification(notificationTitle, notificationOptions);
                }
                
                // Opsional: Anda bisa menambahkan Alert/Toastify disini agar user sadar
                // alert("Pesan Baru: " + notificationTitle);
            });
        @endif
    </script>

    {{-- Stack Scripts untuk halaman spesifik --}}
    @stack('scripts')
</body>

</html>