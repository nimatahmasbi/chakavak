import { apiCall } from './api.js';
import { loadList } from './list.js';

export function banGroup(groupId) {
    if (!confirm('آیا وضعیت مسدودی گروه تغییر کند؟')) return;
    
    apiCall('admin_ban_group', { group_id: groupId }).then(data => {
        if(data.status === 'ok') loadList('groups');
    });
}

export function deleteGroup(groupId) {
    if (!confirm('هشدار: با حذف گروه تمام پیام‌ها و اعضای آن حذف می‌شوند. ادامه می‌دهید؟')) return;

    apiCall('admin_delete_group', { group_id: groupId }).then(data => {
        if(data.status === 'ok') loadList('groups');
    });
}