import { apiCall } from './api.js';

export function loadList(type) {
    const tbody = document.getElementById('list-' + type);
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="7" class="text-center p-4 text-gray-500">درحال دریافت اطلاعات...</td></tr>';

    apiCall('admin_get_lists', { list_type: type }).then(data => {
        if (data.status === 'ok') {
            // آپدیت آمار
            if (data.stats) {
                if(document.getElementById('stat-users')) document.getElementById('stat-users').innerText = data.stats.users;
                if(document.getElementById('stat-groups')) document.getElementById('stat-groups').innerText = data.stats.groups;
                if(document.getElementById('stat-msgs')) document.getElementById('stat-msgs').innerText = data.stats.msgs;
            }
            // رندر جدول
            renderTable(type, data.list, tbody);
        } else {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center p-4 text-red-500">خطا: ${data.msg}</td></tr>`;
        }
    });
}

function renderTable(type, list, tbody) {
    if (!list || list.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center p-4 text-gray-400">موردی یافت نشد.</td></tr>';
        return;
    }

    let html = '';
    
    if (type === 'users') {
        list.forEach(u => {
            const isActive = (u.is_approved == 1);
            const badge = isActive 
                ? '<span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-bold">فعال</span>'
                : '<span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-bold">مسدود</span>';
            
            const btnTxt = isActive ? 'مسدود کردن' : 'آزاد کردن';
            const btnCls = isActive ? 'text-red-600 border-red-200 hover:bg-red-50' : 'text-green-600 border-green-200 hover:bg-green-50';

            html += `
            <tr class="border-b hover:bg-gray-50 transition">
                <td class="p-3 text-gray-500 text-sm">${u.id}</td>
                <td class="p-3 font-bold text-gray-800">${u.first_name} ${u.last_name || ''}</td>
                <td class="p-3 text-blue-600 text-xs dir-ltr text-right">@${u.username}</td>
                <td class="p-3 text-gray-600 text-xs dir-ltr text-right font-mono">${u.phone}</td>
                <td class="p-3">${badge}</td>
                <td class="p-3 flex gap-2 justify-end">
                    <button onclick="openChatModal(${u.id}, '${u.first_name}')" class="bg-blue-50 text-blue-600 border border-blue-100 px-3 py-1 rounded text-xs hover:bg-blue-100">پیام</button>
                    <button onclick="toggleUserStatus(${u.id})" class="border px-3 py-1 rounded text-xs transition ${btnCls}">${btnTxt}</button>
                </td>
            </tr>`;
        });
    } 
    else if (type === 'groups') {
        list.forEach(g => {
            const isBanned = (g.is_banned == 1);
            html += `
            <tr class="border-b hover:bg-gray-50 transition">
                <td class="p-3 text-gray-500 text-sm">${g.id}</td>
                <td class="p-3 font-bold text-gray-800">${g.name}</td>
                <td class="p-3"><span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-xs">${g.type}</span></td>
                <td class="p-3">${isBanned ? '<span class="text-red-500 font-bold text-xs">مسدود</span>' : '<span class="text-green-500 font-bold text-xs">فعال</span>'}</td>
                <td class="p-3 flex gap-2 justify-end">
                    <button onclick="banGroup(${g.id})" class="text-orange-500 border border-orange-200 px-2 py-1 rounded text-xs hover:bg-orange-50">${isBanned ? 'آزاد کردن' : 'مسدود کردن'}</button>
                    <button onclick="deleteGroup(${g.id})" class="text-red-500 border border-red-200 px-2 py-1 rounded text-xs hover:bg-red-50">حذف</button>
                </td>
            </tr>`;
        });
    }
    tbody.innerHTML = html;
}