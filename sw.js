self.addEventListener('push', function(event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    const data = event.data ? event.data.json() : {};
    const title = data.title || 'پیام جدید';
    const message = data.body || 'شما یک پیام جدید دارید';
    const icon = 'assets/img/chakavak.png';

    event.waitUntil(
        self.registration.showNotification(title, {
            body: message,
            icon: icon,
            badge: icon,
            vibrate: [100, 50, 100],
            data: { url: data.url || '/' }
        })
    );
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    event.waitUntil(
        clients.openWindow(event.notification.data.url)
    );
});

// کش کردن فایل‌های اصلی برای آفلاین (اختیاری)
self.addEventListener('install', (e) => {
    e.waitUntil(
        caches.open('chakavak-store').then((cache) => cache.addAll([
            '/',
            '/index.php',
            '/dashboard.php',
            '/assets/css/style.css',
            '/assets/img/chakavak.png'
        ]))
    );
});