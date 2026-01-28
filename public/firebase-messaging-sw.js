importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');

// --- KONFIGURASI FIREBASE (Wajib Diisi sama dengan di .env) ---
firebase.initializeApp({
    apiKey: "ISI_API_KEY_DISINI",
    authDomain: "ISI_AUTH_DOMAIN_DISINI",
    projectId: "ISI_PROJECT_ID_DISINI",
    storageBucket: "ISI_STORAGE_BUCKET_DISINI",
    messagingSenderId: "ISI_SENDER_ID_DISINI",
    appId: "ISI_APP_ID_DISINI"
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(function (payload) {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);

    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/assets/images/logo-mini.svg', // Ganti dengan path icon app jika ada
        sound: 'default'
    };

    self.registration.showNotification(notificationTitle,
        notificationOptions);
});
