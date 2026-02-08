<div class="max-w-2xl mx-auto">
    <h3 class="font-bold text-xl mb-6 flex items-center gap-2 text-blue-600">
        💬 تنظیمات سامانه پیامک (IPPanel)
    </h3>
    
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="mb-6">
            <label class="block text-sm font-bold text-gray-700 mb-2">کلید دسترسی (API Key)</label>
            <input id="inp_ippanel_key" class="w-full border border-gray-300 p-3 rounded-lg ltr text-left focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition" type="password" placeholder="e.g. U-H9...">
            <p class="text-xs text-gray-400 mt-1">کلید API دریافتی از پنل IPPanel</p>
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-bold text-gray-700 mb-2">شماره خط ارسال کننده</label>
            <input id="inp_ippanel_line" class="w-full border border-gray-300 p-3 rounded-lg ltr text-left focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition" placeholder="e.g. +983000...">
        </div>

        <div class="flex justify-end pt-4 border-t">
            <button onclick="saveIPPanel()" class="bg-blue-600 text-white px-8 py-2.5 rounded-lg shadow hover:bg-blue-700 transition font-bold flex items-center gap-2">
                <span>ذخیره تنظیمات پیامک</span>
            </button>
        </div>
    </div>
</div>