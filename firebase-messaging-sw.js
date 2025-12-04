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
messaging.onBackgroundMessage(function(payload) {
  console.log('[SW] Pesan Masuk:', payload);

  // Cek apakah data ada di payload.data (Server Laravel pakai format 'data')
  // Atau di payload.notification (Format standar Firebase)
  const data = payload.data || payload.notification;

  if (!data) {
      console.error('[SW] Payload kosong!', payload);
      return;
  }

  const notificationTitle = data.title || "Info Absensi";
  const notificationOptions = {
    body: data.body || "Ada pembaruan data.",
    // Pakai icon Google dulu biar pasti muncul (hindari 404)
    icon: 'https://www.gstatic.com/mobilesdk/160503_mobilesdk/logo/2x/firebase_28dp.png',
    tag: 'audit-notif-' + Date.now(),
    renotify: true,
    data: {
        url: data.url || '/'
    }
  };

  return self.registration.showNotification(notificationTitle, notificationOptions);
});

// Event Klik Notifikasi
self.addEventListener('notificationclick', function(event) {
    console.log('[SW] Notifikasi diklik.');
    event.notification.close();

    event.waitUntil(
        clients.matchAll({type: 'window'}).then(function(clientList) {
            // Cek URL tujuan
            const urlToOpen = event.notification.data.url || '/';

            // Jika tab sudah ada, fokuskan
            for (var i = 0; i < clientList.length; i++) {
                var client = clientList[i];
                if (client.url === urlToOpen && 'focus' in client) {
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