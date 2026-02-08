<?php 
require 'config.php'; 
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }
if (isset($_GET['logout'])) { session_destroy(); header("Location: login.php"); exit; } 
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت چکاوک</title>
    <script src="../libs/tailwind.js"></script>
    <link href="../libs/vazir/font.css" rel="stylesheet">
    <script src="../libs/crypto-js.js"></script>
    <style> body { font-family: 'Vazirmatn', sans-serif; } </style>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8 bg-white p-4 rounded-xl shadow-sm">
            <h1 class="text-2xl font-bold text-gray-800">مدیریت چکاوک</h1>
            <div class="flex gap-4">
                <a href="../dashboard.php" target="_blank" class="text-blue-600 hover:underline">مشاهده سایت</a>
                <a href="?logout=1" class="text-red-600 font-bold">خروج</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-blue-500 text-white p-6 rounded-xl shadow">
                <div class="text-3xl font-bold" id="stat-users">...</div><div>کاربران</div>
            </div>
            <div class="bg-green-500 text-white p-6 rounded-xl shadow">
                <div class="text-3xl font-bold" id="stat-groups">...</div><div>گروه‌ها</div>
            </div>
            <div class="bg-purple-500 text-white p-6 rounded-xl shadow">
                <div class="text-3xl font-bold" id="stat-msgs">...</div><div>پیام‌ها</div>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 mb-4 border-b border-gray-300 pb-2">
            <button onclick="loadSection('users')" class="tab-btn bg-blue-600 text-white px-5 py-2 rounded-lg shadow transition" id="btn-users">👥 کاربران</button>
            <button onclick="loadSection('groups')" class="tab-btn bg-white text-gray-700 px-5 py-2 rounded-lg shadow transition" id="btn-groups">📢 گروه‌ها</button>
            
            <div class="w-px bg-gray-300 mx-2"></div>
            
            <button onclick="loadSection('ippanel')" class="tab-btn bg-white text-gray-700 px-5 py-2 rounded-lg shadow transition hover:bg-blue-50" id="btn-ippanel">💬 پیامک</button>
            <button onclick="loadSection('twofa')" class="tab-btn bg-white text-gray-700 px-5 py-2 rounded-lg shadow transition hover:bg-indigo-50" id="btn-twofa">🛡️ 2FA</button>
            <button onclick="loadSection('passkey')" class="tab-btn bg-white text-gray-700 px-5 py-2 rounded-lg shadow transition hover:bg-emerald-50" id="btn-passkey">🔑 Passkey</button>
        </div>

        <div id="content-area" class="bg-white p-6 rounded-xl shadow min-h-[400px]">
            <div class="text-center text-gray-500 mt-10">در حال بارگذاری...</div>
        </div>
    </div>

    <?php include 'sections/modals.php'; ?>

    <script type="module" src="../assets/js/admin.js?v=5"></script>
</body>
</html>