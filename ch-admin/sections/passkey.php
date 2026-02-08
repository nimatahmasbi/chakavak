<div class="max-w-2xl mx-auto">
    <h3 class="font-bold text-xl mb-6 flex items-center gap-2 text-emerald-600">
        🔑 ورود با Passkey
    </h3>
    
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h4 class="font-bold text-gray-800">فعال‌سازی Passkey</h4>
                <p class="text-sm text-gray-500 mt-1">اجازه به کاربران برای ورود با اثر انگشت، تشخیص چهره یا پین دستگاه</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" id="chk_enable_passkey" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
            </label>
        </div>

        <div class="bg-emerald-50 text-emerald-800 p-4 rounded-lg text-sm mb-6 border border-emerald-100">
            <strong class="block mb-1">فناوری نوین:</strong>
            Passkey استانداردی جدید برای حذف رمزهای عبور است. این قابلیت امنیت را به شدت افزایش داده و تجربه کاربری را بهبود می‌بخشد.
        </div>

        <div class="flex justify-end pt-4 border-t">
            <button onclick="savePasskey()" class="bg-emerald-600 text-white px-8 py-2.5 rounded-lg shadow hover:bg-emerald-700 transition font-bold">
                ذخیره تنظیمات Passkey
            </button>
        </div>
    </div>
</div>