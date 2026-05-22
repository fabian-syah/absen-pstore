importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');

// --- KONFIGURASI FIREBASE ---
firebase.initializeApp({
    apiKey: "AIzaSyA27iUWIsqv_6A4kzGq12qt0eEicfkgOmI",
    authDomain: "bote-1a4b9.firebaseapp.com",
    projectId: "bote-1a4b9",
    storageBucket: "bote-1a4b9.firebasestorage.app",
    messagingSenderId: "898324022444",
    appId: "1:898324022444:web:e930d0fa96262ddf6c4b09"
});

const messaging = firebase.messaging();

// Handle background FCM messages
messaging.onBackgroundMessage(function (payload) {
    console.log('[firebase-messaging-sw.js] Background message received:', payload);

    // Ambil title dan body dari notification ATAU data field
    const title = (payload.notification && payload.notification.title) 
        || (payload.data && payload.data.title) 
        || "Notifikasi";
    const body = (payload.notification && payload.notification.body) 
        || (payload.data && payload.data.body) 
        || "";
    const icon = (payload.notification && payload.notification.icon)
        || (payload.data && payload.data.icon)
        || '/assets/images/logo-mini.svg';
    const url = (payload.data && payload.data.url) 
        || (payload.fcmOptions && payload.fcmOptions.link)
        || '/';

    const notificationOptions = {
        body: body,
        icon: icon,
        badge: '/favicon.ico',
        vibrate: [300, 100, 300, 100, 300],
        tag: 'push-' + Date.now(),
        renotify: true,
        requireInteraction: true,
        data: { url: url }
    };

    return self.registration.showNotification(title, notificationOptions);
});
