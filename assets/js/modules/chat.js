import { state } from './state.js';
import { apiCall } from './api.js';
import { openModal } from './ui.js';
import { enc } from './encryption.js';
import { renderChatListItems, renderMessagesHTML } from './renderer.js';
import { sendReq } from './sender.js';
import { showPublicProfile } from './auth.js';

// --- لیست چت ---
export function loadChats() {
    apiCall('get_chats_list').then(d => {
        // اعتبارسنجی پاسخ: اگر d وجود نداشت یا status اوکی نبود، ادامه نده
        if (!d || d.status !== 'ok') return;
        
        state.chatListCache = d.list;
        
        // پیدا کردن تب فعال
        let activeEl = document.querySelector('.active-tab');
        filterChats(activeEl ? activeEl.id.replace('tab-', '') : 'all');
    });
}

export function filterChats(type) {
    // مدیریت تب‌ها
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
    
    document.getElementById('headName').innerText = name;
    document.getElementById('headAvatar').src = av;
    document.getElementById('headStatus').innerText = '...';
    
    let setBtn = document.getElementById('groupSettingsBtn');
    if (type != 'dm') setBtn.classList.remove('hidden'); else setBtn.classList.add('hidden');

    const sidebar = document.getElementById('sidebar');
    const screen = document.getElementById('screen-chat');
    sidebar.classList.add('hidden', 'md:flex'); 
    screen.classList.remove('hidden');
    screen.classList.add('flex');

    loadMsg(true);
}

export function closeChat() {
    state.currChat = null;
    const sidebar = document.getElementById('sidebar');
    const screen = document.getElementById('screen-chat');
    sidebar.classList.remove('hidden');
    screen.classList.add('hidden');
    loadChats();
}

// --- دریافت پیام‌ها ---
export function loadMsg(forceScroll) {
    if (!state.currChat) return;
    
    apiCall('get_messages', { target_id: state.currChat.id, type: state.currChat.type }).then(d => {
        // *** اصلاح حیاتی: جلوگیری از کرش وقتی اینترنت قطع است ***
        if (!d || d.status !== 'ok') {
            if (d && d.network_error) {
                document.getElementById('headStatus').innerText = 'درحال تلاش برای اتصال...';
            }
            return;
        }

        state.currentKey = d.chat_key;
        
        let statusText = (state.currChat.type == 'dm') ? 
            (d.header.status == 'online' ? 'آنلاین' : '') : 
            d.members_count + ' عضو';
        document.getElementById('headStatus').innerText = statusText;

        let box = document.getElementById('msgBox');
        let html = renderMessagesHTML(d.list, MY_ID); 
        
        if (box.innerHTML.length != html.length) { 
            box.innerHTML = html;
            if (forceScroll) box.scrollTop = box.scrollHeight;
        }
    });
}

export function sendText() {
    let t = document.getElementById('msgInput').value;
    if (!t.trim()) return;
    sendReq(enc(t), null).then(() => {
        document.getElementById('msgInput').value = '';
    });
}

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
            d.members.forEach(m => {
                let del = (d.is_admin && m.id != MY_ID) ? `<button onclick="removeMember(${m.id})" class="text-red-500 text-xs ml-auto">حذف</button>` : '';
                h += `<div class="flex items-center p-2 border-b border-gray-100 dark:border-gray-700">
                        <img src="${m.avatar || 'assets/img/chakavak.png'}" class="w-8 h-8 rounded-full mr-2"> 
                        <span class="text-sm text-gray-800 dark:text-gray-200">${m.first_name}</span> 
                        ${del}
                      </div>`;
            });
            document.getElementById('gMembersList').innerHTML = h;
            openModal('groupInfoModal');
        });
    }
}

// اتصال به Window
window.loadChats = loadChats;
window.filterChats = filterChats;
window.openChat = openChat;
window.closeChat = closeChat;
window.loadMsg = loadMsg;
window.sendText = sendText;
window.clickHeader = clickHeader;