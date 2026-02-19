import { apiCall } from './api.js';

// تابع اصلی که توسط admin.js فراخوانی می‌شود
export function loadUsers() {
    apiCall('admin_get_lists').then(d => {
        if(d.status === 'ok') {
            renderUsersTable(d.users);
        } else {
            console.error('Error loading users:', d);
        }
    });
}

// تابع رندر کردن جدول
function renderUsersTable(list) {
    const table = document.getElementById('usersTable');
    // اگر المنت جدول در صفحه نبود، کاری نکن (جلوگیری از خطا)
    if (!table) return;

    let html = '';
    
    // اگر لیست خالی بود
    if(!list || list.length === 0) {
        table.innerHTML = '<tr><td colspan="5" class="p-8 text-center text-gray-400 bg-gray-50 rounded-lg m-4">هیچ کاربری در سیستم یافت نشد.</td></tr>';
        return;
    }

    list.forEach(u => {
        // دکمه وضعیت (فعال/مسدود)
        let statusBtn = u.is_approved == 1 
            ? `<button onclick="toggleUser(${u.id}, 0)" class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold border border-green-200 hover:bg-green-200 transition flex items-center gap-1 mx-auto"><span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>فعال</button>`
            : `<button onclick="toggleUser(${u.id}, 1)" class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold border border-red-200 hover:bg-red-200 transition flex items-center gap-1 mx-auto"><span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>مسدود</button>`;
        
        let avatar = (u.avatar && u.avatar !== 'default') ? '../'+u.avatar : '../assets/img/chakavak.png';

        html += `
        <tr class="border-b border-gray-50 hover:bg-gray-50 transition group">
            <td class="p-4 text-center text-gray-400 font-mono text-xs">#${u.id}</td>
            
            <td class="p-4">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <img src="${avatar}" class="w-10 h-10 rounded-full bg-gray-200 object-cover border border-gray-200 group-hover:border-blue-300 transition">
                        ${u.is_approved == 1 ? '<span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-white rounded-full"></span>' : ''}
                    </div>
                    <div>
                        <div class="font-bold text-gray-800 text-sm">${u.first_name} ${u.last_name || ''}</div>
                        <div class="text-xs text-gray-500 font-mono mt-0.5">@${u.username}</div>
                    </div>
                </div>
            </td>
            
            <td class="p-4 text-sm font-mono text-gray-600">${u.phone}</td>
            
            <td class="p-4 text-center">
                ${statusBtn}
            </td>
            
            <td class="p-4 text-center">
                <div class="flex items-center justify-center gap-2">
                    <button onclick="viewUserChats(${u.id})" class="bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white px-3 py-1.5 rounded-lg text-xs transition border border-blue-100 hover:border-blue-600">
                        پیام‌ها
                    </button>
                    <button onclick="editUser(${u.id})" class="bg-gray-50 text-gray-600 hover:bg-gray-600 hover:text-white px-3 py-1.5 rounded-lg text-xs transition border border-gray-200 hover:border-gray-600">
                        ویرایش
                    </button>
                </div>
            </td>
        </tr>`;
    });
    
    table.innerHTML = html;
}

// اتصال توابع کمکی به پنجره برای استفاده در HTML (onclick)
window.toggleUser = function(uid, state) {
    let action = state == 1 ? 'فعال' : 'مسدود';
    if(!confirm(`آیا از ${action} کردن این کاربر مطمئن هستید؟`)) return;
    
    apiCall('admin_toggle_user', { user_id: uid, state: state }).then(d => {
        if(d.status === 'ok') loadUsers(); // رفرش لیست
    });
}

