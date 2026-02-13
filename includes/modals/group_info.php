<div id="groupInfoModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm animate-fade-in">
    <div class="bg-white dark:bg-gray-800 w-full max-w-sm rounded-2xl p-0 shadow-2xl flex flex-col max-h-[85vh]">
        
        <div class="h-24 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-t-2xl relative shrink-0">
            <button onclick="closeModal('groupInfoModal')" class="absolute top-3 right-3 bg-black/20 text-white rounded-full p-1.5 hover:bg-black/40 transition">✕</button>
        </div>

        <div class="px-6 relative flex-1 overflow-y-auto custom-scrollbar pb-6">
            <div class="-mt-10 mb-4 flex flex-col items-center">
                <div class="relative group">
                    <img id="gInfoAvatar" src="assets/img/chakavak.png" class="w-20 h-20 rounded-full border-4 border-white dark:border-gray-800 bg-gray-200 object-cover">
                    <div id="adminActions" class="hidden absolute inset-0 bg-black/50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition cursor-pointer">
                        <label for="gInfoAvatarInput" class="text-white text-xs cursor-pointer">ویرایش</label>
                        <input type="file" id="gInfoAvatarInput" hidden accept="image/*">
                    </div>
                </div>
                
                <h3 id="gInfoName" class="font-bold text-lg mt-2 text-gray-800 dark:text-white">...</h3>
                <input id="gInfoNameEdit" class="hidden mt-2 border rounded p-1 text-sm text-center bg-transparent dark:text-white border-gray-300 dark:border-gray-600" placeholder="نام جدید">
                
                <button onclick="saveGroupEdit()" id="btnSaveGroup" class="hidden mt-2 text-xs bg-green-500 text-white px-3 py-1 rounded-full">ذخیره</button>
            </div>

            <div id="addMemberBox" class="hidden mb-6 bg-gray-50 dark:bg-gray-700/50 p-3 rounded-xl border border-gray-100 dark:border-gray-700">
                <label class="text-xs font-bold text-gray-500 dark:text-gray-400 mb-2 block">افزودن عضو جدید</label>
                <div class="flex gap-2">
                    <input id="addMemberInput" type="text" placeholder="نام کاربری یا شماره..." class="flex-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500 dark:text-white" onkeypress="if(event.key === 'Enter') addMemberFromInput()">
                    <button onclick="addMemberFromInput()" class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-3 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </button>
                </div>
                <button onclick="openContactPickerForGroup()" class="w-full mt-2 text-blue-600 dark:text-blue-400 text-xs font-bold hover:underline flex items-center justify-center gap-1">
                    <span>📂</span> انتخاب از مخاطبین
                </button>
            </div>

            <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">اعضای گروه</h4>
            <div id="gMembersList" class="space-y-1">
                </div>
        </div>
    </div>
</div>