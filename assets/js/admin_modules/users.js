import { apiCall } from './api.js';

export function loadUsers() {
    apiCall('admin_get_lists').then(d => {
        if(d.status === 'ok') renderUsers(d.users);
    });
}

function renderUsers(list) {
    let html = '';
    list.forEach(u => {
        let statusBtn = u.is_approved == 1 
            ? `<button onclick="toggleUser(${u.id}, 0)" class="bg-red-100 text-red-600 px-2 py-1 rounded text-xs hover:bg-red-200">مسدود</button>`
            : `<button onclick="toggleUser(${u.id}, 1)" class="bg-green-100 text-green-600 px-2 py-1 rounded text-xs hover:bg-green-200">فعال</button>`;
            
        html += `
        <tr class="border-b hover:bg-gray-50">
            <td class="p-3">${u.id}</td>
            <td class="p-3 flex items-center gap-2">
                <img src="../${u.avatar || 'assets/img/chakavak.png'}" class="w-8 h-8 rounded-full bg-gray-200">
                <div>
                    <div class="font-bold text-sm">${u.first_name} ${u.last_name}</div>
                    <div class="text-xs text-gray-500">@${u.username}</div>
                </div>
            </td>
            <td class="p-3 text-xs font-mono">${u.phone}</td>
            <td class="p-3">
                <button onclick="viewUserChats(${u.id})" class="bg-blue-500 text-white px-2 py-1 rounded text-xs hover:bg-blue-600 mr-1">چت‌ها</button>
                ${statusBtn}
                <button onclick="editUser(${u.id})" class="bg-gray-500 text-white px-2 py-1 rounded text-xs hover:bg-gray-600 ml-1">ویرایش</button>
            </td>
        </tr>`;
    });
    document.getElementById('usersTable').innerHTML = html;
}

window.toggleUser = function(uid, state) {
    if(!confirm('تغییر وضعیت؟')) return;
    apiCall('admin_toggle_user', { user_id: uid, state: state }).then(() => loadUsers());
}

window.viewUserChats = function(uid) {
    const modal = document.getElementById('adminModal');
    const content = document.getElementById('adminModalContent');
    content.innerHTML = '<div class="text-center">درحال دریافت...</div>';
    modal.classList.remove('hidden');
    
    apiCall('admin_get_user_chats_list', { user_id: uid }).then(d => {
        let h = `<h3 class="font-bold mb-3">تاریخچه چت ${uid}</h3><div class="space-y-2 max-h-[400px] overflow-y-auto">`;
        if(d.list && d.list.length > 0) {
            d.list.forEach(t => {
                h += `<div class="flex justify-between items-center bg-gray-50 p-2 rounded border">
                        <span>${t.first_name} ${t.last_name}</span>
                        <button onclick="loadDmHistory(${uid}, ${t.id})" class="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded">متن</button>
                      </div>`;
            });
        } else {
            h += '<div class="text-gray-500 text-sm">خالی</div>';
        }
        h += '</div><button onclick="document.getElementById(\'adminModal\').classList.add(\'hidden\')" class="mt-4 w-full bg-gray-200 py-2 rounded">بستن</button>';
        content.innerHTML = h;
    });
}

window.loadDmHistory = function(u1, u2) {
    const content = document.getElementById('adminModalContent');
    content.innerHTML = '<div class="text-center">درحال لود...</div>';
    apiCall('admin_get_dm_history', { user1: u1, user2: u2 }).then(d => {
        let h = `<div class="flex justify-between mb-2"><h3 class="font-bold">چت</h3> <button onclick="viewUserChats(${u1})" class="text-xs text-blue-500">بازگشت</button></div>`;
        h += '<div class="space-y-2 max-h-[400px] overflow-y-auto bg-gray-100 p-2 rounded">';
        d.list.forEach(m => {
            let align = m.sender_id == u1 ? 'text-right' : 'text-left';
            let bg = m.sender_id == u1 ? 'bg-white' : 'bg-blue-100';
            h += `<div class="${align}"><div class="inline-block ${bg} p-2 rounded shadow-sm text-sm max-w-[80%]">${m.message}</div></div>`;
        });
        h += '</div>';
        content.innerHTML = h;
    });
}