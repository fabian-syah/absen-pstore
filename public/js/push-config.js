/**
 * Push Notification Registration Logic (VAPID)
 */
const VAPID_PUBLIC_KEY = "GANTI_DENGAN_PUBLIC_KEY_DARI_VPS";

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

function initPush() {
    if (!('serviceWorker' in navigator)) return;
    if (!('PushManager' in window)) return;

    navigator.serviceWorker.register('/sw.js')
        .then(function (registration) {
            console.log('Service Worker Registered');
            return registration.pushManager.getSubscription()
                .then(async function (subscription) {
                    if (subscription) {
                        return subscription;
                    }

                    const convertedVapidKey = urlBase64ToUint8Array(VAPID_PUBLIC_KEY);

                    return registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: convertedVapidKey
                    });
                });
        })
        .then(function (subscription) {
            // Kirim ke Backend VPS
            fetch('/api/push-subscription', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + (window.api_token || '') // Sesuaikan jika pakai Sanctum
                },
                body: json.stringify(subscription)
            })
            .then(res => res.json())
            .then(data => console.log('Push Subscription Saved:', data))
            .catch(err => console.error('Failed to save subscription:', err));
        })
        .catch(function (err) {
            console.error('Service Worker Registration Failed:', err);
        });
}

// Jalankan saat page load
window.addEventListener('load', initPush);
