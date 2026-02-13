import './modules/ui.js';
import './modules/auth.js';
import './modules/group.js';
import './modules/sender.js';
import './modules/media.js';
import './modules/security.js';
import './modules/notify.js';
import { loadChats, loadMsg, sendText } from './modules/chat.js';
import { state } from './modules/state.js';
import { openModal } from './modules/ui.js'; // ایمپورت openModal

document.addEventListener('DOMContentLoaded', () => {
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('sw.js').catch(console.error);
    }

    // بررسی اینکه آیا باید تنظیمات باز شود (بعد از تغییر زبان)
    if (localStorage.getItem('reopenSettings') === 'true') {
        localStorage.removeItem('reopenSettings');
        openModal('settingsModal');
    }

    // تم
    if (localStorage.getItem('theme') !== 'light') { 
        document.documentElement.classList.add('dark');
        let sw = document.getElementById('themeSwitch'); 
        if (sw) sw.checked = true; 
    } else {
        document.documentElement.classList.remove('dark');
    }

    // بستن منوهای باز با کلیک بیرون
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#attachMenu') && !e.target.closest('button[onclick*="attachMenu"]')) {
            const m = document.getElementById('attachMenu'); if(m) m.style.display = 'none';
        }
        if (!e.target.closest('#addMenu') && !e.target.closest('button[onclick*="addMenu"]')) {
            const m = document.getElementById('addMenu'); if(m) m.classList.add('hidden');
        }
        // بستن منوی پیام
        let mm = document.getElementById('msgMenu'); 
        if (mm && !mm.classList.contains('hidden') && !e.target.closest('#msgMenu')) {
            mm.classList.add('hidden');
        }
    });

    // مدیریت اینپوت پیام (ارتفاع خودکار)
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

    // شروع برنامه
    loadChats();
    setInterval(() => { if (state.currChat) loadMsg(false); else loadChats(); }, 3000);
});