<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <div>
            <h2 class="font-bold text-gray-800 text-lg">لیست کاربران</h2>
            <p class="text-xs text-gray-500 mt-1">مدیریت تمام اعضای ثبت نام شده</p>
        </div>
        <span class="text-xs font-bold bg-blue-100 text-blue-600 px-3 py-1.5 rounded-full">Users</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-right border-collapse">
            <thead class="bg-gray-50 border-b border-gray-100 text-gray-500 text-xs uppercase font-semibold">
                <tr>
                    <th class="p-4 w-16 text-center">ID</th>
                    <th class="p-4">مشخصات کاربر</th>
                    <th class="p-4">شماره تماس</th>
                    <th class="p-4 text-center">وضعیت</th>
                    <th class="p-4 text-center">عملیات</th>
                </tr>
            </thead>
            <tbody id="usersTable" class="divide-y divide-gray-100 text-sm text-gray-700 bg-white">
                <tr>
                    <td colspan="5" class="p-8 text-center text-gray-400 flex flex-col items-center justify-center gap-2">
                        <div class="w-6 h-6 border-2 border-gray-300 border-t-blue-600 rounded-full animate-spin"></div>
                        <span>درحال دریافت اطلاعات کاربران...</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>