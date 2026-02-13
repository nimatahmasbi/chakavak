import { state } from './state.js';
import { apiCall } from './api.js';
import { openModal } from './ui.js';
import { enc } from './encryption.js';
import { renderChatListItems, renderMessagesHTML } from './renderer.js';
import { sendReq } from './sender.js';
import { showPublicProfile } from './auth.js';

// --- دریافت و نمایش لیست چت‌ها ---
export function loadChats() {
    apiCall('get_chats_list').then(d => {
        // بررسی اعتبار پاسخ
        if (!d || d.status !== 'ok') {
            console.error('Chat Load Error:', d);
            return;
        }
        
        state.chatListCache = d.list;
        
        // رفرش لیست بر اساس تب فعال
        let activeEl = document.querySelector('.active-tab');
        filterChats(activeEl ? activeEl.id.replace('tab-', '') : 'all');
    });
}

// --- فیلتر کردن تب‌ها ---
export function filterChats(type) {
    // تغییر استایل دکمه‌ها
    document.querySelectorAll('.tab-btn').forEach(b => { 
        b.classList.remove('active-tab', 'text-blue-600', 'border-blue-600'); 
        b.classList.add('text-gray-500', 'dark:text-gray-400', 'border-transparent'); 
    });
    
    let btn = document.getElementById('tab-' + type);
    if(btn) { 
        btn.classList.add('active-tab', 'text-blue-600', 'border-blue-600'); 
        btn.classList.remove('text-gray-500', 'dark:text-gray-400', 'border-transparent'); 
    }

    let list = state.chatListCache || [];
    let html = renderChatListItems(list, type);
    
    const listEl = document.getElementById('chatList');
    if(listEl) listEl.innerHTML = html;
}

// --- باز کردن چت ---
export function openChat(id, type, name, av) {
    state.currChat = { id, type };
    
    // آپدیت هدر
    const hName = document.getElementById('headName');
    const hAv = document.getElementById('headAvatar');
    const hStatus = document.getElementById('headStatus');
    
    if(hName) hName.innerText = name;
    if(hAv) hAv.src = av;
    if(hStatus) hStatus.innerText = 'درحال اتصال...';
    
    // دکمه تنظیمات
    let setBtn = document.getElementById('groupSettingsBtn');
    if(setBtn) {
        if (type != 'dm') setBtn.classList.remove('hidden'); 
        else setBtn.classList.add('hidden');
    }

    // مدیریت نمایش (موبایل/دسکتاپ)
    const sidebar = document.getElementById('sidebar');
    const screenMain = document.getElementById('screen-main');
    const screenChat = document.getElementById('screen-chat');
    
    // اگر موبایل است، سایدبار را مخفی کن
    if (window.innerWidth < 768 && sidebar) {
        sidebar.classList.add('hidden');
        sidebar.classList.remove('flex');
    }
    
    // نمایش صفحه چت
    if (screenMain) screenMain.classList.add('hidden');
    if (screenChat) {
        screenChat.classList.remove('hidden');
        screenChat.classList.add('flex');
    }

    loadMsg(true);
}

// --- بستن چت ---
export function closeChat() {
    state.currChat = null;
    
    const sidebar = document.getElementById('sidebar');
    const screenMain = document.getElementById('screen-main');
    const screenChat = document.getElementById('screen-chat');
    
    if (window.innerWidth < 768) {
        // موبایل: نمایش لیست
        if (sidebar) {
            sidebar.classList.remove('hidden');
            sidebar.classList.add('flex');
        }
        if (screenChat) {
            screenChat.classList.add('hidden');
            screenChat.classList.remove('flex');
        }
    } else {
        // دسکتاپ: نمایش صفحه خالی
        if (screenChat) screenChat.classList.add('hidden');
        if (screenMain) screenMain.classList.remove('hidden');
    }
    
    loadChats();
}

