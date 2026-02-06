import './modules/ui.js';
import './modules/auth.js';
import './modules/chat.js';
import './modules/group.js';
import './modules/media.js';
import { loadChats, loadMsg, sendText } from './modules/chat.js';
import { state } from './modules/state.js';

document.addEventListener('DOMContentLoaded', () => {
    // فراخوانی امن بعد از اطمینان از اتصال توابع به window
    setTimeout(() => {
        if(window.loadChats) window.loadChats();
    }, 200);

    // تنظیمات زبان و تم
    if (typeof applyLang === "function") applyLang();
    if (localStorage.getItem('theme') === 'light') document.body.classList.add('light-mode');

    // مدیریت Input پیام
    const input = document.getElementById('msgInput');
    if (input) {
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                window.sendText();
            }
        });
    }

    // آپدیت خودکار
    setInterval(() => { 
        if (state.currChat) loadMsg(false); 
        else loadChats(); 
    }, 3000);
});