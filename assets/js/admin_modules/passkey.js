import { apiCall } from './api.js';

export function loadPasskey() {
    apiCall('admin_get_settings').then(d => {
        if (d.status === 'ok' && d.data) {
            const el = document.getElementById('chk_enable_passkey');
            if(el) el.checked = (d.data.enable_passkey == '1');
        }
    });
}

export function savePasskey() {
    const enabled = document.getElementById('chk_enable_passkey').checked ? '1' : '0';

    apiCall('admin_save_settings', {
        enable_passkey: enabled
    }).then(d => {
        if (d.status === 'ok') alert('تنظیمات Passkey بروزرسانی شد.');
        else alert('خطا در ذخیره‌سازی');
    });
}