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

        // Hanya jalankan jika User Login adalah AUDIT atau ADMIN
        @if(auth()->check() && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
            
            // --- FUNGSI SIMPAN TOKEN KE SERVER ---
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

            // --- PERBAIKAN: MANUAL REGISTER SERVICE WORKER ---
            // Ini memaksa browser mengaktifkan file background agar siap terima pesan
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/firebase-messaging-sw.js')
                .then(function(registration) {
                    console.log('Service Worker Registered with scope:', registration.scope);
                    
                    // Setelah SW aktif, minta izin notifikasi
                    Notification.requestPermission().then((permission) => {
                        if (permission === 'granted') {
                            console.log('Izin notifikasi diberikan.');
                            
                            // Ambil Token menggunakan SW registration yang valid
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
                        } else {
                            console.log('Izin notifikasi DITOLAK user.');
                        }
                    });

                }).catch(function(err) {
                    console.log('Service Worker registration failed:', err);
                });
            }

            // --- HANDLER SAAT TAB DIBUKA (FOREGROUND) - VERSI AGRESIF ---
            messaging.onMessage((payload) => {
                console.log('DATA DITERIMA (Foreground): ', payload);
                
                // Ambil data langsung dari 'data' payload (Standar V1)
                // Fallback ke 'notification' jika kosong
                var data = payload.data || payload.notification || {};

                var title = data.title || "Notifikasi Baru";
                var body = data.body || "Cek dashboard untuk detail.";
                // Gunakan icon online google biar aman dari error 404 lokal
                var icon = 'https://www.gstatic.com/mobilesdk/160503_mobilesdk/logo/2x/firebase_28dp.png'; 
                var url = data.url || '/';

                // Tampilkan Notifikasi Native
                if (Notification.permission === 'granted') {
                    var notif = new Notification(title, {
                        body: body,
                        icon: icon,
                        tag: 'audit-alert-' + Date.now(), // Tag unik
                        requireInteraction: true // Notif tidak hilang sendiri
                    });

                    notif.onclick = function() {
                        window.focus();
                        if(url) window.location.href = url;
                        this.close();
                    };
                } else {
                    console.log("Notifikasi masuk tapi izin belum granted.");
                }
            });
        @endif
    </script>

    {{-- ======================================================= --}}
    {{-- TOMBOL DEBUG MANUAL (HAPUS JIKA SUDAH BERHASIL) --}}
    {{-- ======================================================= --}}
    @if(auth()->check() && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
        <button onclick="paksaNotif()" class="btn btn-danger btn-sm" style="position:fixed; bottom:20px; right:20px; z-index:99999; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
            🔔 TES NOTIFIKASI
        </button>

        <script>
        function paksaNotif() {
            if (!("Notification" in window)) {
                alert("Browser ini TIDAK support notifikasi desktop.");
                return;
            }

            if (Notification.permission === "granted") {
                new Notification("Tes Manual Berhasil!", {
                    body: "Jika Anda melihat ini, berarti Browser & Windows SEHAT. Tunggu data dari server.",
                    icon: "https://www.gstatic.com/mobilesdk/160503_mobilesdk/logo/2x/firebase_28dp.png"
                });
            } else if (Notification.permission !== "denied") {
                Notification.requestPermission().then(function (permission) {
                    if (permission === "granted") {
                        new Notification("Izin Diterima!", { body: "Sekarang coba tes lagi." });
                    }
                });
            } else {
                alert("Izin Ditolak! Cek pengaturan situs di browser (icon gembok).");
            }
        }
        </script>
    @endif

    {{-- Stack Scripts untuk halaman spesifik --}}
    @stack('scripts')
</body>

</html>