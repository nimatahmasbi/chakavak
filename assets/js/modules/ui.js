import { state } from './state.js';
import { loadSecuritySettings } from './security.js';
import { updateUiStatus } from './notify.js';

export function openModal(id) {
    let el = document.getElementById(id);
    if (el) {
        el.classList.remove('hidden');
        if (id == 'settingsModal') {
            switchSettingsTab('general');
            setTimeout(() => { if(window.checkNotifPermission) window.checkNotifPermission(); }, 200);
        }
    }
}

export function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

export function switchSettingsTab(tab) {
    document.querySelectorAll('[id^="set-tab-"]').forEach(el => el.classList.add('hidden'));
    document.getElementById('set-tab-' + tab).classList.remove('hidden');
    
    document.querySelectorAll('[id^="tab-btn-"]').forEach(btn => {
        btn.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600', 'bg-blue-50');
        btn.classList.add('text-gray-500');
    });
    
    document.getElementById('tab-btn-' + tab).classList.remove('text-gray-500');
    document.getElementById('tab-btn-' + tab).classList.add('text-blue-600', 'border-b-2', 'border-blue-600', 'bg-blue-50');

    if(tab === 'security') loadSecuritySettings();
}

// --- اصلاح تم و زبان ---
export function toggleTheme() {
    // Tailwind برای دارک مود به کلاس روی تگ html نگاه می‌کند
    const html = document.documentElement;
    const isDark = html.classList.contains('dark');
    const sw = document.getElementById('themeSwitch');

    if (isDark) {
        html.classList.remove('dark');
        localStorage.setItem('theme', 'light');
        document.cookie = "theme=light; path=/; max-age=31536000"; // برای PHP
        if(sw) sw.checked = false;
    } else {
        html.classList.add('dark');
        localStorage.setItem('theme', 'dark');
        document.cookie = "theme=dark; path=/; max-age=31536000"; // برای PHP
        if(sw) sw.checked = true;
    }
}

export function toggleLang() {
    let cur = localStorage.getItem('lang') || 'fa';
    let next = cur === 'fa' ? 'en' : 'fa';
    localStorage.setItem('lang', next);
    // ریلود صفحه برای اعمال تغییرات
    location.reload();
}

export async function appUpdate() {
    if(!confirm('آیا برنامه بروزرسانی شود؟')) return;
    try {
        if ('serviceWorker' in navigator) {
            const regs = await navigator.serviceWorker.getRegistrations();
            for (let reg of regs) await reg.unregister();
        }
        if ('caches' in window) {
            const keys = await caches.keys();
            await Promise.all(keys.map(k => caches.delete(k)));
        }
        localStorage.removeItem('chatListCache');
        location.reload(true);
    } catch (e) {
        location.reload();
    }
}

// اتصال به Window
window.openModal = openModal;
window.closeModal = closeModal;
window.switchSettingsTab = switchSettingsTab;
window.toggleTheme = toggleTheme;
window.toggleLang = toggleLang;
window.appUpdate = appUpdate;