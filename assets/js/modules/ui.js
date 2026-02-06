import { state } from './state.js';
import { loadContacts } from './auth.js'; 

// --- مدیریت مودال‌ها ---
export function openModal(id) {
    let el = document.getElementById(id);
    if (el) {
        el.classList.remove('hidden');
        if (id == 'contactModal') {
            // اگر تابع loadContacts لود شده باشد صدا می‌زند
            if(typeof loadContacts === 'function') loadContacts();
        }
    }
}

export function closeModal(id) {
    let el = document.getElementById(id);
    if (el) el.classList.add('hidden');
}

export function openCreateModal(type) {
    state.createType = type;
    let title = document.getElementById('createTitle');
    if(title) title.innerText = (type == 'channel' ? 'New Channel' : 'New Group');
    openModal('createModal');
}

export function openSettings() {
    openModal('settingsModal');
}

// --- تنظیمات ظاهری ---
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
    alert('Edit mode coming soon');
}

// --- اتصال به Window (حیاتی برای دکمه‌های HTML) ---
window.openModal = openModal;
window.closeModal = closeModal;
window.openCreateModal = openCreateModal;
window.openSettings = openSettings;
window.toggleTheme = toggleTheme;
window.toggleLang = toggleLang;
window.toggleEditMode = toggleEditMode;