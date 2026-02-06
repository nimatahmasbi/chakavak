import { dec } from './encryption.js';

export function renderChatListItems(list, type) {
    if (!list || list.length === 0) return '<div class="text-center text-gray-500 mt-10">هیچ چتی وجود ندارد</div>';
    
    let html = '';
    list.forEach(c => {
        if (type == 'personal' && c.type != 'dm') return;
        if (type == 'group' && c.type != 'group') return;
        if (type == 'channel' && c.type != 'channel') return;

        let av = c.avatar || 'assets/img/chakavak.png';
        let name = c.name || (c.first_name + ' ' + c.last_name);
        let badge = c.unread > 0 ? `<div class="bg-blue-500 text-white text-[10px] font-bold px-1.5 rounded-full min-w-[18px] text-center">${c.unread}</div>` : '';
        
        // *** رمزگشایی پیام آخر ***
        let lastM = c.last_msg || '';
        // اگر کلید چت وجود دارد (برای گروه‌ها) یا کلاً رمز شده است، تلاش کن باز کنی
        // نکته: اگر chat_key خالی باشد (مثلاً در DM اگر کلید عمومی نداریم)، ممکن است درست باز نشود.
        // اما تابع dec طوری نوشته شده که اگر نتوانست، متن اصلی را برمی‌گرداند.
        lastM = dec(lastM, c.chat_key || ''); 
        
        if(lastM.length > 30) lastM = lastM.substr(0, 30) + '...';

        html += `
        <div onclick="openChat(${c.id},'${c.type}','${name}','${av}')" class="flex items-center px-4 py-3 hover:bg-[var(--bg-secondary)] cursor-pointer transition border-b border-[var(--border-color)]">
            <img src="${av}" class="w-12 h-12 rounded-full mx-2 object-cover bg-gray-200">
            <div class="flex-1 min-w-0">
                <div class="flex justify-between items-baseline mb-1">
                    <div class="font-bold text-[var(--text-primary)] truncate">${name}</div>
                    <div class="text-xs text-[var(--text-secondary)]">${c.last_time ? c.last_time.substr(11, 5) : ''}</div>
                </div>
                <div class="flex justify-between items-center">
                    <div class="text-sm text-[var(--text-secondary)] truncate w-full opacity-70" dir="auto">${lastM}</div>
                    ${badge}
                </div>
            </div>
        </div>`;
    });
    return html;
}

export function renderMessagesHTML(list, myId) {
    let html = '';
    list.forEach(m => {
        let isMe = (m.sender_id == myId);
        let txt = dec(m.message); // رمزگشایی پیام
        let cls = isMe ? 'bg-blue-600 self-end rounded-l-2xl rounded-tr-2xl text-white' : 'bg-[var(--bg-secondary)] self-start rounded-r-2xl rounded-tl-2xl text-[var(--text-primary)]';
        
        let content = txt;
        if (m.file_path) {
            if (m.file_type == 'image') content = `<img src="${m.file_path}" class="rounded-lg max-w-[220px] mb-1 cursor-pointer hover:opacity-90" onclick="showLightbox('${m.file_path}')">` + content;
            else if (m.file_type == 'voice') content = `<audio controls src="${m.file_path}" class="max-w-[200px]"></audio>`;
            else content = `<a href="${m.file_path}" target="_blank" class="flex items-center gap-2 bg-black/20 p-2 rounded text-xs">📎 فایل</a>` + content;
        }

        html += `
        <div oncontextmenu="showMsgOptions(event, ${m.id}); return false;" class="flex flex-col mb-2 max-w-[75%] p-3 ${cls} shadow-sm relative min-w-[80px] group text-sm leading-6">
            ${!isMe ? `<div class="text-xs font-bold mb-1 opacity-70 cursor-pointer" onclick="showPublicProfile(${m.sender_id})">${m.first_name}</div>` : ''}
            <div class="break-words whitespace-pre-wrap" dir="auto">${content}</div>
            <div class="text-[10px] opacity-60 text-end mt-1 flex justify-end gap-1">
                <span>${m.created_at.substr(11, 5)}</span>
                ${isMe ? (m.is_read ? '✓✓' : '✓') : ''}
            </div>
        </div>`;
    });
    return html;
}