<div class="flex justify-between items-center mb-4">
    <h3 class="font-bold text-lg">مدیریت کاربران</h3>
    <button onclick="loadList('users')" class="text-gray-500 hover:text-blue-600 text-sm">🔄 بروزرسانی</button>
</div>
<div class="overflow-x-auto">
    <table class="w-full text-right text-sm">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="p-3">شناسه</th>
                <th class="p-3">نام کامل</th>
                <th class="p-3">نام کاربری</th>
                <th class="p-3">شماره</th>
                <th class="p-3">وضعیت</th>
                <th class="p-3">عملیات</th>
            </tr>
        </thead>
        <tbody id="list-users">
            </tbody>
    </table>
</div>