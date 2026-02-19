import { apiCall } from './api.js';

export function loadGroups() {
    apiCall('admin_get_lists').then(d => {
        if(d.status === 'ok') renderGroupsTable(d.groups);
    });
}

function renderGroupsTable(list) {
    const table = document.getElementById('groupsTable');
    if (!table) return;

    let html = '';
    if(list.length === 0) {
        html = '<tr><td colspan="5" class="p-8 text-center text-gray-400">هیچ گروهی یافت نشد</td></tr>';
    } else {
        list.forEach(g => {
            // نشانگر حذف شده
            let deletedBadge = g.is_deleted == 1 
                ? '<span class="ml-2 text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded">حذف شده</span>' 
                : '';
                
            let typeBadge = g.type === 'channel' 
                ? '<span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-purple-100 text-purple-700">📢 کانال</span>' 
                : '<span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-100 text-blue-700">👥 گروه</span>';
            
            let avatar = g.avatar ? '../'+g.avatar : '../assets/img/chakavak.png';

            html += `
            <tr class="border-b hover:bg-gray-50 transition">
                <td class="p-4 text-gray-500">${g.id}</td>
                <td class="p-4 flex items-center gap-3">
                    <img src="${avatar}" class="w-10 h-10 rounded-full bg-gray-200 object-cover">
                    <div>
                        <span class="font-bold text-gray-800 text-sm">${g.name}</span>
                        ${deletedBadge}
                    </div>
                </td>
                <td class="p-4 text-center">${typeBadge}</td>
                <td class="p-4 text-sm text-gray-500 text-center">${g.created_at}</td>
                <td class="p-4 text-center flex justify-center gap-2">
                    <button onclick="viewGroupMsgs(${g.id}, '${g.name}')" class="bg-indigo-500 text-white px-2 py-1 rounded text-xs hover:bg-indigo-600">پیام‌ها</button>
                    <button onclick="deleteGroup(${g.id})" class="bg-red-500 text-white px-2 py-1 rounded text-xs hover:bg-red-600">حذف کامل</button>
                </td>
            </tr>`;
        });
    }
    table.innerHTML = html;
}

// تابع دیکد کردن (ساده شده با CryptoJS)
function decryptMsg(cipher, key) {
    if(!key || !cipher) return cipher;
    try {
        // فرض بر این است که از CryptoJS استفاده شده است
        if(typeof CryptoJS !== 'undefined') {
            var bytes = CryptoJS.AES.decrypt(cipher, key);
            var originalText = bytes.toString(CryptoJS.enc.Utf8);
            if(originalText) return originalText;
        }
        return cipher; // اگر دیکد نشد خود متن را نشان بده
    } catch(e) { return cipher; }
}

window.viewGroupMsgs = function(gid, name) {
    const modal = document.getElementById('adminModal');
    const content = document.getElementById('adminModalContent');
    content.innerHTML = `<h3 class="font-bold mb-3 border-b pb-2">پیام‌های ${name}</h3><div id="gMsgsList" class="max-h-[350px] overflow-y-auto border p-3 rounded bg-gray-50">درحال لود...</div><button onclick="document.getElementById('adminModal').classList.add('hidden')" class="mt-4 w-full bg-gray-200 py-2 rounded">بستن</button>`;
    modal.classList.remove('hidden');
    
    apiCall('admin_get_group_msgs', { group_id: gid }).then(d => {
        let h = '';
        if (d.list && d.list.length > 0) {
            d.list.forEach(m => {
                // تلاش برای دیکد کردن
                let msgText = d.chat_key ? decryptMsg(m.message, d.chat_key) : m.message;
                
                h += `<div class="mb-2 text-sm border-b pb-1">
                        <div class="flex justify-between">
                            <span class="font-bold text-blue-600 text-xs">${m.first_name}</span>
                            <span class="text-[10px] text-gray-400">${m.created_at}</span>
                        </div>
                        <div class="text-gray-700 mt-1 break-words">${msgText}</div>
                      </div>`;
            });
        } else {
            h = '<div class="text-center text-gray-500 py-2">هیچ پیامی نیست.</div>';
        }
        document.getElementById('gMsgsList').innerHTML = h;
    });
}

window.deleteGroup = function(gid) {
    if(confirm('هشدار: حذف کامل غیرقابل بازگشت است. آیا مطمئن هستید؟')) {
        apiCall('admin_delete_group', { group_id: gid }).then(() => loadGroups());
    }
}