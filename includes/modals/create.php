<div id="createModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm animate-fade-in">
    <div class="bg-white dark:bg-gray-800 w-full max-w-sm rounded-2xl p-6 shadow-2xl transform transition-all scale-100">
        
        <h3 id="createTitle" class="text-lg font-bold mb-4 text-center text-gray-800 dark:text-white">ساخت گروه جدید</h3>
        
        <div class="mb-6 text-center">
            <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full mx-auto mb-3 flex items-center justify-center text-3xl">📷</div>
            <p class="text-xs text-gray-400">آیکون به صورت پیش‌فرض انتخاب می‌شود</p>
        </div>

        <input type="text" id="createInput" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 mb-6 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition text-gray-800 dark:text-white" placeholder="نام را وارد کنید...">
        
        <div class="flex gap-3">
            <button onclick="closeModal('createModal')" class="flex-1 py-2.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition font-bold">لغو</button>
            <button onclick="doCreate()" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-lg shadow-blue-200 transition font-bold">ایجاد کردن</button>
        </div>
    </div>
</div>