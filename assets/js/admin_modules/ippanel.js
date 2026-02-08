import { apiCall } from './api.js';

export function loadIPPanel() {
    apiCall('admin_get_settings').then(d => {
        if (d.status === 'ok' && d.data) {
            const k = document.getElementById('inp_ippanel_key');
            const l = document.getElementById('inp_ippanel_line');
            if(k) k.value = d.data.ippanel_key || '';
            if(l) l.value = d.data.ippanel_line || '';
        }
    });
}

export function saveIPPanel() {
    const k = document.getElementById('inp_ippanel_key').value;
    const l = document.getElementById('inp_ippanel_line').value;

    apiCall('admin_save_settings', {
        ippanel_key: k,
        ippanel_line: l
    }).then(d => {
        if (d.status === 'ok') alert('تنظیمات پیامک با موفقیت ذخیره شد.');
        else alert('خطا در ذخیره‌سازی');
    });
}