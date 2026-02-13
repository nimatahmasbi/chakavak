import { apiCall } from '../api.js';

// --- توابع کمکی تبدیل بافر ---
function strToBuffer(str) { return Uint8Array.from(atob(str), c => c.charCodeAt(0)); }
function bufferToStr(buf) { return btoa(String.fromCharCode(...new Uint8Array(buf))); }

// --- تولید HTML ---
export function renderPasskeys(data) {
    if (!data.system_passkey) return '';

    let html = `
    <div class="mb-2">
        <h4 class="font-bold text-gray-700 text-sm mb-2 flex items-center gap-2">🔑 کلیدهای عبور (Passkeys)</h4>
        <div class="space-y-2 mb-3">`;

    if (data.passkeys && data.passkeys.length > 0) {
        data.passkeys.forEach(k => {
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
    
    return html;
}

// --- عملیات ثبت Passkey ---
export async function registerPasskey() {
    if (!window.PublicKeyCredential) return alert("مرورگر شما پشتیبانی نمی‌کند.");

    const start = await apiCall('passkey_register_start');
    if (start.status !== 'ok') return alert('خطا در شروع ثبت: ' + (start.msg || 'Unknown'));

    try {
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

        const finish = await apiCall('passkey_register_finish', {
            credential_id: bufferToStr(credential.rawId),
            public_key: JSON.stringify(credential.response),
            device_name: 'Device ' + new Date().toLocaleDateString('fa-IR')
        });

        if (finish.status === 'ok') {
            alert('دستگاه افزوده شد!');
            if(window.loadSecuritySettings) window.loadSecuritySettings();
        } else {
            alert('خطا در ثبت نهایی');
        }

    } catch (e) {
        console.error(e);
    }
}

// --- حذف Passkey ---
export function deletePasskey(id) {
    if (confirm('حذف شود؟')) {
        apiCall('delete_passkey', { key_id: id }).then(() => {
            if(window.loadSecuritySettings) window.loadSecuritySettings();
        });
    }
}