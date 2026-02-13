import { apiCall } from './api.js';

// کلید عمومی شما
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

export async function requestPermission() {
    if (!("Notification" in window)) return;
    const permission = await Notification.requestPermission();
    if (permission === 'granted') {
        subscribeUserToPush();
        updateUiStatus('granted');
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
        await apiCall('save_push_sub', {
            endpoint: subscription.endpoint,
            keys: JSON.stringify(subscription.toJSON().keys)
        });
    } catch (err) {
        console.log('Push Error (Ignored):', err);
    }
}

export function updateUiStatus(status) {
    const el = document.getElementById('notifStatus');
    if (el) el.innerText = (status === 'granted' ? 'فعال' : 'غیرفعال');
}

window.requestNotifyPermission = requestPermission;
window.checkNotifPermission = () => updateUiStatus(Notification.permission);