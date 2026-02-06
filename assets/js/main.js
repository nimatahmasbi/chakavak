import './modules/ui.js';
import './modules/auth.js';
import './modules/group.js';
import './modules/media.js'; // حتما باید باشد
import { loadChats, loadMsg, sendText } from './modules/chat.js';
import { state } from './modules/state.js';

document.addEventListener('DOMContentLoaded', () => {
    // PWA
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('sw.js').catch(console.error);
    }

    // تنظیمات
    if (typeof applyLang === "function") applyLang();
    
    if (localStorage.getItem('theme') !== 'light') { 
        document.body.classList.remove('light-mode'); 
        let sw = document.getElementById('themeSwitch'); 
        if (sw) sw.checked = true; 
    } else {
        document.body.classList.add('light-mode');
    }

    // بستن منوها
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#attachMenu') && !e.target.closest('button[onclick*="attachMenu"]')) {
            const m = document.getElementById('attachMenu'); if(m) m.style.display = 'none';
        }
        if (!e.target.closest('#addMenu') && !e.target.closest('button[onclick*="addMenu"]')) {
            const m = document.getElementById('addMenu'); if(m) m.classList.add('hidden');
        }
        let mm = document.getElementById('msgMenu'); 
        if (mm && mm.style.display == 'block' && !e.target.closest('.context-menu')) mm.style.display = 'none';
    });

    // مدیریت ورودی
    const input = document.getElementById('msgInput');
    if (input) {
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendText();
                setTimeout(() => { input.style.height = '48px'; }, 10);
            }
        });
        input.addEventListener('input', function() {
            this.style.height = 'auto'; 
            this.style.height = (this.scrollHeight) + 'px'; 
        });
    }

    // شروع
    loadChats();
    setInterval(() => { if (state.currChat) loadMsg(false); else loadChats(); }, 3000);
});