<div id="settingsModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 w-full max-w-sm rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[85vh] animate-scale-in transition-colors duration-300">
        
        <div class="bg-gray-50 dark:bg-gray-700 p-4 border-b dark:border-gray-600 flex justify-between items-center sticky top-0 z-10">
            <h3 class="font-bold text-gray-800 dark:text-gray-100">تنظیمات</h3>
            <button onclick="closeModal('settingsModal')" class="text-gray-500 hover:text-red-500 cursor-pointer text-2xl leading-none">&times;</button>
        </div>
        
        <div class="flex border-b dark:border-gray-600 bg-white dark:bg-gray-800">
            <button onclick="switchSettingsTab('general')" id="tab-btn-general" class="flex-1 py-3 text-sm font-bold text-blue-600 border-b-2 border-blue-600 bg-blue-50 dark:bg-gray-700 transition cursor-pointer">عمومی</button>
            <button onclick="switchSettingsTab('profile')" id="tab-btn-profile" class="flex-1 py-3 text-sm font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer">پروفایل</button>
            <button onclick="switchSettingsTab('security')" id="tab-btn-security" class="flex-1 py-3 text-sm font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer">امنیت</button>
        </div>

        <div class="overflow-y-auto p-4 custom-scrollbar bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200">
            
            <div id="set-tab-general">
                <div onclick="toggleTheme()" class="w-full p-3 flex justify-between items-center hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg mb-2 border border-transparent hover:border-gray-100 dark:hover:border-gray-600 transition cursor-pointer select-none">
                    <span class="flex items-center gap-2 text-sm">🌙 حالت شب</span>
                    <label class="relative inline-flex items-center pointer-events-none">
                        <input type="checkbox" id="themeSwitch" class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <div onclick="toggleLang()" class="w-full p-3 flex justify-between items-center hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg mb-2 border border-transparent hover:border-gray-100 dark:hover:border-gray-600 transition cursor-pointer select-none">
                    <span class="flex items-center gap-2 text-sm">🌐 زبان</span>
                    <span class="text-xs font-bold text-blue-600 bg-blue-100 dark:bg-blue-900 dark:text-blue-200 px-2 py-1 rounded">FA / EN</span>
                </div>

                <div onclick="requestNotifyPermission()" class="w-full p-3 flex justify-between items-center hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg mb-2 border border-transparent hover:border-gray-100 dark:hover:border-gray-600 transition cursor-pointer select-none">
                    <span class="flex items-center gap-2 text-sm">🔔 اعلان‌ها</span>
                    <span id="notifStatus" class="text-xs text-gray-400">بررسی...</span>
                </div>
                
                <hr class="my-4 border-gray-100 dark:border-gray-700">

                <button id="btnUpdateApp" onclick="appUpdate()" class="w-full p-3 mb-2 text-blue-600 dark:text-blue-400 font-bold text-center hover:bg-blue-50 dark:hover:bg-gray-700 rounded-lg transition text-sm flex items-center justify-center gap-2 cursor-pointer">
                    <span>🔄</span> بروزرسانی برنامه
                </button>
                
                <button onclick="logout()" class="w-full p-3 text-red-500 font-bold text-center hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition text-sm cursor-pointer">
                    خروج از حساب
                </button>
            </div>

            <div id="set-tab-profile" class="hidden">
                <div class="text-center mb-6 relative">
                    <div class="relative inline-block group">
                        <img id="set_prof_avatar" src="assets/img/chakavak.png" class="w-24 h-24 rounded-full border-4 border-gray-100 dark:border-gray-700 object-cover bg-gray-200">
                        <label for="set_prof_file" class="absolute bottom-0 right-0 bg-blue-600 text-white rounded-full p-2 shadow-lg cursor-pointer hover:bg-blue-700 transition transform hover:scale-110">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </label>
                        <input type="file" id="set_prof_file" class="hidden" accept="image/*" onchange="previewProfileImage(this)">
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold mb-1 text-gray-500">نام</label>
                            <input id="set_prof_fname" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold mb-1 text-gray-500">نام خانوادگی</label>
                            <input id="set_prof_lname" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold mb-1 text-gray-500">نام کاربری (@)</label>
                        <input id="set_prof_user" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500 ltr text-left">
                    </div>

                    <div>
                        <label class="block text-xs font-bold mb-1 text-gray-500">بیوگرافی</label>
                        <textarea id="set_prof_bio" rows="2" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
                    </div>

                    <div class="pt-4">
                        <h4 class="text-xs font-bold text-gray-400 mb-2 border-b dark:border-gray-700 pb-1">شبکه‌های اجتماعی</h4>
                        <div class="space-y-2">
                            <input id="set_prof_tele" placeholder="Telegram ID" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500 ltr text-left">
                            <input id="set_prof_insta" placeholder="Instagram ID" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500 ltr text-left">
                        </div>
                    </div>

                    <button onclick="saveMyProfile()" class="w-full bg-blue-600 text-white py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200/50 hover:bg-blue-700 transition mt-4">
                        ذخیره تغییرات
                    </button>
                </div>
            </div>

            <div id="set-tab-security" class="hidden">
                <div id="securitySettingsBox">
                    </div>
            </div>

        </div>
    </div>
</div>

<script>
function previewProfileImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('set_prof_avatar').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>