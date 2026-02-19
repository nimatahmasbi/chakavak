<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <div>
            <h2 class="font-bold text-gray-800 text-lg">لیست گفتگوها</h2>
            <p class="text-xs text-gray-500 mt-1">مدیریت گروه‌ها و کانال‌های ایجاد شده</p>
        </div>
        <span class="text-xs font-bold bg-purple-100 text-purple-600 px-3 py-1.5 rounded-full">Groups & Channels</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-right border-collapse">
            <thead class="bg-gray-50 border-b border-gray-100 text-gray-500 text-xs uppercase font-semibold">
                <tr>
                    <th class="p-4 w-16 text-center">ID</th>
                    <th class="p-4">نام گفتگو</th>
                    <th class="p-4 text-center">نوع</th>
                    <th class="p-4">تاریخ ایجاد</th>
                    <th class="p-4 text-center">عملیات</th>
                </tr>
            </thead>
            <tbody id="groupsTable" class="divide-y divide-gray-100 text-sm text-gray-700 bg-white">
                <tr>
                    <td colspan="5" class="p-8 text-center text-gray-400 flex flex-col items-center justify-center gap-2">
                        <div class="w-6 h-6 border-2 border-gray-300 border-t-purple-600 rounded-full animate-spin"></div>
                        <span>درحال دریافت اطلاعات گروه‌ها...</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>