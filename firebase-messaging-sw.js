importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');

const firebaseConfig = {
    apiKey: "AIzaSyA27iUWIsqv_6A4kzGq12qt0eEicfkgOmI",
    authDomain: "bote-1a4b9.firebaseapp.com",
    projectId: "bote-1a4b9",
    storageBucket: "bote-1a4b9.firebasestorage.app",
    messagingSenderId: "898324022444",
    appId: "1:898324022444:web:e930d0fa96262ddf6c4b09"
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

// Handler Background: Kosongkan saja handler onBackgroundMessage
// Biarkan browser menghandle tampilan notifikasi secara otomatis dari payload 'notification' PHP.

// Handler Klik Notifikasi (Penting agar membuka halaman)
self.addEventListener('notificationclick', function(event) {
    console.log('[SW] Notifikasi diklik.');
    event.notification.close();

    // Ambil URL dari payload data atau fallback ke home
    const urlToOpen = event.notification.data?.click_action || event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({type: 'window', includeUncontrolled: true}).then(function(clientList) {
            // Jika tab sudah ada, fokuskan
            for (var i = 0; i < clientList.length; i++) {
                var client = clientList[i];
                if (client.url.includes(urlToOpen) && 'focus' in client) {
                    return client.focus();
                }
            }
            // Jika belum ada, buka baru
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});