const translations = {
    en: {
        app_name: "Chakavak",
        edit: "Edit",
        search_placeholder: "Search...",
        tab_all: "All",
        tab_personal: "Personal",
        tab_groups: "Groups",
        tab_channels: "Channels",
        nav_contacts: "Contacts",
        nav_chats: "Chats",
        nav_settings: "Settings",
        new_group: "New Group",
        new_channel: "New Channel",
        new_contact: "New Contact",
        dark_mode: "Dark Mode",
        language: "Language",
        save: "Save",
        cancel: "Cancel",
        create: "Create",
        log_out: "Log Out",
        first_name: "First Name",
        last_name: "Last Name",
        username: "Username",
        bio: "Bio",
        online: "online",
        members: "members",
        subscribers: "subscribers",
        send_msg: "Message...",
        add_member: "Add Member",
        remove: "Remove",
        file: "File",
        photo: "Photo",
        voice_rec: "Hold for Voice",
        typing: "typing...",
        admin: "admin",
        // *** کلیدهای جدید ***
        add_contact_placeholder: "Username or Phone...",
        add_btn: "Add",
        user_not_found: "User not found"
    },
    fa: {
        app_name: "چکاوک",
        edit: "ویرایش",
        search_placeholder: "جستجو...",
        tab_all: "همه",
        tab_personal: "شخصی",
        tab_groups: "گروه‌ها",
        tab_channels: "کانال‌ها",
        nav_contacts: "مخاطبین",
        nav_chats: "گفتگوها",
        nav_settings: "تنظیمات",
        new_group: "گروه جدید",
        new_channel: "کانال جدید",
        new_contact: "مخاطب جدید",
        dark_mode: "حالت شب",
        language: "زبان",
        save: "ذخیره",
        cancel: "لغو",
        create: "ایجاد",
        log_out: "خروج",
        first_name: "نام",
        last_name: "نام خانوادگی",
        username: "نام کاربری",
        bio: "بیوگرافی",
        online: "آنلاین",
        members: "عضو",
        subscribers: "دنبال‌کننده",
        send_msg: "ارسال پیام...",
        add_member: "افزودن عضو",
        remove: "حذف",
        file: "فایل",
        photo: "تصویر",
        voice_rec: "ضبط صدا (نگه دارید)",
        typing: "در حال نوشتن...",
        admin: "مدیر",
        // *** کلیدهای جدید ***
        add_contact_placeholder: "نام کاربری یا موبایل...",
        add_btn: "افزودن",
        user_not_found: "کاربر یافت نشد"
    }
};

function t(key) {
    const lang = localStorage.getItem('lang') || 'fa';
    return translations[lang][key] || key;
}

function applyLang() {
    const lang = localStorage.getItem('lang') || 'fa';
    document.documentElement.dir = lang === 'fa' ? 'rtl' : 'ltr';
    document.documentElement.lang = lang;
    
    document.querySelectorAll('[data-t]').forEach(el => {
        el.innerText = t(el.getAttribute('data-t'));
    });
    
    document.querySelectorAll('[data-tp]').forEach(el => {
        el.placeholder = t(el.getAttribute('data-tp'));
    });
}