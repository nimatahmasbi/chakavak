<div id="settingsModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-sm rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[80vh]">
        <div class="bg-gray-50 p-4 border-b flex justify-between items-center sticky top-0 z-10">
            <h3 class="font-bold text-gray-800" data-t="settings">تنظیمات</h3>
            <button onclick="closeModal('settingsModal')" class="text-gray-500 hover:text-red-500 text-2xl leading-none">&times;</button>
        </div>
        
        <div class="flex border-b">
            <button onclick="switchSettingsTab('general')" id="tab-btn-general" class="flex-1 py-3 text-sm font-bold text-blue-600 border-b-2 border-blue-600 bg-blue-50">عمومی</button>
            <button onclick="switchSettingsTab('security')" id="tab-btn-security" class="flex-1 py-3 text-sm font-bold text-gray-500 hover:bg-gray-50">امنیت</button>
        </div>

        <div class="overflow-y-auto p-4 custom-scrollbar">
            
            <div id="set-tab-general">
                <button onclick="toggleTheme()" class="w-full p-3 flex justify-between items-center text-[var(--text-primary)] hover:bg-[var(--bg-primary)] transition rounded-lg mb-2">
                    <span class="flex items-center gap-2">🌙 <span data-t="dark_mode">حالت شب</span></span>
                    <label class="relative inline-flex items-center cursor-pointer pointer-events-none">
                        <input type="checkbox" id="themeSwitch" class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </button>

                <button onclick="toggleLang()" class="w-full p-3 flex justify-between items-center text-[var(--text-primary)] hover:bg-[var(--bg-primary)] transition rounded-lg mb-2">
                    <span class="flex items-center gap-2">🌐 <span data-t="language">زبان</span></span>
                    <span class="text-xs font-bold text-blue-600 bg-blue-100 px-2 py-1 rounded">FA / EN</span>
                </button>

                <button onclick="requestNotifyPermission()" class="w-full p-3 flex justify-between items-center text-[var(--text-primary)] hover:bg-[var(--bg-primary)] transition rounded-lg mb-2">
                    <span class="flex items-center gap-2">🔔 <span data-t="notifications">اعلان‌ها</span></span>
                    <span id="notifStatus" class="text-xs text-gray-400">بررسی...</span>
                </button>
                
                <hr class="my-4 border-gray-200">
                
                <button onclick="logout()" class="w-full p-3 text-red-500 font-bold text-center hover:bg-red-50 rounded-lg transition" data-t="log_out">
                    خروج از حساب
                </button>
            </div>

            <div id="set-tab-security" class="hidden">
                <div id="securitySettingsBox">
                    <div class="text-center text-gray-400 mt-4">در حال بارگذاری...</div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function switchSettingsTab(tab) {
    document.querySelectorAll('[id^="set-tab-"]').forEach(el => el.classList.add('hidden'));
    document.getElementById('set-tab-'+tab).classList.remove('hidden');
    
    document.querySelectorAll('[id^="tab-btn-"]').forEach(btn => {
        btn.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600', 'bg-blue-50');
        btn.classList.add('text-gray-500');
    });
    
    const btn = document.getElementById('tab-btn-'+tab);
    btn.classList.remove('text-gray-500');
    btn.classList.add('text-blue-600', 'border-b-2', 'border-blue-600', 'bg-blue-50');

    if(tab === 'security' && window.loadSecuritySettings) {
        window.loadSecuritySettings();
    }
}
</script>