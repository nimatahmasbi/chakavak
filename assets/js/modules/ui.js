import { state } from './state.js';

// --- مودال‌ها ---
export function openModal(id) {
    let el = document.getElementById(id);
    if (el) {
        el.classList.remove('hidden');
        // اگر مودال مخاطبین است، لیست را لود کن (با استفاده از window)
        if (id == 'contactModal' && window.loadContacts) window.loadContacts();
    }
}

export function closeModal(id) {
    let el = document.getElementById(id);
    if (el) el.classList.add('hidden');
}

export function openCreateModal(type) {
    state.createType = type;
    let t = document.getElementById('createTitle');
    if(t) t.innerText = (type == 'channel' ? 'ساخت کانال' : 'ساخت گروه');
    openModal('createModal');
}

export function openSettings() {
    openModal('settingsModal');
}

// --- تنظیمات ---
export function toggleTheme() {
    document.body.classList.toggle('light-mode');
    localStorage.setItem('theme', document.body.classList.contains('light-mode') ? 'light' : 'dark');
}

export function toggleLang() {
    let cur = localStorage.getItem('lang') || 'fa';
    localStorage.setItem('lang', cur === 'fa' ? 'en' : 'fa');
    location.reload();
}

export function toggleEditMode() {
    alert('ویرایش به زودی...');
}

// اتصال به Window
window.openModal = openModal;
window.closeModal = closeModal;
window.openCreateModal = openCreateModal;
window.openSettings = openSettings;
window.toggleTheme = toggleTheme;
window.toggleLang = toggleLang;
window.toggleEditMode = toggleEditMode;