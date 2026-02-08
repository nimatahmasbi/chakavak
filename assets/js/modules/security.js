import { apiCall } from './api.js';

// تبدیل رشته به بافر (برای Passkey)
function strToBuffer(str) {
    return Uint8Array.from(atob(str), c => c.charCodeAt(0));
}
function bufferToStr(buf) {
    return btoa(String.fromCharCode(...new Uint8Array(buf)));
}

// --- بارگذاری وضعیت امنیت ---
export function loadSecuritySettings() {
    const box = document.getElementById('securitySettingsBox');
    if (!box) return;
    
    box.innerHTML = '<div class="text-center text-gray-500">درحال دریافت اطلاعات...</div>';

    apiCall('get_security_status').then(d => {
        if (d.status !== 'ok') return;

        let html = '';

        // 1. بخش 2FA
        if (d.system_2fa) {
            const btnColor = d.user_2fa ? 'bg-red-50 text-red-600 border-red-200' : 'bg-green-50 text-green-600 border-green-200';
            const btnText = d.user_2fa ? 'غیرفعال‌سازی' : 'فعال‌سازی';
            const statusText = d.user_2fa ? '<span class="text-green-600 font-bold">فعال</span>' : '<span class="text-gray-500">غیرفعال</span>';

            html += `
            <div class="mb-6 border-b pb-4">
                <div class="flex justify-between items-center mb-2">
                    <h4 class="font-bold text-gray-700 flex items-center gap-2">🛡️ تایید دو مرحله‌ای (پیامک)</h4>
                    ${statusText}
                </div>
                <p class="text-xs text-gray-500 mb-3">با فعال‌سازی این گزینه، هنگام ورود پیامک تایید ارسال می‌شود.</p>
                <button onclick="toggle2FA(${d.user_2fa ? 0 : 1})" class="w-full border py-2 rounded-lg text-sm font-bold ${btnColor}">${btnText}</button>
            </div>`;
        }

        // 2. بخش Passkey
        if (d.system_passkey) {
            html += `
            <div class="mb-4">
                <h4 class="font-bold text-gray-700 flex items-center gap-2 mb-2">🔑 کلیدهای عبور (Passkeys)</h4>
                <p class="text-xs text-gray-500 mb-3">ورود سریع و امن بدون رمز عبور با اثر انگشت یا تشخیص چهره.</p>
                
                <div class="space-y-2 mb-3">`;
                
            if (d.passkeys.length > 0) {
                d.passkeys.forEach(k => {
                    html += `
                    <div class="flex justify-between items-center bg-gray-50 p-2 rounded border text-sm">
                        <span>📱 ${k.name}</span>
                        <button onclick="deletePasskey(${k.id})" class="text-red-500 text-xs">حذف</button>
                    </div>`;
                });
            } else {
                html += `<div class="text-center text-xs text-gray-400 py-2">هنوز کلیدی ثبت نشده است</div>`;
            }

            html += `</div>
                <button onclick="registerPasskey()" class="w-full bg-gray-800 text-white py-2 rounded-lg text-sm font-bold shadow hover:bg-black transition">+ افزودن دستگاه جدید</button>
            </div>`;
        }

        if (!d.system_2fa && !d.system_passkey) {
            html = '<div class="text-center text-gray-400 py-4">امکانات امنیتی توسط مدیر غیرفعال شده است.</div>';
        }

        box.innerHTML = html;
    });
}

// --- عملیات 2FA ---
export function toggle2FA(enable) {
    apiCall('toggle_2fa', { enable: enable }).then(d => {
        if (d.status === 'ok') loadSecuritySettings();
    });
}

// --- عملیات Passkey ---
export async function registerPasskey() {
    if (!window.PublicKeyCredential) return alert("دستگاه شما از Passkey پشتیبانی نمی‌کند.");

    // 1. دریافت Challenge از سرور
    const start = await apiCall('passkey_register_start');
    if (start.status !== 'ok') return alert('خطا در شروع ثبت');

    try {
        // 2. ساخت کلید در مرورگر
        const credential = await navigator.credentials.create({
            publicKey: {
                challenge: strToBuffer(start.challenge),
                rp: { name: "Chakavak App" },
                user: {
                    id: strToBuffer(start.user.id.toString()),
                    name: start.user.name,
                    displayName: start.user.displayName
                },
                pubKeyCredParams: [{ alg: -7, type: "public-key" }],
                authenticatorSelection: { authenticatorAttachment: "platform" },
                timeout: 60000,
                attestation: "direct"
            }
        });

        // 3. ارسال نتیجه به سرور
        const finish = await apiCall('passkey_register_finish', {
            credential_id: bufferToStr(credential.rawId),
            public_key: JSON.stringify(credential.response), // ذخیره ساده برای دمو
            device_name: 'Device ' + new Date().toLocaleDateString()
        });

        if (finish.status === 'ok') {
            alert('دستگاه با موفقیت افزوده شد!');
            loadSecuritySettings();
        } else {
            alert('خطا در ثبت نهایی');
        }

    } catch (e) {
        console.error(e);
        alert('ثبت لغو شد یا خطا رخ داد.');
    }
}

export function deletePasskey(id) {
    if (confirm('حذف شود؟')) {
        apiCall('delete_passkey', { key_id: id }).then(() => loadSecuritySettings());
    }
}

// اتصال به Window
window.loadSecuritySettings = loadSecuritySettings;
window.toggle2FA = toggle2FA;
window.registerPasskey = registerPasskey;
window.deletePasskey = deletePasskey;