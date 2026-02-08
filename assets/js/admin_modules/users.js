import { apiCall } from './api.js';
import { loadList } from './list.js'; // برای رفرش کردن لیست بعد از تغییر

export function toggleUserStatus(userId) {
    if (!confirm('آیا مطمئن هستید که می‌خواهید وضعیت این کاربر را تغییر دهید؟')) {
        return;
    }

    apiCall('admin_toggle_user', { user_id: userId }).then(data => {
        if (data.status === 'ok') {
            // لیست کاربران را رفرش کن تا وضعیت جدید نمایش داده شود
            loadList('users');
        } else {
            alert('خطا: ' + (data.msg || 'مشکلی پیش آمد'));
        }
    });
}