import { apiCall } from './api.js';

export function loadSettings() {
    apiCall('admin_get_settings').then(d => {
        if (d.status === 'ok' && d.data) {
            setValue('set_ippanel_key', d.data.ippanel_key);
            setValue('set_ippanel_line', d.data.ippanel_line);
            setChecked('set_enable_2fa', d.data.enable_2fa == '1');
            setChecked('set_enable_passkey', d.data.enable_passkey == '1');
        }
    });
}

export function saveSettings() {
    const data = {
        ippanel_key: getValue('set_ippanel_key'),
        ippanel_line: getValue('set_ippanel_line'),
        enable_2fa: getChecked('set_enable_2fa') ? '1' : '0',
        enable_passkey: getChecked('set_enable_passkey') ? '1' : '0'
    };

    apiCall('admin_save_settings', data).then(d => {
        if (d.status === 'ok') {
            alert('تنظیمات با موفقیت ذخیره شد.');
        } else {
            alert('خطا در ذخیره سازی');
        }
    });
}

// توابع کمکی داخلی
function getValue(id) { const el = document.getElementById(id); return el ? el.value : ''; }
function setValue(id, val) { const el = document.getElementById(id); if(el) el.value = val || ''; }
function getChecked(id) { const el = document.getElementById(id); return el ? el.checked : false; }
function setChecked(id, val) { const el = document.getElementById(id); if(el) el.checked = val; }