// --- دریافت پیام‌ها ---
export function loadMsg(forceScroll) {
    if (!state.currChat) return;
    
    apiCall('get_messages', { target_id: state.currChat.id, type: state.currChat.type }).then(d => {
        if (!d || d.status !== 'ok') return;

        state.currentKey = d.chat_key;
        
        let statusText = (state.currChat.type == 'dm') ? 
            (d.header.status == 'online' ? 'آنلاین' : '') : 
            d.members_count + ' عضو';
            
        const hStat = document.getElementById('headStatus');
        if(hStat) hStat.innerText = statusText;

        let box = document.getElementById('msgBox');
        if(box) {
            // دریافت ID کاربر فعلی از متغیر گلوبال
            let myId = (typeof MY_ID !== 'undefined' ? MY_ID : 0);
            let html = renderMessagesHTML(d.list, myId); 
            
            if (box.innerHTML.length != html.length) { 
                box.innerHTML = html;
                if (forceScroll) box.scrollTop = box.scrollHeight;
            }
        }
    });
}

// --- ارسال متن ---
export function sendText() {
    let input = document.getElementById('msgInput');
    let t = input.value;
    if (!t.trim()) return;

    sendReq(enc(t), null).then(() => {
        input.value = '';
        input.style.height = '48px';
        input.focus();
    });
}

// --- مدیریت هدر ---
export function clickHeader() {
    if (!state.currChat) return;
    
    if (state.currChat.type == 'dm') {
        showPublicProfile(state.currChat.id);
    } else {
        apiCall('get_group_details', { group_id: state.currChat.id }).then(d => {
            if (!d || d.status != 'ok') return;
            
            document.getElementById('gInfoName').innerText = d.group.name;
            document.getElementById('gInfoAvatar').src = d.group.avatar || 'assets/img/chakavak.png';
            
            let adminBox = document.getElementById('adminActions');
            let addBox = document.getElementById('addMemberBox');
            
            if (d.is_admin) { 
                adminBox.classList.remove('hidden'); 
                addBox.classList.remove('hidden'); 
            } else { 
                adminBox.classList.add('hidden'); 
                addBox.classList.add('hidden'); 
            }
            
            let h = '';
            let myId = (typeof MY_ID !== 'undefined' ? MY_ID : 0);
            d.members.forEach(m => {
                let del = (d.is_admin && m.id != myId) ? 
                    `<button onclick="removeMember(${m.id})" class="text-red-500 text-xs ml-auto bg-red-50 px-2 py-1 rounded">حذف</button>` : '';
                h += `<div class="flex items-center p-3 border-b border-gray-100 dark:border-gray-700">
                        <img src="${m.avatar || 'assets/img/chakavak.png'}" class="w-10 h-10 rounded-full ml-3 object-cover"> 
                        <span class="text-sm font-bold text-gray-800 dark:text-gray-200">${m.first_name} ${m.last_name || ''}</span> 
                        ${del}
                      </div>`;
            });
            document.getElementById('gMembersList').innerHTML = h;
            openModal('groupInfoModal');
        });
    }
}

// --- منوی راست کلیک ---
export function showMsgOptions(e, msgId) {
    e.preventDefault(); 
    const menu = document.getElementById('msgMenu');
    if (!menu) return;

    let x = e.pageX;
    let y = e.pageY;
    if (window.innerHeight - y < 150) y -= 120;
    
    menu.style.top = `${y}px`;
    menu.style.left = `${x}px`;
    menu.classList.remove('hidden');
    window.currentMsgId = msgId; 
}

export function deleteMessage() {
    if(!window.currentMsgId) return;
    if(!confirm('حذف پیام؟')) return;
    
    apiCall('admin_delete_msg', { msg_id: window.currentMsgId }).then(d => {
        document.getElementById('msgMenu').classList.add('hidden');
        if (d.status === 'ok') loadMsg(false);
    });
}

// اتصال توابع
window.loadChats = loadChats;
window.filterChats = filterChats;
window.openChat = openChat;
window.closeChat = closeChat;
window.loadMsg = loadMsg;
window.sendText = sendText;
window.clickHeader = clickHeader;
window.showMsgOptions = showMsgOptions;
window.deleteMessage = deleteMessage;