<div class="max-w-2xl mx-auto">
    <h3 class="font-bold text-xl mb-6 flex items-center gap-2 text-indigo-600">
        🛡️ احراز هویت دو مرحله‌ای (2FA)
    </h3>
    
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h4 class="font-bold text-gray-800">وضعیت 2FA</h4>
                <p class="text-sm text-gray-500 mt-1">اجباری کردن ورود دو مرحله‌ای (پیامک/Google Auth) برای همه کاربران</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" id="chk_enable_2fa" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
            </label>
        </div>

        <div class="bg-indigo-50 text-indigo-800 p-4 rounded-lg text-sm mb-6 border border-indigo-100">
            <strong class="block mb-1">نکته امنیتی:</strong>
            با فعال‌سازی این گزینه، تمام کاربران (شامل مدیران) برای ورود نیاز به تایید کد پیامکی خواهند داشت. از تنظیم صحیح پنل پیامک اطمینان حاصل کنید.
        </div>

        <div class="flex justify-end pt-4 border-t">
            <button onclick="save2FA()" class="bg-indigo-600 text-white px-8 py-2.5 rounded-lg shadow hover:bg-indigo-700 transition font-bold">
                ذخیره تنظیمات امنیتی
            </button>
        </div>
    </div>
</div>