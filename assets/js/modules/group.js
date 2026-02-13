import { apiCall } from './api.js';
import { closeModal } from './ui.js';
import { loadChats } from './chat.js';
import { state } from './state.js';
import { loadContacts } from './auth.js';

// --- ساخت گروه ---
export function doCreate() {
    const input = document.getElementById('createInput');
    const name = input ? input.value : '';
    if (!name.trim()) return alert('نام وارد کنید');
    apiCall('create_group', { name: name, gtype: state.createType || 'group' }).then(d => {
        if (d.status === 'ok') { closeModal('createModal'); loadChats(); if(input) input.value=''; } 
        else alert(d.msg || 'خطا');
    });
}

// --- ویرایش گروه ---
export function saveGroupEdit() {
    if (!state.currChat) return;
    const name = document.getElementById('gInfoNameEdit').value;
    const file = document.getElementById('gInfoAvatarInput').files[0];
    const fd = new FormData();
    fd.append('group_id', state.currChat.id); fd.append('name', name);
    if (file) fd.append('avatar', file);
    apiCall('edit_group', fd, true).then(d => { if (d.status === 'ok') location.reload(); });
}

// --- افزودن عضو (از اینپوت) ---
export function addMemberFromInput() {
    if (!state.currChat) return;
    const input = document.getElementById('addMemberInput');
    const target = input.value;
    if (!target.trim()) return alert('نام کاربری یا شماره را وارد کنید');

    apiCall('add_group_member', { group_id: state.currChat.id, target: target }).then(d => {
        if(d.status === 'ok') {
            alert('عضو اضافه شد');
            input.value = '';
            if(window.clickHeader) window.clickHeader(); // رفرش
        } else {
            alert(d.msg || 'کاربر یافت نشد');
        }
    });
}

// --- باز کردن لیست مخاطبین برای افزودن ---
export function openContactPickerForGroup() {
    // تنظیم فلگ که نشان می‌دهد داریم عضو اضافه می‌کنیم نه چت جدید
    state.isAddingToGroup = true;
    
    // باز کردن مودال مخاطبین
    if(window.openModal) window.openModal('contactModal');
    if(window.loadContacts) window.loadContacts();
}

// --- حذف عضو ---
export function removeMember(uid) {
    if(!confirm('حذف شود؟')) return;
    apiCall('remove_group_member', { group_id: state.currChat.id, user_id: uid }).then(d => {
        if(d.status === 'ok') { if(window.clickHeader) window.clickHeader(); } 
        else alert(d.msg);
    });
}

window.doCreate = doCreate;
window.saveGroupEdit = saveGroupEdit;
window.addMemberFromInput = addMemberFromInput;
window.openContactPickerForGroup = openContactPickerForGroup;
window.removeMember = removeMember;