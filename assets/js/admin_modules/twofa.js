import { apiCall } from './api.js';

export function load2FA() {
    apiCall('admin_get_settings').then(d => {
        if (d.status === 'ok' && d.data) {
            const el = document.getElementById('chk_enable_2fa');
            if(el) el.checked = (d.data.enable_2fa == '1');
        }
    });
}

export function save2FA() {
    const enabled = document.getElementById('chk_enable_2fa').checked ? '1' : '0';

    apiCall('admin_save_settings', {
        enable_2fa: enabled
    }).then(d => {
        if (d.status === 'ok') alert('تنظیمات امنیتی 2FA بروزرسانی شد.');
        else alert('خطا در ذخیره‌سازی');
    });
}