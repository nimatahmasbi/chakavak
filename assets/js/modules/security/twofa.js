import { apiCall } from '../api.js';

// --- تولید HTML ---
export function render2FA(data) {
    if (!data.system_2fa) return '';

    const isActive = data.user_2fa == 1;
    const btnColor = isActive ? 'bg-red-50 text-red-600 border-red-200' : 'bg-green-50 text-green-600 border-green-200';
    const btnText = isActive ? 'غیرفعال‌سازی' : 'فعال‌سازی';
    const statusText = isActive ? '<span class="text-green-600 font-bold">فعال</span>' : '<span class="text-gray-400">غیرفعال</span>';

    return `
    <div class="mb-6 border-b border-gray-100 pb-4">
        <div class="flex justify-between items-center mb-2">
            <h4 class="font-bold text-gray-700 text-sm flex items-center gap-2">🛡️ تایید دو مرحله‌ای</h4>
            <span class="text-xs">${statusText}</span>
        </div>
        <p class="text-xs text-gray-500 mb-3 leading-5">هنگام ورود کد تایید پیامک می‌شود.</p>
        <button onclick="toggle2FA(${isActive ? 0 : 1})" class="w-full border py-2 rounded-lg text-sm font-bold transition ${btnColor}">${btnText}</button>
    </div>`;
}

// --- عملیات تغییر وضعیت ---
export function toggle2FA(enable) {
    const box = document.getElementById('securitySettingsBox');
    if(box) box.style.opacity = '0.5';
    
    apiCall('toggle_2fa', { enable: enable }).then(d => {
        if(box) box.style.opacity = '1';
        if (d.status === 'ok') {
            // رفرش کردن لیست (تابع گلوبال در security.js تعریف می‌شود)
            if(window.loadSecuritySettings) window.loadSecuritySettings();
        } else {
            alert(d.msg || 'خطا');
        }
    });
}