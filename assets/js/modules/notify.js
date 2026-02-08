import { apiCall } from './api.js';

// کلید عمومی شما (VAPID)
const VAPID_PUBLIC_KEY = 'BCtFbCSSgF4eBRZnihPPB7mqjvYGwqsVbWPCl4i3raFHZF7aPXID_10bQEgYPynu9YJFewpjtGwf7W37S5Ei2-8';

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

export function checkPermission() {
    if (!("Notification" in window)) return 'unsupported';
    return Notification.permission;
}

export async function requestPermission() {
    if (!("Notification" in window)) return alert("دستگاه پشتیبانی نمی‌کند");

    const permission = await Notification.requestPermission();
    if (permission === 'granted') {
        subscribeUserToPush();
        updateUiStatus('granted');
        new Notification("چکاوک", { body: "اعلان‌ها فعال شدند!", icon: "assets/img/chakavak.png" });
    } else {
        updateUiStatus('denied');
    }
}

async function subscribeUserToPush() {
    if (!('serviceWorker' in navigator)) return;

    try {
        const registration = await navigator.serviceWorker.ready;
        let subscription = await registration.pushManager.getSubscription();
        
        if (!subscription) {
            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY)
            });
        }

        // ارسال به سرور
        await apiCall('save_push_sub', {
            endpoint: subscription.endpoint,
            keys: JSON.stringify(subscription.toJSON().keys)
        });

    } catch (err) {
        console.error('Push Error:', err);
    }
}

export function updateUiStatus(status = null) {
    const el = document.getElementById('notifStatus');
    if (!el) return;
    const current = status || checkPermission();
    
    if (current === 'granted') { el.innerText = 'فعال'; el.className = 'text-xs text-green-500 font-bold'; }
    else if (current === 'denied') { el.innerText = 'مسدود'; el.className = 'text-xs text-red-500'; }
    else { el.innerText = 'غیرفعال'; el.className = 'text-xs text-gray-400'; }
}

window.requestNotifyPermission = requestPermission;
window.checkNotifPermission = updateUiStatus;