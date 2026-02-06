import { state } from './state.js';
import { loadContacts } from './auth.js'; 

export function openModal(id) {
    let el = document.getElementById(id);
    if (el) {
        el.classList.remove('hidden');
        if (id == 'contactModal') loadContacts();
    }
}

export function closeModal(id) {
    let el = document.getElementById(id);
    if (el) el.classList.add('hidden');
}

export function openCreateModal(type) {
    state.createType = type;
    let title = document.getElementById('createTitle');
    if(title) title.innerText = (type == 'channel' ? 'کانال جدید' : 'گروه جدید');
    openModal('createModal');
}

export function openSettings() {
    openModal('settingsModal');
}

export function toggleTheme() {
    document.body.classList.toggle('light-mode');
    localStorage.setItem('theme', document.body.classList.contains('light-mode') ? 'light' : 'dark');
}

export function toggleLang() {
    let cur = localStorage.getItem('lang') || 'fa';
    localStorage.setItem('lang', cur === 'fa' ? 'en' : 'fa');
    location.reload();
}

// اتصال حیاتی به Window
window.openModal = openModal;
window.closeModal = closeModal;
window.openCreateModal = openCreateModal;
window.openSettings = openSettings;
window.toggleTheme = toggleTheme;
window.toggleLang = toggleLang;