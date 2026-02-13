import { apiCall } from './api.js';

export function loadGroups() {
    apiCall('admin_get_lists').then(d => {
        if(d.status === 'ok') renderGroups(d.groups);
    });
}

function renderGroups(list) {
    let html = '';
    list.forEach(g => {
        html += `
        <tr class="border-b hover:bg-gray-50">
            <td class="p-3">${g.id}</td>
            <td class="p-3 flex items-center gap-2">
                <img src="../${g.avatar || 'assets/img/chakavak.png'}" class="w-8 h-8 rounded-full bg-gray-200">
                ${g.name}
            </td>
            <td class="p-3"><span class="badge ${g.type=='channel'?'bg-purple-100 text-purple-700':'bg-blue-100 text-blue-700'}">${g.type=='channel'?'کانال':'گروه'}</span></td>
            <td class="p-3 text-xs text-gray-500">${g.created_at}</td>
            <td class="p-3 flex gap-2">
                <button onclick="viewGroupMsgs(${g.id}, '${g.name}')" class="bg-indigo-500 text-white px-2 py-1 rounded text-xs hover:bg-indigo-600">پیام‌ها</button>
                <button onclick="msgGroupAdmin(${g.id})" class="bg-teal-500 text-white px-2 py-1 rounded text-xs hover:bg-teal-600">پیام به مدیر</button>
                <button onclick="deleteGroup(${g.id})" class="bg-red-500 text-white px-2 py-1 rounded text-xs hover:bg-red-600">حذف</button>
            </td>
        </tr>`;
    });
    document.getElementById('groupsTable').innerHTML = html;
}

window.viewGroupMsgs = function(gid, name) {
    const modal = document.getElementById('adminModal');
    const content = document.getElementById('adminModalContent');
    content.innerHTML = `<h3 class="font-bold mb-3">پیام‌های ${name}</h3><div id="gMsgsList" class="max-h-60 overflow-y-auto border p-2 rounded bg-gray-50">درحال لود...</div>`;
    modal.classList.remove('hidden');
    apiCall('admin_get_group_msgs', { group_id: gid }).then(d => {
        let h = '';
        d.list.forEach(m => {
            h += `<div class="mb-2 text-sm border-b pb-1"><span class="font-bold text-blue-600">${m.first_name}:</span> ${m.message}</div>`;
        });
        document.getElementById('gMsgsList').innerHTML = h || 'پیامی نیست';
    });
}

window.msgGroupAdmin = function(gid) {
    const msg = prompt('پیام برای مدیر گروه:');
    if(msg) {
        apiCall('admin_send_to_owner', { group_id: gid, message: msg }).then(d => {
            alert(d.status==='ok' ? 'ارسال شد' : 'خطا');
        });
    }
}

window.deleteGroup = function(gid) {
    if(confirm('حذف شود؟')) {
        apiCall('admin_delete_group', { group_id: gid }).then(() => loadGroups());
    }
}