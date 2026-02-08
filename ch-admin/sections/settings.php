<h3 class="font-bold text-lg mb-6 border-b pb-2">تنظیمات سامانه</h3>
<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
        <h4 class="font-bold text-blue-600 mb-4 flex items-center gap-2">💬 سامانه پیامک (IPPanel)</h4>
        <div class="mb-4">
            <label class="block text-sm text-gray-600 mb-1">API Key</label>
            <input id="set_ippanel_key" class="w-full border p-2 rounded ltr text-left bg-white focus:border-blue-500 outline-none" type="password">
        </div>
        <div class="mb-2">
            <label class="block text-sm text-gray-600 mb-1">شماره خط ارسال</label>
            <input id="set_ippanel_line" class="w-full border p-2 rounded ltr text-left bg-white focus:border-blue-500 outline-none">
        </div>
    </div>

    <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
        <h4 class="font-bold text-red-600 mb-4 flex items-center gap-2">🛡️ امنیت و دسترسی</h4>
        <div class="flex items-center justify-between mb-4 bg-white p-3 rounded border hover:bg-gray-50 transition cursor-pointer" onclick="document.getElementById('set_enable_2fa').click()">
            <span class="text-sm">احراز هویت دو مرحله‌ای (2FA)</span>
            <input type="checkbox" id="set_enable_2fa" class="w-5 h-5 accent-blue-600">
        </div>
        <div class="flex items-center justify-between bg-white p-3 rounded border hover:bg-gray-50 transition cursor-pointer" onclick="document.getElementById('set_enable_passkey').click()">
            <span class="text-sm">ورود با Passkey</span>
            <input type="checkbox" id="set_enable_passkey" class="w-5 h-5 accent-blue-600">
        </div>
    </div>
</div>
<div class="mt-8 flex justify-end">
    <button onclick="saveSettings()" class="bg-blue-600 text-white px-8 py-2 rounded-lg shadow hover:bg-blue-700 transition font-bold">ذخیره تنظیمات</button>
</div>