// Service Worker v2 - Force update
const SW_VERSION = 'v2';
console.log('[SW] Version:', SW_VERSION);

// Force activate immediately (skip waiting)
self.addEventListener('install', function(event) {
    console.log('[SW] Installing version:', SW_VERSION);
    self.skipWaiting();
});

self.addEventListener('activate', function(event) {
    console.log('[SW] Activating version:', SW_VERSION);
    event.waitUntil(clients.claim());
});

self.addEventListener('push', function (event) {
    console.log('[SW] Push Received.');
    let data = {};
    
    if (event.data) {
        const rawText = event.data.text();
        try {
            const parsed = JSON.parse(rawText);
            console.log('[SW] Push Data (JSON):', parsed);

            // Format dari Web Push VAPID (minishlink/web-push)
            if (parsed.title) {
                data = parsed;
            }
            // Format dari FCM (nested notification/data)
            else if (parsed.notification) {
                data = {
                    title: parsed.notification.title,
                    body: parsed.notification.body,
                    icon: parsed.notification.icon,
                    url: (parsed.data && parsed.data.url) || (parsed.fcmOptions && parsed.fcmOptions.link) || "/"
                };
            }
            // Format data-only FCM
            else if (parsed.data) {
                data = {
                    title: parsed.data.title,
                    body: parsed.data.body,
                    icon: parsed.data.icon,
                    url: parsed.data.url || "/"
                };
            }
        } catch (e) {
            data = { title: "Notifikasi", body: rawText };
            console.log('[SW] Push Data (Text):', rawText);
        }
    }

    const title = data.title || "Notifikasi";
    const options = {
        body: data.body || "",
        icon: data.icon || "/assets/images/logo-mini.svg",
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
