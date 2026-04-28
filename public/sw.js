self.addEventListener('push', function (event) {
    console.log('[Service Worker] Push Received.');
    let data = {};
    
    if (event.data) {
        const rawText = event.data.text();
        try {
            // Coba parse manual dari teks
            data = JSON.parse(rawText);
            console.log('[Service Worker] Push Data (JSON):', data);
        } catch (e) {
            // Jika gagal, anggap itu teks biasa
            data = { title: "Notifikasi Absensi", body: rawText };
            console.log('[Service Worker] Push Data (Text):', rawText);
        }
    }

    const title = data.title || "Notifikasi Absensi";
    const options = {
        body: data.body || "Cek aplikasi untuk informasi terbaru.",
        icon: data.icon || "/favicon.ico",
        badge: "/favicon.ico",
        vibrate: [300, 100, 300, 100, 300], // Getaran untuk High Priority
        tag: 'audit-verification-' + (data.id || Date.now()), // Unik agar tidak digabung browser
        renotify: true, // Bunyikan suara lagi
        requireInteraction: true, // Notif tetap di layar sampai diklik/ditutup
        data: {
            url: data.url || "/"
        }
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    const urlToOpen = event.notification.data.url;

    event.waitUntil(
        clients.matchAll({
            type: 'window',
            includeUncontrolled: true
        }).then(function (clientList) {
            for (let i = 0; i < clientList.length; i++) {
                let client = clientList[i];
                if (client.url === urlToOpen && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});
