import { state } from './state.js';
import { apiCall } from './api.js';
import { openModal, closeModal } from './ui.js';
import { clickHeader, loadChats } from './chat.js'; 

// ساخت گروه
export function doCreate() {
    let name = document.getElementById('createName').value;
    if(!name || !name.trim()) return alert('نام را وارد کنید');

    apiCall('create_group', { name: name, gtype: state.createType }).then(d => {
        if (d.status == 'ok') {
            closeModal('createModal');
            document.getElementById('createName').value = ''; // پاک کردن فرم
            loadChats(); // رفرش لیست چت
        } else {
            alert(d.msg || 'Error');
        }
    });
}

// ویرایش گروه
export function toggleEditGroup() {
    let box = document.getElementById('editGroupBox');
    if(box) box.classList.toggle('hidden');
}

export function saveGroupEdit() {
    let name = document.getElementById('editGName').value;
    let file = document.getElementById('editGFile').files[0];
    
    let fd = new FormData();
    fd.append('group_id', state.currChat.id);
    fd.append('name', name);
    if (file) fd.append('avatar', file);

    apiCall('edit_group', fd, true).then(d => {
        if (d.status == 'ok') {
            alert('Saved');
            clickHeader(); // رفرش اطلاعات هدر
            toggleEditGroup();
        } else {
            alert(d.msg);
        }
    });
}

// مدیریت اعضا
export function addMember() {
    let target = document.getElementById('addInput').value;
    apiCall('add_group_member', { group_id: state.currChat.id, target: target }).then(d => {
        if (d.status == 'ok') {
            clickHeader(); 
            document.getElementById('addInput').value = '';
        } else {
            alert(d.msg || 'User not found');
        }
    });
}

export function removeMember(uid) {
    if (confirm('Remove user?')) {
        apiCall('remove_group_member', { group_id: state.currChat.id, user_id: uid }).then(() => clickHeader());
    }
}

// --- اتصال به Window ---
window.doCreate = doCreate;
window.toggleEditGroup = toggleEditGroup;
window.saveGroupEdit = saveGroupEdit;
window.addMember = addMember;
window.removeMember = removeMember;