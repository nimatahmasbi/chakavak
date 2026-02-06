const CACHE_NAME = 'chakavak-v5'; // ورژن جدید

const ASSETS = [
  'dashboard.php',
  'assets/css/style.css',
  'assets/js/main.js',
  'assets/json/manifest.json',
  'libs/tailwind.js',
  'libs/vazir/font.css', // فایل فونت جدید
  'libs/crypto-js.js',
  'assets/img/chakavak.png'
  // نکته: default.png اگر ندارید اینجا ننویسید چون باعث خطای کل برنامه می‌شود
];

self.addEventListener('install', (e) => {
  self.skipWaiting();
  e.waitUntil(caches.open(CACHE_NAME).then((cache) => cache.addAll(ASSETS)));
});

self.addEventListener('activate', (e) => {
  e.waitUntil(caches.keys().then((keys) => Promise.all(keys.map((k) => k !== CACHE_NAME && caches.delete(k)))));
});

self.addEventListener('fetch', (e) => {
  if (e.request.method === 'GET') {
    e.respondWith(caches.match(e.request).then((r) => r || fetch(e.request)));
  }
});

// VAPID Push (اگر بعدا فعال کردید)
self.addEventListener('push', (e) => {
  if (!(self.Notification && self.Notification.permission === 'granted')) return;
  const data = e.data ? e.data.json() : { title: 'Chakavak', body: 'New Message' };
  e.waitUntil(self.registration.showNotification(data.title, {
    body: data.body,
    icon: 'assets/img/chakavak.png',
    data: { url: 'dashboard.php' }
  }));
});