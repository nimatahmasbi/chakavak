<div id="profileModal" class="hidden fixed inset-0 bg-black/60 z-[60] flex items-center justify-center p-4 backdrop-blur-sm animate-fade-in">
    <div class="bg-white dark:bg-gray-800 w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden transform transition-all scale-100 flex flex-col max-h-[80vh]">
        
        <div class="h-24 bg-gradient-to-r from-blue-500 to-purple-600 relative">
            <button onclick="closeModal('profileModal')" class="absolute top-3 right-3 bg-black/20 hover:bg-black/40 text-white rounded-full p-1.5 transition backdrop-blur-md">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="px-6 pb-6 relative flex-1 overflow-y-auto custom-scrollbar">
            <div class="-mt-12 mb-4 flex justify-center">
                <img id="view_prof_avatar" src="assets/img/chakavak.png" class="w-24 h-24 rounded-full border-4 border-white dark:border-gray-800 object-cover bg-gray-200 shadow-lg">
            </div>

            <div class="text-center mb-6">
                <h3 id="view_prof_name" class="text-xl font-black text-gray-800 dark:text-white mb-1">...</h3>
                <div id="view_prof_username" class="text-sm text-blue-500 font-mono bg-blue-50 dark:bg-blue-900/30 inline-block px-3 py-1 rounded-full">@username</div>
            </div>

            <div class="mb-6">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">بیوگرافی</label>
                <div id="view_prof_bio" class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed bg-gray-50 dark:bg-gray-700/50 p-3 rounded-xl border border-gray-100 dark:border-gray-700 min-h-[60px]">
                    -
                </div>
            </div>

            <div class="space-y-3">
                <div id="box_prof_tele" class="hidden flex items-center p-3 rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-700/30">
                    <span class="text-blue-500 text-lg ml-3">✈️</span>
                    <div class="flex-1">
                        <div class="text-[10px] text-gray-400">تلگرام</div>
                        <div id="view_prof_tele" class="text-sm font-bold text-gray-700 dark:text-gray-200 dir-ltr text-left"></div>
                    </div>
                </div>

                <div id="box_prof_insta" class="hidden flex items-center p-3 rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-700/30">
                    <span class="text-pink-600 text-lg ml-3">📸</span>
                    <div class="flex-1">
                        <div class="text-[10px] text-gray-400">اینستاگرام</div>
                        <div id="view_prof_insta" class="text-sm font-bold text-gray-700 dark:text-gray-200 dir-ltr text-left"></div>
                    </div>
                </div>
            </div>

            <button onclick="startChatFromProfile()" class="mt-8 w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-blue-200/50 dark:shadow-none transition transform active:scale-95 flex items-center justify-center gap-2">
                <span>💬</span> شروع گفتگو
            </button>
        </div>
    </div>
</div>