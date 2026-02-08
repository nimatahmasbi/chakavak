import { loadSection } from './admin_modules/ui.js';
import { loadList } from './admin_modules/list.js';
import { toggleUserStatus } from './admin_modules/users.js';
import { banGroup, deleteGroup } from './admin_modules/groups.js';
import { openChatModal, sendDm } from './admin_modules/dm.js';

// ماژول‌های جدید
import { saveIPPanel } from './admin_modules/ippanel.js';
import { save2FA } from './admin_modules/twofa.js';
import { savePasskey } from './admin_modules/passkey.js';

// اتصال به Window
window.loadSection = loadSection;
window.loadList = loadList;
window.toggleUserStatus = toggleUserStatus;
window.banGroup = banGroup;
window.deleteGroup = deleteGroup;
window.openChatModal = openChatModal;
window.sendDm = sendDm;

// اتصال توابع ذخیره‌سازی جدید
window.saveIPPanel = saveIPPanel;
window.save2FA = save2FA;
window.savePasskey = savePasskey;

// اجرای اولیه
document.addEventListener('DOMContentLoaded', () => {
    loadSection('users');
    
    // بستن مودال چت با Enter
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && e.target.id === 'dmInput') {
            sendDm();
        }
    });
});