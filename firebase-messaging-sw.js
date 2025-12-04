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

// Handler Background (Saat Tab Ditutup/Minimize)
messaging.onBackgroundMessage((payload) => {
  console.log('[SW] Background Data Message Received:', payload);

  // KARENA KITA PAKAI 'data', KITA HARUS AMBIL DARI payload.data
  const notificationTitle = payload.data.title;
  const notificationOptions = {
    body: payload.data.body,
    icon: '/assets/images/favicon.png', // Pastikan gambar ini ada!
    tag: 'audit-notification', // Agar notif tidak menumpuk
    renotify: true, // Agar bunyi terus meski notif lama belum diclose
    data: {
        url: payload.data.url
    }
  };

  return self.registration.showNotification(notificationTitle, notificationOptions);
});

// Event Klik Notifikasi
self.addEventListener('notificationclick', function(event) {
    console.log('[SW] Notification click received.');
    event.notification.close();

    event.waitUntil(
        clients.matchAll({type: 'window'}).then(windowClients => {
            // Cek kalau tab sudah terbuka, fokuskan. Kalau belum, buka baru.
            for (var i = 0; i < windowClients.length; i++) {
                var client = windowClients[i];
                if (client.url === event.notification.data.url && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(event.notification.data.url);
            }
        })
    );
});