import { apiCall } from './api.js';
// ایمپورت از زیرماژول‌ها
import { render2FA, toggle2FA } from './security/twofa.js';
import { renderPasskeys, registerPasskey, deletePasskey } from './security/passkey.js';

// --- تابع اصلی لود کردن تنظیمات ---
export function loadSecuritySettings() {
    const box = document.getElementById('securitySettingsBox');
    if (!box) return;
    
    box.innerHTML = '<div class="text-center text-gray-500 py-4 text-sm">درحال دریافت اطلاعات امنیتی...</div>';

    apiCall('get_security_status').then(d => {
        if (!d || d.status !== 'ok') {
            box.innerHTML = '<div class="text-center text-red-500 text-sm py-4">خطا در دریافت اطلاعات.<br>لطفاً مجدد تلاش کنید.</div>';
            return;
        }

        let html = '';
        
        // 1. رندر کردن 2FA
        html += render2FA(d);
        
        // 2. رندر کردن Passkeys
        html += renderPasskeys(d);

        if (!d.system_2fa && !d.system_passkey) {
            html = '<div class="text-center text-gray-400 py-6 text-sm bg-gray-50 rounded-lg">امکانات امنیتی توسط مدیر غیرفعال شده است.</div>';
        }

        box.innerHTML = html;
    }).catch(err => {
        console.error(err);
        box.innerHTML = '<div class="text-center text-red-500 text-sm py-4">خطای شبکه</div>';
    });
}

// --- اتصال توابع به Window (برای اینکه دکمه‌های HTML کار کنند) ---
window.loadSecuritySettings = loadSecuritySettings;
window.toggle2FA = toggle2FA;
window.registerPasskey = registerPasskey;
window.deletePasskey = deletePasskey;