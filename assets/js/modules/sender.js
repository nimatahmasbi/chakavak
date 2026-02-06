import { apiCall } from './api.js';
import { state } from './state.js';

// تابع ارسال درخواست (مشترک بین chat.js و media.js)
export function sendReq(message, file, isImage = 0) {
    let fd = new FormData();
    fd.append('target_id', state.currChat.id);
    fd.append('type', state.currChat.type);
    fd.append('message', message);
    
    if (file) {
        fd.append('file', file);
        fd.append('is_image', isImage);
    }

    return apiCall('send_message', fd, true).then(() => {
        // پس از ارسال موفق، اگر تابع رفرش وجود داشت صدا بزن
        if (window.loadMsg) window.loadMsg(true);
    });
}