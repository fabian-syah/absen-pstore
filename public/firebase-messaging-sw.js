importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');

// Salin Config Manual (JANGAN PAKAI BLADE {{ config(...) }} DISINI)
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

messaging.onBackgroundMessage((payload) => {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  
  const notificationTitle = payload.notification.title;
  const notificationOptions = {
    body: payload.notification.body,
    icon: '/assets/images/favicon.png' 
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});