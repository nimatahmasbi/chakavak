import { apiCall } from './api.js';

let currentChatUserId = null;

export function openChatModal(userId, name) {
    currentChatUserId = userId;
    const modal = document.getElementById('dmModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.getElementById('dmTargetName').innerText = name;
        document.getElementById('dmTargetId').value = userId;
        loadDmHistory();
    }
}

export function loadDmHistory() {
    if (!currentChatUserId) return;
    const box = document.getElementById('dmHistory');
    box.innerHTML = '...';

    apiCall('admin_get_dm_history', { target_id: currentChatUserId }).then(d => {
        if (d.status === 'ok') {
            let html = '';
            d.list.forEach(m => {
                const isAdmin = (m.sender_id == 1);
                const align = isAdmin ? 'items-end ml-auto' : 'items-start mr-auto';
                const bg = isAdmin ? 'bg-blue-100' : 'bg-white border';
                html += `<div class="flex flex-col mb-2 max-w-[80%] ${align}"><div class="${bg} p-2 rounded text-sm">${m.message}</div></div>`;
            });
            box.innerHTML = html || 'پیامی نیست';
            box.scrollTop = box.scrollHeight;
        }
    });
}

export function sendDm() {
    const input = document.getElementById('dmInput');
    const msg = input.value.trim();
    if (!msg || !currentChatUserId) return;

    apiCall('admin_send_dm', { target_id: currentChatUserId, message: msg }).then(d => {
        if (d.status === 'ok') {
            input.value = '';
            loadDmHistory();
        }
    });
}