import { loadList } from './list.js';
import { loadIPPanel } from './ippanel.js';
import { load2FA } from './twofa.js';
import { loadPasskey } from './passkey.js';

export async function loadSection(name) {
    // 1. آپدیت دکمه‌ها
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white');
        btn.classList.add('bg-white', 'text-gray-700');
    });
    
    const activeBtn = document.getElementById('btn-' + name);
    if (activeBtn) {
        activeBtn.classList.remove('bg-white', 'text-gray-700');
        activeBtn.classList.add('bg-blue-600', 'text-white');
    }

    // 2. لود قالب HTML
    const contentArea = document.getElementById('content-area');
    contentArea.innerHTML = '<div class="text-center py-10 text-gray-500">در حال بارگذاری...</div>';

    try {
        const response = await fetch(`sections/${name}.php`);
        if (!response.ok) throw new Error("فایل قالب یافت نشد: " + name);
        
        const html = await response.text();
        contentArea.innerHTML = html;

        // 3. اجرای تابع لودر مربوطه
        switch (name) {
            case 'users':
            case 'groups':
                loadList(name);
                break;
            case 'ippanel':
                loadIPPanel();
                break;
            case 'twofa':
                load2FA();
                break;
            case 'passkey':
                loadPasskey();
                break;
        }

    } catch (error) {
        console.error(error);
        contentArea.innerHTML = `<div class="bg-red-100 text-red-600 p-4 rounded text-center">خطا: ${error.message}</div>`;
    }
}