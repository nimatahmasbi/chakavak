import { apiCall } from './api.js';
import { state } from './state.js';

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
        if (window.loadMsg) window.loadMsg(true);
    });
}