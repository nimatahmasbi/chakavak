<div id="settingsModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white w-full max-w-sm rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[80vh] animate-scale-in">
        
        <div class="bg-gray-50 p-4 border-b flex justify-between items-center sticky top-0 z-10">
            <h3 class="font-bold text-gray-800">تنظیمات</h3>
            <button onclick="closeModal('settingsModal')" class="text-gray-500 text-2xl leading-none hover:text-red-500 cursor-pointer p-2">&times;</button>
        </div>
        
        <div class="flex border-b bg-white">
            <button onclick="switchSettingsTab('general')" id="tab-btn-general" class="flex-1 py-3 text-sm font-bold text-blue-600 border-b-2 border-blue-600 bg-blue-50 transition cursor-pointer">عمومی</button>
            <button onclick="switchSettingsTab('security')" id="tab-btn-security" class="flex-1 py-3 text-sm font-bold text-gray-500 hover:bg-gray-50 transition cursor-pointer">امنیت</button>
        </div>

        <div class="overflow-y-auto p-4 custom-scrollbar bg-white">
            
            <div id="set-tab-general">
                <div onclick="toggleTheme()" class="w-full p-3 flex justify-between items-center hover:bg-gray-50 rounded-lg mb-2 border border-transparent hover:border-gray-100 transition cursor-pointer select-none">
                    <span class="flex items-center gap-2 text-sm">🌙 حالت شب</span>
                    <label class="relative inline-flex items-center pointer-events-none">
                        <input type="checkbox" id="themeSwitch" class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <div onclick="toggleLang()" class="w-full p-3 flex justify-between items-center hover:bg-gray-50 rounded-lg mb-2 border border-transparent hover:border-gray-100 transition cursor-pointer select-none">
                    <span class="flex items-center gap-2 text-sm">🌐 زبان</span>
                    <span class="text-xs font-bold text-blue-600 bg-blue-100 px-2 py-1 rounded">FA / EN</span>
                </div>

                <div onclick="requestNotifyPermission()" class="w-full p-3 flex justify-between items-center hover:bg-gray-50 rounded-lg mb-2 border border-transparent hover:border-gray-100 transition cursor-pointer select-none">
                    <span class="flex items-center gap-2 text-sm">🔔 اعلان‌ها</span>
                    <span id="notifStatus" class="text-xs text-gray-400">بررسی...</span>
                </div>
                
                <hr class="my-4 border-gray-100">

                <button id="btnUpdateApp" onclick="appUpdate()" class="w-full p-3 mb-2 text-blue-600 font-bold text-center hover:bg-blue-50 rounded-lg transition text-sm flex items-center justify-center gap-2 cursor-pointer">
                    <span>🔄</span> بروزرسانی برنامه
                </button>
                
                <button onclick="logout()" class="w-full p-3 text-red-500 font-bold text-center hover:bg-red-50 rounded-lg transition text-sm cursor-pointer">
                    خروج از حساب
                </button>
                
                <div class="text-center mt-4">
                    <span class="text-[10px] text-gray-300">نسخه 6.2 (PWA)</span>
                </div>
            </div>

            <div id="set-tab-security" class="hidden">
                <div id="securitySettingsBox">
                    </div>
            </div>

        </div>
    </div>
</div>