window.viewUserChats = function(uid) {
    const modal = document.getElementById('adminModal');
    const content = document.getElementById('adminModalContent');
    content.innerHTML = '<div class="text-center p-8"><div class="animate-spin w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-2"></div><span class="text-gray-500 text-sm">درحال بارگذاری گفتگوها...</span></div>';
    modal.classList.remove('hidden');
    
    apiCall('admin_get_user_chats_list', { user_id: uid }).then(d => {
        let h = `<div class="flex justify-between items-center mb-4 pb-3 border-b"><h3 class="font-bold text-gray-800">تاریخچه چت‌های کاربر #${uid}</h3><span class="text-xs bg-gray-100 px-2 py-1 rounded text-gray-500">${d.list ? d.list.length : 0} گفتگو</span></div>
                 <div class="space-y-2 max-h-[400px] overflow-y-auto custom-scrollbar p-1">`;
        
        if(d.list && d.list.length > 0) {
            d.list.forEach(t => {
                h += `<div class="flex justify-between items-center bg-white p-3 rounded-xl border border-gray-100 hover:border-blue-300 hover:shadow-sm transition group">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">${t.first_name.substr(0,1)}</div>
                            <span class="text-sm font-medium text-gray-700">${t.first_name} ${t.last_name || ''}</span>
                        </div>
                        <button onclick="loadDmHistory(${uid}, ${t.id})" class="text-xs bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg hover:bg-blue-600 hover:text-white transition">مشاهده متن</button>
                      </div>`;
            });
        } else {
            h += '<div class="text-center py-8 text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-200">هیچ گفتگویی یافت نشد.</div>';
        }
        h += '</div><button onclick="document.getElementById(\'adminModal\').classList.add(\'hidden\')" class="mt-4 w-full bg-gray-100 text-gray-600 hover:bg-gray-200 font-bold py-3 rounded-xl transition">بستن</button>';
        content.innerHTML = h;
    });
}

window.loadDmHistory = function(u1, u2) {
    const content = document.getElementById('adminModalContent');
    content.innerHTML = '<div class="text-center p-8"><div class="animate-spin w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-2"></div><span class="text-gray-500 text-sm">درحال دریافت متن پیام‌ها...</span></div>';
    
    apiCall('admin_get_dm_history', { user1: u1, user2: u2 }).then(d => {
        let h = `<div class="flex justify-between items-center mb-2 pb-2 border-b">
                    <button onclick="viewUserChats(${u1})" class="text-xs flex items-center gap-1 text-blue-600 hover:bg-blue-50 px-2 py-1 rounded transition">← بازگشت</button>
                    <h3 class="font-bold text-sm text-gray-700">متن گفتگو</h3>
                 </div>`;
        
        h += '<div class="space-y-3 max-h-[400px] overflow-y-auto bg-gray-50 p-4 rounded-xl border border-gray-100 custom-scrollbar">';
        
        if(d.list && d.list.length > 0) {
            d.list.forEach(m => {
                let isMe = (m.sender_id == u1);
                let align = isMe ? 'justify-end' : 'justify-start';
                let bg = isMe ? 'bg-blue-600 text-white rounded-tr-none' : 'bg-white text-gray-800 border border-gray-200 rounded-tl-none';
                let name = isMe ? 'کاربر هدف' : 'مخاطب';
                
                h += `<div class="flex ${align}">
                        <div class="max-w-[80%] ${bg} p-3 rounded-2xl shadow-sm text-sm">
                            <div class="text-[10px] opacity-70 mb-1 ${isMe ? 'text-blue-100 text-right' : 'text-gray-400 text-left'}">${m.created_at}</div>
                            <div class="leading-relaxed break-words">${m.message}</div>
                            ${m.file_path ? `<div class="mt-2 text-xs bg-black/10 p-1 rounded flex items-center gap-1">📎 فایل پیوست</div>` : ''}
                        </div>
                      </div>`;
            });
        } else {
            h += '<div class="text-center text-gray-400 text-sm">پیامی رد و بدل نشده است.</div>';
        }
        
        h += '</div><button onclick="document.getElementById(\'adminModal\').classList.add(\'hidden\')" class="mt-4 w-full bg-gray-100 text-gray-600 hover:bg-gray-200 font-bold py-3 rounded-xl transition">بستن</button>';
        content.innerHTML = h;
    });
}