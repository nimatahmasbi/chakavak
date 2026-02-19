import { loadDashboardData } from './admin_modules/list.js';
import { loadUsers } from './admin_modules/users.js';
import { loadGroups } from './admin_modules/groups.js';
import { loadSettings } from './admin_modules/settings.js';

// اجرای کد پس از لود کامل صفحه
document.addEventListener('DOMContentLoaded', () => {
    
    // گرفتن نام صفحه از ویژگی data-page در تگ body
    const page = document.body.getAttribute('data-page');
    console.log('Admin Page Loaded:', page);

    if (page === 'dashboard') {
        loadDashboardData();
    } else if (page === 'users') {
        loadUsers();
    } else if (page === 'groups') {
        loadGroups();
    } else if (page === 'settings') {
        loadSettings();
    }

    // مدیریت منوی موبایل (باز و بسته کردن سایدبار)
    const btn = document.getElementById('menuBtn');
    const sidebar = document.getElementById('sidebar');
    if(btn && sidebar) {
        btn.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            // در حالت موبایل، ترنسلیت را برمی‌داریم تا دیده شود
            sidebar.classList.toggle('translate-x-0'); 
            // با توجه به کلاس‌های تیلویند ممکن است نیاز به تنظیم دقیق باشد
            // اما همین toggle کافیست اگر کلاس‌ها درست باشند
        });
    }
});