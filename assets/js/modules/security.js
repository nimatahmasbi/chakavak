import { apiCall } from './api.js';

// --- توابع کمکی تبدیل داده (برای Passkey) ---
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
    
    // نمایش لودینگ
    box.innerHTML = '<div class="text-center text-gray-500 py-4 text-sm">درحال دریافت اطلاعات امنیتی...</div>';

    apiCall('get_security_status').then(d => {
        if (d.status !== 'ok') {
            box.innerHTML = '<div class="text-center text-red-500 text-sm py-4">خطا در دریافت اطلاعات.<br>لطفاً مجدد تلاش کنید.</div>';
            return;
        }

        let html = '';

        // 1. بخش تایید دو مرحله‌ای (2FA)
        if (d.system_2fa) {
            const btnColor = d.user_2fa ? 'bg-red-50 text-red-600 border-red-200' : 'bg-green-50 text-green-600 border-green-200';
            const btnText = d.user_2fa ? 'غیرفعال‌سازی' : 'فعال‌سازی';
            const statusText = d.user_2fa ? '<span class="text-green-600 font-bold">فعال</span>' : '<span class="text-gray-400">غیرفعال</span>';

            html += `
            <div class="mb-6 border-b border-gray-100 pb-4">
                <div class="flex justify-between items-center mb-2">
                    <h4 class="font-bold text-gray-700 text-sm flex items-center gap-2">🛡️ تایید دو مرحله‌ای</h4>
                    <span class="text-xs">${statusText}</span>
                </div>
                <p class="text-xs text-gray-500 mb-3 leading-5">با فعال‌سازی این گزینه، هنگام ورود به حساب کاربری کد تایید پیامک خواهد شد.</p>
                <button onclick="toggle2FA(${d.user_2fa ? 0 : 1})" class="w-full border py-2 rounded-lg text-sm font-bold transition ${btnColor}">${btnText}</button>
            </div>`;
        }

        // 2. بخش Passkey (اثر انگشت / چهره)
        if (d.system_passkey) {
            html += `
            <div class="mb-2">
                <h4 class="font-bold text-gray-700 text-sm mb-2 flex items-center gap-2">🔑 کلیدهای عبور (Passkeys)</h4>
                <p class="text-xs text-gray-500 mb-3 leading-5">ورود امن و سریع بدون نیاز به رمز عبور با استفاده از اثر انگشت یا تشخیص چهره.</p>
                
                <div class="space-y-2 mb-3">`;
                
            if (d.passkeys && d.passkeys.length > 0) {
                d.passkeys.forEach(k => {
                    html += `
                    <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg border border-gray-100 text-sm">
                        <span class="truncate max-w-[160px] text-gray-700">📱 ${k.name}</span>
                        <button onclick="deletePasskey(${k.id})" class="text-red-500 text-xs bg-white border border-red-100 px-2 py-1 rounded hover:bg-red-50 transition">حذف</button>
                    </div>`;
                });
            } else {
                html += `<div class="text-center text-xs text-gray-400 py-2 bg-gray-50 rounded-lg border border-dashed border-gray-200">هنوز دستگاهی ثبت نشده است</div>`;
            }

            html += `</div>
                <button onclick="registerPasskey()" class="w-full bg-blue-600 text-white py-2.5 rounded-lg text-sm font-bold shadow-sm hover:bg-blue-700 transition flex items-center justify-center gap-2">
                    <span>+</span> افزودن دستگاه جدید
                </button>
            </div>`;
        }

        // اگر هر دو غیرفعال باشند
        if (!d.system_2fa && !d.system_passkey) {
            html = '<div class="text-center text-gray-400 py-6 text-sm bg-gray-50 rounded-lg">امکانات امنیتی توسط مدیر سیستم غیرفعال شده است.</div>';
        }

        box.innerHTML = html;
    }).catch(err => {
        console.error(err);
        box.innerHTML = '<div class="text-center text-red-500 text-sm py-4">خطای ارتباط با سرور</div>';
    });
}

// --- تغییر وضعیت 2FA ---
export function toggle2FA(enable) {
    const box = document.getElementById('securitySettingsBox');
    if(box) box.style.opacity = '0.5'; // افکت لودینگ
    
    apiCall('toggle_2fa', { enable: enable }).then(d => {
        if(box) box.style.opacity = '1';
        
        if (d.status === 'ok') {
            loadSecuritySettings(); // رفرش لیست
        } else {
            alert(d.msg || 'خطا در تغییر وضعیت');
        }
    });
}

// --- ثبت Passkey جدید ---
export async function registerPasskey() {
    if (!window.PublicKeyCredential) return alert("مرورگر یا دستگاه شما از Passkey پشتیبانی نمی‌کند.");

    // 1. دریافت Challenge از سرور
    const start = await apiCall('passkey_register_start');
    if (start.status !== 'ok') return alert('خطا در شروع فرآیند ثبت: ' + (start.msg || 'Unknown'));

    try {
        // 2. ایجاد کلید در مرورگر
        const credential = await navigator.credentials.create({
            publicKey: {
                challenge: strToBuffer(start.challenge),
                rp: { name: "Chakavak App" },
                user: {
                    id: strToBuffer(start.user.id.toString()),
                    name: start.user.name,
                    displayName: start.user.displayName
                },
                pubKeyCredParams: [{ alg: -7, type: "public-key" }, { alg: -257, type: "public-key" }],
                authenticatorSelection: { authenticatorAttachment: "platform", userVerification: "preferred" },
                timeout: 60000,
                attestation: "direct"
            }
        });

        // 3. ارسال نتیجه به سرور
        const finish = await apiCall('passkey_register_finish', {
            credential_id: bufferToStr(credential.rawId),
            public_key: JSON.stringify(credential.response),
            device_name: 'Device ' + new Date().toLocaleDateString('fa-IR')
        });

        if (finish.status === 'ok') {
            alert('دستگاه با موفقیت افزوده شد!');
            loadSecuritySettings();
        } else {
            alert('خطا در ثبت نهایی: ' + finish.msg);
        }

    } catch (e) {
        console.error(e);
        if (e.name !== 'NotAllowedError') {
            alert('عملیات لغو شد یا خطا رخ داد.');
        }
    }
}

// --- حذف Passkey ---
export function deletePasskey(id) {
    if (confirm('آیا مطمئن هستید که می‌خواهید این دستگاه را حذف کنید؟')) {
        apiCall('delete_passkey', { key_id: id }).then(() => loadSecuritySettings());
    }
}

// --- اتصال به Window (حیاتی) ---
window.loadSecuritySettings = loadSecuritySettings;
window.toggle2FA = toggle2FA;
window.registerPasskey = registerPasskey;
window.deletePasskey = deletePasskey;