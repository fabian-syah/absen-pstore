self.addEventListener('push', function (event) {
    console.log('[Service Worker] Push Received.');
    let data = {};
    
    if (event.data) {
        try {
            // Coba baca sebagai JSON
            data = event.data.json();
            console.log('[Service Worker] Push Data (JSON):', data);
        } catch (e) {
            // Jika bukan JSON (seperti tes manual tadi), baca sebagai teks
            const text = event.data.text();
            data = { title: "Notifikasi Absensi", body: text };
            console.log('[Service Worker] Push Data (Text):', text);
        }
    }

    const title = data.title || "Notifikasi Absensi";
    const options = {
        body: data.body || "Cek aplikasi untuk informasi terbaru.",
        icon: data.icon || "/favicon.ico",
        badge: "/favicon.ico",
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
