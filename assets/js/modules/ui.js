import { state } from './state.js';
import { loadSecuritySettings } from './security.js';
import { loadMyProfile } from './auth.js';
import { updateUiStatus } from './notify.js';

// --- مدیریت باز کردن مودال‌ها ---
export function openModal(id) {
    let el = document.getElementById(id);
    if (el) {
        el.classList.remove('hidden');
        
        // اگر مودال تنظیمات باز شد
        if (id == 'settingsModal') {
            // بازیابی تب فعال قبلی از حافظه یا انتخاب پیش‌فرض 'general'
            const savedTab = localStorage.getItem('lastSettingsTab') || 'general';
            switchSettingsTab(savedTab);
            
            // هماهنگ‌سازی دکمه‌ها (مثل تم شب) با وضعیت واقعی سیستم
            syncSettingsUI(); 
            
            // بررسی وضعیت نوتیفیکیشن با کمی تاخیر
            setTimeout(() => { 
                if(window.checkNotifPermission) window.checkNotifPermission(); 
            }, 200);
        }
    }
}

// --- بستن مودال ---
export function closeModal(id) {
    let el = document.getElementById(id);
    if (el) el.classList.add('hidden');
}

// --- باز کردن مودال ساخت گروه/کانال ---
export function openCreateModal(type) {
    state.createType = type;
    let t = document.getElementById('createTitle');
    // تغییر عنوان مودال بر اساس نوع انتخاب شده
    if(t) {
        t.innerText = (type == 'channel' ? 'ساخت کانال جدید' : 'ساخت گروه جدید');
    }
    openModal('createModal');
}

// --- تابع کمکی برای هماهنگی وضعیت دکمه‌ها (مثل تاگل شب/روز) ---
function syncSettingsUI() {
    const isDark = document.documentElement.classList.contains('dark') || localStorage.getItem('theme') === 'dark';
    const themeSw = document.getElementById('themeSwitch');
    if(themeSw) themeSw.checked = isDark;
}

// --- سوییچ بین تب‌های تنظیمات ---
export function switchSettingsTab(tab) {
    // ذخیره تب فعلی در حافظه تا بعد از رفرش روی همین تب بماند
    localStorage.setItem('lastSettingsTab', tab);

    // مخفی کردن محتوای همه تب‌ها
    document.querySelectorAll('[id^="set-tab-"]').forEach(el => el.classList.add('hidden'));
    
    // نمایش محتوای تب انتخاب شده
    let target = document.getElementById('set-tab-' + tab);
    if(target) target.classList.remove('hidden');
    
    // غیرفعال کردن استایل همه دکمه‌های تب
    document.querySelectorAll('[id^="tab-btn-"]').forEach(btn => {
        btn.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600', 'bg-blue-50', 'dark:bg-gray-700', 'dark:text-blue-400');
        btn.classList.add('text-gray-500', 'dark:text-gray-400');
    });
    
    // فعال کردن استایل دکمه تب انتخاب شده
    let activeBtn = document.getElementById('tab-btn-' + tab);
    if(activeBtn) {
        activeBtn.classList.remove('text-gray-500', 'dark:text-gray-400');
        activeBtn.classList.add('text-blue-600', 'border-b-2', 'border-blue-600', 'bg-blue-50', 'dark:bg-gray-700', 'dark:text-blue-400');
    }

    // لود کردن اطلاعات مربوط به تب‌های خاص (پروفایل یا امنیت)
    if(tab === 'security') {
        loadSecuritySettings();
    }
    if(tab === 'profile') {
        loadMyProfile();
    }
}

// --- تغییر حالت شب و روز ---
export function toggleTheme() {
    const html = document.documentElement;
    const isDark = html.classList.contains('dark');
    const sw = document.getElementById('themeSwitch');

    if (isDark) {
        html.classList.remove('dark'); 
        localStorage.setItem('theme', 'light'); 
        document.cookie = "theme=light; path=/; max-age=31536000"; 
        if(sw) sw.checked = false;
    } else {
        html.classList.add('dark'); 
        localStorage.setItem('theme', 'dark'); 
        document.cookie = "theme=dark; path=/; max-age=31536000"; 
        if(sw) sw.checked = true;
    }
}

// --- تغییر زبان ---
export function toggleLang() {
    let cur = localStorage.getItem('lang') || 'fa'; 
    let next = cur === 'fa' ? 'en' : 'fa';
    
    // ذخیره زبان جدید
    localStorage.setItem('lang', next); 
    document.cookie = `lang=${next}; path=/; max-age=31536000`;
    
    // نشانه گذاری برای اینکه بعد از ریلود، تنظیمات دوباره باز شود
    localStorage.setItem('reopenSettings', 'true');
    
    location.reload();
}

// --- بروزرسانی برنامه و پاکسازی کش ---
export async function appUpdate() {
    if(!confirm('آیا می‌خواهید برنامه را بروزرسانی کنید؟ (کش مرورگر پاک می‌شود)')) return;
    
    try {
        // حذف سرویس ورکرها
        if ('serviceWorker' in navigator) { 
            const regs = await navigator.serviceWorker.getRegistrations(); 
            for (let r of regs) await r.unregister(); 
        }
        
        // حذف کش‌های ذخیره شده
        if ('caches' in window) { 
            const keys = await caches.keys(); 
            await Promise.all(keys.map(k => caches.delete(k))); 
        }
        
        // حذف کش لیست چت
        localStorage.removeItem('chatListCache'); 
        
        // ریلود اجباری
        location.reload(true);
    } catch (e) { 
        console.error(e);
        location.reload(); 
    }
}

// اتصال توابع به شیء سراسری window
window.openModal = openModal;
window.closeModal = closeModal;
window.openCreateModal = openCreateModal;
window.switchSettingsTab = switchSettingsTab;
window.toggleTheme = toggleTheme;
window.toggleLang = toggleLang;
window.appUpdate = appUpdate;