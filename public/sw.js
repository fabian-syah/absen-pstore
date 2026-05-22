self.addEventListener('push', function (event) {
    console.log('[Service Worker] Push Received.');
    let data = {};
    
    if (event.data) {
        const rawText = event.data.text();
        try {
            // Coba parse manual dari teks
            const parsed = JSON.parse(rawText);
            console.log('[Service Worker] Push Data (JSON):', parsed);

            // FCM HTTP v1 mengirim data di dalam "notification" atau "data" field
            // Prioritaskan notification field, fallback ke data field, lalu root level
            if (parsed.notification) {
                data = {
                    title: parsed.notification.title || parsed.data?.title,
                    body: parsed.notification.body || parsed.data?.body,
                    icon: parsed.notification.icon || parsed.data?.icon,
                    url: parsed.data?.url || parsed.fcmOptions?.link || "/"
                };
            } else if (parsed.data) {
                data = parsed.data;
            } else {
                // Data langsung di root (format Web Push VAPID)
                data = parsed;
            }
        } catch (e) {
            // Jika gagal parse JSON, anggap itu teks biasa
            data = { title: "Notifikasi Absensi", body: rawText };
            console.log('[Service Worker] Push Data (Text):', rawText);
        }
    }

    const title = data.title || "Notifikasi Absensi";
    const options = {
        body: data.body || "Cek aplikasi untuk informasi terbaru.",
        icon: data.icon || "/favicon.ico",
        badge: "/favicon.ico",
        vibrate: [300, 100, 300, 100, 300],
        tag: 'push-notif-' + Date.now(),
        renotify: true,
        requireInteraction: true,
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
