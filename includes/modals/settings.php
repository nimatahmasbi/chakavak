<div id="settingsModal" class="hidden fixed inset-0 bg-[var(--bg-primary)] z-50 flex flex-col">
    <div class="pt-safe px-4 pb-3 bg-[var(--bg-secondary)] flex justify-between items-center border-b border-[var(--border-color)]">
        <button onclick="closeModal('settingsModal')" class="text-[var(--accent-color)]" data-t="cancel">Close</button>
        <span class="font-bold" data-t="nav_settings">Settings</span>
        <button onclick="saveProfile()" class="text-[var(--accent-color)] font-bold" data-t="save">Save</button>
    </div>

    <div class="p-4 space-y-4 overflow-y-auto flex-1">
        <div class="flex items-center gap-4 bg-[var(--bg-secondary)] p-4 rounded-xl">
            <img src="<?=$me['avatar']=='default'?'assets/img/chakavak.png':$me['avatar']?>" class="w-16 h-16 rounded-full object-cover border border-gray-100">
            <div>
                <h2 class="font-bold text-xl text-[var(--text-primary)]"><?=$me['first_name'].' '.$me['last_name']?></h2>
                <p class="text-[var(--text-secondary)] dir-ltr text-right"><?=$me['phone']?></p>
                <p class="text-[var(--accent-color)] text-sm">@<?=$me['username']?></p>
            </div>
        </div>
        
        <div class="bg-[var(--bg-secondary)] rounded-xl overflow-hidden">
            <div class="p-3 border-b border-[var(--border-color)] flex justify-between items-center">
                <span class="flex items-center gap-2">🌙 <span data-t="dark_mode">Dark Mode</span></span>
                <input type="checkbox" id="themeSwitch" onclick="toggleTheme()" class="w-5 h-5">
            </div>
            <div class="p-3 border-b border-[var(--border-color)] flex justify-between items-center">
                <span class="flex items-center gap-2">🌐 <span data-t="language">Language</span></span>
                <input type="checkbox" id="langSwitch" onclick="toggleLang()" class="w-5 h-5">
            </div>
            
            <button onclick="requestNotifyPermission()" class="w-full p-3 flex justify-between items-center text-[var(--text-primary)] hover:bg-[var(--bg-primary)] transition">
                <span class="flex items-center gap-2">🔔 <span data-t="enable_notif">Enable Notifications</span></span>
                <span id="notifStatus" class="text-xs text-gray-500">Tap to allow</span>
            </button>
        </div>

        <div class="space-y-2">
            <label class="text-xs text-[var(--text-secondary)] px-2">اطلاعات پایه</label>
            <input id="setFname" value="<?=$me['first_name']?>" class="w-full bg-[var(--bg-secondary)] p-3 rounded-xl outline-none text-[var(--text-primary)]" placeholder="نام">
            <input id="setLname" value="<?=$me['last_name']?>" class="w-full bg-[var(--bg-secondary)] p-3 rounded-xl outline-none text-[var(--text-primary)]" placeholder="نام خانوادگی">
            <input id="setUname" value="<?=$me['username']?>" class="w-full bg-[var(--bg-secondary)] p-3 rounded-xl outline-none text-[var(--text-primary)] text-left dir-ltr" placeholder="Username">
            <textarea id="setBio" class="w-full bg-[var(--bg-secondary)] p-3 rounded-xl outline-none h-20 text-[var(--text-primary)]" placeholder="بیوگرافی"><?=$me['bio']?></textarea>
        </div>

        <div class="space-y-2 border-t border-[var(--border-color)] pt-4">
            <label class="text-xs text-[var(--text-secondary)] px-2">شبکه‌های اجتماعی</label>
            <div class="grid grid-cols-2 gap-2">
                <input id="setTele" value="<?=$me['social_telegram']??''?>" class="w-full bg-[var(--bg-secondary)] p-3 rounded-xl outline-none text-[var(--text-primary)] text-left dir-ltr" placeholder="Telegram">
                <input id="setInsta" value="<?=$me['social_instagram']??''?>" class="w-full bg-[var(--bg-secondary)] p-3 rounded-xl outline-none text-[var(--text-primary)] text-left dir-ltr" placeholder="Instagram">
                <input id="setWhats" value="<?=$me['social_whatsapp']??''?>" class="w-full bg-[var(--bg-secondary)] p-3 rounded-xl outline-none text-[var(--text-primary)] text-left dir-ltr" placeholder="WhatsApp">
                <input id="setLinked" value="<?=$me['social_linkedin']??''?>" class="w-full bg-[var(--bg-secondary)] p-3 rounded-xl outline-none text-[var(--text-primary)] text-left dir-ltr" placeholder="LinkedIn">
            </div>
        </div>

        <button onclick="logout()" class="w-full bg-[var(--bg-secondary)] text-red-500 py-3 rounded-xl font-bold mt-4 mb-4" data-t="log_out">Log Out</button>
    </div>
</div>