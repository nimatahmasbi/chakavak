import { apiCall } from './api.js';
import { openModal, closeModal } from './ui.js';
import { state } from './state.js';

let currentProfileId = null;

// --- لود کردن پروفایل من (در تنظیمات) ---
export function loadMyProfile() {
    const fname = document.getElementById('set_prof_fname');
    if(fname) fname.value = '...';
    
    apiCall('get_user_info').then(d => {
        if (d.status === 'ok') {
            const u = d.data;
            setValue('set_prof_fname', u.first_name);
            setValue('set_prof_lname', u.last_name);
            setValue('set_prof_user', u.username);
            setValue('set_prof_bio', u.bio);
            setValue('set_prof_tele', u.social_telegram);
            setValue('set_prof_insta', u.social_instagram);
            
            const av = u.avatar && u.avatar !== 'default' ? u.avatar : 'assets/img/chakavak.png';
            const img = document.getElementById('set_prof_avatar');
            if(img) img.src = av;
        }
    });
}

// --- ذخیره تغییرات پروفایل ---
export function saveMyProfile() {
    const fd = new FormData();
    fd.append('fname', getValue('set_prof_fname'));
    fd.append('lname', getValue('set_prof_lname'));
    fd.append('uname', getValue('set_prof_user'));
    fd.append('bio', getValue('set_prof_bio'));
    fd.append('tele', getValue('set_prof_tele'));
    fd.append('insta', getValue('set_prof_insta'));
    fd.append('whats', ''); 
    fd.append('linked', '');
    
    const file = document.getElementById('set_prof_file').files[0];
    if (file) {
        fd.append('avatar', file);
    }

    apiCall('update_profile', fd, true).then(d => {
        if (d.status === 'ok') {
            alert('تغییرات ذخیره شد');
            location.reload(); 
        } else {
            alert(d.msg || 'خطا در ذخیره‌سازی');
        }
    });
}

// --- نمایش پروفایل عمومی (کلیک روی نام در هدر) ---
export function showPublicProfile(uid) {
    currentProfileId = uid;
    
    // باز کردن مودال پروفایل (فایل includes/modals/profile.php)
    openModal('profileModal');
    
    // پاکسازی اطلاعات قبلی
    setText('view_prof_name', '...');
    setText('view_prof_username', '...');
    setText('view_prof_bio', '...');
    
    apiCall('get_user_info', { uid: uid }).then(d => {
        if(d.status === 'ok') {
            const u = d.data;
            const name = u.first_name + ' ' + (u.last_name || '');
            const av = u.avatar && u.avatar !== 'default' ? u.avatar : 'assets/img/chakavak.png';
            
            setText('view_prof_name', name);
            setText('view_prof_username', '@' + u.username);
            
            const img = document.getElementById('view_prof_avatar');
            if(img) img.src = av;
            
            setText('view_prof_bio', u.bio || 'بدون بیوگرافی');
            
            // نمایش شبکه‌های اجتماعی
            handleSocial('box_prof_tele', 'view_prof_tele', u.social_telegram);
            handleSocial('box_prof_insta', 'view_prof_insta', u.social_instagram);
        }
    });
}

// شروع چت از داخل پروفایل
export function startChatFromProfile() {
    if(currentProfileId && window.openChat) {
        closeModal('profileModal');
        const name = document.getElementById('view_prof_name').innerText;
        const av = document.getElementById('view_prof_avatar').src;
        window.openChat(currentProfileId, 'dm', name, av);
    }
}

// --- لیست مخاطبین ---
export function loadContacts() {
    let listEl = document.getElementById('contactList');
    if (!listEl) return;
    listEl.innerHTML = '<div class="text-center p-4 text-gray-500 text-sm">درحال بارگذاری...</div>';
    
    apiCall('get_contacts').then(d => {
        let h = '';
        if(d.list && d.list.length > 0){
            d.list.forEach(u => {
                let av = u.avatar && u.avatar !== 'default' ? u.avatar : 'assets/img/chakavak.png';
                let name = u.first_name + ' ' + u.last_name;
                
                // تابع handleContactClick تصمیم می‌گیرد که چت باز شود یا کاربر به گروه اضافه شود
                h += `<div onclick="handleContactClick('${u.username}', ${u.id}, '${name}', '${av}')" class="flex items-center p-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded cursor-pointer border-b border-gray-100 dark:border-gray-700 transition">
                    <img src="${av}" class="w-10 h-10 rounded-full mx-3 object-cover bg-gray-200">
                    <div>
                        <div class="font-bold text-gray-800 dark:text-gray-200 text-sm">${name}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">@${u.username}</div>
                    </div>
                </div>`;
            });
        } else {
            h = '<div class="text-center text-gray-400 mt-6 text-sm">مخاطبی یافت نشد.<br>از دکمه + پایین استفاده کنید.</div>';
        }
        listEl.innerHTML = h;
    });
}

// --- لاجیک کلیک روی مخاطب ---
export function handleContactClick(username, uid, name, av) {
    // اگر در حال افزودن عضو به گروه هستیم (از طریق مودال گروه)
    if (state.isAddingToGroup && state.currChat) {
        if(!confirm(`آیا می‌خواهید ${name} را به گروه اضافه کنید؟`)) return;
        
        apiCall('add_group_member', {
            group_id: state.currChat.id,
            target: username // API با یوزرنیم کار می‌کند
        }).then(d => {
            if(d.status === 'ok') {
                alert('عضو با موفقیت اضافه شد');
                closeModal('contactModal');
                state.isAddingToGroup = false; // خروج از حالت افزودن
                if(window.clickHeader) window.clickHeader(); // رفرش لیست اعضای گروه
            } else {
                alert(d.msg || 'خطا در افزودن عضو');
            }
        });
    } 
    // حالت عادی: باز کردن چت
    else {
        startDirect(uid, name, av);
    }
}

// افزودن مخاطب جدید
export function addNewContact() {
    let q = document.getElementById('newContactInput').value;
    if (!q) return alert('نام کاربری یا شماره را وارد کنید');
    
    apiCall('search_contact', { query: q }).then(d => {
        if (d.status == 'ok') {
            closeModal('contactModal');
            if(window.openChat) window.openChat(d.user.id, 'dm', d.user.first_name + ' ' + (d.user.last_name||''), d.user.avatar);
            loadContacts();
            document.getElementById('newContactInput').value = '';
        } else {
            alert('کاربر یافت نشد');
        }
    });
}

export function startDirect(uid, name, av) {
    closeModal('contactModal');
    if(window.openChat) window.openChat(uid, 'dm', name, av);
}

// توابع کمکی
function getValue(id) { return document.getElementById(id) ? document.getElementById(id).value : ''; }
function setValue(id, val) { if(document.getElementById(id)) document.getElementById(id).value = val || ''; }
function setText(id, val) { if(document.getElementById(id)) document.getElementById(id).innerText = val; }
function handleSocial(boxId, textId, val) {
    const box = document.getElementById(boxId);
    if(val) {
        box.classList.remove('hidden');
        document.getElementById(textId).innerText = val;
    } else {
        box.classList.add('hidden');
    }
}

// اتصال به Window
window.loadContacts = loadContacts;
window.handleContactClick = handleContactClick;
window.addNewContact = addNewContact;
window.startDirect = startDirect;
window.saveMyProfile = saveMyProfile;
window.loadMyProfile = loadMyProfile;
window.showPublicProfile = showPublicProfile;
window.startChatFromProfile = startChatFromProfile;