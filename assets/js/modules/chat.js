import { state } from './state.js';
import { apiCall } from './api.js';
import { openModal } from './ui.js';
import { enc } from './encryption.js';
import { renderChatListItems, renderMessagesHTML } from './renderer.js';
import { sendReq } from './sender.js'; // فقط به sender نیاز دارد

// --- لیست چت ---
export function loadChats() {
    apiCall('get_chats_list').then(d => {
        if(d.status !== 'ok') return;
        state.chatListCache = d.list;
        let activeEl = document.querySelector('.active-tab');
        filterChats(activeEl ? activeEl.id.replace('tab-', '') : 'all');
    });
}

export function filterChats(type) {
    document.querySelectorAll('.tab-btn').forEach(b => { 
        b.classList.remove('active-tab'); 
        b.classList.add('text-[var(--text-secondary)]'); 
    });
    let btn = document.getElementById('tab-' + type);
    if(btn) { btn.classList.add('active-tab'); btn.classList.remove('text-[var(--text-secondary)]'); }

    let list = state.chatListCache || [];
    let html = renderChatListItems(list, type);
    document.getElementById('chatList').innerHTML = html;
}

// --- چت روم ---
export function openChat(id, type, name, av) {
    state.currChat = { id, type };
    
    document.getElementById('headName').innerText = name;
    document.getElementById('headAvatar').src = av;
    document.getElementById('headStatus').innerText = '...';
    
    let setBtn = document.getElementById('groupSettingsBtn');
    if (type != 'dm') setBtn.classList.remove('hidden'); 
    else setBtn.classList.add('hidden');

    document.getElementById('screen-chat').classList.remove('hidden');
    loadMsg(true);
}

export function closeChat() {
    document.getElementById('screen-chat').classList.add('hidden');
    state.currChat = null;
    loadChats();
}

export function loadMsg(forceScroll) {
    if (!state.currChat) return;
    
    apiCall('get_messages', { target_id: state.currChat.id, type: state.currChat.type }).then(d => {
        state.currentKey = d.chat_key; // کلید ذخیره می‌شود
        
        let statusText = (state.currChat.type == 'dm') ? 
            (d.header.status == 'online' ? 'آنلاین' : d.header.status) : 
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

// کلیک روی هدر (پروفایل یا گروه)
export function clickHeader() {
    if (state.currChat.type == 'dm') {
        // جلوگیری از چرخه: استفاده از window
        if(window.showPublicProfile) window.showPublicProfile(state.currChat.id);
    } else {
        apiCall('get_group_details', { group_id: state.currChat.id }).then(d => {
            if (d.status != 'ok') return;
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
                // دکمه حذف عضو (از window استفاده می‌کند)
                let del = (d.is_admin && m.id != MY_ID) ? `<button onclick="removeMember(${m.id})" class="text-red-500 text-xs ml-auto">حذف</button>` : '';
                h += `<div class="flex items-center p-2 border-b border-[var(--border-color)]">
                        <img src="${m.avatar || 'assets/img/chakavak.png'}" class="w-8 h-8 rounded-full mr-2"> 
                        <span class="text-sm text-[var(--text-primary)]">${m.first_name}</span> 
                        ${del}
                      </div>`;
            });
            document.getElementById('gMembersList').innerHTML = h;
            openModal('groupInfoModal');
        });
    }
}

// اتصال حیاتی به Window
window.loadChats = loadChats;
window.filterChats = filterChats;
window.openChat = openChat;
window.closeChat = closeChat;
window.loadMsg = loadMsg;
window.sendText = sendText;
window.clickHeader = clickHeader;