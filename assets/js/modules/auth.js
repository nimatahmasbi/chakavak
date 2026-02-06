import { apiCall } from './api.js';
import { openModal, closeModal } from './ui.js';
// نکته: import { openChat } را حذف کردیم

export function loadContacts() {
    let listEl = document.getElementById('contactList');
    if(!listEl) return;
    listEl.innerHTML = '...';
    
    apiCall('get_contacts').then(d => {
        let h = '';
        d.list.forEach(u => {
            let av = u.avatar || 'assets/img/chakavak.png';
            // استفاده از window.startDirect بجای تابع داخلی
            h += `<div onclick="startDirect(${u.id},'${u.first_name} ${u.last_name}','${av}')" class="flex items-center p-3 hover:bg-[var(--bg-secondary)] rounded cursor-pointer border-b border-[var(--border-color)]">
                <img src="${av}" class="w-10 h-10 rounded-full mx-3 object-cover">
                <div><div class="font-bold text-[var(--text-primary)]">${u.first_name} ${u.last_name}</div><div class="text-xs text-[var(--text-secondary)]">@${u.username}</div></div></div>`;
        });
        listEl.innerHTML = h || '<div class="text-center text-gray-500 mt-4">مخاطبی یافت نشد</div>';
    });
}

export function addNewContact() {
    let q = document.getElementById('newContactInput').value;
    if (!q) return alert('نام کاربری یا شماره را وارد کنید');
    
    apiCall('search_contact', { query: q }).then(d => {
        if (d.status == 'ok') {
            closeModal('contactModal');
            // استفاده از window.openChat برای جلوگیری از ایمپورت مستقیم
            if(window.openChat) window.openChat(d.user.id, 'dm', d.user.first_name + ' ' + d.user.last_name, d.user.avatar);
            loadContacts();
        } else {
            alert('کاربر یافت نشد');
        }
    });
}

export function startDirect(uid, name, av) {
    closeModal('contactModal');
    closeModal('publicProfileModal');
    if(window.openChat) window.openChat(uid, 'dm', name, av);
}

export function showPublicProfile(uid) {
    apiCall('get_user_info', { uid }).then(d => {
        if(d.status !== 'ok') return;
        let u = d.data;
        document.getElementById('pubAvatar').src = u.avatar;
        document.getElementById('pubName').innerText = u.first_name + ' ' + u.last_name;
        document.getElementById('pubUser').innerText = '@' + u.username;
        document.getElementById('pubBio').innerText = u.bio || '';
        openModal('publicProfileModal');
    });
}

export function saveProfile() {
    let fd = new FormData();
    const fields = ['setFname', 'setLname', 'setUname', 'setBio'];
    fields.forEach(id => {
        let el = document.getElementById(id);
        if (el) fd.append(id.replace('set', '').toLowerCase().replace('fname', 'fname'), el.value);
    });
    apiCall('update_profile', fd, true).then(d => {
        if (d.status == 'ok') location.reload(); else alert(d.msg);
    });
}

export function logout() {
    if (confirm('خروج؟')) apiCall('logout').then(() => window.location.href = 'index.php');
}

window.loadContacts = loadContacts;
window.addNewContact = addNewContact;
window.startDirect = startDirect;
window.showPublicProfile = showPublicProfile;
window.saveProfile = saveProfile;
window.logout = logout;